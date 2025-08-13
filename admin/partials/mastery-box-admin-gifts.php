<?php
/**
 * Provide a admin area view for managing gifts
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>
<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php if (isset($_GET['message'])): ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php
                switch ($_GET['message']) {
                    case 'created':
                        _e('Gift created successfully!', 'mastery-box');
                        break;
                    case 'updated':
                        _e('Gift updated successfully!', 'mastery-box');
                        break;
                    case 'deleted':
                        _e('Gift deleted successfully!', 'mastery-box');
                        break;
                }
                ?>
            </p>
        </div>
    <?php endif; ?>

    <div class="mastery-box-gifts-page">
        <div class="mastery-box-gifts-form">
            <h2><?php echo $edit_gift ? __('Edit Gift', 'mastery-box') : __('Add New Gift', 'mastery-box'); ?></h2>

            <form method="post" action="">
                <?php wp_nonce_field('mastery_box_gift_action', 'mastery_box_nonce'); ?>
                <?php if ($edit_gift): ?>
                    <input type="hidden" name="gift_id" value="<?php echo esc_attr($edit_gift->id); ?>">
                <?php endif; ?>

                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row"><label for="gift_name"><?php _e('Gift Name', 'mastery-box'); ?></label></th>
                            <td>
                                <input type="text" name="gift_name" id="gift_name" class="regular-text"
                                       value="<?php echo $edit_gift ? esc_attr($edit_gift->name) : ''; ?>" required>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="gift_description"><?php _e('Description', 'mastery-box'); ?></label></th>
                            <td>
                                <textarea name="gift_description" id="gift_description" rows="4" cols="50"><?php 
                                    echo $edit_gift ? esc_textarea($edit_gift->description) : ''; 
                                ?></textarea>
                                <p class="description"><?php _e('This message will be shown to users when they win this gift.', 'mastery-box'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="gift_quality"><?php _e('Quality/Tier', 'mastery-box'); ?></label></th>
                            <td>
                                <select name="gift_quality" id="gift_quality">
                                    <option value="bronze" <?php selected($edit_gift ? $edit_gift->quality : '', 'bronze'); ?>><?php _e('Bronze', 'mastery-box'); ?></option>
                                    <option value="silver" <?php selected($edit_gift ? $edit_gift->quality : '', 'silver'); ?>><?php _e('Silver', 'mastery-box'); ?></option>
                                    <option value="gold" <?php selected($edit_gift ? $edit_gift->quality : '', 'gold'); ?>><?php _e('Gold', 'mastery-box'); ?></option>
                                    <option value="platinum" <?php selected($edit_gift ? $edit_gift->quality : '', 'platinum'); ?>><?php _e('Platinum', 'mastery-box'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="gift_quantity"><?php _e('Quantity', 'mastery-box'); ?></label></th>
                            <td>
                                <input type="number" name="gift_quantity" id="gift_quantity" min="0"
                                       value="<?php echo $edit_gift && isset($edit_gift->quantity) ? esc_attr($edit_gift->quantity) : ''; ?>">
                                <p class="description"><?php _e('Leave blank for unlimited. Otherwise, prizes are limited to this quantity.', 'mastery-box'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="win_percentage"><?php _e('Win Percentage', 'mastery-box'); ?></label></th>
                            <td>
                                <input type="number" name="win_percentage" id="win_percentage"
                                       min="0" max="100" step="0.01"
                                       value="<?php echo $edit_gift ? esc_attr($edit_gift->win_percentage) : '10'; ?>">
                                <span>%</span>
                                <p class="description"><?php _e('Probability that users will win this gift (0-100%).', 'mastery-box'); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php submit_button($edit_gift ? __('Update Gift', 'mastery-box') : __('Add Gift', 'mastery-box'), 'primary', 'submit_gift'); ?>

                <?php if ($edit_gift): ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=mastery-box-gifts')); ?>" class="button button-secondary">
                        <?php _e('Cancel', 'mastery-box'); ?>
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <div class="mastery-box-gifts-list">
            <h2><?php _e('Existing Gifts', 'mastery-box'); ?></h2>

            <?php if (!empty($gifts)): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Name', 'mastery-box'); ?></th>
                            <th><?php _e('Quality', 'mastery-box'); ?></th>
                            <th><?php _e('Quantity', 'mastery-box'); ?></th>
                            <th><?php _e('Win %', 'mastery-box'); ?></th>
                            <th><?php _e('Created', 'mastery-box'); ?></th>
                            <th><?php _e('Actions', 'mastery-box'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($gifts as $gift): ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($gift->name); ?></strong>
                                    <?php if (!empty($gift->description)): ?>
                                        <div class="row-actions">
                                            <small><?php echo esc_html(wp_trim_words($gift->description, 10)); ?></small>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="gift-quality gift-quality-<?php echo esc_attr($gift->quality); ?>">
                                        <?php echo esc_html(ucfirst($gift->quality)); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    if (!isset($gift->quantity) || $gift->quantity === null || $gift->quantity === '') {
                                        _e('Unlimited', 'mastery-box');
                                    } else {
                                        echo esc_html($gift->quantity);
                                    }
                                    ?>
                                </td>
                                <td><?php echo esc_html($gift->win_percentage); ?>%</td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($gift->created_at))); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=mastery-box-gifts&edit=' . $gift->id)); ?>" 
                                       class="button button-small"><?php _e('Edit', 'mastery-box'); ?></a>
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=mastery-box-gifts&action=delete&id=' . $gift->id), 'delete_gift_' . $gift->id)); ?>" 
                                       class="button button-small button-link-delete" 
                                       onclick="return confirm('<?php _e('Are you sure you want to delete this gift?', 'mastery-box'); ?>')">
                                        <?php _e('Delete', 'mastery-box'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><?php _e('No gifts found. Add your first gift above.', 'mastery-box'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
