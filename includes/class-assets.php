<?php
/**
 * Frontend asset loading.
 *
 * Assets are registered, never blindly enqueued. Each widget advertises the
 * handles it needs through `get_style_depends()` / `get_script_depends()`, so
 * Elementor only prints them on pages that actually render a Xoom widget.
 *
 * @package Xoom_Addons
 */

namespace Xoom_Addons;

defined( 'ABSPATH' ) || exit;

/**
 * Registers conditional frontend assets and applies performance tweaks.
 */
class Assets {

	const STYLE_HANDLE  = 'xoom-addons-widgets';
	const SCRIPT_HANDLE = 'xoom-addons-widgets';

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
	 * Register the plugin hooks. Only called when Elementor is active.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'elementor/frontend/after_register_styles', array( $this, 'register_styles' ) );
		add_action( 'elementor/frontend/after_register_scripts', array( $this, 'register_scripts' ) );

		if ( $this->is_enabled( 'defer_scripts' ) ) {
			add_filter( 'script_loader_tag', array( $this, 'defer_script' ), 10, 3 );
		}

		if ( $this->is_enabled( 'remove_emoji_script' ) ) {
			add_action( 'init', array( $this, 'remove_emoji_script' ) );
		}
	}

	/**
	 * Read a performance toggle.
	 *
	 * @param string $key Setting key.
	 * @return bool
	 */
	private function is_enabled( $key ) {
		return (bool) $this->settings->get( Settings::GROUP_PERFORMANCE, $key, false );
	}

	/**
	 * Whether per-widget conditional loading is active.
	 *
	 * When disabled, the shared assets are printed on every page.
	 *
	 * @return bool
	 */
	private function is_optimized() {
		return (bool) $this->settings->get( Settings::GROUP_PERFORMANCE, 'optimize_assets', true );
	}

	/**
	 * Register the shared widget stylesheet.
	 *
	 * @return void
	 */
	public function register_styles() {
		wp_register_style(
			self::STYLE_HANDLE,
			XOOM_ADDONS_ASSETS_URL . 'css/widgets.css',
			array(),
			XOOM_ADDONS_VERSION
		);

		if ( ! $this->is_optimized() ) {
			wp_enqueue_style( self::STYLE_HANDLE );
		}
	}

	/**
	 * Register the shared widget script.
	 *
	 * @return void
	 */
	public function register_scripts() {
		wp_register_script(
			self::SCRIPT_HANDLE,
			XOOM_ADDONS_ASSETS_URL . 'js/widgets.js',
			array(),
			XOOM_ADDONS_VERSION,
			true
		);

		if ( ! $this->is_optimized() ) {
			wp_enqueue_script( self::SCRIPT_HANDLE );
		}
	}

	/**
	 * Add `defer` to the plugin's own frontend script.
	 *
	 * @param string $tag    Script tag markup.
	 * @param string $handle Script handle.
	 * @param string $src    Script source URL.
	 * @return string
	 */
	public function defer_script( $tag, $handle, $src ) {
		if ( self::SCRIPT_HANDLE !== $handle ) {
			return $tag;
		}

		if ( false !== strpos( $tag, ' defer' ) ) {
			return $tag;
		}

		return str_replace( '<script ', '<script defer ', $tag );
	}

	/**
	 * Drop the WordPress emoji detection script and styles.
	 *
	 * @return void
	 */
	public function remove_emoji_script() {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	}
}
