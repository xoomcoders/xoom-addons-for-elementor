<?php
/**
 * Reusable toggle switch.
 *
 * Expects `$toggle` in scope with keys:
 *  - scope   (string) `widgets` or `modules`.
 *  - id      (string) Component slug.
 *  - checked (bool)   Current state.
 *  - label   (string) Accessible name.
 *
 * @package Xoom_Addons\Admin
 */

defined( 'ABSPATH' ) || exit;

$toggle_input_id = 'xoom-toggle-' . $toggle['scope'] . '-' . $toggle['id'];
?>
<label class="xoom-switch">
	<input
		type="checkbox"
		id="<?php echo esc_attr( $toggle_input_id ); ?>"
		class="xoom-switch__input"
		role="switch"
		aria-checked="<?php echo $toggle['checked'] ? 'true' : 'false'; ?>"
		aria-label="<?php echo esc_attr( $toggle['label'] ); ?>"
		data-xoom-toggle
		data-scope="<?php echo esc_attr( $toggle['scope'] ); ?>"
		data-id="<?php echo esc_attr( $toggle['id'] ); ?>"
		value="1"
		<?php checked( $toggle['checked'] ); ?>
	/>
	<span class="xoom-switch__track" aria-hidden="true">
		<span class="xoom-switch__thumb"></span>
	</span>
</label>
