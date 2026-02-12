/**
 * Cinema Brief Engine — Admin Schema Live Preview
 * Handles real-time JSON-LD schema preview in the Movie Data meta box.
 *
 * @package CinemaBrief
 * @since 3.3
 */

(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {

        // 1. Elements
        const inputs = document.querySelectorAll(
            '#cb_movie_title, #cb_rating, #cb_director, #cb_cast, #cb_duration, #cb_release_date, #cb_pros, #cb_cons, #cb_synopsis'
        );
        const outputBox = document.getElementById('cb_schema_json');

        // Bail if not on the right screen
        if ( ! outputBox ) return;

        // 2. Static Data from PHP (via wp_localize_script → cbSchemaData)
        const d = window.cbSchemaData || {};

        // =====================================================================
        // Helper: Convert duration to ISO 8601
        // Regex patterns are passed from PHP (single source of truth)
        // See also: helpers.php → cb_convert_duration_to_iso()
        // =====================================================================
        const reH = new RegExp(d.durationRegexH || '(\\d+)\\s*h(?:ours?)?');
        const reM = new RegExp(d.durationRegexM || '(\\d+)\\s*m(?:in(?:utes?)?)?');

        function durationToISO(raw) {
            if (!raw) return '';
            raw = raw.toLowerCase().trim();
            let hours = 0, minutes = 0;
            let hMatch = raw.match(reH);
            let mMatch = raw.match(reM);
            if (hMatch) hours = parseInt(hMatch[1]);
            if (mMatch) minutes = parseInt(mMatch[1]);
            if (hours === 0 && minutes === 0 && /^\d+$/.test(raw)) minutes = parseInt(raw);
            if (hours === 0 && minutes === 0) return '';
            let iso = 'PT';
            if (hours > 0) iso += hours + 'H';
            if (minutes > 0) iso += minutes + 'M';
            return iso;
        }

        // =====================================================================
        // The Live Update Function
        // =====================================================================
        function updateSchema() {
            // Gather Data from inputs
            let movieTitleInput = document.getElementById('cb_movie_title');
            let movieName = (movieTitleInput && movieTitleInput.value.trim()) || d.movieName || d.movieTitle || "";
            let rating    = document.getElementById('cb_rating').value || "0";
            let director  = document.getElementById('cb_director').value || "";
            let date      = document.getElementById('cb_release_date').value || "";
            let castRaw   = document.getElementById('cb_cast').value || "";
            let durationR = document.getElementById('cb_duration').value || "";
            let synopsis  = document.getElementById('cb_synopsis').value || "";
            let prosRaw   = document.getElementById('cb_pros').value || "";
            let consRaw   = document.getElementById('cb_cons').value || "";

            // Clamp rating 0-10
            let ratingNum = parseFloat(rating);
            if (isNaN(ratingNum) || ratingNum < 0) ratingNum = 0;
            if (ratingNum > 10) ratingNum = 10;
            rating = String(ratingNum);

            // Process Lists
            let actors = castRaw.split(',')
                .map(n => n.trim()).filter(n => n)
                .map(n => ({ "@type": "Person", "name": n }));

            let positiveNotes = prosRaw.split('\n')
                .map(n => n.trim()).filter(n => n)
                .map((n, i) => ({ "@type": "ListItem", "position": i + 1, "name": n }));

            let negativeNotes = consRaw.split('\n')
                .map(n => n.trim()).filter(n => n)
                .map((n, i) => ({ "@type": "ListItem", "position": i + 1, "name": n }));

            // Build Movie Object (conditionally include fields)
            let movie = {
                "@type": "Movie",
                "name": movieName,
                "datePublished": date,
                "director": { "@type": "Person", "name": director },
                "actor": actors,
                "inLanguage": d.langCode || "en",
                "countryOfOrigin": { "@type": "Country", "name": "India" }
            };

            // Conditional fields — omit if empty
            if (d.imgUrl) movie["image"] = d.imgUrl;
            if (d.genres && d.genres.length > 0) movie["genre"] = d.genres;
            let durationISO = durationToISO(durationR);
            if (durationISO) movie["duration"] = durationISO;
            if (synopsis) movie["description"] = synopsis;

            // Build Publisher
            let publisher = {
                "@type": "Organization",
                "name": "CinemaBrief",
                "url": d.siteUrl || ""
            };
            if (d.logoUrl) {
                publisher["logo"] = {
                    "@type": "ImageObject",
                    "url": d.logoUrl
                };
            }

            // Build Full Schema
            let schemaData = {
                "@context": "https://schema.org",
                "@type": "Review",
                "url": d.permalink || "",
                "headline": d.movieTitle || "",
                "inLanguage": "en",
                "mainEntityOfPage": {
                    "@type": "WebPage",
                    "@id": d.permalink || ""
                },
                "datePublished": d.datePublished || "",
                "dateModified": d.dateModified || "",
                "itemReviewed": movie,
                "reviewRating": {
                    "@type": "Rating",
                    "ratingValue": rating,
                    "bestRating": "10",
                    "worstRating": "1"
                },
                "author": {
                    "@type": "Organization",
                    "name": "CinemaBrief",
                    "url": d.siteUrl || ""
                },
                "publisher": publisher
            };

            // Optional Notes — only add if exist
            if (positiveNotes.length > 0) {
                schemaData["positiveNotes"] = {
                    "@type": "ItemList",
                    "itemListElement": positiveNotes
                };
            }
            if (negativeNotes.length > 0) {
                schemaData["negativeNotes"] = {
                    "@type": "ItemList",
                    "itemListElement": negativeNotes
                };
            }

            // Print to Box (Pretty Print)
            outputBox.value = JSON.stringify(schemaData, null, 2);
        }

        // =====================================================================
        // Listen for changes
        // =====================================================================
        inputs.forEach(function(input) {
            input.addEventListener('input', updateSchema);
            input.addEventListener('change', updateSchema); // For date pickers
        });

        // =====================================================================
        // Copy Button Logic (Modern Clipboard API with fallback)
        // =====================================================================
        var copyBtn = document.getElementById('cb_copy_btn');
        if (copyBtn) {
            copyBtn.addEventListener('click', function(e) {
                e.preventDefault();
                var btn = this;

                // Modern Clipboard API
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(outputBox.value).then(function() {
                        btn.innerHTML = '✅ Copied!';
                        setTimeout(function() { btn.innerHTML = '📋 Copy & Validate'; }, 2000);
                    }).catch(function() {
                        // Fallback
                        fallbackCopy();
                    });
                } else {
                    fallbackCopy();
                }

                function fallbackCopy() {
                    outputBox.select();
                    try {
                        document.execCommand('copy');
                        btn.innerHTML = '✅ Copied!';
                    } catch(err) {
                        btn.innerHTML = '❌ Failed';
                    }
                    setTimeout(function() { btn.innerHTML = '📋 Copy & Validate'; }, 2000);
                }

                // Open Validator
                if (d.validatorUrl) {
                    window.open(d.validatorUrl, '_blank');
                }
            });
        }

    });
})();
