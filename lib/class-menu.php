<?php
namespace WPJM\lib;

use WPJM\lib\SelectMenu;

/**
 * Class WpjmMenu
 * @package WPJM\lib
 */
class Menu {

	protected $wpjm_refresh;
	protected $needs_refresh;
	protected $wpjm_menu;
	protected $cached;

	public function __construct() {
		$this->checkNeedsRefresh();
		$this->wpjm_menu = $this->buildMenu();
	}

	protected function buildMenu( $cached = false ) {
		$this->wpjm_menu = new SelectMenu($cached);
	}

	public function render() {
		echo $this->wpjm_menu;

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			wp_die();
		} else {
			die;
		}
	}

}