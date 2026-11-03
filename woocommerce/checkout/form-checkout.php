<?php
/**
 * Checkout form template.
 *
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}
?>

<div class="sawerna-shop sawerna-checkout-page">
	<div class="sawerna-shop__breadcrumb-row">
		<div class="sawerna-shop__breadcrumb-container">
			<?php woocommerce_breadcrumb(); ?>
		</div>
	</div>

	<div class="sawerna-checkout">
		<div class="sawerna-checkout__container">
			<header class="sawerna-checkout__header">
				<div class="sawerna-checkout__heading">
					<h1 class="sawerna-checkout__title"><?php esc_html_e( 'Vormista tellimus', 'sawerna' ); ?></h1>
				</div>

				<div class="sawerna-checkout__steps">
					<?php sawerna_woocommerce_render_checkout_steps( 'checkout' ); ?>
				</div>
			</header>

			<form name="checkout" method="post" class="checkout woocommerce-checkout sawerna-checkout__form" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">
				<div class="sawerna-checkout__layout">
					<div class="sawerna-checkout__main">
						<?php do_action( 'woocommerce_before_checkout_form', $checkout ); ?>

						<?php if ( $checkout->get_checkout_fields() ) : ?>
							<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

							<div class="sawerna-checkout__customer-details" id="customer_details">
								<div class="sawerna-checkout__section sawerna-checkout__section--billing">
									<?php do_action( 'woocommerce_checkout_billing' ); ?>
								</div>

								<div class="sawerna-checkout__section sawerna-checkout__section--shipping">
									<?php do_action( 'woocommerce_checkout_shipping' ); ?>
								</div>
							</div>

							<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
						<?php endif; ?>
					</div>

					<aside class="sawerna-checkout__sidebar">
						<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
						<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

						<div id="order_review" class="woocommerce-checkout-review-order sawerna-checkout__review-order">
							<?php woocommerce_order_review(); ?>
							<?php woocommerce_checkout_payment(); ?>
						</div>

						<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
					</aside>
				</div>
			</form>
		</div>
	</div>
</div>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
