<?php
/*
Uninstall procedure (Removes the plugin cleanly)
*/


// Checks if it is accessed from Wordpress Admin
if ( ! function_exists( 'add_action' ) ) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
	
}


// Make sure that we are uninstalling
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
	
}



// Delete options from DB
delete_option( 'media_features_crop' );
delete_option( 'media_features_resize' );
delete_option( 'media_features_crop' );
delete_option( 'media_features_jpeg_quality' );
delete_option( 'media_features_plugin_version' );

// Remove Categories
$terms = get_terms( 'media_category' );
if ( ! $terms )
	return;

foreach ( $terms as $term )
	wp_delete_term( $term->term_id, 'media_category' );

// Bye! See you soon!
