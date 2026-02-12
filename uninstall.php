<?php
/**
 * Cinema Brief Engine — Uninstall Handler
 * Runs when the plugin is deleted from WordPress.
 * Cleans up all plugin data from the database.
 *
 * @package CinemaBrief
 * @since 3.3
 */

// Security: Only run if WordPress initiated the uninstall
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

/**
 * Remove all plugin post meta from movie_reviews posts.
 * This is a destructive operation — only runs on plugin DELETE, not deactivation.
 */
$meta_keys = array(
    '_cb_rating',
    '_cb_movie_title',
    '_cb_director',
    '_cb_cast',
    '_cb_duration',
    '_cb_release_date',
    '_cb_verdict',
    '_cb_synopsis',
    '_cb_pros',
    '_cb_cons',
    '_cb_schema_json',
    '_cb_schema_lock',
);

// Delete all post meta for each key
foreach ( $meta_keys as $key ) {
    delete_metadata( 'post', 0, $key, '', true );
}

// Note: We do NOT delete the movie_reviews posts or taxonomy terms.
// Those are user content and should remain even if the plugin is removed.
// If you want to delete them too, uncomment the section below:

/*
// WARNING: This permanently deletes ALL movie review posts!
$reviews = get_posts( array(
    'post_type'      => 'movie_reviews',
    'posts_per_page' => -1,
    'post_status'    => 'any',
    'fields'         => 'ids',
) );
foreach ( $reviews as $id ) {
    wp_delete_post( $id, true );
}
*/

// Flush rewrite rules
flush_rewrite_rules();
