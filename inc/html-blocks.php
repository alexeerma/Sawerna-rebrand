<?php
/**
 * UNUSED — not loaded by functions.php.
 * The active implementation is plugins/sawerna-core/includes/html-blocks.php.
 * Kept only for reference; edits here have no effect on the site.
 *
 * HTML Blocks Custom Post Type
 * Elementor-powered content blocks for use in menus and throughout the site
 *
 * @package Sawerna
 */

if (!defined('ABSPATH')) {
  exit;
}

/**
 * Flush rewrite rules on theme activation
 */
function sawerna_html_blocks_flush_rewrites() {
  sawerna_register_html_blocks_cpt();
  flush_rewrite_rules();
}
add_action('after_switch_theme', 'sawerna_html_blocks_flush_rewrites');

/**
 * Register HTML Blocks Custom Post Type
 */
function sawerna_register_html_blocks_cpt() {
  $labels = array(
    'name'                  => _x('HTML Blocks', 'Post Type General Name', 'sawerna'),
    'singular_name'         => _x('HTML Block', 'Post Type Singular Name', 'sawerna'),
    'menu_name'             => __('HTML Blocks', 'sawerna'),
    'name_admin_bar'        => __('HTML Block', 'sawerna'),
    'archives'              => __('Block Archives', 'sawerna'),
    'attributes'            => __('Block Attributes', 'sawerna'),
    'parent_item_colon'     => __('Parent Block:', 'sawerna'),
    'all_items'             => __('All HTML Blocks', 'sawerna'),
    'add_new_item'          => __('Add New HTML Block', 'sawerna'),
    'add_new'               => __('Add New', 'sawerna'),
    'new_item'              => __('New HTML Block', 'sawerna'),
    'edit_item'             => __('Edit HTML Block', 'sawerna'),
    'update_item'           => __('Update HTML Block', 'sawerna'),
    'view_item'             => __('View HTML Block', 'sawerna'),
    'view_items'            => __('View HTML Blocks', 'sawerna'),
    'search_items'          => __('Search HTML Block', 'sawerna'),
    'not_found'             => __('Not found', 'sawerna'),
    'not_found_in_trash'    => __('Not found in Trash', 'sawerna'),
    'featured_image'        => __('Featured Image', 'sawerna'),
    'set_featured_image'    => __('Set featured image', 'sawerna'),
    'remove_featured_image' => __('Remove featured image', 'sawerna'),
    'use_featured_image'    => __('Use as featured image', 'sawerna'),
    'insert_into_item'      => __('Insert into block', 'sawerna'),
    'uploaded_to_this_item' => __('Uploaded to this block', 'sawerna'),
    'items_list'            => __('HTML Blocks list', 'sawerna'),
    'items_list_navigation' => __('HTML Blocks list navigation', 'sawerna'),
    'filter_items_list'     => __('Filter blocks list', 'sawerna'),
  );

  $args = array(
    'label'                 => __('HTML Block', 'sawerna'),
    'description'           => __('Elementor-powered content blocks for menus', 'sawerna'),
    'labels'                => $labels,
    'supports'              => array('title', 'editor', 'elementor', 'permalink', 'custom-fields'),
    'hierarchical'          => false,
    'public'                => true,
    'show_ui'               => true,
    'show_in_menu'          => true,
    'menu_position'         => 25,
    'menu_icon'             => 'dashicons-layout',
    'show_in_admin_bar'     => true,
    'show_in_nav_menus'     => true,
    'can_export'            => true,
    'has_archive'           => false,
    'exclude_from_search'   => true,
    'publicly_queryable'    => true,
    'capability_type'       => 'page',
    'show_in_rest'          => true,
    'rewrite'               => array(
      'slug'       => 'html-block',
      'with_front' => false,
    ),
  );

  register_post_type('sawerna_html_block', $args);
}
add_action('init', 'sawerna_register_html_blocks_cpt', 0);

/**
 * Enable Elementor support for HTML Blocks
 */
function sawerna_add_elementor_support_to_html_blocks($cpt_support) {
  // Check if Elementor is active
  if (!did_action('elementor/loaded')) {
    return $cpt_support;
  }

  // Add HTML Blocks to Elementor supported post types
  $cpt_support[] = 'sawerna_html_block';
  
  return $cpt_support;
}
add_filter('elementor/utils/get_public_post_types', 'sawerna_add_elementor_support_to_html_blocks');

/**
 * Add Elementor support to HTML Blocks post type
 */
function sawerna_enable_elementor_for_html_blocks() {
  // Check if Elementor is active
  if (!class_exists('\Elementor\Plugin')) {
    return;
  }
  
  // Add Elementor support
  add_post_type_support('sawerna_html_block', 'elementor');
}
add_action('init', 'sawerna_enable_elementor_for_html_blocks', 50);

/**
 * Get HTML Block content
 * Returns the rendered Elementor content or regular post content
 *
 * @param int $block_id Post ID of the HTML Block
 * @return string Rendered block content
 */
function sawerna_get_html_block_content($block_id) {
  $block_id = absint($block_id);
  if (!$block_id) {
    return '';
  }

  static $content_cache = array();
  if (array_key_exists($block_id, $content_cache)) {
    return $content_cache[$block_id];
  }

  // Check if Elementor is active and this post is built with Elementor.
  if (class_exists('\Elementor\Plugin')) {
    $elementor_instance = \Elementor\Plugin::instance();
    $document = $elementor_instance->documents->get($block_id);

    if ($document && $document->is_built_with_elementor()) {
      $content_cache[$block_id] = $elementor_instance->frontend->get_builder_content_for_display($block_id);
      return $content_cache[$block_id];
    }
  }

  // Fallback to regular post content.
  $post = get_post($block_id);
  if ($post) {
    $content_cache[$block_id] = apply_filters('the_content', $post->post_content);
    return $content_cache[$block_id];
  }

  $content_cache[$block_id] = '';
  return $content_cache[$block_id];
}

/**
 * Add custom column to HTML Blocks admin list
 */
function sawerna_html_blocks_custom_columns($columns) {
  $new_columns = array();
  $new_columns['cb'] = $columns['cb'];
  $new_columns['title'] = $columns['title'];
  $new_columns['shortcode'] = __('Shortcode', 'sawerna');
  $new_columns['elementor'] = __('Edit with Elementor', 'sawerna');
  $new_columns['date'] = $columns['date'];
  return $new_columns;
}
add_filter('manage_sawerna_html_block_posts_columns', 'sawerna_html_blocks_custom_columns');

/**
 * Populate custom columns
 */
function sawerna_html_blocks_custom_column_content($column, $post_id) {
  switch ($column) {
    case 'shortcode':
      echo '<code>[sawerna_html_block id="' . esc_attr($post_id) . '"]</code>';
      break;
    case 'elementor':
      if (class_exists('\Elementor\Plugin')) {
        $url = admin_url('post.php?post=' . $post_id . '&action=elementor');
        echo '<a href="' . esc_url($url) . '" class="button button-small">' . esc_html__('Edit with Elementor', 'sawerna') . '</a>';
      } else {
        echo '<span style="color: #999;">' . esc_html__('Elementor not active', 'sawerna') . '</span>';
      }
      break;
  }
}
add_action('manage_sawerna_html_block_posts_custom_column', 'sawerna_html_blocks_custom_column_content', 10, 2);

/**
 * Add shortcode to display HTML Blocks anywhere
 * Usage: [sawerna_html_block id="123"]
 */
function sawerna_html_block_shortcode($atts) {
  $atts = shortcode_atts(array(
    'id' => 0,
  ), $atts, 'sawerna_html_block');

  $block_id = intval($atts['id']);
  if (!$block_id) {
    return '';
  }

  return sawerna_get_html_block_content($block_id);
}
add_shortcode('sawerna_html_block', 'sawerna_html_block_shortcode');

/**
 * Admin notice if Elementor is not installed
 */
function sawerna_html_blocks_elementor_notice() {
  if (!did_action('elementor/loaded')) {
    $screen = get_current_screen();
    if ($screen && $screen->post_type === 'sawerna_html_block') {
      ?>
      <div class="notice notice-warning">
        <p>
          <strong><?php esc_html_e('Elementor is not installed or activated.', 'sawerna'); ?></strong><br>
          <?php esc_html_e('HTML Blocks work best with Elementor. You can still use the WordPress editor, but Elementor provides a much better visual editing experience.', 'sawerna'); ?>
          <a href="<?php echo esc_url(admin_url('plugin-install.php?s=elementor&tab=search&type=term')); ?>" class="button button-primary" style="margin-left: 10px;">
            <?php esc_html_e('Install Elementor', 'sawerna'); ?>
          </a>
        </p>
      </div>
      <?php
    }
  }
}
add_action('admin_notices', 'sawerna_html_blocks_elementor_notice');
