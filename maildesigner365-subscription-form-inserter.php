<?php
/*
Plugin Name: Mail Designer 365 Subscription Form Inserter
Plugin URI: https://www.maildesigner365.com
Description: Insert Mail Designer 365 subscription forms into your posts and pages.
Version: 1.0.7
Date: 2024-10-15
Author: Alex Brummer
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: maildesigner365-subscription-form-inserter
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

define("MAILDESIGNER365_SUBSCRIPTION_FORM_INSERTER_VERSION", "1.0");
define("MAILHQ_BASE_URL", "https://my.maildesigner365.com");

// add admin menu (settings)
add_action('admin_menu', 'maildesigner365_subscription_form_menu');
function maildesigner365_subscription_form_menu() {
    add_options_page('Mail Designer 365 Subscription Form Inserter', 'Mail Designer 365 Subscription Form Inserter', 'manage_options', 'maildesigner365-subscription-form', 'maildesigner365_subscription_form_options');
}

// create admin UI
function maildesigner365_subscription_form_options() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page!'));
    }

    echo '<div class="wrap">
        <h2>Mail Designer 365 Subscription Form Inserter</h2>
        <p>Insert a Mail Designer 365 subscription form into your posts and pages.</p>
        <br>
        <hr>';

    // lookup API key and API secret from database
    $api_key = get_option('maildesigner365-subscription-form-api-key');
    $api_secret = get_option('maildesigner365-subscription-form-api-secret');

    $save_button_title = "Set API Credentials &amp; Fetch Forms";
    if ($api_key != "" && $api_secret != "") {
        $save_button_title = "Update API Credentials &amp; Refresh Forms";
    }

    // section to enter/update API key and API secret
    echo '<div class="wrap">
        <h2>API credentials</h2>
        <p>Enter your Mail Designer 365 API key and API secret below to get started.</p>
        <p>You can find them in the "Settings" tab of your <a href="https://my.maildesigner365.com/teams/preferences" target="_blank">Mail Designer 365 team</a>.</p>';

    // form to enter/update API key and API secret
    echo '<table class="form-table">
        <tr>
            <th scope="row">
                <label for="maildesigner365-subscription-form-api-key">API key</label>
            </th>
            <td>
                <input type="password" autocomplete="one-time-code" name="maildesigner365-subscription-form-api-key" id="maildesigner365-subscription-form-api-key" value="' . esc_attr($api_key) . '" class="regular-text">&nbsp;<a id="maildesigner365-subscription-form-api-key-reveal" class="dashicons dashicons-visibility" href="#">&nbsp;</a><a id="maildesigner365-subscription-form-api-key-hide" class="dashicons dashicons-hidden" href="#" style="display: none;">&nbsp;</a>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="maildesigner365-subscription-form-api-secret">API secret</label>
            </th>
            <td>
                <input type="password" autocomplete="one-time-code" name="maildesigner365-subscription-form-api-secret" id="maildesigner365-subscription-form-api-secret" value="' . esc_attr($api_secret) . '" class="regular-text">&nbsp;<a id="maildesigner365-subscription-form-api-secret-reveal" class="dashicons dashicons-visibility" href="#">&nbsp;</a><a id="maildesigner365-subscription-form-api-secret-hide" class="dashicons dashicons-hidden" href="#" style="display: none;">&nbsp;</a>
            </td>
        </tr>
    </table>';
    echo '<input type="hidden" name="_wpnonce" id="_wpnonce" value="' . esc_attr(wp_create_nonce('maildesigner365-subscription-form-nonce')) . '">';
    echo '<button id="maildesigner365-subscription-form-save-api">' . esc_html($save_button_title) . '</button>';

    // hidden loading indicator - shown while saving
    echo '<div id="maildesigner365-subscription-form-loading">
        <div id="maildesigner365-subscription-form-loading-spinner"></div>
    </div>';

    // inner wrapper end
    echo ' </div>';

    // if API key and API secret are not set, only show form to enter them but do not show form selection
    if (!$api_key || !$api_secret) {
        // outer wrapper end
        echo '</div>';
        return;
    }

    // spacing
    echo '<br><br><hr>';

    // get all forms (either from cache or from API)
    $forms = maildesigner365_subscription_form_get_forms();

    // handle error cases
    if (!is_array($forms)) {
        // something went wrong fetching the forms
        echo '<div class="wrap">
                <h2>Could not retrieve any forms from Mail Designer 365 Delivery Center</h2>
                <p>Please check your API key and API secret in the settings above.</p>
            </div>
        </div>';
        return;
    }
    if (count($forms) < 1) {
        // fetch was ok, but no forms are available
        echo '<div class="wrap">
            <h2>No forms available</h2>
            <p>Please first create a form in the <a href="' . esc_url(MAILHQ_BASE_URL . '/teams/deliveries/signupForms') . '" target="_blank">Mail Designer 365 Delivery Center</a>.</p>
            </div>
        </div>';
        return;
    }

    // preselect first form
    if (isset($forms[0]['id'])) {
        $form = $forms[0]['id'];
    }

    // show dropdown with forms
    echo '<div class="wrap">
        <h2>Select one of the available forms to generate the shortcode for it</h2>
        <p>You can manage your forms in the <a href="' . esc_url(MAILHQ_BASE_URL . '/teams/deliveries/signupForms') . '" target="_blank">Mail Designer 365 Delivery Center</a>.</p>
        <p>Copy the generated shortcode and paste it into a post or page where you want to display the signup form.</p>';

    echo '<select id="maildesigner365-subscription-form-form">';
    foreach ($forms as $form_item) {
        $selected = '';
        if ($form_item['id'] == $form) {
            $selected = 'selected';
        }
        echo '<option value="' . esc_attr($form_item['id']) . '" ' . esc_attr($selected) . '>' . esc_html($form_item['name']) . '</option>';
    }

    echo '</select> ';
    echo '<input type="text" id="maildesigner365-subscription-form-shortcode" value="[maildesigner365-subscription-form id=&quot;' . esc_html($form) . '&quot;]" style="width: 600px; max-width: 98%; text-align: center;" readonly>';
    echo ' <a href="#" id="maildesigner365-subscription-form-copy-shortcode" class="dashicons dashicons-clipboard">&nbsp;</a>';
    echo ' <span id="maildesigner365-subscription-form-copy-shortcode-success" style="display: none;" class="dashicons dashicons-yes">&nbsp;</span>';

    // outer wrapper end
    echo '</div>';
}

// functinality to save api key and api secret
add_action('wp_ajax_maildesigner365_subscription_form_save_api_credentials', 'maildesigner365_subscription_form_save_api_credentials_ajax');
function maildesigner365_subscription_form_save_api_credentials_ajax() {
    $wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field($_POST['_wpnonce']) : '';
    if (!wp_verify_nonce($wpnonce, 'maildesigner365-subscription-form-nonce')) {
        wp_die('Security check failed!');
    }
    $api_key = isset($_POST['api_key']) ? sanitize_text_field($_POST['api_key']) : '';
    $api_secret = isset($_POST['api_secret']) ? sanitize_text_field($_POST['api_secret']) : '';
    // update credentials
    update_option('maildesigner365-subscription-form-api-key', $api_key);
    update_option('maildesigner365-subscription-form-api-secret', $api_secret);
    // update forms

    $forms = maildesigner365_subscription_form_get_forms(true);
    wp_die();
}

// function to get the forms from database cache or API
function maildesigner365_subscription_form_get_forms($refresh = false) {

    // try to read the forms from the settings if already fetched and not in refresh mode
    $forms_stored = get_option('maildesigner365-subscription-form-forms');
    if ($forms_stored && !$refresh) {
        $forms = json_decode($forms_stored, true);
        return $forms;
    }

    // otherwise fetch new content from MAILHQ API
    update_option('maildesigner365-subscription-form-forms', '');

    $api_key = get_option('maildesigner365-subscription-form-api-key');
    $api_secret = get_option('maildesigner365-subscription-form-api-secret');
    $url = MAILHQ_BASE_URL . '/integration/api/forms/signup';

    // get user language to be sent as Accept-Language header to the API
    $accept_language = sanitize_text_field($_SERVER['HTTP_ACCEPT_LANGUAGE']);
    if (trim($accept_language) == "") {
        // fallback based on user locale
        $user_language = substr(get_locale(), 0, 2);
        $accept_language = strtolower($user_language) . "-" . strtoupper(str_replace("en", "us", $user_language));
    }

    // build headers
    $headers = "Authorization: Basic " . base64_encode(trim($api_key) . ":" . trim($api_secret)) . "\r\n" .
        "Accept: application/json\r\n" .
        "Accept-Language: " . $accept_language . "\r\n" .
        "Content-Type: application/json\r\n" .
        "Content-Length: 0\r\n" .
        "X-Origin-Application: Wordpress\r\n" .
        "Connection: close\r\n\r\n";

    // call API using the best method available or die with error message
    $response = wp_remote_get($url, array(
        'headers' => $headers,
    ));
    if (is_wp_error($response)) {
        return false;
    }

    // parse response
    $response = wp_remote_retrieve_body($response);

    // if response is not valid JSON, return false
    if (!$forms = json_decode($response, true)) {
        return false;
    }

    // if response contains an error, return false
    if (isset($forms['error'])) {
        return false;
    }

    // store latest API response in database
    update_option('maildesigner365-subscription-form-forms', wp_json_encode($forms));

    // return forms
    return $forms;
}


// register settings
add_action('admin_init', 'maildesigner365_subscription_form_settings');
function maildesigner365_subscription_form_settings() {
    register_setting('maildesigner365-subscription-form', 'maildesigner365-subscription-form-forms');
    register_setting('maildesigner365-subscription-form', 'maildesigner365-subscription-form-form');
    register_setting('maildesigner365-subscription-form', 'maildesigner365-subscription-form-api-key');
    register_setting('maildesigner365-subscription-form', 'maildesigner365-subscription-form-api-secret');
}

// register shortcode
add_shortcode('maildesigner365-subscription-form', 'maildesigner365_subscription_form_shortcode');

// shortcode function
function maildesigner365_subscription_form_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => '',
    ), $atts, 'maildesigner365-subscription-form');
    $id = $atts['id'];
    $forms = maildesigner365_subscription_form_get_forms();
    if ($id != "" && $forms && is_array($forms)) {
        for ($i = 0; $i < count($forms); $i++) {
            if ($forms[$i]['id'] == $id) {
                return maildesigner365_subscription_form_html($forms[$i]);
            }
        }
    }
    return '';
}

// html code for the subscription form (includes CSS and JS from MailHQ CDN - user/form customized at MailHQ)
function maildesigner365_subscription_form_html($form = array()) {
    $script_url = $form['script_url'];
    $style_url = $form['style_url'];

    // add inline script to load the form and additional functionality
    $native_js = 'function loadScript(e,n){var t=document.createElement("script");t.type="text/javascript",t.src=e,t.onerror=function(){"function"==typeof n&&n(new Error("Failed to load script: "+e))},t.onload=function(){"function"==typeof n&&injectFormHTML()},document.head.appendChild(t)}loadScript("' . esc_url(MAILHQ_BASE_URL . $script_url) . '",(function(e){if(e){console.error(e);var n=0===(navigator.language||navigator.userLanguage).indexOf("de")?"[ Formular konnte nicht geladen werden - bitte versuchen Sie es später erneut ]":"[ Unable to load signup form - please try again later ]";document.getElementById("md365-signup-container")&&(document.getElementById("md365-signup-container").innerHTML="<p><code>"+n+"</code></p>")}}));';
    wp_register_script('maildesigner365-subscription-form-loader', false);
    wp_enqueue_script('maildesigner365-subscription-form-loader');
    wp_add_inline_script('maildesigner365-subscription-form-loader', $native_js);

    // enqueue CSS file to style the form
    wp_enqueue_style('maildesigner365-subscription-form', esc_url(MAILHQ_BASE_URL . $style_url));

    // create the form container
    $html = '<div id="md365-signup-container"></div>';
    return $html;
}

// javascript needed for the admin settings page
function maildesigner365_subscription_form_admin_js() {
    $output = '<script type="text/javascript">';

    // select the shortcode when the user clicks on it
    $output .= '
        jQuery("#maildesigner365-subscription-form-shortcode").click(function() {
            jQuery("#maildesigner365-subscription-form-shortcode").select();
        });';

    // copy the shortcode to the clipboard when user clicks the copy link
    $output .= '
        jQuery("#maildesigner365-subscription-form-copy-shortcode").click(function() {
        jQuery("#maildesigner365-subscription-form-shortcode").select();
        jQuery("#maildesigner365-subscription-form-copy-shortcode").hide();
        jQuery("#maildesigner365-subscription-form-copy-shortcode-success").show();
        document.execCommand("copy");
        setTimeout(function() {
            jQuery("#maildesigner365-subscription-form-copy-shortcode-success").hide();
            jQuery("#maildesigner365-subscription-form-copy-shortcode").show();
            }, 5000);
        });';

    // update content of selector when form dropdown changes
    $output .= '
        jQuery("#maildesigner365-subscription-form-form").change(function() {
            var form = jQuery("#maildesigner365-subscription-form-form").val();
            jQuery("#maildesigner365-subscription-form-shortcode").val("[maildesigner365-subscription-form id=\"" + form + "\"]");
        });';


    // handle click on API key/secret reveal and hide buttons
    $output .= '
        jQuery("#maildesigner365-subscription-form-api-key-reveal").click(function() {
            jQuery("#maildesigner365-subscription-form-api-key").attr("type", "text");
            jQuery("#maildesigner365-subscription-form-api-key-reveal").hide();
            jQuery("#maildesigner365-subscription-form-api-key-hide").show();
        });

        jQuery("#maildesigner365-subscription-form-api-secret-reveal").click(function() {
            jQuery("#maildesigner365-subscription-form-api-secret").attr("type", "text");
            jQuery("#maildesigner365-subscription-form-api-secret-reveal").hide();
            jQuery("#maildesigner365-subscription-form-api-secret-hide").show();
        });

        jQuery("#maildesigner365-subscription-form-api-key-hide").click(function() {
            jQuery("#maildesigner365-subscription-form-api-key").attr("type", "password");
            jQuery("#maildesigner365-subscription-form-api-key-reveal").show();
            jQuery("#maildesigner365-subscription-form-api-key-hide").hide();
        });

        jQuery("#maildesigner365-subscription-form-api-secret-hide").click(function() {
            jQuery("#maildesigner365-subscription-form-api-secret").attr("type", "password");
            jQuery("#maildesigner365-subscription-form-api-secret-reveal").show();
            jQuery("#maildesigner365-subscription-form-api-secret-hide").hide();
        });';

    // handle click on api credentials save button
    $output .= '
        jQuery("#maildesigner365-subscription-form-save-api").click(function() {
            jQuery("#maildesigner365-subscription-form-loading").show();
            jQuery("#maildesigner365-subscription-form-save-api").attr("disabled", "disabled");
            var api_key = jQuery("#maildesigner365-subscription-form-api-key").val();
            var api_secret = jQuery("#maildesigner365-subscription-form-api-secret").val();
            var _wpnonce = jQuery("#_wpnonce").val();
            jQuery.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    action: "maildesigner365_subscription_form_save_api_credentials",
                    api_key: api_key,
                    api_secret: api_secret,
                    _wpnonce: _wpnonce
                },
                success: function(response) {
                    window.location.reload();
                }
            });
        });';

    $output .= '</script>';

    echo wp_kses($output, array(
        'script' => array(
            'type' => array()
        )
    ));
}

// css needed for the admin settings page
function maildesigner365_subscription_form_admin_css() {
    $output = '<style type="text/css">
        #maildesigner365-subscription-form-api-key-reveal,
        #maildesigner365-subscription-form-api-secret-reveal,
        #maildesigner365-subscription-form-api-key-hide,
        #maildesigner365-subscription-form-api-secret-hide,
        #maildesigner365-subscription-form-copy-shortcode,
        #maildesigner365-subscription-form-copy-shortcode-success {
            margin-top: 0.5%;
            color: #666;
            text-decoration: none;
        }

        #maildesigner365-subscription-form-loading {
            display: none;
            z-index: 9999;
            position: absolute;
            top: 0;
            left: -1%;
            width: 102%;
            height: 100%;
            padding: 40px;
            background-color: rgba(0,0,0,0.1);
        }

        #maildesigner365-subscription-form-loading-spinner {
            position: absolute;
            top: 25%;
            left: 50%;
            width: 50px;
            height: 50px;
            margin-top: -25px;
            margin-left: -25px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 2s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
    </style>';

    echo wp_kses($output, array(
        'style' => array(
            'type' => array()
        )
    ));
}

// initialize
add_action('admin_print_styles', 'maildesigner365_subscription_form_admin_css');
add_action('admin_print_footer_scripts', 'maildesigner365_subscription_form_admin_js');

// add settings link on plugin page
function maildesigner365_subscription_form_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=maildesigner365-subscription-form">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}
$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'maildesigner365_subscription_form_settings_link');
