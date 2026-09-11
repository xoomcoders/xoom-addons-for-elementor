<?php
/**
 * Settings screen: General, Performance and Advanced tabs.
 *
 * Adding a setting means adding one entry to the schema below — the markup,
 * persistence and sanitisation are all driven from it.
 *
 * @package Xoom_Addons\Admin
 */

use Xoom_Addons\Settings;

defined( 'ABSPATH' ) || exit;

$tabs = array(
	Settings::GROUP_GENERAL     => array(
		'label'       => __( 'General', 'xoom-addons-for-elementor' ),
		'description' => __( 'Applies across every widget and extension.', 'xoom-addons-for-elementor' ),
		'fields'      => array(
			'category_label' => array(
				'type'        => 'text',
				'label'       => __( 'Elementor panel label', 'xoom-addons-for-elementor' ),
				'description' => __( 'The category name used for Xoom widgets in the Elementor widget panel.', 'xoom-addons-for-elementor' ),
			),
			'show_pro_items' => array(
				'type'        => 'switch',
				'label'       => __( 'Show Pro components', 'xoom-addons-for-elementor' ),
				'description' => __( 'List locked Pro widgets and extensions in the dashboard.', 'xoom-addons-for-elementor' ),
			),
		),
	),
	Settings::GROUP_PERFORMANCE => array(
		'label'       => __( 'Performance', 'xoom-addons-for-elementor' ),
		'description' => __( 'Decide exactly when the plugin loads its CSS and JavaScript.', 'xoom-addons-for-elementor' ),
		'fields'      => array(
			'optimize_assets'     => array(
				'type'        => 'switch',
				'label'       => __( 'Load assets only when required', 'xoom-addons-for-elementor' ),
				'description' => __( 'Recommended. Plugin CSS and JS are printed only on pages that actually render a Xoom widget.', 'xoom-addons-for-elementor' ),
			),
			'defer_scripts'       => array(
				'type'        => 'switch',
				'label'       => __( 'Defer frontend JavaScript', 'xoom-addons-for-elementor' ),
				'description' => __( 'Adds the defer attribute so the plugin script never blocks rendering.', 'xoom-addons-for-elementor' ),
			),
			'remove_emoji_script' => array(
				'type'        => 'switch',
				'label'       => __( 'Remove WordPress emoji script', 'xoom-addons-for-elementor' ),
				'description' => __( 'Drops the emoji detection script and styles WordPress adds to every page.', 'xoom-addons-for-elementor' ),
			),
		),
	),
	Settings::GROUP_ADVANCED    => array(
		'label'       => __( 'Advanced', 'xoom-addons-for-elementor' ),
		'description' => __( 'Maintenance and data handling.', 'xoom-addons-for-elementor' ),
		'fields'      => array(
			'delete_data_on_uninstall' => array(
				'type'        => 'switch',
				'label'       => __( 'Delete settings on uninstall', 'xoom-addons-for-elementor' ),
				'description' => __( 'When enabled, all Xoom Addons options are removed when the plugin is deleted from WordPress.', 'xoom-addons-for-elementor' ),
			),
		),
	),
);

include __DIR__ . '/../partials/header.php';
?>

<header class="xoom-page-head">
	<div class="xoom-page-head__text">
		<h1 class="xoom-page-title"><?php esc_html_e( 'Settings', 'xoom-addons-for-elementor' ); ?></h1>
		<p class="xoom-page-subtitle"><?php esc_html_e( 'Control how Xoom Addons behaves across this site.', 'xoom-addons-for-elementor' ); ?></p>
	</div>
</header>

<section class="xoom-panel">
	<div class="xoom-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Settings sections', 'xoom-addons-for-elementor' ); ?>">
		<?php $is_first = true; ?>
		<?php foreach ( $tabs as $group => $tab ) : ?>
			<button
				type="button"
				role="tab"
				id="xoom-tab-<?php echo esc_attr( $group ); ?>"
				class="xoom-tab<?php echo $is_first ? ' is-active' : ''; ?>"
				aria-controls="xoom-tabpanel-<?php echo esc_attr( $group ); ?>"
				aria-selected="<?php echo $is_first ? 'true' : 'false'; ?>"
				tabindex="<?php echo $is_first ? '0' : '-1'; ?>"
				data-xoom-tab="<?php echo esc_attr( $group ); ?>"
			>
				<?php echo esc_html( $tab['label'] ); ?>
			</button>
			<?php $is_first = false; ?>
		<?php endforeach; ?>
	</div>

	<?php $is_first = true; ?>
	<?php foreach ( $tabs as $group => $tab ) : ?>
		<div
			class="xoom-tabpanel"
			id="xoom-tabpanel-<?php echo esc_attr( $group ); ?>"
			role="tabpanel"
			aria-labelledby="xoom-tab-<?php echo esc_attr( $group ); ?>"
			data-xoom-tabpanel="<?php echo esc_attr( $group ); ?>"
			<?php echo $is_first ? '' : 'hidden'; ?>
		>
			<form class="xoom-settings-form" data-xoom-settings-form data-group="<?php echo esc_attr( $group ); ?>">
				<div class="xoom-panel__head">
					<div>
						<h2 class="xoom-panel__title"><?php echo esc_html( $tab['label'] ); ?></h2>
						<p class="xoom-panel__subtitle"><?php echo esc_html( $tab['description'] ); ?></p>
					</div>
				</div>

				<div class="xoom-settings-list">
					<?php foreach ( $tab['fields'] as $key => $field ) : ?>
						<?php
						$field_id = 'xoom-field-' . $group . '-' . $key;
						$value    = $settings->get( $group, $key, '' );
						?>
						<div class="xoom-setting">
							<div class="xoom-setting__text">
								<label class="xoom-setting__label" for="<?php echo esc_attr( $field_id ); ?>">
									<?php echo esc_html( $field['label'] ); ?>
								</label>
								<p class="xoom-setting__desc"><?php echo esc_html( $field['description'] ); ?></p>
							</div>

							<div class="xoom-setting__control">
								<?php if ( 'switch' === $field['type'] ) : ?>
									<label class="xoom-switch">
										<input
											type="checkbox"
											id="<?php echo esc_attr( $field_id ); ?>"
											class="xoom-switch__input"
											role="switch"
											aria-checked="<?php echo $value ? 'true' : 'false'; ?>"
											name="<?php echo esc_attr( $key ); ?>"
											value="1"
											data-xoom-setting="bool"
											<?php checked( (bool) $value ); ?>
										/>
										<span class="xoom-switch__track" aria-hidden="true">
											<span class="xoom-switch__thumb"></span>
										</span>
									</label>
								<?php else : ?>
									<input
										type="text"
										id="<?php echo esc_attr( $field_id ); ?>"
										class="xoom-input"
										name="<?php echo esc_attr( $key ); ?>"
										value="<?php echo esc_attr( (string) $value ); ?>"
										data-xoom-setting="text"
									/>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<div class="xoom-form-actions">
					<button type="submit" class="xoom-btn xoom-btn--primary">
						<?php esc_html_e( 'Save changes', 'xoom-addons-for-elementor' ); ?>
					</button>
					<button type="button" class="xoom-btn xoom-btn--ghost" data-xoom-reset data-group="<?php echo esc_attr( $group ); ?>">
						<?php esc_html_e( 'Restore defaults', 'xoom-addons-for-elementor' ); ?>
					</button>
				</div>
			</form>
		</div>
		<?php $is_first = false; ?>
	<?php endforeach; ?>
</section>

<?php
include __DIR__ . '/../partials/footer.php';
