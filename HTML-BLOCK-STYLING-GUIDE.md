# HTML Block Dropdown - Styling Guide

## Current Configuration

Your HTML block dropdown now matches the mega menu exactly:

### ✅ Dimensions & Layout
- **Width**: 1200px (same as mega menu)
- **Padding**: 35px 50px 40px 50px (same as mega menu)
- **Shadow**: 0 4px 12px rgba(0, 0, 0, 0.1) (same as mega menu)
- **Background**: White (#fff)
- **Position**: Centered below menu item

### ✅ Content Area
The inner container (where your Elementor content goes) has:
- **Total Width**: 1200px
- **Content Width**: 1100px (after padding: 1200px - 50px left - 50px right)
- **Height**: Auto (expands based on content)

---

## Recommended Elementor Settings

When designing your HTML block in Elementor:

### 1. **Section Settings**
- **Content Width**: Full Width (100%)
- **Column Gap**: Default or Custom (20-40px recommended)
- **Stretch Section**: No (already full width)

### 2. **Column Layout**
For 1100px content width, recommended column layouts:

#### Single Column
- Full width: Perfect for banners, promos

#### Two Columns (50/50)
- Each column: ~540px
- Good for: Image + Text, Two categories

#### Three Columns (33/33/33)
- Each column: ~353px
- Good for: Product highlights, Features, Links

#### Four Columns (25/25/25/25)
- Each column: ~260px
- Good for: Icon grid, Product grid

### 3. **Spacing**
- **Section Padding**: Set to 0 (padding is handled by dropdown container)
- **Column Padding**: 10px (default)
- **Widget Margins**: 0-20px depending on content

---

## Content Examples

### Example 1: Two-Column Promo
```
[Image/Banner]  |  [Text Content]
(550px)         |  (550px)
                |  - Heading
                |  - Description
                |  - CTA Button
```

### Example 2: Three-Column Categories
```
[Category 1]  |  [Category 2]  |  [Category 3]
(360px)       |  (360px)       |  (360px)
- Image       |  - Image       |  - Image
- Title       |  - Title       |  - Title
- Link        |  - Link        |  - Link
```

### Example 3: Feature Grid
```
[Icon + Text]  [Icon + Text]  [Icon + Text]  [Icon + Text]
(275px each)
```

---

## Visual Appearance

### Desktop (>1236px)
```
┌──────────────────────────────────────────────────────────────┐
│                     Top Bar Menu Item ▼                       │
└──────────────────────────────────────────────────────────────┘
        ┌──────────────────────────────────────────┐
        │  ╔════════════════════════════════════╗  │
        │  ║                                    ║  │ ← Shadow
        │  ║    [Your Elementor Content Here]   ║  │
        │  ║    1100px usable width             ║  │
        │  ║                                    ║  │
        │  ╚════════════════════════════════════╝  │
        │  1200px total width (centered)          │
        └──────────────────────────────────────────┘
             50px padding left/right
```

### Mobile/Tablet (<1236px)
- Width: calc(100vw - 36px)
- Max-width: 1200px
- Responsive columns (stack on mobile)

---

## Elementor Tips

### Best Practices
✅ Use Elementor's responsive mode to test mobile
✅ Optimize images (use WebP if possible)
✅ Keep column count reasonable (max 4 on desktop)
✅ Use heading hierarchy (H2, H3) for SEO
✅ Add hover effects to links/buttons
✅ Test loading speed after adding content

### Widgets That Work Well
✅ **Heading** - Titles and subtitles
✅ **Text Editor** - Body copy
✅ **Image** - Banners, products, icons
✅ **Button** - Call-to-action
✅ **Icon** - Visual indicators
✅ **Spacer** - Control vertical spacing
✅ **Divider** - Separate content sections

### Avoid
❌ Heavy animations (can be laggy in dropdowns)
❌ Video backgrounds (performance)
❌ Too many columns (>4 gets cramped)
❌ Very tall content (>600px viewport height)

---

## Color Schemes

Match your brand or try these combinations:

### Clean & Minimal
- Background: White
- Text: #333 (dark gray)
- Accent: Your brand color
- Links: #000 (black) with underline on hover

### Contrast
- Background: Light gray (#f8f8f8)
- Text: #000
- Accent boxes: White with shadow
- Buttons: Brand color

---

## Testing Checklist

After creating your HTML block:

- [ ] Dropdown opens on click/hover
- [ ] Content is centered and 1200px wide
- [ ] Images are optimized and load quickly
- [ ] Links work correctly
- [ ] Buttons have hover states
- [ ] Mobile layout stacks properly
- [ ] Content is readable (good contrast)
- [ ] No horizontal scrolling
- [ ] Dropdown closes properly (click outside, ESC key)
- [ ] No layout shift on load

---

## Quick Fixes

### Content Too Wide
- Check Elementor section width settings
- Ensure columns add up to 100%
- Remove any custom width overrides

### Content Not Centered
- Already handled by CSS
- If issue persists, check Elementor alignment settings

### Spacing Issues
- Adjust widget margins in Elementor
- Use spacer widgets for vertical spacing
- Check column gap settings

### Images Not Displaying
- Verify image URLs are correct
- Check image permissions
- Optimize large images (>500KB)

---

## Advanced Customization

If you need to adjust the dropdown width or padding:

### Change Width
Edit `/assets/css/header-desktop.css`:
```css
.sawerna-html-block-dropdown {
  width: 1400px; /* Change from 1200px */
}
```

### Change Padding
```css
.sawerna-html-block-dropdown-inner {
  padding: 40px 60px; /* Adjust as needed */
}
```

### Change Shadow
```css
.sawerna-html-block-dropdown {
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15); /* Stronger shadow */
}
```

---

## Support

For questions or issues, refer to the main HTML-BLOCKS-README.md file.

**Happy designing! 🎨**
