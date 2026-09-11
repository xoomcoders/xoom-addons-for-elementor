<?php
/**
 * Shared toolbar for the Widgets and Extensions screens.
 *
 * Expects in scope:
 *  - $scope          (string) `widgets` or `modules`.
 *  - $search_label   (string) Accessible label for the search field.
 *  - $filter_legend  (string) Accessible legend for the filter chips.
 *  - $filter_options (array)  slug => label.
 *  - $show_package   (bool)   Whether to render the Free/Pro filter.
 *
 * @package Xoom_Addons\Admin
 */

defined( 'ABSPATH' ) || exit;

$search_id = 'xoom-search-' . $scope;
?>
<div class="xoom-toolbar-card">
	<div class="xoom-toolbar">
		<div class="xoom-toolbar__search">
			<label class="screen-reader-text" for="<?php echo esc_attr( $search_id ); ?>">
				<?php echo esc_html( $search_label ); ?>
			</label>
			<span class="dashicons dashicons-search" aria-hidden="true"></span>
			<input
				type="search"
				id="<?php echo esc_attr( $search_id ); ?>"
				class="xoom-input xoom-input--search"
				placeholder="<?php echo esc_attr( $search_label ); ?>"
				autocomplete="off"
				data-xoom-search
			/>
		</div>

		<div class="xoom-toolbar__segments">
			<div class="xoom-segmented" role="group" aria-label="<?php esc_attr_e( 'Filter by status', 'xoom-addons-for-elementor' ); ?>">
				<button type="button" class="xoom-segmented__btn is-active" data-xoom-status="all" aria-pressed="true"><?php esc_html_e( 'All', 'xoom-addons-for-elementor' ); ?></button>
				<button type="button" class="xoom-segmented__btn" data-xoom-status="active" aria-pressed="false"><?php esc_html_e( 'Active', 'xoom-addons-for-elementor' ); ?></button>
				<button type="button" class="xoom-segmented__btn" data-xoom-status="inactive" aria-pressed="false"><?php esc_html_e( 'Inactive', 'xoom-addons-for-elementor' ); ?></button>
			</div>

			<?php if ( $show_package ) : ?>
				<div class="xoom-segmented" role="group" aria-label="<?php esc_attr_e( 'Filter by package', 'xoom-addons-for-elementor' ); ?>">
					<button type="button" class="xoom-segmented__btn is-active" data-xoom-package="all" aria-pressed="true"><?php esc_html_e( 'Free & Pro', 'xoom-addons-for-elementor' ); ?></button>
					<button type="button" class="xoom-segmented__btn" data-xoom-package="free" aria-pressed="false"><?php esc_html_e( 'Free', 'xoom-addons-for-elementor' ); ?></button>
					<button type="button" class="xoom-segmented__btn" data-xoom-package="pro" aria-pressed="false"><?php esc_html_e( 'Pro', 'xoom-addons-for-elementor' ); ?></button>
				</div>
			<?php endif; ?>
		</div>

		<div class="xoom-toolbar__actions">
			<button
				type="button"
				class="xoom-btn xoom-btn--ghost"
				data-xoom-bulk="enable"
				data-scope="<?php echo esc_attr( $scope ); ?>"
			>
				<?php esc_html_e( 'Enable all', 'xoom-addons-for-elementor' ); ?>
			</button>
			<button
				type="button"
				class="xoom-btn xoom-btn--ghost"
				data-xoom-bulk="disable"
				data-scope="<?php echo esc_attr( $scope ); ?>"
			>
				<?php esc_html_e( 'Disable all', 'xoom-addons-for-elementor' ); ?>
			</button>
		</div>
	</div>

	<div class="xoom-filters">
		<div class="xoom-filter-chips" role="group" aria-label="<?php echo esc_attr( $filter_legend ); ?>">
			<button type="button" class="xoom-filter-chip is-active" data-xoom-filter="" aria-pressed="true">
				<?php esc_html_e( 'All', 'xoom-addons-for-elementor' ); ?>
			</button>
			<?php foreach ( $filter_options as $slug => $label ) : ?>
				<button
					type="button"
					class="xoom-filter-chip"
					data-xoom-filter="<?php echo esc_attr( $slug ); ?>"
					aria-pressed="false"
				>
					<?php echo esc_html( $label ); ?>
				</button>
			<?php endforeach; ?>
		</div>

		<p class="xoom-results" data-xoom-results aria-live="polite"></p>
	</div>
</div>
