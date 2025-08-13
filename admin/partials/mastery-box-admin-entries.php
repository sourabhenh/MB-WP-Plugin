<?php
if (!defined('WPINC')) die;

// 1. Prepare column order and readable labels (from settings)
$field_defs = get_option('mastery_box_form_fields');
$field_columns = [];
$field_labels  = [];
$default_order = [
    'fullname','emailaddress','mobilenumber','emirates','nationality','store','receipt_file'
	
	//,'termsandconditions'

];
foreach ($default_order as $field) {
    $field_columns[] = $field;
}
if ($field_defs) {
    foreach (explode("\n", $field_defs) as $f) {
        $parts = explode('|', trim($f));
        if (count($parts) >= 2) {
            $field_labels[strtolower($parts[0])] = $parts[1];
        }
    }
}

?>
<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php if (isset($_GET['message']) && $_GET['message'] === 'deleted'): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Entry deleted successfully', 'mastery-box'); ?></p>
        </div>
    <?php endif; ?>

    <!-- Export to CSV -->
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom: 1em;">
        <?php wp_nonce_field('mastery_box_export_entries', 'mastery_box_export_nonce'); ?>
        <input type="hidden" name="action" value="mastery_box_export_entries">
        <button type="submit" class="button button-primary"><?php _e('Export All Entries to CSV', 'mastery-box'); ?></button>
    </form>

    <?php if (!empty($entries)): ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <?php foreach ($field_columns as $col_key): ?>
                        <th><?php echo isset($field_labels[$col_key]) ? esc_html($field_labels[$col_key]) : esc_html(ucfirst($col_key)); ?></th>
                    <?php endforeach; ?>
                    <th><?php _e('Box', 'mastery-box'); ?></th>
                    <th><?php _e('Result', 'mastery-box'); ?></th>
                    <th><?php _e('Gift Won', 'mastery-box'); ?></th>
                    <th><?php _e('Date', 'mastery-box'); ?></th>
                    <th><?php _e('IP Address', 'mastery-box'); ?></th>
                    <th><?php _e('Actions', 'mastery-box'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entries as $entry): ?>
                    <?php $user_data = json_decode($entry->user_data, true); ?>
                    <tr>
                        <?php foreach ($field_columns as $col_key): ?>
                            <td>
                                <?php
                                $rawval = isset($user_data[$col_key]) ? $user_data[$col_key] : '';
                                if ($col_key === 'receipt_file' && $rawval) {
                                    echo '<a href="' . esc_url($rawval) . '" target="_blank">' . __('Download', 'mastery-box') . '</a>';
                                } elseif ($col_key === 'termsandconditions') {
                                    echo $rawval ? __('Checked','mastery-box') : '';
                                } else {
                                    echo esc_html($rawval);
                                }
                                ?>
                            </td>
                        <?php endforeach; ?>
                        <td><?php echo esc_html($entry->chosen_box); ?></td>
                        <td><?php echo $entry->is_winner ? '<span style="color:green;">WIN</span>' : '<span style="color:red;">LOSE</span>'; ?></td>
                        <td><?php echo $entry->gift_name ? esc_html($entry->gift_name) : __('No prize', 'mastery-box'); ?></td>
                        <td><?php echo esc_html(date_i18n(get_option('date_format').' '.get_option('time_format'), strtotime($entry->created_at))); ?></td>
                        <td><?php echo esc_html($entry->ip_address); ?></td>
                        <td>
                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=mastery-box-entries&action=delete&id=' . $entry->id), 'delete_entry_' . $entry->id)); ?>" onclick="return confirm('<?php _e('Are you sure you want to delete this entry?', 'mastery-box'); ?>')" class="button button-small"><?php _e('Delete', 'mastery-box'); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p><?php _e('No entries yet.', 'mastery-box'); ?></p>
    <?php endif; ?>
</div>
