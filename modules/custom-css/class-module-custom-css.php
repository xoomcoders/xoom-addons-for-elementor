<?php
/**
 * Custom CSS extension.
 *
 * Adds a per-element CSS editor to every Elementor element and folds the
 * result into the element's generated stylesheet.
 *
 * @package Xoom_Addons\Modules
 */

namespace Xoom_Addons\Modules\Custom_Css;

use Elementor\Controls_Manager;
use Xoom_Addons\Abstracts\Base_Module;

defined( 'ABSPATH' ) || exit;

/**
 * Injects an "Custom CSS" control into every element's Advanced tab.
 */
class Module_Custom_Css extends Base_Module {

	/**
	 * Control id shared by the editor and the renderer.
	 */
	const CONTROL = 'xoom_custom_css';

	/**
	 * Register the extension hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'elementor/element/after_section_end', array( $this, 'add_control' ), 10, 2 );
		add_action( 'elementor/element/parse_css', array( $this, 'render_css' ), 10, 2 );
	}

	/**
	 * Append the CSS editor to the Advanced tab of any element.
	 *
	 * @param \Elementor\Controls_Stack $element    Element being edited.
	 * @param string                    $section_id Id of the section that just ended.
	 * @return void
	 */
	public function add_control( $element, $section_id ) {
		if ( '_section_style' !== $section_id ) {
			return;
		}

		if ( ! is_object( $element ) || ! method_exists( $element, 'start_controls_section' ) ) {
			return;
		}

		$element->start_controls_section(
			'xoom_custom_css_section',
			array(
				'tab'   => Controls_Manager::TAB_ADVANCED,
				'label' => __( 'Custom CSS', 'xoom-addons-for-elementor' ),
			)
		);

		$element->add_control(
			self::CONTROL,
			array(
				'label'       => __( 'CSS', 'xoom-addons-for-elementor' ),
				'type'        => Controls_Manager::CODE,
				'language'    => 'css',
				'render_type' => 'none',
				'label_block' => true,
				'description' => __( 'Target this element with the selector "selector".', 'xoom-addons-for-elementor' ),
			)
		);

		$element->end_controls_section();
	}

	/**
	 * Fold the element's custom CSS into its generated stylesheet.
	 *
	 * @param \Elementor\Core\Files\CSS\Post $post_css Generated CSS file.
	 * @param \Elementor\Element_Base        $element  Element being parsed.
	 * @return void
	 */
	public function render_css( $post_css, $element ) {
		if ( ! is_object( $element ) || ! method_exists( $element, 'get_settings_for_display' ) ) {
			return;
		}

		$css = $element->get_settings_for_display( self::CONTROL );

		if ( empty( $css ) || ! is_string( $css ) ) {
			return;
		}

		if ( ! is_object( $post_css ) || ! method_exists( $post_css, 'get_stylesheet' ) ) {
			return;
		}

		$stylesheet = $post_css->get_stylesheet();

		if ( ! is_object( $stylesheet ) || ! method_exists( $stylesheet, 'add_raw_css' ) ) {
			return;
		}

		$stylesheet->add_raw_css(
			sprintf(
				'.elementor-element-%1$s{%2$s}',
				$element->get_id(),
				$css
			)
		);
	}
}
