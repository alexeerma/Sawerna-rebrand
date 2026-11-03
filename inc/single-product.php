<?php
/**
 * Custom single product helpers.
 *
 * @package Sawerna
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue single product assets.
 *
 * @return void
 */
function sawerna_single_product_enqueue_assets() {
	if ( ! is_product() ) {
		return;
	}

	$dir = get_template_directory();
	$uri = get_template_directory_uri();

	$css_path = $dir . '/assets/css/single-product.css';
	$js_path  = $dir . '/js/single-product.js';

	if ( file_exists( $css_path ) ) {
		$version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $css_path ) : _S_VERSION;
		wp_enqueue_style( 'sawerna-single-product', $uri . '/assets/css/single-product.css', array( 'sawerna-style', 'sawerna-woocommerce-style', 'sawerna-product-card', 'sawerna-swatches' ), $version );
	}

	if ( file_exists( $js_path ) ) {
		$version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $js_path ) : _S_VERSION;
		wp_enqueue_script( 'sawerna-single-product', $uri . '/js/single-product.js', array(), $version, true );
		wp_script_add_data( 'sawerna-single-product', 'defer', true );
	}
}
add_action( 'wp_enqueue_scripts', 'sawerna_single_product_enqueue_assets', 30 );

/**
 * Register single product content metabox.
 *
 * @return void
 */
function sawerna_single_product_register_metabox() {
	add_meta_box(
		'sawerna-single-product-tabs',
		__( 'Single product tabs', 'sawerna' ),
		'sawerna_single_product_render_metabox',
		'product',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes_product', 'sawerna_single_product_register_metabox' );

/**
 * Render single product content metabox.
 *
 * @param WP_Post $post Product post object.
 * @return void
 */
function sawerna_single_product_render_metabox( $post ) {
	$additional = (string) get_post_meta( $post->ID, '_sawerna_tab_additional_info', true );
	$transport  = (string) get_post_meta( $post->ID, '_sawerna_tab_transport', true );
	$fabric     = (string) get_post_meta( $post->ID, '_sawerna_tab_fabric', true );

	wp_nonce_field( 'sawerna_single_product_tabs', 'sawerna_single_product_tabs_nonce' );
	?>
	<div class="sawerna-single-product-admin-fields">
		<p>
			<strong><?php esc_html_e( 'Lisainfo', 'sawerna' ); ?></strong>
		</p>
		<?php
		wp_editor(
			$additional,
			'_sawerna_tab_additional_info',
			array(
				'textarea_name' => '_sawerna_tab_additional_info',
				'textarea_rows' => 8,
				'media_buttons' => true,
				'teeny'         => false,
			)
		);
		?>

		<p>
			<strong><?php esc_html_e( 'Kauba transport', 'sawerna' ); ?></strong>
		</p>
		<?php
		wp_editor(
			$transport,
			'_sawerna_tab_transport',
			array(
				'textarea_name' => '_sawerna_tab_transport',
				'textarea_rows' => 8,
				'media_buttons' => true,
				'teeny'         => false,
			)
		);
		?>

		<p>
			<strong><?php esc_html_e( 'Kangavalik', 'sawerna' ); ?></strong>
		</p>
		<?php
		wp_editor(
			$fabric,
			'_sawerna_tab_fabric',
			array(
				'textarea_name' => '_sawerna_tab_fabric',
				'textarea_rows' => 8,
				'media_buttons' => true,
				'teeny'         => false,
			)
		);
		?>
	</div>
	<?php
}

/**
 * Save custom single-product tab content fields.
 *
 * @param int     $post_id Product post ID.
 * @param WP_Post $post Product post object.
 * @return void
 */
function sawerna_single_product_save_data_fields( $post_id, $post ) {
	if ( ! $post instanceof WP_Post || 'product' !== $post->post_type ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$nonce = isset( $_POST['sawerna_single_product_tabs_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['sawerna_single_product_tabs_nonce'] ) ) : '';
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'sawerna_single_product_tabs' ) ) {
		return;
	}

	$meta_keys = array(
		'_sawerna_tab_additional_info',
		'_sawerna_tab_transport',
		'_sawerna_tab_fabric',
	);

	foreach ( $meta_keys as $meta_key ) {
		$value = isset( $_POST[ $meta_key ] ) ? wp_kses_post( wp_unslash( $_POST[ $meta_key ] ) ) : '';
		update_post_meta( $post_id, $meta_key, $value );
	}
}
add_action( 'save_post_product', 'sawerna_single_product_save_data_fields', 20, 2 );

/**
 * Get gallery image IDs for the single product page.
 *
 * @param WC_Product $product Product instance.
 * @return int[]
 */
function sawerna_get_single_product_gallery_image_ids( $product ) {
	$image_ids = array();

	if ( $product && $product->get_image_id() ) {
		$image_ids[] = (int) $product->get_image_id();
	}

	if ( $product ) {
		$image_ids = array_merge( $image_ids, array_map( 'absint', $product->get_gallery_image_ids() ) );
	}

	$image_ids = array_values( array_unique( array_filter( $image_ids ) ) );

	return apply_filters( 'sawerna_single_product_gallery_image_ids', $image_ids, $product );
}

/**
 * Get payment option rows for the product summary.
 *
 * @param WC_Product $product Product instance.
 * @return array<int, array{title:string,detail:string}>
 */
function sawerna_get_single_product_payment_options( $product ) {
	$options = array(
		array(
			'title'  => __( 'Või al. 12x99.92 €', 'sawerna' ),
			'detail' => __( 'kasutades Inbank järelmaksu (0% intress, 0€ lepingutasu)', 'sawerna' ),
		),
	);

	return apply_filters( 'sawerna_single_product_payment_options', $options, $product );
}

/**
 * Get dummy feature rows shown before the add to cart section.
 *
 * @param WC_Product $product Product instance.
 * @return array<int, array{title:string,value:string,detail:string,icon:string}>
 */
function sawerna_get_single_product_feature_rows( $product ) {
	$rows = array(
		array(
			'title'  => __( 'Vali diivani nurga suund:', 'sawerna' ),
			'value'  => __( 'Vasak nurk', 'sawerna' ),
			'detail' => __( 'Näidisvalik kuni pluginiliidestus lisatakse.', 'sawerna' ),
			'icon'   => 'corner',
		),
		array(
			'title'  => __( 'Vali kattekangas:', 'sawerna' ),
			'value'  => __( 'Aurora 12', 'sawerna' ),
			'detail' => __( 'hinnagrupp 1', 'sawerna' ),
			'icon'   => 'fabric',
		),
		array(
			'title'  => __( 'Vali jalgade stiil ja värv:', 'sawerna' ),
			'value'  => __( 'Jala nimetus', 'sawerna' ),
			'detail' => __( 'must', 'sawerna' ),
			'icon'   => 'legs',
		),
	);

	return apply_filters( 'sawerna_single_product_feature_rows', $rows, $product );
}

/**
 * Get HTML for a small feature icon.
 *
 * @param string $icon Icon key.
 * @return string
 */
function sawerna_get_single_product_feature_icon_svg( $icon ) {
	switch ( $icon ) {
		case 'corner':
			return '<svg viewBox="0 0 160 110" aria-hidden="true" focusable="false"><path d="M14 12h128v24H48v58H14V12zm18 18v46h16V54h76V30H32z" fill="none" stroke="currentColor" stroke-width="4" stroke-linejoin="round"/></svg>';
		case 'fabric':
			return '<svg viewBox="0 0 160 110" aria-hidden="true" focusable="false"><rect x="10" y="18" width="140" height="74" rx="4" fill="currentColor" opacity="0.12"/><path d="M10 30l140 24M10 48l140 24M10 66l120 20M24 18l-14 26M52 18L10 92M84 18L22 92M116 18L54 92M148 18L86 92" fill="none" stroke="currentColor" stroke-width="3"/></svg>';
		case 'legs':
			return '<svg viewBox="0 0 90 120" aria-hidden="true" focusable="false"><path d="M46 8h10l-2 14H37L35 8h11zM34 22h24l-10 84c-1 8-11 8-12 0L34 22z" fill="currentColor"/><path d="M22 18h46" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round"/></svg>';
		default:
			return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"/></svg>';
	}
}

/**
 * Get dummy summary badges.
 *
 * @param WC_Product $product Product instance.
 * @return array<int, array{icon:string,text:string}>
 */
function sawerna_get_single_product_summary_badges( $product ) {
	$badges = array(
		array(
			'icon' => 'location',
			'text' => __( 'Tule tutvu näidisega Tallinna salongis', 'sawerna' ),
		),
		array(
			'icon' => 'delivery',
			'text' => __( 'Laos olemas (3 tk)', 'sawerna' ),
		),
	);

	return apply_filters( 'sawerna_single_product_summary_badges', $badges, $product );
}

/**
 * Get badge icon HTML.
 *
 * @param string $icon Icon key.
 * @return string
 */
function sawerna_get_single_product_summary_badge_icon_svg( $icon ) {
	if ( 'delivery' === $icon ) {
		return sawerna_get_svg_inline( 'Asset 18.svg' );
	}

	return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 22s7-7.2 7-12a7 7 0 1 0-14 0c0 4.8 7 12 7 12zm0-8.5A3.5 3.5 0 1 1 12 6a3.5 3.5 0 0 1 0 7.5z" fill="currentColor"/></svg>';
}

/**
 * Get summary tabs.
 *
 * @param WC_Product $product Product instance.
 * @return array<int, array{key:string,title:string,content:string}>
 */
function sawerna_get_single_product_tabs( $product ) {
	$tabs        = array();
	$description = '';
	$short_desc  = '';
	$additional_meta = (string) $product->get_meta( '_sawerna_tab_additional_info', true );
	$transport_meta  = (string) $product->get_meta( '_sawerna_tab_transport', true );
	$fabric_meta     = (string) $product->get_meta( '_sawerna_tab_fabric', true );

	if ( $product ) {
		$description = apply_filters( 'the_content', $product->get_description() );
		$short_desc  = apply_filters( 'woocommerce_short_description', $product->get_short_description() );
	}

	if ( '' === trim( wp_strip_all_tags( $description ) ) && '' !== trim( wp_strip_all_tags( $short_desc ) ) ) {
		$description = wpautop( $short_desc );
	}

	if ( '' === trim( wp_strip_all_tags( $description ) ) ) {
		$description = '<p>' . esc_html__( 'Kirjeldus lisatakse peagi.', 'sawerna' ) . '</p>';
	}

	ob_start();
	woocommerce_product_additional_information_tab();
	$additional_html = trim( (string) ob_get_clean() );
	if ( '' !== trim( wp_strip_all_tags( $additional_meta ) ) ) {
		$additional_html = apply_filters( 'the_content', $additional_meta );
	}
	if ( '' === wp_strip_all_tags( $additional_html ) ) {
		$additional_html = '<p>' . esc_html__( 'Lisainfo lisatakse peagi.', 'sawerna' ) . '</p>';
	}

	$transport_default = '' !== trim( wp_strip_all_tags( $transport_meta ) )
		? apply_filters( 'the_content', $transport_meta )
		: '<p>' . esc_html__( 'Tarneinfo lisatakse siia koos reaalse transpordiloogikaga.', 'sawerna' ) . '</p>';

	$transport_html = apply_filters(
		'sawerna_single_product_transport_content',
		$transport_default,
		$product
	);

	$fabric_default = '' !== trim( wp_strip_all_tags( $fabric_meta ) )
		? apply_filters( 'the_content', $fabric_meta )
		: '<p>' . esc_html__( 'Kangavaliku sisu on hetkel naidis. See asendatakse hiljem pluginist tuleva andmega.', 'sawerna' ) . '</p>';

	$fabric_html = apply_filters(
		'sawerna_single_product_fabric_content',
		$fabric_default,
		$product
	);

	ob_start();
	comments_template();
	$reviews_html = trim( (string) ob_get_clean() );

	$tabs[] = array(
		'key'     => 'description',
		'title'   => __( 'Toote kirjeldus', 'sawerna' ),
		'content' => $description,
	);
	$tabs[] = array(
		'key'     => 'additional_information',
		'title'   => __( 'Lisainfo', 'sawerna' ),
		'content' => $additional_html,
	);
	$tabs[] = array(
		'key'     => 'transport',
		'title'   => __( 'Kauba transport', 'sawerna' ),
		'content' => (string) $transport_html,
	);
	$tabs[] = array(
		'key'     => 'fabric',
		'title'   => __( 'Kangavalik', 'sawerna' ),
		'content' => (string) $fabric_html,
	);
	$tabs[] = array(
		'key'     => 'reviews',
		'title'   => sprintf(
			/* translators: %d is the amount of reviews. */
			__( 'Arvustused (%d)', 'sawerna' ),
			(int) $product->get_review_count()
		),
		'content' => $reviews_html,
	);

	return apply_filters( 'sawerna_single_product_tabs', $tabs, $product );
}

/**
 * Get resolved brand label for product.
 *
 * @param WC_Product $product Product instance.
 * @return string
 */
function sawerna_get_single_product_brand_label( $product ) {
	$brand = '';

	// Ordered brand taxonomies: admin-resolved first, then every known candidate.
	// Use the first taxonomy that actually has terms on this product, so an empty
	// native product_brand taxonomy is skipped in favour of the synced attribute.
	$candidates = array();

	if ( function_exists( 'sawerna_shop_get_registered_filter_taxonomy' ) ) {
		$resolved_taxonomy = (string) sawerna_shop_get_registered_filter_taxonomy( 'brand' );
		if ( '' !== $resolved_taxonomy ) {
			$candidates[] = $resolved_taxonomy;
		}
	}

	if ( function_exists( 'sawerna_shop_get_filter_registry' ) ) {
		$registry = sawerna_shop_get_filter_registry();
		if ( ! empty( $registry['brand']['taxonomies'] ) && is_array( $registry['brand']['taxonomies'] ) ) {
			$candidates = array_merge( $candidates, $registry['brand']['taxonomies'] );
		}
	}

	$candidates = array_values( array_unique( array_filter( array_map( 'sanitize_key', $candidates ) ) ) );

	foreach ( $candidates as $brand_taxonomy ) {
		if ( ! taxonomy_exists( $brand_taxonomy ) ) {
			continue;
		}

		$terms = get_the_terms( $product->get_id(), $brand_taxonomy );
		if ( ! is_wp_error( $terms ) && is_array( $terms ) && ! empty( $terms ) ) {
			$links = array();
			foreach ( $terms as $term ) {
				$link = get_term_link( $term );
				if ( is_wp_error( $link ) ) {
					$links[] = esc_html( $term->name );
				} else {
					$links[] = sprintf(
						'<a href="%1$s">%2$s</a>',
						esc_url( $link ),
						esc_html( $term->name )
					);
				}
			}
			$brand = implode( ', ', $links );
			break;
		}
	}

	if ( '' === $brand ) {
		$meta_keys = array( 'brand', '_brand', '_product_brand', 'manufacturer' );
		foreach ( $meta_keys as $meta_key ) {
			$value = $product->get_meta( $meta_key, true );
			if ( is_string( $value ) && '' !== trim( $value ) ) {
				$brand = trim( $value );
				break;
			}
		}
	}

	return (string) apply_filters( 'sawerna_single_product_brand_label', $brand, $product );
}

/**
 * Get linked upsell products.
 *
 * @param WC_Product $product Product instance.
 * @param int        $limit Maximum items.
 * @return WC_Product[]
 */
function sawerna_get_single_product_related_items( $product, $limit = 12 ) {
	$upsell_ids  = array_map( 'absint', $product->get_upsell_ids() );
	$product_ids = array_values( array_unique( array_filter( $upsell_ids ) ) );
	$product_ids = array_diff( $product_ids, array( $product->get_id() ) );

	if ( empty( $product_ids ) ) {
		return array();
	}

	$items = array();
	foreach ( array_slice( $product_ids, 0, $limit ) as $product_id ) {
		$item = wc_get_product( $product_id );
		if ( $item instanceof WC_Product && $item->is_visible() ) {
			$items[] = $item;
		}
	}

	return apply_filters( 'sawerna_single_product_related_items', $items, $product, $limit );
}

/**
 * Use the requested add to cart label on single product pages.
 *
 * @param string $text Button label.
 * @return string
 */
function sawerna_single_product_add_to_cart_text( $text ) {
	if ( is_product() ) {
		return __( 'Lisa ostukorvi', 'sawerna' );
	}

	return $text;
}
add_filter( 'woocommerce_product_single_add_to_cart_text', 'sawerna_single_product_add_to_cart_text' );

/**
 * Render quantity decrease button inside WooCommerce quantity fields on single product pages.
 *
 * @return void
 */
function sawerna_single_product_quantity_minus_button() {
	if ( ! is_product() ) {
		return;
	}

	echo '<button type="button" class="minus" aria-label="' . esc_attr__( 'Vahenda kogust', 'sawerna' ) . '">−</button>';
}
add_action( 'woocommerce_before_quantity_input_field', 'sawerna_single_product_quantity_minus_button' );

/**
 * Render quantity increase button inside WooCommerce quantity fields on single product pages.
 *
 * @return void
 */
function sawerna_single_product_quantity_plus_button() {
	if ( ! is_product() ) {
		return;
	}

	echo '<button type="button" class="plus" aria-label="' . esc_attr__( 'Suurenda kogust', 'sawerna' ) . '">+</button>';
}
add_action( 'woocommerce_after_quantity_input_field', 'sawerna_single_product_quantity_plus_button' );

/**
 * Start variation selectors empty on single product pages unless the URL explicitly preselects them.
 *
 * @param array $args Dropdown args.
 * @return array
 */
function sawerna_single_product_clear_default_variation_selection( $args ) {
	if ( ! is_product() ) {
		return $args;
	}

	$attribute = isset( $args['attribute'] ) ? (string) $args['attribute'] : '';
	if ( '' === $attribute ) {
		return $args;
	}

	$request_key = 'attribute_' . sanitize_title( $attribute );
	$request     = wp_unslash( $_REQUEST );

	if ( isset( $request[ $request_key ] ) && '' !== (string) $request[ $request_key ] ) {
		return $args;
	}

	$args['selected'] = '';

	return $args;
}
add_filter( 'woocommerce_dropdown_variation_attribute_options_args', 'sawerna_single_product_clear_default_variation_selection', 20 );
