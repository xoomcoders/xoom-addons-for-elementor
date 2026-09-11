<?php
/**
 * Catalog service.
 *
 * The read model behind the dashboard: it merges the declarative registries
 * with the saved state to produce filtered, presentation-ready component
 * lists and counts. Both the admin screens and the Elementor managers read
 * from the same registries, so the dashboard can never drift from reality.
 *
 * The registries are filterable, which is how the Pro plugin contributes its
 * own widgets and extensions without the free plugin knowing about them.
 *
 * @package Xoom_Addons
 */

namespace Xoom_Addons;

use Xoom_Addons\Registries\Module_Registry;
use Xoom_Addons\Registries\Widget_Registry;

defined( 'ABSPATH' ) || exit;

/**
 * Queryable view over the widget and extension registries.
 */
class Catalog {

	/**
	 * Settings repository.
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings Settings repository.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Whether the Pro plugin is loaded at all.
	 *
	 * Answers "is Pro installed?", which is what the upgrade promo needs —
	 * it should disappear once the Pro plugin is present, licensed or not,
	 * because Pro renders its own panel either way.
	 *
	 * For "are Pro features actually active?" use {@see edition_label()},
	 * which reflects what is really in the registry.
	 *
	 * @return bool
	 */
	public function is_pro_active() {
		return (bool) did_action( 'xoom_addons_pro_loaded' );
	}

	/**
	 * Whether any registered component comes from the Pro plugin.
	 *
	 * Derived from the registry rather than from a licence flag, so the free
	 * plugin needs no knowledge of licensing and can never claim Pro features
	 * that are not actually registered.
	 *
	 * @return bool
	 */
	public function has_pro_components() {
		foreach ( array( Widget_Registry::all(), Module_Registry::all() ) as $components ) {
			foreach ( $components as $component ) {
				if ( 'pro' === $component['package'] ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Whether a widget is currently enabled.
	 *
	 * Components default to enabled per their registry entry, so a newly
	 * shipped widget works without the user visiting the dashboard.
	 *
	 * @param array $widget Normalised widget record.
	 * @return bool
	 */
	public function is_widget_enabled( array $widget ) {
		return $this->settings->is_enabled( Settings::GROUP_WIDGETS, $widget['id'], $widget['default_enabled'] );
	}

	/**
	 * Whether an extension is currently enabled.
	 *
	 * @param array $module Normalised extension record.
	 * @return bool
	 */
	public function is_module_enabled( array $module ) {
		return $this->settings->is_enabled( Settings::GROUP_MODULES, $module['id'], $module['default_enabled'] );
	}

	/**
	 * Retrieve widgets matching the supplied filters.
	 *
	 * @param array $args {
	 *     Optional. Query arguments.
	 *
	 *     @type string $search   Free-text search across title, description, keywords and slug.
	 *     @type string $category Dashboard category slug.
	 *     @type string $status   `all`, `active` or `inactive`.
	 *     @type string $package  `all`, `free` or `pro`.
	 * }
	 * @return array<string, array> Widget records with an `enabled` flag.
	 */
	public function widgets( array $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'search'   => '',
				'category' => '',
				'status'   => 'all',
				'package'  => 'all',
			)
		);

		$items = array();

		foreach ( Widget_Registry::all() as $id => $widget ) {
			$enabled = $this->is_widget_enabled( $widget );

			if ( ! $this->matches( $widget, $args, $enabled ) ) {
				continue;
			}

			$widget['enabled'] = $enabled;

			$items[ $id ] = $widget;
		}

		return $items;
	}

	/**
	 * Retrieve extensions matching the supplied filters.
	 *
	 * @param array $args Same shape as {@see widgets()}, with `group` replacing `category`.
	 * @return array<string, array> Extension records with an `enabled` flag.
	 */
	public function modules( array $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'search'  => '',
				'group'   => '',
				'status'  => 'all',
				'package' => 'all',
			)
		);

		$items = array();

		foreach ( Module_Registry::all() as $id => $module ) {
			$enabled = $this->is_module_enabled( $module );

			// Mirror the group into `categories` so the shared matcher and the
			// widget cards can treat both component types identically.
			$module['categories'] = array( $module['group'] );

			if ( ! $this->matches( $module, $args, $enabled ) ) {
				continue;
			}

			$module['enabled'] = $enabled;

			$items[ $id ] = $module;
		}

		return $items;
	}

	/**
	 * Shared filter matcher.
	 *
	 * @param array $component Normalised component record.
	 * @param array $args      Query arguments.
	 * @param bool  $enabled   Resolved enabled state.
	 * @return bool
	 */
	private function matches( array $component, array $args, $enabled ) {
		$category   = isset( $args['category'] ) ? $args['category'] : '';
		$group      = isset( $args['group'] ) ? $args['group'] : '';
		$categories = isset( $component['categories'] ) ? (array) $component['categories'] : array();

		if ( '' !== $category && ! in_array( $category, $categories, true ) ) {
			return false;
		}

		if ( '' !== $group && ( ! isset( $component['group'] ) || $group !== $component['group'] ) ) {
			return false;
		}

		$status = isset( $args['status'] ) ? $args['status'] : 'all';

		if ( 'active' === $status && ! $enabled ) {
			return false;
		}

		if ( 'inactive' === $status && $enabled ) {
			return false;
		}

		$package = isset( $args['package'] ) ? $args['package'] : 'all';

		if ( 'all' !== $package && $package !== $component['package'] ) {
			return false;
		}

		$search = isset( $args['search'] ) ? trim( (string) $args['search'] ) : '';

		if ( '' === $search ) {
			return true;
		}

		$haystack = implode(
			' ',
			array_merge(
				array( $component['id'], $component['title'], $component['description'] ),
				(array) $component['keywords']
			)
		);

		return false !== stripos( $haystack, $search );
	}

	/**
	 * Aggregate widget statistics for the dashboard.
	 *
	 * @return array{total:int,active:int,inactive:int,free:int,pro:int}
	 */
	public function widget_counts() {
		return $this->counts( Widget_Registry::all(), Settings::GROUP_WIDGETS );
	}

	/**
	 * Aggregate extension statistics for the dashboard.
	 *
	 * @return array{total:int,active:int,inactive:int,free:int,pro:int}
	 */
	public function module_counts() {
		return $this->counts( Module_Registry::all(), Settings::GROUP_MODULES );
	}

	/**
	 * Count a component set by state and package.
	 *
	 * @param array  $components Normalised components.
	 * @param string $group      Settings group used for the enabled lookup.
	 * @return array{total:int,active:int,inactive:int,free:int,pro:int}
	 */
	private function counts( array $components, $group ) {
		$counts = array(
			'total'    => count( $components ),
			'active'   => 0,
			'inactive' => 0,
			'free'     => 0,
			'pro'      => 0,
		);

		foreach ( $components as $component ) {
			if ( 'pro' === $component['package'] ) {
				++$counts['pro'];
			} else {
				++$counts['free'];
			}

			if ( $this->settings->is_enabled( $group, $component['id'], $component['default_enabled'] ) ) {
				++$counts['active'];
			} else {
				++$counts['inactive'];
			}
		}

		return $counts;
	}

	/**
	 * Resolve which components of the suite are active, for display.
	 *
	 * @return string
	 */
	public function edition_label() {
		if ( $this->has_pro_components() ) {
			return __( 'Free + Pro', 'xoom-addons-for-elementor' );
		}

		return __( 'Free', 'xoom-addons-for-elementor' );
	}
}
