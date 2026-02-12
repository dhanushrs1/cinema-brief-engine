<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Add Columns
add_filter( 'manage_movie_reviews_posts_columns', 'cb_set_custom_columns' );
function cb_set_custom_columns( $columns ) {
    $new_columns = array();
    foreach($columns as $key => $value) {
        $new_columns[$key] = $value;
        if($key == 'title') {
            $new_columns['cb_rating'] = 'Rating'; 
        }
    }
    return $new_columns;
}

// Display Data
add_action( 'manage_movie_reviews_posts_custom_column', 'cb_custom_column_data', 10, 2 );
function cb_custom_column_data( $column, $post_id ) {
    if ( $column == 'cb_rating' ) {
        $rating = get_post_meta( $post_id, '_cb_rating', true );
        if ( $rating ) {
            echo '<strong>' . esc_html( $rating ) . '/10</strong>';
        } else {
            echo '<span style="color:#ccc;">-</span>';
        }
    }
}