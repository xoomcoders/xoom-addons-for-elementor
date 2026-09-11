<?php
/**
 * Settings repository.
 *
 * All plugin state lives in four flat options (one per group). Widget and
 * module state is stored as an explicit `id => bool` map so that toggling a
 * component is a single, race-free write.
 *
 * @package Xoom_Addons
 */

namespace Xoom_Addons;

defined( 'ABSPATH' ) || exit;

/**
 * Typed, cached access to plugin options.
 */
class Settings {

	const GROUP_WIDGETS     = 'widgets';
	const GROUP_MODULES     = 'modules';
	const GROUP_GENERAL     = 'general';
	const GROUP_PERFORMANCE = 'performance';
	const GROUP_ADVANCED    = 'advanced';

	/**
	 * Option name for each group.
	 *
	 * @var array<string, string>
	 */
	private static $option_keys = array(
		self::GROUP_WIDGETS     => 'xoom_addons_widget_status',
		self::GROUP_MODULES     => 'xoom_addons_module_status',
		self::GROUP_GENERAL     => 'xoom_addons_general',
		self::GROUP_PERFORMANCE => 'xoom_addons_performance',
		self::GROUP_ADVANCED    => 'xoom_addons_advanced',
	);

	/**
	 * Per-request cache of resolved group values.
	 *
	 * @var array<string, array>
	 */
	private $cache = array();

	/**
	 * Default values for scalar setting groups.
	 *
	 * Widget and module groups intentionally default to an empty map: their
	 * defaults are owned by the registries and resolved by {@see is_enabled()}.
	 *
	 * @param string|null $group Optional group to restrict the result to.
	 * @return array
	 */
	public function defaults( $group = null ) {
		$defaults = array(
			self::GROUP_WIDGETS     => array(),
			self::GROUP_MODULES     => array(),
			self::GROUP_GENERAL     => array(
				'category_label' => __( 'Xoom Addons', 'xoom-addons-for-elementor' ),
				'show_pro_items' => true,
			),
			self::GROUP_PERFORMANCE => array(
				'optimize_assets'     => true,
				'defer_scripts'       => false,
				'remove_emoji_script' => false,
			),
			self::GROUP_ADVANCED    => array(
				'delete_data_on_uninstall' => false,
			),
		);

		/**
		 * Filter the registered default settings.
		 *
		 * @param array $defaults Defaults keyed by group.
		 */
		$defaults = apply_filters( 'xoom_addons_settings_defaults', $defaults );

		if ( null === $group ) {
			return $defaults;
		}

		return isset( $defaults[ $group ] ) ? $defaults[ $group ] : array();
	}

	/**
	 * Sanitisation schema per group (key => type).
	 *
	 * @param string $group Group name.
	 * @return array<string, string>
	 */
	private function schema( $group ) {
		switch ( $group ) {
			case self::GROUP_GENERAL:
				return array(
					'category_label' => 'text',
					'show_pro_items' => 'bool',
				);

			case self::GROUP_PERFORMANCE:
				return array(
					'optimize_assets'     => 'bool',
					'defer_scripts'       => 'bool',
					'remove_emoji_script' => 'bool',
				);

			case self::GROUP_ADVANCED:
				return array(
					'delete_data_on_uninstall' => 'bool',
				);
		}

		return array();
	}

	/**
	 * Option name for a group.
	 *
	 * @param string $group Group name.
	 * @return string
	 */
	public function option_key( $group ) {
		return isset( self::$option_keys[ $group ] ) ? self::$option_keys[ $group ] : '';
	}

	/**
	 * Resolve every value of a group, merged over its defaults.
	 *
	 * @param string $group Group name.
	 * @return array
	 */
	public function all( $group ) {
		if ( isset( $this->cache[ $group ] ) ) {
			return $this->cache[ $group ];
		}

		$key    = $this->option_key( $group );
		$stored = '' !== $key ? get_option( $key, array() ) : array();
		$stored = is_array( $stored ) ? $stored : array();

		$this->cache[ $group ] = array_merge( $this->defaults( $group ), $stored );

		return $this->cache[ $group ];
	}

	/**
	 * Read a single key from a group.
	 *
	 * @param string $group   Group name.
	 * @param string $key     Setting key.
	 * @param mixed  $default Fallback when the key is missing.
	 * @return mixed
	 */
	public function get( $group, $key, $default = null ) {
		$data = $this->all( $group );

		return array_key_exists( $key, $data ) ? $data[ $key ] : $default;
	}

	/**
	 * Whether a component (widget or extension) is enabled.
	 *
	 * Falls back to the component's own default when the user has never
	 * explicitly toggled it, which keeps newly shipped components working
	 * out of the box.
	 *
	 * @param string $group          {@see GROUP_WIDGETS} or {@see GROUP_MODULES}.
	 * @param string $id             Component identifier.
	 * @param bool   $default_enabled Registry-provided default.
	 * @return bool
	 */
	public function is_enabled( $group, $id, $default_enabled = true ) {
		$data = $this->all( $group );

		if ( array_key_exists( $id, $data ) ) {
			return (bool) $data[ $id ];
		}

		return (bool) $default_enabled;
	}

	/**
	 * Persist an explicit status map for a component group.
	 *
	 * @param string $group Group name.
	 * @param array  $map   Map of component id => bool.
	 * @return bool
	 */
	public function set_status( $group, array $map ) {
		$clean = array();

		foreach ( $map as $id => $enabled ) {
			$id = sanitize_key( $id );

			if ( '' === $id ) {
				continue;
			}

			$clean[ $id ] = (bool) $enabled;
		}

		return $this->set_group( $group, $clean );
	}

	/**
	 * Merge sanitised values into a scalar settings group.
	 *
	 * @param string $group  Group name.
	 * @param array  $values Raw values.
	 * @return bool
	 */
	public function update( $group, array $values ) {
		$schema    = $this->schema( $group );
		$sanitized = array();

		foreach ( $schema as $key => $type ) {
			if ( ! array_key_exists( $key, $values ) || ! is_scalar( $values[ $key ] ) ) {
				continue;
			}

			switch ( $type ) {
				case 'bool':
					$sanitized[ $key ] = rest_sanitize_boolean( $values[ $key ] );
					break;

				case 'text':
				default:
					$sanitized[ $key ] = sanitize_text_field( (string) $values[ $key ] );
					break;
			}
		}

		return $this->set_group( $group, array_merge( $this->all( $group ), $sanitized ) );
	}

	/**
	 * Replace a group's stored value and refresh the cache.
	 *
	 * @param string $group Group name.
	 * @param array  $value Value to store.
	 * @return bool
	 */
	public function set_group( $group, array $value ) {
		$key = $this->option_key( $group );

		if ( '' === $key ) {
			return false;
		}

		$this->cache[ $group ] = array_merge( $this->defaults( $group ), $value );

		return update_option( $key, $value );
	}

	/**
	 * Restore a group to its defaults.
	 *
	 * @param string $group Group name.
	 * @return bool
	 */
	public function reset( $group ) {
		return $this->set_group( $group, $this->defaults( $group ) );
	}

	/**
	 * Create any missing option rows using current defaults.
	 *
	 * @return void
	 */
	public function install_defaults() {
		foreach ( array_keys( self::$option_keys ) as $group ) {
			$key = $this->option_key( $group );

			if ( '' !== $key && false === get_option( $key, false ) ) {
				add_option( $key, $this->defaults( $group ) );
			}
		}
	}

	/**
	 * Drop the in-memory cache (used after out-of-band writes).
	 *
	 * @return void
	 */
	public function flush_cache() {
		$this->cache = array();
	}
}
