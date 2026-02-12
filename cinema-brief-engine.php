<?php
/**
 * Plugin Name: Cinema Brief Engine (Modular)
 * Description: The professional, multi-file architecture for CinemaBrief.in
 * Version: 3.4
 * Author: Cinema Brief
 * Text Domain: cinemabrief
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =========================================================================
// Plugin Constants
// =========================================================================
define( 'CB_ENGINE_PATH', plugin_dir_path( __FILE__ ) );
define( 'CB_ENGINE_URL',  plugin_dir_url( __FILE__ ) );

// =========================================================================
// Load Modules
// =========================================================================
require_once CB_ENGINE_PATH . 'includes/helpers.php';
require_once CB_ENGINE_PATH . 'includes/cpt-reviews.php';
require_once CB_ENGINE_PATH . 'includes/taxonomies.php';
require_once CB_ENGINE_PATH . 'includes/meta-boxes.php';
require_once CB_ENGINE_PATH . 'includes/admin-columns.php';
require_once CB_ENGINE_PATH . 'includes/schema-generator.php';

// =========================================================================
// Enqueue Admin Assets (Only on movie_reviews screens)
// =========================================================================
function cb_load_admin_assets() {
    global $typenow;
    if ( 'movie_reviews' !== $typenow ) {
        return;
    }

    // Admin CSS
    wp_enqueue_style(
        'cb-admin-css',
        CB_ENGINE_URL . 'includes/admin-style.css',
        array(),
        '3.4'
    );

    // Schema Preview JS (only on post edit screens)
    $screen = get_current_screen();
    if ( $screen && $screen->base === 'post' ) {
        wp_enqueue_script(
            'cb-schema-preview',
            CB_ENGINE_URL . 'assets/js/admin-schema-preview.js',
            array(),
            '3.4',
            true
        );

        // Pass PHP data to JS
        global $post;
        if ( $post ) {
            $permalink      = get_permalink( $post->ID );
            $img_url        = get_the_post_thumbnail_url( $post->ID, 'full' );
            $genre_terms    = get_the_terms( $post->ID, 'movie_genre' );
            $genres         = array();
            if ( $genre_terms && ! is_wp_error( $genre_terms ) ) {
                $genres = array_values( wp_list_pluck( $genre_terms, 'name' ) );
            }

            wp_localize_script( 'cb-schema-preview', 'cbSchemaData', array(
                'movieTitle'    => get_the_title( $post->ID ),
                'movieName'     => get_post_meta( $post->ID, '_cb_movie_title', true ),
                'imgUrl'        => $img_url ? $img_url : '',
                'siteUrl'       => home_url(),
                'permalink'     => $permalink,
                'logoUrl'       => cb_get_publisher_logo_url(),
                'genres'        => $genres,
                'langCode'      => cb_get_language_code( $post->ID ),
                'datePublished' => get_the_date( 'c', $post->ID ),
                'dateModified'  => get_the_modified_date( 'c', $post->ID ),
                'validatorUrl'  => 'https://validator.schema.org/',
                // Duration regex (single source of truth — also used in helpers.php)
                'durationRegexH' => '(\\d+)\\s*h(?:ours?)?',
                'durationRegexM' => '(\\d+)\\s*m(?:in(?:utes?)?)?',
            ) );
        }
    }
}
add_action( 'admin_enqueue_scripts', 'cb_load_admin_assets' );

// =========================================================================
// Enqueue Frontend Styles (shortcode styles)
// =========================================================================
function cb_load_frontend_styles() {
    if ( is_singular( 'movie_reviews' ) ) {
        wp_enqueue_style(
            'cb-frontend-css',
            CB_ENGINE_URL . 'assets/css/frontend-style.css',
            array(),
            '3.4'
        );
    }
}
add_action( 'wp_enqueue_scripts', 'cb_load_frontend_styles' );

// =========================================================================
// Activation Hook — Flush Rewrite Rules
// =========================================================================
function cb_plugin_activate() {
    cb_register_cpt();
    cb_register_taxonomies();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'cb_plugin_activate' );

// =========================================================================
// Deactivation Hook — Cleanup Rewrite Rules
// =========================================================================
function cb_plugin_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'cb_plugin_deactivate' );