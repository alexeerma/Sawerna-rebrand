<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Sawerna
 */

get_header();
?>

	<main id="primary" class="site-main sawerna-blog-archive">
		<div class="sawerna-blog-archive__container">

		<?php if ( have_posts() ) : ?>

			<?php get_template_part( 'template-parts/blog/archive', 'header' ); ?>

			<div class="sawerna-blog-archive__grid">

			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content', get_post_type() );
			endwhile;
			?>
			</div>

			<div class="sawerna-blog-archive__pagination">
				<?php
				the_posts_pagination(
					array(
						'prev_text' => __( 'Eelmine', 'sawerna' ),
						'next_text' => __( 'Järgmine', 'sawerna' ),
					)
				);
				?>
			</div>

		<?php else : ?>

			<?php get_template_part( 'template-parts/blog/archive', 'header' ); ?>
			<?php get_template_part( 'template-parts/content', 'none' ); ?>

		<?php endif; ?>
		</div>

	</main><!-- #main -->

<?php
get_footer();
