<?php
/**
 * Custom Sawerna thank-you page.
 *
 * @package WooCommerce\Templates
 * @version 11.2.0
 *
 * @var WC_Order $order
 */

defined( 'ABSPATH' ) || exit;

$get_delivery_text = static function ( WC_Order $order ) {
	$shipping_method = trim( (string) $order->get_shipping_method() );

	if ( '' !== $shipping_method ) {
		return esc_html( $shipping_method );
	}

	$shipping_address = $order->get_formatted_shipping_address();

	if ( '' !== trim( wp_strip_all_tags( (string) $shipping_address ) ) ) {
		return wp_kses_post( $shipping_address );
	}

	return esc_html__( 'Tulen ise järgi', 'sawerna' );
};

$get_billing_details = static function ( WC_Order $order ) {
	$details = array();

	$billing_name = trim( $order->get_formatted_billing_full_name() );
	if ( '' !== $billing_name ) {
		$details[] = esc_html( $billing_name );
	}

	$billing_company = trim( (string) $order->get_billing_company() );
	if ( '' !== $billing_company ) {
		$details[] = esc_html( $billing_company );
	}

	$billing_address_1 = trim( (string) $order->get_billing_address_1() );
	if ( '' !== $billing_address_1 ) {
		$details[] = esc_html( $billing_address_1 );
	}

	$billing_address_2 = trim( (string) $order->get_billing_address_2() );
	if ( '' !== $billing_address_2 ) {
		$details[] = esc_html( $billing_address_2 );
	}

	$city_line = trim( implode( ' ', array_filter( array( $order->get_billing_postcode(), $order->get_billing_city() ) ) ) );
	if ( '' !== $city_line ) {
		$details[] = esc_html( $city_line );
	}

	$billing_country = trim( (string) $order->get_billing_country() );
	if ( '' !== $billing_country && function_exists( 'WC' ) && WC()->countries ) {
		$countries = WC()->countries->get_countries();
		$details[] = esc_html( isset( $countries[ $billing_country ] ) ? $countries[ $billing_country ] : $billing_country );
	}

	$billing_phone = trim( (string) $order->get_billing_phone() );
	if ( '' !== $billing_phone ) {
		$details[] = sprintf(
			/* translators: %s: Customer billing phone number. */
			esc_html__( 'Telefon: %s', 'sawerna' ),
			esc_html( $billing_phone )
		);
	}

	$billing_email = trim( (string) $order->get_billing_email() );
	if ( '' !== $billing_email ) {
		$details[] = sprintf(
			/* translators: %s: Customer billing email. */
			esc_html__( 'E-mail: %s', 'sawerna' ),
			esc_html( $billing_email )
		);
	}

	return array_values( array_filter( $details ) );
};
?>

<div class="woocommerce-order sawerna-thankyou">
	<?php if ( $order ) : ?>
		<?php do_action( 'woocommerce_before_thankyou', $order->get_id() ); ?>

		<?php if ( $order->has_status( 'failed' ) ) : ?>
			<section class="sawerna-thankyou__hero sawerna-thankyou__hero--failed">
				<div class="sawerna-thankyou__container">
					<h1 class="sawerna-thankyou__title"><?php esc_html_e( 'Tellimuse vormistamine ebaõnnestus', 'sawerna' ); ?></h1>
					<?php
					$default_order_failed_text = esc_html__( 'Kahjuks ei saanud makset töödelda. Palun proovi uuesti või võta meiega ühendust.', 'sawerna' );

					/**
					 * Filters the message shown when an order has failed.
					 *
					 * @param string   $message The failed order message.
					 * @param WC_Order $order   The failed order.
					 *
					 * @since 11.2.0
					 */
					$order_failed_text = apply_filters( 'woocommerce_thankyou_order_failed_text', $default_order_failed_text, $order );
					$order_failed_text = is_string( $order_failed_text ) ? $order_failed_text : $default_order_failed_text;
					?>
					<p class="sawerna-thankyou__lead">
						<?php echo wp_kses_post( $order_failed_text ); ?>
					</p>
				</div>
			</section>

			<section class="sawerna-thankyou__content">
				<div class="sawerna-thankyou__container sawerna-thankyou__failed-actions">
					<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="sawerna-thankyou__button">
						<?php esc_html_e( 'Maksa uuesti', 'sawerna' ); ?>
					</a>

					<?php if ( is_user_logged_in() ) : ?>
						<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="sawerna-thankyou__button sawerna-thankyou__button--secondary">
							<?php esc_html_e( 'Minu konto', 'sawerna' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</section>
		<?php else : ?>
			<section class="sawerna-thankyou__hero">
				<div class="sawerna-thankyou__container">
					<h1 class="sawerna-thankyou__title"><?php esc_html_e( 'Sinu tellimus on edukalt saadetud!', 'sawerna' ); ?></h1>
					<p class="sawerna-thankyou__lead">
						<?php esc_html_e( 'Aitäh, et meilt ostsite! Loodame sinuga kohtuda juba üsna pea.', 'sawerna' ); ?>
					</p>
				</div>
			</section>

			<section class="sawerna-thankyou__summary-shell" aria-label="<?php esc_attr_e( 'Tellimuse kokkuvõte', 'sawerna' ); ?>">
				<div class="sawerna-thankyou__container">
					<div class="sawerna-thankyou__summary">
						<div class="sawerna-thankyou__summary-item">
							<span class="sawerna-thankyou__summary-label"><?php esc_html_e( 'Tellimuse number:', 'sawerna' ); ?></span>
							<strong class="sawerna-thankyou__summary-value"><?php echo esc_html( $order->get_order_number() ); ?></strong>
						</div>

						<div class="sawerna-thankyou__summary-item">
							<span class="sawerna-thankyou__summary-label"><?php esc_html_e( 'Kuupäev:', 'sawerna' ); ?></span>
							<strong class="sawerna-thankyou__summary-value">
								<?php
								echo esc_html(
									wc_format_datetime(
										$order->get_date_created(),
										_x( 'd.m.Y', 'thank you page order date format', 'sawerna' )
									)
								);
								?>
							</strong>
						</div>

						<div class="sawerna-thankyou__summary-item">
							<span class="sawerna-thankyou__summary-label"><?php esc_html_e( 'E-mail:', 'sawerna' ); ?></span>
							<strong class="sawerna-thankyou__summary-value">
								<?php echo $order->get_billing_email() ? esc_html( $order->get_billing_email() ) : esc_html__( 'Puudub', 'sawerna' ); ?>
							</strong>
						</div>

						<div class="sawerna-thankyou__summary-item">
							<span class="sawerna-thankyou__summary-label"><?php esc_html_e( 'Maksumus kokku:', 'sawerna' ); ?></span>
							<strong class="sawerna-thankyou__summary-value"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong>
						</div>
					</div>
				</div>
			</section>

			<section class="sawerna-thankyou__content">
				<div class="sawerna-thankyou__container sawerna-thankyou__content-container">
					<section class="sawerna-thankyou__section sawerna-thankyou__section--overview">
						<h2 class="sawerna-thankyou__section-title"><?php esc_html_e( 'Tellimuse ülevaade', 'sawerna' ); ?></h2>

						<div class="sawerna-thankyou__table" role="table" aria-label="<?php esc_attr_e( 'Tellimuse ülevaade', 'sawerna' ); ?>">
							<div class="sawerna-thankyou__table-head" role="row">
								<div role="columnheader"><?php esc_html_e( 'Toode', 'sawerna' ); ?></div>
								<div role="columnheader"><?php esc_html_e( 'Maksumus', 'sawerna' ); ?></div>
							</div>

							<div class="sawerna-thankyou__table-body">
								<?php
								foreach ( $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) ) as $item_id => $item ) :
									if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
										continue;
									}

									$product           = $item->get_product();
									$is_visible        = $product && $product->is_visible();
									$product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );
									$item_name         = apply_filters( 'woocommerce_order_item_name', $product_permalink ? sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), esc_html( $item->get_name() ) ) : esc_html( $item->get_name() ), $item, $is_visible );
									$item_quantity     = $item->get_quantity();
									$refunded_quantity = $order->get_qty_refunded_for_item( $item_id );
									$quantity_display  = $refunded_quantity ? '<del>' . esc_html( (string) $item_quantity ) . '</del> <ins>' . esc_html( (string) ( $item_quantity - ( $refunded_quantity * -1 ) ) ) . '</ins>' : esc_html( (string) $item_quantity );
									$item_meta         = wc_display_item_meta( $item, array( 'echo' => false ) );
									?>
									<div class="sawerna-thankyou__line" role="row">
										<div class="sawerna-thankyou__product" role="cell">
											<span class="sawerna-thankyou__product-name">
												<?php echo wp_kses_post( $item_name ); ?>
												<span class="sawerna-thankyou__product-quantity">
													<?php
													printf(
														/* translators: %s: Product quantity. */
														esc_html__( '× %s', 'sawerna' ),
														wp_kses_post( $quantity_display )
													);
													?>
												</span>
											</span>

											<?php if ( '' !== $item_meta ) : ?>
												<div class="sawerna-thankyou__product-meta"><?php echo wp_kses_post( $item_meta ); ?></div>
											<?php endif; ?>
										</div>
										<div class="sawerna-thankyou__amount" role="cell"><?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?></div>
									</div>
								<?php endforeach; ?>
							</div>

							<div class="sawerna-thankyou__totals">
								<div class="sawerna-thankyou__total-row sawerna-thankyou__total-row--strong">
									<div><?php esc_html_e( 'Vahesumma:', 'sawerna' ); ?></div>
									<div><?php echo wp_kses_post( $order->get_subtotal_to_display() ); ?></div>
								</div>

								<?php if ( (float) $order->get_discount_total() > 0 ) : ?>
									<div class="sawerna-thankyou__total-row">
										<div><?php esc_html_e( 'Soodustus:', 'sawerna' ); ?></div>
										<div><?php echo wp_kses_post( wc_price( -1 * (float) $order->get_discount_total(), array( 'currency' => $order->get_currency() ) ) ); ?></div>
									</div>
								<?php endif; ?>

								<?php if ( (float) $order->get_shipping_total() > 0 ) : ?>
									<div class="sawerna-thankyou__total-row">
										<div><?php esc_html_e( 'Tarne:', 'sawerna' ); ?></div>
										<div><?php echo wp_kses_post( wc_price( (float) $order->get_shipping_total(), array( 'currency' => $order->get_currency() ) ) ); ?></div>
									</div>
								<?php endif; ?>

								<?php foreach ( $order->get_fees() as $fee ) : ?>
									<div class="sawerna-thankyou__total-row">
										<div><?php echo esc_html( $fee->get_name() ); ?></div>
										<div><?php echo wp_kses_post( wc_price( (float) $fee->get_total(), array( 'currency' => $order->get_currency() ) ) ); ?></div>
									</div>
								<?php endforeach; ?>

								<div class="sawerna-thankyou__total-row">
									<div><?php esc_html_e( 'Tarneaadress:', 'sawerna' ); ?></div>
									<div><?php echo $get_delivery_text( $order ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
								</div>

								<div class="sawerna-thankyou__total-row sawerna-thankyou__total-row--grand">
									<div><?php esc_html_e( 'Maksumus kokku:', 'sawerna' ); ?></div>
									<div><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></div>
								</div>
							</div>
						</div>
					</section>

					<div class="sawerna-thankyou__gateway">
						<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
						<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>
					</div>

					<section class="sawerna-thankyou__section sawerna-thankyou__section--billing">
						<h2 class="sawerna-thankyou__section-title"><?php esc_html_e( 'Arveldusandmed', 'sawerna' ); ?></h2>
						<address class="sawerna-thankyou__billing">
							<?php foreach ( $get_billing_details( $order ) as $line ) : ?>
								<span><?php echo $line; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<?php endforeach; ?>
						</address>
					</section>
				</div>
			</section>
		<?php endif; ?>
	<?php else : ?>
		<section class="sawerna-thankyou__hero">
			<div class="sawerna-thankyou__container">
				<h1 class="sawerna-thankyou__title"><?php esc_html_e( 'Tellimus on vastu võetud', 'sawerna' ); ?></h1>
				<p class="sawerna-thankyou__lead">
					<?php esc_html_e( 'Aitäh. Tellimuse detailid saadetakse sinu e-posti aadressile.', 'sawerna' ); ?>
				</p>
			</div>
		</section>
	<?php endif; ?>
</div>
