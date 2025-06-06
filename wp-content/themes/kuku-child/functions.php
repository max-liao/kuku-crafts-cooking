<?php
/**
 * Kuku Child Theme Functions
 *
 * Enqueues the React app bundles on the “Performance Test – Pine Cone” page,
 * using the hashed filenames from asset-manifest.json.
 */

function kuku_enqueue_react() {
    // Only load on the specific page slug
    if ( ! is_page( 'performance-test-pine-cone' ) ) {
        return;
    }

    // Path to the manifest file
    $manifest_path = get_stylesheet_directory() . '/react-build/asset-manifest.json';
    if ( ! file_exists( $manifest_path ) ) {
        return;
    }

    // Decode the manifest
    $manifest = json_decode( file_get_contents( $manifest_path ), true );
    if ( ! isset( $manifest['files']['main.js'] ) ) {
        return;
    }

    // Enqueue the JS bundle (manifest already has the full WP-relative URL)
    $main_js = $manifest['files']['main.js'];
    wp_enqueue_script(
        'kuku-react-app',
        esc_url( $main_js ),  // e.g. "/wp-content/themes/kuku-child/react-build/static/js/main.69396cde.js"
        array(),
        null,
        true
    );

    // Enqueue the CSS bundle if it exists
    if ( isset( $manifest['files']['main.css'] ) ) {
        $main_css = $manifest['files']['main.css'];
        wp_enqueue_style(
            'kuku-react-css',
            esc_url( $main_css ),  // e.g. "/wp-content/themes/kuku-child/react-build/static/css/main.e6c13ad2.css"
            array(),
            null
        );
    }
}

add_action( 'wp_enqueue_scripts', 'kuku_enqueue_react' );
function kuku_register_pinecone_block() {
  // Load dependencies and version from the built asset file
  $asset_file = include( get_theme_file_path( 'build/pine-cone-block/index.asset.php' ) );

  wp_register_script(
    'kuku-pinecone-block',
    get_theme_file_uri( 'build/pine-cone-block/index.js' ),
    $asset_file['dependencies'],
    $asset_file['version']
  );

  register_block_type( get_theme_file_path( 'build/pine-cone-block' ) );
}
add_action( 'init', 'kuku_register_pinecone_block' );

// Register the REST endpoint
add_action('rest_api_init', function() {
    register_rest_route('myplugin/v1', '/update-sheet/', array(
        'methods' => 'POST',
        'callback' => 'myplugin_update_sheet_callback',
        'permission_callback' => function() {
            // Application Passwords handle authentication, so allow if logged in
            return current_user_can('edit_posts');
        },
    ));
});
