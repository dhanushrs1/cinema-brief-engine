<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Register Meta Boxes
function cb_add_meta_boxes() {
    add_meta_box('cb_movie_data', '🎬 Movie Data & Verdict', 'cb_render_data_box', 'movie_reviews', 'normal', 'high');
    add_meta_box('cb_schema_box', '🤖 Google Schema (JSON-LD)', 'cb_render_schema_box', 'movie_reviews', 'normal', 'default');
}
add_action( 'add_meta_boxes', 'cb_add_meta_boxes' );

// Render Data Box
function cb_render_data_box( $post ) {
    wp_nonce_field( 'cb_save_data', 'cb_nonce' );
    
    // Retrieve existing values
    $rating = get_post_meta( $post->ID, '_cb_rating', true );
    $director = get_post_meta( $post->ID, '_cb_director', true );
    $cast = get_post_meta( $post->ID, '_cb_cast', true );
    $duration = get_post_meta( $post->ID, '_cb_duration', true );
    $date = get_post_meta( $post->ID, '_cb_release_date', true );
    
    // NEW FIELDS
    $verdict = get_post_meta( $post->ID, '_cb_verdict', true );
    $pros = get_post_meta( $post->ID, '_cb_pros', true );
    $cons = get_post_meta( $post->ID, '_cb_cons', true );
    ?>
    
    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px;">
        <div>
            <p><label><strong>Rating (0-10)</strong></label><br>
            <input type="number" step="0.1" max="10" name="cb_rating" value="<?php echo esc_attr($rating); ?>" style="width:100%"></p>
            
            <p><label><strong>Release Date</strong></label><br>
            <input type="date" name="cb_release_date" value="<?php echo esc_attr($date); ?>" style="width:100%"></p>

            <p><label><strong>Verdict (Status)</strong></label><br>
            <select name="cb_verdict" style="width:100%">
                <option value="">Select Verdict...</option>
                <option value="Blockbuster" <?php selected($verdict, 'Blockbuster'); ?>>Blockbuster (⭐⭐⭐⭐⭐)</option>
                <option value="Hit" <?php selected($verdict, 'Hit'); ?>>Hit (⭐⭐⭐⭐)</option>
                <option value="Average" <?php selected($verdict, 'Average'); ?>>Average (⭐⭐⭐)</option>
                <option value="Flop" <?php selected($verdict, 'Flop'); ?>>Flop (⭐⭐)</option>
                <option value="Disaster" <?php selected($verdict, 'Disaster'); ?>>Disaster (⭐)</option>
            </select></p>
        </div>

        <div>
            <p><label><strong>Director</strong></label><br>
            <input type="text" name="cb_director" value="<?php echo esc_attr($director); ?>" style="width:100%"></p>
            
            <p><label><strong>Duration</strong></label><br>
            <input type="text" name="cb_duration" value="<?php echo esc_attr($duration); ?>" placeholder="2h 30m" style="width:100%"></p>
            
            <p><label><strong>Cast</strong></label><br>
            <input type="text" name="cb_cast" value="<?php echo esc_attr($cast); ?>" style="width:100%"></p>
        </div>
    </div>

    <hr>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
        <div>
            <label><strong style="color:green;">✅ Pros (One per line)</strong></label><br>
            <textarea name="cb_pros" rows="5" style="width:100%" placeholder="Great Acting&#10;Good Music"><?php echo esc_textarea($pros); ?></textarea>
        </div>
        <div>
            <label><strong style="color:red;">❌ Cons (One per line)</strong></label><br>
            <textarea name="cb_cons" rows="5" style="width:100%" placeholder="Slow Second Half&#10;Bad Editing"><?php echo esc_textarea($cons); ?></textarea>
        </div>
    </div>

    <?php
}

function cb_render_schema_box( $post ) {
    $schema = get_post_meta( $post->ID, '_cb_schema_json', true );
    echo '<p><em>Auto-generated. Clear box and save to reset.</em></p>';
    echo '<textarea name="cb_schema_json" style="width:100%; height:200px; font-family:monospace; background:#f4f4f4;">' . esc_textarea($schema) . '</textarea>';
}