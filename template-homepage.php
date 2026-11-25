<?php
/**
 * Template Name: Home Page
 * Template Post Type: page
 *
 * @package Sawerna
 */

get_header();

$category_terms        = sawerna_get_homepage_category_terms();
$homepage_options      = sawerna_get_homepage_options();
$wide_slides           = ! empty( $homepage_options['hero_wide_slides'] ) && is_array( $homepage_options['hero_wide_slides'] ) ? $homepage_options['hero_wide_slides'] : array();
$narrow_slides         = ! empty( $homepage_options['hero_narrow_slides'] ) && is_array( $homepage_options['hero_narrow_slides'] ) ? $homepage_options['hero_narrow_slides'] : array();
$new_products          = sawerna_get_homepage_new_products( $homepage_options );
$recommendation_tabs   = sawerna_get_homepage_recommendation_tabs( $homepage_options );
$factory_products      = sawerna_get_homepage_factory_products( $homepage_options );
$sale_products         = sawerna_get_homepage_sale_products( $homepage_options );
$best_sellers          = sawerna_get_homepage_best_sellers( $homepage_options );
$blog_posts            = sawerna_get_homepage_blog_posts();
$testimonials          = sawerna_get_homepage_testimonials();
$archive_url           = ! empty( $homepage_options['products_archive_url'] ) ? (string) $homepage_options['products_archive_url'] : ( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) );
$factory_archive_url   = ! empty( $homepage_options['factory_archive_url'] ) ? (string) $homepage_options['factory_archive_url'] : $archive_url;
$sale_archive_url      = ! empty( $homepage_options['sale_archive_url'] ) ? (string) $homepage_options['sale_archive_url'] : $archive_url;
$best_sellers_url      = ! empty( $homepage_options['best_sellers_archive_url'] ) ? (string) $homepage_options['best_sellers_archive_url'] : $archive_url;
$blog_archive_url      = ! empty( $homepage_options['blog_archive_url'] ) ? (string) $homepage_options['blog_archive_url'] : home_url( '/blog/' );
$category_heading      = ! empty( $homepage_options['category_heading'] ) ? (string) $homepage_options['category_heading'] : __( 'Alusta shoppamist oiges kategoorias', 'sawerna' );
$category_more_label   = ! empty( $homepage_options['category_more_label'] ) ? (string) $homepage_options['category_more_label'] : __( 'Kuva rohkem kategooriaid', 'sawerna' );
$category_less_label   = ! empty( $homepage_options['category_less_label'] ) ? (string) $homepage_options['category_less_label'] : __( 'Peida kategooriad', 'sawerna' );
$visible_category_rows = 2;
$visible_category_max  = 8;
$about_title           = (string) $homepage_options['about_title'];
$about_subtitle        = (string) $homepage_options['about_subtitle'];
$about_text            = (string) $homepage_options['about_text'];
$about_button_text     = (string) $homepage_options['about_button_text'];
$about_button_url      = (string) $homepage_options['about_button_url'];
$testimonials_title    = (string) $homepage_options['testimonials_title'];
$home_blog_title       = (string) $homepage_options['blog_title'];
?>

<main id="primary" class="site-main sawerna-homepage">
	<?php if ( ! empty( $wide_slides ) || ! empty( $narrow_slides ) ) : ?>
		<section class="sawerna-homepage__hero">
			<div class="sawerna-homepage__container">
				<div class="sawerna-home-hero">
					<div class="sawerna-home-hero__grid<?php echo ( ! empty( $wide_slides ) && ! empty( $narrow_slides ) ) ? ' sawerna-home-hero__grid--dual' : ''; ?>">
						<?php if ( ! empty( $wide_slides ) ) : ?>
							<div class="sawerna-home-hero__lane sawerna-home-hero__lane--wide" data-home-slider="wide">
								<div class="sawerna-home-hero__viewport">
									<div class="sawerna-home-hero__track" data-home-slider-track="1">
										<?php foreach ( $wide_slides as $index => $slide ) : ?>
											<?php $image_url = ! empty( $slide['image_id'] ) ? sawerna_homepage_hero_image_url( (int) $slide['image_id'] ) : ''; ?>
											<div class="sawerna-home-hero__slide" data-home-slide="<?php echo esc_attr( (string) $index ); ?>">
												<div class="sawerna-home-hero__card sawerna-home-hero__card--wide"<?php echo $image_url ? ' style="background-image:url(' . esc_url( $image_url ) . ')"' : ''; ?>>
													<div class="sawerna-home-hero__overlay"></div>
													<div class="sawerna-home-hero__content">
														<?php if ( ! empty( $slide['title'] ) ) : ?>
															<h2 class="sawerna-home-hero__title"><?php echo esc_html( (string) $slide['title'] ); ?></h2>
														<?php endif; ?>
														<?php if ( ! empty( $slide['text'] ) ) : ?>
															<p class="sawerna-home-hero__text"><?php echo nl2br( esc_html( (string) $slide['text'] ) ); ?></p>
														<?php endif; ?>
														<?php if ( ! empty( $slide['button_text'] ) && ! empty( $slide['button_url'] ) ) : ?>
															<a class="sawerna-home-hero__button" href="<?php echo esc_url( (string) $slide['button_url'] ); ?>">
																<?php echo esc_html( (string) $slide['button_text'] ); ?>
															</a>
														<?php endif; ?>
													</div>
												</div>
											</div>
										<?php endforeach; ?>
									</div>
								</div>

								<?php if ( count( $wide_slides ) > 1 ) : ?>
									<div class="sawerna-home-hero__nav">
										<button type="button" class="sawerna-home-hero__arrow" data-home-slider-prev="1" aria-label="<?php esc_attr_e( 'Previous wide slide', 'sawerna' ); ?>">
											<span aria-hidden="true">&larr;</span>
										</button>
										<div class="sawerna-home-hero__dots" role="tablist" aria-label="<?php esc_attr_e( 'Homepage wide slides', 'sawerna' ); ?>">
											<?php foreach ( $wide_slides as $index => $slide ) : ?>
												<button type="button" class="sawerna-home-hero__dot<?php echo 0 === $index ? ' is-active' : ''; ?>" data-home-slider-dot="<?php echo esc_attr( (string) $index ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: slide number. */ __( 'Go to wide slide %d', 'sawerna' ), $index + 1 ) ); ?>" aria-pressed="<?php echo 0 === $index ? 'true' : 'false'; ?>"></button>
											<?php endforeach; ?>
										</div>
										<button type="button" class="sawerna-home-hero__arrow" data-home-slider-next="1" aria-label="<?php esc_attr_e( 'Next wide slide', 'sawerna' ); ?>">
											<span aria-hidden="true">&rarr;</span>
										</button>
									</div>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php if ( ! empty( $narrow_slides ) ) : ?>
							<div class="sawerna-home-hero__lane sawerna-home-hero__lane--narrow" data-home-slider="narrow">
								<div class="sawerna-home-hero__viewport">
									<div class="sawerna-home-hero__track" data-home-slider-track="1">
										<?php foreach ( $narrow_slides as $index => $slide ) : ?>
											<?php
											$image_url  = ! empty( $slide['image_id'] ) ? sawerna_homepage_hero_image_url( (int) $slide['image_id'] ) : '';
											$right_list = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $slide['list'] ) ) );
											?>
											<div class="sawerna-home-hero__slide" data-home-slide="<?php echo esc_attr( (string) $index ); ?>">
												<div class="sawerna-home-hero__card sawerna-home-hero__card--narrow"<?php echo $image_url ? ' style="background-image:url(' . esc_url( $image_url ) . ')"' : ''; ?>>
													<div class="sawerna-home-hero__overlay"></div>
													<div class="sawerna-home-hero__content">
														<?php if ( ! empty( $slide['title'] ) ) : ?>
															<h3 class="sawerna-home-hero__side-title"><?php echo esc_html( (string) $slide['title'] ); ?></h3>
														<?php endif; ?>
														<?php if ( ! empty( $slide['text'] ) ) : ?>
															<p class="sawerna-home-hero__side-text"><?php echo nl2br( esc_html( (string) $slide['text'] ) ); ?></p>
														<?php endif; ?>
														<?php if ( ! empty( $right_list ) ) : ?>
															<ul class="sawerna-home-hero__list">
																<?php foreach ( $right_list as $item ) : ?>
																	<li><?php echo esc_html( $item ); ?></li>
																<?php endforeach; ?>
															</ul>
														<?php endif; ?>
														<?php if ( ! empty( $slide['button_text'] ) && ! empty( $slide['button_url'] ) ) : ?>
															<a class="sawerna-home-hero__button sawerna-home-hero__button--light" href="<?php echo esc_url( (string) $slide['button_url'] ); ?>">
																<?php echo esc_html( (string) $slide['button_text'] ); ?>
															</a>
														<?php endif; ?>
													</div>
												</div>
											</div>
										<?php endforeach; ?>
									</div>
								</div>

								<?php if ( count( $narrow_slides ) > 1 ) : ?>
									<div class="sawerna-home-hero__nav">
										<button type="button" class="sawerna-home-hero__arrow" data-home-slider-prev="1" aria-label="<?php esc_attr_e( 'Previous narrow slide', 'sawerna' ); ?>">
											<span aria-hidden="true">&larr;</span>
										</button>
										<div class="sawerna-home-hero__dots" role="tablist" aria-label="<?php esc_attr_e( 'Homepage narrow slides', 'sawerna' ); ?>">
											<?php foreach ( $narrow_slides as $index => $slide ) : ?>
												<button type="button" class="sawerna-home-hero__dot<?php echo 0 === $index ? ' is-active' : ''; ?>" data-home-slider-dot="<?php echo esc_attr( (string) $index ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: slide number. */ __( 'Go to narrow slide %d', 'sawerna' ), $index + 1 ) ); ?>" aria-pressed="<?php echo 0 === $index ? 'true' : 'false'; ?>"></button>
											<?php endforeach; ?>
										</div>
										<button type="button" class="sawerna-home-hero__arrow" data-home-slider-next="1" aria-label="<?php esc_attr_e( 'Next narrow slide', 'sawerna' ); ?>">
											<span aria-hidden="true">&rarr;</span>
										</button>
									</div>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $category_terms ) ) : ?>
		<section class="sawerna-homepage__categories">
			<div class="sawerna-homepage__container">
				<?php if ( '' !== trim( $category_heading ) ) : ?>
					<header class="sawerna-homepage__section-head">
						<h2 class="sawerna-homepage__section-title"><?php echo esc_html( $category_heading ); ?></h2>
					</header>
				<?php endif; ?>

				<div class="sawerna-home-categories" data-home-categories="1">
					<div class="sawerna-home-categories__grid">
						<?php foreach ( array_slice( $category_terms, 0, $visible_category_max ) as $category_term ) : ?>
							<a class="sawerna-home-categories__item" href="<?php echo esc_url( get_term_link( $category_term ) ); ?>">
								<span class="sawerna-home-categories__media"><?php echo wp_kses_post( sawerna_get_homepage_category_image_html( $category_term ) ); ?></span>
								<span class="sawerna-home-categories__name"><?php echo esc_html( $category_term->name ); ?></span>
							</a>
						<?php endforeach; ?>
					</div>

					<?php if ( count( $category_terms ) > $visible_category_max ) : ?>
						<div class="sawerna-home-categories__more" data-home-categories-more="1" hidden>
							<div class="sawerna-home-categories__grid">
								<?php foreach ( array_slice( $category_terms, $visible_category_max ) as $category_term ) : ?>
									<a class="sawerna-home-categories__item" href="<?php echo esc_url( get_term_link( $category_term ) ); ?>">
										<span class="sawerna-home-categories__media"><?php echo wp_kses_post( sawerna_get_homepage_category_image_html( $category_term ) ); ?></span>
										<span class="sawerna-home-categories__name"><?php echo esc_html( $category_term->name ); ?></span>
									</a>
								<?php endforeach; ?>
							</div>
						</div>

						<div class="sawerna-home-categories__toggle-wrap">
							<button
								type="button"
								class="sawerna-home-categories__toggle"
								data-home-categories-toggle="1"
								data-label-more="<?php echo esc_attr( $category_more_label ); ?>"
								data-label-less="<?php echo esc_attr( $category_less_label ); ?>"
								aria-expanded="false"
							>
								<span data-home-categories-toggle-label="1"><?php echo esc_html( $category_more_label ); ?></span>
								<span class="sawerna-home-categories__toggle-icon" aria-hidden="true"></span>
							</button>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $new_products ) ) : ?>
		<section class="sawerna-homepage__products">
			<div class="sawerna-homepage__container">
				<div class="sawerna-home-products__section-head">
					<h2 class="sawerna-homepage__section-title"><?php esc_html_e( 'Uued tooted Sawernas', 'sawerna' ); ?></h2>
					<a class="sawerna-home-products__more" href="<?php echo esc_url( $archive_url ); ?>">
						<span><?php esc_html_e( 'Näita rohkem', 'sawerna' ); ?></span>
						<span class="sawerna-home-products__more-icon" aria-hidden="true"><?php echo sawerna_get_svg_inline( 'Asset 9.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					</a>
				</div>

				<?php sawerna_homepage_render_products_slider( $new_products ); ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $recommendation_tabs ) ) : ?>
		<section class="sawerna-homepage__recommendations">
			<div class="sawerna-homepage__container">
				<div class="sawerna-home-products__section-head sawerna-home-products__section-head--center">
					<h2 class="sawerna-homepage__section-title"><?php esc_html_e( 'Sawerna soovitab', 'sawerna' ); ?></h2>
				</div>

				<div class="sawerna-home-recommendations" data-home-recommendations="1">
					<div class="sawerna-home-recommendations__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Recommended categories', 'sawerna' ); ?>">
						<?php foreach ( $recommendation_tabs as $index => $rec_tab ) : ?>
							<button
								type="button"
								class="sawerna-home-recommendations__tab<?php echo 0 === $index ? ' is-active' : ''; ?>"
								data-home-recommendations-tab="<?php echo esc_attr( (string) $index ); ?>"
								role="tab"
								aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>"
							>
								<?php echo esc_html( $rec_tab['term']->name ); ?>
							</button>
						<?php endforeach; ?>
					</div>

					<div class="sawerna-home-recommendations__panels">
						<?php foreach ( $recommendation_tabs as $index => $rec_tab ) : ?>
							<div class="sawerna-home-recommendations__panel<?php echo 0 === $index ? ' is-active' : ''; ?>" data-home-recommendations-panel="<?php echo esc_attr( (string) $index ); ?>"<?php echo 0 === $index ? '' : ' hidden'; ?>>
								<?php sawerna_homepage_render_products_slider( $rec_tab['products'] ); ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( '' !== trim( $about_title . $about_subtitle . $about_text ) || ! empty( $factory_products ) || ! empty( $testimonials ) ) : ?>
		<section class="sawerna-homepage__brand-band">
			<div class="sawerna-homepage__container">
				<?php if ( '' !== trim( $about_title . $about_subtitle . $about_text ) ) : ?>
					<div class="sawerna-home-brand">
						<?php if ( '' !== trim( $about_title ) ) : ?>
							<h2 class="sawerna-home-brand__title"><?php echo esc_html( $about_title ); ?></h2>
						<?php endif; ?>
						<?php if ( '' !== trim( $about_subtitle ) ) : ?>
							<p class="sawerna-home-brand__subtitle"><?php echo esc_html( $about_subtitle ); ?></p>
						<?php endif; ?>
						<?php if ( '' !== trim( $about_text ) ) : ?>
							<div class="sawerna-home-brand__text"><?php echo wp_kses_post( wpautop( esc_html( $about_text ) ) ); ?></div>
						<?php endif; ?>

						<?php if ( '' !== trim( $about_button_text ) && '' !== trim( $about_button_url ) ) : ?>
							<div class="sawerna-home-brand__cta-wrap">
								<a class="sawerna-home-brand__cta" href="<?php echo esc_url( $about_button_url ); ?>">
									<span><?php echo esc_html( $about_button_text ); ?></span>
									<span class="sawerna-home-brand__cta-icon" aria-hidden="true"><?php echo sawerna_get_svg_inline( 'Asset 9.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								</a>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $factory_products ) ) : ?>
					<section class="sawerna-homepage__factory-products">
						<div class="sawerna-home-products__section-head">
							<h2 class="sawerna-homepage__section-title"><?php esc_html_e( 'Sawerna oma tehase tooted', 'sawerna' ); ?></h2>
							<div class="sawerna-home-products__more-group">
								<span class="sawerna-home-products__signature" aria-hidden="true"><?php echo sawerna_get_svg_inline( 'Asset 24.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<a class="sawerna-home-products__more" href="<?php echo esc_url( $factory_archive_url ); ?>">
									<span><?php esc_html_e( 'Näita rohkem', 'sawerna' ); ?></span>
									<span class="sawerna-home-products__more-icon" aria-hidden="true"><?php echo sawerna_get_svg_inline( 'Asset 9.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								</a>
							</div>
						</div>

						<?php sawerna_homepage_render_products_slider( $factory_products ); ?>
					</section>
				<?php endif; ?>

				<?php if ( ! empty( $testimonials ) ) : ?>
					<section class="sawerna-homepage__testimonials">
						<div class="sawerna-home-testimonials__heading-wrap">
							<h2 class="sawerna-homepage__section-title"><?php echo esc_html( $testimonials_title ); ?></h2>
						</div>

						<?php
						$testimonial_count  = count( $testimonials );
						$testimonial_groups = array();

						if ( $testimonial_count <= 3 ) {
							$testimonial_groups[] = $testimonials;
						} else {
							for ( $group_index = 0; $group_index < $testimonial_count; $group_index++ ) {
								$group = array();

								for ( $offset = 0; $offset < 3; $offset++ ) {
									$group[] = $testimonials[ ( $group_index + $offset ) % $testimonial_count ];
								}

								$testimonial_groups[] = $group;
							}
						}
						?>
						<div class="sawerna-home-testimonials" data-home-testimonials="1">
							<?php if ( count( $testimonial_groups ) > 1 ) : ?>
								<div class="sawerna-home-testimonials__controls">
									<button type="button" class="sawerna-home-testimonials__arrow" data-home-testimonials-prev="1" aria-label="<?php esc_attr_e( 'Previous testimonials', 'sawerna' ); ?>">
										<?php echo sawerna_get_svg_inline( 'Asset 9.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									</button>
									<div class="sawerna-home-testimonials__dots">
										<?php foreach ( $testimonial_groups as $group_index => $group ) : ?>
											<button type="button" class="sawerna-home-testimonials__dot<?php echo 0 === $group_index ? ' is-active' : ''; ?>" data-home-testimonials-dot="<?php echo esc_attr( (string) $group_index ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: slide number. */ __( 'Go to testimonial slide %d', 'sawerna' ), $group_index + 1 ) ); ?>"></button>
										<?php endforeach; ?>
									</div>
									<button type="button" class="sawerna-home-testimonials__arrow is-next" data-home-testimonials-next="1" aria-label="<?php esc_attr_e( 'Next testimonials', 'sawerna' ); ?>">
										<?php echo sawerna_get_svg_inline( 'Asset 9.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									</button>
								</div>
							<?php endif; ?>

							<div class="sawerna-home-testimonials__viewport">
								<div class="sawerna-home-testimonials__track" data-home-testimonials-track="1">
									<?php foreach ( $testimonial_groups as $group_index => $group ) : ?>
										<div class="sawerna-home-testimonials__slide" data-home-testimonials-slide="<?php echo esc_attr( (string) $group_index ); ?>">
											<div class="sawerna-home-testimonials__grid">
												<?php foreach ( $group as $testimonial ) : ?>
													<?php $feedback_date = get_post_meta( $testimonial->ID, '_sawerna_testimonial_date', true ); ?>
													<article class="sawerna-home-testimonials__item">
														<div class="sawerna-home-testimonials__text"><?php echo wp_kses_post( wpautop( esc_html( wp_strip_all_tags( (string) $testimonial->post_content ) ) ) ); ?></div>
														<h3 class="sawerna-home-testimonials__name"><?php echo esc_html( get_the_title( $testimonial ) ); ?></h3>
														<?php if ( '' !== trim( (string) $feedback_date ) ) : ?>
															<p class="sawerna-home-testimonials__date"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( (string) $feedback_date ) ) ); ?></p>
														<?php endif; ?>
													</article>
												<?php endforeach; ?>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
					</section>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $sale_products ) || ! empty( $best_sellers ) ) : ?>
		<section class="sawerna-homepage__commerce-sections">
			<div class="sawerna-homepage__container">
				<?php if ( ! empty( $sale_products ) ) : ?>
					<section class="sawerna-homepage__sales">
						<div class="sawerna-home-products__section-head">
							<h2 class="sawerna-homepage__section-title sawerna-homepage__section-title--with-icon">
								<span class="sawerna-homepage__section-icon" aria-hidden="true"><?php echo sawerna_get_svg_inline( 'Asset 15.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<span><?php esc_html_e( 'Sooduskampaania', 'sawerna' ); ?></span>
							</h2>
							<a class="sawerna-home-products__more" href="<?php echo esc_url( $sale_archive_url ); ?>">
								<span><?php esc_html_e( 'Näita kõiki sooduspakkumisi', 'sawerna' ); ?></span>
								<span class="sawerna-home-products__more-icon" aria-hidden="true"><?php echo sawerna_get_svg_inline( 'Asset 9.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							</a>
						</div>

						<?php sawerna_homepage_render_products_slider( $sale_products ); ?>
					</section>
				<?php endif; ?>

				<?php if ( ! empty( $sale_products ) && ! empty( $best_sellers ) ) : ?>
					<div class="sawerna-homepage__section-divider" aria-hidden="true"></div>
				<?php endif; ?>

				<?php if ( ! empty( $best_sellers ) ) : ?>
					<section class="sawerna-homepage__best-sellers">
						<div class="sawerna-home-products__section-head">
							<h2 class="sawerna-homepage__section-title"><?php esc_html_e( 'Klientide poolt enim ostetud', 'sawerna' ); ?></h2>
							<a class="sawerna-home-products__more" href="<?php echo esc_url( $best_sellers_url ); ?>">
								<span><?php esc_html_e( 'Näita rohkem', 'sawerna' ); ?></span>
								<span class="sawerna-home-products__more-icon" aria-hidden="true"><?php echo sawerna_get_svg_inline( 'Asset 9.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							</a>
						</div>

						<?php sawerna_homepage_render_products_slider( $best_sellers ); ?>
					</section>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $blog_posts ) ) : ?>
		<section class="sawerna-homepage__blog-band">
			<div class="sawerna-homepage__container">
				<div class="sawerna-home-products__section-head sawerna-home-products__section-head--stacked">
					<h2 class="sawerna-homepage__section-title"><?php echo esc_html( $home_blog_title ); ?></h2>
					<a class="sawerna-home-products__more" href="<?php echo esc_url( $blog_archive_url ); ?>">
						<span><?php esc_html_e( 'Kuva kõik artiklid', 'sawerna' ); ?></span>
						<span class="sawerna-home-products__more-icon" aria-hidden="true"><?php echo sawerna_get_svg_inline( 'Asset 9.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					</a>
				</div>

				<div class="sawerna-home-blog__grid">
					<?php foreach ( $blog_posts as $post_item ) : ?>
						<?php
						$post_permalink = get_permalink( $post_item );
						$image_html     = get_the_post_thumbnail(
							$post_item,
							'large',
							array(
								'class'    => 'sawerna-home-blog__image',
								'loading'  => 'lazy',
								'decoding' => 'async',
								'sizes'    => '(max-width: 1024px) 50vw, 380px',
							)
						);
						$excerpt        = wp_trim_words(
							wp_strip_all_tags( has_excerpt( $post_item ) ? $post_item->post_excerpt : $post_item->post_content ),
							18,
							'...'
						);
						?>
						<article class="sawerna-home-blog__item">
							<a class="sawerna-home-blog__media" href="<?php echo esc_url( $post_permalink ); ?>">
								<?php if ( $image_html ) : ?>
									<?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<?php endif; ?>
							</a>
							<div class="sawerna-home-blog__body">
								<p class="sawerna-home-blog__date"><?php echo esc_html( get_the_date( '', $post_item ) ); ?></p>
								<h3 class="sawerna-home-blog__title"><a href="<?php echo esc_url( $post_permalink ); ?>"><?php echo esc_html( get_the_title( $post_item ) ); ?></a></h3>
								<p class="sawerna-home-blog__excerpt"><?php echo esc_html( $excerpt ); ?></p>
								<a class="sawerna-home-blog__more" href="<?php echo esc_url( $post_permalink ); ?>">
									<span><?php esc_html_e( 'Loe lähemalt', 'sawerna' ); ?></span>
									<span class="sawerna-home-products__more-icon" aria-hidden="true"><?php echo sawerna_get_svg_inline( 'Asset 9.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								</a>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( have_posts() ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<?php if ( '' !== trim( (string) get_the_content() ) ) : ?>
				<section class="sawerna-homepage__content">
					<div class="sawerna-homepage__container entry-content">
						<?php the_content(); ?>
					</div>
				</section>
			<?php endif; ?>
		<?php endwhile; ?>
	<?php endif; ?>
</main>

<?php
get_footer();
