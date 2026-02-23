<?php
/**
 * Database class for Custom Admin Notes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CAN_DB {

	/**
	 * Get table name with prefix
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'admin_notes';
	}

	/**
	 * Create the custom table
	 */
	public static function create_table() {
		global $wpdb;
		$table_name      = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			title varchar(255) NOT NULL,
			content text NOT NULL,
			color_label varchar(50) DEFAULT 'info',
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Drop table on uninstall (optional, usually kept for data safety)
	 */
	public static function drop_table() {
		global $wpdb;
		$table_name = self::get_table_name();
		$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
	}
}
