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

if (!defined('ABSPATH')) {
	exit;
}

$theme_uri = get_template_directory_uri();

$footer_slider_items = sawerna_get_footer_slider_items();

$footer_contacts = sawerna_get_footer_contacts();

$footer_socials = [
	[
		'label' => __('Facebook', 'sawerna'),
		'url' => get_theme_mod('rm_footer_social_facebook', 'https://facebook.com/'),
		'icon' => $theme_uri . '/assets/SVG/fb.svg',
	],
	[
		'label' => __('Instagram', 'sawerna'),
		'url' => get_theme_mod('rm_footer_social_instagram', 'https://instagram.com/'),
		'icon' => $theme_uri . '/assets/SVG/insta.svg',
	],
	[
		'label' => __('TikTok', 'sawerna'),
		'url' => get_theme_mod('rm_footer_social_tiktok', 'https://tiktok.com/'),
		'icon' => $theme_uri . '/assets/SVG/tiktok.svg',
	],
];

$footer_year = date_i18n('Y');
$footer_bottom_text = get_theme_mod(
	'rm_footer_bottom_text',
	sprintf(__('© %s Sawerna. Veebiarendus: Nimmi. Tippasemel e-poe lahendused ettevõtetele.', 'sawerna'), $footer_year)
);
$fallback_copyright = get_theme_mod('rm_footer_copyright', '');
$fallback_developer = get_theme_mod('rm_footer_developer_text', '');
if (trim($footer_bottom_text) === '' && (trim($fallback_copyright) !== '' || trim($fallback_developer) !== '')) {
	$footer_bottom_text = trim($fallback_copyright . ' ' . $fallback_developer);
}
$has_footer_bottom = trim($footer_bottom_text) !== '';
?>
<footer class="rm-footer" role="contentinfo">
	<div class="rm-footer__newsletter">
		<div class="rm-footer__sectionInner">
			<div class="rm-footer__newsletterGrid">
				<div class="rm-footer__newsletterContent">
					<h2 class="rm-footer__newsletterTitle">
						<?php echo esc_html__('Põnevad sisustusideed ja parimad pakkumised Sinu postkastis', 'sawerna'); ?>
					</h2>
					<p class="rm-footer__newsletterText">
						<?php
						echo esc_html__(
							'Liitu Sawerna uudiskirjaga, et olla kursis värskete uudiste, sisustusideede ja parimate pakkumistega. Liitudes saad -10% oma järgmiselt e-poe ostult.',
							'sawerna'
						);
						?>
					</p>
					<a class="rm-footer__newsletterLink" href="#">
						<?php echo esc_html__('Tutvu sooduspakkumiste tingimustega.', 'sawerna'); ?>
					</a>
				</div>
				<form class="rm-footer__newsletterForm" action="#" method="post">
					<label class="rm-footer__newsletterLabel" for="rm-footer-newsletter-email">
						<?php echo esc_html__('Sisestage oma e-posti aadress', 'sawerna'); ?>
					</label>
					<input id="rm-footer-newsletter-email" type="email" name="newsletter_email"
						placeholder="<?php echo esc_attr__('Sisestage oma e-posti aadress', 'sawerna'); ?>" required>
					<button type="submit"><?php echo esc_html__('LIITU', 'sawerna'); ?></button>
				</form>
			</div>
		</div>
	</div>
	<div class="rm-footer__logoRow">
		<div class="rm-footer__sectionInner">
			<div class="rm-footer__logoHeader">
				<p class="rm-footer__logoIntro">
					<?php echo esc_html__('Esindame ka tuntud ja kvaliteetseid kaubamärke', 'sawerna'); ?>
				</p>
				<div class="rm-footer__sliderArrows rm-footer__sliderArrows--mobile">
					<button class="rm-footer__sliderArrow rm-footer__sliderArrow--prev" type="button"
						aria-label="<?php echo esc_attr__('Eelmine', 'sawerna'); ?>">
						<img src="<?php echo esc_url($theme_uri . '/assets/SVG/Asset 27.svg'); ?>" alt="">
					</button>
					<button class="rm-footer__sliderArrow rm-footer__sliderArrow--next" type="button"
						aria-label="<?php echo esc_attr__('Järgmine', 'sawerna'); ?>">
						<img src="<?php echo esc_url($theme_uri . '/assets/SVG/Asset 26.svg'); ?>" alt="">
					</button>
				</div>
			</div>
			<div class="rm-footer__logoSlider" aria-label="<?php echo esc_attr__('Partnerlogod', 'sawerna'); ?>">
				<button class="rm-footer__sliderArrow rm-footer__sliderArrow--prev rm-footer__sliderArrow--desktop" type="button"
					aria-label="<?php echo esc_attr__('Eelmine', 'sawerna'); ?>">
					<img src="<?php echo esc_url($theme_uri . '/assets/SVG/Asset 27.svg'); ?>" alt="">
				</button>
				<div class="rm-footer__logoViewport">
				<div class="rm-footer__logoTrack">
					<div class="rm-footer__logoGroup">
						<?php foreach ($footer_slider_items as $item): ?>
							<?php
							$label = $item['label'] ?? '';
							$image = $item['image'] ?? '';
							$url = $item['url'] ?? '';
							if ($label === '' && $image === '') {
								continue;
							}
							?>
							<?php if (!empty($url)): ?>
								<a class="rm-footer__logoItem" href="<?php echo esc_url($url); ?>">
							<?php else: ?>
								<span class="rm-footer__logoItem">
							<?php endif; ?>
								<?php if (!empty($image)): ?>
									<img class="rm-footer__logoImage" src="<?php echo esc_url($image); ?>"
										alt="<?php echo esc_attr($label !== '' ? $label : __('Logo', 'sawerna')); ?>">
								<?php else: ?>
									<?php echo esc_html($label); ?>
								<?php endif; ?>
							<?php if (!empty($url)): ?>
								</a>
							<?php else: ?>
								</span>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
					<div class="rm-footer__logoGroup" aria-hidden="true">
						<?php foreach ($footer_slider_items as $item): ?>
							<?php
							$label = $item['label'] ?? '';
							$image = $item['image'] ?? '';
							$url = $item['url'] ?? '';
							if ($label === '' && $image === '') {
								continue;
							}
							?>
							<?php if (!empty($url)): ?>
								<a class="rm-footer__logoItem" href="<?php echo esc_url($url); ?>">
							<?php else: ?>
								<span class="rm-footer__logoItem">
							<?php endif; ?>
								<?php if (!empty($image)): ?>
									<img class="rm-footer__logoImage" src="<?php echo esc_url($image); ?>"
										alt="<?php echo esc_attr($label !== '' ? $label : __('Logo', 'sawerna')); ?>">
								<?php else: ?>
									<?php echo esc_html($label); ?>
								<?php endif; ?>
							<?php if (!empty($url)): ?>
								</a>
							<?php else: ?>
								</span>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</div>
				</div>
				<button class="rm-footer__sliderArrow rm-footer__sliderArrow--next rm-footer__sliderArrow--desktop" type="button"
					aria-label="<?php echo esc_attr__('Järgmine', 'sawerna'); ?>">
					<img src="<?php echo esc_url($theme_uri . '/assets/SVG/Asset 26.svg'); ?>" alt="">
				</button>
			</div>
		</div>
	</div>

	<div class="rm-footer__contacts rm-footer__accordionItem">
		<div class="rm-footer__sectionInner">
			<h2 class="rm-footer__sectionTitle">
				<button class="rm-footer__accordionToggle" type="button" aria-expanded="false" aria-controls="rm-footer-contacts">
					<span class="rm-footer__accordionLabel"><?php echo esc_html__('Kontaktid', 'sawerna'); ?></span>
					<span class="rm-footer__accordionIcon" aria-hidden="true"></span>
				</button>
			</h2>
			<div class="rm-footer__accordionPanel" id="rm-footer-contacts">
				<div class="rm-footer__contactGrid">
					<?php foreach ($footer_contacts as $contact): ?>
						<div class="rm-footer__contactCol">
							<h3 class="rm-footer__contactTitle"><?php echo esc_html($contact['title']); ?></h3>
							<?php foreach ($contact['lines'] as $line): ?>
								<p class="rm-footer__contactLine"><?php echo esc_html($line); ?></p>
							<?php endforeach; ?>
							<?php if (!empty($contact['phone'])): ?>
								<a class="rm-footer__contactLink"
									href="<?php echo esc_url('tel:' . preg_replace('/[^0-9\+]/', '', (string) $contact['phone'])); ?>">
									<?php echo esc_html($contact['phone']); ?>
								</a>
							<?php endif; ?>
							<?php if (!empty($contact['email'])): ?>
								<a class="rm-footer__contactLink"
									href="<?php echo esc_url('mailto:' . sanitize_email((string) $contact['email'])); ?>">
									<?php echo esc_html($contact['email']); ?>
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
							<span class="rm-footer__accordionLabel"><?php echo esc_html__('Üldinfo', 'sawerna'); ?></span>
							<span class="rm-footer__accordionIcon" aria-hidden="true"></span>
						</button>
					</h3>
					<div class="rm-footer__accordionPanel" id="rm-footer-general">
						<?php
						wp_nav_menu([
							'theme_location' => 'footer_general_info',
							'container' => false,
							'menu_class' => 'rm-footer__menu',
							'fallback_cb' => false,
							'depth' => 1,
						]);
						?>
						<div class="rm-footer__socialBlock rm-footer__desktopOnly">
							<p class="rm-footer__socialTitle"><?php echo esc_html__('Jälgi meie tegemisi', 'sawerna'); ?></p>
							<div class="rm-footer__socialLinks">
								<?php foreach ($footer_socials as $social): ?>
									<?php if (!empty($social['url'])): ?>
										<a class="rm-footer__socialLink" href="<?php echo esc_url($social['url']); ?>" target="_blank"
											rel="noopener noreferrer" aria-label="<?php echo esc_attr($social['label']); ?>">
											<img src="<?php echo esc_url($social['icon']); ?>" alt="">
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
							<span class="rm-footer__accordionLabel"><?php echo esc_html__('Kliendile', 'sawerna'); ?></span>
							<span class="rm-footer__accordionIcon" aria-hidden="true"></span>
						</button>
					</h3>
					<div class="rm-footer__accordionPanel" id="rm-footer-client">
						<?php
						wp_nav_menu([
							'theme_location' => 'footer_client_info',
							'container' => false,
							'menu_class' => 'rm-footer__menu',
							'fallback_cb' => false,
							'depth' => 1,
						]);
						?>
					</div>
				</div>

				<div class="rm-footer__menuCol rm-footer__accordionItem">
					<h3 class="rm-footer__menuTitle">
						<button class="rm-footer__accordionToggle" type="button" aria-expanded="false"
							aria-controls="rm-footer-services">
							<span class="rm-footer__accordionLabel"><?php echo esc_html__('Teenused', 'sawerna'); ?></span>
							<span class="rm-footer__accordionIcon" aria-hidden="true"></span>
						</button>
					</h3>
					<div class="rm-footer__accordionPanel" id="rm-footer-services">
						<?php
						wp_nav_menu([
							'theme_location' => 'footer_services_info',
							'container' => false,
							'menu_class' => 'rm-footer__menu',
							'fallback_cb' => false,
							'depth' => 1,
						]);
						?>
					</div>
				</div>

				<div class="rm-footer__menuCol rm-footer__company rm-footer__accordionItem">
					<h3 class="rm-footer__menuTitle">
						<button class="rm-footer__accordionToggle" type="button" aria-expanded="false"
							aria-controls="rm-footer-company">
							<span class="rm-footer__accordionLabel"><?php echo esc_html__('Ettevõttest', 'sawerna'); ?></span>
							<span class="rm-footer__accordionIcon" aria-hidden="true"></span>
						</button>
					</h3>
					<div class="rm-footer__accordionPanel" id="rm-footer-company">
						<p class="rm-footer__companyHeading"><?php echo esc_html__('Sawerna OÜ', 'sawerna'); ?></p>
						<p class="rm-footer__companyLine">Registrikood: 14953904</p>
						<p class="rm-footer__companyLine">KMKR: EE102305235</p>
						<p class="rm-footer__companyLine">Hannula, Savarna küla, Kanepi vald,</p>
						<p class="rm-footer__companyLine">63418 Põlvamaa, Eesti</p>
						<img class="rm-footer__badge rm-footer__desktopOnly"
							src="<?php echo esc_url($theme_uri . '/assets/SVG/Asset 25.svg'); ?>"
							alt="<?php echo esc_attr__('Usalduspartner', 'sawerna'); ?>">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="rm-footer__mobileExtras">
		<div class="rm-footer__sectionInner">
			<div class="rm-footer__mobileExtrasRow">
				<div class="rm-footer__socialBlock">
					<p class="rm-footer__socialTitle"><?php echo esc_html__('Jälgi meie tegemisi', 'sawerna'); ?></p>
					<div class="rm-footer__socialLinks">
						<?php foreach ($footer_socials as $social): ?>
							<?php if (!empty($social['url'])): ?>
								<a class="rm-footer__socialLink" href="<?php echo esc_url($social['url']); ?>" target="_blank"
									rel="noopener noreferrer" aria-label="<?php echo esc_attr($social['label']); ?>">
									<img src="<?php echo esc_url($social['icon']); ?>" alt="">
								</a>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</div>
				<img class="rm-footer__badge" src="<?php echo esc_url($theme_uri . '/assets/SVG/Asset 25.svg'); ?>"
					alt="<?php echo esc_attr__('Usalduspartner', 'sawerna'); ?>">
			</div>
		</div>
	</div>
	<?php if ($has_footer_bottom): ?>
		<div class="rm-footer__bottom">
			<div class="rm-footer__sectionInner">
				<div class="rm-footer__bottomRow">
					<p class="rm-footer__bottomText"><?php echo wp_kses_post($footer_bottom_text); ?></p>
				</div>
			</div>
		</div>
	<?php endif; ?>
</footer>

<?php wp_footer(); ?>
</body>

</html>