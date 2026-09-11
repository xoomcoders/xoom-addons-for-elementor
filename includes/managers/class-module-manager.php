<?php
/**
 * Extension loading.
 *
 * @package Xoom_Addons\Managers
 */

namespace Xoom_Addons\Managers;

use Xoom_Addons\Abstracts\Base_Module;
use Xoom_Addons\Catalog;
use Xoom_Addons\Registries\Module_Registry;

defined( 'ABSPATH' ) || exit;

/**
 * Loads every enabled extension once, at boot time.
 */
class Module_Manager {

	/**
	 * Catalog service.
	 *
	 * @var Catalog
	 */
	private $catalog;

	/**
	 * Slugs of the extensions loaded this request.
	 *
	 * @var string[]
	 */
	private $loaded = array();

	/**
	 * Constructor.
	 *
	 * @param Catalog $catalog Catalog service.
	 */
	public function __construct( Catalog $catalog ) {
		$this->catalog = $catalog;

		$this->load_modules();
	}

	/**
	 * Instantiate the enabled extensions so they can register their hooks.
	 *
	 * @return void
	 */
	private function load_modules() {
		foreach ( Module_Registry::all() as $id => $module ) {
			if ( ! $this->catalog->is_module_enabled( $module ) ) {
				continue;
			}

			$class = isset( $module['class'] ) ? (string) $module['class'] : '';

			if ( '' === $class ) {
				continue;
			}

			if ( ! class_exists( $class ) && ! empty( $module['file'] ) ) {
				$file = XOOM_ADDONS_PATH . $module['file'];

				if ( is_readable( $file ) ) {
					require_once $file;
				}
			}

			if ( ! class_exists( $class ) || ! is_subclass_of( $class, Base_Module::class ) ) {
				continue;
			}

			new $class();

			$this->loaded[] = $id;
		}
	}

	/**
	 * Slugs of the extensions loaded during this request.
	 *
	 * @return string[]
	 */
	public function loaded() {
		return $this->loaded;
	}
}
