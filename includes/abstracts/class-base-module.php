<?php
/**
 * Shared base class for every extension shipped with the plugin.
 *
 * @package Xoom_Addons\Abstracts
 */

namespace Xoom_Addons\Abstracts;

defined( 'ABSPATH' ) || exit;

/**
 * Extensions register their own WordPress/Elementor hooks in {@see init()}.
 */
abstract class Base_Module {

	/**
	 * Boot the extension immediately after it is loaded.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Register the extension hooks.
	 *
	 * @return void
	 */
	abstract public function init();
}
