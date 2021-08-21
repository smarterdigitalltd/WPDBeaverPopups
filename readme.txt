=== {{ PLUGIN_NAME }} ===
Contributors: smarterdigitalltd, davetoomey
Tags: beaver builder, popup builder, opt in, marketing
Requires at least: {{ PLUGIN_MINIMUM_WP }}
Requires PHP: {{ PLUGIN_MINIMUM_PHP }}
Tested up to: {{ PLUGIN_WP_TESTED_UP_TO }}
Stable tag: {{ PLUGIN_VERSION }}
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Build modal and fly-out popups using Beaver Builder. Manage popups across the site with a simple and intuitive dashboard.

Requires Beaver Builder version 1.10 and above.

== Description ==

Finally, a popup plugin you have total control of.

Capture leads and subscribers with popups that are designed from top to bottom using the tried, tested and loved Beaver Builder page builder, then manage the triggers for your popups from a single manager dashboard.

No more popup clashes, no more dragging placeholder modules on to the page - just one place to manage your popups.

= Drag and drop popup builder =

Built on top of Beaver Builder, Beaver Popups gives you **complete** control over the design and structure of your popups.

Utilise Beaver Builder's modules to create any type of popup you like. A contact form, purchase page or email subscription capture!

Not only that, but you then also have 1 dashboard from where to manage them.

= Create modals or fly-out popups =

Choose to create full modal popups with a background overlay, or more subtle fly-out popups (or perhaps a combination!)

= Choose from the following triggers =

- Scroll depth (percentage)
- Entrance (timed)
- Exit intent
- Attach to a BB, UABB or Powerpack button (on click)

= Integration with Beaver Builder Subscribe Form =

- When a visitor successfully subscribes to your list from a form inside a popup, it will be attributed to the popup (which will be used for reporting in future releases)

= Other features =

- Entrance and exit animations
- Border radius/box shadow for a subtle eye-catching effect with no CSS required
- Set limits for the number of times a popup should display
- MANY more features planned

== Installation ==

Get started by simply:

1. Upload the plugin files to the `/wp-content/plugins/beaver-popups` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress Admin
3. Add a new popup via WPAdmin > Beaver Popups > Add New
3. Manage your popups via WPAdmin > Beaver Popups > Popup Manager (more details below)

== Frequently Asked Questions ==

= How do I prevent popups from displaying on mobile phones? =

When you're editing a popup, head to Popup Options, and you'll see a setting for 'Disable on devices'. Simply choose 1 or more devices to disable the popup on, save and publish.

= How do I create a popup? =

Once the plugin is activated, head to WPAdmin > Beaver Popups > Add New (video coming soon)

= How do I add a popup to my site? =

Once you've created the popup, head to WPAdmin > Beaver Popups > Popup Manager.

You can assign a popup site-wide, to an entire post type (such as Pages or Posts), or a single page or post.

Simply hit the '+' button in the trigger column (entrance, exit or scroll) and choose the popup you created.

If you choose an 'entrance' trigger, you'll be asked how many seconds delay there should be. If you choose the 'scroll' trigger, then you will be asked how far the visitor should scroll down the page by percentage.

If you want to add a popup when you click on a Beaver Builder button, simply edit the button, select 'Popup' from the click action, and then type the title of your popup in the field that appears below.

= How do I create a 'fly out' popup? =

Create or edit a popup in Beaver Builder.

You will see 'Popup Settings' in the top bar. When the box opens, click 'Popup Styles'.

Choose 'Fly Out' from the Popup Type select field.

= How do I change the background overlay for a modal popup? =

Create or edit a popup in Beaver Builder.

Open 'Popup Settings' in the top bar. When the box opens, click 'Popup Styles'.

Choose 'Color' from the Overlay Type select field.

Choose the background color from the color pi

= How do I change the open animation for my popup? =

Create or edit a popup in Beaver Builder.

Open 'Popup Settings' in the top bar. When the box opens, click 'Popup Styles'.

Select an animation from the 'Open Animation' and 'Close Animation' select fields under 'Popup Animations'.

= How do I open a popup with a link =

Inside the WordPress admin > Beaver Popups > Shortcode generator. You can then place the generated shortcode inside text inside the regular WordPress editor, or Gutenberg.

= How do I open a popup when I scroll to a SPECIFIC row? =

While editing a page with Beaver Builder, edit the row that you want to use as the trigger and click the 'Advanced' tab and look for the "Beaver Popups" section.

From there you can simply type the name of the popup that you want to display.

= Troubleshooting =

If you're noticing any layout or style issues within a popup, be sure to clear your cache in Beaver Builder, your host, your caching plugin and your CDN.

== Screenshots ==

1. An example 'modal' style popup
2. The popup manager dashboard
3. Creating a 'flyout' style popup in Beaver Builder
4. The flyout popup on the front end

== Changelog ==
= 1.2.2 =
* (Fix) Beaver Builder button bug fix

= 1.2.1 =
* (Fix) Permissions fix

= 1.2.0 =
* (Feature) Open popup via a normal link with a shortcode
* (Feature) Open a popup when the visitor scrolls to a specific row in Beaver Builder
* (Feature) "Close and don't show again" - option added to buttons added while editing a popup in Beaver Builder
* (Improvements) Numerous improvements, bug fixes and compatibility checks for WordPress 5.0.3+, BB 2.2+, Gutenberg!
* (Improvements) Min capability is 'editor'
* (Requirements) Bumped PHP min version from 5.4 to 5.6 and WP tested up to from 5.2 to 5.3.1

= 1.1.0 =
* (Fix) Issue with buttons not opening popups from saved rows
* (Improvement) Clarification on why you could set a cookie on closing - rather than opening - a popup
* (Improvement) Better specificity for buttons that CLOSE popups

= 1.0.11 =
* (Fix) Fixed bug with Themer layouts (thanks David Waumsley)

= 1.0.10 =
* (Fix) Fix bug where users can't delete scope in Popup Manager
* (Improvement) Help new users find Popup Settings with glow on button

= 1.0.8 =
* (Feature) Disable popups for different screen sizes
* (Improvement) Improved support for Beaver Builder 2
* (Improvement) Improved live preview when creating popups

= 1.0.7 =
* BB Alpha 3 compatibility

= 1.0.6 =
* (Fix) Fix how JavaScript is added to Themer layouts where no Beaver Builder modules exist
* (Improvement) Improve how Beaver Popups interacts with Beaver Themer
* (Improvement) Added an option to prevent modal from closing when you click on the overlay
* (Improvement) Added an option to hide the close icon
* (Improvement) Added an option to BB button, UABB button and PowerPack 'Super' button to close the current popup
* (Improvement) Added an option to allow you to specify when cookies should be added - when the popup open or closes

= 1.0.5 =
* (Fix) Fixed an issue with button-triggered popups inside Beaver Themer layouts

= 1.0.4 =
* (Improvement) Add cookie support - only show popups a certain number of times to a user
* (Improvement) Add BB subscribe form support - email opt-ins are counted and attributed to a popup for reporting
* (Improvement) Popup impressions are counted and attributed to a popup for reporting

= 1.0.3 =
* (Fix) Fix Themer CSS bundling issue

= 1.0.2 =
* (Improvement) Row CSS - apply CSS directly to a row in the Popup Page Builder
* (Improvement) Close button control, with button preview in editor
* (Feature) WPD Optimised Video support (lightweight videos w/ autoplay - requires WPD BB Additions 1.8.7)
* (Improvement) General code refactoring

= 1.0.1 =
* (Improvement) Close animation improvements
* (Improvement) Integration with Powerpack Smart Button
* (Improvement) Integration with UABB Button
* (Improvement) UI improvement in editor when managing rows
* (Testing) Tested with WordPress 4.8

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.2.0 =
Tested with BB 2.2, WordPress 5.0.3.
