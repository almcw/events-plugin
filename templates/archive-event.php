<?php
/**
 * Events Plugin: archive template for the 'event' post type.
 *
 * Loaded via template_include when the active theme does not provide its own
 * archive-event.php. Uses get_header/get_footer so the site theme's chrome
 * (navigation, footer, etc.) wraps the content as normal.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main id="ep-archive" class="ep-archive">
	<div class="ep-container">

		<header class="ep-archive__header">
			<h1 class="ep-archive__title">
				<?php esc_html_e( 'Upcoming Events', 'events-plugin' ); ?>
			</h1>
		</header>

		<?php echo Events_Plugin_Query::render_events_list(); ?>

	</div>
</main>

<?php
get_footer();
