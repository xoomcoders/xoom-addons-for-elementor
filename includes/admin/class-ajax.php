<?php
/**
 * Admin AJAX endpoints.
 *
 * Every request is nonce verified, capability checked and sanitised against
 * the registry before it can touch stored state.
 *
 * @package Xoom_Addons\Admin
 */

namespace Xoom_Addons\Admin;

use Xoom_Addons\Catalog;
use Xoom_Addons\Registries\Module_Registry;
use Xoom_Addons\Registries\Widget_Registry;
use Xoom_Addons\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Handles component toggles and settings persistence.
 */
class Ajax {

	/**
	 * Nonce action, shared with the admin screen.
	 */
	const NONCE_ACTION = Admin::NONCE_ACTION;

	/**
	 * Settings repository.
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Catalog service.
	 *
	 * @var Catalog
	 */
	private $catalog;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings Settings repository.
	 * @param Catalog  $catalog  Catalog service.
	 */
	public function __construct( Settings $settings, Catalog $catalog ) {
		$this->settings = $settings;
		$this->catalog  = $catalog;

		add_action( 'wp_ajax_xoom_addons_toggle', array( $this, 'toggle' ) );
		add_action( 'wp_ajax_xoom_addons_sync', array( $this, 'sync' ) );
		add_action( 'wp_ajax_xoom_addons_bulk', array( $this, 'bulk' ) );
		add_action( 'wp_ajax_xoom_addons_save_settings', array( $this, 'save_settings' ) );
		add_action( 'wp_ajax_xoom_addons_reset_group', array( $this, 'reset_group' ) );
	}

	/**
	 * Verify the nonce and the current user's capability.
	 *
	 * @return void
	 */
	private function guard() {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		if ( ! current_user_can( Admin::CAPABILITY ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to do this.', 'xoom-addons-for-elementor' ) ), 403 );
		}
	}

	/**
	 * Toggle a single widget or extension.
	 *
	 * @return void
	 */
	public function toggle() {
		$this->guard();

		$group   = $this->resolve_group( $this->posted_scope() );
		$id      = isset( $_POST['id'] ) ? sanitize_key( wp_unslash( $_POST['id'] ) ) : '';
		$enabled = isset( $_POST['enabled'] ) && rest_sanitize_boolean( wp_unslash( $_POST['enabled'] ) );

		if ( '' === $group ) {
			wp_send_json_error( array( 'message' => __( 'Unknown component type.', 'xoom-addons-for-elementor' ) ), 400 );
		}

		$components = $this->registry( $group );

		if ( ! isset( $components[ $id ] ) ) {
			wp_send_json_error( array( 'message' => __( 'Unknown component.', 'xoom-addons-for-elementor' ) ), 404 );
		}

		$status         = $this->settings->all( $group );
		$status[ $id ]  = $enabled;
		$this->settings->set_status( $group, $status );

		wp_send_json_success( $this->state_payload( $group ) );
	}

	/**
	 * Persist a batch of state changes for one scope.
	 *
	 * The dashboard debounces rapid toggling into a single request, so this
	 * is the endpoint that actually carries user intent.
	 *
	 * @return void
	 */
	public function sync() {
		$this->guard();

		$group = $this->resolve_group( $this->posted_scope() );

		if ( '' === $group ) {
			wp_send_json_error( array( 'message' => __( 'Unknown component type.', 'xoom-addons-for-elementor' ) ), 400 );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in guard().
		$changes = isset( $_POST['status'] ) && is_array( $_POST['status'] ) ? wp_unslash( $_POST['status'] ) : array();

		$components = $this->registry( $group );
		$status     = $this->settings->all( $group );
		$changed    = 0;

		foreach ( $changes as $id => $enabled ) {
			$id = sanitize_key( $id );

			if ( '' === $id || ! isset( $components[ $id ] ) ) {
				continue;
			}

			$status[ $id ] = rest_sanitize_boolean( $enabled );
			++$changed;
		}

		if ( $changed > 0 ) {
			$this->settings->set_status( $group, $status );
		}

		wp_send_json_success( $this->state_payload( $group ) );
	}

	/**
	 * Enable or disable many components at once.
	 *
	 * @return void
	 */
	public function bulk() {
		$this->guard();

		$group   = $this->resolve_group( $this->posted_scope() );
		$enabled = isset( $_POST['enabled'] ) && rest_sanitize_boolean( wp_unslash( $_POST['enabled'] ) );

		if ( '' === $group ) {
			wp_send_json_error( array( 'message' => __( 'Unknown component type.', 'xoom-addons-for-elementor' ) ), 400 );
		}

		$components = $this->registry( $group );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in guard().
		$requested = isset( $_POST['ids'] ) ? (array) wp_unslash( $_POST['ids'] ) : array();
		$requested = array_filter( array_map( 'sanitize_key', $requested ) );

		$targets = empty( $requested ) ? array_keys( $components ) : $requested;

		$status = $this->settings->all( $group );

		foreach ( $targets as $id ) {
			if ( ! isset( $components[ $id ] ) ) {
				continue;
			}

			$status[ $id ] = $enabled;
		}

		$this->settings->set_status( $group, $status );

		wp_send_json_success( $this->state_payload( $group ) );
	}

	/**
	 * Persist a scalar settings group.
	 *
	 * @return void
	 */
	public function save_settings() {
		$this->guard();

		$group = $this->resolve_settings_group();

		if ( '' === $group ) {
			wp_send_json_error( array( 'message' => __( 'Unknown settings group.', 'xoom-addons-for-elementor' ) ), 400 );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in guard().
		$values = isset( $_POST['values'] ) && is_array( $_POST['values'] ) ? wp_unslash( $_POST['values'] ) : array();

		$this->settings->update( $group, $values );

		wp_send_json_success(
			array(
				'group'   => $group,
				'values'  => $this->settings->all( $group ),
				'message' => __( 'Settings saved.', 'xoom-addons-for-elementor' ),
			)
		);
	}

	/**
	 * Restore a scalar settings group to its defaults.
	 *
	 * @return void
	 */
	public function reset_group() {
		$this->guard();

		$group = $this->resolve_settings_group();

		if ( '' === $group ) {
			wp_send_json_error( array( 'message' => __( 'Unknown settings group.', 'xoom-addons-for-elementor' ) ), 400 );
		}

		$this->settings->reset( $group );

		wp_send_json_success(
			array(
				'group'   => $group,
				'values'  => $this->settings->all( $group ),
				'message' => __( 'Defaults restored.', 'xoom-addons-for-elementor' ),
			)
		);
	}

	/**
	 * Read and validate the posted scope.
	 *
	 * @return string
	 */
	private function posted_scope() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in guard().
		return isset( $_POST['scope'] ) ? sanitize_key( wp_unslash( $_POST['scope'] ) ) : '';
	}

	/**
	 * Map an admin scope to its settings group.
	 *
	 * @param string $scope `widgets` or `modules`.
	 * @return string Settings group, or an empty string when invalid.
	 */
	private function resolve_group( $scope ) {
		if ( 'widgets' === $scope ) {
			return Settings::GROUP_WIDGETS;
		}

		if ( 'modules' === $scope ) {
			return Settings::GROUP_MODULES;
		}

		return '';
	}

	/**
	 * Map the posted settings tab to a settings group.
	 *
	 * @return string Settings group, or an empty string when invalid.
	 */
	private function resolve_settings_group() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in guard().
		$tab = isset( $_POST['group'] ) ? sanitize_key( wp_unslash( $_POST['group'] ) ) : '';

		$allowed = array( Settings::GROUP_GENERAL, Settings::GROUP_PERFORMANCE, Settings::GROUP_ADVANCED );

		return in_array( $tab, $allowed, true ) ? $tab : '';
	}

	/**
	 * Fetch the registry for a settings group.
	 *
	 * @param string $group Settings group.
	 * @return array
	 */
	private function registry( $group ) {
		return Settings::GROUP_WIDGETS === $group ? Widget_Registry::all() : Module_Registry::all();
	}

	/**
	 * Build the response payload used to refresh live counters.
	 *
	 * @param string $group Settings group that changed.
	 * @return array
	 */
	private function state_payload( $group ) {
		$is_widgets = Settings::GROUP_WIDGETS === $group;

		return array(
			'scope'  => $is_widgets ? 'widgets' : 'modules',
			'counts' => $is_widgets ? $this->catalog->widget_counts() : $this->catalog->module_counts(),
		);
	}
}
