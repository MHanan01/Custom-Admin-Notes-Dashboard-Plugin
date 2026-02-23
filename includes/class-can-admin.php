<?php
/**
 * Admin logic for Custom Admin Notes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CAN_Admin {

	public function __construct() {
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register Dashboard Widget
	 */
	public function add_dashboard_widget() {
		if ( current_user_can( 'manage_options' ) ) {
			wp_add_dashboard_widget(
				'can_admin_notes_widget',
				__( 'Quick Admin Notes', 'custom-admin-notes' ),
				array( $this, 'render_widget' )
			);
		}
	}

	/**
	 * Enqueue CSS and JS
	 */
	public function enqueue_assets( $hook ) {
		if ( 'index.php' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'can-admin-style', CAN_URL . 'assets/css/admin-style.css', array(), CAN_VERSION );
		wp_enqueue_script( 'can-admin-script', CAN_URL . 'assets/js/admin-script.js', array( 'jquery' ), CAN_VERSION, true );

		wp_localize_script( 'can-admin-script', 'canData', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'can_nonce' ),
			'confirm_delete' => __( 'Are you sure you want to delete this note?', 'custom-admin-notes' )
		) );
	}

	/**
	 * Render Widget UI
	 */
	public function render_widget() {
		?>
		<div id="can-notes-container" class="can-widget-wrapper">
			<div class="can-header">
				<div class="can-search-box">
					<input type="text" id="can-search-input" placeholder="<?php esc_attr_e( 'Search notes...', 'custom-admin-notes' ); ?>">
				</div>
				<button type="button" id="can-toggle-form" class="button button-primary">
					<span class="dashicons dashicons-plus"></span> <?php _e( 'Add Note', 'custom-admin-notes' ); ?>
				</button>
			</div>

			<div id="can-note-form-wrapper" style="display:none;">
				<form id="can-note-form">
					<input type="hidden" id="can-note-id" value="">
					<div class="can-form-group">
						<input type="text" id="can-note-title" name="title" placeholder="<?php esc_attr_e( 'Title', 'custom-admin-notes' ); ?>" required>
					</div>
					<div class="can-form-group">
						<textarea id="can-note-content" name="content" placeholder="<?php esc_attr_e( 'Write your note...', 'custom-admin-notes' ); ?>" required></textarea>
					</div>
					<div class="can-form-group">
						<label><?php _e( 'Color Label:', 'custom-admin-notes' ); ?></label>
						<div class="can-color-options">
							<label class="can-color-radio info">
								<input type="radio" name="color_label" value="info" checked>
								<span>Info</span>
							</label>
							<label class="can-color-radio warning">
								<input type="radio" name="color_label" value="warning">
								<span>Warning</span>
							</label>
							<label class="can-color-radio important">
								<input type="radio" name="color_label" value="important">
								<span>Important</span>
							</label>
						</div>
					</div>
					<div class="can-form-actions">
						<button type="submit" class="button button-primary" id="can-save-note">
							<span class="can-btn-text"><?php _e( 'Save Note', 'custom-admin-notes' ); ?></span>
							<span class="spinner"></span>
						</button>
						<button type="button" id="can-cancel-form" class="button"><?php _e( 'Cancel', 'custom-admin-notes' ); ?></button>
					</div>
				</form>
			</div>

			<div id="can-notes-list" class="can-notes-list">
				<!-- Notes will be loaded here via AJAX -->
				<div class="can-loading">
					<span class="spinner is-active"></span>
					<?php _e( 'Loading notes...', 'custom-admin-notes' ); ?>
				</div>
			</div>
		</div>
		<?php
	}
}
