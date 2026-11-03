<?php
/**
 * Checkout coupon form.
 *
 * @package WooCommerce\Templates
 * @version 9.8.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! wc_coupons_enabled() ) {
	return;
}
?>

<form class="checkout_coupon woocommerce-form-coupon sawerna-checkout-coupon" method="post" id="woocommerce-checkout-form-coupon">
	<div class="sawerna-checkout-coupon__row">
		<label for="coupon_code" class="screen-reader-text"><?php esc_html_e( 'Coupon:', 'woocommerce' ); ?></label>
		<input type="text" name="coupon_code" class="input-text sawerna-checkout-coupon__input" placeholder="<?php esc_attr_e( 'Sooduskupongi kood', 'sawerna' ); ?>" id="coupon_code" value="" />
		<button type="submit" class="button sawerna-checkout-coupon__button" name="apply_coupon" value="<?php esc_attr_e( 'Lisa', 'sawerna' ); ?>"><?php esc_html_e( 'Lisa', 'sawerna' ); ?></button>
	</div>
</form>
