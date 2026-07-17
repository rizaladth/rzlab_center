/**
 * =============================================
 * RizalLab Command - Main JavaScript
 * =============================================
 * Handle semua proses AJAX: Login, Register, Reset Password
 * Menggunakan jQuery untuk AJAX requests.
 */

$(document).ready(function () {

    // =============================================
    // Utility Functions
    // =============================================

    /**
     * Menampilkan alert pada container tertentu
     * @param {string} containerId - ID container alert
     * @param {string} type - 'success' | 'danger' | 'warning'
     * @param {string} message - Pesan yang ditampilkan
     */
    function showAlert(containerId, type, message) {
        const $alert = $('#' + containerId);
        $alert.removeClass('d-none alert-success alert-danger alert-warning')
               .addClass('alert-' + type)
               .html('<i class="bi bi-' + (type === 'success' ? 'check-circle-fill' : type === 'danger' ? 'exclamation-triangle-fill' : 'info-circle-fill') + ' me-2"></i>' + message);

        // Scroll to alert
        $('html, body').animate({
            scrollTop: $alert.offset().top - 100
        }, 300);
    }

    /**
     * Toggle loading state pada tombol
     * @param {string} btnId - ID tombol
     * @param {boolean} loading - true untuk loading
     */
    function toggleButton(btnId, loading) {
        const $btn = $('#' + btnId);
        if (loading) {
            $btn.prop('disabled', true)
                .find('.btn-text').addClass('d-none').end()
                .find('.spinner-border').removeClass('d-none');
        } else {
            $btn.prop('disabled', false)
                .find('.btn-text').removeClass('d-none').end()
                .find('.spinner-border').addClass('d-none');
        }
    }

    /**
     * Kirim AJAX request
     * @param {string} url - Endpoint URL
     * @param {object} data - Data yang dikirim
     * @param {string} alertId - ID alert container
     * @param {string} btnId - ID tombol
     * @param {function} onSuccess - Callback saat success
     */
    function sendRequest(url, data, alertId, btnId, onSuccess) {
        toggleButton(btnId, true);

        $.ajax({
            url: url,
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            dataType: 'json',
            timeout: 15000,
            success: function (response) {
                if (response.status === 'success') {
                    showAlert(alertId, 'success', response.message);
                    if (typeof onSuccess === 'function') {
                        onSuccess(response);
                    }
                } else {
                    showAlert(alertId, 'danger', response.message);
                }
            },
            error: function (xhr) {
                let msg = 'Terjadi kesalahan. Silakan coba lagi.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                showAlert(alertId, 'danger', msg);
            },
            complete: function () {
                toggleButton(btnId, false);
            }
        });
    }


    // =============================================
    // Login Form Handler
    // =============================================

    $('#loginForm').on('submit', function (e) {
        e.preventDefault();

        const data = {
            username: $('#username').val().trim(),
            password: $('#password').val()
        };

        sendRequest('api/login.php', data, 'loginAlert', 'loginBtn', function (response) {
            // Redirect ke dashboard setelah 1 detik
            setTimeout(function () {
                window.location.href = response.redirect || 'dashboard.php';
            }, 1000);
        });
    });


    // =============================================
    // Register Form Handler
    // =============================================

    $('#registerForm').on('submit', function (e) {
        e.preventDefault();

        const password = $('#password').val();
        const confirmPassword = $('#confirm_password').val();

        if (password !== confirmPassword) {
            showAlert('registerAlert', 'danger', 'Konfirmasi password tidak cocok');
            return;
        }

        if (password.length < 6) {
            showAlert('registerAlert', 'danger', 'Password minimal 6 karakter');
            return;
        }

        const data = {
            full_name:       $('#full_name').val().trim(),
            username:        $('#username').val().trim(),
            email:           $('#email').val().trim(),
            password:        password,
            confirm_password: confirmPassword
        };

        sendRequest('api/register.php', data, 'registerAlert', 'registerBtn', function () {
            // Reset form setelah berhasil
            $('#registerForm')[0].reset();
            // Redirect ke login setelah 2 detik
            setTimeout(function () {
                window.location.href = 'index.php?registered=1';
            }, 2000);
        });
    });


    // =============================================
    // Forgot Password - Request Token
    // =============================================

    $('#resetRequestForm').on('submit', function (e) {
        e.preventDefault();

        const data = {
            action: 'request',
            email:  $('#resetEmail').val().trim()
        };

        sendRequest('api/reset_password.php', data, 'resetAlert', 'resetRequestBtn', function (response) {
            // Jika ada debug_token (untuk demo), tampilkan di step 2
            if (response.debug_token) {
                $('#resetToken').val(response.debug_token);
            }
            // Pindah ke step 2 setelah 1 detik
            setTimeout(function () {
                $('#stepRequest').addClass('d-none');
                $('#stepReset').removeClass('d-none');
            }, 1000);
        });
    });


    // =============================================
    // Forgot Password - Reset Password
    // =============================================

    $('#resetPasswordForm').on('submit', function (e) {
        e.preventDefault();

        const newPass = $('#newPassword').val();
        const confirmPass = $('#confirmNewPassword').val();

        if (newPass !== confirmPass) {
            showAlert('resetAlert', 'danger', 'Konfirmasi password tidak cocok');
            return;
        }

        if (newPass.length < 6) {
            showAlert('resetAlert', 'danger', 'Password minimal 6 karakter');
            return;
        }

        const data = {
            action:          'reset',
            token:           $('#resetToken').val().trim(),
            new_password:    newPass,
            confirm_password: confirmPass
        };

        sendRequest('api/reset_password.php', data, 'resetAlert', 'resetPasswordBtn', function () {
            // Redirect ke login setelah 2 detik
            setTimeout(function () {
                window.location.href = 'index.php?reset=1';
            }, 2000);
        });
    });


    // =============================================
    // Toggle Password Visibility
    // =============================================

    $(document).on('click', '.toggle-password', function () {
        const targetId = $(this).data('target');
        const $input = $('#' + targetId);
        const $icon = $(this).find('i');

        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $icon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            $input.attr('type', 'password');
            $icon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });


    // =============================================
    // URL Params - Show messages from redirects
    // =============================================

    const urlParams = new URLSearchParams(window.location.search);

    if (urlParams.get('registered') === '1') {
        showAlert('loginAlert', 'success', 'Akun berhasil dibuat! Silakan login.');
    }

    if (urlParams.get('reset') === '1') {
        showAlert('loginAlert', 'success', 'Password berhasil direset! Silakan login dengan password baru.');
    }

});
