<?php
/**
 * Extensions screen.
 *
 * @package Xoom_Addons\Admin
 */

use Xoom_Addons\Registries\Module_Registry;
use Xoom_Addons\Settings;

defined( 'ABSPATH' ) || exit;

$show_pro = (bool) $settings->get( Settings::GROUP_GENERAL, 'show_pro_items', true );
$groups   = Module_Registry::groups();

$registered = $catalog->modules();

if ( ! $show_pro ) {
	$registered = array_filter(
		$registered,
		static function ( $module ) {
			return 'free' === $module['package'];
		}
	);
}

$components = array();
$has_pro    = false;

foreach ( $registered as $module ) {
	$group_label = isset( $groups[ $module['group'] ] ) ? $groups[ $module['group'] ] : $module['group'];

	if ( 'pro' === $module['package'] ) {
		$has_pro = true;
	}

	$module['chips'] = array( $group_label );
	$module['tags']  = implode( ' ', array( $module['id'], $module['title'], $module['description'], $group_label ) );

	$components[] = $module;
}

$counts = $catalog->module_counts();

$scope          = 'modules';
$search_label   = __( 'Search extensions', 'xoom-addons-for-elementor' );
$filter_legend  = __( 'Filter extensions by group', 'xoom-addons-for-elementor' );
$filter_options = $groups;
$show_package   = $has_pro;

include __DIR__ . '/../partials/header.php';
?>

<header class="xoom-page-head">
	<div class="xoom-page-head__text">
		<h1 class="xoom-page-title"><?php esc_html_e( 'Extensions', 'xoom-addons-for-elementor' ); ?></h1>
		<p class="xoom-page-subtitle">
			<?php
			printf(
				/* translators: 1: total extensions, 2: active extensions, 3: inactive extensions. */
				esc_html__( '%1$d extensions available · %2$d active · %3$d inactive', 'xoom-addons-for-elementor' ),
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
