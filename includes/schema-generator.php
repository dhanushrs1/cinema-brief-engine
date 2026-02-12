<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// =============================================================================
// 1. SAVE LOGIC CONTROLLER
//    - Saves Meta Fields & Triggers Schema Generation
// =============================================================================
function cb_save_post_data( $post_id ) {
    // Security & Permissions Check
    if ( ! isset( $_POST['cb_nonce'] ) || ! wp_verify_nonce( $_POST['cb_nonce'], 'cb_save_data' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    // 1. Save All Meta Fields
    $fields = ['cb_rating', 'cb_director', 'cb_cast', 'cb_duration', 'cb_release_date', 'cb_verdict', 'cb_pros', 'cb_cons'];
    
    foreach ( $fields as $field ) {
        if ( isset( $_POST[$field] ) ) {
            // Use sanitize_textarea_field for Pros/Cons to preserve new lines
            if ( $field == 'cb_pros' || $field == 'cb_cons' ) {
                update_post_meta( $post_id, '_' . $field, sanitize_textarea_field( $_POST[$field] ) );
            } else {
                update_post_meta( $post_id, '_' . $field, sanitize_text_field( $_POST[$field] ) );
            }
        }
    }

    // 2. Handle Schema Generation
    // If the user manually edited the Schema box, save that specific version.
    if ( ! empty( $_POST['cb_schema_json'] ) ) {
        // wp_unslash prevents double escaping of quotes
        update_post_meta( $post_id, '_cb_schema_json', wp_unslash( $_POST['cb_schema_json'] ) );
    } else {
        // Otherwise, Auto-Generate it based on the new data
        $json = cb_build_schema( $post_id );
        update_post_meta( $post_id, '_cb_schema_json', $json );
    }
}
add_action( 'save_post', 'cb_save_post_data' );


// =============================================================================
// 2. THE SCHEMA BUILDER (The Brain)
//    - Converts Meta Data into JSON-LD
// =============================================================================
function cb_build_schema( $post_id ) {
    $post = get_post($post_id);
    
    // Retrieve Data
    $rating = get_post_meta( $post_id, '_cb_rating', true );
    $director = get_post_meta( $post_id, '_cb_director', true );
    $cast_str = get_post_meta( $post_id, '_cb_cast', true );
    $date = get_post_meta( $post_id, '_cb_release_date', true );
    $pros_str = get_post_meta( $post_id, '_cb_pros', true );
    $cons_str = get_post_meta( $post_id, '_cb_cons', true );
    $img_url = get_the_post_thumbnail_url( $post_id, 'full' );

    // Language Detection (Defaults to Kannada if tagged, else English)
    $terms = get_the_terms( $post_id, 'movie_language' );
    $lang_code = ($terms && !is_wp_error($terms)) ? 'kn' : 'en'; 

    // Process Actors (Split comma-separated string)
    $actors = [];
    if($cast_str) {
        $names = explode(',', $cast_str);
        foreach($names as $n) {
            if(trim($n)) $actors[] = ["@type" => "Person", "name" => trim($n)];
        }
    }
    
    // Process Pros (Positive Notes)
    $positiveNotes = [];
    if($pros_str) {
        $items = explode("\n", $pros_str); // Split by new line
        $i = 1;
        foreach($items as $item) {
            if(trim($item)) $positiveNotes[] = ["@type" => "ListItem", "position" => $i++, "name" => trim($item)];
        }
    }

    // Process Cons (Negative Notes)
    $negativeNotes = [];
    if($cons_str) {
        $items = explode("\n", $cons_str);
        $i = 1;
        foreach($items as $item) {
            if(trim($item)) $negativeNotes[] = ["@type" => "ListItem", "position" => $i++, "name" => trim($item)];
        }
    }

    // Build the Schema Array
    $data = [
        "@context" => "https://schema.org",
        "@type" => "Review",
        "itemReviewed" => [
            "@type" => "Movie",
            "name" => $post->post_title,
            "image" => $img_url ? $img_url : "",
            "datePublished" => $date,
            "director" => ["@type" => "Person", "name" => $director],
            "actor" => $actors,
            "inLanguage" => $lang_code,
            "countryOfOrigin" => ["@type" => "Country", "name" => "India"]
        ],
        "reviewRating" => [
            "@type" => "Rating",
            "ratingValue" => $rating ? $rating : "0",
            "bestRating" => "10", // UPDATED: 10-Point Scale
            "worstRating" => "1"
        ],
        "author" => [
            "@type" => "Organization",
            "name" => "CinemaBrief",
            "url" => home_url()
        ],
        "reviewBody" => wp_trim_words( $post->post_content, 25 )
    ];

    // Inject Positive/Negative Notes only if they exist
    if(!empty($positiveNotes)) {
        $data["positiveNotes"] = ["@type" => "ItemList", "itemListElement" => $positiveNotes];
    }
    if(!empty($negativeNotes)) {
        $data["negativeNotes"] = ["@type" => "ItemList", "itemListElement" => $negativeNotes];
    }

    // Return JSON (Pretty Printed + Unicode support for Kannada)
    return json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
}


// =============================================================================
// 3. FRONTEND INJECTOR
//    - Prints the JSON-LD into the <head> of the website
// =============================================================================
function cb_inject_schema() {
    if ( is_singular( 'movie_reviews' ) ) {
        $json = get_post_meta( get_the_ID(), '_cb_schema_json', true );
        if ( $json ) {
            echo "\n\n";
            echo '<script type="application/ld+json">' . $json . '</script>';
            echo "\n\n";
        }
    }
}
add_action( 'wp_head', 'cb_inject_schema' );