<?php
/**
 * Cinema Brief Engine — Shared Helper Functions
 * Reusable utilities used across multiple plugin modules.
 *
 * @package CinemaBrief
 * @since 3.3
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get the publisher logo URL dynamically from WordPress settings.
 * Priority: Custom Logo → Site Icon → empty string.
 *
 * @since 3.3
 * @return string Logo URL or empty string if none found.
 */
function cb_get_publisher_logo_url() {
    $logo_url = '';

    // 1. Try the theme's Custom Logo (Appearance → Customize → Site Identity)
    $custom_logo_id = get_theme_mod( 'custom_logo' );
    if ( $custom_logo_id ) {
        $logo_url = wp_get_attachment_image_url( $custom_logo_id, 'full' );
    }

    // 2. Fallback to Site Icon (Settings → General → Site Icon)
    if ( ! $logo_url ) {
        $site_icon_id = get_option( 'site_icon' );
        if ( $site_icon_id ) {
            $logo_url = wp_get_attachment_image_url( $site_icon_id, 'full' );
        }
    }

    return $logo_url ? $logo_url : '';
}


/**
 * Get the language code for a movie review post based on its taxonomy term slug.
 * Maps taxonomy slugs to ISO 639-1 language codes.
 *
 * @since 3.3
 * @param int $post_id The post ID to check.
 * @return string ISO 639-1 language code (defaults to 'en').
 */
function cb_get_language_code( $post_id ) {
    $terms = get_the_terms( $post_id, 'movie_language' );

    if ( ! $terms || is_wp_error( $terms ) ) {
        return 'en';
    }

    // Map of taxonomy slugs → ISO 639-1 codes
    $lang_map = array(
        'kannada'  => 'kn',
        'english'  => 'en',
        'hindi'    => 'hi',
        'tamil'    => 'ta',
        'telugu'   => 'te',
        'malayalam' => 'ml',
        'marathi'  => 'mr',
        'bengali'  => 'bn',
    );

    $slug = $terms[0]->slug;
    return isset( $lang_map[ $slug ] ) ? $lang_map[ $slug ] : 'en';
}


/**
 * Convert human-readable duration to ISO 8601 format.
 * Supports: "2h 30m", "2h30m", "150m", "2h", "150 min", "2 hours 30 min", "150"
 *
 * @since 3.2
 * @param string $raw The raw duration string.
 * @return string ISO 8601 duration (e.g., "PT2H30M") or empty string on failure.
 */
function cb_convert_duration_to_iso( $raw ) {
    $raw     = strtolower( trim( $raw ) );
    $hours   = 0;
    $minutes = 0;

    // Match "Xh", "X hours" patterns
    if ( preg_match( '/(\d+)\s*h(?:ours?)?/', $raw, $h_match ) ) {
        $hours = intval( $h_match[1] );
    }
    // Match "Xm", "X min", "X minutes" patterns
    if ( preg_match( '/(\d+)\s*m(?:in(?:utes?)?)?/', $raw, $m_match ) ) {
        $minutes = intval( $m_match[1] );
    }

    // Pure number fallback → assume minutes
    if ( $hours === 0 && $minutes === 0 && preg_match( '/^(\d+)$/', $raw, $pure ) ) {
        $minutes = intval( $pure[1] );
    }

    if ( $hours === 0 && $minutes === 0 ) {
        return '';
    }

    $iso = 'PT';
    if ( $hours > 0 )   $iso .= $hours . 'H';
    if ( $minutes > 0 ) $iso .= $minutes . 'M';

    return $iso;
}
