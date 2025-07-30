jQuery(document).ready(function($) {
    function startTimer(seconds, timerElement, resendButton) {
        let timeLeft = seconds;
        resendButton.prop('disabled', true);

        const interval = setInterval(function() {
            const minutes = Math.floor(timeLeft / 60);
            const remainingSeconds = timeLeft % 60;
            timerElement.text(`(${minutes}:${remainingSeconds < 10 ? '0' : ''}${remainingSeconds} صبر کنید)`);
            timeLeft--;

            if (timeLeft < 0) {
                clearInterval(interval);
                timerElement.text('');
                resendButton.prop('disabled', false);
            }
        }, 1000);
    }

    $('#custom-register-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: pluginValues.ajax_url,
            data: {
                action: 'custom_register',
                nonce: pluginValues.register_nonce,
                reg_name: $('#reg_name').val(),
                reg_email: $('#reg_email').val(),
                reg_mobile: $('#reg_mobile').val(),
                reg_password: $('#reg_password').val(),
                reg_password_confirm: $('#reg_password_confirm').val()
            },
            success: function(response) {
                if (response.success) {
                    $('#register-message').html('<p class="success">' + response.data.message + '</p>');
                    $('#custom-register-form').hide();
                    $('#otp-form').show();
                    $('#otp_mobile').val(response.data.mobile);
                    $('#otp_user_id').val(response.data.user_id);
                    const resendButton = $('#resend-otp');
                    resendButton.data('mobile', response.data.mobile);
                    resendButton.data('user-id', response.data.user_id);
                    startTimer(response.data.wait_time, $('#resend-timer'), resendButton);
                } else {
                    $('#register-message').html('<p class="error">' + response.data + '</p>');
                }
            },
            error: function() {
                $('#register-message').html('<p class="error">خطایی رخ داد. لطفاً دوباره تلاش کنید.</p>');
            }
        });
    });

    $('#custom-login-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: pluginValues.ajax_url,
            data: {
                action: 'custom_login',
                nonce: pluginValues.login_nonce,
                login_credential: $('#login_credential').val(),
                login_password: $('#login_password').val()
            },
            success: function(response) {
                if (response.success) {
                    $('#login-message').html('<p class="success">' + response.data + '</p>');
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                } else {
                    if (response.data.show_otp_form) {
                        $('#login-message').html('<p class="error">' + response.data.message + '</p>');
                        $('#custom-login-form').hide();
                        $('#otp-form-login').show();
                        $('#otp_mobile_login').val(response.data.mobile);
                        $('#otp_user_id_login').val(response.data.user_id);
                        const resendButton = $('#resend-otp-login');
                        resendButton.data('mobile', response.data.mobile);
                        resendButton.data('user-id', response.data.user_id);
                        startTimer(response.data.wait_time, $('#resend-timer-login'), resendButton);
                    } else {
                        $('#login-message').html('<p class="error">' + response.data + '</p>');
                    }
                }
            },
            error: function() {
                $('#login-message').html('<p class="error">خطایی رخ داد. لطفاً دوباره تلاش کنید.</p>');
            }
        });
    });

    $('#forgot-password-link').on('click', function(e) {
        e.preventDefault();
        $('#custom-login-form').hide();
        $('#forgot-password-form').show();
    });

    $('#custom-forgot-password-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: pluginValues.ajax_url,
            data: {
                action: 'custom_forgot_password',
                nonce: pluginValues.forgot_password_nonce,
                forgot_credential: $('#forgot_credential').val()
            },
            success: function(response) {
                if (response.success) {
                    $('#forgot-password-message').html('<p class="success">' + response.data.message + '</p>');
                    $('#custom-forgot-password-form').hide();
                    $('#otp-form-forgot').show();
                    $('#otp_mobile_forgot').val(response.data.mobile);
                    $('#otp_user_id_forgot').val(response.data.user_id);
                    const resendButton = $('#resend-otp-forgot');
                    resendButton.data('mobile', response.data.mobile);
                    resendButton.data('user-id', response.data.user_id);
                    startTimer(response.data.wait_time, $('#resend-timer-forgot'), resendButton);
                } else {
                    $('#forgot-password-message').html('<p class="error">' + response.data + '</p>');
                }
            },
            error: function() {
                $('#forgot-password-message').html('<p class="error">خطایی رخ داد. لطفاً دوباره تلاش کنید.</p>');
            }
        });
    });

    $('#custom-verify-otp-form, #custom-verify-otp-login-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const isLoginForm = form.attr('id') === 'custom-verify-otp-login-form';
        const otpMessageElement = isLoginForm ? $('#otp-message-login') : $('#otp-message');

        $.ajax({
            type: 'POST',
            url: pluginValues.ajax_url,
            data: {
                action: 'custom_verify_otp',
                nonce: pluginValues.verify_otp_nonce,
                otp_mobile: form.find('[name="otp_mobile"]').val(),
                otp_code: form.find('[name="otp_code"]').val(),
                user_id: form.find('[name="otp_user_id"]').val(),
                context: isLoginForm ? 'login' : 'register'
            },
            success: function(response) {
                if (response.success) {
                    otpMessageElement.html('<p class="success">' + response.data.message + '</p>');
                    setTimeout(function() {
                        window.location.href = response.data.redirect;
                    }, 2000);
                } else {
                    otpMessageElement.html('<p class="error">' + response.data + '</p>');
                }
            },
            error: function() {
                otpMessageElement.html('<p class="error">خطایی رخ داد. لطفاً دوباره تلاش کنید.</p>');
            }
        });
    });

    $('#custom-verify-otp-forgot-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: pluginValues.ajax_url,
            data: {
                action: 'custom_verify_otp',
                nonce: pluginValues.verify_otp_nonce,
                otp_mobile: $('#otp_mobile_forgot').val(),
                otp_code: $('#otp_code_forgot').val(),
                user_id: $('#otp_user_id_forgot').val(),
                context: 'forgot_password'
            },
            success: function(response) {
                if (response.success) {
                    $('#otp-message-forgot').html('<p class="success">' + response.data.message + '</p>');
                    if (response.data.show_reset_form) {
                        $('#otp-form-forgot').hide();
                        $('#reset-password-form').show();
                        $('#reset_user_id').val($('#otp_user_id_forgot').val());
                    }
                } else {
                    $('#otp-message-forgot').html('<p class="error">' + response.data + '</p>');
                }
            },
            error: function() {
                $('#otp-message-forgot').html('<p class="error">خطایی رخ داد. لطفاً دوباره تلاش کنید.</p>');
            }
        });
    });

    $('#custom-reset-password-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: pluginValues.ajax_url,
            data: {
                action: 'custom_reset_password',
                nonce: pluginValues.reset_password_nonce,
                user_id: $('#reset_user_id').val(),
                new_password: $('#new_password').val(),
                new_password_confirm: $('#new_password_confirm').val()
            },
            success: function(response) {
                if (response.success) {
                    $('#reset-password-message').html('<p class="success">' + response.data + '</p>');
                    setTimeout(function() {
                        $('#reset-password-form').hide();
                        $('#custom-login-form').show();
                        $('#forgot-password-form').hide();
                    }, 2000);
                } else {
                    $('#reset-password-message').html('<p class="error">' + response.data + '</p>');
                }
            },
            error: function() {
                $('#reset-password-message').html('<p class="error">خطایی رخ داد. لطفاً دوباره تلاش کنید.</p>');
            }
        });
    });

    $('#resend-otp, #resend-otp-login, #resend-otp-forgot').on('click', function() {
        const button = $(this);
        $.ajax({
            type: 'POST',
            url: pluginValues.ajax_url,
            data: {
                action: 'custom_resend_otp',
                nonce: pluginValues.resend_otp_nonce,
                mobile: button.data('mobile'),
                user_id: button.data('user-id')
            },
            success: function(data) {
                if (data.success) {
                    $('#otp-message, #otp-message-forgot, #login-message').html('<p class="success">' + data.data.message + '</p>');
                    startTimer(data.data.wait_time, button.closest('form').find('[id^=resend-timer]'));
                } else {
                    $('#otp-message, #otp-message-forgot, #login-message').html('<p class="error">' + data.data + '</p>');
                }
            },
            error: function() {
                $('#otp-message, #otp-message-forgot, #login-message').html('<p class="error">خطایی رخ داد. لطفاً دوباره تلاش کنید.</p>');
            }
        });
    });
});