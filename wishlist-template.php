<?php
/**
 * Template Name: Wishlist
 *
 * Wishlist page template with built-in shortcode.
 *
 * @package Sawerna
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

get_header();
?>

<main id="primary" class="site-main sawerna-wishlist-page">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<section class="sawerna-wishlist-page__content">
			<?php echo do_shortcode( '[sawerna_wishlist]' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</section>
		<?php
	endwhile;
	?>
</main><!-- #main -->

<?php
get_footer();
