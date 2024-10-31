=== PageSpeed Dashboard ===
Contributors: PL
Tags: pagespeed, cache, dashboard, google, insights
Tested up to: 6.5.5
Stable tag: 1.4.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.txt

Displays the PageSpeed score of the home page in the WordPress dashboard and allows clearing site cache.

== Description ==

PageSpeed Dashboard is a simple WordPress plugin that displays the PageSpeed score of your site's home page directly in the WordPress dashboard. This plugin fetches scores from the Google PageSpeed Insights API and highlights performance levels using color codes. It also provides a feature to clear your site's cache for improved performance.

== Third-Party Services ==

This plugin uses the Google PageSpeed Insights API to fetch PageSpeed scores. When you use this plugin, requests are sent to the following service:
* [Google PageSpeed Insights API](https://developers.google.com/speed/docs/insights/v5/get-started)
  * [Terms of Use](https://developers.google.com/terms/)
  * [Privacy Policy](https://policies.google.com/privacy)

== Installation ==

1. Upload the `pagespeed-dashboard` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. A widget will be created with the name 'PageSpeed Score' on the dashboard.

== Frequently Asked Questions ==

= How do I get an API key for Google PageSpeed Insights? =

You can get an API key by following the instructions on the [Google PageSpeed Insights API page](https://developers.google.com/speed/docs/insights/v5/get-started).

= What do the different colors of the score indicate? =

* Green: The PageSpeed score is 80 or above, indicating good performance.
* Yellow: The PageSpeed score is between 40 and 79, indicating moderate performance.
* Red: The PageSpeed score is below 40, indicating poor performance.

= How often are the PageSpeed scores updated? =

Scores are fetched and updated each time you click the "Fetch Scores" button in the dashboard widget.

== Changelog ==

= 1.0.0 =
* Initial release.

= 1.0.1 =
* Introduced cache clearing option for improved site performance.

= 1.1.0 =
* This update includes a new feature for fetching all images with size (in KB) and displaying the table.

= 1.2.0 =
* This update includes a new option for downloading a list of images available on the homepage.

= 1.3.0 =
* This update includes minor security improvements for the plugin.

= 1.4.0 =
* This update includes other minor security improvements for the plugin.

= 1.4.1 =
* This update includes minor security improvements for the plugin.

= 1.4.2 =
* This update introduces new steps to guide users before clearing the cache.

= 1.4.3 =
* Removed clear cache functionality for security concern.

== License ==

This plugin is licensed under the GPLv2 or later. For more information, visit [GPLv3 License](https://www.gnu.org/licenses/gpl-3.0.txt).
