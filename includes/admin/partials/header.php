<?php
/**
 * Dashboard top bar and primary navigation.
 *
 * Expects `$current_page` in scope (provided by Admin::render_view()).
 *
 * @package Xoom_Addons\Admin
 */

use Xoom_Addons\Admin\Admin;

defined( 'ABSPATH' ) || exit;

$edition_label = $catalog->edition_label();

$nav_icons = array(
	Admin::PAGE_DASHBOARD  => 'dashicons-dashboard',
	Admin::PAGE_WIDGETS    => 'dashicons-screenoptions',
	Admin::PAGE_EXTENSIONS => 'dashicons-admin-plugins',
	Admin::PAGE_SETTINGS   => 'dashicons-admin-generic',
);

$edition_slug = strtolower( str_replace( array( ' ', '+' ), array( '-', '' ), $edition_label ) );
?>
<div class="xoom-shell">

	<header class="xoom-topbar">
		<div class="xoom-topbar__inner">
			<a class="xoom-brand" href="<?php echo esc_url( $this->url( Admin::PAGE_DASHBOARD ) ); ?>">
				<span class="xoom-brand__mark" aria-hidden="true">
					<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" focusable="false">
						<path d="M12 2 3 6.5v11L12 22l9-4.5v-11L12 2Z" fill="currentColor" opacity=".18"/>
						<path d="M12 4.6 5.2 8v8L12 19.4 18.8 16V8L12 4.6Z" fill="none" stroke="currentColor" stroke-width="1.4"/>
						<path d="M12 8.4 8.6 10v4L12 15.6 15.4 14v-4L12 8.4Z" fill="currentColor"/>
					</svg>
				</span>
				<span class="xoom-brand__text">
					<span class="xoom-brand__name"><?php esc_html_e( 'Xoom Addons', 'xoom-addons-for-elementor' ); ?></span>
					<span class="xoom-brand__meta">
						<?php
						printf(
							/* translators: %s: plugin version number. */
							esc_html__( 'v%s', 'xoom-addons-for-elementor' ),
							esc_html( XOOM_ADDONS_VERSION )
						);
						?>
						<span class="xoom-brand__sep" aria-hidden="true">&middot;</span>
						<span class="xoom-edition xoom-edition--<?php echo esc_attr( $edition_slug ); ?>">
							<?php echo esc_html( $edition_label ); ?>
						</span>
					</span>
				</span>
			</a>

			<nav class="xoom-nav" aria-label="<?php esc_attr_e( 'Xoom Addons pages', 'xoom-addons-for-elementor' ); ?>">
				<?php foreach ( $this->pages() as $slug => $label ) : ?>
					<a
						class="xoom-nav__link<?php echo $slug === $current_page ? ' is-active' : ''; ?>"
						href="<?php echo esc_url( $this->url( $slug ) ); ?>"
						<?php echo $slug === $current_page ? ' aria-current="page"' : ''; ?>
					>
						<?php if ( isset( $nav_icons[ $slug ] ) ) : ?>
							<span class="dashicons <?php echo esc_attr( $nav_icons[ $slug ] ); ?>" aria-hidden="true"></span>
						<?php endif; ?>
						<span><?php echo esc_html( $label ); ?></span>
					</a>
				<?php endforeach; ?>
			</nav>

			<div
				class="xoom-topbar__status"
				data-xoom-toasts
				role="status"
				aria-live="polite"
			></div>
		</div>
	</header>

	<main class="xoom-content">
		<div class="xoom-container">
