<?php
/**
 * My Account navigation.
 *
 * @package WooCommerce\Templates
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

$items = function_exists( 'sawerna_woocommerce_account_menu_items' ) ? sawerna_woocommerce_account_menu_items() : array();

do_action( 'woocommerce_before_account_navigation' );
?>

<nav class="woocommerce-MyAccount-navigation sawerna-account-nav" aria-label="<?php esc_attr_e( 'Minu konto lehed', 'sawerna' ); ?>">
	<div class="sawerna-account-nav__title"><?php esc_html_e( 'Minu konto', 'sawerna' ); ?></div>

	<ul class="sawerna-account-nav__list">
		<?php foreach ( $items as $key => $item ) : ?>
			<?php
			$endpoint = isset( $item['endpoint'] ) ? (string) $item['endpoint'] : (string) $key;
			$is_current = function_exists( 'wc_is_current_account_menu_item' ) && wc_is_current_account_menu_item( $endpoint );

			if ( 'dashboard' === $endpoint ) {
				$is_current = is_account_page() && ! is_wc_endpoint_url();
			}

			$classes = array( 'sawerna-account-nav__item' );
			if ( $is_current ) {
				$classes[] = 'is-active';
			}
			?>
			<li class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
				<a class="sawerna-account-nav__link" href="<?php echo esc_url( (string) $item['url'] ); ?>"<?php echo $is_current ? ' aria-current="page"' : ''; ?>>
					<span class="sawerna-account-nav__icon">
						<?php echo function_exists( 'sawerna_woocommerce_account_icon' ) ? sawerna_woocommerce_account_icon( (string) $item['icon'] ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>
					<span class="sawerna-account-nav__label"><?php echo esc_html( (string) $item['label'] ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>

<?php do_action( 'woocommerce_after_account_navigation' ); ?>
