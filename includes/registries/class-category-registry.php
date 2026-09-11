<?php
/**
 * Dashboard category registry.
 *
 * Categories group widgets inside the plugin dashboard (they are independent
 * of the single Elementor panel category). Adding a category here makes it
 * available to every widget definition and to the widgets screen filters.
 *
 * @package Xoom_Addons\Registries
 */

namespace Xoom_Addons\Registries;

defined( 'ABSPATH' ) || exit;

/**
 * Provides the ordered list of dashboard widget categories.
 */
class Category_Registry {

	/**
	 * Retrieve every category keyed by slug.
	 *
	 * @return array<string, array>
	 */
	public static function all() {
		$categories = array(
			'basic'       => array(
				'title'       => __( 'Basic', 'xoom-addons-for-elementor' ),
				'description' => __( 'Everyday building blocks for any layout.', 'xoom-addons-for-elementor' ),
				'icon'        => 'dashicons-screenoptions',
			),
			'content'     => array(
				'title'       => __( 'Content', 'xoom-addons-for-elementor' ),
				'description' => __( 'Rich content, media and layout widgets.', 'xoom-addons-for-elementor' ),
				'icon'        => 'dashicons-layout',
			),
			'marketing'   => array(
				'title'       => __( 'Marketing', 'xoom-addons-for-elementor' ),
				'description' => __( 'Convert visitors with pricing, CTAs and social proof.', 'xoom-addons-for-elementor' ),
				'icon'        => 'dashicons-megaphone',
			),
			'forms'       => array(
				'title'       => __( 'Forms', 'xoom-addons-for-elementor' ),
				'description' => __( 'Capture leads with native and third-party forms.', 'xoom-addons-for-elementor' ),
				'icon'        => 'dashicons-email-alt',
			),
			'woocommerce' => array(
				'title'       => __( 'WooCommerce', 'xoom-addons-for-elementor' ),
				'description' => __( 'Build high-converting storefronts.', 'xoom-addons-for-elementor' ),
				'icon'        => 'dashicons-cart',
			),
			'dynamic'     => array(
				'title'       => __( 'Dynamic', 'xoom-addons-for-elementor' ),
				'description' => __( 'Query and display posts, terms and custom fields.', 'xoom-addons-for-elementor' ),
				'icon'        => 'dashicons-database',
			),
		);

		/**
		 * Filter the dashboard widget categories.
		 *
		 * @param array $categories Categories keyed by slug.
		 */
		return apply_filters( 'xoom_addons_widget_categories', $categories );
	}

	/**
	 * Retrieve a single category.
	 *
	 * @param string $slug Category slug.
	 * @return array|null
	 */
	public static function get( $slug ) {
		$categories = self::all();

		return isset( $categories[ $slug ] ) ? $categories[ $slug ] : null;
	}

	/**
	 * Human readable category title with a safe fallback.
	 *
	 * @param string $slug Category slug.
	 * @return string
	 */
	public static function title( $slug ) {
		$category = self::get( $slug );

		return $category ? $category['title'] : $slug;
	}
}
