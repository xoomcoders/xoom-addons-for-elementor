<?php
/**
 * Shared component grid, used by the Widgets and Extensions screens.
 *
 * Expects in scope:
 *  - $components (array) Records enriched with `chips` and `tags`.
 *  - $scope      (string) `widgets` or `modules`.
 *
 * @package Xoom_Addons\Admin
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $components ) ) {
	return;
}
?>
<div class="xoom-grid" data-xoom-grid>
	<?php
	foreach ( $components as $component ) {
		include __DIR__ . '/component-card.php';
	}
	?>
</div>

<p class="xoom-empty" data-xoom-empty hidden>
	<span class="dashicons dashicons-search" aria-hidden="true"></span>
	<?php esc_html_e( 'No components match your filters.', 'xoom-addons-for-elementor' ); ?>
</p>
