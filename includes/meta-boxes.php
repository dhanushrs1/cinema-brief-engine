<?php
/**
 * Cinema Brief Engine — Meta Boxes
 * Registers and renders the Movie Data and Schema Preview meta boxes.
 *
 * @package CinemaBrief
 * @since 3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =============================================================================
// 1. REGISTER THE BOXES
// =============================================================================
function cb_add_meta_boxes() {
    add_meta_box(
        'cb_movie_data',
        __( '🎬 Movie Data & Verdict', 'cinemabrief' ),
        'cb_render_data_box',
        'movie_reviews',
        'normal',
        'high'
    );
    add_meta_box(
        'cb_schema_box',
        __( '🤖 Google Schema (JSON-LD) — Live Preview', 'cinemabrief' ),
        'cb_render_schema_box',
        'movie_reviews',
        'normal',
        'default'
    );
}
add_action( 'add_meta_boxes', 'cb_add_meta_boxes' );


// =============================================================================
// 2. THE DATA INPUT BOX
// =============================================================================
function cb_render_data_box( $post ) {
    wp_nonce_field( 'cb_save_data', 'cb_nonce' );

    // Get existing values
    $rating   = get_post_meta( $post->ID, '_cb_rating', true );
    $director = get_post_meta( $post->ID, '_cb_director', true );
    $cast     = get_post_meta( $post->ID, '_cb_cast', true );
    $duration = get_post_meta( $post->ID, '_cb_duration', true );
    $date     = get_post_meta( $post->ID, '_cb_release_date', true );
    $verdict  = get_post_meta( $post->ID, '_cb_verdict', true );
    $synopsis = get_post_meta( $post->ID, '_cb_synopsis', true );
    $pros     = get_post_meta( $post->ID, '_cb_pros', true );
    $cons     = get_post_meta( $post->ID, '_cb_cons', true );
    ?>

    <div class="cb-form-grid">
        <div class="cb-form-col">
            <div class="cb-field">
                <label for="cb_rating"><?php esc_html_e( 'Rating (0-10)', 'cinemabrief' ); ?></label>
                <input type="number" id="cb_rating" name="cb_rating" step="0.1" min="0" max="10"
                       value="<?php echo esc_attr( $rating ); ?>">
            </div>

            <div class="cb-field">
                <label for="cb_release_date"><?php esc_html_e( 'Release Date', 'cinemabrief' ); ?></label>
                <input type="date" id="cb_release_date" name="cb_release_date"
                       value="<?php echo esc_attr( $date ); ?>">
            </div>

            <div class="cb-field">
                <label for="cb_verdict"><?php esc_html_e( 'Verdict', 'cinemabrief' ); ?></label>
                <select id="cb_verdict" name="cb_verdict">
                    <option value=""><?php esc_html_e( 'Select Verdict...', 'cinemabrief' ); ?></option>
                    <option value="Blockbuster" <?php selected( $verdict, 'Blockbuster' ); ?>><?php esc_html_e( 'Blockbuster (⭐⭐⭐⭐⭐)', 'cinemabrief' ); ?></option>
                    <option value="Hit" <?php selected( $verdict, 'Hit' ); ?>><?php esc_html_e( 'Hit (⭐⭐⭐⭐)', 'cinemabrief' ); ?></option>
                    <option value="Above Average" <?php selected( $verdict, 'Above Average' ); ?>><?php esc_html_e( 'Above Average (⭐⭐⭐½)', 'cinemabrief' ); ?></option>
                    <option value="Average" <?php selected( $verdict, 'Average' ); ?>><?php esc_html_e( 'Average (⭐⭐⭐)', 'cinemabrief' ); ?></option>
                    <option value="Below Average" <?php selected( $verdict, 'Below Average' ); ?>><?php esc_html_e( 'Below Average (⭐⭐)', 'cinemabrief' ); ?></option>
                    <option value="Flop" <?php selected( $verdict, 'Flop' ); ?>><?php esc_html_e( 'Flop (⭐)', 'cinemabrief' ); ?></option>
                </select>
            </div>
        </div>

        <div class="cb-form-col">
            <div class="cb-field">
                <label for="cb_director"><?php esc_html_e( 'Director', 'cinemabrief' ); ?></label>
                <input type="text" id="cb_director" name="cb_director"
                       value="<?php echo esc_attr( $director ); ?>"
                       placeholder="<?php esc_attr_e( 'Director name', 'cinemabrief' ); ?>">
            </div>

            <div class="cb-field">
                <label for="cb_duration"><?php esc_html_e( 'Duration', 'cinemabrief' ); ?></label>
                <input type="text" id="cb_duration" name="cb_duration"
                       value="<?php echo esc_attr( $duration ); ?>"
                       placeholder="2h 30m">
                <span class="cb-field-hint"><?php esc_html_e( 'Format: 2h 30m, 150m, 2h — auto-converts to ISO 8601', 'cinemabrief' ); ?></span>
            </div>

            <div class="cb-field">
                <label for="cb_cast"><?php esc_html_e( 'Cast', 'cinemabrief' ); ?></label>
                <input type="text" id="cb_cast" name="cb_cast"
                       value="<?php echo esc_attr( $cast ); ?>"
                       placeholder="<?php esc_attr_e( 'Actor 1, Actor 2, Actor 3', 'cinemabrief' ); ?>">
                <span class="cb-field-hint"><?php esc_html_e( 'Comma-separated actor names', 'cinemabrief' ); ?></span>
            </div>
        </div>
    </div>

    <hr>

    <!-- Movie Synopsis -->
    <div class="cb-field cb-field-full">
        <label for="cb_synopsis">🎞️ <?php esc_html_e( 'Movie Synopsis / Description', 'cinemabrief' ); ?></label>
        <textarea id="cb_synopsis" name="cb_synopsis" rows="3"
                  placeholder="<?php esc_attr_e( 'Write a brief movie synopsis for schema description...', 'cinemabrief' ); ?>"><?php echo esc_textarea( $synopsis ); ?></textarea>
        <span class="cb-field-hint"><?php esc_html_e( 'Used as the Movie "description" in Google Schema. Keep it concise (1-3 sentences).', 'cinemabrief' ); ?></span>
    </div>

    <hr>

    <!-- Pros & Cons -->
    <div class="cb-form-grid">
        <div class="cb-field">
            <label for="cb_pros" class="cb-label-pros">✅ <?php esc_html_e( 'Pros (One per line)', 'cinemabrief' ); ?></label>
            <textarea id="cb_pros" name="cb_pros" rows="5" class="cb-textarea-pros"
                      placeholder="<?php esc_attr_e( "Great Acting\nGood Music", 'cinemabrief' ); ?>"><?php echo esc_textarea( $pros ); ?></textarea>
        </div>
        <div class="cb-field">
            <label for="cb_cons" class="cb-label-cons">❌ <?php esc_html_e( 'Cons (One per line)', 'cinemabrief' ); ?></label>
            <textarea id="cb_cons" name="cb_cons" rows="5" class="cb-textarea-cons"
                      placeholder="<?php esc_attr_e( "Slow Second Half\nBad Editing", 'cinemabrief' ); ?>"><?php echo esc_textarea( $cons ); ?></textarea>
        </div>
    </div>
    <?php
}


// =============================================================================
// 3. THE SCHEMA PREVIEW BOX (No inline JS — uses enqueued admin-schema-preview.js)
// =============================================================================
function cb_render_schema_box( $post ) {
    $schema    = get_post_meta( $post->ID, '_cb_schema_json', true );
    $permalink = get_permalink( $post->ID );

    $google_test_url = 'https://search.google.com/test/rich-results?url=' . urlencode( $permalink );
    ?>

    <div class="cb-toolbar">
        <div class="cb-toolbar-status">
            <span class="dashicons dashicons-update"></span>
            <?php esc_html_e( 'Live Sync Active', 'cinemabrief' ); ?>
        </div>

        <div class="cb-toolbar-actions">
            <a href="<?php echo esc_url( $google_test_url ); ?>" target="_blank" class="button button-small">
                ⚡ <?php esc_html_e( 'Test Live URL', 'cinemabrief' ); ?>
            </a>
            <button type="button" class="button button-small button-primary" id="cb_copy_btn">
                📋 <?php esc_html_e( 'Copy & Validate', 'cinemabrief' ); ?>
            </button>
        </div>
    </div>

    <textarea id="cb_schema_json" name="cb_schema_json" class="cb-schema-textarea"><?php echo esc_textarea( $schema ); ?></textarea>
    <?php
}