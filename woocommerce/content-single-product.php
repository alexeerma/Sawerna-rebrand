<?php
/**
 * Custom single product content template.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package Sawerna
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product instanceof WC_Product ) {
	return;
}

$gallery_image_ids       = sawerna_get_single_product_gallery_image_ids( $product );
$active_image_id         = ! empty( $gallery_image_ids ) ? $gallery_image_ids[0] : 0;
$labels                  = sawerna_get_product_labels( $product );
$wishlist_html           = sawerna_get_wishlist_button_html( $product->get_id(), 'card' );
$sale_label              = sawerna_get_sale_label_text( $product );
$payment_options         = sawerna_get_single_product_payment_options( $product );
$summary_badges          = sawerna_get_single_product_summary_badges( $product );
$location_badge_texts    = array();
$stock_badge_text        = '';
$attribute_selector_rows = sawerna_get_single_product_attribute_selector_rows( $product );
$fabric_banner           = function_exists( 'sawerna_get_fabric_banner_data' ) ? sawerna_get_fabric_banner_data( $product->get_id() ) : null;
$fabric_card_html        = '';
$fabric_card_placed      = false;
if ( $fabric_banner ) {
	ob_start();
	?>
	<div class="sawerna-single-product__fabric-card-wrap" data-fabric-card="1">
		<div class="sawerna-single-product__fabric-card">
			<div class="sawerna-single-product__fabric-card-media">
				<?php
				if ( $fabric_banner['image_id'] && wp_attachment_is_image( $fabric_banner['image_id'] ) ) {
					echo wp_get_attachment_image(
						$fabric_banner['image_id'],
						'medium_large',
						false,
						array(
							'class'    => 'sawerna-single-product__fabric-card-image',
							'alt'      => '',
							'loading'  => 'lazy',
							'decoding' => 'async',
							'sizes'    => '(max-width: 767px) 40vw, 240px',
						)
					);
				} else {
					$fabric_default_image = sawerna_fabric_banner_default_image();
					?>
					<img
						class="sawerna-single-product__fabric-card-image"
						src="<?php echo esc_url( $fabric_default_image['src'] ); ?>"
						srcset="<?php echo esc_attr( $fabric_default_image['srcset'] ); ?>"
						sizes="(max-width: 767px) 40vw, 240px"
						width="<?php echo esc_attr( (string) $fabric_default_image['width'] ); ?>"
						height="<?php echo esc_attr( (string) $fabric_default_image['height'] ); ?>"
						alt=""
						loading="lazy"
						decoding="async"
					>
					<?php
				}
				?>
			</div>
			<div class="sawerna-single-product__fabric-card-body">
				<?php if ( '' !== $fabric_banner['title'] ) : ?>
					<p class="sawerna-single-product__fabric-card-title"><?php echo esc_html( $fabric_banner['title'] ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== $fabric_banner['text'] ) : ?>
					<p class="sawerna-single-product__fabric-card-text"><?php echo esc_html( $fabric_banner['text'] ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== $fabric_banner['link_url'] ) : ?>
					<a class="sawerna-single-product__fabric-card-link" href="<?php echo esc_url( $fabric_banner['link_url'] ); ?>">
						<span><?php echo esc_html( $fabric_banner['link_label'] ); ?></span>
						<svg class="sawerna-single-product__fabric-card-arrow" viewBox="0 0 20 20" width="18" height="18" aria-hidden="true" focusable="false"><path d="M3 10h13M11 4.5 16.5 10 11 15.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
	$fabric_card_html = (string) ob_get_clean();
}
$product_tabs            = sawerna_get_single_product_tabs( $product );
$related_items           = sawerna_get_single_product_related_items( $product );
$brand_label             = sawerna_get_single_product_brand_label( $product );
$pdf_url                 = function_exists( 'sawerna_get_single_product_pdf_url' ) ? sawerna_get_single_product_pdf_url( $product ) : '';
$category_list           = wc_get_product_category_list( $product->get_id(), ', ' );
$theme_uri               = get_template_directory_uri();
$display_sku             = function_exists( 'sawerna_get_single_product_display_sku' ) ? sawerna_get_single_product_display_sku( $product ) : trim( (string) $product->get_sku() );
$main_image_sizes        = '(max-width: 767px) calc(100vw - 20px), 600px';

foreach ( $summary_badges as $summary_badge ) {
	if ( empty( $summary_badge['icon'] ) || empty( $summary_badge['text'] ) ) {
		continue;
	}

	if ( 'location' === $summary_badge['icon'] ) {
		$location_badge_texts[] = (string) $summary_badge['text'];
	} elseif ( 'delivery' === $summary_badge['icon'] ) {
		$stock_badge_text = (string) $summary_badge['text'];
	}
}

$location_badge_texts = array_values( array_unique( array_filter( array_map( 'trim', $location_badge_texts ) ) ) );
$stock_badge_text     = trim( $stock_badge_text );
?>

<?php do_action( 'woocommerce_before_single_product' ); ?>

<?php if ( post_password_required() ) : ?>
	<?php echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php return; ?>
<?php endif; ?>

<div class="sawerna-shop sawernau-single-product-shell">
	<div class="sawerna-shop__breadcrumb-row">
		<div class="sawerna-shop__breadcrumb-container">
			<?php woocommerce_breadcrumb(); ?>
		</div>
	</div>

	<div class="sawerna-single-product__container">
		<article id="product-<?php the_ID(); ?>" <?php wc_product_class( 'sawerna-single-product', $product ); ?>>
			<div class="sawerna-single-product__hero">
				<div class="sawerna-single-product__gallery" data-single-product-gallery="1">
					<div class="sawerna-single-product__main-media">
						<?php if ( ! empty( $labels ) ) : ?>
							<div class="sawerna-product-card__labels sawerna-single-product__labels">
								<?php foreach ( $labels as $label ) : ?>
									<span class="sawerna-product-card__label sawerna-product-card__label--<?php echo esc_attr( $label['type'] ); ?>"<?php echo ! empty( $label['color'] ) ? ' style="background-color: ' . esc_attr( $label['color'] ) . ';"' : ''; ?>>
										<?php echo esc_html( $label['text'] ); ?>
									</span>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

						<?php if ( $wishlist_html ) : ?>
							<div class="sawerna-single-product__wishlist">
								<?php echo $wishlist_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
						<?php endif; ?>

						<div class="sawerna-single-product__main-image" data-gallery-main="1">
							<?php
							if ( $active_image_id ) {
								echo wp_get_attachment_image(
									$active_image_id,
									'full',
									false,
									array(
										'class'    => 'sawerna-single-product__main-image-tag',
										'loading'  => 'eager',
										'decoding' => 'async',
										'sizes'    => $main_image_sizes,
									)
								); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							} else {
								echo wp_kses_post( wc_placeholder_img( 'full' ) );
							}
							?>
						</div>

						<?php if ( count( $gallery_image_ids ) > 1 ) : ?>
							<button type="button" class="sawerna-single-product__main-arrow sawerna-single-product__main-arrow--prev" data-gallery-main-nav="prev" aria-label="<?php esc_attr_e( 'Eelmine pilt', 'sawerna' ); ?>">
								<img src="<?php echo esc_url( $theme_uri . '/assets/SVG/Asset 27.svg' ); ?>" alt="">
							</button>
							<button type="button" class="sawerna-single-product__main-arrow sawerna-single-product__main-arrow--next" data-gallery-main-nav="next" aria-label="<?php esc_attr_e( 'Jargmine pilt', 'sawerna' ); ?>">
								<img src="<?php echo esc_url( $theme_uri . '/assets/SVG/Asset 27.svg' ); ?>" alt="">
							</button>
							<div class="sawerna-single-product__gallery-count" data-gallery-count="1" aria-live="polite">
								<span class="sawerna-single-product__gallery-progress" aria-hidden="true">
									<span class="sawerna-single-product__gallery-progress-bar" data-gallery-progress="1"></span>
								</span>
								<span class="sawerna-single-product__gallery-count-text" data-gallery-count-text="1"><?php echo esc_html( '1/' . count( $gallery_image_ids ) ); ?></span>
							</div>
						<?php endif; ?>
					</div>

					<?php if ( ! empty( $gallery_image_ids ) ) : ?>
						<div class="sawerna-single-product__thumbs" data-gallery-thumbs="1">
							<?php foreach ( $gallery_image_ids as $index => $image_id ) : ?>
								<?php
								$thumb_html      = wp_get_attachment_image(
									$image_id,
									'woocommerce_thumbnail',
									false,
									array(
										'loading'  => 'lazy',
										'decoding' => 'async',
									)
								);
									$full_src    = wp_get_attachment_image_url( $image_id, 'full' );
									$full_srcset = wp_get_attachment_image_srcset( $image_id, 'full' );
									$full_sizes  = $main_image_sizes;
									$alt         = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
								?>
									<button
									type="button"
									class="sawerna-single-product__thumb<?php echo 0 === $index ? ' is-active' : ''; ?>"
									data-gallery-thumb="1"
										data-image-id="<?php echo esc_attr( (string) $image_id ); ?>"
										data-full-src="<?php echo esc_url( (string) $full_src ); ?>"
										data-full-srcset="<?php echo esc_attr( (string) $full_srcset ); ?>"
										data-full-sizes="<?php echo esc_attr( (string) $full_sizes ); ?>"
										data-alt="<?php echo esc_attr( (string) $alt ); ?>"
										aria-pressed="<?php echo 0 === $index ? 'true' : 'false'; ?>"
									>
									<?php echo $thumb_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</button>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

						<div class="sawerna-single-product__lightbox" data-gallery-lightbox="1" hidden>
							<button type="button" class="sawerna-single-product__lightbox-close" data-gallery-close="1" aria-label="<?php esc_attr_e( 'Sulge galerii', 'sawerna' ); ?>">×</button>
							<button type="button" class="sawerna-single-product__lightbox-arrow sawerna-single-product__lightbox-arrow--prev" data-gallery-nav="prev" aria-label="<?php esc_attr_e( 'Eelmine pilt', 'sawerna' ); ?>">
								<img src="<?php echo esc_url( $theme_uri . '/assets/SVG/Asset 27.svg' ); ?>" alt="">
							</button>
							<div class="sawerna-single-product__lightbox-inner">
								<div class="sawerna-single-product__lightbox-image-wrap">
									<?php if ( $active_image_id ) : ?>
										<?php
										echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											$active_image_id,
											'full',
											false,
											array(
												'class' => 'sawerna-single-product__lightbox-image',
												'data-gallery-lightbox-image' => '1',
											)
										);
										?>
									<?php endif; ?>
								</div>
								<?php if ( ! empty( $gallery_image_ids ) ) : ?>
									<div class="sawerna-single-product__lightbox-thumbs">
										<?php foreach ( $gallery_image_ids as $index => $image_id ) : ?>
											<?php
											$lightbox_thumb      = wp_get_attachment_image(
												$image_id,
												'thumbnail',
												false,
												array(
													'loading'  => 'lazy',
													'decoding' => 'async',
												)
											);
												$lightbox_full   = wp_get_attachment_image_url( $image_id, 'full' );
												$lightbox_srcset = wp_get_attachment_image_srcset( $image_id, 'full' );
												$lightbox_sizes  = '100vw';
												$lightbox_alt    = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
											?>
												<button
												type="button"
												class="sawerna-single-product__lightbox-thumb<?php echo 0 === $index ? ' is-active' : ''; ?>"
													data-gallery-lightbox-thumb="1"
													data-lightbox-index="<?php echo esc_attr( (string) $index ); ?>"
													data-full-src="<?php echo esc_url( (string) $lightbox_full ); ?>"
													data-full-srcset="<?php echo esc_attr( (string) $lightbox_srcset ); ?>"
													data-full-sizes="<?php echo esc_attr( (string) $lightbox_sizes ); ?>"
													data-alt="<?php echo esc_attr( (string) $lightbox_alt ); ?>"
													aria-pressed="<?php echo 0 === $index ? 'true' : 'false'; ?>"
												>
												<?php echo $lightbox_thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
											</button>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							</div>
							<button type="button" class="sawerna-single-product__lightbox-arrow sawerna-single-product__lightbox-arrow--next" data-gallery-nav="next" aria-label="<?php esc_attr_e( 'Järgmine pilt', 'sawerna' ); ?>">
								<img src="<?php echo esc_url( $theme_uri . '/assets/SVG/Asset 26.svg' ); ?>" alt="">
							</button>
						</div>
				</div>

				<div class="sawerna-single-product__summary">
					<?php if ( '' !== $display_sku || $product->is_type( 'variable' ) ) : ?>
						<div class="sawerna-single-product__sku" data-single-product-sku="1"<?php echo '' === $display_sku ? ' hidden="hidden"' : ''; ?>>
							<?php
							printf(
								/* translators: %s is the SKU. */
								esc_html__( 'Tootekood: %s', 'sawerna' ),
								'<span data-single-product-sku-text="1">' . esc_html( $display_sku ) . '</span>'
							);
							?>
						</div>
					<?php endif; ?>

					<h1 class="sawerna-single-product__title"><?php echo esc_html( get_the_title() ); ?></h1>

					<div class="sawerna-single-product__price-grid">
						<div class="sawerna-single-product__price-col sawerna-single-product__price-col--price">
							<div class="sawerna-single-product__price-main">
								<?php if ( $product->get_price_html() ) : ?>
									<div class="sawerna-single-product__price" data-single-product-price="1">
										<?php echo wp_kses_post( $product->get_price_html() ); ?>
									</div>
								<?php endif; ?>

								<?php if ( $sale_label ) : ?>
									<span class="sawerna-single-product__sale-badge"><?php echo esc_html( $sale_label ); ?></span>
								<?php endif; ?>
							</div>
						</div>

						<div class="sawerna-single-product__price-col sawerna-single-product__price-col--payments">
							<?php foreach ( $payment_options as $payment_option ) : ?>
								<div class="sawerna-single-product__payment-option">
									<?php if ( ! empty( $payment_option['title'] ) ) : ?>
										<div class="sawerna-single-product__payment-title" data-payment-summary-title="1"><?php echo esc_html( $payment_option['title'] ); ?></div>
									<?php endif; ?>
									<?php if ( ! empty( $payment_option['detail'] ) ) : ?>
										<div class="sawerna-single-product__payment-detail">
											<span data-payment-summary-detail="1"><?php echo esc_html( $payment_option['detail'] ); ?></span>
											<button
												type="button"
												class="sawerna-single-product__payment-info-button"
												data-payment-drawer-open="1"
												aria-haspopup="dialog"
												aria-expanded="false"
												aria-controls="sawerna-payment-drawer"
												aria-label="<?php esc_attr_e( 'Vaata järelmaksu võimalusi', 'sawerna' ); ?>"
											>
												<span class="sawerna-single-product__info-icon" aria-hidden="true">
													<?php echo sawerna_get_svg_inline( 'more-info-icon.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
												</span>
											</button>
										</div>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					</div>

					<?php if ( ! empty( $location_badge_texts ) || '' !== $stock_badge_text ) : ?>
						<div class="sawerna-single-product__badges" data-single-product-badges="1">
							<div
								class="sawerna-single-product__location-badges"
								data-single-product-location-badges="1"
								<?php echo empty( $location_badge_texts ) ? ' hidden="hidden"' : ''; ?>
							>
								<?php foreach ( $location_badge_texts as $location_badge_text ) : ?>
									<div class="sawerna-single-product__badge" data-single-product-location-badge="1">
										<span class="sawerna-single-product__badge-icon" aria-hidden="true">
											<?php echo sawerna_get_single_product_summary_badge_icon_svg( 'location' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										</span>
										<span class="sawerna-single-product__badge-text" data-single-product-location-badge-text="1"><?php echo esc_html( $location_badge_text ); ?></span>
									</div>
								<?php endforeach; ?>
								<template data-single-product-location-badge-template="1">
									<div class="sawerna-single-product__badge" data-single-product-location-badge="1">
										<span class="sawerna-single-product__badge-icon" aria-hidden="true">
											<?php echo sawerna_get_single_product_summary_badge_icon_svg( 'location' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										</span>
										<span class="sawerna-single-product__badge-text" data-single-product-location-badge-text="1"></span>
									</div>
								</template>
							</div>
							<div
								class="sawerna-single-product__badge sawerna-single-product__badge--stock"
								data-single-product-stock-badge="1"
								<?php echo '' === $stock_badge_text ? ' hidden="hidden"' : ''; ?>
							>
								<span class="sawerna-single-product__badge-icon" aria-hidden="true">
									<?php echo sawerna_get_single_product_summary_badge_icon_svg( 'delivery' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</span>
								<span class="sawerna-single-product__badge-text" data-single-product-stock-badge-text="1"><?php echo esc_html( $stock_badge_text ); ?></span>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $attribute_selector_rows ) ) : ?>
						<div
							class="sawerna-single-product__selectors"
							data-product-attribute-selectors="1"
						>
							<div class="sawerna-single-product__features">
								<?php foreach ( $attribute_selector_rows as $row ) : ?>
									<?php
									$has_selection  = ! empty( $row['selected']['value'] );
									$row_has_fabric = $fabric_card_html && ! $fabric_card_placed && 'fabric' === ( $row['icon'] ?? '' );
									?>
									<button
										type="button"
										class="sawerna-single-product__feature-row sawerna-single-product__feature-row--selector<?php echo $has_selection ? '' : ' is-empty'; ?><?php echo $row_has_fabric ? ' has-fabric-card' : ''; ?>"
										data-selector-row="1"
										data-attribute-key="<?php echo esc_attr( $row['attribute_key'] ); ?>"
										data-attribute-name="<?php echo esc_attr( $row['attribute_name'] ); ?>"
										aria-haspopup="dialog"
										aria-expanded="false"
									>
										<span class="sawerna-single-product__feature-header">
											<span class="sawerna-single-product__feature-title-wrap">
												<span class="sawerna-single-product__feature-title"><?php echo esc_html( $row['title'] ); ?></span>
												<span class="sawerna-single-product__info-icon" aria-hidden="true">
													<?php echo sawerna_get_svg_inline( 'more-info-icon.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
												</span>
											</span>
												<span class="sawerna-single-product__feature-arrow" aria-hidden="true">
													<img src="<?php echo esc_url( $theme_uri . '/assets/SVG/Asset 8.svg' ); ?>" alt="">
												</span>
										</span>
										<span class="sawerna-single-product__feature-body">
											<span class="sawerna-single-product__feature-icon sawerna-single-product__selector-preview" data-selector-preview="1" aria-hidden="true">
												<?php
												if ( $has_selection ) {
													echo sawerna_get_single_product_attribute_preview_html( $row ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
												}
												?>
											</span>
											<span class="sawerna-single-product__feature-content">
												<span class="sawerna-single-product__feature-value" data-selector-value="1"><?php echo $has_selection ? esc_html( $row['selected']['label'] ) : ''; ?></span>
												<span class="sawerna-single-product__feature-detail" data-selector-detail="1"><?php echo $has_selection ? esc_html( $row['selected']['detail'] ) : ''; ?></span>
											</span>
										</span>
									</button>
									<?php
									if ( $row_has_fabric ) {
										echo $fabric_card_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above from escaped parts.
										$fabric_card_placed = true;
									}
									?>
								<?php endforeach; ?>
								<?php
								if ( $fabric_card_html && ! $fabric_card_placed ) {
									// No native fabric row: single-product.js moves this after the THWEPO "kangas" row and unhides it.
									echo str_replace( 'data-fabric-card="1"', 'data-fabric-card="1" data-fabric-card-pending="1" hidden', $fabric_card_html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built above from escaped parts.
								}
								?>
							</div>

							<div class="sawerna-single-product__selector-drawer" data-selector-drawer="1" hidden>
								<div class="sawerna-single-product__selector-backdrop" data-selector-close="1"></div>
								<div class="sawerna-single-product__selector-panel" role="dialog" aria-modal="true" aria-labelledby="sawerna-selector-drawer-title">
									<div class="sawerna-single-product__selector-panel-head">
										<h2 class="sawerna-single-product__selector-panel-title" id="sawerna-selector-drawer-title" data-selector-drawer-title="1"></h2>
										<button type="button" class="sawerna-single-product__selector-panel-close" data-selector-close="1" aria-label="<?php esc_attr_e( 'Sulge valikud', 'sawerna' ); ?>">×</button>
									</div>

									<div class="sawerna-single-product__selector-panels">
										<?php foreach ( $attribute_selector_rows as $row ) : ?>
											<?php $has_selection = ! empty( $row['selected']['value'] ); ?>
											<section
												class="sawerna-single-product__selector-group"
												data-selector-panel-for="<?php echo esc_attr( $row['attribute_key'] ); ?>"
												data-attribute-name="<?php echo esc_attr( $row['attribute_name'] ); ?>"
												data-attribute-label="<?php echo esc_attr( $row['label'] ); ?>"
												hidden
											>
												<div class="sawerna-single-product__selector-current"<?php echo $has_selection ? '' : ' hidden'; ?>>
														<div class="sawerna-single-product__selector-current-preview" data-selector-current-preview="1">
															<?php
															if ( $has_selection ) {
																echo sawerna_get_single_product_attribute_preview_html( $row ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
															}
															?>
														</div>
														<div class="sawerna-single-product__selector-current-copy">
															<div class="sawerna-single-product__selector-current-label"><?php esc_html_e( 'Valitud:', 'sawerna' ); ?> <strong data-selector-current-value="1"><?php echo $has_selection ? esc_html( $row['selected']['label'] ) : ''; ?></strong></div>
															<div class="sawerna-single-product__selector-current-detail" data-selector-current-detail="1"><?php echo $has_selection ? esc_html( $row['selected']['detail'] ) : ''; ?></div>
														</div>
												</div>

												<div class="sawerna-single-product__selector-options sawerna-single-product__selector-options--<?php echo esc_attr( $row['swatch_type'] ); ?>">
													<?php foreach ( $row['options'] as $option ) : ?>
														<?php $is_selected = $option['value'] === $row['selected']['value']; ?>
														<button
															type="button"
															class="sawerna-single-product__selector-option"
															data-selector-option="1"
															data-attribute-name="<?php echo esc_attr( $row['attribute_name'] ); ?>"
															data-attribute-key="<?php echo esc_attr( $row['attribute_key'] ); ?>"
															data-option-value="<?php echo esc_attr( $option['value'] ); ?>"
															data-option-label="<?php echo esc_attr( $option['label'] ); ?>"
															data-option-detail="<?php echo esc_attr( $option['detail'] ); ?>"
															data-option-color="<?php echo esc_attr( $option['color'] ); ?>"
															data-option-image="<?php echo esc_url( $option['image_url'] ); ?>"
															data-swatch-type="<?php echo esc_attr( $option['swatch_type'] ); ?>"
															aria-pressed="<?php echo $is_selected ? 'true' : 'false'; ?>"
														>
															<span class="sawerna-single-product__selector-option-media">
																<?php if ( 'image' === $option['swatch_type'] && ! empty( $option['image_url'] ) ) : ?>
																	<img src="<?php echo esc_url( $option['image_url'] ); ?>" alt="<?php echo esc_attr( $option['label'] ); ?>">
																<?php elseif ( 'color' === $option['swatch_type'] && ! empty( $option['color'] ) ) : ?>
																	<span class="sawerna-single-product__selector-option-color" style="background-color: <?php echo esc_attr( $option['color'] ); ?>;"></span>
																<?php else : ?>
																	<span class="sawerna-single-product__selector-option-icon" aria-hidden="true">
																		<?php echo sawerna_get_single_product_feature_icon_svg( $row['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
																	</span>
																<?php endif; ?>
															</span>
															<span class="sawerna-single-product__selector-option-copy">
																<span class="sawerna-single-product__selector-option-label"><?php echo esc_html( $option['label'] ); ?></span>
																<?php if ( ! empty( $option['detail'] ) ) : ?>
																	<span class="sawerna-single-product__selector-option-detail"><?php echo esc_html( $option['detail'] ); ?></span>
																<?php endif; ?>
															</span>
														</button>
													<?php endforeach; ?>
												</div>
											</section>
										<?php endforeach; ?>
									</div>

									<div class="sawerna-single-product__selector-panel-foot">
										<button type="button" class="sawerna-single-product__selector-apply" data-selector-apply="1">
											<?php esc_html_e( 'Rakenda', 'sawerna' ); ?>
										</button>
									</div>
								</div>
							</div>
						</div>
					<?php endif; ?>

					<?php
						$jarelmaks_providers      = function_exists( 'sawerna_get_jarelmaks_providers' ) ? sawerna_get_jarelmaks_providers() : array();
						$jarelmaks_providers_json = wp_json_encode( $jarelmaks_providers );
						$jarelmaks_disclaimer     = function_exists( 'sawerna_get_page_option' ) ? sawerna_get_page_option( 'jarelmaks_disclaimer' ) : '';
					if ( '' === $jarelmaks_disclaimer ) {
						$jarelmaks_disclaimer = __( 'Arvutus on ligikaudne. Täpne pakkumine sõltub laenuandja tingimustest ja krediidiotsusest.', 'sawerna' );
					}
					?>
					<div class="sawerna-single-product__payment-drawer" data-payment-drawer="1" data-jarelmaks-providers="<?php echo esc_attr( $jarelmaks_providers_json ); ?>" hidden>
						<div class="sawerna-single-product__payment-backdrop" data-payment-drawer-close="1"></div>
						<div class="sawerna-single-product__payment-panel" role="dialog" aria-modal="true" aria-labelledby="sawerna-payment-drawer-title" id="sawerna-payment-drawer">
							<div class="sawerna-single-product__payment-panel-head">
								<h2 class="sawerna-single-product__payment-panel-title" id="sawerna-payment-drawer-title"><?php esc_html_e( 'Vali järelmaks', 'sawerna' ); ?></h2>
								<button type="button" class="sawerna-single-product__payment-panel-close" data-payment-drawer-close="1" aria-label="<?php esc_attr_e( 'Sulge järelmaksu valikud', 'sawerna' ); ?>">×</button>
							</div>

							<div class="sawerna-single-product__payment-panel-body">
								<form class="sawerna-payment-calc" data-payment-calc="1" novalidate>

									<div class="sawerna-payment-calc__row">
										<label class="sawerna-payment-calc__label" for="sawerna-payment-provider"><?php esc_html_e( 'Pakkuja', 'sawerna' ); ?></label>
										<select class="sawerna-payment-calc__select" id="sawerna-payment-provider" data-payment-calc-provider="1">
											<!-- populated by JS from sawernaProduct.jarelmaksProviders -->
										</select>
									</div>

									<div class="sawerna-payment-calc__row">
										<label class="sawerna-payment-calc__label" for="sawerna-payment-period"><?php esc_html_e( 'Periood', 'sawerna' ); ?></label>
										<select class="sawerna-payment-calc__select" id="sawerna-payment-period" data-payment-calc-period="1">
											<?php foreach ( array( 3, 6, 12, 18, 24, 36, 48, 60 ) as $months ) : ?>
												<option value="<?php echo esc_attr( (string) $months ); ?>"<?php echo 12 === $months ? ' selected' : ''; ?>>
													<?php echo esc_html( sprintf( /* translators: %d: number of months. */ _n( '%d kuu', '%d kuud', $months, 'sawerna' ), $months ) ); ?>
												</option>
											<?php endforeach; ?>
										</select>
									</div>

									<div class="sawerna-payment-calc__row">
										<label class="sawerna-payment-calc__label" for="sawerna-payment-downpayment"><?php esc_html_e( 'Sissemakse (€)', 'sawerna' ); ?></label>
										<input
											class="sawerna-payment-calc__input"
											type="number"
											id="sawerna-payment-downpayment"
											data-payment-calc-downpayment="1"
											min="0"
											step="1"
											value="0"
										>
									</div>

									<div class="sawerna-payment-calc__result">
										<span class="sawerna-payment-calc__result-label"><?php esc_html_e( 'Ligikaudne kuumakse', 'sawerna' ); ?></span>
										<span class="sawerna-payment-calc__result-amount" data-payment-calc-amount="1">...</span>
									</div>

									<p class="sawerna-payment-calc__disclaimer">
										<?php echo esc_html( $jarelmaks_disclaimer ); ?>
									</p>

								</form>
							</div>
						</div>
					</div>

					<div class="sawerna-single-product__purchase">
						<?php woocommerce_template_single_add_to_cart(); ?>
					</div>

					<div class="sawerna-single-product__pdf" data-single-product-pdf="1" <?php echo '' === $pdf_url ? ' hidden' : ''; ?>>
						<a
							href="<?php echo esc_url( $pdf_url ); ?>"
							class="sawerna-single-product__pdf-link"
							data-single-product-pdf-link="1"
							target="_blank"
							rel="noopener noreferrer"
						>
							<svg class="sawerna-single-product__pdf-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
								<path fill="currentColor" d="M6 2c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6H6zm7 1.5L18.5 9H13V3.5zM8 13h1.5c.83 0 1.5.67 1.5 1.5S10.33 16 9.5 16H9v1.5H8V13zm1 2h.5c.28 0 .5-.22.5-.5s-.22-.5-.5-.5H9v1zm3-2h1.6c.77 0 1.4.63 1.4 1.4v1.2c0 .77-.63 1.4-1.4 1.4H12v-4zm1 3h.6c.22 0 .4-.18.4-.4v-1.2c0-.22-.18-.4-.4-.4H13v2zm3-3h2.5v.75H16.5v.75H16.5v.75H16v-2.25z"/>
							</svg>
							<?php esc_html_e( 'Tootejoonis', 'sawerna' ); ?>
						</a>
					</div>

					<div class="sawerna-single-product__meta">
						<?php if ( $category_list ) : ?>
							<div class="sawerna-single-product__meta-row">
								<span class="sawerna-single-product__meta-label"><?php esc_html_e( 'Kategooriad:', 'sawerna' ); ?></span>
								<span class="sawerna-single-product__meta-value"><?php echo wp_kses_post( $category_list ); ?></span>
							</div>
						<?php endif; ?>

						<?php if ( $brand_label ) : ?>
							<div class="sawerna-single-product__meta-row">
								<span class="sawerna-single-product__meta-label"><?php esc_html_e( 'Bränd:', 'sawerna' ); ?></span>
								<span class="sawerna-single-product__meta-value"><?php echo wp_kses_post( $brand_label ); ?></span>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<?php if ( ! empty( $product_tabs ) ) : ?>
				<section class="sawerna-single-product__tabs sawerna-single-product__tabs--desktop" data-product-tabs="1">
					<div class="sawerna-single-product__tab-list" role="tablist" aria-label="<?php esc_attr_e( 'Toote sisu', 'sawerna' ); ?>">
						<?php foreach ( $product_tabs as $index => $product_tab ) : ?>
							<button
								type="button"
								class="sawerna-single-product__tab-button<?php echo 0 === $index ? ' is-active' : ''; ?>"
								id="tab-button-<?php echo esc_attr( $product_tab['key'] ); ?>"
								data-tab-button="<?php echo esc_attr( $product_tab['key'] ); ?>"
								role="tab"
								aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>"
								aria-controls="tab-panel-<?php echo esc_attr( $product_tab['key'] ); ?>"
							>
								<?php echo esc_html( $product_tab['title'] ); ?>
							</button>
						<?php endforeach; ?>
					</div>

					<div class="sawerna-single-product__tab-panels">
						<?php foreach ( $product_tabs as $index => $product_tab ) : ?>
							<div
								class="sawerna-single-product__tab-panel<?php echo 0 === $index ? ' is-active' : ''; ?>"
								id="tab-panel-<?php echo esc_attr( $product_tab['key'] ); ?>"
								data-tab-panel="<?php echo esc_attr( $product_tab['key'] ); ?>"
								role="tabpanel"
								aria-labelledby="tab-button-<?php echo esc_attr( $product_tab['key'] ); ?>"
								<?php echo 0 === $index ? '' : 'hidden'; ?>
							>
								<?php
								if ( 'reviews' === $product_tab['key'] ) {
									// Reviews HTML is generated by WooCommerce templates (forms, nonces, scripts)
									// which wp_kses_post() would strip; it is trusted output, so echo it raw.
									echo $product_tab['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								} else {
									echo wp_kses_post( $product_tab['content'] );
								}
								?>
							</div>
						<?php endforeach; ?>
					</div>
				</section>

				<section class="sawerna-single-product__tabs sawerna-single-product__tabs--mobile" data-product-accordion="1">
					<div class="sawerna-single-product__accordion-list">
						<?php foreach ( $product_tabs as $index => $product_tab ) : ?>
							<div class="sawerna-single-product__accordion-item">
								<button
									type="button"
									class="sawerna-single-product__accordion-button"
									data-accordion-button="<?php echo esc_attr( $product_tab['key'] ); ?>"
									aria-expanded="false"
									aria-controls="accordion-panel-<?php echo esc_attr( $product_tab['key'] ); ?>"
									id="accordion-button-<?php echo esc_attr( $product_tab['key'] ); ?>"
								>
									<span><?php echo esc_html( $product_tab['title'] ); ?></span>
									<span class="sawerna-single-product__accordion-icon" aria-hidden="true"></span>
								</button>
								<div
									class="sawerna-single-product__accordion-panel"
									id="accordion-panel-<?php echo esc_attr( $product_tab['key'] ); ?>"
									data-accordion-panel="<?php echo esc_attr( $product_tab['key'] ); ?>"
									aria-labelledby="accordion-button-<?php echo esc_attr( $product_tab['key'] ); ?>"
									hidden
								>
									<?php
									if ( 'reviews' === $product_tab['key'] ) {
										echo $product_tab['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									} else {
										echo wp_kses_post( $product_tab['content'] );
									}
									?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $related_items ) ) : ?>
				<section class="sawerna-single-product__related<?php echo count( $related_items ) > 2 ? ' is-slider-enabled' : ''; ?>" data-related-products="<?php echo count( $related_items ) > 2 ? '1' : '0'; ?>">
					<div class="sawerna-single-product__related-header">
						<h2 class="sawerna-single-product__related-title"><?php esc_html_e( 'Seotud tooted', 'sawerna' ); ?></h2>

						<?php if ( count( $related_items ) > 2 ) : ?>
							<div class="sawerna-single-product__related-nav">
								<button type="button" class="sawerna-single-product__related-arrow sawerna-single-product__related-arrow--prev" data-related-nav="prev" aria-label="<?php esc_attr_e( 'Eelmised tooted', 'sawerna' ); ?>">
									<img src="<?php echo esc_url( $theme_uri . '/assets/SVG/Asset 27.svg' ); ?>" alt="">
								</button>
								<button type="button" class="sawerna-single-product__related-arrow sawerna-single-product__related-arrow--next" data-related-nav="next" aria-label="<?php esc_attr_e( 'Järgmised tooted', 'sawerna' ); ?>">
									<img src="<?php echo esc_url( $theme_uri . '/assets/SVG/Asset 26.svg' ); ?>" alt="">
								</button>
							</div>
						<?php endif; ?>
					</div>

					<div class="sawerna-single-product__related-track-wrap">
						<div class="sawerna-single-product__related-track">
							<?php foreach ( $related_items as $related_product ) : ?>
								<div class="sawerna-single-product__related-item">
									<?php sawerna_render_product_card( $related_product, 'archive' ); ?>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</section>
			<?php endif; ?>
		</article>
	</div>
</div>

<?php do_action( 'woocommerce_after_single_product' ); ?>
