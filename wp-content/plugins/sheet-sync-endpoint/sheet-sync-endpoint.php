<?php
/**
 * Plugin Name: Sheet Sync Endpoint
 * Description: Adds a custom REST endpoint to receive data from Google Sheets.
 * Version: 1.0
 * Author: Your Name
 */

add_action( 'rest_api_init', 'myplugin_register_rest_endpoint' );

/**
 * Register  /wp-json/myplugin/v1/update-sheet/  (POST)
 */
function myplugin_register_rest_endpoint() {
    register_rest_route(
        'myplugin/v1',
        '/update-sheet/',
        array(
            'methods'             => 'POST',
            'callback'            => 'myplugin_update_sheet_callback',
            'permission_callback' => 'myplugin_permission_check',
        )
    );
}

/**
 * Allow any logged-in user who can edit posts.
 */
function myplugin_permission_check() {
    return current_user_can( 'edit_posts' );
}

/**
 * Handle the incoming Sheet payload.
 *
 * @param mixed $request  The REST request object (no class type-hint here!).
 * @return WP_REST_Response|WP_Error
 */
function myplugin_update_sheet_callback( $request ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'sheet_data';
    $params = $request->get_json_params();

    if ( empty( $params['rows'] ) || ! is_array( $params['rows'] ) ) {
        return new WP_Error(
            'invalid_payload',
            'Expected a "rows" array in JSON',
            array( 'status' => 400 )
        );
    }

    $rows = $params['rows'];
    $inserted = 0;

    foreach ( $rows as $row ) {
        // Insert each row as JSON
        $wpdb->insert(
            $table_name,
            array( 'data' => wp_json_encode( $row ) ),
            array( '%s' )
        );
        $inserted++;
    }

    return rest_ensure_response(
        array(
            'status'        => 'success',
            'inserted_rows' => $inserted,
        )
    );
}

// Hook to create table on activation
register_activation_hook( __FILE__, 'myplugin_create_table' );

function myplugin_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'sheet_data';
    $charset_collate = $wpdb->get_charset_collate();

    // Table schema: id (auto), data (JSON), created_at
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        data LONGTEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
