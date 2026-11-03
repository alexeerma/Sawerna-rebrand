<?php
/**
 * Native attribute swatches for product archives and single product pages.
 *
 * Data model
 * ----------
 * Attribute level:  option  sawerna_swatch_type_{$attr_slug}  → 'select'|'color'|'image'|'label'
 * Term level:       term meta sawerna_swatch_color             → hex string  e.g. #ffffff
 *                   term meta sawerna_swatch_image             → attachment ID (int as string)
 *                   term meta sawerna_swatch_label             → short display label
 *
 * @package Sawerna
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

// ---------------------------------------------------------------------------
// Helper: get / set attribute swatch type
// ---------------------------------------------------------------------------

/**
 * Get the swatch display type for a product attribute taxonomy.
 *
 * @param string $taxonomy Full taxonomy slug, e.g. 'pa_varv'.
 * @return string 'select'|'color'|'image'|'label'
 */
function sawerna_swatches_get_attribute_type( string $taxonomy ): string {
	$attr_slug = sawerna_swatches_taxonomy_to_slug( $taxonomy );
	if ( '' === $attr_slug ) {
		return 'select';
	}

	$type = get_option( 'sawerna_swatch_type_' . $attr_slug, 'select' );

	return in_array( $type, array( 'select', 'color', 'image', 'label' ), true ) ? $type : 'select';
}

/**
 * Convert a full taxonomy slug to the plain attribute slug.
 *
 * @param string $taxonomy e.g. 'pa_varv'.
 * @return string e.g. 'varv', or '' if not a product attribute.
 */
function sawerna_swatches_taxonomy_to_slug( string $taxonomy ): string {
	if ( 0 !== strpos( $taxonomy, 'pa_' ) ) {
		return '';
	}

	return substr( $taxonomy, 3 );
}

// ---------------------------------------------------------------------------
// Helper: read term swatch data
// ---------------------------------------------------------------------------

/**
 * Get swatch color hex for a term.
 *
 * Checks the native sawerna key first, then legacy plugin keys as fallback
 * so sites migrating from a plugin keep their existing data.
 *
 * @param WP_Term $term Term object.
 * @return string Hex color or empty string.
 */
function sawerna_swatches_get_term_color( WP_Term $term ): string {
	$keys = array(
		'sawerna_swatch_color',
		'swatch_color',
		'color',
		'color_hex',
		'attribute_color',
		'product_attribute_color',
	);

	foreach ( $keys as $key ) {
		$value = get_term_meta( $term->term_id, $key, true );
		if ( is_string( $value ) && '' !== trim( $value ) ) {
			return sanitize_hex_color( trim( $value ) ) ?? trim( $value );
		}
	}

	return '';
}

/**
 * Get swatch image attachment ID for a term.
 *
 * @param WP_Term $term Term object.
 * @return int Attachment ID, or 0.
 */
function sawerna_swatches_get_term_image_id( WP_Term $term ): int {
	$value = get_term_meta( $term->term_id, 'sawerna_swatch_image', true );

	return absint( $value );
}

/**
 * Get swatch label override for a term.
 * Falls back to the term name when no override is stored.
 *
 * @param WP_Term $term Term object.
 * @return string
 */
function sawerna_swatches_get_term_label( WP_Term $term ): string {
	$label = get_term_meta( $term->term_id, 'sawerna_swatch_label', true );

	return ( is_string( $label ) && '' !== trim( $label ) ) ? trim( $label ) : $term->name;
}

// ---------------------------------------------------------------------------
// Admin: "Display type" field on attribute edit screens
// ---------------------------------------------------------------------------

/**
 * Output the Display type field on attribute term edit screens.
 * WordPress passes the term as the first argument on `{$taxonomy}_edit_form_fields`.
 *
 * @param WP_Term|string $term_or_taxonomy Term object on edit screens, or taxonomy slug when called directly.
 */
function sawerna_swatches_add_attribute_type_field( $term_or_taxonomy ): void {
	$taxonomy = $term_or_taxonomy instanceof WP_Term ? $term_or_taxonomy->taxonomy : (string) $term_or_taxonomy;
	$type     = sawerna_swatches_get_attribute_type( $taxonomy );
	$options = array(
		'select' => __( 'Select (default)', 'sawerna' ),
		'color'  => __( 'Color swatches', 'sawerna' ),
		'image'  => __( 'Image swatches', 'sawerna' ),
		'label'  => __( 'Label / text swatches', 'sawerna' ),
	);
	?>
	<tr class="form-field">
		<th scope="row">
			<label for="sawerna_swatch_type"><?php esc_html_e( 'Swatch display type', 'sawerna' ); ?></label>
		</th>
		<td>
			<select name="sawerna_swatch_type" id="sawerna_swatch_type">
				<?php foreach ( $options as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $type, $value ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<p class="description">
				<?php esc_html_e( 'Choose how variation options are shown on product cards and the single product page.', 'sawerna' ); ?>
			</p>
		</td>
	</tr>
	<?php
}

/**
 * Save the Display type option when an attribute is edited.
 * Hooked dynamically for every registered pa_* taxonomy.
 *
 * @param int $term_id Term ID.
 */
function sawerna_swatches_save_attribute_type( int $term_id ): void {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WC handles nonce for taxonomy saves.
	if ( ! isset( $_POST['sawerna_swatch_type'] ) ) {
		return;
	}

	$type       = sanitize_key( wp_unslash( $_POST['sawerna_swatch_type'] ) );
	$allowed    = array( 'select', 'color', 'image', 'label' );
	$type       = in_array( $type, $allowed, true ) ? $type : 'select';
	$term       = get_term( $term_id );
	$attr_slug  = ( $term instanceof WP_Term ) ? sawerna_swatches_taxonomy_to_slug( $term->taxonomy ) : '';

	if ( '' !== $attr_slug ) {
		update_option( 'sawerna_swatch_type_' . $attr_slug, $type );
	}
}

// ---------------------------------------------------------------------------
// Admin: term-level swatch fields (color / image / label)
// ---------------------------------------------------------------------------

/**
 * Output swatch fields on the Add Term form.
 *
 * @param string $taxonomy Taxonomy slug.
 */
function sawerna_swatches_add_term_fields( string $taxonomy ): void {
	$type = sawerna_swatches_get_attribute_type( $taxonomy );
	if ( 'select' === $type ) {
		return;
	}

	echo '<div class="form-field sawerna-swatch-term-fields" data-swatch-type="' . esc_attr( $type ) . '">';
	sawerna_swatches_render_term_field_inputs( $type, null );
	echo '</div>';
}

/**
 * Output swatch fields on the Edit Term form.
 *
 * @param WP_Term $term     Term being edited.
 * @param string  $taxonomy Taxonomy slug.
 */
function sawerna_swatches_edit_term_fields( WP_Term $term, string $taxonomy ): void {
	$type = sawerna_swatches_get_attribute_type( $taxonomy );
	if ( 'select' === $type ) {
		return;
	}

	echo '<tr class="form-field sawerna-swatch-term-fields" data-swatch-type="' . esc_attr( $type ) . '">';
	echo '<th scope="row">' . esc_html__( 'Swatch', 'sawerna' ) . '</th>';
	echo '<td>';
	sawerna_swatches_render_term_field_inputs( $type, $term );
	echo '</td>';
	echo '</tr>';
}

/**
 * Render the appropriate input(s) for a term depending on swatch type.
 *
 * @param string       $type Swatch type.
 * @param WP_Term|null $term Existing term, or null on add.
 */
function sawerna_swatches_render_term_field_inputs( string $type, ?WP_Term $term ): void {
	switch ( $type ) {
		case 'color':
			$color = $term ? sawerna_swatches_get_term_color( $term ) : '';
			?>
			<label for="sawerna_swatch_color"><?php esc_html_e( 'Swatch color', 'sawerna' ); ?></label>
			<input
				type="text"
				name="sawerna_swatch_color"
				id="sawerna_swatch_color"
				value="<?php echo esc_attr( $color ); ?>"
				class="sawerna-color-picker"
				maxlength="7"
				placeholder="#ffffff"
			>
			<p class="description"><?php esc_html_e( 'Choose a color to represent this option.', 'sawerna' ); ?></p>
			<?php
			break;

		case 'image':
			$image_id  = $term ? sawerna_swatches_get_term_image_id( $term ) : 0;
			$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
			?>
			<label><?php esc_html_e( 'Swatch image', 'sawerna' ); ?></label>
			<div class="sawerna-swatch-image-uploader">
				<div class="sawerna-swatch-image-preview">
					<?php if ( $image_url ) : ?>
						<img src="<?php echo esc_url( $image_url ); ?>" alt="" style="max-width:60px;">
					<?php endif; ?>
				</div>
				<input type="hidden" name="sawerna_swatch_image" id="sawerna_swatch_image" value="<?php echo esc_attr( (string) $image_id ); ?>">
				<button type="button" class="button sawerna-swatch-upload-btn"><?php esc_html_e( 'Select image', 'sawerna' ); ?></button>
				<?php if ( $image_id ) : ?>
					<button type="button" class="button sawerna-swatch-remove-btn"><?php esc_html_e( 'Remove', 'sawerna' ); ?></button>
				<?php endif; ?>
			</div>
			<p class="description"><?php esc_html_e( 'Upload or select a small image for this option.', 'sawerna' ); ?></p>
			<?php
			break;

		case 'label':
			$label = $term ? get_term_meta( $term->term_id, 'sawerna_swatch_label', true ) : '';
			?>
			<label for="sawerna_swatch_label"><?php esc_html_e( 'Swatch label', 'sawerna' ); ?></label>
			<input
				type="text"
				name="sawerna_swatch_label"
				id="sawerna_swatch_label"
				value="<?php echo esc_attr( is_string( $label ) ? $label : '' ); ?>"
				maxlength="10"
				placeholder="<?php echo $term ? esc_attr( $term->name ) : ''; ?>"
			>
			<p class="description"><?php esc_html_e( 'Short label shown on the swatch button. Defaults to term name if empty.', 'sawerna' ); ?></p>
			<?php
			break;
	}
}

/**
 * Save term swatch meta on create and edit.
 *
 * @param int $term_id Term ID.
 */
function sawerna_swatches_save_term_meta( int $term_id ): void {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}

	// phpcs:disable WordPress.Security.NonceVerification.Missing

	if ( isset( $_POST['sawerna_swatch_color'] ) ) {
		$color = sanitize_hex_color( wp_unslash( $_POST['sawerna_swatch_color'] ) );
		if ( null !== $color ) {
			update_term_meta( $term_id, 'sawerna_swatch_color', $color );
		} else {
			delete_term_meta( $term_id, 'sawerna_swatch_color' );
		}
	}

	if ( isset( $_POST['sawerna_swatch_image'] ) ) {
		$image_id = absint( wp_unslash( $_POST['sawerna_swatch_image'] ) );
		if ( $image_id > 0 ) {
			update_term_meta( $term_id, 'sawerna_swatch_image', $image_id );
		} else {
			delete_term_meta( $term_id, 'sawerna_swatch_image' );
		}
	}

	if ( isset( $_POST['sawerna_swatch_label'] ) ) {
		$label = sanitize_text_field( wp_unslash( $_POST['sawerna_swatch_label'] ) );
		if ( '' !== $label ) {
			update_term_meta( $term_id, 'sawerna_swatch_label', $label );
		} else {
			delete_term_meta( $term_id, 'sawerna_swatch_label' );
		}
	}

	// phpcs:enable WordPress.Security.NonceVerification.Missing
}

// ---------------------------------------------------------------------------
// Admin: asset enqueue
// ---------------------------------------------------------------------------

/**
 * Enqueue admin scripts and styles on attribute term screens.
 *
 * @param string $hook Current admin page hook.
 */
function sawerna_swatches_admin_assets( string $hook ): void {
	// Only on edit-tags and term.php pages for product attribute taxonomies.
	if ( ! in_array( $hook, array( 'edit-tags.php', 'term.php' ), true ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 0 !== strpos( $screen->taxonomy, 'pa_' ) ) {
		return;
	}

	$dir = get_template_directory();
	$uri = get_template_directory_uri();

	$css_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $dir . '/assets/css/swatches.css' ) : _S_VERSION;
	$js_version  = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $dir . '/assets/js/swatches-admin.js' ) : _S_VERSION;

	wp_enqueue_style( 'sawerna-swatches-admin', $uri . '/assets/css/swatches.css', array( 'wp-color-picker' ), $css_version );
	wp_enqueue_script( 'sawerna-swatches-admin', $uri . '/assets/js/swatches-admin.js', array( 'wp-color-picker', 'wp-util', 'media-upload' ), $js_version, true );
	wp_localize_script(
		'sawerna-swatches-admin',
		'sawernaSwatchesAdmin',
		array(
			'uploadTitle'  => __( 'Select swatch image', 'sawerna' ),
			'uploadButton' => __( 'Use this image', 'sawerna' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'sawerna_swatches_admin_assets' );

// ---------------------------------------------------------------------------
// Frontend: replace WooCommerce variation <select> with swatch buttons
// ---------------------------------------------------------------------------

/**
 * Replace the default WooCommerce variation dropdown with swatch buttons
 * when the attribute has a non-select display type.
 *
 * @param string  $html    Original select HTML.
 * @param array   $args    Arguments passed to wc_dropdown_variation_attribute_options.
 * @param WP_Term $term    Not used here.
 * @return string
 */
function sawerna_swatches_dropdown_html( string $html, array $args ): string {
	$taxonomy = isset( $args['attribute'] ) ? wc_attribute_taxonomy_name( $args['attribute'] ) : '';
	if ( '' === $taxonomy ) {
		// attribute may already be the full taxonomy slug.
		$taxonomy = isset( $args['attribute'] ) ? (string) $args['attribute'] : '';
	}

	$type = sawerna_swatches_get_attribute_type( $taxonomy );

	if ( 'select' === $type ) {
		return $html;
	}

	$product = $args['product'] ?? null;
	if ( ! ( $product instanceof WC_Product ) ) {
		return $html;
	}

	$options  = $args['options'] ?? array();
	$selected = $args['selected'] ?? '';

	if ( empty( $options ) && $product instanceof WC_Product_Variable ) {
		$attributes = $product->get_variation_attributes();
		$options    = $attributes[ $taxonomy ] ?? array();
	}

	if ( empty( $options ) ) {
		return $html;
	}

	// Build the hidden native select so WooCommerce variation JS still works.
	$hidden_select = sawerna_swatches_build_hidden_select( $args, $options, $selected );
	$buttons_html  = sawerna_swatches_build_swatch_buttons( $type, $taxonomy, $options, $selected );

	return $buttons_html . $hidden_select;
}
add_filter( 'woocommerce_dropdown_variation_attribute_options_html', 'sawerna_swatches_dropdown_html', 20, 2 );

/**
 * Build a hidden <select> that WooCommerce's native variation.js uses.
 *
 * @param array  $args     Original dropdown args.
 * @param array  $options  Term slugs / values.
 * @param string $selected Currently selected value.
 * @return string HTML.
 */
function sawerna_swatches_build_hidden_select( array $args, array $options, string $selected ): string {
	$attribute = esc_attr( $args['attribute'] ?? '' );
	$id        = esc_attr( sanitize_title( $args['attribute'] ?? '' ) );
	$name      = 'attribute_' . sanitize_title( $args['attribute'] ?? '' );

	$out  = '<select name="' . esc_attr( $name ) . '" id="' . $id . '" class="sawerna-swatches-select" style="display:none;" data-attribute_name="attribute_' . $attribute . '" aria-hidden="true">';
	$out .= '<option value="">' . esc_html__( 'Choose an option', 'sawerna' ) . '</option>';

	foreach ( $options as $option ) {
		$option = is_object( $option ) ? $option->slug : $option;
		$out   .= '<option value="' . esc_attr( $option ) . '"' . selected( $selected, $option, false ) . '>' . esc_html( $option ) . '</option>';
	}

	$out .= '</select>';

	return $out;
}

/**
 * Build the visible swatch button row.
 *
 * @param string $type     Swatch type: color|image|label.
 * @param string $taxonomy Full taxonomy slug.
 * @param array  $options  Term slugs.
 * @param string $selected Currently selected slug.
 * @return string HTML.
 */
function sawerna_swatches_build_swatch_buttons( string $type, string $taxonomy, array $options, string $selected ): string {
	$attr_key = sanitize_title( $taxonomy );
	$out      = '<div class="sawerna-swatches" data-attribute="attribute_' . esc_attr( $attr_key ) . '" role="radiogroup">';

	foreach ( $options as $option ) {
		$slug       = is_object( $option ) ? $option->slug : (string) $option;
		$is_active  = ( $slug === $selected );
		$term       = taxonomy_exists( $taxonomy ) ? get_term_by( 'slug', $slug, $taxonomy ) : null;
		$label      = $term instanceof WP_Term ? sawerna_swatches_get_term_label( $term ) : $slug;
		$aria_label = $label;

		$classes = array( 'sawerna-swatch', 'sawerna-swatch--' . $type );
		if ( $is_active ) {
			$classes[] = 'is-selected';
		}

		switch ( $type ) {
			case 'color':
				$color = $term instanceof WP_Term ? sawerna_swatches_get_term_color( $term ) : '';
				$style = $color ? 'background-color:' . esc_attr( $color ) . ';' : '';
				$out  .= '<button type="button" class="' . esc_attr( implode( ' ', $classes ) ) . '" data-value="' . esc_attr( $slug ) . '" style="' . esc_attr( $style ) . '" aria-label="' . esc_attr( $aria_label ) . '" aria-pressed="' . ( $is_active ? 'true' : 'false' ) . '" title="' . esc_attr( $label ) . '"></button>';
				break;

			case 'image':
				$image_id  = $term instanceof WP_Term ? sawerna_swatches_get_term_image_id( $term ) : 0;
				$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
				if ( $image_url ) {
					$out .= '<button type="button" class="' . esc_attr( implode( ' ', $classes ) ) . '" data-value="' . esc_attr( $slug ) . '" aria-label="' . esc_attr( $aria_label ) . '" aria-pressed="' . ( $is_active ? 'true' : 'false' ) . '">';
					$out .= '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $label ) . '" loading="lazy">';
					$out .= '</button>';
				} else {
					// Fallback to label button if no image is set.
					$out .= '<button type="button" class="' . esc_attr( implode( ' ', array_merge( $classes, array( 'sawerna-swatch--label' ) ) ) ) . '" data-value="' . esc_attr( $slug ) . '" aria-label="' . esc_attr( $aria_label ) . '" aria-pressed="' . ( $is_active ? 'true' : 'false' ) . '">' . esc_html( $label ) . '</button>';
				}
				break;

			case 'label':
			default:
				$out .= '<button type="button" class="' . esc_attr( implode( ' ', $classes ) ) . '" data-value="' . esc_attr( $slug ) . '" aria-label="' . esc_attr( $aria_label ) . '" aria-pressed="' . ( $is_active ? 'true' : 'false' ) . '">' . esc_html( $label ) . '</button>';
				break;
		}
	}

	$out .= '</div>';

	return $out;
}

// ---------------------------------------------------------------------------
// Frontend: asset enqueue
// ---------------------------------------------------------------------------

/**
 * Enqueue swatch styles and JS on single product pages.
 */
function sawerna_swatches_frontend_assets(): void {
	if ( ! is_product() ) {
		return;
	}

	$dir = get_template_directory();
	$uri = get_template_directory_uri();

	$css_path = $dir . '/assets/css/swatches.css';
	$js_path  = $dir . '/assets/js/swatches.js';

	$css_version = ( defined( 'WP_DEBUG' ) && WP_DEBUG && file_exists( $css_path ) ) ? filemtime( $css_path ) : _S_VERSION;
	$js_version  = ( defined( 'WP_DEBUG' ) && WP_DEBUG && file_exists( $js_path ) ) ? filemtime( $js_path ) : _S_VERSION;

	wp_enqueue_style( 'sawerna-swatches', $uri . '/assets/css/swatches.css', array( 'sawerna-style' ), $css_version );
	wp_enqueue_script( 'sawerna-swatches', $uri . '/assets/js/swatches.js', array( 'jquery', 'wc-add-to-cart-variation' ), $js_version, true );
}
add_action( 'wp_enqueue_scripts', 'sawerna_swatches_frontend_assets', 20 );

// ---------------------------------------------------------------------------
// Dynamic hook registration for all pa_* taxonomies
// ---------------------------------------------------------------------------

/**
 * Attach admin hooks to every registered product attribute taxonomy.
 * Runs on init (after WooCommerce registers taxonomies) and is
 * limited to admin only.
 */
function sawerna_swatches_register_taxonomy_hooks(): void {
	if ( ! is_admin() ) {
		return;
	}

	$attribute_taxonomies = wc_get_attribute_taxonomies();
	if ( empty( $attribute_taxonomies ) ) {
		return;
	}

	foreach ( $attribute_taxonomies as $taxonomy_obj ) {
		$taxonomy = wc_attribute_taxonomy_name( $taxonomy_obj->attribute_name );

		// Attribute-level display type field.
		add_action( $taxonomy . '_edit_form_fields', 'sawerna_swatches_add_attribute_type_field' );
		add_action( 'edited_' . $taxonomy, 'sawerna_swatches_save_attribute_type' );

		// Term-level swatch fields.
		add_action( $taxonomy . '_add_form_fields', 'sawerna_swatches_add_term_fields' );
		add_action( $taxonomy . '_edit_form_fields', 'sawerna_swatches_edit_term_fields', 10, 2 );

		add_action( 'created_' . $taxonomy, 'sawerna_swatches_save_term_meta' );
		add_action( 'edited_' . $taxonomy, 'sawerna_swatches_save_term_meta' );
	}
}
add_action( 'init', 'sawerna_swatches_register_taxonomy_hooks', 20 );
