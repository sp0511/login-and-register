<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Custom_Login_Register_OTP_Handler {

    public function create_otp_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'custom_otp_codes';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            mobile varchar(11) NOT NULL,
            otp_code varchar(6) NOT NULL,
            created_at datetime NOT NULL,
            expires_at datetime NOT NULL,
            attempt_count int DEFAULT 0,
            last_attempt_at datetime,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    public function generate_otp($user_id, $mobile) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'custom_otp_codes';

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT attempt_count, last_attempt_at FROM $table_name WHERE mobile = %s ORDER BY created_at DESC",
                $mobile
            )
        );

        $max_attempts = 3;
        $lockout_duration = HOUR_IN_SECONDS;
        $wait_times = [MINUTE_IN_SECONDS, 2 * MINUTE_IN_SECONDS, 5 * MINUTE_IN_SECONDS];

        if ( $row ) {
            if ( $row->attempt_count >= $max_attempts ) {
                $last_attempt_time = strtotime( $row->last_attempt_at );
                if ( ( time() - $last_attempt_time ) < $lockout_duration ) {
                    wp_send_json_error( 'تعداد تلاش‌ها به حداکثر رسیده است. لطفاً یک ساعت دیگر امتحان کنید.' );
                }
            }
            $wpdb->delete( $table_name, [ 'mobile' => $mobile ], [ '%s' ] );
        }

        $otp_code = sprintf('%06d', mt_rand(100000, 999999));
        $created_at = current_time('mysql');
        $expires_at = date('Y-m-d H:i:s', strtotime($created_at . ' +5 minutes'));
        $attempt_count = $row ? $row->attempt_count + 1 : 1;

        $wpdb->insert(
            $table_name,
            [
                'user_id' => $user_id,
                'mobile' => $mobile,
                'otp_code' => $otp_code,
                'created_at' => $created_at,
                'expires_at' => $expires_at,
                'attempt_count' => $attempt_count,
                'last_attempt_at' => $created_at,
            ],
            ['%d', '%s', '%s', '%s', '%s', '%d', '%s']
        );

        error_log("OTP Code for $mobile: $otp_code");

        return [
            'otp_code' => $otp_code,
            'wait_time' => $wait_times[min($attempt_count - 1, count($wait_times) - 1)],
        ];
    }

    public function verify_otp($mobile, $otp_code, $user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'custom_otp_codes';

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE mobile = %s AND otp_code = %s AND user_id = %d AND expires_at > %s",
                $mobile,
                $otp_code,
                current_time('mysql')
            )
        );

        if ($row) {
            $user = get_user_by('ID', $row->user_id);
            if ($user) {
                update_user_meta($user->ID, 'is_verified', true);
                $wpdb->delete( $table_name, [ 'mobile' => $mobile ], [ '%s' ] );
                return $user;
            }
        }

        return false;
    }
}