<?php
/**
 * Heading widget.
 *
 * @package Xoom_Addons\Widgets
 */

namespace Xoom_Addons\Widgets\Heading;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Typography;
use Xoom_Addons\Abstracts\Base_Widget;

defined( 'ABSPATH' ) || exit;

/**
 * A heading with an optional highlighted fragment and link.
 */
class Widget_Heading extends Base_Widget {

	/**
	 * Elementor widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'xoom-heading';
	}

	/**
	 * Widget label shown in the panel.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Heading', 'xoom-addons-for-elementor' );
	}

	/**
	 * Panel icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-heading';
	}

	/**
	 * Search keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords() {
		return array( 'heading', 'title', 'text', 'headline' );
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
				'label' => __( 'Heading', 'xoom-addons-for-elementor' ),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'xoom-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXTAREA,
				'default'     => __( 'A heading that stands out', 'xoom-addons-for-elementor' ),
				'placeholder' => __( 'Enter your heading', 'xoom-addons-for-elementor' ),
				'dynamic'     => array( 'active' => true ),
				'rows'        => 3,
			)
		);

		$this->add_control(
			'highlight_text',
			array(
				'label'       => __( 'Highlighted text', 'xoom-addons-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => __( 'Optional highlighted words', 'xoom-addons-for-elementor' ),
				'dynamic'     => array( 'active' => true ),
			)
		);

		$this->add_control(
			'tag',
			array(
				'label'   => __( 'HTML tag', 'xoom-addons-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h2',
				'options' => array(
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
					'{{WRAPPER}} .xoom-heading-wrap' => 'text-align: {{VALUE}};',
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
			'title_color',
			array(
				'label'     => __( 'Text color', 'xoom-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .xoom-heading' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'highlight_color',
			array(
				'label'     => __( 'Highlight color', 'xoom-addons-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .xoom-heading__highlight' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'typography',
				'selector' => '{{WRAPPER}} .xoom-heading',
			)
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			array(
				'name'     => 'text_shadow',
				'selector' => '{{WRAPPER}} .xoom-heading',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Normalise the requested HTML tag against a safe allow list.
	 *
	 * @param string $tag Requested tag.
	 * @return string
	 */
	private function safe_tag( $tag ) {
		$allowed = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span', 'p' );
		$tag     = strtolower( (string) $tag );

		return in_array( $tag, $allowed, true ) ? $tag : 'h2';
	}

	/**
	 * Render the widget on the frontend.
	 *
	 * @return void
	 */
	protected function render() {
		$settings  = $this->get_settings_for_display();
		$tag       = $this->safe_tag( $settings['tag'] );
		$has_link  = ! empty( $settings['link']['url'] );
		$highlight = isset( $settings['highlight_text'] ) ? trim( (string) $settings['highlight_text'] ) : '';

		if ( $has_link ) {
			$this->add_link_attributes( 'link', $settings['link'] );
			$this->add_render_attribute( 'link', 'class', 'xoom-heading__link' );
		}
		?>
		<div class="xoom-heading-wrap">
			<<?php echo esc_html( $tag ); ?> class="xoom-heading">
				<?php if ( $has_link ) : ?>
					<a <?php echo $this->get_render_attribute_string( 'link' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor escapes attributes. ?>>
				<?php endif; ?>

				<?php echo esc_html( $settings['title'] ); ?>

				<?php if ( '' !== $highlight ) : ?>
					<span class="xoom-heading__highlight"><?php echo esc_html( $highlight ); ?></span>
				<?php endif; ?>

				<?php if ( $has_link ) : ?>
					</a>
				<?php endif; ?>
			</<?php echo esc_html( $tag ); ?>>
		</div>
		<?php
	}
}
