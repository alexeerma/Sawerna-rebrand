<?php
/**
 * The template for displaying search results pages.
 *
 * @package Sawerna
 */

get_header();

$search_query  = get_search_query( false );
$results_count = isset( $wp_query ) && $wp_query instanceof WP_Query ? (int) $wp_query->found_posts : 0;
?>

<main id="primary" class="sawerna-search-page">
	<section class="sawerna-search-page__hero">
		<div class="sawerna-search-page__container">
			<p class="sawerna-search-page__eyebrow"><?php esc_html_e( 'Otsing', 'sawerna' ); ?></p>
			<h1 class="sawerna-search-page__title">
				<?php
				if ( '' !== trim( $search_query ) ) {
					printf(
						/* translators: %s: Search query. */
						esc_html__( 'Tulemused otsingule "%s"', 'sawerna' ),
						esc_html( $search_query )
					);
				} else {
					esc_html_e( 'Otsi Sawerna valikust', 'sawerna' );
				}
				?>
			</h1>

			<form role="search" method="get" class="sawerna-search-page__form" action="<?php echo esc_url( home_url( '/' ) ); ?>" data-sawerna-search-form>
				<label for="sawerna-search-page-input" class="screen-reader-text"><?php esc_html_e( 'Otsi', 'sawerna' ); ?></label>
				<input
					type="search"
					id="sawerna-search-page-input"
					class="sawerna-search-page__input"
					name="s"
					value="<?php echo esc_attr( $search_query ); ?>"
					placeholder="<?php esc_attr_e( 'Otsi tooteid...', 'sawerna' ); ?>"
					autocomplete="off"
					aria-autocomplete="list"
					aria-expanded="false"
					aria-controls="sawerna-search-page-results"
					data-sawerna-search-input
				>
				<button type="submit" class="sawerna-search-page__submit">
					<?php esc_html_e( 'Otsi', 'sawerna' ); ?>
				</button>
				<div
					id="sawerna-search-page-results"
					class="sawerna-ajax-search"
					data-sawerna-search-results
					role="region"
					aria-label="<?php esc_attr_e( 'Otsingu tulemused', 'sawerna' ); ?>"
					hidden
				></div>
			</form>
		</div>
	</section>

	<section class="sawerna-search-page__results">
		<div class="sawerna-search-page__container">
			<?php if ( have_posts() ) : ?>
				<div class="sawerna-search-page__summary">
					<?php
					printf(
						/* translators: %d: Number of results. */
						esc_html( _n( '%d tulemus', '%d tulemust', $results_count, 'sawerna' ) ),
						(int) $results_count
					);
					?>
				</div>

				<div class="sawerna-search-page__grid">
					<?php
					while ( have_posts() ) :
						the_post();

						$result_id     = get_the_ID();
						$result_type   = get_post_type( $result_id );
						$is_product    = 'product' === $result_type && function_exists( 'wc_get_product' );
						$product       = $is_product ? wc_get_product( $result_id ) : false;
						$image_url     = get_the_post_thumbnail_url( $result_id, 'medium_large' );
						$result_label  = '';
						$result_detail = '';

						if ( $is_product ) {
							$terms = get_the_terms( $result_id, 'product_cat' );

							if ( is_array( $terms ) && ! empty( $terms ) && $terms[0] instanceof WP_Term ) {
								$result_label = $terms[0]->name;
							} else {
								$result_label = __( 'Toode', 'sawerna' );
							}

							if ( $product instanceof WC_Product ) {
								$result_detail = $product->get_price_html();

								if ( ! $image_url && function_exists( 'wc_placeholder_img_src' ) ) {
									$image_url = wc_placeholder_img_src( 'woocommerce_thumbnail' );
								}
							}
						} else {
							$result_type_obj = get_post_type_object( (string) $result_type );
							$result_label    = $result_type_obj && isset( $result_type_obj->labels->singular_name ) ? (string) $result_type_obj->labels->singular_name : __( 'Leht', 'sawerna' );
							$result_detail   = wp_strip_all_tags( get_the_excerpt() );
						}
						?>
						<article id="post-<?php the_ID(); ?>" <?php post_class( 'sawerna-search-card' ); ?>>
							<a class="sawerna-search-card__media" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
								<?php if ( $image_url ) : ?>
									<img src="<?php echo esc_url( $image_url ); ?>" alt="" loading="lazy" decoding="async">
								<?php endif; ?>
							</a>
							<div class="sawerna-search-card__body">
								<div class="sawerna-search-card__meta"><?php echo esc_html( $result_label ); ?></div>
								<h2 class="sawerna-search-card__title">
									<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</h2>
								<?php if ( '' !== trim( (string) $result_detail ) ) : ?>
									<div class="<?php echo $is_product ? 'sawerna-search-card__price' : 'sawerna-search-card__excerpt'; ?>">
										<?php echo $is_product ? wp_kses_post( $result_detail ) : esc_html( wp_trim_words( $result_detail, 22 ) ); ?>
									</div>
								<?php endif; ?>
							</div>
							<a class="sawerna-search-card__link" href="<?php the_permalink(); ?>">
								<?php esc_html_e( 'Vaata lähemalt', 'sawerna' ); ?>
							</a>
						</article>
					<?php endwhile; ?>
				</div>

				<?php
				the_posts_pagination(
					array(
						'mid_size'  => 1,
						'prev_text' => __( 'Eelmised', 'sawerna' ),
						'next_text' => __( 'Järgmised', 'sawerna' ),
					)
				);
				?>
			<?php else : ?>
				<div class="sawerna-search-page__empty">
					<h2><?php esc_html_e( 'Tulemusi ei leitud', 'sawerna' ); ?></h2>
					<p><?php esc_html_e( 'Proovi teist märksõna või kirjuta toote nimi veidi lühemalt.', 'sawerna' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</section>
</main>

<?php
get_footer();
