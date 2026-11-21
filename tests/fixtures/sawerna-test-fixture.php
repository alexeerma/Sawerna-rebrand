<?php
/**
 * Plugin Name: Sawerna test fixture (TESTS ONLY)
 * Description: Copied into mu-plugins by Playwright global setup and removed in teardown.
 *              Never deploy. Only active on *.local hosts with WP_DEBUG on.
 *
 * Endpoints (?sawerna_test=...):
 *   shortcode  Render [sawerna_products] inside a theme page. Params: layout, category, limit, menu_cat (slug).
 *              Response header X-Sawerna-Cache: HIT|MISS tells whether the output came from the cache.
 *   flush      Invalidate the shortcode cache.
 *   widgets    JSON list of registered Elementor widget names.
 *   widget     Render one Sawerna Elementor widget with its default settings. Param: type.
 *   blocks     Render serialized block markup server-side. Param: markup.
 * Cookie sawerna_layout_preview=<layout> switches every menu products block to that layout + category="menu".
 *
 * @package Sawerna
 */

// phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput

$sawerna_test_host = isset( $_SERVER['HTTP_HOST'] ) ? (string) $_SERVER['HTTP_HOST'] : '';
if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG || ! preg_match( '/\.local$/', $sawerna_test_host ) ) {
	return;
}

if ( ! empty( $_COOKIE['sawerna_layout_preview'] ) ) {
	add_filter( 'sawerna_shortcode_cache_enabled', '__return_false' );
	add_filter(
		'shortcode_atts_sawerna_products',
		function ( $out ) {
			$out['layout']   = sanitize_key( wp_unslash( $_COOKIE['sawerna_layout_preview'] ) );
			$out['category'] = 'menu';
			$out['limit']    = 4;
			return $out;
		}
	);
}


/*
 * Newsletter test support. Cookies come from the Playwright browser, so they also apply to its AJAX requests.
 * sawerna_test_nl     JSON settings merged over saved newsletter settings (plus "api_key", "webhook_secret").
 * sawerna_mock_http   HTTP status to return for every outgoing request; requests are logged instead of sent.
 */
if ( ! empty( $_COOKIE['sawerna_test_nl'] ) ) {
	// Process sync + coupon inline so tests can read _sync_status right after the AJAX response.
	add_filter( 'sawerna_newsletter_process_async', '__return_false' );
	$sawerna_test_nl = json_decode( wp_unslash( $_COOKIE['sawerna_test_nl'] ), true );
	if ( is_array( $sawerna_test_nl ) ) {
		$secrets = array(
			'api_key'        => 'sawerna_newsletter_api_key',
			'webhook_secret' => 'sawerna_newsletter_webhook_secret',
		);
		foreach ( $secrets as $key => $option ) {
			if ( isset( $sawerna_test_nl[ $key ] ) ) {
				$value = (string) $sawerna_test_nl[ $key ];
				add_filter( "pre_option_{$option}", fn() => $value );
				unset( $sawerna_test_nl[ $key ] );
			}
		}
		add_filter(
			'option_sawerna_newsletter_settings',
			fn( $saved ) => array_merge( is_array( $saved ) ? $saved : array(), $sawerna_test_nl )
		);
		add_filter( 'default_option_sawerna_newsletter_settings', fn() => $sawerna_test_nl );
	}
}
if ( ! empty( $_COOKIE['sawerna_mock_http'] ) ) {
	$sawerna_mock_status = absint( $_COOKIE['sawerna_mock_http'] );
	add_filter(
		'pre_http_request',
		function ( $pre, $args, $url ) use ( $sawerna_mock_status ) {
			if ( false !== strpos( (string) $url, 'wp-cron.php' ) ) {
				return $pre; // WordPress' own background cron ping: not part of the test.
			}
			$log   = get_transient( 'sawerna_test_http_log' );
			$log   = is_array( $log ) ? $log : array();
			$log[] = array(
				'url'     => $url,
				'method'  => $args['method'] ?? 'GET',
				'headers' => $args['headers'] ?? array(),
				'body'    => $args['body'] ?? '',
			);
			set_transient( 'sawerna_test_http_log', $log, HOUR_IN_SECONDS );
			return array(
				'headers'  => array(),
				'body'     => '{}',
				'response' => array( 'code' => $sawerna_mock_status, 'message' => 'mock' ),
				'cookies'  => array(),
				'filename' => null,
			);
		},
		1,
		3
	);
}

// Cookie sawerna_menu_preview=<url-encoded block markup>: every HTML block renders this markup instead (menu screenshots).
if ( ! empty( $_COOKIE['sawerna_menu_preview'] ) ) {
	$sawerna_preview_markup = (string) wp_unslash( $_COOKIE['sawerna_menu_preview'] );
	add_filter( 'sawerna_shortcode_cache_enabled', '__return_false' );
	add_filter( 'sawerna_html_block_content', fn() => do_shortcode( do_blocks( $sawerna_preview_markup ) ) );
}

add_action(
	'template_redirect',
	function () {
		if ( empty( $_GET['sawerna_test'] ) ) {
			return;
		}
		$action = sanitize_key( wp_unslash( $_GET['sawerna_test'] ) );
		nocache_headers();

		if ( 'blocks' === $action ) {
			// Render serialized block markup server-side (front-end context, no menu context).
			header( 'Content-Type: text/html; charset=utf-8' );
			echo do_shortcode( do_blocks( (string) wp_unslash( $_GET['markup'] ?? '' ) ) ); // Same order as the_content. // phpcs:ignore WordPress.Security.EscapeOutput
			exit;
		}

		if ( 'http_log' === $action ) {
			$log = get_transient( 'sawerna_test_http_log' );
			delete_transient( 'sawerna_test_http_log' );
			wp_send_json( is_array( $log ) ? $log : array() );
		}

		// Test data only: everything on the reserved @sawerna-test.invalid domain.
		if ( 'nl_cleanup' === $action ) {
			global $wpdb;
			$ids = get_posts(
				array(
					'post_type'      => 'sawerna_subscriber',
					'post_status'    => 'any',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					's'              => '@sawerna-test.invalid',
				)
			);
			foreach ( $ids as $id ) {
				if ( false !== strpos( (string) get_the_title( $id ), '@sawerna-test.invalid' ) ) {
					wp_delete_post( $id, true );
				}
			}
			$coupons = get_posts(
				array(
					'post_type'      => 'shop_coupon',
					'post_status'    => 'any',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'meta_query'     => array( array( 'key' => 'customer_email', 'value' => 'sawerna-test.invalid', 'compare' => 'LIKE' ) ),
				)
			);
			foreach ( $coupons as $id ) {
				wp_delete_post( $id, true );
			}
			$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_sawerna\_nl\_rl\_%' OR option_name LIKE '\_transient\_timeout\_sawerna\_nl\_rl\_%' OR option_name LIKE '\_transient\_sawerna\_nl\_coupons\_%' OR option_name LIKE '\_transient\_timeout\_sawerna\_nl\_coupons\_%'" );
			wp_send_json( array( 'subscribers' => count( $ids ), 'coupons' => count( $coupons ) ) );
		}

		if ( 'nl_subscriber' === $action ) {
			$email = sanitize_email( wp_unslash( $_GET['email'] ?? '' ) );
			if ( ! str_ends_with( $email, '@sawerna-test.invalid' ) ) {
				wp_die( 'Test emails only', 403 );
			}
			$found = get_posts( array( 'post_type' => 'sawerna_subscriber', 'post_status' => 'any', 'title' => $email, 'posts_per_page' => 5, 'fields' => 'ids' ) );
			$out   = array( 'count' => count( $found ) );
			if ( $found ) {
				$meta          = get_post_meta( $found[0] );
				$out['status'] = $meta['_sync_status'][0] ?? '';
				$out['code']   = $meta['_coupon_code'][0] ?? '';
				$out['skipped'] = $meta['_coupon_skipped'][0] ?? '';
				$out['normalized'] = $meta['_email_normalized'][0] ?? '';
				$out['raw_ip'] = isset( $_SERVER['REMOTE_ADDR'] ) && false !== strpos( wp_json_encode( $meta ), (string) $_SERVER['REMOTE_ADDR'] );
				if ( $out['code'] && function_exists( 'wc_get_coupon_id_by_code' ) ) {
					$coupon        = new WC_Coupon( $out['code'] );
					$out['coupon'] = array(
						'type'     => $coupon->get_discount_type(),
						'amount'   => (float) $coupon->get_amount(),
						'limit'    => $coupon->get_usage_limit(),
						'emails'   => $coupon->get_email_restrictions(),
						'expires'  => $coupon->get_date_expires() ? $coupon->get_date_expires()->getTimestamp() : 0,
					);
				}
			}
			wp_send_json( $out );
		}

		if ( 'patterns' === $action ) {
			$out = array();
			foreach ( WP_Block_Patterns_Registry::get_instance()->get_all_registered() as $pattern ) {
				if ( 0 === strpos( $pattern['name'], 'sawerna/' ) ) {
					$out[] = array(
						'name'       => $pattern['name'],
						'title'      => $pattern['title'],
						'postTypes'  => $pattern['postTypes'] ?? array(),
						'blockTypes' => $pattern['blockTypes'] ?? array(),
						'html'       => do_shortcode( do_blocks( $pattern['content'] ) ),
					);
				}
			}
			wp_send_json( $out );
		}

		if ( 'flush' === $action ) {
			sawerna_flush_shortcode_cache();
			wp_send_json( array( 'version' => sawerna_get_shortcode_cache_version() ) );
		}

		if ( 'widgets' === $action ) {
			$names = array();
			if ( class_exists( '\Elementor\Plugin' ) ) {
				$names = array_keys( \Elementor\Plugin::instance()->widgets_manager->get_widget_types() );
			}
			wp_send_json( array_values( array_filter( $names, fn( $n ) => 0 === strpos( $n, 'sawerna_' ) ) ) );
		}

		if ( 'widget' === $action ) {
			$type   = sanitize_key( wp_unslash( $_GET['type'] ?? '' ) );
			$widget = \Elementor\Plugin::instance()->elements_manager->create_element_instance(
				array(
					'elType'     => 'widget',
					'widgetType' => $type,
					'id'         => 'sawtest1',
					'settings'   => isset( $_GET['settings'] ) ? (array) json_decode( wp_unslash( $_GET['settings'] ), true ) : array(),
				)
			);
			header( 'Content-Type: text/html; charset=utf-8' );
			if ( $widget ) {
				$widget->print_element();
			}
			exit;
		}

		if ( 'shortcode' === $action ) {
			$atts = array();
			foreach ( array( 'layout', 'category', 'limit', 'on_sale', 'show_delivery', 'orderby', 'order' ) as $key ) {
				if ( isset( $_GET[ $key ] ) ) {
					$atts[ $key ] = sanitize_text_field( wp_unslash( $_GET[ $key ] ) );
				}
			}
			$context = 0;
			if ( ! empty( $_GET['menu_cat'] ) ) {
				$term    = get_term_by( 'slug', sanitize_title( wp_unslash( $_GET['menu_cat'] ) ), 'product_cat' );
				$context = $term ? (int) $term->term_id : 0;
			}

			$shortcode = '[sawerna_products';
			foreach ( $atts as $key => $value ) {
				$shortcode .= ' ' . $key . '="' . esc_attr( $value ) . '"';
			}
			$shortcode .= ']';

			sawerna_menu_context_term_id( $context );
			$parsed = shortcode_parse_atts( substr( $shortcode, 17, -1 ) );
			$hit    = false !== get_transient( sawerna_get_shortcode_cache_key( 'sawerna_products', is_array( $parsed ) ? $parsed : array() ) );
			$html   = do_shortcode( $shortcode );
			sawerna_menu_context_term_id( 0 );

			header( 'X-Sawerna-Cache: ' . ( $hit ? 'HIT' : 'MISS' ) );
			get_header();
			echo '<main id="sawerna-test-output" style="max-width:1000px;margin:40px auto;padding:0 20px">' . $html . '</main>'; // phpcs:ignore WordPress.Security.EscapeOutput
			get_footer();
			exit;
		}
	}
);
