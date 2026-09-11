<?php
/**
 * Reusable component card for a widget or an extension.
 *
 * Expects `$component` in scope with keys: id, title, description, icon,
 * package, enabled, chips (array) and tags (string).
 * Also expects `$scope` (`widgets` or `modules`).
 *
 * @package Xoom_Addons\Admin
 */

defined( 'ABSPATH' ) || exit;

$component_classes = array( 'xoom-card', 'xoom-component' );

$component_classes[] = $component['enabled'] ? 'is-enabled' : 'is-disabled';
?>
<article
	class="<?php echo esc_attr( implode( ' ', $component_classes ) ); ?>"
	data-xoom-component
	data-id="<?php echo esc_attr( $component['id'] ); ?>"
	data-scope="<?php echo esc_attr( $scope ); ?>"
	data-status="<?php echo $component['enabled'] ? 'active' : 'inactive'; ?>"
	data-package="<?php echo esc_attr( $component['package'] ); ?>"
	data-categories="<?php echo esc_attr( implode( ' ', $component['categories'] ) ); ?>"
	data-tags="<?php echo esc_attr( strtolower( $component['tags'] ) ); ?>"
>
	<div class="xoom-component__top">
		<span class="xoom-component__icon" aria-hidden="true">
			<span class="dashicons <?php echo esc_attr( $component['icon'] ); ?>"></span>
		</span>

		<?php if ( 'pro' === $component['package'] ) : ?>
			<span class="xoom-badge xoom-badge--pro"><?php esc_html_e( 'Pro', 'xoom-addons-for-elementor' ); ?></span>
		<?php endif; ?>

		<?php
		$toggle = array(
			'scope'   => $scope,
			'id'      => $component['id'],
			'checked' => (bool) $component['enabled'],
			'label'   => sprintf(
				/* translators: %s: component name. */
				__( 'Enable %s', 'xoom-addons-for-elementor' ),
				$component['title']
			),
		);

		include __DIR__ . '/toggle.php';
		?>
	</div>

	<h3 class="xoom-component__title"><?php echo esc_html( $component['title'] ); ?></h3>

	<?php if ( '' !== $component['description'] ) : ?>
		<p class="xoom-component__desc"><?php echo esc_html( $component['description'] ); ?></p>
	<?php endif; ?>

	<?php if ( ! empty( $component['chips'] ) ) : ?>
		<div class="xoom-component__footer">
			<ul class="xoom-chips">
				<?php foreach ( $component['chips'] as $chip ) : ?>
					<li class="xoom-chip"><?php echo esc_html( $chip ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>
</article>
