<?php
/**
 * Plugin Name: Restrict User Content
 * Description: Limits the Posts/Media pages to only show content created by the logged in user.
 * Author: Ryan Welcher
 * Version: 1.0.2
 * Author URI: http://www.ryanwelcher.com
 * Text Domain: ruc
 */

if ( ! class_exists('Restrict_User_Content') ) :

//get the base class
	if ( ! class_exists( 'RW_Plugin_Base' ) ) {
		require_once plugin_dir_path( __FILE__ ) . '/_inc/RW_Plugin_Base.php';
	}
//get the interface
	if ( !interface_exists( 'I_RW_Plugin_Base' ) ) {
		require_once plugin_dir_path( __FILE__ ) . '/_inc/I_RW_Plugin_Base.php';
	}



	/**
	 * Class Definition
	 */
	class Restrict_User_Content extends RW_Plugin_Base implements I_RW_Plugin_Base {

		/**
		 * @var bool Does this plugin need a settings page?
		 */
		private $_has_settings_page = true;

		/**
		 * var string Slug name for the settings page
		 */
		private $_settings_page_name = 'restrict_user_content_settings_page';


		/**
		 * @var array default settings
		 */
		private $_default_settings = array(
			'additional_user_ids' => '0',
		);


		/**
		 * @var The name of the settings in the database
		 */
		private $_settings_name = 'restrict_user_content_settings';


		/**
		 * Construct
		 */
		function __construct() {

			//call super class constructor
			parent::__construct( __FILE__, $this->_has_settings_page, $this->_settings_page_name );

			//set some details
			$this->_settings_menu_title = 'Restrict User Content';

			//Start your custom goodness
			add_action( 'pre_get_posts', 				array( $this, 'ruc_pre_get_posts_media_user_only' ) );
			add_filter( 'parse_query',					array( $this, 'ruc_parse_query_useronly'		) );
			add_filter( 'ajax_query_attachments_args',	array( $this, 'ruc_ajax_attachments_useronly'		) );




		}


		//=================
		// ACTION CALLBACKS
		//=================

		/**
		 * Augment the query on the media page
		 *
		 * This is tied into the settings to show media uploaded by the user and
		 * any others as indicated in the settings panel. This will allow site admins to create
		 * a sandbox with images that are available to all users.
		 */
		function ruc_pre_get_posts_media_user_only( $query ) {

			if(strpos( $_SERVER[ 'REQUEST_URI' ], '/wp-admin/upload.php' ) !== false ) {

				$query->set( 'author__in', array('1') );

				if ( !current_user_can( 'update_core' ) ) {
					$query->set( 'author__in', array('1')/*$this->ruc_create_list_of_user_ids()*/ );
				}
			}
		}


		//=================
		// FILTER CALLBACKS
		//=================


		/**
		 * Only show the posts for the current non-admin user.
		 *
		 * Great function written by Sarah Gooding.
		 * Slightly updated to use wp_get_current_user() instead of globalizing the $current_user variable
		 *
		 * @link {http://premium.wpmudev.org/blog/how-to-limit-the-wordpres-posts-screen-to-only-show-authors-their-own-posts/}
		 */
		function ruc_parse_query_useronly( $wp_query ) {
			if ( strpos( $_SERVER[ 'REQUEST_URI' ], '/wp-admin/edit.php' ) !== false ) {
				if ( !current_user_can( 'update_core' ) ) {
					$current_user = wp_get_current_user();
					$wp_query->set( 'author', $current_user->ID );
				}
			}
		}


		/**
		 * Filter the media uploader similar to the pre_get_post
		 */
		function ruc_ajax_attachments_useronly( $query ) {

			if( !current_user_can( 'update_core' ) ) {
				$users = $this->ruc_create_list_of_user_ids();

				$query['author__in'] = $users; //array( '373' );
			}

			return $query;
		}




		/**
		 * Parse the array for the user list
		 * @return array An array of all of the allows user ID and the current user
		 */
		private function ruc_create_list_of_user_ids() {

			$current_user = wp_get_current_user();

			$settings = ( $option = get_option($this->_settings_name) ) ? $option : $this->_default_settings;
			//create the array from the string
			$users = explode(',', $settings['additional_user_ids'] );
			//add the the current user id to the beginning
			array_unshift( $users , $current_user->ID );
			return $users;
		}





		//=================
		// SETTINGS PAGE
		//=================
		/**
		 * Install
		 *
		 * Required by the interface - can be stubbed out if nothing is required on activation
		 * @used-by register_activation_hook() in the parent class
		 */
		function rw_plugin_install() {

			if( $this->_has_settings_page ) {

				//look for the settings
				$settings = get_option($this->_settings_name);

				if(!$settings) {
					add_option( $this->_settings_name, $this->_default_settings );
				}else{

					if( isset( $_POST[$this->_settings_name] ) ) {
						$updated_settings = wp_parse_args( $_POST[$this->_settings_name], $this->_default_settings );
					}else{
						$updated_settings = get_option( $this->_settings_name );
					}

					update_option( $this->_settings_name, $updated_settings );
				}
			}
		}


		/**
		 * Settings Page Meta Boxes
		 *
		 * Hook to create the settings meta boxes
		 * Required by the interface
		 *
		 * @used-by add_meta_boxes_settings_page_{$this->_pagename} action  in the parent class
		 */
		function rw_plugin_create_meta_boxes() {

			//debug area
			add_meta_box(
				'debug_area', //Meta box ID
				__('Debug', 'ruc'), //Meta box Title
				array(&$this, 'rw_render_debug_setting_box'), //Callback defining the plugin's innards
				'settings_page_'.$this->_pagename, // Screen to which to add the meta box
				'side' // Context
			);

			//-- additional users to allow
			add_meta_box(
				'additional_users',
				__('Additional Media', 'ruc'),
				array( &$this, 'render_additional_users_meta_box'),
				'settings_page_'.$this->_pagename, // Screen to which to add the meta box
				'normal' // Context
			);

		}

		/**
		 * Render the debug meta box
		 */
		function rw_render_debug_setting_box() {
			$settings = $this->get_settings();
			?>
			<table class="form-table">
				<tr>
					<td colspan="2">
						<textarea class="widefat" rows="10"><?php print_r( $settings );?></textarea>
					</td>
				</tr>
			</table>
		<?php
		}



		/**
		 * Callback for the additional_users meta box
		 */
		function render_additional_users_meta_box() {
			$settings = $this->get_settings();
			include plugin_dir_path( __FILE__ ) . '/_views/additional-users-meta-box.php';
		}

		/**
		 * Method to save the  settings
		 *
		 * Saves the settings
		 * Required by the interface
		 *
		 * @used-by Custom action "rw_plugin_save_options" in the parent class
		 */
		function rw_plugin_save_settings() {
			//lets just make sure we can save
			if ( !empty($_POST) && check_admin_referer( "{$this->_pagename}_save_settings", "{$this->_pagename}_settings_nonce" ) ) {
				//save
				if( isset( $_POST['submit'] ) ) {
					//status message
					$old_settings = get_option( $this->_settings_name );
					$updated_settings = wp_parse_args( $_POST[$this->_settings_name], $old_settings );
					update_option($this->_settings_name, $updated_settings);
					printf('<div class="updated"> <p> %s </p> </div>', __('Settings Saved', 'ruc' ) );
				}

				//reset
				if( isset( $_POST['reset'] ) ) {
					//status message
					update_option($this->_settings_name, $this->_default_settings );
					printf('<div class="error"> <p> %s </p> </div>', __('Settings reset to defaults', 'ruc') );
				}
			}
		}

		/**
		 * Retrieve the plugin settings
		 * @return  array Saved settings for this plugin
		 */
		function get_settings( ) {
			$settings = ( $option = get_option($this->_settings_name) ) ? $option : $this->_default_settings;
			return $settings;
		}


		/**
		 * Filters the name of the settings page
		 * uses the custom filter "rw_settings_page_title"
		 */
		function rw_settings_page_title_filter($title) {
			return __('Restrict User Content Settings', 'ruc');
		}
	}


//create an instance of the class
	new Restrict_User_Content();

endif;