<?php
/*
Plugin Name: PopUp for Google Analytics
Description: This plugin creates popup where your users can set "accept" for loading Google Analytics or "Reject" which doesnt load Google Analytics.
Version: 1.0
Author: Finland93
*/

// Enqueue styles and scripts
add_action('wp_enqueue_scripts', 'custom_gdpr_popup_enqueue_styles_and_scripts');
function custom_gdpr_popup_enqueue_styles_and_scripts() {
    // Enqueue CSS
    wp_enqueue_style('custom-gdpr-popup-style', plugins_url('css/custom-gdpr-popup.css', __FILE__));

    // Enqueue JS
    wp_enqueue_script('custom-gdpr-popup', plugins_url('js/custom-gdpr-popup.js', __FILE__), array('jquery'), null, true);

    // Localize script to pass data to JS
    wp_localize_script('custom-gdpr-popup', 'customGdprPopup', array(
        'analyticsScript' => get_option('google_analytics_code', ''),
    ));
}

// Add admin menu page
add_action('admin_menu', 'custom_gdpr_popup_menu');
function custom_gdpr_popup_menu() {
    add_menu_page(
        'GDPR Popup Settings',
        'GDPR Popup',
        'manage_options',
        'custom-gdpr-popup',
        'custom_gdpr_popup_settings_page'
    );
}

// Callback function for settings page
function custom_gdpr_popup_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['gdpr_settings_nonce']) && wp_verify_nonce($_POST['gdpr_settings_nonce'], 'gdpr_settings')) {
        // Save settings here
        update_option('gdpr_popup_text', sanitize_text_field($_POST['gdpr_popup_text']));
        update_option('gdpr_accept_text', sanitize_text_field($_POST['gdpr_accept_text']));
        update_option('gdpr_reject_text', sanitize_text_field($_POST['gdpr_reject_text']));
        update_option('google_analytics_code', sanitize_text_field($_POST['google_analytics_code']));
        echo '<div class="notice notice-success"><p>Settings saved successfully.</p></div>';
    }

    $gdpr_popup_text = get_option('gdpr_popup_text', 'Sivustomme käyttää evästeitä');
    $gdpr_accept_text = get_option('gdpr_accept_text', 'Hyväksy');
    $gdpr_reject_text = get_option('gdpr_reject_text', 'Hylkää');
    $google_analytics_code = get_option('google_analytics_code', '');

    ?>
    <div class="wrap">
        <h2>GDPR Popup Settings</h2>
        <form method="post" action="">
            <?php wp_nonce_field('gdpr_settings', 'gdpr_settings_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">Popup Text</th>
                    <td><input type="text" name="gdpr_popup_text" value="<?php echo esc_attr($gdpr_popup_text); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row">Accept Button Text</th>
                    <td><input type="text" name="gdpr_accept_text" value="<?php echo esc_attr($gdpr_accept_text); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row">Reject Button Text</th>
                    <td><input type="text" name="gdpr_reject_text" value="<?php echo esc_attr($gdpr_reject_text); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row">Google Analytics Code</th>
                    <td><input type="text" name="google_analytics_code" value="<?php echo esc_attr($google_analytics_code); ?>" class="regular-text"></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Frontend popup
add_action('wp_footer', 'custom_gdpr_popup_frontend_popup');
function custom_gdpr_popup_frontend_popup() {
    echo '<div id="gdpr-popup">';
    echo '<div>';
    echo '<p>' . esc_html(get_option('gdpr_popup_text', 'Our website uses cookies')) . '</p>';
    echo '<button id="accept-btn">' . esc_html(get_option('gdpr_accept_text', 'Accept')) . '</button>';
    echo '<button id="reject-btn">' . esc_html(get_option('gdpr_reject_text', 'Reject')) . '</button>';
    echo '</div>';
    echo '</div>';
}

// Add uninstallation hook
register_uninstall_hook(__FILE__, 'custom_gdpr_popup_uninstall');

// Uninstallation logic
function custom_gdpr_popup_uninstall() {
    // Delete plugin options
    delete_option('gdpr_popup_text');
    delete_option('gdpr_accept_text');
    delete_option('gdpr_reject_text');
    delete_option('google_analytics_code');
}
?>