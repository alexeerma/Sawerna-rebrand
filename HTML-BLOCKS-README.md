# HTML Blocks - Documentation

## Overview

HTML Blocks is a powerful feature that allows you to create Elementor-powered dropdown content for your menu items. Similar to WoodMart theme's HTML blocks, this functionality integrates seamlessly with Elementor for visual content creation.

---

## Features

✅ **Elementor Integration** - Design complex layouts visually with drag & drop  
✅ **Menu Dropdown Support** - Attach HTML blocks to any top bar menu item  
✅ **1200px Width Container** - Similar to mega menu styling  
✅ **Reusable Content** - Create once, use anywhere  
✅ **Responsive** - Mobile-friendly design  
✅ **Keyboard Accessible** - Full ARIA support  

---

## How to Use

### Step 1: Create an HTML Block

1. Go to WordPress Admin → **HTML Blocks** → **Add New**
2. Give your block a title (e.g., "Promo Banner", "Contact Info")
3. Click **"Edit with Elementor"** button
4. Design your content using Elementor's visual editor:
   - Add sections, columns, widgets
   - Insert images, text, buttons, forms, etc.
   - Style everything visually
5. Click **Update/Publish** when done

**Tip:** You can also use the WordPress editor if Elementor is not needed for simple content.

---

### Step 2: Add HTML Block to Menu

1. Go to **Appearance** → **Menus**
2. Select your **Top Bar Menu** (or create one if needed)
3. You have two options:

#### Option A: Add from HTML Blocks Panel (Left Side)
- Look for the **"HTML Blocks"** panel on the left
- Check the HTML block you want to add
- Click **"Add to Menu"**

#### Option B: Add to Existing Menu Item
- Click on any existing menu item in your menu to expand it
- Scroll down to find **"HTML Block Dropdown"** field
- Select your HTML block from the dropdown
- Click **"Save Menu"**

---

### Step 3: View on Frontend

1. Visit your website
2. The menu item with an HTML block will have a small dropdown arrow (▼)
3. Click or hover over the menu item to see the dropdown
4. Your Elementor-designed content will appear in a 1200px wide container

---

## Dropdown Behavior

### Opening
- **Hover**: Dropdown opens when you hover over the menu item
- **Click**: Clicking the menu item toggles the dropdown

### Closing
- **Click Outside**: Clicking anywhere outside closes the dropdown
- **ESC Key**: Press Escape to close
- **Auto Close**: Moving away from menu item closes after a brief delay

---

## Styling

### Default Styling
- **Width**: 1200px (matches mega menu)
- **Background**: White
- **Shadow**: Soft shadow for depth
- **Padding**: 30px inside container
- **Animation**: Smooth fade in/out

### Customization
All styles can be customized in:
- `/assets/css/header-desktop.css` - Dropdown container styles
- `/assets/css/html-blocks.css` - Elementor content styles

---

## Best Practices

### Content Design
✅ Keep content concise - dropdowns should be scannable  
✅ Use columns for organized layouts (2-3 columns work well)  
✅ Add clear call-to-action buttons  
✅ Use high-quality images (but optimize for web)  
✅ Test on mobile devices  

### Performance
✅ Optimize images before uploading  
✅ Avoid heavy animations in dropdowns  
✅ Keep Elementor widgets to minimum necessary  
✅ Cache Elementor CSS (Elementor → Settings → Advanced)  

---

## Example Use Cases

### 1. **Promotional Banner**
- Hero image with overlay text
- "Shop Now" button
- Limited time offer message

### 2. **Contact Information**
- Company address with map
- Phone numbers
- Email addresses
- Business hours

### 3. **Multi-Column Content**
- Category highlights (3 columns)
- Featured products grid
- Blog post previews
- Social media links

### 4. **Form Integration**
- Newsletter signup
- Quick contact form
- Search form

---

## Shortcode Usage

HTML Blocks can also be used anywhere via shortcode:

```php
[sawerna_html_block id="123"]
```

Replace `123` with your HTML Block post ID.

**Usage:**
- In posts/pages content
- In widgets
- In theme template files: `<?php echo do_shortcode('[sawerna_html_block id="123"]'); ?>`

---

## Troubleshooting

### Dropdown Not Showing
1. Make sure you saved the menu
2. Verify the HTML block is published (not draft)
3. Clear cache if using a caching plugin
4. Check browser console for JavaScript errors

### Elementor Content Not Displaying
1. Ensure Elementor is installed and activated
2. Edit the HTML block and click "Update" in Elementor
3. Regenerate Elementor CSS (Elementor → Tools → Regenerate CSS)

### Styling Issues
1. Clear browser cache
2. Check if Elementor is loading its CSS
3. Inspect element to identify conflicting styles
4. Adjust padding in `/assets/css/html-blocks.css`

### Mobile Issues
1. Test in responsive mode in Elementor
2. Adjust column settings for mobile breakpoints
3. Reduce padding on mobile in CSS

---

## Technical Details

### Files Created
- `/inc/html-blocks.php` - CPT registration & Elementor support
- `/inc/menu-html-blocks.php` - Custom walker & menu integration
- `/assets/css/html-blocks.css` - Dropdown content styles
- JavaScript in `/js/header-desktop.js` - Dropdown functionality

### Custom Post Type
- **Post Type**: `sawerna_html_block`
- **Supports**: Title, Editor, Elementor
- **Visibility**: Admin only (not public pages)
- **Menu Location**: Shows in "HTML Blocks" menu in admin

### Menu Meta Fields
- **Meta Key**: `_menu_item_html_block`
- **Value**: Post ID of HTML Block
- **Storage**: Post meta for menu items

---

## Advanced Customization

### Change Dropdown Width
Edit `/assets/css/header-desktop.css`:

```css
.sawerna-html-block-dropdown {
  width: 1000px; /* Change from 1200px */
}
```

### Change Animation Speed
```css
.sawerna-html-block-dropdown {
  transition: opacity 0.5s ease; /* Change from 0.3s */
}
```

### Add Custom CSS Classes
Use the menu item CSS Classes field in menu editor to add custom classes.

---

## Support & Updates

For issues or feature requests, refer to theme documentation or contact support.

**Version**: 1.0  
**Last Updated**: January 2026  
**Compatibility**: WordPress 5.9+, Elementor 3.0+
