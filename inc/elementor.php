<?php
/**
 * Elementor integration.
 *
 * Pages built with Elementor keep the theme header and footer (page.php or Elementor's
 * "Elementor Full Width" template) while Elementor owns the full width of the content area.
 *
 * @package Sawerna
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the current (or given) page is rendered by Elementor.
 *
 * True for pages saved in Elementor mode and for the Elementor editor preview iframe,
 * so a brand new page already gets the full-width content area while it is being built.
 *
 * @param int $post_id Optional post ID. Defaults to the queried object.
 * @return bool
 */
function sawerna_is_elementor_page( $post_id = 0 ) {
	if ( ! did_action( 'elementor/loaded' ) || ! class_exists( '\Elementor\Plugin' ) ) {
		return false;
	}

	$post_id = $post_id ? (int) $post_id : (int) get_queried_object_id();
	if ( ! $post_id ) {
		return false;
	}

	$elementor = \Elementor\Plugin::$instance;

	if ( isset( $elementor->preview ) && method_exists( $elementor->preview, 'is_preview_mode' ) && $elementor->preview->is_preview_mode( $post_id ) ) {
		return true;
	}

	$document = isset( $elementor->documents ) ? $elementor->documents->get( $post_id ) : null;

	return $document && $document->is_built_with_elementor();
}

/**
 * Body class for Elementor pages (theme CSS scopes full-width rules with it).
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function sawerna_elementor_body_class( $classes ) {
	if ( is_page() && sawerna_is_elementor_page() ) {
		$classes[] = 'sawerna-elementor-page';
	}
	return $classes;
}
add_filter( 'body_class', 'sawerna_elementor_body_class' );

/**
 * Keep Elementor enabled for pages and posts.
 *
 * Elementor reads its supported post types from the elementor_cpt_support option
 * (Elementor > Settings > General). If pages were unticked there, the "Edit with Elementor"
 * button disappears and the editor refuses to open, so pages are always added back.
 *
 * @param mixed $value Option value.
 * @return array
 */
function sawerna_elementor_cpt_support( $value ) {
	$value = is_array( $value ) ? $value : array( 'post' );
	if ( ! in_array( 'page', $value, true ) ) {
		$value[] = 'page';
	}
	return $value;
}
add_filter( 'option_elementor_cpt_support', 'sawerna_elementor_cpt_support' );
add_filter( 'default_option_elementor_cpt_support', 'sawerna_elementor_cpt_support' );
