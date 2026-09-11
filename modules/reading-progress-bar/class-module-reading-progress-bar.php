<?php
/**
 * Reading Progress Bar extension.
 *
 * @package Xoom_Addons\Modules
 */

namespace Xoom_Addons\Modules\Reading_Progress_Bar;

use Xoom_Addons\Abstracts\Base_Module;

defined( 'ABSPATH' ) || exit;

/**
 * Renders a scroll progress indicator on singular views.
 */
class Module_Reading_Progress_Bar extends Base_Module {

	/**
	 * Style handle.
	 */
	const STYLE_HANDLE = 'xoom-addons-reading-progress';

	/**
	 * Script handle.
	 */
	const SCRIPT_HANDLE = 'xoom-addons-reading-progress';

	/**
	 * Register the extension hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'wp_footer', array( $this, 'render' ) );
	}

	/**
	 * Whether the indicator should appear on the current request.
	 *
	 * @return bool
	 */
	private function is_applicable() {
		return ! is_admin() && is_singular();
	}

	/**
	 * Enqueue the indicator assets.
	 *
	 * @return void
	 */
	public function enqueue() {
		if ( ! $this->is_applicable() ) {
			return;
		}

		wp_enqueue_style(
			self::STYLE_HANDLE,
			XOOM_ADDONS_ASSETS_URL . 'css/reading-progress.css',
			array(),
			XOOM_ADDONS_VERSION
		);

		wp_enqueue_script(
			self::SCRIPT_HANDLE,
			XOOM_ADDONS_ASSETS_URL . 'js/reading-progress.js',
			array(),
			XOOM_ADDONS_VERSION,
			true
		);
	}

	/**
	 * Print the indicator markup.
	 *
	 * @return void
	 */
	public function render() {
		if ( ! $this->is_applicable() ) {
			return;
		}
		?>
		<div class="xoom-reading-progress" role="presentation" aria-hidden="true">
			<span class="xoom-reading-progress__bar" data-xoom-progress></span>
		</div>
		<?php
	}
}
