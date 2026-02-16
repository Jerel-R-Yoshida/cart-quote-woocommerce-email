# Cart Quote WooCommerce & Email

[![Version](https://img.shields.io/badge/version-1.0.32-blue.svg)](https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email)
[![WordPress](https://img.shields.io/badge/WordPress-%3E%3D5.8-blue.svg)](https://wordpress.org/)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-%3E%3D6.0-purple.svg)](https://woocommerce.com/)
[![PHP](https://img.shields.io/badge/PHP-%3E%3D7.4-777bb4.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-GPL%20v2-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

Transform your WooCommerce checkout into a quote submission system with Google Calendar integration and email notifications. No payment processing required.

## Features

- **Quote Submission System** - Replace WooCommerce checkout with a quote request form
- **Google Calendar Integration** - Automatically create calendar events for quote meetings
- **Google Meet Support** - Generate Google Meet links for virtual meetings
- **Email Notifications** - Send confirmation emails to customers and notifications to admins
- **Admin Dashboard** - Manage quotes, update statuses, and track activity
- **Meeting Editor** - Edit meeting dates/times from the admin panel
- **Elementor Widgets** - Drag-and-drop widgets for quote forms and cart display
- **Shortcodes** - Flexible shortcodes for any page or post
- **CSV Export** - Export quotes for external processing
- **Debug Logging** - Comprehensive logging for troubleshooting

## Requirements

- WordPress 5.8 or higher
- WooCommerce 6.0 or higher
- PHP 7.4 or higher
- MySQL 5.7 or higher

## Installation

### From ZIP File

1. Download the latest release ZIP file
2. Go to WordPress Admin > Plugins > Add New > Upload Plugin
3. Upload the ZIP file and click "Install Now"
4. Activate the plugin
5. Configure settings at Cart Quotes > Settings

### Manual Installation

1. Extract the ZIP file to your `/wp-content/plugins/` directory
2. Rename the folder to `cart-quote-woocommerce-email`
3. Activate the plugin from the Plugins menu
4. Configure settings at Cart Quotes > Settings

## Configuration

### General Settings

Navigate to **Cart Quotes > Settings**:

| Setting | Description | Default |
|---------|-------------|---------|
| Quote ID Prefix | Prefix for quote IDs | Q |
| Quote Start Number | Starting number for quotes | 1001 |

### Email Settings

| Setting | Description | Default |
|---------|-------------|---------|
| Send to Admin | Send notification to admin | Yes |
| Send to Client | Send confirmation to customer | Yes |
| Admin Email | Email address for notifications | WordPress admin email |
| Admin Email Subject | Subject line for admin emails | New Quote Submission #{quote_id} |
| Client Email Subject | Subject line for client emails | Thank you for your quote request #{quote_id} |

### Meeting Settings

| Setting | Description | Default |
|---------|-------------|---------|
| Meeting Duration | Default duration for meetings | 60 minutes |
| Time Slots | Available time slots for meetings | 09:00, 11:00, 14:00, 16:00 |
| Auto Create Event | Create Google Calendar event when status changes to Contacted | No |
| Google Meet | Enable Google Meet link creation | No |

### Google Calendar Setup

1. Go to **Cart Quotes > Google Calendar**
2. Create a Google Cloud project and OAuth credentials
3. Enter your Client ID and Client Secret
4. Click "Connect Google Calendar"
5. Authorize access to your Google Calendar

## Usage

### Shortcodes

#### Quote Form with Cart

```
[cart_quote_form show_cart="true"]
```

Displays the quote submission form with the current cart contents.

#### Cart Display

```
[cart_quote_cart show_button="true"]
```

Displays the full cart with quantity controls.

#### Mini Cart

```
[cart_quote_mini_cart show_subtotal="true" show_count="true"]
```

Displays a compact dropdown cart widget.

### Elementor Widgets

The plugin provides three Elementor widgets:

1. **Cart Quote Form** - Full quote submission form
2. **Cart Quote Cart** - Shopping cart display
3. **Cart Quote Mini Cart** - Compact dropdown cart

### Admin Management

Navigate to **Cart Quotes > All Quotes** to:

- View all submitted quotes
- Filter by status (Pending, Contacted, Closed, Canceled)
- Search by customer name, email, or company
- View quote details and activity log
- Update quote status
- Edit meeting date/time
- Create Google Calendar events
- Create Google Meet links
- Save admin notes
- Resend emails
- Export to CSV

## Quote Statuses

| Status | Description |
|--------|-------------|
| Pending | New quote, awaiting review |
| Contacted | Initial contact made with customer |
| Closed | Quote resolved (accepted or declined) |
| Canceled | Quote canceled by customer or admin |

## Developer Hooks

### Actions

```php
// After quote submission
do_action('cart_quote_after_submission', $insert_id, $quote_id, $insert_data);

// Auto-create Google event
do_action('cart_quote_auto_create_event', $quote);
```

### Filters

```php
// Modify email headers
add_filter('cart_quote_email_headers', function($headers, $type) {
    $headers[] = 'X-Custom-Header: Value';
    return $headers;
}, 10, 2);
```

## Debug Logging

Enable debug logging by setting these constants in `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Logs are written to `wp-content/debug.log` with the `[Cart Quote]` prefix.

## Security

- **Nonce Verification**: All AJAX requests verify nonces
- **Capability Checks**: Admin actions require proper permissions
- **Input Sanitization**: All user input is sanitized
- **Token Encryption**: Google OAuth tokens are encrypted with AES-256-CBC
- **SQL Injection Prevention**: All queries use prepared statements
- **XSS Prevention**: Output is properly escaped

## File Structure

```
cart-quote-woocommerce-email/
├── cart-quote-woocommerce-email.php    # Main plugin file
├── readme.txt                          # WordPress.org readme
├── uninstall.php                       # Uninstall handler
├── assets/
│   ├── css/
│   │   ├── admin.css                   # Admin styles
│   │   └── frontend.css                # Frontend styles
│   └── js/
│       ├── admin.js                    # Admin JavaScript
│       └── frontend.js                 # Frontend JavaScript
├── src/
│   ├── Admin/                          # Admin functionality
│   ├── Core/                           # Core classes
│   ├── Database/                       # Database operations
│   ├── Elementor/                      # Elementor widgets
│   ├── Emails/                         # Email handling
│   ├── Frontend/                       # Frontend functionality
│   ├── Google/                         # Google Calendar integration
│   └── WooCommerce/                    # WooCommerce integration
└── templates/
    ├── admin/                          # Admin templates
    ├── emails/                         # Email templates
    └── frontend/                       # Frontend templates
```

## Changelog

### 1.0.32
- Version sync and documentation updates

### 1.0.31
- UX: Page refreshes 1 second after successful meeting update
- Added PHP unit tests for meeting update validation

### 1.0.29
- Bug fix: Removed unused initTooltips() function

### 1.0.28
- Git hooks fix: Fixed wiki update not running after push

### 1.0.27
- New: Admin notes confirmation popup
- New: Meeting Editor section on quote detail page
- New: Google Meet toggle setting
- New: "Create Google Meet" button on quote detail

### 1.0.20 - 1.0.24
- UX improvements for meeting details layout
- Bug fixes for jQuery animation errors
- Critical AJAX fixes for frontend

### 1.0.10 - 1.0.16
- Performance optimizations (caching, query monitoring, rate limiting)
- Repository cleanup (reduced to 42 files)
- Deployment validation system

### 1.0.9
- Fixed ZIP archive format for Linux servers
- Code cleanup and bug fixes

### 1.0.7 - 1.0.8
- Initial repository setup
- Documentation enhancements

## Support

For issues and feature requests, please use the [GitHub Issues](https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/issues) page.

## License

This plugin is licensed under the GPL v2 or later.

```
Copyright (C) 2026 Jerel Yoshida

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```
