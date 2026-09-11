<?php
/**
 * Counter widget.
 *
 * @package Xoom_Addons\Widgets
 */

namespace Xoom_Addons\Widgets\Counter;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Xoom_Addons\Abstracts\Base_Widget;

defined( 'ABSPATH' ) || exit;

/**
 * An animated number counter. The final value is rendered server side, so the
 * figure is correct even before (or without) JavaScript.
 */
class Widget_Counter extends Base_Widget {

	/**
	 * Elementor widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'xoom-counter';
	}

	/**
	 * Widget label shown in the panel.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Counter', 'xoom-addons-for-elementor' );
	}

	/**
	 * Panel icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-counter';
	}

	/**
	 * Search keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords() {
		return array( 'counter', 'number', 'stats', 'odometer' );
	}

	/**
	 * The counter needs the shared frontend script to animate.
	 *
	 * @return string[]
	 */
	public function get_script_depends() {
		return array( 'xoom-addons-widgets' );
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
				'label' => __( 'Counter', 'xoom-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'starting_number',
			array(
				'label'   => __( 'Starting number', 'xoom-addons-for-elementor' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 0,
			)
		);

		$this->add_control(
			'ending_number',
			array(
				'label'   => __( 'Ending number', 'xoom-addons-for-elementor' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 250,
			)
		);

		$this->add_control(
			'prefix',
			array(
				'label'       => __( 'Prefix', 'xoom-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => '(',
				'dynamic'     => array( 'active' => true ),
			)
		);

		$this->add_control(
			'suffix',
			array(
				'label'       => __( 'Suffix', 'xoom-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '+',
				'placeholder' => '+',
				'dynamic'     => array( 'active' => true ),
			)
		);

		$this->add_control(
			'thousand_separator',
			array(
				'label'   => __( 'Thousand separator', 'xoom-addons-for-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);

		$this->add_control(
			'duration',
			array(
				'label'   => __( 'Duration (ms)', 'xoom-addons-for-elementor' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 200,
				'max'     => 10000,
				'step'    => 100,
				'default' => 1600,
			)
		);

		$this->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'xoom-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Projects delivered', 'xoom-addons-for-elementor' ),
				'label_block' => true,
				'dynamic'     => array( 'active' => true ),
				'separator'   => 'before',
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
					'{{WRAPPER}} .xoom-counter' => 'text-align: {{VALUE}};',
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
			'number_color',
			array(
				'label'     => __( 'Number color', 'xoom-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .xoom-counter__value' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'number_typography',
				'selector' => '{{WRAPPER}} .xoom-counter__value',
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'Title color', 'xoom-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array(
					'{{WRAPPER}} .xoom-counter__title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .xoom-counter__title',
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

		$start     = isset( $settings['starting_number'] ) ? (float) $settings['starting_number'] : 0;
		$end       = isset( $settings['ending_number'] ) ? (float) $settings['ending_number'] : 0;
		$duration  = isset( $settings['duration'] ) ? absint( $settings['duration'] ) : 1600;
		$separator = 'yes' === $settings['thousand_separator'];
		$prefix    = isset( $settings['prefix'] ) ? (string) $settings['prefix'] : '';
		$suffix    = isset( $settings['suffix'] ) ? (string) $settings['suffix'] : '';
		$title     = isset( $settings['title'] ) ? (string) $settings['title'] : '';

		$display = $separator ? number_format_i18n( $end ) : (string) $end;
		?>
		<div
			class="xoom-counter"
			data-xoom-counter
			data-start="<?php echo esc_attr( $start ); ?>"
			data-end="<?php echo esc_attr( $end ); ?>"
			data-duration="<?php echo esc_attr( $duration ); ?>"
			data-separator="<?php echo $separator ? '1' : '0'; ?>"
		>
			<div class="xoom-counter__value">
				<?php if ( '' !== $prefix ) : ?>
					<span class="xoom-counter__prefix"><?php echo esc_html( $prefix ); ?></span>
				<?php endif; ?>

				<span class="xoom-counter__number" data-xoom-counter-value><?php echo esc_html( $display ); ?></span>

				<?php if ( '' !== $suffix ) : ?>
					<span class="xoom-counter__suffix"><?php echo esc_html( $suffix ); ?></span>
				<?php endif; ?>
			</div>

			<?php if ( '' !== $title ) : ?>
				<p class="xoom-counter__title"><?php echo esc_html( $title ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}
}
