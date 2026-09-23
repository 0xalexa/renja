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
  'capaian-kinerja': { title: 'Capaian Kinerja OPD', navKey: 'capaian-kinerja' },
};

// Database Berkas Resmi yang Tersedia untuk Pengguna
const staticFallbackDb = {
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

let db = {
  'renja-murni': [],
  'renja-perubahan': [],
  'pk-murni': [],
  'pk-perubahan': [],
  'dpa-murni': [],
  'dpa-perubahan': [],
  'surat-masuk': [],
  'surat-keluar': []
};

// Sinkronisasi data resmi dari Database Administrator (Server Laravel)
function syncDatabaseFromServer() {
  if (window.serverDb && typeof window.serverDb === 'object') {
    Object.keys(db).forEach(key => {
      db[key] = Array.isArray(window.serverDb[key]) ? window.serverDb[key] : [];
    });
  } else {
    db = JSON.parse(JSON.stringify(staticFallbackDb));
  }
}
syncDatabaseFromServer();

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
    const oc = link.getAttribute('onclick') || '';
    if (oc.includes(`'${pageId}'`) || oc.includes(`"${pageId}"`)) {
      link.classList.add('active');
    }
  });

  // Close mobile navigation menu
  document.getElementById('navMenu')?.classList.remove('open');
  document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('dropdown-open'));

  // Render Table
  if (pageId === 'capaian-kinerja') {
    if (typeof renderCapaianTable === 'function') renderCapaianTable();
  } else {
    renderModuleTable(pageId);
  }
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
  const renjaCount = (db['renja-murni']?.length || 0) + (db['renja-perubahan']?.length || 0);
  const pkDpaCount = (db['pk-murni']?.length || 0) + (db['pk-perubahan']?.length || 0) + (db['dpa-murni']?.length || 0) + (db['dpa-perubahan']?.length || 0);
  const suratMasukCount = db['surat-masuk']?.length || 0;
  const suratKeluarCount = db['surat-keluar']?.length || 0;

  const e1 = document.getElementById('kpiRenja'); if (e1) e1.textContent = renjaCount;
  const e2 = document.getElementById('kpiPkDpa'); if (e2) e2.textContent = pkDpaCount;
  const e3 = document.getElementById('kpiSuratMasuk'); if (e3) e3.textContent = suratMasukCount;
  const e4 = document.getElementById('kpiSuratKeluar'); if (e4) e4.textContent = suratKeluarCount;

  const badgeCapaian = document.getElementById('badgeCountCapaian');
  if (badgeCapaian && typeof capaianDb !== 'undefined' && Array.isArray(capaianDb)) {
    badgeCapaian.textContent = `${capaianDb.length} Data`;
  }
}

// Table Renderers (Fokus Pengguna: Detail & Unduh)
function renderDokumenTable(tableId, moduleKey) {
  const tbody = document.querySelector('#' + tableId + ' tbody');
  if (!tbody) return;
  const data = db[moduleKey] || [];

  if (data.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:24px;color:var(--text-muted);">Belum ada berkas dokumen yang diunggah oleh Administrator.</td></tr>`;
    return;
  }

  tbody.innerHTML = data.map((d, idx) => `
    <tr>
      <td style="text-align:center;">${idx + 1}</td>
      <td style="text-align:center;"><span class="badge badge-gray">${d.tahun}</span></td>
      <td><strong>${d.judul}</strong></td>
      <td style="color:var(--text-muted);">${d.keterangan || '-'}</td>
      <td>
        ${d.link_drive ? `
          <a href="${d.link_drive}" target="_blank" rel="noopener noreferrer" class="file-attachment" style="color:#00875a;font-weight:600;" title="Buka tautan Google Drive">
            ${d.file} <span style="font-size:11px;">(Google Drive)</span>
          </a>
        ` : `
          <a href="#" class="file-attachment" onclick="downloadFile('${d.file}', 'dokumen', ${d.id}); return false;">
            ${d.file} <span style="font-size:11px;color:var(--text-muted);">(${d.size})</span>
          </a>
        `}
      </td>
      <td style="text-align:center;">${getStatusBadge(d.status)}</td>
      <td style="text-align:center;">
        <div class="btn-action-group" style="justify-content:center;">
          <button class="btn btn-outline btn-sm" onclick="openDetailModal('dokumen', '${moduleKey}', ${d.id})">Detail</button>
          <button class="btn btn-primary btn-sm" onclick="downloadFile('${d.file}', 'dokumen', ${d.id})">Unduh</button>
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
    tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text-muted);">Belum ada arsip surat yang tercatat oleh Administrator.</td></tr>`;
    return;
  }

  tbody.innerHTML = data.map((s, idx) => `
    <tr>
      <td style="text-align:center;">${idx + 1}</td>
      <td><strong>${s.nomor}</strong></td>
      <td style="text-align:center;">${formatDate(s.tanggal)}</td>
      <td>${s.perihal}</td>
      <td>${s.instansi || s.pengirim || '-'}</td>
      <td>
        ${s.link_drive ? `
          <a href="${s.link_drive}" target="_blank" rel="noopener noreferrer" class="file-attachment" style="color:#00875a;font-weight:600;" title="Buka tautan Google Drive">
            ${s.file} <span style="font-size:11px;">(Google Drive)</span>
          </a>
        ` : `
          <a href="#" class="file-attachment" onclick="downloadFile('${s.file}', 'surat', ${s.id}); return false;">
            ${s.file}
          </a>
        `}
      </td>
      <td style="text-align:center;">${getStatusBadge(s.status || (isMasuk ? 'Diproses' : 'Terkirim'))}</td>
      <td style="text-align:center;">
        <div class="btn-action-group" style="justify-content:center;">
          <button class="btn btn-outline btn-sm" onclick="openDetailModal('surat', '${moduleKey}', ${s.id})">Detail</button>
          <button class="btn btn-primary btn-sm" onclick="downloadFile('${s.file}', 'surat', ${s.id})">Unduh</button>
        </div>
      </td>
    </tr>
  `).join('');
}

function renderDashboardRecent() {
  const tbody = document.getElementById('dashboardRecentTable');
  if (!tbody) return;

  const allItems = [];
  const moduleLabels = {
    'renja-murni': 'Renja Murni',
    'renja-perubahan': 'Renja Perubahan',
    'pk-murni': 'PK Murni',
    'pk-perubahan': 'PK Perubahan',
    'dpa-murni': 'DPA Murni',
    'dpa-perubahan': 'DPA Perubahan',
    'surat-masuk': 'Surat Masuk',
    'surat-keluar': 'Surat Keluar'
  };

  Object.keys(moduleLabels).forEach(key => {
    const list = db[key] || [];
    list.forEach(item => {
      allItems.push({
        id: item.id,
        tahun: item.tahun || (item.tanggal ? String(item.tanggal).substring(0, 4) : 2026),
        title: item.judul || item.perihal || item.nomor || 'Berkas Dokumen',
        mod: moduleLabels[key],
        modKey: key,
        type: key.startsWith('surat') ? 'surat' : 'dokumen',
        file: item.file || item.file_name || '-',
        date: item.date || item.tanggal || '-',
        status: item.status || 'Lengkap',
        link_drive: item.link_drive
      });
    });
  });

  // Urutkan dokumen terbaru berdasarkan tanggal
  allItems.sort((a, b) => (b.date || '').localeCompare(a.date || ''));
  const recent = allItems.slice(0, 5);

  if (recent.length === 0) {
    tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text-muted);">Belum ada dokumen yang diunggah oleh Administrator.</td></tr>`;
    return;
  }

  tbody.innerHTML = recent.map((it, idx) => `
    <tr>
      <td style="text-align:center;">${idx + 1}</td>
      <td style="text-align:center;"><span class="badge badge-gray">${it.tahun}</span></td>
      <td><strong>${it.title}</strong></td>
      <td><span class="badge badge-blue">${it.mod}</span></td>
      <td>
        ${it.link_drive ? `
          <a href="${it.link_drive}" target="_blank" rel="noopener noreferrer" class="file-attachment" style="color:#00875a;font-weight:600;" title="Buka tautan Google Drive">
            ${it.file} <span style="font-size:11px;">(Google Drive)</span>
          </a>
        ` : `
          <a href="#" class="file-attachment" onclick="downloadFile('${it.file}', '${it.type}', ${it.id}); return false;">
            ${it.file}
          </a>
        `}
      </td>
      <td style="text-align:center;">${formatDate(it.date)}</td>
      <td style="text-align:center;">${getStatusBadge(it.status)}</td>
      <td style="text-align:center;">
        <div class="btn-action-group" style="justify-content:center;">
          <button class="btn btn-outline btn-sm" onclick="openDetailModal('${it.type}', '${it.modKey}', ${it.id})">Detail</button>
          <button class="btn btn-primary btn-sm" onclick="downloadFile('${it.file}', '${it.type}', ${it.id})">Unduh</button>
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
function downloadFile(filename, type, id) {
  if (id) {
    if (type === 'surat') {
      window.location.href = `/surat/download/${id}`;
      return;
    } else {
      window.location.href = `/dokumen/download/${id}`;
      return;
    }
  }
  showToast(`Memulai proses pengunduhan: ${filename}`, 'info');
}
window.downloadFile = downloadFile;

// Modal System
function openModal(id) {
  const modal = typeof id === 'string' ? document.getElementById(id) : id;
  if (modal) {
    modal.classList.add('show');
    modal.classList.add('active');
    modal.style.display = 'flex';
  }
}
window.openModal = openModal;

function closeModal(id) {
  const modal = typeof id === 'string' ? document.getElementById(id) : id;
  if (modal) {
    modal.classList.remove('show');
    modal.classList.remove('active');
    modal.style.display = 'none';
  }
}
window.closeModal = closeModal;

// Tutup popup modal saat pengguna mengklik di luar area dialog (pada backdrop/overlay)
document.addEventListener('click', function (e) {
  if (e.target && e.target.classList && e.target.classList.contains('modal-overlay')) {
    closeModal(e.target);
  }
});

// Tutup popup modal saat menekan tombol Escape di keyboard
document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.show, .modal-overlay.active, .modal-overlay[style*="display: flex"]').forEach(m => {
      closeModal(m);
    });
  }
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
    if (dlBtn) dlBtn.onclick = () => downloadFile(item.file, 'dokumen', item.id);
  } else {
    const isMasuk = moduleKey === 'surat-masuk';
    if (titleEl) titleEl.textContent = isMasuk ? 'DETAIL AGENDA SURAT MASUK' : 'DETAIL AGENDA SURAT KELUAR';
    content.innerHTML = `
      <table class="gov-table" style="margin:0;">
        <tr><td style="width:30%;background:#edf2f7;font-weight:700;">Nomor Surat</td><td><strong>${item.nomor}</strong></td></tr>
        <tr><td style="background:#edf2f7;font-weight:700;">Tanggal Surat</td><td>${formatDate(item.tanggal)}</td></tr>
        <tr><td style="background:#edf2f7;font-weight:700;">Perihal Surat</td><td>${item.perihal}</td></tr>
        <tr><td style="background:#edf2f7;font-weight:700;">${isMasuk ? 'Asal Instansi' : 'Tujuan Instansi'}</td><td>${item.instansi || item.pengirim || '-'}</td></tr>
        <tr><td style="background:#edf2f7;font-weight:700;">${isMasuk ? 'Disposisi' : 'Penandatangan'}</td><td>${item.extra || '-'}</td></tr>
        <tr><td style="background:#edf2f7;font-weight:700;">Berkas Scan</td><td>${item.file}</td></tr>
        <tr><td style="background:#edf2f7;font-weight:700;">Status Agenda</td><td>${getStatusBadge(item.status || 'Tercatat')}</td></tr>
      </table>
    `;
    if (dlBtn) dlBtn.onclick = () => downloadFile(item.file, 'surat', item.id);
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

  const chartData = [
    db['renja-murni']?.length || 0,
    db['renja-perubahan']?.length || 0,
    db['pk-murni']?.length || 0,
    db['pk-perubahan']?.length || 0,
    db['dpa-murni']?.length || 0,
    db['dpa-perubahan']?.length || 0,
    db['surat-masuk']?.length || 0,
    db['surat-keluar']?.length || 0
  ];

  chartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Renja Murni', 'Renja Perub.', 'PK Murni', 'PK Perub.', 'DPA Murni', 'DPA Perub.', 'Surat Masuk', 'Surat Keluar'],
      datasets: [{
        label: 'Total Berkas Tersedia T.A. 2026',
        data: chartData,
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
        borderRadius: 4
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
          ticks: {
            precision: 0,
            font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 }
          },
          grid: { color: '#e2e8f0' }
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

/* ==========================================================================
   MODUL CAPAIAN KINERJA PORTAL (e-SAKIP & BUKTI PENDUKUNG)
   ========================================================================== */
let rawServerCapaian = window.serverCapaianDb;
if (rawServerCapaian && typeof rawServerCapaian === 'object' && !Array.isArray(rawServerCapaian)) {
  rawServerCapaian = Object.values(rawServerCapaian);
}
let capaianDb = (rawServerCapaian !== undefined && Array.isArray(rawServerCapaian))
  ? rawServerCapaian
  : (window.serverDb ? [] : [
      {
        id: 1,
        tahun: 2026,
        triwulan: 'TW I',
        sasaran: 'Meningkatnya nilai perdagangan dalam dan luar negeri',
        indikator: 'Kontribusi Sektor Perdagangan terhadap PDRB',
        satuan: 'Persentase',
        target_tahunan: 22.12,
        pagu_anggaran: 60940133317.00,
        target_tw1: 22.12,
        target_tw2: 22.12,
        target_tw3: 22.12,
        target_tw4: 22.12,
        realisasi_kinerja: 22.51,
        capaian_kinerja_persen: 101.76,
        predikat_kinerja: 'Sangat Berhasil',
        realisasi_keuangan: 15235033329.00,
        capaian_keuangan_persen: 25.00,
        bukti_link: 'https://drive.google.com/drive/folders/18XtuS2NgJyn8FBHwF_cw09qM4JuhPy7P?usp=sharing',
        bukti_file_path: null,
        bukti_file_name: 'Laporan_PDRB_BPS_TW1.pdf',
        bukti_file_size: '2.4 MB',
        bukti_keterangan: 'Telah diunggah Laporan BPS Triwulan I dan Berita Acara Rekonsiliasi PDRB Dinas.',
        status_bukti: 'Lengkap'
      },
      {
        id: 2,
        tahun: 2026,
        triwulan: 'TW I',
        sasaran: 'Terwujudnya potensi ekspor ke luar negeri',
        indikator: 'Nilai Ekspor',
        satuan: 'US$',
        target_tahunan: 34901763.59,
        pagu_anggaran: 370388830.00,
        target_tw1: 8725440.90,
        target_tw2: 8725440.90,
        target_tw3: 8725440.90,
        target_tw4: 8725440.89,
        realisasi_kinerja: 9182027.81,
        capaian_kinerja_persen: 105.23,
        predikat_kinerja: 'Sangat Berhasil',
        realisasi_keuangan: 92500000.00,
        capaian_keuangan_persen: 24.97,
        bukti_link: 'https://drive.google.com/drive/folders/18XtuS2NgJyn8FBHwF_cw09qM4JuhPy7P?usp=sharing',
        bukti_file_path: null,
        bukti_file_name: 'Rekap_Ekspor_BeaCukai_TW1.pdf',
        bukti_file_size: '1.8 MB',
        bukti_keterangan: 'Rekapitulasi resmi data ekspor dari Kantor Wilayah Bea dan Cukai T.A 2026.',
        status_bukti: 'Lengkap'
      },
      {
        id: 3,
        tahun: 2026,
        triwulan: 'TW I',
        sasaran: 'PENGEMBANGAN EKSPOR',
        indikator: 'Persentase pelaku usaha potensial ekspor yang dibina',
        satuan: '%',
        target_tahunan: 14.29,
        pagu_anggaran: 370388830.00,
        target_tw1: 0.00,
        target_tw2: 0.00,
        target_tw3: 7.15,
        target_tw4: 7.14,
        realisasi_kinerja: 0.00,
        capaian_kinerja_persen: 100.00,
        predikat_kinerja: 'Sangat Berhasil',
        realisasi_keuangan: 0.00,
        capaian_keuangan_persen: 0.00,
        bukti_link: '',
        bukti_file_path: null,
        bukti_file_name: null,
        bukti_file_size: null,
        bukti_keterangan: 'Pelaksanaan program pembinaan terjadwal pada Triwulan III dan IV.',
        status_bukti: 'Catatan'
      },
      {
        id: 4,
        tahun: 2026,
        triwulan: 'TW I',
        sasaran: 'Penyelenggaraan Promosi Dagang Melalui Pameran Dagang dan Misi Dagang Ekspor Yang Terdapat Di Luar Daerah',
        indikator: 'Persentase event promosi/atau misi dagang yang diikuti',
        satuan: '%',
        target_tahunan: 100.00,
        pagu_anggaran: 370388830.00,
        target_tw1: 0.00,
        target_tw2: 0.00,
        target_tw3: 0.00,
        target_tw4: 100.00,
        realisasi_kinerja: 0.00,
        capaian_kinerja_persen: 100.00,
        predikat_kinerja: 'Sangat Berhasil',
        realisasi_keuangan: 0.00,
        capaian_keuangan_persen: 0.00,
        bukti_link: 'https://drive.google.com/drive/folders/18XtuS2NgJyn8FBHwF_cw09qM4JuhPy7P?usp=sharing',
        bukti_file_path: null,
        bukti_file_name: 'Jadwal_Misi_Dagang_2026.pdf',
        bukti_file_size: '850 KB',
        bukti_keterangan: 'Event pameran dijadwalkan pada Triwulan IV, SK penugasan telah diterbitkan.',
        status_bukti: 'Ada Link'
      }
    ]);

let customCapaianYears = new Set([2026]);
let activeCapaianTahun = 2026;
let activeCapaianTriwulan = 'TW I';
let capaianSearchQuery = '';

function parseFlexibleNumber(val) {
  if (!val) return 0;
  if (typeof val === 'number') return isNaN(val) ? 0 : val;
  let str = String(val).trim().replace(/%/g, '').replace(/\s/g, '');
  if (str.includes(',') && str.includes('.')) {
    str = str.replace(/\./g, '').replace(',', '.');
  } else if (str.includes(',')) {
    str = str.replace(',', '.');
  }
  const parsed = parseFloat(str);
  return isNaN(parsed) ? 0 : parsed;
}
window.parseFlexibleNumber = parseFlexibleNumber;

function parseRupiahInput(val) {
  if (!val) return 0;
  if (typeof val === 'number') return isNaN(val) ? 0 : val;
  let str = String(val).replace(/[^0-9]/g, '');
  return str ? parseInt(str, 10) : 0;
}
window.parseRupiahInput = parseRupiahInput;

function formatRupiahInputThis(el) {
  if (!el) return;
  const raw = el.value.replace(/[^0-9]/g, '');
  if (!raw) {
    el.value = '';
    return;
  }
  const num = parseInt(raw, 10);
  el.value = num.toLocaleString('id-ID');
}
window.formatRupiahInputThis = formatRupiahInputThis;

function formatRupiahCapaian(val) {
  if (val === null || val === undefined || isNaN(val) || val === '') return '0';
  const num = parseFloat(val);
  return 'Rp ' + num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}
window.formatRupiahCapaian = formatRupiahCapaian;

function formatDesimalCapaian(val) {
  if (val === null || val === undefined || isNaN(val) || val === '') return '0,00';
  const num = parseFloat(val);
  return num.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
window.formatDesimalCapaian = formatDesimalCapaian;

function getPredikatBadgeHtml(predikat) {
  if (!predikat || predikat === '-' || predikat === 'Belum Ada') {
    return '<span style="color:#94a3b8;font-size:11px;font-weight:600;">-</span>';
  }
  let cls = 'kurang';
  const p = String(predikat).toLowerCase();
  if (p.includes('sangat tinggi') || p.includes('sangat berhasil')) cls = 'sangat-berhasil';
  else if (p.includes('tinggi') || p.includes('berhasil')) cls = 'berhasil';
  else if (p.includes('sedang') || p.includes('cukup')) cls = 'cukup';
  else if (p.includes('kurang') || p.includes('rendah')) cls = 'kurang';
  return `<span class="predikat-chip ${cls}" style="white-space:nowrap;font-size:10.5px;padding:2px 8px;">${predikat}</span>`;
}
window.getPredikatBadgeHtml = getPredikatBadgeHtml;

function getPredikatFromPercent(pct) {
  if (pct >= 90) return 'Sangat Berhasil';
  if (pct >= 75) return 'Berhasil';
  if (pct >= 60) return 'Cukup';
  if (pct >= 50) return 'Sedang';
  return 'Kurang';
}
window.getPredikatFromPercent = getPredikatFromPercent;

function populateCapaianYearFilter(selectedYear) {
  const selectEl = document.getElementById('capaianFilterTahun');
  if (!selectEl) return;

  capaianDb.forEach(item => {
    if (item.tahun) customCapaianYears.add(parseInt(item.tahun));
  });

  if (window.serverCapaianYears && Array.isArray(window.serverCapaianYears)) {
    window.serverCapaianYears.forEach(y => customCapaianYears.add(parseInt(y)));
  }

  const sortedYears = Array.from(customCapaianYears).sort((a, b) => b - a);

  if (selectedYear) {
    activeCapaianTahun = parseInt(selectedYear);
  } else if (!activeCapaianTahun || !sortedYears.includes(activeCapaianTahun)) {
    activeCapaianTahun = sortedYears[0] || (new Date()).getFullYear();
  }

  selectEl.innerHTML = '';
  sortedYears.forEach(year => {
    const opt = document.createElement('option');
    opt.value = year;
    opt.textContent = year;
    if (year === activeCapaianTahun) opt.selected = true;
    selectEl.appendChild(opt);
  });
}
window.populateCapaianYearFilter = populateCapaianYearFilter;

function onCapaianFilterChange() {
  const selectTahun = document.getElementById('capaianFilterTahun');
  const selectTW = document.getElementById('capaianFilterTriwulan');
  if (selectTahun) activeCapaianTahun = parseInt(selectTahun.value) || activeCapaianTahun;
  if (selectTW) activeCapaianTriwulan = selectTW.value;
  renderCapaianTable();
}
window.onCapaianFilterChange = onCapaianFilterChange;

function onCapaianSearch(query) {
  capaianSearchQuery = (query || '').toLowerCase().trim();
  renderCapaianTable();
}
window.onCapaianSearch = onCapaianSearch;

function renderBuktiCellUser(row) {
  let html = '<div style="display:flex;flex-direction:column;gap:3px;align-items:center;">';

  if (row.bukti_link) {
    html += `
      <a href="${row.bukti_link}" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:4px;font-size:11px;color:#00875a;font-weight:700;text-decoration:underline;" title="Buka Tautan: ${row.bukti_link}">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
        <span>Tautan Link</span>
      </a>
    `;
  }

  if (row.bukti_file_name || row.bukti_file_path) {
    const fileName = row.bukti_file_name || 'Berkas_Bukti.pdf';
    html += `
      <a href="/portal/capaian-kinerja/download-bukti/${row.id}" target="_blank" style="display:inline-flex;align-items:center;gap:4px;font-size:11px;color:#0284c7;font-weight:700;text-decoration:underline;" title="Unduh: ${fileName}">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        <span>${fileName.length > 15 ? fileName.substring(0, 15) + '...' : fileName}</span>
      </a>
    `;
  }

  html += `
    <button type="button" onclick="event.stopPropagation(); openModalUserCapaian(${row.id}, '${activeCapaianTriwulan}')" style="display:inline-flex;align-items:center;gap:4px;font-size:10.5px;font-weight:700;padding:2px 7px;border-radius:4px;border:1.5px dashed #00875a;color:#00875a;background:#f0fdf4;cursor:pointer;margin-top:2px;" title="Isi / Perbarui Bukti Pendukung">
      + Isi Bukti
    </button>
  `;

  html += '</div>';
  return html;
}
window.renderBuktiCellUser = renderBuktiCellUser;

function renderCapaianTable() {
  const tbody = document.getElementById('tbodyCapaianKinerja');
  const thead = document.getElementById('theadCapaianKinerja');
  if (!tbody || !thead) return;

  // Pastikan sinkron dengan data server jika ada
  if (window.serverCapaianDb) {
    let raw = window.serverCapaianDb;
    if (typeof raw === 'object' && !Array.isArray(raw)) raw = Object.values(raw);
    if (Array.isArray(raw) && (capaianDb.length === 0 || capaianDb !== raw)) {
      capaianDb = raw;
    }
  }

  const selectTahun = document.getElementById('capaianFilterTahun');
  const selectTW = document.getElementById('capaianFilterTriwulan');
  if (selectTahun && selectTahun.value) activeCapaianTahun = parseInt(selectTahun.value) || activeCapaianTahun;
  if (selectTW && selectTW.value) activeCapaianTriwulan = selectTW.value;

  populateCapaianYearFilter(activeCapaianTahun);
  const isSemua = (activeCapaianTriwulan === 'Semua');

  // Atur Header Dual-View
  if (!isSemua) {
    thead.innerHTML = `
      <tr class="head-top">
        <th rowspan="3" style="width:38px;text-align:center;">No</th>
        <th rowspan="3" class="th-left col-text" style="text-align:left;min-width:220px;">Tujuan / Sasaran / Program / Kegiatan / Sub Kegiatan</th>
        <th rowspan="3" class="th-left col-text" style="text-align:left;min-width:200px;">Indikator Kinerja</th>
        <th colspan="3" id="thDataTahun">Data ${activeCapaianTahun}</th>
        <th colspan="4">Target Kinerja</th>
        <th colspan="3" id="thGroupCapaianKinerja">Capaian Kinerja</th>
        <th colspan="2" id="thGroupCapaianKeuangan">Capaian Keuangan</th>
        <th rowspan="3" style="text-align:center;white-space:nowrap;min-width:130px;">Bukti Pendukung</th>
        <th rowspan="3" style="text-align:center;white-space:nowrap;width:1%;">Aksi</th>
      </tr>
      <tr class="head-sub">
        <th rowspan="2" style="text-align:right;">Target</th>
        <th rowspan="2" style="text-align:right;">Rp</th>
        <th rowspan="2" style="text-align:center;">Satuan</th>
        <th rowspan="2" style="text-align:right;">TW I</th>
        <th rowspan="2" style="text-align:right;">TW II</th>
        <th rowspan="2" style="text-align:right;">TW III</th>
        <th rowspan="2" style="text-align:right;">TW IV</th>
        <th colspan="3" style="text-align:center;">${activeCapaianTriwulan}</th>
        <th colspan="2" style="text-align:center;">${activeCapaianTriwulan}</th>
      </tr>
      <tr class="head-detail">
        <th style="text-align:right;">Realisasi</th>
        <th style="text-align:right;">Capaian (%)</th>
        <th style="text-align:center;">Predikat</th>
        <th style="text-align:right;">Realisasi</th>
        <th style="text-align:right;">Capaian (%)</th>
      </tr>
    `;
  } else {
    thead.innerHTML = `
      <tr class="head-top">
        <th rowspan="3" style="width:38px;text-align:center;">No</th>
        <th rowspan="3" class="th-left col-text" style="text-align:left;min-width:220px;">Tujuan / Sasaran / Program / Kegiatan / Sub Kegiatan</th>
        <th rowspan="3" class="th-left col-text" style="text-align:left;min-width:200px;">Indikator Kinerja</th>
        <th colspan="6">Data 2026</th>
        <th colspan="4">Target Kinerja</th>
        <th colspan="15">Capaian Kinerja</th>
        <th colspan="10">Capaian Keuangan</th>
        <th colspan="2">Target Akhir Renstra</th>
        <th colspan="2">Capaian Realisasi Akhir Renstra</th>
        <th rowspan="3" style="text-align:center;white-space:nowrap;min-width:130px;">Bukti Pendukung</th>
        <th rowspan="3" style="text-align:center;white-space:nowrap;width:1%;">Aksi</th>
      </tr>
      <tr class="head-sub">
        <th rowspan="2" style="text-align:right;">Target</th>
        <th colspan="4" style="text-align:center;">Rp</th>
        <th rowspan="2" style="text-align:center;">Satuan</th>
        <th rowspan="2" style="text-align:right;">TW I</th>
        <th rowspan="2" style="text-align:right;">TW II</th>
        <th rowspan="2" style="text-align:right;">TW III</th>
        <th rowspan="2" style="text-align:right;">TW IV</th>
        <th colspan="3" style="text-align:center;">TW I</th>
        <th colspan="3" style="text-align:center;">TW II</th>
        <th colspan="3" style="text-align:center;">TW III</th>
        <th colspan="3" style="text-align:center;">TW IV</th>
        <th colspan="3" style="text-align:center;background:#fff1f2;">Total 2026</th>
        <th colspan="2" style="text-align:center;">TW I</th>
        <th colspan="2" style="text-align:center;">TW II</th>
        <th colspan="2" style="text-align:center;">TW III</th>
        <th colspan="2" style="text-align:center;">TW IV</th>
        <th colspan="2" style="text-align:center;background:#eff6ff;">Total 2026</th>
        <th rowspan="2" style="text-align:right;">Kinerja</th>
        <th rowspan="2" style="text-align:right;">Rp</th>
        <th rowspan="2" style="text-align:right;">Kinerja</th>
        <th rowspan="2" style="text-align:right;">Rp</th>
      </tr>
      <tr class="head-detail">
        <th style="text-align:right;">TW I</th>
        <th style="text-align:right;">TW II</th>
        <th style="text-align:right;">TW III</th>
        <th style="text-align:right;">TW IV</th>
        <th style="text-align:right;">Real.</th>
        <th style="text-align:right;">%</th>
        <th style="text-align:center;">Predikat</th>
        <th style="text-align:right;">Real.</th>
        <th style="text-align:right;">%</th>
        <th style="text-align:center;">Predikat</th>
        <th style="text-align:right;">Real.</th>
        <th style="text-align:right;">%</th>
        <th style="text-align:center;">Predikat</th>
        <th style="text-align:right;">Real.</th>
        <th style="text-align:right;">%</th>
        <th style="text-align:center;">Predikat</th>
        <th style="text-align:right;background:#fff1f2;">Real.</th>
        <th style="text-align:right;background:#fff1f2;">%</th>
        <th style="text-align:center;background:#fff1f2;">Predikat</th>
        <th style="text-align:right;">Real.</th>
        <th style="text-align:right;">%</th>
        <th style="text-align:right;">Real.</th>
        <th style="text-align:right;">%</th>
        <th style="text-align:right;">Real.</th>
        <th style="text-align:right;">%</th>
        <th style="text-align:right;">Real.</th>
        <th style="text-align:right;">%</th>
        <th style="text-align:right;background:#eff6ff;">Real.</th>
        <th style="text-align:right;background:#eff6ff;">%</th>
      </tr>
      <tr class="head-num" style="background:#f8fafc;color:#64748b;font-size:10px;font-weight:700;text-align:center;">
        <td>(1)</td><td>(2)</td><td>(3)</td><td>(4)</td><td>(5)</td><td>(6)</td><td>(7)</td><td>(8)</td><td>(9)</td>
        <td>(10)</td><td>(11)</td><td>(12)</td><td>(13)</td><td>(14)</td><td>(15)</td><td>(16)</td><td>(17)</td><td>(18)</td>
        <td>(19)</td><td>(20)</td><td>(21)</td><td>(22)</td><td>(23)</td><td>(24)</td><td>(25)</td><td>(26)</td><td>(27)</td>
        <td>(28)</td><td>(29)</td><td>(30)</td><td>(31)</td><td>(32)</td><td>(33)</td><td>(34)</td><td>(35)</td><td>(36)</td>
        <td>(37)</td><td>(38)</td><td>(39)</td><td>(40)</td><td>(41)</td><td>(42)</td><td>(43)</td><td>(44)</td>
      </tr>
    `;
  }

  // Filter Data
  const filtered = capaianDb.filter(item => {
    const matchTahun = parseInt(item.tahun) === parseInt(activeCapaianTahun);
    let matchQuery = true;
    if (capaianSearchQuery) {
      const combined = `${item.sasaran || ''} ${item.indikator || ''} ${item.satuan || ''} ${item.bukti_keterangan || ''}`.toLowerCase();
      matchQuery = combined.includes(capaianSearchQuery);
    }
    return matchTahun && matchQuery;
  });

  if (filtered.length === 0) {
    const emptyColspan = isSemua ? 44 : 17;
    tbody.innerHTML = `
      <tr>
        <td colspan="${emptyColspan}" style="text-align:center;padding:36px 20px;color:#64748b;">
          <div style="font-size:32px;margin-bottom:8px;">📊</div>
          <div style="font-weight:700;font-size:14px;color:#1e293b;">Belum ada data capaian kinerja untuk Tahun ${activeCapaianTahun}</div>
        </td>
      </tr>
    `;
    return;
  }

  let html = '';
  filtered.forEach((row, index) => {
    try {
      // Pagu TW 1..4
    const p1 = parseFloat(row.pagu_tw1) > 0 ? parseFloat(row.pagu_tw1) : (parseFloat(row.pagu_anggaran) > 0 ? parseFloat(row.pagu_anggaran) / 4 : 0);
    const p2 = parseFloat(row.pagu_tw2) > 0 ? parseFloat(row.pagu_tw2) : (parseFloat(row.pagu_anggaran) > 0 ? parseFloat(row.pagu_anggaran) / 4 : 0);
    const p3 = parseFloat(row.pagu_tw3) > 0 ? parseFloat(row.pagu_tw3) : (parseFloat(row.pagu_anggaran) > 0 ? parseFloat(row.pagu_anggaran) / 4 : 0);
    const p4 = parseFloat(row.pagu_tw4) > 0 ? parseFloat(row.pagu_tw4) : (parseFloat(row.pagu_anggaran) > 0 ? parseFloat(row.pagu_anggaran) / 4 : 0);

    // Target Fisik TW 1..4
    const t1 = parseFloat(row.target_tw1) || 0;
    const t2 = parseFloat(row.target_tw2) || 0;
    const t3 = parseFloat(row.target_tw3) || 0;
    const t4 = parseFloat(row.target_tw4) || 0;

    // Realisasi Fisik TW 1..4
    const rk1 = parseFloat(row.realisasi_kinerja_tw1 !== undefined ? row.realisasi_kinerja_tw1 : row.realisasi_kinerja) || 0;
    const ck1 = parseFloat(row.capaian_kinerja_tw1 !== undefined && row.capaian_kinerja_tw1 > 0 ? row.capaian_kinerja_tw1 : (t1 > 0 ? (rk1 / t1) * 100 : row.capaian_kinerja_persen)) || 0;
    const pk1 = row.predikat_kinerja_tw1 || (rk1 > 0 ? row.predikat_kinerja : '-');

    const rk2 = parseFloat(row.realisasi_kinerja_tw2) || 0;
    const ck2 = parseFloat(row.capaian_kinerja_tw2 !== undefined && row.capaian_kinerja_tw2 > 0 ? row.capaian_kinerja_tw2 : (t2 > 0 ? (rk2 / t2) * 100 : 0)) || 0;
    const pk2 = row.predikat_kinerja_tw2 || (rk2 > 0 ? getPredikatFromPercent(ck2) : '-');

    const rk3 = parseFloat(row.realisasi_kinerja_tw3) || 0;
    const ck3 = parseFloat(row.capaian_kinerja_tw3 !== undefined && row.capaian_kinerja_tw3 > 0 ? row.capaian_kinerja_tw3 : (t3 > 0 ? (rk3 / t3) * 100 : 0)) || 0;
    const pk3 = row.predikat_kinerja_tw3 || (rk3 > 0 ? getPredikatFromPercent(ck3) : '-');

    const rk4 = parseFloat(row.realisasi_kinerja_tw4) || 0;
    const ck4 = parseFloat(row.capaian_kinerja_tw4 !== undefined && row.capaian_kinerja_tw4 > 0 ? row.capaian_kinerja_tw4 : (t4 > 0 ? (rk4 / t4) * 100 : 0)) || 0;
    const pk4 = row.predikat_kinerja_tw4 || (rk4 > 0 ? getPredikatFromPercent(ck4) : '-');

    const rkTot = parseFloat(row.realisasi_kinerja_total !== undefined && row.realisasi_kinerja_total > 0 ? row.realisasi_kinerja_total : (rk1 + rk2 + rk3 + rk4)) || 0;
    const ckTot = parseFloat(row.capaian_kinerja_total !== undefined && row.capaian_kinerja_total > 0 ? row.capaian_kinerja_total : (parseFloat(row.target_tahunan) > 0 ? (rkTot / parseFloat(row.target_tahunan)) * 100 : row.capaian_kinerja_persen)) || 0;
    const pkTot = row.predikat_kinerja_total || (rkTot > 0 ? row.predikat_kinerja : '-');

    // Realisasi Keuangan TW 1..4 (Dihitung dari Pagu Anggaran Total: Realisasi / Pagu * 100%)
    const totalPaguHitung = parseFloat(row.pagu_anggaran) > 0 ? parseFloat(row.pagu_anggaran) : (p1 + p2 + p3 + p4);
    const rq1 = parseFloat(row.realisasi_keuangan_tw1 !== undefined && row.realisasi_keuangan_tw1 > 0 ? row.realisasi_keuangan_tw1 : row.realisasi_keuangan) || 0;
    const cq1 = totalPaguHitung > 0 ? (rq1 / totalPaguHitung) * 100 : (parseFloat(row.capaian_keuangan_tw1) || 0);

    const rq2 = parseFloat(row.realisasi_keuangan_tw2) || 0;
    const cq2 = totalPaguHitung > 0 ? (rq2 / totalPaguHitung) * 100 : (parseFloat(row.capaian_keuangan_tw2) || 0);

    const rq3 = parseFloat(row.realisasi_keuangan_tw3) || 0;
    const cq3 = totalPaguHitung > 0 ? (rq3 / totalPaguHitung) * 100 : (parseFloat(row.capaian_keuangan_tw3) || 0);

    const rq4 = parseFloat(row.realisasi_keuangan_tw4) || 0;
    const cq4 = totalPaguHitung > 0 ? (rq4 / totalPaguHitung) * 100 : (parseFloat(row.capaian_keuangan_tw4) || 0);

    const rqTot = parseFloat(row.realisasi_keuangan_total !== undefined && row.realisasi_keuangan_total > 0 ? row.realisasi_keuangan_total : (rq1 + rq2 + rq3 + rq4)) || 0;
    const cqTot = totalPaguHitung > 0 ? (rqTot / totalPaguHitung) * 100 : (parseFloat(row.capaian_keuangan_total) || 0);

    if (!isSemua) {
      // FORMAT SINGLE TRIWULAN (17 KOLOM)
      let rkCur = 0, ckCur = 0, pkCur = '-';
      let rqCur = 0, cqCur = 0;

      if (activeCapaianTriwulan === 'TW I') {
        rkCur = rk1; ckCur = ck1; pkCur = pk1;
        rqCur = rq1; cqCur = cq1;
      } else if (activeCapaianTriwulan === 'TW II') {
        rkCur = rk2; ckCur = ck2; pkCur = pk2;
        rqCur = rq2; cqCur = cq2;
      } else if (activeCapaianTriwulan === 'TW III') {
        rkCur = rk3; ckCur = ck3; pkCur = pk3;
        rqCur = rq3; cqCur = cq3;
      } else if (activeCapaianTriwulan === 'TW IV') {
        rkCur = rk4; ckCur = ck4; pkCur = pk4;
        rqCur = rq4; cqCur = cq4;
      }

      html += `
        <tr data-capaian-id="${row.id}" onclick="openModalUserCapaian(${row.id}, '${activeCapaianTriwulan}')" style="cursor:pointer;" title="Klik untuk mengisi / mengedit Realisasi Capaian Kinerja & Bukti">
          <td style="text-align:center;font-weight:700;color:#64748b;">${index + 1}</td>
          <td class="col-text" style="font-weight:700;color:#1e293b;line-height:1.4;min-width:220px;">${row.sasaran || '-'}</td>
          <td class="col-text" style="color:#334155;line-height:1.4;min-width:200px;">${row.indikator || '-'}</td>
          <td style="text-align:right;font-weight:600;white-space:nowrap;">${formatDesimalCapaian(row.target_tahunan)}</td>
          <td style="text-align:right;font-weight:600;color:#1e3a8a;white-space:nowrap;">${formatRupiahCapaian(row.pagu_anggaran)}</td>
          <td style="text-align:center;font-weight:600;white-space:nowrap;">${row.satuan || '-'}</td>
          <td style="text-align:right;white-space:nowrap;">${formatDesimalCapaian(row.target_tw1)}</td>
          <td style="text-align:right;white-space:nowrap;">${formatDesimalCapaian(row.target_tw2)}</td>
          <td style="text-align:right;white-space:nowrap;">${formatDesimalCapaian(row.target_tw3)}</td>
          <td style="text-align:right;white-space:nowrap;">${formatDesimalCapaian(row.target_tw4)}</td>
          <td style="text-align:right;font-weight:700;color:#c2410c;white-space:nowrap;">${rkCur > 0 ? formatDesimalCapaian(rkCur) : '-'}</td>
          <td style="text-align:right;font-weight:700;color:#b91c1c;white-space:nowrap;">${(rkCur > 0 || ckCur > 0) ? formatDesimalCapaian(ckCur) + '%' : '-'}</td>
          <td style="text-align:center;white-space:nowrap;">${getPredikatBadgeHtml(pkCur)}</td>
          <td style="text-align:right;font-weight:700;color:#1e40af;white-space:nowrap;">${rqCur > 0 ? formatRupiahCapaian(rqCur) : '-'}</td>
          <td style="text-align:right;font-weight:700;color:#1d4ed8;white-space:nowrap;">${(rqCur > 0 || cqCur > 0) ? formatDesimalCapaian(cqCur) + '%' : '-'}</td>
          <td style="text-align:center;white-space:nowrap;padding:6px 8px;" onclick="event.stopPropagation()">${renderBuktiCellUser(row)}</td>
          <td style="text-align:center;white-space:nowrap;" onclick="event.stopPropagation()">
            <button type="button" class="agenda-btn edit" onclick="openModalUserCapaian(${row.id}, '${activeCapaianTriwulan}')" title="Isi / Sunting Realisasi Kinerja & Keuangan" style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:6px;border:1px solid #cbd5e1;background:#ffffff;cursor:pointer;color:#00875a;">
              <svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" stroke-width="2" fill="none"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
            </button>
          </td>
        </tr>
      `;
    } else {
      // FORMAT LENGKAP "SEMUA / TAHUNAN" (42 KOLOM)
      const rpjmdKin = parseFloat(row.target_rpjmd_kinerja) || 0;
      const rpjmdKeu = parseFloat(row.target_rpjmd_keuangan) || 0;
      const cRenKin = parseFloat(row.capaian_renstra_kinerja !== undefined && row.capaian_renstra_kinerja > 0 ? row.capaian_renstra_kinerja : (rpjmdKin > 0 ? (rkTot / rpjmdKin) * 100 : 0)) || 0;
      const cRenKeu = parseFloat(row.capaian_renstra_keuangan !== undefined && row.capaian_renstra_keuangan > 0 ? row.capaian_renstra_keuangan : (rpjmdKeu > 0 ? (rqTot / rpjmdKeu) * 100 : 0)) || 0;

      html += `
        <tr data-capaian-id="${row.id}" onclick="openModalUserCapaian(${row.id})" style="cursor:pointer;" title="Klik untuk mengisi / mengedit Realisasi Capaian Kinerja & Bukti">
          <td style="text-align:center;font-weight:700;color:#64748b;">${index + 1}</td>
          <td class="col-text" style="font-weight:700;color:#1e293b;line-height:1.4;min-width:220px;">${row.sasaran || '-'}</td>
          <td class="col-text" style="color:#334155;line-height:1.4;min-width:200px;">${row.indikator || '-'}</td>
          <td style="text-align:right;font-weight:600;white-space:nowrap;">${formatDesimalCapaian(row.target_tahunan)}</td>
          <td style="text-align:right;font-weight:600;color:#1e3a8a;white-space:nowrap;">${p1 > 0 ? formatRupiahCapaian(p1) : '-'}</td>
          <td style="text-align:right;font-weight:600;color:#1e3a8a;white-space:nowrap;">${p2 > 0 ? formatRupiahCapaian(p2) : '-'}</td>
          <td style="text-align:right;font-weight:600;color:#1e3a8a;white-space:nowrap;">${p3 > 0 ? formatRupiahCapaian(p3) : '-'}</td>
          <td style="text-align:right;font-weight:600;color:#1e3a8a;white-space:nowrap;">${p4 > 0 ? formatRupiahCapaian(p4) : '-'}</td>
          <td style="text-align:center;font-weight:600;white-space:nowrap;">${row.satuan || '-'}</td>
          <td style="text-align:right;white-space:nowrap;">${formatDesimalCapaian(row.target_tw1)}</td>
          <td style="text-align:right;white-space:nowrap;">${formatDesimalCapaian(row.target_tw2)}</td>
          <td style="text-align:right;white-space:nowrap;">${formatDesimalCapaian(row.target_tw3)}</td>
          <td style="text-align:right;white-space:nowrap;">${formatDesimalCapaian(row.target_tw4)}</td>
          <td style="text-align:right;font-weight:600;color:#c2410c;white-space:nowrap;">${rk1 > 0 ? formatDesimalCapaian(rk1) : '-'}</td>
          <td style="text-align:right;font-weight:700;color:#b91c1c;white-space:nowrap;">${(rk1 > 0 || ck1 > 0) ? formatDesimalCapaian(ck1) + '%' : '-'}</td>
          <td style="text-align:center;white-space:nowrap;">${getPredikatBadgeHtml(pk1)}</td>
          <td style="text-align:right;font-weight:600;color:#166534;white-space:nowrap;">${rk2 > 0 ? formatDesimalCapaian(rk2) : '-'}</td>
          <td style="text-align:right;font-weight:700;color:#15803d;white-space:nowrap;">${(rk2 > 0 || ck2 > 0) ? formatDesimalCapaian(ck2) + '%' : '-'}</td>
          <td style="text-align:center;white-space:nowrap;">${getPredikatBadgeHtml(pk2)}</td>
          <td style="text-align:right;font-weight:600;color:#0369a1;white-space:nowrap;">${rk3 > 0 ? formatDesimalCapaian(rk3) : '-'}</td>
          <td style="text-align:right;font-weight:700;color:#0284c7;white-space:nowrap;">${(rk3 > 0 || ck3 > 0) ? formatDesimalCapaian(ck3) + '%' : '-'}</td>
          <td style="text-align:center;white-space:nowrap;">${getPredikatBadgeHtml(pk3)}</td>
          <td style="text-align:right;font-weight:600;color:#6b21a8;white-space:nowrap;">${rk4 > 0 ? formatDesimalCapaian(rk4) : '-'}</td>
          <td style="text-align:right;font-weight:700;color:#7e22ce;white-space:nowrap;">${(rk4 > 0 || ck4 > 0) ? formatDesimalCapaian(ck4) + '%' : '-'}</td>
          <td style="text-align:center;white-space:nowrap;">${getPredikatBadgeHtml(pk4)}</td>
          <td style="text-align:right;font-weight:800;color:#991b1b;background:#fff1f2;white-space:nowrap;">${rkTot > 0 ? formatDesimalCapaian(rkTot) : '-'}</td>
          <td style="text-align:right;font-weight:800;color:#b91c1c;background:#fff1f2;white-space:nowrap;">${(rkTot > 0 || ckTot > 0) ? formatDesimalCapaian(ckTot) + '%' : '-'}</td>
          <td style="text-align:center;background:#fff1f2;white-space:nowrap;">${getPredikatBadgeHtml(pkTot)}</td>
          <td style="text-align:right;font-weight:600;white-space:nowrap;">${rq1 > 0 ? formatRupiahCapaian(rq1) : '-'}</td>
          <td style="text-align:right;font-weight:700;color:#c2410c;white-space:nowrap;">${(rq1 > 0 || cq1 > 0) ? formatDesimalCapaian(cq1) + '%' : '-'}</td>
          <td style="text-align:right;font-weight:600;white-space:nowrap;">${rq2 > 0 ? formatRupiahCapaian(rq2) : '-'}</td>
          <td style="text-align:right;font-weight:700;color:#166534;white-space:nowrap;">${(rq2 > 0 || cq2 > 0) ? formatDesimalCapaian(cq2) + '%' : '-'}</td>
          <td style="text-align:right;font-weight:600;white-space:nowrap;">${rq3 > 0 ? formatRupiahCapaian(rq3) : '-'}</td>
          <td style="text-align:right;font-weight:700;color:#0369a1;white-space:nowrap;">${(rq3 > 0 || cq3 > 0) ? formatDesimalCapaian(cq3) + '%' : '-'}</td>
          <td style="text-align:right;font-weight:600;white-space:nowrap;">${rq4 > 0 ? formatRupiahCapaian(rq4) : '-'}</td>
          <td style="text-align:right;font-weight:700;color:#6b21a8;white-space:nowrap;">${(rq4 > 0 || cq4 > 0) ? formatDesimalCapaian(cq4) + '%' : '-'}</td>
          <td style="text-align:right;font-weight:800;color:#1e3a8a;background:#eff6ff;white-space:nowrap;">${rqTot > 0 ? formatRupiahCapaian(rqTot) : '-'}</td>
          <td style="text-align:right;font-weight:800;color:#1d4ed8;background:#eff6ff;white-space:nowrap;">${(rqTot > 0 || cqTot > 0) ? formatDesimalCapaian(cqTot) + '%' : '-'}</td>
          <td style="text-align:right;font-weight:600;white-space:nowrap;">${rpjmdKin > 0 ? formatDesimalCapaian(rpjmdKin) : '-'}</td>
          <td style="text-align:right;font-weight:600;color:#1e3a8a;white-space:nowrap;">${rpjmdKeu > 0 ? formatRupiahCapaian(rpjmdKeu) : '-'}</td>
          <td style="text-align:right;font-weight:700;color:#0f766e;white-space:nowrap;">${cRenKin > 0 ? formatDesimalCapaian(cRenKin) + '%' : '-'}</td>
          <td style="text-align:right;font-weight:700;color:#0f766e;white-space:nowrap;">${cRenKeu > 0 ? formatDesimalCapaian(cRenKeu) + '%' : '-'}</td>
          <td style="text-align:center;white-space:nowrap;padding:6px 8px;" onclick="event.stopPropagation()">${renderBuktiCellUser(row)}</td>
          <td style="text-align:center;white-space:nowrap;" onclick="event.stopPropagation()">
            <button type="button" class="agenda-btn edit" onclick="openModalUserCapaian(${row.id})" title="Isi / Sunting Realisasi Kinerja & Keuangan" style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:6px;border:1px solid #cbd5e1;background:#ffffff;cursor:pointer;color:#00875a;">
              <svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" stroke-width="2" fill="none"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
            </button>
          </td>
        </tr>
      `;
    }
    } catch (rowErr) {
      console.error('Error rendering capaian row:', rowErr, row);
    }
  });

  tbody.innerHTML = html;
}
window.renderCapaianTable = renderCapaianTable;

// Muat Realisasi Triwulan ke Form Modal Pengguna
function loadUserTriwulanRealisasiIntoForm(item, tw) {
  const setSafe = (elemId, val) => {
    const el = document.getElementById(elemId);
    if (el) el.value = (val !== null && val !== undefined) ? val : '';
  };
  const fmtRupiahField = (val) => {
    if (!val || parseFloat(val) <= 0) return '';
    return parseFloat(val).toLocaleString('id-ID');
  };

  if (tw === 'TW I') {
    const rk = item.realisasi_kinerja_tw1 !== undefined ? item.realisasi_kinerja_tw1 : (item.realisasi_kinerja || '');
    const ck = item.capaian_kinerja_tw1 !== undefined ? item.capaian_kinerja_tw1 : (item.capaian_kinerja_persen || '');
    const pk = item.predikat_kinerja_tw1 || item.predikat_kinerja || '';
    const rq = item.realisasi_keuangan_tw1 !== undefined ? item.realisasi_keuangan_tw1 : (item.realisasi_keuangan || '');
    const cq = item.capaian_keuangan_tw1 !== undefined ? item.capaian_keuangan_tw1 : (item.capaian_keuangan_persen || '');
    setSafe('userCrudRealisasiKinerja', rk);
    setSafe('userCrudPersenKinerja', ck ? parseFloat(ck).toFixed(2) : '');
    setSafe('userCrudPredikat', pk || 'Sangat Berhasil');
    setSafe('userCrudRealisasiKeuangan', fmtRupiahField(rq));
    setSafe('userCrudPersenKeuangan', cq ? parseFloat(cq).toFixed(2) : '');
  } else if (tw === 'TW II') {
    setSafe('userCrudRealisasiKinerja', item.realisasi_kinerja_tw2 || '');
    setSafe('userCrudPersenKinerja', item.capaian_kinerja_tw2 ? parseFloat(item.capaian_kinerja_tw2).toFixed(2) : '');
    setSafe('userCrudPredikat', item.predikat_kinerja_tw2 || 'Sangat Berhasil');
    setSafe('userCrudRealisasiKeuangan', fmtRupiahField(item.realisasi_keuangan_tw2));
    setSafe('userCrudPersenKeuangan', item.capaian_keuangan_tw2 ? parseFloat(item.capaian_keuangan_tw2).toFixed(2) : '');
  } else if (tw === 'TW III') {
    setSafe('userCrudRealisasiKinerja', item.realisasi_kinerja_tw3 || '');
    setSafe('userCrudPersenKinerja', item.capaian_kinerja_tw3 ? parseFloat(item.capaian_kinerja_tw3).toFixed(2) : '');
    setSafe('userCrudPredikat', item.predikat_kinerja_tw3 || 'Sangat Berhasil');
    setSafe('userCrudRealisasiKeuangan', fmtRupiahField(item.realisasi_keuangan_tw3));
    setSafe('userCrudPersenKeuangan', item.capaian_keuangan_tw3 ? parseFloat(item.capaian_keuangan_tw3).toFixed(2) : '');
  } else if (tw === 'TW IV') {
    setSafe('userCrudRealisasiKinerja', item.realisasi_kinerja_tw4 || '');
    setSafe('userCrudPersenKinerja', item.capaian_kinerja_tw4 ? parseFloat(item.capaian_kinerja_tw4).toFixed(2) : '');
    setSafe('userCrudPredikat', item.predikat_kinerja_tw4 || 'Sangat Berhasil');
    setSafe('userCrudRealisasiKeuangan', fmtRupiahField(item.realisasi_keuangan_tw4));
    setSafe('userCrudPersenKeuangan', item.capaian_keuangan_tw4 ? parseFloat(item.capaian_keuangan_tw4).toFixed(2) : '');
  }

  setSafe('userCrudBuktiLink', item.bukti_link || '');
  setSafe('userCrudBuktiKeterangan', item.bukti_keterangan || '');

  const existFile = document.getElementById('userCrudExistingFile');
  if (existFile) {
    if (item.bukti_file_name) {
      existFile.style.display = 'block';
      existFile.innerHTML = `📁 File tersimpan: <a href="/portal/capaian-kinerja/download-bukti/${item.id}" target="_blank" style="color:#00875a;font-weight:700;text-decoration:underline;">${item.bukti_file_name}</a> ${item.bukti_file_size ? '(' + item.bukti_file_size + ')' : ''}`;
    } else {
      existFile.style.display = 'none';
    }
  }
}
window.loadUserTriwulanRealisasiIntoForm = loadUserTriwulanRealisasiIntoForm;

// Buka Modal Khusus Pengguna (Hanya Input Realisasi Kinerja, Keuangan & Bukti)
function openModalUserCapaian(id, selectedTw) {
  const item = capaianDb.find(x => x.id == id);
  if (!item) return;

  const modal = document.getElementById('modalUserCapaian');
  if (!modal) return;

  const setSafe = (elemId, val) => {
    const el = document.getElementById(elemId);
    if (el) el.value = (val !== null && val !== undefined) ? val : '';
  };
  const setHtml = (elemId, val) => {
    const el = document.getElementById(elemId);
    if (el) el.innerHTML = val || '-';
  };

  setSafe('userCrudId', item.id);
  setHtml('userViewSasaran', item.sasaran);
  setHtml('userViewIndikator', item.indikator);
  setHtml('userViewTargetTahunan', `${formatDesimalCapaian(item.target_tahunan)} ${item.satuan || ''}`);
  setHtml('userViewPagu', formatRupiahCapaian(item.pagu_anggaran));

  setHtml('userViewTw1', formatDesimalCapaian(item.target_tw1));
  setHtml('userViewTw2', formatDesimalCapaian(item.target_tw2));
  setHtml('userViewTw3', formatDesimalCapaian(item.target_tw3));
  setHtml('userViewTw4', formatDesimalCapaian(item.target_tw4));

  let currentTw = selectedTw || (activeCapaianTriwulan === 'Semua' ? 'TW I' : activeCapaianTriwulan);
  const selectTw = document.getElementById('userCrudTriwulan');
  if (selectTw) selectTw.value = currentTw;

  loadUserTriwulanRealisasiIntoForm(item, currentTw);
  onModalUserTriwulanChange();

  modal.classList.add('active');
  modal.classList.add('show');
  modal.style.display = 'flex';
}
window.openModalUserCapaian = openModalUserCapaian;

function onModalUserTriwulanChange() {
  const tw = document.getElementById('userCrudTriwulan')?.value || 'TW I';
  const labelHeader = document.getElementById('labelUserTriwulanHeader');
  const labelFisik = document.getElementById('labelUserRealFisik');
  const labelKeu = document.getElementById('labelUserRealKeu');
  if (labelHeader) labelHeader.textContent = `Input Realisasi & Perhitungan Otomatis (${tw})`;
  if (labelFisik) labelFisik.innerHTML = `Realisasi Kinerja Fisik (${tw}) <span style="font-size:11px;color:#9a3412;font-weight:normal;">(Opsional)</span>`;
  if (labelKeu) labelKeu.textContent = `Realisasi Keuangan Rp (${tw})`;

  const id = document.getElementById('userCrudId')?.value;
  if (id) {
    const item = capaianDb.find(x => x.id == id);
    if (item) loadUserTriwulanRealisasiIntoForm(item, tw);
  }
  recalculateUserCapaianForm();
}
window.onModalUserTriwulanChange = onModalUserTriwulanChange;

function recalculateUserCapaianForm() {
  const id = document.getElementById('userCrudId')?.value;
  const item = id ? capaianDb.find(x => x.id == id) : null;
  const tw = document.getElementById('userCrudTriwulan')?.value || 'TW I';

  let targetAktif = 0;
  if (item) {
    if (tw === 'TW I') targetAktif = parseFloat(item.target_tw1) || parseFloat(item.target_tahunan) || 0;
    else if (tw === 'TW II') targetAktif = parseFloat(item.target_tw2) || parseFloat(item.target_tahunan) || 0;
    else if (tw === 'TW III') targetAktif = parseFloat(item.target_tw3) || parseFloat(item.target_tahunan) || 0;
    else if (tw === 'TW IV') targetAktif = parseFloat(item.target_tw4) || parseFloat(item.target_tahunan) || 0;
  }

  const paguTotal = item ? (parseFloat(item.pagu_anggaran) || 0) : 0;
  let paguTw = paguTotal > 0 ? (paguTotal / 4) : 0;
  if (item) {
    if (tw === 'TW I' && parseFloat(item.pagu_tw1) > 0) paguTw = parseFloat(item.pagu_tw1);
    else if (tw === 'TW II' && parseFloat(item.pagu_tw2) > 0) paguTw = parseFloat(item.pagu_tw2);
    else if (tw === 'TW III' && parseFloat(item.pagu_tw3) > 0) paguTw = parseFloat(item.pagu_tw3);
    else if (tw === 'TW IV' && parseFloat(item.pagu_tw4) > 0) paguTw = parseFloat(item.pagu_tw4);
  }

  const elRealKin = document.getElementById('userCrudRealisasiKinerja');
  const elRealKeu = document.getElementById('userCrudRealisasiKeuangan');
  const rKin = parseFlexibleNumber(elRealKin?.value);
  const rKeu = parseRupiahInput(elRealKeu?.value);

  const elPersenKin = document.getElementById('userCrudPersenKinerja');
  const elPredikat = document.getElementById('userCrudPredikat');
  const elPersenKeu = document.getElementById('userCrudPersenKeuangan');

  // % Capaian Fisik
  const cKin = targetAktif > 0 ? (rKin / targetAktif) * 100 : 0;
  if (elPersenKin && document.activeElement !== elPersenKin) {
    elPersenKin.value = (rKin > 0 || targetAktif > 0) ? cKin.toFixed(2) : '';
  }

  // Predikat
  if (elPredikat && document.activeElement !== elPredikat) {
    if (rKin > 0 || targetAktif > 0) {
      elPredikat.value = getPredikatFromPercent(cKin);
    }
  }

  // % Capaian Keuangan: (Realisasi Keuangan / Pagu Anggaran) * 100%
  const cKeu = paguTotal > 0 ? (rKeu / paguTotal) * 100 : 0;
  if (elPersenKeu && document.activeElement !== elPersenKeu) {
    elPersenKeu.value = (rKeu > 0 || paguTotal > 0) ? cKeu.toFixed(2) : '';
  }
}
window.recalculateUserCapaianForm = recalculateUserCapaianForm;

async function handleUserCapaianSubmit(event) {
  event.preventDefault();
  const id = document.getElementById('userCrudId')?.value;
  if (!id) return;

  const item = capaianDb.find(x => x.id == id);
  if (!item) return;

  const tw = document.getElementById('userCrudTriwulan')?.value || 'TW I';
  const curRealKin = parseFlexibleNumber(document.getElementById('userCrudRealisasiKinerja')?.value);
  const curCapKin = parseFlexibleNumber(document.getElementById('userCrudPersenKinerja')?.value);
  const curPredKin = document.getElementById('userCrudPredikat')?.value || getPredikatFromPercent(curCapKin);

  const curRealKeu = parseRupiahInput(document.getElementById('userCrudRealisasiKeuangan')?.value);
  const curCapKeu = parseFlexibleNumber(document.getElementById('userCrudPersenKeuangan')?.value);

  const buktiLink = document.getElementById('userCrudBuktiLink')?.value || '';
  const buktiKeterangan = document.getElementById('userCrudBuktiKeterangan')?.value || '';
  const fileInput = document.getElementById('userCrudBuktiFile');

  // Realisasi Fisik TW 1 - 4
  const rKin1 = tw === 'TW I' ? curRealKin : (parseFloat(item.realisasi_kinerja_tw1) || 0);
  const cKin1 = tw === 'TW I' ? curCapKin : (parseFloat(item.capaian_kinerja_tw1) || 0);
  const pKin1 = tw === 'TW I' ? curPredKin : (item.predikat_kinerja_tw1 || '-');

  const rKin2 = tw === 'TW II' ? curRealKin : (parseFloat(item.realisasi_kinerja_tw2) || 0);
  const cKin2 = tw === 'TW II' ? curCapKin : (parseFloat(item.capaian_kinerja_tw2) || 0);
  const pKin2 = tw === 'TW II' ? curPredKin : (item.predikat_kinerja_tw2 || '-');

  const rKin3 = tw === 'TW III' ? curRealKin : (parseFloat(item.realisasi_kinerja_tw3) || 0);
  const cKin3 = tw === 'TW III' ? curCapKin : (parseFloat(item.capaian_kinerja_tw3) || 0);
  const pKin3 = tw === 'TW III' ? curPredKin : (item.predikat_kinerja_tw3 || '-');

  const rKin4 = tw === 'TW IV' ? curRealKin : (parseFloat(item.realisasi_kinerja_tw4) || 0);
  const cKin4 = tw === 'TW IV' ? curCapKin : (parseFloat(item.capaian_kinerja_tw4) || 0);
  const pKin4 = tw === 'TW IV' ? curPredKin : (item.predikat_kinerja_tw4 || '-');

  // Realisasi Keuangan TW 1 - 4
  const rKeu1 = tw === 'TW I' ? curRealKeu : (parseFloat(item.realisasi_keuangan_tw1) || 0);
  const cKeu1 = tw === 'TW I' ? curCapKeu : (parseFloat(item.capaian_keuangan_tw1) || 0);

  const rKeu2 = tw === 'TW II' ? curRealKeu : (parseFloat(item.realisasi_keuangan_tw2) || 0);
  const cKeu2 = tw === 'TW II' ? curCapKeu : (parseFloat(item.capaian_keuangan_tw2) || 0);

  const rKeu3 = tw === 'TW III' ? curRealKeu : (parseFloat(item.realisasi_keuangan_tw3) || 0);
  const cKeu3 = tw === 'TW III' ? curCapKeu : (parseFloat(item.capaian_keuangan_tw3) || 0);

  const rKeu4 = tw === 'TW IV' ? curRealKeu : (parseFloat(item.realisasi_keuangan_tw4) || 0);
  const cKeu4 = tw === 'TW IV' ? curCapKeu : (parseFloat(item.capaian_keuangan_tw4) || 0);

  const rKinTot = rKin1 + rKin2 + rKin3 + rKin4;
  const cKinTot = parseFloat(item.target_tahunan) > 0 ? (rKinTot / parseFloat(item.target_tahunan)) * 100 : 0;
  const pKinTot = rKinTot > 0 ? getPredikatFromPercent(cKinTot) : '-';

  const rKeuTot = rKeu1 + rKeu2 + rKeu3 + rKeu4;
  const cKeuTot = parseFloat(item.pagu_anggaran) > 0 ? (rKeuTot / parseFloat(item.pagu_anggaran)) * 100 : 0;

  // Kirim ke Backend Laravel
  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  let serverData = null;

  if (csrfMeta && csrfMeta.content) {
    const formData = new FormData();
    formData.append('triwulan', tw);
    formData.append('realisasi_kinerja_tw1', rKin1);
    formData.append('capaian_kinerja_tw1', cKin1);
    formData.append('predikat_kinerja_tw1', pKin1);

    formData.append('realisasi_kinerja_tw2', rKin2);
    formData.append('capaian_kinerja_tw2', cKin2);
    formData.append('predikat_kinerja_tw2', pKin2);

    formData.append('realisasi_kinerja_tw3', rKin3);
    formData.append('capaian_kinerja_tw3', cKin3);
    formData.append('predikat_kinerja_tw3', pKin3);

    formData.append('realisasi_kinerja_tw4', rKin4);
    formData.append('capaian_kinerja_tw4', cKin4);
    formData.append('predikat_kinerja_tw4', pKin4);

    formData.append('realisasi_keuangan_tw1', rKeu1);
    formData.append('capaian_keuangan_tw1', cKeu1);

    formData.append('realisasi_keuangan_tw2', rKeu2);
    formData.append('capaian_keuangan_tw2', cKeu2);

    formData.append('realisasi_keuangan_tw3', rKeu3);
    formData.append('capaian_keuangan_tw3', cKeu3);

    formData.append('realisasi_keuangan_tw4', rKeu4);
    formData.append('capaian_keuangan_tw4', cKeu4);

    formData.append('bukti_link', buktiLink);
    formData.append('bukti_keterangan', buktiKeterangan);
    if (fileInput && fileInput.files && fileInput.files[0]) {
      formData.append('bukti_file', fileInput.files[0]);
    }

    try {
      const res = await fetch(`/portal/capaian-kinerja/${id}/update`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfMeta.content,
          'Accept': 'application/json'
        },
        body: formData
      });
      const resData = await res.json();
      if (resData.success && resData.data) {
        serverData = resData.data;
      }
    } catch (err) {
      console.warn('Portal capaian update notice:', err);
    }
  }

  // Update Data di Memori Lokal
  item.realisasi_kinerja_tw1 = rKin1;
  item.capaian_kinerja_tw1 = cKin1;
  item.predikat_kinerja_tw1 = pKin1;

  item.realisasi_kinerja_tw2 = rKin2;
  item.capaian_kinerja_tw2 = cKin2;
  item.predikat_kinerja_tw2 = pKin2;

  item.realisasi_kinerja_tw3 = rKin3;
  item.capaian_kinerja_tw3 = cKin3;
  item.predikat_kinerja_tw3 = pKin3;

  item.realisasi_kinerja_tw4 = rKin4;
  item.capaian_kinerja_tw4 = cKin4;
  item.predikat_kinerja_tw4 = pKin4;

  item.realisasi_kinerja_total = rKinTot;
  item.capaian_kinerja_total = cKinTot;
  item.predikat_kinerja_total = pKinTot;

  item.realisasi_keuangan_tw1 = rKeu1;
  item.capaian_keuangan_tw1 = cKeu1;

  item.realisasi_keuangan_tw2 = rKeu2;
  item.capaian_keuangan_tw2 = cKeu2;

  item.realisasi_keuangan_tw3 = rKeu3;
  item.capaian_keuangan_tw3 = cKeu3;

  item.realisasi_keuangan_tw4 = rKeu4;
  item.capaian_keuangan_tw4 = cKeu4;

  item.realisasi_keuangan_total = rKeuTot;
  item.capaian_keuangan_total = cKeuTot;

  item.bukti_link = buktiLink;
  item.bukti_keterangan = buktiKeterangan;
  if (fileInput && fileInput.files && fileInput.files[0]) {
    item.bukti_file_name = fileInput.files[0].name;
    item.bukti_file_size = Math.round(fileInput.files[0].size / 1024) + ' KB';
  }
  if (serverData) {
    if (serverData.bukti_file_name) item.bukti_file_name = serverData.bukti_file_name;
    if (serverData.bukti_file_path) item.bukti_file_path = serverData.bukti_file_path;
    if (serverData.bukti_file_size) item.bukti_file_size = serverData.bukti_file_size;
  }

  // Fallback backward compat
  item.realisasi_kinerja = rKinTot;
  item.capaian_kinerja_persen = cKinTot;
  item.predikat_kinerja = pKinTot;
  item.realisasi_keuangan = rKeuTot;
  item.capaian_keuangan_persen = cKeuTot;

  const modal = document.getElementById('modalUserCapaian');
  if (modal) {
    modal.classList.remove('active');
    modal.classList.remove('show');
    modal.style.display = 'none';
  }

  renderCapaianTable();

  if (typeof showToast === 'function') {
    showToast(`Capaian Kinerja & Keuangan (${tw}) berhasil disimpan!`, 'success');
  }
}
window.handleUserCapaianSubmit = handleUserCapaianSubmit;

function exportExcelCapaian() {
  const selectTahun = document.getElementById('capaianFilterTahun');
  const selectTW = document.getElementById('capaianFilterTriwulan');
  const tahun = selectTahun ? selectTahun.value : activeCapaianTahun;
  const tw = selectTW ? selectTW.value : activeCapaianTriwulan;

  window.location.href = `/portal/capaian-kinerja/export-excel?tahun=${tahun}&triwulan=${encodeURIComponent(tw)}`;
}
window.exportExcelCapaian = exportExcelCapaian;

function cetakLaporanCapaian() {
  const selectTahun = document.getElementById('capaianFilterTahun');
  const selectTW = document.getElementById('capaianFilterTriwulan');
  const tahun = selectTahun ? selectTahun.value : (typeof activeCapaianTahun !== 'undefined' ? activeCapaianTahun : '2026');
  const tw = selectTW ? selectTW.value : (typeof activeCapaianTriwulan !== 'undefined' ? activeCapaianTriwulan : 'Semua');
  const baseUrl = (typeof window !== 'undefined' && window.appUrl) ? window.appUrl.replace(/\/$/, '') : '';

  const url = `${baseUrl}/portal/capaian-kinerja/cetak?tahun=${encodeURIComponent(tahun)}&triwulan=${encodeURIComponent(tw)}`;
  window.open(url, '_blank');
}
window.cetakLaporanCapaian = cetakLaporanCapaian;

// ==========================================================================
// BOOTSTRAP INISIALISASI PORTAL
// ==========================================================================
function initPortalApplication() {
  setupTopbarDate();
  renderAllTables();
  initChart();
  setupSearchFilters();
  if (typeof populateCapaianYearFilter === 'function') {
    populateCapaianYearFilter(activeCapaianTahun);
  }
  if (document.getElementById('tbodyCapaianKinerja') && typeof renderCapaianTable === 'function') {
    renderCapaianTable();
  }

  // Handle URL Hash (misal #capaian-kinerja)
  const hash = (window.location.hash || '').replace('#', '').trim();
  if (hash && PAGES[hash]) {
    navigate(hash);
  } else {
    const activePage = document.querySelector('.page.active');
    if (activePage && activePage.id === 'page-capaian-kinerja') {
      if (typeof renderCapaianTable === 'function') renderCapaianTable();
    }
  }
}
window.initPortalApplication = initPortalApplication;

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initPortalApplication);
} else {
  initPortalApplication();
}