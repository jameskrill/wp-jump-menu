<?php

/**
 * Class WPJM_Select_Menu
 * @package WPJM\lib
 */
class WPJM_Select_Menu {

	private $options;
	private $wpjm_menu_html;
	private $status_colors = array();
	private $cached = false;
	private $post_id = 0;

	public function __construct( $cached = false ) {
		$this->cached = $cached;
		$this->options = get_option( 'wpjm_options' );

		if ( isset( $_GET['post_id'] ) )
			$this->post_id = $_GET['post_id'];
		
		$this->status_colors = $this->set_status_colors();
	}

	public function get_menu() {
		$this->wpjm_menu_html = '';
		$this->build_select_start();
		if ( $this->options['postTypes'] && ! empty( $this->options['postTypes'] ) ) {
			foreach ( $this->options['postTypes'] as $key => $value ) {
				$this->build_select_options($key, $value);
			}
		}
		$this->build_select_end();
		
		return $this->wpjm_menu_html;
	}
	
	private function build_select_start() {
		// Start echoing the select menu
		if ( isset( $this->options['useChosen'] ) && 'true' == $this->options['useChosen'] ) {
			$this->wpjm_menu_html .= '<select id="wp-pdd" data-placeholder="- Select to Edit -" class="chosen-select">';
			$this->wpjm_menu_html .= '<option></option>';
		} else {
			$this->wpjm_menu_html .= '<select id="wp-pdd">';
			$this->wpjm_menu_html .= '<option>-- Select to Edit --</option>';
		}

		$this->wpjm_menu_html = apply_filters( 'wpjm-filter-beginning-of-list', $this->wpjm_menu_html );
	}
	
	private function build_select_options($key, $value) {
		global $current_user, $post, $post_id, $options;

		$options = $this->options;
		$post_id = $this->post_id;

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
						break;

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
				'post_status'    => ( is_array( $post_status ) ? ( in_array( 'any', $post_status ) ? 'any' : $post_status ) : $post_status ),
			);

			if ( 'attachment' == $wpjm_cpt ) {
				$args['post_status'] = 'any';
			}

			if ( 'attachment' == $wpjm_cpt && ! empty( $postmimetype ) ) {
				$args['post_mime_type'] = $postmimetype;
			}

			if ( ! $this->cached ) {
				// Manually cache results
				$pd_posts = get_posts( $args );
				set_transient( 'wpjm_menu_' . $wpjm_cpt, $pd_posts );
			} else {
				// Manually get cache
				$pd_posts = get_transient( 'wpjm_menu_' . $wpjm_cpt );
				// Unless it doesn't exist, then use get_posts
				if ( ! $pd_posts ) {
					$pd_posts = get_posts( $args );
					set_transient( 'wpjm_menu_' . $wpjm_cpt, $pd_posts );
				}
			}

			// Count the posts
			$pd_total_posts = count( $pd_posts );

			$this->wpjm_menu_html .= '<optgroup label="' . $cpt_labels->name . '">';

			if ( 'Media' != $cpt_labels->name ) {

				if ( isset( $this->options['showaddnew'] ) && $this->options['showaddnew'] && current_user_can( $post_type_object->cap->edit_posts ) ) {
					$this->wpjm_menu_html .= '<option value="post-new.php?post_type=';
					$this->wpjm_menu_html .= $cpt_obj->name;
					$this->wpjm_menu_html .= '">+ Add New ' . $cpt_labels->singular_name . ' +</option>';
				}
			}

			// Order the posts by mime/type if this is attachments
			if ( ( 'attachment' == $wpjm_cpt ) && ( 'mime_type' == $sortby ) ) {
				function mime_sort( $a, $b ) {
					return strcmp( $a->post_mime_type, $b->post_mime_type );
				}

				usort( $pd_posts, 'mime_sort' );
			}

			// Loop through posts
			foreach ( $pd_posts as $pd_post ) {

				// Increase the interator by 1
				$pd_i ++;

				// Open the <option> tag
				$this->wpjm_menu_html .= '<option data-permalink="' . get_permalink( $pd_post->ID ) . '" data-post-id="' . $pd_post->ID . '" value="';
				// echo the edit link based on post ID
				$edit_link    = ( is_admin() || ( ! isset( $this->options['frontEndJump'] ) || ! $this->options['frontEndJump'] ) ? get_edit_post_link( $pd_post->ID ) : get_permalink( $pd_post->ID ) );
				$this->wpjm_menu_html .= $edit_link;
				$this->wpjm_menu_html .= '"';

				// Check to see if you are currently editing this post
				// If so, make it the selected value
				if ( ( isset( $_GET['post'] ) && ( $pd_post->ID == $_GET['post'] ) ) || ( isset( $post_id ) && ( $pd_post->ID == $post_id ) ) ) {
					$this->wpjm_menu_html .= ' selected="selected"';
				}

				if ( ! current_user_can( $post_type_object->cap->edit_post, $pd_post->ID ) ) {
					$this->wpjm_menu_html .= ' disabled="disabled"';
				}

				// Set the color
				if ( isset( $this->status_colors[ $pd_post->post_status ] ) ) {
					$this->wpjm_menu_html .= ' style="color: ' . $this->status_colors[ $pd_post->post_status ] . ';"';
				}

				// If the setting to show ID's is true, show the ID in ()
				if ( ( isset( $this->options['showID'] ) && true == $this->options['showID'] ) ) {
					$this->wpjm_menu_html .= ' data-show-post-id="true"';
				}

				// If the setting to show the post type is true, show it
				if ( ( isset( $this->options['showPostType'] ) && true == $this->options['showPostType'] ) ) {
					$this->wpjm_menu_html .= ' data-post-type="' . get_post_type( $pd_post->ID ) . '"';
				}

				$this->wpjm_menu_html .= '>';

				// Print the post title
				$this->wpjm_menu_html .= WPJM::get_page_title( $pd_post->post_title );

				if ( 'publish' != $pd_post->post_status && 'inherit' != $pd_post->post_status ) {
					$this->wpjm_menu_html .= ' - ' . $pd_post->post_status;
				}

				if ( 'attachment' == $pd_post->post_type ) {
					$this->wpjm_menu_html .= ' (' . $pd_post->post_mime_type . ')';
				}

				if ( 'future' == $pd_post->post_status ) {
					$this->wpjm_menu_html .= ' - ' . $pd_post->post_date;
				}

				// close the <option> tag
				$this->wpjm_menu_html .= '</option>';
			} // foreach ($pd_posts as $pd_post)

			$this->wpjm_menu_html .= '</optgroup>';

		} else {

			require_once( WPJM__PLUGIN_DIR . 'lib/class-wpjm-walker.php' );

			// If this a hierarchical post type, use the custom Walker class to create the page tree
			$ordered_list_walker = new WPJM_Walker_PageDropDown();

			$this->wpjm_menu_html .= '<optgroup label="' . $cpt_labels->name . '">';

			if ( isset( $this->options['showaddnew'] ) && $this->options['showaddnew'] && ( current_user_can( $post_type_object->cap->edit_posts ) || current_user_can( $post_type_object->cap->edit_pages ) ) ) {
				$this->wpjm_menu_html .= '<option value="post-new.php?post_type=';
				$this->wpjm_menu_html .= $cpt_obj->name;
				$this->wpjm_menu_html .= '">+ Add New ' . $cpt_labels->singular_name . ' +</option>';
			}

			// Go through the non-published pages
			foreach ( $post_status as $status ) {

				if ( 'publish' == $status ) {
					continue;
				}

				// Get pages
				$args = array(
					'orderby'        => $sortby,
					'order'          => $sort,
					'posts_per_page' => $numberposts,
					'post_type'      => $wpjm_cpt,
					'post_status'    => $status,
				);

				if ( ! $this->cached ) {
					// Manually cache results
					$pd_posts_drafts = get_posts( $args );
					set_transient( 'wpjm_menu_' . $wpjm_cpt . '_' . $status, $pd_posts_drafts );
				} else {
					// Manually get cache
					$pd_posts_drafts = get_transient( 'wpjm_menu_' . $wpjm_cpt . '_' . $status );
					// Unless it doesn't exist, then use get_posts
					if ( ! $pd_posts_drafts ) {
						$pd_posts_drafts = get_posts( $args );
						set_transient( 'wpjm_menu_' . $wpjm_cpt . '_' . $status, $pd_posts_drafts );
					}
				}

				// Loop through posts
				foreach ( $pd_posts_drafts as $pd_post ) {

					// Increase the interator by 1
					$pd_i ++;

					// Open the <option> tag
					$this->wpjm_menu_html .= '<option data-permalink="' . get_permalink( $pd_post->ID ) . '" data-post-id="' . $pd_post->ID . '" value="';
					// echo the edit link based on post ID
					$edit_link    = ( is_admin() || ( ! isset( $this->options['frontEndJump'] ) || ! $this->options['frontEndJump'] ) ? get_edit_post_link( $pd_post->ID ) : get_permalink( $pd_post->ID ) );
					$this->wpjm_menu_html .= $edit_link;
					$this->wpjm_menu_html .= '"';

					// Check to see if you are currently editing this post
					// If so, make it the selected value
					if ( ( isset( $_GET['post'] ) && ( $pd_post->ID == $_GET['post'] ) ) || ( isset( $post_id ) && ( $pd_post->ID == $post_id ) ) ) {
						$this->wpjm_menu_html .= ' selected="selected"';
					}

					if ( ! current_user_can( $post_type_object->cap->edit_post, $pd_post->ID ) ) {
						$this->wpjm_menu_html .= ' disabled="disabled"';
					}

					// Set the color
					if ( isset( $this->status_colors[ $pd_post->post_status ] ) ) {
						$this->wpjm_menu_html .= ' style="color: ' . $this->status_colors[ $pd_post->post_status ] . ';"';
					}

					// If the setting to show ID's is true, show the ID in ()
					if ( ( isset( $this->options['showID'] ) && true == $this->options['showID'] ) ) {
						$this->wpjm_menu_html .= ' data-show-post-id="true"';
					}

					// If the setting to show the post type is true, show it
					if ( ( isset( $this->options['showPostType'] ) && true == $this->options['showPostType'] ) ) {
						$this->wpjm_menu_html .= ' data-post-type="' . get_post_type( $pd_post->ID ) . '"';
					}

					$this->wpjm_menu_html .= '>';

					// Print the post title
					$this->wpjm_menu_html .= WPJM::get_page_title( $pd_post->post_title );

					if ( 'publish' != $pd_post->post_status ) {
						$this->wpjm_menu_html .= ' - ' . $status;
					}

					if ( 'future' == $pd_post->post_status ) {
						$this->wpjm_menu_html .= ' - ' . $pd_post->post_date;
					}

					// close the <option> tag
					$this->wpjm_menu_html .= '</option>';

				} // foreach ($pd_posts as $pd_post)

			}
			// Done with non-published pages
			if ( is_array( $post_status ) ) {

				if ( in_array( 'publish', $post_status ) ) {

					$args = array(
						'walker'      => $ordered_list_walker,
						'post_type'   => $wpjm_cpt,
						'echo'        => 0,
						'depth'       => $numberposts,
						'sort_column' => $sortby,
						'sort_order'  => $sort,
						'title_li'    => '',
					);

					if ( ! $this->cached ) {
						// Manually cache results
						$pd_pages = wp_list_pages( $args );
						$pd_pages = str_replace( ' selected="selected"', '', $pd_pages );
						set_transient( 'wpjm_menu_' . $wpjm_cpt, $pd_pages );
					} else {
						// Manually get cache
						$pd_pages = get_transient( 'wpjm_menu_' . $wpjm_cpt );
						// Unless it doesn't exist, then use get_posts
						if ( ! $pd_pages || empty( $pd_pages ) ) {
							$pd_pages = wp_list_pages( $args );
							$pd_pages = str_replace( ' selected="selected"', '', $pd_pages );
							set_transient( 'wpjm_menu_' . $wpjm_cpt, $pd_pages );
						}
					}

					$this->wpjm_menu_html .= $pd_pages;

				}
			} elseif ( 'publish' == $post_status ) {

				$args = array(
					'walker'      => $ordered_list_walker,
					'post_type'   => $wpjm_cpt,
					'echo'        => 0,
					'depth'       => $numberposts,
					'sort_column' => $sortby,
					'sort_order'  => $sort,
					'title_li'    => '',
				);

				if ( ! $this->cached ) {
					// Manually cache results
					$pd_pages = wp_list_pages( $args );
					$pd_pages = str_replace( ' selected="selected"', '', $pd_pages );
					set_transient( 'wpjm_menu_' . $wpjm_cpt, $pd_pages );
				} else {
					// Manually get cache
					$pd_pages = get_transient( 'wpjm_menu_' . $wpjm_cpt );
					// Unless it doesn't exist, then use get_posts
					if ( ! $pd_pages || empty( $pd_pages ) ) {
						$pd_pages = wp_list_pages( $args );
						$pd_pages = str_replace( ' selected="selected"', '', $pd_pages );
						set_transient( 'wpjm_menu_' . $wpjm_cpt, $pd_pages );
					}
				}

				$this->wpjm_menu_html .= $pd_pages;
			}

			$this->wpjm_menu_html .= '</optgroup>';
		} // end if (is_hierarchical)
	}
	
	private function build_select_end() {

		$this->wpjm_menu_html = apply_filters( 'wpjm-filter-end-of-list', $this->wpjm_menu_html );

		// Print the options page link
		if ( current_user_can( 'activate_plugins' ) ) {

			$this->wpjm_menu_html .= '<optgroup label="// Jump Menu Options //">';
			$this->wpjm_menu_html .= '<option value="' . admin_url() . 'options-general.php?page=wpjm-options">Jump Menu Options Page</option>';
			$this->wpjm_menu_html .= '</optgroup>';

		}

		// Close the select drop down
		$this->wpjm_menu_html .= '</select>';
		
	}
	
	private function set_status_colors() {
		return array(
			'publish'    => ( ! empty( $this->options['statusColors']['publish'] ) ? '#' . $this->options['statusColors']['publish'] : '' ),
			'pending'    => ( ! empty( $this->options['statusColors']['pending'] ) ? '#' . $this->options['statusColors']['pending'] : '' ),
			'draft'      => ( ! empty( $this->options['statusColors']['draft'] ) ? '#' . $this->options['statusColors']['draft'] : '' ),
			'auto-draft' => ( ! empty( $this->options['statusColors']['auto-draft'] ) ? '#' . $this->options['statusColors']['auto-draft'] : '' ),
			'future'     => ( ! empty( $this->options['statusColors']['future'] ) ? '#' . $this->options['statusColors']['future'] : '' ),
			'private'    => ( ! empty( $this->options['statusColors']['private'] ) ? '#' . $this->options['statusColors']['private'] : '' ),
			'inherit'    => ( ! empty( $this->options['statusColors']['inherit'] ) ? '#' . $this->options['statusColors']['inherit'] : '' ),
			'trash'      => ( ! empty( $this->options['statusColors']['trash'] ) ? '#' . $this->options['statusColors']['trash'] : '' ),
		);
	}

}