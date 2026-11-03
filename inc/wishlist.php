<?php
/**
 * Wishlist functionality.
 *
 * @package Sawerna
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

if ( ! defined( 'SAWERNA_WISHLIST_COOKIE' ) ) {
	define( 'SAWERNA_WISHLIST_COOKIE', 'sawerna_wishlist' );
}

if ( ! defined( 'SAWERNA_WISHLIST_META_KEY' ) ) {
	define( 'SAWERNA_WISHLIST_META_KEY', 'sawerna_wishlist_items' );
}

/**
 * Get runtime wishlist cache.
 *
 * @return int[]|null
 */
function sawerna_get_wishlist_runtime_ids_cache(): ?array {
	if ( isset( $GLOBALS['sawerna_wishlist_runtime_ids'] ) && is_array( $GLOBALS['sawerna_wishlist_runtime_ids'] ) ) {
		return $GLOBALS['sawerna_wishlist_runtime_ids'];
	}
	return null;
}

/**
 * Set runtime wishlist cache.
 *
 * @param int[] $ids Wishlist product IDs.
 */
function sawerna_set_wishlist_runtime_ids_cache( array $ids ): void {
	$GLOBALS['sawerna_wishlist_runtime_ids'] = array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );
}

/**
 * Check if a wishlist plugin is active.
 *
 * @return bool
 */
function sawerna_is_wishlist_plugin_active(): bool {
	return ( defined( 'YITH_WCWL' ) && function_exists( 'YITH_WCWL' ) ) || function_exists( 'tinv_url_wishlist_default' );
}

/**
 * Check if the theme wishlist should be used.
 *
 * @return bool
 */
function sawerna_is_theme_wishlist_enabled(): bool {
	return class_exists( 'WooCommerce' ) && ! sawerna_is_wishlist_plugin_active();
}

/**
 * Get wishlist page ID from template or slug.
 *
 * @return int
 */
function sawerna_get_wishlist_page_id(): int {
	if ( array_key_exists( 'sawerna_wishlist_page_id_cache', $GLOBALS ) ) {
		return (int) $GLOBALS['sawerna_wishlist_page_id_cache'];
	}

	$templates = array( 'wishlist-template.php', 'template-wishlist.php' );
	foreach ( $templates as $template ) {
		$pages = get_pages(
			array(
				'meta_key'    => '_wp_page_template',
				'meta_value'  => $template,
				'number'      => 1,
				'post_status' => 'publish',
			)
		);
		if ( ! empty( $pages ) ) {
			$page_id                                   = (int) $pages[0]->ID;
			$GLOBALS['sawerna_wishlist_page_id_cache'] = $page_id;
			return $page_id;
		}
	}

	$page = get_page_by_path( 'lemmikud' );
	if ( ! $page ) {
		$page = get_page_by_path( 'wishlist' );
	}
	$page_id                                   = $page ? (int) $page->ID : 0;
	$GLOBALS['sawerna_wishlist_page_id_cache'] = $page_id;
	return $page_id;
}

/**
 * Create the wishlist page if WooCommerce is active and no wishlist page exists.
 * Recreates the page if it was deleted so /lemmikud/ always works.
 */
function sawerna_maybe_create_wishlist_page(): void {
	if ( ! sawerna_is_theme_wishlist_enabled() ) {
		return;
	}

	$existing_id = sawerna_get_wishlist_page_id();
	if ( $existing_id > 0 ) {
		return;
	}

	$page_id = wp_insert_post(
		array(
			'post_title'   => __( 'Lemmikud', 'sawerna' ),
			'post_name'    => 'lemmikud',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
			'post_author'  => get_current_user_id() ? get_current_user_id() : 1,
		),
		true
	);

	if ( is_wp_error( $page_id ) || $page_id <= 0 ) {
		return;
	}

	update_post_meta( $page_id, '_wp_page_template', 'wishlist-template.php' );
	$GLOBALS['sawerna_wishlist_page_id_cache'] = (int) $page_id;
}
add_action( 'init', 'sawerna_maybe_create_wishlist_page', 20 );

/**
 * Get wishlist item IDs from cookie.
 *
 * @return int[]
 */
function sawerna_get_wishlist_cookie_ids(): array {
	if ( empty( $_COOKIE[ SAWERNA_WISHLIST_COOKIE ] ) ) {
		return array();
	}
	$raw = sanitize_text_field( wp_unslash( $_COOKIE[ SAWERNA_WISHLIST_COOKIE ] ) );
	if ( '' === $raw ) {
		return array();
	}
	$ids = array_map( 'absint', explode( ',', $raw ) );
	return array_values( array_unique( array_filter( $ids ) ) );
}

/**
 * Get wishlist item IDs for a user.
 *
 * @param int $user_id User ID.
 * @return int[]
 */
function sawerna_get_wishlist_user_ids( int $user_id ): array {
	$stored = get_user_meta( $user_id, SAWERNA_WISHLIST_META_KEY, true );
	if ( is_string( $stored ) ) {
		$stored = explode( ',', $stored );
	}
	if ( ! is_array( $stored ) ) {
		$stored = array();
	}
	$ids = array_map( 'absint', $stored );
	return array_values( array_unique( array_filter( $ids ) ) );
}

/**
 * Get wishlist item IDs for current visitor.
 *
 * @return int[]
 */
function sawerna_get_wishlist_product_ids(): array {
	$ids_cache = sawerna_get_wishlist_runtime_ids_cache();
	if ( null !== $ids_cache ) {
		return $ids_cache;
	}

	if ( ! class_exists( 'WooCommerce' ) ) {
		$ids_cache = array();
		sawerna_set_wishlist_runtime_ids_cache( $ids_cache );
		return $ids_cache;
	}
	if ( is_user_logged_in() ) {
		$ids_cache = sawerna_get_wishlist_user_ids( get_current_user_id() );
		sawerna_set_wishlist_runtime_ids_cache( $ids_cache );
		return $ids_cache;
	}
	$ids_cache = sawerna_get_wishlist_cookie_ids();
	sawerna_set_wishlist_runtime_ids_cache( $ids_cache );
	return $ids_cache;
}

/**
 * Clear wishlist cookie.
 */
function sawerna_clear_wishlist_cookie() {
	$path   = defined( 'COOKIEPATH' ) ? COOKIEPATH : '/';
	$domain = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';
	$secure = is_ssl();
	setcookie( SAWERNA_WISHLIST_COOKIE, '', time() - DAY_IN_SECONDS, $path, $domain, $secure, true );
	unset( $_COOKIE[ SAWERNA_WISHLIST_COOKIE ] );
	sawerna_set_wishlist_runtime_ids_cache( array() );
}

/**
 * Save wishlist item IDs for current visitor.
 *
 * @param int[] $ids Wishlist product IDs.
 */
function sawerna_save_wishlist_product_ids( array $ids ) {
	$ids = array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );
	sawerna_set_wishlist_runtime_ids_cache( $ids );

	if ( is_user_logged_in() ) {
		update_user_meta( get_current_user_id(), SAWERNA_WISHLIST_META_KEY, $ids );
		return;
	}

	if ( empty( $ids ) ) {
		sawerna_clear_wishlist_cookie();
		return;
	}

	$value  = implode( ',', $ids );
	$expiry = time() + MONTH_IN_SECONDS;
	$path   = defined( 'COOKIEPATH' ) ? COOKIEPATH : '/';
	$domain = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';
	$secure = is_ssl();
	setcookie( SAWERNA_WISHLIST_COOKIE, $value, $expiry, $path, $domain, $secure, true );
	$_COOKIE[ SAWERNA_WISHLIST_COOKIE ] = $value;
}

/**
 * Merge guest wishlist into user account on login.
 */
function sawerna_maybe_merge_wishlist_cookie() {
	if ( ! sawerna_is_theme_wishlist_enabled() ) {
		return;
	}
	if ( ! is_user_logged_in() ) {
		return;
	}
	$cookie_ids = sawerna_get_wishlist_cookie_ids();
	if ( empty( $cookie_ids ) ) {
		return;
	}
	$user_ids = sawerna_get_wishlist_user_ids( get_current_user_id() );
	$merged   = array_values( array_unique( array_merge( $user_ids, $cookie_ids ) ) );
	sawerna_save_wishlist_product_ids( $merged );
	sawerna_clear_wishlist_cookie();
}
add_action( 'init', 'sawerna_maybe_merge_wishlist_cookie' );

/**
 * Get wishlist count.
 *
 * @return int
 */
function sawerna_get_wishlist_count(): int {
	return count( sawerna_get_wishlist_product_ids() );
}

/**
 * Check if a product is in the wishlist.
 *
 * @param int $product_id Product ID.
 * @return bool
 */
function sawerna_is_product_in_wishlist( int $product_id ): bool {
	if ( $product_id <= 0 ) {
		return false;
	}
	return in_array( $product_id, sawerna_get_wishlist_product_ids(), true );
}

/**
 * Get wishlist button markup.
 *
 * @param int    $product_id Product ID.
 * @param string $context Context string.
 * @return string
 */
function sawerna_get_wishlist_button_html( int $product_id = 0, string $context = 'loop' ): string {
	if ( ! sawerna_is_theme_wishlist_enabled() ) {
		return '';
	}
	if ( ! $product_id ) {
		$product_id = get_the_ID();
	}
	$product_id = absint( $product_id );
	if ( ! $product_id || 'product' !== get_post_type( $product_id ) ) {
		return '';
	}

	$in_wishlist  = sawerna_is_product_in_wishlist( $product_id );
	$label_add    = __( 'Add to wishlist', 'sawerna' );
	$label_remove = __( 'Remove from wishlist', 'sawerna' );
	$label        = $in_wishlist ? $label_remove : $label_add;

	$classes = array( 'sawerna-wishlist-button' );
	if ( '' !== $context ) {
		$classes[] = 'sawerna-wishlist-button--' . sanitize_html_class( $context );
	}
	if ( 'card' === $context ) {
		$classes[] = 'sawerna-wishlist-button--icon';
	}
	if ( $in_wishlist ) {
		$classes[] = 'is-active';
	}

	return sprintf(
		'<button type="button" class="%1$s" data-product-id="%2$d" data-in-wishlist="%3$s" aria-pressed="%4$s" aria-label="%7$s"><span class="sawerna-wishlist-button__icon" aria-hidden="true">%5$s</span><span class="sawerna-wishlist-button__text">%6$s</span></button>',
		esc_attr( implode( ' ', $classes ) ),
		$product_id,
		$in_wishlist ? '1' : '0',
		$in_wishlist ? 'true' : 'false',
		sawerna_header_icon_wishlist(),
		esc_html( $label ),
		esc_attr( $label )
	);
}

/**
 * Render wishlist button in templates.
 *
 * @param int    $product_id Product ID.
 * @param string $context Context string.
 */
function sawerna_render_wishlist_button( int $product_id = 0, string $context = 'loop' ) {
	$html = sawerna_get_wishlist_button_html( $product_id, $context );
	if ( '' !== $html ) {
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

/**
 * Add wishlist button in product loops.
 */
function sawerna_add_wishlist_button_loop() {
	sawerna_render_wishlist_button( get_the_ID(), 'loop' );
}
add_action( 'woocommerce_after_shop_loop_item', 'sawerna_add_wishlist_button_loop', 15 );

/**
 * Add wishlist button on single product pages.
 */
function sawerna_add_wishlist_button_single() {
	sawerna_render_wishlist_button( get_the_ID(), 'single' );
}
add_action( 'woocommerce_single_product_summary', 'sawerna_add_wishlist_button_single', 35 );

/**
 * Handle wishlist toggle (AJAX).
 */
function sawerna_wishlist_toggle() {
	if ( ! sawerna_is_theme_wishlist_enabled() ) {
		wp_send_json_error( array( 'message' => __( 'Wishlist is unavailable.', 'sawerna' ) ) );
	}

	check_ajax_referer( 'sawerna_wishlist', 'nonce' );
	$product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
	if ( ! $product_id || 'product' !== get_post_type( $product_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid product.', 'sawerna' ) ) );
	}

	$ids         = sawerna_get_wishlist_product_ids();
	$in_wishlist = in_array( $product_id, $ids, true );

	if ( $in_wishlist ) {
		$ids = array_values( array_diff( $ids, array( $product_id ) ) );
	} else {
		$ids[] = $product_id;
		$ids   = array_values( array_unique( $ids ) );
	}

	sawerna_save_wishlist_product_ids( $ids );

	wp_send_json_success(
		array(
			'in_wishlist'  => ! $in_wishlist,
			'count'        => count( $ids ),
			'items'        => $ids,
			'wishlist_url' => sawerna_get_wishlist_url(),
		)
	);
}
add_action( 'wp_ajax_sawerna_wishlist_toggle', 'sawerna_wishlist_toggle' );
add_action( 'wp_ajax_nopriv_sawerna_wishlist_toggle', 'sawerna_wishlist_toggle' );

/**
 * Get wishlist order options.
 *
 * @return array<string,string>
 */
function sawerna_wishlist_get_order_options(): array {
	$options = function_exists( 'sawerna_shop_get_order_options' ) ? sawerna_shop_get_order_options() : array();

	if ( isset( $options['menu_order'] ) ) {
		unset( $options['menu_order'] );
	}

	return ! empty( $options ) ? $options : array(
		'popularity' => __( 'Populaarsuse järgi', 'sawerna' ),
		'rating'     => __( 'Keskmise hinnangu järgi', 'sawerna' ),
		'date_desc'  => __( 'Uudsuse järgi', 'sawerna' ),
		'price_asc'  => __( 'Hind alates odavamast', 'sawerna' ),
		'price_desc' => __( 'Hind alates kallimast', 'sawerna' ),
	);
}

/**
 * Get current wishlist ordering.
 *
 * @return string
 */
function sawerna_wishlist_get_current_orderby(): string {
	$allowed = array_keys( sawerna_wishlist_get_order_options() );
	$orderby = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'date_desc';

	if ( ! in_array( $orderby, $allowed, true ) ) {
		$orderby = 'date_desc';
	}

	return $orderby;
}

/**
 * Build wishlist product query args.
 *
 * @param int[]  $ids Wishlist product IDs.
 * @param string $orderby Selected order.
 * @return array<string,mixed>
 */
function sawerna_wishlist_get_query_args( array $ids, string $orderby ): array {
	$query_args = array(
		'post_type'      => 'product',
		'post__in'       => $ids,
		'posts_per_page' => -1,
		'no_found_rows'  => true,
	);

	switch ( $orderby ) {
		case 'popularity':
			$query_args['meta_key'] = 'total_sales';
			$query_args['orderby']  = 'meta_value_num';
			$query_args['order']    = 'DESC';
			break;
		case 'rating':
			$query_args['meta_key'] = '_wc_average_rating';
			$query_args['orderby']  = 'meta_value_num';
			$query_args['order']    = 'DESC';
			break;
		case 'price_asc':
			$query_args['meta_key'] = '_price';
			$query_args['orderby']  = 'meta_value_num';
			$query_args['order']    = 'ASC';
			break;
		case 'price_desc':
			$query_args['meta_key'] = '_price';
			$query_args['orderby']  = 'meta_value_num';
			$query_args['order']    = 'DESC';
			break;
		case 'date_desc':
		default:
			$query_args['orderby'] = 'date';
			$query_args['order']   = 'DESC';
			break;
	}

	return $query_args;
}

/**
 * Wishlist shortcode output.
 *
 * @return string
 */
function sawerna_wishlist_shortcode(): string {
	if ( ! sawerna_is_theme_wishlist_enabled() ) {
		return '';
	}

	$ids = sawerna_get_wishlist_product_ids();
	if ( empty( $ids ) ) {
		return '<p class="sawerna-wishlist-empty">' . esc_html__( 'Your wishlist is empty.', 'sawerna' ) . '</p>';
	}

	$orderby = sawerna_wishlist_get_current_orderby();
	$query   = new WP_Query( sawerna_wishlist_get_query_args( $ids, $orderby ) );

	if ( ! $query->have_posts() ) {
		return '<p class="sawerna-wishlist-empty">' . esc_html__( 'Your wishlist is empty.', 'sawerna' ) . '</p>';
	}

	ob_start();
	?>
	<div class="sawerna-wishlist woocommerce">
		<div class="sawerna-wishlist__toolbar">
			<div class="sawerna-wishlist__result">
				<?php echo esc_html( sprintf( __( '%d toodet', 'sawerna' ), (int) $query->found_posts ) ); ?>
			</div>
			<form class="sawerna-wishlist__sort" method="get" action="<?php echo esc_url( get_permalink() ); ?>">
				<label class="sawerna-wishlist__sort-label" for="sawerna-wishlist-orderby"><?php esc_html_e( 'Järjesta', 'sawerna' ); ?></label>
				<select id="sawerna-wishlist-orderby" name="orderby" class="sawerna-wishlist__sort-select" onchange="this.form.submit()">
					<?php foreach ( sawerna_wishlist_get_order_options() as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $orderby, $value ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</form>
		</div>
		<div class="sawerna-product-card-grid sawerna-product-card-grid--wishlist">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				$product = wc_get_product( get_the_ID() );
				if ( ! $product ) {
					continue;
				}
				?>
				<?php echo sawerna_get_product_card_html( $product, 'wishlist' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endwhile; ?>
		</div>
	</div>
	<?php
	wp_reset_postdata();
	return (string) ob_get_clean();
}
add_shortcode( 'sawerna_wishlist', 'sawerna_wishlist_shortcode' );

/**
 * Get wishlist URL.
 * Checks for common wishlist plugins.
 *
 * @return string Wishlist URL or empty string.
 */
function sawerna_get_wishlist_url() {
	// Check for YITH WooCommerce Wishlist
	if ( defined( 'YITH_WCWL' ) && function_exists( 'YITH_WCWL' ) ) {
		$wishlist = call_user_func( 'YITH_WCWL' );
		if ( $wishlist && method_exists( $wishlist, 'get_wishlist_url' ) ) {
			return esc_url( $wishlist->get_wishlist_url() );
		}
	}
	// Check for TI WooCommerce Wishlist
	if ( function_exists( 'tinv_url_wishlist_default' ) ) {
		return esc_url( tinv_url_wishlist_default() );
	}
	$page_id = sawerna_get_wishlist_page_id();
	if ( $page_id ) {
		return esc_url( get_permalink( $page_id ) );
	}
	// Default fallback
	return esc_url( home_url( '/lemmikud/' ) );
}

/**
 * Enqueue wishlist assets.
 */
function sawerna_wishlist_enqueue_assets() {
	if ( is_admin() || ! sawerna_is_theme_wishlist_enabled() ) {
		return;
	}

	$wishlist_css_path = get_template_directory() . '/assets/css/wishlist.css';
	if ( file_exists( $wishlist_css_path ) ) {
		$wishlist_css_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $wishlist_css_path ) : _S_VERSION;
		wp_enqueue_style( 'sawerna-wishlist', get_template_directory_uri() . '/assets/css/wishlist.css', array( 'sawerna-style' ), $wishlist_css_version );
	}

	$wishlist_js_path = get_template_directory() . '/js/wishlist.js';
	if ( file_exists( $wishlist_js_path ) ) {
		$wishlist_js_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $wishlist_js_path ) : _S_VERSION;
		wp_enqueue_script( 'sawerna-wishlist', get_template_directory_uri() . '/js/wishlist.js', array(), $wishlist_js_version, true );
		wp_localize_script(
			'sawerna-wishlist',
			'sawernaWishlist',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'sawerna_wishlist' ),
				'items'   => sawerna_get_wishlist_product_ids(),
				'texts'   => array(
					'add'      => __( 'Add to wishlist', 'sawerna' ),
					'remove'   => __( 'Remove from wishlist', 'sawerna' ),
					'adding'   => __( 'Adding...', 'sawerna' ),
					'removing' => __( 'Removing...', 'sawerna' ),
					'empty'    => __( 'Your wishlist is empty.', 'sawerna' ),
					'error'    => __( 'Something went wrong. Please try again.', 'sawerna' ),
				),
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'sawerna_wishlist_enqueue_assets' );
