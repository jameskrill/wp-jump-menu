<?php
/**
 * settings.php
 *
 * The options page for WP Jump Menu
 *
 */

$wpjm_options = get_option('wpjm_options');

function wpjm_admin_init() {

	// Register our setting
	register_setting( 'wpjm_options', 'wpjm_options', 'wpjm_options_validate' );

	// Add the main section
	add_settings_section( 'wpjm_post_types', 'Post Types', 'wpjm_post_type_section_text', 'wpjm' );
	add_settings_section( 'wpjm_main', 'Styling Options', 'wpjm_section_text', 'wpjm-2' );

	// Post Types Fields
	add_settings_field( 'wpjm_postTypes',
			'Post Types to Include',
			'wpjm_postTypes_checkbox',
			'wpjm',
			'wpjm_post_types' );

	// Add the other fields
	add_settings_field( 'wpjm_position',
			'Position of Jump Menu Bar',
			'wpjm_position_radio',
			'wpjm-2',
			'wpjm_main' );

	add_settings_field( 'wpjm_frontend',
			'Show on Front-End',
			'wpjm_frontend_checkbox',
			'wpjm-2',
			'wpjm_main' );

	add_settings_field( 'wpjm_frontendjump',
			'Use Front-End Jump',
			'wpjm_frontendjump_checkbox',
			'wpjm-2',
			'wpjm_main' );

	add_settings_field( 'wpjm_useChosen',
			'Use Chosen Select Menu',
			'wpjm_useChosen_checkbox',
			'wpjm-2',
			'wpjm_main' );

	add_settings_field( 'wpjm_chosenTextAlign',
			'Chosen Text Alignment',
			'wpjm_chosenTextAlign_radio',
			'wpjm-2',
			'wpjm_main' );

	add_settings_field( 'wpjm_showID',
			'Show ID',
			'wpjm_showID_checkbox',
			'wpjm-2',
			'wpjm_main' );

	add_settings_field( 'wpjm_showaddnew',
			'Show "Add New" link',
			'wpjm_showaddnew_checkbox',
			'wpjm-2',
			'wpjm_main' );

	add_settings_field( 'wpjm_barColors',
			'Jump Menu Bar Colors',
			'wpjm_barColors_checkbox',
			'wpjm-2',
			'wpjm_main' );

	add_settings_field( 'wpjm_statusColors',
			'Status Colors',
			'wpjm_statusColors_checkbox',
			'wpjm-2',
			'wpjm_main' );

	add_settings_field( 'wpjm_logoIcon',
			'Logo Icon URL',
			'wpjm_logoIcon_text',
			'wpjm-2',
			'wpjm_main' );

	add_settings_field( 'wpjm_message',
			'Message',
			'wpjm_message_textarea',
			'wpjm-2',
			'wpjm_main' );

	add_settings_field( 'wpjm_title',
			'WPJM Title',
			'wpjm_title_text',
			'wpjm-2',
			'wpjm_main' );



} // wpjm_admin_init()



function wpjm_section_text() {
	echo '<p class="description">These settings will change the position and colors of the Jump Menu.</p>';
}

function wpjm_post_type_section_text() {
	echo '<p class="description">Choose the post types you want to include in the Jump Menu.<br/>Click and drag the rows to change the order in which they appear in the Jump Menu.</p>';
}

// --------------------------------
// Callbacks for fields
// --------------------------------
//

// Position
function wpjm_position_radio() {
global $wpjm_options;
?>
<div>
	<input type="radio" value="wpAdminBar" name="wpjm_options[position]" id="wpjm_position" class="wpjm_position" <?php checked($wpjm_options['position'], 'wpAdminBar'); ?> />
	WP Admin Bar<br/>
	<input type="radio" value='top' name="wpjm_options[position]" id="wpjm_position" class="wpjm_position" <?php checked($wpjm_options['position'], 'top'); ?> />
		 Top of screen*<br/>
	<input type="radio" value="bottom" name="wpjm_options[position]" id="wpjm_position" class="wpjm_position" <?php checked($wpjm_options['position'], 'bottom'); ?> />
		 Bottom of screen*<br/>
	<p><em><small>* - As of WPJM 3.4.3 Top and Bottom positions are not officially supported. Top and Bottom were original placements of the WPJM. It may not display properly if you choose these positions.  WP Admin Bar is recommended.</small></em></p>
</div>
<script>
jQuery(function($){
	$('.wpjm_position').on('change',function(e){
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
function wpjm_frontend_checkbox() {
	global $wpjm_options;
?>
<div>
	<input type="checkbox" value="true" name="wpjm_options[frontend]" id="wpjm_frontend" class="wpjm_frontend" <?php if(isset($wpjm_options['frontend'])) { checked($wpjm_options['frontend'], 'true'); } ?> />&nbsp;&nbsp;<span class="description">Show the jump menu on the front-end of the site.</span>
</div>
<?php
}

// Front-End Jump
function wpjm_frontendjump_checkbox() {
	global $wpjm_options;
?>
<div>
	<input type="checkbox" value="true" name="wpjm_options[frontEndJump]" id="wpjm_frontEndJump" class="wpjm_frontEndJump" <?php if(isset($wpjm_options['frontEndJump'])) { checked($wpjm_options['frontEndJump'], 'true'); } ?> />&nbsp;&nbsp;<span class="description">Clicking on items in the Jump Menu on the front-end of the site jumps to the pages on the front-end (not backend).</span>
</div>
<?php
}

// Use Chosen
//
function wpjm_useChosen_checkbox() {
	global $wpjm_options;
?>
<div>
	<input type="checkbox" value="true" name="wpjm_options[useChosen]" id="wpjm_useChosen" <?php if(isset($wpjm_options['useChosen'])) { checked($wpjm_options['useChosen'], 'true'); } ?> />&nbsp;&nbsp;<span class="description">Use <a href="http://harvesthq.github.com/chosen/" target="_blank">Chosen</a> plugin to display jump menu.  Adds search functionality and status colors.</span>
</div>
<script>
jQuery(function($){
	$('#wpjm_useChosen').on('change',function(e){
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

// Chosen Text Alignment
//
function wpjm_chosenTextAlign_radio() {
	global $wpjm_options;
?>
<div>
	<input type="radio" value="left" name="wpjm_options[chosenTextAlign]" id="wpjm_chosenTextAlign" <?php checked($wpjm_options['chosenTextAlign'], 'left'); ?> /> Left Aligned
	<br/>
	<input type="radio" value="right" name="wpjm_options[chosenTextAlign]" id="wpjm_chosenTextAlign" <?php checked($wpjm_options['chosenTextAlign'], 'right'); ?> /> Right Aligned
</div>
<?php
}

// Show ID
function wpjm_showID_checkbox() {
global $wpjm_options;
?>
<div>
	<input type="checkbox" value="true" name="wpjm_options[showID]" id="wpjm_showID" <?php if(isset($wpjm_options['showID'])) { checked($wpjm_options['showID'], 'true'); } ?> />&nbsp;&nbsp;<span class="description">Display the post object's ID next to the item in the jump menu.</span>
</div>
<?php
}

// Show Add New
function wpjm_showAddNew_checkbox() {
global $wpjm_options;
?>
<div>
	<input type="checkbox" value="true" name="wpjm_options[showaddnew]" id="wpjm_showaddnew" <?php if(isset($wpjm_options['showaddnew'])) { checked($wpjm_options['showaddnew'], 'true'); } ?> />&nbsp;&nbsp;<span class="description">Display an "Add New" link under each post type in the jump menu.</span>
</div>
<?php
}


// Jump Menu Bar Colors
//
function wpjm_barColors_checkbox() {
global $wpjm_options;
?>
<div>
	<span class="description">Click on the input to select a color, or enter the hex value.<br/>When you are choosing a color, the jump menu (if top or bottom is selected) will give you a live preview of your color changes.<br/>Changes are NOT saved until you click the "Save Changes" button.</span>
</div>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[backgroundColor]" id="wpjm_backgroundColor" value="<?php if(isset($wpjm_options['backgroundColor'])) { echo $wpjm_options['backgroundColor']; } ?>" rel="#jump_menu|backgroundColor" size="6" />
	<span class="description">Background Color</span>
</div>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[borderColor]" id="wpjm_borderColor" value="<?php if(isset($wpjm_options['borderColor'])) { echo $wpjm_options['borderColor']; } ?>" rel="#jump_menu|borderColor" size="6" />
	<span class="description">Border Color</span>
</div>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[fontColor]" id="wpjm_fontColor" value="<?php if(isset($wpjm_options['fontColor'])) { echo $wpjm_options['fontColor']; } ?>" rel="#jump_menu|color" size="6" />
	<span class="description">Font Color</span>
</div>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[linkColor]" id="wpjm_linkColor" value="<?php if(isset($wpjm_options['linkColor'])) { echo $wpjm_options['linkColor']; } ?>" rel="#jump_menu p a:link, #jump_menu p a:visited, #jump_menu p a:hover|color" size="6" />
	<span class="description">Link Color</span>
<div>
<?php
}

// Status Colors
//
function wpjm_statusColors_checkbox() {
global $wpjm_options;
?>
<div>
	<span class="description"><strong>Must be using Chosen plugin for status colors to appear.</strong><br/>Click on the input to select a color, or enter the hex value.</span>
</div>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[statusColors][publish]" id="wpjm_statusColors_publish" value="<?php echo $wpjm_options['statusColors']['publish']; ?>" size="6" />
	<span class="description">Publish</span>
</div>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[statusColors][pending]" id="wpjm_statusColors_pending" value="<?php echo $wpjm_options['statusColors']['pending']; ?>" size="6" />
	<span class="description">Pending</span>
</div>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[statusColors][draft]" id="wpjm_statusColors_draft" value="<?php echo $wpjm_options['statusColors']['draft']; ?>" size="6" />
	<span class="description">Draft</span>
</div>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[statusColors][auto-draft]" id="wpjm_statusColors_auto-draft" value="<?php echo $wpjm_options['statusColors']['auto-draft']; ?>" size="6" />
	<span class="description">Auto-Draft</span>
</div>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[statusColors][future]" id="wpjm_statusColors_future" value="<?php echo $wpjm_options['statusColors']['future']; ?>" size="6" />
	<span class="description">Future</span>
</div>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[statusColors][private]" id="wpjm_statusColors_private" value="<?php echo $wpjm_options['statusColors']['private']; ?>" size="6" />
	<span class="description">Private</span>
</div>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[statusColors][inherit]" id="wpjm_statusColors_inherit" value="<?php echo $wpjm_options['statusColors']['inherit']; ?>" size="6" />
	<span class="description">Inherit (media)</span>
</div>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[statusColors][trash]" id="wpjm_statusColors_trash" value="<?php echo $wpjm_options['statusColors']['trash']; ?>" size="6" />
	<span class="description">Trash</span>
</div>
<?php
}

// Background Color
//
function wpjm_backgroundColor_text() {
global $wpjm_options;
?>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[backgroundColor]" id="wpjm_backgroundColor" value="<?php echo $wpjm_options['backgroundColor']; ?>" rel="#jump_menu|backgroundColor" size="6" />
	<span class="description">Click to select color, or enter hex value</span>
</div>
<?php
}


// Font Color
//
function wpjm_fontColor_text() {
global $wpjm_options;
?>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[fontColor]" id="wpjm_fontColor" value="<?php echo $wpjm_options['fontColor']; ?>" rel="#jump_menu|color" size="6" />
<span class="description">Click to select color, or enter hex value</span>
</div>
<?php
}


// Border Color
//
function wpjm_borderColor_text() {
global $wpjm_options;
?>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[borderColor]" id="wpjm_borderColor" value="<?php echo $wpjm_options['borderColor']; ?>" rel="#jump_menu|borderColor" size="6" />
	<span class="description">Click to select color, or enter hex value</span>
</div>
<?php
}


// Link Color
//
function wpjm_linkColor_text() {
global $wpjm_options;
?>
<div>
	<input class="colorPicker" type="text" name="wpjm_options[linkColor]" id="wpjm_linkColor" value="<?php echo $wpjm_options['linkColor']; ?>" rel="#jump_menu p a:link, #jump_menu p a:visited, #jump_menu p a:hover|color" size="6" />
	<span class="description">Click to select color, or enter hex value</span>
<div>
<?php
}


// Logo Icon URL
//
function wpjm_logoIcon_text() {
global $wpjm_options;
?>
<div>
	<input type="text" name="wpjm_options[logoIcon]" id="wpjm_logoIcon" value="<?php echo $wpjm_options['logoIcon']; ?>" size="75" />
</div>
<span class="description">*Optional: The URL to the icon displayed next to the message in the jump bar.</span>
<?php
}


// Message
//
function wpjm_message_textarea() {
global $wpjm_options;
?>
<div>
	<textarea name="wpjm_options[message]" id="wpjm_message" cols="60" rows="3" ><?php echo $wpjm_options['message']; ?></textarea>
</div>
<span class="description">*Optional: Short message to include on left side of Jump bar (Top and Bottom positions only, not WP Admin Toolbar).  HTML is ok.</span>
<?php
}


// WP Jump Menu Title
//
function wpjm_title_text() {
global $wpjm_options;
?>
<div>
	<input type="text" name="wpjm_options[title]" id="wpjm_title" value="<?php echo $wpjm_options['title']; ?>" size="75" />
</div>
<span class="description">The title that appears to the left of the jump menu in all positions.</span>
<?php
}


// Post Types
//
function wpjm_postTypes_checkbox() {
global $wpjm_options;
?>

<style>
.widefat td { vertical-align: top; }
</style>
	<div>

		<table id="wpjm-post-types-table" class="wp-list-table widefat fixed pages" cellspacing="0">
			<thead>
			<tr>
				<th scope="col" id="cb" class="manage-column column-cb check-column">
					<label class="screen-reader-text" for="cb-select-all-1">Select All</label>
					<input id="cb-select-all-1" type="checkbox" /></th>
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
					<input id="cb-select-all-2" type="checkbox" /></th>
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
			$post_types = get_post_types('','objects');

			// Get the array of selected post types
			$selected_post_types_arr = $wpjm_options['postTypes'];

				// A function to sort the $post_type array by the $selected array
				function sortArrayByArray($array,$orderArray) {
				    $ordered = array();
				    foreach($orderArray as $key) {
				        if(array_key_exists($key,$array)) {
				                $ordered[$key] = $array[$key];
				                unset($array[$key]);
				        }
				    }
				    return $ordered + $array;
				}

				if (is_array($selected_post_types_arr)) {
					// Make an array of only the keys from the selected post types
					$array2 = array_keys($selected_post_types_arr);
					// And... sort it, returning an organized array;
					// with the unselected post types at the end
					$custom_array_order = sortArrayByArray($post_types, $array2);
				} else {
					$custom_array_order = $post_types;
				}




		?>

			<?php
			$alt = "";
			foreach ($custom_array_order as $pt) {
				if ( ($pt->name == 'nav_menu_item') || ($pt->name == 'revision') ) continue;
				// Check for existence of values
				if (!isset($wpjm_options['postTypes'][$pt->name])) {
					if (!is_post_type_hierarchical( $pt->name )) {
						$wpjm_options['postTypes'][$pt->name] = array(
							'show' => '0',
							'sortby' => 'date',
							'sort' => 'DESC',
							'numberposts' => '-1',
							'poststatus' => array('publish','draft')
						);
					} else {
						$wpjm_options['postTypes'][$pt->name] = array(
							'show' => '0',
							'sortby' => 'menu_order',
							'sort' => 'ASC',
							'numberposts' => '0',
							'poststatus' => array('publish','draft')
						);
					}
				}
				?>
			<tr class="<?php if ($alt==""){ $alt = "alternate"; echo $alt; } else { $alt = ""; echo $alt;  } ?>" valign="top">
				<th class="check-column" scope="row">
					<input type="checkbox" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][show]" id="wpjm_postType_<?php echo $pt->name; ?>" value="1" <?php checked($wpjm_options['postTypes'][$pt->name]['show'], 1 ); ?> />
				</td>
				<td>
					<strong><?php echo $pt->labels->name; ?></strong>
				</td>
				<td>
					<div>
						<input type="text" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][numberposts]" id="wpjm_number<?php echo $pt->name; ?>" value="<?php echo $wpjm_options['postTypes'][$pt->name]['numberposts']; ?>" size="3" />
						<?php if (!is_post_type_hierarchical($pt->name)) { ?>
						<br/><span class="description">How many posts to show.<br/>-1 to display all.</span>
						<?php } else { ?>
						<br/><span class="description">Depth Level<br/>0 to show all.</span>
						<?php } ?>
					</div>
				</td>
				<td>
					<select name="wpjm_options[postTypes][<?php echo $pt->name; ?>][sortby]" id="wpjm_sort<?php echo $pt->name; ?>by">
						<option value="menu_order" <?php selected( $wpjm_options['postTypes'][$pt->name]['sortby'], 'menu_order'); ?>>Menu Order</option>
						<option value="author" <?php selected( $wpjm_options['postTypes'][$pt->name]['sortby'], 'author'); ?>>Author</option>
						<option value="date" <?php selected( $wpjm_options['postTypes'][$pt->name]['sortby'], 'date'); ?>>Date</option>
						<option value="ID" <?php selected( $wpjm_options['postTypes'][$pt->name]['sortby'], 'ID'); ?>>ID</option>
						<option value="modified" <?php selected( $wpjm_options['postTypes'][$pt->name]['sortby'], 'modified'); ?>>Modified</option>
						<option value="comment_count" <?php selected( $wpjm_options['postTypes'][$pt->name]['sortby'], 'comment_count'); ?>>Comment Count</option>
						<option value="parent" <?php selected( $wpjm_options['postTypes'][$pt->name]['sortby'], 'parent'); ?>>Parent</option>
						<option value="title" <?php selected( $wpjm_options['postTypes'][$pt->name]['sortby'], 'title'); ?>>Title</option>
						<?php
						if ($pt->name == 'attachment') { ?>
						<option value="mime_type" <?php selected( $wpjm_options['postTypes'][$pt->name]['sortby'], 'mime_type'); ?>>Mime Type</option>
						<?php
						}
						?>
					</select>
					<br/><span class="description"><a href="http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters" target="_blank"><?php _e('Documentation', 'wp-jump-menu'); ?></a></span>
					<?php if ($pt->name == 'attachment' && isset($wpjm_options['postTypes'][$pt->name]['postmimetypes'])) { ?>
					<div class="mime-types">
						<br/>
						<strong>Show Media Types:</strong><br/>
						<input type="checkbox" value="all" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][postmimetypes][]" id="wpjm_postmimetypes<?php echo $pt->name; ?>" <?php if (is_array($wpjm_options['postTypes'][$pt->name]['postmimetypes'])) echo (in_array('all',$wpjm_options['postTypes'][$pt->name]['postmimetypes'])?' checked="checked"':''); ?> /> All
						<br/>
						<input type="checkbox" value="images" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][postmimetypes][]" id="wpjm_postmimetypes<?php echo $pt->name; ?>" <?php if (is_array($wpjm_options['postTypes'][$pt->name]['postmimetypes'])) echo (in_array('images',$wpjm_options['postTypes'][$pt->name]['postmimetypes'])?' checked="checked"':''); ?> /> Images
						<br/>
						<input type="checkbox" value="videos" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][postmimetypes][]" id="wpjm_postmimetypes<?php echo $pt->name; ?>" <?php if (is_array($wpjm_options['postTypes'][$pt->name]['postmimetypes'])) echo (in_array('videos',$wpjm_options['postTypes'][$pt->name]['postmimetypes'])?' checked="checked"':''); ?> /> Videos
						<br/>
						<input type="checkbox" value="audio" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][postmimetypes][]" id="wpjm_postmimetypes<?php echo $pt->name; ?>" <?php if (is_array($wpjm_options['postTypes'][$pt->name]['postmimetypes'])) echo (in_array('audio',$wpjm_options['postTypes'][$pt->name]['postmimetypes'])?' checked="checked"':''); ?> /> Audio
						<br/>
						<input type="checkbox" value="documents" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][postmimetypes][]" id="wpjm_postmimetypes<?php echo $pt->name; ?>" <?php if (is_array($wpjm_options['postTypes'][$pt->name]['postmimetypes'])) echo (in_array('documents',$wpjm_options['postTypes'][$pt->name]['postmimetypes'])?' checked="checked"':''); ?> /> Documents
						<br/>

					</div>
					<?php
					}
					?>
				</td>
				<td>
					<div>
						<input type="radio" value="ASC" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][sort]" id="wpjm_sort<?php echo $pt->name; ?>" <?php checked($wpjm_options['postTypes'][$pt->name]['sort'], 'ASC' ); ?> /> ASC <span class="description">(a-z, 1-10)</span>
						<br>
						<input type="radio" value="DESC" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][sort]" id="wpjm_sort<?php echo $pt->name; ?>" <?php checked($wpjm_options['postTypes'][$pt->name]['sort'], 'DESC' ); ?> /> DESC <span class="description">(z-a, 10-1)</span>
					</div>
				</td>
				<td>
					<div style="float: left; margin-right: 20px;">
						<input type="checkbox" value="publish" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][poststatus][]" id="wpjm_poststatus<?php echo $pt->name; ?>" <?php if (is_array($wpjm_options['postTypes'][$pt->name]['poststatus'])) echo (in_array('publish',$wpjm_options['postTypes'][$pt->name]['poststatus'])?' checked="checked"':''); ?> /> Publish<br/>

						<input type="checkbox" value="pending" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][poststatus][]" id="wpjm_poststatus<?php echo $pt->name; ?>" <?php if (is_array($wpjm_options['postTypes'][$pt->name]['poststatus'])) echo (in_array('pending',$wpjm_options['postTypes'][$pt->name]['poststatus'])?' checked="checked"':''); ?> /> Pending<br/>

						<input type="checkbox" value="draft" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][poststatus][]" id="wpjm_poststatus<?php echo $pt->name; ?>" <?php if (is_array($wpjm_options['postTypes'][$pt->name]['poststatus']))  echo (in_array('draft',$wpjm_options['postTypes'][$pt->name]['poststatus'])?' checked="checked"':''); ?> /> Draft<br/>

						<input type="checkbox" value="auto-draft" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][poststatus][]" id="wpjm_poststatus<?php echo $pt->name; ?>" <?php if (is_array($wpjm_options['postTypes'][$pt->name]['poststatus'])) echo (in_array('auto-draft',$wpjm_options['postTypes'][$pt->name]['poststatus'])?' checked="checked"':''); ?> /> Auto-Draft<br/>

						<input type="checkbox" value="future" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][poststatus][]" id="wpjm_poststatus<?php echo $pt->name; ?>" <?php if (is_array($wpjm_options['postTypes'][$pt->name]['poststatus'])) echo (in_array('future',$wpjm_options['postTypes'][$pt->name]['poststatus'])?' checked="checked"':''); ?> /> Future<br/>



					</div>
					<div style="float: left;">
					<input type="checkbox" value="private" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][poststatus][]" id="wpjm_poststatus<?php echo $pt->name; ?>" <?php if (is_array($wpjm_options['postTypes'][$pt->name]['poststatus'])) echo (in_array('private',$wpjm_options['postTypes'][$pt->name]['poststatus'])?' checked="checked"':''); ?> /> Private<br/>

						<input type="checkbox" value="inherit" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][poststatus][]" id="wpjm_poststatus<?php echo $pt->name; ?>" <?php if (is_array($wpjm_options['postTypes'][$pt->name]['poststatus'])) echo (in_array('inherit',$wpjm_options['postTypes'][$pt->name]['poststatus'])?' checked="checked"':''); ?> /> Inherit<br/>

						<input type="checkbox" value="trash" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][poststatus][]" id="wpjm_poststatus<?php echo $pt->name; ?>" <?php if (is_array($wpjm_options['postTypes'][$pt->name]['poststatus'])) echo (in_array('trash',$wpjm_options['postTypes'][$pt->name]['poststatus'])?' checked="checked"':''); ?> /> Trash<br/>

						<input type="checkbox" value="any" name="wpjm_options[postTypes][<?php echo $pt->name; ?>][poststatus][]" id="wpjm_poststatus<?php echo $pt->name; ?>" <?php if (is_array($wpjm_options['postTypes'][$pt->name]['poststatus'])) echo (in_array('any',$wpjm_options['postTypes'][$pt->name]['poststatus'])?' checked="checked"':''); ?> /> Any<br/>
						</div>
						<div style="clear: both;"><span class="description"><small><strong>NOTE:</strong> Trash items will only display if Any is NOT selected.<br/><strong>NOTE:</strong> If your items are not showing up, try choosing "Inherit" or "Any".</small></span></div>
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

function wpjm_options_validate( $input ) {
	$newinput = $input;
	foreach($newinput['postTypes'] as $key => $value) {
		if (!isset($newinput['postTypes'][$key]['show'])) {
			unset($newinput['postTypes'][$key]);
		} else {
			if (!isset($newinput['postTypes'][$key]['sort'])) {
				$newinput['postTypes'][$key]['sort'] = 'ASC';
			}
			if ($newinput['postTypes'][$key]['numberposts'] == "" || $newinput['postTypes'][$key]['numberposts'] < -1) {
				$newinput['postTypes'][$key]['numberposts'] = '-1';
			}
			if (!isset($newinput['postTypes'][$key]['poststatus'])) {
				$newinput['postTypes'][$key]['poststatus'] = array('publish');
				if ($key == 'attachment') {
					$newinput['postTypes'][$key]['poststatus'] = array('publish','inherit');
				}
			}
			if ($key == 'attachment') {
				if ( ( !isset($newinput['postTypes'][$key]['postmimetypes']) ) || ( in_array('all', $newinput['postTypes'][$key]['postmimetypes']) ) ) {
					$newinput['postTypes'][$key]['postmimetypes'] = array('all');
				}
			}

		}

	}
	return $newinput;
}


?>
