<?php
/**
 * Plugin Name: Mastery Box
 * Plugin URI: https://your-website.com
 * Description: An interactive WordPress plugin game where users fill out a form, proceed to a game page with gift boxes, pick one, and instantly see if they've won.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: mastery-box
 * Domain Path: /languages
 */

if (!ob_get_level()) {
    ob_start();
}

if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'MASTERY_BOX_VERSION', '1.0.0' );
define( 'MASTERY_BOX_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MASTERY_BOX_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

function activate_mastery_box() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-mastery-box-activator.php';
    Mastery_Box_Activator::activate();
}

function deactivate_mastery_box() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-mastery-box-deactivator.php';
    Mastery_Box_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_mastery_box' );
register_deactivation_hook( __FILE__, 'deactivate_mastery_box' );

/**
 * Enqueue media scripts for admin
 */
add_action( 'admin_enqueue_scripts', function() {
    wp_enqueue_media();
} );

/**
 * Save handler for settings (runs before any output)
 */
add_action( 'admin_post_masterybox_save_settings', 'mastery_box_handle_settings_save' );

function mastery_box_handle_settings_save() {
    if ( ! isset( $_POST['mastery_box_nonce'] ) || ! wp_verify_nonce( $_POST['mastery_box_nonce'], 'mastery_box_settings_action' ) ) {
        wp_die( __( 'Security check failed', 'mastery-box' ) );
    }

    // Save existing settings
    update_option( 'mastery_box_form_fields', sanitize_textarea_field( $_POST['form_fields'] ?? '' ) );
    update_option( 'mastery_box_number_of_boxes', intval( $_POST['number_of_boxes'] ?? 3 ) );
    update_option( 'mastery_box_win_message', sanitize_textarea_field( $_POST['win_message'] ?? '' ) );
    update_option( 'mastery_box_lose_message', sanitize_textarea_field( $_POST['lose_message'] ?? '' ) );

    // Save Terms & Conditions label (HTML allowed)
    update_option( 'mastery_box_terms_label', wp_kses_post( stripslashes( $_POST['terms_label'] ?? '' ) ) );

    // NEW: Save box images
    update_option( 'mastery_box_default_box_image', esc_url_raw( $_POST['default_box_image'] ?? '' ) );
    
    $box_images = array();
    if ( !empty( $_POST['box_images'] ) && is_array( $_POST['box_images'] ) ) {
        foreach ( $_POST['box_images'] as $idx => $url ) {
            $idx = intval( $idx );
            $url = esc_url_raw( $url );
            if ( $idx > 0 && !empty( $url ) ) {
                $box_images[$idx] = $url;
            }
        }
    }
    update_option( 'mastery_box_box_images', $box_images );

    wp_redirect( admin_url( 'admin.php?page=mastery-box-settings&message=updated' ) );
    exit;
}

add_action( 'admin_post_mastery_box_export_entries', 'mastery_box_export_entries_to_csv' );

function mastery_box_export_entries_to_csv() {
    if ( !current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Unauthorized', 'mastery-box' ) );
    }
    if ( !isset( $_POST['mastery_box_export_nonce'] ) || !wp_verify_nonce( $_POST['mastery_box_export_nonce'], 'mastery_box_export_entries' ) ) {
        wp_die( __( 'Security check failed', 'mastery-box' ) );
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'masterybox_entries';
    $gifts_table = $wpdb->prefix . 'masterybox_gifts';

    // Columns as set in admin table
    $default_order = ['fullname','emailaddress','mobilenumber','emirates','nationality','store','receipt_file','termsandconditions'];

    // Read field config for labels
    $field_defs = get_option( 'mastery_box_form_fields' );
    $field_columns = [];
    $field_labels  = [];
    foreach ( $default_order as $field ) {
        $field_columns[] = $field;
    }
    if ( $field_defs ) {
        foreach ( explode( "\n", $field_defs ) as $f ) {
            $parts = explode( '|', trim( $f ) );
            if ( count( $parts ) >= 2 ) {
                $field_labels[strtolower( $parts[0] )] = $parts[1];
            }
        }
    }

    // Fetch all with gift name
    $entries = $wpdb->get_results( "
        SELECT e.*, g.name as gift_name
        FROM $table_name e
        LEFT JOIN $gifts_table g ON e.gift_won = g.id
        ORDER BY e.id ASC
    ", ARRAY_A );

    if ( empty( $entries ) ) {
        wp_die( __( 'No entries found to export.', 'mastery-box' ) );
    }

    // Send csv headers
    header( 'Content-Type: text/csv; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename=mastery-box-entries-' . date( 'Y-m-d' ) . '.csv' );
    $output = fopen( 'php://output', 'w' );

    // Output column header row
    $csv_headers = [];
    foreach ( $field_columns as $col ) {
        $csv_headers[] = isset( $field_labels[$col] ) ? $field_labels[$col] : ucfirst( $col );
    }
    $csv_headers = array_merge(
        $csv_headers,
        ['Box', 'Result', 'Gift Won', 'Date', 'IP Address']
    );
    fputcsv( $output, $csv_headers );

    foreach ( $entries as $entry ) {
        $user_data = json_decode( $entry['user_data'], true );
        $row = [];
        foreach ( $field_columns as $col ) {
            $val = isset( $user_data[$col] ) ? $user_data[$col] : '';
            if ( $col === 'receipt_file' && $val ) {
                $row[] = $val;
            } elseif ( $col === 'termsandconditions' ) {
                $row[] = $val ? 'Checked' : '';
            } else {
                $row[] = $val;
            }
        }
        $row[] = $entry['chosen_box'];
        $row[] = $entry['is_winner'] ? 'WIN' : 'LOSE';
        $row[] = $entry['gift_name'] ? $entry['gift_name'] : 'No prize';
        $row[] = $entry['created_at'];
        $row[] = $entry['ip_address'];
        fputcsv( $output, $row );
    }
    fclose( $output );
    exit;
}

require plugin_dir_path( __FILE__ ) . 'includes/class-mastery-box.php';

function run_mastery_box() {
    $plugin = new Mastery_Box();
    $plugin->run();
}
run_mastery_box();
