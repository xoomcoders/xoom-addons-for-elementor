<?php
/**
 * Button widget.
 *
 * @package Xoom_Addons\Widgets
 */

namespace Xoom_Addons\Widgets\Button;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Xoom_Addons\Abstracts\Base_Widget;

defined( 'ABSPATH' ) || exit;

/**
 * A call-to-action button with an optional icon.
 */
class Widget_Button extends Base_Widget {

	/**
	 * Elementor widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'xoom-button';
	}

	/**
	 * Widget label shown in the panel.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Button', 'xoom-addons-for-elementor' );
	}

	/**
	 * Panel icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-button';
	}

	/**
	 * Search keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords() {
		return array( 'button', 'cta', 'link', 'action' );
	}

	/**
	 * Register the widget controls.
	 *
	 * @return void
	 */
	protected function register_controls() {
		
		$this->start_controls_section(
			'section_content',
			array(
				'label' => __( 'Button', 'xoom-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'text',
			array(
				'label'       => __( 'Label', 'xoom-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Get started', 'xoom-addons-for-elementor' ),
				'placeholder' => __( 'Button label', 'xoom-addons-for-elementor' ),
				'dynamic'     => array( 'active' => true ),
			)
		);

		$this->add_control(
			'link',
			array(
				'label'     => __( 'Link', 'xoom-addons-for-elementor' ),
				'type'      => Controls_Manager::URL,
				'default'   => array( 'url' => '#' ),
				'dynamic'   => array( 'active' => true ),
				'separator' => 'before',
			)
		);

		$this->add_control(
			'icon',
			array(
				'label'       => __( 'Icon', 'xoom-addons-for-elementor' ),
				'type'        => Controls_Manager::ICONS,
				'label_block' => false,
			)
		);

		$this->add_control(
			'icon_position',
			array(
				'label'     => __( 'Icon position', 'xoom-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'after',
				'options'   => array(
					'before' => __( 'Before text', 'xoom-addons-for-elementor' ),
					'after'  => __( 'After text', 'xoom-addons-for-elementor' ),
				),
				'condition' => array( 'icon[value]!' => '' ),
			)
		);

		$this->add_responsive_control(
			'size',
			array(
				'label'     => __( 'Size', 'xoom-addons-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'md',
				'options'   => array(
					'sm' => __( 'Small', 'xoom-addons-for-elementor' ),
					'md' => __( 'Medium', 'xoom-addons-for-elementor' ),
					'lg' => __( 'Large', 'xoom-addons-for-elementor' ),
				),
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'align',
			array(
				'label'     => __( 'Alignment', 'xoom-addons-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'left',
				'options'   => array(
					'left'   => array(
						'title' => __( 'Left', 'xoom-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'xoom-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'xoom-addons-for-elementor' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .xoom-button-wrap' => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'full_width',
			array(
				'label'     => __( 'Full width', 'xoom-addons-for-elementor' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => '',
				'selectors' => array(
					'{{WRAPPER}} .xoom-button' => 'width: 100%;',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			array(
				'label' => __( 'Style', 'xoom-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'typography',
				'selector' => '{{WRAPPER}} .xoom-button',
			)
		);

		$this->start_controls_tabs( 'tabs_colors' );

		$this->start_controls_tab(
			'tab_normal',
			array( 'label' => __( 'Normal', 'xoom-addons-for-elementor' ) )
		);

		$this->add_control(
			'text_color',
			array(
				'label'     => __( 'Text color', 'xoom-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .xoom-button' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'background_color',
			array(
				'label'     => __( 'Background color', 'xoom-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#5b4bff',
				'selectors' => array(
					'{{WRAPPER}} .xoom-button' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_hover',
			array( 'label' => __( 'Hover', 'xoom-addons-for-elementor' ) )
		);

		$this->add_control(
			'text_color_hover',
			array(
				'label'     => __( 'Text color', 'xoom-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .xoom-button:hover, {{WRAPPER}} .xoom-button:focus' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'background_color_hover',
			array(
				'label'     => __( 'Background color', 'xoom-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .xoom-button:hover, {{WRAPPER}} .xoom-button:focus' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'      => 'border',
				'selector'  => '{{WRAPPER}} .xoom-button',
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'border_radius',
			array(
				'label'      => __( 'Border radius', 'xoom-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .xoom-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'padding',
			array(
				'label'      => __( 'Padding', 'xoom-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .xoom-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'icon_size',
			array(
				'label'      => __( 'Icon size', 'xoom-addons-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array(
					'px' => array(
						'min' => 8,
						'max' => 64,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .xoom-button__icon'     => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .xoom-button__icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array( 'icon[value]!' => '' ),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'box_shadow',
				'selector' => '{{WRAPPER}} .xoom-button',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render the widget on the frontend.
	 *
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$has_icon     = ! empty( $settings['icon']['value'] );
		$icon_position = isset( $settings['icon_position'] ) ? $settings['icon_position'] : 'after';
		$size         = isset( $settings['size'] ) && '' !== $settings['size'] ? $settings['size'] : 'md';

		$this->add_render_attribute(
			'button',
			'class',
			array(
				'xoom-button',
				'xoom-button--' . $size,
			)
		);

		if ( 'yes' === $settings['full_width'] ) {
			$this->add_render_attribute( 'button', 'class', 'xoom-button--full' );
		}

		$this->add_link_attributes( 'button', $settings['link'] );
		?>
		<div class="xoom-button-wrap">
			<a <?php echo $this->get_render_attribute_string( 'button' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor escapes attributes. ?>>
				<?php if ( $has_icon && 'before' === $icon_position ) : ?>
					<span class="xoom-button__icon" aria-hidden="true">
						<?php Icons_Manager::render_icon( $settings['icon'], array( 'aria-hidden' => 'true' ) ); ?>
					</span>
				<?php endif; ?>

				<span class="xoom-button__text"><?php echo esc_html( $settings['text'] ); ?></span>

				<?php if ( $has_icon && 'after' === $icon_position ) : ?>
					<span class="xoom-button__icon" aria-hidden="true">
						<?php Icons_Manager::render_icon( $settings['icon'], array( 'aria-hidden' => 'true' ) ); ?>
					</span>
				<?php endif; ?>
			</a>
		</div>
		<?php
	}
}
