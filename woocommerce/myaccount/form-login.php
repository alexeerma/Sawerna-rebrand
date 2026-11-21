<?php
/**
 * Login Form
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.9.0
 */

defined( 'ABSPATH' ) || exit;

$registration_enabled = 'yes' === get_option( 'woocommerce_enable_myaccount_registration' );

do_action( 'woocommerce_before_customer_login_form' );

$login_banner_image = get_theme_mod( 'sawerna_login_banner_image', '' );
$login_banner_text  = get_theme_mod( 'sawerna_login_banner_text', '' );
?>

<?php if ( $login_banner_image || $login_banner_text ) : ?>
<div class="sawerna-login-banner">
	<?php if ( $login_banner_image ) : ?>
	<div class="sawerna-login-banner__image" style="background-image: url('<?php echo esc_url( str_replace( ')', '%29', $login_banner_image ) ); ?>');" role="img" aria-hidden="true"></div>
	<?php endif; ?>
	<?php if ( $login_banner_text ) : ?>
	<div class="sawerna-login-banner__text"><?php echo wp_kses_post( $login_banner_text ); ?></div>
	<?php endif; ?>
</div>
<?php endif; ?>

<div class="sawerna-login<?php echo $registration_enabled ? ' sawerna-login--with-register' : ''; ?>">

	<?php if ( $registration_enabled ) : ?>
	<div class="sawerna-login__columns" id="customer_login">
		<div class="sawerna-login__col u-column1 col-1">
	<?php endif; ?>

		<h2 class="sawerna-login__heading"><?php esc_html_e( 'Login', 'woocommerce' ); ?></h2>

		<form class="sawerna-login__form woocommerce-form woocommerce-form-login login" method="post" novalidate>

			<?php do_action( 'woocommerce_login_form_start' ); ?>

			<div class="sawerna-login__field">
				<label class="sawerna-login__label" for="username">
					<?php esc_html_e( 'Username or email address', 'woocommerce' ); ?>
					<span class="sawerna-login__required" aria-hidden="true">*</span>
				</label>
				<input
					type="text"
					class="sawerna-login__input input-text woocommerce-Input--text"
					name="username"
					id="username"
					autocomplete="username"
					value="<?php echo ( ! empty( $_POST['username'] ) && is_string( $_POST['username'] ) ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['username'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only redisplay of the submitted value; WC_Form_Handler verifies the nonce when processing. ?>"
					required
					aria-required="true"
				><?php // @codingStandardsIgnoreLine ?>
			</div>

			<div class="sawerna-login__field">
				<label class="sawerna-login__label" for="password">
					<?php esc_html_e( 'Password', 'woocommerce' ); ?>
					<span class="sawerna-login__required" aria-hidden="true">*</span>
				</label>
				<input
					class="sawerna-login__input input-text woocommerce-Input--text"
					type="password"
					name="password"
					id="password"
					autocomplete="current-password"
					required
					aria-required="true"
				>
			</div>

			<?php do_action( 'woocommerce_login_form' ); ?>

			<div class="sawerna-login__actions">
				<label class="sawerna-login__remember">
					<input class="sawerna-login__checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever">
					<span><?php esc_html_e( 'Remember me', 'woocommerce' ); ?></span>
				</label>
				<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
				<button
					type="submit"
					class="sawerna-login__btn woocommerce-form-login__submit"
					name="login"
					value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>"
				>
					<?php esc_html_e( 'Log in', 'woocommerce' ); ?>
				</button>
			</div>

			<p class="sawerna-login__lost-password">
				<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>">
					<?php esc_html_e( 'Lost your password?', 'woocommerce' ); ?>
				</a>
			</p>

			<?php do_action( 'woocommerce_login_form_end' ); ?>

		</form>

	<?php if ( $registration_enabled ) : ?>
		</div>

		<div class="sawerna-login__col sawerna-login__col--register u-column2 col-2">

			<h2 class="sawerna-login__heading"><?php esc_html_e( 'Register', 'woocommerce' ); ?></h2>

			<form method="post" class="sawerna-login__form woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?>>

				<?php do_action( 'woocommerce_register_form_start' ); ?>

				<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
					<div class="sawerna-login__field">
						<label class="sawerna-login__label" for="reg_username">
							<?php esc_html_e( 'Username', 'woocommerce' ); ?>
							<span class="sawerna-login__required" aria-hidden="true">*</span>
						</label>
						<input
							type="text"
							class="sawerna-login__input input-text woocommerce-Input--text"
							name="username"
							id="reg_username"
							autocomplete="username"
							value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['username'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only redisplay of the submitted value; WC_Form_Handler verifies the nonce when processing. ?>"
							required
							aria-required="true"
						><?php // @codingStandardsIgnoreLine ?>
					</div>
				<?php endif; ?>

				<div class="sawerna-login__field">
					<label class="sawerna-login__label" for="reg_email">
						<?php esc_html_e( 'Email address', 'woocommerce' ); ?>
						<span class="sawerna-login__required" aria-hidden="true">*</span>
					</label>
					<input
						type="email"
						class="sawerna-login__input input-text woocommerce-Input--text"
						name="email"
						id="reg_email"
						autocomplete="email"
						value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['email'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only redisplay of the submitted value; WC_Form_Handler verifies the nonce when processing. ?>"
						required
						aria-required="true"
					><?php // @codingStandardsIgnoreLine ?>
				</div>

				<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
					<div class="sawerna-login__field">
						<label class="sawerna-login__label" for="reg_password">
							<?php esc_html_e( 'Password', 'woocommerce' ); ?>
							<span class="sawerna-login__required" aria-hidden="true">*</span>
						</label>
						<input
							type="password"
							class="sawerna-login__input input-text woocommerce-Input--text"
							name="password"
							id="reg_password"
							autocomplete="new-password"
							required
							aria-required="true"
						>
					</div>
				<?php else : ?>
					<p class="sawerna-login__hint"><?php esc_html_e( 'A link to set a new password will be sent to your email address.', 'woocommerce' ); ?></p>
				<?php endif; ?>

				<?php do_action( 'woocommerce_register_form' ); ?>

				<div class="sawerna-login__actions sawerna-login__actions--register">
					<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
					<button
						type="submit"
						class="sawerna-login__btn woocommerce-Button woocommerce-form-register__submit"
						name="register"
						value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>"
					>
						<?php esc_html_e( 'Register', 'woocommerce' ); ?>
					</button>
				</div>

				<?php do_action( 'woocommerce_register_form_end' ); ?>

			</form>

		</div>
	</div>
	<?php endif; ?>

</div>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
