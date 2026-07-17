$(document).ready(function () {

    /* =============================================
       INDEX.PHP - TOP UP LOGIC
       ============================================= */

    let selectedGame    = '';
    let selectedNominal = '';
    let selectedHarga   = 0;
    let selectedMetode  = '';

    // ── Pilih Game ──
    $(document).on('click', '.game-card', function () {
        $('.game-card').removeClass('selected');
        $(this).addClass('selected');

        selectedGame = $(this).data('game');
        selectedNominal = '';
        selectedHarga = 0;
        selectedMetode = '';

        $('#game_type').val(selectedGame);
        $('#form-title').text(selectedGame === 'mobile_legends' ? 'Top Up Mobile Legends' : 'Top Up Free Fire');
        $('#zone-id-group').toggleClass('d-none', selectedGame !== 'mobile_legends');
        $('#topup-form-container').removeClass('d-none');

        // Reset
        $('#nominal-list').html('<div class="col-12 text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div> Memuat nominal...</div>');
        $('.nominal-card, .payment-card').removeClass('selected');
        $('#ringkasan').addClass('d-none');
        $('#btn-beli').prop('disabled', true);
        $('#user_id_game').val('');
        $('#zone_id').val('');

        // Load nominal via AJAX
        $.ajax({
            url: 'proses_transaksi.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ action: 'get_nominal', game_type: selectedGame }),
            success: function (res) {
                if (res.success) {
                    let html = '';
                    $.each(res.data, function (i, item) {
                        html += `
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="card nominal-card h-100" data-nominal="${item.nominal}" data-harga="${item.harga}">
                                    <div class="card-body text-center p-3">
                                        <p class="fw-bold mb-1 fs-6">${item.nominal}</p>
                                        <p class="nominal-harga text-primary fw-semibold mb-0 small">Rp ${formatRupiah(item.harga)}</p>
                                    </div>
                                </div>
                            </div>`;
                    });
                    $('#nominal-list').html(html);
                }
            },
            error: function () {
                $('#nominal-list').html('<div class="col-12"><div class="alert alert-danger">Gagal memuat data nominal</div></div>');
            }
        });

        $('html, body').animate({ scrollTop: $('#topup-form-container').offset().top - 80 }, 400);
    });

    // ── Pilih Nominal ──
    $(document).on('click', '.nominal-card', function () {
        $('.nominal-card').removeClass('selected');
        $(this).addClass('selected');
        selectedNominal = $(this).data('nominal');
        selectedHarga   = parseInt($(this).data('harga'));
        updateRingkasan();
    });

    // ── Pilih Metode Pembayaran ──
    $(document).on('click', '.payment-card', function () {
        $('.payment-card').removeClass('selected');
        $(this).addClass('selected');
        selectedMetode = $(this).data('metode');
        updateRingkasan();
    });

    // ── Update Ringkasan ──
    function updateRingkasan() {
        let uid = $('#user_id_game').val().trim();
        let zid = $('#zone_id').val().trim();

        $('#summary-userid').text(uid || '-');
        $('#summary-zoneid').text(selectedGame === 'mobile_legends' ? (zid || '-') : 'Tidak perlu');
        $('#summary-nominal').text(selectedNominal || '-');
        $('#summary-harga').text(selectedHarga ? 'Rp ' + formatRupiah(selectedHarga) : '-');
        $('#summary-metode').text(selectedMetode || '-');

        if (selectedNominal && selectedMetode && uid) {
            $('#ringkasan').removeClass('d-none');
            $('#btn-beli').prop('disabled', false);
        } else {
            $('#ringkasan').addClass('d-none');
            $('#btn-beli').prop('disabled', true);
        }
    }

    $(document).on('input', '#user_id_game, #zone_id', function () {
        updateRingkasan();
    });

    // ── Beli / Submit Transaksi ──
    $('#form-topup').on('submit', function (e) {
        e.preventDefault();

        let userId = $('#user_id_game').val().trim();
        let zoneId = $('#zone_id').val().trim();

        if (!userId) {
            showAlert('alert-message', 'danger', 'User ID wajib diisi');
            $('#user_id_game').addClass('is-invalid');
            return;
        }
        $('#user_id_game').removeClass('is-invalid');

        if (selectedGame === 'mobile_legends' && !zoneId) {
            showAlert('alert-message', 'danger', 'Zone ID wajib diisi untuk Mobile Legends');
            $('#zone_id').addClass('is-invalid');
            return;
        }
        $('#zone_id').removeClass('is-invalid');

        let payload = {
            action: 'beli',
            user_id_game: userId,
            zone_id: zoneId,
            game_type: selectedGame,
            nominal: selectedNominal,
            harga: selectedHarga,
            metode_pembayaran: selectedMetode
        };

        let $btn = $('#btn-beli');
        $btn.addClass('btn-loading').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Memproses...');

        $.ajax({
            url: 'proses_transaksi.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            success: function (res) {
                $btn.removeClass('btn-loading').prop('disabled', false).html('<i class="bi bi-bag-check"></i> Beli Sekarang');
                if (res.success) {
                    $('#modal-pesan').text(res.message);
                    $('#modal-id').text('#' + res.data.id);
                    let modal = new bootstrap.Modal(document.getElementById('modalSukses'));
                    modal.show();
                    resetForm();
                } else {
                    showAlert('alert-message', 'danger', res.message);
                }
            },
            error: function () {
                $btn.removeClass('btn-loading').prop('disabled', false).html('<i class="bi bi-bag-check"></i> Beli Sekarang');
                showAlert('alert-message', 'danger', 'Terjadi kesalahan server. Silakan coba lagi.');
            }
        });
    });

    function resetForm() {
        selectedNominal = '';
        selectedHarga = 0;
        selectedMetode = '';
        $('#user_id_game').val('');
        $('#zone_id').val('');
        $('.nominal-card, .payment-card').removeClass('selected');
        $('#ringkasan').addClass('d-none');
        $('#btn-beli').prop('disabled', true);
    }

    /* =============================================
       LOGIN.PHP - LOGIN LOGIC
       ============================================= */

    $('#form-login').on('submit', function (e) {
        e.preventDefault();

        let username = $('#username').val().trim();
        let password = $('#password').val().trim();

        if (!username || !password) {
            showAlert('login-alert', 'danger', 'Username dan password harus diisi');
            return;
        }

        let $btn = $('#btn-login');
        $btn.addClass('btn-loading').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Login...');

        $.ajax({
            url: 'proses_login.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ username: username, password: password }),
            success: function (res) {
                if (res.success) {
                    showAlert('login-alert', 'success', res.message + ' Mengalihkan...');
                    setTimeout(function () {
                        window.location.href = 'admin_dashboard.php';
                    }, 800);
                } else {
                    $btn.removeClass('btn-loading').prop('disabled', false).html('<i class="bi bi-box-arrow-in-right"></i> Login');
                    showAlert('login-alert', 'danger', res.message);
                }
            },
            error: function () {
                $btn.removeClass('btn-loading').prop('disabled', false).html('<i class="bi bi-box-arrow-in-right"></i> Login');
                showAlert('login-alert', 'danger', 'Terjadi kesalahan server');
            }
        });
    });

    /* =============================================
       ADMIN DASHBOARD - TRANSAKSI LOGIC
       ============================================= */

    $(document).on('click', '#btn-refresh', function () {
        loadTransaksi();
    });

    $(document).on('change', '.select-status', function () {
        let id     = $(this).data('id');
        let status = $(this).val();

        let $row = $(this).closest('tr');
        $.ajax({
            url: 'proses_transaksi.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ action: 'update_status', id: id, status: status }),
            success: function (res) {
                if (res.success) {
                    showAlert('admin-alert', 'success', res.message);
                    loadTransaksi();
                } else {
                    showAlert('admin-alert', 'danger', res.message);
                }
            },
            error: function () {
                showAlert('admin-alert', 'danger', 'Gagal mengupdate status');
            }
        });
    });

});

/* =============================================
   SHARED FUNCTIONS
   ============================================= */

function loadTransaksi() {
    $.ajax({
        url: 'proses_transaksi.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'get_transaksi' }),
        success: function (res) {
            if (res.success) {
                renderTabel(res.data);
            }
        },
        error: function () {
            $('#tabel-transaksi').html('<tr><td colspan="9" class="text-center text-danger py-4">Gagal memuat data</td></tr>');
        }
    });
}

function renderTabel(data) {
    if (data.length === 0) {
        $('#tabel-transaksi').html('<tr><td colspan="9" class="text-center text-muted py-4"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Belum ada transaksi</td></tr>');
        $('#stat-total, #stat-pending, #stat-sukses, #stat-gagal').text('0');
        return;
    }

    let pending = 0, sukses = 0, gagal = 0;
    let html = '';

    $.each(data, function (i, t) {
        let badgeClass = '';
        let badgeBg = '';
        if (t.status === 'Pending') { badgeClass = 'bg-warning text-dark'; pending++; }
        else if (t.status === 'Sukses') { badgeClass = 'bg-success'; sukses++; }
        else if (t.status === 'Gagal') { badgeClass = 'bg-danger'; gagal++; }

        let gameLabel = t.game_type === 'mobile_legends' ? 'Mobile Legends' : 'Free Fire';
        let tanggal = new Date(t.tanggal).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

        html += `
        <tr>
            <td class="fw-bold">#${t.id}</td>
            <td>${escapeHtml(t.user_id_game)}</td>
            <td>${t.zone_id ? escapeHtml(t.zone_id) : '-'}</td>
            <td><span class="badge bg-dark">${gameLabel}</span></td>
            <td>${escapeHtml(t.nominal)}</td>
            <td>${escapeHtml(t.metode_pembayaran)}</td>
            <td><span class="badge badge-status ${badgeClass}">${t.status}</span></td>
            <td><small>${tanggal}</small></td>
            <td>
                <select class="form-select form-select-sm select-status" data-id="${t.id}" style="min-width:120px;">
                    <option value="Pending" ${t.status === 'Pending' ? 'selected' : ''}>Pending</option>
                    <option value="Sukses" ${t.status === 'Sukses' ? 'selected' : ''}>Sukses</option>
                    <option value="Gagal" ${t.status === 'Gagal' ? 'selected' : ''}>Gagal</option>
                </select>
            </td>
        </tr>`;
    });

    $('#tabel-transaksi').html(html);
    $('#stat-total').text(data.length);
    $('#stat-pending').text(pending);
    $('#stat-sukses').text(sukses);
    $('#stat-gagal').text(gagal);
}

function showAlert(elementId, type, message) {
    let $el = $('#' + elementId);
    $el.removeClass('alert-success alert-danger alert-warning alert-info')
       .addClass('alert alert-' + type)
       .html(message)
       .removeClass('d-none');
    setTimeout(function () { $el.addClass('d-none'); }, 5000);
}

function formatRupiah(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function escapeHtml(str) {
    let div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}
