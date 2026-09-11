<?php
/**
 * Extension (module) registry.
 *
 * Extensions are non-widget features — controls injected into Elementor,
 * editor helpers, performance tweaks. They follow the same declarative
 * shape as widgets so the dashboard can manage both identically.
 *
 * Separate plugins add their own extensions through the `xoom_addons_modules`
 * filter.
 *
 * @package Xoom_Addons\Registries
 */

namespace Xoom_Addons\Registries;

defined( 'ABSPATH' ) || exit;

/**
 * Declares and normalises the extension catalog.
 */
class Module_Registry {

	/**
	 * Extension groups used by the dashboard filters.
	 *
	 * @return array<string, string>
	 */
	public static function groups() {
		return apply_filters(
			'xoom_addons_module_groups',
			array(
				'design'      => __( 'Design', 'xoom-addons-for-elementor' ),
				'editing'     => __( 'Editing', 'xoom-addons-for-elementor' ),
				'performance' => __( 'Performance', 'xoom-addons-for-elementor' ),
				'woocommerce' => __( 'WooCommerce', 'xoom-addons-for-elementor' ),
			)
		);
	}

	/**
	 * Raw extension definitions keyed by slug.
	 *
	 * @return array<string, array>
	 */
	private static function definitions() {
		return array(
			/* ---------------------------------------------------------- Free */
			'custom-css'           => array(
				'title'       => __( 'Custom CSS', 'xoom-addons-for-elementor' ),
				'description' => __( 'Add a per-element CSS editor to every Elementor element.', 'xoom-addons-for-elementor' ),
				'group'       => 'design',
				'icon'        => 'dashicons-editor-code',
				'class'       => 'Xoom_Addons\\Modules\\Custom_Css\\Module_Custom_Css',
				'file'        => 'modules/custom-css/class-module-custom-css.php',
			),
			'post-duplicator'      => array(
				'title'       => __( 'Post Duplicator', 'xoom-addons-for-elementor' ),
				'description' => __( 'Clone posts, pages and templates in a single click.', 'xoom-addons-for-elementor' ),
				'group'       => 'editing',
				'icon'        => 'dashicons-admin-page',
				'class'       => 'Xoom_Addons\\Modules\\Post_Duplicator\\Module_Post_Duplicator',
				'file'        => 'modules/post-duplicator/class-module-post-duplicator.php',
			),
			'reading-progress-bar' => array(
				'title'       => __( 'Reading Progress Bar', 'xoom-addons-for-elementor' ),
				'description' => __( 'Show how far visitors have scrolled through a page.', 'xoom-addons-for-elementor' ),
				'group'       => 'design',
				'icon'        => 'dashicons-chart-area',
				'class'       => 'Xoom_Addons\\Modules\\Reading_Progress_Bar\\Module_Reading_Progress_Bar',
				'file'        => 'modules/reading-progress-bar/class-module-reading-progress-bar.php',
			),
		);
	}

	/**
	 * Normalise a raw definition into a complete extension record.
	 *
	 * @param string $id         Extension slug.
	 * @param array  $definition Raw definition.
	 * @return array
	 */
	private static function normalize( $id, array $definition ) {
		$package = ( isset( $definition['package'] ) && 'pro' === $definition['package'] ) ? 'pro' : 'free';

		$defaults = array(
			'id'              => $id,
			'title'           => ucwords( str_replace( '-', ' ', $id ) ),
			'description'     => '',
			'group'           => 'design',
			'package'         => $package,
			'icon'            => 'dashicons-admin-plugins',
			'class'           => '',
			'file'            => '',
			'default_enabled' => 'free' === $package,
		);

		$module = array_merge( $defaults, $definition );

		$module['id']      = $id;
		$module['package'] = $package;

		return $module;
	}

	/**
	 * Retrieve every normalised extension keyed by slug.
	 *
	 * @return array<string, array>
	 */
	public static function all() {
		$modules = array();

		foreach ( self::definitions() as $id => $definition ) {
			$modules[ $id ] = self::normalize( $id, $definition );
		}

		/**
		 * Filter the extension catalog.
		 *
		 * @param array $modules Normalised extensions keyed by slug.
		 */
		return apply_filters( 'xoom_addons_modules', $modules );
	}

	/**
	 * Retrieve a single extension record.
	 *
	 * @param string $id Extension slug.
	 * @return array|null
	 */
	public static function get( $id ) {
		$modules = self::all();

		return isset( $modules[ $id ] ) ? $modules[ $id ] : null;
	}
}
