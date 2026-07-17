/* RzLab Center - Main JavaScript */

(function() {
    'use strict';

    /* ========== Base URL ========== */
    const scriptEl = document.currentScript;
    const scriptSrc = scriptEl ? scriptEl.src : '';
    const BASE = scriptSrc.replace(/assets\/js\/main\.js.*$/, '');
    window.BASE = BASE;

    /* ========== Sidebar Toggle ========== */
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    const topHeader = document.querySelector('.top-header');
    const toggleBtns = document.querySelectorAll('.sidebar-toggle');

    toggleBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('mobile-open');
            } else {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('sidebar-collapsed');
                topHeader.classList.toggle('sidebar-collapsed');
            }
        });
    });

    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 768 && sidebar.classList.contains('mobile-open')) {
            if (!sidebar.contains(e.target) && !e.target.closest('.sidebar-toggle')) {
                sidebar.classList.remove('mobile-open');
            }
        }
    });

    /* ========== Modal System ========== */
    window.openModal = function(modalId) {
        const overlay = document.getElementById(modalId);
        if (overlay) {
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeModal = function(modalId) {
        const overlay = document.getElementById(modalId);
        if (overlay) {
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    };

    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });

    document.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', () => {
            const overlay = btn.closest('.modal-overlay');
            if (overlay) {
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });

    /* ========== Toast Notification System ========== */
    window.showToast = function(message, type = 'info') {
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const icons = {
            success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
            error: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
            info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>'
        };

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <span class="toast-icon">${icons[type] || icons.info}</span>
            <span class="toast-message">${message}</span>
            <button class="toast-close" onclick="this.parentElement.remove()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        `;
        container.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('removing');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    };

    /* ========== AJAX Helper ========== */
    window.ajaxRequest = async function(url, options = {}) {
        const defaults = {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        };
        const config = { ...defaults, ...options };

        if (config.body && !(config.body instanceof FormData)) {
            config.headers['Content-Type'] = 'application/x-www-form-urlencoded';
            if (typeof config.body === 'object') {
                config.body = new URLSearchParams(config.body).toString();
            }
        }
        if (config.body instanceof FormData) {
            delete config.headers['Content-Type'];
        }

        try {
            const response = await fetch(url, config);
            const text = await response.text();
            try {
                return JSON.parse(text);
            } catch {
                return { status: 'error', message: 'Invalid JSON response', raw: text };
            }
        } catch (err) {
            return { status: 'error', message: err.message };
        }
    };

    /* ========== Inventory Table Manager ========== */
    window.InventoryManager = {
        currentPage: 1,
        perPage: 10,
        searchQuery: '',
        filterCategory: '',
        filterCondition: '',
        filterRoom: '',

        async load() {
            const params = new URLSearchParams({
                action: 'list',
                page: this.currentPage,
                per_page: this.perPage,
                search: this.searchQuery,
                category: this.filterCategory,
                condition: this.filterCondition,
                room: this.filterRoom
            });

            const result = await ajaxRequest(`${BASE}admin/kelola_inventaris.php?${params}`);

            if (result.status === 'success') {
                this.renderTable(result.data);
                this.renderPagination(result.total, result.page, result.per_page);
            } else {
                showToast(result.message || 'Gagal memuat data', 'error');
            }
        },

        renderTable(items) {
            const tbody = document.getElementById('inventory-tbody');
            if (!tbody) return;

            if (!items || items.length === 0) {
                tbody.innerHTML = `
                    <tr><td colspan="9">
                        <div class="empty-state">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                            <h3>Tidak ada data</h3>
                            <p>Belum ada item inventaris yang ditemukan.</p>
                        </div>
                    </td></tr>`;
                return;
            }

            tbody.innerHTML = items.map(item => {
                const condClass = item.condition.toLowerCase();
                return `
                <tr data-id="${item.id}">
                    <td><span class="table-item-code">${escapeHtml(item.item_code)}</span></td>
                    <td><span class="table-item-name">${escapeHtml(item.item_name)}</span></td>
                    <td>${escapeHtml(item.brand)}</td>
                    <td><span class="badge-category">${escapeHtml(item.category)}</span></td>
                    <td><span class="mono" style="font-size:11px">${escapeHtml(item.serial_number)}</span></td>
                    <td><span class="badge-status badge-${condClass}">${escapeHtml(item.condition)}</span></td>
                    <td>${escapeHtml(item.lab_room)}</td>
                    <td>${item.quantity}</td>
                    <td>
                        <div class="table-actions">
                            <button class="btn btn-ghost btn-icon btn-sm" onclick="InventoryManager.edit(${item.id})" title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                            <button class="btn btn-ghost btn-icon btn-sm" onclick="InventoryManager.remove(${item.id})" title="Hapus" style="color:var(--red-400)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>`;
            }).join('');
        },

        renderPagination(total, page, perPage) {
            const totalPages = Math.ceil(total / perPage);
            const infoEl = document.getElementById('pagination-info');
            const btnsEl = document.getElementById('pagination-btns');
            if (!infoEl || !btnsEl) return;

            const start = (page - 1) * perPage + 1;
            const end = Math.min(page * perPage, total);
            infoEl.textContent = total > 0
                ? `Menampilkan ${start}-${end} dari ${total} item`
                : 'Tidak ada data';

            let btns = '';
            if (page > 1) {
                btns += `<button class="btn btn-ghost btn-sm" onclick="InventoryManager.goToPage(${page - 1})">Sebelumnya</button>`;
            }
            for (let i = Math.max(1, page - 2); i <= Math.min(totalPages, page + 2); i++) {
                btns += `<button class="btn ${i === page ? 'btn-primary' : 'btn-ghost'} btn-sm" onclick="InventoryManager.goToPage(${i})">${i}</button>`;
            }
            if (page < totalPages) {
                btns += `<button class="btn btn-ghost btn-sm" onclick="InventoryManager.goToPage(${page + 1})">Selanjutnya</button>`;
            }
            btnsEl.innerHTML = btns;
        },

        goToPage(page) {
            this.currentPage = page;
            this.load();
        },

        async edit(id) {
            const result = await ajaxRequest(`${BASE}admin/kelola_inventaris.php?action=detail&id=${id}`);
            if (result.status === 'success') {
                const f = result.data;
                document.getElementById('edit-id').value = f.id;
                document.getElementById('edit-item_code').value = f.item_code;
                document.getElementById('edit-item_name').value = f.item_name;
                document.getElementById('edit-brand').value = f.brand;
                document.getElementById('edit-category').value = f.category;
                document.getElementById('edit-serial_number').value = f.serial_number;
                document.getElementById('edit-condition').value = f.condition;
                document.getElementById('edit-lab_room').value = f.lab_room;
                document.getElementById('edit-quantity').value = f.quantity;
                openModal('modal-edit');
            } else {
                showToast(result.message || 'Gagal mengambil data', 'error');
            }
        },

        async remove(id) {
            if (!confirm('Yakin ingin menghapus item ini?')) return;
            const result = await ajaxRequest(`${BASE}admin/kelola_inventaris.php`, {
                method: 'POST',
                body: JSON.stringify({ action: 'delete', id: id }),
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (result.status === 'success') {
                showToast('Item berhasil dihapus', 'success');
                this.load();
            } else {
                showToast(result.message || 'Gagal menghapus item', 'error');
            }
        }
    };

    /* ========== Form Handlers ========== */
    document.addEventListener('DOMContentLoaded', function() {

        /* --- Header Search --- */
        const headerSearch = document.getElementById('header-search');
        if (headerSearch) {
            headerSearch.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const q = this.value.trim();
                    if (q) {
                        window.location.href = `${BASE}admin/kelola_inventaris.php?search=${encodeURIComponent(q)}`;
                    } else {
                        window.location.href = `${BASE}admin/kelola_inventaris.php`;
                    }
                }
            });
        }

        /* --- Notification Dropdown --- */
        const notifBtn = document.getElementById('notif-btn');
        const notifDropdown = document.getElementById('notif-dropdown');
        if (notifBtn && notifDropdown) {
            notifBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                notifDropdown.classList.toggle('active');
                const pd = document.getElementById('profile-dropdown');
                if (pd) pd.classList.remove('active');
            });
        }

        /* --- Profile Dropdown --- */
        const profileToggle = document.getElementById('profile-toggle');
        const profileDropdown = document.getElementById('profile-dropdown');
        if (profileToggle && profileDropdown) {
            profileToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                profileDropdown.classList.toggle('active');
                if (notifDropdown) notifDropdown.classList.remove('active');
            });
        }

        /* Close dropdowns on outside click */
        document.addEventListener('click', function(e) {
            if (notifDropdown && !notifDropdown.contains(e.target) && e.target !== notifBtn && !notifBtn?.contains(e.target)) {
                notifDropdown.classList.remove('active');
            }
            if (profileDropdown && !profileDropdown.contains(e.target) && !profileToggle?.contains(e.target)) {
                profileDropdown.classList.remove('active');
            }
        });

        /* --- Laporan Baru Button --- */
        const btnLaporan = document.getElementById('btn-laporan-baru');
        if (btnLaporan) {
            btnLaporan.addEventListener('click', async function() {
                const select = document.getElementById('laporan-item-select');
                if (select && select.options.length <= 1) {
                    const res = await ajaxRequest(`${BASE}admin/kelola_inventaris.php?action=list&per_page=100`);
                    if (res.status === 'success' && res.data) {
                        select.innerHTML = '<option value="">-- Pilih Item --</option>' +
                            res.data.map(i => `<option value="${i.id}">${escapeHtml(i.item_name)} (${escapeHtml(i.item_code)})</option>`).join('');
                    }
                }
                openModal('modal-laporan-baru');
            });
        }

        /* --- Admin Damage Report Form --- */
        const adminReportForm = document.getElementById('form-admin-damage-report');
        if (adminReportForm) {
            adminReportForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'report_damage');
                const result = await ajaxRequest(`${BASE}api/report_damage.php`, {
                    method: 'POST',
                    body: formData
                });
                if (result.status === 'success') {
                    showToast('Laporan kerusakan berhasil dikirim', 'success');
                    closeModal('modal-laporan-baru');
                    this.reset();
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    showToast(result.message || 'Gagal mengirim laporan', 'error');
                }
            });
        }

        /* Add Item Form */
        const addForm = document.getElementById('form-add-item');
        if (addForm) {
            addForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'add');
                const result = await ajaxRequest(`${BASE}admin/kelola_inventaris.php`, {
                    method: 'POST',
                    body: formData
                });
                if (result.status === 'success') {
                    showToast('Item berhasil ditambahkan', 'success');
                    closeModal('modal-add');
                    this.reset();
                    InventoryManager.load();
                } else {
                    showToast(result.message || 'Gagal menambahkan item', 'error');
                }
            });
        }

        /* Edit Item Form */
        const editForm = document.getElementById('form-edit-item');
        if (editForm) {
            editForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'update');
                const result = await ajaxRequest(`${BASE}admin/kelola_inventaris.php`, {
                    method: 'POST',
                    body: formData
                });
                if (result.status === 'success') {
                    showToast('Item berhasil diperbarui', 'success');
                    closeModal('modal-edit');
                    InventoryManager.load();
                } else {
                    showToast(result.message || 'Gagal memperbarui item', 'error');
                }
            });
        }

        /* Damage Report Form */
        const reportForm = document.getElementById('form-damage-report');
        if (reportForm) {
            reportForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'report_damage');
                const result = await ajaxRequest(`${BASE}api/report_damage.php`, {
                    method: 'POST',
                    body: formData
                });
                if (result.status === 'success') {
                    showToast('Laporan kerusakan berhasil dikirim', 'success');
                    this.reset();
                    const successDiv = document.getElementById('report-success-msg');
                    if (successDiv) successDiv.style.display = 'block';
                    setTimeout(() => { if (successDiv) successDiv.style.display = 'none'; }, 5000);
                } else {
                    showToast(result.message || 'Gagal mengirim laporan', 'error');
                }
            });
        }

        /* Filter handlers */
        const searchInput = document.getElementById('filter-search');
        let searchTimeout;
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    InventoryManager.searchQuery = this.value;
                    InventoryManager.currentPage = 1;
                    InventoryManager.load();
                }, 300);
            });
        }

        const catSelect = document.getElementById('filter-category');
        const condSelect = document.getElementById('filter-condition');
        const roomSelect = document.getElementById('filter-room');

        if (catSelect) catSelect.addEventListener('change', function() { InventoryManager.filterCategory = this.value; InventoryManager.currentPage = 1; InventoryManager.load(); });
        if (condSelect) condSelect.addEventListener('change', function() { InventoryManager.filterCondition = this.value; InventoryManager.currentPage = 1; InventoryManager.load(); });
        if (roomSelect) roomSelect.addEventListener('change', function() { InventoryManager.filterRoom = this.value; InventoryManager.currentPage = 1; InventoryManager.load(); });

        /* Auto-load inventory table */
        if (document.getElementById('inventory-tbody')) {
            const urlParams = new URLSearchParams(window.location.search);
            const searchParam = urlParams.get('search');
            if (searchParam) {
                InventoryManager.searchQuery = searchParam;
                const searchInput = document.getElementById('filter-search');
                if (searchInput) searchInput.value = searchParam;
            }
            InventoryManager.load();
        }
    });

    /* ========== Utility ========== */
    function escapeHtml(text) {
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }

    window.escapeHtml = escapeHtml;

})();
