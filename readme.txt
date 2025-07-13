== === Plugin Monitor ===
   Contributors: kawsarahmedr
   Tags: plugin updates, plugin tracker, plugin monitor, wordpress.org plugin data, plugin info, update notifier, plugin stats
   Requires at least: 5.0
   Tested up to: 6.8
   Requires PHP: 7.4
   Stable tag: 1.0.0
   License: GPLv2 or later
   License URI: https://www.gnu.org/licenses/gpl-2.0.html

   Track WordPress.org plugin update information using the official `plugins_api()` function. View plugin stats in a simple dashboard card layout with scheduled updates via cron.

   == Description ==

   **Plugin Monitor** lets you easily track and display update details for any public WordPress.org plugin by using their slugs. Designed for developers, agencies, and site admins who need a central view of important plugin info.

   This plugin pulls plugin information from the WordPress.org repository using the official `plugins_api()` function, stores the data using WordPress transients, and refreshes it hourly using a cron job.

   No complicated setup. No multiple admin pages. Just paste your plugin slugs and you're good to go.

   === 👇 Features ===

   - Track plugin data using `plugins_api()`
   - Supports multiple plugin slugs (comma-separated)
   - Cron job updates data hourly
   - Displays:
     - Plugin Name
     - Version
     - Active Installations
     - Rating
     - Number of Ratings
     - Last Updated Date
     - Plugin Homepage
   - Admin page with card-based layout
   - Summary metrics: Total Active Installs, Total Ratings

   == Installation ==

   1. Upload the plugin folder to the `/wp-content/plugins/` directory or install via the WordPress admin.
   2. Activate the plugin.
   3. Go to **Dashboard → Plugin Monitor**
   4. Enter WordPress.org plugin slugs (e.g., `woocommerce, contact-form-7`) in the input field.
   5. Click "Save & Refresh Data" to fetch information.

   == Frequently Asked Questions ==

   = Where do I find the plugin slug? =
   You can get the slug from the plugin's WordPress.org URL. For example, for https://wordpress.org/plugins/woocommerce/, the slug is `woocommerce`.

   = Can I track premium or non-directory plugins? =
   No. Plugin Monitor only tracks plugins listed on the official [WordPress.org repository](https://wordpress.org/plugins/).

   = How often does the data update? =
   Once per hour via WordPress cron. You can also manually refresh data by clicking the "Save & Refresh Data" button.

   = Can I display this data on the frontend? =
   Not yet — this plugin is currently focused on admin-only usage.

   == Screenshots ==

   1. Input plugin slugs and view tracked data.
   2. Plugin card with detailed information.
   3. Summary section showing total installs and ratings.

   == Changelog ==

   = 1.0.0 =
   * Initial release
   * Fetches and displays plugin data via `plugins_api()`
   * Hourly cron job for updates
   * Summary stats for all tracked plugins

   == Upgrade Notice ==

   = 1.0.0 =
   Initial release of Plugin Monitor. Track plugin stats and updates for any WordPress.org plugin.

   == SEO Keywords ==

   WordPress plugin update monitor, track plugin stats, plugins_api example, WordPress plugin update checker, plugin monitor GitHub, wp plugin update tracker, monitor plugin versions, wp.org plugin info, plugin data viewer, WordPress cron plugin example

   == Support ==

   Need help or want to contribute? Visit our [GitHub repo](https://github.com/kawsarahmedr/plugin-monitor) or submit an issue. If you find a bug or have a feature request, please open an issue on GitHub. Contributions are welcome!