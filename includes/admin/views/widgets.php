<?php
/**
 * Widgets screen.
 *
 * @package Xoom_Addons\Admin
 */

use Xoom_Addons\Registries\Category_Registry;
use Xoom_Addons\Settings;

defined( 'ABSPATH' ) || exit;

$show_pro = (bool) $settings->get( Settings::GROUP_GENERAL, 'show_pro_items', true );

$registered = $catalog->widgets();

if ( ! $show_pro ) {
	$registered = array_filter(
		$registered,
		static function ( $widget ) {
			return 'free' === $widget['package'];
		}
	);
}

$components = array();
$has_pro    = false;

foreach ( $registered as $widget ) {
	$chips = array();

	foreach ( $widget['categories'] as $slug ) {
		$chips[] = Category_Registry::title( $slug );
	}

	if ( 'pro' === $widget['package'] ) {
		$has_pro = true;
	}

	$widget['chips'] = $chips;
	$widget['tags']  = implode(
		' ',
		array_merge(
			array( $widget['id'], $widget['title'], $widget['description'] ),
			$widget['keywords'],
			$chips
		)
	);

	$components[] = $widget;
}

$filter_options = array();

foreach ( Category_Registry::all() as $slug => $category ) {
	$filter_options[ $slug ] = $category['title'];
}

$counts = $catalog->widget_counts();

$scope          = 'widgets';
$search_label   = __( 'Search widgets', 'xoom-addons-for-elementor' );
$filter_legend  = __( 'Filter widgets by category', 'xoom-addons-for-elementor' );
$show_package   = $has_pro;

include __DIR__ . '/../partials/header.php';
?>

<header class="xoom-page-head">
	<div class="xoom-page-head__text">
		<h1 class="xoom-page-title"><?php esc_html_e( 'Widgets', 'xoom-addons-for-elementor' ); ?></h1>
		<p class="xoom-page-subtitle">
			<?php
			printf(
				/* translators: 1: total widgets, 2: active widgets, 3: inactive widgets. */
				esc_html__( '%1$d widgets available · %2$d active · %3$d inactive', 'xoom-addons-for-elementor' ),
				(int) $counts['total'],
				(int) $counts['active'],
				(int) $counts['inactive']
			);
			?>
		</p>
	</div>
</header>

<?php
include __DIR__ . '/../partials/catalog-toolbar.php';
include __DIR__ . '/../partials/catalog-grid.php';

include __DIR__ . '/../partials/footer.php';
