<?php
/**
 * Custom single product template.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product.php.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     Sawerna
 * @version     1.6.4
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
?>

<?php
	do_action( 'woocommerce_before_main_content' );
?>

<?php while ( have_posts() ) : ?>
	<?php the_post(); ?>
	<?php wc_get_template_part( 'content', 'single-product' ); ?>
<?php endwhile; ?>

<?php
	do_action( 'woocommerce_after_main_content' );
?>

<?php
get_footer( 'shop' );
