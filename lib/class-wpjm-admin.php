<?php

class WPJM_Admin {

	private static $initiated = false;
	private static $options = array();
	private static $admin_page_slug = '';

	public static function init() {
		if ( ! self::$initiated ) {

			// permission Testing
			if ( ! current_user_can( 'edit_posts' ) ) {
				return false;
			}

			self::$options = get_option( WPJM_OPTIONS );

			self::init_hooks();
		}
	}

	private static function init_hooks() {
		self::$initiated = true;

		// check permissions
		if ( ! current_user_can( 'manage_options' ) )
			return false;

		// Actions
		// Add the admin menu option under Settings
		add_action( 'admin_menu', array( 'WPJM_Admin', 'admin_menu' ) );
		add_action( 'admin_print_scripts-settings_page_wpjm-options', array( 'WPJM_Admin', 'settings_scripts' ) );
		add_action( 'admin_init', array( 'WPJM_Admin', 'register_settings' ) );

		self::register_scripts();

	}

	private static function register_scripts() {
		// register scripts and styles
		$scripts = array(
			'wpjm-jquery-colorpicker' => WPJM__PLUGIN_URL . '/assets/js/colorpicker/js/colorpicker.js',
		);
		WPJM::register_scripts($scripts);

		$styles = array(
			'wpjm-colorpicker-css' => WPJM__PLUGIN_URL . '/assets/js/colorpicker/css/colorpicker.css',
			'wpjm-settings-css'    => WPJM__PLUGIN_URL . '/assets/css/wpjm-settings.css',
		);
		WPJM::register_styles($styles);
	}

	public static function settings_scripts() {
		// Colorpicker
		wp_enqueue_script( 'wpjm-jquery-colorpicker' );
		wp_enqueue_style( 'wpjm-colorpicker-css' );

		// Settings page CSS
		wp_enqueue_style( 'wpjm-settings-css' );
	}

	public static function register_settings() {
		global $wp_version;

		// Register our setting
		register_setting( 'wpjm_options', 'wpjm_options', array('WPJM_Admin', 'wpjm_options_validate') );

		// Add the main section
		add_settings_section( 'wpjm_post_types', 'Post Types', array('WPJM_Admin', 'wpjm_post_type_section_text'), 'wpjm' );
		add_settings_section( 'wpjm_main', 'Styling Options', array('WPJM_Admin', 'wpjm_section_text'), 'wpjm-2' );

		// Post Types Fields
		add_settings_field( 'wpjm_postTypes',
			'Post Types to Include',
			array( 'WPJM_Admin', 'wpjm_postTypes_checkbox'),
			'wpjm',
			'wpjm_post_types' );

		// Add the other fields
		add_settings_field( 'wpjm_position',
			'Position of Jump Menu Bar',
			array('WPJM_Admin', 'wpjm_position_radio'),
			'wpjm-2',
			'wpjm_main' );

		if (version_compare($wp_version, '3.4.3', '<')) {
			add_settings_field( 'wpjm_frontend',
				'Show on Front-End',
				array('WPJM_Admin', 'wpjm_frontend_checkbox'),
				'wpjm-2',
				'wpjm_main' );


			add_settings_field( 'wpjm_frontendjump',
				'Use Front-End Jump',
				array('WPJM_Admin', 'wpjm_frontendjump_checkbox'),
				'wpjm-2',
				'wpjm_main' );
		}

		add_settings_field( 'wpjm_useChosen',
			'Use Chosen Select Menu',
			array('WPJM_Admin', 'wpjm_useChosen_checkbox'),
			'wpjm-2',
			'wpjm_main' );

		add_settings_field( 'wpjm_useShortcut',
			'Use Shortcut Key',
			array('WPJM_Admin', 'wpjm_shortcutkey_checkbox'),
			'wpjm-2',
			'wpjm_main' );

		add_settings_field( 'wpjm_chosenTextAlign',
			'Chosen Text Alignment',
			array('WPJM_Admin', 'wpjm_chosenTextAlign_radio'),
			'wpjm-2',
			'wpjm_main' );

		add_settings_field( 'wpjm_showID',
			'Show ID',
			array('WPJM_Admin', 'wpjm_showID_checkbox'),
			'wpjm-2',
			'wpjm_main' );

		add_settings_field( 'wpjm_showPostType',
			'Show Post Type',
			array('WPJM_Admin', 'wpjm_showPostType_checkbox'),
			'wpjm-2',
			'wpjm_main' );

		add_settings_field( 'wpjm_showaddnew',
			'Show "Add New" link',
			array('WPJM_Admin', 'wpjm_showaddnew_checkbox'),
			'wpjm-2',
			'wpjm_main' );

		add_settings_field( 'wpjm_statusColors',
			'Status Colors',
			array('WPJM_Admin', 'wpjm_statusColors_checkbox'),
			'wpjm-2',
			'wpjm_main' );

		if (version_compare($wp_version, '3.4.3', '<')) {
			add_settings_field( 'wpjm_barColors',
				'Jump Menu Bar Colors',
				array('WPJM_Admin', 'wpjm_barColors_checkbox'),
				'wpjm-2',
				'wpjm_main' );

			add_settings_field( 'wpjm_logoIcon',
				'Logo Icon URL',
				array('WPJM_Admin', 'wpjm_logoIcon_text'),
				'wpjm-2',
				'wpjm_main' );

			add_settings_field( 'wpjm_message',
				'Message',
				array('WPJM_Admin', 'wpjm_message_textarea'),
				'wpjm-2',
				'wpjm_main' );
		}

		add_settings_field( 'wpjm_title',
			'WPJM Title',
			array('WPJM_Admin', 'wpjm_title_text'),
			'wpjm-2',
			'wpjm_main' );
	}

	public static function wpjm_section_text() {
		echo '<p class="description">These settings will change the position and colors of the Jump Menu.</p>';
	}

	public static function wpjm_post_type_section_text() {
		echo '<p class="description">Choose the post types you want to include in the Jump Menu.<br/>Click and drag the rows to change the order in which they appear in the Jump Menu.</p>';
	}

	/**
	 *  admin_menu
	 *
	 * @description:
	 * @since 1.0.0
	 * @created: 12/12/12
	 **/
	public static function admin_menu() {
		self::$admin_page_slug = add_options_page( 'Jump Menu Options',
			'Jump Menu Options',
			'edit_posts',
			'wpjm-options',
			array( 'WPJM_Admin', 'wpjm_options_page' )
		);
	}

	/**
	 *  wpjm_options_page
	 *
	 * @description: the options page
	 * @since: 3.0
	 * @created: 12/12/12
	 **/
	public static function wpjm_options_page() {

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
      <h2>WP Jump Menu <?php echo WPJM_VERSION; ?></h2>

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

	// --------------------------------
// Callbacks for fields
// --------------------------------
//

// Position
public static function wpjm_position_radio() {
	global $wp_version;

	$wp_version_compare = version_compare($wp_version, '3.4.3', '<');

  if ($wp_version_compare) {
	  ?>
    <div>
      <input type="radio" value="wpAdminBar" name="wpjm_options[position]" id="wpjm_position"
             class="wpjm_position" <?php checked( self::$options['position'], 'wpAdminBar' ); ?> />
      WP Admin Bar<br/>
      <input type="radio" value='top' name="wpjm_options[position]" id="wpjm_position"
             class="wpjm_position" <?php checked( self::$options['position'], 'top' ); ?> />
      Top of screen*<br/>
      <input type="radio" value="bottom" name="wpjm_options[position]" id="wpjm_position"
             class="wpjm_position" <?php checked( self::$options['position'], 'bottom' ); ?> />
      Bottom of screen*<br/>
      <p><em>
          <small>* - As of WPJM 3.4.3 Top and Bottom positions are not officially supported. Top and Bottom were
            original placements of the WPJM. It may not display properly if you choose these positions. WP Admin
            Bar is recommended.
          </small>
        </em></p>
    </div>
	  <?php
  } else {
    ?>
      <strong>WP Admin Bar*</strong>
      <input type="hidden" value="wpAdminBar" name="wpjm_options[position]" id="wpjm_position"
             class="wpjm_position" />
    <p><em>
        <small>* - As of WPJM 3.4.3 Top and Bottom positions are not officially supported.<br/>
          Top and Bottom were original placements of the WPJM.</small>
      </em></p>

    <?php
  }
  ?>
	<script>
		jQuery(function ($) {
			$('.wpjm_position').on('change', function (e) {
				var value = this.value;
				if (value === "wpAdminBar") {
					// Hide Top/Bottom only fields
					$('#wpjm_frontend').closest('tr').hide();
					$('#wpjm_backgroundColor').closest('tr').hide();
					$('#wpjm_logoIcon').closest('tr').hide();
					$('#wpjm_message').closest('tr').hide();
					// Show wpAdminBar only fields
					$('#wpjm_statusColors_publish').closest('tr').show();
				} else {
					// Show Top/Bottom only fields
					$('#wpjm_frontend').closest('tr').show();
					$('#wpjm_backgroundColor').closest('tr').show();
					$('#wpjm_logoIcon').closest('tr').show();
					$('#wpjm_message').closest('tr').show();
					// Hide wpAdminBar only fields
					$('#wpjm_statusColors_publish').closest('tr').hide();
				}
			});
			$('.wpjm_position:checked').trigger('change');
		});
	</script>
	<?php
}

// Show on Front-End
public static function wpjm_frontend_checkbox() {

	?>
	<div>
		<input type="checkbox" value="true" name="wpjm_options[frontend]" id="wpjm_frontend"
		       class="wpjm_frontend" <?php if ( isset( self::$options['frontend'] ) ) {
			checked( self::$options['frontend'], 'true' );
		} ?> />&nbsp;&nbsp;<span class="description">Show the jump menu on the front-end of the site.</span>
	</div>
	<?php
}

// Front-End Jump
public static function wpjm_frontendjump_checkbox() {

	?>
	<div>
		<input type="checkbox" value="true" name="wpjm_options[frontEndJump]" id="wpjm_frontEndJump"
		       class="wpjm_frontEndJump" <?php if ( isset( self::$options['frontEndJump'] ) ) {
			checked( self::$options['frontEndJump'], 'true' );
		} ?> />&nbsp;&nbsp;<span class="description">Clicking on items in the Jump Menu on the front-end of the site jumps to the pages on the front-end (not backend).</span>
	</div>
	<?php
}

// Use Chosen
//
public static function wpjm_useChosen_checkbox() {

	?>
	<div>
		<input type="checkbox" value="true" name="wpjm_options[useChosen]"
		       id="wpjm_useChosen" <?php if ( isset( self::$options['useChosen'] ) ) {
			checked( self::$options['useChosen'], 'true' );
		} ?> />&nbsp;&nbsp;<span class="description">Use <a href="http://harvesthq.github.com/chosen/" target="_blank">Chosen</a> plugin to display jump menu.  Adds search functionality and status colors.</span>
	</div>
	<script>
		jQuery(function ($) {
			$('#wpjm_useChosen').on('change', function (e) {
				var checked = $(this).attr('checked');
				if (checked) {
					$('#wpjm_chosenTextAlign').closest('tr').show();
					$('#wpjm_statusColors_publish').closest('tr').show();
				} else {
					$('#wpjm_chosenTextAlign').closest('tr').hide();
					$('#wpjm_statusColors_publish').closest('tr').hide();
				}
			});
			$('#wpjm_useChosen').trigger('change');
		});
	</script>
	<?php
}

// Use Shortcut Key
public static function wpjm_shortcutkey_checkbox() {

  ?>
  <div>
    <input type="checkbox" value="true" name="wpjm_options[useShortcut]" id="wpjm_useShortcut"
           class="wpjm_useShortcut" <?php if ( isset( self::$options['useShortcut'] ) ) {
      checked( self::$options['useShortcut'], 'true' );
    } ?> />&nbsp;&nbsp;<span class="description">Use CTRL+J to open Jump Menu (may conflict with some browser/OS keyboard shortcuts).</span>
  </div>
  <?php
}

// Chosen Text Alignment
//
public static function wpjm_chosenTextAlign_radio() {

	?>
	<div>
		<input type="radio" value="left" name="wpjm_options[chosenTextAlign]"
		       id="wpjm_chosenTextAlign" <?php checked( self::$options['chosenTextAlign'], 'left' ); ?> /> Left Aligned
		<br/>
		<input type="radio" value="right" name="wpjm_options[chosenTextAlign]"
		       id="wpjm_chosenTextAlign" <?php checked( self::$options['chosenTextAlign'], 'right' ); ?> /> Right Aligned
	</div>
	<?php
}

// Show ID
public static function wpjm_showID_checkbox() {

	?>
	<div>
		<input type="checkbox" value="true" name="wpjm_options[showID]"
		       id="wpjm_showID" <?php if ( isset( self::$options['showID'] ) ) {
			checked( self::$options['showID'], 'true' );
		} ?> />&nbsp;&nbsp;<span
			class="description">Display the post object's ID next to the item in the jump menu.</span>
	</div>
	<?php
}

// Show Post Type
public static function wpjm_showPostType_checkbox() {

	?>
	<div>
		<input type="checkbox" value="true" name="wpjm_options[showPostType]"
		       id="wpjm_showPostType" <?php if ( isset( self::$options['showPostType'] ) ) {
			checked( self::$options['showPostType'], 'true' );
		} ?> />&nbsp;&nbsp;<span class="description">Display the post object's post type next to the item in the jump menu.</span>
	</div>
	<?php
}

// Show Add New
public static function wpjm_showAddNew_checkbox() {

	?>
	<div>
		<input type="checkbox" value="true" name="wpjm_options[showaddnew]"
		       id="wpjm_showaddnew" <?php if ( isset( self::$options['showaddnew'] ) ) {
			checked( self::$options['showaddnew'], 'true' );
		} ?> />&nbsp;&nbsp;<span
			class="description">Display an "Add New" link under each post type in the jump menu.</span>
	</div>
	<?php
}


// Jump Menu Bar Colors
//
public static function wpjm_barColors_checkbox() {

?>
<div>
	<span class="description">Click on the input to select a color, or enter the hex value.<br/>When you are choosing a color, the jump menu (if top or bottom is selected) will give you a live preview of your color changes.<br/>Changes are NOT saved until you click the "Save Changes" button.</span>
</div>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[backgroundColor]" id="wpjm_backgroundColor"
	       value="<?php if ( isset( self::$options['backgroundColor'] ) ) {
		       echo self::$options['backgroundColor'];
	       } ?>" rel="#jump_menu|backgroundColor" size="6"/>
	<span class="description">Background Color</span>
</div>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[borderColor]" id="wpjm_borderColor"
	       value="<?php if ( isset( self::$options['borderColor'] ) ) {
		       echo self::$options['borderColor'];
	       } ?>" rel="#jump_menu|borderColor" size="6"/>
	<span class="description">Border Color</span>
</div>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[fontColor]" id="wpjm_fontColor"
	       value="<?php if ( isset( self::$options['fontColor'] ) ) {
		       echo self::$options['fontColor'];
	       } ?>" rel="#jump_menu|color" size="6"/>
	<span class="description">Font Color</span>
</div>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[linkColor]" id="wpjm_linkColor"
	       value="<?php if ( isset( self::$options['linkColor'] ) ) {
		       echo self::$options['linkColor'];
	       } ?>" rel="#jump_menu p a:link, #jump_menu p a:visited, #jump_menu p a:hover|color" size="6"/>
	<span class="description">Link Color</span>
	<div>
		<?php
		}

		// Status Colors
		//
		public static function wpjm_statusColors_checkbox() {

			?>
			<div>
				<span class="description"><strong>Must be using Chosen plugin for status colors to appear.</strong><br/>Click on the input to select a color, or enter the hex value.</span>
			</div>
			<div>
				<input class="colorPicker" type="text" name="wpjm_options[statusColors][publish]"
				       id="wpjm_statusColors_publish" value="<?php echo self::$options['statusColors']['publish']; ?>"
				       size="6"/>
				<span class="description">Publish</span>
			</div>
			<div>
				<input class="colorPicker" type="text" name="wpjm_options[statusColors][pending]"
				       id="wpjm_statusColors_pending" value="<?php echo self::$options['statusColors']['pending']; ?>"
				       size="6"/>
				<span class="description">Pending</span>
			</div>
			<div>
				<input class="colorPicker" type="text" name="wpjm_options[statusColors][draft]"
				       id="wpjm_statusColors_draft" value="<?php echo self::$options['statusColors']['draft']; ?>"
				       size="6"/>
				<span class="description">Draft</span>
			</div>
			<div>
				<input class="colorPicker" type="text" name="wpjm_options[statusColors][auto-draft]"
				       id="wpjm_statusColors_auto-draft"
				       value="<?php echo self::$options['statusColors']['auto-draft']; ?>" size="6"/>
				<span class="description">Auto-Draft</span>
			</div>
			<div>
				<input class="colorPicker" type="text" name="wpjm_options[statusColors][future]"
				       id="wpjm_statusColors_future" value="<?php echo self::$options['statusColors']['future']; ?>"
				       size="6"/>
				<span class="description">Future</span>
			</div>
			<div>
				<input class="colorPicker" type="text" name="wpjm_options[statusColors][private]"
				       id="wpjm_statusColors_private" value="<?php echo self::$options['statusColors']['private']; ?>"
				       size="6"/>
				<span class="description">Private</span>
			</div>
			<div>
				<input class="colorPicker" type="text" name="wpjm_options[statusColors][inherit]"
				       id="wpjm_statusColors_inherit" value="<?php echo self::$options['statusColors']['inherit']; ?>"
				       size="6"/>
				<span class="description">Inherit (media)</span>
			</div>
			<div>
				<input class="colorPicker" type="text" name="wpjm_options[statusColors][trash]"
				       id="wpjm_statusColors_trash" value="<?php echo self::$options['statusColors']['trash']; ?>"
				       size="6"/>
				<span class="description">Trash</span>
			</div>
			<?php
		}

		// Background Color
		//
		public static function wpjm_backgroundColor_text() {

			?>
			<div>
				<input class="colorPicker" type="text" name="wpjm_options[backgroundColor]" id="wpjm_backgroundColor"
				       value="<?php echo self::$options['backgroundColor']; ?>" rel="#jump_menu|backgroundColor"
				       size="6"/>
				<span class="description">Click to select color, or enter hex value</span>
			</div>
			<?php
		}


		// Font Color
		//
		public static function wpjm_fontColor_text() {

			?>
			<div>
				<input class="colorPicker" type="text" name="wpjm_options[fontColor]" id="wpjm_fontColor"
				       value="<?php echo self::$options['fontColor']; ?>" rel="#jump_menu|color" size="6"/>
				<span class="description">Click to select color, or enter hex value</span>
			</div>
			<?php
		}


		// Border Color
		//
		public static function wpjm_borderColor_text() {

			?>
			<div>
				<input class="colorPicker" type="text" name="wpjm_options[borderColor]" id="wpjm_borderColor"
				       value="<?php echo self::$options['borderColor']; ?>" rel="#jump_menu|borderColor" size="6"/>
				<span class="description">Click to select color, or enter hex value</span>
			</div>
			<?php
		}


		// Link Color
		//
		public static function wpjm_linkColor_text() {

		?>
		<div>
			<input class="colorPicker" type="text" name="wpjm_options[linkColor]" id="wpjm_linkColor"
			       value="<?php echo self::$options['linkColor']; ?>"
			       rel="#jump_menu p a:link, #jump_menu p a:visited, #jump_menu p a:hover|color" size="6"/>
			<span class="description">Click to select color, or enter hex value</span>
			<div>
				<?php
				}


				// Logo Icon URL
				//
				public static function wpjm_logoIcon_text() {

					?>
					<div>
						<input type="text" name="wpjm_options[logoIcon]" id="wpjm_logoIcon"
						       value="<?php echo self::$options['logoIcon']; ?>" size="75"/>
					</div>
					<span class="description">*Optional: The URL to the icon displayed next to the message in the jump bar.</span>
					<?php
				}


				// Message
				//
				public static function wpjm_message_textarea() {

					?>
					<div>
						<textarea name="wpjm_options[message]" id="wpjm_message" cols="60"
						          rows="3"><?php echo self::$options['message']; ?></textarea>
					</div>
					<span class="description">*Optional: Short message to include on left side of Jump bar (Top and Bottom positions only, not WP Admin Toolbar).  HTML is ok.</span>
					<?php
				}


				// WP Jump Menu Title
				//
				public static function wpjm_title_text() {

					?>
					<div>
						<input type="text" name="wpjm_options[title]" id="wpjm_title"
						       value="<?php echo self::$options['title']; ?>" size="75"/>
					</div>
					<span
						class="description">The title that appears to the left of the jump menu in all positions.</span>
					<?php
				}


				// Post Types
				//
				public static function wpjm_postTypes_checkbox() {

					?>

					<style>
						.widefat td {
							vertical-align: top;
						}
					</style>
					<div>

						<table id="wpjm-post-types-table" class="wp-list-table widefat fixed pages" cellspacing="0">
							<thead>
							<tr>
								<th scope="col" id="cb" class="manage-column column-cb check-column">
									<label class="screen-reader-text" for="cb-select-all-1">Select All</label>
									<input id="cb-select-all-1" type="checkbox"/></th>
								<th scope="col" class="manage-column wpjm-post-types-title-col">Post Types</th>
								<th scope="col" class="manage-column wpjm-numberposts-col">Show</th>
								<th scope="col" class="manage-column wpjm-order-by-col">Order By</th>
								<th scope="col" class="manage-column wpjm-order-col">Order</th>
								<th scope="col" class="manage-column wpjm-showdrafts-col">Post Status</th>
							</tr>
							</thead>
							<tfoot>
							<tr>
								<th scope="col" class="manage-column column-cb check-column">
									<label class="screen-reader-text" for="cb-select-all-2">Select All</label>
									<input id="cb-select-all-2" type="checkbox"/></th>
								<th scope="col" class="manage-column wpjm-post-types-title-col">Post Types</th>
								<th scope="col" class="manage-column wpjm-numberposts-col">Show</th>
								<th scope="col" class="manage-column wpjm-order-by-col">Order By</th>
								<th scope="col" class="manage-column wpjm-order-col">Order</th>
								<th scope="col" class="manage-column wpjm-showdrafts-col">Post Status</th>
							</tr>
							</tfoot>
							<tbody id="the-list">
							<?php

							// Get the array of registered post types (array of objects)
							$post_types = get_post_types( '', 'objects' );

							// Get the array of selected post types
							$selected_post_types_arr = self::$options['postTypes'];

							// A function to sort the $post_type array by the $selected array
							function sortArrayByArray( $array, $orderArray ) {
								$ordered = array();
								foreach ( $orderArray as $key ) {
									if ( array_key_exists( $key, $array ) ) {
										$ordered[ $key ] = $array[ $key ];
										unset( $array[ $key ] );
									}
								}

								return $ordered + $array;
							}

							if ( is_array( $selected_post_types_arr ) ) {
								// Make an array of only the keys from the selected post types
								$array2 = array_keys( $selected_post_types_arr );
								// And... sort it, returning an organized array;
								// with the unselected post types at the end
								$custom_array_order = sortArrayByArray( $post_types, $array2 );
							} else {
								$custom_array_order = $post_types;
							}


							?>

							<?php
							$alt = "";
							foreach ( $custom_array_order as $pt ) {
								if ( ( $pt->name == 'nav_menu_item' ) || ( $pt->name == 'revision' ) ) {
									continue;
								}
								// Check for existence of values
								if ( ! isset( self::$options['postTypes'][ $pt->name ] ) ) {
									if ( ! is_post_type_hierarchical( $pt->name ) ) {
										self::$options['postTypes'][ $pt->name ] = array(
											'show'        => '0',
											'sortby'      => 'date',
											'sort'        => 'DESC',
											'numberposts' => '-1',
											'poststatus'  => array( 'publish', 'draft' )
										);
									} else {
										self::$options['postTypes'][ $pt->name ] = array(
											'show'        => '0',
											'sortby'      => 'menu_order',
											'sort'        => 'ASC',
											'numberposts' => '0',
											'poststatus'  => array( 'publish', 'draft' )
										);
									}
								}
								?>
								<tr class="<?php if ( $alt == "" ) {
									$alt = "alternate";
									echo $alt;
								} else {
									$alt = "";
									echo $alt;
								} ?>" valign="top">
									<th class="check-column" scope="row">
										<input type="checkbox"
										       name="wpjm_options[postTypes][<?php echo $pt->name; ?>][show]"
										       id="wpjm_postType_<?php echo $pt->name; ?>"
										       value="1" <?php checked( self::$options['postTypes'][ $pt->name ]['show'], 1 ); ?> />
									</td>
									<td>
										<strong><?php echo $pt->labels->name; ?></strong>
									</td>
									<td>
										<div>
											<input type="text"
											       name="wpjm_options[postTypes][<?php echo $pt->name; ?>][numberposts]"
											       id="wpjm_number<?php echo $pt->name; ?>"
											       value="<?php echo self::$options['postTypes'][ $pt->name ]['numberposts']; ?>"
											       size="3"/>
											<?php if ( ! is_post_type_hierarchical( $pt->name ) ) { ?>
												<br/><span class="description">How many posts to show.<br/>-1 to display all.</span>
											<?php } else { ?>
												<br/><span class="description">Depth Level<br/>0 to show all.</span>
											<?php } ?>
										</div>
									</td>
									<td>
										<select name="wpjm_options[postTypes][<?php echo $pt->name; ?>][sortby]"
										        id="wpjm_sort<?php echo $pt->name; ?>by">
											<option
												value="menu_order" <?php selected( self::$options['postTypes'][ $pt->name ]['sortby'], 'menu_order' ); ?>>
												Menu Order
											</option>
											<option
												value="author" <?php selected( self::$options['postTypes'][ $pt->name ]['sortby'], 'author' ); ?>>
												Author
											</option>
											<option
												value="date" <?php selected( self::$options['postTypes'][ $pt->name ]['sortby'], 'date' ); ?>>
												Date
											</option>
											<option
												value="ID" <?php selected( self::$options['postTypes'][ $pt->name ]['sortby'], 'ID' ); ?>>
												ID
											</option>
											<option
												value="modified" <?php selected( self::$options['postTypes'][ $pt->name ]['sortby'], 'modified' ); ?>>
												Modified
											</option>
											<option
												value="comment_count" <?php selected( self::$options['postTypes'][ $pt->name ]['sortby'], 'comment_count' ); ?>>
												Comment Count
											</option>
											<option
												value="parent" <?php selected( self::$options['postTypes'][ $pt->name ]['sortby'], 'parent' ); ?>>
												Parent
											</option>
											<option
												value="title" <?php selected( self::$options['postTypes'][ $pt->name ]['sortby'], 'title' ); ?>>
												Title
											</option>
											<?php
											if ( $pt->name == 'attachment' ) { ?>
												<option
													value="mime_type" <?php selected( self::$options['postTypes'][ $pt->name ]['sortby'], 'mime_type' ); ?>>
													Mime Type
												</option>
												<?php
											}
											?>
										</select>
										<br/><span class="description"><a
												href="http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters"
												target="_blank"><?php _e( 'Documentation', 'wp-jump-menu' ); ?></a></span>
										<?php if ( $pt->name == 'attachment' && isset( self::$options['postTypes'][ $pt->name ]['postmimetypes'] ) ) { ?>
											<div class="mime-types">
												<br/>
												<strong>Show Media Types:</strong><br/>
												<input type="checkbox" value="all"
												       name="wpjm_options[postTypes][<?php echo $pt->name; ?>][postmimetypes][]"
												       id="wpjm_postmimetypes<?php echo $pt->name; ?>" <?php if ( is_array( self::$options['postTypes'][ $pt->name ]['postmimetypes'] ) ) {
													echo( in_array( 'all', self::$options['postTypes'][ $pt->name ]['postmimetypes'] ) ? ' checked="checked"' : '' );
												} ?> /> All
												<br/>
												<input type="checkbox" value="images"
												       name="wpjm_options[postTypes][<?php echo $pt->name; ?>][postmimetypes][]"
												       id="wpjm_postmimetypes<?php echo $pt->name; ?>" <?php if ( is_array( self::$options['postTypes'][ $pt->name ]['postmimetypes'] ) ) {
													echo( in_array( 'images', self::$options['postTypes'][ $pt->name ]['postmimetypes'] ) ? ' checked="checked"' : '' );
												} ?> /> Images
												<br/>
												<input type="checkbox" value="videos"
												       name="wpjm_options[postTypes][<?php echo $pt->name; ?>][postmimetypes][]"
												       id="wpjm_postmimetypes<?php echo $pt->name; ?>" <?php if ( is_array( self::$options['postTypes'][ $pt->name ]['postmimetypes'] ) ) {
													echo( in_array( 'videos', self::$options['postTypes'][ $pt->name ]['postmimetypes'] ) ? ' checked="checked"' : '' );
												} ?> /> Videos
												<br/>
												<input type="checkbox" value="audio"
												       name="wpjm_options[postTypes][<?php echo $pt->name; ?>][postmimetypes][]"
												       id="wpjm_postmimetypes<?php echo $pt->name; ?>" <?php if ( is_array( self::$options['postTypes'][ $pt->name ]['postmimetypes'] ) ) {
													echo( in_array( 'audio', self::$options['postTypes'][ $pt->name ]['postmimetypes'] ) ? ' checked="checked"' : '' );
												} ?> /> Audio
												<br/>
												<input type="checkbox" value="documents"
												       name="wpjm_options[postTypes][<?php echo $pt->name; ?>][postmimetypes][]"
												       id="wpjm_postmimetypes<?php echo $pt->name; ?>" <?php if ( is_array( self::$options['postTypes'][ $pt->name ]['postmimetypes'] ) ) {
													echo( in_array( 'documents', self::$options['postTypes'][ $pt->name ]['postmimetypes'] ) ? ' checked="checked"' : '' );
												} ?> /> Documents
												<br/>

											</div>
											<?php
										}
										?>
									</td>
									<td>
										<div>
											<input type="radio" value="ASC"
											       name="wpjm_options[postTypes][<?php echo $pt->name; ?>][sort]"
											       id="wpjm_sort<?php echo $pt->name; ?>" <?php checked( self::$options['postTypes'][ $pt->name ]['sort'], 'ASC' ); ?> />
											ASC <span class="description">(a-z, 1-10)</span>
											<br>
											<input type="radio" value="DESC"
											       name="wpjm_options[postTypes][<?php echo $pt->name; ?>][sort]"
											       id="wpjm_sort<?php echo $pt->name; ?>" <?php checked( self::$options['postTypes'][ $pt->name ]['sort'], 'DESC' ); ?> />
											DESC <span class="description">(z-a, 10-1)</span>
										</div>
									</td>
									<td>
                    <?php
                    if ($pt->name == "attachment") {
                      ?>
                      N/A
                      <input type="hidden" value="any"
                             name="wpjm_options[postTypes][<?php echo $pt->name; ?>][poststatus][]"
                             id="wpjm_poststatus<?php echo $pt->name; ?>" />
                      <?php
                    } else {
	                    ?>
                      <div style="float: left; margin-right: 20px;">
                        <input type="checkbox" value="publish"
                               name="wpjm_options[postTypes][<?php echo $pt->name; ?>][poststatus][]"
                               id="wpjm_poststatus<?php echo $pt->name; ?>" <?php if ( is_array( self::$options['postTypes'][ $pt->name ]['poststatus'] ) ) {
			                    echo( in_array( 'publish', self::$options['postTypes'][ $pt->name ]['poststatus'] ) ? ' checked="checked"' : '' );
		                    } ?> /> Publish<br/>

                        <input type="checkbox" value="pending"
                               name="wpjm_options[postTypes][<?php echo $pt->name; ?>][poststatus][]"
                               id="wpjm_poststatus<?php echo $pt->name; ?>" <?php if ( is_array( self::$options['postTypes'][ $pt->name ]['poststatus'] ) ) {
			                    echo( in_array( 'pending', self::$options['postTypes'][ $pt->name ]['poststatus'] ) ? ' checked="checked"' : '' );
		                    } ?> /> Pending<br/>

                        <input type="checkbox" value="draft"
                               name="wpjm_options[postTypes][<?php echo $pt->name; ?>][poststatus][]"
                               id="wpjm_poststatus<?php echo $pt->name; ?>" <?php if ( is_array( self::$options['postTypes'][ $pt->name ]['poststatus'] ) ) {
			                    echo( in_array( 'draft', self::$options['postTypes'][ $pt->name ]['poststatus'] ) ? ' checked="checked"' : '' );
		                    } ?> /> Draft<br/>

                        <input type="checkbox" value="auto-draft"
                               name="wpjm_options[postTypes][<?php echo $pt->name; ?>][poststatus][]"
                               id="wpjm_poststatus<?php echo $pt->name; ?>" <?php if ( is_array( self::$options['postTypes'][ $pt->name ]['poststatus'] ) ) {
			                    echo( in_array( 'auto-draft', self::$options['postTypes'][ $pt->name ]['poststatus'] ) ? ' checked="checked"' : '' );
		                    } ?> /> Auto-Draft<br/>

                        <input type="checkbox" value="future"
                               name="wpjm_options[postTypes][<?php echo $pt->name; ?>][poststatus][]"
                               id="wpjm_poststatus<?php echo $pt->name; ?>" <?php if ( is_array( self::$options['postTypes'][ $pt->name ]['poststatus'] ) ) {
			                    echo( in_array( 'future', self::$options['postTypes'][ $pt->name ]['poststatus'] ) ? ' checked="checked"' : '' );
		                    } ?> /> Future<br/>


                      </div>
                      <div style="float: left;">
                        <input type="checkbox" value="private"
                               name="wpjm_options[postTypes][<?php echo $pt->name; ?>][poststatus][]"
                               id="wpjm_poststatus<?php echo $pt->name; ?>" <?php if ( is_array( self::$options['postTypes'][ $pt->name ]['poststatus'] ) ) {
			                    echo( in_array( 'private', self::$options['postTypes'][ $pt->name ]['poststatus'] ) ? ' checked="checked"' : '' );
		                    } ?> /> Private<br/>

                        <input type="checkbox" value="inherit"
                               name="wpjm_options[postTypes][<?php echo $pt->name; ?>][poststatus][]"
                               id="wpjm_poststatus<?php echo $pt->name; ?>" <?php if ( is_array( self::$options['postTypes'][ $pt->name ]['poststatus'] ) ) {
			                    echo( in_array( 'inherit', self::$options['postTypes'][ $pt->name ]['poststatus'] ) ? ' checked="checked"' : '' );
		                    } ?> /> Inherit<br/>

                        <input type="checkbox" value="trash"
                               name="wpjm_options[postTypes][<?php echo $pt->name; ?>][poststatus][]"
                               id="wpjm_poststatus<?php echo $pt->name; ?>" <?php if ( is_array( self::$options['postTypes'][ $pt->name ]['poststatus'] ) ) {
			                    echo( in_array( 'trash', self::$options['postTypes'][ $pt->name ]['poststatus'] ) ? ' checked="checked"' : '' );
		                    } ?> /> Trash<br/>

                        <input type="checkbox" value="any"
                               name="wpjm_options[postTypes][<?php echo $pt->name; ?>][poststatus][]"
                               id="wpjm_poststatus<?php echo $pt->name; ?>" <?php if ( is_array( self::$options['postTypes'][ $pt->name ]['poststatus'] ) ) {
			                    echo( in_array( 'any', self::$options['postTypes'][ $pt->name ]['poststatus'] ) ? ' checked="checked"' : '' );
		                    } ?> /> Any<br/>
                      </div>
                      <div style="clear: both;"><span class="description"><small><strong>NOTE:</strong> Trash items will only display if Any is NOT selected.<br/><strong>NOTE:</strong> If your items are not showing up, try choosing "Inherit" or "Any".</small></span>
                      </div>
	                    <?php
                    }
                    ?>
									</td>
								</tr>
							<?php } ?>
							</tbody>
						</table>
						<br>
					</div>
					<?php
				}


				// TODO: Continue adding the rest of the fields from over there ----->

				// validate our options

				public static function wpjm_options_validate( $input ) {
					$newinput = $input;
					foreach ( $newinput['postTypes'] as $key => $value ) {
						if ( ! isset( $newinput['postTypes'][ $key ]['show'] ) ) {
							unset( $newinput['postTypes'][ $key ] );
						} else {
							if ( ! isset( $newinput['postTypes'][ $key ]['sort'] ) ) {
								$newinput['postTypes'][ $key ]['sort'] = 'ASC';
							}
							if ( $newinput['postTypes'][ $key ]['numberposts'] == "" || $newinput['postTypes'][ $key ]['numberposts'] < - 1 ) {
								$newinput['postTypes'][ $key ]['numberposts'] = '-1';
							}
							if ( ! isset( $newinput['postTypes'][ $key ]['poststatus'] ) ) {
								$newinput['postTypes'][ $key ]['poststatus'] = array( 'publish' );
								if ( $key == 'attachment' ) {
									$newinput['postTypes'][ $key ]['poststatus'] = array( 'publish', 'inherit' );
								}
							}
							if ( $key == 'attachment' ) {
								if ( ( ! isset( $newinput['postTypes'][ $key ]['postmimetypes'] ) ) || ( in_array( 'all', $newinput['postTypes'][ $key ]['postmimetypes'] ) ) ) {
									$newinput['postTypes'][ $key ]['postmimetypes'] = array( 'all' );
								}
							}

						}

					}
//	error_log('Logging cache label: ');
//	update_option( 'wpjm_needs_refresh', 1 );
					$needs_refresh = set_transient( WPJM_NEEDS_REFRESH_CACHE_LABEL, 1 );

//	error_log($needs_refresh);
					return $newinput;
				}

}