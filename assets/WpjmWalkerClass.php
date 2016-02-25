<?php
// Custom Walker Class to walk through the page/custom post type hierarchy tree
class WPJM_Walker_PageDropDown extends Walker_PageDropDown {

	var $tree_type = "page";

	function start_el(&$output, $page, $depth = 0, $args = array(), $id = 0) {

		global $current_user, $post;

		// Get options to determine whether or not to show ID
		$options = get_option( 'wpjm_options' );

		$status_color = array(
		'publish' => (!empty($options['statusColors']['publish'])?'#'.$options['statusColors']['publish']:'#000000'),
		'pending' => (!empty($options['statusColors']['pending'])?'#'.$options['statusColors']['pending']:'#999999'),
		'draft' => (!empty($options['statusColors']['draft'])?'#'.$options['statusColors']['draft']:'#999999'),
		'auto-draft' => (!empty($options['statusColors']['auto-draft'])?'#'.$options['statusColors']['auto-draft']:'#999999'),
		'future' => (!empty($options['statusColors']['future'])?'#'.$options['statusColors']['future']:'#398f2c'),
		'private' => (!empty($options['statusColors']['private'])?'#'.$options['statusColors']['private']:'#999999'),
		'inherit' => (!empty($options['statusColors']['inherit'])?'#'.$options['statusColors']['inherit']:'#333333'),
		'trash' => (!empty($options['statusColors']['trash'])?'#'.$options['statusColors']['trash']:'#ff0000')
		);

		$pad = str_repeat(' &#8212;', $depth * 1);

		$editLink = (is_admin() || (!isset($options['frontEndJump']) || !$options['frontEndJump']) ? get_edit_post_link($page->ID) : get_permalink($page->ID));
		$output .= "\t<option data-permalink=\"".get_permalink($page->ID)."\" class=\"level-$depth\" value=\"".$editLink."\"";
		if ( (isset($_GET['post']) && ($page->ID == $_GET['post'])) || (isset($post) && ($page->ID == $post->ID)) )
			$output .= ' selected="selected"';

		$post_type_object = get_post_type_object( $args['post_type'] );

		if (!current_user_can($post_type_object->cap->edit_post,$page->ID))
			$output .= ' disabled="disabled"';

			$output .= ' style="color: '.$status_color['publish'].';"';
			// If the setting to show ID's is true, show the ID in ()
			if ( (isset($options['showID']) && $options['showID'] == true) ) {
				$output .= ' data-post-id="'.$page->ID.'"';
			}
		$output .= '>';
		$title = apply_filters( 'list_pages', $page->post_title );
		if (isset($options['useChosen']) && $options['useChosen'] == 'true' && (isset($options['chosenTextAlign']) && ($options['chosenTextAlign'] == 'right' || !isset($options['chosenTextAlign']) ) ) ) {
			$output .= esc_html( $title ) . $pad;
		} else {
			$output .= $pad . ' ' . esc_html( $title );
		}

		$output .= "</option>\n";
	}
}
// end WPJM_Walker_PageDropDown class
?>
