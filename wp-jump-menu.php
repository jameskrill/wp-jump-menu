<?php
/*
Plugin Name: WP Jump Menu
Plugin URI: http://wpjumpmenu.com
Description: Creates a drop-down menu (jump menu) in a bar across the top or bottom of the screen that makes it easy to jump right to a page, post, or custom post type in the admin area to edit.
Version: 3.6.2
Author: Jim Krill
Author URI: http://krillwebdesign.com
License: GPL
Copyright: Jim Krill
*/

/**
 * Class WpJumpMenu
 */
class WpJumpMenu {
	var $dir,
		$path,
		$version,
		$upgrade_version,
		$options,
		$current_user,
		$options_page,
		$menu_cache_label,
		$menu_refresh_cache_label;

	/**
	 *  Constructor
	 *
	 * @description:
	 * @since 3.0
	 * @created: 12/12/12
	 **/
	function __construct() {

		// vars
		$this->path                     = plugin_dir_path( __FILE__ );
		$this->dir                      = plugins_url( '', __FILE__ );
		$this->version                  = '3.6.3';
		$this->upgrade_version          = '';
		$this->options                  = get_option( 'wpjm_options' );
		$this->menu_cache_label         = "wpjm_menu";
		$this->menu_refresh_cache_label = "wpjm_needs_refresh";

		// set text domain
		load_plugin_textdomain( 'wp-jump-menu', false, basename( dirname( __FILE__ ) ) . '/languages' );

		// actions
		add_action( 'init', array( $this, 'init' ) );

		// Activation Hook
		register_activation_hook( __FILE__, array( $this, 'wpjm_install' ) );

		return true;
	}


	/**
	 *  Init
	 * @description:
	 * @since 3.0
	 * @created: 12/12/12
	 **/
	function init() {

		global $wp_version;

		// Do not load if this is the network admin
		if ( is_network_admin() ) {
			return false;
		}

		// Permission Testing
		$this->current_user = wp_get_current_user();
		if ( ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

		// actions

		// Clear LocalStorage on save
		foreach ( $this->options['postTypes'] as $key => $val ) {
			add_action( 'save_post_' . $key, array( $this, 'clear_local_storage' ), 10, 3 );
		}
		// fires when attachments are edited
    add_action( 'edit_attachment', array( $this, 'clear_local_storage_after_media_upload') );
		// should fire on ajax "clear_local_storage" action
		add_action( 'wp_ajax_clear_local_storage', array( $this, 'ajax_clear_local_storage' ) );

		// Add the admin menu option under Settings
		if ( current_user_can( 'manage_options' ) ) {
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		}

		// Load Scripts
		add_action( 'admin_print_scripts', array( $this, 'wpjm_admin_scripts' ) );
		add_action( 'admin_print_footer_scripts', array( $this, 'ajax_clear_local_storage_script' ) );
		add_action( 'admin_print_scripts-settings_page_wpjm-options', array( $this, 'wpjm_settings_scripts' ) );
		add_action( 'admin_print_styles', array( $this, 'wpjm_css' ) );
		add_action( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );
		add_action( 'wp_enqueue_scripts', array( $this, 'wpjm_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'wpjm_scripts' ) );

		if ( $this->options['position'] == 'wpAdminBar' ) {
			if ( is_admin_bar_showing() ) {
				add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 25 );
				add_action( 'wp_print_styles', array( $this, 'wpjm_css' ) );
			}
		} else {
			if ( isset( $this->options['frontend'] ) && $this->options['frontend'] == 'true' ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'wpjm_frontend_scripts' ) );
				add_action( 'wp_footer', array( $this, 'wpjm_footer' ) );
			}
			add_action( 'admin_footer', array( $this, 'wpjm_footer' ) );
			add_action( 'wp_print_styles', array( $this, 'wpjm_css' ) );
		}

		// Load menu using ajax request
		add_action( 'wp_ajax_wpjm_menu', array( $this, 'wpjm_menu' ) );

		// Options page settings form
		add_action( 'admin_init', 'wpjm_admin_init' );

		// register scripts
		$scripts = array(
			'wpjm-admin-js'           => $this->dir . '/assets/js/wpjm-admin.js',
			'wpjm-main-js'            => $this->dir . '/assets/js/wpjm-main.js',
			'wpjm-jquery-colorpicker' => $this->dir . '/assets/js/colorpicker/js/colorpicker.js',
			'wpjm-chosenjs'           => $this->dir . '/assets/js/chosen/custom.chosen.jquery.js',
			'wpjm-jquery-hotkeys'     => $this->dir . '/assets/js/jquery/jquery.hotkeys.js'
		);

		foreach ( $scripts as $k => $v ) {
			wp_register_script( $k, $v, array( 'jquery' ), $this->version, true );
		}

		// localize main script
		$post_id = isset( $_GET['post'] ) ? $_GET['post'] : 0;
		$post_id = isset( $_GET['page_id'] ) ? $_GET['page_id'] : $post_id;
		$post_id = isset( $_GET['attachment_id'] ) ? $_GET['attachment_id'] : $post_id;
		$post_id = isset( $_GET['p'] ) ? $_GET['p'] : $post_id;
		wp_localize_script( 'wpjm-main-js', 'wpjm_opt', array(
			'baseUrl'       => admin_url( 'admin-ajax.php' ),
			'useChosen'     => isset( $this->options['useChosen'] ) && $this->options['useChosen'] == 'true',
			'position'      => esc_js( $this->options['position'] ),
			'reloadText'    => __( 'Refresh Jump Menu' ),
			'currentPageID' => $post_id,
			'useShortcut'   => isset( $this->options['useShortcut'] ) && $this->options['useShortcut'] == 'true',
      'isAdmin'       => is_admin()
		) );

		// register styles
		$styles = array(
			'wpjm-colorpicker-css' => $this->dir . '/assets/js/colorpicker/css/colorpicker.css',
			'chosencss'            => $this->dir . '/assets/js/chosen/chosen.css',
			'chosencss-wpadminbar' => $this->dir . '/assets/js/chosen/chosen-wpadmin.css',
			'wpjm-settings-css'    => $this->dir . '/assets/css/wpjm-settings.css',
			'wpjm-css'             => $this->dir . '/assets/css/wpjm.css'
		);

		foreach ( $styles as $k => $v ) {
			wp_register_style( $k, $v, false, $this->version );
		}


		// Upgrade
		$current_version = get_option( 'wpjm_version' );
		if ( empty( $current_version ) || $current_version < $this->version ) {

			// initiate install/update
			$this->wpjm_install();

		}

	}

	/**
	 * clear_local_storage
	 *
	 * @descrtiption: sets a wp option to set a mark that we need to clear localstorage
	 * @since 3.5
	 * @created: 03/20/2016
	 */
	function clear_local_storage( $post_id, $post, $update ) {
		// Do nothing if this is a auto-draft, revision, etc.
		if ( ! $update ) {
			return;
		}
		set_transient( $this->menu_refresh_cache_label, 1 );
	}

	function clear_local_storage_after_media_upload( $file ) {
		set_transient( $this->menu_refresh_cache_label, 1 );
		return $file;
	}

	/**
	 * ajax_clear_local_storage
   *
   * Ajax function to clear local storage. Used when attachments are added or edited.
	 */
	function ajax_clear_local_storage() {
		set_transient( $this->menu_refresh_cache_label, 1 );
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
	function ajax_clear_local_storage_script() {
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
              var $setting = jQuery(event.target).closest('[data-setting]'),
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
	 *  admin_menu
	 *
	 * @description:
	 * @since 1.0.0
	 * @created: 12/12/12
	 **/
	function admin_menu() {
		$this->options_page = add_options_page( 'Jump Menu Options',
			'Jump Menu Options',
			'edit_posts',
			'wpjm-options',
			array( $this, 'wpjm_options_page' )
		);
	}


	/**
	 *  wpjm_admin_scripts
	 *
	 * @description:
	 * @since 3.0
	 * @created: 12/12/12
	 **/
	function wpjm_admin_scripts() {
		// jquery ui - sortable
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-widget' );
		wp_enqueue_script( 'wpjm-admin-js' );
	}

	function wpjm_frontend_scripts() {

	}

	function wpjm_scripts() {
		wp_enqueue_style( 'wpjm-css' );

		$loadScript = false;
		if ( $this->options['position'] == 'wpAdminBar' ) {
			if ( is_admin_bar_showing() ) {
				$loadScript = true;
			}
		} else {
			$loadScript = true;
		}

		if ( isset( $this->options['useChosen'] ) && $this->options['useChosen'] == 'true' ) {
			if ( $loadScript == true ) {
				wp_enqueue_script( 'wpjm-chosenjs' );
			}

			if ( $this->options['position'] == 'wpAdminBar' ) {
				if ( is_admin_bar_showing() ) {
					wp_enqueue_style( 'chosencss-wpadminbar' );
				}
			} else {
				wp_enqueue_style( 'chosencss' );
				wp_enqueue_style( 'chosencss-wpadminbar' );
			}
		}

		if ( isset( $this->options['useShortcut'] ) && $this->options['useShortcut'] == 'true' ) {
			if ( $loadScript == true ) {
				wp_enqueue_script( 'wpjm-jquery-hotkeys' );
			}
		}


		if ( $loadScript == true ) {
			wp_enqueue_script( 'wpjm-main-js' );
		}

	}

	function wpjm_settings_scripts() {
		// Colorpicker
		wp_enqueue_script( 'wpjm-jquery-colorpicker' );
		wp_enqueue_style( 'wpjm-colorpicker-css' );

		// Settings page CSS
		wp_enqueue_style( 'wpjm-settings-css' );
	}


	/**
	 *  wpjm_options_page
	 *
	 * @description: the options page
	 * @since: 3.0
	 * @created: 12/12/12
	 **/
	function wpjm_options_page() {

		// Update success message
		if ( isset( $_POST['save_post_page_values'] ) ) {
			$message = "Options updated successfully!";
		}

		if ( ! empty( $message ) ) :
			?>
      <div id="message" class="updated"><p><?php echo $message; ?></p></div>
		  <?php
		endif;
		?>

    <div class="wrap">
      <div id="icon-options-general" class="icon32">
        <br/>
      </div>
      <h2>WP Jump Menu <?php echo $this->version; ?></h2>

      <form action="options.php" method="post" id="wpjm-options-form">
				<?php settings_fields( 'wpjm_options' ); ?>
        <div class="wpjm-post-types-wrapper">
					<?php do_settings_sections( 'wpjm' ); ?>
        </div>
        <p class="submit">
          <input type="Submit" type="submit" value="<?php esc_attr_e( 'Save Changes' ); ?>"
                 class="button button-primary"/>
        </p>
        <div class="wpjm-additional-settings-wrapper">
					<?php do_settings_sections( 'wpjm-2' ); ?>
        </div>
        <p class="submit">
          <input type="Submit" type="submit" value="<?php esc_attr_e( 'Save Changes' ); ?>"
                 class="button button-primary"/>
        </p>
      </form>
    </div>

		<?php
	}


	/**
	* plugin_action_links
	*
	* @description: adds "settings" link on plugins page
	* @since: 3.0
	* @created: 12/12/12
	**/
	function plugin_action_links( $links, $file ) {
		static $this_plugin;
		if ( ! $this_plugin ) {
			$this_plugin = plugin_basename( __FILE__ );
		}

		if ( $file == $this_plugin ) {
			$settings_link = '<a href="options-general.php?page=wpjm-options">' . __( "Settings" ) . '</a>';
			array_unshift( $links, $settings_link );
		}

		return $links;
	}


	/**
	*  wpjm_css
  *
  *  Styles that contain variables from settings; injected into wp_print_styles or admin_print_styles
	*
	*  @description:
	*  @since: 3.0
	*  @created: 12/12/12
	**/
	function wpjm_css() {
		?>
    <style type='text/css'>
      <?php
			if ( $this->options['position'] == 'wpAdminBar' ) {
				if ( is_admin_bar_showing() ) {

				}
			} else {
				?>
      #jump_menu {
      <?php
			echo $this->options['position'] . ": " . ( $this->options['position'] == 'top' ? ( is_admin_bar_showing() ? "28px" : "0" ) : "0" ) . ";\n";
			echo "background: #" . $this->options['backgroundColor'] . ";\n";
			echo "color: #" . $this->options['fontColor'] . ";\n";
			echo "border-" . ( $this->options['position'] == 'top' ? 'bottom' : 'top' ) . ": 2px solid #" . $this->options['borderColor'] . ";\n";
			?>
      }

      #jump_menu p a:link,
      #jump_menu p a:visited,
      #jump_menu p a:hover {
        color: <?php echo "#" . $this->options['linkColor']; ?>;
      }

      #jump_menu p.jm_credits img.wpjm_logo {
      <?php
			echo ( isset( $this->options['logoWidth'] ) ? 'width: ' . $this->options['logoWidth'] . 'px;' : 'width: auto;' );
			?>
      }

      #jump_menu .chosen-container .post-id {
        float: <?php echo ( isset( $this->options['chosenTextAlign'] ) && $this->options['chosenTextAlign'] != "right" ? "right" : 'none' ); ?> !important;
      }

      body {
      <?php
			echo ( $this->options['position'] == 'top' ? 'padding-top: 42px !important;' : 'padding-bottom: 42px !important;' );
			?>
      }

      <?php
      // #footer style if position = footer
      echo ( $this->options['position'] == 'bottom' ? '#footer { bottom: 42px !important; }' : '' );
		}

		?>

      #wpadminbar #wp-admin-bar-top-secondary #wp-admin-bar-wp-jump-menu .chosen-container * {
        text-align: <?php echo ( isset( $this->options['chosenTextAlign'] ) ? $this->options['chosenTextAlign'] : 'right' ); ?> !important;
      }

      #wp-admin-bar-wp-jump-menu span.loader {
        background: transparent url(<?php echo $this->dir; ?>/assets/images/ajax-loader.gif) no-repeat center center;
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
	*  @description: Adds the jump-menu into the admin toolbar
	*  @since: 3.0
	*  @created: 12/12/12
	**/
	function admin_bar_menu() {
		global $wp_admin_bar;

		if ( is_admin_bar_showing() ) {
			$wp_admin_bar->add_menu( array(
				'id'     => 'wp-jump-menu',
				'parent' => 'top-secondary',
				'title'  => $this->options['title'],
				'meta'   => array(
					'html' => '<span class="loader"></span>'
				)
			) );

		}
	}

	/**
	*  wpjm_footer
	*
	*  @description:
	*  @since: 3.0
	*  @created: 12/12/12
	**/
	function wpjm_footer() {
		echo '<div id="jump_menu">';
		echo '<p class="wpjm_need_help">';

		echo '<span class="wpjm-logo-title">' . $this->options['title'] . '</span>';
		// Jump to page edit
		echo $this->wpjm_page_dropdown();

		echo '</p>';
		echo '<p class="jm_credits">';
		echo( ! empty( $this->options['logoIcon'] ) ? '<a href="' . get_bloginfo( 'url' ) . '"><img class="wpjm_logo" src="' . $this->options['logoIcon'] . '" alt="" /></a>' : '' );
		echo $this->options['message'];
		echo '</p>';
		?>
    <script>
      jQuery(document).ready(function ($) {

				<?php
				if ( isset( $this->options['showID'] ) && $this->options['showID'] == "true" ) {
				  if ( isset( $this->options['useChosen'] ) && $this->options['useChosen'] == 'true') {

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
        if ( isset( $this->options['useChosen'] ) && $this->options['useChosen'] == 'true' ) {
          ?>
          jQuery('#wp-pdd').bind('liszt:ready', function () {
            jQuery('ul.chosen-results li').prepend('<span class="front-end"></span>');
          });
				  <?php
        }
        ?>

        jQuery('#wp-pdd').on('change', function () {
          window.location = this.value;
        })<?php if ( isset( $this->options['useChosen'] ) && $this->options['useChosen'] == 'true' ) { ?>.customChosen({
          position: "<?php echo esc_js( $this->options['position'] ); ?>",
          search_contains: true
        })<?php } ?>;
      });
    </script>
		<?php
		echo '</div>';
	}

	/**
	 * wpjm_menu()
	 *
	 * Ajax function to load the menu
	 *
	 * @echo html select menu
	 */
	function wpjm_menu() {

		global $post_id;

		$post_id = 0;
		if ( isset( $_GET['post_id'] ) ) {
			$post_id = $_GET['post_id'];
		}

		$wpjm_refresh = isset( $_GET['refresh'] ) ? $_GET['refresh'] : false;
		$needs_refresh = get_transient( $this->menu_refresh_cache_label );

		// If we need a non-cached version...
		if ( $needs_refresh == 1 || $wpjm_refresh == true ) {
			$wpjm_menu = $this->wpjm_page_dropdown( false );

			if ( $needs_refresh == 1 ) {
				delete_transient( $this->menu_refresh_cache_label );
			}

		} else {

		  // Otherwise load the cached version
			$wpjm_menu = $this->wpjm_page_dropdown( true );

		}

		echo $wpjm_menu;

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			wp_die();
		} else {
			die;
		}
	}

	/**
	 *  wpjm_page_dropdown
	 *
	 * @description: the main function to display the drop-down menu
	 * @since: 3.0
	 * @created: 12/12/12
	 **/
	function wpjm_page_dropdown( $cached = false ) {

		global $current_user, $post, $post_id, $options;

		$options = $this->options;

		// Is this needed?
		require_once( 'assets/WpjmWalkerClass.php' );

		// Get Custom Post Types settings (will iterate through later)
		$custom_post_types = $this->options['postTypes'];

		// Set post status colors
		$status_color = array(
			'publish'    => ( ! empty( $this->options['statusColors']['publish'] ) ? '#' . $this->options['statusColors']['publish'] : '' ),
			'pending'    => ( ! empty( $this->options['statusColors']['pending'] ) ? '#' . $this->options['statusColors']['pending'] : '' ),
			'draft'      => ( ! empty( $this->options['statusColors']['draft'] ) ? '#' . $this->options['statusColors']['draft'] : '' ),
			'auto-draft' => ( ! empty( $this->options['statusColors']['auto-draft'] ) ? '#' . $this->options['statusColors']['auto-draft'] : '' ),
			'future'     => ( ! empty( $this->options['statusColors']['future'] ) ? '#' . $this->options['statusColors']['future'] : '' ),
			'private'    => ( ! empty( $this->options['statusColors']['private'] ) ? '#' . $this->options['statusColors']['private'] : '' ),
			'inherit'    => ( ! empty( $this->options['statusColors']['inherit'] ) ? '#' . $this->options['statusColors']['inherit'] : '' ),
			'trash'      => ( ! empty( $this->options['statusColors']['trash'] ) ? '#' . $this->options['statusColors']['trash'] : '' )
		);

		$wpjm_string = '';

		// Start echoing the select menu
		if ( isset( $this->options['useChosen'] ) && $this->options['useChosen'] == 'true' ) {
			$wpjm_string .= '<select id="wp-pdd" data-placeholder="- Select to Edit -" class="chosen-select">';
			$wpjm_string .= '<option></option>';
		} else {
			$wpjm_string .= '<select id="wp-pdd">';
			$wpjm_string .= '<option>-- Select to Edit --</option>';
		}

		$wpjm_string = apply_filters( 'wpjm-filter-beginning-of-list', $wpjm_string );

		// Loop through custom posts types, and echo them out
		if ( $custom_post_types ) {

			$wpjm_cpts = $custom_post_types; // should be array
			if ( $wpjm_cpts ) {

				// Loop through each post type as $key, $value
				// --------------------------------------------------------------------------------------
				// The $key is the name of the post type: i.e. 'page', 'post', or 'custom_post_type_name'
				// The $value is an array of options
				//		$value['sortby']
				//		$value['sort']
				//		$value['numberposts']
				// --------------------------------------------------------------------------------------
				foreach ( $wpjm_cpts as $key => $value ) {

					// Set variables
					$wpjm_cpt         = $key;                        // name of the post type
					$post_type_object = get_post_type_object( $wpjm_cpt );
					$sortby           = $value['sortby'];                // orderby value
					$sort             = $value['sort'];                    // order value
					$numberposts      = $value['numberposts'];    // number of posts to display
					$showdrafts       = ( isset( $value['showdrafts'] ) ? $value['showdrafts'] : '' );        // show drafts, true or false
					$post_status      = $value['poststatus'];
					$postmimetype     = array();
					if ( isset( $value['postmimetypes'] ) && is_array( $value['postmimetypes'] ) ) {
						foreach ( $value['postmimetypes'] as $mime ) {
							switch ( $mime ) {
								case 'images':
									$postmimetype[] = 'image/jpeg';
									$postmimetype[] = 'image/png';
									$postmimetype[] = 'image/gif';
									$postmimetype[] = 'image';
									break;

								case 'videos':
									$postmimetype[] = 'video/mpeg';
									$postmimetype[] = 'video/mp4';
									$postmimetype[] = 'video/quicktime';
									$postmimetype[] = 'video';
									break;

								case 'audio':
									$postmimetype[] = 'audio/mpeg';
									$postmimetype[] = 'audio/mp3';
									$postmimetype[] = 'audio';

								case 'documents':
									$postmimetype[] = 'text/csv';
									$postmimetype[] = 'text/plain';
									$postmimetype[] = 'text/xml';
									$postmimetype[] = 'text';
									break;

								default:
									$postmimetype = 'all';
									break;
							}
						}

						if ( ! is_array( $postmimetype ) ) {
							$postmimetype = '';
						}
					}

					// Get the labels for this post type
					$cpt_obj    = get_post_type_object( $wpjm_cpt );
					$cpt_labels = $cpt_obj->labels;

					// Set the iterator to zero
					$pd_i = 0;

					// If this is not hierarchical, get list of posts and display the <option>s
					if ( ! is_post_type_hierarchical( $wpjm_cpt ) ) {

						// Get Posts
						$args = array(
							'orderby'        => $sortby,
							'order'          => $sort,
							'posts_per_page' => $numberposts,
							'post_type'      => $wpjm_cpt,
							'post_status'    => ( is_array( $post_status ) ? ( in_array( 'any', $post_status ) ? 'any' : $post_status ) : $post_status )
						);

						if ( $wpjm_cpt == "attachment" ) {
							$args['post_status'] = "any";
						}

						if ( $wpjm_cpt == "attachment" && ! empty( $postmimetype ) ) {
							$args['post_mime_type'] = $postmimetype;
						}

						if ( $cached == false ) {
							// Manually cache results
							$pd_posts = get_posts( $args );
							set_transient( 'wpjm_menu_' . $wpjm_cpt, $pd_posts );
						} else {
							// Manually get cache
							$pd_posts = get_transient( 'wpjm_menu_' . $wpjm_cpt );
							// Unless it doesn't exist, then use get_posts
							if ( false == $pd_posts ) {
								$pd_posts = get_posts( $args );
								set_transient( 'wpjm_menu_' . $wpjm_cpt, $pd_posts );
							}
						}

						// Count the posts
						$pd_total_posts = count( $pd_posts );

						$wpjm_string .= '<optgroup label="' . $cpt_labels->name . '">';

						if ( $cpt_labels->name != 'Media' ) {

							if ( isset( $this->options['showaddnew'] ) && $this->options['showaddnew'] && current_user_can( $post_type_object->cap->edit_posts ) ) {
								$wpjm_string .= '<option value="post-new.php?post_type=';
								$wpjm_string .= $cpt_obj->name;
								$wpjm_string .= '">+ Add New ' . $cpt_labels->singular_name . ' +</option>';
							}

						}

						// Order the posts by mime/type if this is attachments
						if ( ( $wpjm_cpt == 'attachment' ) && ( $sortby == 'mime_type' ) ) {
							function mime_sort( $a, $b ) {
								return strcmp( $a->post_mime_type, $b->post_mime_type );
							}

							usort( $pd_posts, "mime_sort" );
						}

						// Loop through posts
						foreach ( $pd_posts as $pd_post ) {

							// Increase the interator by 1
							$pd_i ++;

							// Open the <option> tag
							$wpjm_string .= '<option data-permalink="' . get_permalink( $pd_post->ID ) . '" value="';
							// echo the edit link based on post ID
							$editLink    = ( is_admin() || ( ! isset( $this->options['frontEndJump'] ) || ! $this->options['frontEndJump'] ) ? get_edit_post_link( $pd_post->ID ) : get_permalink( $pd_post->ID ) );
							$wpjm_string .= $editLink;
							$wpjm_string .= '"';

							// Check to see if you are currently editing this post
							// If so, make it the selected value
							if ( ( isset( $_GET['post'] ) && ( $pd_post->ID == $_GET['post'] ) ) || ( isset( $post_id ) && ( $pd_post->ID == $post_id ) ) ) {
								$wpjm_string .= ' selected="selected"';
							}

							if ( ! current_user_can( $post_type_object->cap->edit_post, $pd_post->ID ) ) {
								$wpjm_string .= ' disabled="disabled"';
							}

							// Set the color
							if ( isset( $status_color[ $pd_post->post_status ] ) ) {
								$wpjm_string .= ' style="color: ' . $status_color[ $pd_post->post_status ] . ';"';
							}

							// If the setting to show ID's is true, show the ID in ()
							if ( ( isset( $this->options['showID'] ) && $this->options['showID'] == true ) ) {
								$wpjm_string .= ' data-post-id="' . $pd_post->ID . '"';
							}

							// If the setting to show the post type is true, show it
							if ( ( isset( $this->options['showPostType'] ) && $this->options['showPostType'] == true ) ) {
								$wpjm_string .= ' data-post-type="' . get_post_type( $pd_post->ID ) . '"';
							}


							$wpjm_string .= '>';

							// Print the post title
							$wpjm_string .= $this->wpjm_get_page_title( $pd_post->post_title );

							if ( $pd_post->post_status != 'publish' && $pd_post->post_status != 'inherit' ) {
								$wpjm_string .= ' - ' . $pd_post->post_status;
							}

							if ( $pd_post->post_type == 'attachment' ) {
								$wpjm_string .= ' (' . $pd_post->post_mime_type . ')';
							}

							if ( $pd_post->post_status == 'future' ) {
								$wpjm_string .= ' - ' . $pd_post->post_date;
							}

							// close the <option> tag
							$wpjm_string .= '</option>';
						} // foreach ($pd_posts as $pd_post)

						$wpjm_string .= '</optgroup>';

					} else {

						// If this a hierarchical post type, use the custom Walker class to create the page tree
						$orderedListWalker = new WPJM_Walker_PageDropDown();

						$wpjm_string .= '<optgroup label="' . $cpt_labels->name . '">';

						if ( isset( $this->options['showaddnew'] ) && $this->options['showaddnew'] && ( current_user_can( $post_type_object->cap->edit_posts ) || current_user_can( $post_type_object->cap->edit_pages ) ) ) {
							$wpjm_string .= '<option value="post-new.php?post_type=';
							$wpjm_string .= $cpt_obj->name;
							$wpjm_string .= '">+ Add New ' . $cpt_labels->singular_name . ' +</option>';
						}

						// Go through the non-published pages
						foreach ( $post_status as $status ) {

							if ( $status == 'publish' ) {
								continue;
							}

							// Get pages
							$args = array(
								'orderby'        => $sortby,
								'order'          => $sort,
								'posts_per_page' => $numberposts,
								'post_type'      => $wpjm_cpt,
								'post_status'    => $status
							);

							if ( $cached == false ) {
								// Manually cache results
								$pd_posts_drafts = get_posts( $args );
								set_transient( 'wpjm_menu_' . $wpjm_cpt . '_' . $status, $pd_posts_drafts );
							} else {
								// Manually get cache
								$pd_posts_drafts = get_transient( 'wpjm_menu_' . $wpjm_cpt . '_' . $status );
								// Unless it doesn't exist, then use get_posts
								if ( false == $pd_posts_drafts ) {
									$pd_posts_drafts = get_posts( $args );
									set_transient( 'wpjm_menu_' . $wpjm_cpt . '_' . $status, $pd_posts_drafts );
								}
							}


							// Loop through posts
							foreach ( $pd_posts_drafts as $pd_post ) {

								// Increase the interator by 1
								$pd_i ++;

								// Open the <option> tag
								$wpjm_string .= '<option data-permalink="' . get_permalink( $pd_post->ID ) . '" value="';
								// echo the edit link based on post ID
								$editLink    = ( is_admin() || ( ! isset( $this->options['frontEndJump'] ) || ! $this->options['frontEndJump'] ) ? get_edit_post_link( $pd_post->ID ) : get_permalink( $pd_post->ID ) );
								$wpjm_string .= $editLink;
								$wpjm_string .= '"';

								// Check to see if you are currently editing this post
								// If so, make it the selected value
								if ( ( isset( $_GET['post'] ) && ( $pd_post->ID == $_GET['post'] ) ) || ( isset( $post_id ) && ( $pd_post->ID == $post_id ) ) ) {
									$wpjm_string .= ' selected="selected"';
								}

								if ( ! current_user_can( $post_type_object->cap->edit_post, $pd_post->ID ) ) {
									$wpjm_string .= ' disabled="disabled"';
								}

								// Set the color
								if ( isset( $status_color[ $pd_post->post_status ] ) ) {
									$wpjm_string .= ' style="color: ' . $status_color[ $pd_post->post_status ] . ';"';
								}

								// If the setting to show ID's is true, show the ID in ()
								if ( ( isset( $this->options['showID'] ) && $this->options['showID'] == true ) ) {
									$wpjm_string .= ' data-post-id="' . $pd_post->ID . '"';
								}

								// If the setting to show the post type is true, show it
								if ( ( isset( $this->options['showPostType'] ) && $this->options['showPostType'] == true ) ) {
									$wpjm_string .= ' data-post-type="' . get_post_type( $pd_post->ID ) . '"';
								}

								$wpjm_string .= '>';

								// Print the post title
								$wpjm_string .= $this->wpjm_get_page_title( $pd_post->post_title );

								if ( $pd_post->post_status != 'publish' ) {
									$wpjm_string .= ' - ' . $status;
								}

								if ( $pd_post->post_status == 'future' ) {
									$wpjm_string .= ' - ' . $pd_post->post_date;
								}

								// close the <option> tag
								$wpjm_string .= '</option>';

							} // foreach ($pd_posts as $pd_post)

						}
						// Done with non-published pages
						if ( is_array( $post_status ) ) {

							if ( in_array( 'publish', $post_status ) ) {

								$args = array(
									'walker'      => $orderedListWalker,
									'post_type'   => $wpjm_cpt,
									'echo'        => 0,
									'depth'       => $numberposts,
									'sort_column' => $sortby,
									'sort_order'  => $sort,
									'title_li'    => ''
								);

								if ( $cached == false ) {
									// Manually cache results
									$pd_pages = wp_list_pages( $args );
									$pd_pages = str_replace( ' selected="selected"', '', $pd_pages );
									set_transient( 'wpjm_menu_' . $wpjm_cpt, $pd_pages );
								} else {
									// Manually get cache
									$pd_pages = get_transient( 'wpjm_menu_' . $wpjm_cpt );
									// Unless it doesn't exist, then use get_posts
									if ( false == $pd_pages || empty( $pd_pages ) ) {
										$pd_pages = wp_list_pages( $args );
										$pd_pages = str_replace( ' selected="selected"', '', $pd_pages );
										set_transient( 'wpjm_menu_' . $wpjm_cpt, $pd_pages );
									}
								}

								$wpjm_string .= $pd_pages;

							}

						} else if ( $post_status == 'publish' ) {

							$args = array(
								'walker'      => $orderedListWalker,
								'post_type'   => $wpjm_cpt,
								'echo'        => 0,
								'depth'       => $numberposts,
								'sort_column' => $sortby,
								'sort_order'  => $sort,
								'title_li'    => ''
							);

							if ( $cached == false ) {
								// Manually cache results
								$pd_pages = wp_list_pages( $args );
								$pd_pages = str_replace( ' selected="selected"', '', $pd_pages );
								set_transient( 'wpjm_menu_' . $wpjm_cpt, $pd_pages );
							} else {
								// Manually get cache
								$pd_pages = get_transient( 'wpjm_menu_' . $wpjm_cpt );
								// Unless it doesn't exist, then use get_posts
								if ( false == $pd_pages || empty( $pd_pages ) ) {
									$pd_pages = wp_list_pages( $args );
									$pd_pages = str_replace( ' selected="selected"', '', $pd_pages );
									set_transient( 'wpjm_menu_' . $wpjm_cpt, $pd_pages );
								}
							}

							$wpjm_string .= $pd_pages;
						}

						$wpjm_string .= '</optgroup>';
					} // end if (is_hierarchical)

				} // end foreach($wpjm_cpts)

			} // end if ($wpjm_cpts)

		} // end if ($custom_post_types)

		$wpjm_string = apply_filters( 'wpjm-filter-end-of-list', $wpjm_string );

		// Print the options page link
		if ( current_user_can( 'activate_plugins' ) ) {

			$wpjm_string .= '<optgroup label="// Jump Menu Options //">';
			$wpjm_string .= '<option value="' . admin_url() . 'options-general.php?page=wpjm-options">Jump Menu Options Page</option>';
			$wpjm_string .= '</optgroup>';

		}


		// Close the select drop down
		$wpjm_string .= '</select>';

		return $wpjm_string;

	} // end wpjm_page_dropdown()

	/**
   * wpjm_get_page_title()
   *
   * Utility function to truncate page titles
   *
	 * @param $pd_title
	 *
	 * @return string
	 */
	function wpjm_get_page_title( $pd_title ) {
		if ( strlen( $pd_title ) > 50 ) {
			return substr( $pd_title, 0, 50 ) . "...";
		} else {
			return $pd_title;
		}
	}


	/**
	*  wpjm_install
	*
	*  @description: Installs the options
	*  @since: 3.0
	*  @created: 12/12/12
	**/
	function wpjm_install() {

		// Populate with default values
		if ( get_option( 'wpjm_position' ) ) {

			$newPostTypes = array(
				'page' => array(
					'show'       => '1',
					'sortby'     => 'menu_order',
					'sort'       => 'ASC',
					'poststatus' => array( 'publish', 'draft' )
				),
				'post' => array(
					'show'       => '1',
					'sortby'     => 'date',
					'sort'       => 'DESC',
					'poststatus' => array( 'publish', 'draft' )
				)
			);

			// Get old custom post types option, append to new variable
			$customPostTypes = get_option( 'wpjm_customPostTypes' );
			$cpt_arr         = explode( ',', $customPostTypes );
			if ( ! empty( $cpt_arr ) ) {
				if ( is_array( $cpt_arr ) ) {
					foreach ( $cpt_arr as $cpt ) {
						$newPostTypes[ $cpt ] = array(
							'show'        => '1',
							'sortby'      => 'menu_order',
							'sort'        => 'ASC',
							'numberposts' => '-1',
							'poststatus'  => array( 'publish', 'draft' )
						);
					}
				} else {
					$newPostTypes[ $cpt_arr ] = array(
						'show'        => '1',
						'sortby'      => 'menu_order',
						'sort'        => 'ASC',
						'numberposts' => '-1',
						'poststatus'  => array( 'publish', 'draft' )
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
				'postTypes'       => $newPostTypes,
				'logoIcon'        => get_option( 'wpjm_logoIcon' ),
				'linkColor'       => get_option( 'wpjm_linkColor' ),
				'message'         => get_option( 'wpjm_message' ),
				'title'           => "WP Jump Menu &raquo;",
				'statusColors'    => array(
					'publish'    => '',
					'pending'    => '',
					'draft'      => '',
					'auto-draft' => '',
					'future'     => '',
					'private'    => '',
					'inherit'    => '',
					'trash'      => ''
				)
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
			if ( empty( $this->options ) ) {
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
							'poststatus'  => array( 'publish', 'draft' )
						),
						'post' => array(
							'show'        => '1',
							'sortby'      => 'date',
							'sort'        => 'DESC',
							'numberposts' => '-1',
							'poststatus'  => array( 'publish', 'draft' )
						)
					),
					'logoIcon'        => 'http://www.krillwebdesign.com/img/jk-og.png',
					'linkColor'       => '1cd0d6',
					'message'         => "Brought to you by <a href='http://www.krillwebdesign.com/' target='_blank'>Krill Web Design</a>.",
					'title'           => "WP Jump Menu &raquo;",
					'statusColors'    => array(
						'publish'    => '',
						'pending'    => '',
						'draft'      => '',
						'auto-draft' => '',
						'future'     => '',
						'private'    => '',
						'inherit'    => '',
						'trash'      => ''
					)
				);
				update_option( 'wpjm_options', $arr );
			} else {

				// Not a new install, but not an upgrade from old version, update post type status'
				if ( ! isset( $this->options['postTypes']['post']['poststatus'] ) ) {
					foreach ( $this->options['postTypes'] as $key => $value ) {
						$this->options['postTypes'][ $key ]['poststatus'] = array( 'publish', 'draft' );
					}
					update_option( 'wpjm_options', $this->options );
				}

				// Remove logo width if it is set
				if ( isset( $this->options['logoWidth'] ) ) {
					unset( $this->options['logoWidth'] );
					update_option( 'wpjm_options', $this->options );
				}

				// Add title if it is not set
				if ( ! isset( $this->options['title'] ) ) {
					$this->options['title'] = "WP Jump Menu &raquo;";
					update_option( 'wpjm_options', $this->options );
				}

			}

		}

		update_option( 'wpjm_version', $this->version );

		return true;

	}


}


// Only run this code if we are NOT within the Network pages on multisite.
if ( ! is_network_admin() ) {
	if ( function_exists( 'current_user_can' ) ) {

		require_once( 'settings.php' );
		$wpjm = new WpJumpMenu();

	}

}

?>
