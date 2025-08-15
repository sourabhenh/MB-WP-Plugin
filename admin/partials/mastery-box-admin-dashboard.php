<?php
/**
 * Provide a admin area view for the plugin
 * This file is used to markup the admin-facing aspects of the plugin.
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="mastery-box-dashboard">
        <div class="mastery-box-stats-grid">
            <div class="mastery-box-stat-card">
                <div class="stat-icon">🎮</div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['total_plays']); ?></h3>
                    <p><?php _e('Total Plays', 'mastery-box'); ?></p>
                </div>
            </div>

            <div class="mastery-box-stat-card">
                <div class="stat-icon">🏆</div>
                <div class="stat-content">
                    <h3><?php echo number_format($stats['total_winners']); ?></h3>
                    <p><?php _e('Total Winners', 'mastery-box'); ?></p>
                </div>
            </div>

            <div class="mastery-box-stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-content">
                    <h3><?php echo $stats['win_percentage']; ?>%</h3>
                    <p><?php _e('Win Rate', 'mastery-box'); ?></p>
                </div>
            </div>
        </div>

        <div class="mastery-box-dashboard-section">
            <h2><?php _e('Gift Distribution', 'mastery-box'); ?></h2>
            <?php if (!empty($stats['gift_distribution'])): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Gift Name', 'mastery-box'); ?></th>
                            <th><?php _e('Quality', 'mastery-box'); ?></th>
                            <th><?php _e('Times Won', 'mastery-box'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['gift_distribution'] as $gift): ?>
                            <tr>
                                <td><?php echo esc_html($gift->name); ?></td>
                                <td>
                                    <span class="gift-quality gift-quality-<?php echo esc_attr($gift->quality); ?>">
                                        <?php echo esc_html(ucfirst($gift->quality)); ?>
                                    </span>
                                </td>
                                <td><?php echo number_format($gift->count); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><?php _e('No data available yet.', 'mastery-box'); ?></p>
            <?php endif; ?>
        </div>

        <div class="mastery-box-dashboard-section">
            <h2><?php _e('Quick Actions', 'mastery-box'); ?></h2>
            <div class="mastery-box-quick-actions">
                <a href="<?php echo admin_url('admin.php?page=mastery-box-gifts'); ?>" class="button button-primary">
                    <?php _e('Manage Gifts', 'mastery-box'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=mastery-box-entries'); ?>" class="button button-secondary">
                    <?php _e('View Entries', 'mastery-box'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=mastery-box-settings'); ?>" class="button button-secondary">
                    <?php _e('Settings', 'mastery-box'); ?>
                </a>
            </div>
        </div>

        <div class="mastery-box-dashboard-section">
            <h2><?php _e('Shortcodes', 'mastery-box'); ?></h2>
            <p><?php _e('Use these shortcodes to display the Mastery Box game on your pages:', 'mastery-box'); ?></p>
            <div class="mastery-box-shortcodes">
                <div class="shortcode-item">
                    <strong><?php _e('Form Shortcode:', 'mastery-box'); ?></strong>
                    <code>[masterybox_form]</code>
                    <p class="description"><?php _e('Displays the entry form where users submit their information.', 'mastery-box'); ?></p>
                </div>
                <div class="shortcode-item">
                    <strong><?php _e('Game Shortcode:', 'mastery-box'); ?></strong>
                    <code>[masterybox_game]</code>
                    <p class="description"><?php _e('Displays the game interface with gift boxes.', 'mastery-box'); ?></p>
                </div>
				<div class="shortcode-item">
                    <strong><?php _e('Result Shortcode:', 'mastery-box'); ?></strong>
                    <code>[masterybox_result]</code>
                    <p class="description"><?php _e('Displays the resuilt interface with gift boxes.', 'mastery-box'); ?></p>
                </div>
				
            </div>
        </div>
    </div>
</div>
