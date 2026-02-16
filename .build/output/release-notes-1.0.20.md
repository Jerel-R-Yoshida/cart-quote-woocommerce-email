# Cart Quote WooCommerce & Email v1.0.20

## 🎨 UX Enhancement Release

### Improved Meeting Details Layout

This release enhances the meeting details section with a cleaner, more professional two-column layout that better utilizes horizontal space.

**Changes:**
- ✅ "Meeting Details" header now spans the full row (acts as section header)
- ✅ Date and time fields display **side-by-side** in responsive two-column grid
- ✅ CSS Grid layout with 15px gap between fields for optimal spacing
- ✅ Mobile-responsive design: automatically stacks vertically on tablets (≤768px) and phones (≤480px)
- ✅ Enhanced visual hierarchy with dedicated header container
- ✅ Updated both shortcode template (`quote-form.php`) and Elementor widget (`Quote_Form_Widget.php`)
- ✅ Better horizontal space utilization for cleaner appearance

**Before:**
```
Meeting Details
┌─────────────────────────┐
│ Preferred Start Date    │
└─────────────────────────┘
┌─────────────────────────┐
│ Preferred Meeting Time  │
└─────────────────────────┘
```

**After:**
```
Meeting Details
┌────────────┬────────────┐
│ Pref. Date │ Pref. Time │
└────────────┴────────────┘
```

**Technical Details:**
- Added `.cart-quote-meeting-header` wrapper for full-width header
- Added `.cart-quote-fields-row` container with CSS Grid (1fr 1fr)
- Added `.cart-quote-field-half` class for 50/50 column distribution
- Responsive breakpoints: 768px (tablet) and 480px (mobile)
- Modified `assets/css/frontend.css` with 42 new lines of CSS

**Impact:**
- More professional, compact layout
- Better use of screen real estate
- Improved mobile experience with automatic stacking
- Maintains all accessibility features (ARIA attributes preserved)

---

## Installation

1. Download `cart-quote-woocommerce-email-v1.0.20.zip`
2. Upload to WordPress via **Plugins > Add New > Upload Plugin**
3. Activate the plugin
4. Configure settings at **WooCommerce > Cart Quote**

## Upgrade Notes

This is a UX enhancement release. Safe to upgrade from any 1.0.x version. No database changes or breaking changes.

---

**Full Changelog:** https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/wiki/Update-Log
