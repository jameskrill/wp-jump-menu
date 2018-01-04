<?php
/*
Plugin Name: WP Jump Menu
Plugin URI: http://wpjumpmenu.com
Description: Creates a drop-down menu (jump menu) in a bar across the top or bottom of the screen that makes it easy to jump right to a page, post, or custom post type in the admin area to edit.
Version: 3.6.2
Author: Jim Krill
Author URI: http://krillwebdesign.com
License: GPLv2 or later
Copyright: Jim Krill
Text Domain: wp-jump-menu
*/

if ( ! function_exists( 'add_action' ) ) {
	echo 'Access Denied.';
	exit;
}

define( 'WPJM_VERSION', '3.6.2' );
define( 'WPJM__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPJM__PLUGIN_URL', plugins_url( '', __FILE__ ) );
define( 'WPJM_OPTIONS', 'wpjm_options' );
define( 'WPJM_CACHE_PREFIX', 'wpjm_menu_' );
define( 'WPJM_NEEDS_REFRESH_CACHE_LABEL', 'wpjm_needs_refresh' );

// Activation Hook
register_activation_hook( __FILE__, array( 'WPJM', 'plugin_activation' ) );

// require classes
require_once( WPJM__PLUGIN_DIR . 'lib/class-wpjm.php' );

// set text domain
load_plugin_textdomain( 'wp-jump-menu', false, basename( dirname( __FILE__ ) ) . '/languages' );

// Only run this code if we are NOT within the Network pages on multisite.
if ( ! is_network_admin() ) {
	if ( function_exists( 'current_user_can' ) ) {

		// load wpjm
		add_action( 'init', array( 'WPJM', 'init' ) );

		// load settings page in admin pages
		if ( is_admin() ) {
			require_once( WPJM__PLUGIN_DIR . 'lib/class-wpjm-admin.php' );
			add_action( 'init', array( 'WPJM_Admin', 'init' ) );
		}
	}
}
