<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function cb_register_taxonomies() {
    
    // 1. Language (Custom Taxonomy for Movie Language)
    register_taxonomy( 'movie_language', 'movie_reviews', array(
        'labels'            => array( 'name' => 'Languages', 'singular_name' => 'Language' ),
        'hierarchical'      => true, // Checkbox style
        'show_ui'           => true,
        'show_admin_column' => true,
        'rewrite'           => array( 'slug' => 'language' ),
        'show_in_rest'      => true,
    ));

    // 2. Genre (Custom Taxonomy for Movie Genre)
    register_taxonomy( 'movie_genre', 'movie_reviews', array(
        'labels'            => array( 'name' => 'Genres', 'singular_name' => 'Genre' ),
        'hierarchical'      => true, // Checkbox style
        'show_ui'           => true,
        'show_admin_column' => true,
        'rewrite'           => array( 'slug' => 'genre' ),
        'show_in_rest'      => true,
    ));

    // Note: Standard 'post_tag' is already registered via the CPT definition.
}
add_action( 'init', 'cb_register_taxonomies' );