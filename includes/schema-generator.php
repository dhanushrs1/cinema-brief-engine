<?php
/**
 * Cinema Brief Engine — Schema Generator
 * Handles save logic, schema building, and frontend JSON-LD injection.
 *
 * @package CinemaBrief
 * @since 3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =============================================================================
// 1. SAVE LOGIC CONTROLLER
//    - Saves Meta Fields & Triggers Schema Generation
//    - Hooked to save_post_movie_reviews (CPT-specific for performance)
// =============================================================================
function cb_save_post_data( $post_id ) {
    // Security & Permissions Check
    if ( ! isset( $_POST['cb_nonce'] ) || ! wp_verify_nonce( $_POST['cb_nonce'], 'cb_save_data' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    // Save All Meta Fields
    $text_fields    = array( 'cb_movie_title', 'cb_director', 'cb_cast', 'cb_duration', 'cb_release_date', 'cb_verdict' );
    $textarea_fields = array( 'cb_pros', 'cb_cons', 'cb_synopsis' );

    // --- Rating: Server-side validation (clamp 0-10) ---
    if ( isset( $_POST['cb_rating'] ) ) {
        $rating_val = floatval( $_POST['cb_rating'] );
        $rating_val = max( 0, min( 10, $rating_val ) );
        // Round to 1 decimal place for consistency
        $rating_val = round( $rating_val, 1 );
        update_post_meta( $post_id, '_cb_rating', $rating_val );
    }

    // --- Text fields ---
    foreach ( $text_fields as $field ) {
        if ( isset( $_POST[ $field ] ) ) {
            update_post_meta( $post_id, '_' . $field, sanitize_text_field( $_POST[ $field ] ) );
        }
    }

    // --- Textarea fields (preserve newlines) ---
    foreach ( $textarea_fields as $field ) {
        if ( isset( $_POST[ $field ] ) ) {
            update_post_meta( $post_id, '_' . $field, sanitize_textarea_field( $_POST[ $field ] ) );
        }
    }

    // Save Schema Lock checkbox
    $lock_value = isset( $_POST['cb_schema_lock'] ) ? '1' : '0';
    update_post_meta( $post_id, '_cb_schema_lock', $lock_value );

    // 2. FORCE RE-GENERATE SCHEMA (Taxonomy Sync Fix)
    // Skip regeneration if Schema Lock is enabled (user wants manual control)
    if ( $lock_value !== '1' ) {
        $json = cb_build_schema( $post_id );
        update_post_meta( $post_id, '_cb_schema_json', $json );
    }
}
add_action( 'save_post_movie_reviews', 'cb_save_post_data', 20 );


// =============================================================================
// 2. THE SCHEMA BUILDER (The Brain)
//    - Converts Meta Data into JSON-LD
//    - Includes all SEO-critical fields for rich snippets
// =============================================================================
function cb_build_schema( $post_id ) {
    $post = get_post( $post_id );

    // Retrieve Data
    $rating       = get_post_meta( $post_id, '_cb_rating', true );
    $movie_title  = get_post_meta( $post_id, '_cb_movie_title', true );
    $director     = get_post_meta( $post_id, '_cb_director', true );
    $cast_str     = get_post_meta( $post_id, '_cb_cast', true );
    $date         = get_post_meta( $post_id, '_cb_release_date', true );
    $duration_raw = get_post_meta( $post_id, '_cb_duration', true );
    $synopsis     = get_post_meta( $post_id, '_cb_synopsis', true );
    $pros_str     = get_post_meta( $post_id, '_cb_pros', true );
    $cons_str     = get_post_meta( $post_id, '_cb_cons', true );
    $img_url      = get_the_post_thumbnail_url( $post_id, 'full' );

    // Language & Genre (via shared helpers)
    $lang_code    = cb_get_language_code( $post_id );
    $duration_iso = $duration_raw ? cb_convert_duration_to_iso( $duration_raw ) : '';

    // Genre Detection
    $genre_terms = get_the_terms( $post_id, 'movie_genre' );
    $genres = array();
    if ( $genre_terms && ! is_wp_error( $genre_terms ) ) {
        $genres = wp_list_pluck( $genre_terms, 'name' );
    }

    // -------------------------------------------------------------------------
    // Process Actors (sanitize before splitting)
    // -------------------------------------------------------------------------
    $actors = array();
    if ( $cast_str ) {
        $names = explode( ',', sanitize_text_field( $cast_str ) );
        foreach ( $names as $n ) {
            $n = trim( $n );
            if ( $n ) {
                $actors[] = array( "@type" => "Person", "name" => $n );
            }
        }
    }

    // -------------------------------------------------------------------------
    // Process Pros (Positive Notes)
    // -------------------------------------------------------------------------
    $positiveNotes = array();
    if ( $pros_str ) {
        $items = explode( "\n", $pros_str );
        $i = 1;
        foreach ( $items as $item ) {
            $item = trim( $item );
            if ( $item ) {
                $positiveNotes[] = array( "@type" => "ListItem", "position" => $i++, "name" => $item );
            }
        }
    }

    // -------------------------------------------------------------------------
    // Process Cons (Negative Notes)
    // -------------------------------------------------------------------------
    $negativeNotes = array();
    if ( $cons_str ) {
        $items = explode( "\n", $cons_str );
        $i = 1;
        foreach ( $items as $item ) {
            $item = trim( $item );
            if ( $item ) {
                $negativeNotes[] = array( "@type" => "ListItem", "position" => $i++, "name" => $item );
            }
        }
    }

    // =========================================================================
    // BUILD THE MOVIE OBJECT
    // =========================================================================
    // Movie name: use dedicated field, fallback to post title
    $movie_name = $movie_title ? $movie_title : $post->post_title;

    $movie = array(
        "@type"          => "Movie",
        "name"           => $movie_name,
        "datePublished"  => $date,
        "director"       => array( "@type" => "Person", "name" => $director ),
        "actor"          => $actors,
        "inLanguage"     => $lang_code,
        "countryOfOrigin" => array( "@type" => "Country", "name" => "India" ),
    );

    // Conditionally add fields — omit if empty (Google prefers omission)
    if ( $img_url )               $movie["image"]       = $img_url;
    if ( ! empty( $genres ) )     $movie["genre"]       = $genres;
    if ( $duration_iso )          $movie["duration"]    = $duration_iso;
    if ( $synopsis )              $movie["description"] = $synopsis;

    // =========================================================================
    // BUILD THE PUBLISHER OBJECT
    // =========================================================================
    $logo_url  = cb_get_publisher_logo_url();
    $org_name  = get_bloginfo( 'name' );
    $publisher = array(
        "@type" => "Organization",
        "name"  => $org_name,
        "url"   => home_url(),
    );
    if ( $logo_url ) {
        $publisher["logo"] = array(
            "@type" => "ImageObject",
            "url"   => $logo_url,
        );
    }

    // =========================================================================
    // BUILD THE FULL REVIEW SCHEMA
    // =========================================================================
    $data = array(
        "@context"         => "https://schema.org",
        "@type"            => "Review",
        "url"              => get_permalink( $post_id ),
        "headline"         => $post->post_title,
        "mainEntityOfPage" => array(
            "@type" => "WebPage",
            "@id"   => get_permalink( $post_id ),
        ),
        "datePublished"    => get_the_date( 'c', $post_id ),
        "dateModified"     => get_the_modified_date( 'c', $post_id ),
        "itemReviewed"     => $movie,
        "reviewRating"     => array(
            "@type"       => "Rating",
            "ratingValue" => $rating ? strval( $rating ) : "0",
            "bestRating"  => "10",
            "worstRating" => "1",
        ),
        "author"           => array(
            "@type" => "Organization",
            "name"  => $org_name,
            "url"   => home_url(),
        ),
        "publisher"        => $publisher,
    );

    // reviewBody: fallback chain — post content → synopsis → generated string
    $review_body = '';
    if ( $post->post_content ) {
        $review_body = html_entity_decode( wp_trim_words( wp_strip_all_tags( $post->post_content ), 50 ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
    } elseif ( $synopsis ) {
        $review_body = $synopsis;
    } else {
        $review_body = sprintf( __( 'Review of %s', 'cinemabrief' ), $post->post_title );
    }
    $data["reviewBody"] = $review_body;

    // Inject Positive/Negative Notes only if they exist
    if ( ! empty( $positiveNotes ) ) {
        $data["positiveNotes"] = array( "@type" => "ItemList", "itemListElement" => $positiveNotes );
    }
    if ( ! empty( $negativeNotes ) ) {
        $data["negativeNotes"] = array( "@type" => "ItemList", "itemListElement" => $negativeNotes );
    }

    return json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
}


// =============================================================================
// 3. FRONTEND INJECTOR
//    - Prints the JSON-LD into the <head> of the website
//    - Priority 20 to prevent theme/plugin conflicts
// =============================================================================
function cb_inject_schema() {
    if ( is_singular( 'movie_reviews' ) ) {
        $json = get_post_meta( get_the_ID(), '_cb_schema_json', true );
        if ( $json ) {
            // Decode & re-encode for safe output (prevents XSS without breaking JSON)
            $decoded = json_decode( $json, true );
            if ( $decoded ) {
                $safe_json = wp_json_encode( $decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
            } else {
                $safe_json = $json; // Fallback if decode fails (should not happen)
            }
            echo "\n\n";
            echo '<script type="application/ld+json">' . $safe_json . '</script>';
            echo "\n\n";
        }
    }
}
add_action( 'wp_head', 'cb_inject_schema', 20 );