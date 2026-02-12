<?php
/**
 * Cinema Brief Engine — Admin Columns
 * Adds custom sortable columns to the Movie Reviews list table.
 *
 * @package CinemaBrief
 * @since 3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =============================================================================
// 1. REGISTER CUSTOM COLUMNS
// =============================================================================
add_filter( 'manage_movie_reviews_posts_columns', 'cb_set_custom_columns' );
function cb_set_custom_columns( $columns ) {
    $new_columns = array();

    foreach ( $columns as $key => $value ) {
        $new_columns[ $key ] = $value;

        // Insert custom columns after the title
        if ( $key === 'title' ) {
            $new_columns['cb_rating']       = __( 'Rating', 'cinemabrief' );
            $new_columns['cb_verdict']      = __( 'Verdict', 'cinemabrief' );
            $new_columns['cb_director']     = __( 'Director', 'cinemabrief' );
            $new_columns['cb_release_date'] = __( 'Release Date', 'cinemabrief' );
        }
    }

    return $new_columns;
}


// =============================================================================
// 2. DISPLAY COLUMN DATA
// =============================================================================
add_action( 'manage_movie_reviews_posts_custom_column', 'cb_custom_column_data', 10, 2 );
function cb_custom_column_data( $column, $post_id ) {

    switch ( $column ) {

        case 'cb_rating':
            $rating = get_post_meta( $post_id, '_cb_rating', true );
            if ( $rating !== '' && $rating !== false ) {
                $rating_num = floatval( $rating );
                // Color code: Green for 7+, Orange for 5-6.9, Red for below 5
                if ( $rating_num >= 7 ) {
                    $color = '#46b450';
                } elseif ( $rating_num >= 5 ) {
                    $color = '#dba617';
                } else {
                    $color = '#d63638';
                }
                echo '<strong style="color:' . esc_attr( $color ) . ';">' . esc_html( $rating ) . '/10</strong>';
            } else {
                echo '<span style="color:#ccc;">—</span>';
            }
            break;

        case 'cb_verdict':
            $verdict = get_post_meta( $post_id, '_cb_verdict', true );
            if ( $verdict ) {
                // Emoji mapping for visual scanning
                $emoji_map = array(
                    'Blockbuster'   => '🔥',
                    'Hit'           => '⭐',
                    'Above Average' => '👍',
                    'Average'       => '😐',
                    'Below Average' => '👎',
                    'Flop'          => '💔',
                );
                $emoji = isset( $emoji_map[ $verdict ] ) ? $emoji_map[ $verdict ] : '';
                echo esc_html( $emoji . ' ' . $verdict );
            } else {
                echo '<span style="color:#ccc;">—</span>';
            }
            break;

        case 'cb_director':
            $director = get_post_meta( $post_id, '_cb_director', true );
            echo $director ? esc_html( $director ) : '<span style="color:#ccc;">—</span>';
            break;

        case 'cb_release_date':
            $date = get_post_meta( $post_id, '_cb_release_date', true );
            if ( $date ) {
                // Format to readable date
                $timestamp = strtotime( $date );
                echo $timestamp ? esc_html( date_i18n( get_option( 'date_format' ), $timestamp ) ) : esc_html( $date );
            } else {
                echo '<span style="color:#ccc;">—</span>';
            }
            break;
    }
}


// =============================================================================
// 3. MAKE COLUMNS SORTABLE
// =============================================================================
add_filter( 'manage_edit-movie_reviews_sortable_columns', 'cb_sortable_columns' );
function cb_sortable_columns( $columns ) {
    $columns['cb_rating']       = 'cb_rating';
    $columns['cb_verdict']      = 'cb_verdict';
    $columns['cb_release_date'] = 'cb_release_date';
    return $columns;
}


// =============================================================================
// 4. HANDLE SORTING QUERIES
// =============================================================================
add_action( 'pre_get_posts', 'cb_custom_column_orderby' );
function cb_custom_column_orderby( $query ) {
    if ( ! is_admin() || ! $query->is_main_query() ) {
        return;
    }

    $orderby = $query->get( 'orderby' );

    switch ( $orderby ) {
        case 'cb_rating':
            $query->set( 'meta_key', '_cb_rating' );
            $query->set( 'orderby', 'meta_value_num' );
            break;

        case 'cb_verdict':
            $query->set( 'meta_key', '_cb_verdict' );
            $query->set( 'orderby', 'meta_value' );
            break;

        case 'cb_release_date':
            $query->set( 'meta_key', '_cb_release_date' );
            $query->set( 'orderby', 'meta_value' );
            break;
    }
}