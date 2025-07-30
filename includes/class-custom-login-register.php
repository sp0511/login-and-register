<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Custom_Login_Register {
    public function __construct() {
        add_action( 'init', [ $this, 'register_shortcodes' ] );
    }

    public function register_shortcodes() {
        add_shortcode( 'custom_register', [ $this, 'render_register_form' ] );
        add_shortcode( 'custom_login', [ $this, 'render_login_form' ] );
    }

    public function render_register_form() {
        if (is_user_logged_in()) {
            return '<p>شما قبلاً وارد شده‌اید!</p>';
        }

        ob_start();
        ?>
        <div class="custom-register-form">
            <h2>ثبت‌نام</h2>
            <div id="register-message"></div>
            <form method="post" id="custom-register-form">
                <p>
                    <label for="reg_name">نام:</label>
                    <input type="text" id="reg_name" name="reg_name" required>
                </p>
                <p>
                    <label for="reg_email">ایمیل:</label>
                    <input type="email" id="reg_email" name="reg_email" required>
                </p>
                <p>
                    <label for="reg_mobile">شماره موبایل:</label>
                    <input type="tel" id="reg_mobile" name="reg_mobile" pattern="[0-9]{11}" required>
                </p>
                <p>
                    <label for="reg_password">رمز عبور:</label>
                    <input type="password" id="reg_password" name="reg_password" required>
                </p>
                <p>
                    <label for="reg_password_confirm">تأیید رمز عبور:</label>
                    <input type="password" id="reg_password_confirm" name="reg_password_confirm" required>
                </p>
                <p>
                    <input type="submit" value="ثبت‌نام">
                </p>
            </form>
            <div id="otp-form" style="display:none;">
                <h2>تأیید کد OTP</h2>
                <div id="otp-message"></div>
                <form method="post" id="custom-verify-otp-form">
                    <p>
                        <label for="otp_code">کد تأیید:</label>
                        <input type="text" id="otp_code" name="otp_code" required>
                        <input type="hidden" id="otp_mobile" name="otp_mobile">
                        <input type="hidden" id="otp_user_id" name="otp_user_id">
                    </p>
                    <p>
                        <input type="submit" value="تأیید کد">
                        <button type="button" id="resend-otp" data-mobile="" data-user-id="" disabled>ارسال مجدد <span id="resend-timer"></span></button>
                    </p>
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function render_login_form() {
        if (is_user_logged_in()) {
            return '<p>شما قبلاً وارد شده‌اید!</p>';
        }

        ob_start();
        ?>
        <div class="custom-login-form">
            <h2>ورود</h2>
            <div id="login-message"></div>
            <form method="post" id="custom-login-form">
                <p>
                    <label for="login_credential">ایمیل یا شماره موبایل:</label>
                    <input type="text" id="login_credential" name="login_credential" required>
                </p>
                <p>
                    <label for="login_password">رمز عبور:</label>
                    <input type="password" id="login_password" name="login_password" required>
                </p>
                <p>
                    <input type="submit" value="ورود">
                    <a href="#" id="forgot-password-link">فراموشی رمز عبور؟</a>
                </p>
            </form>

            <div id="otp-form-login" style="display:none;">
                <h2>تأیید کد OTP</h2>
                <div id="otp-message-login"></div>
                <form method="post" id="custom-verify-otp-login-form">
                    <p>
                        <label for="otp_code_login">کد تأیید:</label>
                        <input type="text" id="otp_code_login" name="otp_code_login" required>
                        <input type="hidden" id="otp_mobile_login" name="otp_mobile_login">
                        <input type="hidden" id="otp_user_id_login" name="otp_user_id_login">
                    </p>
                    <p>
                        <input type="submit" value="تأیید کد">
                        <button type="button" id="resend-otp-login" data-mobile="" data-user-id="" disabled>ارسال مجدد <span id="resend-timer-login"></span></button>
                    </p>
                </form>
            </div>

            <div id="forgot-password-form" style="display:none;">
                <h2>بازنشانی رمز عبور</h2>
                <div id="forgot-password-message"></div>
                <form method="post" id="custom-forgot-password-form">
                    <p>
                        <label for="forgot_credential">شماره موبایل:</label>
                        <input type="text" id="forgot_credential" name="forgot_credential" required>
                    </p>
                    <p>
                        <input type="submit" value="ارسال کد OTP">
                    </p>
                </form>
                <div id="otp-form-forgot" style="display:none;">
                    <h2>تأیید کد OTP</h2>
                    <div id="otp-message-forgot"></div>
                    <form method="post" id="custom-verify-otp-forgot-form">
                        <p>
                            <label for="otp_code_forgot">کد تأیید:</label>
                            <input type="text" id="otp_code_forgot" name="otp_code_forgot" required>
                            <input type="hidden" id="otp_mobile_forgot" name="otp_mobile_forgot">
                            <input type="hidden" id="otp_user_id_forgot" name="otp_user_id_forgot">
                        </p>
                        <p>
                            <input type="submit" value="تأیید کد">
                            <button type="button" id="resend-otp-forgot" data-mobile="" data-user-id="" disabled>ارسال مجدد <span id="resend-timer-forgot"></span></button>
                        </p>
                    </form>
                </div>
                <div id="reset-password-form" style="display:none;">
                    <h2>تغییر رمز عبور</h2>
                    <div id="reset-password-message"></div>
                    <form method="post" id="custom-reset-password-form">
                        <p>
                            <label for="new_password">رمز عبور جدید:</label>
                            <input type="password" id="new_password" name="new_password" required>
                        </p>
                        <p>
                            <label for="new_password_confirm">تأیید رمز عبور جدید:</label>
                            <input type="password" id="new_password_confirm" name="new_password_confirm" required>
                        </p>
                        <p>
                            <input type="hidden" id="reset_user_id" name="reset_user_id">
                            <input type="submit" value="تغییر رمز عبور">
                        </p>
                    </form>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

}