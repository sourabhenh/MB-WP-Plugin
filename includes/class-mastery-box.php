<?php

/**
 * The file that defines the core plugin class
 */
class Mastery_Box {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     */
    public function __construct() {
        if (defined('MASTERY_BOX_VERSION')) {
            $this->version = MASTERY_BOX_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'mastery-box';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-mastery-box-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-mastery-box-i18n.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-mastery-box-admin.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-mastery-box-public.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-mastery-box-database.php';

        $this->loader = new Mastery_Box_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     */
    private function set_locale() {
        $plugin_i18n = new Mastery_Box_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     */
    private function define_admin_hooks() {
        $plugin_admin = new Mastery_Box_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');
        $this->loader->add_action('admin_init', $plugin_admin, 'admin_init');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     */
    private function define_public_hooks() {
        $plugin_public = new Mastery_Box_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        $this->loader->add_action('init', $plugin_public, 'init_shortcodes');
        $this->loader->add_action('wp_ajax_mastery_box_submit_form', $plugin_public, 'handle_form_submission');
        $this->loader->add_action('wp_ajax_nopriv_mastery_box_submit_form', $plugin_public, 'handle_form_submission');
        $this->loader->add_action('wp_ajax_mastery_box_play_game', $plugin_public, 'handle_game_play');
        $this->loader->add_action('wp_ajax_nopriv_mastery_box_play_game', $plugin_public, 'handle_game_play');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}
