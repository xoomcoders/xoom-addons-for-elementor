<?php
/**
 * Widget registry.
 *
 * The single declarative source of truth for every widget the dashboard can
 * manage. Adding a widget means adding one entry here (plus its class file
 * when it ships with this plugin) — the dashboard, filters, counts and the
 * Elementor registration pipeline all derive from this list.
 *
 * Separate plugins add their own widgets through the `xoom_addons_widgets`
 * filter, so the free plugin never contains code it does not ship.
 *
 * Entry keys:
 *  - title          (string) Human readable name shown in the dashboard.
 *  - description    (string) One line summary used on the widget card.
 *  - categories     (array)  Dashboard category slugs.
 *  - package        (string) `free` or `pro`.
 *  - keywords       (array)  Extra search terms.
 *  - icon           (string) Dashicon class used on the card.
 *  - class          (string) Fully qualified Elementor widget class.
 *  - file           (string) Optional plugin-relative path to the class file.
 *                            Omit it when the class is autoloadable.
 *  - default_enabled (bool)  State before a user toggles it (defaults to
 *                            `true` for free and `false` for pro).
 *
 * @package Xoom_Addons\Registries
 */

namespace Xoom_Addons\Registries;

defined( 'ABSPATH' ) || exit;

/**
 * Declares and normalises the widget catalog.
 */
class Widget_Registry {

	/**
	 * Raw widget definitions keyed by slug.
	 *
	 * @return array<string, array>
	 */
	private static function definitions() {
		return array(
			/* ---------------------------------------------------------- Free */
			'heading'            => array(
				'title'       => __( 'Heading', 'xoom-addons-for-elementor' ),
				'description' => __( 'Flexible heading with gradient, stroke and highlight options.', 'xoom-addons-for-elementor' ),
				'categories'  => array( 'basic' ),
				'keywords'    => array( 'title', 'text', 'headline' ),
				'icon'        => 'dashicons-editor-textcolor',
				'class'       => 'Xoom_Addons\\Widgets\\Heading\\Widget_Heading',
				'file'        => 'widgets/heading/class-widget-heading.php',
			),
			'button'             => array(
				'title'       => __( 'Button', 'xoom-addons-for-elementor' ),
				'description' => __( 'Conversion-focused buttons with icon, hover and full-width control.', 'xoom-addons-for-elementor' ),
				'categories'  => array( 'basic', 'marketing' ),
				'keywords'    => array( 'cta', 'link', 'action' ),
				'icon'        => 'dashicons-button',
				'class'       => 'Xoom_Addons\\Widgets\\Button\\Widget_Button',
				'file'        => 'widgets/button/class-widget-button.php',
			),
			'icon-box'           => array(
				'title'       => __( 'Icon Box', 'xoom-addons-for-elementor' ),
				'description' => __( 'Pair an icon with a title and description in flexible layouts.', 'xoom-addons-for-elementor' ),
				'categories'  => array( 'basic', 'content' ),
				'keywords'    => array( 'feature', 'icon', 'box' ),
				'icon'        => 'dashicons-info',
				'class'       => 'Xoom_Addons\\Widgets\\Icon_Box\\Widget_Icon_Box',
				'file'        => 'widgets/icon-box/class-widget-icon-box.php',
			),
			'info-list'          => array(
				'title'       => __( 'Info List', 'xoom-addons-for-elementor' ),
				'description' => __( 'Repeater powered list of features with custom icons.', 'xoom-addons-for-elementor' ),
				'categories'  => array( 'content' ),
				'keywords'    => array( 'list', 'features', 'bullets' ),
				'icon'        => 'dashicons-list-view',
				'class'       => 'Xoom_Addons\\Widgets\\Info_List\\Widget_Info_List',
				'file'        => 'widgets/info-list/class-widget-info-list.php',
			),
			'counter'            => array(
				'title'       => __( 'Counter', 'xoom-addons-for-elementor' ),
				'description' => __( 'Animated number counters with prefix, suffix and duration.', 'xoom-addons-for-elementor' ),
				'categories'  => array( 'marketing' ),
				'keywords'    => array( 'number', 'stats', 'odometer' ),
				'icon'        => 'dashicons-chart-bar',
				'class'       => 'Xoom_Addons\\Widgets\\Counter\\Widget_Counter',
				'file'        => 'widgets/counter/class-widget-counter.php',
			),
		);
	}

	/**
	 * Normalise a raw definition into a complete widget record.
	 *
	 * @param string $id         Widget slug.
	 * @param array  $definition Raw definition.
	 * @return array
	 */
	private static function normalize( $id, array $definition ) {
		$package = ( isset( $definition['package'] ) && 'pro' === $definition['package'] ) ? 'pro' : 'free';

		$defaults = array(
			'id'              => $id,
			'name'            => 'xoom-' . $id,
			'title'           => ucwords( str_replace( '-', ' ', $id ) ),
			'description'     => '',
			'categories'      => array( 'basic' ),
			'keywords'        => array(),
			'package'         => $package,
			'icon'            => 'dashicons-admin-generic',
			'class'           => '',
			'file'            => '',
			'default_enabled' => 'free' === $package,
		);

		$widget = array_merge( $defaults, $definition );

		$widget['id']         = $id;
		$widget['package']    = $package;
		$widget['categories'] = array_values( array_filter( (array) $widget['categories'] ) );
		$widget['keywords']   = array_values( array_filter( (array) $widget['keywords'] ) );

		return $widget;
	}

	/**
	 * Retrieve every normalised widget keyed by slug.
	 *
	 * @return array<string, array>
	 */
	public static function all() {
		$widgets = array();

		foreach ( self::definitions() as $id => $definition ) {
			$widgets[ $id ] = self::normalize( $id, $definition );
		}

		/**
		 * Filter the widget catalog.
		 *
		 * @param array $widgets Normalised widgets keyed by slug.
		 */
		return apply_filters( 'xoom_addons_widgets', $widgets );
	}

	/**
	 * Retrieve a single widget record.
	 *
	 * @param string $id Widget slug.
	 * @return array|null
	 */
	public static function get( $id ) {
		$widgets = self::all();

		return isset( $widgets[ $id ] ) ? $widgets[ $id ] : null;
	}
}
