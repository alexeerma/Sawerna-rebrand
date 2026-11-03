<?php
/**
 * Checkout billing form.
 *
 * @package WooCommerce\Templates
 * @version 3.6.0
 * @global WC_Checkout $checkout
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="woocommerce-billing-fields sawerna-checkout-fields sawerna-checkout-fields--billing">
	<h2 class="sawerna-checkout-fields__title">
		<?php
		if ( wc_ship_to_billing_address_only() && WC()->cart->needs_shipping() ) {
			esc_html_e( 'Arveldusandmed ja kohaletoimetamine', 'sawerna' );
		} else {
			esc_html_e( 'Arveldusandmed', 'sawerna' );
		}
		?>
	</h2>

	<?php do_action( 'woocommerce_before_checkout_billing_form', $checkout ); ?>

	<div class="woocommerce-billing-fields__field-wrapper sawerna-checkout-fields__grid">
		<?php
		$fields = $checkout->get_checkout_fields( 'billing' );

		foreach ( $fields as $key => $field ) {
			woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
		}
		?>
	</div>

	<div class="sawerna-checkout-fields__account-optin">
		<?php if ( ! is_user_logged_in() ) : ?>
			<label class="sawerna-checkout-checkbox" for="sawerna-create-account">
				<input id="sawerna-create-account" type="checkbox" checked>
				<span><?php esc_html_e( 'Loo mulle Sawerna lehele püsikonto', 'sawerna' ); ?></span>
			</label>
		<?php else : ?>
			<?php do_action( 'woocommerce_after_checkout_billing_form', $checkout ); ?>
		<?php endif; ?>
	</div>
</div>
