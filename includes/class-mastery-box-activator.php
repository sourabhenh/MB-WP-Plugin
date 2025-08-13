<?php

/**
 * Fired during plugin activation
 */
class Mastery_Box_Activator {

    /**
     * Activate the plugin.
     */
    public static function activate() {
        require_once plugin_dir_path(__FILE__) . 'class-mastery-box-database.php';
        Mastery_Box_Database::create_tables();

        // Create default gifts
        self::create_default_gifts();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Create default gifts
     */
    private static function create_default_gifts() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'masterybox_gifts';

        // Check if we already have gifts
        $existing_gifts = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

        if ($existing_gifts == 0) {
            // Insert default gifts
            $default_gifts = array(
                array(
                    'name' => 'Gold Prize',
                    'description' => 'Congratulations! You won the Gold Prize!',
                    'quality' => 'gold',
                    'win_percentage' => 10.0
                ),
                array(
                    'name' => 'Silver Prize',
                    'description' => 'Great! You won the Silver Prize!',
                    'quality' => 'silver',
                    'win_percentage' => 20.0
                ),
                array(
                    'name' => 'Bronze Prize',
                    'description' => 'Nice! You won the Bronze Prize!',
                    'quality' => 'bronze',
                    'win_percentage' => 30.0
                )
            );

            foreach ($default_gifts as $gift) {
                $wpdb->insert($table_name, $gift);
            }
        }
    }
}
