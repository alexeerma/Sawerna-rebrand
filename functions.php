<?php
/**
 * Sawerna functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Sawerna
 */

if ( ! defined( '_S_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	define( '_S_VERSION', '1.2.0' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- _s starter constant used across theme; renaming would break references.
}

/**
 * Check whether Sawerna Core is active.
 *
 * @return bool
 */
function sawerna_is_core_active() {
	return defined( 'SAWERNA_CORE_VERSION' );
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function sawerna_setup() {
	/*
		* Make theme available for translation.
		* Translations can be filed in the /languages/ directory.
		* If you're building a theme based on Sawerna, use a find and replace
		* to change 'sawerna' to the name of your theme in all the template files.
		*/
	load_theme_textdomain( 'sawerna', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
		* Let WordPress manage the document title.
		* By adding theme support, we declare that this theme does not use a
		* hard-coded <title> tag in the document head, and expect WordPress to
		* provide it for us.
		*/
	add_theme_support( 'title-tag' );

	/*
		* Enable support for Post Thumbnails on posts and pages.
		*
		* @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		*/
	add_theme_support( 'post-thumbnails' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus(
		array(
			'menu-1'                         => esc_html__( 'Primary', 'sawerna' ),
			'header_left'                    => __( 'Header Left Menu', 'sawerna' ),
			'header_right'                   => __( 'Header Right Menu', 'sawerna' ),
			'header_top_bar'                 => __( 'Top Bar Menu', 'sawerna' ),
			'header_mega_menu'               => __( 'Mega Menu (Product Catalog)', 'sawerna' ),
			'header_mobile_overlay_language' => __( 'Mobile Overlay Language Menu', 'sawerna' ),
			'header_mobile_overlay_bottom'   => __( 'Mobile Overlay Bottom Menu', 'sawerna' ),
			'footer_menu'                    => __( 'Footer Menu', 'sawerna' ),
			'footer_general_info'            => __( 'Footer General Info Menu', 'sawerna' ),
			'footer_client_info'             => __( 'Footer Client Info Menu', 'sawerna' ),
			'footer_services_info'           => __( 'Footer Services Info Menu', 'sawerna' ),
		)
	);

	/*
		* Switch default core markup for search form, comment form, and comments
		* to output valid HTML5.
		*/
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Set up the WordPress core custom background feature.
	add_theme_support(
		'custom-background',
		apply_filters(
			'sawerna_custom_background_args',
			array(
				'default-color' => 'ffffff',
				'default-image' => '',
			)
		)
	);

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	/**
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);

	// Homepage category cards use a sharper square crop than WooCommerce's default thumbnail.
	add_image_size( 'sawerna-home-category', 600, 600, true );
}
add_action( 'after_setup_theme', 'sawerna_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function sawerna_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'sawerna_content_width', 640 );
}
add_action( 'after_setup_theme', 'sawerna_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function sawerna_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'sawerna' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'sawerna' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar(
		array(
			'name'          => esc_html__( 'Shop Sidebar', 'sawerna' ),
			'id'            => 'shop-sidebar',
			'description'   => esc_html__( 'Widgets for WooCommerce product filters.', 'sawerna' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'sawerna_widgets_init' );

/**
 * Enqueue scripts and styles with performance optimizations.
 */
function sawerna_scripts() {
	// Get file modification time for cache busting in development
	$theme_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( get_template_directory() . '/style.css' ) : _S_VERSION;

	// Fonts CSS (load first for better performance)
	$fonts_css_path = get_template_directory() . '/assets/css/fonts.css';
	if ( file_exists( $fonts_css_path ) ) {
		$fonts_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $fonts_css_path ) : $theme_version;
		wp_enqueue_style( 'sawerna-fonts', get_template_directory_uri() . '/assets/css/fonts.css', array(), $fonts_version );
	}

	// Main stylesheet
	wp_enqueue_style( 'sawerna-style', get_stylesheet_uri(), array( 'sawerna-fonts' ), $theme_version );
	wp_style_add_data( 'sawerna-style', 'rtl', 'replace' );

	// Header styles (desktop)
	$header_desktop_css_path = get_template_directory() . '/assets/css/header-desktop.css';
	if ( file_exists( $header_desktop_css_path ) ) {
		$header_desktop_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $header_desktop_css_path ) : $theme_version;
		wp_enqueue_style( 'sawerna-header-desktop', get_template_directory_uri() . '/assets/css/header-desktop.css', array( 'sawerna-style' ), $header_desktop_version );
	}

	// Content components (menu products, banner, image grid, image with text, blog cards), used in the mega menu on every page.
	$components_css_path = get_template_directory() . '/assets/css/sawerna-components.css';
	if ( file_exists( $components_css_path ) ) {
		$components_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $components_css_path ) : $theme_version;
		wp_enqueue_style( 'sawerna-components', get_template_directory_uri() . '/assets/css/sawerna-components.css', array( 'sawerna-style' ), $components_version );
	}

	// Legacy header styles (for backward compatibility)
	$header_css_path = get_template_directory() . '/assets/css/header.css';
	if ( file_exists( $header_css_path ) ) {
		$header_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $header_css_path ) : $theme_version;
		wp_enqueue_style( 'sawerna-header', get_template_directory_uri() . '/assets/css/header.css', array( 'sawerna-style' ), $header_version );
	}

	// Footer styles
	$footer_css_path = get_template_directory() . '/assets/css/footer.css';
	if ( file_exists( $footer_css_path ) ) {
		$footer_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $footer_css_path ) : $theme_version;
		wp_enqueue_style( 'sawerna-footer', get_template_directory_uri() . '/assets/css/footer.css', array( 'sawerna-style' ), $footer_version );
	}

	// HTML Blocks styles
	$html_blocks_css_path = get_template_directory() . '/assets/css/html-blocks.css';
	if ( file_exists( $html_blocks_css_path ) ) {
		$html_blocks_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $html_blocks_css_path ) : $theme_version;
		wp_enqueue_style( 'sawerna-html-blocks', get_template_directory_uri() . '/assets/css/html-blocks.css', array( 'sawerna-style' ), $html_blocks_version );
	}

	// Page styles
	$page_css_path = get_template_directory() . '/assets/css/page.css';
	if ( is_page() && ! is_front_page() && file_exists( $page_css_path ) ) {
		$page_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $page_css_path ) : $theme_version;
		wp_enqueue_style( 'sawerna-page', get_template_directory_uri() . '/assets/css/page.css', array( 'sawerna-style' ), $page_version );
	}

	// 404 styles
	$error_404_css_path = get_template_directory() . '/assets/css/error-404.css';
	if ( is_404() && file_exists( $error_404_css_path ) ) {
		$error_404_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $error_404_css_path ) : $theme_version;
		wp_enqueue_style( 'sawerna-error-404', get_template_directory_uri() . '/assets/css/error-404.css', array( 'sawerna-style' ), $error_404_version );
	}

	// Blog archive/list/single styles
	$blog_css_path = get_template_directory() . '/assets/css/blog.css';
	if ( ( is_home() || is_category() || is_tag() || is_author() || is_date() || is_post_type_archive( 'post' ) || is_singular( 'post' ) ) && file_exists( $blog_css_path ) ) {
		$blog_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $blog_css_path ) : $theme_version;
		wp_enqueue_style( 'sawerna-blog', get_template_directory_uri() . '/assets/css/blog.css', array( 'sawerna-style' ), $blog_version );
	}

	// Search results styles
	$search_css_path = get_template_directory() . '/assets/css/search.css';
	if ( is_search() && file_exists( $search_css_path ) ) {
		$search_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $search_css_path ) : $theme_version;
		wp_enqueue_style( 'sawerna-search', get_template_directory_uri() . '/assets/css/search.css', array( 'sawerna-style' ), $search_version );
	}

	// Navigation script - defer for better performance
	$nav_js_path = get_template_directory() . '/js/navigation.js';
	if ( file_exists( $nav_js_path ) ) {
		$nav_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $nav_js_path ) : $theme_version;
		wp_enqueue_script( 'sawerna-navigation', get_template_directory_uri() . '/js/navigation.js', array(), $nav_version, true );
		wp_script_add_data( 'sawerna-navigation', 'defer', true );
	}

	// Header desktop script - defer for better performance
	$header_desktop_js_path = get_template_directory() . '/js/header-desktop.js';
	if ( file_exists( $header_desktop_js_path ) ) {
		$header_desktop_js_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $header_desktop_js_path ) : $theme_version;
		wp_enqueue_script( 'sawerna-header-desktop', get_template_directory_uri() . '/js/header-desktop.js', array(), $header_desktop_js_version, true );
		wp_script_add_data( 'sawerna-header-desktop', 'defer', true );
	}

	// Mobile menu script - defer for better performance
	$mobile_menu_js_path = get_template_directory() . '/js/mobile-menu.js';
	if ( file_exists( $mobile_menu_js_path ) ) {
		$mobile_menu_js_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $mobile_menu_js_path ) : $theme_version;
		wp_enqueue_script( 'sawerna-mobile-menu', get_template_directory_uri() . '/js/mobile-menu.js', array(), $mobile_menu_js_version, true );
		wp_script_add_data( 'sawerna-mobile-menu', 'defer', true );
	}

	// Footer accordion script - defer for better performance
	$footer_accordion_js_path = get_template_directory() . '/js/footer-accordion.js';
	if ( file_exists( $footer_accordion_js_path ) ) {
		$footer_accordion_js_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $footer_accordion_js_path ) : $theme_version;
		wp_enqueue_script( 'sawerna-footer-accordion', get_template_directory_uri() . '/js/footer-accordion.js', array(), $footer_accordion_js_version, true );
		wp_script_add_data( 'sawerna-footer-accordion', 'defer', true );
	}

	// Footer slider script - defer for better performance
	$footer_slider_js_path = get_template_directory() . '/js/footer-slider.js';
	if ( file_exists( $footer_slider_js_path ) ) {
		$footer_slider_js_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $footer_slider_js_path ) : $theme_version;
		wp_enqueue_script( 'sawerna-footer-slider', get_template_directory_uri() . '/js/footer-slider.js', array(), $footer_slider_js_version, true );
		wp_script_add_data( 'sawerna-footer-slider', 'defer', true );
	}

	// Legacy header script (for backward compatibility)
	$header_js_path = get_template_directory() . '/js/header.js';
	if ( file_exists( $header_js_path ) ) {
		$header_js_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $header_js_path ) : $theme_version;
		wp_enqueue_script( 'sawerna-header', get_template_directory_uri() . '/js/header.js', array(), $header_js_version, true );
		wp_script_add_data( 'sawerna-header', 'defer', true );
	}

	// Comment reply script - only load when needed
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'sawerna_scripts' );

/**
 * Get the page assigned as the blog posts page.
 *
 * @return int
 */
function sawerna_get_blog_page_id() {
	return max( 0, (int) get_option( 'page_for_posts', 0 ) );
}

/**
 * Get the main blog archive URL.
 *
 * @return string
 */
function sawerna_get_blog_archive_url() {
	$blog_page_id = sawerna_get_blog_page_id();

	if ( $blog_page_id > 0 ) {
		return (string) get_permalink( $blog_page_id );
	}

	$archive_url = get_post_type_archive_link( 'post' );

	return $archive_url ? (string) $archive_url : (string) home_url( '/blog/' );
}

/**
 * Get the breadcrumb label for the blog archive.
 *
 * @return string
 */
function sawerna_get_blog_archive_label() {
	$blog_page_id = sawerna_get_blog_page_id();

	if ( $blog_page_id > 0 ) {
		$title = get_the_title( $blog_page_id );

		if ( is_string( $title ) && '' !== trim( $title ) ) {
			return $title;
		}
	}

	return __( 'Blogi', 'sawerna' );
}

/**
 * Get the editable heading shown at the top of the blog archive.
 *
 * @return string
 */
function sawerna_get_blog_archive_heading() {
	$blog_page_id = sawerna_get_blog_page_id();

	if ( $blog_page_id > 0 ) {
		$heading = (string) get_post_meta( $blog_page_id, '_sawerna_blog_archive_heading', true );

		if ( '' !== trim( $heading ) ) {
			return $heading;
		}

		$title = get_the_title( $blog_page_id );
		if ( is_string( $title ) && '' !== trim( $title ) ) {
			return $title;
		}
	}

	return __( 'Blogi', 'sawerna' );
}

/**
 * Get blog archive category filters.
 *
 * @return WP_Term[]
 */
function sawerna_get_blog_archive_categories() {
	$categories = get_categories(
		array(
			'taxonomy'   => 'category',
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	return is_array( $categories ) ? $categories : array();
}

/**
 * Register blog archive settings meta box for the posts page.
 *
 * @param WP_Post $post Current post object.
 * @return void
 */
function sawerna_register_blog_archive_metabox( $post ) {
	$blog_page_id = sawerna_get_blog_page_id();

	if ( $blog_page_id < 1 || ! ( $post instanceof WP_Post ) || (int) $post->ID !== $blog_page_id ) {
		return;
	}

	add_meta_box(
		'sawerna-blog-archive-settings',
		__( 'Blog Archive Settings', 'sawerna' ),
		'sawerna_render_blog_archive_metabox',
		'page',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes_page', 'sawerna_register_blog_archive_metabox' );

/**
 * Render blog archive settings meta box.
 *
 * @param WP_Post $post Current post object.
 * @return void
 */
function sawerna_render_blog_archive_metabox( $post ) {
	$blog_page_id = sawerna_get_blog_page_id();

	if ( (int) $post->ID !== $blog_page_id ) {
		return;
	}

	$heading = (string) get_post_meta( $post->ID, '_sawerna_blog_archive_heading', true );

	wp_nonce_field( 'sawerna_blog_archive_settings', 'sawerna_blog_archive_settings_nonce' );
	?>
	<p>
		<label for="sawerna-blog-archive-heading"><strong><?php esc_html_e( 'Archive heading', 'sawerna' ); ?></strong></label>
	</p>
	<p>
		<textarea
			class="widefat"
			rows="3"
			id="sawerna-blog-archive-heading"
			name="sawerna_blog_archive_heading"
		><?php echo esc_textarea( $heading ); ?></textarea>
	</p>
	<p class="description">
		<?php esc_html_e( 'Used as the large heading on the main blog page and category archive views. Leave empty to fall back to the page title.', 'sawerna' ); ?>
	</p>
	<?php
}

/**
 * Save blog archive settings.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function sawerna_save_blog_archive_metabox( $post_id ) {
	$blog_page_id = sawerna_get_blog_page_id();

	if ( $post_id !== $blog_page_id ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_page', $post_id ) ) {
		return;
	}

	$nonce = isset( $_POST['sawerna_blog_archive_settings_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['sawerna_blog_archive_settings_nonce'] ) ) : '';

	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'sawerna_blog_archive_settings' ) ) {
		return;
	}

	$heading = isset( $_POST['sawerna_blog_archive_heading'] ) ? sanitize_textarea_field( wp_unslash( $_POST['sawerna_blog_archive_heading'] ) ) : '';

	if ( '' === trim( $heading ) ) {
		delete_post_meta( $post_id, '_sawerna_blog_archive_heading' );
		return;
	}

	update_post_meta( $post_id, '_sawerna_blog_archive_heading', $heading );
}
add_action( 'save_post_page', 'sawerna_save_blog_archive_metabox' );

/**
 * Find the upright Inter face registered through theme.json / global styles (wp-fonts-local).
 *
 * WooCommerce installs a variable Inter (300-900) into global styles. It is printed after
 * assets/css/fonts.css with the same family name, so it wins for every upright weight and is
 * the font actually rendered; the theme's static upright faces are never used while it exists.
 *
 * @return string Font file URL, or '' when no such face is registered.
 */
function sawerna_get_global_styles_inter_url() {
	if ( ! class_exists( 'WP_Font_Face_Resolver' ) ) {
		return '';
	}

	foreach ( (array) WP_Font_Face_Resolver::get_fonts_from_theme_json() as $faces ) {
		foreach ( (array) $faces as $face ) {
			$family = isset( $face['font-family'] ) ? trim( (string) $face['font-family'], "\"' " ) : '';
			$style  = isset( $face['font-style'] ) ? (string) $face['font-style'] : 'normal';
			if ( 'Inter' !== $family || 'normal' !== $style || empty( $face['src'] ) ) {
				continue;
			}

			$src = is_array( $face['src'] ) ? reset( $face['src'] ) : $face['src'];
			if ( is_string( $src ) && '' !== $src ) {
				return $src;
			}
		}
	}

	return '';
}

/**
 * Preload the fonts and hero images needed for first paint.
 *
 * The theme's static upright Inter TTFs used to be preloaded here (4 x ~335 KB at high priority),
 * but they are overridden by the global-styles variable Inter (see above) and never rendered.
 * Preloading the face that is actually used keeps text rendering identical and frees bandwidth.
 */
function sawerna_preload_fonts() {
	$inter_url = sawerna_get_global_styles_inter_url();

	if ( '' !== $inter_url ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( $inter_url )
		);
	} else {
		$fonts_dir = get_template_directory_uri() . '/assets/fonts/';
		foreach ( array( 'Regular', 'Medium', 'SemiBold' ) as $weight ) {
			printf(
				'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
				esc_url( $fonts_dir . 'Inter_18pt-' . $weight . '.woff2' )
			);
		}
	}

	// Homepage hero: the first slide of each lane is a CSS background (inline style), which the
	// preload scanner cannot discover. It is the LCP element, so fetch it early.
	if ( is_front_page() && is_page_template( 'template-homepage.php' ) && function_exists( 'sawerna_get_homepage_options' ) ) {
		$options = sawerna_get_homepage_options();
		foreach ( array( 'hero_narrow_slides', 'hero_wide_slides' ) as $lane ) {
			$slides = ! empty( $options[ $lane ] ) && is_array( $options[ $lane ] ) ? array_values( $options[ $lane ] ) : array();
			$url    = ! empty( $slides[0]['image_id'] ) ? sawerna_homepage_hero_image_url( (int) $slides[0]['image_id'] ) : '';
			if ( '' !== $url ) {
				printf( '<link rel="preload" href="%s" as="image" fetchpriority="high">' . "\n", esc_url( $url ) );
			}
		}
	}

	// Contact / About heroes are full-width inline CSS backgrounds (LCP) as well.
	$hero_id = 0;
	if ( is_page_template( 'contact-page.php' ) && function_exists( 'sawerna_get_contact_options' ) ) {
		$contact_options = sawerna_get_contact_options();
		$hero_id         = ! empty( $contact_options['hero_image_id'] ) ? (int) $contact_options['hero_image_id'] : 0;
	} elseif ( is_page_template( 'about-page.php' ) && function_exists( 'sawerna_get_about_options' ) ) {
		$about_options = sawerna_get_about_options();
		$hero_id       = ! empty( $about_options['hero_image_id'] ) ? (int) $about_options['hero_image_id'] : 0;
	}
	$hero_url = $hero_id ? wp_get_attachment_image_url( $hero_id, 'full' ) : '';
	if ( $hero_url ) {
		printf( '<link rel="preload" href="%s" as="image" fetchpriority="high">' . "\n", esc_url( $hero_url ) );
	}
}
add_action( 'wp_head', 'sawerna_preload_fonts', 1 );

/**
 * Image URL used for homepage hero card backgrounds.
 *
 * Cards are at most 785x460 CSS px (background-size: cover on square sources), so the
 * 1536px variant covers them at 2x; WordPress falls back to the original when that
 * size does not exist. Originals are up to 2560px / 550 KB.
 *
 * @param int $image_id Attachment ID.
 * @return string
 */
function sawerna_homepage_hero_image_url( $image_id ) {
	$url = wp_get_attachment_image_url( (int) $image_id, '1536x1536' );
	return $url ? (string) $url : '';
}

/**
 * Elementor content here never uses its default Roboto / Roboto Slab kit fonts (all text is
 * Inter), yet Elementor enqueued them as render-blocking Google Fonts stylesheets on every page.
 */
add_filter( 'elementor/frontend/print_google_fonts', '__return_false' );

/**
 * WooCommerce "coming soon" (store pages only) renders its block template between get_header()
 * and get_footer() from inside the template_include filter, so the block styles it needs are only
 * enqueued mid-body and printed in the footer: the page first paints unstyled, then shifts
 * (desktop CLS ~0.24 on shop/category/product/cart). Enqueue them in <head> for that case only.
 */
function sawerna_coming_soon_head_styles() {
	if ( ! doing_filter( 'template_include' ) ) {
		return;
	}

	foreach ( array( 'wc-blocks-style', 'wc-blocks-style-coming-soon' ) as $handle ) {
		if ( wp_style_is( $handle, 'registered' ) ) {
			wp_enqueue_style( $handle );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'sawerna_coming_soon_head_styles', 20 );

/**
 * Add the defer attribute to scripts that ask for it.
 *
 * Theme and Sawerna Core scripts mark themselves with wp_script_add_data( $handle, 'defer', true ).
 * Core only understands the newer 'strategy' data, so this filter honours the flag (previously a
 * hard-coded list that missed the homepage and footer scripts).
 *
 * @param string $tag    Script tag HTML.
 * @param string $handle Script handle.
 * @return string
 */
function sawerna_script_loader_tag( $tag, $handle ) {
	$legacy_list = array( 'sawerna-wishlist', 'sawerna-mini-cart' ); // Enqueued without the data flag.
	$wants_defer = wp_scripts()->get_data( $handle, 'defer' ) || in_array( $handle, $legacy_list, true );

	if ( $wants_defer && false === strpos( $tag, ' defer' ) && false !== strpos( $tag, ' src=' ) ) {
		$tag = str_replace( ' src=', ' defer src=', $tag );
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'sawerna_script_loader_tag', 10, 2 );

/**
 * Add lazy loading support for images.
 */
function sawerna_add_lazy_loading( $attr ) {
	// Never lazy-load the image core marked as the LCP candidate (fetchpriority=high).
	if ( ! is_admin() && ( empty( $attr['fetchpriority'] ) || 'high' !== $attr['fetchpriority'] ) ) {
		$attr['loading']  = 'lazy';
		$attr['decoding'] = 'async';
	}
	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'sawerna_add_lazy_loading', 10, 1 );

/**
 * Remove unnecessary WordPress features for better performance.
 */
function sawerna_disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
}
add_action( 'init', 'sawerna_disable_emojis' );

/**
 * Remove unnecessary query strings from static resources.
 */
function sawerna_remove_query_strings( $src ) {
	if ( strpos( $src, '?ver=' ) ) {
		$src = remove_query_arg( 'ver', $src );
	}
	return $src;
}
// Only remove query strings in production (not in development)
if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
	add_filter( 'script_loader_src', 'sawerna_remove_query_strings', 15, 1 );
	add_filter( 'style_loader_src', 'sawerna_remove_query_strings', 15, 1 );
}

/**
 * Security: Disable file editing from WordPress admin.
 */
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- WordPress core constant.
}

/**
 * Enable SVG upload support in WordPress media library.
 */
function sawerna_enable_svg_upload( $mimes ) {
	if ( current_user_can( 'manage_options' ) ) {
		$mimes['svg']  = 'image/svg+xml';
		$mimes['svgz'] = 'image/svg+xml';
	}
	return $mimes;
}
add_filter( 'upload_mimes', 'sawerna_enable_svg_upload' );

/**
 * Fix SVG display in media library.
 */
function sawerna_fix_svg_display() {
	echo '<style>
		.attachment-266x266, .thumbnail img {
			width: 100% !important;
			height: auto !important;
		}
		.media-icon img[src$=".svg"] {
			width: 100%;
			height: auto;
		}
	</style>';
}
add_action( 'admin_head', 'sawerna_fix_svg_display' );

/**
 * Get SVG logo choices for customizer.
 *
 * @return array Array of SVG filenames.
 */
function sawerna_get_svg_logo_choices() {
	$choices = array( '' => __( 'Use WordPress Custom Logo', 'sawerna' ) );
	$svg_dir = get_template_directory() . '/assets/SVG/';

	if ( is_dir( $svg_dir ) ) {
		$files = glob( $svg_dir . '*.svg' );
		if ( $files ) {
			foreach ( $files as $file ) {
				$filename             = basename( $file );
				$choices[ $filename ] = $filename;
			}
		}
	}

	return $choices;
}

/**
 * Prepare inline SVG logo markup for output in the header.
 *
 * @param string $svg_markup Raw SVG content.
 * @return string Modified SVG markup.
 */
function sawerna_prepare_inline_logo_svg_markup( $svg_markup ) {
	if ( empty( $svg_markup ) ) {
		return '';
	}

	// Add the same class hooks the header CSS already targets.
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

	return $svg_markup;
}

function sawerna_custom_logo_output( $html ) {
	$custom_logo_id   = (int) get_theme_mod( 'custom_logo', 0 );
	$custom_logo_mime = $custom_logo_id > 0 ? (string) get_post_mime_type( $custom_logo_id ) : '';
	$site_name        = get_bloginfo( 'name' );
	$home_url         = esc_url( home_url( '/' ) );

	// Keep standard WordPress custom logos for common raster formats.
	if ( $custom_logo_id > 0 && 0 === strpos( $custom_logo_mime, 'image/' ) && 'image/svg+xml' !== $custom_logo_mime ) {
		return $html;
	}

	// Support uploaded SVG custom logos by inlining the attachment file.
	if ( $custom_logo_id > 0 && 'image/svg+xml' === $custom_logo_mime ) {
		$attachment_path = get_attached_file( $custom_logo_id );

		if ( ! empty( $attachment_path ) && file_exists( $attachment_path ) ) {
			$svg_markup = file_get_contents( $attachment_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local attachment file, not a remote URL.
			$svg_markup = preg_replace( '/<\?xml[^>]*\?>/i', '', (string) $svg_markup );
			$svg_markup = sawerna_prepare_inline_logo_svg_markup( $svg_markup );

			if ( ! empty( $svg_markup ) ) {
				return sprintf(
					'<a href="%1$s" class="custom-logo-link" rel="home" aria-label="%2$s">%3$s</a>',
					$home_url,
					esc_attr( $site_name ),
					$svg_markup
				);
			}
		}
	}

	$svg_logo = get_theme_mod( 'sawerna_header_logo_svg', '' );
	if ( empty( $svg_logo ) ) {
		return $html;
	}

	$svg_path = sawerna_get_svg_path( $svg_logo );
	if ( ! file_exists( $svg_path ) ) {
		return $html;
	}

	$svg_markup = sawerna_get_svg_inline( $svg_logo );
	$svg_markup = sawerna_prepare_inline_logo_svg_markup( $svg_markup );

	if ( empty( $svg_markup ) ) {
		return $html;
	}

	return sprintf(
		'<a href="%1$s" class="custom-logo-link" rel="home" aria-label="%2$s">%3$s</a>',
		$home_url,
		esc_attr( $site_name ),
		$svg_markup
	);
}
add_filter( 'get_custom_logo', 'sawerna_custom_logo_output', 10, 1 );

/**
 * Get client IP address securely.
 *
 * @return string Client IP address.
 */
function sawerna_get_client_ip() {
	$ip_keys = array(
		'HTTP_CLIENT_IP',
		'HTTP_X_FORWARDED_FOR',
		'HTTP_X_FORWARDED',
		'HTTP_X_CLUSTER_CLIENT_IP',
		'HTTP_FORWARDED_FOR',
		'HTTP_FORWARDED',
		'REMOTE_ADDR',
	);
	foreach ( $ip_keys as $key ) {
		if ( array_key_exists( $key, $_SERVER ) === true ) {
			foreach ( explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ) ) as $ip ) {
				$ip = trim( $ip );
				if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
					return sanitize_text_field( $ip );
				}
			}
		}
	}
	return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
}

/**
 * Security: Track login attempts (basic protection).
 * Note: For production, consider using a dedicated security plugin like Wordfence.
 */
function sawerna_track_login_attempt() {
	$ip = sawerna_get_client_ip();
	if ( empty( $ip ) ) {
		return;
	}
	$attempts = get_transient( 'sawerna_login_attempts_' . $ip );
	$attempts = $attempts ? $attempts + 1 : 1;
	set_transient( 'sawerna_login_attempts_' . $ip, $attempts, 15 * MINUTE_IN_SECONDS );
}
add_action( 'wp_login_failed', 'sawerna_track_login_attempt' );

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * WP admin login page branding.
 */
require get_template_directory() . '/inc/admin-login.php';

/**
 * Homepage template and admin fields.
 */
require get_template_directory() . '/inc/homepage-template.php';

/**
 * Contact page template and admin fields.
 */
require get_template_directory() . '/inc/contact-template.php';

/**
 * About page template and admin fields.
 */
require get_template_directory() . '/inc/about-template.php';

/**
 * Sawerna Fabric admin page (global fabric banner text).
 */
require get_template_directory() . '/inc/fabric-options.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}

/**
 * Load WooCommerce compatibility file.
 */
if ( class_exists( 'WooCommerce' ) ) {
	require get_template_directory() . '/inc/woocommerce.php';
}

/**
 * Show a clear notice when the core plugin is missing.
 *
 * @return void
 */
function sawerna_core_required_notice() {
	if ( ! current_user_can( 'activate_plugins' ) || sawerna_is_core_active() ) {
		return;
	}

	echo '<div class="notice notice-error"><p>' .
		esc_html__( 'Sawerna theme requires the Sawerna Core plugin to provide WooCommerce, wishlist, filters, swatches, HTML blocks, and product admin functionality.', 'sawerna' ) .
		'</p></div>';
}
add_action( 'admin_notices', 'sawerna_core_required_notice' );

add_action(
	'customize_register',
	function ( WP_Customize_Manager $wp_customize ) {
		// Header Section
		$wp_customize->add_section(
			'sawerna_header',
			array(
				'title'    => __( 'Header Settings', 'sawerna' ),
				'priority' => 30,
			)
		);

		// Header top bar promotional text
		$wp_customize->add_setting(
			'sawerna_header_promo_text',
			array(
				'default'           => __( 'Tasuta tarne alates 1 000 € ostust!', 'sawerna' ),
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			'sawerna_header_promo_text',
			array(
				'section'     => 'sawerna_header',
				'label'       => __( 'Promotional Text (Top Bar)', 'sawerna' ),
				'type'        => 'text',
				'description' => __( 'Text displayed in the center of the top header bar', 'sawerna' ),
			)
		);

		// Mobile header phone number
		$wp_customize->add_setting(
			'sawerna_header_phone',
			array(
				'default'           => '+372 6066 941',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			'sawerna_header_phone',
			array(
				'section' => 'sawerna_header',
				'label'   => __( 'Phone (Mobile Header)', 'sawerna' ),
				'type'    => 'text',
			)
		);

		// Option to use SVG logo from assets folder
		$wp_customize->add_setting(
			'sawerna_header_logo_svg',
			array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_file_name',
			)
		);
		$wp_customize->add_control(
			'sawerna_header_logo_svg',
			array(
				'section'     => 'sawerna_header',
				'label'       => __( 'Use SVG Logo from Assets', 'sawerna' ),
				'type'        => 'select',
				'description' => __( 'Select an SVG logo from the assets/SVG folder. Leave empty to use WordPress custom logo.', 'sawerna' ),
				'choices'     => sawerna_get_svg_logo_choices(),
			)
		);

		// Footer Section
		$wp_customize->add_section(
			'rm_footer',
			array(
				'title'    => __( 'Footer', 'sawerna' ),
				'priority' => 160,
			)
		);

		$wp_customize->add_setting(
			'rm_footer_phone',
			array(
				'default'           => '+372 6066 941',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			'rm_footer_phone',
			array(
				'section' => 'rm_footer',
				'label'   => __( 'Phone', 'sawerna' ),
				'type'    => 'text',
			)
		);

		$wp_customize->add_setting(
			'rm_footer_email',
			array(
				'default'           => 'info@rm.ee',
				'sanitize_callback' => 'sanitize_email',
			)
		);
		$wp_customize->add_control(
			'rm_footer_email',
			array(
				'section' => 'rm_footer',
				'label'   => __( 'Email', 'sawerna' ),
				'type'    => 'text',
			)
		);

		$footer_year = date_i18n( 'Y' );
		$wp_customize->add_setting(
			'rm_footer_bottom_text',
			array(
				/* translators: %s: current year. */
				'default'           => sprintf( __( '© %s Sawerna. Veebiarendus: Nimmi. Tippasemel e-poe lahendused ettevõtetele.', 'sawerna' ), $footer_year ),
				'sanitize_callback' => 'wp_kses_post',
			)
		);
		$wp_customize->add_control(
			'rm_footer_bottom_text',
			array(
				'section'     => 'rm_footer',
				'label'       => __( 'Footer Bottom Text', 'sawerna' ),
				'type'        => 'textarea',
				'description' => __( 'You can add links using HTML, e.g. <a href="https://example.com">Text</a>.', 'sawerna' ),
			)
		);

		$socials = array(
			'rm_footer_social_facebook'  => array( __( 'Facebook URL', 'sawerna' ), 'https://facebook.com/' ),
			'rm_footer_social_instagram' => array( __( 'Instagram URL', 'sawerna' ), 'https://instagram.com/' ),
			'rm_footer_social_tiktok'    => array( __( 'TikTok URL', 'sawerna' ), 'https://tiktok.com/' ),
			'rm_footer_social_youtube'   => array( __( 'YouTube URL', 'sawerna' ), 'https://youtube.com/' ),
		);

		foreach ( $socials as $key => [$label, $default] ) {
			$wp_customize->add_setting(
				$key,
				array(
					'default'           => $default,
					'sanitize_callback' => 'esc_url_raw',
				)
			);
			$wp_customize->add_control(
				$key,
				array(
					'section' => 'rm_footer',
					'label'   => $label,
					'type'    => 'url',
				)
			);
		}
	}
);

/**
 * Footer contacts defaults and helpers.
 */
function sawerna_footer_contacts_defaults(): array {
	return array(
		'ecommerce_support' => array(
			'title'  => __( 'E-poe klienditeenindus', 'sawerna' ),
			'line_1' => 'E-R 10-18',
			'line_2' => '',
			'phone'  => '+372 5687 8750',
			'email'  => 'info@sawerna.ee',
		),
		'tallinn_salon'     => array(
			'title'  => __( 'Tallinna salong', 'sawerna' ),
			'line_1' => 'E-R 11-19, L 11-15',
			'line_2' => 'Pärnu mnt 139b / Karamelli 2',
			'phone'  => '+372 5687 8750',
			'email'  => 'info@sawerna.ee',
		),
		'tartu_showroom'    => array(
			'title'  => __( 'Showroom Tartu Kaubamajas', 'sawerna' ),
			'line_1' => 'E-L 10-21, P 10-19',
			'line_2' => 'Riia 1, Tartu',
			'phone'  => '+372 5687 8750',
			'email'  => 'info@sawerna.ee',
		),
		'outlet_sawerna'    => array(
			'title'  => __( 'Outlet Sawernas', 'sawerna' ),
			'line_1' => 'E-R 10-15, L-P kokkuleppel',
			'line_2' => 'Hannula, Savarna küla',
			'phone'  => '+372 5687 9844',
			'email'  => 'hendri@sawerna.ee',
		),
		'warehouse_sawerna' => array(
			'title'  => __( 'Ladu Sawernas', 'sawerna' ),
			'line_1' => 'E-R 10-15, L-P kokkuleppel',
			'line_2' => 'Hannula, Savarna küla',
			'phone'  => '+372 5687 9843',
			'email'  => 'roland@sawerna.ee',
		),
	);
}

function sawerna_footer_contacts_labels(): array {
	return array(
		'ecommerce_support' => __( 'E-poe klienditeenindus', 'sawerna' ),
		'tallinn_salon'     => __( 'Tallinna salong', 'sawerna' ),
		'tartu_showroom'    => __( 'Showroom Tartu Kaubamajas', 'sawerna' ),
		'outlet_sawerna'    => __( 'Outlet Sawernas', 'sawerna' ),
		'warehouse_sawerna' => __( 'Ladu Sawernas', 'sawerna' ),
	);
}

function sawerna_get_footer_contacts_settings(): array {
	$defaults = sawerna_footer_contacts_defaults();
	$stored   = get_option( 'sawerna_footer_contacts', array() );
	$contacts = array();

	foreach ( $defaults as $key => $default ) {
		$data = $default;
		if ( isset( $stored[ $key ] ) && is_array( $stored[ $key ] ) ) {
			$data = array_merge( $data, $stored[ $key ] );
		}
		$contacts[ $key ] = $data;
	}

	return $contacts;
}

function sawerna_get_footer_contacts(): array {
	$contacts = array();
	$settings = sawerna_get_footer_contacts_settings();

	foreach ( $settings as $data ) {
		$lines = array_values(
			array_filter(
				array(
					$data['line_1'] ?? '',
					$data['line_2'] ?? '',
				),
				static function ( $value ) {
					return trim( (string) $value ) !== '';
				}
			)
		);

		$contacts[] = array(
			'title' => $data['title'] ?? '',
			'lines' => $lines,
			'phone' => $data['phone'] ?? '',
			'email' => $data['email'] ?? '',
		);
	}

	return $contacts;
}

function sawerna_sanitize_footer_contacts( $input ): array {
	$defaults  = sawerna_footer_contacts_defaults();
	$sanitized = array();

	foreach ( $defaults as $key => $default ) {
		$item              = ( isset( $input[ $key ] ) && is_array( $input[ $key ] ) ) ? $input[ $key ] : array();
		$sanitized[ $key ] = array(
			'title'  => sanitize_text_field( $item['title'] ?? $default['title'] ),
			'line_1' => sanitize_text_field( $item['line_1'] ?? '' ),
			'line_2' => sanitize_text_field( $item['line_2'] ?? '' ),
			'phone'  => sanitize_text_field( $item['phone'] ?? '' ),
			'email'  => sanitize_email( $item['email'] ?? '' ),
		);
	}

	return $sanitized;
}

/**
 * Footer slider defaults and helpers.
 *
 * Storage is a plain, unlimited-length list (not fixed logo_1..logo_8 slots),
 * so admins can add as many logos as they need from the "Footer Slider" admin page.
 */
function sawerna_footer_slider_default_item(): array {
	return array(
		'label' => '',
		'image' => '',
		'url'   => '',
	);
}

/**
 * Get saved footer slider items, transparently reading the legacy
 * logo_1..logo_8 keyed option shape as well as the current list shape.
 */
function sawerna_get_footer_slider_items(): array {
	$stored = get_option( 'sawerna_footer_slider', array() );

	if ( ! is_array( $stored ) ) {
		return array();
	}

	$items = array();
	foreach ( $stored as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}

		$label = isset( $item['label'] ) ? (string) $item['label'] : '';
		$image = isset( $item['image'] ) ? (string) $item['image'] : '';
		$url   = isset( $item['url'] ) ? (string) $item['url'] : '';

		if ( '' === $label && '' === $image ) {
			continue;
		}

		$items[] = array(
			'label' => $label,
			'image' => $image,
			'url'   => $url,
		);
	}

	return $items;
}

function sawerna_sanitize_footer_slider( $input ): array {
	if ( ! is_array( $input ) ) {
		return array();
	}

	$sanitized = array();
	foreach ( $input as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}

		$label = sanitize_text_field( $item['label'] ?? '' );
		$image = esc_url_raw( $item['image'] ?? '' );
		$url   = esc_url_raw( $item['url'] ?? '' );

		if ( '' === $label && '' === $image && '' === $url ) {
			continue;
		}

		$sanitized[] = array(
			'label' => $label,
			'image' => $image,
			'url'   => $url,
		);
	}

	return array_values( $sanitized );
}

function sawerna_cart_settings_defaults(): array {
	return array(
		'free_shipping_threshold'    => '1000',
		'mini_cart_show_shop_button' => '1',
	);
}

function sawerna_get_cart_settings(): array {
	$defaults = sawerna_cart_settings_defaults();
	$stored   = get_option( 'sawerna_cart_settings', array() );

	if ( ! is_array( $stored ) ) {
		return $defaults;
	}

	return array_merge( $defaults, $stored );
}

function sawerna_sanitize_cart_settings( $input ): array {
	$threshold = 0;

	if ( isset( $input['free_shipping_threshold'] ) ) {
		$raw_threshold = wp_unslash( $input['free_shipping_threshold'] );
		$threshold     = function_exists( 'wc_format_decimal' ) ? wc_format_decimal( $raw_threshold, 2 ) : (float) str_replace( ',', '.', (string) $raw_threshold );
	}

	return array(
		'free_shipping_threshold'    => (string) max( 0, (float) $threshold ),
		'mini_cart_show_shop_button' => isset( $input['mini_cart_show_shop_button'] ) ? '1' : '0',
	);
}

function sawerna_get_free_shipping_threshold(): float {
	$settings = sawerna_get_cart_settings();

	return isset( $settings['free_shipping_threshold'] ) ? (float) $settings['free_shipping_threshold'] : 0.0;
}

function sawerna_register_menu_options_page() {
	add_menu_page(
		__( 'Menu options', 'sawerna' ),
		__( 'Menu options', 'sawerna' ),
		'manage_options',
		'sawerna-menu-options',
		'sawerna_render_menu_options_page',
		'dashicons-admin-generic',
		60
	);

	$footer_slider_hook = add_submenu_page(
		'sawerna-menu-options',
		__( 'Footer Slider', 'sawerna' ),
		__( 'Footer Slider', 'sawerna' ),
		'manage_options',
		'sawerna-footer-slider-options',
		'sawerna_render_footer_slider_options_page'
	);

	add_action( 'load-' . $footer_slider_hook, 'sawerna_footer_slider_enqueue_admin_assets' );

	add_submenu_page(
		'sawerna-menu-options',
		__( 'Cart', 'sawerna' ),
		__( 'Cart', 'sawerna' ),
		'manage_options',
		'sawerna-cart-options',
		'sawerna_render_cart_options_page'
	);
}
add_action( 'admin_menu', 'sawerna_register_menu_options_page' );

function sawerna_register_menu_options_settings() {
	register_setting(
		'sawerna_menu_options_contacts',
		'sawerna_footer_contacts',
		array(
			'sanitize_callback' => 'sawerna_sanitize_footer_contacts',
		)
	);
	register_setting(
		'sawerna_menu_options_slider',
		'sawerna_footer_slider',
		array(
			'sanitize_callback' => 'sawerna_sanitize_footer_slider',
		)
	);
	register_setting(
		'sawerna_menu_options_cart',
		'sawerna_cart_settings',
		array(
			'sanitize_callback' => 'sawerna_sanitize_cart_settings',
		)
	);
}
add_action( 'admin_init', 'sawerna_register_menu_options_settings' );

function sawerna_render_menu_options_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$contacts = sawerna_get_footer_contacts_settings();
	$labels   = sawerna_footer_contacts_labels();
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Menu options', 'sawerna' ); ?></h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'sawerna_menu_options_contacts' ); ?>

			<?php foreach ( $labels as $key => $label ) : ?>
				<?php $contact = $contacts[ $key ] ?? array(); ?>
				<h2><?php echo esc_html( $label ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="sawerna-footer-<?php echo esc_attr( $key ); ?>-title">
								<?php echo esc_html__( 'Title', 'sawerna' ); ?>
							</label>
						</th>
						<td>
							<input class="regular-text" type="text"
								id="sawerna-footer-<?php echo esc_attr( $key ); ?>-title"
								name="sawerna_footer_contacts[<?php echo esc_attr( $key ); ?>][title]"
								value="<?php echo esc_attr( $contact['title'] ?? '' ); ?>">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sawerna-footer-<?php echo esc_attr( $key ); ?>-line1">
								<?php echo esc_html__( 'Line 1', 'sawerna' ); ?>
							</label>
						</th>
						<td>
							<input class="regular-text" type="text"
								id="sawerna-footer-<?php echo esc_attr( $key ); ?>-line1"
								name="sawerna_footer_contacts[<?php echo esc_attr( $key ); ?>][line_1]"
								value="<?php echo esc_attr( $contact['line_1'] ?? '' ); ?>">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sawerna-footer-<?php echo esc_attr( $key ); ?>-line2">
								<?php echo esc_html__( 'Line 2', 'sawerna' ); ?>
							</label>
						</th>
						<td>
							<input class="regular-text" type="text"
								id="sawerna-footer-<?php echo esc_attr( $key ); ?>-line2"
								name="sawerna_footer_contacts[<?php echo esc_attr( $key ); ?>][line_2]"
								value="<?php echo esc_attr( $contact['line_2'] ?? '' ); ?>">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sawerna-footer-<?php echo esc_attr( $key ); ?>-phone">
								<?php echo esc_html__( 'Phone', 'sawerna' ); ?>
							</label>
						</th>
						<td>
							<input class="regular-text" type="text"
								id="sawerna-footer-<?php echo esc_attr( $key ); ?>-phone"
								name="sawerna_footer_contacts[<?php echo esc_attr( $key ); ?>][phone]"
								value="<?php echo esc_attr( $contact['phone'] ?? '' ); ?>">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sawerna-footer-<?php echo esc_attr( $key ); ?>-email">
								<?php echo esc_html__( 'Email', 'sawerna' ); ?>
							</label>
						</th>
						<td>
							<input class="regular-text" type="email"
								id="sawerna-footer-<?php echo esc_attr( $key ); ?>-email"
								name="sawerna_footer_contacts[<?php echo esc_attr( $key ); ?>][email]"
								value="<?php echo esc_attr( $contact['email'] ?? '' ); ?>">
						</td>
					</tr>
				</table>
			<?php endforeach; ?>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Render one footer slider row (also used for the JS "add logo" template).
 *
 * @param int|string $index Array index for this row's field names.
 * @param array      $item  Row data (label/image/url).
 * @return string
 */
function sawerna_get_footer_slider_row_markup( $index, array $item ): string {
	ob_start();
	?>
	<div class="sawerna-footer-slider-row" data-sawerna-footer-slider-row="1" style="border:1px solid #dcdcde; background:#fff; padding:12px 16px; margin-bottom:12px; max-width:520px;">
		<p>
			<label><?php echo esc_html__( 'Text label', 'sawerna' ); ?></label><br>
			<input class="regular-text" type="text"
				name="sawerna_footer_slider[<?php echo esc_attr( (string) $index ); ?>][label]"
				value="<?php echo esc_attr( $item['label'] ?? '' ); ?>">
		</p>
		<p>
			<label><?php echo esc_html__( 'Image URL', 'sawerna' ); ?></label><br>
			<input class="regular-text" type="url"
				name="sawerna_footer_slider[<?php echo esc_attr( (string) $index ); ?>][image]"
				value="<?php echo esc_attr( $item['image'] ?? '' ); ?>">
		</p>
		<p>
			<label><?php echo esc_html__( 'Link URL', 'sawerna' ); ?></label><br>
			<input class="regular-text" type="url"
				name="sawerna_footer_slider[<?php echo esc_attr( (string) $index ); ?>][url]"
				value="<?php echo esc_attr( $item['url'] ?? '' ); ?>">
		</p>
		<p style="margin-bottom:0;">
			<button type="button" class="button-link-delete" data-sawerna-footer-slider-remove="1"><?php echo esc_html__( 'Remove logo', 'sawerna' ); ?></button>
		</p>
	</div>
	<?php
	return (string) ob_get_clean();
}

function sawerna_render_footer_slider_options_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$items = sawerna_get_footer_slider_items();
	if ( empty( $items ) ) {
		$items = array( sawerna_footer_slider_default_item() );
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Footer Slider', 'sawerna' ); ?></h1>
		<p><?php echo esc_html__( 'Add as many partner logos as you need — there is no fixed limit.', 'sawerna' ); ?></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'sawerna_menu_options_slider' ); ?>

			<div data-sawerna-footer-slider-rows="1">
				<?php foreach ( $items as $index => $item ) : ?>
					<?php echo sawerna_get_footer_slider_row_markup( $index, $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endforeach; ?>
			</div>

			<template id="sawerna-footer-slider-row-template">
				<?php echo sawerna_get_footer_slider_row_markup( '__INDEX__', sawerna_footer_slider_default_item() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</template>

			<p>
				<button type="button" class="button" data-sawerna-footer-slider-add="1"><?php echo esc_html__( '+ Add logo', 'sawerna' ); ?></button>
			</p>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Enqueue the repeater script for the Footer Slider admin page only.
 *
 * @return void
 */
function sawerna_footer_slider_enqueue_admin_assets() {
	$script_path = get_template_directory() . '/assets/js/footer-slider-admin.js';
	if ( ! file_exists( $script_path ) ) {
		return;
	}

	$version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $script_path ) : _S_VERSION;
	wp_enqueue_script( 'sawerna-footer-slider-admin', get_template_directory_uri() . '/assets/js/footer-slider-admin.js', array(), $version, true );
}

function sawerna_render_cart_options_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$settings = sawerna_get_cart_settings();
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Cart', 'sawerna' ); ?></h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'sawerna_menu_options_cart' ); ?>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="sawerna-cart-free-shipping-threshold">
							<?php echo esc_html__( 'Free shipping threshold', 'sawerna' ); ?>
						</label>
					</th>
					<td>
						<input
							class="regular-text"
							type="number"
							min="0"
							step="0.01"
							id="sawerna-cart-free-shipping-threshold"
							name="sawerna_cart_settings[free_shipping_threshold]"
							value="<?php echo esc_attr( $settings['free_shipping_threshold'] ?? '0' ); ?>"
						>
						<p class="description">
							<?php echo esc_html__( 'Amount needed for free shipping progress in the slide-out cart.', 'sawerna' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="sawerna-cart-mini-cart-show-shop-button">
							<?php echo esc_html__( 'Show "Poodi" button on empty mini-cart', 'sawerna' ); ?>
						</label>
					</th>
					<td>
						<input
							type="checkbox"
							id="sawerna-cart-mini-cart-show-shop-button"
							name="sawerna_cart_settings[mini_cart_show_shop_button]"
							value="1"
							<?php checked( '1', $settings['mini_cart_show_shop_button'] ?? '1' ); ?>
						>
						<p class="description">
							<?php echo esc_html__( 'Display a link to the shop when the slide-out cart is empty.', 'sawerna' ); ?>
						</p>
					</td>
				</tr>
			</table>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Get SVG file URL from the assets/SVG folder.
 *
 * @param string $filename SVG filename (e.g., 'sawerna_logo.svg').
 * @return string Full URL to the SVG file.
 */
function sawerna_get_svg_url( $filename ) {
	$svg_path = get_template_directory_uri() . '/assets/SVG/' . sanitize_file_name( $filename );
	return esc_url( $svg_path );
}

/**
 * Get SVG file path from the assets/SVG folder.
 *
 * @param string $filename SVG filename (e.g., 'sawerna_logo.svg').
 * @return string Full path to the SVG file.
 */
function sawerna_get_svg_path( $filename ) {
	// Don't sanitize if filename contains spaces (for files like "Asset 4.svg")
	// Only sanitize if needed for security, but preserve spaces for actual filenames
	$sanitized      = sanitize_file_name( $filename );
	$original_path  = get_template_directory() . '/assets/SVG/' . $filename;
	$sanitized_path = get_template_directory() . '/assets/SVG/' . $sanitized;

	// Check if original filename exists first (with space)
	if ( file_exists( $original_path ) ) {
		return $original_path;
	}
	// Fallback to sanitized filename
	return $sanitized_path;
}

/**
 * Include SVG file content inline.
 * Useful for embedding SVG directly in HTML for better performance.
 *
 * @param string $filename SVG filename (e.g., 'sawerna_logo.svg').
 * @return string SVG content or empty string if file doesn't exist.
 */
function sawerna_get_svg_inline( $filename ) {
	$file_path = sawerna_get_svg_path( $filename );
	if ( file_exists( $file_path ) ) {
		$svg_content = file_get_contents( $file_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$svg_content = preg_replace( '/<\?xml[^>]*\?>/i', '', $svg_content );
		$svg_content = preg_replace( '/<script[\s\S]*?<\/script>/i', '', $svg_content );
		$svg_content = preg_replace( '/\s+on\w+="[^"]*"/i', '', $svg_content );
		$svg_content = preg_replace( "/\s+on\w+='[^']*'/i", '', $svg_content );
		return $svg_content;
	}
	return '';
}

/**
 * Footer icon helper functions.
 * These return SVG icons for use in the footer.
 *
 * @return string SVG markup
 */
function sawerna_footer_icon_phone(): string {
	return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M6.6 10.8c1.5 3 3.6 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1.1-.3 1.2.4 2.5.6 3.9.6.6 0 1 .4 1 1V21c0 .6-.4 1-1 1C10.5 22 2 13.5 2 3c0-.6.4-1 1-1h3.3c.6 0 1 .4 1 1 0 1.3.2 2.7.6 3.9.1.4 0 .8-.3 1.1L6.6 10.8z"/></svg>';
}

function sawerna_footer_icon_mail(): string {
	return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5L4 8V6l8 5 8-5v2z"/></svg>';
}

function sawerna_footer_icon_facebook(): string {
	return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.4h-1.2c-1.2 0-1.6.7-1.6 1.5V12h2.7l-.4 2.9h-2.3v7A10 10 0 0 0 22 12z"/></svg>';
}

function sawerna_footer_icon_instagram(): string {
	return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>';
}

function sawerna_footer_icon_youtube(): string {
	return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>';
}

/**
 * Header icon helper functions.
 * These return SVG icons for use in the header.
 *
 * @return string SVG markup
 */
function sawerna_header_icon_account(): string {
	$svg_content = sawerna_get_svg_inline( 'Asset 11.svg' );
	if ( ! empty( $svg_content ) ) {
		return $svg_content;
	}
	// Fallback to inline SVG
	return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>';
}

function sawerna_header_icon_search(): string {
	$svg_content = sawerna_get_svg_inline( 'Asset 12.svg' );
	if ( ! empty( $svg_content ) ) {
		return $svg_content;
	}
	// Fallback to inline SVG
	return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>';
}

function sawerna_header_icon_wishlist(): string {
	$svg_content = sawerna_get_svg_inline( 'Asset 13.svg' );
	if ( ! empty( $svg_content ) ) {
		return $svg_content;
	}
	// Fallback to inline SVG
	return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>';
}

function sawerna_header_icon_cart(): string {
	$svg_content = sawerna_get_svg_inline( 'Asset 14.svg' );
	if ( ! empty( $svg_content ) ) {
		return $svg_content;
	}
	// Fallback to inline SVG
	return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>';
}

function sawerna_header_icon_menu(): string {
	return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>';
}

/**
 * Get WooCommerce cart count.
 *
 * @return int Cart item count.
 */
function sawerna_get_cart_count() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return 0;
	}
	return WC()->cart->get_cart_contents_count();
}

/**
 * Get WooCommerce cart total.
 *
 * @return string Formatted cart total.
 */
function sawerna_get_cart_total() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return '';
	}
	return WC()->cart->get_cart_subtotal();
}

/**
 * Get account URL.
 *
 * @return string Account URL.
 */
function sawerna_get_account_url() {
	if ( class_exists( 'WooCommerce' ) ) {
		return esc_url( wc_get_page_permalink( 'myaccount' ) );
	}
	return esc_url( wp_login_url() );
}

/**
 * Register inline styles for the single blog post shortcode card.
 *
 * @return void
 */
function sawerna_blog_post_shortcode_assets() {
	wp_register_style( 'sawerna-blog-post-shortcode', false, array(), _S_VERSION );

	$css = '
		.sawerna-blog-post-card {
			--sawerna-blog-card-accent: #a99c6a;
			--sawerna-blog-card-muted: #7d7d78;
			--sawerna-blog-card-date: #aaa49a;
			box-sizing: border-box;
			display: block;
			width: 100%;
			max-width: 390px;
			overflow: hidden;
			background: #fff;
			color: #2c2c2c;
			text-decoration: none;
			box-shadow: none;
		}

		.sawerna-blog-post-card,
		.sawerna-blog-post-card * {
			box-sizing: border-box;
		}

		.sawerna-blog-post-card:hover,
		.sawerna-blog-post-card:focus {
			color: #2c2c2c;
			text-decoration: none;
		}

		.sawerna-blog-post-card__image {
			display: block;
			width: 100%;
			aspect-ratio: 1.22 / 1;
			overflow: hidden;
			background: #f1f1f1;
		}

		.sawerna-blog-post-card__image img {
			display: block;
			width: 100%;
			height: 100%;
			object-fit: cover;
		}

		.sawerna-blog-post-card__body {
			display: block;
			padding: 22px 22px 24px;
		}

		.sawerna-blog-post-card__date {
			display: block;
			margin: 0 0 16px;
			color: var(--sawerna-blog-card-date);
			font-size: 13px;
			font-weight: 800;
			letter-spacing: .04em;
			line-height: 1.25;
			text-transform: uppercase;
		}

		.sawerna-blog-post-card__title {
			display: block;
			margin: 0 0 14px;
			color: #2f2f2f;
			font-size: 20px;
			font-weight: 800;
			line-height: 1.2;
		}

		.sawerna-blog-post-card__excerpt {
			display: block;
			margin: 0 0 22px;
			color: var(--sawerna-blog-card-muted);
			font-size: 16px;
			font-weight: 400;
			line-height: 1.55;
		}

		.sawerna-blog-post-card__read-more {
			display: inline-flex;
			align-items: center;
			gap: 10px;
			color: var(--sawerna-blog-card-accent);
			font-size: 16px;
			font-weight: 800;
			line-height: 1.25;
		}

		.sawerna-blog-post-card__arrow {
			position: relative;
			display: inline-block;
			width: 22px;
			height: 14px;
			flex: 0 0 22px;
		}

		.sawerna-blog-post-card__arrow::before,
		.sawerna-blog-post-card__arrow::after {
			position: absolute;
			content: "";
			background: currentColor;
		}

		.sawerna-blog-post-card__arrow::before {
			top: 6px;
			left: 0;
			width: 22px;
			height: 2px;
		}

		.sawerna-blog-post-card__arrow::after {
			top: 2px;
			right: 0;
			width: 10px;
			height: 10px;
			border-top: 2px solid currentColor;
			border-right: 2px solid currentColor;
			background: transparent;
			transform: rotate(45deg);
		}

		.sawerna-blog-post-card--header {
			max-width: 360px;
		}

		.sawerna-blog-post-card--header .sawerna-blog-post-card__body {
			padding: 18px 20px 20px;
		}

		.sawerna-blog-post-card--header .sawerna-blog-post-card__title {
			font-size: 18px;
		}

		@media (max-width: 480px) {
			.sawerna-blog-post-card {
				max-width: 100%;
			}

			.sawerna-blog-post-card__body {
				padding: 18px;
			}
		}
	';

	wp_add_inline_style( 'sawerna-blog-post-shortcode', $css );
}
add_action( 'wp_enqueue_scripts', 'sawerna_blog_post_shortcode_assets' );

/**
 * Render one published blog post as a card.
 *
 * @param array<string,string> $atts Shortcode attributes.
 * @return string
 */
function sawerna_render_blog_post_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'id'            => '',
			'slug'          => '',
			'image_size'    => 'large',
			'show_image'    => 'yes',
			'show_excerpt'  => 'yes',
			'show_date'     => 'yes',
			'excerpt_words' => 18,
			'read_more'     => __( 'Loe lähemalt', 'sawerna' ),
			'variant'       => '',
		),
		$atts,
		'blog_post'
	);

	$post = null;

	if ( ! empty( $atts['id'] ) ) {
		$post = get_post( absint( $atts['id'] ) );
	} elseif ( ! empty( $atts['slug'] ) ) {
		$post = get_page_by_path( sanitize_title( $atts['slug'] ), OBJECT, 'post' );
	}

	if ( ! $post || 'post' !== $post->post_type || 'publish' !== $post->post_status ) {
		return '';
	}

	wp_enqueue_style( 'sawerna-blog-post-shortcode' );

	$permalink     = get_permalink( $post );
	$title         = get_the_title( $post );
	$excerpt_words = max( 1, absint( $atts['excerpt_words'] ) );
	$classes       = array( 'sawerna-blog-post-card' );

	if ( 'header' === $atts['variant'] ) {
		$classes[] = 'sawerna-blog-post-card--header';
	}

	$output = '<a class="' . esc_attr( implode( ' ', $classes ) ) . '" href="' . esc_url( $permalink ) . '">';

	if ( 'yes' === $atts['show_image'] && has_post_thumbnail( $post ) ) {
		$output .= '<span class="sawerna-blog-post-card__image">';
		$output .= get_the_post_thumbnail(
			$post,
			sanitize_key( $atts['image_size'] ),
			array(
				'loading'  => 'lazy',
				'decoding' => 'async',
			)
		);
		$output .= '</span>';
	}

	$output .= '<span class="sawerna-blog-post-card__body">';

	if ( 'yes' === $atts['show_date'] ) {
		$output .= '<span class="sawerna-blog-post-card__date">' . esc_html( get_the_date( 'F j, Y', $post ) ) . '</span>';
	}

	$output .= '<span class="sawerna-blog-post-card__title">' . esc_html( $title ) . '</span>';

	if ( 'yes' === $atts['show_excerpt'] ) {
		$output .= '<span class="sawerna-blog-post-card__excerpt">';
		$output .= esc_html( wp_trim_words( get_the_excerpt( $post ), $excerpt_words ) );
		$output .= '</span>';
	}

	if ( '' !== trim( $atts['read_more'] ) ) {
		$output .= '<span class="sawerna-blog-post-card__read-more">';
		$output .= esc_html( $atts['read_more'] );
		$output .= '<span class="sawerna-blog-post-card__arrow" aria-hidden="true"></span>';
		$output .= '</span>';
	}

	$output .= '</span>';
	$output .= '</a>';

	return $output;
}
add_shortcode( 'blog_post', 'sawerna_render_blog_post_shortcode' );
add_shortcode( 'single_blog_post', 'sawerna_render_blog_post_shortcode' );

add_filter( 'widget_text', 'do_shortcode' );
add_filter( 'widget_text_content', 'do_shortcode' );
