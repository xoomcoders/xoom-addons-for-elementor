<?php
/**
 * Dashboard screen: overview, quick actions and system status.
 *
 * @package Xoom_Addons\Admin
 */

use Xoom_Addons\Admin\Admin;
use Xoom_Addons\Settings;

defined( 'ABSPATH' ) || exit;

$widget_counts = $catalog->widget_counts();
$module_counts = $catalog->module_counts();

$optimize_assets = (bool) $settings->get( Settings::GROUP_PERFORMANCE, 'optimize_assets', true );

$elementor_version = defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '';
$elementor_ok      = '' !== $elementor_version && version_compare( $elementor_version, XOOM_ADDONS_MIN_ELEMENTOR_VERSION, '>=' );
$php_ok            = version_compare( PHP_VERSION, XOOM_ADDONS_MIN_PHP_VERSION, '>=' );

include __DIR__ . '/../partials/header.php';
?>

<section class="xoom-hero">
	<div class="xoom-hero__body">
		<p class="xoom-hero__eyebrow">
			<span class="xoom-hero__pulse" aria-hidden="true"></span>
			<?php esc_html_e( 'Dashboard', 'xoom-addons-for-elementor' ); ?>
		</p>
		<h1 class="xoom-hero__title"><?php esc_html_e( 'A leaner, faster Elementor toolkit.', 'xoom-addons-for-elementor' ); ?></h1>
		<p class="xoom-hero__desc">
			<?php esc_html_e( 'Enable only what each site needs. Disabled widgets and extensions are never loaded, and their CSS and JavaScript never reach the page.', 'xoom-addons-for-elementor' ); ?>
		</p>
		<div class="xoom-hero__actions">
			<a class="xoom-btn xoom-btn--primary" href="<?php echo esc_url( $this->url( Admin::PAGE_WIDGETS ) ); ?>">
				<span class="dashicons dashicons-screenoptions" aria-hidden="true"></span>
				<?php esc_html_e( 'Manage widgets', 'xoom-addons-for-elementor' ); ?>
			</a>
			<a class="xoom-btn xoom-btn--ghost" href="<?php echo esc_url( $this->url( Admin::PAGE_SETTINGS ) ); ?>">
				<span class="dashicons dashicons-admin-generic" aria-hidden="true"></span>
				<?php esc_html_e( 'Global settings', 'xoom-addons-for-elementor' ); ?>
			</a>
		</div>
	</div>
</section>

<div class="xoom-stats">
	<article class="xoom-stat">
		<div class="xoom-stat__head">
			<span class="xoom-stat__icon" aria-hidden="true">
				<span class="dashicons dashicons-screenoptions"></span>
			</span>
			<span class="xoom-stat__label"><?php esc_html_e( 'Widgets', 'xoom-addons-for-elementor' ); ?></span>
		</div>
		<span class="xoom-stat__value" data-xoom-stat="widgets-total"><?php echo (int) $widget_counts['total']; ?></span>
		<span class="xoom-stat__meta">
			<span class="xoom-stat__badge is-ok">
				<span class="xoom-status-dot is-ok" aria-hidden="true"></span>
				<span data-xoom-stat="widgets-active"><?php echo (int) $widget_counts['active']; ?></span>
				<?php esc_html_e( 'active', 'xoom-addons-for-elementor' ); ?>
			</span>
			<span class="xoom-stat__badge">
				<span data-xoom-stat="widgets-inactive"><?php echo (int) $widget_counts['inactive']; ?></span>
				<?php esc_html_e( 'inactive', 'xoom-addons-for-elementor' ); ?>
			</span>
		</span>
	</article>

	<article class="xoom-stat">
		<div class="xoom-stat__head">
			<span class="xoom-stat__icon" aria-hidden="true">
				<span class="dashicons dashicons-admin-plugins"></span>
			</span>
			<span class="xoom-stat__label"><?php esc_html_e( 'Extensions', 'xoom-addons-for-elementor' ); ?></span>
		</div>
		<span class="xoom-stat__value" data-xoom-stat="modules-total"><?php echo (int) $module_counts['total']; ?></span>
		<span class="xoom-stat__meta">
			<span class="xoom-stat__badge is-ok">
				<span class="xoom-status-dot is-ok" aria-hidden="true"></span>
				<span data-xoom-stat="modules-active"><?php echo (int) $module_counts['active']; ?></span>
				<?php esc_html_e( 'active', 'xoom-addons-for-elementor' ); ?>
			</span>
			<span class="xoom-stat__badge">
				<span data-xoom-stat="modules-inactive"><?php echo (int) $module_counts['inactive']; ?></span>
				<?php esc_html_e( 'inactive', 'xoom-addons-for-elementor' ); ?>
			</span>
		</span>
	</article>

	<article class="xoom-stat">
		<div class="xoom-stat__head">
			<span class="xoom-stat__icon" aria-hidden="true">
				<span class="dashicons dashicons-performance"></span>
			</span>
			<span class="xoom-stat__label"><?php esc_html_e( 'Asset loading', 'xoom-addons-for-elementor' ); ?></span>
		</div>
		<span class="xoom-stat__value xoom-stat__value--text">
			<?php echo $optimize_assets ? esc_html__( 'Conditional', 'xoom-addons-for-elementor' ) : esc_html__( 'Global', 'xoom-addons-for-elementor' ); ?>
		</span>
		<span class="xoom-stat__meta">
			<span class="xoom-stat__badge<?php echo $optimize_assets ? ' is-ok' : ''; ?>">
				<span class="xoom-status-dot<?php echo $optimize_assets ? ' is-ok' : ''; ?>" aria-hidden="true"></span>
				<?php
				echo $optimize_assets
					? esc_html__( 'CSS & JS load where used', 'xoom-addons-for-elementor' )
					: esc_html__( 'CSS & JS load on every page', 'xoom-addons-for-elementor' );
				?>
			</span>
		</span>
	</article>

	<article class="xoom-stat">
		<div class="xoom-stat__head">
			<span class="xoom-stat__icon" aria-hidden="true">
				<span class="dashicons dashicons-shield-alt"></span>
			</span>
			<span class="xoom-stat__label"><?php esc_html_e( 'Elementor', 'xoom-addons-for-elementor' ); ?></span>
		</div>
		<span class="xoom-stat__value xoom-stat__value--text">
			<?php echo $elementor_ok ? esc_html( $elementor_version ) : esc_html__( 'Not detected', 'xoom-addons-for-elementor' ); ?>
		</span>
		<span class="xoom-stat__meta">
			<span class="xoom-stat__badge<?php echo $elementor_ok ? ' is-ok' : ' is-bad'; ?>">
				<span class="xoom-status-dot<?php echo $elementor_ok ? ' is-ok' : ' is-bad'; ?>" aria-hidden="true"></span>
				<?php
				echo $elementor_ok
					? esc_html__( 'Compatible', 'xoom-addons-for-elementor' )
					: esc_html__( 'Elementor is required', 'xoom-addons-for-elementor' );
				?>
			</span>
		</span>
	</article>
</div>

<?php
/**
 * Extension point: additional dashboard panels.
 *
 * The Pro plugin renders its licence panel here, so the free plugin never
 * has to know anything about licences.
 *
 * @param \Xoom_Addons\Catalog $catalog Catalog service.
 */
do_action( 'xoom_addons_dashboard_panels', $catalog );

if ( ! $catalog->is_pro_active() ) :
	?>
	<section class="xoom-panel xoom-promo">
		<span class="xoom-badge xoom-badge--soft"><?php esc_html_e( 'Pro', 'xoom-addons-for-elementor' ); ?></span>
		<div class="xoom-promo__text">
			<h2 class="xoom-panel__title"><?php esc_html_e( 'Unlock more with Xoom Addons Pro', 'xoom-addons-for-elementor' ); ?></h2>
			<p class="xoom-panel__subtitle">
				<?php esc_html_e( 'Pro adds advanced widgets such as Advanced Heading and Flip Box, plus priority support — installed as a separate plugin so this one stays lean.', 'xoom-addons-for-elementor' ); ?>
			</p>
		</div>
		<a
			class="xoom-btn xoom-btn--ghost xoom-promo__cta"
			href="<?php echo esc_url( 'https://example.com/xoom-addons-pro' ); ?>"
			target="_blank"
			rel="noopener noreferrer"
		>
			<?php esc_html_e( 'Explore Pro', 'xoom-addons-for-elementor' ); ?>
			<span class="dashicons dashicons-external" aria-hidden="true"></span>
		</a>
	</section>
	<?php
endif;
?>

<div class="xoom-columns">
	<section class="xoom-panel">
		<div class="xoom-panel__head">
			<div class="xoom-panel__heading">
				<span class="xoom-panel__icon" aria-hidden="true">
					<span class="dashicons dashicons-superhero"></span>
				</span>
				<div class="xoom-panel__titles">
					<h2 class="xoom-panel__title"><?php esc_html_e( 'Quick actions', 'xoom-addons-for-elementor' ); ?></h2>
					<p class="xoom-panel__subtitle"><?php esc_html_e( 'Apply a state to every available component at once.', 'xoom-addons-for-elementor' ); ?></p>
				</div>
			</div>
		</div>

		<ul class="xoom-actions">
			<li class="xoom-action">
				<span class="xoom-action__icon is-on" aria-hidden="true">
					<span class="dashicons dashicons-yes-alt"></span>
				</span>
				<span class="xoom-action__text">
					<strong><?php esc_html_e( 'Enable all widgets', 'xoom-addons-for-elementor' ); ?></strong>
					<span><?php esc_html_e( 'Turn on every widget you have access to.', 'xoom-addons-for-elementor' ); ?></span>
				</span>
				<button type="button" class="xoom-btn xoom-btn--ghost" data-xoom-bulk="enable" data-scope="widgets">
					<?php esc_html_e( 'Enable', 'xoom-addons-for-elementor' ); ?>
				</button>
			</li>
			<li class="xoom-action">
				<span class="xoom-action__icon is-off" aria-hidden="true">
					<span class="dashicons dashicons-dismiss"></span>
				</span>
				<span class="xoom-action__text">
					<strong><?php esc_html_e( 'Disable all widgets', 'xoom-addons-for-elementor' ); ?></strong>
					<span><?php esc_html_e( 'Keep only the widgets you actually use.', 'xoom-addons-for-elementor' ); ?></span>
				</span>
				<button type="button" class="xoom-btn xoom-btn--ghost" data-xoom-bulk="disable" data-scope="widgets">
					<?php esc_html_e( 'Disable', 'xoom-addons-for-elementor' ); ?>
				</button>
			</li>
			<li class="xoom-action">
				<span class="xoom-action__icon is-on" aria-hidden="true">
					<span class="dashicons dashicons-yes-alt"></span>
				</span>
				<span class="xoom-action__text">
					<strong><?php esc_html_e( 'Enable all extensions', 'xoom-addons-for-elementor' ); ?></strong>
					<span><?php esc_html_e( 'Turn on every extension you have access to.', 'xoom-addons-for-elementor' ); ?></span>
				</span>
				<button type="button" class="xoom-btn xoom-btn--ghost" data-xoom-bulk="enable" data-scope="modules">
					<?php esc_html_e( 'Enable', 'xoom-addons-for-elementor' ); ?>
				</button>
			</li>
			<li class="xoom-action">
				<span class="xoom-action__icon is-off" aria-hidden="true">
					<span class="dashicons dashicons-dismiss"></span>
				</span>
				<span class="xoom-action__text">
					<strong><?php esc_html_e( 'Disable all extensions', 'xoom-addons-for-elementor' ); ?></strong>
					<span><?php esc_html_e( 'Remove every non-essential extension.', 'xoom-addons-for-elementor' ); ?></span>
				</span>
				<button type="button" class="xoom-btn xoom-btn--ghost" data-xoom-bulk="disable" data-scope="modules">
					<?php esc_html_e( 'Disable', 'xoom-addons-for-elementor' ); ?>
				</button>
			</li>
		</ul>
	</section>

	<section class="xoom-panel">
		<div class="xoom-panel__head">
			<div class="xoom-panel__heading">
				<span class="xoom-panel__icon" aria-hidden="true">
					<span class="dashicons dashicons-heart"></span>
				</span>
				<div class="xoom-panel__titles">
					<h2 class="xoom-panel__title"><?php esc_html_e( 'System status', 'xoom-addons-for-elementor' ); ?></h2>
					<p class="xoom-panel__subtitle"><?php esc_html_e( 'Environment details for this installation.', 'xoom-addons-for-elementor' ); ?></p>
				</div>
			</div>
		</div>

		<ul class="xoom-status-list">
			<li>
				<span><?php esc_html_e( 'Xoom Addons', 'xoom-addons-for-elementor' ); ?></span>
				<strong><?php echo esc_html( XOOM_ADDONS_VERSION ); ?></strong>
			</li>
			<li>
				<span><?php esc_html_e( 'Edition', 'xoom-addons-for-elementor' ); ?></span>
				<strong><?php echo esc_html( $catalog->edition_label() ); ?></strong>
			</li>
			<li>
				<span><?php esc_html_e( 'WordPress', 'xoom-addons-for-elementor' ); ?></span>
				<strong><?php echo esc_html( get_bloginfo( 'version' ) ); ?></strong>
			</li>
			<li>
				<span><?php esc_html_e( 'PHP', 'xoom-addons-for-elementor' ); ?></span>
				<strong>
					<?php echo esc_html( PHP_VERSION ); ?>
					<span class="xoom-status-dot<?php echo $php_ok ? ' is-ok' : ' is-bad'; ?>" aria-hidden="true"></span>
				</strong>
			</li>
			<li>
				<span><?php esc_html_e( 'Elementor', 'xoom-addons-for-elementor' ); ?></span>
				<strong><?php echo $elementor_ok ? esc_html( $elementor_version ) : esc_html__( 'Missing', 'xoom-addons-for-elementor' ); ?></strong>
			</li>
		</ul>
	</section>
</div>

<?php
include __DIR__ . '/../partials/footer.php';
