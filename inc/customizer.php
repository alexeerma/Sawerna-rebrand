<?php
/**
 * Sawerna Theme Customizer
 *
 * @package Sawerna
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function sawerna_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	// ------------------------------------------------------------------
	// Login Page Banner
	// ------------------------------------------------------------------
	$wp_customize->add_section(
		'sawerna_login_page',
		array(
			'title'    => __( 'Login Page', 'sawerna' ),
			'priority' => 160,
		)
	);

	$wp_customize->add_setting(
		'sawerna_login_banner_image',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'sawerna_login_banner_image',
			array(
				'label'   => __( 'Banner image', 'sawerna' ),
				'section' => 'sawerna_login_page',
			)
		)
	);

	$wp_customize->add_setting(
		'sawerna_login_banner_text',
		array(
			'default'           => '',
			'sanitize_callback' => 'wp_kses_post',
		)
	);
	$wp_customize->add_control(
		'sawerna_login_banner_text',
		array(
			'label'   => __( 'Banner text', 'sawerna' ),
			'section' => 'sawerna_login_page',
			'type'    => 'textarea',
		)
	);

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial(
			'blogname',
			array(
				'selector'        => '.site-title a',
				'render_callback' => 'sawerna_customize_partial_blogname',
			)
		);
		$wp_customize->selective_refresh->add_partial(
			'blogdescription',
			array(
				'selector'        => '.site-description',
				'render_callback' => 'sawerna_customize_partial_blogdescription',
			)
		);
	}
}
add_action( 'customize_register', 'sawerna_customize_register' );

/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function sawerna_customize_partial_blogname() {
	bloginfo( 'name' );
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function sawerna_customize_partial_blogdescription() {
	bloginfo( 'description' );
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function sawerna_customize_preview_js() {
	wp_enqueue_script( 'sawerna-customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), _S_VERSION, true );
}
add_action( 'customize_preview_init', 'sawerna_customize_preview_js' );
