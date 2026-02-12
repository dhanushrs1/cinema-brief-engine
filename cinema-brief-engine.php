<?php
/**
 * Plugin Name: Cinema Brief Engine (Modular)
 * Description: The professional, multi-file architecture for CinemaBrief.in
 * Version: 3.1
 * Author: Cinema Brief
 * Text Domain: cinemabrief
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Define Plugin Constants
define( 'CB_ENGINE_PATH', plugin_dir_path( __FILE__ ) );

// Load The Modules
require_once CB_ENGINE_PATH . 'includes/cpt-reviews.php';
require_once CB_ENGINE_PATH . 'includes/taxonomies.php';
require_once CB_ENGINE_PATH . 'includes/meta-boxes.php';
require_once CB_ENGINE_PATH . 'includes/admin-columns.php';
require_once CB_ENGINE_PATH . 'includes/schema-generator.php';
function cb_load_admin_styles() {
    global $typenow;
    if ( 'movie_reviews' == $typenow ) {
        wp_enqueue_style( 'cb_admin_css', plugin_dir_url( __FILE__ ) . 'includes/admin-style.css' );
    }
}
add_action( 'admin_enqueue_scripts', 'cb_load_admin_styles' );