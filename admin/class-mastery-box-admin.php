<?php
/**
 * The admin-specific functionality of the plugin.
 */
class Mastery_Box_Admin {

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/mastery-box-admin.css', array(), $this->version, 'all');
    }

    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/mastery-box-admin.js', array('jquery'), $this->version, false);
        wp_localize_script($this->plugin_name, 'mastery_box_admin_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('mastery_box_admin_nonce')
        ));
    }

    public function add_admin_menu() {
        add_menu_page(
            __('Mastery Box', 'mastery-box'),
            __('Mastery Box', 'mastery-box'),
            'manage_options',
            'mastery-box',
            array($this, 'display_dashboard'),
            'dashicons-games',
            30
        );

        add_submenu_page('mastery-box', __('Dashboard', 'mastery-box'), __('Dashboard', 'mastery-box'), 'manage_options', 'mastery-box', array($this, 'display_dashboard'));
        add_submenu_page('mastery-box', __('Gifts', 'mastery-box'), __('Gifts', 'mastery-box'), 'manage_options', 'mastery-box-gifts', array($this, 'display_gifts'));
        add_submenu_page('mastery-box', __('Entries', 'mastery-box'), __('Entries', 'mastery-box'), 'manage_options', 'mastery-box-entries', array($this, 'display_entries'));
        add_submenu_page('mastery-box', __('Settings', 'mastery-box'), __('Settings', 'mastery-box'), 'manage_options', 'mastery-box-settings', array($this, 'display_settings'));
    }

    public function admin_init() {
        register_setting('mastery_box_settings', 'mastery_box_form_fields');
        register_setting('mastery_box_settings', 'mastery_box_number_of_boxes');
        register_setting('mastery_box_settings', 'mastery_box_win_message');
        register_setting('mastery_box_settings', 'mastery_box_lose_message');
        register_setting('mastery_box_settings', 'mastery_box_terms_label');
    }

    public function display_dashboard() {
        $stats = Mastery_Box_Database::get_statistics();
        include_once plugin_dir_path(__FILE__) . 'partials/mastery-box-admin-dashboard.php';
    }

    public function display_gifts() {
        if (!empty($_POST['submit_gift']) && isset($_POST['mastery_box_nonce']) && wp_verify_nonce($_POST['mastery_box_nonce'], 'mastery_box_gift_action')) {
            $this->handle_gift_submission();
        }
        if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete') {
            if (wp_verify_nonce($_GET['_wpnonce'], 'delete_gift_' . $_GET['id'])) {
                Mastery_Box_Database::delete_gift(intval($_GET['id']));
                wp_redirect(admin_url('admin.php?page=mastery-box-gifts&message=deleted'));
                exit;
            }
        }

        $gifts = Mastery_Box_Database::get_gifts();
        $edit_gift = isset($_GET['edit']) ? Mastery_Box_Database::get_gift(intval($_GET['edit'])) : null;

        include_once plugin_dir_path(__FILE__) . 'partials/mastery-box-admin-gifts.php';
    }

    public function display_entries() {
        // Handle delete
         if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete') {
        if (wp_verify_nonce($_GET['_wpnonce'], 'delete_entry_' . $_GET['id'])) {
            Mastery_Box_Database::delete_entry(intval($_GET['id']));
            wp_redirect(admin_url('admin.php?page=mastery-box-entries&message=deleted'));
            exit; // important to stop further output
        } else {
            wp_die(__('Security check failed', 'mastery-box'));
        }
    }

        $page         = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page     = 20;
        $offset       = ($page - 1) * $per_page;
        $entries      = Mastery_Box_Database::get_entries($offset, $per_page);
        $total_entries = Mastery_Box_Database::get_entries_count();
        $total_pages  = ceil($total_entries / $per_page);

        include_once plugin_dir_path(__FILE__) . 'partials/mastery-box-admin-entries.php';
    }

    public function display_settings() {
        include_once plugin_dir_path(__FILE__) . 'partials/mastery-box-admin-settings.php';
    }

    private function handle_gift_submission() {
        $quantity = (!empty($_POST['gift_quantity']) && is_numeric($_POST['gift_quantity'])) ? intval($_POST['gift_quantity']) : null;
        $data = array(
            'name'          => sanitize_text_field($_POST['gift_name']),
            'description'   => sanitize_textarea_field($_POST['gift_description']),
            'quality'       => sanitize_text_field($_POST['gift_quality']),
            'quantity'      => $quantity,
            'win_percentage'=> floatval($_POST['win_percentage'])
        );

        $gift_id = isset($_POST['gift_id']) ? intval($_POST['gift_id']) : null;
        Mastery_Box_Database::save_gift($data, $gift_id);
        $message = $gift_id ? 'updated' : 'created';
        wp_redirect(admin_url('admin.php?page=mastery-box-gifts&message=' . $message));
        exit;
    }
}
