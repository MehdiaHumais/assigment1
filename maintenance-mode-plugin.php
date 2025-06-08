<?php
/*
Plugin Name: Maintenance Mode Plugin
Description: Displays a maintenance message for non-logged-in users while allowing logged-in users to browse normally.
Version: 1.0
Author: Mehdia Humais
*/

if (!defined('ABSPATH')) exit; 


function mh_maintenance_mode() {
    if (is_user_logged_in()) return; 

    if (is_admin() || defined('DOING_AJAX') && DOING_AJAX || strpos($_SERVER['REQUEST_URI'], '/wp-json/') === 0) return;

    $enabled = get_option('mh_maintenance_mode_enabled');
    if ($enabled !== '1') return;

    $message = get_option('mh_maintenance_mode_message', 'This site is currently under maintenance. Please check back later.');
    $message = wp_kses_post($message); 

  
    wp_die(
        "<div style='text-align:center; margin-top:100px; font-family:sans-serif;'>
            <h1>" . esc_html(get_bloginfo('name')) . "</h1>
            <p style='font-size:20px;'>$message</p>
        </div>",
        'Maintenance Mode',
        array('response' => 200)
    );
}
add_action('template_redirect', 'mh_maintenance_mode');


function mh_maintenance_mode_menu() {
    add_options_page('Maintenance Mode', 'Maintenance Mode', 'manage_options', 'mh-maintenance-mode', 'mh_maintenance_mode_settings_page');
}
add_action('admin_menu', 'mh_maintenance_mode_menu');


function mh_maintenance_mode_settings_page() {
    if (!current_user_can('manage_options')) return;

    if (isset($_POST['mh_maintenance_save'])) {
        check_admin_referer('mh_maintenance_save');

        $enabled = isset($_POST['mh_enabled']) ? '1' : '0';
        $message = sanitize_text_field($_POST['mh_message']);

        update_option('mh_maintenance_mode_enabled', $enabled);
        update_option('mh_maintenance_mode_message', $message);

        echo '<div class="updated"><p>Settings saved.</p></div>';
    }

    $enabled = get_option('mh_maintenance_mode_enabled', '0');
    $message = get_option('mh_maintenance_mode_message', 'This site is currently under maintenance. Please check back later.');
    ?>
    <div class="wrap">
        <h1>Maintenance Mode Settings</h1>
        <form method="post">
            <?php wp_nonce_field('mh_maintenance_save'); ?>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Enable Maintenance Mode</th>
                    <td><input type="checkbox" name="mh_enabled" value="1" <?php checked($enabled, '1'); ?> /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Custom Message</th>
                    <td><textarea name="mh_message" rows="4" cols="50"><?php echo esc_textarea($message); ?></textarea></td>
                </tr>
            </table>

            <p class="submit"><input type="submit" name="mh_maintenance_save" class="button-primary" value="Save Changes" /></p>
        </form>
    </div>
    <?php
}
