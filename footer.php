<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Sawerna
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$theme_uri = get_template_directory_uri();

$footer_slider_items = sawerna_get_footer_slider_items();

$footer_contacts = sawerna_get_footer_contacts();

$footer_socials = array(
	array(
		'label' => __( 'Facebook', 'sawerna' ),
		'url'   => get_theme_mod( 'rm_footer_social_facebook', 'https://facebook.com/' ),
		'icon'  => $theme_uri . '/assets/SVG/fb.svg',
	),
	array(
		'label' => __( 'Instagram', 'sawerna' ),
		'url'   => get_theme_mod( 'rm_footer_social_instagram', 'https://instagram.com/' ),
		'icon'  => $theme_uri . '/assets/SVG/insta.svg',
	),
	array(
		'label' => __( 'TikTok', 'sawerna' ),
		'url'   => get_theme_mod( 'rm_footer_social_tiktok', 'https://tiktok.com/' ),
		'icon'  => $theme_uri . '/assets/SVG/tiktok.svg',
	),
);

$footer_year        = date_i18n( 'Y' );
$footer_bottom_text = (string) get_theme_mod(
	'rm_footer_bottom_text',
	/* translators: %s: current year. */
	sprintf( __( '© %s Sawerna. Veebiarendus: Nimmi. Tippasemel e-poe lahendused ettevõtetele.', 'sawerna' ), $footer_year )
);
$fallback_copyright = (string) get_theme_mod( 'rm_footer_copyright', '' );
$fallback_developer = (string) get_theme_mod( 'rm_footer_developer_text', '' );
if ( trim( $footer_bottom_text ) === '' && ( trim( $fallback_copyright ) !== '' || trim( $fallback_developer ) !== '' ) ) {
	$footer_bottom_text = trim( $fallback_copyright . ' ' . $fallback_developer );
}
$has_footer_bottom = trim( $footer_bottom_text ) !== '';

// Newsletter texts: settings from sawerna-core when available, hard-coded fallbacks otherwise.
$newsletter_defaults = array(
	'enabled'         => true,
	'title'           => __( 'Põnevad sisustusideed ja parimad pakkumised Sinu postkastis', 'sawerna' ),
	'text'            => __( 'Liitu Sawerna uudiskirjaga, et olla kursis värskete uudiste, sisustusideede ja parimate pakkumistega. Liitudes saad -10% oma järgmiselt e-poe ostult.', 'sawerna' ),
	'terms_page_id'   => 0,
	'terms_link_text' => __( 'Tutvu sooduspakkumiste tingimustega.', 'sawerna' ),
	'placeholder'     => __( 'Sisestage oma e-posti aadress', 'sawerna' ),
	'button_text'     => __( 'LIITU', 'sawerna' ),
	'consent_mode'    => 'text',
	'consent_text'    => '',
);
$newsletter          = $newsletter_defaults;
if ( function_exists( 'sawerna_newsletter_get_settings' ) ) {
	$newsletter_settings = sawerna_newsletter_get_settings();
	if ( is_array( $newsletter_settings ) ) {
		$newsletter = array_merge( $newsletter_defaults, $newsletter_settings );
	}
}
foreach ( array( 'title', 'text', 'terms_link_text', 'placeholder', 'button_text', 'consent_text' ) as $newsletter_key ) {
	$newsletter[ $newsletter_key ] = trim( (string) $newsletter[ $newsletter_key ] );
}
$newsletter['consent_mode'] = 'checkbox' === $newsletter['consent_mode'] ? 'checkbox' : 'text';
$newsletter_enabled         = ! empty( $newsletter['enabled'] );
$newsletter_terms_id        = absint( $newsletter['terms_page_id'] );
$newsletter_terms_url       = $newsletter_terms_id && 'publish' === get_post_status( $newsletter_terms_id ) ? (string) get_permalink( $newsletter_terms_id ) : '';
if ( 'checkbox' === $newsletter['consent_mode'] && '' === $newsletter['consent_text'] ) {
	$newsletter['consent_text'] = __( 'Nõustun Sawerna uudiskirja saamisega.', 'sawerna' );
}
?>
<footer class="rm-footer" role="contentinfo">
	<?php if ( $newsletter_enabled ) : ?>
	<div class="rm-footer__newsletter">
		<div class="rm-footer__sectionInner">
			<div class="rm-footer__newsletterGrid">
				<div class="rm-footer__newsletterContent">
					<h2 class="rm-footer__newsletterTitle">
						<?php echo esc_html( $newsletter['title'] ); ?>
					</h2>
					<p class="rm-footer__newsletterText">
						<?php echo esc_html( $newsletter['text'] ); ?>
					</p>
					<?php if ( '' !== $newsletter_terms_url && '' !== $newsletter['terms_link_text'] ) : ?>
					<a class="rm-footer__newsletterLink" href="<?php echo esc_url( $newsletter_terms_url ); ?>">
						<?php echo esc_html( $newsletter['terms_link_text'] ); ?>
					</a>
					<?php endif; ?>
				</div>
				<?php
				/*
				 * Page-cache note: the nonce and "started" timestamp are printed into the HTML. An old "started"
				 * is accepted, but on a cached page the nonce expires after 12-24h and the back end then answers 403
				 * ("Leht on aegunud..."); newsletter.js shows that message. Keep cache TTL below 12h if this matters.
				 */
				?>
				<form class="rm-footer__newsletterForm" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" data-sawerna-newsletter>
					<label class="rm-footer__newsletterLabel" for="rm-footer-newsletter-email">
						<?php echo esc_html( $newsletter['placeholder'] ); ?>
					</label>
					<input id="rm-footer-newsletter-email" type="email" name="email" autocomplete="email"
						placeholder="<?php echo esc_attr( $newsletter['placeholder'] ); ?>" required
						<?php echo 'text' === $newsletter['consent_mode'] && '' !== $newsletter['consent_text'] ? 'aria-describedby="rm-footer-newsletter-consent"' : ''; ?>>
					<div class="rm-footer__newsletterHp" aria-hidden="true">
						<label for="rm-footer-newsletter-website"><?php echo esc_html__( 'Veebisait', 'sawerna' ); ?></label>
						<input id="rm-footer-newsletter-website" type="text" name="website" value="" tabindex="-1" autocomplete="off">
					</div>
					<input type="hidden" name="action" value="sawerna_newsletter_subscribe">
					<input type="hidden" name="started" value="<?php echo esc_attr( (string) time() ); ?>">
					<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'sawerna_newsletter' ) ); ?>">
					<?php $sawerna_newsletter_lang = apply_filters( 'wpml_current_language', null ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Third-party WPML hook. ?>
					<?php if ( is_string( $sawerna_newsletter_lang ) && '' !== $sawerna_newsletter_lang && 'all' !== $sawerna_newsletter_lang ) : ?>
					<input type="hidden" name="lang" value="<?php echo esc_attr( $sawerna_newsletter_lang ); ?>">
					<?php endif; ?>
					<?php if ( 'checkbox' === $newsletter['consent_mode'] ) : ?>
					<div class="rm-footer__newsletterConsent rm-footer__newsletterConsent--checkbox">
						<input id="rm-footer-newsletter-consent" type="checkbox" name="consent" value="1" required>
						<label for="rm-footer-newsletter-consent"><?php echo esc_html( $newsletter['consent_text'] ); ?></label>
					</div>
					<?php endif; ?>
					<button type="submit"><?php echo esc_html( $newsletter['button_text'] ); ?></button>
					<?php if ( 'text' === $newsletter['consent_mode'] && '' !== $newsletter['consent_text'] ) : ?>
					<p id="rm-footer-newsletter-consent" class="rm-footer__newsletterConsent"><?php echo esc_html( $newsletter['consent_text'] ); ?></p>
					<?php endif; ?>
					<p id="rm-footer-newsletter-status" class="rm-footer__newsletterStatus" role="status" aria-live="polite"></p>
				</form>
			</div>
		</div>
	</div>
	<?php endif; ?>
	<div class="rm-footer__logoRow">
		<div class="rm-footer__sectionInner">
			<div class="rm-footer__logoHeader">
				<p class="rm-footer__logoIntro">
					<?php echo esc_html__( 'Esindame ka tuntud ja kvaliteetseid kaubamärke', 'sawerna' ); ?>
				</p>
				<div class="rm-footer__sliderArrows rm-footer__sliderArrows--mobile">
					<button class="rm-footer__sliderArrow rm-footer__sliderArrow--prev" type="button"
						aria-label="<?php echo esc_attr__( 'Eelmine', 'sawerna' ); ?>">
						<img src="<?php echo esc_url( $theme_uri . '/assets/SVG/Asset 27.svg' ); ?>" alt="">
					</button>
					<button class="rm-footer__sliderArrow rm-footer__sliderArrow--next" type="button"
						aria-label="<?php echo esc_attr__( 'Järgmine', 'sawerna' ); ?>">
						<img src="<?php echo esc_url( $theme_uri . '/assets/SVG/Asset 26.svg' ); ?>" alt="">
					</button>
				</div>
			</div>
			<div class="rm-footer__logoSlider" aria-label="<?php echo esc_attr__( 'Partnerlogod', 'sawerna' ); ?>">
				<button class="rm-footer__sliderArrow rm-footer__sliderArrow--prev rm-footer__sliderArrow--desktop" type="button"
					aria-label="<?php echo esc_attr__( 'Eelmine', 'sawerna' ); ?>">
					<img src="<?php echo esc_url( $theme_uri . '/assets/SVG/Asset 27.svg' ); ?>" alt="">
				</button>
				<div class="rm-footer__logoViewport">
				<div class="rm-footer__logoTrack">
					<div class="rm-footer__logoGroup">
						<?php foreach ( $footer_slider_items as $item ) : ?>
							<?php
							$label = $item['label'] ?? '';
							$image = $item['image'] ?? '';
							$url   = $item['url'] ?? '';
							if ( '' === $label && '' === $image ) {
								continue;
							}
							?>
							<?php if ( ! empty( $url ) ) : ?>
								<a class="rm-footer__logoItem" href="<?php echo esc_url( $url ); ?>">
							<?php else : ?>
								<span class="rm-footer__logoItem">
							<?php endif; ?>
								<?php if ( ! empty( $image ) ) : ?>
									<img class="rm-footer__logoImage" src="<?php echo esc_url( $image ); ?>"
										alt="<?php echo esc_attr( '' !== $label ? $label : __( 'Logo', 'sawerna' ) ); ?>">
								<?php else : ?>
									<?php echo esc_html( $label ); ?>
								<?php endif; ?>
							<?php if ( ! empty( $url ) ) : ?>
								</a>
							<?php else : ?>
								</span>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
					<div class="rm-footer__logoGroup" aria-hidden="true">
						<?php foreach ( $footer_slider_items as $item ) : ?>
							<?php
							$label = $item['label'] ?? '';
							$image = $item['image'] ?? '';
							$url   = $item['url'] ?? '';
							if ( '' === $label && '' === $image ) {
								continue;
							}
							?>
							<?php if ( ! empty( $url ) ) : ?>
								<a class="rm-footer__logoItem" href="<?php echo esc_url( $url ); ?>">
							<?php else : ?>
								<span class="rm-footer__logoItem">
							<?php endif; ?>
								<?php if ( ! empty( $image ) ) : ?>
									<img class="rm-footer__logoImage" src="<?php echo esc_url( $image ); ?>"
										alt="<?php echo esc_attr( '' !== $label ? $label : __( 'Logo', 'sawerna' ) ); ?>">
								<?php else : ?>
									<?php echo esc_html( $label ); ?>
								<?php endif; ?>
							<?php if ( ! empty( $url ) ) : ?>
								</a>
							<?php else : ?>
								</span>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</div>
				</div>
				<button class="rm-footer__sliderArrow rm-footer__sliderArrow--next rm-footer__sliderArrow--desktop" type="button"
					aria-label="<?php echo esc_attr__( 'Järgmine', 'sawerna' ); ?>">
					<img src="<?php echo esc_url( $theme_uri . '/assets/SVG/Asset 26.svg' ); ?>" alt="">
				</button>
			</div>
		</div>
	</div>

	<div class="rm-footer__contacts rm-footer__accordionItem">
		<div class="rm-footer__sectionInner">
			<h2 class="rm-footer__sectionTitle">
				<button class="rm-footer__accordionToggle" type="button" aria-expanded="false" aria-controls="rm-footer-contacts">
					<span class="rm-footer__accordionLabel"><?php echo esc_html__( 'Kontaktid', 'sawerna' ); ?></span>
					<span class="rm-footer__accordionIcon" aria-hidden="true"></span>
				</button>
			</h2>
			<div class="rm-footer__accordionPanel" id="rm-footer-contacts">
				<div class="rm-footer__contactGrid">
					<?php foreach ( $footer_contacts as $contact ) : ?>
						<div class="rm-footer__contactCol">
							<h3 class="rm-footer__contactTitle"><?php echo esc_html( $contact['title'] ); ?></h3>
							<?php foreach ( $contact['lines'] as $line ) : ?>
								<p class="rm-footer__contactLine"><?php echo esc_html( $line ); ?></p>
							<?php endforeach; ?>
							<?php if ( ! empty( $contact['phone'] ) ) : ?>
								<a class="rm-footer__contactLink"
									href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9\+]/', '', (string) $contact['phone'] ) ); ?>">
									<?php echo esc_html( $contact['phone'] ); ?>
								</a>
							<?php endif; ?>
							<?php if ( ! empty( $contact['email'] ) ) : ?>
								<a class="rm-footer__contactLink"
									href="<?php echo esc_url( 'mailto:' . sanitize_email( (string) $contact['email'] ) ); ?>">
									<?php echo esc_html( $contact['email'] ); ?>
								</a>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>

	<div class="rm-footer__menus">
		<div class="rm-footer__sectionInner">
			<div class="rm-footer__menuGrid">
				<div class="rm-footer__menuCol rm-footer__accordionItem">
					<h3 class="rm-footer__menuTitle">
						<button class="rm-footer__accordionToggle" type="button" aria-expanded="false"
							aria-controls="rm-footer-general">
							<span class="rm-footer__accordionLabel"><?php echo esc_html__( 'Üldinfo', 'sawerna' ); ?></span>
							<span class="rm-footer__accordionIcon" aria-hidden="true"></span>
						</button>
					</h3>
					<div class="rm-footer__accordionPanel" id="rm-footer-general">
						<?php
						wp_nav_menu(
							array(
								'theme_location' => 'footer_general_info',
								'container'      => false,
								'menu_class'     => 'rm-footer__menu',
								'fallback_cb'    => false,
								'depth'          => 1,
							)
						);
						?>
						<div class="rm-footer__socialBlock rm-footer__desktopOnly">
							<p class="rm-footer__socialTitle"><?php echo esc_html__( 'Jälgi meie tegemisi', 'sawerna' ); ?></p>
							<div class="rm-footer__socialLinks">
								<?php foreach ( $footer_socials as $social ) : ?>
									<?php if ( ! empty( $social['url'] ) ) : ?>
										<a class="rm-footer__socialLink" href="<?php echo esc_url( $social['url'] ); ?>" target="_blank"
											rel="noopener noreferrer" aria-label="<?php echo esc_attr( $social['label'] ); ?>">
											<img src="<?php echo esc_url( $social['icon'] ); ?>" alt="">
										</a>
									<?php endif; ?>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				</div>

				<div class="rm-footer__menuCol rm-footer__accordionItem">
					<h3 class="rm-footer__menuTitle">
						<button class="rm-footer__accordionToggle" type="button" aria-expanded="false"
							aria-controls="rm-footer-client">
							<span class="rm-footer__accordionLabel"><?php echo esc_html__( 'Kliendile', 'sawerna' ); ?></span>
							<span class="rm-footer__accordionIcon" aria-hidden="true"></span>
						</button>
					</h3>
					<div class="rm-footer__accordionPanel" id="rm-footer-client">
						<?php
						wp_nav_menu(
							array(
								'theme_location' => 'footer_client_info',
								'container'      => false,
								'menu_class'     => 'rm-footer__menu',
								'fallback_cb'    => false,
								'depth'          => 1,
							)
						);
						?>
					</div>
				</div>

				<div class="rm-footer__menuCol rm-footer__accordionItem">
					<h3 class="rm-footer__menuTitle">
						<button class="rm-footer__accordionToggle" type="button" aria-expanded="false"
							aria-controls="rm-footer-services">
							<span class="rm-footer__accordionLabel"><?php echo esc_html__( 'Teenused', 'sawerna' ); ?></span>
							<span class="rm-footer__accordionIcon" aria-hidden="true"></span>
						</button>
					</h3>
					<div class="rm-footer__accordionPanel" id="rm-footer-services">
						<?php
						wp_nav_menu(
							array(
								'theme_location' => 'footer_services_info',
								'container'      => false,
								'menu_class'     => 'rm-footer__menu',
								'fallback_cb'    => false,
								'depth'          => 1,
							)
						);
						?>
					</div>
				</div>

				<div class="rm-footer__menuCol rm-footer__company rm-footer__accordionItem">
					<h3 class="rm-footer__menuTitle">
						<button class="rm-footer__accordionToggle" type="button" aria-expanded="false"
							aria-controls="rm-footer-company">
							<span class="rm-footer__accordionLabel"><?php echo esc_html__( 'Ettevõttest', 'sawerna' ); ?></span>
							<span class="rm-footer__accordionIcon" aria-hidden="true"></span>
						</button>
					</h3>
					<div class="rm-footer__accordionPanel" id="rm-footer-company">
						<p class="rm-footer__companyHeading"><?php echo esc_html__( 'Sawerna OÜ', 'sawerna' ); ?></p>
						<p class="rm-footer__companyLine">Registrikood: 14953904</p>
						<p class="rm-footer__companyLine">KMKR: EE102305235</p>
						<p class="rm-footer__companyLine">Hannula, Savarna küla, Kanepi vald,</p>
						<p class="rm-footer__companyLine">63418 Põlvamaa, Eesti</p>
						<img class="rm-footer__badge rm-footer__desktopOnly"
							src="<?php echo esc_url( $theme_uri . '/assets/SVG/Asset 25.svg' ); ?>"
							alt="<?php echo esc_attr__( 'Usalduspartner', 'sawerna' ); ?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="rm-footer__mobileExtras">
		<div class="rm-footer__sectionInner">
			<div class="rm-footer__mobileExtrasRow">
				<div class="rm-footer__socialBlock">
					<p class="rm-footer__socialTitle"><?php echo esc_html__( 'Jälgi meie tegemisi', 'sawerna' ); ?></p>
					<div class="rm-footer__socialLinks">
						<?php foreach ( $footer_socials as $social ) : ?>
							<?php if ( ! empty( $social['url'] ) ) : ?>
								<a class="rm-footer__socialLink" href="<?php echo esc_url( $social['url'] ); ?>" target="_blank"
									rel="noopener noreferrer" aria-label="<?php echo esc_attr( $social['label'] ); ?>">
									<img src="<?php echo esc_url( $social['icon'] ); ?>" alt="">
								</a>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</div>
				<img class="rm-footer__badge" src="<?php echo esc_url( $theme_uri . '/assets/SVG/Asset 25.svg' ); ?>"
					alt="<?php echo esc_attr__( 'Usalduspartner', 'sawerna' ); ?>">
			</div>
		</div>
	</div>
	<?php if ( $has_footer_bottom ) : ?>
		<div class="rm-footer__bottom">
			<div class="rm-footer__sectionInner">
				<div class="rm-footer__bottomRow">
					<p class="rm-footer__bottomText"><?php echo wp_kses_post( $footer_bottom_text ); ?></p>
				</div>
			</div>
		</div>
	<?php endif; ?>
</footer>

<?php wp_footer(); ?>
</body>

</html>