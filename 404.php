<?php
/**
 * The template for displaying 404 pages.
 *
 * @package Sawerna
 */

get_header();

$shop_url      = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$blog_url      = function_exists( 'sawerna_get_blog_archive_url' ) ? sawerna_get_blog_archive_url() : home_url( '/blog/' );
$account_url   = function_exists( 'sawerna_get_account_url' ) ? sawerna_get_account_url() : wp_login_url();
$categories    = function_exists( 'sawerna_get_homepage_category_terms' ) ? array_slice( sawerna_get_homepage_category_terms(), 0, 4 ) : array();
$uploads       = wp_get_upload_dir();
$hero_image    = isset( $uploads['baseurl'] ) ? trailingslashit( (string) $uploads['baseurl'] ) . '2026/02/Diivanvoodi-Loora-2-1320x1320.jpg' : '';
$search_query  = get_search_query();
?>

<main id="primary" class="site-main sawerna-error-page">
	<section class="sawerna-error-page__hero">
		<div class="sawerna-error-page__container">
			<div class="sawerna-error-page__shell">
				<div class="sawerna-error-page__content">
					<p class="sawerna-error-page__eyebrow"><?php esc_html_e( 'Viga 404', 'sawerna' ); ?></p>
					<h1 class="sawerna-error-page__title"><?php esc_html_e( 'See leht jäi teele kaduma.', 'sawerna' ); ?></h1>
					<p class="sawerna-error-page__lead"><?php esc_html_e( 'Otsitud lehte ei ole enam siin, aga aitame sul kiiresti tagasi õigete toodete ja kategooriateni.', 'sawerna' ); ?></p>

					<form role="search" method="get" class="sawerna-error-page__search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
						<label class="screen-reader-text" for="sawerna-error-search"><?php esc_html_e( 'Search', 'sawerna' ); ?></label>
						<input id="sawerna-error-search" type="search" name="s" value="<?php echo esc_attr( $search_query ); ?>" placeholder="<?php esc_attr_e( 'Otsi toodet või kategooriat...', 'sawerna' ); ?>">
						<button type="submit"><?php esc_html_e( 'Otsi', 'sawerna' ); ?></button>
					</form>

					<div class="sawerna-error-page__actions">
						<a class="sawerna-error-page__button sawerna-error-page__button--primary" href="<?php echo esc_url( $shop_url ); ?>">
							<?php esc_html_e( 'Mine poodi', 'sawerna' ); ?>
						</a>
						<a class="sawerna-error-page__button sawerna-error-page__button--secondary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
							<?php esc_html_e( 'Avalehele', 'sawerna' ); ?>
						</a>
					</div>

					<div class="sawerna-error-page__helper-links">
						<a href="<?php echo esc_url( $blog_url ); ?>"><?php esc_html_e( 'Vaata blogi', 'sawerna' ); ?></a>
						<a href="<?php echo esc_url( $account_url ); ?>"><?php esc_html_e( 'Minu konto', 'sawerna' ); ?></a>
					</div>
				</div>

				<div class="sawerna-error-page__visual"<?php echo $hero_image ? ' style="background-image:url(' . esc_url( $hero_image ) . ')"' : ''; ?>>
					<div class="sawerna-error-page__visual-overlay"></div>
					<div class="sawerna-error-page__visual-content">
						<span class="sawerna-error-page__code" aria-hidden="true">404</span>
						<div class="sawerna-error-page__note">
							<h2><?php esc_html_e( 'Jätkame siit', 'sawerna' ); ?></h2>
							<p><?php esc_html_e( 'Leia uus lemmik meie populaarsetest kategooriatest või alusta otsingut uuesti.', 'sawerna' ); ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="sawerna-error-page__links">
		<div class="sawerna-error-page__container">
			<div class="sawerna-error-page__section-head">
				<p class="sawerna-error-page__section-kicker"><?php esc_html_e( 'Kiired otseteed', 'sawerna' ); ?></p>
				<h2 class="sawerna-error-page__section-title"><?php esc_html_e( 'Alusta siit', 'sawerna' ); ?></h2>
			</div>

			<div class="sawerna-error-page__grid">
				<?php if ( ! empty( $categories ) ) : ?>
					<?php foreach ( $categories as $term ) : ?>
						<a class="sawerna-error-page__card" href="<?php echo esc_url( get_term_link( $term ) ); ?>">
							<span class="sawerna-error-page__card-media">
								<?php echo wp_kses_post( sawerna_get_homepage_category_image_html( $term ) ); ?>
							</span>
							<span class="sawerna-error-page__card-body">
								<strong><?php echo esc_html( $term->name ); ?></strong>
								<span><?php esc_html_e( 'Vaata tooteid', 'sawerna' ); ?></span>
							</span>
						</a>
					<?php endforeach; ?>
				<?php else : ?>
					<a class="sawerna-error-page__card" href="<?php echo esc_url( $shop_url ); ?>">
						<span class="sawerna-error-page__card-body">
							<strong><?php esc_html_e( 'Pood', 'sawerna' ); ?></strong>
							<span><?php esc_html_e( 'Mine ostlema', 'sawerna' ); ?></span>
						</span>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</section>
</main>

<?php
get_footer();
