<?php
/**
 * Cinema Brief Engine — Taxonomies
 * Registers custom taxonomies for Movie Language and Movie Genre.
 *
 * @package CinemaBrief
 * @since 3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =============================================================================
// REGISTER CUSTOM TAXONOMIES
// =============================================================================
function cb_register_taxonomies() {

    // 1. Language (for inLanguage schema detection)
    register_taxonomy( 'movie_language', 'movie_reviews', array(
        'labels' => array(
            'name'              => __( 'Languages', 'cinemabrief' ),
            'singular_name'     => __( 'Language', 'cinemabrief' ),
            'search_items'      => __( 'Search Languages', 'cinemabrief' ),
            'all_items'         => __( 'All Languages', 'cinemabrief' ),
            'edit_item'         => __( 'Edit Language', 'cinemabrief' ),
            'update_item'       => __( 'Update Language', 'cinemabrief' ),
            'add_new_item'      => __( 'Add New Language', 'cinemabrief' ),
            'new_item_name'     => __( 'New Language Name', 'cinemabrief' ),
            'menu_name'         => __( 'Languages', 'cinemabrief' ),
        ),
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'rewrite'           => array( 'slug' => 'language' ),
        'show_in_rest'      => true,
    ) );

    // 2. Genre (for genre schema field)
    register_taxonomy( 'movie_genre', 'movie_reviews', array(
        'labels' => array(
            'name'              => __( 'Genres', 'cinemabrief' ),
            'singular_name'     => __( 'Genre', 'cinemabrief' ),
            'search_items'      => __( 'Search Genres', 'cinemabrief' ),
            'all_items'         => __( 'All Genres', 'cinemabrief' ),
            'edit_item'         => __( 'Edit Genre', 'cinemabrief' ),
            'update_item'       => __( 'Update Genre', 'cinemabrief' ),
            'add_new_item'      => __( 'Add New Genre', 'cinemabrief' ),
            'new_item_name'     => __( 'New Genre Name', 'cinemabrief' ),
            'menu_name'         => __( 'Genres', 'cinemabrief' ),
        ),
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'rewrite'           => array( 'slug' => 'genre' ),
        'show_in_rest'      => true,
    ) );

    // Note: Standard 'post_tag' is shared via the CPT definition.
}
add_action( 'init', 'cb_register_taxonomies' );