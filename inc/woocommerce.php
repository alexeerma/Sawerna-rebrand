<?php
/**
 * WooCommerce Compatibility File
 *
 * @link https://woocommerce.com/
 *
 * @package Sawerna
 */

/**
 * Require the privacy policy checkbox on checkout.
 */
function sawerna_validate_privacy_policy_checkbox() {
	if ( empty( $_POST['sawerna_privacy_policy'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		wc_add_notice( __( 'Palun nõustu privaatsuspoliitikaga, et jätkata.', 'sawerna' ), 'error' );
	}
}
add_action( 'woocommerce_checkout_process', 'sawerna_validate_privacy_policy_checkbox' );

/**
 * WooCommerce setup function.
 *
 * @link https://docs.woocommerce.com/document/third-party-custom-theme-compatibility/
 * @link https://github.com/woocommerce/woocommerce/wiki/Enabling-product-gallery-features-(zoom,-swipe,-lightbox)
 * @link https://github.com/woocommerce/woocommerce/wiki/Declaring-WooCommerce-support-in-themes
 *
 * @return void
 */
function sawerna_woocommerce_setup() {
	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 150,
			'single_image_width'    => 300,
			'product_grid'          => array(
				'default_rows'    => 3,
				'min_rows'        => 1,
				'default_columns' => 4,
				'min_columns'     => 1,
				'max_columns'     => 6,
			),
		)
	);
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'sawerna_woocommerce_setup' );

/**
 * WooCommerce specific scripts & stylesheets.
 *
 * @return void
 */
function sawerna_woocommerce_scripts() {
	wp_enqueue_style( 'sawerna-woocommerce-style', get_template_directory_uri() . '/woocommerce.css', array(), _S_VERSION );

	$font_path   = WC()->plugin_url() . '/assets/fonts/';
	$inline_font = '@font-face {
			font-family: "star";
			src: url("' . $font_path . 'star.eot");
			src: url("' . $font_path . 'star.eot?#iefix") format("embedded-opentype"),
				url("' . $font_path . 'star.woff") format("woff"),
				url("' . $font_path . 'star.ttf") format("truetype"),
				url("' . $font_path . 'star.svg#star") format("svg");
			font-weight: normal;
			font-style: normal;
		}';

	wp_add_inline_style( 'sawerna-woocommerce-style', $inline_font );

	$shop_css_path = get_template_directory() . '/assets/css/shop.css';
	if ( file_exists( $shop_css_path ) ) {
		$shop_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $shop_css_path ) : _S_VERSION;
		wp_enqueue_style( 'sawerna-shop-style', get_template_directory_uri() . '/assets/css/shop.css', array( 'sawerna-style', 'sawerna-woocommerce-style' ), $shop_version );
	}
}
add_action( 'wp_enqueue_scripts', 'sawerna_woocommerce_scripts' );

/**
 * Enqueue theme mini-cart UI assets.
 *
 * @return void
 */
function sawerna_woocommerce_mini_cart_ui_assets() {
	$script_path = get_template_directory() . '/js/mini-cart.js';

	if ( ! file_exists( $script_path ) ) {
		return;
	}

	$version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $script_path ) : _S_VERSION;
	wp_enqueue_script( 'sawerna-mini-cart', get_template_directory_uri() . '/js/mini-cart.js', array(), $version, true );
	wp_script_add_data( 'sawerna-mini-cart', 'defer', true );
	wp_localize_script(
		'sawerna-mini-cart',
		'sawernaMiniCart',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'sawerna_mini_cart' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'sawerna_woocommerce_mini_cart_ui_assets', 35 );

/**
 * Enqueue cart page assets.
 *
 * @return void
 */
function sawerna_woocommerce_cart_assets() {
	if ( ! is_cart() ) {
		return;
	}

	$theme_dir = get_template_directory();
	$theme_uri = get_template_directory_uri();
	$css_path  = $theme_dir . '/assets/css/cart.css';
	$js_path   = $theme_dir . '/js/cart.js';

	if ( file_exists( $css_path ) ) {
		$css_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $css_path ) : _S_VERSION;
		wp_enqueue_style( 'sawerna-cart', $theme_uri . '/assets/css/cart.css', array( 'sawerna-style', 'sawerna-woocommerce-style', 'sawerna-shop-style', 'sawerna-product-card' ), $css_version );
	}

	if ( file_exists( $js_path ) ) {
		$js_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $js_path ) : _S_VERSION;
		wp_enqueue_script( 'sawerna-cart', $theme_uri . '/js/cart.js', array(), $js_version, true );
		wp_script_add_data( 'sawerna-cart', 'defer', true );
	}
}
add_action( 'wp_enqueue_scripts', 'sawerna_woocommerce_cart_assets', 40 );

/**
 * Enqueue My Account page assets.
 *
 * @return void
 */
function sawerna_woocommerce_my_account_assets() {
	if ( ! function_exists( 'is_account_page' ) || ! is_account_page() ) {
		return;
	}

	$theme_dir = get_template_directory();
	$theme_uri = get_template_directory_uri();
	$css_path  = $theme_dir . '/assets/css/account.css';

	if ( file_exists( $css_path ) ) {
		$css_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $css_path ) : _S_VERSION;
		wp_enqueue_style( 'sawerna-account', $theme_uri . '/assets/css/account.css', array( 'sawerna-style', 'sawerna-woocommerce-style', 'sawerna-shop-style' ), $css_version );
	}
}
add_action( 'wp_enqueue_scripts', 'sawerna_woocommerce_my_account_assets', 40 );

/**
 * Get the Sawerna account menu configuration.
 *
 * @return array<string,array{label:string,url:string,icon:string,endpoint:string}>
 */
function sawerna_woocommerce_account_menu_items() {
	$items = array(
		'dashboard' => array(
			'label'    => __( 'Ülevaade', 'sawerna' ),
			'url'      => wc_get_account_endpoint_url( 'dashboard' ),
			'icon'     => 'grid',
			'endpoint' => 'dashboard',
		),
		'orders'    => array(
			'label'    => __( 'Tellimused', 'sawerna' ),
			'url'      => wc_get_account_endpoint_url( 'orders' ),
			'icon'     => 'list',
			'endpoint' => 'orders',
		),
		'downloads' => array(
			'label'    => __( 'Allalaadimised', 'sawerna' ),
			'url'      => wc_get_account_endpoint_url( 'downloads' ),
			'icon'     => 'download',
			'endpoint' => 'downloads',
		),
		'edit-address' => array(
			'label'    => __( 'Aadressid', 'sawerna' ),
			'url'      => wc_get_account_endpoint_url( 'edit-address' ),
			'icon'     => 'pin',
			'endpoint' => 'edit-address',
		),
		'edit-account' => array(
			'label'    => __( 'Konto andmed', 'sawerna' ),
			'url'      => wc_get_account_endpoint_url( 'edit-account' ),
			'icon'     => 'user',
			'endpoint' => 'edit-account',
		),
		'wishlist' => array(
			'label'    => __( 'Soovinimekiri', 'sawerna' ),
			'url'      => function_exists( 'sawerna_get_wishlist_url' ) ? sawerna_get_wishlist_url() : home_url( '/wishlist/' ),
			'icon'     => 'heart',
			'endpoint' => 'wishlist',
		),
		'newsletter' => array(
			'label'    => __( 'Uudiskiri', 'sawerna' ),
			'url'      => '#rm-footer-newsletter-email',
			'icon'     => 'mail',
			'endpoint' => 'newsletter',
		),
		'customer-logout' => array(
			'label'    => __( 'Logi välja', 'sawerna' ),
			'url'      => wc_logout_url(),
			'icon'     => 'logout',
			'endpoint' => 'customer-logout',
		),
	);

	return $items;
}

/**
 * Get an inline account icon.
 *
 * @param string $icon Icon key.
 * @return string
 */
function sawerna_woocommerce_account_icon( $icon ) {
	$icons = array(
		'grid' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="4" y="4" width="6" height="6"></rect><rect x="14" y="4" width="6" height="6"></rect><rect x="4" y="14" width="6" height="6"></rect><rect x="14" y="14" width="6" height="6"></rect></svg>',
		'list' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="6" y="4" width="12" height="16" rx="1"></rect><path d="M9 9h1.5M13 9h2M9 13h1.5M13 13h2M9 17h1.5M13 17h2"></path></svg>',
		'download' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 4v11"></path><path d="M8 11l4 4 4-4"></path><path d="M5 19h14"></path><path d="M6 15v4h12v-4"></path></svg>',
		'pin' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 21s7-6.2 7-12a7 7 0 0 0-14 0c0 5.8 7 12 7 12z"></path><circle cx="12" cy="9" r="2.4"></circle></svg>',
		'user' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="8" r="4"></circle><path d="M4.5 21a7.5 7.5 0 0 1 15 0"></path></svg>',
		'heart' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20.5 5.8a5.2 5.2 0 0 0-7.4 0L12 6.9l-1.1-1.1a5.2 5.2 0 0 0-7.4 7.4L12 21l8.5-7.8a5.2 5.2 0 0 0 0-7.4z"></path></svg>',
		'mail' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="4" y="6" width="16" height="13" rx="1"></rect><path d="M4 8l8 6 8-6"></path><path d="M8 4h8"></path></svg>',
		'logout' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M10 5H5v14h5"></path><path d="M13 8l4 4-4 4"></path><path d="M8 12h9"></path></svg>',
	);

	return isset( $icons[ $icon ] ) ? $icons[ $icon ] : $icons['grid'];
}

/**
 * Determine whether the current request should use the custom checkout UI.
 *
 * @return bool
 */
function sawerna_woocommerce_is_custom_checkout_context() {
	return function_exists( 'is_checkout' ) && is_checkout() && ! is_wc_endpoint_url( 'order-pay' ) && ! is_wc_endpoint_url( 'order-received' );
}

/**
 * Determine whether the current request is the checkout thank-you endpoint.
 *
 * @return bool
 */
function sawerna_woocommerce_is_thankyou_context() {
	return function_exists( 'is_checkout' ) && is_checkout() && function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-received' );
}

/**
 * Enqueue checkout page assets.
 *
 * @return void
 */
function sawerna_woocommerce_checkout_assets() {
	if ( ! sawerna_woocommerce_is_custom_checkout_context() ) {
		return;
	}

	$theme_dir = get_template_directory();
	$theme_uri = get_template_directory_uri();
	$css_path  = $theme_dir . '/assets/css/checkout.css';

	if ( file_exists( $css_path ) ) {
		$css_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $css_path ) : _S_VERSION;
		wp_enqueue_style( 'sawerna-checkout', $theme_uri . '/assets/css/checkout.css', array( 'sawerna-style', 'sawerna-woocommerce-style', 'sawerna-shop-style' ), $css_version );
	}
}
add_action( 'wp_enqueue_scripts', 'sawerna_woocommerce_checkout_assets', 40 );

/**
 * Enqueue order received / thank-you page assets.
 *
 * @return void
 */
function sawerna_woocommerce_thankyou_assets() {
	if ( ! sawerna_woocommerce_is_thankyou_context() ) {
		return;
	}

	$theme_dir = get_template_directory();
	$theme_uri = get_template_directory_uri();
	$css_path  = $theme_dir . '/assets/css/thankyou.css';

	if ( file_exists( $css_path ) ) {
		$css_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $css_path ) : _S_VERSION;
		wp_enqueue_style( 'sawerna-thankyou', $theme_uri . '/assets/css/thankyou.css', array( 'sawerna-style', 'sawerna-woocommerce-style', 'sawerna-shop-style' ), $css_version );
	}
}
add_action( 'wp_enqueue_scripts', 'sawerna_woocommerce_thankyou_assets', 40 );

/**
 * Configure checkout hooks for the custom Sawerna checkout.
 *
 * @return void
 */
function sawerna_woocommerce_checkout_page_setup() {
	if ( ! sawerna_woocommerce_is_custom_checkout_context() ) {
		return;
	}

	remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
}
add_action( 'wp', 'sawerna_woocommerce_checkout_page_setup' );

/**
 * Configure thank-you page hooks for the custom Sawerna order received UI.
 *
 * @return void
 */
function sawerna_woocommerce_thankyou_page_setup() {
	if ( ! sawerna_woocommerce_is_thankyou_context() ) {
		return;
	}

	remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 );
}
add_action( 'wp', 'sawerna_woocommerce_thankyou_page_setup' );

/**
 * Replace the block-based checkout page content with the classic checkout output.
 *
 * @param string $content Page content.
 * @return string
 */
function sawerna_woocommerce_force_classic_checkout_content( $content ) {
	if ( ! ( sawerna_woocommerce_is_custom_checkout_context() || sawerna_woocommerce_is_thankyou_context() ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	global $post;

	if ( ! $post instanceof WP_Post || ! has_block( 'woocommerce/checkout', $post ) ) {
		return $content;
	}

	return do_shortcode( '[woocommerce_checkout]' );
}
add_filter( 'the_content', 'sawerna_woocommerce_force_classic_checkout_content', 20 );

/**
 * Customize checkout labels for the Sawerna checkout UI.
 *
 * @param array $fields Checkout fields.
 * @return array
 */
function sawerna_woocommerce_checkout_fields( $fields ) {
	if ( ! sawerna_woocommerce_is_custom_checkout_context() ) {
		return $fields;
	}

	if ( isset( $fields['order']['order_comments'] ) ) {
		$fields['order']['order_comments']['label']       = __( 'Tellimuse kommentaar', 'sawerna' );
		$fields['order']['order_comments']['placeholder'] = __( 'Vajadusel lisa siia toote kohta lisainfot, näiteks erimõõtude toote kohaletoimetamise suhtes', 'sawerna' );
	}

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'sawerna_woocommerce_checkout_fields' );

/**
 * Use the custom place-order button text on checkout.
 *
 * @param string $text Button text.
 * @return string
 */
function sawerna_woocommerce_order_button_text( $text ) {
	if ( sawerna_woocommerce_is_custom_checkout_context() ) {
		return __( 'Esitage oma tellimus', 'sawerna' );
	}

	return $text;
}
add_filter( 'woocommerce_order_button_text', 'sawerna_woocommerce_order_button_text' );

/**
 * Refresh the full custom checkout review wrapper during WooCommerce AJAX updates.
 *
 * WooCommerce core targets `.woocommerce-checkout-review-order-table` by default.
 * Our custom layout wraps the review in a larger Sawerna section, so swapping the
 * inner table can nest duplicate review blocks after an order review refresh.
 *
 * @param array $fragments Checkout fragments.
 * @return array
 */
function sawerna_woocommerce_checkout_review_fragments( $fragments ) {
	if ( ! sawerna_woocommerce_is_custom_checkout_context() ) {
		return $fragments;
	}

	if ( isset( $fragments['.woocommerce-checkout-review-order-table'] ) ) {
		$fragments['.sawerna-checkout-review'] = $fragments['.woocommerce-checkout-review-order-table'];
		unset( $fragments['.woocommerce-checkout-review-order-table'] );
	}

	return $fragments;
}
add_filter( 'woocommerce_update_order_review_fragments', 'sawerna_woocommerce_checkout_review_fragments' );

if ( ! function_exists( 'sawerna_woocommerce_checkout_login_url' ) ) {
	/**
	 * Return the login URL used by the custom checkout prompt.
	 *
	 * @return string
	 */
	function sawerna_woocommerce_checkout_login_url() {
		$account_url = wc_get_page_permalink( 'myaccount' );

		return $account_url ? add_query_arg( 'redirect_to', wc_get_checkout_url(), $account_url ) : wp_login_url( wc_get_checkout_url() );
	}
}

/**
 * Disable the default WooCommerce stylesheet.
 *
 * Removing the default WooCommerce stylesheet and enqueing your own will
 * protect you during WooCommerce core updates.
 *
 * @link https://docs.woocommerce.com/document/disable-the-default-stylesheet/
 */
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

/**
 * Add 'woocommerce-active' class to the body tag.
 *
 * @param  array $classes CSS classes applied to the body tag.
 * @return array $classes modified to include 'woocommerce-active' class.
 */
function sawerna_woocommerce_active_body_class( $classes ) {
	$classes[] = 'woocommerce-active';

	return $classes;
}
add_filter( 'body_class', 'sawerna_woocommerce_active_body_class' );

/**
 * Align WooCommerce breadcrumb home text with the rest of the theme.
 *
 * @param array $defaults Breadcrumb defaults.
 * @return array
 */
function sawerna_woocommerce_breadcrumb_defaults( $defaults ) {
	$defaults['home'] = esc_html__( 'Esileht', 'sawerna' );

	return $defaults;
}
add_filter( 'woocommerce_breadcrumb_defaults', 'sawerna_woocommerce_breadcrumb_defaults' );

if ( ! function_exists( 'sawerna_woocommerce_cart_badge' ) ) {
	/**
	 * Render cart count badge markup.
	 *
	 * @return string
	 */
	function sawerna_woocommerce_cart_badge() {
		$count = function_exists( 'sawerna_get_cart_count' ) ? (int) sawerna_get_cart_count() : 0;

		return sprintf(
			'<span class="sawerna-header__cart-count" aria-hidden="true">%s</span>',
			$count > 0 ? esc_html( (string) $count ) : ''
		);
	}
}

if ( ! function_exists( 'sawerna_woocommerce_cart_trigger' ) ) {
	/**
	 * Render header cart trigger.
	 *
	 * @param string $context Desktop or mobile trigger context.
	 * @return void
	 */
	function sawerna_woocommerce_cart_trigger( $context = 'desktop' ) {
		$context = 'mobile' === $context ? 'mobile' : 'desktop';
		$title   = esc_attr__( 'Shopping cart', 'sawerna' );
		$class   = sprintf(
			'sawerna-header__icon-btn sawerna-header__icon-btn--cart sawerna-header__cart-trigger sawerna-header__cart-trigger--%s',
			esc_attr( $context )
		);
		?>
		<a
			href="<?php echo esc_url( wc_get_cart_url() ); ?>"
			class="<?php echo esc_attr( $class ); ?>"
			data-mini-cart-trigger="1"
			aria-label="<?php echo $title; ?>"
			title="<?php echo $title; ?>"
			aria-controls="sawerna-mini-cart"
			aria-expanded="false"
		>
			<?php echo sawerna_header_icon_cart(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php echo sawerna_woocommerce_cart_badge(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</a>
		<?php
	}
}

if ( ! function_exists( 'sawerna_woocommerce_mini_cart_count_label' ) ) {
	/**
	 * Return the cart item count label used in the mini-cart header.
	 *
	 * @return string
	 */
	function sawerna_woocommerce_mini_cart_count_label() {
		$count = function_exists( 'sawerna_get_cart_count' ) ? (int) sawerna_get_cart_count() : 0;

		if ( $count < 1 ) {
			return '';
		}

		return sprintf(
			/* translators: %d is the number of products in the cart. */
			_n( '(%d toode)', '(%d toodet)', $count, 'sawerna' ),
			$count
		);
	}
}

if ( ! function_exists( 'sawerna_woocommerce_mini_cart_item_attributes' ) ) {
	/**
	 * Build formatted variation attributes for a mini-cart item.
	 *
	 * @param array        $cart_item Cart item data.
	 * @param WC_Product   $product Product object.
	 * @return array<int, array<string, string>>
	 */
	function sawerna_woocommerce_mini_cart_item_attributes( $cart_item, $product ) {
		$attributes = array();

		if ( empty( $cart_item['variation'] ) || ! is_array( $cart_item['variation'] ) ) {
			return $attributes;
		}

		foreach ( $cart_item['variation'] as $attribute_key => $attribute_value ) {
			if ( '' === $attribute_value || null === $attribute_value ) {
				continue;
			}

			$taxonomy = str_replace( 'attribute_', '', (string) $attribute_key );
			$label    = wc_attribute_label( $taxonomy, $product );
			$value    = (string) $attribute_value;

			if ( taxonomy_exists( $taxonomy ) ) {
				$term = get_term_by( 'slug', $value, $taxonomy );
				$value = $term && ! is_wp_error( $term ) ? $term->name : $value;
			}

			$attributes[] = array(
				'label' => $label,
				'value' => wptexturize( rawurldecode( $value ) ),
			);
		}

		return $attributes;
	}
}

if ( ! function_exists( 'sawerna_woocommerce_mini_cart_free_shipping_data' ) ) {
	/**
	 * Collect free shipping progress data for the mini-cart.
	 *
	 * @return array<string, float|int|string|bool>
	 */
	function sawerna_woocommerce_mini_cart_free_shipping_data() {
		$threshold = function_exists( 'sawerna_get_free_shipping_threshold' ) ? (float) sawerna_get_free_shipping_threshold() : 0;
		$subtotal  = 0.0;

		if ( WC()->cart ) {
			$subtotal = (float) WC()->cart->get_displayed_subtotal();
		}

		if ( $threshold <= 0 ) {
			return array(
				'threshold'    => 0.0,
				'subtotal'     => $subtotal,
				'remaining'    => 0.0,
				'progress'     => 0,
				'has_reached'  => true,
				'message_text' => '',
			);
		}

		$remaining = max( 0, $threshold - $subtotal );
		$progress  = min( 100, (int) round( ( $subtotal / $threshold ) * 100 ) );
		$has_reached = $remaining <= 0;
		$message_text = $has_reached
			? esc_html__( 'Said tasuta tarne!', 'sawerna' )
			: sprintf(
				/* translators: %s is the amount left until free shipping. */
				esc_html__( '%s puudu tasuta tarnest', 'sawerna' ),
				wp_strip_all_tags( html_entity_decode( wp_kses_post( wc_price( $remaining ) ), ENT_QUOTES, 'UTF-8' ) )
			);

		return array(
			'threshold'    => $threshold,
			'subtotal'     => $subtotal,
			'remaining'    => $remaining,
			'progress'     => $progress,
			'has_reached'  => $has_reached,
			'message_text' => $message_text,
		);
	}
}

if ( ! function_exists( 'sawerna_woocommerce_mini_cart_remove_icon' ) ) {
	/**
	 * Return inline SVG markup for the mini-cart remove icon.
	 *
	 * @return string
	 */
	function sawerna_woocommerce_mini_cart_remove_icon() {
		$svg_markup = sawerna_get_svg_inline( 'eemalda_kustuta.svg' );

		if ( ! empty( $svg_markup ) ) {
			if ( false === strpos( $svg_markup, 'focusable=' ) ) {
				$svg_markup = preg_replace( '/<svg\b/i', '<svg focusable="false"', $svg_markup, 1 );
			}

			if ( false === strpos( $svg_markup, 'aria-hidden=' ) ) {
				$svg_markup = preg_replace( '/<svg\b/i', '<svg aria-hidden="true"', $svg_markup, 1 );
			}

			$svg_markup = preg_replace( '/<path\b((?:(?!fill=)[^>])*)>/', '<path$1 fill="currentColor">', $svg_markup );

			return $svg_markup;
		}

		return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M9 3h6l1 2h4v2H4V5h4l1-2zm1 7h2v8h-2v-8zm4 0h2v8h-2v-8zM7 10h2v8H7v-8z"/></svg>';
	}
}

if ( ! function_exists( 'sawerna_woocommerce_mini_cart_items_markup' ) ) {
	/**
	 * Render the list of cart items shown in the mini-cart.
	 *
	 * @return void
	 */
	function sawerna_woocommerce_mini_cart_items_markup() {
		$cart_items = WC()->cart ? WC()->cart->get_cart() : array();

		if ( empty( $cart_items ) ) {
			$show_shop_btn = function_exists( 'sawerna_get_cart_settings' )
				? ( sawerna_get_cart_settings()['mini_cart_show_shop_button'] ?? '1' ) === '1'
				: true;
			?>
			<p class="sawerna-mini-cart__empty-message"><?php esc_html_e( 'Ostukorv on hetkel tühi.', 'sawerna' ); ?></p>
			<?php if ( $show_shop_btn ) : ?>
				<div class="sawerna-mini-cart__empty-actions">
					<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="sawerna-mini-cart__button sawerna-mini-cart__button--primary">
						<?php esc_html_e( 'Poodi', 'sawerna' ); ?>
					</a>
				</div>
			<?php endif; ?>
			<?php
			return;
		}
		?>
		<ul class="sawerna-mini-cart__items" aria-label="<?php esc_attr_e( 'Ostukorvi tooted', 'sawerna' ); ?>">
			<?php foreach ( $cart_items as $cart_item_key => $cart_item ) : ?>
				<?php
				$product = isset( $cart_item['data'] ) ? $cart_item['data'] : false;

				if ( ! $product || ! $product->exists() || $cart_item['quantity'] < 1 ) {
					continue;
				}

				$product_name = $product->get_name();
				$product_link = apply_filters( 'woocommerce_cart_item_permalink', $product->is_visible() ? $product->get_permalink() : '', $cart_item, $cart_item_key );
				$thumbnail    = $product->get_image( 'woocommerce_thumbnail' );
				$attributes   = sawerna_woocommerce_mini_cart_item_attributes( $cart_item, $product );
				?>
				<li class="sawerna-mini-cart__item" data-mini-cart-item="<?php echo esc_attr( $cart_item_key ); ?>" data-mini-cart-quantity="<?php echo esc_attr( (string) $cart_item['quantity'] ); ?>">
					<div class="sawerna-mini-cart__item-media">
						<?php if ( $product_link ) : ?>
							<a href="<?php echo esc_url( $product_link ); ?>" class="sawerna-mini-cart__image-link">
								<?php echo wp_kses_post( $thumbnail ); ?>
							</a>
						<?php else : ?>
							<span class="sawerna-mini-cart__image-link">
								<?php echo wp_kses_post( $thumbnail ); ?>
							</span>
						<?php endif; ?>
					</div>
					<div class="sawerna-mini-cart__item-content">
						<div class="sawerna-mini-cart__item-top">
							<?php if ( $product_link ) : ?>
								<a href="<?php echo esc_url( $product_link ); ?>" class="sawerna-mini-cart__item-title"><?php echo esc_html( $product_name ); ?></a>
							<?php else : ?>
								<span class="sawerna-mini-cart__item-title"><?php echo esc_html( $product_name ); ?></span>
							<?php endif; ?>
							<button
								type="button"
								class="sawerna-mini-cart__remove"
								data-mini-cart-remove="<?php echo esc_attr( $cart_item_key ); ?>"
								aria-label="<?php echo esc_attr( sprintf( __( 'Eemalda toode "%s" ostukorvist', 'sawerna' ), $product_name ) ); ?>"
							>
								<?php echo sawerna_woocommerce_mini_cart_remove_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</button>
						</div>

						<?php if ( ! empty( $attributes ) ) : ?>
							<div class="sawerna-mini-cart__attributes">
								<?php foreach ( $attributes as $attribute ) : ?>
									<p class="sawerna-mini-cart__attribute">
										<span class="sawerna-mini-cart__attribute-label"><?php echo esc_html( $attribute['label'] ); ?>:</span>
										<span class="sawerna-mini-cart__attribute-value"><?php echo esc_html( $attribute['value'] ); ?></span>
									</p>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

						<div class="sawerna-mini-cart__item-bottom">
							<div class="sawerna-mini-cart__quantity-controls" aria-label="<?php esc_attr_e( 'Muuda kogust', 'sawerna' ); ?>">
								<button type="button" class="sawerna-mini-cart__quantity-button" data-mini-cart-decrease="<?php echo esc_attr( $cart_item_key ); ?>" aria-label="<?php esc_attr_e( 'Vähenda kogust', 'sawerna' ); ?>">−</button>
								<span class="sawerna-mini-cart__quantity-value" data-mini-cart-quantity-value="1"><?php echo esc_html( (string) $cart_item['quantity'] ); ?></span>
								<button type="button" class="sawerna-mini-cart__quantity-button" data-mini-cart-increase="<?php echo esc_attr( $cart_item_key ); ?>" aria-label="<?php esc_attr_e( 'Suurenda kogust', 'sawerna' ); ?>">+</button>
							</div>
							<div class="sawerna-mini-cart__price">
								<?php echo wp_kses_post( WC()->cart->get_product_price( $product ) ); ?>
							</div>
						</div>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
	}
}

if ( ! function_exists( 'sawerna_woocommerce_mini_cart_footer_markup' ) ) {
	/**
	 * Render the subtotal, progress bar and action buttons in the mini-cart.
	 *
	 * @return void
	 */
	function sawerna_woocommerce_mini_cart_footer_markup() {
		$free_shipping = sawerna_woocommerce_mini_cart_free_shipping_data();
		$has_items      = WC()->cart && ! WC()->cart->is_empty();

		if ( ! $has_items ) {
			return;
		}
		?>
		<div class="sawerna-mini-cart__summary">
			<?php if ( ! empty( $free_shipping['message_text'] ) ) : ?>
				<div class="sawerna-mini-cart__shipping-progress" aria-live="polite">
					<p class="sawerna-mini-cart__shipping-text"><?php echo esc_html( $free_shipping['message_text'] ); ?></p>
					<div class="sawerna-mini-cart__shipping-track" aria-hidden="true">
						<span class="sawerna-mini-cart__shipping-bar" style="width: <?php echo esc_attr( (string) $free_shipping['progress'] ); ?>%;"></span>
					</div>
				</div>
			<?php endif; ?>

			<div class="sawerna-mini-cart__subtotal">
				<span class="sawerna-mini-cart__subtotal-label"><?php esc_html_e( 'Vahesumma:', 'sawerna' ); ?></span>
				<span class="sawerna-mini-cart__subtotal-value"><?php echo wp_kses_post( WC()->cart->get_cart_subtotal() ); ?></span>
			</div>

			<div class="sawerna-mini-cart__actions">
				<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="sawerna-mini-cart__button sawerna-mini-cart__button--primary"><?php esc_html_e( 'Vormista tellimus', 'sawerna' ); ?></a>
				<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="sawerna-mini-cart__button sawerna-mini-cart__button--secondary"><?php esc_html_e( 'Vaata ostukorvi', 'sawerna' ); ?></a>
			</div>
		</div>
		<?php
	}
}

if ( ! function_exists( 'sawerna_woocommerce_mini_cart_inner' ) ) {
	/**
	 * Render mini-cart inner markup.
	 *
	 * @return void
	 */
	function sawerna_woocommerce_mini_cart_inner() {
		$count_label = sawerna_woocommerce_mini_cart_count_label();
		?>
		<div class="sawerna-mini-cart__header">
			<h2 class="sawerna-mini-cart__title" id="sawerna-mini-cart-title">
				<?php esc_html_e( 'Ostukorv', 'sawerna' ); ?>
				<?php if ( '' !== $count_label ) : ?>
					<span class="sawerna-mini-cart__title-count"><?php echo esc_html( $count_label ); ?></span>
				<?php endif; ?>
			</h2>
			<button type="button" class="sawerna-mini-cart__close" data-mini-cart-close="1" aria-label="<?php esc_attr_e( 'Sulge ostukorv', 'sawerna' ); ?>">×</button>
		</div>
		<div class="sawerna-mini-cart__body">
			<?php sawerna_woocommerce_mini_cart_items_markup(); ?>
			<?php sawerna_woocommerce_mini_cart_footer_markup(); ?>
		</div>
		<?php
	}
}

if ( ! function_exists( 'sawerna_woocommerce_render_mini_cart' ) ) {
	/**
	 * Render the off-canvas mini-cart shell in the footer.
	 *
	 * @return void
	 */
	function sawerna_woocommerce_render_mini_cart() {
		?>
		<div class="sawerna-mini-cart" data-mini-cart="1" hidden>
			<button type="button" class="sawerna-mini-cart__overlay" data-mini-cart-close="1" aria-label="<?php esc_attr_e( 'Sulge ostukorv', 'sawerna' ); ?>"></button>
			<aside class="sawerna-mini-cart__panel" id="sawerna-mini-cart" role="dialog" aria-modal="true" aria-labelledby="sawerna-mini-cart-title" tabindex="-1">
				<div class="sawerna-mini-cart__panel-inner">
					<?php sawerna_woocommerce_mini_cart_inner(); ?>
				</div>
			</aside>
		</div>
		<?php
	}
}
add_action( 'wp_footer', 'sawerna_woocommerce_render_mini_cart' );

if ( ! function_exists( 'sawerna_woocommerce_get_empty_cart_url' ) ) {
	/**
	 * Return the nonce-protected clear cart URL.
	 *
	 * @return string
	 */
	function sawerna_woocommerce_get_empty_cart_url() {
		$url = add_query_arg( 'sawerna-empty-cart', '1', wc_get_cart_url() );

		return (string) wp_nonce_url( $url, 'sawerna_empty_cart', 'sawerna-empty-cart-nonce' );
	}
}

/**
 * Empty the cart via a nonce-protected cart page action.
 *
 * @return void
 */
function sawerna_woocommerce_handle_empty_cart() {
	if ( ! is_cart() ) {
		return;
	}

	if ( empty( $_GET['sawerna-empty-cart'] ) || ! WC()->cart ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$nonce = isset( $_GET['sawerna-empty-cart-nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['sawerna-empty-cart-nonce'] ) ) : '';

	if ( ! wp_verify_nonce( $nonce, 'sawerna_empty_cart' ) ) {
		return;
	}

	WC()->cart->empty_cart();

	wp_safe_redirect( wc_get_cart_url() );
	exit;
}
add_action( 'template_redirect', 'sawerna_woocommerce_handle_empty_cart', 20 );

if ( ! function_exists( 'sawerna_woocommerce_checkout_steps' ) ) {
	/**
	 * Return the checkout progress steps.
	 *
	 * @return array<string, array<string, string|int>>
	 */
	function sawerna_woocommerce_checkout_steps() {
		return array(
			'cart'     => array(
				'number' => 1,
				'label'  => __( 'Ostukorv', 'sawerna' ),
				'url'    => wc_get_cart_url(),
			),
			'checkout' => array(
				'number' => 2,
				'label'  => __( 'Tellimuse vormistamine', 'sawerna' ),
				'url'    => wc_get_checkout_url(),
			),
			'received' => array(
				'number' => 3,
				'label'  => __( 'Tellimus on täidetud', 'sawerna' ),
				'url'    => '',
			),
		);
	}
}

if ( ! function_exists( 'sawerna_woocommerce_render_checkout_steps' ) ) {
	/**
	 * Render the checkout progress steps.
	 *
	 * @param string $current Current step key.
	 * @return void
	 */
	function sawerna_woocommerce_render_checkout_steps( $current = 'cart' ) {
		$steps     = sawerna_woocommerce_checkout_steps();
		$step_keys = array_keys( $steps );
		$current   = isset( $steps[ $current ] ) ? $current : 'cart';
		$current_i = array_search( $current, $step_keys, true );
		?>
		<ol class="sawerna-checkout-steps" aria-label="<?php esc_attr_e( 'Tellimuse etapid', 'sawerna' ); ?>">
			<?php foreach ( $steps as $step_key => $step ) : ?>
				<?php
				$step_index = array_search( $step_key, $step_keys, true );
				$classes    = array( 'sawerna-checkout-steps__item' );

				if ( $step_key === $current ) {
					$classes[] = 'is-current';
				} elseif ( false !== $current_i && false !== $step_index && $step_index < $current_i ) {
					$classes[] = 'is-complete';
				}
				?>
				<li class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
					<span class="sawerna-checkout-steps__text">
						<span class="sawerna-checkout-steps__number"><?php echo esc_html( (string) $step['number'] ); ?>.</span>
						<span class="sawerna-checkout-steps__label"><?php echo esc_html( (string) $step['label'] ); ?></span>
					</span>

					<?php if ( 'received' !== $step_key ) : ?>
						<span class="sawerna-checkout-steps__separator" aria-hidden="true">→</span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ol>
		<?php
	}
}

if ( ! function_exists( 'sawerna_woocommerce_get_cart_recommendations' ) ) {
	/**
	 * Resolve product recommendations for the cart page.
	 *
	 * @param int $limit Maximum products to return.
	 * @return WC_Product[]
	 */
	function sawerna_woocommerce_get_cart_recommendations( $limit = 8 ) {
		if ( ! WC()->cart ) {
			return array();
		}

		$limit            = max( 1, absint( $limit ) );
		$cart             = WC()->cart->get_cart();
		$exclude_ids      = array();
		$cart_product_ids = array();
		$candidate_ids    = array_map( 'absint', WC()->cart->get_cross_sells() );

		foreach ( $cart as $cart_item ) {
			$product_id = isset( $cart_item['product_id'] ) ? absint( $cart_item['product_id'] ) : 0;

			if ( $product_id > 0 ) {
				$cart_product_ids[] = $product_id;
				$exclude_ids[]      = $product_id;
			}

			if ( ! empty( $cart_item['variation_id'] ) ) {
				$exclude_ids[] = absint( $cart_item['variation_id'] );
			}
		}

		$exclude_ids      = array_values( array_unique( array_filter( $exclude_ids ) ) );
		$cart_product_ids = array_values( array_unique( array_filter( $cart_product_ids ) ) );
		$candidate_ids    = array_values( array_unique( array_filter( $candidate_ids ) ) );

		if ( count( $candidate_ids ) < $limit ) {
			foreach ( $cart_product_ids as $product_id ) {
				$related_ids = wc_get_related_products( $product_id, $limit * 2, array_merge( $exclude_ids, $candidate_ids ) );

				foreach ( $related_ids as $related_id ) {
					$related_id = absint( $related_id );

					if ( $related_id < 1 || in_array( $related_id, $exclude_ids, true ) || in_array( $related_id, $candidate_ids, true ) ) {
						continue;
					}

					$candidate_ids[] = $related_id;

					if ( count( $candidate_ids ) >= $limit ) {
						break 2;
					}
				}
			}
		}

		$products = array();

		foreach ( $candidate_ids as $candidate_id ) {
			$product = wc_get_product( $candidate_id );

			if ( $product instanceof WC_Product && $product->is_visible() ) {
				$products[] = $product;
			}

			if ( count( $products ) >= $limit ) {
				break;
			}
		}

		if ( count( $products ) < $limit ) {
			$existing_product_ids = array();

			foreach ( $products as $existing_product ) {
				if ( $existing_product instanceof WC_Product ) {
					$existing_product_ids[] = $existing_product->get_id();
				}
			}

			$fallback_products = wc_get_products(
				array(
					'status'  => 'publish',
					'limit'   => $limit - count( $products ),
					'exclude' => array_merge( $exclude_ids, $existing_product_ids ),
					'orderby' => 'date',
					'order'   => 'DESC',
					'return'  => 'objects',
				)
			);

			foreach ( $fallback_products as $fallback_product ) {
				if ( $fallback_product instanceof WC_Product && $fallback_product->is_visible() ) {
					$products[] = $fallback_product;
				}
			}
		}

		return apply_filters( 'sawerna_cart_recommendation_products', array_slice( $products, 0, $limit ), $limit, $cart );
	}
}

/**
 * Related Products Args.
 *
 * @param array $args related products args.
 * @return array $args related products args.
 */
function sawerna_woocommerce_related_products_args( $args ) {
	$defaults = array(
		'posts_per_page' => 3,
		'columns'        => 3,
	);

	$args = wp_parse_args( $defaults, $args );

	return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'sawerna_woocommerce_related_products_args' );

/**
 * Remove default WooCommerce wrapper.
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

if ( ! function_exists( 'sawerna_woocommerce_wrapper_before' ) ) {
	/**
	 * Before Content.
	 *
	 * Wraps all WooCommerce content in wrappers which match the theme markup.
	 *
	 * @return void
	 */
	function sawerna_woocommerce_wrapper_before() {
		?>
			<main id="primary" class="site-main">
		<?php
	}
}
add_action( 'woocommerce_before_main_content', 'sawerna_woocommerce_wrapper_before' );

if ( ! function_exists( 'sawerna_woocommerce_wrapper_after' ) ) {
	/**
	 * After Content.
	 *
	 * Closes the wrapping divs.
	 *
	 * @return void
	 */
	function sawerna_woocommerce_wrapper_after() {
		?>
			</main><!-- #main -->
		<?php
	}
}
add_action( 'woocommerce_after_main_content', 'sawerna_woocommerce_wrapper_after' );

/**
 * Sample implementation of the WooCommerce Mini Cart.
 *
 * You can add the WooCommerce Mini Cart to header.php like so ...
 *
	<?php
		if ( function_exists( 'sawerna_woocommerce_header_cart' ) ) {
			sawerna_woocommerce_header_cart();
		}
	?>
 */

if ( ! function_exists( 'sawerna_woocommerce_cart_link' ) ) {
	/**
	 * Cart Link.
	 *
	 * Displayed a link to the cart including the number of items present and the cart total.
	 *
	 * @return void
	 */
	function sawerna_woocommerce_cart_link() {
		?>
		<a class="cart-contents" href="<?php echo esc_url( wc_get_cart_url() ); ?>" title="<?php esc_attr_e( 'View your shopping cart', 'sawerna' ); ?>">
			<?php
			$item_count_text = sprintf(
				/* translators: number of items in the mini cart. */
				_n( '%d item', '%d items', WC()->cart->get_cart_contents_count(), 'sawerna' ),
				WC()->cart->get_cart_contents_count()
			);
			?>
			<span class="amount"><?php echo wp_kses_data( WC()->cart->get_cart_subtotal() ); ?></span> <span class="count"><?php echo esc_html( $item_count_text ); ?></span>
		</a>
		<?php
	}
}

if ( ! function_exists( 'sawerna_woocommerce_header_cart' ) ) {
	/**
	 * Display Header Cart.
	 *
	 * @return void
	 */
	function sawerna_woocommerce_header_cart() {
		if ( is_cart() ) {
			$class = 'current-menu-item';
		} else {
			$class = '';
		}
		?>
		<ul id="site-header-cart" class="site-header-cart">
			<li class="<?php echo esc_attr( $class ); ?>">
				<?php sawerna_woocommerce_cart_link(); ?>
			</li>
			<li>
				<?php
				$instance = array(
					'title' => '',
				);

				the_widget( 'WC_Widget_Cart', $instance );
				?>
			</li>
		</ul>
		<?php
	}
}
