<?php
/**
 * Shared base class for every widget shipped with the plugin.
 *
 * @package Xoom_Addons\Abstracts
 */

namespace Xoom_Addons\Abstracts;

use Elementor\Widget_Base;
use Xoom_Addons\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Adds the shared Elementor category and the plugin's conditional asset
 * handles on top of Elementor's widget base.
 */
abstract class Base_Widget extends Widget_Base {

	/**
	 * Every widget lives under the single Xoom Addons panel category.
	 *
	 * @return string[]
	 */
	public function get_categories() {
		return array( Plugin::ELEMENTOR_CATEGORY );
	}

	/**
	 * Shared stylesheet, loaded by Elementor only when the widget renders.
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		return array( 'xoom-addons-widgets' );
	}

	/**
	 * Opt-in script handle. Widgets that need runtime behaviour override this.
	 *
	 * @return string[]
	 */
	public function get_script_depends() {
		return array();
	}
}
