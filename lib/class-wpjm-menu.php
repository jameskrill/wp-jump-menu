<?php

/**
 * Class WPJM_Menu
 * @package WPJM\lib
 */
class WPJM_Menu {

	private static $wpjm_refresh = false;
	private static $needs_refresh;
	private static $wpjm_menu;
	private static $cached;

	public static function init() {
		self::checkNeedsRefresh();
		self::$wpjm_menu = self::get_menu();
		self::render();
	}

	private static function checkNeedsRefresh() {
		if ( isset( $_GET['refresh'] ) )
			self::$wpjm_refresh = true;

		self::$needs_refresh = get_transient( WPJM_NEEDS_REFRESH_CACHE_LABEL );
	}

	public static function get_menu() {
		if ( 1 == self::$needs_refresh || self::$wpjm_refresh ) {
			$wpjm_menu = new WPJM_Select_Menu( false );

			if ( 1 == self::$needs_refresh )
				delete_transient( WPJM_NEEDS_REFRESH_CACHE_LABEL );

		} else {
			$wpjm_menu = new WPJM_Select_Menu( true );
		}

		// Returns the html of the menu
		return $wpjm_menu->get_menu();
	}

	public static function render() {
		echo self::$wpjm_menu;

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			wp_die();
		} else {
			die;
		}
	}

}