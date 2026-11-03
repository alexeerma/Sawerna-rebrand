<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Sawerna
 */

get_header();
?>

	<main id="primary" class="site-main sawerna-blog-archive">
		<div class="sawerna-blog-archive__container">

		<?php
		if ( have_posts() ) :

			if ( is_home() && ! is_front_page() ) :
				get_template_part( 'template-parts/blog/archive', 'header' );
			endif;

			?>
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
			<?php

		else :

			if ( is_home() && ! is_front_page() ) :
				get_template_part( 'template-parts/blog/archive', 'header' );
			endif;

			get_template_part( 'template-parts/content', 'none' );

		endif;
		?>
		</div>

	</main><!-- #main -->

<?php
get_footer();
