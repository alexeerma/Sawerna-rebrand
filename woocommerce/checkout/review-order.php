<?php
/**
 * Checkout order review.
 *
 * @package WooCommerce\Templates
 * @version 5.2.0
 */

defined( 'ABSPATH' ) || exit;

$total_tax = (float) WC()->cart->get_total_tax();
?>

<section class="sawerna-checkout-review woocommerce-checkout-review-order-table">
	<div class="sawerna-checkout-review__summary">
		<h2 class="sawerna-checkout-review__title"><?php esc_html_e( 'Minu tellimus', 'sawerna' ); ?></h2>

		<div class="sawerna-checkout-review__panel">
			<div class="sawerna-checkout-review__header">
				<div class="sawerna-checkout-review__header-label"><?php esc_html_e( 'Toode', 'sawerna' ); ?></div>
				<div class="sawerna-checkout-review__header-label sawerna-checkout-review__header-label--subtotal"><?php esc_html_e( 'Vahesumma', 'sawerna' ); ?></div>
			</div>

			<div class="sawerna-checkout-review__items">
				<?php
				do_action( 'woocommerce_review_order_before_cart_contents' );

				foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
					$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

					if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 || ! apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
						continue;
					}

					$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'woocommerce_thumbnail' ), $cart_item, $cart_item_key );
					?>
					<div class="sawerna-checkout-review__item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
						<div class="sawerna-checkout-review__item-media">
							<?php echo wp_kses_post( $thumbnail ); ?>
						</div>

						<div class="sawerna-checkout-review__item-content">
							<div class="sawerna-checkout-review__item-title-row">
								<div class="sawerna-checkout-review__item-title">
									<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ); ?>
									<span class="sawerna-checkout-review__item-quantity"><?php echo esc_html( sprintf( 'x%s', (string) $cart_item['quantity'] ) ); ?></span>
								</div>
								<div class="sawerna-checkout-review__item-subtotal">
									<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ) ); ?>
								</div>
							</div>

							<div class="sawerna-checkout-review__item-meta">
								<?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
						</div>
					</div>
					<?php
				}

				do_action( 'woocommerce_review_order_after_cart_contents' );
				?>
			</div>

			<table class="shop_table sawerna-checkout-review__totals">
				<tbody>
					<tr class="cart-subtotal">
						<th><?php esc_html_e( 'Vahesumma:', 'sawerna' ); ?></th>
						<td><?php wc_cart_totals_subtotal_html(); ?></td>
					</tr>

					<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
						<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>
						<?php wc_cart_totals_shipping_html(); ?>
						<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>
					<?php endif; ?>

					<tr class="sawerna-checkout-review__coupon-row">
						<td colspan="2"><?php wc_get_template( 'checkout/form-coupon.php' ); ?></td>
					</tr>

					<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
						<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
							<th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
							<td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
						</tr>
					<?php endforeach; ?>

					<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
						<tr class="fee">
							<th><?php echo esc_html( $fee->name ); ?></th>
							<td><?php wc_cart_totals_fee_html( $fee ); ?></td>
						</tr>
					<?php endforeach; ?>

					<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
						<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
							<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
								<tr class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
									<th><?php echo esc_html( $tax->label ); ?></th>
									<td><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr class="tax-total">
								<th><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></th>
								<td><?php wc_cart_totals_taxes_total_html(); ?></td>
							</tr>
						<?php endif; ?>
					<?php endif; ?>
				</tbody>

				<tfoot>
					<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

					<tr class="order-total">
						<th><?php esc_html_e( 'Kokku:', 'sawerna' ); ?></th>
						<td>
							<div class="sawerna-checkout-review__order-total">
								<?php wc_cart_totals_order_total_html(); ?>

								<?php if ( $total_tax > 0 ) : ?>
									<div class="sawerna-checkout-review__tax-note">
										<?php
										printf(
											/* translators: %s: included VAT amount. */
											esc_html__( 'Sisaldab käibemaksu %s', 'sawerna' ),
											wp_kses_post( wc_price( $total_tax ) )
										);
										?>
									</div>
								<?php endif; ?>
							</div>
						</td>
					</tr>

					<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>
				</tfoot>
			</table>
		</div>
	</div>
</section>
