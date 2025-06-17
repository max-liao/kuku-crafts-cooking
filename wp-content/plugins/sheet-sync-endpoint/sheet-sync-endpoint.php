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

    // Clear old data before inserting new sheet rows
    $wpdb->query("DELETE FROM $table_name");

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

// Shortcode: [show_sheet_data]
add_shortcode('show_sheet_data', 'myplugin_render_sheet_data');
function myplugin_render_sheet_data() {
    global $wpdb;
    $table = $wpdb->prefix . 'sheet_data';

    // Fetch all rows from the table, oldest first
    $rows = $wpdb->get_results("SELECT data, created_at FROM $table ORDER BY id ASC", ARRAY_A);

    // If no data found, return a message
    if ( empty( $rows ) ) {
        return '<p>No data found.</p>';
    }

    // Start building the HTML table
    $output = '<table border="1" cellpadding="6" style="border-collapse: collapse; width: 100%;">';
    $output .= '<thead><tr>';

    // Add row number header
    $output .= '<th>#</th>';

    // Get column headers from the first row of data
    $first_row_data = json_decode( $rows[0]['data'], true );
    foreach ( array_keys( $first_row_data ) as $key ) {
        $output .= '<th>' . esc_html( $key ) . '</th>';
    }

    // Add "Created At" column header
    $output .= '<th>Created At</th></tr></thead><tbody>';

    // Initialize row counter
    $row_num = 1;

    // Loop through each row in the database
    foreach ( $rows as $row ) {
        $data = json_decode( $row['data'], true );

        $output .= '<tr>';

        // Add row number column
        $output .= '<td>' . $row_num++ . '</td>';

        // Output each column in the same order as the header
        foreach ( $first_row_data as $key => $_ ) {
            $output .= '<td>' . esc_html( $data[ $key ] ?? '' ) . '</td>';
        }

        // Add created_at timestamp
        $output .= '<td>' . esc_html( $row['created_at'] ) . '</td>';
        $output .= '</tr>';
    }

    $output .= '</tbody></table>';

    return $output;
}
