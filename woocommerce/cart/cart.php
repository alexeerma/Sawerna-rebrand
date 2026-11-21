<?php
/**
 * Cart page template.
 *
 * This template overrides the default WooCommerce cart layout with the
 * custom Sawerna design while preserving standard cart actions and hooks.
 *
 * @package WooCommerce\Templates
 * @version 11.3.0
 */

defined( 'ABSPATH' ) || exit;

$count_label     = function_exists( 'sawerna_woocommerce_mini_cart_count_label' ) ? sawerna_woocommerce_mini_cart_count_label() : '';
$free_shipping   = function_exists( 'sawerna_woocommerce_mini_cart_free_shipping_data' ) ? sawerna_woocommerce_mini_cart_free_shipping_data() : array();
$recommendations = function_exists( 'sawerna_woocommerce_get_cart_recommendations' ) ? sawerna_woocommerce_get_cart_recommendations( 8 ) : array();
$theme_uri       = get_template_directory_uri();

do_action( 'woocommerce_before_cart' );
?>

<div class="sawerna-shop sawerna-cart-page">
	<div class="sawerna-shop__breadcrumb-row">
		<div class="sawerna-shop__breadcrumb-container">
			<?php woocommerce_breadcrumb(); ?>
		</div>
	</div>

	<div class="sawerna-cart">
		<div class="sawerna-cart__container">
			<header class="sawerna-cart__header">
				<div class="sawerna-cart__heading">
					<h1 class="sawerna-cart__title">
						<?php esc_html_e( 'Ostukorv', 'sawerna' ); ?>
						<?php if ( '' !== $count_label ) : ?>
							<span class="sawerna-cart__title-count"><?php echo esc_html( $count_label ); ?></span>
						<?php endif; ?>
					</h1>
				</div>

				<div class="sawerna-cart__steps">
					<?php sawerna_woocommerce_render_checkout_steps( 'cart' ); ?>
				</div>

				<div class="sawerna-cart__clear">
					<a href="<?php echo esc_url( sawerna_woocommerce_get_empty_cart_url() ); ?>" class="sawerna-cart__clear-link">
						<span class="sawerna-cart__clear-icon" aria-hidden="true">
							<?php echo sawerna_woocommerce_mini_cart_remove_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
						<span><?php esc_html_e( 'Tühjenda ostukorv', 'sawerna' ); ?></span>
					</a>
				</div>
			</header>

			<div class="sawerna-cart__layout">
				<div class="sawerna-cart__main">
					<?php if ( ! empty( $free_shipping['message_text'] ) ) : ?>
						<?php
						$shipping_message = ! empty( $free_shipping['has_reached'] )
							? esc_html__( 'Said tasuta tarne!', 'sawerna' )
							: sprintf(
								/* translators: %s is the amount remaining until free shipping. */
								esc_html__( 'Lisa ostukorvi veel %s väärtuses tooteid ja saad tasuta tarne!', 'sawerna' ),
								wp_strip_all_tags( html_entity_decode( wp_kses_post( wc_price( (float) $free_shipping['remaining'] ) ), ENT_QUOTES, 'UTF-8' ) )
							);
						?>
						<div class="sawerna-cart__shipping-progress" aria-live="polite">
							<p class="sawerna-cart__shipping-text"><?php echo esc_html( $shipping_message ); ?></p>
							<div class="sawerna-cart__shipping-track" aria-hidden="true">
								<span class="sawerna-cart__shipping-bar" style="width: <?php echo esc_attr( (string) $free_shipping['progress'] ); ?>%;"></span>
							</div>
						</div>
					<?php endif; ?>

					<form class="woocommerce-cart-form sawerna-cart__form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
						<?php do_action( 'woocommerce_before_cart_table' ); ?>

						<div class="sawerna-cart__table-wrap">
							<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents sawerna-cart__table" cellspacing="0">
								<thead>
									<tr>
										<th scope="col" class="product-name"><?php esc_html_e( 'Toode', 'sawerna' ); ?></th>
										<th scope="col" class="product-price"><?php esc_html_e( 'Hind', 'sawerna' ); ?></th>
										<th scope="col" class="product-quantity"><?php esc_html_e( 'Kogus', 'sawerna' ); ?></th>
										<th scope="col" class="product-subtotal"><?php esc_html_e( 'Vahesumma', 'sawerna' ); ?></th>
										<th scope="col" class="product-remove"><span class="screen-reader-text"><?php esc_html_e( 'Remove item', 'woocommerce' ); ?></span></th>
									</tr>
								</thead>
								<tbody>
									<?php do_action( 'woocommerce_before_cart_contents' ); ?>

									<?php foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) : ?>
										<?php
										$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
										$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
										$visible    = apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key );

										if ( ! ( $_product instanceof WC_Product ) || ! $_product->exists() || $cart_item['quantity'] <= 0 || ! $visible ) {
											continue;
										}

										$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
										$cart_item_name    = WC()->cart->get_item_product_name( $cart_item, $_product );
										$product_name      = apply_filters( 'woocommerce_cart_item_name', $cart_item_name, $cart_item, $cart_item_key );
										$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'woocommerce_thumbnail' ), $cart_item, $cart_item_key );
										$unit_price_text   = wp_strip_all_tags( html_entity_decode( wp_kses_post( wc_price( (float) wc_get_price_to_display( $_product ) ) ), ENT_QUOTES, 'UTF-8' ) );

										if ( $_product->is_sold_individually() ) {
											$min_quantity = 1;
											$max_quantity = 1;
										} else {
											$min_quantity = 0;
											$max_quantity = $_product->get_max_purchase_quantity();
										}

										$product_quantity = woocommerce_quantity_input(
											array(
												'input_name'   => "cart[{$cart_item_key}][qty]",
												'input_value'  => $cart_item['quantity'],
												'max_value'    => $max_quantity,
												'min_value'    => $min_quantity,
												'product_name' => $product_name,
											),
											$_product,
											false
										);
										?>
										<tr class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
											<td class="product-name" data-title="<?php esc_attr_e( 'Toode', 'sawerna' ); ?>">
												<div class="sawerna-cart__product">
													<div class="sawerna-cart__product-media">
														<?php if ( $product_permalink ) : ?>
															<a href="<?php echo esc_url( $product_permalink ); ?>" class="sawerna-cart__product-image">
																<?php echo wp_kses_post( $thumbnail ); ?>
															</a>
														<?php else : ?>
															<span class="sawerna-cart__product-image">
																<?php echo wp_kses_post( $thumbnail ); ?>
															</span>
														<?php endif; ?>
													</div>

													<div class="sawerna-cart__product-content">
														<?php if ( $product_permalink ) : ?>
															<a href="<?php echo esc_url( $product_permalink ); ?>" class="sawerna-cart__product-title"><?php echo esc_html( $product_name ); ?></a>
														<?php else : ?>
															<span class="sawerna-cart__product-title"><?php echo esc_html( $product_name ); ?></span>
														<?php endif; ?>

														<div class="sawerna-cart__product-meta">
															<?php do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key ); ?>
															<?php echo wc_get_formatted_cart_item_data( $cart_item, false, $cart_item_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

															<?php if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) : ?>
																<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>', $product_id, $cart_item, $cart_item_key ) ); ?>
															<?php endif; ?>
														</div>

														<p class="sawerna-cart__product-unit-price"><?php echo esc_html( sprintf( '%1$sx%2$s', (string) $cart_item['quantity'], $unit_price_text ) ); ?></p>
													</div>
												</div>
											</td>

											<td class="product-price" data-title="<?php esc_attr_e( 'Hind', 'sawerna' ); ?>">
												<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ) ); ?>
											</td>

											<td class="product-quantity" data-title="<?php esc_attr_e( 'Kogus', 'sawerna' ); ?>">
												<div class="sawerna-cart__quantity" data-cart-quantity="1">
													<button type="button" class="sawerna-cart__quantity-button sawerna-cart__quantity-button--decrease" data-cart-quantity-decrease="1" aria-label="<?php esc_attr_e( 'Vähenda kogust', 'sawerna' ); ?>">−</button>
													<?php echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
													<button type="button" class="sawerna-cart__quantity-button sawerna-cart__quantity-button--increase" data-cart-quantity-increase="1" aria-label="<?php esc_attr_e( 'Suurenda kogust', 'sawerna' ); ?>">+</button>
												</div>
											</td>

											<td class="product-subtotal" data-title="<?php esc_attr_e( 'Vahesumma', 'sawerna' ); ?>">
												<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ) ); ?>
											</td>

											<td class="product-remove" data-title="<?php esc_attr_e( 'Eemalda', 'sawerna' ); ?>">
												<?php
												echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
													'woocommerce_cart_item_remove_link',
													sprintf(
														'<a role="button" href="%s" class="sawerna-cart__remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">%s</a>',
														esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
														esc_attr( sprintf( /* translators: %s: product name. */ __( 'Eemalda toode "%s" ostukorvist', 'sawerna' ), wp_strip_all_tags( $product_name ) ) ),
														esc_attr( (string) $product_id ),
														esc_attr( $_product->get_sku() ),
														sawerna_woocommerce_mini_cart_remove_icon()
													),
													$cart_item_key
												);
												?>
											</td>
										</tr>
									<?php endforeach; ?>

									<?php do_action( 'woocommerce_cart_contents' ); ?>
									<?php do_action( 'woocommerce_after_cart_contents' ); ?>
								</tbody>
							</table>
						</div>

						<div class="sawerna-cart__footer">
							<?php if ( wc_coupons_enabled() ) : ?>
								<div class="sawerna-cart__coupon coupon">
									<label for="coupon_code" class="screen-reader-text"><?php esc_html_e( 'Coupon:', 'woocommerce' ); ?></label>
									<input type="text" name="coupon_code" class="input-text sawerna-cart__coupon-input" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Sooduskupongi kood', 'sawerna' ); ?>">
									<button type="submit" class="button sawerna-cart__coupon-button" name="apply_coupon" value="<?php esc_attr_e( 'Lisa', 'sawerna' ); ?>"><?php esc_html_e( 'Lisa', 'sawerna' ); ?></button>
									<?php do_action( 'woocommerce_cart_coupon' ); ?>
								</div>
							<?php endif; ?>

							<button type="submit" class="button screen-reader-text" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>"><?php esc_html_e( 'Update cart', 'woocommerce' ); ?></button>

							<?php do_action( 'woocommerce_cart_actions' ); ?>
							<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
						</div>

						<?php do_action( 'woocommerce_after_cart_table' ); ?>
					</form>
				</div>

				<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>

				<aside class="sawerna-cart__summary" aria-label="<?php esc_attr_e( 'Tellimuse kokkuvõte', 'sawerna' ); ?>">
					<?php woocommerce_cart_totals(); ?>
				</aside>
			</div>

			<?php if ( ! empty( $recommendations ) ) : ?>
				<section class="sawerna-cart__recommendations<?php echo count( $recommendations ) > 4 ? ' is-slider-enabled' : ''; ?>" data-cart-recommendations="<?php echo count( $recommendations ) > 4 ? '1' : '0'; ?>">
					<div class="sawerna-cart__recommendations-header">
						<h2 class="sawerna-cart__recommendations-title"><?php esc_html_e( 'Sulle võib ka meeldida', 'sawerna' ); ?></h2>

						<?php if ( count( $recommendations ) > 4 ) : ?>
							<div class="sawerna-cart__recommendations-nav">
								<button type="button" class="sawerna-cart__recommendations-arrow sawerna-cart__recommendations-arrow--prev" data-cart-related-nav="prev" aria-label="<?php esc_attr_e( 'Eelmised tooted', 'sawerna' ); ?>">
									<img src="<?php echo esc_url( $theme_uri . '/assets/SVG/Asset 27.svg' ); ?>" alt="">
								</button>
								<button type="button" class="sawerna-cart__recommendations-arrow sawerna-cart__recommendations-arrow--next" data-cart-related-nav="next" aria-label="<?php esc_attr_e( 'Järgmised tooted', 'sawerna' ); ?>">
									<img src="<?php echo esc_url( $theme_uri . '/assets/SVG/Asset 26.svg' ); ?>" alt="">
								</button>
							</div>
						<?php endif; ?>
					</div>

					<div class="sawerna-cart__recommendations-track-wrap">
						<div class="sawerna-cart__recommendations-track">
							<?php foreach ( $recommendations as $recommended_product ) : ?>
								<div class="sawerna-cart__recommendations-item">
									<?php sawerna_render_product_card( $recommended_product, 'archive' ); ?>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</section>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
