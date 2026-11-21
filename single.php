<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Sawerna
 */

get_header();
?>

	<?php if ( is_singular( 'post' ) ) : ?>
	<main id="primary" class="site-main sawerna-blog-single">
		<?php
		while ( have_posts() ) :
			the_post();

			get_template_part( 'template-parts/content', 'single-post' );

			if ( comments_open() || get_comments_number() ) :
				?>
				<section class="sawerna-blog-post__comments-wrap">
					<div class="sawerna-blog-post__container">
						<?php comments_template(); ?>
					</div>
				</section>
				<?php
			endif;

			get_template_part( 'template-parts/blog/recent', 'posts' );

		endwhile;
		?>
	</main><!-- #main -->
		<?php
else :
	?>
	<main id="primary" class="site-main">

		<?php
		while ( have_posts() ) :
			the_post();

			get_template_part( 'template-parts/content', get_post_type() );

			the_post_navigation(
				array(
					'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous:', 'sawerna' ) . '</span> <span class="nav-title">%title</span>',
					'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next:', 'sawerna' ) . '</span> <span class="nav-title">%title</span>',
				)
			);

			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;

		endwhile;
		?>

	</main><!-- #main -->

	<?php
	get_sidebar();
endif;
get_footer();
