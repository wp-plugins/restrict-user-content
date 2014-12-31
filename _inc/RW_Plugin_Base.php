<?php
/**
 * Base Class for Mediaplus WordPress Plugins
 */

if( !class_exists('RW_Plugin_Base')):
class RW_Plugin_Base {
	
	/**
	 * @var holds the name of the file 
	 */
	protected $_filename = __FILE__;
	
	/**
	 * @ var The text domain for this plugin
	 */
	protected $_text_domain = 'mp-plugin';
	
	
	/**
	 * @var The settings page name
	 */
	protected $_pagename = '';
	
	/**
	 * @var Settings Page Titles
	 */
	protected $_settings_menu_title = 'RW Plugin Title';
	
	/**
	 * Construct
	 *
	 * @param bool Whether we need to show a settings page
	 * @param string The settings page slug
	 */
	function __construct( $file_reference, $has_settings_page = true, $settings_page_slug = '' ) {
		global $current_screen;
		
		//set some vars
		$this->_filename = $file_reference;
		$this->_pagename = $settings_page_slug;
		
		
		//activation hook
		register_activation_hook( $this->_filename, array(&$this,'rw_plugin_install') );
		
		//do we need a settings page?
		if( $has_settings_page ) {
			//add the settings link on the plugin screen
			add_filter('network_admin_plugin_action_links_'.  plugin_basename( $this->_filename ) ,  array( &$this,'rw_plugin_filter_action_links')); 
			add_filter('plugin_action_links_' .  plugin_basename( $this->_filename )  , array(&$this,'rw_plugin_filter_action_links')); 
		
			//create the settings page
			add_action('admin_menu',array(&$this, 'rw_plugin_settings_page') );
			
			//add the filter to the page title
			add_filter("{$this->_pagename}_settings_page_title", array( &$this, 'rw_settings_page_title_filter') );
			//add the action for the save setup
			add_action("{$this->_pagename}_plugin_save_options",array( &$this, 'rw_plugin_save_settings') );
			
			//add the meta boxes to the settings panel
			add_action('load-settings_page_'.$this->_pagename, array( &$this, 'rw_plugin_add_screen_meta_boxes' ) );
			add_action('admin_footer-settings_page_'.$this->_pagename, array( &$this, 'rw_plugin_print_script_in_footer') );
			//make the call to create some meta boxes
			add_action('add_meta_boxes_settings_page_'.$this->_pagename, array( &$this, 'rw_add_default_meta_boxes' ) );
			add_action('add_meta_boxes_settings_page_'.$this->_pagename, array( &$this, 'rw_plugin_create_meta_boxes' ) );
		}
	}
	
	
	//=============
	// Plugins page
	//=============
	
	/**
	 * Add the settings panel to the plugin page
	 * @param array The links for the plugin
	 * @return array
	 */
	function rw_plugin_filter_action_links($links) {
		$links['settings'] = sprintf( '<a href="%s"> %s </a>', admin_url( 'options-general.php?page='.$this->_pagename ), __( 'Settings', 'plugin_domain' ) );
		return $links;
	}
	
	//==============
	// SETTINGS PAGE
	//==============
	
	/**
	 * Creates the settings page
	 * 
	 * The callback function is in the Interface and should will be defined in the child class
	 *
	 */
	function rw_plugin_settings_page() {
		
		add_options_page(
			sprintf( __('%s', $this->_text_domain), $this->_settings_menu_title ),
			sprintf( __('%s', $this->_text_domain), $this->_settings_menu_title ),
			'manage_options',
			$this->_pagename,
			array( &$this, 'rw_plugin_render_settings_page')
		);
	}
	
	/**
	 * The method will pull in the default view pages
	 * 
	 * The settings page is more of a framework, it can be overridden but there is not much point
	 */
	function rw_plugin_render_settings_page(){
		include plugin_dir_path( $this->_filename ) .'_views/view-settings-page.php';
	}
	
	/**
	 * Settings Page Meta Boxes
	 */
	function rw_plugin_add_screen_meta_boxes() {
		/* Trigger the add_meta_boxes hooks to allow meta boxes to be added */
		do_action('add_meta_boxes_settings_page_'.$this->_pagename, null);
    	do_action('add_meta_boxes', 'settings_page_'.$this->_pagename, null);
		
		/* Enqueue WordPress' script for handling the meta boxes */
    	wp_enqueue_script('postbox');
		
		/* Add screen option: user can choose between 1 or 2 columns (default 2) */
    	add_screen_option('layout_columns', array('max' => 2, 'default' => 2) );
	}
	
	/**
	 * Adds the javascript for the meta boxes
	 */
	function rw_plugin_print_script_in_footer() {
		 ?>
    <script>jQuery(document).ready(function(){ postboxes.add_postbox_toggles(pagenow);});</script>
    <?php
	}
	
	/**
	 * Create a default save settings box
	 */
	function rw_add_default_meta_boxes() {
		add_meta_box(
			'save_settings', //Meta box ID
			__('Save Settings',$this->_text_domain), //Meta box Title
        array(&$this, 'rw_render_save_setting_box'), //Callback defining the plugin's innards
        'settings_page_'.$this->_pagename, // Screen to which to add the meta box
        'side' // Context
    	);
	}
	
	/**
	 * Render the save settings box
	 */
	function rw_render_save_setting_box() {
		?>
      <table class="form-table">
      <tr>
      	<td>
      	<input id="submit" class="button button-primary" type="submit" value="<?php _e('Save Changes',$this->_text_domain);?>" name="submit">
         </td>
         <td>
      	<input id="submit" class="button " type="submit" value="<?php _e('Reset To Defaults',$this->_text_domain);?>" name="reset">	
         </td>
      </tr>
      </table>
      <?php
	}
	
	/**
	 * Helpers
	 */
	

	/**
	 * Function to process custom fields
	 * @param array Array of custom fields to process. The key is the name of the custom field
	 * @param int Post Id to add the fields to 
	 * @todo add some error messaging when in dev_mode
	 */
	protected function rw_process_custom_fields($post_id, $custom_post_meta) {
		//check for the array so we don't break the loop
		if( $custom_post_meta ) {
			//save each item as a custom field
			foreach ($custom_post_meta as $key => $value) { // Cycle through the $custom_post_meta array!
			
			  if( wp_is_post_revision( $post_id ) ) {
				  return; // Don't store custom data twice
			  }
			  
			  $value = implode(',', (array)$value); // If $value is an array, make it a CSV (unlikely)
					
			  if(get_post_meta($post_id, $key, FALSE)) { // If the custom field already has a value
					update_post_meta($post_id, $key, $value);
			  } else { // If the custom field doesn't have a value
					add_post_meta($post_id, $key, $value);
			  }
			  
			  if (!$value) {
				delete_post_meta($post_id, $key); // Delete if blank
			  }
			}
		}
	}
}
endif;