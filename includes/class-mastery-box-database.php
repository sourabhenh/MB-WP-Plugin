<?php
/**
 * Database operations for the plugin
 */
class Mastery_Box_Database {

    /**
     * Create/upgrade plugin tables
     */
    public static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Gifts table
        $gifts_table = $wpdb->prefix . 'masterybox_gifts';
        $gifts_sql = "CREATE TABLE $gifts_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            quality varchar(50),
            quantity int(11) DEFAULT NULL,
            win_percentage decimal(5,2) DEFAULT 0.00,
            gift_image varchar(255) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Entries table
        $entries_table = $wpdb->prefix . 'masterybox_entries';
        $entries_sql = "CREATE TABLE $entries_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            user_data longtext,
            gift_won int(11) DEFAULT NULL,
            is_winner tinyint(1) DEFAULT 0,
            chosen_box int(11) NOT NULL,
            ip_address varchar(45),
            user_agent text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY gift_won (gift_won),
            KEY is_winner (is_winner),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($gifts_sql);
        dbDelta($entries_sql);

        // In case the table existed without gift_image, add it
        $columns = $wpdb->get_col("DESC $gifts_table", 0);
        if ($columns && !in_array('gift_image', $columns, true)) {
            $wpdb->query("ALTER TABLE $gifts_table ADD COLUMN gift_image varchar(255) DEFAULT NULL");
        }
    }

    /**
     * Get all gifts
     */
    public static function get_gifts() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'masterybox_gifts';
        return $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");
    }

    /**
     * Get gift by ID
     */
    public static function get_gift($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'masterybox_gifts';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", intval($id)));
    }

    /**
     * Insert or update gift
     * $data allowed keys: name, description, quality, quantity (nullable), win_percentage, gift_image
     */
    public static function save_gift($data, $id = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'masterybox_gifts';

        // Sanitize expected keys
        $allowed = array('name','description','quality','quantity','win_percentage','gift_image');
        $clean = array();
        foreach ($allowed as $k) {
            if (array_key_exists($k, $data)) {
                if ($k === 'quantity') {
                    $clean[$k] = ($data[$k] === '' || $data[$k] === null) ? null : intval($data[$k]);
                } elseif ($k === 'win_percentage') {
                    $clean[$k] = floatval($data[$k]);
                } elseif ($k === 'gift_image') {
                    $clean[$k] = esc_url_raw($data[$k]);
                } else {
                    $clean[$k] = is_string($data[$k]) ? sanitize_text_field($data[$k]) : $data[$k];
                }
            }
        }

        if ($id) {
            return $wpdb->update(
                $table_name,
                $clean,
                array('id' => intval($id))
            );
        } else {
            return $wpdb->insert(
                $table_name,
                $clean
            );
        }
    }

    /**
     * Delete gift
     */
    public static function delete_gift($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'masterybox_gifts';
        return $wpdb->delete($table_name, array('id' => intval($id)), array('%d'));
    }

    /**
     * Insert entry
     */
    public static function insert_entry($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'masterybox_entries';
        return $wpdb->insert($table_name, $data);
    }

    /**
     * Get entries with pagination (includes gift_name, gift_quality, gift_image)
     */
    public static function get_entries($offset = 0, $limit = 20) {
        global $wpdb;
        $entries_table = $wpdb->prefix . 'masterybox_entries';
        $gifts_table   = $wpdb->prefix . 'masterybox_gifts';

        return $wpdb->get_results($wpdb->prepare("
            SELECT e.*, g.name as gift_name, g.quality as gift_quality, g.gift_image as gift_image
            FROM $entries_table e
            LEFT JOIN $gifts_table g ON e.gift_won = g.id
            ORDER BY e.created_at DESC
            LIMIT %d OFFSET %d
        ", $limit, $offset));
    }

    /**
     * Get entries total count
     */
    public static function get_entries_count() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'masterybox_entries';
        return $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    }

    /**
     * Delete entry
     */
    public static function delete_entry($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'masterybox_entries';
        return $wpdb->delete($table_name, array('id' => intval($id)), array('%d'));
    }

    /**
     * Get statistics
     */
    public static function get_statistics() {
        global $wpdb;
        $entries_table = $wpdb->prefix . 'masterybox_entries';
        $gifts_table   = $wpdb->prefix . 'masterybox_gifts';

        $stats = array();

        $stats['total_plays']   = $wpdb->get_var("SELECT COUNT(*) FROM $entries_table");
        $stats['total_winners'] = $wpdb->get_var("SELECT COUNT(*) FROM $entries_table WHERE is_winner = 1");
        $stats['win_percentage'] = $stats['total_plays'] > 0 ? round(($stats['total_winners'] / $stats['total_plays']) * 100, 2) : 0;

        $stats['gift_distribution'] = $wpdb->get_results("
            SELECT g.name, g.quality, COUNT(e.id) as count
            FROM $gifts_table g
            LEFT JOIN $entries_table e ON g.id = e.gift_won
            GROUP BY g.id
            ORDER BY count DESC
        ");

        return $stats;
    }

    /**
     * Determine winning gift based on percentages and quantity
     */
    public static function determine_winner() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'masterybox_gifts';

        $gifts = $wpdb->get_results("
            SELECT * FROM $table_name
            WHERE win_percentage > 0 AND (quantity IS NULL OR quantity > 0)
            ORDER BY win_percentage DESC
        ");

        if (empty($gifts)) {
            return null;
        }

        $random = mt_rand(0, 10000) / 100; // 0.00 - 100.00
        $cumulative = 0;

        foreach ($gifts as $gift) {
            $cumulative += floatval($gift->win_percentage);
            if ($random <= $cumulative) {
                if (!is_null($gift->quantity)) {
                    $new_qty = max(0, intval($gift->quantity) - 1);
                    $wpdb->update($table_name, array('quantity' => $new_qty), array('id' => intval($gift->id)));
                }
                return $gift;
            }
        }

        return null;
    }
}
