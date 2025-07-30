<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

class Custom_Login_Register_Ajax_Handlers {
    public function __construct() {
        add_action( 'wp_ajax_custom_register', [ $this, 'ajax_register' ] );
        add_action( 'wp_ajax_nopriv_custom_register', [ $this, 'ajax_register' ] );
        add_action( 'wp_ajax_custom_login', [ $this, 'ajax_login' ] );
        add_action( 'wp_ajax_nopriv_custom_login', [ $this, 'ajax_login' ] );
        add_action( 'wp_ajax_custom_forgot_password', [ $this, 'ajax_forgot_password' ] );
        add_action( 'wp_ajax_nopriv_custom_forgot_password', [ $this, 'ajax_forgot_password' ] );
        add_action( 'wp_ajax_custom_verify_otp', [ $this, 'ajax_verify_otp' ] );
        add_action( 'wp_ajax_nopriv_custom_verify_otp', [ $this, 'ajax_verify_otp' ] );
        add_action( 'wp_ajax_custom_resend_otp', [ $this, 'ajax_resend_otp' ] );
        add_action( 'wp_ajax_nopriv_custom_resend_otp', [ $this, 'ajax_resend_otp' ] );
        add_action( 'wp_ajax_custom_reset_password', [ $this, 'ajax_reset_password' ] );
        add_action( 'wp_ajax_nopriv_custom_reset_password', [ $this, 'ajax_reset_password' ] );
    }

    public function ajax_register() {

        check_ajax_referer( 'custom_register_action', 'nonce' );
        $name = sanitize_text_field( $_POST['reg_name'] );
        $email = sanitize_email( $_POST['reg_email'] );
        $mobile = sanitize_text_field( $_POST['reg_mobile'] );
        $password = $_POST['reg_password'];
        $password_confirm = $_POST['reg_password_confirm'];

        if ( empty( $name ) || empty( $email ) || empty( $mobile ) || empty( $password ) || empty( $password_confirm ) ) {
            wp_send_json_error( 'لطفاً تمام فیلدها را پر کنید.' );
        }

        if ( ! is_email( $email ) ) {
            wp_send_json_error( 'ایمیل واردشده معتبر نیست.' );
        }

        if ( ! preg_match( '/^09[0-9]{9}$/', $mobile ) ) {
            wp_send_json_error( 'شماره موبایل باید ۱۱ رقم و با ۰۹ شروع شود.' );
        }

        if ( $password !== $password_confirm ) {
            wp_send_json_error( 'رمز عبور و تأیید آن مطابقت ندارند.' );
        }

        if ( strlen( $password ) < 6 ) {
            wp_send_json_error( 'رمز عبور باید حداقل ۶ کاراکتر باشد.' );
        }

        if ( email_exists( $email ) ) {
            wp_send_json_error( 'این ایمیل قبلاً ثبت شده است.' );
        }

        $users = get_users( [
            'meta_key' => 'mobile_number',
            'meta_value' => $mobile,
            'number' => 1,
        ] );
        if ( ! empty( $users ) ) {
            wp_send_json_error( 'این شماره موبایل قبلاً ثبت شده است.' );
        }

        $username = sanitize_user( current( explode( '@', $email ) ), true );
        $original_username = $username;
        $counter = 1;
        while ( username_exists( $username ) ) {
            $username = $original_username . $counter;
            $counter++;
        }

        $user_id = wp_create_user( $username, $password, $email );

        if ( is_wp_error( $user_id ) ) {
            wp_send_json_error( 'خطا در ایجاد کاربر: ' . $user_id->get_error_message() );
        }

        update_user_meta( $user_id, 'first_name', $name );
        update_user_meta( $user_id, 'mobile_number', $mobile );
        update_user_meta( $user_id, 'is_verified', false );

        $otp_handler = new Custom_Login_Register_OTP_Handler();
        $otp_data = $otp_handler->generate_otp( $user_id, $mobile );
        // $api_key = get_option( 'clr_melipayamak_api_key', '' );
        // $sender_number = get_option( 'clr_melipayamak_sender_number', '' );
        // if ( empty( $api_key ) || empty( $sender_number ) ) {
        //     wp_send_json_error( 'کلید API یا شماره خط ملی‌پیامک تنظیم نشده است.' );
        // }

        // $api_url = 'https://api.melipayamak.com/api/send/simple';
        // $data = [
        //     'api_key' => $api_key,
        //     'to' => $mobile,
        //     'from' => $sender_number,
        //     'text' => "کد تأیید شما: $otp_code",
        // ];

        // $response = wp_remote_post( $api_url, [
        //     'body' => json_encode( $data ),
        //     'headers' => [ 'Content-Type' => 'application/json' ],
        // ] );

        // if ( is_wp_error( $response ) ) {
        //     wp_send_json_error( 'خطا در ارسال پیامک: ' . $response->get_error_message() );
        // }

        // $body = wp_remote_retrieve_body( $response );
        // $result = json_decode( $body, true );

        // if ( ! isset( $result['status'] ) || $result['status'] !== 'success' ) {
        //     wp_send_json_error( 'خطا در ارسال پیامک: ' . ( $result['message'] ?? 'پاسخ نامعتبر از ملی‌پیامک' ) );
        // }

        wp_send_json_success( [
            'message' => 'ثبت‌نام انجام شد! کد تأیید به شماره موبایل شما ارسال شد.',
            'mobile' => $mobile,
            'user_id' => $user_id,
            'wait_time' => $otp_data['wait_time'],
        ] );
    }

    public function ajax_verify_otp() {
        check_ajax_referer( 'custom_verify_otp_action', 'nonce' );

        $mobile = sanitize_text_field( $_POST['otp_mobile'] );
        $otp_code = sanitize_text_field( $_POST['otp_code'] );
        $user_id = intval( $_POST['user_id'] );

        if ( empty( $mobile ) || empty( $otp_code ) || empty( $user_id ) ) {
            wp_send_json_error( 'لطفاً تمام فیلدها را پر کنید.' );
        }

        if ( ! preg_match( '/^09[0-9]{9}$/', $mobile ) ) {
            wp_send_json_error( 'شماره موبایل باید ۱۱ رقم و با ۰۹ شروع شود.' );
        }

        $otp_handler = new Custom_Login_Register_OTP_Handler();
        $user = $otp_handler->verify_otp( $mobile, $otp_code );

        if ( is_object( $user ) && isset( $user->ID ) && $user->ID == $user_id ) {
            wp_set_current_user( $user->ID );
            wp_set_auth_cookie( $user->ID, true );
            wp_send_json_success( 'تأیید و ورود با موفقیت انجام شد!' );
        } else {
            wp_send_json_error( 'کد تأیید نامعتبر است یا منقضی شده.' );
        }
    }

    public function ajax_resend_otp() {
        check_ajax_referer( 'custom_resend_otp_action', 'nonce' );

        $mobile = sanitize_text_field( $_POST['mobile'] );
        $user_id = intval( $_POST['user_id'] );

        if ( empty( $mobile ) || empty( $user_id ) ) {
            wp_send_json_error( 'اطلاعات ناقص است.' );
        }

        if ( ! preg_match( '/^09[0-9]{9}$/', $mobile ) ) {
            wp_send_json_error( 'شماره موبایل باید ۱۱ رقم و با ۰۹ شروع شود.' );
        }

        $user = get_user_by( 'ID', $user_id );
        if ( ! $user || get_user_meta( $user_id, 'mobile_number', true ) !== $mobile ) {
            wp_send_json_error( 'کاربر یا شماره موبایل یافت نشد.' );
        }

        $otp_handler = new Custom_Login_Register_OTP_Handler();
        $otp_data = $otp_handler->generate_otp( $user_id, $mobile );

        wp_send_json_success( [
            'message' => 'کد تأیید جدید به شماره موبایل شما ارسال شد.',
            'wait_time' => $otp_data['wait_time'],
        ] );
    }

    public function ajax_login() {
        check_ajax_referer( 'custom_login_action', 'nonce' );
        $credential = sanitize_text_field( $_POST['login_credential'] );
        $password = $_POST['login_password'];

        if ( empty( $credential ) || empty( $password ) ) {
            wp_send_json_error( 'لطفاً تمام فیلدها را پر کنید.' );
        }

        $user = null;
        if ( is_email( $credential ) ) {
            $user = get_user_by( 'email', $credential );
        } else {
            $users = get_users( [
                'meta_key' => 'mobile_number',
                'meta_value' => $credential,
                'number' => 1,
            ] );
            if ( ! empty( $users ) ) {
                $user = $users[0];
            }
        }

        if ( ! $user ) {
            wp_send_json_error( 'ایمیل یا شماره موبایل یافت نشد.' );
        }

        $creds = [
            'user_login' => $user->user_login,
            'user_password' => $password,
            'remember' => true,
        ];

        $login_result = wp_signon( $creds, false );

        if ( is_wp_error( $login_result ) ) {
            wp_send_json_error( 'رمز عبور اشتباه است.' );
        }

        wp_set_current_user( $user->ID );
        wp_set_auth_cookie( $user->ID, true );

        wp_send_json_success( 'ورود با موفقیت انجام شد!' );
    }

    public function ajax_forgot_password() {
        check_ajax_referer( 'custom_forgot_password_action', 'nonce' );

        $credential = sanitize_text_field( $_POST['forgot_credential'] );

        if ( empty( $credential ) ) {
            wp_send_json_error( 'لطفاً شماره موبایل را وارد کنید.' );
        }

            $users = get_users( [
                'meta_key' => 'mobile_number',
                'meta_value' => $credential,
                'number' => 1,
            ] );
            if ( ! empty( $users ) ) {
                $user = $users[0];
            }

        if ( ! $user ) {
            wp_send_json_error( ' شماره موبایل یافت نشد.' );
        }

        $otp_handler = new Custom_Login_Register_OTP_Handler();
        $otp_data = $otp_handler->generate_otp( $user->ID, get_user_meta( $user->ID, 'mobile_number', true ) );

        wp_send_json_success( [
            'message' => 'کد تأیید به شماره موبایل شما ارسال شد.',
            'mobile' => get_user_meta( $user->ID, 'mobile_number', true ),
            'user_id' => $user->ID,
            'wait_time' => $otp_data['wait_time'],
        ] );
    }
}