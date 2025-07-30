<?php
/**
 * Plugin Name: Custom Login Register
 * Description: A custom login and registration plugin with OTP verification.
 * Version: 1.0.0
 * Author: soheil pourahmadi
 */

// if ( ! defined( 'ABSPATH' ) ) {
//     exit; // Exit if accessed directly
// }

// define('plugin_url', plugin_dir_url(__FILE__));

// // Include main class
// require_once plugin_dir_path( __FILE__ ) . 'includes/class-custom-login-register.php';

// add_action('wp_enqueue_scripts', function () {
//     wp_enqueue_script('custom-login-register', plugin_url . '/assets/js/custom-login-register.js', array(), get_script_version('/assets/js/custom-login-register.js'), true);
//     wp_localize_script(
//     'custom-login-register',
//     'pluginValues',
// array(
//         'ajax_url' => admin_url('admin-ajax.php'),
//         'register_nonce' => wp_create_nonce( 'custom_register_action' ),
//         )
//     );
// });

// // Initialize the plugin
// function custom_login_register_init() {
//     $plugin = new Custom_Login_Register();
// }
// add_action( 'plugins_loaded', 'custom_login_register_init' );



if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

define('plugin_url', plugin_dir_url(__FILE__));
define('plugin_path', plugin_dir_path( __FILE__ ));

// Include main class
require_once plugin_path . 'includes/class-custom-login-register.php';
require_once plugin_path . 'includes/class-ajax-handlers.php';
require_once plugin_path . 'includes/class-otp-handler.php';
require_once plugin_path . 'includes/class-settings.php';

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
        )
    );
});

// Initialize the plugin
function custom_login_register_init() {
    $plugin = new Custom_Login_Register();
    $ajax_handlers = new Custom_Login_Register_Ajax_Handlers();
    $otp_handler = new Custom_Login_Register_OTP_Handler();
    $settings = new Custom_Login_Register_Settings();
}
add_action( 'plugins_loaded', 'custom_login_register_init' );