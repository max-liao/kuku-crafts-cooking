<?php
/**
 * Plugin Name: Sheet Sync Endpoint
 * Description: Adds custom REST endpoints to receive and serve data from Google Sheets.
 * Version: 1.3
 * Author: Max Liao
 */

// Register REST endpoints when the REST API is initialized
add_action('rest_api_init', 'myplugin_register_rest_endpoints');

/**
 * Register both POST and GET endpoints:
 * - POST: Used by Google Apps Script to send sheet data
 * - GET: Used by the frontend (e.g. React) to fetch that data
 */
function myplugin_register_rest_endpoints() {
    // POST endpoint: /wp-json/google_sheets_plugin/v1/update-sheet/
    register_rest_route(
        'google_sheets_plugin/v1',
        '/update-sheet/',
        array(
            'methods'             => 'POST',
            'callback'            => 'myplugin_update_sheet_callback',
            'permission_callback' => 'myplugin_permission_check',
        )
    );

    // GET endpoint: /wp-json/google_sheets_plugin/v1/get-sheet/
    register_rest_route(
        'google_sheets_plugin/v1',
        '/get-sheet/',
        array(
            'methods'             => 'GET',
            'callback'            => 'myplugin_get_sheet_data',
            'permission_callback' => '__return_true', // Public access
        )
    );
}

/**
 * Permission check for the POST route
 * Only logged-in users with post-editing rights can update the sheet
 */
function myplugin_permission_check() {
    return current_user_can('edit_posts');
}

/**
 * Callback for the POST endpoint
 * Expects a JSON payload with a "rows" array
 * Clears old data and inserts new rows into the custom database table
 */
function myplugin_update_sheet_callback($request) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'sheet_data';
    $params = $request->get_json_params();

    // Validate payload
    if (empty($params['rows']) || !is_array($params['rows'])) {
        return new WP_Error(
            'invalid_payload',
            'Expected a "rows" array in JSON',
            array('status' => 400)
        );
    }

    $rows = $params['rows'];
    $inserted = 0;

    // Wipe previous data
    $wpdb->query("DELETE FROM $table_name");

    // Insert new data
    foreach ($rows as $row) {
        $wpdb->insert(
            $table_name,
            array('data' => wp_json_encode($row)),
            array('%s')
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

/**
 * Callback for the GET endpoint
 * Returns stored sheet data as a decoded JSON array
 */
function myplugin_get_sheet_data() {
    global $wpdb;
    $table = $wpdb->prefix . 'sheet_data';

    $rows = $wpdb->get_results("SELECT data FROM $table ORDER BY id ASC", ARRAY_A);

    if (empty($rows)) {
        return rest_ensure_response([]);
    }

    // Decode each row's JSON before returning
    return rest_ensure_response(array_map(function ($row) {
        return json_decode($row['data'], true);
    }, $rows));
}

/**
 * Hook that runs on plugin activation
 * Creates the custom table if it doesn't already exist
 */
register_activation_hook(__FILE__, 'myplugin_create_table');

function myplugin_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'sheet_data';
    $charset_collate = $wpdb->get_charset_collate();

    // Table: id, JSON-encoded data, timestamp
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        data LONGTEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * Optional shortcode: [show_sheet_data]
 * Renders the saved sheet data in an HTML table for non-JS fallback
 */
add_shortcode('show_sheet_data', 'myplugin_render_sheet_data');

function myplugin_render_sheet_data() {
    global $wpdb;
    $table = $wpdb->prefix . 'sheet_data';

    $rows = $wpdb->get_results("SELECT data, created_at FROM $table ORDER BY id ASC", ARRAY_A);

    if (empty($rows)) {
        return '<p>No data found.</p>';
    }

    // Start building the HTML table
    $output = '<table border="1" cellpadding="6" style="border-collapse: collapse; width: 100%;">';
    $output .= '<thead><tr><th>#</th>';

    // Generate headers from the first row’s keys
    $first_row_data = json_decode($rows[0]['data'], true);
    foreach (array_keys($first_row_data) as $key) {
        $output .= '<th>' . esc_html($key) . '</th>';
    }
    $output .= '<th>Created At</th></tr></thead><tbody>';

    // Render each row
    $row_num = 1;
    foreach ($rows as $row) {
        $data = json_decode($row['data'], true);
        $output .= '<tr><td>' . $row_num++ . '</td>';
        foreach ($first_row_data as $key => $_) {
            $output .= '<td>' . esc_html($data[$key] ?? '') . '</td>';
        }
        $output .= '<td>' . esc_html($row['created_at']) . '</td></tr>';
    }

    $output .= '</tbody></table>';
    return $output;
}
