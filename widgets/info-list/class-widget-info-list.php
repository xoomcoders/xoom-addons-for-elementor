<?php
/**
 * Info List widget.
 *
 * @package Xoom_Addons\Widgets
 */

namespace Xoom_Addons\Widgets\Info_List;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Repeater;
use Xoom_Addons\Abstracts\Base_Widget;

defined( 'ABSPATH' ) || exit;

/**
 * A repeater powered list of features with custom icons.
 */
class Widget_Info_List extends Base_Widget {

	/**
	 * Elementor widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'xoom-info-list';
	}

	/**
	 * Widget label shown in the panel.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Info List', 'xoom-addons-for-elementor' );
	}

	/**
	 * Panel icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-bullet-list';
	}

	/**
	 * Search keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords() {
		return array( 'list', 'features', 'bullets', 'checklist' );
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
				'label' => __( 'Info List', 'xoom-addons-for-elementor' ),
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'text',
			array(
				'label'       => __( 'Text', 'xoom-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'List item', 'xoom-addons-for-elementor' ),
				'label_block' => true,
				'dynamic'     => array( 'active' => true ),
			)
		);

		$repeater->add_control(
			'icon',
			array(
				'label'   => __( 'Icon', 'xoom-addons-for-elementor' ),
				'type'    => Controls_Manager::ICONS,
				'default' => array(
					'value'   => 'fas fa-check',
					'library' => 'fa-solid',
				),
			)
		);

		$repeater->add_control(
			'link',
			array(
				'label'   => __( 'Link', 'xoom-addons-for-elementor' ),
				'type'    => Controls_Manager::URL,
				'default' => array( 'url' => '' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Items', 'xoom-addons-for-elementor' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ text }}}',
				'default'     => array(
					array(
						'text' => __( 'Fast, conditional asset loading', 'xoom-addons-for-elementor' ),
					),
					array(
						'text' => __( 'Enable only the widgets you use', 'xoom-addons-for-elementor' ),
					),
					array(
						'text' => __( 'Accessible, responsive components', 'xoom-addons-for-elementor' ),
					),
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

		$this->add_control(
			'icon_color',
			array(
				'label'     => __( 'Icon color', 'xoom-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#12a150',
				'selectors' => array(
					'{{WRAPPER}} .xoom-info-list__icon'     => 'color: {{VALUE}};',
					'{{WRAPPER}} .xoom-info-list__icon svg' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'icon_size',
			array(
				'label'     => __( 'Icon size', 'xoom-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 8,
						'max' => 64,
					),
				),
				'default'   => array( 'size' => 16 ),
				'selectors' => array(
					'{{WRAPPER}} .xoom-info-list__icon'     => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .xoom-info-list__icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label'     => __( 'Text color', 'xoom-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array(
					'{{WRAPPER}} .xoom-info-list__text' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'text_typography',
				'selector' => '{{WRAPPER}} .xoom-info-list__text',
			)
		);

		$this->add_responsive_control(
			'item_gap',
			array(
				'label'     => __( 'Item spacing', 'xoom-addons-for-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 40,
					),
				),
				'default'   => array( 'size' => 10 ),
				'selectors' => array(
					'{{WRAPPER}} .xoom-info-list__item' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
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

		if ( empty( $settings['items'] ) || ! is_array( $settings['items'] ) ) {
			return;
		}
		?>
		<ul class="xoom-info-list">
			<?php
			foreach ( $settings['items'] as $index => $item ) {
				$key      = 'item-' . (int) $index;
				$has_icon = ! empty( $item['icon']['value'] );
				$has_link = ! empty( $item['link']['url'] ) && ! empty( $item['text'] );

				if ( $has_link ) {
					$this->add_link_attributes( $key, $item['link'] );
					$this->add_render_attribute( $key, 'class', 'xoom-info-list__link' );
				}
				?>
				<li class="xoom-info-list__item">
					<?php if ( $has_icon ) : ?>
						<span class="xoom-info-list__icon" aria-hidden="true">
							<?php Icons_Manager::render_icon( $item['icon'], array( 'aria-hidden' => 'true' ) ); ?>
						</span>
					<?php endif; ?>

					<?php if ( $has_link ) : ?>
						<a
							<?php echo $this->get_render_attribute_string( $key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor escapes attributes. ?>
						>
					<?php endif; ?>

					<span class="xoom-info-list__text"><?php echo esc_html( $item['text'] ); ?></span>

					<?php if ( $has_link ) : ?>
						</a>
					<?php endif; ?>
				</li>
				<?php
			}
			?>
		</ul>
		<?php
	}
}
