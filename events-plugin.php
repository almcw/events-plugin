<?php
/**
 * Plugin Name:       Events Plugin
 * Plugin URI:        https://happening.co.uk
 * Description:       Lightweight public events listing. Create events in wp-admin; display them sorted by date via archive template or [events_list] shortcode.
 * Version:           1.0.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * License:           GPL v2 or later
 * Text Domain:       events-plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EVENTS_PLUGIN_VERSION', '1.0.1' );
define( 'EVENTS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'EVENTS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once EVENTS_PLUGIN_DIR . 'includes/class-event-post-type.php';
require_once EVENTS_PLUGIN_DIR . 'includes/class-event-meta-box.php';
require_once EVENTS_PLUGIN_DIR . 'includes/class-event-query.php';

/*
 * =============================================================================
 * Hook stubs — reserved for future extensions
 * =============================================================================
 *
 * events_plugin_rsvp_created (action)
 *   Fires after a successful RSVP submission.
 *   Reserved for the future RSVP extension — do not implement here.
 *   When the RSVP extension is built, call:
 *     do_action( 'events_plugin_rsvp_created', $rsvp_data, $event_id );
 *   after persisting the RSVP record.
 */

// Activation: register CPT first so rewrite rules flush correctly.
register_activation_hook( __FILE__, function () {
	Events_Plugin_Post_Type::register();
	flush_rewrite_rules();
} );

// Deactivation: clean up rewrite rules.
register_deactivation_hook( __FILE__, function () {
	flush_rewrite_rules();
} );

// Bootstrap all plugin components.
Events_Plugin_Post_Type::init();
Events_Plugin_Meta_Box::init();
Events_Plugin_Query::init();
