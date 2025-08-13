/**
 * Admin JavaScript for the Mastery Box plugin
 */

(function($) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     */

    $(document).ready(function() {

        // Initialize admin functionality
        initializeAdminFeatures();

    });

    /**
     * Initialize admin features
     */
    function initializeAdminFeatures() {

        // Add confirmation dialogs for delete actions
        $('.button-link-delete').on('click', function(e) {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
                return false;
            }
        });

        // Auto-dismiss notices after 5 seconds
        setTimeout(function() {
            $('.notice.is-dismissible').fadeOut();
        }, 5000);

        // Form field preview for settings page
        if ($('#form_fields').length) {
            initializeFormFieldPreview();
        }

        // Win percentage validation
        if ($('#win_percentage').length) {
            initializeWinPercentageValidation();
        }
    }

    /**
     * Initialize form field preview
     */
    function initializeFormFieldPreview() {
        var $textarea = $('#form_fields');
        var $preview = $('<div id="form-preview" style="margin-top: 15px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd;"><h4>Preview:</h4><div id="preview-content"></div></div>');

        $textarea.after($preview);

        function updatePreview() {
            var fields = $textarea.val().split('\n');
            var previewHtml = '';

            fields.forEach(function(field) {
                field = field.trim();
                if (field) {
                    var parts = field.split('|');
                    if (parts.length >= 3) {
                        var name = parts[0];
                        var label = parts[1];
                        var type = parts[2];
                        var required = parts[3] === 'required';

                        previewHtml += '<div style="margin-bottom: 10px;">';
                        previewHtml += '<label><strong>' + label + '</strong>';
                        if (required) previewHtml += ' <span style="color: red;">*</span>';
                        previewHtml += '</label><br>';
                        previewHtml += '<input type="' + type + '" placeholder="' + label + '" style="width: 100%; max-width: 300px;" disabled>';
                        previewHtml += '</div>';
                    }
                }
            });

            $('#preview-content').html(previewHtml || '<em>No valid fields defined.</em>');
        }

        $textarea.on('input', updatePreview);
        updatePreview(); // Initial preview
    }

    /**
     * Initialize win percentage validation
     */
    function initializeWinPercentageValidation() {
        $('#win_percentage').on('input', function() {
            var value = parseFloat($(this).val());
            var $warning = $('#percentage-warning');

            // Remove existing warning
            $warning.remove();

            if (value > 50) {
                $(this).after('<p id="percentage-warning" style="color: #d63638; font-size: 0.9em; margin-top: 5px;">⚠️ High win percentage may result in too many winners.</p>');
            }
        });
    }

})(jQuery);
