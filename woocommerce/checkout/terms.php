<?php
/**
 * Checkout terms and conditions area.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/terms.php.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package Sawerna
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

if ( apply_filters( 'woocommerce_checkout_show_terms', true ) && function_exists( 'wc_terms_and_conditions_checkbox_enabled' ) ) {
	do_action( 'woocommerce_checkout_before_terms_and_conditions' );

	?>
	<div class="woocommerce-terms-and-conditions-wrapper">
		<?php
		/**
		 * @hooked wc_checkout_privacy_policy_text() - 20
		 * @hooked wc_terms_and_conditions_page_content() - 30
		 */
		do_action( 'woocommerce_checkout_terms_and_conditions' );
		?>

		<?php
		$privacy_policy_url = function_exists( 'get_privacy_policy_url' ) ? get_privacy_policy_url() : '';
		?>
		<p class="form-row validate-required sawerna-checkout-privacy-row">
			<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
				<input
					type="checkbox"
					class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox"
					name="sawerna_privacy_policy"
					id="sawerna_privacy_policy"
					value="1"
					<?php checked( isset( $_POST['sawerna_privacy_policy'] ), true ); // phpcs:ignore WordPress.Security.NonceVerification.Missing ?>
				/>
				<span>
					<?php
					if ( $privacy_policy_url ) {
						printf(
							/* translators: %s: privacy policy URL */
							wp_kses(
								__( 'Olen lugenud ja nõustun <a href="%s" target="_blank" rel="noopener noreferrer">privaatsuspoliitikaga</a>', 'sawerna' ),
								array( 'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ) )
							),
							esc_url( $privacy_policy_url )
						);
					} else {
						esc_html_e( 'Olen lugenud ja nõustun privaatsuspoliitikaga', 'sawerna' );
					}
					?>&nbsp;<abbr class="required" title="<?php esc_attr_e( 'required', 'woocommerce' ); ?>">*</abbr>
				</span>
			</label>
		</p>

		<?php if ( wc_terms_and_conditions_checkbox_enabled() ) : ?>
			<p class="form-row validate-required">
				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
					<input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="terms" <?php checked( apply_filters( 'woocommerce_terms_is_checked_default', isset( $_POST['terms'] ) ), true ); // phpcs:ignore WordPress.Security.NonceVerification.Missing ?> id="terms" />
					<span class="woocommerce-terms-and-conditions-checkbox-text"><?php wc_terms_and_conditions_checkbox_text(); ?>&nbsp;<abbr class="required" title="<?php esc_attr_e( 'required', 'woocommerce' ); ?>">*</abbr></span>
				</label>
				<input type="hidden" name="terms-field" value="1" />
			</p>
		<?php endif; ?>
	</div>
	<?php

	do_action( 'woocommerce_checkout_after_terms_and_conditions' );
}
