<?php
/**
 * Template Name: About Us Page
 * Template Post Type: page
 *
 * @package Sawerna
 */

get_header();

$about_options  = sawerna_get_about_options();
$hero_image_url = ! empty( $about_options['hero_image_id'] ) ? wp_get_attachment_image_url( (int) $about_options['hero_image_id'], 'full' ) : '';
$accent_image   = ! empty( $about_options['hero_accent_image_id'] )
	? wp_get_attachment_image(
		(int) $about_options['hero_accent_image_id'],
		'medium_large',
		false,
		array(
			'class'    => 'sawerna-about-hero__accent-image',
			'loading'  => 'eager',
			'decoding' => 'async',
			'sizes'    => '(max-width: 767px) 220px, 360px',
		)
	)
	: '';
$owner_image    = ! empty( $about_options['owner_image_id'] )
	? wp_get_attachment_image(
		(int) $about_options['owner_image_id'],
		'large',
		false,
		array(
			'class'    => 'sawerna-about-owner__image',
			'loading'  => 'lazy',
			'decoding' => 'async',
			'sizes'    => '(max-width: 767px) 100vw, 360px',
		)
	)
	: '';
?>

<main id="primary" class="site-main sawerna-about">
	<section class="sawerna-about-hero"<?php echo $hero_image_url ? ' style="background-image:url(' . esc_url( $hero_image_url ) . ')"' : ''; ?>>
		<div class="sawerna-about-hero__overlay"></div>
		<div class="sawerna-about__container">
			<div class="sawerna-about-hero__content">
				<?php if ( '' !== trim( (string) $about_options['hero_eyebrow'] ) ) : ?>
					<p class="sawerna-about-hero__eyebrow"><?php echo esc_html( (string) $about_options['hero_eyebrow'] ); ?></p>
				<?php endif; ?>

				<?php if ( '' !== trim( (string) $about_options['hero_title'] ) ) : ?>
					<h1 class="sawerna-about-hero__title"><?php echo esc_html( (string) $about_options['hero_title'] ); ?></h1>
				<?php endif; ?>

				<?php if ( '' !== trim( (string) $about_options['hero_text'] ) ) : ?>
					<p class="sawerna-about-hero__text"><?php echo nl2br( esc_html( (string) $about_options['hero_text'] ) ); ?></p>
				<?php endif; ?>

				<?php if ( $accent_image ) : ?>
					<div class="sawerna-about-hero__accent" aria-hidden="true">
						<?php echo $accent_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<section class="sawerna-about-story" aria-label="<?php esc_attr_e( 'About Sawerna', 'sawerna' ); ?>">
		<div class="sawerna-about__container">
			<div class="sawerna-about-story__grid">
				<div class="sawerna-about-story__main">
					<?php if ( have_posts() ) : ?>
						<?php while ( have_posts() ) : ?>
							<?php the_post(); ?>
							<?php if ( '' !== trim( (string) get_the_content() ) ) : ?>
								<?php
								$about_content                                      = apply_filters( 'the_content', get_the_content() ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core filter.
								list( $content_before_quote, $content_after_quote ) = sawerna_about_split_content_before_last_paragraph( $about_content );
								?>
								<div class="sawerna-about-story__content entry-content">
									<?php echo $content_before_quote; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<?php sawerna_about_render_story_quote( $about_options ); ?>
									<?php echo $content_after_quote; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</div>
							<?php endif; ?>
						<?php endwhile; ?>
					<?php endif; ?>

					<?php
					wp_link_pages(
						array(
							'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'sawerna' ),
							'after'  => '</div>',
						)
					);
					?>
				</div>

				<aside class="sawerna-about-owner" aria-label="<?php esc_attr_e( 'Owner testimonial', 'sawerna' ); ?>">
					<div class="sawerna-about-owner__card">
						<span class="sawerna-about-owner__mark" aria-hidden="true">&rdquo;</span>

						<?php if ( '' !== trim( (string) $about_options['owner_card_text'] ) ) : ?>
							<p class="sawerna-about-owner__text"><?php echo nl2br( esc_html( (string) $about_options['owner_card_text'] ) ); ?></p>
						<?php endif; ?>

						<?php if ( '' !== trim( (string) $about_options['owner_name'] ) || '' !== trim( (string) $about_options['owner_title'] ) ) : ?>
							<div class="sawerna-about-owner__person">
								<?php if ( '' !== trim( (string) $about_options['owner_name'] ) ) : ?>
									<strong><?php echo esc_html( (string) $about_options['owner_name'] ); ?></strong>
								<?php endif; ?>
								<?php if ( '' !== trim( (string) $about_options['owner_title'] ) ) : ?>
									<span><?php echo esc_html( (string) $about_options['owner_title'] ); ?></span>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php if ( $owner_image ) : ?>
							<div class="sawerna-about-owner__media">
								<?php echo $owner_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
						<?php endif; ?>
					</div>
				</aside>
			</div>
		</div>
	</section>
</main>

<?php
get_footer();
