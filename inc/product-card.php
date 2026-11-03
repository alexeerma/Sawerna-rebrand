<?php
/**
 * Product card helpers and admin fields.
 *
 * @package Sawerna
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register product label and swatch fields.
 */
function sawerna_product_card_general_fields() {
	echo '<div class="options_group">';

	woocommerce_wp_text_input(
		array(
			'id'          => '_sawerna_label_sale',
			'label'       => __( 'Sale label text', 'sawerna' ),
			'desc_tip'    => true,
			'description' => __( 'Optional override for the sale label text. Leave empty to show discount percentage.', 'sawerna' ),
		)
	);

	woocommerce_wp_text_input(
		array(
			'id'          => '_sawerna_label_sale_color',
			'label'       => __( 'Sale label color', 'sawerna' ),
			'desc_tip'    => true,
			'description' => __( 'Background color for the sale label. Use a hex value like #c94c43.', 'sawerna' ),
			'placeholder' => '#c94c43',
		)
	);

	woocommerce_wp_text_input(
		array(
			'id'          => '_sawerna_label_new',
			'label'       => __( 'New label text', 'sawerna' ),
			'desc_tip'    => true,
			'description' => __( 'Text for the “New” label (e.g., UUS).', 'sawerna' ),
		)
	);

	woocommerce_wp_text_input(
		array(
			'id'          => '_sawerna_label_new_color',
			'label'       => __( 'New label color', 'sawerna' ),
			'desc_tip'    => true,
			'description' => __( 'Background color for the new label. Use a hex value like #7d8c6a.', 'sawerna' ),
			'placeholder' => '#7d8c6a',
		)
	);

	woocommerce_wp_text_input(
		array(
			'id'          => '_sawerna_label_design',
			'label'       => __( 'Design label text', 'sawerna' ),
			'desc_tip'    => true,
			'description' => __( 'Text for the “Design yourself” label.', 'sawerna' ),
		)
	);

	woocommerce_wp_text_input(
		array(
			'id'          => '_sawerna_label_design_color',
			'label'       => __( 'Design label color', 'sawerna' ),
			'desc_tip'    => true,
			'description' => __( 'Background color for the design label. Use a hex value like #8c7a5f.', 'sawerna' ),
			'placeholder' => '#8c7a5f',
		)
	);

	woocommerce_wp_text_input(
		array(
			'id'          => '_sawerna_label_custom',
			'label'       => __( 'Custom label text', 'sawerna' ),
			'desc_tip'    => true,
			'description' => __( 'Optional extra label text.', 'sawerna' ),
		)
	);

	woocommerce_wp_text_input(
		array(
			'id'          => '_sawerna_label_custom_color',
			'label'       => __( 'Custom label color', 'sawerna' ),
			'desc_tip'    => true,
			'description' => __( 'Background color for the custom label. Use a hex value like #333333.', 'sawerna' ),
			'placeholder' => '#333333',
		)
	);

	woocommerce_wp_text_input(
		array(
			'id'          => '_sawerna_swatch_attribute',
			'label'       => __( 'Swatch attribute (slug)', 'sawerna' ),
			'desc_tip'    => true,
			'description' => __( 'Use a variation attribute slug like pa_kangas, pa_fabric, or pa_color. Leave empty to auto-detect.', 'sawerna' ),
		)
	);

	echo '</div>';
}
add_action( 'woocommerce_product_options_general_product_data', 'sawerna_product_card_general_fields' );

/**
 * Register the archive interior image metabox.
 *
 * @return void
 */
function sawerna_product_card_register_image_metabox() {
	add_meta_box(
		'sawerna-product-archive-interior-image',
		__( 'Archive interior image', 'sawerna' ),
		'sawerna_product_card_render_image_metabox',
		'product',
		'side',
		'low'
	);
}
add_action( 'add_meta_boxes_product', 'sawerna_product_card_register_image_metabox' );

/**
 * Render the archive interior image metabox.
 *
 * @param WP_Post $post Product post object.
 * @return void
 */
function sawerna_product_card_render_image_metabox( $post ) {
	$interior_image_id  = absint( get_post_meta( $post->ID, '_sawerna_archive_interior_image_id', true ) );
	$interior_image_src = $interior_image_id ? wp_get_attachment_image_url( $interior_image_id, 'thumbnail' ) : '';

	wp_nonce_field( 'sawerna_archive_interior_image', 'sawerna_archive_interior_image_nonce' );
	?>
	<div class="sawerna-product-admin-image-metabox">
		<p>
			<?php esc_html_e( 'Used on the product archive when “Interjööriga pildid” is enabled. If empty, the normal featured image is shown.', 'sawerna' ); ?>
		</p>
		<div class="sawerna-product-admin-image" data-product-admin-image="archive-interior">
			<input type="hidden" id="_sawerna_archive_interior_image_id" name="_sawerna_archive_interior_image_id" value="<?php echo esc_attr( (string) $interior_image_id ); ?>">
			<div class="sawerna-product-admin-image__preview" data-product-admin-image-preview="1">
				<?php if ( $interior_image_src ) : ?>
					<img src="<?php echo esc_url( $interior_image_src ); ?>" alt="" style="max-width:100%; height:auto;">
				<?php endif; ?>
			</div>
			<p class="sawerna-product-admin-image__actions">
				<button type="button" class="button" data-product-admin-image-upload="1"><?php esc_html_e( 'Choose image', 'sawerna' ); ?></button>
				<button type="button" class="button" data-product-admin-image-remove="1"<?php echo $interior_image_id ? '' : ' style="display:none;"'; ?>><?php esc_html_e( 'Remove image', 'sawerna' ); ?></button>
			</p>
		</div>
	</div>
	<?php
}

/**
 * Register delivery fields in shipping tab (simple products).
 */
function sawerna_product_card_shipping_fields() {
	echo '<div class="options_group">';

	woocommerce_wp_select(
		array(
			'id'          => '_sawerna_delivery_status',
			'label'       => __( 'Delivery status', 'sawerna' ),
			'desc_tip'    => true,
			'description' => __( 'Shown on product cards.', 'sawerna' ),
			'options'     => array(
				''           => __( '— Select —', 'sawerna' ),
				'instock'    => __( 'Kohe olemas', 'sawerna' ),
				'order'      => __( 'Tellimisel', 'sawerna' ),
				'outofstock' => __( 'Laost otsas', 'sawerna' ),
			),
		)
	);

	woocommerce_wp_text_input(
		array(
			'id'                => '_sawerna_delivery_weeks',
			'label'             => __( 'Delivery weeks', 'sawerna' ),
			'type'              => 'number',
			'custom_attributes' => array(
				'min'  => 0,
				'step' => 1,
			),
			'desc_tip'          => true,
			'description'       => __( 'Only used when delivery status is “Tellimisel”.', 'sawerna' ),
		)
	);

	echo '</div>';
}
add_action( 'woocommerce_product_options_shipping', 'sawerna_product_card_shipping_fields' );

/**
 * Register delivery fields for variations.
 *
 * @param int     $loop Loop index.
 * @param array   $variation_data Variation data.
 * @param WP_Post $variation Variation post object.
 */
function sawerna_product_card_variation_fields( $loop, $variation_data, $variation ) {
	$status = get_post_meta( $variation->ID, '_sawerna_delivery_status', true );
	$weeks  = get_post_meta( $variation->ID, '_sawerna_delivery_weeks', true );

	woocommerce_wp_select(
		array(
			'id'          => "sawerna_delivery_status[{$loop}]",
			'label'       => __( 'Delivery status', 'sawerna' ),
			'value'       => $status,
			'options'     => array(
				''           => __( '— Select —', 'sawerna' ),
				'instock'    => __( 'Kohe olemas', 'sawerna' ),
				'order'      => __( 'Tellimisel', 'sawerna' ),
				'outofstock' => __( 'Laost otsas', 'sawerna' ),
			),
			'wrapper_class' => 'form-row form-row-first',
		)
	);

	woocommerce_wp_text_input(
		array(
			'id'                => "sawerna_delivery_weeks[{$loop}]",
			'label'             => __( 'Delivery weeks', 'sawerna' ),
			'type'              => 'number',
			'value'             => $weeks,
			'custom_attributes' => array(
				'min'  => 0,
				'step' => 1,
			),
			'wrapper_class'     => 'form-row form-row-last',
		)
	);
}
add_action( 'woocommerce_product_after_variable_attributes', 'sawerna_product_card_variation_fields', 10, 3 );

/**
 * Save product-level fields.
 *
 * @param WC_Product $product Product instance.
 */
function sawerna_product_card_save_fields( $product ) {
	$text_fields = array(
		'_sawerna_label_sale',
		'_sawerna_label_new',
		'_sawerna_label_design',
		'_sawerna_label_custom',
	);

	$color_fields = array(
		'_sawerna_label_sale_color',
		'_sawerna_label_new_color',
		'_sawerna_label_design_color',
		'_sawerna_label_custom_color',
	);

	foreach ( $text_fields as $field ) {
		$value = isset( $_POST[ $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) : '';
		$product->update_meta_data( $field, $value );
	}

	foreach ( $color_fields as $field ) {
		$value = isset( $_POST[ $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) : '';
		$color = is_string( $value ) ? sanitize_hex_color( trim( $value ) ) : '';
		$product->update_meta_data( $field, $color ? $color : '' );
	}

	$swatch_attribute = isset( $_POST['_sawerna_swatch_attribute'] ) ? sanitize_key( wp_unslash( $_POST['_sawerna_swatch_attribute'] ) ) : '';
	$product->update_meta_data( '_sawerna_swatch_attribute', $swatch_attribute );

	$delivery_status = isset( $_POST['_sawerna_delivery_status'] ) ? sanitize_text_field( wp_unslash( $_POST['_sawerna_delivery_status'] ) ) : '';
	$allowed_status  = array( 'instock', 'order', 'outofstock' );
	$product->update_meta_data( '_sawerna_delivery_status', in_array( $delivery_status, $allowed_status, true ) ? $delivery_status : '' );

	$delivery_weeks = isset( $_POST['_sawerna_delivery_weeks'] ) ? absint( wp_unslash( $_POST['_sawerna_delivery_weeks'] ) ) : 0;
	$product->update_meta_data( '_sawerna_delivery_weeks', $delivery_weeks ? (string) $delivery_weeks : '' );
}
add_action( 'woocommerce_admin_process_product_object', 'sawerna_product_card_save_fields' );

/**
 * Save archive interior image metabox.
 *
 * @param int     $post_id Product post ID.
 * @param WP_Post $post    Product post object.
 * @return void
 */
function sawerna_product_card_save_image_metabox( $post_id, $post ) {
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

	$nonce = isset( $_POST['sawerna_archive_interior_image_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['sawerna_archive_interior_image_nonce'] ) ) : '';
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'sawerna_archive_interior_image' ) ) {
		return;
	}

	$interior_image_id = isset( $_POST['_sawerna_archive_interior_image_id'] ) ? absint( wp_unslash( $_POST['_sawerna_archive_interior_image_id'] ) ) : 0;
	update_post_meta( $post_id, '_sawerna_archive_interior_image_id', $interior_image_id );
}
add_action( 'save_post_product', 'sawerna_product_card_save_image_metabox', 10, 2 );

/**
 * Save variation fields.
 *
 * @param int $variation_id Variation ID.
 * @param int $loop Loop index.
 */
function sawerna_product_card_save_variation_fields( $variation_id, $loop ) {
	if ( ! current_user_can( 'edit_post', absint( $variation_id ) ) ) {
		return;
	}

	$status = isset( $_POST['sawerna_delivery_status'][ $loop ] ) ? sanitize_text_field( wp_unslash( $_POST['sawerna_delivery_status'][ $loop ] ) ) : '';
	$allowed_status = array( 'instock', 'order', 'outofstock' );
	if ( ! in_array( $status, $allowed_status, true ) ) {
		$status = '';
	}

	$weeks = isset( $_POST['sawerna_delivery_weeks'][ $loop ] ) ? absint( wp_unslash( $_POST['sawerna_delivery_weeks'][ $loop ] ) ) : 0;

	update_post_meta( $variation_id, '_sawerna_delivery_status', $status );
	update_post_meta( $variation_id, '_sawerna_delivery_weeks', $weeks );
}
add_action( 'woocommerce_save_product_variation', 'sawerna_product_card_save_variation_fields', 10, 2 );

/**
 * Enqueue product card styles.
 */
function sawerna_product_card_enqueue_assets() {
	if ( is_admin() || ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	$product_card_css_path = get_template_directory() . '/assets/css/product-card.css';
	if ( file_exists( $product_card_css_path ) ) {
		$product_card_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $product_card_css_path ) : _S_VERSION;
		wp_enqueue_style( 'sawerna-product-card', get_template_directory_uri() . '/assets/css/product-card.css', array( 'sawerna-style', 'sawerna-woocommerce-style' ), $product_card_version );
	}
}
add_action( 'wp_enqueue_scripts', 'sawerna_product_card_enqueue_assets', 20 );

/**
 * Enqueue product card admin assets.
 *
 * @param string $hook Current admin page hook.
 * @return void
 */
function sawerna_product_card_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'product' !== $screen->post_type ) {
		return;
	}

	$script_path = get_template_directory() . '/assets/js/product-card-admin.js';
	if ( ! file_exists( $script_path ) ) {
		return;
	}

	wp_enqueue_media();

	$version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $script_path ) : _S_VERSION;
	wp_enqueue_script( 'sawerna-product-card-admin', get_template_directory_uri() . '/assets/js/product-card-admin.js', array( 'jquery' ), $version, true );
	wp_localize_script(
		'sawerna-product-card-admin',
		'sawernaProductCardAdmin',
		array(
			'uploadTitle'  => __( 'Select archive interior image', 'sawerna' ),
			'uploadButton' => __( 'Use this image', 'sawerna' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'sawerna_product_card_admin_assets' );

/**
 * Resolve current archive interior-image state.
 *
 * @return bool
 */
function sawerna_shop_is_interior_images_enabled(): bool {
	$state = isset( $GLOBALS['sawerna_shop_render_state'] ) && is_array( $GLOBALS['sawerna_shop_render_state'] )
		? $GLOBALS['sawerna_shop_render_state']
		: array();

	return ! empty( $state['interior_images'] );
}

/**
 * Resolve which image should be shown on product cards.
 *
 * @param WC_Product $product Product.
 * @param string     $context Render context.
 * @return int
 */
function sawerna_get_product_card_image_id( $product, string $context = 'loop' ): int {
	$image_id = (int) $product->get_image_id();

	if ( 'archive' !== $context || ! sawerna_shop_is_interior_images_enabled() ) {
		return $image_id;
	}

	$interior_image_id = (int) get_post_meta( $product->get_id(), '_sawerna_archive_interior_image_id', true );
	if ( $interior_image_id > 0 && wp_get_attachment_image_url( $interior_image_id, 'thumbnail' ) ) {
		return $interior_image_id;
	}

	return $image_id;
}

/**
 * Get default product label colors.
 *
 * @return array<string, string>
 */
function sawerna_get_product_label_default_colors(): array {
	return array(
		'sale'   => '#c94c43',
		'new'    => '#7d8c6a',
		'design' => '#8c7a5f',
		'custom' => '#333333',
	);
}

/**
 * Get sale label text.
 *
 * @param WC_Product $product Product.
 * @return string
 */
function sawerna_get_sale_label_text( $product ): string {
	if ( ! $product->is_on_sale() ) {
		return '';
	}

	$override = $product->get_meta( '_sawerna_label_sale', true );
	if ( $override ) {
		return $override;
	}

	$discount = 0;

	if ( $product instanceof WC_Product_Variable ) {
		$prices = $product->get_variation_prices( true );
		foreach ( $prices['sale_price'] as $variation_id => $sale_price ) {
			$regular_price = isset( $prices['regular_price'][ $variation_id ] ) ? (float) $prices['regular_price'][ $variation_id ] : 0;
			$sale_price    = (float) $sale_price;
			if ( $regular_price > 0 && $sale_price > 0 && $sale_price < $regular_price ) {
				$current_discount = (int) round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
				if ( $current_discount > $discount ) {
					$discount = $current_discount;
				}
			}
		}
	} else {
		$regular_price = (float) $product->get_regular_price();
		$sale_price    = (float) $product->get_sale_price();
		if ( $regular_price > 0 && $sale_price > 0 && $sale_price < $regular_price ) {
			$discount = (int) round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
		}
	}

	if ( $discount > 0 ) {
		return '-' . $discount . '%';
	}

	return __( 'Sale', 'sawerna' );
}

/**
 * Get label list for a product.
 *
 * @param WC_Product $product Product.
 * @return array<int, array{type:string,text:string,color:string}>
 */
function sawerna_get_product_labels( $product ): array {
	$labels         = array();
	$default_colors = sawerna_get_product_label_default_colors();

	$sale_text = sawerna_get_sale_label_text( $product );
	if ( $sale_text ) {
		$labels[] = array(
			'type'  => 'sale',
			'text'  => $sale_text,
			'color' => (string) ( $product->get_meta( '_sawerna_label_sale_color', true ) ?: $default_colors['sale'] ),
		);
	}

	$new_text = $product->get_meta( '_sawerna_label_new', true );
	if ( $new_text ) {
		$labels[] = array(
			'type'  => 'new',
			'text'  => $new_text,
			'color' => (string) ( $product->get_meta( '_sawerna_label_new_color', true ) ?: $default_colors['new'] ),
		);
	}

	$design_text = $product->get_meta( '_sawerna_label_design', true );
	if ( $design_text ) {
		$labels[] = array(
			'type'  => 'design',
			'text'  => $design_text,
			'color' => (string) ( $product->get_meta( '_sawerna_label_design_color', true ) ?: $default_colors['design'] ),
		);
	}

	$custom_text = $product->get_meta( '_sawerna_label_custom', true );
	if ( $custom_text ) {
		$labels[] = array(
			'type'  => 'custom',
			'text'  => $custom_text,
			'color' => (string) ( $product->get_meta( '_sawerna_label_custom_color', true ) ?: $default_colors['custom'] ),
		);
	}

	return $labels;
}

/**
 * Get delivery data for a product.
 *
 * @param WC_Product $product Product.
 * @return array{status:string,weeks:int}
 */
function sawerna_get_delivery_data( $product ): array {
	$status = $product->get_meta( '_sawerna_delivery_status', true );
	$weeks  = (int) $product->get_meta( '_sawerna_delivery_weeks', true );

	if ( ! $status ) {
		$status = $product->is_in_stock() ? 'instock' : 'outofstock';
	}

	return array(
		'status' => $status,
		'weeks'  => $weeks,
	);
}

/**
 * Format delivery text.
 *
 * @param string $status Status.
 * @param int    $weeks Weeks.
 * @return string
 */
function sawerna_format_delivery_text( string $status, int $weeks ): string {
	if ( 'instock' === $status ) {
		return __( 'Kohe olemas', 'sawerna' );
	}

	if ( 'order' === $status ) {
		if ( $weeks > 0 ) {
			return sprintf( __( 'Tellimisel %s nädalat', 'sawerna' ), $weeks );
		}
		return __( 'Tellimisel', 'sawerna' );
	}

	return __( 'Laost otsas', 'sawerna' );
}

/**
 * Get delivery text for variable product.
 *
 * @param WC_Product_Variable $product Product.
 * @return string
 */
function sawerna_get_variable_delivery_text( $product ): string {
	$variation_ids = $product->get_children();
	if ( empty( $variation_ids ) ) {
		$data = sawerna_get_delivery_data( $product );
		return sawerna_format_delivery_text( $data['status'], $data['weeks'] );
	}

	$statuses = array();
	$weeks    = array();

	foreach ( $variation_ids as $variation_id ) {
		$variation = wc_get_product( $variation_id );
		if ( ! $variation ) {
			continue;
		}
		$data = sawerna_get_delivery_data( $variation );
		$statuses[] = $data['status'];
		if ( 'order' === $data['status'] && $data['weeks'] > 0 ) {
			$weeks[] = $data['weeks'];
		}
	}

	if ( in_array( 'instock', $statuses, true ) ) {
		return sawerna_format_delivery_text( 'instock', 0 );
	}

	if ( in_array( 'order', $statuses, true ) ) {
		$unique_weeks = array_values( array_unique( $weeks ) );
		if ( 1 === count( $unique_weeks ) ) {
			return sawerna_format_delivery_text( 'order', (int) $unique_weeks[0] );
		}
		return sawerna_format_delivery_text( 'order', 0 );
	}

	return sawerna_format_delivery_text( 'outofstock', 0 );
}

/**
 * Get delivery text for a product.
 *
 * @param WC_Product $product Product.
 * @return string
 */
function sawerna_get_product_delivery_text( $product ): string {
	if ( $product->is_type( 'variable' ) ) {
		return sawerna_get_variable_delivery_text( $product );
	}

	$data = sawerna_get_delivery_data( $product );
	return sawerna_format_delivery_text( $data['status'], $data['weeks'] );
}

/**
 * Get swatch attribute key for a product.
 *
 * @param WC_Product $product Product.
 * @return string
 */
function sawerna_get_product_swatch_attribute_key( $product ): string {
	$manual = $product->get_meta( '_sawerna_swatch_attribute', true );
	if ( $manual ) {
		return $manual;
	}

	if ( ! ( $product instanceof WC_Product_Variable ) ) {
		return '';
	}

	$attributes = $product->get_variation_attributes();
	if ( empty( $attributes ) ) {
		return '';
	}

	$preferred = array(
		'pa_kangas',
		'pa_fabric',
		'pa_color',
		'pa_colour',
		'pa_varv',
		'pa_varvitoon',
	);

	foreach ( $preferred as $key ) {
		if ( isset( $attributes[ $key ] ) ) {
			return $key;
		}
	}

	$keys = array_keys( $attributes );
	return $keys ? $keys[0] : '';
}

/**
 * Get swatch color for a term.
 *
 * @param WP_Term $term Term.
 * @return string
 */
function sawerna_get_swatch_color_for_term( $term ): string {
	$keys = array(
		'sawerna_swatch_color',  // Native key written by this theme's swatch system.
		'color',
		'color_hex',
		'swatch_color',
		'attribute_color',
		'product_attribute_color',
	);

	foreach ( $keys as $key ) {
		$value = get_term_meta( $term->term_id, $key, true );
		if ( is_string( $value ) && '' !== trim( $value ) ) {
			return trim( $value );
		}
	}

	return '';
}

/**
 * Get swatch image ID for a term.
 *
 * @param WP_Term $term Term.
 * @return int
 */
function sawerna_get_swatch_image_id_for_term( $term ): int {
	$image_id = get_term_meta( $term->term_id, 'sawerna_swatch_image', true );

	return absint( $image_id );
}

/**
 * Render swatches for a product.
 *
 * @param WC_Product $product Product.
 * @return string
 */
function sawerna_get_product_swatches_html( $product ): string {
	if ( ! $product->is_type( 'variable' ) ) {
		return '';
	}

	$attribute_key = sawerna_get_product_swatch_attribute_key( $product );
	if ( '' === $attribute_key ) {
		return '';
	}

	if ( ! ( $product instanceof WC_Product_Variable ) ) {
		return '';
	}

	if ( ! taxonomy_exists( $attribute_key ) ) {
		return '';
	}

	$attributes = $product->get_variation_attributes();
	$options    = $attributes[ $attribute_key ] ?? array();
	$options    = array_values( array_unique( array_filter( $options ) ) );

	if ( empty( $options ) ) {
		return '';
	}

	$type  = function_exists( 'sawerna_swatches_get_attribute_type' ) ? sawerna_swatches_get_attribute_type( $attribute_key ) : 'select';

	if ( 'label' === $type || 'select' === $type ) {
		return '';
	}

	$items = array();

	foreach ( $options as $option ) {
		$term = get_term_by( 'slug', $option, $attribute_key );
		if ( ! ( $term instanceof WP_Term ) ) {
			continue;
		}

		$label    = $term->name;
		$color    = sawerna_get_swatch_color_for_term( $term );
		$image_id = sawerna_get_swatch_image_id_for_term( $term );

		if ( 'image' === $type ) {
			if ( ! $image_id ) {
				continue;
			}

			$image_url = wp_get_attachment_image_url( $image_id, 'thumbnail' );
			if ( ! $image_url ) {
				continue;
			}

			$items[] = array(
				'type'      => 'image',
				'label'     => $label,
				'image_url' => $image_url,
			);
			continue;
		}

		if ( 'color' === $type ) {
			if ( '' === $color ) {
				continue;
			}

			$items[] = array(
				'type'  => 'color',
				'label' => $label,
				'color' => $color,
			);
			continue;
		}
	}

	if ( empty( $items ) ) {
		return '';
	}

	$visible_items = array_slice( $items, 0, 4 );
	$hidden_count  = max( 0, count( $items ) - count( $visible_items ) );

	ob_start();
	?>
	<div class="sawerna-product-card__swatches" aria-label="<?php echo esc_attr__( 'Fabric options', 'sawerna' ); ?>">
		<?php foreach ( $visible_items as $item ) : ?>
			<?php if ( 'image' === $item['type'] ) : ?>
				<span class="sawerna-product-card__swatch sawerna-product-card__swatch--image" title="<?php echo esc_attr( $item['label'] ); ?>" aria-label="<?php echo esc_attr( $item['label'] ); ?>">
					<img src="<?php echo esc_url( $item['image_url'] ); ?>" alt="<?php echo esc_attr( $item['label'] ); ?>" loading="lazy">
				</span>
			<?php else : ?>
				<span class="sawerna-product-card__swatch sawerna-product-card__swatch--color" style="background-color: <?php echo esc_attr( $item['color'] ); ?>;" title="<?php echo esc_attr( $item['label'] ); ?>" aria-label="<?php echo esc_attr( $item['label'] ); ?>"></span>
			<?php endif; ?>
		<?php endforeach; ?>
		<?php if ( $hidden_count > 0 ) : ?>
			<span class="sawerna-product-card__swatch-more-count" aria-label="<?php echo esc_attr( sprintf( __( '%d more options', 'sawerna' ), $hidden_count ) ); ?>">
				<?php echo esc_html( '+' . $hidden_count ); ?>
			</span>
		<?php endif; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Get product card HTML.
 *
 * @param WC_Product $product Product.
 * @param string     $context Context.
 * @return string
 */
function sawerna_get_product_card_html( $product, string $context = 'loop' ): string {
	if ( ! $product ) {
		return '';
	}

	$labels        = sawerna_get_product_labels( $product );
	$delivery_text = sawerna_get_product_delivery_text( $product );
	$swatches_html = sawerna_get_product_swatches_html( $product );
	$wishlist_html = sawerna_get_wishlist_button_html( $product->get_id(), 'card' );

	ob_start();
	?>
	<article class="sawerna-product-card">
		<div class="sawerna-product-card__media">
			<a class="sawerna-product-card__image" href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>">
				<?php
				$image_id = sawerna_get_product_card_image_id( $product, $context );
				if ( $image_id ) {
					echo wp_get_attachment_image(
						$image_id,
						'full',
						false,
						array(
							'class'    => 'attachment-full size-full',
							'loading'  => 'lazy',
							'decoding' => 'async',
						)
					); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				} else {
					echo wp_kses_post( wc_placeholder_img( 'woocommerce_thumbnail' ) );
				}
				?>
			</a>
			<?php if ( ! empty( $labels ) ) : ?>
				<div class="sawerna-product-card__labels">
					<?php foreach ( $labels as $label ) : ?>
						<span class="sawerna-product-card__label sawerna-product-card__label--<?php echo esc_attr( $label['type'] ); ?>"<?php echo ! empty( $label['color'] ) ? ' style="background-color: ' . esc_attr( $label['color'] ) . ';"' : ''; ?>>
							<?php echo esc_html( $label['text'] ); ?>
						</span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<?php if ( $wishlist_html ) : ?>
				<div class="sawerna-product-card__wishlist">
					<?php echo $wishlist_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			<?php endif; ?>
		</div>
		<div class="sawerna-product-card__body">
			<h3 class="sawerna-product-card__title">
				<a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>">
					<?php echo esc_html( $product->get_name() ); ?>
				</a>
			</h3>
			<?php
			$rating_count = (int) $product->get_rating_count();
			$rating_html  = wc_get_rating_html( (float) $product->get_average_rating(), $rating_count );
			?>
			<?php if ( $rating_html ) : ?>
				<div class="sawerna-product-card__rating">
					<?php echo wp_kses_post( $rating_html ); ?>
					<?php if ( $rating_count > 0 ) : ?>
						<span class="sawerna-product-card__rating-count">
							(<?php echo esc_html( (string) $rating_count ); ?>)
						</span>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<?php if ( $product->get_price_html() ) : ?>
				<div class="sawerna-product-card__price">
					<?php echo wp_kses_post( $product->get_price_html() ); ?>
				</div>
			<?php endif; ?>
			<?php if ( $delivery_text ) : ?>
				<div class="sawerna-product-card__delivery">
					<span class="sawerna-product-card__delivery-icon" aria-hidden="true">
						<?php echo sawerna_get_svg_inline( 'Asset 18.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>
					<span class="sawerna-product-card__delivery-text">
						<?php echo esc_html( $delivery_text ); ?>
					</span>
				</div>
			<?php endif; ?>
			<?php if ( $swatches_html ) : ?>
				<?php echo $swatches_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>
		</div>
	</article>
	<?php
	return (string) ob_get_clean();
}

/**
 * Render product card.
 *
 * @param WC_Product $product Product.
 * @param string     $context Context.
 */
function sawerna_render_product_card( $product, string $context = 'loop' ) {
	$html = sawerna_get_product_card_html( $product, $context );
	if ( '' !== $html ) {
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

/**
 * Set archive columns to three.
 *
 * @return int
 */
function sawerna_loop_shop_columns(): int {
	return 3;
}
add_filter( 'loop_shop_columns', 'sawerna_loop_shop_columns', 20 );
