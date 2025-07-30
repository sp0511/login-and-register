<?php
/**
 * Plugin Name: Custom Login Register
 * Description: A custom login and registration plugin with OTP verification.
 * Version: 1.0.0
 * Author: soheil pourahmadi
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

define('plugin_url', plugin_dir_url(__FILE__));
define('plugin_path', plugin_dir_path( __FILE__ ));

register_activation_hook(__FILE__, function() {
    require_once plugin_path . 'includes/class-otp-handler.php';
    (new Custom_Login_Register_OTP_Handler())->create_otp_table();
});

// Include main class
require_once plugin_path . 'includes/class-custom-login-register.php';
require_once plugin_path . 'includes/class-ajax-handlers.php';
require_once plugin_path . 'includes/class-otp-handler.php';

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script('custom-login-register', plugin_url . '/assets/js/custom-login-register.js', array(), get_script_version('/assets/js/custom-login-register.js'), true);
    wp_localize_script(
    'custom-login-register',
    'pluginValues',
array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'register_nonce' => wp_create_nonce( 'custom_register_action' ),
        'login_nonce' => wp_create_nonce( 'custom_login_action' ),
        'forgot_password_nonce' => wp_create_nonce( 'custom_forgot_password_action' ),
        'verify_otp_nonce' => wp_create_nonce( 'custom_verify_otp_action' ),
        'resend_otp_nonce' => wp_create_nonce( 'custom_resend_otp_action' ),
        )
    );
});

// Initialize the plugin
function custom_login_register_init() {
    $plugin = new Custom_Login_Register();
    $ajax_handlers = new Custom_Login_Register_Ajax_Handlers();
    $otp_handler = new Custom_Login_Register_OTP_Handler();
}
add_action( 'plugins_loaded', 'custom_login_register_init' );

function send_sms($to, $argsStr) {
    $url = 'https://sms.simorgh.dev/send-sms/send-sms'; 

    $body = array(
        'patternId'   => '351509',
        'to'          => $to,
        'argsStr'     => $argsStr,
        'patternName' => 'login',
        'systemName'  => 'blockpost',
    );

    $response = wp_remote_post($url, array(
        'method'      => 'POST',
        'timeout'     => 30,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking'    => true,
        'headers'     => array('Content-Type' => 'application/json'),
        'body'        => json_encode($body),
        'cookies'     => array(),
    ));

    if (is_wp_error($response)) {
        error_log($response->get_error_message());
        return false;
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $response_body = json_decode(wp_remote_retrieve_body($response), true);

    if ($status_code == 200 && isset($response_body['result']['success']) && $response_body['result']['success']) {
        return $response_body;
    } else {
        error_log(print_r($response_body, true));
        return false;
    }
}