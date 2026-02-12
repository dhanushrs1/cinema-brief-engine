<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function cb_register_cpt() {
    $labels = array(
        'name'               => 'Movie Reviews',
        'singular_name'      => 'Movie Review',
        'menu_name'          => 'Movie Reviews',
        'add_new'            => 'Add Review',
        'add_new_item'       => 'Add New Movie Review',
        'edit_item'          => 'Edit Review',
        'new_item'           => 'New Review',
        'view_item'          => 'View Review',
        'search_items'       => 'Search Reviews',
        'not_found'          => 'No reviews found',
        'all_items'          => 'All Reviews',
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_position'       => 5,
        'menu_icon'           => 'dashicons-tickets-alt',
        'query_var'           => true,
        'rewrite'             => array( 'slug' => 'reviews', 'with_front' => false ),
        'capability_type'     => 'post',
        'has_archive'         => 'reviews',
        'hierarchical'        => false,
        'taxonomies'          => array( 'post_tag' ), // <--- THIS ENABLES SHARED TAGS
        'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'revisions' ),
        'show_in_rest'        => true,
    );

    register_post_type( 'movie_reviews', $args );
}
add_action( 'init', 'cb_register_cpt' );

// Shortcode to display Pros list with Checkmarks
add_shortcode('cb_pros_list', function() {
    $pros = get_post_meta(get_the_ID(), '_cb_pros', true);
    if(!$pros) return '';
    $items = explode("\n", $pros);
    $html = '<ul style="list-style:none; padding:0;">';
    foreach($items as $item) {
        if(trim($item)) $html .= '<li style="margin-bottom:8px;">✅ ' . esc_html($item) . '</li>';
    }
    return $html . '</ul>';
});

// Shortcode to display Cons list with Crosses
add_shortcode('cb_cons_list', function() {
    $cons = get_post_meta(get_the_ID(), '_cb_cons', true);
    if(!$cons) return '';
    $items = explode("\n", $cons);
    $html = '<ul style="list-style:none; padding:0;">';
    foreach($items as $item) {
        if(trim($item)) $html .= '<li style="margin-bottom:8px;">❌ ' . esc_html($item) . '</li>';
    }
    return $html . '</ul>';
});