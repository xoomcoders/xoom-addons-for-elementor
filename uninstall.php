<?php
/**
 * Uninstall routine.
 *
 * Only removes stored data when the user explicitly opted in from
 * Xoom Addons → Settings → Advanced.
 *
 * @package Xoom_Addons
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$options = array(
	'xoom_addons_widget_status',
	'xoom_addons_module_status',
	'xoom_addons_general',
	'xoom_addons_performance',
	'xoom_addons_advanced',
	'xoom_addons_version',
);

$advanced = get_option( 'xoom_addons_advanced', array() );

if ( ! is_array( $advanced ) || empty( $advanced['delete_data_on_uninstall'] ) ) {
	return;
}

foreach ( $options as $option ) {
	delete_option( $option );
}
