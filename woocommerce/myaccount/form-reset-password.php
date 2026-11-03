<?php
/**
 * Lost password reset form.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.2.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_reset_password_form' );
?>

<div class="sawerna-login">

	<h2 class="sawerna-login__heading"><?php esc_html_e( 'Set new password', 'woocommerce' ); ?></h2>

	<form method="post" class="sawerna-login__form woocommerce-ResetPassword lost_reset_password">

		<p class="sawerna-login__hint">
			<?php echo wp_kses_post( apply_filters( 'woocommerce_reset_password_message', esc_html__( 'Enter a new password below.', 'woocommerce' ) ) ); ?>
		</p>

		<div class="sawerna-login__field">
			<label class="sawerna-login__label" for="password_1">
				<?php esc_html_e( 'New password', 'woocommerce' ); ?>
				<span class="sawerna-login__required" aria-hidden="true">*</span>
			</label>
			<input
				type="password"
				class="sawerna-login__input input-text woocommerce-Input--text"
				name="password_1"
				id="password_1"
				autocomplete="new-password"
				required
				aria-required="true"
			>
		</div>

		<div class="sawerna-login__field">
			<label class="sawerna-login__label" for="password_2">
				<?php esc_html_e( 'Re-enter new password', 'woocommerce' ); ?>
				<span class="sawerna-login__required" aria-hidden="true">*</span>
			</label>
			<input
				type="password"
				class="sawerna-login__input input-text woocommerce-Input--text"
				name="password_2"
				id="password_2"
				autocomplete="new-password"
				required
				aria-required="true"
			>
		</div>

		<input type="hidden" name="reset_key" value="<?php echo esc_attr( $args['key'] ); ?>">
		<input type="hidden" name="reset_login" value="<?php echo esc_attr( $args['login'] ); ?>">

		<?php do_action( 'woocommerce_resetpassword_form' ); ?>

		<div class="sawerna-login__actions sawerna-login__actions--register">
			<input type="hidden" name="wc_reset_password" value="true">
			<?php wp_nonce_field( 'reset_password', 'woocommerce-reset-password-nonce' ); ?>
			<button type="submit" class="sawerna-login__btn woocommerce-Button" value="<?php esc_attr_e( 'Save', 'woocommerce' ); ?>">
				<?php esc_html_e( 'Save', 'woocommerce' ); ?>
			</button>
		</div>

	</form>

</div>

<?php do_action( 'woocommerce_after_reset_password_form' ); ?>
