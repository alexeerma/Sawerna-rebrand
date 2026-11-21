<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Sawerna
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="sawerna-header" role="banner">
	<!-- Row 1: Promotional Text + Top Bar Menu -->
	<div class="sawerna-header__row-1">
	<div class="sawerna-header__container">
		<div class="sawerna-header__row-1-inner">
		<!-- Promotional Text (Customizable) -->
		<div class="sawerna-header__promo-text">
			<?php
			$promo_text = get_theme_mod( 'sawerna_header_promo_text', __( 'Tasuta tarne alates 1 000 € ostust!', 'sawerna' ) );
			if ( ! empty( $promo_text ) ) {
				echo '<span>' . esc_html( $promo_text ) . '</span>';
			}
			?>
		</div>

		<!-- Top Bar Menu -->
		<nav class="sawerna-header__top-menu" aria-label="<?php esc_attr_e( 'Top bar navigation', 'sawerna' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'header_top_bar',
					'container'      => false,
					'menu_class'     => 'sawerna-top-menu',
					'fallback_cb'    => false,
					'depth'          => 2,
					'walker'         => new Sawerna_Walker_Nav_Menu_HTML_Blocks(),
				)
			);
			?>
		</nav>
		</div>
	</div>
	</div>

	<!-- Row 2: Logo | Search | Actions -->
	<div class="sawerna-header__row-2">
	<div class="sawerna-header__container">
		<div class="sawerna-header__row-2-inner">
		<!-- Column 1: Logo -->
		<div class="sawerna-header__logo-col">
			<div class="sawerna-header__logo">
			<?php
			$custom_logo = get_custom_logo();
			if ( ! empty( $custom_logo ) ) {
				echo $custom_logo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				// Fallback: Check for SVG logo from assets folder
				$svg_logo = get_theme_mod( 'sawerna_header_logo_svg', '' );
				if ( ! empty( $svg_logo ) && file_exists( sawerna_get_svg_path( $svg_logo ) ) ) {
					$site_name  = get_bloginfo( 'name' );
					$svg_markup = sawerna_get_svg_inline( $svg_logo );

					if ( ! empty( $svg_markup ) ) {
						if ( false === strpos( $svg_markup, 'class=' ) ) {
							$svg_markup = preg_replace( '/<svg\b/i', '<svg class="custom-logo"', $svg_markup, 1 );
						} elseif ( preg_match( '/<svg\b[^>]*class="([^"]*)"/i', $svg_markup ) ) {
							$svg_markup = preg_replace( '/(<svg\b[^>]*class=")([^"]*)"/i', '$1$2 custom-logo"', $svg_markup, 1 );
						}

						if ( false === strpos( $svg_markup, 'focusable=' ) ) {
							$svg_markup = preg_replace( '/<svg\b/i', '<svg focusable="false"', $svg_markup, 1 );
						}

						if ( false === strpos( $svg_markup, 'aria-hidden=' ) ) {
							$svg_markup = preg_replace( '/<svg\b/i', '<svg aria-hidden="true"', $svg_markup, 1 );
						}
						?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="custom-logo-link" rel="home" aria-label="<?php echo esc_attr( $site_name ); ?>">
						<?php echo $svg_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
						<?php
					} else {
						?>
					<a class="sawerna-header__site-title" href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<?php bloginfo( 'name' ); ?>
					</a>
						<?php
					}
				} else {
					// Final fallback: Site title
					?>
				<a class="sawerna-header__site-title" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php bloginfo( 'name' ); ?>
				</a>
					<?php
				}
			}
			?>
			</div>
		</div>

		<!-- Column 2: Search Input -->
		<div class="sawerna-header__search-col">
			<form role="search" method="get" class="sawerna-header__search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" data-sawerna-search-form>
			<label for="sawerna-search-input" class="screen-reader-text"><?php esc_html_e( 'Otsi', 'sawerna' ); ?></label>
			<input 
				type="search" 
				id="sawerna-search-input"
				class="sawerna-header__search-input" 
				placeholder="<?php esc_attr_e( 'Otsi tooteid: kirjuta toote nimi, mida soovid otsida...', 'sawerna' ); ?>" 
				value="<?php echo get_search_query(); ?>" 
				name="s"
				autocomplete="off"
				aria-autocomplete="list"
				aria-expanded="false"
				aria-controls="sawerna-header-search-results"
				data-sawerna-search-input
			/>
			<button type="submit" class="sawerna-header__search-submit" aria-label="<?php esc_attr_e( 'Otsi', 'sawerna' ); ?>">
				<?php echo sawerna_header_icon_search(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted theme SVG, sanitized in sawerna_get_svg_inline(). ?>
			</button>
			<div
				id="sawerna-header-search-results"
				class="sawerna-ajax-search"
				data-sawerna-search-results
				role="region"
				aria-label="<?php esc_attr_e( 'Otsingu tulemused', 'sawerna' ); ?>"
				hidden
			></div>
			</form>
		</div>

		<!-- Column 3: Login/Register + Icons -->
		<div class="sawerna-header__actions-col">
			<div class="sawerna-header__actions">
			<!-- Login/Register -->
			<?php if ( ! is_user_logged_in() ) : ?>
				<a href="<?php echo esc_url( sawerna_get_account_url() ); ?>" class="sawerna-header__login-btn">
				<?php esc_html_e( 'Logi sisse / Registreeru', 'sawerna' ); ?>
				</a>
			<?php else : ?>
				<a href="<?php echo esc_url( sawerna_get_account_url() ); ?>" class="sawerna-header__account-link">
				<?php
				$logged_in_user = wp_get_current_user();
				$display_name   = ! empty( $logged_in_user->display_name ) ? $logged_in_user->display_name : $logged_in_user->user_login;
				/* translators: %s: user display name. */
				printf( esc_html__( 'Tere, %s', 'sawerna' ), esc_html( $display_name ) );
				?>
				</a>
			<?php endif; ?>

			<!-- My Account Icon -->
			<a href="<?php echo esc_url( sawerna_get_account_url() ); ?>" class="sawerna-header__icon-btn sawerna-header__icon-btn--account" aria-label="<?php esc_attr_e( 'My account', 'sawerna' ); ?>" title="<?php esc_attr_e( 'My account', 'sawerna' ); ?>">
				<?php echo sawerna_header_icon_account(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted theme SVG, sanitized in sawerna_get_svg_inline(). ?>
			</a>

			<!-- Wishlist Icon -->
			<?php if ( class_exists( 'WooCommerce' ) ) : ?>
				<?php
				$wishlist_count      = function_exists( 'sawerna_get_wishlist_count' ) && function_exists( 'sawerna_is_theme_wishlist_enabled' ) && sawerna_is_theme_wishlist_enabled() ? sawerna_get_wishlist_count() : 0;
				$wishlist_count_text = $wishlist_count > 0 ? (string) $wishlist_count : '';
				?>
				<a href="<?php echo esc_url( function_exists( 'sawerna_get_wishlist_url' ) ? sawerna_get_wishlist_url() : home_url( '/wishlist/' ) ); ?>" class="sawerna-header__icon-btn sawerna-header__icon-btn--wishlist" aria-label="<?php esc_attr_e( 'Wishlist', 'sawerna' ); ?>" title="<?php esc_attr_e( 'Wishlist', 'sawerna' ); ?>">
				<?php echo sawerna_header_icon_wishlist(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted theme SVG, sanitized in sawerna_get_svg_inline(). ?>
				<?php if ( function_exists( 'sawerna_is_theme_wishlist_enabled' ) && sawerna_is_theme_wishlist_enabled() ) : ?>
					<span class="sawerna-header__wishlist-count" aria-hidden="true"><?php echo esc_html( $wishlist_count_text ); ?></span>
				<?php endif; ?>
				</a>
			<?php endif; ?>

			<!-- Cart Icon -->
			<?php if ( class_exists( 'WooCommerce' ) ) : ?>
				<?php sawerna_woocommerce_cart_trigger( 'desktop' ); ?>
			<?php endif; ?>
			</div>
		</div>
		</div>
	</div>
	</div>

	<!-- Row 3: Mega Menu (Black Background) -->
	<div class="sawerna-header__row-3">
	<div class="sawerna-header__container">
		<nav class="sawerna-header__mega-menu-nav" aria-label="<?php esc_attr_e( 'Main navigation', 'sawerna' ); ?>">
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'header_mega_menu',
				'container'      => false,
				'menu_class'     => 'sawerna-mega-menu',
				'fallback_cb'    => false,
				'depth'          => 3,
				'walker'         => new Sawerna_Walker_Nav_Menu_Mega_Menu_HTML_Blocks(),
			)
		);
		?>
		</nav>
	</div>
	</div>

	<!-- Mobile Header (Hidden on desktop) -->
	<div class="sawerna-header__mobile">
	<div class="sawerna-header__container">
		<div class="sawerna-header__mobile-top">
		<button class="sawerna-header__mobile-toggle" type="button" aria-label="<?php esc_attr_e( 'Open menu', 'sawerna' ); ?>" aria-expanded="false">
			<span class="sawerna-header__mobile-toggle-icon" aria-hidden="true">
			<?php echo sawerna_get_svg_inline( 'Asset 3.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted theme SVG, sanitized in sawerna_get_svg_inline(). ?>
			</span>
		</button>

		<div class="sawerna-header__mobile-logo">
			<?php
			$custom_logo = get_custom_logo();
			if ( ! empty( $custom_logo ) ) {
				echo $custom_logo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				// Fallback: Check for SVG logo from assets folder
				$svg_logo = get_theme_mod( 'sawerna_header_logo_svg', '' );
				if ( ! empty( $svg_logo ) && file_exists( sawerna_get_svg_path( $svg_logo ) ) ) {
					$site_name  = get_bloginfo( 'name' );
					$svg_markup = sawerna_get_svg_inline( $svg_logo );

					if ( ! empty( $svg_markup ) ) {
						if ( false === strpos( $svg_markup, 'class=' ) ) {
							$svg_markup = preg_replace( '/<svg\b/i', '<svg class="custom-logo"', $svg_markup, 1 );
						} elseif ( preg_match( '/<svg\b[^>]*class="([^"]*)"/i', $svg_markup ) ) {
							$svg_markup = preg_replace( '/(<svg\b[^>]*class=")([^"]*)"/i', '$1$2 custom-logo"', $svg_markup, 1 );
						}

						if ( false === strpos( $svg_markup, 'focusable=' ) ) {
							$svg_markup = preg_replace( '/<svg\b/i', '<svg focusable="false"', $svg_markup, 1 );
						}

						if ( false === strpos( $svg_markup, 'aria-hidden=' ) ) {
							$svg_markup = preg_replace( '/<svg\b/i', '<svg aria-hidden="true"', $svg_markup, 1 );
						}
						?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="custom-logo-link" rel="home" aria-label="<?php echo esc_attr( $site_name ); ?>">
						<?php echo $svg_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
						<?php
					} else {
						?>
				<a class="sawerna-header__site-title" href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<?php bloginfo( 'name' ); ?>
				</a>
						<?php
					}
				} else {
					// Final fallback: Site title
					?>
				<a class="sawerna-header__site-title" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php bloginfo( 'name' ); ?>
				</a>
					<?php
				}
			}
			?>
		</div>

		<div class="sawerna-header__mobile-actions">
			<a href="<?php echo esc_url( sawerna_get_account_url() ); ?>" class="sawerna-header__icon-btn sawerna-header__icon-btn--account" aria-label="<?php esc_attr_e( 'My account', 'sawerna' ); ?>">
			<?php echo sawerna_header_icon_account(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted theme SVG, sanitized in sawerna_get_svg_inline(). ?>
			</a>

			<?php if ( class_exists( 'WooCommerce' ) ) : ?>
				<?php
				$wishlist_count_mobile      = function_exists( 'sawerna_get_wishlist_count' ) && function_exists( 'sawerna_is_theme_wishlist_enabled' ) && sawerna_is_theme_wishlist_enabled() ? sawerna_get_wishlist_count() : 0;
				$wishlist_count_text_mobile = $wishlist_count_mobile > 0 ? (string) $wishlist_count_mobile : '';
				?>
			<a href="<?php echo esc_url( function_exists( 'sawerna_get_wishlist_url' ) ? sawerna_get_wishlist_url() : home_url( '/wishlist/' ) ); ?>" class="sawerna-header__icon-btn sawerna-header__icon-btn--wishlist" aria-label="<?php esc_attr_e( 'Wishlist', 'sawerna' ); ?>">
				<?php echo sawerna_header_icon_wishlist(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted theme SVG, sanitized in sawerna_get_svg_inline(). ?>
				<?php if ( function_exists( 'sawerna_is_theme_wishlist_enabled' ) && sawerna_is_theme_wishlist_enabled() ) : ?>
				<span class="sawerna-header__wishlist-count" aria-hidden="true"><?php echo esc_html( $wishlist_count_text_mobile ); ?></span>
				<?php endif; ?>
			</a>
			<?php endif; ?>

			<?php if ( class_exists( 'WooCommerce' ) ) : ?>
				<?php sawerna_woocommerce_cart_trigger( 'mobile' ); ?>
			<?php endif; ?>
		</div>
		</div>

		<div class="sawerna-header__mobile-search">
		<form role="search" method="get" class="sawerna-header__search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" data-sawerna-search-form>
			<label for="sawerna-mobile-search-input" class="screen-reader-text"><?php esc_html_e( 'Otsi', 'sawerna' ); ?></label>
			<input 
			type="search" 
			id="sawerna-mobile-search-input"
			class="sawerna-header__search-input" 
			placeholder="<?php esc_attr_e( 'Otsi tooteid: kirjuta toote nimi, mida soovid otsida...', 'sawerna' ); ?>" 
			value="<?php echo get_search_query(); ?>" 
			name="s"
			autocomplete="off"
			aria-autocomplete="list"
			aria-expanded="false"
			aria-controls="sawerna-mobile-search-results"
			data-sawerna-search-input
			/>
			<button type="submit" class="sawerna-header__search-submit" aria-label="<?php esc_attr_e( 'Otsi', 'sawerna' ); ?>">
			<?php echo sawerna_header_icon_search(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted theme SVG, sanitized in sawerna_get_svg_inline(). ?>
			</button>
			<div
			id="sawerna-mobile-search-results"
			class="sawerna-ajax-search"
			data-sawerna-search-results
			role="region"
			aria-label="<?php esc_attr_e( 'Otsingu tulemused', 'sawerna' ); ?>"
			hidden
			></div>
		</form>
		</div>

		<?php
		$promo_text = get_theme_mod( 'sawerna_header_promo_text', __( 'Tasuta tarne alates 1 000 € ostust!', 'sawerna' ) );
		$phone      = get_theme_mod( 'sawerna_header_phone', '' );
		if ( empty( $phone ) ) {
			$phone = get_theme_mod( 'rm_footer_phone', '+372 6066 941' );
		}
		$phone_link = preg_replace( '/[^0-9\+]/', '', (string) $phone );
		$promo_icon = sawerna_get_svg_inline( 'check.svg' );
		if ( empty( $promo_icon ) ) {
			$promo_icon = sawerna_get_svg_inline( 'Asset 19.svg' );
		}
		$phone_icon = sawerna_get_svg_inline( 'telefon.svg' );
		if ( empty( $phone_icon ) ) {
			$phone_icon = sawerna_get_svg_inline( 'Asset 19.svg' );
		}
		?>
		<div class="sawerna-header__mobile-info">
		<?php if ( ! empty( $promo_text ) ) : ?>
			<div class="sawerna-header__mobile-info-item">
			<span class="sawerna-header__mobile-info-icon" aria-hidden="true"><?php echo $promo_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted theme SVG, sanitized in sawerna_get_svg_inline(). ?></span>
			<span class="sawerna-header__mobile-info-text"><?php echo esc_html( $promo_text ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( ! empty( $phone ) ) : ?>
			<a class="sawerna-header__mobile-info-item" href="<?php echo esc_url( 'tel:' . $phone_link ); ?>">
			<span class="sawerna-header__mobile-info-icon" aria-hidden="true"><?php echo $phone_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted theme SVG, sanitized in sawerna_get_svg_inline(). ?></span>
			<span class="sawerna-header__mobile-info-text"><?php echo esc_html( $phone ); ?></span>
			</a>
		<?php endif; ?>
		</div>
	</div>
	</div>

	<!-- Mobile Menu Overlay -->
	<div class="sawerna-header__mobile-overlay" hidden>
	<div class="sawerna-header__mobile-overlay-inner">
		<div class="sawerna-header__mobile-overlay-header">
		<nav class="sawerna-header__mobile-language-nav" aria-label="<?php esc_attr_e( 'Overlay language menu', 'sawerna' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'header_mobile_overlay_language',
					'container'      => false,
					'menu_class'     => 'sawerna-mobile-language-menu',
					'fallback_cb'    => false,
					'depth'          => 1,
				)
			);
			?>
		</nav>
		<button class="sawerna-header__mobile-close" type="button" aria-label="<?php esc_attr_e( 'Close menu', 'sawerna' ); ?>">
			<span class="sawerna-header__mobile-close-icon" aria-hidden="true">
			<svg viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
				<line x1="3" y1="3" x2="13" y2="13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
				<line x1="13" y1="3" x2="3" y2="13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
			</svg>
			</span>
		</button>
		</div>

		<nav class="sawerna-header__mobile-main-nav" aria-label="<?php esc_attr_e( 'Main navigation', 'sawerna' ); ?>">
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'header_mega_menu',
				'container'      => false,
				'menu_class'     => 'sawerna-mobile-menu sawerna-mobile-menu--main',
				'fallback_cb'    => false,
				'depth'          => 3,
				'walker'         => new Sawerna_Walker_Nav_Menu_Mega_Menu_HTML_Blocks( false ), // HTML blocks are desktop-only.
			)
		);
		?>
		</nav>

		<div class="sawerna-header__mobile-overlay-info">
		<div class="sawerna-header__mobile-info">
			<?php if ( ! empty( $promo_text ) ) : ?>
			<div class="sawerna-header__mobile-info-item">
				<span class="sawerna-header__mobile-info-icon" aria-hidden="true"><?php echo $promo_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted theme SVG, sanitized in sawerna_get_svg_inline(). ?></span>
				<span class="sawerna-header__mobile-info-text"><?php echo esc_html( $promo_text ); ?></span>
			</div>
			<?php endif; ?>
			<?php if ( ! empty( $phone ) ) : ?>
			<a class="sawerna-header__mobile-info-item" href="<?php echo esc_url( 'tel:' . $phone_link ); ?>">
				<span class="sawerna-header__mobile-info-icon" aria-hidden="true"><?php echo $phone_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted theme SVG, sanitized in sawerna_get_svg_inline(). ?></span>
				<span class="sawerna-header__mobile-info-text"><?php echo esc_html( $phone ); ?></span>
			</a>
			<?php endif; ?>
		</div>
		</div>

		<div class="sawerna-header__mobile-user">
		<a href="<?php echo esc_url( sawerna_get_account_url() ); ?>" class="sawerna-header__mobile-user-link">
			<span class="sawerna-header__mobile-user-icon" aria-hidden="true"><?php echo sawerna_header_icon_account(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted theme SVG, sanitized in sawerna_get_svg_inline(). ?></span>
			<span class="sawerna-header__mobile-user-text">
			<?php if ( ! is_user_logged_in() ) : ?>
				<?php esc_html_e( 'Logi sisse / Liitu', 'sawerna' ); ?>
			<?php else : ?>
				<?php
				$logged_in_user = wp_get_current_user();
				$display_name   = ! empty( $logged_in_user->display_name ) ? $logged_in_user->display_name : $logged_in_user->user_login;
				/* translators: %s: user display name. */
				printf( esc_html__( 'Tere, %s', 'sawerna' ), esc_html( $display_name ) );
				?>
			<?php endif; ?>
			</span>
		</a>

		<?php if ( class_exists( 'WooCommerce' ) ) : ?>
			<a href="<?php echo esc_url( function_exists( 'sawerna_get_wishlist_url' ) ? sawerna_get_wishlist_url() : home_url( '/wishlist/' ) ); ?>" class="sawerna-header__mobile-user-link">
			<span class="sawerna-header__mobile-user-icon" aria-hidden="true"><?php echo sawerna_header_icon_wishlist(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted theme SVG, sanitized in sawerna_get_svg_inline(). ?></span>
			<span class="sawerna-header__mobile-user-text"><?php esc_html_e( 'Soovitud kaubad', 'sawerna' ); ?></span>
			</a>
			<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="sawerna-header__mobile-user-link">
			<span class="sawerna-header__mobile-user-icon" aria-hidden="true"><?php echo sawerna_header_icon_cart(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted theme SVG, sanitized in sawerna_get_svg_inline(). ?></span>
			<span class="sawerna-header__mobile-user-text"><?php esc_html_e( 'Ostukorv', 'sawerna' ); ?></span>
			</a>
		<?php endif; ?>
		</div>

		<nav class="sawerna-header__mobile-topbar-nav" aria-label="<?php esc_attr_e( 'Mobile overlay bottom navigation', 'sawerna' ); ?>">
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'header_mobile_overlay_bottom',
				'container'      => false,
				'menu_class'     => 'sawerna-mobile-topbar',
				'fallback_cb'    => false,
				'depth'          => 1,
			)
		);
		?>
		</nav>
	</div>
	</div>

	<!-- Search Overlay -->
	<div class="sawerna-header__search-overlay" hidden>
	<div class="sawerna-header__search-overlay-inner">
		<button class="sawerna-header__search-close" type="button" aria-label="<?php esc_attr_e( 'Close search', 'sawerna' ); ?>">
		<span></span>
		<span></span>
		</button>
		<div class="sawerna-header__search-form-wrapper">
		<?php get_search_form(); ?>
		</div>
	</div>
	</div>
</header>
