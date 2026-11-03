<?php
/**
 * UNUSED — not loaded by functions.php.
 * The active implementation is plugins/sawerna-core/includes/menu-html-blocks.php.
 * Kept only for reference; edits here have no effect on the site.
 *
 * Menu HTML Blocks Integration
 * Adds HTML Blocks as custom menu items with dropdown support
 *
 * @package Sawerna
 */

if (!defined('ABSPATH')) {
  exit;
}

/**
 * Get published HTML blocks.
 *
 * @return WP_Post[]
 */
function sawerna_get_published_html_blocks() {
  static $html_blocks = null;

  if (null !== $html_blocks) {
    return $html_blocks;
  }

  $html_blocks = get_posts(array(
    'post_type' => 'sawerna_html_block',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
    'post_status' => 'publish',
  ));

  return $html_blocks;
}

/**
 * Custom Walker for Top Bar Menu with HTML Block support
 */
class Sawerna_Walker_Nav_Menu_HTML_Blocks extends Walker_Nav_Menu {
  
  /**
   * Resolve the HTML block ID for a menu item.
   */
  private function get_html_block_id($item) {
    $html_block_id = get_post_meta($item->ID, '_menu_item_html_block', true);
    if ($html_block_id && get_post_status($html_block_id) === 'publish') {
      return (int) $html_block_id;
    }

    if (isset($item->object, $item->object_id) && $item->object === 'sawerna_html_block') {
      $block_id = (int) $item->object_id;
      if ($block_id && get_post_status($block_id) === 'publish') {
        return $block_id;
      }
    }

    return 0;
  }

  /**
   * Starts the element output.
   */
  public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
    $indent = ($depth) ? str_repeat("\t", $depth) : '';
    
    $classes = empty($item->classes) ? array() : (array) $item->classes;
    $classes[] = 'menu-item-' . $item->ID;
    
    // Check if this menu item has an HTML block attached
    $html_block_id = $this->get_html_block_id($item);
    if ($html_block_id) {
      $classes[] = 'menu-item-has-html-block';
      $classes[] = 'menu-item-has-children';
    }
    
    /**
     * Filters the arguments for a single nav menu item.
     */
    $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args, $depth));
    $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';
    
    /**
     * Filters the ID applied to a menu item's list item element.
     */
    $id = apply_filters('nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args, $depth);
    $id = $id ? ' id="' . esc_attr($id) . '"' : '';
    
    $output .= $indent . '<li' . $id . $class_names . '>';
    
    $atts = array();
    $atts['title']  = !empty($item->attr_title) ? $item->attr_title : '';
    $atts['target'] = !empty($item->target) ? $item->target : '';
    $atts['rel']    = !empty($item->xfn) ? $item->xfn : '';
    $atts['href']   = !empty($item->url) ? $item->url : '';
    
    // If this item has an HTML block, prevent default link behavior
    if ($html_block_id) {
      $atts['href'] = '#';
      $atts['class'] = 'menu-item-html-block-trigger';
      $atts['aria-expanded'] = 'false';
      $atts['aria-haspopup'] = 'true';
    }
    
    /**
     * Filters the HTML attributes applied to a menu item's anchor element.
     */
    $atts = apply_filters('nav_menu_link_attributes', $atts, $item, $args, $depth);
    
    $attributes = '';
    foreach ($atts as $attr => $value) {
      if (!empty($value)) {
        $value = ('href' === $attr) ? esc_url($value) : esc_attr($value);
        $attributes .= ' ' . $attr . '="' . $value . '"';
      }
    }
    
    /** This filter is documented in wp-includes/post-template.php */
    $title = apply_filters('the_title', $item->title, $item->ID);
    
    /**
     * Filters a menu item's title.
     */
    $title = apply_filters('nav_menu_item_title', $title, $item, $args, $depth);
    
    $item_output = isset($args->before) ? $args->before : '';
    $item_output .= '<a' . $attributes . '>';
    $item_output .= (isset($args->link_before) ? $args->link_before : '') . $title . (isset($args->link_after) ? $args->link_after : '');
    $item_output .= '</a>';
    $item_output .= isset($args->after) ? $args->after : '';
    
    /**
     * Filters a menu item's starting output.
     */
    $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
  }
  
  /**
   * Ends the element output, adds HTML block content before closing tag
   */
  public function end_el(&$output, $item, $depth = 0, $args = null) {
    // Check if this menu item has an HTML block attached
    $html_block_id = $this->get_html_block_id($item);
    
    if ($html_block_id) {
      // Get the HTML block content
      $block_content = sawerna_get_html_block_content($html_block_id);
      
      if (!empty($block_content)) {
        // Wrap the HTML block in a dropdown container
        $output .= '<div class="sawerna-html-block-dropdown">';
        $output .= '<div class="sawerna-html-block-dropdown-inner">';
        $output .= $block_content;
        $output .= '</div>';
        $output .= '</div>';
      }
    }
    
    $output .= "</li>\n";
  }
}

/**
 * Add HTML Block selector to menu item settings
 */
function sawerna_add_html_block_nav_menu_meta_box() {
  add_meta_box(
    'sawerna_html_blocks_nav_menu',
    __('HTML Blocks', 'sawerna'),
    'sawerna_html_blocks_nav_menu_meta_box_content',
    'nav-menus',
    'side',
    'default'
  );
}
add_action('admin_init', 'sawerna_add_html_block_nav_menu_meta_box');

/**
 * Display HTML Blocks in menu meta box
 */
function sawerna_html_blocks_nav_menu_meta_box_content() {
  $html_blocks = sawerna_get_published_html_blocks();
  
  ?>
  <div id="posttype-sawerna-html-blocks" class="posttypediv">
    <div id="tabs-panel-sawerna-html-blocks" class="tabs-panel tabs-panel-active">
      <ul id="sawerna-html-blocks-checklist" class="categorychecklist form-no-clear">
        <?php if (!empty($html_blocks)) : ?>
          <?php foreach ($html_blocks as $block) : ?>
            <li>
              <label class="menu-item-title">
                <input type="checkbox" class="menu-item-checkbox" name="menu-item[-<?php echo esc_attr($block->ID); ?>][menu-item-object-id]" value="<?php echo esc_attr($block->ID); ?>">
                <?php echo esc_html($block->post_title); ?>
              </label>
              <input type="hidden" class="menu-item-type" name="menu-item[-<?php echo esc_attr($block->ID); ?>][menu-item-type]" value="post_type">
              <input type="hidden" class="menu-item-object" name="menu-item[-<?php echo esc_attr($block->ID); ?>][menu-item-object]" value="sawerna_html_block">
              <input type="hidden" class="menu-item-title" name="menu-item[-<?php echo esc_attr($block->ID); ?>][menu-item-title]" value="<?php echo esc_attr($block->post_title); ?>">
              <input type="hidden" class="menu-item-url" name="menu-item[-<?php echo esc_attr($block->ID); ?>][menu-item-url]" value="#">
            </li>
          <?php endforeach; ?>
        <?php else : ?>
          <li><?php esc_html_e('No HTML Blocks found. Create one first!', 'sawerna'); ?></li>
        <?php endif; ?>
      </ul>
    </div>
    <p class="button-controls">
      <span class="add-to-menu">
        <input type="submit" class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e('Add to Menu', 'sawerna'); ?>" name="add-post-type-menu-item" id="submit-posttype-sawerna-html-blocks">
        <span class="spinner"></span>
      </span>
    </p>
  </div>
  <?php
}

/**
 * Add custom field to menu items for selecting HTML blocks
 */
function sawerna_menu_item_html_block_field($item_id, $item, $depth, $args) {
  $html_block_id = get_post_meta($item_id, '_menu_item_html_block', true);
  
  // Get all HTML blocks.
  $html_blocks = sawerna_get_published_html_blocks();
  
  ?>
  <p class="field-html-block description description-wide">
    <label for="edit-menu-item-html-block-<?php echo esc_attr($item_id); ?>">
      <?php esc_html_e('HTML Block Dropdown', 'sawerna'); ?><br>
      <select name="menu-item-html-block[<?php echo esc_attr($item_id); ?>]" id="edit-menu-item-html-block-<?php echo esc_attr($item_id); ?>" class="widefat edit-menu-item-html-block">
        <option value=""><?php esc_html_e('— No HTML Block —', 'sawerna'); ?></option>
        <?php foreach ($html_blocks as $block) : ?>
          <option value="<?php echo esc_attr($block->ID); ?>" <?php selected($html_block_id, $block->ID); ?>>
            <?php echo esc_html($block->post_title); ?>
          </option>
        <?php endforeach; ?>
      </select>
      <span class="description"><?php esc_html_e('Select an HTML Block to display in a dropdown when this menu item is clicked/hovered.', 'sawerna'); ?></span>
    </label>
  </p>
  <?php
}
add_action('wp_nav_menu_item_custom_fields', 'sawerna_menu_item_html_block_field', 10, 4);

/**
 * Save custom field value
 */
function sawerna_save_menu_item_html_block($menu_id, $menu_item_db_id, $args) {
  if ( ! current_user_can( 'edit_theme_options' ) ) {
    return;
  }
  if (isset($_POST['menu-item-html-block'][$menu_item_db_id])) {
    $html_block_id = absint(wp_unslash($_POST['menu-item-html-block'][$menu_item_db_id]));
    update_post_meta($menu_item_db_id, '_menu_item_html_block', $html_block_id);
  } else {
    delete_post_meta($menu_item_db_id, '_menu_item_html_block');
  }
}
add_action('wp_update_nav_menu_item', 'sawerna_save_menu_item_html_block', 10, 3);

/**
 * Add admin styles for menu editor
 */
function sawerna_menu_html_blocks_admin_styles() {
  $screen = get_current_screen();
  if ($screen && $screen->id === 'nav-menus') {
    ?>
    <style>
      .field-html-block {
        margin: 10px 0 !important;
      }
      .field-html-block label {
        display: block;
        margin-bottom: 5px;
      }
      .field-html-block select {
        width: 100%;
      }
      .field-html-block .description {
        font-size: 12px;
        font-style: italic;
        color: #666;
        margin-top: 5px;
        display: block;
      }
    </style>
    <?php
  }
}
add_action('admin_head', 'sawerna_menu_html_blocks_admin_styles');
