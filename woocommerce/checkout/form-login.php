<?php
/**
 * Checkout login prompt.
 *
 * @package WooCommerce\Templates
 * @version 10.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( is_user_logged_in() ) {
	return;
}
?>

<section class="sawerna-checkout-login" aria-label="<?php esc_attr_e( 'Sisselogimine', 'sawerna' ); ?>">
	<div class="sawerna-checkout-login__copy">
		<h2 class="sawerna-checkout-login__title"><?php esc_html_e( 'Kas oled Sawerna püsiklient?', 'sawerna' ); ?></h2>
	</div>

	<a class="sawerna-checkout-login__button" href="<?php echo esc_url( sawerna_woocommerce_checkout_login_url() ); ?>">
		<?php esc_html_e( 'Logi sisse / Liitu', 'sawerna' ); ?>
	</a>
</section>
