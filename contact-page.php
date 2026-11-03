<?php
/**
 * Template Name: Contact Page
 * Template Post Type: page
 *
 * @package Sawerna
 */

get_header();

$contact_options = sawerna_get_contact_options();
$hero_image_url  = ! empty( $contact_options['hero_image_id'] ) ? wp_get_attachment_image_url( (int) $contact_options['hero_image_id'], 'full' ) : '';
$hero_side_image = ! empty( $contact_options['hero_side_image_id'] )
	? wp_get_attachment_image(
		(int) $contact_options['hero_side_image_id'],
		'medium_large',
		false,
		array(
			'class'    => 'sawerna-contact-hero__side-image',
			'loading'  => 'eager',
			'decoding' => 'async',
			'sizes'    => '(max-width: 767px) 160px, 240px',
		)
	)
	: '';
$locations       = ! empty( $contact_options['locations'] ) && is_array( $contact_options['locations'] ) ? $contact_options['locations'] : array();
$people          = ! empty( $contact_options['people'] ) && is_array( $contact_options['people'] ) ? array_slice( $contact_options['people'], 0, 4 ) : array();
?>

<main id="primary" class="site-main sawerna-contact">
	<section class="sawerna-contact-hero"<?php echo $hero_image_url ? ' style="background-image:url(' . esc_url( $hero_image_url ) . ')"' : ''; ?>>
		<div class="sawerna-contact-hero__overlay"></div>
		<div class="sawerna-contact__container">
			<div class="sawerna-contact-hero__inner">
				<div class="sawerna-contact-hero__content">
					<?php if ( '' !== trim( (string) $contact_options['hero_subtitle'] ) ) : ?>
						<p class="sawerna-contact-hero__eyebrow"><?php echo esc_html( (string) $contact_options['hero_subtitle'] ); ?></p>
					<?php endif; ?>

					<?php if ( '' !== trim( (string) $contact_options['hero_title'] ) ) : ?>
						<h1 class="sawerna-contact-hero__title"><?php echo esc_html( (string) $contact_options['hero_title'] ); ?></h1>
					<?php endif; ?>

					<?php if ( '' !== trim( (string) $contact_options['hero_text'] ) ) : ?>
						<p class="sawerna-contact-hero__text"><?php echo nl2br( esc_html( (string) $contact_options['hero_text'] ) ); ?></p>
					<?php endif; ?>
				</div>

				<?php if ( $hero_side_image ) : ?>
					<div class="sawerna-contact-hero__side" aria-hidden="true">
						<?php echo $hero_side_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>
			<?php if ( '' !== trim( (string) get_the_content() ) ) : ?>
				<section class="sawerna-contact__intro">
					<div class="sawerna-contact__content entry-content">
						<?php
						the_content();

						wp_link_pages(
							array(
								'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'sawerna' ),
								'after'  => '</div>',
							)
						);
						?>
					</div>
				</section>
			<?php endif; ?>
		<?php endwhile; ?>
	<?php endif; ?>

	<?php if ( ! empty( $locations ) ) : ?>
		<section class="sawerna-contact__locations" aria-label="<?php esc_attr_e( 'Store locations', 'sawerna' ); ?>">
			<div class="sawerna-contact__locations-inner">
				<?php foreach ( $locations as $location ) : ?>
					<?php
					$location = wp_parse_args( is_array( $location ) ? $location : array(), sawerna_contact_location_defaults() );
					$image    = ! empty( $location['image_id'] )
						? wp_get_attachment_image(
							(int) $location['image_id'],
							'medium_large',
							false,
							array(
								'class'    => 'sawerna-contact-location__image',
								'loading'  => 'lazy',
								'decoding' => 'async',
								'sizes'    => '(max-width: 767px) 100vw, 220px',
							)
						)
						: '';
					$address_lines = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $location['address'] ) ) );
					$hour_lines    = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $location['hours'] ) ) );
					$contact_lines = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $location['contacts'] ) ) );
					$note_lines    = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $location['note'] ) ) );
					$directions_query = trim( implode( ', ', array_filter( array_merge( array( (string) $location['name'] ), $address_lines ) ) ) );
					$maps_url         = '' !== trim( (string) $location['maps_url'] ) ? (string) $location['maps_url'] : ( '' !== $directions_query ? add_query_arg( array( 'api' => '1', 'query' => $directions_query ), 'https://www.google.com/maps/search/' ) : '' );
					$waze_url         = '' !== trim( (string) $location['waze_url'] ) ? (string) $location['waze_url'] : ( '' !== $directions_query ? add_query_arg( array( 'q' => $directions_query, 'navigate' => 'yes' ), 'https://waze.com/ul' ) : '' );
					?>
					<article class="sawerna-contact-location">
						<div class="sawerna-contact-location__media">
							<?php if ( $image ) : ?>
								<?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php endif; ?>
						</div>

						<div class="sawerna-contact-location__body">
							<?php if ( '' !== trim( (string) $location['name'] ) ) : ?>
								<h2 class="sawerna-contact-location__title"><?php echo esc_html( (string) $location['name'] ); ?></h2>
							<?php endif; ?>

							<?php if ( ! empty( $address_lines ) ) : ?>
								<div class="sawerna-contact-location__group">
									<?php foreach ( $address_lines as $line ) : ?>
										<p><?php echo esc_html( $line ); ?></p>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $hour_lines ) || ! empty( $contact_lines ) ) : ?>
								<div class="sawerna-contact-location__details">
									<?php if ( ! empty( $hour_lines ) ) : ?>
										<div class="sawerna-contact-location__group">
											<?php foreach ( $hour_lines as $line ) : ?>
												<p><?php echo wp_kses_post( sawerna_contact_format_rich_text( $line ) ); ?></p>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>

									<?php if ( ! empty( $contact_lines ) ) : ?>
										<div class="sawerna-contact-location__group">
											<?php foreach ( $contact_lines as $line ) : ?>
												<p><?php echo sawerna_contact_format_contact_line( $line ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $note_lines ) ) : ?>
								<div class="sawerna-contact-location__note">
									<?php foreach ( $note_lines as $line ) : ?>
										<p><?php echo wp_kses_post( sawerna_contact_format_rich_text( $line ) ); ?></p>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>

						<div class="sawerna-contact-location__directions">
							<h3 class="sawerna-contact-location__directions-title"><?php esc_html_e( 'Kuidas meieni jõuda?', 'sawerna' ); ?></h3>

							<div class="sawerna-contact-location__buttons">
								<?php if ( '' !== trim( $maps_url ) ) : ?>
									<a class="sawerna-contact-location__button" href="<?php echo esc_url( $maps_url ); ?>" target="_blank" rel="noopener">
										<span aria-hidden="true"><?php echo sawerna_contact_icon_google_maps(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
										<span><?php esc_html_e( 'Google Maps', 'sawerna' ); ?></span>
									</a>
								<?php endif; ?>

								<?php if ( '' !== trim( $waze_url ) ) : ?>
									<a class="sawerna-contact-location__button" href="<?php echo esc_url( $waze_url ); ?>" target="_blank" rel="noopener">
										<span aria-hidden="true"><?php echo sawerna_contact_icon_waze(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
										<span><?php esc_html_e( 'Waze juhised', 'sawerna' ); ?></span>
									</a>
								<?php endif; ?>
							</div>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $people ) ) : ?>
		<section class="sawerna-contact__people" aria-label="<?php esc_attr_e( 'Contact people', 'sawerna' ); ?>">
			<div class="sawerna-contact__people-inner">
				<?php if ( '' !== trim( (string) $contact_options['people_title'] ) ) : ?>
					<h2 class="sawerna-contact-people__title"><?php echo esc_html( (string) $contact_options['people_title'] ); ?></h2>
				<?php endif; ?>

				<div class="sawerna-contact-people__grid">
					<?php foreach ( $people as $person ) : ?>
						<?php
						$person = wp_parse_args( is_array( $person ) ? $person : array(), sawerna_contact_person_defaults() );

						if ( 0 === (int) $person['image_id'] && '' === trim( (string) $person['name'] ) && '' === trim( (string) $person['role'] ) && '' === trim( (string) $person['email'] ) && '' === trim( (string) $person['phone'] ) ) {
							continue;
						}

						$person_image = ! empty( $person['image_id'] )
							? wp_get_attachment_image(
								(int) $person['image_id'],
								'medium_large',
								false,
								array(
									'class'    => 'sawerna-contact-person__image',
									'loading'  => 'lazy',
									'decoding' => 'async',
									'sizes'    => '(max-width: 767px) calc(50vw - 24px), 280px',
								)
							)
							: '';
						$person_email = sanitize_email( (string) $person['email'] );
						$person_phone = trim( (string) $person['phone'] );
						$phone_href   = preg_replace( '/[^0-9+]/', '', $person_phone );
						?>
						<article class="sawerna-contact-person">
							<div class="sawerna-contact-person__media">
								<?php if ( $person_image ) : ?>
									<?php echo $person_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<?php else : ?>
									<div class="sawerna-contact-person__placeholder" aria-hidden="true"></div>
								<?php endif; ?>
							</div>

							<?php if ( '' !== trim( (string) $person['name'] ) ) : ?>
								<h3 class="sawerna-contact-person__name"><?php echo esc_html( (string) $person['name'] ); ?></h3>
							<?php endif; ?>

							<?php if ( '' !== trim( (string) $person['role'] ) ) : ?>
								<p class="sawerna-contact-person__role"><?php echo esc_html( (string) $person['role'] ); ?></p>
							<?php endif; ?>

							<?php if ( '' !== $person_email || '' !== $person_phone ) : ?>
								<div class="sawerna-contact-person__contact">
									<?php if ( '' !== $person_email ) : ?>
										<a href="<?php echo esc_url( 'mailto:' . $person_email ); ?>"><?php echo esc_html( $person_email ); ?></a>
									<?php endif; ?>

									<?php if ( '' !== $person_phone ) : ?>
										<?php if ( '' !== $phone_href ) : ?>
											<a href="<?php echo esc_url( 'tel:' . $phone_href ); ?>"><?php echo esc_html( $person_phone ); ?></a>
										<?php else : ?>
											<span><?php echo esc_html( $person_phone ); ?></span>
										<?php endif; ?>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>
</main>

<?php
get_footer();
