<?php
/**
 * Main plugin orchestrator.
 *
 * @package Xoom_Addons
 */

namespace Xoom_Addons;

defined( 'ABSPATH' ) || exit;

/**
 * Wires the plugin services together and bootstraps Elementor integration.
 */
final class Plugin {

	/**
	 * Stored plugin version, used for lightweight upgrade routines.
	 */
	const OPTION_VERSION = 'xoom_addons_version';

	/**
	 * Elementor panel category slug used by every shipped widget.
	 */
	const ELEMENTOR_CATEGORY = 'xoom-addons';

	/**
	 * Single plugin instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

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
	 * Asset loader.
	 *
	 * @var Assets
	 */
	private $assets;

	/**
	 * Retrieve the shared plugin instance.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Build services and register bootstrap hooks.
	 */
	private function __construct() {
		$this->settings = new Settings();
		$this->catalog  = new Catalog( $this->settings );
		$this->assets   = new Assets( $this->settings );

		$this->boot();
	}

	/**
	 * Settings repository accessor.
	 *
	 * @return Settings
	 */
	public function settings() {
		return $this->settings;
	}

	/**
	 * Catalog service accessor.
	 *
	 * @return Catalog
	 */
	public function catalog() {
		return $this->catalog;
	}

	/**
	 * Asset loader accessor.
	 *
	 * @return Assets
	 */
	public function assets() {
		return $this->assets;
	}

	/**
	 * Register the plugin hooks.
	 *
	 * Admin UI always loads so the dashboard stays reachable even when
	 * Elementor is missing — only the Elementor integration is gated.
	 *
	 * @return void
	 */
	private function boot() {
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// Priority 1 so extensions announced on this hook have registered
		// their listeners: they boot on `plugins_loaded`, which runs before
		// any `init` callback.
		add_action( 'init', array( $this, 'announce_loaded' ), 1 );

		if ( is_admin() ) {
			new Admin\Admin( $this->settings, $this->catalog );
			new Admin\Ajax( $this->settings, $this->catalog );

			add_filter( 'plugin_action_links_' . XOOM_ADDONS_BASE, array( $this, 'action_links' ) );
		}

		if ( ! $this->has_elementor() ) {
			add_action( 'admin_notices', array( $this, 'notice_missing_elementor' ) );
			return;
		}

		if ( version_compare( ELEMENTOR_VERSION, XOOM_ADDONS_MIN_ELEMENTOR_VERSION, '<' ) ) {
			add_action( 'admin_notices', array( $this, 'notice_minimum_elementor' ) );
			return;
		}

		$this->assets->register_hooks();

		new Managers\Widget_Manager( $this->catalog );
		new Managers\Module_Manager( $this->catalog );

		add_action( 'elementor/elements/categories_registered', array( $this, 'register_elementor_category' ) );
	}

	/**
	 * Announce that the free plugin is ready for extensions.
	 *
	 * @return void
	 */
	public function announce_loaded() {
		/**
		 * Fires once the free plugin's services are ready.
		 *
		 * Extensions (such as the Pro plugin) hook here to register their own
		 * widgets, extensions, settings and assets. Fired on `init` so every
		 * plugin has had the chance to register a listener.
		 *
		 * @param Plugin $plugin The plugin container.
		 */
		do_action( 'xoom_addons_loaded', $this );
	}

	/**
	 * Whether Elementor is active and past the loading gate.
	 *
	 * @return bool
	 */
	private function has_elementor() {
		return did_action( 'elementor/loaded' ) && defined( 'ELEMENTOR_VERSION' );
	}

	/**
	 * Load translations.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'xoom-addons-for-elementor',
			false,
			dirname( XOOM_ADDONS_BASE ) . '/languages'
		);
	}

	/**
	 * Register the shared widgets category inside the Elementor panel.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager Elementor elements manager.
	 * @return void
	 */
	public function register_elementor_category( $elements_manager ) {
		$label = (string) $this->settings->get( Settings::GROUP_GENERAL, 'category_label', __( 'Xoom Addons', 'xoom-addons-for-elementor' ) );
		$label = '' !== trim( $label ) ? trim( $label ) : __( 'Xoom Addons', 'xoom-addons-for-elementor' );

		$elements_manager->add_category(
			self::ELEMENTOR_CATEGORY,
			array(
				'title' => $label,
				'icon'  => 'eicon-nerd',
			)
		);
	}

	/**
	 * Add a settings shortcut to the plugins list row.
	 *
	 * @param array $links Existing action links.
	 * @return array
	 */
	public function action_links( $links ) {
		$settings = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=xoom-addons' ) ),
			esc_html__( 'Settings', 'xoom-addons-for-elementor' )
		);

		array_unshift( $links, $settings );

		return $links;
	}

	/**
	 * Warn that Elementor is required.
	 *
	 * @return void
	 */
	public function notice_missing_elementor() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		printf(
			'<div class="notice notice-warning"><p>%s</p></div>',
			sprintf(
				/* translators: 1: plugin name, 2: Elementor. */
				esc_html__( '%1$s requires %2$s to be installed and activated. The dashboard is available, but no widgets will render until Elementor is active.', 'xoom-addons-for-elementor' ),
				'<strong>' . esc_html__( 'Xoom Addons for Elementor', 'xoom-addons-for-elementor' ) . '</strong>',
				'<strong>' . esc_html__( 'Elementor', 'xoom-addons-for-elementor' ) . '</strong>'
			)
		);
	}

	/**
	 * Warn that the installed Elementor build is too old.
	 *
	 * @return void
	 */
	public function notice_minimum_elementor() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		printf(
			'<div class="notice notice-warning"><p>%s</p></div>',
			sprintf(
				/* translators: 1: plugin name, 2: Elementor, 3: required version. */
				esc_html__( '%1$s requires %2$s version %3$s or greater.', 'xoom-addons-for-elementor' ),
				'<strong>' . esc_html__( 'Xoom Addons for Elementor', 'xoom-addons-for-elementor' ) . '</strong>',
				'<strong>' . esc_html__( 'Elementor', 'xoom-addons-for-elementor' ) . '</strong>',
				esc_html( XOOM_ADDONS_MIN_ELEMENTOR_VERSION )
			)
		);
	}

	/**
	 * Seed default options on activation.
	 *
	 * @return void
	 */
	public static function on_activate() {
		if ( version_compare( PHP_VERSION, XOOM_ADDONS_MIN_PHP_VERSION, '<' ) ) {
			deactivate_plugins( XOOM_ADDONS_BASE );

			wp_die(
				sprintf(
					/* translators: 1: plugin name, 2: PHP, 3: required version. */
					esc_html__( '%1$s requires %2$s version %3$s or greater.', 'xoom-addons-for-elementor' ),
					'<strong>' . esc_html__( 'Xoom Addons for Elementor', 'xoom-addons-for-elementor' ) . '</strong>',
					'<strong>PHP</strong>',
					esc_html( XOOM_ADDONS_MIN_PHP_VERSION )
				),
				esc_html__( 'Plugin activation error', 'xoom-addons-for-elementor' ),
				array( 'back_link' => true )
			);
		}

		$settings = new Settings();
		$settings->install_defaults();

		update_option( self::OPTION_VERSION, XOOM_ADDONS_VERSION );

		set_transient( 'xoom_addons_activation_redirect', 1, 30 );
	}

	/**
	 * Clean up transient state on deactivation.
	 *
	 * Stored settings are intentionally preserved so re-activating restores
	 * the previous configuration. Use the Advanced settings tab to opt into
	 * full data removal on uninstall.
	 *
	 * @return void
	 */
	public static function on_deactivate() {
		delete_transient( 'xoom_addons_activation_redirect' );
	}
}
