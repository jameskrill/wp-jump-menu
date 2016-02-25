=== Plugin Name ===
Contributors: jkrill
Donate link: http://www.krillwebdesign.com
Plugin URI: http://wpjumpmenu.com
Author URI: http://krillwebdesign.com
Tags: posts, pages, admin, jump, menu, quick, links, custom post types
Requires at least: 3.2.1
Tested up to: 4.2.3
Stable tag: 3.4.3

Creates a drop-down menu in the admin area which makes it easy to jump to a page, post, custom post type or media file for editing.

== Description ==

<h3><a href="http://www.wpjumpmenu.com">WATCH A DEMONSTRATION</a></h3>

https://vimeo.com/120853682

It will take 30 seconds to realize you can not live without this plugin.

<strong>Creates a drop-down menu in the admin area which makes it easy to jump to a page, post, custom post type or media file for editing.

<ul><li>One click and you're editing!</li><li>Shift+Click and you're viewing the page on the front end!</li></ul></strong>

<h3>THIS PLUGIN IS A MUST HAVE FOR WORDPRESS DEVELOPERS!</h3>

<em><strong>UPDATE:</strong> Now featuring <a href="http://harvesthq.github.com/chosen/" target="_blank">Chosen</a> JavaScript library for select menu styling and functionality. </em>

Sick of having to go to your Posts list or Pages list to edit an entry?  Me too.

The WP Jump Menu plugin is a useful plugin that adds a drop-down "jump" menu to the bottom or top of the WordPress admin area which allows you to select from a list of pages, posts or custom post types and jump right into editing that page.

<h3>Why is this needed?</h3>

By adding this ability, you can save TONS OF TIME when editing a bunch of posts or pages.  No more do you have to click on Pages, and then search for the right page in the list, and then click on the title or on edit.   Simply find the page or post in the drop-down (which is neatly organized and indented) and it will jump to edit that page or post.

<h3>Customizable</h3>

The plugin comes with an options page where you can edit the position of the jump bar (top or bottom of the screen, or in the WordPress admin bar), whether or not to use Chosen (which features searching through the drop down!), the background color, font color, link color, border color and the icon and message that optionally get displayed on the left hand side (only when not in the WordPress admin bar), status colors, and many more options.

<h2>Great for theme developers to help clients navigate in the admin area of WordPress.</h2>
We use this plugin on all of our projects and decided it was time to release it to the world!

Enjoy!

<em>Please provide feedback for this plugin to help improve it!  Having issues?!? Post in the support forum and I will fix it right away!</em>

== Installation ==


1. Upload the `wp-jump-menu` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. You'll find the options under Settings->WP Jump Menu
4. In the settings, you can customize the jump menu's location, colors, sort order, and enter in custom post types (comma seperated) to list in the jump menu.
5. Make sure you click save to save your settings.

== Frequently Asked Questions ==

= Is there any way to add additional content to the jump menu? =

Yes.

There are two filters that run at the beginning of the menu and at the end.

The first is:

`wpjm-filter-beginning-of-list`

which happens after the first <option> which is "Select to Edit"

and the second is:

`wpjm-filter-end-of-list`

which happens just before the last <optgroup> which is for the Jump Menu Settings page.

Your filter function would look something like this:

    function wpjm_filter( $wpjm_string ) {

        $wpjm_string .= '<optgroup label="Global Content Blocks">';
        $wpjm_string .= '<option value="url-on-select">Option Name</option>';
        $wpjm_string .= '</optgroup>';

        return $wpjm_string;

    }

And your apply_filters would look like this:

`apply_filters( 'wpjm-beginning-of-list', 'wpjm-filter' )`

= The jump menu isn't working. =

If the jump menu isn't working, please submit a forum post and I will respond asap. <a href="http://wordpress.org/tags/wp-jump-menu?forum_id=10#postform">Click here to start a forum thread</a>.

Make sure you have the latest version of WordPress and the latest version of WP-Jump-Menu.

= How do I put my logo in? =

The branding options are only accessible when the position of the jump menu is set to top or bottom, and not WP Admin Bar. You need to upload your logo icon, through WordPress maybe, and get the full URL to the image.  This you will put into the Logo Icon field in the options.

== Screenshots ==

1. The Jump Menu in the WordPress Admin Bar

2. The Options Page - Here you can edit the drop-down list of custom post types (as well as built in post types) and their order, change the colors of the WP Jump Menu, and specify a few other options!

3. The Jump Menu - Just a drop down, but one POWERFUL drop down!

== Changelog ==
= 3.4.3 =
* Minor bug fixes.

= 3.4.2 =
* Updated Chosen to 1.4.2
* Copied Chosen to a custom file to avoid conflict with chosen being loaded outside of WPJM, and to preserve customizations on update.
* Fixed responsive styling of menu. Added more responsive breaks, adjusted the default view so it takes up less room in the WP Admin bar. Please leave comments in support if you find any bugs.
* Improved :hover state styling on Chosen dropdown menu.

= 3.4.1 =
* Removed error_log() from main php file.

= 3.4.0 =
* Please report all bugs!  If you get Javascript errors, please report what other plugins you have installed and activated. Thanks!
* Style adjustments, bug fixes, code cleanup, added labels, etc.
* Removed jQuery.ui.position script as it was not needed anymore and was causing some JS errors.
* Changed Chosen settings: Search by ID, search contains (search parts of page names). Best way to search ids is to start with "id: " and then the id.  You must have show ID turned on and be using Chosen.

= 3.3.1 =
* Resolved conflict with SOLA Newsletters plugin (and other plugins that used the same colorpicker javascript)
* Added filters for adding items at the beginning or end of menu (see FAQ for more instructions)
* Fixed some styling issues on the settings page

= 3.3 =
* Internal clean-up and bug-fixes.
* Tested and working on WP 4.0

= 3.2.2 =
* Additional bug fixes.
* Added new setting: Front-End Jump - If checked, the jump menu on the front-end of the site will serve as a way to jump to pages on the front-end of the site (instead of jumping to edit them in the backend)
* Updated Chosen plugin (1.1.0)

= 3.2.1 =
* Bug Fixes - set WP_DEBUG to true and removed all errors / warnings.

= 3.2 =
* Cleaned up and updated for WordPress 3.8+
* Improved Settings page
* Changed pages (or post types that act like pages) number of pages to be "depth" instead.
* Updated default install values to use chosen and left aligned.

= 3.1.7 =
* Subtle styling changes to non-chosen select menu.
* Added responsive hiding for non-wpadmin bar jump menu bar text.

= 3.1.6 =
* Removed Transients as it was not allowing the current page being editing to be selected.

= 3.1.5 =
* Bug Fix

= 3.1.4 =
* Now using transients to increase performance.  PLEASE LET ME KNOW IF THE MENU DOES NOT SHOW UPDATED CONTENT AFTER CERTAIN EVENTS.  It should reset transients after posts/pages are saved/updated - and when wpjm settings are changed.

= 3.1.3 =
* Removing the tooltips completely (was causing errors for some people)

= 3.1.2 =
* Added option to show/hide jump menu on front-end (when using top or bottom positioning)

= 3.1.1 =
* NEW! View an item on the front end by holding SHIFT and left clicking an item in the jump menu!
* This only works when using the Chosen JavaScript plugin.  Goto WP-Jump-Menu settings and activate the Chosen plugin.  Then click the drop down, hover over an item, hold shift and click - and it will load the page on the front end!

= 3.1.0 =
* + NEW! Filter Media by type.
* You can now select which media types you want to display in the jump menu.  To order by media type, select "Mime Type" from the order by drop down on the Media post type.
* Fixed a layout issue when viewed in the WP Toolbar in Firefox.

= 3.0.5 =
* Fixed the bug where the settings page was blank.  Only affected certain sites using certain themes.

= 3.0.4 =
* More bug fixes.
* Fixed sorting of pages (hierarchy post types)

= 3.0.3 =
* Bug Fix: Multi-Site, broke page when in Network Admin

= 3.0.2 =
* Bug Fixes
* Temporarily removed WP Tip for Jump Menu
* Added ability to left align chosen menu.

= 3.0.1 =
* Bug Fixes

= 3.0 =
* Speed Improvements
* Completely reworked from the ground up (plugin put into it's own class)
* Chosen JS library added for additional functionality
* With Chosen, now you can search through the menu quickly
* Add or remove the title (the text that appears just to the left of the menu)
* Changed the output formatting of the jump menu item's text

= 2.5.1 =
* Fixed bug where jump menu was not showing up in the WP Admin Bar (Toolbar) on the front end of sites.

= 2.5 =
* Added capabilities support.  Now only the posts/pages a certain user CAN edit, will show in the jump menu.  If a user does not have the ability to add or edit posts or pages, the jump menu will not show up.

= 2.4.3 =
* Fixed a javascript bug if a theme was enqueueing a javascript file with the target "jquery-functions".

= 2.4.2 =
* Fixed an error if wp-config.php is moved out of it's default location

= 2.4.1 =
* Fixed typo (thanks johnw1965)
* Fixed bug (content paths were wrong if plugins directory was outside of wp-content - thanks JiDaii)
* Added responsive scaling to the jump menu when placed in the WordPress admin bar

= 2.4 =
* Added new option to show "Add New" link under each post type in the jump menu.
* Fixed a bug that was preventing heirarchical pagers from showing correctly in the menu.

= 2.3.4 =
* Fixed a bug that was causing a javascript error in WP versions 3.2.1 and lower.

= 2.3.3 =
* Added the ability to choose what color each post status will appear as in the jump menu.
* Added the date to show on future/scheduled posts/pages.

= 2.3.2 =
* Fixed another small bug.

= 2.3.1 =
* Fixed a bug pertaining to arrays.

= 2.3 =
* Added post status options for each post type.  Now you can choose which posts/pages to display per post type based on their post status (i.e. Published, Drafts, Pending, Private, etc.).  See the new options in the WP Jump Menu Options page.

= 2.2.9 =
* Fixed a bug when showing drafts.

= 2.2.8 =
* Added the ability to show Drafts.

= 2.2.7 =
* Updated the code for insertion into the WP Admin Bar. WP compliant now.

= 2.2.6 =
* BETA testing Jump Menu in WP Admin Bar.  Please report any bugs.

= 2.2.5 =
* Tested on WP 3.3
* Fixed positioning bar when positioned on bottom (if WP admin bar was showing)

= 2.2.4 =
* Replaced the listing of heirarchical pages/post types with WP native Walker functionality. Should improve listing of parent / child pages in drop-down.
* Cleaned up the code, added comments
* Used get_edit_post_link() instead of hard coding link to edit post page (yay!)
* Added the option to show or hide the ID next to the post/page name in the drop-down

= 2.2.3 =
* Bug fixed: Order By was not working because the orderby value names had not been updated to exclude "post-".  Thanks Tim for the bug report.

= 2.2.2 =
* Minor fix to jqueryfunctions.js

= 2.2.1 =
* Forgot to add default value for "number of posts" to show when updating or installing for the first time.  Post types had to be saved the first time to get the list to show up correctly.

= 2.2 =
* Completed/fixed the sorting of the post types on the options page. Now you can sort the post types in the order you want them to appear in the Jump Menu, and when you save that order, the table-list will stay in the order you saved it in.
* Started using wp_cache to store the list of posts, pages, etc. that Jump Menu displays in it's drop-down, so it is not querying the database on every page load.  Hopefully I did this right and it works, if anyone wants to jump in the code and check for me... great!

= 2.1.4 =
* Fixed error (variable not array)

= 2.1.3 =
* Added the ability to sort the Post Types by clicking and dragging on their rows and re-ordering them.  This will effect the order they display in the Jump Menu.
* Updated text on options page.

= 2.1.2 =
* Small bug fixed: default post and page post type were not set right.

= 2.1.1 =
* Fixed Update issue: When updated, WP Jump Menu would stop working because it uses new option values.  New default values should be set when updated.

= 2.1 =
* Fixed bug: Can be displayed at the top of the screen WITH the WP Admin Bar, whereas before it was being hidden when both were activated and on the top.
* Totally redid the Post Types and the way you select them.  You can now select (or deselect) any post type.  Not just custom post types.  So you can totally turn off pages, or posts - or now you can add attachments!
* Redid the options page using wp_settings.  Much better!
* Added the title "WP Jump Menu" next to the drop-down

= 2.0 =
* Totally redid the form layout to match native WP admin styles
* Updated the color styling of the jump menu bar so you can live preview the changes as you change the colors to see what it will look like
* Got rid of Logo Width (automatically determines the width of the logo based on the logo URL)
* Changed the custom post type selector from manual (you had to type in a comma separated list) to showing checkboxes for each custom post type that exists, allowing you to simply select each one you want to show in the drop-down.

= 1.4 =
* Cleaned up a little bit, and updated the readme.txt

= 1.3 =
* Changed how the optional logo is displayed (from background css to <img> tag)

= 1.2 =
* Added color picker to hex inputs
* Added sort order options for pages and posts
* Added number of posts to display option

= 1.1 =
* Fixed minor js bug
* Included a screenshot

= 1.0 =
* Initial Release

== Upgrade Notice ==

= 1.3 =
* Updated logo option

= 1.2 =
* Updates to options in admin area

= 1.1 =
Stable working version.

= 1.0 =
* Initial Release
