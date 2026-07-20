<?php
/**
 * Test data seeder — Events Plugin
 *
 * Creates 4 sample events: 2 in the past (should be hidden), 2 in the future
 * (should be visible, sorted soonest first). Used to verify that the archive
 * template and [events_list] shortcode both filter and sort correctly.
 *
 * Run with WP-CLI from your WordPress root:
 *   wp eval-file wp-content/plugins/events-plugin/bin/seed-test-events.php
 *
 * To remove all seeded events afterwards:
 *   wp post delete $(wp post list --post_type=event --format=ids) --force
 */

$events = array(
	array(
		'title'    => 'Saltburn Beach Clean [PAST — should be hidden]',
		'date'     => date( 'Y-m-d', strtotime( '-3 weeks' ) ),
		'time'     => '10:00',
		'location' => 'Saltburn Beach, North Yorkshire',
		'content'  => 'A community beach clean-up. This event is in the past and must NOT appear in any events list.',
	),
	array(
		'title'    => 'Saltburn Folk Festival [PAST — should be hidden]',
		'date'     => date( 'Y-m-d', strtotime( '-1 day' ) ),
		'time'     => '14:00',
		'location' => 'Saltburn Community Hall',
		'content'  => 'Annual folk festival. This event was yesterday and must NOT appear in any events list.',
	),
	array(
		'title'    => 'Saltburn Makers Market [FUTURE — should appear first]',
		'date'     => date( 'Y-m-d', strtotime( '+2 weeks' ) ),
		'time'     => '10:00',
		'location' => 'Saltburn Bandstand',
		'content'  => 'A monthly market showcasing local makers and crafters from across the Tees Valley. This is the sooner of the two future events and should appear first in the list.',
	),
	array(
		'title'    => 'Saltburn Kite Festival [FUTURE — should appear second]',
		'date'     => date( 'Y-m-d', strtotime( '+6 weeks' ) ),
		'time'     => '11:00',
		'location' => 'Saltburn Cliff Top',
		'content'  => 'An afternoon of kites, food trucks, and family fun on the Saltburn cliff top. This is the later of the two future events and should appear second in the list.',
	),
);

foreach ( $events as $event ) {
	$post_id = wp_insert_post( array(
		'post_type'    => 'event',
		'post_title'   => $event['title'],
		'post_content' => $event['content'],
		'post_status'  => 'publish',
	) );

	if ( is_wp_error( $post_id ) ) {
		echo 'ERROR creating: ' . $event['title'] . ' — ' . $post_id->get_error_message() . "\n";
		continue;
	}

	update_post_meta( $post_id, '_event_date',     $event['date'] );
	update_post_meta( $post_id, '_event_time',     $event['time'] );
	update_post_meta( $post_id, '_event_location', $event['location'] );

	echo 'Created (#' . $post_id . '): ' . $event['title'] . ' — ' . $event['date'] . "\n";
}

echo "\n";
echo "Seed complete. Verify:\n";
echo "  1. Visit /events/         — 2 events shown (Makers Market first, Kite Festival second)\n";
echo "  2. Add [events_list] to a page — same 2 events, same order\n";
echo "  3. Click each event — single page shows date, time, location, description\n";
echo "  4. Past events (Beach Clean, Folk Festival) are absent from both views\n";
