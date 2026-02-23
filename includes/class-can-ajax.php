<?php
/**
 * AJAX handler for Custom Admin Notes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CAN_AJAX {

	public function __construct() {
		add_action( 'wp_ajax_can_fetch_notes', array( $this, 'fetch_notes' ) );
		add_action( 'wp_ajax_can_save_note', array( $this, 'save_note' ) );
		add_action( 'wp_ajax_can_delete_note', array( $this, 'delete_note' ) );
	}

	/**
	 * Verify request security
	 */
	private function verify_request() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'custom-admin-notes' ) ) );
		}
		check_ajax_referer( 'can_nonce', 'nonce' );
	}

	/**
	 * Fetch notes (supports search)
	 */
	public function fetch_notes() {
		$this->verify_request();

		global $wpdb;
		$table_name = CAN_DB::get_table_name();
		$search     = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';

		$query = "SELECT * FROM $table_name";
		if ( ! empty( $search ) ) {
			$query .= $wpdb->prepare( " WHERE title LIKE %s OR content LIKE %s", '%' . $wpdb->esc_like( $search ) . '%', '%' . $wpdb->esc_like( $search ) . '%' );
		}
		$query .= " ORDER BY created_at DESC";

		$notes = $wpdb->get_results( $query );

		ob_start();
		if ( $notes ) {
			foreach ( $notes as $note ) {
				$this->render_note_item( $note );
			}
		} else {
			echo '<p class="can-no-notes">' . __( 'No notes found.', 'custom-admin-notes' ) . '</p>';
		}
		$html = ob_get_clean();

		wp_send_json_success( array( 'html' => $html ) );
	}

	/**
	 * Save/Update a note
	 */
	public function save_note() {
		$this->verify_request();

		global $wpdb;
		$table_name = CAN_DB::get_table_name();

		$id          = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$title       = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
		$content     = isset( $_POST['content'] ) ? wp_kses_post( $_POST['content'] ) : '';
		$color_label = isset( $_POST['color_label'] ) ? sanitize_text_field( $_POST['color_label'] ) : 'info';

		if ( empty( $title ) || empty( $content ) ) {
			wp_send_json_error( array( 'message' => __( 'Title and content are required.', 'custom-admin-notes' ) ) );
		}

		$data = array(
			'title'       => $title,
			'content'     => $content,
			'color_label' => $color_label,
		);

		if ( $id > 0 ) {
			$wpdb->update( $table_name, $data, array( 'id' => $id ) );
		} else {
			$wpdb->insert( $table_name, $data );
		}

		wp_send_json_success( array( 'message' => __( 'Note saved successfully!', 'custom-admin-notes' ) ) );
	}

	/**
	 * Delete a note
	 */
	public function delete_note() {
		$this->verify_request();

		global $wpdb;
		$table_name = CAN_DB::get_table_name();
		$id         = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

		if ( $id > 0 ) {
			$wpdb->delete( $table_name, array( 'id' => $id ) );
			wp_send_json_success( array( 'message' => __( 'Note deleted.', 'custom-admin-notes' ) ) );
		}

		wp_send_json_error( array( 'message' => __( 'Invalid ID.', 'custom-admin-notes' ) ) );
	}

	/**
	 * Render single note item HTML
	 */
	private function render_note_item( $note ) {
		?>
		<div class="can-note-item label-<?php echo esc_attr( $note->color_label ); ?>" data-id="<?php echo esc_attr( $note->id ); ?>">
			<div class="can-note-header">
				<h4 class="can-note-title"><?php echo esc_html( $note->title ); ?></h4>
				<div class="can-note-actions">
					<button type="button" class="can-edit-note dashicons dashicons-edit" title="Edit"></button>
					<button type="button" class="can-delete-note dashicons dashicons-trash" title="Delete"></button>
				</div>
			</div>
			<div class="can-note-body">
				<?php echo wpautop( esc_html( $note->content ) ); ?>
			</div>
			<div class="can-note-footer">
				<span class="can-date"><?php echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $note->created_at ) ); ?></span>
				<span class="can-label-pill"><?php echo ucfirst( $note->color_label ); ?></span>
			</div>
		</div>
		<?php
	}
}
