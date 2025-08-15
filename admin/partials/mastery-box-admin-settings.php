<?php
if ( ! defined( 'WPINC' ) ) { die; }

$form_fields = get_option(
    'mastery_box_form_fields',
    "fullname|Full Name|text|required\n" .
    "emailaddress|Email Address|email|required\n" .
    "mobilenumber|Mobile Number|tel|required\n" .
    "emirates|Emirates|select|required\n" .
    "nationality|Nationality|select|required\n" .
    "store|Store|text|required\n" .
    "receipt_file|File Upload|file|required\n" .
    "termsandconditions|Terms & Conditions|checkbox|required"
);
$number_of_boxes = get_option( 'mastery_box_number_of_boxes', 3 );
$win_message     = get_option( 'mastery_box_win_message', __( 'Congratulations! You won!', 'mastery-box' ) );
$lose_message    = get_option( 'mastery_box_lose_message', __( 'Better luck next time!', 'mastery-box' ) );

$terms_label     = get_option( 'mastery_box_terms_label', 'By clicking "Submit" I agree to the competition\'s <a href="">Terms and Conditions</a>' );

// NEW: Box images options
$default_box_image = get_option( 'mastery_box_default_box_image', '' );
$box_images = get_option( 'mastery_box_box_images', array() );
if ( ! is_array( $box_images ) ) {
    $box_images = array();
}
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <?php if ( isset( $_GET['message'] ) && $_GET['message'] === 'updated' ): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e( 'Settings updated successfully!', 'mastery-box' ); ?></p>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <?php wp_nonce_field( 'mastery_box_settings_action', 'mastery_box_nonce' ); ?>
        <input type="hidden" name="action" value="masterybox_save_settings" />

        <div class="mastery-box-settings-sections">

            <div class="mastery-box-settings-section">
                <h2><?php _e( 'Form Configuration', 'mastery-box' ); ?></h2>
                <table class="form-table"><tbody>
                    <tr>
                        <th><label for="form_fields"><?php _e( 'Form Fields', 'mastery-box' ); ?></label></th>
                        <td>
                            <textarea name="form_fields" id="form_fields" rows="8" class="large-text code"><?php echo esc_textarea( $form_fields ); ?></textarea>
                            <p class="description"><?php _e( 'Each field on a new line. Format: field_name|Label|type|required', 'mastery-box' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="terms_label"><?php _e( 'Terms & Conditions Label', 'mastery-box' ); ?></label></th>
                        <td>
                            <textarea name="terms_label" id="terms_label" rows="3" class="large-text"><?php echo esc_textarea( $terms_label ); ?></textarea>
                            <p class="description"><?php _e( 'You can include HTML. Example: By clicking "Submit" I agree to the competition\'s <a href="/terms">Terms and Conditions</a>', 'mastery-box' ); ?></p>
                        </td>
                    </tr>
                </tbody></table>
            </div>

            <div class="mastery-box-settings-section">
                <h2><?php _e( 'Game Configuration', 'mastery-box' ); ?></h2>
                <table class="form-table"><tbody>
                    <tr>
                        <th><label for="number_of_boxes"><?php _e( 'Number of Boxes', 'mastery-box' ); ?></label></th>
                        <td>
                            <input type="number" name="number_of_boxes" id="number_of_boxes" min="3" max="10" value="<?php echo esc_attr( $number_of_boxes ); ?>" />
                            <p class="description"><?php _e( 'How many boxes to display in the game (3–10).', 'mastery-box' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="win_message"><?php _e( 'Default Win Message', 'mastery-box' ); ?></label></th>
                        <td><textarea name="win_message" id="win_message" rows="3" cols="50"><?php echo esc_textarea( $win_message ); ?></textarea></td>
                    </tr>
                    <tr>
                        <th><label for="lose_message"><?php _e( 'Lose Message', 'mastery-box' ); ?></label></th>
                        <td><textarea name="lose_message" id="lose_message" rows="3" cols="50"><?php echo esc_textarea( $lose_message ); ?></textarea></td>
                    </tr>
					
					
					
							
					
                </tbody></table>
            </div>

            <!-- NEW: Box Images Section -->
            <div class="mastery-box-settings-section">
                <h2><?php _e( 'Box Images', 'mastery-box' ); ?></h2>
                <table class="form-table"><tbody>
                    <tr>
                        <th scope="row">
                            <label for="default_box_image"><?php _e( 'Default Box Image', 'mastery-box' ); ?></label>
                        </th>
                        <td>
                            <div style="margin-bottom:8px;">
                                <input type="text" name="default_box_image" id="default_box_image" class="regular-text" value="<?php echo esc_attr( $default_box_image ); ?>" placeholder="<?php esc_attr_e( 'Image URL', 'mastery-box' ); ?>" />
                                <button type="button" class="button mastery-box-upload" data-target="default_box_image"><?php _e( 'Upload/Select', 'mastery-box' ); ?></button>
                                <?php if ( ! empty( $default_box_image ) ): ?>
                                    <div style="margin-top:8px;">
                                        <img src="<?php echo esc_url( $default_box_image ); ?>" alt="" style="max-width:150px;height:auto;border:1px solid #ddd;padding:2px;background:#fff;" />
                                    </div>
                                <?php endif; ?>
                            </div>
                            <p class="description"><?php _e( 'Used for all boxes if a specific image is not set for a box.', 'mastery-box' ); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label><?php _e( 'Per-Box Images', 'mastery-box' ); ?></label>
                        </th>
                        <td>
                            <p class="description" style="margin-bottom:10px;"><?php _e( 'Set images for individual boxes. If left empty, the Default Box Image will be used for that box.', 'mastery-box' ); ?></p>
                            <?php for ( $i = 1; $i <= max( 1, intval( $number_of_boxes ) ); $i++ ): 
                                $url = isset( $box_images[$i] ) ? $box_images[$i] : '';
                            ?>
                                <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                                    <label style="min-width:70px;"><?php echo sprintf( __( 'Box %d', 'mastery-box' ), $i ); ?></label>
                                    <input type="text" name="box_images[<?php echo $i; ?>]" id="box_image_<?php echo $i; ?>" class="regular-text" value="<?php echo esc_attr( $url ); ?>" placeholder="<?php esc_attr_e( 'Image URL', 'mastery-box' ); ?>" />
                                    <button type="button" class="button mastery-box-upload" data-target="box_image_<?php echo $i; ?>"><?php _e( 'Upload/Select', 'mastery-box' ); ?></button>
                                    <?php if ( ! empty( $url ) ): ?>
                                        <img src="<?php echo esc_url( $url ); ?>" alt="" style="max-width:60px;height:auto;border:1px solid #ddd;padding:2px;background:#fff;" />
                                    <?php endif; ?>
                                </div>
                            <?php endfor; ?>
                            <p class="description"><?php _e( 'This list reflects the current "Number of Boxes" setting.', 'mastery-box' ); ?></p>
                        </td>
                    </tr>
                </tbody></table>
            </div>

        </div>

        <?php submit_button( __( 'Save Settings', 'mastery-box' ) ); ?>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    function openMedia(targetInputId) {
        var frame = wp.media({
            title: 'Select or Upload Image',
            button: { text: 'Use this image' },
            library: { type: 'image' },
            multiple: false
        });
        
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            var $input = $('#' + targetInputId);
            var $button = $input.next('.mastery-box-upload');
            
            // Set the image URL
            $input.val(attachment.url);
            
            // Add/update preview image
            var $existingPreview = $button.next('img');
            if ($existingPreview.length) {
                // Update existing preview
                $existingPreview.attr('src', attachment.url);
            } else {
                // Add new preview
                $button.after('<img src="' + attachment.url + '" alt="" style="max-width:60px;height:auto;border:1px solid #ddd;padding:2px;background:#fff;margin-left:8px;" />');
            }
        });
        
        frame.open();
    }

    $(document).on('click', '.mastery-box-upload', function(e) {
        e.preventDefault();
        var target = $(this).data('target');
        if (target) {
            openMedia(target);
        }
    });
});
</script>

