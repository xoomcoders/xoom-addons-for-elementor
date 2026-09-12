<?php
/**
 * Reusable Elementor control builders.
 *
 * @package Xoom_Addons\Helpers
 */

namespace Xoom_Addons\Helpers;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;

defined( 'ABSPATH' ) || exit;

/**
 * Builds the control sets every widget repeats (padding, margin, typography,
 * colour, link and button states) so they can be shared instead of copied
 * from widget to widget.
 */
class Controls {

	/**
	 * Add heading, padding, margin, typography and colour controls.
	 *
	 * @param \Elementor\Controls_Stack $stack      Controls stack (widget or section).
	 * @param string                    $label      Group label, already translated.
	 * @param string                    $selector   CSS selector the controls target.
	 * @param string                    $condition  Layout value the controls are shown for.
	 * @param string                    $style      CSS property used by the colour control.
	 * @param bool                      $typography Whether to add the typography control.
	 * @param bool                      $color      Whether to add the colour control.
	 * @return void
	 */
	public static function general_style( $stack, $label, $selector, $condition, $style = 'color', $typography = true, $color = true ) {
		$key = self::key( $label );

		$stack->add_control(
			$key . '_subtitle',
			array(
				'type'      => Controls_Manager::HEADING,
				'label'     => esc_html( $label ),
				'separator' => 'after',
				'condition' => array( 'layout_type' => $condition ),
			)
		);

		$stack->add_responsive_control(
			$key . '_padding',
			array(
				'label'      => esc_html__( 'Padding', 'xoom-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					$selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array( 'layout_type' => $condition ),
			)
		);

		$stack->add_responsive_control(
			$key . '_margin',
			array(
				'label'      => esc_html__( 'Margin', 'xoom-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					$selector => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array( 'layout_type' => $condition ),
			)
		);

		if ( $typography ) {
			$stack->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'      => $key . '_typo',
					'label'     => esc_html__( 'Typography', 'xoom-addons-for-elementor' ),
					'selector'  => $selector,
					'condition' => array( 'layout_type' => $condition ),
				)
			);
		}

		if ( $color ) {
			$stack->add_control(
				$key . '_color',
				array(
					'label'     => esc_html__( 'Color', 'xoom-addons-for-elementor' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						$selector => $style . ': {{VALUE}}',
					),
					'condition' => array( 'layout_type' => $condition ),
				)
			);
		}
	}

	/**
	 * Add typography plus normal/hover colour controls for a link.
	 *
	 * @param \Elementor\Controls_Stack $stack          Controls stack (widget or section).
	 * @param string                    $label          Group label, already translated.
	 * @param string                    $selector       CSS selector for the default state.
	 * @param string                    $hover_selector CSS selector for the hover state.
	 * @param string                    $condition      Layout value the controls are shown for.
	 * @param bool                      $typography     Whether to add the typography control.
	 * @param bool                      $color          Whether to add the colour tabs.
	 * @return void
	 */
	public static function link_style( $stack, $label, $selector, $hover_selector, $condition, $typography = true, $color = true ) {
		$key = self::key( $label );

		$stack->add_control(
			$key . '_subtitle',
			array(
				'type'      => Controls_Manager::HEADING,
				'label'     => esc_html( $label ),
				'separator' => 'after',
				'condition' => array( 'layout_type' => $condition ),
			)
		);

		$stack->add_responsive_control(
			$key . '_padding',
			array(
				'label'      => esc_html__( 'Padding', 'xoom-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					$selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array( 'layout_type' => $condition ),
			)
		);

		$stack->add_responsive_control(
			$key . '_margin',
			array(
				'label'      => esc_html__( 'Margin', 'xoom-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					$selector => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array( 'layout_type' => $condition ),
			)
		);

		if ( $typography ) {
			$stack->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'      => $key . '_typo',
					'label'     => esc_html__( 'Typography', 'xoom-addons-for-elementor' ),
					'selector'  => $selector,
					'condition' => array( 'layout_type' => $condition ),
				)
			);
		}

		if ( ! $color ) {
			return;
		}

		$stack->start_controls_tabs( $key . '_tabs_link' );

		$stack->start_controls_tab(
			$key . '_tab_link_normal',
			array(
				'label'     => esc_html__( 'Normal', 'xoom-addons-for-elementor' ),
				'condition' => array( 'layout_type' => $condition ),
			)
		);

		$stack->add_control(
			$key . '_color',
			array(
				'label'     => esc_html__( 'Color', 'xoom-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$selector => 'color: {{VALUE}};',
				),
				'condition' => array( 'layout_type' => $condition ),
			)
		);

		$stack->end_controls_tab();

		$stack->start_controls_tab(
			$key . '_tab_link_hover',
			array(
				'label'     => esc_html__( 'Hover', 'xoom-addons-for-elementor' ),
				'condition' => array( 'layout_type' => $condition ),
			)
		);

		$stack->add_control(
			$key . '_hover_color',
			array(
				'label'     => esc_html__( 'Color', 'xoom-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$hover_selector => 'color: {{VALUE}};',
				),
				'condition' => array( 'layout_type' => $condition ),
			)
		);

		$stack->end_controls_tab();
		$stack->end_controls_tabs();
	}

	/**
	 * Add the standard button control set (spacing, type, border, shadow and
	 * normal/hover colour tabs).
	 *
	 * @param \Elementor\Controls_Stack $stack             Controls stack (widget or section).
	 * @param string                    $label             Group label, already translated.
	 * @param string                    $selector          CSS selector for the default state.
	 * @param string                    $hover_selector    CSS selector for the hover state.
	 * @param string                    $condition         Layout value the controls are shown for.
	 * @return void
	 */
	public static function button_style( $stack, $label, $selector, $hover_selector = '', $condition = 'layout_one' ) {
		$key = self::key( $label );

		$stack->add_control(
			$key . '_subtitle_label',
			array(
				'type'      => Controls_Manager::HEADING,
				'label'     => esc_html( $label ),
				'separator' => 'after',
				'condition' => array( 'layout_type' => $condition ),
			)
		);

		$stack->add_responsive_control(
			$key . '_padding',
			array(
				'label'      => esc_html__( 'Padding', 'xoom-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					$selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array( 'layout_type' => $condition ),
			)
		);

		$stack->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => $key . '_typography',
				'selector'  => $selector,
				'condition' => array( 'layout_type' => $condition ),
			)
		);

		$stack->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => $key . '_border',
				'selector'  => $selector,
				'condition' => array( 'layout_type' => $condition ),
			)
		);

		$stack->add_control(
			$key . '_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'xoom-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					$selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array( 'layout_type' => $condition ),
			)
		);

		$stack->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'      => $key . '_box_shadow',
				'selector'  => $selector,
				'condition' => array( 'layout_type' => $condition ),
			)
		);

		$stack->start_controls_tabs( $key . '_tabs_button' );

		$stack->start_controls_tab(
			$key . '_tab_button_normal',
			array(
				'label'     => esc_html__( 'Normal', 'xoom-addons-for-elementor' ),
				'condition' => array( 'layout_type' => $condition ),
			)
		);

		$stack->add_control(
			$key . '_color',
			array(
				'label'     => esc_html__( 'Text Color', 'xoom-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$selector => 'color: {{VALUE}};',
				),
				'condition' => array( 'layout_type' => $condition ),
			)
		);

		$stack->add_control(
			$key . '_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'xoom-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$selector => 'background-color: {{VALUE}};',
				),
				'condition' => array( 'layout_type' => $condition ),
			)
		);

		$stack->end_controls_tab();

		$stack->start_controls_tab(
			$key . '_tab_button_hover',
			array(
				'label'     => esc_html__( 'Hover', 'xoom-addons-for-elementor' ),
				'condition' => array( 'layout_type' => $condition ),
			)
		);

		$stack->add_control(
			$key . '_hover_color',
			array(
				'label'     => esc_html__( 'Text Color', 'xoom-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$selector => 'color: {{VALUE}};',
				),
				'condition' => array( 'layout_type' => $condition ),
			)
		);

		$stack->add_control(
			$key . '_hover_bg_color',
			array(
				'label'     => esc_html__( 'Background Color', 'xoom-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$hover_selector => 'background-color: {{VALUE}};',
				),
				'condition' => array( 'layout_type' => $condition ),
			)
		);

		$stack->add_control(
			$key . '_hover_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'xoom-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$hover_selector => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'layout_type'            => $condition,
					$key . '_border_border!' => '',
				),
			)
		);

		$stack->end_controls_tab();
		$stack->end_controls_tabs();
	}

	/**
	 * Add a select control that lets the user pick the HTML tag for a heading.
	 *
	 * @param \Elementor\Controls_Stack $stack   Controls stack (widget or section).
	 * @param string                    $label   Control label, already translated.
	 * @param string                    $default Default tag.
	 * @param string                    $layout  Layout suffix used to keep control names unique.
	 * @return void
	 */
	public static function heading_tag( $stack, $label, $default = 'h3', $layout = '' ) {
		$key  = self::key( $label );
		$name = $key . '_tag' . ( '' !== $layout ? '_' . $layout : '' );

		$stack->add_control(
			$name,
			array(
				'label'       => sprintf(
					/* translators: %s: heading label. */
					esc_html__( '%s Tag', 'xoom-addons-for-elementor' ),
					esc_html( $label )
				),
				'type'        => Controls_Manager::SELECT,
				'label_block' => true,
				'options'     => array(
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
					'p'    => 'p',
				),
				'default'     => $default,
			)
		);
	}

	/**
	 * Turn a human label into a safe, unique control-name prefix.
	 *
	 * @param string $label Group label.
	 * @return string
	 */
	private static function key( $label ) {
		return str_replace( ' ', '_', trim( (string) $label ) );
	}
}
