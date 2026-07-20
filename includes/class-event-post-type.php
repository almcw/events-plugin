<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Events_Plugin_Post_Type {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
	}

	public static function register() {
		$labels = array(
			'name'               => __( 'Events', 'events-plugin' ),
			'singular_name'      => __( 'Event', 'events-plugin' ),
			'add_new'            => __( 'Add New Event', 'events-plugin' ),
			'add_new_item'       => __( 'Add New Event', 'events-plugin' ),
			'edit_item'          => __( 'Edit Event', 'events-plugin' ),
			'new_item'           => __( 'New Event', 'events-plugin' ),
			'view_item'          => __( 'View Event', 'events-plugin' ),
			'view_items'         => __( 'View Events', 'events-plugin' ),
			'search_items'       => __( 'Search Events', 'events-plugin' ),
			'not_found'          => __( 'No events found.', 'events-plugin' ),
			'not_found_in_trash' => __( 'No events found in trash.', 'events-plugin' ),
			'all_items'          => __( 'All Events', 'events-plugin' ),
			'menu_name'          => __( 'Events', 'events-plugin' ),
		);

		register_post_type( 'event', array(
			'labels'        => $labels,
			'public'        => true,
			'has_archive'   => true,
			'supports'      => array( 'title', 'editor', 'thumbnail' ),
			'hierarchical'  => false,
			'menu_icon'     => 'dashicons-calendar-alt',
			'rewrite'       => array( 'slug' => 'events' ),
			'show_in_rest'  => true,
		) );
	}
}
