<?php
/**
 * My Account dashboard.
 *
 * @package WooCommerce\Templates
 * @version 4.4.0
 */

defined( 'ABSPATH' ) || exit;

$logged_in_user = wp_get_current_user();
$display_name   = $logged_in_user instanceof WP_User && '' !== trim( $logged_in_user->display_name ) ? $logged_in_user->display_name : __( 'klient', 'sawerna' );
$items          = function_exists( 'sawerna_woocommerce_account_menu_items' ) ? sawerna_woocommerce_account_menu_items() : array();
$card_keys      = array( 'orders', 'downloads', 'edit-address', 'edit-account', 'wishlist', 'newsletter', 'customer-logout' );
?>

<section class="sawerna-account-dashboard">
	<header class="sawerna-account-dashboard__header">
		<h1 class="sawerna-account-dashboard__title">
			<?php
			printf(
				/* translators: %s: Customer display name. */
				wp_kses_post( __( 'Tere tulemast, <strong>%s!</strong>', 'sawerna' ) ),
				esc_html( $display_name )
			);
			?>
		</h1>

		<p class="sawerna-account-dashboard__intro">
			<?php
			$allowed_html = array(
				'a' => array(
					'href' => array(),
				),
			);
			if ( wc_shipping_enabled() ) {
				/* translators: 1: Orders URL. 2: Addresses URL. 3: Account details URL. */
				$dashboard_desc = __( 'Oma konto juhtpaneelilt saate vaadata oma <a href="%1$s">viimaseid tellimusi</a>, hallata oma <a href="%2$s">saatmis- ja arveldusaadresse</a> ning <a href="%3$s">muuta oma parooli ja konto andmeid</a>.', 'sawerna' );
			} else {
				/* translators: 1: Orders URL. 2: Address URL. 3: Account details URL. */
				$dashboard_desc = __( 'Oma konto juhtpaneelilt saate vaadata oma <a href="%1$s">viimaseid tellimusi</a>, hallata oma <a href="%2$s">arveldusaadressi</a> ning <a href="%3$s">muuta oma parooli ja konto andmeid</a>.', 'sawerna' );
			}
			printf(
				wp_kses( $dashboard_desc, $allowed_html ),
				esc_url( wc_get_account_endpoint_url( 'orders' ) ),
				esc_url( wc_get_account_endpoint_url( 'edit-address' ) ),
				esc_url( wc_get_account_endpoint_url( 'edit-account' ) )
			);
			?>
		</p>
	</header>

	<div class="sawerna-account-dashboard__grid">
		<?php foreach ( $card_keys as $key ) : ?>
			<?php if ( ! empty( $items[ $key ] ) ) : ?>
				<a class="sawerna-account-card" href="<?php echo esc_url( (string) $items[ $key ]['url'] ); ?>">
					<span class="sawerna-account-card__icon">
						<?php echo function_exists( 'sawerna_woocommerce_account_icon' ) ? sawerna_woocommerce_account_icon( (string) $items[ $key ]['icon'] ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>
					<span class="sawerna-account-card__label"><?php echo esc_html( (string) $items[ $key ]['label'] ); ?></span>
				</a>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
</section>

<?php
/**
 * My Account dashboard.
 *
 * @since 2.6.0
 */
do_action( 'woocommerce_account_dashboard' );

/**
 * Deprecated woocommerce_before_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action( 'woocommerce_before_my_account' );

/**
 * Deprecated woocommerce_after_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action( 'woocommerce_after_my_account' );
