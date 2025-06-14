<?php
/**
 * Plugin Name: Sheet Sync Endpoint
 * Description: Adds a custom REST endpoint to receive data from Google Sheets.
 * Version: 1.1
 * Author: Your Name
 */

add_action( 'rest_api_init', 'myplugin_register_rest_endpoint' );

/**
 * Register /wp-json/myplugin/v1/update-sheet/ (POST)
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

// Create table on plugin activation
register_activation_hook( __FILE__, 'myplugin_create_table' );

function myplugin_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'sheet_data';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        data LONGTEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

// ✅ Shortcode: [show_sheet_data]
add_shortcode('show_sheet_data', 'myplugin_render_sheet_data');

function myplugin_render_sheet_data() {
    global $wpdb;
    $table = $wpdb->prefix . 'sheet_data';

    // Get the latest 10 rows
    $rows = $wpdb->get_results("SELECT data, created_at FROM $table ORDER BY created_at DESC LIMIT 10", ARRAY_A);
    if ( empty( $rows ) ) {
        return '<p>No data found.</p>';
    }

    $output = '<table border="1" cellpadding="6" style="border-collapse: collapse; width: 100%;">';
    $output .= '<thead><tr>';

    // Extract keys from first row
    $first_row_data = json_decode( $rows[0]['data'], true );
    foreach ( array_keys( $first_row_data ) as $key ) {
        $output .= '<th>' . esc_html( $key ) . '</th>';
    }
    $output .= '<th>Created At</th></tr></thead><tbody>';

    foreach ( $rows as $row ) {
        $data = json_decode( $row['data'], true );
        $output .= '<tr>';
        foreach ( $first_row_data as $key => $_ ) {
            $output .= '<td>' . esc_html( $data[ $key ] ?? '' ) . '</td>';
        }
        $output .= '<td>' . esc_html( $row['created_at'] ) . '</td>';
        $output .= '</tr>';
    }

    $output .= '</tbody></table>';
    return $output;
}
