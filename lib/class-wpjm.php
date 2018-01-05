<?php

/**
 * Class WPJM
 */
class WPJM {

	private static $initiated    = false;
	private static $options      = array();
	private static $current_user = null;

	/**
	 *  Init
	 * @description:
	 * @since 3.0
	 * @created: 12/12/12
	 **/
	public static function init() {
		if ( ! self::$initiated ) {

			// permission Testing
			if ( ! current_user_can( 'edit_posts' ) ) {
				return false;
			}

			// set class variables
			self::$options      = get_option( 'wpjm_options' );
			self::$current_user = wp_get_current_user();

			self::init_scripts();

			// setup init actions/hooks
			self::init_hooks();

			// check for upgrade
			$current_version = get_option( 'wpjm_version' );
			if ( empty( $current_version ) || $current_version < WPJM_VERSION ) {
				// initiate install/update
				self::plugin_activation();
			}
		}
	}

	private static function init_hooks() {
		self::$initiated = true;

		// Clear cache when things are changed
		// Clear LocalStorage on save
		foreach ( self::$options['postTypes'] as $key => $val ) {
			add_action( 'save_post_' . $key, array( 'WPJM', 'clear_local_storage' ), 10, 3 );
		}
		// fires when attachments are edited
		add_action( 'edit_attachment', array( 'WPJM', 'clear_local_storage_after_media_upload' ) );
		// should fire on ajax "clear_local_storage" action
		add_action( 'wp_ajax_clear_local_storage', array( 'WPJM', 'ajax_clear_local_storage' ) );

		// insert action links under plugin name on wp plugins page
		add_action( 'plugin_action_links', array( 'WPJM', 'plugin_action_links' ), 10, 2 );

		// Load Scripts

		// load scripts on both front-end and back-end
		add_action( 'wp_enqueue_scripts', array( 'WPJM', 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( 'WPJM', 'enqueue_scripts' ) );
		add_action( 'wp_print_styles', array( 'WPJM', 'inject_styles' ) );
		add_action( 'admin_print_styles', array( 'WPJM', 'inject_styles' ) );

		// load only in back-end
		add_action( 'admin_enqueue_scripts', array( 'WPJM', 'admin_enqueue_scripts' ) );
		add_action( 'admin_print_footer_scripts', array( 'WPJM', 'ajax_clear_local_storage_script' ) );

		if ( 'wpAdminBar' == self::$options['position'] ) {
			if ( is_admin_bar_showing() ) {
				add_action( 'admin_bar_menu', array( 'WPJM', 'admin_bar_menu' ), 25 );
				add_action( 'wp_print_styles', array( 'WPJM', 'inject_styles' ) );
			}
		} else {
			add_action( 'admin_footer', array( 'WPJM', 'wpjm_footer' ) );
			if ( isset( self::$options['frontend'] ) && 'true' == self::$options['frontend'] ) {
				add_action( 'wp_enqueue_scripts', array( 'WPJM', 'wpjm_frontend_scripts' ) );
				add_action( 'wp_footer', array( 'WPJM', 'wpjm_footer' ) );
			}
		}

		// Load menu using ajax request
    // Main entry point for creation of the menu
    // Loads WPJM_Menu which loads WPJM_Select_Menu which builds the menu.
		add_action( 'wp_ajax_wpjm_menu', array( 'WPJM_Menu', 'init' ) );
	}

	private static function init_scripts() {
		// register scripts
		$scripts = array(
			'wpjm-admin-js'       => WPJM__PLUGIN_URL . '/assets/js/wpjm-admin.js',
			'wpjm-main-js'        => WPJM__PLUGIN_URL . '/assets/js/wpjm-main.js',
			'wpjm-chosenjs'       => WPJM__PLUGIN_URL . '/assets/js/chosen/custom.chosen.jquery.js',
			'wpjm-jquery-hotkeys' => WPJM__PLUGIN_URL . '/assets/js/jquery/jquery.hotkeys.js',
		);
		self::register_scripts( $scripts );

		// localize main script
		$post_id = isset( $_GET['post'] ) ? $_GET['post'] : 0;
		$post_id = isset( $_GET['page_id'] ) ? $_GET['page_id'] : $post_id;
		$post_id = isset( $_GET['attachment_id'] ) ? $_GET['attachment_id'] : $post_id;
		$post_id = isset( $_GET['p'] ) ? $_GET['p'] : $post_id;
		wp_localize_script(
			'wpjm-main-js', 'wpjm_opt', array(
				'baseUrl'       => admin_url( 'admin-ajax.php' ),
				'useChosen'     => isset( self::$options['useChosen'] ) && 'true' == self::$options['useChosen'],
				'position'      => esc_js( self::$options['position'] ),
				'reloadText'    => __( 'Refresh Jump Menu' ),
				'currentPageID' => $post_id,
				'useShortcut'   => isset( self::$options['useShortcut'] ) && 'true' == self::$options['useShortcut'],
				'isAdmin'       => is_admin(),
			)
		);

		// register styles
		$styles = array(
			'chosencss'            => WPJM__PLUGIN_URL . '/assets/js/chosen/chosen.css',
			'chosencss-wpadminbar' => WPJM__PLUGIN_URL . '/assets/js/chosen/chosen-wpadmin.css',
			'wpjm-css'             => WPJM__PLUGIN_URL . '/assets/css/wpjm.css',
		);
		self::register_styles( $styles );
	}

	/**
	 * loop through array of scripts and register them
	 *
	 * @param $scripts array(slug => url)
	 *
	 * @return array|bool
	 */
	public static function register_scripts( $scripts ) {
		if ( empty( $scripts ) || gettype( $scripts ) != 'array' ) {
			return false;
		}

		$scripts_registered = array();
		foreach ( $scripts as $k => $v ) {
			$scripts_registered[] = wp_register_script( $k, $v, array( 'jquery' ), WPJM_VERSION, true );
		}

		return $scripts_registered;
	}

	/**
	 * loop through array of styles and register them
	 *
	 * @param $styles array(slug => url)
	 *
	 * @return array|bool
	 */
	public static function register_styles( $styles ) {
		if ( empty( $styles ) || gettype( $styles ) != 'array' ) {
			return false;
		}

		$styles_registered = array();
		foreach ( $styles as $k => $v ) {
			$styles_registered[] = wp_register_style( $k, $v, false, WPJM_VERSION );
		}

		return $styles_registered;
	}

	/**
	 * clear_local_storage
	 *
	 * @description: sets a wp option to set a mark that we need to clear localstorage
	 * @since 3.5
	 * @created: 03/20/2016
	 */
	public static function clear_local_storage( $post_id, $post, $update ) {
		// Do nothing if this is a auto-draft, revision, etc.
		if ( ! $update ) {
			return;
		}
		set_transient( WPJM_NEEDS_REFRESH_CACHE_LABEL, 1 );
	}

	public static function clear_local_storage_after_media_upload( $file ) {
		set_transient( WPJM_NEEDS_REFRESH_CACHE_LABEL, 1 );

		return $file;
	}

	/**
	 * ajax_clear_local_storage
	 *
	 * Ajax function to clear local storage. Used when attachments are added or edited.
	 */
	public static function ajax_clear_local_storage() {
		set_transient( WPJM_NEEDS_REFRESH_CACHE_LABEL, 1 );
		wp_die();
	}

	/**
	 * ajax_clear_local_storage_script()
	 *
	 * Makes an ajax request (action: clear local storage) when attachments are added or edited.
	 *
	 * Connects with wp.Uploader and wp.media to override some event listeners to call the ajax function.
	 *
	 */
	public static function ajax_clear_local_storage_script() {
		?>
	<script>
	  var ajax_clear = function () {
		jQuery.post(wp.ajax.settings.url, {action: 'clear_local_storage'}, function () {
		  wpjm.wpjm_refresh();
		});
	  };

	  if (wp.Uploader) {
		(function (uploader) {
		  jQuery.extend(uploader.prototype, {
			success: function (file_attachment) {
			  ajax_clear();
			}
		  });
		})(wp.Uploader);
	  }

	  if (wp.media) {
		(function (media) {
		  jQuery.extend(media.view.Attachment.Details.prototype, {
			updateSetting: function (event) {
			  var $setting = $(event.target).closest('[data-setting]'),
				setting, value;
			  if (!$setting.length) {
				return;
			  }
			  setting = $setting.data('setting');
			  value = event.target.value;
			  if (this.model.get(setting) !== value) {
				this.save(setting, value);
				ajax_clear();
			  }
			}
		  });
		})(wp.media);
	  }
	</script>
		<?php
	}

	/**
	 *  loads scripts on admin pages
	 *
	 * @description:
	 * @since 3.0
	 * @created: 12/12/12
	 **/
	public static function admin_enqueue_scripts() {
		// jquery ui - sortable
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-widget' );
		wp_enqueue_script( 'wpjm-admin-js' );
	}

	/**
	 * loads scripts on front-end pages
   *
   * @description:
   * @since 3.6.1
   * @created: 1/1/2018
	 */
	public static function wpjm_frontend_scripts() {

	}

	/**
	 * loads scripts required to make wpjm work (front and back-end)
   *
	 */
	public static function enqueue_scripts() {
		wp_enqueue_style( 'wpjm-css' );

		$load_script = false;
		if ( 'wpAdminBar' == self::$options['position'] ) {
			if ( is_admin_bar_showing() ) {
				$load_script = true;
			}
		} else {
			$load_script = true;
		}

		if ( isset( self::$options['useChosen'] ) && 'true' == self::$options['useChosen'] ) {
			if ( $load_script ) {
				wp_enqueue_script( 'wpjm-chosenjs' );
			}

			if ( 'wpAdminBar' == self::$options['position'] ) {
				if ( is_admin_bar_showing() ) {
					wp_enqueue_style( 'chosencss-wpadminbar' );
				}
			} else {
				wp_enqueue_style( 'chosencss' );
				wp_enqueue_style( 'chosencss-wpadminbar' );
			}
		}

		if ( isset( self::$options['useShortcut'] ) && 'true' == self::$options['useShortcut'] ) {
			if ( $load_script ) {
				wp_enqueue_script( 'wpjm-jquery-hotkeys' );
			}
		}

		if ( $load_script ) {
			wp_enqueue_script( 'wpjm-main-js' );
		}

	}


	/**
	 * plugin_action_links
	 *
	 * @description: adds "settings" link on plugins page
	 * @since: 3.0
	 * @created: 12/12/12
	 **/
	public static function plugin_action_links( $links, $file ) {
		static $this_plugin;
		if ( ! $this_plugin ) {
			$this_plugin = plugin_basename( __FILE__ );
		}

		if ( $file == $this_plugin ) {
			$settings_link = '<a href="options-general.php?page=wpjm-options">' . __( 'Settings' ) . '</a>';
			array_unshift( $links, $settings_link );
		}

		return $links;
	}


	/**
	 *  inject_styles
	 *
	 *  Styles that contain variables from settings; injected into wp_print_styles or admin_print_styles
	 *
	 * @description:
	 * @since: 3.0
	 * @created: 12/12/12
	 **/
	public static function inject_styles() {
		?>
	<style type='text/css'>
		<?php
		if ( 'wpAdminBar' == self::$options['position'] ) {
			if ( is_admin_bar_showing() ) {

			}
		} else {
			?>
	  #jump_menu {
		<?php
		echo self::$options['position'] . ': ' . ( 'top' == self::$options['position'] ? ( is_admin_bar_showing() ? '28px' : '0' ) : '0' ) . ";\n";
		echo 'background: #' . self::$options['backgroundColor'] . ";\n";
		echo 'color: #' . self::$options['fontColor'] . ";\n";
		echo 'border-' . ( 'top' == self::$options['position'] ? 'bottom' : 'top' ) . ': 2px solid #' . self::$options['borderColor'] . ";\n";
		?>
	  }

	  #jump_menu p a:link,
	  #jump_menu p a:visited,
	  #jump_menu p a:hover {
		color: <?php echo '#' . self::$options['linkColor']; ?>;
	  }

	  #jump_menu p.jm_credits img.wpjm_logo {
		<?php
		echo ( isset( self::$options['logoWidth'] ) ? 'width: ' . self::$options['logoWidth'] . 'px;' : 'width: auto;' );
		?>
	  }

	  #jump_menu .chosen-container .post-id {
		float: <?php echo ( isset( self::$options['chosenTextAlign'] ) && 'right' != self::$options['chosenTextAlign'] ? 'right' : 'none' ); ?> !important;
	  }

	  body {
		<?php
		echo ( 'top' == self::$options['position'] ? 'padding-top: 42px !important;' : 'padding-bottom: 42px !important;' );
		?>
	  }

		<?php
		// #footer style if position = footer
		echo ( 'bottom' == self::$options['position'] ? '#footer { bottom: 42px !important; }' : '' );
		}

			?>

	  #wpadminbar #wp-admin-bar-top-secondary #wp-admin-bar-wp-jump-menu .chosen-container * {
		text-align: <?php echo ( isset( self::$options['chosenTextAlign'] ) ? self::$options['chosenTextAlign'] : 'right' ); ?> !important;
	  }

	  #wp-admin-bar-wp-jump-menu span.loader {
		background: transparent url(<?php echo WPJM__PLUGIN_URL; ?>/assets/images/ajax-loader.gif) no-repeat center center;
	  }
	</style>
	<!--[if IE 6]>
	<style type='text/css'>
	  #jump_menu {
		position: relative;
	  }

	  #jump_menu_clear {
		display: none;
	  }
	</style>
	<![endif]-->

		<?php
	}


	/**
	 *  admin_bar_menu
	 *
	 * @description: Adds the jump-menu into the admin toolbar
	 * @since: 3.0
	 * @created: 12/12/12
	 **/
	public static function admin_bar_menu() {
		global $wp_admin_bar;

		if ( is_admin_bar_showing() ) {
			$wp_admin_bar->add_menu(
				array(
					'id'     => 'wp-jump-menu',
					'parent' => 'top-secondary',
					'title'  => self::$options['title'],
					'meta'   => array(
						'html' => '<span class="loader"></span>',
					),
				)
			);

		}
	}

	/**
	 * @deprecated
   *
   * for older versions of WordPress you could load the menu in a bar on the top or bottom of the page
	 *
	 * @description:
	 * @since: 3.0
	 * @created: 12/12/12
	 **/
	public static function wpjm_footer() {
		echo '<div id="jump_menu">';
		echo '<p class="wpjm_need_help">';

		echo '<span class="wpjm-logo-title">' . self::$options['title'] . '</span>';
		// Jump to page edit
    $wpjm_menu = new WPJM_Select_Menu();
		echo $wpjm_menu->get_menu();

		echo '</p>';
		echo '<p class="jm_credits">';
		echo( ! empty( self::$options['logoIcon'] ) ? '<a href="' . get_bloginfo( 'url' ) . '"><img class="wpjm_logo" src="' . self::$options['logoIcon'] . '" alt="" /></a>' : '' );
		echo self::$options['message'];
		echo '</p>';
		?>
    <script>
      jQuery(document).ready(function ($) {

        <?php
        if ( isset( self::$options['showID'] ) && 'true' == self::$options['showID'] ) {
          if ( isset( self::$options['useChosen'] ) && 'true' == self::$options['useChosen'] ) {

          } else {
            ?>
            jQuery('#wp-pdd').find('option').each(function (i) {
              if (jQuery(this).attr('data-post-id')) {
              jQuery(this).append(' (' + jQuery(this).attr('data-post-id') + ') ');
              }
            });
            <?php
          }
        }
        ?>

        <?php
        if ( isset( self::$options['useChosen'] ) && 'true' == self::$options['useChosen'] ) {
          ?>
          jQuery('#wp-pdd').bind('liszt:ready', function () {
            jQuery('ul.chosen-results li').prepend('<span class="front-end"></span>');
          });
          <?php
        }
        ?>

        jQuery('#wp-pdd').on('change', function () {
          window.location = this.value;
        })
        <?php
        if ( isset( self::$options['useChosen'] ) && 'true' == self::$options['useChosen'] ) {
          ?>
        .customChosen({
        position:        "<?php echo esc_js( self::$options['position'] ); ?>",
        search_contains: true
        })<?php } ?>;

      });
    </script>
		<?php
		echo '</div>';
	}

	/**
	 * wpjm_get_page_title()
	 *
	 * Utility function to truncate page titles
	 *
	 * @param $pd_title
	 *
	 * @return string
	 */
	public static function get_page_title( $pd_title ) {
		if ( strlen( $pd_title ) > 50 ) {
			return substr( $pd_title, 0, 50 ) . '...';
		} else {
			return $pd_title;
		}
	}

	/**
	 *  plugin_activation
	 *
	 * @description: Installs the options
	 * @since: 3.0
	 * @created: 12/12/12
	 **/
	public static function plugin_activation() {

		// Populate with default values
		if ( get_option( 'wpjm_position' ) ) {

			$new_post_types = array(
				'page' => array(
					'show'       => '1',
					'sortby'     => 'menu_order',
					'sort'       => 'ASC',
					'poststatus' => array( 'publish', 'draft' ),
				),
				'post' => array(
					'show'       => '1',
					'sortby'     => 'date',
					'sort'       => 'DESC',
					'poststatus' => array( 'publish', 'draft' ),
				),
			);

			// Get old custom post types option, append to new variable
			$custom_post_types = get_option( 'wpjm_customPostTypes' );
			$cpt_arr           = explode( ',', $custom_post_types );
			if ( ! empty( $cpt_arr ) ) {
				if ( is_array( $cpt_arr ) ) {
					foreach ( $cpt_arr as $cpt ) {
						$new_post_types[ $cpt ] = array(
							'show'        => '1',
							'sortby'      => 'menu_order',
							'sort'        => 'ASC',
							'numberposts' => '-1',
							'poststatus'  => array( 'publish', 'draft' ),
						);
					}
				} else {
					$new_post_types[ $cpt_arr ] = array(
						'show'        => '1',
						'sortby'      => 'menu_order',
						'sort'        => 'ASC',
						'numberposts' => '-1',
						'poststatus'  => array( 'publish', 'draft' ),
					);
				}
			}

			$arr = array(
				'position'        => get_option( 'wpjm_position' ),
				'useChosen'       => 'true',
				'useShortcut'     => 'false',
				'chosenTextAlign' => 'left',
				'showID'          => 'false',
				'showPostType'    => 'false',
				'showaddnew'      => 'true',
				'frontend'        => 'true',
				'frontEndJump'    => 'true',
				'backgroundColor' => get_option( 'wpjm_backgroundColor' ),
				'fontColor'       => get_option( 'wpjm_fontColor' ),
				'borderColor'     => get_option( 'wpjm_borderColor' ),
				'postTypes'       => $new_post_types,
				'logoIcon'        => get_option( 'wpjm_logoIcon' ),
				'linkColor'       => get_option( 'wpjm_linkColor' ),
				'message'         => get_option( 'wpjm_message' ),
				'title'           => 'WP Jump Menu &raquo;',
				'statusColors'    => array(
					'publish'    => '',
					'pending'    => '',
					'draft'      => '',
					'auto-draft' => '',
					'future'     => '',
					'private'    => '',
					'inherit'    => '',
					'trash'      => '',
				),
			);

			update_option( 'wpjm_options', $arr );

			delete_option( 'wpjm_position' );
			delete_option( 'wpjm_sortpagesby' );
			delete_option( 'wpjm_sortpages' );
			delete_option( 'wpjm_sortpostsby' );
			delete_option( 'wpjm_sortposts' );
			delete_option( 'wpjm_numberposts' );
			delete_option( 'wpjm_backgroundColor' );
			delete_option( 'wpjm_fontColor' );
			delete_option( 'wpjm_borderColor' );
			delete_option( 'wpjm_customPostTypes' );
			delete_option( 'wpjm_logoIcon' );
			delete_option( 'wpjm_logoWidth' );
			delete_option( 'wpjm_linkColor' );
			delete_option( 'wpjm_message' );

		} else {

			// If this is a new install, set the default options
			if ( empty( self::$options ) ) {
				$arr = array(
					'position'        => 'wpAdminBar',
					'useChosen'       => 'true',
					'useShortcut'     => 'false',
					'chosenTextAlign' => 'left',
					'showID'          => 'false',
					'showPostType'    => 'false',
					'showaddnew'      => 'true',
					'frontend'        => 'true',
					'frontEndJump'    => 'true',
					'backgroundColor' => 'e0e0e0',
					'fontColor'       => '787878',
					'borderColor'     => '666666',
					'postTypes'       => array(
						'page' => array(
							'show'        => '1',
							'sortby'      => 'menu_order',
							'sort'        => 'ASC',
							'numberposts' => '0',
							'poststatus'  => array( 'publish', 'draft' ),
						),
						'post' => array(
							'show'        => '1',
							'sortby'      => 'date',
							'sort'        => 'DESC',
							'numberposts' => '-1',
							'poststatus'  => array( 'publish', 'draft' ),
						),
					),
					'logoIcon'        => 'http://www.krillwebdesign.com/img/jk-og.png',
					'linkColor'       => '1cd0d6',
					'message'         => "Brought to you by <a href='http://www.krillwebdesign.com/' target='_blank'>Krill Web Design</a>.",
					'title'           => 'WP Jump Menu &raquo;',
					'statusColors'    => array(
						'publish'    => '',
						'pending'    => '',
						'draft'      => '',
						'auto-draft' => '',
						'future'     => '',
						'private'    => '',
						'inherit'    => '',
						'trash'      => '',
					),
				);
				update_option( 'wpjm_options', $arr );
			} else {

				// Not a new install, but not an upgrade from old version, update post type status'
				if ( ! isset( self::$options['postTypes']['post']['poststatus'] ) ) {
					foreach ( self::$options['postTypes'] as $key => $value ) {
						self::$options['postTypes'][ $key ]['poststatus'] = array( 'publish', 'draft' );
					}
					update_option( 'wpjm_options', self::$options );
				}

				// Remove logo width if it is set
				if ( isset( self::$options['logoWidth'] ) ) {
					unset( self::$options['logoWidth'] );
					update_option( 'wpjm_options', self::$options );
				}

				// Add title if it is not set
				if ( ! isset( self::$options['title'] ) ) {
					self::$options['title'] = 'WP Jump Menu &raquo;';
					update_option( 'wpjm_options', self::$options );
				}
			}
		}

		update_option( 'wpjm_version', WPJM_VERSION );

		return true;

	}

}
