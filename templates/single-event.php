<?php
/**
 * Events Plugin: single event template.
 *
 * Loaded via template_include when the active theme does not provide its own
 * single-event.php. Uses get_header/get_footer for normal theme chrome.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$post_id = get_the_ID();

	/**
	 * Filter: events_plugin_can_view_event
	 *
	 * Return false to prevent the current user seeing this event.
	 * Defaults to true (all events visible) in the MVP.
	 * Reserved for a future member-gating extension.
	 *
	 * @param bool $can_view Whether the current user can see this event.
	 * @param int  $post_id  The event post ID.
	 */
	if ( ! apply_filters( 'events_plugin_can_view_event', true, $post_id ) ) :
		?>
		<div class="ep-container">
			<p class="ep-access-denied">
				<?php esc_html_e( 'This event is not available.', 'events-plugin' ); ?>
			</p>
			<p class="ep-back-link">
				<a href="<?php echo esc_url( get_post_type_archive_link( 'event' ) ); ?>">
					&larr; <?php esc_html_e( 'All Events', 'events-plugin' ); ?>
				</a>
			</p>
		</div>
		<?php
		continue;
	endif;

	$date     = get_post_meta( $post_id, '_event_date', true );
	$time     = get_post_meta( $post_id, '_event_time', true );
	$location = get_post_meta( $post_id, '_event_location', true );

	/**
	 * Filter: events_plugin_event_display_data
	 *
	 * Modify event data before it is rendered into a template.
	 * Reserved for future extensions that need to transform display values.
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
	<article id="ep-event-<?php the_ID(); ?>" class="ep-single-event">
		<div class="ep-container">

			<header class="ep-single-event__header">
				<h1 class="ep-single-event__title"><?php the_title(); ?></h1>

				<ul class="ep-single-event__meta">
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
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
			<div class="ep-single-event__image">
				<?php the_post_thumbnail( 'large', array( 'class' => 'ep-single-event__img' ) ); ?>
			</div>
			<?php endif; ?>

			<div class="ep-single-event__content">
				<?php the_content(); ?>
			</div>

			<?php
			/*
			 * RSVP section — reserved for future RSVP extension.
			 *
			 * Filter: events_plugin_can_rsvp
			 *   Controls whether the current user is offered an RSVP option.
			 *   Defaults to false in the MVP (no RSVP UI is shown).
			 *   The future RSVP extension hooks here to enable booking.
			 *
			 * Filter: events_plugin_rsvp_form_fields
			 *   Modify the RSVP form fields array before the form is rendered.
			 *   Returns an empty array in the MVP.
			 *   The future RSVP extension hooks here to inject form fields.
			 *
			 * Action: events_plugin_render_rsvp_form
			 *   Fires when an RSVP form should be rendered.
			 *   The future RSVP extension hooks here to output the form HTML.
			 */
			$can_rsvp = apply_filters( 'events_plugin_can_rsvp', false, $post_id );
			if ( $can_rsvp ) {
				$fields = apply_filters( 'events_plugin_rsvp_form_fields', array(), $post_id );
				do_action( 'events_plugin_render_rsvp_form', $fields, $post_id );
			}
			?>

			<p class="ep-back-link">
				<a href="<?php echo esc_url( get_post_type_archive_link( 'event' ) ); ?>">
					&larr; <?php esc_html_e( 'All Events', 'events-plugin' ); ?>
				</a>
			</p>

		</div>
	</article>
	<?php

endwhile;

get_footer();
