<?php
/**
 * Plugin Name:       Xoom Addons for Elementor
 * Plugin URI:        https://example.com/xoom-addons-for-elementor
 * Description:       A modern, performance-first Elementor addons plugin. Ships a premium component-based dashboard, a scalable widget/extension manager and conditional asset loading.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Xoom Addons
 * Author URI:        https://example.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       xoom-addons-for-elementor
 * Domain Path:       /languages
 *
 * @package Xoom_Addons
 */

defined( 'ABSPATH' ) || exit;

define( 'XOOM_ADDONS_VERSION', '1.0.0' );
define( 'XOOM_ADDONS_FILE', __FILE__ );
define( 'XOOM_ADDONS_BASE', plugin_basename( __FILE__ ) );
define( 'XOOM_ADDONS_PATH', plugin_dir_path( __FILE__ ) );
define( 'XOOM_ADDONS_URL', plugin_dir_url( __FILE__ ) );
define( 'XOOM_ADDONS_ASSETS_URL', XOOM_ADDONS_URL . 'assets/' );

define( 'XOOM_ADDONS_MIN_ELEMENTOR_VERSION', '3.5.0' );
define( 'XOOM_ADDONS_MIN_PHP_VERSION', '7.4' );

require_once XOOM_ADDONS_PATH . 'includes/class-autoloader.php';
Xoom_Addons\Autoloader::register();

register_activation_hook( __FILE__, array( 'Xoom_Addons\Plugin', 'on_activate' ) );
register_deactivation_hook( __FILE__, array( 'Xoom_Addons\Plugin', 'on_deactivate' ) );

/**
 * Boot the plugin once every other plugin (including Elementor) has loaded.
 *
 * Priority 20 guarantees Elementor has fired `elementor/loaded` first.
 */
add_action( 'plugins_loaded', array( 'Xoom_Addons\Plugin', 'instance' ), 20 );
