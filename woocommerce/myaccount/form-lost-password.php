<?php
/**
 * Lost password form
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.2.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_lost_password_form' );
?>

<div class="sawerna-login">

	<h2 class="sawerna-login__heading"><?php esc_html_e( 'Lost your password?', 'woocommerce' ); ?></h2>

	<form method="post" class="sawerna-login__form woocommerce-ResetPassword lost_reset_password">

		<p class="sawerna-login__hint">
			<?php echo wp_kses_post( apply_filters( 'woocommerce_lost_password_message', esc_html__( 'Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.', 'woocommerce' ) ) ); ?>
		</p>

		<div class="sawerna-login__field">
			<label class="sawerna-login__label" for="user_login">
				<?php esc_html_e( 'Username or email', 'woocommerce' ); ?>
				<span class="sawerna-login__required" aria-hidden="true">*</span>
			</label>
			<input
				class="sawerna-login__input input-text woocommerce-Input--text"
				type="text"
				name="user_login"
				id="user_login"
				autocomplete="username"
				required
				aria-required="true"
			>
		</div>

		<?php do_action( 'woocommerce_lostpassword_form' ); ?>

		<div class="sawerna-login__actions sawerna-login__actions--register">
			<input type="hidden" name="wc_reset_password" value="true">
			<?php wp_nonce_field( 'lost_password', 'woocommerce-lost-password-nonce' ); ?>
			<button type="submit" class="sawerna-login__btn woocommerce-Button" value="<?php esc_attr_e( 'Reset password', 'woocommerce' ); ?>">
				<?php esc_html_e( 'Reset password', 'woocommerce' ); ?>
			</button>
		</div>

	</form>

</div>

<?php do_action( 'woocommerce_after_lost_password_form' ); ?>
