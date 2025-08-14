<?php
/**
 * The public-facing functionality of the plugin.
 */
class Mastery_Box_Public {

    private $plugin_name;
    private $version;

    // UAE Emirates
    private $emirates = array(
        'Abu Dhabi', 'Dubai', 'Sharjah', 'Ajman',
        'Umm Al Quwain', 'Ras Al Khaimah', 'Fujairah'
    );

    // All countries list
    private $countries = array(
        'Afghanistan', 'Albania', 'Algeria', 'Andorra', 'Angola', 'Antigua and Barbuda', 'Argentina', 'Armenia', 'Australia', 'Austria', 
        'Azerbaijan', 'Bahamas', 'Bahrain', 'Bangladesh', 'Barbados', 'Belarus', 'Belgium', 'Belize', 'Benin', 'Bhutan', 'Bolivia', 'Bosnia and Herzegovina',
        'Botswana', 'Brazil', 'Brunei', 'Bulgaria', 'Burkina Faso', 'Burundi', 'Cabo Verde', 'Cambodia', 'Cameroon', 'Canada', 'Central African Republic',
        'Chad', 'Chile', 'China', 'Colombia', 'Comoros', 'Congo', 'Costa Rica', 'Croatia', 'Cuba', 'Cyprus', 'Czechia', 'Denmark', 'Djibouti', 'Dominica',
        'Dominican Republic', 'Ecuador', 'Egypt', 'El Salvador', 'Equatorial Guinea', 'Eritrea', 'Estonia', 'Eswatini', 'Ethiopia', 'Fiji', 'Finland', 'France',
        'Gabon', 'Gambia', 'Georgia', 'Germany', 'Ghana', 'Greece', 'Grenada', 'Guatemala', 'Guinea', 'Guinea-Bissau', 'Guyana', 'Haiti', 'Honduras', 'Hungary',
        'Iceland', 'India', 'Indonesia', 'Iran', 'Iraq', 'Ireland', 'Israel', 'Italy', 'Jamaica', 'Japan', 'Jordan', 'Kazakhstan', 'Kenya', 'Kiribati',
        'Kuwait', 'Kyrgyzstan', 'Laos', 'Latvia', 'Lebanon', 'Lesotho', 'Liberia', 'Libya', 'Liechtenstein', 'Lithuania', 'Luxembourg', 'Madagascar',
        'Malawi', 'Malaysia', 'Maldives', 'Mali', 'Malta', 'Marshall Islands', 'Mauritania', 'Mauritius', 'Mexico', 'Micronesia', 'Moldova', 'Monaco',
        'Mongolia', 'Montenegro', 'Morocco', 'Mozambique', 'Myanmar', 'Namibia', 'Nauru', 'Nepal', 'Netherlands', 'New Zealand', 'Nicaragua', 'Niger',
        'Nigeria', 'North Korea', 'North Macedonia', 'Norway', 'Oman', 'Pakistan', 'Palau', 'Palestine', 'Panama', 'Papua New Guinea', 'Paraguay', 'Peru',
        'Philippines', 'Poland', 'Portugal', 'Qatar', 'Romania', 'Russia', 'Rwanda', 'Saint Kitts and Nevis', 'Saint Lucia', 'Saint Vincent and the Grenadines',
        'Samoa', 'San Marino', 'Sao Tome and Principe', 'Saudi Arabia', 'Senegal', 'Serbia', 'Seychelles', 'Sierra Leone', 'Singapore', 'Slovakia',
        'Slovenia', 'Solomon Islands', 'Somalia', 'South Africa', 'South Korea', 'South Sudan', 'Spain', 'Sri Lanka', 'Sudan', 'Suriname', 'Sweden',
        'Switzerland', 'Syria', 'Taiwan', 'Tajikistan', 'Tanzania', 'Thailand', 'Timor-Leste', 'Togo', 'Tonga', 'Trinidad and Tobago', 'Tunisia', 'Turkey',
        'Turkmenistan', 'Tuvalu', 'Uganda', 'Ukraine', 'United Arab Emirates', 'United Kingdom', 'United States', 'Uruguay', 'Uzbekistan', 'Vanuatu',
        'Vatican City', 'Venezuela', 'Vietnam', 'Yemen', 'Zambia', 'Zimbabwe'
    );

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/mastery-box-public.css', array(), $this->version, 'all');
    }

    public function enqueue_scripts() {
        $form_page_url = site_url('/enter-contest/');
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/mastery-box-public.js', array('jquery'), $this->version, false);
        wp_localize_script($this->plugin_name, 'mastery_box_ajax', array(
            'ajax_url'        => admin_url('admin-ajax.php'),
            'nonce'           => wp_create_nonce('mastery_box_nonce'),
            'number_of_boxes' => get_option('mastery_box_number_of_boxes', 3),
            'win_message'     => get_option('mastery_box_win_message', __('Congratulations! You won!', 'mastery-box')),
            'lose_message'    => get_option('mastery_box_lose_message', __('Better luck next time!', 'mastery-box')),
            'form_page_url'   => $form_page_url
        ));
    }

    public function init_shortcodes() {
        add_shortcode('masterybox_form', array($this, 'display_form_shortcode'));
        add_shortcode('masterybox_game', array($this, 'display_game_shortcode'));
        add_shortcode('masterybox_result', array($this, 'display_result_shortcode'));
    }

    public function display_form_shortcode($atts) {
        $atts = shortcode_atts(array('redirect_url' => ''), $atts);
        ob_start();
        $this->display_form($atts);
        return ob_get_clean();
    }

    public function display_game_shortcode($atts) {
        $atts = shortcode_atts(array('boxes' => get_option('mastery_box_number_of_boxes', 3)), $atts);
        ob_start();
        $this->display_game($atts);
        return ob_get_clean();
    }

    private function display_form($atts) {
        $form_fields = get_option('mastery_box_form_fields',
            "fullname|Full Name|text|required\n" .
            "emailaddress|Email Address|email|required\n" .
            "mobilenumber|Mobile Number|tel|required\n" .
            "emirates|Emirates|select|required\n" .
            "nationality|Nationality|select|required\n" .
            "store|Store|text|required\n" .
            "receipt_file|File Upload|file|required\n" .
            "termsandconditions|Terms & Conditions|checkbox|required"
        );

        $redirect_url = $atts['redirect_url'] ? $atts['redirect_url'] : get_permalink();
        echo '<div id="mastery-box-form-container">';
        echo '<form id="mastery-box-form" method="post" enctype="multipart/form-data">';
        echo wp_nonce_field('mastery_box_form_action', 'mastery_box_nonce', true, false);
        echo '<input type="hidden" name="redirect_url" value="' . esc_url($redirect_url) . '">';

        if ($form_fields) {
            $fields = explode("\n", $form_fields);
            foreach ($fields as $field) {
                if (trim($field)) {
                    $field_parts   = explode('|', trim($field));
                    if (count($field_parts) >= 3) {
                        $field_name     = $field_parts[0];
                        $field_label    = $field_parts[1];
                        $field_type     = $field_parts[2];
                        $field_required = isset($field_parts[3]) && $field_parts[3] === 'required';

                        echo '<div class="mastery-box-field">';

                        if ($field_name === 'termsandconditions' && $field_type === 'checkbox') {
                            $custom_label = get_option('mastery_box_terms_label', $field_label);
                            echo '<input type="checkbox" name="' . esc_attr($field_name) . '" id="' . esc_attr($field_name) . '" ' . ($field_required ? 'required' : '') . '>';
                            echo '<label for="' . esc_attr($field_name) . '">' . wp_kses_post($custom_label);
                            if ($field_required) {
                                echo ' <span class="required">*</span>';
                            }
                            echo '</label>';
                        }
                        elseif ($field_type === 'select' && $field_name === 'emirates') {
                            echo '<label for="' . esc_attr($field_name) . '">' . esc_html($field_label);
                            if ($field_required) echo ' <span class="required">*</span>';
                            echo '</label>';
                            echo '<select name="' . esc_attr($field_name) . '" id="' . esc_attr($field_name) . '" ' . ($field_required ? 'required' : '') . '>';
                            echo '<option value="">' . esc_html__('Select Emirates', 'mastery-box') . '</option>';
                            foreach ($this->emirates as $em) {
                                echo '<option value="' . esc_attr($em) . '">' . esc_html($em) . '</option>';
                            }
                            echo '</select>';
                        }
                        elseif ($field_type === 'select' && $field_name === 'nationality') {
                            echo '<label for="' . esc_attr($field_name) . '">' . esc_html($field_label);
                            if ($field_required) echo ' <span class="required">*</span>';
                            echo '</label>';
                            echo '<select name="' . esc_attr($field_name) . '" id="' . esc_attr($field_name) . '" ' . ($field_required ? 'required' : '') . '>';
                            echo '<option value="">' . esc_html__('Select Nationality', 'mastery-box') . '</option>';
                            foreach ($this->countries as $cn) {
                                echo '<option value="' . esc_attr($cn) . '">' . esc_html($cn) . '</option>';
                            }
                            echo '</select>';
                        }
                        elseif ($field_type === 'file') {
                            echo '<label for="' . esc_attr($field_name) . '">' . esc_html($field_label);
                            if ($field_required) echo ' <span class="required">*</span>';
                            echo '</label>';
                            echo '<input type="file" name="' . esc_attr($field_name) . '" id="' . esc_attr($field_name) . '" ' . ($field_required ? 'required' : '') . '>';
                        }
                        else {
                            echo '<label for="' . esc_attr($field_name) . '">' . esc_html($field_label);
                            if ($field_required) echo ' <span class="required">*</span>';
                            echo '</label>';
                            echo '<input type="' . esc_attr($field_type) . '" name="' . esc_attr($field_name) . '" id="' . esc_attr($field_name) . '" placeholder="' . esc_attr($field_label) . '" ' . ($field_required ? 'required' : '') . '>';
                        }

                        echo '</div>';
                    }
                }
            }
        }

        echo '<div class="mastery-box-field"><button type="submit" id="mastery-box-submit">' . __('Submit', 'mastery-box') . '</button></div>';
        echo '</form>';
        echo '<div id="mastery-box-message"></div>';
        echo '</div>';
    }

    private function display_game($atts) {
        $num_boxes = max(1, min(10, intval($atts['boxes'])));
        if ($num_boxes < 1) $num_boxes = 3;
        if ($num_boxes > 10) $num_boxes = 10;

        // Get box images
        $default_box_image = get_option('mastery_box_default_box_image', '');
        $box_images = get_option('mastery_box_box_images', array());
        if (!is_array($box_images)) {
            $box_images = array();
        }

        echo '<div id="mastery-box-game-container">';
        echo '<div id="mastery-box-boxes" class="boxes-container">';

        for ($i = 1; $i <= $num_boxes; $i++) {
            $img_url = isset($box_images[$i]) ? $box_images[$i] : $default_box_image;
            
            echo '<div class="mastery-box" data-box="' . $i . '">';
            echo '<div class="box-inner">';
            echo '<div class="box-front">';
            
            if (!empty($img_url)) {
                echo '<div class="box-art"><img src="' . esc_url($img_url) . '" alt="' . esc_attr(sprintf(__('Box %d', 'mastery-box'), $i)) . '" /></div>';
            } else {
                echo '<div class="box-number">' . $i . '</div>';
            }
            
            echo '</div>';
            echo '<div class="box-back"><div class="box-content"></div></div>';
            echo '</div></div>';
        }

        echo '</div>';
        echo '<div id="mastery-box-result" style="display: none;">';
        echo '<div id="result-content"></div>';
        echo '<button id="play-again-btn" style="display: none;">' . __('Play Again', 'mastery-box') . '</button>';
        echo '</div></div>';
    }

    public function display_result_shortcode() {
        if (!session_id()) { session_start(); }
        $result = isset($_SESSION['mastery_box_last_result']) ? $_SESSION['mastery_box_last_result'] : null;

        ob_start();
        echo '<div class="mastery-box-result-page">';
        if ($result) {
            if (!empty($result['is_winner'])) {
                echo '<h2>' . __('🎉 Congratulations! You won!', 'mastery-box') . '</h2>';
                echo '<p>' . esc_html($result['message']) . '</p>';
                if (!empty($result['gift_name'])) {
                    echo '<p><strong>' . __('Prize:', 'mastery-box') . '</strong> ' . esc_html($result['gift_name']) . '</p>';
                }
            } else {
                echo '<h2>' . __('Better luck next time!', 'mastery-box') . '</h2>';
                echo '<p>' . esc_html($result['message']) . '</p>';
            }
        } else {
            echo '<p>' . __('No game result found. Please play the game first.', 'mastery-box') . '</p>';
        }
        echo '</div>';
        return ob_get_clean();
    }

    public function handle_form_submission() {
        if (!wp_verify_nonce($_POST['nonce'], 'mastery_box_nonce')) {
            wp_send_json_error(__('Security check failed', 'mastery-box'));
        }
        $user_data   = array();
        $form_fields = get_option('mastery_box_form_fields');

        if ($form_fields) {
            $fields = explode("\n", $form_fields);
            foreach ($fields as $field) {
                if (trim($field)) {
                    $field_parts   = explode('|', trim($field));
                    if (count($field_parts) >= 3) {
                        $field_name     = $field_parts[0];
                        $field_label    = $field_parts[1];
                        $field_type     = $field_parts[2];
                        $field_required = isset($field_parts[3]) && $field_parts[3] === 'required';

                        if ($field_type === 'file') {
                            if (isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] === 0) {
                                require_once(ABSPATH . 'wp-admin/includes/file.php');
                                $uploaded = wp_handle_upload($_FILES[$field_name], array('test_form' => false));
                                if (isset($uploaded['url'])) {
                                    $user_data[$field_name] = esc_url_raw($uploaded['url']);
                                } else {
                                    wp_send_json_error(sprintf(__('Failed to upload file for %s.', 'mastery-box'), $field_label));
                                }
                            } elseif ($field_required) {
                                wp_send_json_error(sprintf(__('File upload for %s is required.', 'mastery-box'), $field_label));
                            }
                        } else {
                            if (isset($_POST[$field_name])) {
                                $user_data[$field_name] = sanitize_text_field($_POST[$field_name]);
                            } elseif ($field_required) {
                                wp_send_json_error(sprintf(__('Field %s is required', 'mastery-box'), $field_label));
                            }
                        }
                    }
                }
            }
        }

        if (!session_id()) { session_start(); }
        $_SESSION['mastery_box_user_data'] = $user_data;
        $_SESSION['mastery_box_played']    = false;

        wp_send_json_success(array(
            'message'  => __('Form submitted successfully!', 'mastery-box'),
            'redirect' => isset($_POST['redirect_url']) ? $_POST['redirect_url'] : ''
        ));
    }

    public function handle_game_play() {
        if (!wp_verify_nonce($_POST['nonce'], 'mastery_box_nonce')) {
            wp_send_json_error(__('Security check failed', 'mastery-box'));
        }
        if (!session_id()) { session_start(); }
        if (!empty($_SESSION['mastery_box_played'])) {
            wp_send_json_error(__('You have already played! Please fill the form again to play.', 'mastery-box'));
        }

        $user_data  = $_SESSION['mastery_box_user_data'] ?? array();
        $chosen_box = intval($_POST['box']);
        $winning_gift = Mastery_Box_Database::determine_winner();
        $is_winner    = !is_null($winning_gift);

        $entry_data = array(
            'user_data'  => json_encode($user_data),
            'gift_won'   => $is_winner ? $winning_gift->id : null,
            'is_winner'  => $is_winner ? 1 : 0,
            'chosen_box' => $chosen_box,
            'ip_address' => $this->get_client_ip(),
            'user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT'])
        );
        Mastery_Box_Database::insert_entry($entry_data);

        $_SESSION['mastery_box_played'] = true;

        $response = $is_winner
            ? array(
                'is_winner'    => true,
                'message'      => $winning_gift->description,
                'gift_name'    => $winning_gift->name,
                'gift_quality' => $winning_gift->quality
            )
            : array(
                'is_winner' => false,
                'message'   => get_option('mastery_box_lose_message', __('Better luck next time!', 'mastery-box'))
            );

        // Store the result in session for the results page
        if (!session_id()) { session_start(); }
        $_SESSION['mastery_box_last_result'] = $response;

        wp_send_json_success($response);
    }

    private function get_client_ip() {
        $ip_keys = array('HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR');
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        return $ip;
                    }
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
