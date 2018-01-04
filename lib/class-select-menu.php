<?php
namespace WPJM\lib;

/**
 * Class WpjmSelectMenu
 * @package WPJM\lib
 */
class SelectMenu {

	protected $wpjm_options;

	public function __construct($cached) {
		$this->options = get_option( 'wpjm_options' );

	}

}