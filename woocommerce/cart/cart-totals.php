<?php
/**
 * Cart totals template.
 *
 * @package WooCommerce\Templates
 * @version 2.3.6
 */

defined( 'ABSPATH' ) || exit;

$count_label = function_exists( 'sawerna_woocommerce_mini_cart_count_label' ) ? sawerna_woocommerce_mini_cart_count_label() : '';
?>

<div class="cart_totals sawerna-cart-totals <?php echo esc_attr( WC()->customer->has_calculated_shipping() ? 'calculated_shipping' : '' ); ?>">
	<?php do_action( 'woocommerce_before_cart_totals' ); ?>

	<div class="sawerna-cart-totals__header">
		<h2 class="sawerna-cart-totals__title">
			<?php esc_html_e( 'Tellimuse kokkuvõte', 'sawerna' ); ?>
			<?php if ( '' !== $count_label ) : ?>
				<span class="sawerna-cart-totals__count"><?php echo esc_html( $count_label ); ?></span>
			<?php endif; ?>
		</h2>
	</div>

	<table cellspacing="0" class="shop_table shop_table_responsive sawerna-cart-totals__table">
		<tr class="cart-subtotal">
			<th><?php esc_html_e( 'Vahesumma:', 'sawerna' ); ?></th>
			<td data-title="<?php esc_attr_e( 'Vahesumma', 'sawerna' ); ?>"><?php wc_cart_totals_subtotal_html(); ?></td>
		</tr>

		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
			<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
				<th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
				<td data-title="<?php echo esc_attr( wc_cart_totals_coupon_label( $coupon, false ) ); ?>"><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
			<?php do_action( 'woocommerce_cart_totals_before_shipping' ); ?>
			<?php wc_cart_totals_shipping_html(); ?>
			<?php do_action( 'woocommerce_cart_totals_after_shipping' ); ?>
		<?php elseif ( WC()->cart->needs_shipping() && 'yes' === get_option( 'woocommerce_enable_shipping_calc' ) ) : ?>
			<tr class="shipping">
				<th><?php esc_html_e( 'Tarne:', 'sawerna' ); ?></th>
				<td data-title="<?php esc_attr_e( 'Tarne', 'sawerna' ); ?>"><?php woocommerce_shipping_calculator(); ?></td>
			</tr>
		<?php endif; ?>

		<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<tr class="fee">
				<th><?php echo esc_html( $fee->name ); ?></th>
				<td data-title="<?php echo esc_attr( $fee->name ); ?>"><?php wc_cart_totals_fee_html( $fee ); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php
		if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) {
			$taxable_address = WC()->customer->get_taxable_address();
			$estimated_text  = '';

			if ( WC()->customer->is_customer_outside_base() && ! WC()->customer->has_calculated_shipping() ) {
				/* translators: %s location. */
				$estimated_text = sprintf( ' <small>' . esc_html__( '(estimated for %s)', 'woocommerce' ) . '</small>', WC()->countries->estimated_for_prefix( $taxable_address[0] ) . WC()->countries->countries[ $taxable_address[0] ] );
			}

			if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
				foreach ( WC()->cart->get_tax_totals() as $code => $tax ) {
					?>
					<tr class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
						<th><?php echo esc_html( $tax->label ) . $estimated_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></th>
						<td data-title="<?php echo esc_attr( $tax->label ); ?>"><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
					</tr>
					<?php
				}
			} else {
				?>
				<tr class="tax-total">
					<th><?php echo esc_html( WC()->countries->tax_or_vat() ) . $estimated_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></th>
					<td data-title="<?php echo esc_attr( WC()->countries->tax_or_vat() ); ?>"><?php wc_cart_totals_taxes_total_html(); ?></td>
				</tr>
				<?php
			}
		}
		?>

		<?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>

		<tr class="order-total">
			<th><?php esc_html_e( 'Kokku:', 'sawerna' ); ?></th>
			<td data-title="<?php esc_attr_e( 'Kokku', 'sawerna' ); ?>"><?php wc_cart_totals_order_total_html(); ?></td>
		</tr>

		<?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>
	</table>

	<div class="wc-proceed-to-checkout sawerna-cart-totals__actions">
		<?php do_action( 'woocommerce_proceed_to_checkout' ); ?>
	</div>

	<?php do_action( 'woocommerce_after_cart_totals' ); ?>
</div>
