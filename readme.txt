=== Mail Designer 365 Subscription Form Inserter ===
Tags: maildesigner365
Requires at least: 6.0
Tested up to: 6.6.2
Stable tag: 1.0.7
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
This plugin allows you to insert a subscription form into your WordPress site.

== Description ==


_Mail Designer 365 Subscription Form Inserter_


This plugin allows you to insert a subscription form into your WordPress site.

The subscription form is created in the Mail Designer 365 Web UI (https://my.maildesigner365.com) and then inserted into your WordPress site using a shortcode.

## Installation

1. Upload the plugin files to the `/wp-content/plugins/mail-designer-365-subscription-form-inserter` directory, or install the plugin through the WordPress plugins screen directly.

2. Activate the plugin through the 'Plugins' screen in WordPress

3. Use the Settings->Mail Designer 365 Subscription Form Inserter screen to configure the plugin

## Settings

1. Enter your Mail Designer 365 API credentials. You can find them in the Mail Designer 365 Web UI under "Settings" -> "API Key".

2. Update the settings, a list of your subscription forms will be fetched from Mail Designer 365.

3. Select the subscription form you want to insert.

4. Copy the shortcode and insert it into your WordPress page or post.

## Changelog

### 1.0.7 (2024-10-15)

* Register admin functions

### 1.0.6 (2024-10-15)

* Remove experimental MCE editor functionality

### 1.0.5 (2024-10-15)

* Update icon

### 1.0.4 (2024-10-15)

* Add example icon

### 1.0.3 (2024-10-15)

* Initial version added to the WordPress plugin repository via SVN

### 1.0.2 (2024-02-01)

* Use `wp_enqueue_script` to load the form JavaScript

### 1.0.1 (2024-03-05)

* Sanitize and escape output to comply with WordPress coding standards

### 1.0.0 (2024-02-01)

* Initial release

## License

GPLv2 or later
