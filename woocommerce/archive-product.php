<?php
/**
 * The Template for displaying product archives.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package Sawerna
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

$archive_title = woocommerce_page_title( false );
$shop_page_id  = wc_get_page_id( 'shop' );
$shop_url      = $shop_page_id > 0 ? get_permalink( $shop_page_id ) : wc_get_page_permalink( 'shop' );
$theme_uri     = get_template_directory_uri();

$hero_parent_category = 0;
$queried_object       = get_queried_object();
if ( $queried_object instanceof WP_Term && 'product_cat' === $queried_object->taxonomy ) {
	$hero_parent_category = (int) $queried_object->term_id;
}

$uncategorized_id = (int) get_option( 'default_product_cat', 0 );

$top_categories = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'parent'     => $hero_parent_category,
		'hide_empty' => true,
		'number'     => 12,
		'orderby'    => 'menu_order',
		'order'      => 'ASC',
		'exclude'    => $uncategorized_id > 0 ? array( $uncategorized_id ) : array(),
	)
);
?>

<div class="sawerna-shop">
	<div class="sawerna-shop__breadcrumb-row">
		<div class="sawerna-shop__breadcrumb-container">
			<?php
			if ( function_exists( 'woocommerce_breadcrumb' ) ) {
				woocommerce_breadcrumb();
			}
			?>
		</div>
	</div>

	<section class="sawerna-shop-hero">
		<div class="sawerna-shop-hero__container">
			<div class="sawerna-shop-hero__header">
				<h1 class="sawerna-shop-hero__title"><?php echo esc_html( $archive_title ); ?></h1>

				<?php if ( is_array( $top_categories ) && ! empty( $top_categories ) ) : ?>
						<div class="sawerna-shop-hero__nav" aria-label="<?php esc_attr_e( 'Kategooriate sirvimine', 'sawerna' ); ?>">
							<button type="button" class="sawerna-shop-hero__nav-button" data-shop-categories-nav="prev" aria-label="<?php esc_attr_e( 'Eelmine kategooria', 'sawerna' ); ?>">
								<img src="<?php echo esc_url( $theme_uri . '/assets/SVG/Asset 27.svg' ); ?>" alt="" aria-hidden="true">
							</button>
							<button type="button" class="sawerna-shop-hero__nav-button" data-shop-categories-nav="next" aria-label="<?php esc_attr_e( 'Järgmine kategooria', 'sawerna' ); ?>">
								<img src="<?php echo esc_url( $theme_uri . '/assets/SVG/Asset 26.svg' ); ?>" alt="" aria-hidden="true">
							</button>
						</div>
				<?php endif; ?>
			</div>

			<?php if ( is_array( $top_categories ) && ! empty( $top_categories ) ) : ?>
				<div class="sawerna-shop-hero__categories-wrap">
					<div class="sawerna-shop-hero__categories" data-shop-categories-track="1" aria-label="<?php esc_attr_e( 'Top product categories', 'sawerna' ); ?>">
					<?php foreach ( $top_categories as $term ) : ?>
					<?php
					$is_active = is_tax( 'product_cat', $term->term_id );
					$term_link = get_term_link( $term );
					$thumb_id  = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
					if ( is_wp_error( $term_link ) ) {
						$term_link = $shop_url;
					}
					?>
					<a class="sawerna-shop-hero__category<?php echo $is_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( $term_link ); ?>">
						<span class="sawerna-shop-hero__category-icon" aria-hidden="true">
							<?php if ( $thumb_id ) : ?>
								<?php echo wp_get_attachment_image( $thumb_id, 'medium', false, array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
							<?php endif; ?>
						</span>
						<span class="sawerna-shop-hero__category-label"><?php echo esc_html( $term->name ); ?></span>
					</a>
					<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<div class="sawerna-shop__container" data-shop-root="1">
		<aside class="sawerna-shop__sidebar" id="shop-filters-panel" role="complementary" aria-label="<?php esc_attr_e( 'Toodete filtrid', 'sawerna' ); ?>">
			<div data-shop-sidebar="1">
				<?php
				$shop_state   = sawerna_shop_get_state();
				$shop_context = sawerna_shop_get_archive_context();
				echo sawerna_shop_render_sidebar( $shop_state, $shop_context ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			</div>
		</aside>

		<div class="sawerna-shop__content">
			<?php
			// Breadcrumb is rendered at the very top of the archive layout.
			remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

			/**
			 * Hook: woocommerce_before_main_content.
			 *
			 * @hooked sawerna_woocommerce_wrapper_before - 10 (outputs <main>)
			 * @hooked woocommerce_breadcrumb - 20
			 */
			do_action( 'woocommerce_before_main_content' );

			$shop_state   = sawerna_shop_get_state();
			$shop_context = sawerna_shop_get_archive_context();
			$dynamic      = sawerna_shop_get_dynamic_parts( $shop_state, $shop_context );
			?>
			<div class="sawerna-shop-app" data-shop-app="1">
				<div data-shop-toolbar="1">
					<?php echo $dynamic['toolbar']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<div data-shop-active="1">
					<?php echo $dynamic['active']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<div data-shop-products="1">
					<?php echo $dynamic['products']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</div>
			<?php

			/**
			 * Hook: woocommerce_after_main_content.
			 *
			 * @hooked sawerna_woocommerce_wrapper_after - 10
			 */
			do_action( 'woocommerce_after_main_content' );
			?>
		</div>
	</div>
</div>

<?php
get_footer( 'shop' );
