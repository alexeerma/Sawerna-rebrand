<?php
/**
 * My Account page.
 *
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="sawerna-account">
	<div class="sawerna-account__layout">
		<?php
		/**
		 * My Account navigation.
		 *
		 * @since 2.6.0
		 */
		do_action( 'woocommerce_account_navigation' );
		?>

		<main class="woocommerce-MyAccount-content sawerna-account__content">
			<?php
			/**
			 * My Account content.
			 *
			 * @since 2.6.0
			 */
			do_action( 'woocommerce_account_content' );
			?>
		</main>
	</div>
</div>
