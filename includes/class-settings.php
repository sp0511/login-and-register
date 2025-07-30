<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Custom_Login_Register_Settings {
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    public function add_settings_page() {
        add_options_page(
            'تنظیمات افزونه ورود و ثبت‌نام',
            'Custom Login Register',
            'manage_options',
            'custom-login-register-settings',
            [ $this, 'render_settings_page' ]
        );
    }

    public function register_settings() {
        register_setting('custom_login_register_settings', 'clr_melipayamak_api_key', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('custom_login_register_settings', 'clr_melipayamak_sender_number', ['sanitize_callback' => 'sanitize_text_field']);

        add_settings_section('clr_melipayamak_section', 'تنظیمات ملی‌پیامک', null, 'custom-login-register-settings');

        add_settings_field(
            'clr_melipayamak_api_key',
            'کلید API ملی‌پیامک',
            [$this, 'render_api_key_field'],
            'custom-login-register-settings',
            'clr_melipayamak_section'
        );

        add_settings_field(
            'clr_melipayamak_sender_number',
            'شماره خط ارسال‌کننده',
            [$this, 'render_sender_number_field'],
            'custom-login-register-settings',
            'clr_melipayamak_section'
        );
    }

    public function render_api_key_field() {
        $api_key = get_option('clr_melipayamak_api_key', '');
        ?>
        <input type="text" name="clr_melipayamak_api_key" value="<?php echo esc_attr($api_key); ?>" size="50">
        <?php
    }

    public function render_sender_number_field() {
        $sender_number = get_option('clr_melipayamak_sender_number', '');
        ?>
        <input type="text" name="clr_melipayamak_sender_number" value="<?php echo esc_attr($sender_number); ?>" size="50">
        <p class="description">شماره خطی که با آن پیامک ارسال می‌شود.</p>
        <?php
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>تنظیمات افزونه ورود و ثبت‌نام</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'custom_login_register_settings' );
                do_settings_sections( 'custom-login-register-settings' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}