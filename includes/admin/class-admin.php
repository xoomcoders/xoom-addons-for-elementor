<?php
/**
 * Admin bootstrap: menu, screens and admin assets.
 *
 * @package Xoom_Addons\Admin
 */

namespace Xoom_Addons\Admin;

use Xoom_Addons\Catalog;
use Xoom_Addons\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the plugin dashboard and loads its assets only on its own screens.
 */
class Admin {

	/**
	 * Capability required to view or change anything.
	 */
	const CAPABILITY = 'manage_options';

	/**
	 * Admin page slugs.
	 */
	const PAGE_DASHBOARD  = 'xoom-addons';
	const PAGE_WIDGETS    = 'xoom-addons-widgets';
	const PAGE_EXTENSIONS = 'xoom-addons-extensions';
	const PAGE_SETTINGS   = 'xoom-addons-settings';

	/**
	 * Nonce action shared by every admin request.
	 */
	const NONCE_ACTION = 'xoom_addons_admin';

	/**
	 * Settings repository.
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Catalog service.
	 *
	 * @var Catalog
	 */
	private $catalog;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings Settings repository.
	 * @param Catalog  $catalog  Catalog service.
	 */
	public function __construct( Settings $settings, Catalog $catalog ) {
		$this->settings = $settings;
		$this->catalog  = $catalog;

		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_init', array( $this, 'maybe_redirect_after_activation' ) );
		add_filter( 'admin_body_class', array( $this, 'body_class' ) );
		add_filter( 'admin_footer_text', array( $this, 'footer_text' ) );
	}

	/**
	 * Register the top level menu and its screens.
	 *
	 * @return void
	 */
	public function register_menu() {
		add_menu_page(
			__( 'Xoom Addons', 'xoom-addons-for-elementor' ),
			__( 'Xoom Addons', 'xoom-addons-for-elementor' ),
			self::CAPABILITY,
			self::PAGE_DASHBOARD,
			array( $this, 'render_dashboard' ),
			'dashicons-screenoptions',
			58.6
		);

		$screens = array(
			self::PAGE_DASHBOARD  => array( __( 'Dashboard', 'xoom-addons-for-elementor' ), 'render_dashboard' ),
			self::PAGE_WIDGETS    => array( __( 'Widgets', 'xoom-addons-for-elementor' ), 'render_widgets' ),
			self::PAGE_EXTENSIONS => array( __( 'Extensions', 'xoom-addons-for-elementor' ), 'render_extensions' ),
			self::PAGE_SETTINGS   => array( __( 'Settings', 'xoom-addons-for-elementor' ), 'render_settings' ),
		);

		foreach ( $screens as $slug => $screen ) {
			add_submenu_page(
				self::PAGE_DASHBOARD,
				$screen[0],
				$screen[0],
				self::CAPABILITY,
				$slug,
				array( $this, $screen[1] )
			);
		}
	}

	/**
	 * Load the dashboard assets, and only on the plugin's own screens.
	 *
	 * @param string $hook_suffix Current admin screen hook.
	 * @return void
	 */
	public function enqueue_assets( $hook_suffix ) {
		if ( ! $this->is_plugin_screen( $hook_suffix ) ) {
			return;
		}

		wp_enqueue_style(
			'xoom-addons-admin',
			XOOM_ADDONS_ASSETS_URL . 'admin/css/admin.css',
			array(),
			XOOM_ADDONS_VERSION
		);

		wp_enqueue_script(
			'xoom-addons-admin',
			XOOM_ADDONS_ASSETS_URL . 'admin/js/admin.js',
			array(),
			XOOM_ADDONS_VERSION,
			true
		);

		wp_localize_script(
			'xoom-addons-admin',
			'xoomAddonsAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( self::NONCE_ACTION ),
				'i18n'    => array(
					'saved'        => __( 'Changes saved.', 'xoom-addons-for-elementor' ),
					'saving'       => __( 'Saving…', 'xoom-addons-for-elementor' ),
					'error'        => __( 'Something went wrong. Please try again.', 'xoom-addons-for-elementor' ),
					'noResults'    => __( 'No components match your filters.', 'xoom-addons-for-elementor' ),
					'enableAll'    => __( 'Enable all', 'xoom-addons-for-elementor' ),
					'disableAll'   => __( 'Disable all', 'xoom-addons-for-elementor' ),
					'confirmAll'   => __( 'Apply this to every visible item?', 'xoom-addons-for-elementor' ),
					'resultsOne'   => __( '%d result', 'xoom-addons-for-elementor' ),
					'resultsMany'  => __( '%d results', 'xoom-addons-for-elementor' ),
					'on'           => __( 'On', 'xoom-addons-for-elementor' ),
					'off'          => __( 'Off', 'xoom-addons-for-elementor' ),
					'resetConfirm' => __( 'Restore these settings to their defaults?', 'xoom-addons-for-elementor' ),
				),
			)
		);
	}

	/**
	 * Whether the current screen belongs to this plugin.
	 *
	 * @param string $hook_suffix Screen hook, or a screen id.
	 * @return bool
	 */
	private function is_plugin_screen( $hook_suffix ) {
		return false !== strpos( (string) $hook_suffix, 'xoom-addons' );
	}

	/**
	 * Admin URL for one of the plugin screens.
	 *
	 * @param string $page Page slug.
	 * @return string
	 */
	public function url( $page ) {
		return admin_url( 'admin.php?page=' . $page );
	}

	/**
	 * The plugin screens, keyed by slug, for the dashboard navigation.
	 *
	 * @return array<string, string>
	 */
	public function pages() {
		return array(
			self::PAGE_DASHBOARD  => __( 'Dashboard', 'xoom-addons-for-elementor' ),
			self::PAGE_WIDGETS    => __( 'Widgets', 'xoom-addons-for-elementor' ),
			self::PAGE_EXTENSIONS => __( 'Extensions', 'xoom-addons-for-elementor' ),
			self::PAGE_SETTINGS   => __( 'Settings', 'xoom-addons-for-elementor' ),
		);
	}

	/**
	 * Tag the plugin screens so styles can be scoped safely.
	 *
	 * @param string $classes Existing body classes.
	 * @return string
	 */
	public function body_class( $classes ) {
		$screen = get_current_screen();

		if ( $screen && $this->is_plugin_screen( $screen->id ) ) {
			$classes .= ' xoom-addons-screen';
		}

		return $classes;
	}

	/**
	 * Replace the admin footer credit on the plugin screens.
	 *
	 * @param string $text Existing footer text.
	 * @return string
	 */
	public function footer_text( $text ) {
		$screen = get_current_screen();

		if ( ! $screen || ! $this->is_plugin_screen( $screen->id ) ) {
			return $text;
		}

		return sprintf(
			/* translators: %s: plugin name. */
			esc_html__( 'Thank you for building with %s.', 'xoom-addons-for-elementor' ),
			'<strong>' . esc_html__( 'Xoom Addons for Elementor', 'xoom-addons-for-elementor' ) . '</strong>'
		);
	}

	/**
	 * Send a freshly activated user straight to the dashboard.
	 *
	 * @return void
	 */
	public function maybe_redirect_after_activation() {
		if ( ! get_transient( 'xoom_addons_activation_redirect' ) ) {
			return;
		}

		delete_transient( 'xoom_addons_activation_redirect' );

		if ( wp_doing_ajax() || is_network_admin() || ! current_user_can( self::CAPABILITY ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- presence check only.
		if ( isset( $_GET['activate-multi'] ) ) {
			return;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=' . self::PAGE_DASHBOARD ) );
		exit;
	}

	/**
	 * Render the dashboard screen.
	 *
	 * @return void
	 */
	public function render_dashboard() {
		$this->render_view( 'dashboard', self::PAGE_DASHBOARD );
	}

	/**
	 * Render the widgets screen.
	 *
	 * @return void
	 */
	public function render_widgets() {
		$this->render_view( 'widgets', self::PAGE_WIDGETS );
	}

	/**
	 * Render the extensions screen.
	 *
	 * @return void
	 */
	public function render_extensions() {
		$this->render_view( 'extensions', self::PAGE_EXTENSIONS );
	}

	/**
	 * Render the settings screen.
	 *
	 * @return void
	 */
	public function render_settings() {
		$this->render_view( 'settings', self::PAGE_SETTINGS );
	}

	/**
	 * Include a view with the shared template context.
	 *
	 * @param string $view         View file name, without extension.
	 * @param string $current_page Current page slug, used by the nav.
	 * @return void
	 */
	private function render_view( $view, $current_page ) {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'xoom-addons-for-elementor' ) );
		}

		$settings = $this->settings;
		$catalog  = $this->catalog;

		$path = __DIR__ . '/views/' . $view . '.php';

		if ( ! is_readable( $path ) ) {
			return;
		}

		include $path;
	}
}
