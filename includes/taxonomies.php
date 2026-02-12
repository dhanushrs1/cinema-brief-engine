<?php
/**
 * Cinema Brief Engine — Taxonomies
 * Registers custom taxonomies for Movie Language and Movie Genre.
 *
 * @package CinemaBrief
 * @since 3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =============================================================================
// REGISTER CUSTOM TAXONOMIES
// =============================================================================
function cb_register_taxonomies() {

    // 1. Language (for inLanguage schema detection)
    register_taxonomy( 'movie_language', 'movie_reviews', array(
        'labels' => array(
            'name'              => __( 'Languages', 'cinemabrief' ),
            'singular_name'     => __( 'Language', 'cinemabrief' ),
            'search_items'      => __( 'Search Languages', 'cinemabrief' ),
            'all_items'         => __( 'All Languages', 'cinemabrief' ),
            'edit_item'         => __( 'Edit Language', 'cinemabrief' ),
            'update_item'       => __( 'Update Language', 'cinemabrief' ),
            'add_new_item'      => __( 'Add New Language', 'cinemabrief' ),
            'new_item_name'     => __( 'New Language Name', 'cinemabrief' ),
            'menu_name'         => __( 'Languages', 'cinemabrief' ),
        ),
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'rewrite'           => array( 'slug' => 'language' ),
        'show_in_rest'      => true,
    ) );

    // 2. Genre (for genre schema field)
    register_taxonomy( 'movie_genre', 'movie_reviews', array(
        'labels' => array(
            'name'              => __( 'Genres', 'cinemabrief' ),
            'singular_name'     => __( 'Genre', 'cinemabrief' ),
            'search_items'      => __( 'Search Genres', 'cinemabrief' ),
            'all_items'         => __( 'All Genres', 'cinemabrief' ),
            'edit_item'         => __( 'Edit Genre', 'cinemabrief' ),
            'update_item'       => __( 'Update Genre', 'cinemabrief' ),
            'add_new_item'      => __( 'Add New Genre', 'cinemabrief' ),
            'new_item_name'     => __( 'New Genre Name', 'cinemabrief' ),
            'menu_name'         => __( 'Genres', 'cinemabrief' ),
        ),
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'rewrite'           => array( 'slug' => 'genre' ),
        'show_in_rest'      => true,
    ) );

    // Note: Standard 'post_tag' is shared via the CPT definition.
}
add_action( 'init', 'cb_register_taxonomies' );


// =============================================================================
// LANGUAGE TAXONOMY CUSTOM FIELDS (ISO Code)
// =============================================================================

// 1. Add Field to "Add New Language" Screen
function cb_add_language_fields() {
    ?>
    <div class="form-field term-iso-code-wrap">
        <label for="term-iso-code"><?php esc_html_e( 'ISO Language Code (639-1 or 639-3)', 'cinemabrief' ); ?></label>
        <input type="text" name="term_iso_code" id="term-iso-code" value="" size="40" aria-required="false" />
        <p class="description"><?php esc_html_e( 'Enter the language code (e.g., "en", "kn", "tcy", "kok"). Used for Google Schema "inLanguage". If empty, the language name itself will be used.', 'cinemabrief' ); ?></p>
    </div>
    <?php
}
add_action( 'movie_language_add_form_fields', 'cb_add_language_fields' );

// 2. Add Field to "Edit Language" Screen
function cb_edit_language_fields( $term ) {
    $iso_code = get_term_meta( $term->term_id, '_cb_language_code', true );
    ?>
    <tr class="form-field term-iso-code-wrap">
        <th scope="row"><label for="term-iso-code"><?php esc_html_e( 'ISO Language Code (639-1 or 639-3)', 'cinemabrief' ); ?></label></th>
        <td>
            <input type="text" name="term_iso_code" id="term-iso-code" value="<?php echo esc_attr( $iso_code ); ?>" size="40" />
            <p class="description"><?php esc_html_e( 'Enter the language code (e.g., "en", "kn", "tcy", "kok"). Used for Google Schema "inLanguage". If empty, the language name itself will be used.', 'cinemabrief' ); ?></p>
        </td>
    </tr>
    <?php
}
add_action( 'movie_language_edit_form_fields', 'cb_edit_language_fields' );

// 3. Save Field Logic
function cb_save_language_fields( $term_id ) {
    if ( isset( $_POST['term_iso_code'] ) ) {
        // Sanitize: Max 10 chars (allow for variants like 'zh-CN'), lowercase, trim
        $code = sanitize_text_field( $_POST['term_iso_code'] );
        $code = strtolower( trim( substr( $code, 0, 10 ) ) );
        update_term_meta( $term_id, '_cb_language_code', $code );
    }
}
add_action( 'created_movie_language', 'cb_save_language_fields' );
add_action( 'edited_movie_language', 'cb_save_language_fields' );

// 4. Add Column to Admin List
function cb_add_language_columns( $columns ) {
    $new_columns = array();
    foreach ( $columns as $key => $value ) {
        $new_columns[ $key ] = $value;
        if ( 'slug' === $key ) { // Insert after Slug
            $new_columns['iso_code'] = __( 'ISO Code', 'cinemabrief' );
        }
    }
    return $new_columns;
}
add_filter( 'manage_edit-movie_language_columns', 'cb_add_language_columns' );

// 5. Render Column Content
function cb_render_language_column( $content, $column_name, $term_id ) {
    if ( 'iso_code' === $column_name ) {
        $code = get_term_meta( $term_id, '_cb_language_code', true );
        return $code ? '<code>' . esc_html( $code ) . '</code>' : '—';
    }
    return $content;
}
add_filter( 'manage_movie_language_custom_column', 'cb_render_language_column', 10, 3 );