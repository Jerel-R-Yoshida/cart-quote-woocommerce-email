# Cart Quote WooCommerce & Email v1.0.19

## 🐛 Bug Fix Release

### Fixed jQuery Animation Error

This release fixes a JavaScript console error that was preventing meeting checkbox animations from working properly.

**Changes:**
- ✅ Replaced non-existent custom easing functions (`easeInOutCubic`, `easeInCubic`) with `'swing'` (jQuery's built-in easing)
- ✅ Eliminated console error: "Cannot read properties of undefined (reading 'length')"
- ✅ Meeting checkbox animations now work without requiring jQuery UI Effects library
- ✅ Improved frontend performance by using native jQuery easing

**Technical Details:**
- Modified `assets/js/frontend.js` lines 454 and 474
- Changed easing from custom functions to jQuery's native `'swing'` easing
- No additional dependencies required

**Impact:**
- Meeting request checkbox toggle animations now work smoothly without errors
- Reduced potential for console errors in production environments
- Better compatibility with minimal jQuery installations

---

## Installation

1. Download `cart-quote-woocommerce-email-v1.0.19.zip`
2. Upload to WordPress via **Plugins > Add New > Upload Plugin**
3. Activate the plugin
4. Configure settings at **WooCommerce > Cart Quote**

## Upgrade Notes

This is a minor bug fix release. Safe to upgrade from any 1.0.x version.

---

**Full Changelog:** https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/wiki/Update-Log
