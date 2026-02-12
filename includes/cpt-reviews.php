<?php
/**
 * Cinema Brief Engine — Custom Post Type: Movie Reviews
 * Registers the movie_reviews CPT and provides shortcodes for pros/cons.
 *
 * @package CinemaBrief
 * @since 3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =============================================================================
// 1. REGISTER CUSTOM POST TYPE
// =============================================================================
function cb_register_cpt() {
    $labels = array(
        'name'               => __( 'Movie Reviews', 'cinemabrief' ),
        'singular_name'      => __( 'Movie Review', 'cinemabrief' ),
        'menu_name'          => __( 'Movie Reviews', 'cinemabrief' ),
        'add_new'            => __( 'Add Review', 'cinemabrief' ),
        'add_new_item'       => __( 'Add New Movie Review', 'cinemabrief' ),
        'edit_item'          => __( 'Edit Review', 'cinemabrief' ),
        'new_item'           => __( 'New Review', 'cinemabrief' ),
        'view_item'          => __( 'View Review', 'cinemabrief' ),
        'view_items'         => __( 'View Reviews', 'cinemabrief' ),
        'search_items'       => __( 'Search Reviews', 'cinemabrief' ),
        'not_found'          => __( 'No reviews found', 'cinemabrief' ),
        'not_found_in_trash' => __( 'No reviews found in Trash', 'cinemabrief' ),
        'all_items'          => __( 'All Reviews', 'cinemabrief' ),
        'archives'           => __( 'Review Archives', 'cinemabrief' ),
        'filter_items_list'  => __( 'Filter reviews list', 'cinemabrief' ),
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
        'taxonomies'          => array( 'post_tag' ),
        'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'revisions' ),
        'show_in_rest'        => true,
    );

    register_post_type( 'movie_reviews', $args );
}
add_action( 'init', 'cb_register_cpt' );


// =============================================================================
// 2. SHORTCODES (Frontend Pros/Cons Lists)
//    - Uses CSS classes instead of inline styles
//    - Styles loaded via frontend-style.css
// =============================================================================

/**
 * [cb_pros_list] — Display pros with checkmarks.
 */
add_shortcode( 'cb_pros_list', function() {
    $pros = get_post_meta( get_the_ID(), '_cb_pros', true );
    if ( ! $pros ) return '';

    $items = explode( "\n", $pros );
    $html  = '<ul class="cb-pros-list">';
    foreach ( $items as $item ) {
        $item = trim( $item );
        if ( $item ) {
            $html .= '<li class="cb-pros-item">✅ ' . esc_html( $item ) . '</li>';
        }
    }
    $html .= '</ul>';

    return $html;
} );

/**
 * [cb_cons_list] — Display cons with crosses.
 */
add_shortcode( 'cb_cons_list', function() {
    $cons = get_post_meta( get_the_ID(), '_cb_cons', true );
    if ( ! $cons ) return '';

    $items = explode( "\n", $cons );
    $html  = '<ul class="cb-cons-list">';
    foreach ( $items as $item ) {
        $item = trim( $item );
        if ( $item ) {
            $html .= '<li class="cb-cons-item">❌ ' . esc_html( $item ) . '</li>';
        }
    }
    $html .= '</ul>';

    return $html;
} );