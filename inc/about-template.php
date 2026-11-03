<?php
/**
 * About page template helpers.
 *
 * @package Sawerna
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function sawerna_about_template_slug() {
	return 'about-page.php';
}

function sawerna_about_options_defaults() {
	return array(
		'hero_image_id'          => 0,
		'hero_accent_image_id'   => 0,
		'hero_eyebrow'           => __( 'MEIST', 'sawerna' ),
		'hero_title'             => __( 'Uus bränd, pikk ajalugu - perefirma eelmise sajandi keskpaigast', 'sawerna' ),
		'hero_text'              => __( 'Sawerna aitab sul teha õiged valikud nii vormi, funktsiooni kui ka materjalide osas.', 'sawerna' ),
		'content_quote_text'     => __( 'Mulle väga meeldib see, mida me teeme. Ma kohe tunnen, et me ajame õiget asja ja senine tagasiside annab ainult hoogu juurde!', 'sawerna' ),
		'content_quote_author'   => __( 'Carl', 'sawerna' ),
		'owner_card_text'        => __( 'Me ei tahtnud lihtsalt midagi müüa müümise pärast. Tahtsime luua midagi, mille eest võib ka päriselt vastutada. Nii toodete kui ka suhtumise mõttes.', 'sawerna' ),
		'owner_name'             => __( 'Carl-Johannes Kinnunen', 'sawerna' ),
		'owner_title'            => __( 'Sawerna asutaja', 'sawerna' ),
		'owner_image_id'         => 0,
	);
}

function sawerna_about_wpml_register_string( $name, $value ) {
	if ( ! is_string( $value ) || '' === trim( $value ) || ! has_action( 'wpml_register_single_string' ) ) {
		return;
	}

	do_action( 'wpml_register_single_string', 'sawerna-page-options', (string) $name, $value );
}

function sawerna_about_wpml_translate_string( $name, $value ) {
	if ( ! is_string( $value ) || '' === trim( $value ) || ! has_filter( 'wpml_translate_single_string' ) ) {
		return (string) $value;
	}

	return (string) apply_filters( 'wpml_translate_single_string', $value, 'sawerna-page-options', (string) $name );
}

function sawerna_about_sanitize_options( $options ) {
	$options  = is_array( $options ) ? $options : array();
	$defaults = sawerna_about_options_defaults();

	return array(
		'hero_image_id'        => absint( $options['hero_image_id'] ?? $defaults['hero_image_id'] ),
		'hero_accent_image_id' => absint( $options['hero_accent_image_id'] ?? $defaults['hero_accent_image_id'] ),
		'hero_eyebrow'         => sanitize_text_field( $options['hero_eyebrow'] ?? $defaults['hero_eyebrow'] ),
		'hero_title'           => sanitize_text_field( $options['hero_title'] ?? $defaults['hero_title'] ),
		'hero_text'            => sanitize_textarea_field( $options['hero_text'] ?? $defaults['hero_text'] ),
		'content_quote_text'   => sanitize_textarea_field( $options['content_quote_text'] ?? $defaults['content_quote_text'] ),
		'content_quote_author' => sanitize_text_field( $options['content_quote_author'] ?? $defaults['content_quote_author'] ),
		'owner_card_text'      => sanitize_textarea_field( $options['owner_card_text'] ?? $defaults['owner_card_text'] ),
		'owner_name'           => sanitize_text_field( $options['owner_name'] ?? $defaults['owner_name'] ),
		'owner_title'          => sanitize_text_field( $options['owner_title'] ?? $defaults['owner_title'] ),
		'owner_image_id'       => absint( $options['owner_image_id'] ?? $defaults['owner_image_id'] ),
	);
}

function sawerna_about_register_wpml_option_strings( array $options ) {
	foreach ( array( 'hero_eyebrow', 'hero_title', 'hero_text', 'content_quote_text', 'content_quote_author', 'owner_card_text', 'owner_name', 'owner_title' ) as $field ) {
		sawerna_about_wpml_register_string( 'about_' . $field, (string) ( $options[ $field ] ?? '' ) );
	}
}

function sawerna_about_translate_wpml_option_strings( array $options ) {
	foreach ( array( 'hero_eyebrow', 'hero_title', 'hero_text', 'content_quote_text', 'content_quote_author', 'owner_card_text', 'owner_name', 'owner_title' ) as $field ) {
		$options[ $field ] = sawerna_about_wpml_translate_string( 'about_' . $field, (string) ( $options[ $field ] ?? '' ) );
	}

	return $options;
}

function sawerna_get_about_options() {
	$stored   = get_option( 'sawerna_about_options', array() );
	$defaults = sawerna_about_options_defaults();
	$options  = sawerna_about_sanitize_options( wp_parse_args( is_array( $stored ) ? $stored : array(), $defaults ) );

	return sawerna_about_translate_wpml_option_strings( $options );
}

function sawerna_about_split_content_before_last_paragraph( $content ) {
	$content = (string) $content;

	if ( '' === trim( $content ) ) {
		return array( '', '' );
	}

	if ( ! preg_match_all( '/<p\b[^>]*>.*?<\/p>/is', $content, $matches, PREG_OFFSET_CAPTURE ) || empty( $matches[0] ) ) {
		return array( $content, '' );
	}

	$last_paragraph = end( $matches[0] );
	$offset         = isset( $last_paragraph[1] ) ? (int) $last_paragraph[1] : 0;

	if ( $offset <= 0 ) {
		return array( $content, '' );
	}

	return array(
		substr( $content, 0, $offset ),
		substr( $content, $offset ),
	);
}

function sawerna_about_render_story_quote( array $options ) {
	if ( '' === trim( (string) ( $options['content_quote_text'] ?? '' ) ) ) {
		return;
	}
	?>
	<blockquote class="sawerna-about-quote">
		<p><?php echo nl2br( esc_html( (string) $options['content_quote_text'] ) ); ?></p>
		<?php if ( '' !== trim( (string) ( $options['content_quote_author'] ?? '' ) ) ) : ?>
			<cite><?php echo esc_html( sprintf( __( '- %s', 'sawerna' ), (string) $options['content_quote_author'] ) ); ?></cite>
		<?php endif; ?>
	</blockquote>
	<?php
}

function sawerna_about_enqueue_assets() {
	if ( ! is_page_template( sawerna_about_template_slug() ) ) {
		return;
	}

	$theme_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( get_template_directory() . '/style.css' ) : _S_VERSION;
	$css_path      = get_template_directory() . '/assets/css/about.css';

	if ( file_exists( $css_path ) ) {
		$css_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $css_path ) : $theme_version;
		wp_enqueue_style( 'sawerna-about', get_template_directory_uri() . '/assets/css/about.css', array( 'sawerna-style' ), $css_version );
	}
}
add_action( 'wp_enqueue_scripts', 'sawerna_about_enqueue_assets' );

function sawerna_about_register_admin_menu() {
	add_submenu_page(
		'sawerna-page-options',
		__( 'About Us', 'sawerna' ),
		__( 'About Us', 'sawerna' ),
		'manage_options',
		'sawerna-about-options',
		'sawerna_about_render_admin_page'
	);
}
add_action( 'admin_menu', 'sawerna_about_register_admin_menu' );

function sawerna_about_options_admin_assets( $hook_suffix ) {
	if ( false === strpos( (string) $hook_suffix, 'sawerna-about-options' ) ) {
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
add_action( 'admin_enqueue_scripts', 'sawerna_about_options_admin_assets' );

function sawerna_about_handle_admin_save() {
	if ( ! is_admin() || ! isset( $_POST['sawerna_about_options_nonce'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sawerna_about_options_nonce'] ) ), 'sawerna_about_options_save' ) ) {
		return;
	}

	$options = sawerna_about_sanitize_options(
		array(
			'hero_image_id'        => isset( $_POST['hero_image_id'] ) ? wp_unslash( $_POST['hero_image_id'] ) : 0,
			'hero_accent_image_id' => isset( $_POST['hero_accent_image_id'] ) ? wp_unslash( $_POST['hero_accent_image_id'] ) : 0,
			'hero_eyebrow'         => isset( $_POST['hero_eyebrow'] ) ? wp_unslash( $_POST['hero_eyebrow'] ) : '',
			'hero_title'           => isset( $_POST['hero_title'] ) ? wp_unslash( $_POST['hero_title'] ) : '',
			'hero_text'            => isset( $_POST['hero_text'] ) ? wp_unslash( $_POST['hero_text'] ) : '',
			'content_quote_text'   => isset( $_POST['content_quote_text'] ) ? wp_unslash( $_POST['content_quote_text'] ) : '',
			'content_quote_author' => isset( $_POST['content_quote_author'] ) ? wp_unslash( $_POST['content_quote_author'] ) : '',
			'owner_card_text'      => isset( $_POST['owner_card_text'] ) ? wp_unslash( $_POST['owner_card_text'] ) : '',
			'owner_name'           => isset( $_POST['owner_name'] ) ? wp_unslash( $_POST['owner_name'] ) : '',
			'owner_title'          => isset( $_POST['owner_title'] ) ? wp_unslash( $_POST['owner_title'] ) : '',
			'owner_image_id'       => isset( $_POST['owner_image_id'] ) ? wp_unslash( $_POST['owner_image_id'] ) : 0,
		)
	);

	sawerna_about_register_wpml_option_strings( $options );
	update_option( 'sawerna_about_options', $options );

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'    => 'sawerna-about-options',
				'updated' => '1',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_init', 'sawerna_about_handle_admin_save' );

function sawerna_about_render_text_field( $id, $label, $value, $type = 'text' ) {
	?>
	<p class="sawerna-home-options__field">
		<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
		<input id="<?php echo esc_attr( $id ); ?>" class="regular-text widefat" type="<?php echo esc_attr( $type ); ?>" name="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( (string) $value ); ?>">
	</p>
	<?php
}

function sawerna_about_render_textarea_field( $id, $label, $value, $rows = 4 ) {
	?>
	<p class="sawerna-home-options__field">
		<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
		<textarea id="<?php echo esc_attr( $id ); ?>" class="widefat" rows="<?php echo esc_attr( (string) $rows ); ?>" name="<?php echo esc_attr( $id ); ?>"><?php echo esc_textarea( (string) $value ); ?></textarea>
	</p>
	<?php
}

function sawerna_about_render_admin_page() {
	$options = sawerna_get_about_options();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'About Us page options', 'sawerna' ); ?></h1>

		<?php if ( isset( $_GET['updated'] ) ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'About Us page options saved.', 'sawerna' ); ?></p></div>
		<?php endif; ?>

		<style>
			.sawerna-home-options__grid { display:grid; gap:24px; max-width:1100px; }
			.sawerna-home-options__card { background:#fff; border:1px solid #dcdcde; border-radius:12px; padding:24px; }
			.sawerna-home-options__card h2 { margin:0 0 12px; font-size:20px; }
			.sawerna-home-options__field { margin:0 0 18px; }
			.sawerna-home-options__field label { display:block; margin:0 0 8px; font-weight:600; }
			.sawerna-home-options__hint { margin:6px 0 18px; color:#646970; }
			.sawerna-home-admin__field { margin: 0 0 16px; }
			.sawerna-home-admin__field label { display: block; margin: 0 0 8px; font-weight: 600; }
			.sawerna-home-admin__image-preview { margin-bottom: 10px; }
			.sawerna-home-admin__image-preview img { display: block; width: 100%; max-width: 240px; border-radius: 8px; height: auto; }
			.sawerna-home-admin__image-actions { display: flex; flex-wrap: wrap; gap: 8px; }
		</style>

		<form method="post" action="">
			<?php wp_nonce_field( 'sawerna_about_options_save', 'sawerna_about_options_nonce' ); ?>

			<div class="sawerna-home-options__grid">
				<section class="sawerna-home-options__card">
					<h2><?php esc_html_e( 'About hero', 'sawerna' ); ?></h2>
					<p class="sawerna-home-options__hint"><?php esc_html_e( 'The hero text and decorative image are shown on top of the hero background.', 'sawerna' ); ?></p>
					<?php sawerna_homepage_render_image_field( 'hero_image_id', (int) $options['hero_image_id'], __( 'Hero background image', 'sawerna' ) ); ?>
					<?php sawerna_homepage_render_image_field( 'hero_accent_image_id', (int) $options['hero_accent_image_id'], __( 'Small image below hero text', 'sawerna' ) ); ?>
					<?php sawerna_about_render_text_field( 'hero_eyebrow', __( 'Small label', 'sawerna' ), $options['hero_eyebrow'] ); ?>
					<?php sawerna_about_render_text_field( 'hero_title', __( 'Hero title', 'sawerna' ), $options['hero_title'] ); ?>
					<?php sawerna_about_render_textarea_field( 'hero_text', __( 'Hero subtext', 'sawerna' ), $options['hero_text'], 4 ); ?>
				</section>

				<section class="sawerna-home-options__card">
					<h2><?php esc_html_e( 'Story quote', 'sawerna' ); ?></h2>
					<p class="sawerna-home-options__hint"><?php esc_html_e( 'This quote is displayed with the regular page content.', 'sawerna' ); ?></p>
					<?php sawerna_about_render_textarea_field( 'content_quote_text', __( 'Quote text', 'sawerna' ), $options['content_quote_text'], 4 ); ?>
					<?php sawerna_about_render_text_field( 'content_quote_author', __( 'Quote author', 'sawerna' ), $options['content_quote_author'] ); ?>
				</section>

				<section class="sawerna-home-options__card">
					<h2><?php esc_html_e( 'Owner testimonial card', 'sawerna' ); ?></h2>
					<p class="sawerna-home-options__hint"><?php esc_html_e( 'This card sits in the right column and overlaps the hero area on desktop.', 'sawerna' ); ?></p>
					<?php sawerna_homepage_render_image_field( 'owner_image_id', (int) $options['owner_image_id'], __( 'Owner image', 'sawerna' ) ); ?>
					<?php sawerna_about_render_textarea_field( 'owner_card_text', __( 'Card text', 'sawerna' ), $options['owner_card_text'], 4 ); ?>
					<?php sawerna_about_render_text_field( 'owner_name', __( 'Owner name', 'sawerna' ), $options['owner_name'] ); ?>
					<?php sawerna_about_render_text_field( 'owner_title', __( 'Owner title', 'sawerna' ), $options['owner_title'] ); ?>
				</section>
			</div>

			<?php submit_button( __( 'Save About Us Options', 'sawerna' ) ); ?>
		</form>
	</div>
	<?php
}
