<?php
/**
 * Plugin Name: Sheet Sync Endpoint
 * Description: Adds a custom REST endpoint to receive data from Google Sheets.
 * Version: 1.3
 * Author: Your Name
 */

add_action('rest_api_init', 'myplugin_register_rest_endpoints');

/**
 * Register both POST and GET endpoints
 */
function myplugin_register_rest_endpoints() {
    // POST: /wp-json/google_sheets_plugin/v1/update-sheet/
    register_rest_route(
        'google_sheets_plugin/v1',
        '/update-sheet/',
        array(
            'methods'             => 'POST',
            'callback'            => 'myplugin_update_sheet_callback',
            'permission_callback' => 'myplugin_permission_check',
        )
    );

    // GET: /wp-json/google_sheets_plugin/v1/get-sheet/
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
 * Check if the current user has permission to update the sheet data
 */
function myplugin_permission_check() {
    return current_user_can('edit_posts');
}

/**
 * Handle incoming Google Sheets data and store it in the database
 */
function myplugin_update_sheet_callback($request) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'sheet_data';
    $params = $request->get_json_params();

    if (empty($params['rows']) || !is_array($params['rows'])) {
        return new WP_Error(
            'invalid_payload',
            'Expected a "rows" array in JSON',
            array('status' => 400)
        );
    }

    $rows = $params['rows'];
    $inserted = 0;

    // Clear old rows to replace with new data
    $wpdb->query("DELETE FROM $table_name");

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
 * Return stored sheet data as JSON
 */
function myplugin_get_sheet_data() {
    global $wpdb;
    $table = $wpdb->prefix . 'sheet_data';

    $rows = $wpdb->get_results("SELECT data FROM $table ORDER BY id ASC", ARRAY_A);

    if (empty($rows)) {
        return rest_ensure_response([]);
    }

    return rest_ensure_response(array_map(function ($row) {
        return json_decode($row['data'], true);
    }, $rows));
}

/**
 * Create the custom table for storing sheet data on plugin activation
 */
register_activation_hook(__FILE__, 'myplugin_create_table');

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

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * Shortcode: [show_sheet_data]
 * Renders the stored sheet data as an HTML table
 */
add_shortcode('show_sheet_data', 'myplugin_render_sheet_data');

function myplugin_render_sheet_data() {
    global $wpdb;
    $table = $wpdb->prefix . 'sheet_data';

    $rows = $wpdb->get_results("SELECT data, created_at FROM $table ORDER BY id ASC", ARRAY_A);

    if (empty($rows)) {
        return '<p>No data found.</p>';
    }

    $output = '<table border="1" cellpadding="6" style="border-collapse: collapse; width: 100%;">';
    $output .= '<thead><tr><th>#</th>';

    $first_row_data = json_decode($rows[0]['data'], true);
    foreach (array_keys($first_row_data) as $key) {
        $output .= '<th>' . esc_html($key) . '</th>';
    }
    $output .= '<th>Created At</th></tr></thead><tbody>';

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
