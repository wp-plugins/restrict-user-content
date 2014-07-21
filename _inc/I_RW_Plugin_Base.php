<?php

/**
 * Interface for RW_Plugin_Base
 */

if(!interface_exists('I_RW_Plugin_Base' )):

interface I_RW_Plugin_Base {
	
	/**
	 * The installation hook callback
	 */
	function rw_plugin_install();
	
	/**
	 * Create the settings page meta boxes
	 */
	function rw_plugin_create_meta_boxes();
	
	/**
	 * Settings save method
	 */
	function rw_plugin_save_settings();
}

endif;