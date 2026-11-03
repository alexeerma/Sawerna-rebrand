<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Sawerna
 */

?>
<?php if ( ! is_singular() && 'post' === get_post_type() ) : ?>
	<?php
	$excerpt = wp_trim_words(
		wp_strip_all_tags( has_excerpt() ? get_the_excerpt() : get_the_content() ),
		18,
		'...'
	);
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'sawerna-blog-card' ); ?>>
		<a
			class="sawerna-blog-card__overlay-link"
			href="<?php the_permalink(); ?>"
			rel="bookmark"
			aria-label="<?php echo esc_attr( get_the_title() ); ?>"
		></a>
		<div class="sawerna-blog-card__media" aria-hidden="true">
			<?php
			if ( has_post_thumbnail() ) {
				the_post_thumbnail(
					'large',
					array(
						'class'    => 'sawerna-blog-card__image',
						'loading'  => 'lazy',
						'decoding' => 'async',
						'sizes'    => '(max-width: 767px) 100vw, (max-width: 1024px) 50vw, 380px',
					)
				);
			}
			?>
		</div>
		<div class="sawerna-blog-card__body">
			<p class="sawerna-blog-card__date"><?php echo esc_html( get_the_date() ); ?></p>
			<h2 class="sawerna-blog-card__title"><?php the_title(); ?></h2>
			<p class="sawerna-blog-card__excerpt"><?php echo esc_html( $excerpt ); ?></p>
			<span class="sawerna-blog-card__more" aria-hidden="true">
				<span><?php esc_html_e( 'Loe lähemalt', 'sawerna' ); ?></span>
				<span class="sawerna-blog-card__more-icon" aria-hidden="true"><?php echo sawerna_get_svg_inline( 'Asset 9.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			</span>
		</div>
	</article>
<?php else : ?>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<header class="entry-header">
			<?php
			if ( is_singular() ) :
				the_title( '<h1 class="entry-title">', '</h1>' );
			else :
				the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
			endif;

			if ( 'post' === get_post_type() ) :
				?>
				<div class="entry-meta">
					<?php
					sawerna_posted_on();
					sawerna_posted_by();
					?>
				</div>
			<?php endif; ?>
		</header>

		<?php sawerna_post_thumbnail(); ?>

		<div class="entry-content">
			<?php
			the_content(
				sprintf(
					wp_kses(
						__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'sawerna' ),
						array(
							'span' => array(
								'class' => array(),
							),
						)
					),
					wp_kses_post( get_the_title() )
				)
			);

			wp_link_pages(
				array(
					'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'sawerna' ),
					'after'  => '</div>',
				)
			);
			?>
		</div>

		<footer class="entry-footer">
			<?php sawerna_entry_footer(); ?>
		</footer>
	</article>
<?php endif; ?>
