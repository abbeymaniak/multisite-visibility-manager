=== Multisite Visibility Manager ===
Contributors: abbeymaniak
Donate link: https://www.buymeacoffee.com/abbeymaniak
Tags: multisite, search visibility, SEO, admin, network
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Manage search engine visibility across all subsites in your multisite network from a single settings page, with search, AJAX, and bulk update support.

== Description ==

**Multisite Visibility Manager** gives WordPress network administrators a simple interface to manage the **"Discourage search engines from indexing this site"** option for every subsite in a multisite network — all in one place.

Perfect for managing staging sites, internal testing environments, or controlling visibility during development or migration.

**Key Features**:
- View the current search visibility status of all subsites in the network.
- Toggle search engine visibility per site with a simple checkbox.
- **Bulk actions** to discourage or allow indexing for multiple sites at once.
- Instant **AJAX updates** — no page refresh required.
- Built-in **search and filter** for large networks.
- Works only in **WordPress Multisite Network Admin**.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/multisite-visibility-manager` directory, or install the plugin through the WordPress Plugins screen in your Network Admin.
2. Activate the plugin through the 'Plugins' screen in your **Network Admin**.
3. Go to **Network Admin > Visibility Manager** to view and manage visibility for all subsites.

== Frequently Asked Questions ==

= Does this plugin work on single-site installations? =

No. This plugin is only for WordPress multisite installations.

= Will this automatically block search engines? =

No. It simply toggles the `blog_public` setting in WordPress for each subsite. Ensure your site visibility settings and robots.txt are configured properly for complete search engine blocking.

= Can I use this plugin to manage visibility for a large network with hundreds of sites? =

Yes! The plugin includes a search filter and AJAX-powered updates for fast, efficient management.

== Screenshots ==

1. plugin activation page
2. Bulk actions with select-all checkbox

== Changelog ==

= 1.0 =
* Initial release with single-site visibility management.
* Added AJAX for instant single-site visibility updates.
* Added admin success messages and search filters.
* Added **bulk update support** with "Select All" checkbox.
* Enhanced AJAX for instant bulk updates.
* Added support for search and filter for large networks.

== Upgrade Notice ==

= 2.0 =
Major update with bulk action support and AJAX optimization. Update for a smoother experience managing multiple sites.

== License ==

This plugin is licensed under the GPLv2 or later. You are free to modify, redistribute, and use it under the same license terms.

== Support ==

For support, suggestions, or feature requests, please visit [support & issues](https://github.com/abbeymaniak/multisite-visibility-manager/issues).
