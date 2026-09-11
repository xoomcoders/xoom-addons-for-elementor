<?php
/**
 * Feature Box widget.
 *
 * @package Xoom_Addons\Widgets
 */

namespace Xoom_Addons\Widgets\Feature_Box;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Xoom_Addons\Abstracts\Base_Widget;

defined( 'ABSPATH' ) || exit;

/**
 * An icon paired with a title and description.
 */
class Widget_Feature_Box extends Base_Widget {

	/**
	 * Elementor widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'xoom-feature-box';
	}

	/**
	 * Widget label shown in the panel.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Feature Box', 'xoom-addons-for-elementor' );
	}

	/**
	 * Panel icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-feature-box';
	}

	/**
	 * Search keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords() {
		return array( 'icon', 'box', 'feature', 'service' );
	}

	/**
	 * Normalise the icon position against an allow list.
	 *
	 * @param string $position Requested position.
	 * @return string
	 */
	private function safe_position( $position ) {
		$allowed = array( 'top', 'left', 'right' );
		$position = strtolower( (string) $position );

		return in_array( $position, $allowed, true ) ? $position : 'top';
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
				'label' => __( 'Feature Box', 'xoom-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'icon',
			array(
				'label'   => __( 'Icon', 'xoom-addons-for-elementor' ),
				'type'    => Controls_Manager::ICONS,
				'default' => array(
					'value'   => 'fas fa-star',
					'library' => 'fa-solid',
				),
			)
		);

		$this->add_control(
			'position',
			array(
				'label'   => __( 'Icon position', 'xoom-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'top',
				'options' => array(
					'top'   => __( 'Top', 'xoom-addons-for-elementor' ),
					'left'  => __( 'Left', 'xoom-addons-for-elementor' ),
					'right' => __( 'Right', 'xoom-addons-for-elementor' ),
				),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'xoom-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Icon box title', 'xoom-addons-for-elementor' ),
				'label_block' => true,
				'dynamic'     => array( 'active' => true ),
			)
		);

		$this->add_control(
			'description',
			array(
				'label'       => __( 'Description', 'xoom-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => __( 'Pair a short supporting sentence with your icon to explain the value you deliver.', 'xoom-addons-for-elementor' ),
				'rows'        => 4,
				'dynamic'     => array( 'active' => true ),
			)
		);

		$this->add_control(
			'link',
			array(
				'label'     => __( 'Link', 'xoom-addons-for-elementor' ),
				'type'      => Controls_Manager::URL,
				'default'   => array( 'url' => '' ),
				'dynamic'   => array( 'active' => true ),
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'align',
			array(
				'label'     => __( 'Alignment', 'xoom-addons-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'center',
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
					'{{WRAPPER}} .xoom-feature-box' => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_box',
			array(
				'label' => __( 'Box', 'xoom-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'box_background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => '{{WRAPPER}} .xoom-feature-box',
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'box_border',
				'selector' => '{{WRAPPER}} .xoom-feature-box',
			)
		);

		$this->add_responsive_control(
			'box_radius',
			array(
				'label'      => __( 'Border radius', 'xoom-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .xoom-feature-box' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'box_padding',
			array(
				'label'      => __( 'Padding', 'xoom-addons-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .xoom-feature-box' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'box_shadow',
				'selector' => '{{WRAPPER}} .xoom-feature-box',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_icon',
			array(
				'label' => __( 'Icon', 'xoom-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'icon_color',
			array(
				'label'     => __( 'Color', 'xoom-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#5b4bff',
				'selectors' => array(
					'{{WRAPPER}} .xoom-feature-box__icon'     => 'color: {{VALUE}};',
					'{{WRAPPER}} .xoom-feature-box__icon svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'icon_size',
			array(
				'label'     => __( 'Size', 'xoom-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 12,
						'max' => 96,
					),
				),
				'default'   => array( 'size' => 34 ),
				'selectors' => array(
					'{{WRAPPER}} .xoom-feature-box__icon'     => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .xoom-feature-box__icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'icon_gap',
			array(
				'label'     => __( 'Spacing', 'xoom-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 80,
					),
				),
				'default'   => array( 'size' => 16 ),
				'selectors' => array(
					'{{WRAPPER}} .xoom-feature-box--left'                       => 'gap: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .xoom-feature-box--right'                      => 'gap: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .xoom-feature-box--top .xoom-feature-box__content' => 'margin-top: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_content',
			array(
				'label' => __( 'Content', 'xoom-addons-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'Title color', 'xoom-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .xoom-feature-box__title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .xoom-feature-box__title',
			)
		);

		$this->add_control(
			'description_color',
			array(
				'label'     => __( 'Description color', 'xoom-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array(
					'{{WRAPPER}} .xoom-feature-box__desc' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'description_typography',
				'selector' => '{{WRAPPER}} .xoom-feature-box__desc',
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

		$position = $this->safe_position( isset( $settings['position'] ) ? $settings['position'] : 'top' );
		$has_icon = ! empty( $settings['icon']['value'] );
		$has_link = ! empty( $settings['link']['url'] );
		$title    = isset( $settings['title'] ) ? (string) $settings['title'] : '';
		$desc     = isset( $settings['description'] ) ? (string) $settings['description'] : '';

		if ( $has_link ) {
			$this->add_link_attributes( 'link', $settings['link'] );
			$this->add_render_attribute( 'link', 'class', 'xoom-feature-box__link' );
		}
		?>
		<div class="xoom-feature-box xoom-feature-box--<?php echo esc_attr( $position ); ?>">
			<?php if ( $has_icon ) : ?>
				<span class="xoom-feature-box__icon" aria-hidden="true">
					<?php Icons_Manager::render_icon( $settings['icon'], array( 'aria-hidden' => 'true' ) ); ?>
				</span>
			<?php endif; ?>

			<div class="xoom-feature-box__content">
				<?php if ( '' !== $title ) : ?>
					<h3 class="xoom-feature-box__title">
						<?php if ( $has_link ) : ?>
							<a <?php echo $this->get_render_attribute_string( 'link' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor escapes attributes. ?>>
						<?php endif; ?>
						<?php echo esc_html( $title ); ?>
						<?php if ( $has_link ) : ?>
							</a>
						<?php endif; ?>
					</h3>
				<?php endif; ?>

				<?php if ( '' !== $desc ) : ?>
					<div class="xoom-feature-box__desc"><?php echo wp_kses_post( $desc ); ?></div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
