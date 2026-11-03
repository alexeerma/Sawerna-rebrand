<?php
/**
 * Template Name: Claims
 * Template Post Type: page
 *
 * @package Sawerna
 */

get_header();
?>

<main id="primary" class="site-main sawerna-claims-page">
	<div class="sawerna-claims-page__container">

		<?php while ( have_posts() ) : the_post(); ?>
			<?php if ( get_the_title() ) : ?>
				<h1 class="sawerna-claims-page__title"><?php the_title(); ?></h1>
			<?php endif; ?>
			<?php if ( get_the_content() ) : ?>
				<div class="sawerna-claims-page__intro entry-content">
					<?php the_content(); ?>
				</div>
			<?php endif; ?>
		<?php endwhile; ?>

		<div class="sawerna-claims-form-wrap" id="sawerna-claims-form-wrap">

			<div class="sawerna-claims-notice sawerna-claims-notice--error" id="sawerna-claims-error" hidden></div>

			<form class="sawerna-claims-form" id="sawerna-claims-form" novalidate>

				<div class="sawerna-claims-form__row">
					<label class="sawerna-claims-form__label" for="claim_name">
						<?php esc_html_e( 'Full name', 'sawerna' ); ?>
						<span class="sawerna-claims-form__required" aria-hidden="true">*</span>
					</label>
					<input
						class="sawerna-claims-form__input"
						type="text"
						id="claim_name"
						name="claim_name"
						autocomplete="name"
						required
					>
				</div>

				<div class="sawerna-claims-form__row">
					<label class="sawerna-claims-form__label" for="claim_email">
						<?php esc_html_e( 'Email address', 'sawerna' ); ?>
						<span class="sawerna-claims-form__required" aria-hidden="true">*</span>
					</label>
					<input
						class="sawerna-claims-form__input"
						type="email"
						id="claim_email"
						name="claim_email"
						autocomplete="email"
						required
					>
				</div>

				<div class="sawerna-claims-form__row sawerna-claims-form__row--half">
					<div>
						<label class="sawerna-claims-form__label" for="claim_order">
							<?php esc_html_e( 'Order number', 'sawerna' ); ?>
						</label>
						<input
							class="sawerna-claims-form__input"
							type="text"
							id="claim_order"
							name="claim_order"
							placeholder="<?php esc_attr_e( 'e.g. 12345', 'sawerna' ); ?>"
						>
					</div>
					<div>
						<label class="sawerna-claims-form__label" for="claim_product">
							<?php esc_html_e( 'Product', 'sawerna' ); ?>
						</label>
						<input
							class="sawerna-claims-form__input"
							type="text"
							id="claim_product"
							name="claim_product"
							placeholder="<?php esc_attr_e( 'Product name or description', 'sawerna' ); ?>"
						>
					</div>
				</div>

				<div class="sawerna-claims-form__row">
					<label class="sawerna-claims-form__label" for="claim_message">
						<?php esc_html_e( 'Describe the issue', 'sawerna' ); ?>
						<span class="sawerna-claims-form__required" aria-hidden="true">*</span>
					</label>
					<textarea
						class="sawerna-claims-form__textarea"
						id="claim_message"
						name="claim_message"
						rows="6"
						required
					></textarea>
				</div>

				<div class="sawerna-claims-form__row">
					<label class="sawerna-claims-form__label" for="claim_attachment">
						<?php esc_html_e( 'Attachment', 'sawerna' ); ?>
						<span class="sawerna-claims-form__label-note"><?php esc_html_e( '(optional — image or PDF, max 8 MB)', 'sawerna' ); ?></span>
					</label>
					<label class="sawerna-claims-file-label" id="sawerna-claims-file-label">
						<input
							class="sawerna-claims-file-input"
							type="file"
							id="claim_attachment"
							name="claim_attachment"
							accept="image/jpeg,image/png,image/webp,image/gif,application/pdf"
						>
						<span class="sawerna-claims-file-placeholder" id="sawerna-claims-file-placeholder">
							<?php esc_html_e( 'Choose file', 'sawerna' ); ?>
						</span>
						<span class="sawerna-claims-file-name" id="sawerna-claims-file-name" hidden></span>
						<span class="sawerna-claims-file-clear" id="sawerna-claims-file-clear" hidden aria-label="<?php esc_attr_e( 'Remove file', 'sawerna' ); ?>">&times;</span>
					</label>
				</div>

				<div class="sawerna-claims-form__footer">
					<p class="sawerna-claims-form__required-note">
						<span aria-hidden="true">*</span> <?php esc_html_e( 'Required fields', 'sawerna' ); ?>
					</p>
					<button class="sawerna-claims-form__submit" type="submit" id="sawerna-claims-submit">
						<?php esc_html_e( 'Submit claim', 'sawerna' ); ?>
					</button>
				</div>

			</form>

			<div class="sawerna-claims-success" id="sawerna-claims-success" hidden>
				<div class="sawerna-claims-success__icon" aria-hidden="true">&#10003;</div>
				<p class="sawerna-claims-success__message" id="sawerna-claims-success-message"></p>
			</div>

		</div>

	</div>
</main>

<?php get_footer(); ?>
