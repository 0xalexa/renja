/* ==========================================================================
   Sistem Informasi Manajemen Dokumen PEP (SIM-PEP)
   Portal Pengguna (Read-Only User View) — Penelusuran & Unduh Berkas
   ========================================================================== */

const PAGES = {
  dashboard: { title: 'Beranda', navKey: 'dashboard' },
  'renja-murni': { title: 'Renja Murni', navKey: 'renja', module: 'renja-murni', category: 'Renja Murni' },
  'renja-perubahan': { title: 'Renja Perubahan', navKey: 'renja', module: 'renja-perubahan', category: 'Renja Perubahan' },
  'pk-murni': { title: 'PK Murni', navKey: 'pk', module: 'pk-murni', category: 'PK Murni' },
  'pk-perubahan': { title: 'PK Perubahan', navKey: 'pk', module: 'pk-perubahan', category: 'PK Perubahan' },
  'dpa-murni': { title: 'DPA Murni', navKey: 'dpa', module: 'dpa-murni', category: 'DPA Murni' },
  'dpa-perubahan': { title: 'DPA Perubahan', navKey: 'dpa', module: 'dpa-perubahan', category: 'DPA Perubahan' },
  'surat-masuk': { title: 'Surat Masuk', navKey: 'surat', module: 'surat-masuk' },
  'surat-keluar': { title: 'Surat Keluar', navKey: 'surat', module: 'surat-keluar' },
  laporan: { title: 'Laporan Simdapangda', navKey: 'laporan', module: 'laporan' },
};

// Database Berkas Resmi yang Tersedia untuk Pengguna
const db = {
  'renja-murni': [
    { id: 101, tahun: 2026, judul: 'Rencana Kerja Dinas Pendidikan T.A. 2026', keterangan: 'Dokumen Renja versi Murni penetapan awal APBD', file: 'renja_dinas_2026_murni.pdf', size: '3.4 MB', date: '2026-09-05', status: 'Lengkap' },
    { id: 102, tahun: 2026, judul: 'Program Prioritas Peningkatan Mutu Satuan Pendidikan', keterangan: 'Dokumen pendukung renja bidang pembinaan', file: 'renja_mutu_pendidikan_2026.pdf', size: '2.1 MB', date: '2026-09-02', status: 'Lengkap' },
    { id: 103, tahun: 2025, judul: 'Rencana Kerja Dinas Pendidikan T.A. 2025', keterangan: 'Arsip penetapan rencana kerja 2025', file: 'renja_2025_final.pdf', size: '4.2 MB', date: '2025-08-14', status: 'Lengkap' },
    { id: 104, tahun: 2024, judul: 'Rencana Kerja Dinas Pendidikan T.A. 2024', keterangan: 'Arsip penetapan rencana kerja 2024', file: 'renja_2024_arsip.pdf', size: '3.8 MB', date: '2024-08-10', status: 'Lengkap' }
  ],
  'renja-perubahan': [
    { id: 201, tahun: 2026, judul: 'Renja Perubahan Bidang Sarana & Prasarana 2026', keterangan: 'Penyesuaian alokasi revitalisasi gedung sekolah', file: 'renja_p_sarpras_2026.pdf', size: '2.7 MB', date: '2026-08-28', status: 'Lengkap' },
    { id: 202, tahun: 2025, judul: 'Renja Perubahan Semester II Dinas Pendidikan 2025', keterangan: 'Revisi program kerja pergeseran anggaran 2025', file: 'renja_perubahan_2025.pdf', size: '1.9 MB', date: '2025-09-12', status: 'Lengkap' }
  ],
  'pk-murni': [
    { id: 301, tahun: 2026, judul: 'Perjanjian Kinerja Kepala Dinas Pendidikan 2026', keterangan: 'Sasaran strategis indeks mutu dan akses pendidikan', file: 'pk_kadis_2026.pdf', size: '1.6 MB', date: '2026-09-03', status: 'Lengkap' },
    { id: 302, tahun: 2026, judul: 'Perjanjian Kinerja Sekretaris Dinas T.A. 2026', keterangan: 'Target kinerja tata kelola dan administrasi PEP', file: 'pk_sekretaris_2026.pdf', size: '1.4 MB', date: '2026-09-01', status: 'Lengkap' },
    { id: 303, tahun: 2025, judul: 'Perjanjian Kinerja Eselon III & IV T.A. 2025', keterangan: 'Pakta integritas dan penetapan target tahun lalu', file: 'pk_pejabat_2025.pdf', size: '3.1 MB', date: '2025-01-20', status: 'Lengkap' }
  ],
  'pk-perubahan': [
    { id: 401, tahun: 2026, judul: 'Adendum Perjanjian Kinerja Bidang Dikdas 2026', keterangan: 'Penyesuaian target angka partisipasi sekolah', file: 'adendum_pk_dikdas_2026.pdf', size: '1.2 MB', date: '2026-07-20', status: 'Perlu Update' },
    { id: 402, tahun: 2025, judul: 'PK Perubahan Eselon III Tahun 2025', keterangan: 'Penyesuaian indikator kinerja utama', file: 'pk_perubahan_2025.pdf', size: '1.8 MB', date: '2025-08-05', status: 'Lengkap' }
  ],
  'dpa-murni': [
    { id: 501, tahun: 2026, judul: 'DPA SKPD Dinas Pendidikan T.A. 2026 (Murni)', keterangan: 'Pagu Anggaran Rp 48.750.000.000,-', file: 'dpa_skpd_murni_2026.pdf', size: '5.6 MB', date: '2026-09-01', status: 'Lengkap' },
    { id: 502, tahun: 2026, judul: 'DPA Pengelolaan Pendidikan Dasar T.A. 2026', keterangan: 'Pagu Anggaran Rp 18.200.000.000,-', file: 'dpa_dikdas_2026.pdf', size: '2.9 MB', date: '2026-08-25', status: 'Lengkap' },
    { id: 503, tahun: 2025, judul: 'DPA Induk Tahun Anggaran 2025', keterangan: 'Pagu Anggaran Rp 44.100.000.000,-', file: 'dpa_induk_2025.pdf', size: '6.1 MB', date: '2025-01-15', status: 'Lengkap' }
  ],
  'dpa-perubahan': [
    { id: 601, tahun: 2026, judul: 'DPPA Program Bantuan Operasional Sekolah', keterangan: 'Tambahan pagu sebesar Rp 2.450.000.000,-', file: 'dppa_bop_2026.pdf', size: '3.1 MB', date: '2026-06-15', status: 'Perlu Update' },
    { id: 602, tahun: 2025, judul: 'DPA Perubahan Dinas Pendidikan T.A. 2025', keterangan: 'Perubahan APBD Semester II 2025', file: 'dpa_perubahan_2025.pdf', size: '4.8 MB', date: '2025-10-02', status: 'Lengkap' }
  ],
  'surat-masuk': [
    { id: 701, nomor: '001/BKPSDM/IX/2026', tanggal: '2026-09-01', perihal: 'Permohonan Data Formasi Kepegawaian T.A. 2027', instansi: 'BKPSDM Kabupaten', extra: 'Disposisi: Kasubag Umum & PEP', file: 'surat_bkpsdm_001.pdf', status: 'Diproses' },
    { id: 702, nomor: '045/SETDA/IX/2026', tanggal: '2026-09-03', perihal: 'Undangan Rapat Koordinasi Evaluasi Renja Triwulan III', instansi: 'Sekretariat Daerah', extra: 'Disposisi: Siapkan Bahan Paparan', file: 'undangan_setda_045.pdf', status: 'Diproses' },
    { id: 703, nomor: '112/BAPPEDA/IX/2026', tanggal: '2026-09-05', perihal: 'Permintaan Sinkronisasi Data Simdapangda 2026', instansi: 'Bappeda Litbang', extra: 'Disposisi: Segera kirim rekapitulasi', file: 'surat_bappeda_112.pdf', status: 'Selesai' }
  ],
  'surat-keluar': [
    { id: 801, nomor: '005/PEP-DISDIK/IX/2026', tanggal: '2026-09-02', perihal: 'Penyampaian Laporan Capaian Renja Semester I 2026', instansi: 'Bappeda Kabupaten', extra: 'Penandatangan: Kepala Dinas', file: 'surat_keluar_005.pdf', status: 'Terkirim' },
    { id: 802, nomor: '006/PEP-DISDIK/IX/2026', tanggal: '2026-09-06', perihal: 'Surat Edaran Pengumpulan Berkas PK & DPA', instansi: 'Seluruh Bidang & UPTD', extra: 'Penandatangan: Sekretaris Dinas', file: 'surat_edaran_006.pdf', status: 'Terkirim' }
  ]
};

let currentPage = 'dashboard';
let chartInstance = null;

// Format Status Badge
function getStatusBadge(status) {
  if (status === 'Lengkap' || status === 'Selesai') {
    return `<span class="badge badge-green">${status}</span>`;
  }
  if (status === 'Perlu Update' || status === 'Diproses') {
    return `<span class="badge badge-yellow">${status}</span>`;
  }
  if (status === 'Terkirim' || status === 'Aktif') {
    return `<span class="badge badge-blue">${status}</span>`;
  }
  return `<span class="badge badge-gray">${status || 'Tercatat'}</span>`;
}

function formatDate(dateStr) {
  if (!dateStr) return '-';
  const parts = dateStr.split('-');
  if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
  return dateStr;
}

// Controller Navigasi
function navigate(pageId) {
  if (!PAGES[pageId]) return;
  currentPage = pageId;

  // Toggle page visibility
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  const target = document.getElementById('page-' + pageId);
  if (target) target.classList.add('active');

  // Toggle active class on main navbar items
  const navKey = PAGES[pageId].navKey;
  document.querySelectorAll('.nav-item[data-nav]').forEach(item => {
    item.classList.toggle('active', item.dataset.nav === navKey);
  });

  // Toggle active class on dropdown links
  document.querySelectorAll('.nav-dropdown-link').forEach(link => {
    link.classList.remove('active');
  });
  const activeDropdownLink = document.querySelector(`.nav-dropdown-link[onclick*="'${pageId}'"]`);
  if (activeDropdownLink) activeDropdownLink.classList.add('active');

  // Close mobile navigation menu
  document.getElementById('navMenu')?.classList.remove('open');
  document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('dropdown-open'));

  // Render Table
  renderModuleTable(pageId);
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Dropdown Mobile / Click Toggle
function toggleDropdown(linkEl) {
  const parent = linkEl.closest('.nav-item');
  if (parent) {
    const wasOpen = parent.classList.contains('dropdown-open');
    document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('dropdown-open'));
    if (!wasOpen) parent.classList.add('dropdown-open');
  }
}

document.addEventListener('click', (e) => {
  if (!e.target.closest('.nav-item')) {
    document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('dropdown-open'));
  }
});

// Mobile Nav Toggle
document.getElementById('mobileNavToggle')?.addEventListener('click', () => {
  document.getElementById('navMenu')?.classList.toggle('open');
});

// Update Statistics & KPI Numbers
function updateStats() {
  const renjaCount = db['renja-murni'].length + db['renja-perubahan'].length;
  const pkDpaCount = db['pk-murni'].length + db['pk-perubahan'].length + db['dpa-murni'].length + db['dpa-perubahan'].length;
  const suratMasukCount = db['surat-masuk'].length + 21;
  const suratKeluarCount = db['surat-keluar'].length + 16;

  const e1 = document.getElementById('kpiRenja'); if (e1) e1.textContent = renjaCount;
  const e2 = document.getElementById('kpiPkDpa'); if (e2) e2.textContent = pkDpaCount;
  const e3 = document.getElementById('kpiSuratMasuk'); if (e3) e3.textContent = suratMasukCount;
  const e4 = document.getElementById('kpiSuratKeluar'); if (e4) e4.textContent = suratKeluarCount;
}

// Table Renderers (Fokus Pengguna: Detail & Unduh)
function renderDokumenTable(tableId, moduleKey) {
  const tbody = document.querySelector('#' + tableId + ' tbody');
  if (!tbody) return;
  const data = db[moduleKey] || [];

  if (data.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:24px;color:var(--text-muted);">Tidak ada berkas dokumen dalam modul ini.</td></tr>`;
    return;
  }

  tbody.innerHTML = data.map((d, idx) => `
    <tr>
      <td style="text-align:center;">${idx + 1}</td>
      <td style="text-align:center;"><span class="badge badge-gray">${d.tahun}</span></td>
      <td><strong>${d.judul}</strong></td>
      <td style="color:var(--text-muted);">${d.keterangan || '-'}</td>
      <td>
        <a href="#" class="file-attachment" onclick="downloadFile('${d.file}'); return false;">
          ${d.file} <span style="font-size:11px;color:var(--text-muted);">(${d.size})</span>
        </a>
      </td>
      <td style="text-align:center;">${getStatusBadge(d.status)}</td>
      <td style="text-align:center;">
        <div class="btn-action-group" style="justify-content:center;">
          <button class="btn btn-outline btn-sm" onclick="openDetailModal('dokumen', '${moduleKey}', ${d.id})">Detail</button>
          <button class="btn btn-primary btn-sm" onclick="downloadFile('${d.file}')">Unduh</button>
        </div>
      </td>
    </tr>
  `).join('');
}

function renderSuratTable(tableId, moduleKey) {
  const tbody = document.querySelector('#' + tableId + ' tbody');
  if (!tbody) return;
  const data = db[moduleKey] || [];
  const isMasuk = moduleKey === 'surat-masuk';

  if (data.length === 0) {
    tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text-muted);">Tidak ada arsip surat.</td></tr>`;
    return;
  }

  tbody.innerHTML = data.map((s, idx) => `
    <tr>
      <td style="text-align:center;">${idx + 1}</td>
      <td><strong>${s.nomor}</strong></td>
      <td style="text-align:center;">${formatDate(s.tanggal)}</td>
      <td>${s.perihal}</td>
      <td>${s.instansi}</td>
      <td>
        <a href="#" class="file-attachment" onclick="downloadFile('${s.file}'); return false;">
          ${s.file}
        </a>
      </td>
      <td style="text-align:center;">${getStatusBadge(s.status || (isMasuk ? 'Diproses' : 'Terkirim'))}</td>
      <td style="text-align:center;">
        <div class="btn-action-group" style="justify-content:center;">
          <button class="btn btn-outline btn-sm" onclick="openDetailModal('surat', '${moduleKey}', ${s.id})">Detail</button>
          <button class="btn btn-primary btn-sm" onclick="downloadFile('${s.file}')">Unduh</button>
        </div>
      </td>
    </tr>
  `).join('');
}

function renderDashboardRecent() {
  const tbody = document.getElementById('dashboardRecentTable');
  if (!tbody) return;

  const items = [
    { no: 1, tahun: 2026, title: db['renja-murni'][0]?.judul, mod: 'Renja Murni', file: db['renja-murni'][0]?.file, date: db['renja-murni'][0]?.date, status: 'Lengkap', modKey: 'renja-murni', id: db['renja-murni'][0]?.id, type: 'dokumen' },
    { no: 2, tahun: 2026, title: db['pk-murni'][0]?.judul, mod: 'PK Murni', file: db['pk-murni'][0]?.file, date: db['pk-murni'][0]?.date, status: 'Lengkap', modKey: 'pk-murni', id: db['pk-murni'][0]?.id, type: 'dokumen' },
    { no: 3, tahun: 2026, title: db['dpa-murni'][0]?.judul, mod: 'DPA Murni', file: db['dpa-murni'][0]?.file, date: db['dpa-murni'][0]?.date, status: 'Lengkap', modKey: 'dpa-murni', id: db['dpa-murni'][0]?.id, type: 'dokumen' },
    { no: 4, tahun: 2026, title: db['surat-masuk'][0]?.perihal, mod: 'Surat Masuk', file: db['surat-masuk'][0]?.file, date: db['surat-masuk'][0]?.tanggal, status: 'Diproses', modKey: 'surat-masuk', id: db['surat-masuk'][0]?.id, type: 'surat' },
    { no: 5, tahun: 2026, title: db['surat-keluar'][0]?.perihal, mod: 'Surat Keluar', file: db['surat-keluar'][0]?.file, date: db['surat-keluar'][0]?.tanggal, status: 'Terkirim', modKey: 'surat-keluar', id: db['surat-keluar'][0]?.id, type: 'surat' }
  ];

  tbody.innerHTML = items.map((it, idx) => `
    <tr>
      <td style="text-align:center;">${idx + 1}</td>
      <td style="text-align:center;"><span class="badge badge-gray">${it.tahun}</span></td>
      <td><strong>${it.title}</strong></td>
      <td><span class="badge badge-blue">${it.mod}</span></td>
      <td>
        <a href="#" class="file-attachment" onclick="downloadFile('${it.file}'); return false;">${it.file}</a>
      </td>
      <td style="text-align:center;">${formatDate(it.date)}</td>
      <td style="text-align:center;">${getStatusBadge(it.status)}</td>
      <td style="text-align:center;">
        <div class="btn-action-group" style="justify-content:center;">
          <button class="btn btn-outline btn-sm" onclick="openDetailModal('${it.type}', '${it.modKey}', ${it.id})">Detail</button>
          <button class="btn btn-primary btn-sm" onclick="downloadFile('${it.file}')">Unduh</button>
        </div>
      </td>
    </tr>
  `).join('');
}

function renderModuleTable(pageKey) {
  if (pageKey === 'dashboard') {
    renderDashboardRecent();
    updateStats();
    return;
  }
  if (pageKey.startsWith('surat')) {
    renderSuratTable('table-' + pageKey, pageKey);
  } else if (db[pageKey]) {
    renderDokumenTable('table-' + pageKey, pageKey);
  }
  updateStats();
}

function renderAllTables() {
  renderDokumenTable('table-renja-murni', 'renja-murni');
  renderDokumenTable('table-renja-perubahan', 'renja-perubahan');
  renderDokumenTable('table-pk-murni', 'pk-murni');
  renderDokumenTable('table-pk-perubahan', 'pk-perubahan');
  renderDokumenTable('table-dpa-murni', 'dpa-murni');
  renderDokumenTable('table-dpa-perubahan', 'dpa-perubahan');
  renderSuratTable('table-surat-masuk', 'surat-masuk');
  renderSuratTable('table-surat-keluar', 'surat-keluar');
  renderDashboardRecent();
  updateStats();
}

// Handler Unduh Dokumen
function downloadFile(filename) {
  showToast(`Memulai proses pengunduhan: ${filename}`, 'info');
}

// Modal System
function openModal(id) {
  document.getElementById(id)?.classList.add('show');
}

function closeModal(id) {
  document.getElementById(id)?.classList.remove('show');
}

document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) closeModal(overlay.id);
  });
});

// Detail Preview Modal (Khusus Pengguna)
function openDetailModal(type, moduleKey, id) {
  const content = document.getElementById('detailListContent');
  const titleEl = document.getElementById('detailModalTitle');
  const dlBtn = document.getElementById('btnDetailDownload');
  if (!content) return;

  const list = db[moduleKey] || [];
  const item = list.find(x => x.id === id);
  if (!item) return;

  if (type === 'dokumen') {
    if (titleEl) titleEl.textContent = 'DETAIL INFORMASI DOKUMEN RESMI';
    content.innerHTML = `
      <table class="gov-table" style="margin:0;">
        <tr><td style="width:30%;background:#edf2f7;font-weight:700;">Nama Dokumen</td><td><strong>${item.judul}</strong></td></tr>
        <tr><td style="background:#edf2f7;font-weight:700;">Tahun Anggaran</td><td>${item.tahun}</td></tr>
        <tr><td style="background:#edf2f7;font-weight:700;">Kategori Modul</td><td>${PAGES[moduleKey]?.category || '-'}</td></tr>
        <tr><td style="background:#edf2f7;font-weight:700;">Keterangan / Uraian</td><td>${item.keterangan || '-'}</td></tr>
        <tr><td style="background:#edf2f7;font-weight:700;">Nama Berkas File</td><td>${item.file} (${item.size})</td></tr>
        <tr><td style="background:#edf2f7;font-weight:700;">Tanggal Penetapan</td><td>${formatDate(item.date)}</td></tr>
        <tr><td style="background:#edf2f7;font-weight:700;">Status Verifikasi</td><td>${getStatusBadge(item.status)}</td></tr>
      </table>
    `;
    if (dlBtn) dlBtn.onclick = () => downloadFile(item.file);
  } else {
    const isMasuk = moduleKey === 'surat-masuk';
    if (titleEl) titleEl.textContent = isMasuk ? 'DETAIL AGENDA SURAT MASUK' : 'DETAIL AGENDA SURAT KELUAR';
    content.innerHTML = `
      <table class="gov-table" style="margin:0;">
        <tr><td style="width:30%;background:#edf2f7;font-weight:700;">Nomor Surat</td><td><strong>${item.nomor}</strong></td></tr>
        <tr><td style="background:#edf2f7;font-weight:700;">Tanggal Surat</td><td>${formatDate(item.tanggal)}</td></tr>
        <tr><td style="background:#edf2f7;font-weight:700;">Perihal Surat</td><td>${item.perihal}</td></tr>
        <tr><td style="background:#edf2f7;font-weight:700;">${isMasuk ? 'Asal Instansi' : 'Tujuan Instansi'}</td><td>${item.instansi}</td></tr>
        <tr><td style="background:#edf2f7;font-weight:700;">${isMasuk ? 'Disposisi' : 'Penandatangan'}</td><td>${item.extra || '-'}</td></tr>
        <tr><td style="background:#edf2f7;font-weight:700;">Berkas Scan</td><td>${item.file}</td></tr>
        <tr><td style="background:#edf2f7;font-weight:700;">Status Agenda</td><td>${getStatusBadge(item.status || 'Tercatat')}</td></tr>
      </table>
    `;
    if (dlBtn) dlBtn.onclick = () => downloadFile(item.file);
  }

  openModal('modalDetail');
}

// Live Search Filter (Penelusuran Dokumen Cepat)
function setupSearchFilters() {
  document.querySelectorAll('.page').forEach(page => {
    const search = page.querySelector('.table-toolbar .search-box');
    const filterYear = page.querySelector('.table-toolbar .filter-year');
    const dateFrom = page.querySelector('.table-toolbar .filter-date-from');
    const dateTo = page.querySelector('.table-toolbar .filter-date-to');
    const table = page.querySelector('table');

    function applyFilter() {
      if (!table) return;
      const q = search ? search.value.toLowerCase().trim() : '';
      const year = filterYear ? filterYear.value.trim() : '';

      const rows = table.querySelectorAll('tbody tr');
      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        let match = true;
        if (q && !text.includes(q)) match = false;
        if (year && !text.includes(year)) match = false;
        row.style.display = match ? '' : 'none';
      });
    }

    if (search) search.addEventListener('input', applyFilter);
    if (filterYear) filterYear.addEventListener('change', applyFilter);
    if (dateFrom) dateFrom.addEventListener('change', applyFilter);
    if (dateTo) dateTo.addEventListener('change', applyFilter);
  });
}

// Toast Notifikasi
function showToast(message, type = 'info') {
  const container = document.getElementById('toastContainer');
  if (!container) return;

  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.innerHTML = `
    <span>${message}</span>
    <button style="background:none;border:none;color:var(--text-muted);font-weight:700;margin-left:12px;cursor:pointer;" onclick="this.parentElement.remove()">&times;</button>
  `;

  container.appendChild(toast);
  setTimeout(() => {
    if (toast.parentElement) toast.remove();
  }, 3200);
}

// Chart.js (Palet Biru Formal Kepemerintahan)
function initChart() {
  const ctx = document.getElementById('chartAnggaran');
  if (!ctx || typeof Chart === 'undefined') return;

  if (chartInstance) chartInstance.destroy();

  chartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Renja Murni', 'Renja Perub.', 'PK Murni', 'PK Perub.', 'DPA Murni', 'DPA Perub.', 'Surat Masuk', 'Surat Keluar'],
      datasets: [{
        label: 'Total Berkas Tersedia T.A. 2026',
        data: [
          db['renja-murni'].length,
          db['renja-perubahan'].length,
          db['pk-murni'].length,
          db['pk-perubahan'].length,
          db['dpa-murni'].length,
          db['dpa-perubahan'].length,
          db['surat-masuk'].length + 21,
          db['surat-keluar'].length + 16
        ],
        backgroundColor: [
          '#1a365d',
          '#2b6cb0',
          '#2f855a',
          '#38a169',
          '#c05621',
          '#dd6b20',
          '#3182ce',
          '#4a5568'
        ],
        borderRadius: 2
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: '#e2e8f0' },
          ticks: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 } }
        },
        x: {
          grid: { display: false },
          ticks: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 11, weight: '600' } }
        }
      }
    }
  });
}

// Tanggal Topbar
function setupTopbarDate() {
  const el = document.getElementById('topbarDate');
  if (el) {
    const now = new Date();
    const opt = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
    el.textContent = now.toLocaleDateString('id-ID', opt);
  }
}

// Bootstrap Aplikasi
document.addEventListener('DOMContentLoaded', () => {
  setupTopbarDate();
  renderAllTables();
  initChart();
  setupSearchFilters();
});

if (document.readyState === 'complete' || document.readyState === 'interactive') {
  setupTopbarDate();
  renderAllTables();
  initChart();
}