<?php
/**
 * Homepage template helpers.
 *
 * @package Sawerna
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get homepage template slug.
 *
 * @return string
 */
function sawerna_homepage_template_slug() {
	return 'template-homepage.php';
}

/**
 * Get default slide data.
 *
 * @return array<string, string|int>
 */
function sawerna_homepage_slide_defaults() {
	return array(
		'left_image_id'     => 0,
		'left_title'        => '',
		'left_text'         => '',
		'left_button_text'  => '',
		'left_button_url'   => '',
		'right_image_id'    => 0,
		'right_title'       => '',
		'right_text'        => '',
		'right_list'        => '',
		'right_button_text' => '',
		'right_button_url'  => '',
	);
}

/**
 * Sanitize homepage slides.
 *
 * @param mixed $slides Raw slides.
 * @return array<int, array<string, string|int>>
 */
function sawerna_sanitize_homepage_slides( $slides ) {
	if ( ! is_array( $slides ) ) {
		return array();
	}

	$sanitized = array();

	foreach ( $slides as $slide ) {
		if ( ! is_array( $slide ) ) {
			continue;
		}

		$item = wp_parse_args( $slide, sawerna_homepage_slide_defaults() );

		$item = array(
			'left_image_id'     => absint( $item['left_image_id'] ),
			'left_title'        => sanitize_text_field( $item['left_title'] ),
			'left_text'         => sanitize_textarea_field( $item['left_text'] ),
			'left_button_text'  => sanitize_text_field( $item['left_button_text'] ),
			'left_button_url'   => esc_url_raw( $item['left_button_url'] ),
			'right_image_id'    => absint( $item['right_image_id'] ),
			'right_title'       => sanitize_text_field( $item['right_title'] ),
			'right_text'        => sanitize_textarea_field( $item['right_text'] ),
			'right_list'        => sanitize_textarea_field( $item['right_list'] ),
			'right_button_text' => sanitize_text_field( $item['right_button_text'] ),
			'right_button_url'  => esc_url_raw( $item['right_button_url'] ),
		);

		$has_content = false;

		foreach ( $item as $value ) {
			if ( is_int( $value ) && $value > 0 ) {
				$has_content = true;
				break;
			}

			if ( is_string( $value ) && '' !== trim( $value ) ) {
				$has_content = true;
				break;
			}
		}

		if ( $has_content ) {
			$sanitized[] = $item;
		}
	}

	return $sanitized;
}

/**
 * Get homepage slides for a page.
 *
 * @param int $post_id Post ID.
 * @return array<int, array<string, string|int>>
 */
function sawerna_get_homepage_slides( $post_id ) {
	$slides = get_post_meta( $post_id, '_sawerna_homepage_slides', true );

	return sawerna_sanitize_homepage_slides( $slides );
}

/**
 * Get the assigned static front page ID.
 *
 * @return int
 */
function sawerna_homepage_front_page_id() {
	return max( 0, (int) get_option( 'page_on_front', 0 ) );
}

/**
 * Resolve an attachment ID from an uploads-relative path.
 *
 * @param string $relative_path Uploads-relative path.
 * @return int
 */
function sawerna_homepage_media_attachment_id_by_upload_path( $relative_path ) {
	$relative_path = ltrim( (string) $relative_path, '/' );
	if ( '' === $relative_path || ! function_exists( 'attachment_url_to_postid' ) ) {
		return 0;
	}

	$uploads = wp_get_upload_dir();
	$baseurl = isset( $uploads['baseurl'] ) ? (string) $uploads['baseurl'] : '';

	if ( '' === $baseurl ) {
		return 0;
	}

	return absint( attachment_url_to_postid( trailingslashit( $baseurl ) . $relative_path ) );
}

/**
 * Get default wide slide data.
 *
 * @return array<string, string|int>
 */
function sawerna_homepage_wide_slide_defaults() {
	return array(
		'image_id'    => 0,
		'title'       => '',
		'text'        => '',
		'button_text' => '',
		'button_url'  => '',
	);
}

/**
 * Get default narrow slide data.
 *
 * @return array<string, string|int>
 */
function sawerna_homepage_narrow_slide_defaults() {
	return array(
		'image_id'    => 0,
		'title'       => '',
		'text'        => '',
		'list'        => '',
		'button_text' => '',
		'button_url'  => '',
	);
}

/**
 * Get hero slides for a specific language code.
 *
 * @param string $lang Language code (e.g. 'et', 'en').
 * @return array<string, array> Keys 'hero_wide' and 'hero_narrow'.
 */
function sawerna_get_homepage_slides_for_lang( $lang ) {
	$lang       = sanitize_key( (string) $lang );
	$stored     = get_option( 'sawerna_homepage_slides_' . $lang, array() );
	$wide_raw   = ( is_array( $stored ) && isset( $stored['hero_wide'] ) && is_array( $stored['hero_wide'] ) ) ? $stored['hero_wide'] : array();
	$narrow_raw = ( is_array( $stored ) && isset( $stored['hero_narrow'] ) && is_array( $stored['hero_narrow'] ) ) ? $stored['hero_narrow'] : array();

	$wide   = sawerna_sanitize_homepage_wide_slides( $wide_raw );
	$narrow = sawerna_sanitize_homepage_narrow_slides( $narrow_raw );

	if ( empty( $wide ) ) {
		$wide = sawerna_homepage_default_wide_slides();
	}

	if ( empty( $narrow ) ) {
		$narrow = sawerna_homepage_default_narrow_slides();
	}

	return array(
		'hero_wide'   => $wide,
		'hero_narrow' => $narrow,
	);
}

/**
 * Register a single WPML string for homepage options.
 *
 * @param string $name  String name.
 * @param string $value String value.
 * @return void
 */
function sawerna_homepage_wpml_register_string( $name, $value ) {
	if ( ! is_string( $value ) || '' === trim( $value ) || ! has_action( 'wpml_register_single_string' ) ) {
		return;
	}

	do_action( 'wpml_register_single_string', 'sawerna-page-options', (string) $name, $value ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Third-party WPML hook.
}

/**
 * Translate a single WPML string for homepage options.
 *
 * @param string $name  String name.
 * @param string $value String value.
 * @return string
 */
function sawerna_homepage_wpml_translate_string( $name, $value ) {
	if ( ! is_string( $value ) || '' === trim( $value ) || ! has_filter( 'wpml_translate_single_string' ) ) {
		return (string) $value;
	}

	return (string) apply_filters( 'wpml_translate_single_string', $value, 'sawerna-page-options', (string) $name ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Third-party WPML hook.
}

/**
 * Register homepage option strings in WPML.
 *
 * @param array<string, mixed> $options Homepage options.
 * @return void
 */
function sawerna_homepage_register_wpml_option_strings( array $options ) {
	$top_level_string_fields = array(
		'category_heading',
		'category_more_label',
		'category_less_label',
		'about_title',
		'about_subtitle',
		'about_text',
		'about_button_text',
		'testimonials_title',
		'blog_title',
	);

	foreach ( $top_level_string_fields as $field ) {
		if ( isset( $options[ $field ] ) ) {
			sawerna_homepage_wpml_register_string( $field, (string) $options[ $field ] );
		}
	}
}

/**
 * Translate homepage option strings through WPML.
 *
 * @param array<string, mixed> $options Homepage options.
 * @return array<string, mixed>
 */
function sawerna_homepage_translate_wpml_option_strings( array $options ) {
	$top_level_string_fields = array(
		'category_heading',
		'category_more_label',
		'category_less_label',
		'about_title',
		'about_subtitle',
		'about_text',
		'about_button_text',
		'testimonials_title',
		'blog_title',
	);

	foreach ( $top_level_string_fields as $field ) {
		if ( isset( $options[ $field ] ) ) {
			$options[ $field ] = sawerna_homepage_wpml_translate_string( $field, (string) $options[ $field ] );
		}
	}

	return $options;
}

/**
 * Sanitize wide slider items.
 *
 * @param mixed $slides Raw slides.
 * @return array<int, array<string, string|int>>
 */
function sawerna_sanitize_homepage_wide_slides( $slides ) {
	if ( ! is_array( $slides ) ) {
		return array();
	}

	$sanitized = array();

	foreach ( $slides as $slide ) {
		if ( ! is_array( $slide ) ) {
			continue;
		}

		$item = wp_parse_args( $slide, sawerna_homepage_wide_slide_defaults() );
		$item = array(
			'image_id'    => absint( $item['image_id'] ),
			'title'       => sanitize_text_field( $item['title'] ),
			'text'        => sanitize_textarea_field( $item['text'] ),
			'button_text' => sanitize_text_field( $item['button_text'] ),
			'button_url'  => esc_url_raw( $item['button_url'] ),
		);

		$has_content = false;
		foreach ( $item as $value ) {
			if ( is_int( $value ) && $value > 0 ) {
				$has_content = true;
				break;
			}

			if ( is_string( $value ) && '' !== trim( $value ) ) {
				$has_content = true;
				break;
			}
		}

		if ( $has_content ) {
			$sanitized[] = $item;
		}
	}

	return $sanitized;
}

/**
 * Sanitize narrow slider items.
 *
 * @param mixed $slides Raw slides.
 * @return array<int, array<string, string|int>>
 */
function sawerna_sanitize_homepage_narrow_slides( $slides ) {
	if ( ! is_array( $slides ) ) {
		return array();
	}

	$sanitized = array();

	foreach ( $slides as $slide ) {
		if ( ! is_array( $slide ) ) {
			continue;
		}

		$item = wp_parse_args( $slide, sawerna_homepage_narrow_slide_defaults() );
		$item = array(
			'image_id'    => absint( $item['image_id'] ),
			'title'       => sanitize_text_field( $item['title'] ),
			'text'        => sanitize_textarea_field( $item['text'] ),
			'list'        => sanitize_textarea_field( $item['list'] ),
			'button_text' => sanitize_text_field( $item['button_text'] ),
			'button_url'  => esc_url_raw( $item['button_url'] ),
		);

		$has_content = false;
		foreach ( $item as $value ) {
			if ( is_int( $value ) && $value > 0 ) {
				$has_content = true;
				break;
			}

			if ( is_string( $value ) && '' !== trim( $value ) ) {
				$has_content = true;
				break;
			}
		}

		if ( $has_content ) {
			$sanitized[] = $item;
		}
	}

	return $sanitized;
}

/**
 * Get default dummy wide slides.
 *
 * @return array<int, array<string, string|int>>
 */
function sawerna_homepage_default_wide_slides() {
	return sawerna_sanitize_homepage_wide_slides(
		array(
			array(
				'image_id'    => sawerna_homepage_media_attachment_id_by_upload_path( '2026/02/Diivanvoodi-Loora-2-1320x1320.jpg' ),
				'title'       => __( 'Kevadine mugavus koju', 'sawerna' ),
				'text'        => __( 'Avasta diivanid ja voodid, mis toovad tuppa rohkem pehmust ja valgust.', 'sawerna' ),
				'button_text' => __( 'Vaata valikut', 'sawerna' ),
				'button_url'  => home_url( '/pood/' ),
			),
			array(
				'image_id'    => sawerna_homepage_media_attachment_id_by_upload_path( '2026/02/Tommy-1-terra48-1320x1320.jpg' ),
				'title'       => __( 'Voodid päriselt puhkamiseks', 'sawerna' ),
				'text'        => __( 'Leia uus voodi või kontinentaalvoodi, mis sobib sinu magamistoa rütmiga.', 'sawerna' ),
				'button_text' => __( 'Vaata voodeid', 'sawerna' ),
				'button_url'  => home_url( '/tootekategooria/voodid/' ),
			),
			array(
				'image_id'    => sawerna_homepage_media_attachment_id_by_upload_path( '2026/02/TV-Laud-Kaisa-5-1320x1320.jpg' ),
				'title'       => __( 'Mööbel, mis loob terviku', 'sawerna' ),
				'text'        => __( 'Praktilised kapid, lauad ja väikemööbel ühte rahulikku kodupilti.', 'sawerna' ),
				'button_text' => __( 'Vaata mööblit', 'sawerna' ),
				'button_url'  => home_url( '/tootekategooria/moobel/' ),
			),
		)
	);
}

/**
 * Get default dummy narrow slides.
 *
 * @return array<int, array<string, string|int>>
 */
function sawerna_homepage_default_narrow_slides() {
	return sawerna_sanitize_homepage_narrow_slides(
		array(
			array(
				'image_id'    => sawerna_homepage_media_attachment_id_by_upload_path( '2026/02/Aurora-vaip-5.webp' ),
				'title'       => __( 'Kodutekstiilid', 'sawerna' ),
				'text'        => __( 'Pehmed detailid, mis lõpetavad ruumi.', 'sawerna' ),
				'list'        => implode( "\n", array( __( 'Pleedid', 'sawerna' ), __( 'Padjad', 'sawerna' ), __( 'Vaibad', 'sawerna' ) ) ),
				'button_text' => __( 'Vaata rohkem', 'sawerna' ),
				'button_url'  => home_url( '/tootekategooria/kodutekstiilid/' ),
			),
			array(
				'image_id'    => sawerna_homepage_media_attachment_id_by_upload_path( '2026/02/Carissa-kollane-3.jpg' ),
				'title'       => __( 'Uued aktsendid', 'sawerna' ),
				'text'        => __( 'Lisa koju värvi ja tekstuuri väikeste sisustuselementidega.', 'sawerna' ),
				'list'        => implode( "\n", array( __( 'Dekoratiivpadjad', 'sawerna' ), __( 'Sisustusdetailid', 'sawerna' ), __( 'Hubased toonid', 'sawerna' ) ) ),
				'button_text' => __( 'Inspireeru', 'sawerna' ),
				'button_url'  => home_url( '/tootekategooria/muu-sisustus/' ),
			),
			array(
				'image_id'    => sawerna_homepage_media_attachment_id_by_upload_path( '2026/02/Tugitool-Moss-Loop-12-3.jpg' ),
				'title'       => __( 'Lemmikud puhkenurka', 'sawerna' ),
				'text'        => __( 'Vali tugitool või tumbad, millega teha kodus mõnus vaikusekoht.', 'sawerna' ),
				'list'        => implode( "\n", array( __( 'Tugitoolid', 'sawerna' ), __( 'Tumbad', 'sawerna' ), __( 'Lugemisnurk', 'sawerna' ) ) ),
				'button_text' => __( 'Vaata valikut', 'sawerna' ),
				'button_url'  => home_url( '/tootekategooria/tugitoolid/' ),
			),
		)
	);
}

/**
 * Get legacy combined slides from the assigned front page.
 *
 * @return array<int, array<string, string|int>>
 */
function sawerna_get_homepage_legacy_combined_slides() {
	$front_page_id = sawerna_homepage_front_page_id();

	if ( $front_page_id < 1 ) {
		return array();
	}

	return sawerna_get_homepage_slides( $front_page_id );
}

/**
 * Get legacy wide slides derived from the old paired slider data.
 *
 * @return array<int, array<string, string|int>>
 */
function sawerna_get_homepage_legacy_wide_slides() {
	$legacy = sawerna_get_homepage_legacy_combined_slides();

	if ( empty( $legacy ) ) {
		return array();
	}

	$slides = array();

	foreach ( $legacy as $slide ) {
		$slides[] = array(
			'image_id'    => isset( $slide['left_image_id'] ) ? absint( $slide['left_image_id'] ) : 0,
			'title'       => isset( $slide['left_title'] ) ? (string) $slide['left_title'] : '',
			'text'        => isset( $slide['left_text'] ) ? (string) $slide['left_text'] : '',
			'button_text' => isset( $slide['left_button_text'] ) ? (string) $slide['left_button_text'] : '',
			'button_url'  => isset( $slide['left_button_url'] ) ? (string) $slide['left_button_url'] : '',
		);
	}

	return sawerna_sanitize_homepage_wide_slides( $slides );
}

/**
 * Get legacy narrow slides derived from the old paired slider data.
 *
 * @return array<int, array<string, string|int>>
 */
function sawerna_get_homepage_legacy_narrow_slides() {
	$legacy = sawerna_get_homepage_legacy_combined_slides();

	if ( empty( $legacy ) ) {
		return array();
	}

	$slides = array();

	foreach ( $legacy as $slide ) {
		$slides[] = array(
			'image_id'    => isset( $slide['right_image_id'] ) ? absint( $slide['right_image_id'] ) : 0,
			'title'       => isset( $slide['right_title'] ) ? (string) $slide['right_title'] : '',
			'text'        => isset( $slide['right_text'] ) ? (string) $slide['right_text'] : '',
			'list'        => isset( $slide['right_list'] ) ? (string) $slide['right_list'] : '',
			'button_text' => isset( $slide['right_button_text'] ) ? (string) $slide['right_button_text'] : '',
			'button_url'  => isset( $slide['right_button_url'] ) ? (string) $slide['right_button_url'] : '',
		);
	}

	return sawerna_sanitize_homepage_narrow_slides( $slides );
}

/**
 * Get legacy homepage meta setting from the front page.
 *
 * @param string $meta_key      Meta key.
 * @param string $default_value Default value.
 * @return string
 */
function sawerna_get_homepage_legacy_setting( $meta_key, $default_value = '' ) {
	$front_page_id = sawerna_homepage_front_page_id();

	if ( $front_page_id < 1 ) {
		return $default_value;
	}

	return sawerna_get_homepage_setting( $front_page_id, $meta_key, $default_value );
}

/**
 * Get homepage setting.
 *
 * @param int    $post_id       Post ID.
 * @param string $key           Setting key.
 * @param string $default_value Default value.
 * @return string
 */
function sawerna_get_homepage_setting( $post_id, $key, $default_value = '' ) {
	$value = get_post_meta( $post_id, $key, true );

	if ( ! is_string( $value ) || '' === trim( $value ) ) {
		return $default_value;
	}

	return $value;
}

/**
 * Enqueue homepage assets.
 *
 * @return void
 */
function sawerna_enqueue_homepage_assets() {
	if ( ! is_page_template( sawerna_homepage_template_slug() ) ) {
		return;
	}

	$theme_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( get_template_directory() . '/style.css' ) : _S_VERSION;
	$css_path      = get_template_directory() . '/assets/css/homepage.css';
	$js_path       = get_template_directory() . '/js/homepage.js';

	if ( file_exists( $css_path ) ) {
		$css_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $css_path ) : $theme_version;
		wp_enqueue_style( 'sawerna-homepage', get_template_directory_uri() . '/assets/css/homepage.css', array( 'sawerna-style' ), $css_version );
	}

	if ( file_exists( $js_path ) ) {
		$js_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $js_path ) : $theme_version;
		wp_enqueue_script( 'sawerna-homepage', get_template_directory_uri() . '/js/homepage.js', array(), $js_version, true );
		wp_script_add_data( 'sawerna-homepage', 'defer', true );
	}
}
add_action( 'wp_enqueue_scripts', 'sawerna_enqueue_homepage_assets' );

/**
 * Register homepage meta box.
 *
 * @return void
 */
function sawerna_homepage_register_metabox() {
	add_meta_box(
		'sawerna-homepage-settings',
		__( 'Homepage Layout', 'sawerna' ),
		'sawerna_homepage_render_metabox',
		'page',
		'normal',
		'high'
	);
}

/**
 * Render image field for homepage slides.
 *
 * @param string $field_name Field name.
 * @param int    $image_id   Attachment ID.
 * @param string $label      Field label.
 * @return void
 */
function sawerna_homepage_render_image_field( $field_name, $image_id, $label ) {
	$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';
	?>
	<div class="sawerna-home-admin__field sawerna-home-admin__field--image">
		<label><?php echo esc_html( $label ); ?></label>
		<div class="sawerna-home-admin__image-control" data-home-image-control="1">
			<input type="hidden" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( (string) $image_id ); ?>" data-home-image-id="1">
			<div class="sawerna-home-admin__image-preview"<?php echo $image_url ? '' : ' hidden'; ?> data-home-image-preview="1">
				<?php if ( $image_url ) : ?>
					<img src="<?php echo esc_url( $image_url ); ?>" alt="">
				<?php endif; ?>
			</div>
			<div class="sawerna-home-admin__image-actions">
				<button type="button" class="button" data-home-image-upload="1"><?php esc_html_e( 'Choose image', 'sawerna' ); ?></button>
				<button type="button" class="button" data-home-image-remove="1"<?php echo $image_url ? '' : ' hidden'; ?>><?php esc_html_e( 'Remove image', 'sawerna' ); ?></button>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Render a homepage slide item.
 *
 * @param array<string, string|int> $slide Slide data.
 * @param int|string                $index Slide index.
 * @return void
 */
function sawerna_homepage_render_slide_fields( array $slide, $index ) {
	$slide = wp_parse_args( $slide, sawerna_homepage_slide_defaults() );
	?>
	<div class="sawerna-home-admin__slide" data-home-slide-item="1">
		<div class="sawerna-home-admin__slide-head">
			<h4><?php esc_html_e( 'Slide', 'sawerna' ); ?></h4>
			<button type="button" class="button-link-delete" data-home-slide-remove="1"><?php esc_html_e( 'Remove slide', 'sawerna' ); ?></button>
		</div>

		<div class="sawerna-home-admin__columns">
			<div class="sawerna-home-admin__card">
				<h5><?php esc_html_e( 'Left card (2/3 width)', 'sawerna' ); ?></h5>
				<?php sawerna_homepage_render_image_field( "sawerna_homepage_slides[{$index}][left_image_id]", (int) $slide['left_image_id'], __( 'Background image', 'sawerna' ) ); ?>
				<p class="sawerna-home-admin__field">
					<label for="sawerna-home-left-title-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Title', 'sawerna' ); ?></label>
					<input id="sawerna-home-left-title-<?php echo esc_attr( (string) $index ); ?>" type="text" class="widefat" name="sawerna_homepage_slides[<?php echo esc_attr( (string) $index ); ?>][left_title]" value="<?php echo esc_attr( (string) $slide['left_title'] ); ?>">
				</p>
				<p class="sawerna-home-admin__field">
					<label for="sawerna-home-left-text-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Text', 'sawerna' ); ?></label>
					<textarea id="sawerna-home-left-text-<?php echo esc_attr( (string) $index ); ?>" class="widefat" rows="4" name="sawerna_homepage_slides[<?php echo esc_attr( (string) $index ); ?>][left_text]"><?php echo esc_textarea( (string) $slide['left_text'] ); ?></textarea>
				</p>
				<p class="sawerna-home-admin__field">
					<label for="sawerna-home-left-button-text-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Button text', 'sawerna' ); ?></label>
					<input id="sawerna-home-left-button-text-<?php echo esc_attr( (string) $index ); ?>" type="text" class="widefat" name="sawerna_homepage_slides[<?php echo esc_attr( (string) $index ); ?>][left_button_text]" value="<?php echo esc_attr( (string) $slide['left_button_text'] ); ?>">
				</p>
				<p class="sawerna-home-admin__field">
					<label for="sawerna-home-left-button-url-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Button URL', 'sawerna' ); ?></label>
					<input id="sawerna-home-left-button-url-<?php echo esc_attr( (string) $index ); ?>" type="url" class="widefat" name="sawerna_homepage_slides[<?php echo esc_attr( (string) $index ); ?>][left_button_url]" value="<?php echo esc_attr( (string) $slide['left_button_url'] ); ?>">
				</p>
			</div>

			<div class="sawerna-home-admin__card">
				<h5><?php esc_html_e( 'Right card (1/3 width)', 'sawerna' ); ?></h5>
				<?php sawerna_homepage_render_image_field( "sawerna_homepage_slides[{$index}][right_image_id]", (int) $slide['right_image_id'], __( 'Background image', 'sawerna' ) ); ?>
				<p class="sawerna-home-admin__field">
					<label for="sawerna-home-right-title-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Title', 'sawerna' ); ?></label>
					<input id="sawerna-home-right-title-<?php echo esc_attr( (string) $index ); ?>" type="text" class="widefat" name="sawerna_homepage_slides[<?php echo esc_attr( (string) $index ); ?>][right_title]" value="<?php echo esc_attr( (string) $slide['right_title'] ); ?>">
				</p>
				<p class="sawerna-home-admin__field">
					<label for="sawerna-home-right-text-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Text', 'sawerna' ); ?></label>
					<textarea id="sawerna-home-right-text-<?php echo esc_attr( (string) $index ); ?>" class="widefat" rows="3" name="sawerna_homepage_slides[<?php echo esc_attr( (string) $index ); ?>][right_text]"><?php echo esc_textarea( (string) $slide['right_text'] ); ?></textarea>
				</p>
				<p class="sawerna-home-admin__field">
					<label for="sawerna-home-right-list-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'List items', 'sawerna' ); ?></label>
					<textarea id="sawerna-home-right-list-<?php echo esc_attr( (string) $index ); ?>" class="widefat" rows="4" name="sawerna_homepage_slides[<?php echo esc_attr( (string) $index ); ?>][right_list]" placeholder="<?php esc_attr_e( "One line per item\nAnother item", 'sawerna' ); ?>"><?php echo esc_textarea( (string) $slide['right_list'] ); ?></textarea>
				</p>
				<p class="sawerna-home-admin__field">
					<label for="sawerna-home-right-button-text-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Button text', 'sawerna' ); ?></label>
					<input id="sawerna-home-right-button-text-<?php echo esc_attr( (string) $index ); ?>" type="text" class="widefat" name="sawerna_homepage_slides[<?php echo esc_attr( (string) $index ); ?>][right_button_text]" value="<?php echo esc_attr( (string) $slide['right_button_text'] ); ?>">
				</p>
				<p class="sawerna-home-admin__field">
					<label for="sawerna-home-right-button-url-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Button URL', 'sawerna' ); ?></label>
					<input id="sawerna-home-right-button-url-<?php echo esc_attr( (string) $index ); ?>" type="url" class="widefat" name="sawerna_homepage_slides[<?php echo esc_attr( (string) $index ); ?>][right_button_url]" value="<?php echo esc_attr( (string) $slide['right_button_url'] ); ?>">
				</p>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Render homepage settings meta box.
 *
 * @param WP_Post $post Post object.
 * @return void
 */
function sawerna_homepage_render_metabox( $post ) {
	$template_slug        = get_page_template_slug( $post );
	$slides               = sawerna_get_homepage_slides( $post->ID );
	$category_heading     = sawerna_get_homepage_setting( $post->ID, '_sawerna_homepage_category_heading', __( 'Alusta shoppamist oiges kategoorias', 'sawerna' ) );
	$category_button_more = sawerna_get_homepage_setting( $post->ID, '_sawerna_homepage_category_more_label', __( 'Kuva rohkem kategooriaid', 'sawerna' ) );
	$category_button_less = sawerna_get_homepage_setting( $post->ID, '_sawerna_homepage_category_less_label', __( 'Peida kategooriad', 'sawerna' ) );

	wp_nonce_field( 'sawerna_homepage_save', 'sawerna_homepage_nonce' );
	?>
	<style>
		.sawerna-home-admin__notice { margin: 0 0 16px; }
		.sawerna-home-admin__field { margin: 0 0 16px; }
		.sawerna-home-admin__field label,
		.sawerna-home-admin__card h5,
		.sawerna-home-admin__slide-head h4 { display: block; margin: 0 0 8px; font-weight: 600; }
		.sawerna-home-admin__slides { display: grid; gap: 18px; }
		.sawerna-home-admin__slide { border: 1px solid #dcdcde; border-radius: 12px; padding: 18px; background: #fff; }
		.sawerna-home-admin__slide-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 18px; }
		.sawerna-home-admin__columns { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
		.sawerna-home-admin__card { border: 1px solid #e2e4e7; border-radius: 10px; padding: 16px; background: #f6f7f7; }
		.sawerna-home-admin__image-preview { margin-bottom: 10px; }
		.sawerna-home-admin__image-preview img { display: block; width: 100%; max-width: 240px; border-radius: 8px; height: auto; }
		.sawerna-home-admin__image-actions { display: flex; flex-wrap: wrap; gap: 8px; }
		.sawerna-home-admin__footer { margin-top: 18px; }
		@media (max-width: 960px) {
			.sawerna-home-admin__columns { grid-template-columns: 1fr; }
		}
	</style>

	<p class="sawerna-home-admin__notice">
		<?php
		echo esc_html(
			sawerna_homepage_template_slug() === $template_slug
				? __( 'These settings are used by the Home Page template.', 'sawerna' )
				: __( 'Assign the “Home Page” template to this page to use these settings on the frontend.', 'sawerna' )
		);
		?>
	</p>

	<p class="sawerna-home-admin__field">
		<label for="sawerna-homepage-category-heading"><?php esc_html_e( 'Category section title', 'sawerna' ); ?></label>
		<input id="sawerna-homepage-category-heading" type="text" class="widefat" name="sawerna_homepage_category_heading" value="<?php echo esc_attr( $category_heading ); ?>">
	</p>

	<div class="sawerna-home-admin__columns">
		<p class="sawerna-home-admin__field">
			<label for="sawerna-homepage-category-more"><?php esc_html_e( 'Show more label', 'sawerna' ); ?></label>
			<input id="sawerna-homepage-category-more" type="text" class="widefat" name="sawerna_homepage_category_more_label" value="<?php echo esc_attr( $category_button_more ); ?>">
		</p>
		<p class="sawerna-home-admin__field">
			<label for="sawerna-homepage-category-less"><?php esc_html_e( 'Show less label', 'sawerna' ); ?></label>
			<input id="sawerna-homepage-category-less" type="text" class="widefat" name="sawerna_homepage_category_less_label" value="<?php echo esc_attr( $category_button_less ); ?>">
		</p>
	</div>

	<div class="sawerna-home-admin__slides" data-home-slides-root="1">
		<?php
		if ( empty( $slides ) ) {
			sawerna_homepage_render_slide_fields( sawerna_homepage_slide_defaults(), 0 );
		} else {
			foreach ( $slides as $index => $slide ) {
				sawerna_homepage_render_slide_fields( $slide, $index );
			}
		}
		?>
	</div>

	<p class="sawerna-home-admin__footer">
		<button type="button" class="button button-primary" data-home-slide-add="1"><?php esc_html_e( 'Add slide', 'sawerna' ); ?></button>
	</p>

	<template id="sawerna-home-slide-template">
		<?php sawerna_homepage_render_slide_fields( sawerna_homepage_slide_defaults(), '__INDEX__' ); ?>
	</template>
	<?php
}

/**
 * Save homepage meta box fields.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function sawerna_homepage_save_metabox( $post_id ) {
	if ( ! isset( $_POST['sawerna_homepage_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sawerna_homepage_nonce'] ) ), 'sawerna_homepage_save' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$slides = sawerna_sanitize_homepage_slides( isset( $_POST['sawerna_homepage_slides'] ) ? wp_unslash( $_POST['sawerna_homepage_slides'] ) : array() );
	update_post_meta( $post_id, '_sawerna_homepage_slides', $slides );

	$category_heading    = isset( $_POST['sawerna_homepage_category_heading'] ) ? sanitize_text_field( wp_unslash( $_POST['sawerna_homepage_category_heading'] ) ) : '';
	$category_more_label = isset( $_POST['sawerna_homepage_category_more_label'] ) ? sanitize_text_field( wp_unslash( $_POST['sawerna_homepage_category_more_label'] ) ) : '';
	$category_less_label = isset( $_POST['sawerna_homepage_category_less_label'] ) ? sanitize_text_field( wp_unslash( $_POST['sawerna_homepage_category_less_label'] ) ) : '';

	update_post_meta( $post_id, '_sawerna_homepage_category_heading', $category_heading );
	update_post_meta( $post_id, '_sawerna_homepage_category_more_label', $category_more_label );
	update_post_meta( $post_id, '_sawerna_homepage_category_less_label', $category_less_label );
}

/**
 * Enqueue homepage admin assets.
 *
 * @param string $hook_suffix Screen hook.
 * @return void
 */
function sawerna_homepage_admin_assets( $hook_suffix ) {
	if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'page' !== $screen->post_type ) {
		return;
	}

	$theme_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( get_template_directory() . '/style.css' ) : _S_VERSION;
	$js_path       = get_template_directory() . '/js/admin-homepage.js';

	wp_enqueue_media();

	if ( file_exists( $js_path ) ) {
		$js_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $js_path ) : $theme_version;
		wp_enqueue_script( 'sawerna-admin-homepage', get_template_directory_uri() . '/js/admin-homepage.js', array( 'jquery' ), $js_version, true );
	}
}

/**
 * Get homepage category terms.
 *
 * @return array<int, WP_Term>
 */
function sawerna_get_homepage_category_terms() {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}

	$uncategorized_id = (int) get_option( 'default_product_cat', 0 );

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'parent'     => 0,
			'orderby'    => 'name',
			'order'      => 'ASC',
			'exclude'    => $uncategorized_id > 0 ? array( $uncategorized_id ) : array(),
		)
	);

	return is_array( $terms ) ? $terms : array();
}

/**
 * Get homepage options defaults.
 *
 * @return array<string, mixed>
 */
function sawerna_homepage_options_defaults() {
	return array(
		'category_heading'            => __( 'Alusta shoppamist oiges kategoorias', 'sawerna' ),
		'category_more_label'         => __( 'Kuva rohkem kategooriaid', 'sawerna' ),
		'category_less_label'         => __( 'Peida kategooriad', 'sawerna' ),
		'products_archive_url'        => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ),
		'new_products_use_latest'     => 1,
		'new_products_ids'            => array(),
		'recommendation_category_ids' => array(),
		'about_title'                 => __( 'Sawerna – uus bränd, pikk ajalugu', 'sawerna' ),
		'about_subtitle'              => __( 'Perefirma eelmise sajandi keskpaigast', 'sawerna' ),
		'about_text'                  => __( 'Sawerna sai alguse soovist teha mööblist asumaks, lihtsamaks ja inimlikumaks. Selle taga on Carl, meie looja ja tegevjuht, kelle peres on mööblit tehtud juba kolm põlvkonda. Kui varasemalt tegutseti rohkem tootja ja hulgimüüjana, siis Carl soovis midagi isiklikumat - suhelda otse klientidega ja pakkuda neile valikuid, mis on läbimõeldud, kvaliteetsed ja päriselt vajalikud. Just nii sündiski Sawerna.', 'sawerna' ),
		'about_button_text'           => __( 'Loe rohkem', 'sawerna' ),
		'about_button_url'            => home_url( '/meist/' ),
		'factory_products_ids'        => array(),
		'factory_archive_url'         => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ),
		'testimonials_title'          => __( 'Mida kliendid meist räägivad?', 'sawerna' ),
		'sale_products_use_random'    => 1,
		'sale_products_ids'           => array(),
		'sale_archive_url'            => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ),
		'best_sellers_use_automatic'  => 1,
		'best_sellers_products_ids'   => array(),
		'best_sellers_archive_url'    => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ),
		'blog_title'                  => __( 'Nõuanded ja blogi', 'sawerna' ),
		'blog_archive_url'            => (int) get_option( 'page_for_posts' ) > 0 ? get_permalink( (int) get_option( 'page_for_posts' ) ) : home_url( '/blog/' ),
	);
}

/**
 * Get homepage options.
 *
 * @return array<string, mixed>
 */
function sawerna_get_homepage_options() {
	// Front end calls this twice per homepage request (hero preload in wp_head + template); the
	// defaults resolve attachment IDs by URL, so memoize per language. Admin is never memoized.
	static $memo = array();
	$memo_key    = is_admin() ? '' : (string) apply_filters( 'wpml_current_language', 'et' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Third-party WPML hook.
	if ( '' !== $memo_key && isset( $memo[ $memo_key ] ) ) {
		return $memo[ $memo_key ];
	}

	$stored   = get_option( 'sawerna_homepage_options', array() );
	$defaults = sawerna_homepage_options_defaults();
	$options  = wp_parse_args( is_array( $stored ) ? $stored : array(), $defaults );

	$lang                                   = (string) apply_filters( 'wpml_current_language', 'et' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Third-party WPML hook.
	$slides                                 = sawerna_get_homepage_slides_for_lang( $lang );
	$options['hero_wide_slides']            = $slides['hero_wide'];
	$options['hero_narrow_slides']          = $slides['hero_narrow'];
	$options['category_heading']            = sanitize_text_field( (string) $options['category_heading'] );
	$options['category_more_label']         = sanitize_text_field( (string) $options['category_more_label'] );
	$options['category_less_label']         = sanitize_text_field( (string) $options['category_less_label'] );
	$options['products_archive_url']        = esc_url_raw( (string) $options['products_archive_url'] );
	$options['new_products_use_latest']     = ! empty( $options['new_products_use_latest'] ) ? 1 : 0;
	$options['new_products_ids']            = array_values( array_filter( array_map( 'absint', is_array( $options['new_products_ids'] ) ? $options['new_products_ids'] : array() ) ) );
	$options['recommendation_category_ids'] = array_values( array_filter( array_map( 'absint', is_array( $options['recommendation_category_ids'] ) ? $options['recommendation_category_ids'] : array() ) ) );
	$options['about_title']                 = sanitize_text_field( (string) $options['about_title'] );
	$options['about_subtitle']              = sanitize_text_field( (string) $options['about_subtitle'] );
	$options['about_text']                  = sanitize_textarea_field( (string) $options['about_text'] );
	$options['about_button_text']           = sanitize_text_field( (string) $options['about_button_text'] );
	$options['about_button_url']            = esc_url_raw( (string) $options['about_button_url'] );
	$options['factory_products_ids']        = array_values( array_filter( array_map( 'absint', is_array( $options['factory_products_ids'] ) ? $options['factory_products_ids'] : array() ) ) );
	$options['factory_archive_url']         = esc_url_raw( (string) $options['factory_archive_url'] );
	$options['testimonials_title']          = sanitize_text_field( (string) $options['testimonials_title'] );
	$options['sale_products_use_random']    = ! empty( $options['sale_products_use_random'] ) ? 1 : 0;
	$options['sale_products_ids']           = array_values( array_filter( array_map( 'absint', is_array( $options['sale_products_ids'] ) ? $options['sale_products_ids'] : array() ) ) );
	$options['sale_archive_url']            = esc_url_raw( (string) $options['sale_archive_url'] );
	$options['best_sellers_use_automatic']  = ! empty( $options['best_sellers_use_automatic'] ) ? 1 : 0;
	$options['best_sellers_products_ids']   = array_values( array_filter( array_map( 'absint', is_array( $options['best_sellers_products_ids'] ) ? $options['best_sellers_products_ids'] : array() ) ) );
	$options['best_sellers_archive_url']    = esc_url_raw( (string) $options['best_sellers_archive_url'] );
	$options['blog_title']                  = sanitize_text_field( (string) $options['blog_title'] );
	$options['blog_archive_url']            = esc_url_raw( (string) $options['blog_archive_url'] );

	if ( empty( $options['hero_wide_slides'] ) ) {
		$legacy_wide                 = sawerna_get_homepage_legacy_wide_slides();
		$options['hero_wide_slides'] = ! empty( $legacy_wide ) ? $legacy_wide : $defaults['hero_wide_slides'];
	}

	if ( empty( $options['hero_narrow_slides'] ) ) {
		$legacy_narrow                 = sawerna_get_homepage_legacy_narrow_slides();
		$options['hero_narrow_slides'] = ! empty( $legacy_narrow ) ? $legacy_narrow : $defaults['hero_narrow_slides'];
	}

	if ( '' === trim( $options['category_heading'] ) ) {
		$options['category_heading'] = sawerna_get_homepage_legacy_setting( '_sawerna_homepage_category_heading', (string) $defaults['category_heading'] );
	}

	if ( '' === trim( $options['category_more_label'] ) ) {
		$options['category_more_label'] = sawerna_get_homepage_legacy_setting( '_sawerna_homepage_category_more_label', (string) $defaults['category_more_label'] );
	}

	if ( '' === trim( $options['category_less_label'] ) ) {
		$options['category_less_label'] = sawerna_get_homepage_legacy_setting( '_sawerna_homepage_category_less_label', (string) $defaults['category_less_label'] );
	}

	$options = sawerna_homepage_translate_wpml_option_strings( $options );
	if ( '' !== $memo_key ) {
		$memo[ $memo_key ] = $options;
	}

	return $options;
}

/**
 * Register homepage options admin page.
 *
 * @return void
 */
function sawerna_homepage_register_admin_menu() {
	add_menu_page(
		__( 'Page options', 'sawerna' ),
		__( 'Page options', 'sawerna' ),
		'manage_options',
		'sawerna-page-options',
		'sawerna_homepage_render_admin_page',
		'dashicons-layout',
		58
	);
	add_submenu_page(
		'sawerna-page-options',
		__( 'Home page', 'sawerna' ),
		__( 'Home page', 'sawerna' ),
		'manage_options',
		'sawerna-page-options',
		'sawerna_homepage_render_admin_page'
	);
}
add_action( 'admin_menu', 'sawerna_homepage_register_admin_menu' );

/**
 * Register testimonials post type.
 *
 * @return void
 */
function sawerna_homepage_register_testimonials_post_type() {
	register_post_type(
		'sawerna_testimonial',
		array(
			'labels'        => array(
				'name'               => __( 'Testimonials', 'sawerna' ),
				'singular_name'      => __( 'Testimonial', 'sawerna' ),
				'add_new'            => __( 'Add New', 'sawerna' ),
				'add_new_item'       => __( 'Add New Testimonial', 'sawerna' ),
				'edit_item'          => __( 'Edit Testimonial', 'sawerna' ),
				'new_item'           => __( 'New Testimonial', 'sawerna' ),
				'view_item'          => __( 'View Testimonial', 'sawerna' ),
				'search_items'       => __( 'Search Testimonials', 'sawerna' ),
				'not_found'          => __( 'No testimonials found.', 'sawerna' ),
				'not_found_in_trash' => __( 'No testimonials found in Trash.', 'sawerna' ),
				'menu_name'          => __( 'Testimonials', 'sawerna' ),
			),
			'public'        => false,
			'show_ui'       => true,
			'show_in_menu'  => true,
			'menu_position' => 59,
			'menu_icon'     => 'dashicons-format-quote',
			'supports'      => array( 'title', 'editor' ),
			'has_archive'   => false,
			'rewrite'       => false,
			'show_in_rest'  => true,
		)
	);
}
add_action( 'init', 'sawerna_homepage_register_testimonials_post_type' );

/**
 * Register testimonial meta box.
 *
 * @return void
 */
function sawerna_homepage_register_testimonial_metabox() {
	add_meta_box(
		'sawerna-testimonial-details',
		__( 'Testimonial Details', 'sawerna' ),
		'sawerna_homepage_render_testimonial_metabox',
		'sawerna_testimonial',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes_sawerna_testimonial', 'sawerna_homepage_register_testimonial_metabox' );

/**
 * Render testimonial meta box.
 *
 * @param WP_Post $post Post object.
 * @return void
 */
function sawerna_homepage_render_testimonial_metabox( $post ) {
	$feedback_date = get_post_meta( $post->ID, '_sawerna_testimonial_date', true );

	wp_nonce_field( 'sawerna_testimonial_save', 'sawerna_testimonial_nonce' );
	?>
	<p>
		<label for="sawerna-testimonial-date"><strong><?php esc_html_e( 'Feedback date', 'sawerna' ); ?></strong></label>
		<input id="sawerna-testimonial-date" type="date" name="sawerna_testimonial_date" value="<?php echo esc_attr( (string) $feedback_date ); ?>">
	</p>
	<p><?php esc_html_e( 'Use the testimonial title as the customer name, and the content editor for the testimonial text.', 'sawerna' ); ?></p>
	<?php
}

/**
 * Save testimonial meta box fields.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function sawerna_homepage_save_testimonial_metabox( $post_id ) {
	if ( ! isset( $_POST['sawerna_testimonial_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sawerna_testimonial_nonce'] ) ), 'sawerna_testimonial_save' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$feedback_date = isset( $_POST['sawerna_testimonial_date'] ) ? sanitize_text_field( wp_unslash( $_POST['sawerna_testimonial_date'] ) ) : '';
	update_post_meta( $post_id, '_sawerna_testimonial_date', $feedback_date );
}
add_action( 'save_post_sawerna_testimonial', 'sawerna_homepage_save_testimonial_metabox' );

/**
 * Enqueue homepage options admin assets.
 *
 * @param string $hook_suffix Screen hook.
 * @return void
 */
function sawerna_homepage_options_admin_assets( $hook_suffix ) {
	if ( 'toplevel_page_sawerna-page-options' !== $hook_suffix ) {
		return;
	}

	if ( class_exists( 'WooCommerce' ) ) {
		wp_enqueue_script( 'wc-enhanced-select' );
		wp_enqueue_style( 'woocommerce_admin_styles' );
	}

	$theme_version   = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( get_template_directory() . '/style.css' ) : _S_VERSION;
	$options_js_path = get_template_directory() . '/js/admin-homepage-options.js';
	$slider_js_path  = get_template_directory() . '/js/admin-homepage.js';

	wp_enqueue_media();

	if ( file_exists( $options_js_path ) ) {
		$js_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $options_js_path ) : $theme_version;
		wp_enqueue_script( 'sawerna-admin-homepage-options', get_template_directory_uri() . '/js/admin-homepage-options.js', array( 'jquery', 'wc-enhanced-select' ), $js_version, true );
	}

	if ( file_exists( $slider_js_path ) ) {
		$js_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $slider_js_path ) : $theme_version;
		wp_enqueue_script( 'sawerna-admin-homepage', get_template_directory_uri() . '/js/admin-homepage.js', array( 'jquery' ), $js_version, true );
	}
}
add_action( 'admin_enqueue_scripts', 'sawerna_homepage_options_admin_assets' );

/**
 * Save homepage options.
 *
 * @return void
 */
function sawerna_homepage_handle_admin_save() {
	if ( ! is_admin() || ! isset( $_POST['sawerna_homepage_options_nonce'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sawerna_homepage_options_nonce'] ) ), 'sawerna_homepage_options_save' ) ) {
		return;
	}

	$options = array(
		'hero_wide_slides'            => isset( $_POST['hero_wide_slides'] ) ? sawerna_sanitize_homepage_wide_slides( wp_unslash( $_POST['hero_wide_slides'] ) ) : array(),
		'hero_narrow_slides'          => isset( $_POST['hero_narrow_slides'] ) ? sawerna_sanitize_homepage_narrow_slides( wp_unslash( $_POST['hero_narrow_slides'] ) ) : array(),
		'category_heading'            => isset( $_POST['category_heading'] ) ? sanitize_text_field( wp_unslash( $_POST['category_heading'] ) ) : '',
		'category_more_label'         => isset( $_POST['category_more_label'] ) ? sanitize_text_field( wp_unslash( $_POST['category_more_label'] ) ) : '',
		'category_less_label'         => isset( $_POST['category_less_label'] ) ? sanitize_text_field( wp_unslash( $_POST['category_less_label'] ) ) : '',
		'products_archive_url'        => isset( $_POST['products_archive_url'] ) ? esc_url_raw( wp_unslash( $_POST['products_archive_url'] ) ) : '',
		'new_products_use_latest'     => isset( $_POST['new_products_use_latest'] ) ? 1 : 0,
		'new_products_ids'            => isset( $_POST['new_products_ids'] ) ? array_values( array_filter( array_map( 'absint', (array) wp_unslash( $_POST['new_products_ids'] ) ) ) ) : array(),
		'recommendation_category_ids' => isset( $_POST['recommendation_category_ids'] ) ? array_values( array_filter( array_map( 'absint', (array) wp_unslash( $_POST['recommendation_category_ids'] ) ) ) ) : array(),
		'about_title'                 => isset( $_POST['about_title'] ) ? sanitize_text_field( wp_unslash( $_POST['about_title'] ) ) : '',
		'about_subtitle'              => isset( $_POST['about_subtitle'] ) ? sanitize_text_field( wp_unslash( $_POST['about_subtitle'] ) ) : '',
		'about_text'                  => isset( $_POST['about_text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['about_text'] ) ) : '',
		'about_button_text'           => isset( $_POST['about_button_text'] ) ? sanitize_text_field( wp_unslash( $_POST['about_button_text'] ) ) : '',
		'about_button_url'            => isset( $_POST['about_button_url'] ) ? esc_url_raw( wp_unslash( $_POST['about_button_url'] ) ) : '',
		'factory_products_ids'        => isset( $_POST['factory_products_ids'] ) ? array_values( array_filter( array_map( 'absint', (array) wp_unslash( $_POST['factory_products_ids'] ) ) ) ) : array(),
		'factory_archive_url'         => isset( $_POST['factory_archive_url'] ) ? esc_url_raw( wp_unslash( $_POST['factory_archive_url'] ) ) : '',
		'testimonials_title'          => isset( $_POST['testimonials_title'] ) ? sanitize_text_field( wp_unslash( $_POST['testimonials_title'] ) ) : '',
		'sale_products_use_random'    => isset( $_POST['sale_products_use_random'] ) ? 1 : 0,
		'sale_products_ids'           => isset( $_POST['sale_products_ids'] ) ? array_values( array_filter( array_map( 'absint', (array) wp_unslash( $_POST['sale_products_ids'] ) ) ) ) : array(),
		'sale_archive_url'            => isset( $_POST['sale_archive_url'] ) ? esc_url_raw( wp_unslash( $_POST['sale_archive_url'] ) ) : '',
		'best_sellers_use_automatic'  => isset( $_POST['best_sellers_use_automatic'] ) ? 1 : 0,
		'best_sellers_products_ids'   => isset( $_POST['best_sellers_products_ids'] ) ? array_values( array_filter( array_map( 'absint', (array) wp_unslash( $_POST['best_sellers_products_ids'] ) ) ) ) : array(),
		'best_sellers_archive_url'    => isset( $_POST['best_sellers_archive_url'] ) ? esc_url_raw( wp_unslash( $_POST['best_sellers_archive_url'] ) ) : '',
		'blog_title'                  => isset( $_POST['blog_title'] ) ? sanitize_text_field( wp_unslash( $_POST['blog_title'] ) ) : '',
		'blog_archive_url'            => isset( $_POST['blog_archive_url'] ) ? esc_url_raw( wp_unslash( $_POST['blog_archive_url'] ) ) : '',
	);

	sawerna_homepage_register_wpml_option_strings( $options );
	update_option( 'sawerna_homepage_options', $options );

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'    => 'sawerna-page-options',
				'updated' => '1',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_init', 'sawerna_homepage_handle_admin_save' );

/**
 * Render homepage product search field.
 *
 * @param string     $field_id    Field ID.
 * @param string     $field_name  Field name.
 * @param array<int> $product_ids Product IDs.
 * @return void
 */
function sawerna_homepage_render_product_search_field( $field_id, $field_name, array $product_ids ) {
	?>
	<select
		class="wc-product-search"
		multiple="multiple"
		style="width: 100%;"
		id="<?php echo esc_attr( $field_id ); ?>"
		name="<?php echo esc_attr( $field_name ); ?>[]"
		data-placeholder="<?php esc_attr_e( 'Search for products…', 'sawerna' ); ?>"
		data-action="woocommerce_json_search_products_and_variations"
	>
		<?php foreach ( $product_ids as $product_id ) : ?>
			<?php $product = wc_get_product( $product_id ); ?>
			<?php if ( $product ) : ?>
				<option value="<?php echo esc_attr( (string) $product_id ); ?>" selected="selected"><?php echo esc_html( wp_strip_all_tags( $product->get_formatted_name() ) ); ?></option>
			<?php endif; ?>
		<?php endforeach; ?>
	</select>
	<?php
}

/**
 * Render wide hero slide fields.
 *
 * @param array<string, string|int> $slide Slide data.
 * @param int|string                $index Slide index.
 * @return void
 */
function sawerna_homepage_render_wide_hero_slide_fields( array $slide, $index ) {
	$slide = wp_parse_args( $slide, sawerna_homepage_wide_slide_defaults() );
	?>
	<div class="sawerna-home-admin__slide" data-home-slide-item="1">
		<div class="sawerna-home-admin__slide-head">
			<h4><?php esc_html_e( 'Wide slide', 'sawerna' ); ?></h4>
			<button type="button" class="button-link-delete" data-home-slide-remove="1"><?php esc_html_e( 'Remove slide', 'sawerna' ); ?></button>
		</div>

		<div class="sawerna-home-admin__card">
			<?php sawerna_homepage_render_image_field( "hero_wide_slides[{$index}][image_id]", (int) $slide['image_id'], __( 'Background image', 'sawerna' ) ); ?>
			<p class="sawerna-home-admin__field">
				<label for="sawerna-home-wide-title-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Title', 'sawerna' ); ?></label>
				<input id="sawerna-home-wide-title-<?php echo esc_attr( (string) $index ); ?>" type="text" class="widefat" name="hero_wide_slides[<?php echo esc_attr( (string) $index ); ?>][title]" value="<?php echo esc_attr( (string) $slide['title'] ); ?>">
			</p>
			<p class="sawerna-home-admin__field">
				<label for="sawerna-home-wide-text-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Text', 'sawerna' ); ?></label>
				<textarea id="sawerna-home-wide-text-<?php echo esc_attr( (string) $index ); ?>" class="widefat" rows="4" name="hero_wide_slides[<?php echo esc_attr( (string) $index ); ?>][text]"><?php echo esc_textarea( (string) $slide['text'] ); ?></textarea>
			</p>
			<p class="sawerna-home-admin__field">
				<label for="sawerna-home-wide-button-text-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Button text', 'sawerna' ); ?></label>
				<input id="sawerna-home-wide-button-text-<?php echo esc_attr( (string) $index ); ?>" type="text" class="widefat" name="hero_wide_slides[<?php echo esc_attr( (string) $index ); ?>][button_text]" value="<?php echo esc_attr( (string) $slide['button_text'] ); ?>">
			</p>
			<p class="sawerna-home-admin__field">
				<label for="sawerna-home-wide-button-url-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Button URL', 'sawerna' ); ?></label>
				<input id="sawerna-home-wide-button-url-<?php echo esc_attr( (string) $index ); ?>" type="url" class="widefat" name="hero_wide_slides[<?php echo esc_attr( (string) $index ); ?>][button_url]" value="<?php echo esc_attr( (string) $slide['button_url'] ); ?>">
			</p>
		</div>
	</div>
	<?php
}

/**
 * Render narrow hero slide fields.
 *
 * @param array<string, string|int> $slide Slide data.
 * @param int|string                $index Slide index.
 * @return void
 */
function sawerna_homepage_render_narrow_hero_slide_fields( array $slide, $index ) {
	$slide = wp_parse_args( $slide, sawerna_homepage_narrow_slide_defaults() );
	?>
	<div class="sawerna-home-admin__slide" data-home-slide-item="1">
		<div class="sawerna-home-admin__slide-head">
			<h4><?php esc_html_e( 'Narrow slide', 'sawerna' ); ?></h4>
			<button type="button" class="button-link-delete" data-home-slide-remove="1"><?php esc_html_e( 'Remove slide', 'sawerna' ); ?></button>
		</div>

		<div class="sawerna-home-admin__card">
			<?php sawerna_homepage_render_image_field( "hero_narrow_slides[{$index}][image_id]", (int) $slide['image_id'], __( 'Background image', 'sawerna' ) ); ?>
			<p class="sawerna-home-admin__field">
				<label for="sawerna-home-narrow-title-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Title', 'sawerna' ); ?></label>
				<input id="sawerna-home-narrow-title-<?php echo esc_attr( (string) $index ); ?>" type="text" class="widefat" name="hero_narrow_slides[<?php echo esc_attr( (string) $index ); ?>][title]" value="<?php echo esc_attr( (string) $slide['title'] ); ?>">
			</p>
			<p class="sawerna-home-admin__field">
				<label for="sawerna-home-narrow-text-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Text', 'sawerna' ); ?></label>
				<textarea id="sawerna-home-narrow-text-<?php echo esc_attr( (string) $index ); ?>" class="widefat" rows="3" name="hero_narrow_slides[<?php echo esc_attr( (string) $index ); ?>][text]"><?php echo esc_textarea( (string) $slide['text'] ); ?></textarea>
			</p>
			<p class="sawerna-home-admin__field">
				<label for="sawerna-home-narrow-list-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'List items', 'sawerna' ); ?></label>
				<textarea id="sawerna-home-narrow-list-<?php echo esc_attr( (string) $index ); ?>" class="widefat" rows="4" name="hero_narrow_slides[<?php echo esc_attr( (string) $index ); ?>][list]" placeholder="<?php esc_attr_e( "One line per item\nAnother item", 'sawerna' ); ?>"><?php echo esc_textarea( (string) $slide['list'] ); ?></textarea>
			</p>
			<p class="sawerna-home-admin__field">
				<label for="sawerna-home-narrow-button-text-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Button text', 'sawerna' ); ?></label>
				<input id="sawerna-home-narrow-button-text-<?php echo esc_attr( (string) $index ); ?>" type="text" class="widefat" name="hero_narrow_slides[<?php echo esc_attr( (string) $index ); ?>][button_text]" value="<?php echo esc_attr( (string) $slide['button_text'] ); ?>">
			</p>
			<p class="sawerna-home-admin__field">
				<label for="sawerna-home-narrow-button-url-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Button URL', 'sawerna' ); ?></label>
				<input id="sawerna-home-narrow-button-url-<?php echo esc_attr( (string) $index ); ?>" type="url" class="widefat" name="hero_narrow_slides[<?php echo esc_attr( (string) $index ); ?>][button_url]" value="<?php echo esc_attr( (string) $slide['button_url'] ); ?>">
			</p>
		</div>
	</div>
	<?php
}

/**
 * Render homepage admin page.
 *
 * @return void
 */
function sawerna_homepage_render_admin_page() {
	$options    = sawerna_get_homepage_options();
	$categories = taxonomy_exists( 'product_cat' )
		? get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		)
		: array();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Page options', 'sawerna' ); ?></h1>

		<?php if ( isset( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only flag for the post-save admin notice. ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Page options saved.', 'sawerna' ); ?></p></div>
		<?php endif; ?>

		<style>
			.sawerna-home-options__grid { display:grid; gap:24px; max-width:1100px; }
			.sawerna-home-options__card { background:#fff; border:1px solid #dcdcde; border-radius:12px; padding:24px; }
			.sawerna-home-options__card h2 { margin:0 0 12px; font-size:20px; }
			.sawerna-home-options__field { margin:0 0 18px; }
			.sawerna-home-options__field label { display:block; margin:0 0 8px; font-weight:600; }
			.sawerna-home-options__hint { margin:6px 0 0; color:#646970; }
			.sawerna-home-options__check { display:flex; align-items:center; gap:10px; }
			.sawerna-home-admin__intro { margin: 0 0 18px; color:#50575e; max-width: 900px; }
			.sawerna-home-admin__field { margin: 0 0 16px; }
			.sawerna-home-admin__field label,
			.sawerna-home-admin__card h5,
			.sawerna-home-admin__slide-head h4 { display: block; margin: 0 0 8px; font-weight: 600; }
			.sawerna-home-admin__slides { display: grid; gap: 18px; }
			.sawerna-home-admin__slide { border: 1px solid #dcdcde; border-radius: 12px; padding: 18px; background: #fff; }
			.sawerna-home-admin__slide-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 18px; }
			.sawerna-home-admin__columns { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
			.sawerna-home-admin__card { border: 1px solid #e2e4e7; border-radius: 10px; padding: 16px; background: #f6f7f7; }
			.sawerna-home-admin__image-preview { margin-bottom: 10px; }
			.sawerna-home-admin__image-preview img { display: block; width: 100%; max-width: 240px; border-radius: 8px; height: auto; }
			.sawerna-home-admin__image-actions { display: flex; flex-wrap: wrap; gap: 8px; }
			.sawerna-home-admin__footer { margin-top: 18px; }
			@media (max-width: 960px) {
				.sawerna-home-admin__columns { grid-template-columns: 1fr; }
			}
		</style>

		<form method="post" action="">
			<?php wp_nonce_field( 'sawerna_homepage_options_save', 'sawerna_homepage_options_nonce' ); ?>

			<div class="sawerna-home-options__grid">
				<section class="sawerna-home-options__card">
					<h2><?php esc_html_e( 'Home page slider', 'sawerna' ); ?></h2>
					<p class="sawerna-home-admin__intro"><?php esc_html_e( 'The homepage hero now uses two independent sliders: a wide 2/3 column slider on the left and a narrow 1/3 column slider on the right.', 'sawerna' ); ?></p>

					<div class="sawerna-home-admin__columns">
						<div>
							<h3><?php esc_html_e( 'Wide slider', 'sawerna' ); ?></h3>
							<div id="sawerna-home-wide-slides-root" class="sawerna-home-admin__slides" data-home-slides-root="1">
								<?php foreach ( $options['hero_wide_slides'] as $index => $slide ) : ?>
									<?php sawerna_homepage_render_wide_hero_slide_fields( $slide, $index ); ?>
								<?php endforeach; ?>
							</div>
							<p class="sawerna-home-admin__footer">
								<button type="button" class="button button-primary" data-home-slide-add="1" data-home-slides-root-target="sawerna-home-wide-slides-root" data-home-template-id="sawerna-home-wide-slide-template"><?php esc_html_e( 'Add wide slide', 'sawerna' ); ?></button>
							</p>
						</div>

						<div>
							<h3><?php esc_html_e( 'Narrow slider', 'sawerna' ); ?></h3>
							<div id="sawerna-home-narrow-slides-root" class="sawerna-home-admin__slides" data-home-slides-root="1">
								<?php foreach ( $options['hero_narrow_slides'] as $index => $slide ) : ?>
									<?php sawerna_homepage_render_narrow_hero_slide_fields( $slide, $index ); ?>
								<?php endforeach; ?>
							</div>
							<p class="sawerna-home-admin__footer">
								<button type="button" class="button button-primary" data-home-slide-add="1" data-home-slides-root-target="sawerna-home-narrow-slides-root" data-home-template-id="sawerna-home-narrow-slide-template"><?php esc_html_e( 'Add narrow slide', 'sawerna' ); ?></button>
							</p>
						</div>
					</div>

					<template id="sawerna-home-wide-slide-template">
						<?php sawerna_homepage_render_wide_hero_slide_fields( sawerna_homepage_wide_slide_defaults(), '__INDEX__' ); ?>
					</template>

					<template id="sawerna-home-narrow-slide-template">
						<?php sawerna_homepage_render_narrow_hero_slide_fields( sawerna_homepage_narrow_slide_defaults(), '__INDEX__' ); ?>
					</template>
				</section>

				<section class="sawerna-home-options__card">
					<h2><?php esc_html_e( 'Homepage categories', 'sawerna' ); ?></h2>
					<p class="sawerna-home-options__field">
						<label for="category_heading"><?php esc_html_e( 'Section title', 'sawerna' ); ?></label>
						<input id="category_heading" class="regular-text widefat" type="text" name="category_heading" value="<?php echo esc_attr( (string) $options['category_heading'] ); ?>">
					</p>
					<div class="sawerna-home-admin__columns">
						<p class="sawerna-home-options__field">
							<label for="category_more_label"><?php esc_html_e( 'Show more label', 'sawerna' ); ?></label>
							<input id="category_more_label" class="regular-text widefat" type="text" name="category_more_label" value="<?php echo esc_attr( (string) $options['category_more_label'] ); ?>">
						</p>
						<p class="sawerna-home-options__field">
							<label for="category_less_label"><?php esc_html_e( 'Show less label', 'sawerna' ); ?></label>
							<input id="category_less_label" class="regular-text widefat" type="text" name="category_less_label" value="<?php echo esc_attr( (string) $options['category_less_label'] ); ?>">
						</p>
					</div>
				</section>

				<section class="sawerna-home-options__card">
					<h2><?php esc_html_e( 'General', 'sawerna' ); ?></h2>
					<p class="sawerna-home-options__field">
						<label for="products_archive_url"><?php esc_html_e( 'Products archive URL', 'sawerna' ); ?></label>
						<input id="products_archive_url" class="regular-text widefat" type="url" name="products_archive_url" value="<?php echo esc_attr( (string) $options['products_archive_url'] ); ?>">
						<span class="sawerna-home-options__hint"><?php esc_html_e( 'Used by the “Näita rohkem” links in homepage product sections.', 'sawerna' ); ?></span>
					</p>
				</section>

				<section class="sawerna-home-options__card">
					<h2><?php esc_html_e( 'New Products In Sawerna', 'sawerna' ); ?></h2>
					<p class="sawerna-home-options__field">
						<label class="sawerna-home-options__check" for="new_products_use_latest">
							<input id="new_products_use_latest" type="checkbox" name="new_products_use_latest" value="1" <?php checked( ! empty( $options['new_products_use_latest'] ) ); ?>>
							<span><?php esc_html_e( 'Use the latest added products automatically', 'sawerna' ); ?></span>
						</label>
					</p>
					<div class="sawerna-home-options__field">
						<label for="new_products_ids"><?php esc_html_e( 'Manual products', 'sawerna' ); ?></label>
						<?php sawerna_homepage_render_product_search_field( 'new_products_ids', 'new_products_ids', $options['new_products_ids'] ); ?>
						<p class="sawerna-home-options__hint"><?php esc_html_e( 'Used when the automatic latest-products option is turned off. The first 12 selected products are shown, scrollable once there are more than 4.', 'sawerna' ); ?></p>
					</div>
				</section>

				<section class="sawerna-home-options__card">
					<h2><?php esc_html_e( 'Sawerna Soovitab Tabs', 'sawerna' ); ?></h2>
					<div class="sawerna-home-options__field">
						<label for="recommendation_category_ids"><?php esc_html_e( 'Recommendation categories', 'sawerna' ); ?></label>
						<select id="recommendation_category_ids" class="wc-enhanced-select" name="recommendation_category_ids[]" multiple="multiple" style="width: 100%;" data-placeholder="<?php esc_attr_e( 'Choose categories…', 'sawerna' ); ?>">
							<?php if ( ! is_wp_error( $categories ) ) : ?>
								<?php foreach ( $categories as $category ) : ?>
									<option value="<?php echo esc_attr( (string) $category->term_id ); ?>" <?php selected( in_array( (int) $category->term_id, $options['recommendation_category_ids'], true ) ); ?>>
										<?php echo esc_html( $category->name ); ?>
									</option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
						<p class="sawerna-home-options__hint"><?php esc_html_e( 'These categories become the tabs. Each tab shows the latest four products from that category.', 'sawerna' ); ?></p>
					</div>
				</section>

				<section class="sawerna-home-options__card">
					<h2><?php esc_html_e( 'About Sawerna', 'sawerna' ); ?></h2>
					<p class="sawerna-home-options__field">
						<label for="about_title"><?php esc_html_e( 'Title', 'sawerna' ); ?></label>
						<input id="about_title" class="regular-text widefat" type="text" name="about_title" value="<?php echo esc_attr( (string) $options['about_title'] ); ?>">
					</p>
					<p class="sawerna-home-options__field">
						<label for="about_subtitle"><?php esc_html_e( 'Subtitle', 'sawerna' ); ?></label>
						<input id="about_subtitle" class="regular-text widefat" type="text" name="about_subtitle" value="<?php echo esc_attr( (string) $options['about_subtitle'] ); ?>">
					</p>
					<p class="sawerna-home-options__field">
						<label for="about_text"><?php esc_html_e( 'Text', 'sawerna' ); ?></label>
						<textarea id="about_text" class="widefat" rows="6" name="about_text"><?php echo esc_textarea( (string) $options['about_text'] ); ?></textarea>
					</p>
					<p class="sawerna-home-options__field">
						<label for="about_button_text"><?php esc_html_e( 'Button text', 'sawerna' ); ?></label>
						<input id="about_button_text" class="regular-text widefat" type="text" name="about_button_text" value="<?php echo esc_attr( (string) $options['about_button_text'] ); ?>">
					</p>
					<p class="sawerna-home-options__field">
						<label for="about_button_url"><?php esc_html_e( 'Button URL', 'sawerna' ); ?></label>
						<input id="about_button_url" class="regular-text widefat" type="url" name="about_button_url" value="<?php echo esc_attr( (string) $options['about_button_url'] ); ?>">
					</p>
				</section>

				<section class="sawerna-home-options__card">
					<h2><?php esc_html_e( 'Sawerna oma tehase tooted', 'sawerna' ); ?></h2>
					<div class="sawerna-home-options__field">
						<label for="factory_products_ids"><?php esc_html_e( 'Factory products', 'sawerna' ); ?></label>
						<?php sawerna_homepage_render_product_search_field( 'factory_products_ids', 'factory_products_ids', $options['factory_products_ids'] ); ?>
						<p class="sawerna-home-options__hint"><?php esc_html_e( 'Select the products shown in this section.', 'sawerna' ); ?></p>
					</div>
					<p class="sawerna-home-options__field">
						<label for="factory_archive_url"><?php esc_html_e( 'More products URL', 'sawerna' ); ?></label>
						<input id="factory_archive_url" class="regular-text widefat" type="url" name="factory_archive_url" value="<?php echo esc_attr( (string) $options['factory_archive_url'] ); ?>">
					</p>
				</section>

				<section class="sawerna-home-options__card">
					<h2><?php esc_html_e( 'Testimonials', 'sawerna' ); ?></h2>
					<p class="sawerna-home-options__field">
						<label for="testimonials_title"><?php esc_html_e( 'Section title', 'sawerna' ); ?></label>
						<input id="testimonials_title" class="regular-text widefat" type="text" name="testimonials_title" value="<?php echo esc_attr( (string) $options['testimonials_title'] ); ?>">
					</p>
					<p class="sawerna-home-options__hint"><?php esc_html_e( 'Testimonials can be added and edited from the Testimonials menu in WP admin.', 'sawerna' ); ?></p>
				</section>

				<section class="sawerna-home-options__card">
					<h2><?php esc_html_e( 'Sooduskampaania', 'sawerna' ); ?></h2>
					<p class="sawerna-home-options__field">
						<label class="sawerna-home-options__check" for="sale_products_use_random">
							<input id="sale_products_use_random" type="checkbox" name="sale_products_use_random" value="1" <?php checked( ! empty( $options['sale_products_use_random'] ) ); ?>>
							<span><?php esc_html_e( 'Use random sale products automatically', 'sawerna' ); ?></span>
						</label>
					</p>
					<div class="sawerna-home-options__field">
						<label for="sale_products_ids"><?php esc_html_e( 'Manual sale products', 'sawerna' ); ?></label>
						<?php sawerna_homepage_render_product_search_field( 'sale_products_ids', 'sale_products_ids', $options['sale_products_ids'] ); ?>
						<p class="sawerna-home-options__hint"><?php esc_html_e( 'Used when the random sale-products option is turned off. The first 12 selected products are shown, scrollable once there are more than 4.', 'sawerna' ); ?></p>
					</div>
					<p class="sawerna-home-options__field">
						<label for="sale_archive_url"><?php esc_html_e( 'Sale section URL', 'sawerna' ); ?></label>
						<input id="sale_archive_url" class="regular-text widefat" type="url" name="sale_archive_url" value="<?php echo esc_attr( (string) $options['sale_archive_url'] ); ?>">
					</p>
				</section>

				<section class="sawerna-home-options__card">
					<h2><?php esc_html_e( 'Klientide poolt enim ostetud', 'sawerna' ); ?></h2>
					<p class="sawerna-home-options__field">
						<label class="sawerna-home-options__check" for="best_sellers_use_automatic">
							<input id="best_sellers_use_automatic" type="checkbox" name="best_sellers_use_automatic" value="1" <?php checked( ! empty( $options['best_sellers_use_automatic'] ) ); ?>>
							<span><?php esc_html_e( 'Use best-selling products automatically', 'sawerna' ); ?></span>
						</label>
					</p>
					<div class="sawerna-home-options__field">
						<label for="best_sellers_products_ids"><?php esc_html_e( 'Manual products', 'sawerna' ); ?></label>
						<?php sawerna_homepage_render_product_search_field( 'best_sellers_products_ids', 'best_sellers_products_ids', $options['best_sellers_products_ids'] ); ?>
						<p class="sawerna-home-options__hint"><?php esc_html_e( 'Used when the automatic best-sellers option is turned off. The first 12 selected products are shown, scrollable once there are more than 4.', 'sawerna' ); ?></p>
					</div>
					<p class="sawerna-home-options__field">
						<label for="best_sellers_archive_url"><?php esc_html_e( 'Best sellers section URL', 'sawerna' ); ?></label>
						<input id="best_sellers_archive_url" class="regular-text widefat" type="url" name="best_sellers_archive_url" value="<?php echo esc_attr( (string) $options['best_sellers_archive_url'] ); ?>">
					</p>
				</section>

				<section class="sawerna-home-options__card">
					<h2><?php esc_html_e( 'Nõuanded ja blogi', 'sawerna' ); ?></h2>
					<p class="sawerna-home-options__field">
						<label for="blog_title"><?php esc_html_e( 'Section title', 'sawerna' ); ?></label>
						<input id="blog_title" class="regular-text widefat" type="text" name="blog_title" value="<?php echo esc_attr( (string) $options['blog_title'] ); ?>">
					</p>
					<p class="sawerna-home-options__field">
						<label for="blog_archive_url"><?php esc_html_e( 'Blog archive URL', 'sawerna' ); ?></label>
						<input id="blog_archive_url" class="regular-text widefat" type="url" name="blog_archive_url" value="<?php echo esc_attr( (string) $options['blog_archive_url'] ); ?>">
					</p>
				</section>
			</div>

			<?php submit_button( __( 'Save Page Options', 'sawerna' ) ); ?>
		</form>
	</div>
	<?php
}

/**
 * Get homepage section products.
 *
 * @param array<string, mixed> $options Homepage options.
 * @return array<int, WC_Product>
 */
function sawerna_get_homepage_new_products( array $options ) {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return array();
	}

	$product_ids = ! empty( $options['new_products_use_latest'] ) ? array() : $options['new_products_ids'];

	if ( ! empty( $product_ids ) ) {
		return wc_get_products(
			array(
				'status'  => 'publish',
				'include' => array_slice( $product_ids, 0, 12 ),
				'limit'   => 12,
				'orderby' => 'post__in',
			)
		);
	}

	return wc_get_products(
		array(
			'status'  => 'publish',
			'limit'   => 12,
			'orderby' => 'date',
			'order'   => 'DESC',
		)
	);
}

/**
 * Get latest products by category term.
 *
 * @param int $term_id Category term ID.
 * @return array<int, WC_Product>
 */
function sawerna_get_homepage_products_by_category( $term_id ) {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return array();
	}

	$term = get_term( $term_id, 'product_cat' );

	if ( ! $term || is_wp_error( $term ) ) {
		return array();
	}

	return wc_get_products(
		array(
			'status'   => 'publish',
			'limit'    => 12,
			'orderby'  => 'date',
			'order'    => 'DESC',
			'category' => array( $term->slug ),
		)
	);
}

/**
 * Get homepage recommendation tabs.
 *
 * @param array<string, mixed> $options Homepage options.
 * @return array<int, array{term:WP_Term,products:array<int,WC_Product>}>
 */
function sawerna_get_homepage_recommendation_tabs( array $options ) {
	$tabs     = array();
	$term_ids = array_slice( $options['recommendation_category_ids'], 0, 8 );

	if ( empty( $term_ids ) && taxonomy_exists( 'product_cat' ) ) {
		$fallback_terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
				'parent'     => 0,
				'orderby'    => 'menu_order',
				'order'      => 'ASC',
				'number'     => 4,
			)
		);

		if ( is_array( $fallback_terms ) ) {
			$term_ids = array_map(
				static function ( $term ) {
					return (int) $term->term_id;
				},
				$fallback_terms
			);
		}
	}

	foreach ( $term_ids as $term_id ) {
		$term = get_term( $term_id, 'product_cat' );

		if ( ! $term || is_wp_error( $term ) ) {
			continue;
		}

		$products = sawerna_get_homepage_products_by_category( $term->term_id );

		if ( empty( $products ) ) {
			continue;
		}

		$tabs[] = array(
			'term'     => $term,
			'products' => $products,
		);
	}

	return $tabs;
}

/**
 * Get homepage factory products.
 *
 * @param array<string, mixed> $options Homepage options.
 * @return array<int, WC_Product>
 */
function sawerna_get_homepage_factory_products( array $options ) {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return array();
	}

	$product_ids = array_slice( $options['factory_products_ids'], 0, 12 );

	if ( empty( $product_ids ) ) {
		return array();
	}

	return wc_get_products(
		array(
			'status'  => 'publish',
			'include' => $product_ids,
			'limit'   => 12,
			'orderby' => 'post__in',
		)
	);
}

/**
 * Get homepage sale products.
 *
 * @param array<string, mixed> $options Homepage options.
 * @return array<int, WC_Product>
 */
function sawerna_get_homepage_sale_products( array $options ) {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return array();
	}

	$product_ids = array();

	if ( ! empty( $options['sale_products_use_random'] ) ) {
		$product_ids = wc_get_product_ids_on_sale();

		if ( empty( $product_ids ) ) {
			return array();
		}

		shuffle( $product_ids );
		$product_ids = array_slice( $product_ids, 0, 12 );
	} else {
		$product_ids = array_slice( $options['sale_products_ids'], 0, 12 );
	}

	if ( empty( $product_ids ) ) {
		return array();
	}

	return wc_get_products(
		array(
			'status'  => 'publish',
			'include' => $product_ids,
			'limit'   => 12,
			'orderby' => 'post__in',
		)
	);
}

/**
 * Get homepage best sellers.
 *
 * @param array<string, mixed> $options Homepage options.
 * @return array<int, WC_Product>
 */
function sawerna_get_homepage_best_sellers( array $options ) {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return array();
	}

	if ( empty( $options['best_sellers_use_automatic'] ) ) {
		$product_ids = array_slice( $options['best_sellers_products_ids'], 0, 12 );

		if ( empty( $product_ids ) ) {
			return array();
		}

		return wc_get_products(
			array(
				'status'  => 'publish',
				'include' => $product_ids,
				'limit'   => 12,
				'orderby' => 'post__in',
			)
		);
	}

	return wc_get_products(
		array(
			'status'   => 'publish',
			'limit'    => 12,
			'orderby'  => 'meta_value_num',
			'meta_key' => 'total_sales', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Bestseller fallback needs total_sales ordering; limit 12.
			'order'    => 'DESC',
		)
	);
}

/**
 * Render a homepage product slider.
 *
 * @param array<int, WC_Product> $products Products to display.
 * @return void
 */
function sawerna_homepage_render_products_slider( array $products ) {
	if ( empty( $products ) || ! function_exists( 'sawerna_get_product_card_html' ) ) {
		return;
	}
	?>
	<div class="sawerna-home-products__slider" data-home-products-slider="1">
		<div class="sawerna-home-products__viewport">
			<div class="sawerna-home-products__grid" data-home-products-track="1">
				<?php foreach ( array_slice( $products, 0, 12 ) as $product ) : ?>
					<div class="sawerna-home-products__item">
						<?php echo sawerna_get_product_card_html( $product, 'loop' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="sawerna-home-products__nav" data-home-products-nav="1" hidden>
			<button type="button" class="sawerna-home-products__arrow" data-home-products-prev="1" aria-label="<?php esc_attr_e( 'Previous products', 'sawerna' ); ?>">
				<?php echo sawerna_get_svg_inline( 'Asset 9.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
			<button type="button" class="sawerna-home-products__arrow is-next" data-home-products-next="1" aria-label="<?php esc_attr_e( 'Next products', 'sawerna' ); ?>">
				<?php echo sawerna_get_svg_inline( 'Asset 9.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
		</div>
	</div>
	<?php
}

/**
 * Get homepage blog posts.
 *
 * @return array<int, WP_Post>
 */
function sawerna_get_homepage_blog_posts() {
	return get_posts(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => 3,
			'ignore_sticky_posts' => true,
		)
	);
}

/**
 * Get homepage testimonials.
 *
 * @return array<int, WP_Post>
 */
function sawerna_get_homepage_testimonials() {
	return get_posts(
		array(
			'post_type'      => 'sawerna_testimonial',
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
			'posts_per_page' => -1,
		)
	);
}

/**
 * Get homepage category image HTML.
 *
 * @param WP_Term $term Category term.
 * @return string
 */
function sawerna_get_homepage_category_image_html( WP_Term $term ) {
	$thumbnail_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );

	if ( $thumbnail_id <= 0 ) {
		return function_exists( 'wc_placeholder_img' ) ? wp_kses_post( wc_placeholder_img( 'woocommerce_thumbnail' ) ) : '';
	}

	return wp_get_attachment_image(
		$thumbnail_id,
		'sawerna-home-category',
		false,
		array(
			'loading'  => 'lazy',
			'decoding' => 'async',
			'sizes'    => '(max-width: 767px) 50vw, (max-width: 1024px) 33vw, 276px',
		)
	);
}
