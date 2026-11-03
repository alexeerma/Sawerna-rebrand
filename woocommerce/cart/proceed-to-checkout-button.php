<?php
/**
 * Proceed to checkout button.
 *
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;
?>

<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="checkout-button button alt wc-forward sawerna-cart-totals__button">
	<?php esc_html_e( 'Vormista tellimus', 'sawerna' ); ?>
</a>
