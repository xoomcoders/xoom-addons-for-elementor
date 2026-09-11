<?php
/**
 * Elementor widget registration.
 *
 * @package Xoom_Addons\Managers
 */

namespace Xoom_Addons\Managers;

use Xoom_Addons\Catalog;
use Xoom_Addons\Registries\Widget_Registry;

defined( 'ABSPATH' ) || exit;

/**
 * Registers every enabled widget with Elementor.
 *
 * Only enabled widgets are ever loaded, which means their PHP, styles and
 * scripts never enter the page — disabled widgets cost nothing at runtime.
 */
class Widget_Manager {

	/**
	 * Catalog service.
	 *
	 * @var Catalog
	 */
	private $catalog;

	/**
	 * Slugs of the widgets that were registered this request.
	 *
	 * @var string[]
	 */
	private $registered = array();

	/**
	 * Constructor.
	 *
	 * @param Catalog $catalog Catalog service.
	 */
	public function __construct( Catalog $catalog ) {
		$this->catalog = $catalog;

		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
	}

	/**
	 * Register the enabled widgets with the Elementor widgets manager.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 * @return void
	 */
	public function register_widgets( $widgets_manager ) {
		foreach ( Widget_Registry::all() as $id => $widget ) {
			if ( ! $this->catalog->is_widget_enabled( $widget ) ) {
				continue;
			}

			if ( ! $this->resolve_class( $widget ) ) {
				continue;
			}

			$class = $widget['class'];

			$widgets_manager->register( new $class() );

			$this->registered[] = $id;
		}
	}

	/**
	 * Load a widget's class file when it is not already autoloadable.
	 *
	 * @param array $widget Normalised widget record.
	 * @return bool Whether the widget class is now available.
	 */
	private function resolve_class( array $widget ) {
		$class = isset( $widget['class'] ) ? (string) $widget['class'] : '';

		if ( '' === $class ) {
			return false;
		}

		if ( ! class_exists( $class ) && ! empty( $widget['file'] ) ) {
			$file = XOOM_ADDONS_PATH . $widget['file'];

			if ( is_readable( $file ) ) {
				require_once $file;
			}
		}

		return class_exists( $class );
	}

	/**
	 * Slugs of the widgets registered during this request.
	 *
	 * @return string[]
	 */
	public function registered() {
		return $this->registered;
	}
}
