=== Beer Journal for Untappd ===
Contributors: jaz_on
Donate link: https://example.com/
Tags: beer, untappd, checkin, brewery, rating, journal, sync
Requires at least: 6.0
Tested up to: 6.7
Stable tag: 0.1.0
Requires PHP: 8.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Import and display your Untappd beer check-ins on your WordPress site with automatic synchronization.

**Development status:** version 0.1.0 is an installable bootstrap only. MVP features (RSS sync, import, CPT, etc.) are planned and documented in the `docs/` tree in the Git repository; they are not yet available in this build.

== Description ==

Beer Journal allows you to automatically sync your Untappd check-ins to your WordPress site, creating a beautiful beer journal with ratings, photos, and detailed information about each beer you've tried.

= Key Features =

* **Automatic RSS Sync**: Automatically syncs your latest Untappd check-ins via RSS feed with adaptive polling based on your activity
* **Historical Import**: Import your entire Untappd history with a manual crawler that respects rate limits
* **Rating System**: Customizable rating system with labels and star mapping (0-5 stars)
* **Image Management**: Automatically imports beer photos to WordPress Media Library with optimization
* **Taxonomies**: Auto-creates beer styles, breweries, and venues as WordPress taxonomies
* **Theme-Agnostic Templates**: Overridable templates that work with any WordPress theme
* **Gutenberg Blocks**: Display check-ins with customizable blocks (Phase 2)

= Important Notes =

**No Official API**: This plugin does not use an official Untappd API (none exists). Instead, it uses the RSS feed for recent check-ins and scrapes HTML pages for complete metadata. Rate limiting is implemented to respect Untappd's servers.

**Data Limitations**: The RSS feed only contains basic information. Complete metadata (rating, ABV, style, etc.) requires scraping individual check-in pages.

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/beer-journal/`
2. Activate the plugin through the 'Plugins' screen
3. Go to **Beer Journal > Settings** to configure your Untappd RSS feed URL
4. Enter your RSS feed URL: `https://untappd.com/rss/user/YOUR_USERNAME`
5. Configure sync frequency and other options
6. Click "Save Settings"
7. Start syncing your check-ins!

== Frequently Asked Questions ==

= Is this plugin official from Untappd? =

No, this is an independent plugin that is not affiliated with or endorsed by Untappd. It respects Untappd's trademark guidelines.

= How does the plugin sync check-ins? =

The plugin uses your Untappd RSS feed to detect new check-ins. For complete metadata (rating, ABV, style, etc.), it scrapes individual check-in pages. Rate limiting is implemented to respect Untappd's servers.

= Can I import my historical check-ins? =

Yes! Use the "Historical Import" feature in the settings to import your entire Untappd history. You can configure batch size and delays to control the import speed.

= What if a check-in fails to import? =

Failed check-ins are saved as drafts with a notification. You can retry failed imports manually from the admin interface. The plugin will also automatically retry up to 3 times.

= Can I customize the rating system? =

Yes! The rating system is fully customizable. You can adjust the mapping rules (how Untappd ratings map to stars) and customize the labels for each rating level.

= Are the templates customizable? =

Yes! All templates are theme-agnostic and can be overridden by your theme. See the documentation for the template hierarchy.

= What WordPress and PHP versions are required? =

WordPress 6.0+ and PHP 8.2+ are required. The plugin is tested up to WordPress 6.7.

== Screenshots ==

1. Check-ins archive page with grid and table views
2. Single check-in view with all details
3. Settings page with synchronization options
4. Rating system configuration interface
5. Historical import with progress tracking

== Changelog ==

= 1.0.0 =
* Initial release
* RSS sync functionality with adaptive polling
* Historical import crawler with batch processing
* Rating system with custom labels and mapping
* Image import to Media Library
* Auto-creation of taxonomies (beer styles, breweries, venues)
* Theme-agnostic templates (archive, single, taxonomies)
* Complete admin settings interface
* Logging and error handling
* Retry logic for failed imports

== Upgrade Notice ==

= 1.0.0 =
Initial release. Install and configure your Untappd RSS feed URL to start syncing check-ins.

