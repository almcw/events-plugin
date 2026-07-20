<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Events_Plugin_Query {

	public static function init() {
		add_filter( 'template_include',   array( __CLASS__, 'load_templates' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_shortcode( 'events_list',     array( __CLASS__, 'shortcode' ) );
	}

	// -------------------------------------------------------------------------
	// Query
	// -------------------------------------------------------------------------

	/**
	 * Build WP_Query args for all event lists, then pass them through the
	 * events_plugin_event_query_args filter.
	 *
	 * Default behaviour (MVP):
	 *   - Post type: event, published only.
	 *   - Exclude events whose _event_date is before today.
	 *   - Sort ascending by _event_date (soonest first).
	 *
	 * Future extensions should hook events_plugin_event_query_args to add
	 * further constraints (e.g. region, capacity) without modifying this file.
	 *
	 * @return array WP_Query arguments.
	 */
	public static function get_query_args() {
		$today = gmdate( 'Y-m-d' );

		$args = array(
			'post_type'      => 'event',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'meta_key'       => '_event_date',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'no_found_rows'  => true,
			'meta_query'     => array(
				array(
					'key'     => '_event_date',
					'value'   => $today,
					'compare' => '>=',
					'type'    => 'DATE',
				),
			),
		);

		/**
		 * Filter: events_plugin_event_query_args
		 *
		 * Modify the WP_Query arguments used to fetch events for all list views
		 * (archive and shortcode). Used in this MVP to hide past events and sort
		 * ascending. Future extensions hook here — e.g. to filter by member
		 * region or cap results by available capacity.
		 *
		 * @param array $args Default WP_Query arguments.
		 */
		return apply_filters( 'events_plugin_event_query_args', $args );
	}

	// -------------------------------------------------------------------------
	// Rendering
	// -------------------------------------------------------------------------

	/**
	 * Run the events query and return rendered list HTML.
	 * Called by both the archive template and the [events_list] shortcode so
	 * the output is always identical regardless of how it is embedded.
	 *
	 * @return string HTML output.
	 */
	public static function render_events_list() {
		$query = new WP_Query( self::get_query_args() );

		ob_start();

		if ( ! $query->have_posts() ) {
			echo '<p class="ep-no-events">' . esc_html__( 'No upcoming events.', 'events-plugin' ) . '</p>';
		} else {
			echo '<ul class="ep-events-list">';

			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();

				/**
				 * Filter: events_plugin_can_view_event
				 *
				 * Return false to hide this event from the current user.
				 * Defaults to true (all events visible) in the MVP.
				 * Reserved for a future member-gating extension.
				 *
				 * @param bool $can_view Whether the current user can see this event.
				 * @param int  $post_id  The event post ID.
				 */
				if ( ! apply_filters( 'events_plugin_can_view_event', true, $post_id ) ) {
					continue;
				}

				$date     = get_post_meta( $post_id, '_event_date', true );
				$time     = get_post_meta( $post_id, '_event_time', true );
				$location = get_post_meta( $post_id, '_event_location', true );

				/**
				 * Filter: events_plugin_event_display_data
				 *
				 * Modify event data before it is rendered into a template.
				 * Reserved for future extensions that need to transform display
				 * values (e.g. format dates differently, append venue details,
				 * inject capacity remaining).
				 *
				 * @param array $data    Event display data.
				 * @param int   $post_id The event post ID.
				 */
				$data = apply_filters( 'events_plugin_event_display_data', array(
					'date'     => $date,
					'time'     => $time,
					'location' => $location,
					'title'    => get_the_title(),
					'url'      => get_permalink(),
				), $post_id );

				$date_formatted = $data['date']
					? date_i18n( get_option( 'date_format' ), strtotime( $data['date'] ) )
					: '';

				$time_formatted = $data['time']
					? date_i18n( get_option( 'time_format' ), strtotime( '2000-01-01 ' . $data['time'] ) )
					: '';

				?>
				<li class="ep-event-card">

					<?php if ( has_post_thumbnail( $post_id ) ) : ?>
					<a class="ep-event-card__image-wrap" href="<?php echo esc_url( $data['url'] ); ?>" tabindex="-1" aria-hidden="true">
						<?php echo get_the_post_thumbnail( $post_id, 'medium', array(
							'class' => 'ep-event-card__image',
							'alt'   => esc_attr( $data['title'] ),
						) ); ?>
					</a>
					<?php endif; ?>

					<div class="ep-event-card__body">

						<h2 class="ep-event-card__title">
							<a href="<?php echo esc_url( $data['url'] ); ?>"><?php echo esc_html( $data['title'] ); ?></a>
						</h2>

						<ul class="ep-event-card__meta">
							<?php if ( $date_formatted ) : ?>
							<li class="ep-meta ep-meta--date">
								<span class="ep-meta__label"><?php esc_html_e( 'Date', 'events-plugin' ); ?></span>
								<time class="ep-meta__value" datetime="<?php echo esc_attr( $data['date'] ); ?>">
									<?php echo esc_html( $date_formatted ); ?>
								</time>
							</li>
							<?php endif; ?>
							<?php if ( $time_formatted ) : ?>
							<li class="ep-meta ep-meta--time">
								<span class="ep-meta__label"><?php esc_html_e( 'Time', 'events-plugin' ); ?></span>
								<span class="ep-meta__value"><?php echo esc_html( $time_formatted ); ?></span>
							</li>
							<?php endif; ?>
							<?php if ( $data['location'] ) : ?>
							<li class="ep-meta ep-meta--location">
								<span class="ep-meta__label"><?php esc_html_e( 'Location', 'events-plugin' ); ?></span>
								<span class="ep-meta__value"><?php echo esc_html( $data['location'] ); ?></span>
							</li>
							<?php endif; ?>
						</ul>

						<a class="ep-event-card__cta" href="<?php echo esc_url( $data['url'] ); ?>">
							<?php esc_html_e( 'View event', 'events-plugin' ); ?>
							<span class="screen-reader-text">&nbsp;&mdash;&nbsp;<?php echo esc_html( $data['title'] ); ?></span>
						</a>

					</div>
				</li>
				<?php
			}

			wp_reset_postdata();
			echo '</ul>';
		}

		return ob_get_clean();
	}

	// -------------------------------------------------------------------------
	// Shortcode
	// -------------------------------------------------------------------------

	/**
	 * [events_list] shortcode.
	 *
	 * Embeds the upcoming events list on any page or post.
	 * Accepts no attributes in the MVP; the query is controlled via the
	 * events_plugin_event_query_args filter.
	 *
	 * @param array $atts Shortcode attributes (unused in MVP).
	 * @return string     Rendered events list HTML.
	 */
	public static function shortcode( $atts ) {
		shortcode_atts( array(), $atts, 'events_list' );
		return '<div class="ep-events-shortcode">' . self::render_events_list() . '</div>';
	}

	// -------------------------------------------------------------------------
	// Template loading
	// -------------------------------------------------------------------------

	/**
	 * Serve plugin templates when the active theme does not provide its own
	 * archive-event.php or single-event.php (theme templates take priority).
	 */
	public static function load_templates( $template ) {
		if ( is_post_type_archive( 'event' ) && ! locate_template( 'archive-event.php' ) ) {
			$plugin_tpl = EVENTS_PLUGIN_DIR . 'templates/archive-event.php';
			if ( file_exists( $plugin_tpl ) ) {
				return $plugin_tpl;
			}
		}

		if ( is_singular( 'event' ) && ! locate_template( 'single-event.php' ) ) {
			$plugin_tpl = EVENTS_PLUGIN_DIR . 'templates/single-event.php';
			if ( file_exists( $plugin_tpl ) ) {
				return $plugin_tpl;
			}
		}

		return $template;
	}

	// -------------------------------------------------------------------------
	// Assets
	// -------------------------------------------------------------------------

	/**
	 * Enqueue the plugin stylesheet.
	 * Loaded on all front-end pages so the [events_list] shortcode is styled
	 * wherever it is placed. The file is small — this is intentional.
	 */
	public static function enqueue_assets() {
		wp_enqueue_style(
			'events-plugin',
			EVENTS_PLUGIN_URL . 'assets/events-plugin.css',
			array(),
			EVENTS_PLUGIN_VERSION
		);
	}
}
