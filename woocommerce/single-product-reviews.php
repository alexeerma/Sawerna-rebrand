<?php
/**
 * Display single product reviews (comments) — Sawerna themed override.
 *
 * Overrides WooCommerce templates/single-product-reviews.php.
 * Uses themed markup and a CSS-only (radio based) rating widget so the
 * "Your rating" control does not depend on WooCommerce's frontend JS.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package Sawerna
 * @version 11.2.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! comments_open() ) {
	return;
}

$review_count = $product->get_review_count();
?>
<div id="reviews" class="woocommerce-Reviews sawerna-reviews">
	<div id="comments" class="sawerna-reviews__list-wrap">
		<h2 class="woocommerce-Reviews-title sawerna-reviews__title">
			<?php
			if ( $review_count && wc_review_ratings_enabled() ) {
				/* translators: 1: reviews count 2: product name */
				$reviews_title = sprintf( esc_html( _n( '%1$s arvustus tootele %2$s', '%1$s arvustust tootele %2$s', $review_count, 'sawerna' ) ), esc_html( (string) $review_count ), '<span>' . wp_kses_post( get_the_title() ) . '</span>' );
				/**
				 * Filters the heading shown above the product review list.
				 *
				 * @since 3.6.0
				 *
				 * @param string     $reviews_title Review count heading. The count is escaped, the product title is run through wp_kses_post() and wrapped in a span.
				 * @param int        $count         Number of reviews for the product.
				 * @param WC_Product $product       Product the reviews belong to.
				 */
				echo apply_filters( 'woocommerce_reviews_title', $reviews_title, $review_count, $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The base heading is sanitised above and the filter returns extension-controlled markup by design.
			} else {
				esc_html_e( 'Arvustused', 'sawerna' );
			}
			?>
		</h2>

		<?php if ( have_comments() ) : ?>
			<ol class="commentlist sawerna-reviews__items">
				<?php wp_list_comments( apply_filters( 'woocommerce_product_review_list_args', array( 'callback' => 'woocommerce_comments' ) ) ); ?>
			</ol>

			<?php
			if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
				echo '<nav class="woocommerce-pagination sawerna-reviews__pagination">';
				paginate_comments_links(
					apply_filters(
						'woocommerce_comment_pagination_args',
						array(
							'prev_text' => is_rtl() ? '&rarr;' : '&larr;',
							'next_text' => is_rtl() ? '&larr;' : '&rarr;',
							'type'      => 'list',
						)
					)
				);
				echo '</nav>';
			endif;
			?>
		<?php else : ?>
			<p class="woocommerce-noreviews sawerna-reviews__empty"><?php esc_html_e( 'Sellel tootel pole veel arvustusi.', 'sawerna' ); ?></p>
		<?php endif; ?>
	</div>

	<?php if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', get_current_user_id(), $product->get_id() ) ) : ?>
		<div id="review_form_wrapper" class="sawerna-reviews__form-wrap">
			<div id="review_form">
				<?php
				$commenter    = wp_get_current_commenter();
				$comment_form = array(
					/* translators: %s is product title */
					'title_reply'         => have_comments() ? esc_html__( 'Lisa arvustus', 'sawerna' ) : sprintf( esc_html__( 'Ole esimene, kes arvustab toodet &ldquo;%s&rdquo;', 'sawerna' ), esc_html( get_the_title() ) ),
					/* translators: %s is product title */
					'title_reply_to'      => esc_html__( 'Vasta kasutajale %s', 'sawerna' ),
					'title_reply_before'  => '<span id="reply-title" class="comment-reply-title sawerna-reviews__form-title" role="heading" aria-level="3">',
					'title_reply_after'   => '</span>',
					'comment_notes_after' => '',
					'label_submit'        => esc_html__( 'Postita', 'sawerna' ),
					'class_submit'        => 'sawerna-reviews__submit',
					'logged_in_as'        => '',
					'comment_field'       => '',
				);

				$name_email_required = (bool) get_option( 'require_name_email', 1 );
				$fields              = array(
					'author' => array(
						'label'        => __( 'Nimi', 'sawerna' ),
						'type'         => 'text',
						'value'        => $commenter['comment_author'],
						'required'     => $name_email_required,
						'autocomplete' => 'name',
					),
					'email'  => array(
						'label'        => __( 'E-post', 'sawerna' ),
						'type'         => 'email',
						'value'        => $commenter['comment_author_email'],
						'required'     => $name_email_required,
						'autocomplete' => 'email',
					),
				);

				$comment_form['fields'] = array();

				foreach ( $fields as $key => $field ) {
					$field_html  = '<p class="comment-form-' . esc_attr( $key ) . ' sawerna-reviews__field">';
					$field_html .= '<label for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] );

					if ( $field['required'] ) {
						$field_html .= '&nbsp;<span class="required">*</span>';
					}

					$field_html .= '</label><input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="' . esc_attr( $field['type'] ) . '" autocomplete="' . esc_attr( $field['autocomplete'] ) . '" value="' . esc_attr( $field['value'] ) . '" size="30" ' . ( $field['required'] ? 'required' : '' ) . ' /></p>';

					$comment_form['fields'][ $key ] = $field_html;
				}

				$account_page_url = wc_get_page_permalink( 'myaccount' );
				if ( $account_page_url ) {
					/* translators: %1$s opening and %2$s closing link tags respectively */
					$comment_form['must_log_in'] = '<p class="must-log-in sawerna-reviews__must-login">' . sprintf( esc_html__( 'Arvustuse lisamiseks pead olema %1$ssisse logitud%2$s.', 'sawerna' ), '<a href="' . esc_url( $account_page_url ) . '">', '</a>' ) . '</p>';
				}

				if ( wc_review_ratings_enabled() ) {
					$rating_required = wc_review_ratings_required();
					$star_options    = array(
						5 => esc_html__( 'Suurepärane', 'sawerna' ),
						4 => esc_html__( 'Hea', 'sawerna' ),
						3 => esc_html__( 'Keskmine', 'sawerna' ),
						2 => esc_html__( 'Kesine', 'sawerna' ),
						1 => esc_html__( 'Väga halb', 'sawerna' ),
					);

					// Implicit labels (input nested in label) so the widget needs no IDs.
					// This tab's HTML is rendered in both the desktop tab and the mobile
					// accordion, so ID-based label association would collide.
					$rating_html  = '<div class="comment-form-rating sawerna-reviews__rating">';
					$rating_html .= '<span class="sawerna-reviews__rating-label" id="comment-form-rating-label">' . esc_html__( 'Sinu hinnang', 'sawerna' ) . ( $rating_required ? '&nbsp;<span class="required">*</span>' : '' ) . '</span>';
					$rating_html .= '<span class="sawerna-reviews__stars" role="radiogroup" aria-labelledby="comment-form-rating-label">';

					foreach ( $star_options as $value => $star_label ) {
						$rating_html .= '<label class="sawerna-reviews__star" title="' . esc_attr( $star_label ) . '">';
						$rating_html .= '<input type="radio" name="rating" value="' . esc_attr( (string) $value ) . '"' . ( $rating_required ? ' required' : '' ) . ' />';
						$rating_html .= '<span class="screen-reader-text">' . esc_html( $star_label ) . '</span>';
						$rating_html .= '</label>';
					}

					$rating_html .= '</span></div>';

					$comment_form['comment_field'] = $rating_html;
				}

				$comment_form['comment_field'] .= '<p class="comment-form-comment sawerna-reviews__field sawerna-reviews__field--comment"><label for="comment">' . esc_html__( 'Sinu arvustus', 'sawerna' ) . '&nbsp;<span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="8" required></textarea></p>';

				comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );
				?>
			</div>
		</div>
	<?php else : ?>
		<p class="woocommerce-verification-required sawerna-reviews__notice"><?php esc_html_e( 'Arvustuse saavad jätta ainult sisse logitud kliendid, kes on toote ostnud.', 'sawerna' ); ?></p>
	<?php endif; ?>

	<div class="clear"></div>
</div>
