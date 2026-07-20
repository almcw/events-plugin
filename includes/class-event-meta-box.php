<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Events_Plugin_Meta_Box {

	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add' ) );
		add_action( 'save_post_event', array( __CLASS__, 'save' ) );
	}

	public static function add() {
		add_meta_box(
			'events_plugin_event_details',
			__( 'Event Details', 'events-plugin' ),
			array( __CLASS__, 'render' ),
			'event',
			'normal',
			'high'
		);
	}

	public static function render( $post ) {
		wp_nonce_field( 'events_plugin_save_meta', 'events_plugin_nonce' );

		$date     = get_post_meta( $post->ID, '_event_date', true );
		$time     = get_post_meta( $post->ID, '_event_time', true );
		$location = get_post_meta( $post->ID, '_event_location', true );
		?>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="event_date">
						<?php esc_html_e( 'Date', 'events-plugin' ); ?>
						<span style="color:#d63638;" aria-hidden="true">*</span>
					</label>
				</th>
				<td>
					<input
						type="date"
						id="event_date"
						name="event_date"
						value="<?php echo esc_attr( $date ); ?>"
						class="regular-text"
					/>
					<p class="description"><?php esc_html_e( 'Required. Format: YYYY-MM-DD.', 'events-plugin' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="event_time"><?php esc_html_e( 'Time', 'events-plugin' ); ?></label>
				</th>
				<td>
					<input
						type="time"
						id="event_time"
						name="event_time"
						value="<?php echo esc_attr( $time ); ?>"
						class="regular-text"
					/>
					<p class="description"><?php esc_html_e( 'Optional.', 'events-plugin' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="event_location"><?php esc_html_e( 'Location', 'events-plugin' ); ?></label>
				</th>
				<td>
					<input
						type="text"
						id="event_location"
						name="event_location"
						value="<?php echo esc_attr( $location ); ?>"
						class="large-text"
						placeholder="<?php esc_attr_e( 'e.g. Saltburn Community Hall', 'events-plugin' ); ?>"
					/>
				</td>
			</tr>
		</table>
		<?php
	}

	public static function save( $post_id ) {
		// Nonce check.
		if ( ! isset( $_POST['events_plugin_nonce'] ) ||
			 ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['events_plugin_nonce'] ) ), 'events_plugin_save_meta' ) ) {
			return;
		}

		// Skip autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Capability check.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save date — validate Y-m-d format.
		if ( isset( $_POST['event_date'] ) ) {
			$date = sanitize_text_field( wp_unslash( $_POST['event_date'] ) );
			if ( $date === '' || preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
				update_post_meta( $post_id, '_event_date', $date );
			}
		}

		// Save time.
		if ( isset( $_POST['event_time'] ) ) {
			update_post_meta( $post_id, '_event_time', sanitize_text_field( wp_unslash( $_POST['event_time'] ) ) );
		}

		// Save location.
		if ( isset( $_POST['event_location'] ) ) {
			update_post_meta( $post_id, '_event_location', sanitize_text_field( wp_unslash( $_POST['event_location'] ) ) );
		}
	}
}
