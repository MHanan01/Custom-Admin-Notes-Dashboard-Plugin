<?php
/**
 * Plugin Name: Quick Admin Notes
 * Plugin URI:  https://github.com/muhammad-hanan/quick-admin-notes
 * Description: Adds a simple admin notes dashboard widget.
 * Version:     1.1
 * Author:      Muhammad Hanan
 * Author URI:  https://muhammadhanan.com
 * License:     GPL2
 * Text Domain: custom-admin-notes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define Constants
define( 'CAN_PATH', plugin_dir_path( __FILE__ ) );
define( 'CAN_URL', plugin_dir_url( __FILE__ ) );
define( 'CAN_VERSION', '1.1' );

// Include required files
require_once CAN_PATH . 'includes/class-can-db.php';
require_once CAN_PATH . 'includes/class-can-admin.php';
require_once CAN_PATH . 'includes/class-can-ajax.php';

/**
 * Main Plugin Class
 */
class CustomAdminNotes {

	public function __construct() {
		// Activation & Deactivation
		register_activation_hook( __FILE__, array( 'CAN_DB', 'create_table' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
		
		// Initialize Components
		$this->init();
	}

	/**
	 * Deactivation logic
	 */
	public function deactivate() {
		// Clear scheduled tasks or other cleanup that doesn't involve deleting data
		flush_rewrite_rules();
	}

	private function init() {
		new CAN_Admin();
		new CAN_AJAX();
	}
}

// Initialize the plugin
new CustomAdminNotes();
