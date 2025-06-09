<?php
/**
 * Kuku Child Theme – functions.php
 */

/* --------------------------------------------------
 * 1. Load child-theme stylesheet
 * -------------------------------------------------- */
add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'kuku-child-style',          // handle
        get_stylesheet_uri(),        // /kuku-child/style.css
        [],                          // no deps; loads after parent because of priority 20
        wp_get_theme()->get( 'Version' )
    );
}, 20 ); // priority 20 ➜ after parent theme styles


/* --------------------------------------------------
 * 2. Enqueue React bundles on the Pine-Cone page
 * -------------------------------------------------- */
function kuku_enqueue_react() {

    if ( ! is_page( 'performance-test-pine-cone' ) ) {
        return;
    }

    $manifest_path = get_stylesheet_directory() . '/react-build/asset-manifest.json';
    if ( ! file_exists( $manifest_path ) ) {
        return;
    }

    $manifest = json_decode( file_get_contents( $manifest_path ), true );
    if ( empty( $manifest['files']['main.js'] ) ) {
        return;
    }

    // JS bundle
    wp_enqueue_script(
        'kuku-react-app',
        esc_url( $manifest['files']['main.js'] ),
        [],
        null,
        true
    );

    // CSS bundle (optional)
    if ( ! empty( $manifest['files']['main.css'] ) ) {
        wp_enqueue_style(
            'kuku-react-css',
            esc_url( $manifest['files']['main.css'] ),
            [],
            null
        );
    }
}
add_action( 'wp_enqueue_scripts', 'kuku_enqueue_react' );


/* --------------------------------------------------
 * 3. Register custom Pine-Cone block
 * -------------------------------------------------- */
function kuku_register_pinecone_block() {

    $asset_file = include get_theme_file_path( 'build/pine-cone-block/index.asset.php' );

    wp_register_script(
        'kuku-pinecone-block',
        get_theme_file_uri( 'build/pine-cone-block/index.js' ),
        $asset_file['dependencies'],
        $asset_file['version']
    );

    register_block_type( get_theme_file_path( 'build/pine-cone-block' ) );
}
add_action( 'init', 'kuku_register_pinecone_block' );


/* --------------------------------------------------
 * 4. Simple REST endpoint for sheet update
 * -------------------------------------------------- */
add_action( 'rest_api_init', function () {
    register_rest_route( 'myplugin/v1', '/update-sheet/', [
        'methods'             => 'POST',
        'callback'            => 'myplugin_update_sheet_callback',
        'permission_callback' => function () {
            return current_user_can( 'edit_posts' );
        },
    ] );
} );


/* --------------------------------------------------
 * 5. Enqueue dark-mode toggle script
 * -------------------------------------------------- */
add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_script(
        'kuku-color-toggle',
        get_stylesheet_directory_uri() . '/js/color-toggle.js',
        [],
        wp_get_theme()->get( 'Version' ),
        true
    );
} );


/* --------------------------------------------------
 * 6. Inline CSS that overrides TT25 global styles in dark mode
 * -------------------------------------------------- */
add_action( 'wp_head', function () { ?>
    <style id="kuku-dark-mode">
        body.dark-mode{
            --wp--preset--color--base:#121212;
            --wp--preset--color--contrast:#e5e5e5;
            --wp--preset--color--primary:#8ab4ff;
            background-color:#121212!important;
            color:#e5e5e5!important;
        }
        body.dark-mode a{color:var(--wp--preset--color--primary)!important;}
        body.dark-mode hr,
        body.dark-mode input,
        body.dark-mode textarea{border-color:#333!important;}
        body.dark-mode .custom-logo-link img{border:2px solid #333;}
    </style>
<?php
}, 100 );   // run after global-styles inline block
