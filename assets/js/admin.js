/* ==========================================================================
   SIM-PEP â€” Script Pengelolaan Berkas & Data Dokumen (Admin Workspace)
   Fitur: Full CRUD (Create, Read, Update, Delete), Auto-Name PDF, Export PDF
   ========================================================================== */

const ADMIN_PAGES = {
  dashboard: { title: 'Dashboard Utama Pengelolaan Dokumen', navKey: 'dashboard' },
  'renja-murni': { title: 'Kelola Renja Murni', navKey: 'renja-murni', category: 'Renja Murni', extraLabel: 'Uraian / Target Pagu Dokumen' },
  'renja-perubahan': { title: 'Kelola Renja Perubahan', navKey: 'renja-perubahan', category: 'Renja Perubahan', extraLabel: 'Uraian Perubahan Program' },
  'pk-murni': { title: 'Kelola PK Murni', navKey: 'pk-murni', category: 'PK Murni', extraLabel: 'Sasaran Strategis & Indikator Kinerja' },
  'pk-perubahan': { title: 'Kelola PK Perubahan', navKey: 'pk-perubahan', category: 'PK Perubahan', extraLabel: 'Sasaran Perubahan & Indikator' },
  'dpa-murni': { title: 'Kelola DPA Murni', navKey: 'dpa-murni', category: 'DPA Murni', extraLabel: 'Kode Rekening / Pagu Belanja (Rp)' },
  'dpa-perubahan': { title: 'Kelola DPA Perubahan', navKey: 'dpa-perubahan', category: 'DPA Perubahan', extraLabel: 'Pergeseran Anggaran (Rp)' },
  'surat-masuk': { title: 'Agenda Surat Masuk', navKey: 'surat-masuk', category: 'Surat Masuk', extraLabel: 'Instansi Pengirim & Disposisi Kasubag' },
  'surat-keluar': { title: 'Agenda Surat Keluar', navKey: 'surat-keluar', category: 'Surat Keluar', extraLabel: 'Instansi Tujuan & Penandatangan' },
  laporan: { title: 'Laporan Simdapangda', navKey: 'laporan' },
  'capaian-kinerja': { title: 'Capaian Kinerja OPD (e-SAKIP)', navKey: 'capaian-kinerja' }
};

// Database Utama Dokumen PEP (Sinkron dengan MySQL Backend Laravel)
let adminDb = window.serverAdminDb || {
  'renja-murni': [],
  'renja-perubahan': [],
  'pk-murni': [],
  'pk-perubahan': [],
  'dpa-murni': [],
  'dpa-perubahan': [],
  'surat-masuk': [],
  'surat-keluar': []
};

let currentAdminPage = 'dashboard';
let adminDeleteTarget = null;
let chartAdminInstance = null;
let chartHopeActivityInstance = null;
const selectedDocMap = new Map(); // key: `${moduleKey}_${id}`, value: { id, moduleKey, judul }
window.selectedDocMap = selectedDocMap;

// Helper: Bersihkan nama file agar menjadi judul rapi
function cleanFileName(filename) {
  if (!filename) return '';
  return filename
    .replace(/\.pdf$/i, '')
    .replace(/[_\-]+/g, ' ')
    .trim();
}

function formatDateIndo(dateStr) {
  if (!dateStr) return '-';
  const parts = dateStr.split('-');
  if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
  return dateStr;
}

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

// Navigasi Sidebar Admin
function navigateAdmin(pageKey) {
  if (!ADMIN_PAGES[pageKey]) return;
  try {
    if (typeof clearAllSelections === 'function') clearAllSelections();
  } catch (e) {}
  currentAdminPage = pageKey;

  document.querySelectorAll('.admin-page').forEach(el => el.classList.remove('active'));
  const target = document.getElementById('admin-page-' + pageKey);
  if (target) target.classList.add('active');

  document.querySelectorAll('.admin-nav-item').forEach(item => {
    item.classList.toggle('active', item.dataset.page === pageKey);
  });

  const info = ADMIN_PAGES[pageKey];
  const titleEl = document.getElementById('adminHeaderTitle');
  if (titleEl && info) titleEl.textContent = info.title;

  document.getElementById('adminSidebar')?.classList.remove('open');

  try {
    renderAdminModuleTable(pageKey);
  } catch (err) {
    console.error('Error rendering table for', pageKey, err);
  }

  if (pageKey === 'dashboard') {
    try {
      renderAdminRecentTable();
      initAdminChart();
    } catch (err) {
      console.error('Error rendering dashboard:', err);
    }
  }

  if (pageKey === 'capaian-kinerja') {
    try {
      if (typeof renderCapaianTable === 'function') {
        renderCapaianTable();
      }
    } catch (err) {
      console.error('Error rendering capaian-kinerja:', err);
    }
  }
  window.scrollTo({ top: 0, behavior: 'smooth' });
}
window.navigateAdmin = navigateAdmin;

// Update Statistik Kartu & Badge Sidebar
function updateAdminStats() {
  let totalDoc = 0;
  for (const key in adminDb) {
    const count = (adminDb[key] || []).length;
    totalDoc += count;
    const badge = document.getElementById('badge-count-' + key);
    if (badge) badge.textContent = count;
  }

  const renjaCount = (adminDb['renja-murni']?.length || 0) + (adminDb['renja-perubahan']?.length || 0);
  const pkCount = (adminDb['pk-murni']?.length || 0) + (adminDb['pk-perubahan']?.length || 0);
  const dpaCount = (adminDb['dpa-murni']?.length || 0) + (adminDb['dpa-perubahan']?.length || 0);
  const pkDpaCount = pkCount + dpaCount;
  const suratCount = (adminDb['surat-masuk']?.length || 0) + (adminDb['surat-keluar']?.length || 0);

  // Hope UI Floating Metric Cards
  const mCardRenja = document.getElementById('metricRenjaVal'); if (mCardRenja) mCardRenja.textContent = renjaCount;
  const mCardPk = document.getElementById('metricPkVal'); if (mCardPk) mCardPk.textContent = pkCount;
  const mCardDpa = document.getElementById('metricDpaVal'); if (mCardDpa) mCardDpa.textContent = dpaCount;
  const mCardSurat = document.getElementById('metricSuratVal'); if (mCardSurat) mCardSurat.textContent = suratCount;
  const hopeTotal = document.getElementById('hopeStatTotal'); if (hopeTotal) hopeTotal.textContent = totalDoc;

  if (typeof capaianDb !== 'undefined' && Array.isArray(capaianDb) && capaianDb.length > 0) {
    const valid = capaianDb.filter(c => parseFloat(c.capaian_kinerja_persen) > 0);
    if (valid.length > 0) {
      const avg = Math.round(valid.reduce((acc, c) => acc + (parseFloat(c.capaian_kinerja_persen) || 0), 0) / valid.length * 10) / 10;
      const mCardCapaian = document.getElementById('metricCapaianAvg');
      if (mCardCapaian) mCardCapaian.textContent = avg + '%';
    }
  }

  // Stat Baris Header Dashboard
  const eTotal = document.getElementById('statTotalDoc'); if (eTotal) eTotal.textContent = totalDoc;
  const eRenja = document.getElementById('statRenjaDoc'); if (eRenja) eRenja.textContent = renjaCount;
  const ePkDpa = document.getElementById('statPkDpaDoc'); if (ePkDpa) ePkDpa.textContent = pkDpaCount;
  const eSurat = document.getElementById('statSuratDoc'); if (eSurat) eSurat.textContent = suratCount;

  // Card 2: Folder Berkas Counters
  const fRenja = document.getElementById('fCountRenja'); if (fRenja) fRenja.textContent = `${renjaCount} files`;
  const fPk = document.getElementById('fCountPk'); if (fPk) fPk.textContent = `${pkCount} files`;
  const fDpa = document.getElementById('fCountDpa'); if (fDpa) fDpa.textContent = `${dpaCount} files`;
  const fSurat = document.getElementById('fCountSurat'); if (fSurat) fSurat.textContent = `${suratCount} files`;

  // Card 4: Donut Center Total Dokumen
  const dCenter = document.getElementById('donutCenterTotal'); if (dCenter) dCenter.textContent = totalDoc;

  // Donut Legends
  let countLengkap = 0, countDiproses = 0, countPerluUpdate = 0, countTerkirim = 0;
  for (const key in adminDb) {
    (adminDb[key] || []).forEach(item => {
      const st = (item.status || '').toLowerCase();
      if (st === 'lengkap' || st === 'selesai') countLengkap++;
      else if (st === 'diproses') countDiproses++;
      else if (st === 'perlu update') countPerluUpdate++;
      else if (st === 'terkirim' || st === 'aktif') countTerkirim++;
    });
  }
  const eL = document.getElementById('donutLegendLengkap'); if (eL) eL.textContent = `Lengkap (${countLengkap})`;
  const eD = document.getElementById('donutLegendDiproses'); if (eD) eD.textContent = `Diproses (${countDiproses})`;
  const eP = document.getElementById('donutLegendPerluUpdate'); if (eP) eP.textContent = `Perlu Update (${countPerluUpdate})`;
  const eT = document.getElementById('donutLegendTerkirim'); if (eT) eT.textContent = `Terkirim (${countTerkirim})`;
}

const ICONS = {
  detail: `<svg class="btn-icon-svg" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`,
  edit: `<svg class="btn-icon-svg" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>`,
  download: `<svg class="btn-icon-svg" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>`,
  delete: `<svg class="btn-icon-svg" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>`,
  plus: `<svg class="btn-icon-svg" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>`
};

// ==========================================================================
// RENDER TABEL MODUL (DENGAN TOMBOL CRUD LENGKAP & ICON)
// ==========================================================================
function handleDownloadDoc(moduleKey, id) {
  const item = (adminDb[moduleKey] || []).find(x => x.id == id);
  if (!item) return;

  if (item.download_url) {
    window.open(item.download_url, '_blank');
    return;
  }
  if (item.link_drive) {
    window.open(item.link_drive, '_blank');
    return;
  }
  const isSurat = moduleKey.startsWith('surat');
  const baseUrl = window.appUrl || '';
  const dlUrl = isSurat 
    ? `${baseUrl}/surat/download/${id}`
    : `${baseUrl}/dokumen/download/${id}`;
  window.open(dlUrl, '_blank');
}

function exportPdf(itemOrFilename) {
  if (typeof itemOrFilename === 'string' && itemOrFilename.startsWith('http')) {
    window.open(itemOrFilename, '_blank');
    return;
  }
  showAdminToast(`Memulai proses pengunduhan berkas: ${itemOrFilename}`, 'info');
}

function renderAdminTable(tableId, moduleKey) {
  const tbody = document.querySelector('#' + tableId + ' tbody');
  if (!tbody) return;
  const data = adminDb[moduleKey] || [];

  if (data.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:28px;color:var(--text-muted);">Belum ada data dokumen pada modul ini. Klik tombol "+ Tambah Dokumen" di atas untuk menambah data baru.</td></tr>`;
    return;
  }

  const isSurat = moduleKey.startsWith('surat');

  tbody.innerHTML = data.map((item, idx) => {
    const isSelected = selectedDocMap && selectedDocMap.has(`${moduleKey}_${item.id}`);

    if (isSurat) {
      return `
        <tr data-doc-id="${item.id}" data-mod-key="${moduleKey}" class="${isSelected ? 'row-selected' : ''}">
          <td style="text-align:center;" class="col-checkbox-cell">
            <input type="checkbox" class="gov-checkbox row-checkbox ${isSelected ? 'checked' : ''}" data-doc-id="${item.id}" data-mod-key="${moduleKey}" ${isSelected ? 'checked' : ''} onclick="event.stopPropagation(); toggleRowSelection(this.closest('tr'))">
          </td>
          <td style="text-align:center;">${formatDateIndo(item.tanggal || item.date)}</td>
          <td>
            <strong>${item.nomor || '-'}</strong>
            <div style="font-size:11px;color:var(--text-muted);">${item.pengirim || '-'}</div>
          </td>
          <td>
            <strong>${item.judul}</strong>
            <div style="font-size:11px;color:var(--text-muted);">${item.keterangan || '-'}</div>
          </td>
          <td>
            ${item.link_drive ? `<span class="badge badge-yellow" style="font-size:10px;margin-right:4px;">Drive</span>` : ''}
            <span style="font-family:monospace;font-size:11px;color:var(--primary);">${item.file}</span>
            <span style="font-size:10px;color:var(--text-muted);">(${item.size})</span>
          </td>
          <td style="text-align:center;">${getStatusBadge(item.status)}</td>
          <td style="text-align:center;">
            <div class="btn-action-group" style="justify-content:center;">
              <button class="btn btn-outline btn-sm" onclick="openDetailModal('${moduleKey}', ${item.id})" title="Lihat Detail">${ICONS.detail} Detail</button>
              <button class="btn btn-warning btn-sm" onclick="openCrudModal('edit', '${moduleKey}', ${item.id})" title="Edit Data">${ICONS.edit} Edit</button>
              <button class="btn btn-primary btn-sm" onclick="handleDownloadDoc('${moduleKey}', ${item.id})" title="Unduh Berkas / Buka Drive">${ICONS.download} Unduh</button>
              <button class="btn btn-danger btn-sm" onclick="openDeleteModal('${moduleKey}', ${item.id}, '${(item.judul || '').replace(/'/g, "\\'")}')" title="Hapus Data">${ICONS.delete} Hapus</button>
            </div>
          </td>
        </tr>
      `;
    }

    return `
      <tr data-doc-id="${item.id}" data-mod-key="${moduleKey}" class="${isSelected ? 'row-selected' : ''}">
        <td style="text-align:center;" class="col-checkbox-cell">
          <input type="checkbox" class="gov-checkbox row-checkbox ${isSelected ? 'checked' : ''}" data-doc-id="${item.id}" data-mod-key="${moduleKey}" ${isSelected ? 'checked' : ''} onclick="event.stopPropagation(); toggleRowSelection(this.closest('tr'))">
        </td>
        <td style="text-align:center;"><span class="badge badge-gray">${item.tahun}</span></td>
        <td>
          <strong>${item.judul}</strong>
        </td>
        <td>
          <span style="color:var(--text-body);font-size:12px;">${item.keterangan || '-'}</span>
        </td>
        <td>
          ${item.link_drive ? `<span class="badge badge-yellow" style="font-size:10px;margin-right:4px;">Drive</span>` : ''}
          <span style="font-family:monospace;font-size:11px;color:var(--primary);">${item.file}</span>
          <span style="font-size:10px;color:var(--text-muted);">(${item.size})</span>
        </td>
        <td style="text-align:center;">${getStatusBadge(item.status)}</td>
        <td style="text-align:center;">
          <div class="btn-action-group" style="justify-content:center;">
            <button class="btn btn-outline btn-sm" onclick="openDetailModal('${moduleKey}', ${item.id})" title="Lihat Detail">${ICONS.detail} Detail</button>
            <button class="btn btn-warning btn-sm" onclick="openCrudModal('edit', '${moduleKey}', ${item.id})" title="Edit Data">${ICONS.edit} Edit</button>
            <button class="btn btn-primary btn-sm" onclick="handleDownloadDoc('${moduleKey}', ${item.id})" title="Unduh Berkas / Buka Drive">${ICONS.download} Unduh</button>
            <button class="btn btn-danger btn-sm" onclick="openDeleteModal('${moduleKey}', ${item.id}, '${(item.judul || '').replace(/'/g, "\\'")}')" title="Hapus Data">${ICONS.delete} Hapus</button>
          </div>
        </td>
      </tr>
    `;
  }).join('');
}

// Render Tabel Dokumen Terakhir di Dashboard
function renderAdminRecentTable() {
  const tbody = document.getElementById('adminRecentTable');
  if (!tbody) return;

  const allItems = [];
  for (const k in adminDb) {
    if (Array.isArray(adminDb[k])) {
      adminDb[k].forEach(item => {
        allItems.push({
          ...item,
          modKey: k,
          modLabel: ADMIN_PAGES[k]?.category || k
        });
      });
    }
  }

  allItems.sort((a, b) => (b.id || 0) - (a.id || 0));
  const recentItems = allItems.slice(0, 10);

  if (recentItems.length === 0) {
    tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;padding:32px;color:var(--text-muted);font-weight:500;">Belum ada dokumen yang diunggah. Silakan klik modul pada menu di samping kiri dan klik tombol <strong>"+ Tambah Dokumen"</strong> untuk mulai menambahkan berkas.</td></tr>`;
    if (typeof syncTableHeightWithCalendar === 'function') {
      window.requestAnimationFrame(syncTableHeightWithCalendar);
    }
    return;
  }

  tbody.innerHTML = recentItems.map((it, idx) => {
    return `
      <tr data-doc-id="${it.id}" data-mod-key="${it.modKey}">
        <td style="text-align:center;"><span class="badge badge-gray">${it.tahun || (it.tanggal ? it.tanggal.substring(0, 4) : '2026')}</span></td>
        <td>
          <strong>${it.judul}</strong>
          <div style="font-size:11px;color:var(--text-muted);">${it.keterangan || '-'}</div>
        </td>
        <td><span class="badge badge-blue">${it.modLabel}</span></td>
        <td>
          ${it.link_drive ? `<span class="badge badge-yellow" style="font-size:10px;margin-right:4px;">Drive</span>` : ''}
          <span style="font-family:monospace;font-size:11px;color:var(--primary);">${it.file}</span>
          <span style="font-size:10px;color:var(--text-muted);">(${it.size})</span>
        </td>
        <td style="text-align:center;">${getStatusBadge(it.status)}</td>
        <td style="text-align:center;">
          <div class="btn-action-group" style="justify-content:center;">
            <button class="btn btn-outline btn-sm" onclick="openDetailModal('${it.modKey}', ${it.id})" title="Lihat Detail">${ICONS.detail} Detail</button>
            <button class="btn btn-warning btn-sm" onclick="openCrudModal('edit', '${it.modKey}', ${it.id})" title="Edit Data">${ICONS.edit} Edit</button>
            <button class="btn btn-primary btn-sm" onclick="handleDownloadDoc('${it.modKey}', ${it.id})" title="Unduh Berkas / Buka Drive">${ICONS.download} Unduh</button>
            <button class="btn btn-danger btn-sm" onclick="openDeleteModal('${it.modKey}', ${it.id}, '${(it.judul || '').replace(/'/g, "\\'")}')" title="Hapus Data">${ICONS.delete} Hapus</button>
          </div>
        </td>
      </tr>
    `;
  }).join('');

  if (typeof syncTableHeightWithCalendar === 'function') {
    window.requestAnimationFrame(syncTableHeightWithCalendar);
  }
}

function renderAdminModuleTable(pageKey) {
  if (pageKey === 'dashboard') {
    renderAdminRecentTable();
    updateAdminStats();
    return;
  }
  if (adminDb[pageKey]) {
    renderAdminTable('admin-table-' + pageKey, pageKey);
  }
  updateAdminStats();
}

function renderAllAdminTables() {
  renderAdminTable('admin-table-renja-murni', 'renja-murni');
  renderAdminTable('admin-table-renja-perubahan', 'renja-perubahan');
  renderAdminTable('admin-table-pk-murni', 'pk-murni');
  renderAdminTable('admin-table-pk-perubahan', 'pk-perubahan');
  renderAdminTable('admin-table-dpa-murni', 'dpa-murni');
  renderAdminTable('admin-table-dpa-perubahan', 'dpa-perubahan');
  renderAdminTable('admin-table-surat-masuk', 'surat-masuk');
  renderAdminTable('admin-table-surat-keluar', 'surat-keluar');
  renderAdminRecentTable();
  updateAdminStats();
}

// ==========================================================================
// MODAL CRUD (CREATE & UPDATE DENGAN AUTO-NAME DARI PDF & GOOGLE DRIVE)
// ==========================================================================
function onCrudModuleChange(newModuleKey) {
  const modInfo = ADMIN_PAGES[newModuleKey] || ADMIN_PAGES['renja-murni'];
  const mode = document.getElementById('crudMode')?.value || 'create';
  const modalKeyInput = document.getElementById('crudModuleKey');
  if (modalKeyInput) modalKeyInput.value = newModuleKey;

  const modFixed = document.getElementById('crudModuleFixedDisplay');
  if (modFixed) modFixed.value = modInfo.category || '';

  const modalTitle = document.getElementById('modalCrudTitle');
  if (modalTitle) {
    modalTitle.textContent = `${mode === 'create' ? 'TAMBAH' : 'EDIT'} DATA DOKUMEN â€” ${(modInfo.category || '').toUpperCase()}`;
  }

  const isSurat = newModuleKey.startsWith('surat');
  const groupSurat = document.getElementById('groupSuratFields');
  if (groupSurat) {
    groupSurat.style.display = isSurat ? 'block' : 'none';
  }

  const instansiLabel = document.getElementById('crudInstansiLabel');
  if (instansiLabel && isSurat) {
    instansiLabel.textContent = newModuleKey === 'surat-masuk' ? 'Instansi Pengirim *' : 'Instansi Tujuan *';
  }

  const titleLabel = document.getElementById('crudTitleLabel');
  if (titleLabel) {
    titleLabel.innerHTML = isSurat ? `Perihal / Subjek Surat <span class="req">*</span>` : `Nama Dokumen / Judul Berkas <span class="req">*</span>`;
  }

  if (isSurat && mode === 'create') {
    const year = new Date().getFullYear();
    const code = newModuleKey === 'surat-masuk' ? 'SM-PEP' : 'SK-PEP';
    const elNomor = document.getElementById('crudNomorSurat');
    if (elNomor && (!elNomor.value || elNomor.value.includes('-PEP/'))) {
      elNomor.value = `001/${code}/${year}`;
    }
    const elTgl = document.getElementById('crudTanggalSurat');
    if (elTgl && !elTgl.value) {
      elTgl.value = new Date().toISOString().split('T')[0];
    }
  }
}
window.onCrudModuleChange = onCrudModuleChange;

function openCrudModal(mode, moduleKey, id = null) {
  const targetModule = moduleKey || currentAdminPage;
  const modInfo = ADMIN_PAGES[targetModule] || ADMIN_PAGES['renja-murni'];

  const form = document.getElementById('formCrud');
  if (form) form.reset();

  document.getElementById('crudMode').value = mode;
  document.getElementById('crudModuleKey').value = targetModule;
  document.getElementById('crudEditId').value = id || '';

  const modalTitle = document.getElementById('modalCrudTitle');
  const modSelect = document.getElementById('crudModuleSelect');
  const modFixed = document.getElementById('crudModuleFixedDisplay');
  const preview = document.getElementById('crudPdfPreview');
  const dropBox = document.getElementById('crudPdfDropzone');
  const fileReq = document.getElementById('crudFileLabel');
  const groupSurat = document.getElementById('groupSuratFields');
  const isSurat = targetModule.startsWith('surat');

  if (dropBox) dropBox.classList.remove('has-file');
  if (preview) { preview.style.display = 'none'; preview.textContent = ''; }

  // Reset multi-file state
  window._crudPdfFiles = [];
  window._rebuildPdfFileList = null;
  const fileList = document.getElementById('crudPdfFileList');
  if (fileList) { fileList.style.display = 'none'; fileList.innerHTML = ''; }


  // Kunci Jenis Dokumen jika sedang berada di dalam fitur modul tertentu
  const isInsideFeature = (currentAdminPage !== 'dashboard') || (mode === 'edit');
  if (isInsideFeature) {
    if (modFixed) {
      modFixed.style.display = 'block';
      modFixed.value = modInfo.category || targetModule;
    }
    if (modSelect) {
      modSelect.style.display = 'none';
      modSelect.value = targetModule;
    }
  } else {
    // Jika dibuka dari tombol di Dashboard utama: beri opsi pilihan modul
    if (modFixed) modFixed.style.display = 'none';
    if (modSelect) {
      modSelect.style.display = 'block';
      modSelect.value = targetModule === 'dashboard' ? 'renja-murni' : targetModule;
    }
  }

  if (groupSurat) {
    groupSurat.style.display = isSurat ? 'block' : 'none';
  }

  const instansiLabel = document.getElementById('crudInstansiLabel');
  if (instansiLabel && isSurat) {
    instansiLabel.textContent = targetModule === 'surat-masuk' ? 'Instansi Pengirim *' : 'Instansi Tujuan *';
  }

  const titleLabel = document.getElementById('crudTitleLabel');
  if (titleLabel) {
    titleLabel.innerHTML = isSurat ? `Perihal / Subjek Surat <span class="req">*</span>` : `Nama Dokumen / Judul Berkas <span class="req">*</span>`;
  }

  if (mode === 'create') {
    if (modalTitle) modalTitle.textContent = `TAMBAH DATA DOKUMEN â€” ${modInfo.category.toUpperCase()}`;
    document.getElementById('btnCrudSubmit').textContent = 'Simpan & Tambah Data';
    if (fileReq) fileReq.innerHTML = `Unggah Berkas PDF Lampiran <span class="req">*</span>`;
    document.getElementById('crudPdfFile').required = true;

    const elTahun = document.getElementById('crudTahun');
    if (elTahun) elTahun.value = new Date().getFullYear();

    if (isSurat) {
      const year = new Date().getFullYear();
      const code = targetModule === 'surat-masuk' ? 'SM-PEP' : 'SK-PEP';
      const elNomor = document.getElementById('crudNomorSurat');
      if (elNomor) elNomor.value = `001/${code}/${year}`;
      const elTgl = document.getElementById('crudTanggalSurat');
      if (elTgl) elTgl.value = new Date().toISOString().split('T')[0];
      const elInst = document.getElementById('crudInstansi');
      if (elInst) elInst.value = '';
    }
  } else {
    // Mode EDIT
    if (modalTitle) modalTitle.textContent = `EDIT DATA DOKUMEN â€” ${modInfo.category.toUpperCase()}`;
    document.getElementById('btnCrudSubmit').textContent = 'Perbarui Data Dokumen';
    if (fileReq) fileReq.innerHTML = `Ganti Berkas PDF Lampiran (Opsional)`;
    document.getElementById('crudPdfFile').required = false;

    // Prefill data yang sedang diedit
    const item = (adminDb[targetModule] || []).find(x => x.id == id);
    if (item) {
      const elTahun = document.getElementById('crudTahun');
      if (elTahun) elTahun.value = item.tahun || '2026';

      document.getElementById('crudJudul').value = item.judul || '';
      document.getElementById('crudStatus').value = item.status || 'Lengkap';

      if (isSurat) {
        const elNomor = document.getElementById('crudNomorSurat');
        if (elNomor) elNomor.value = item.nomor || '';
        const elTgl = document.getElementById('crudTanggalSurat');
        if (elTgl) elTgl.value = item.tanggal || item.date || '';
        const elInst = document.getElementById('crudInstansi');
        if (elInst) elInst.value = item.pengirim || '';
      }

      if (preview && dropBox && item.file && item.file !== '-') {
        dropBox.classList.add('has-file');
        preview.style.display = 'block';
        preview.innerHTML = `Berkas saat ini: <strong>${item.file}</strong> (${item.size})`;
      }
    }
  }

  document.getElementById('modalCrud')?.classList.add('show');
}

function closeAdminModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.remove('show');
    modal.classList.remove('active');
    modal.style.display = 'none';
  }
}

// Tutup popup modal saat pengguna mengklik di luar area dialog (pada backdrop/overlay)
document.addEventListener('click', function (e) {
  if (e.target && e.target.classList && e.target.classList.contains('modal-overlay')) {
    closeAdminModal(e.target.id);
  }
});

// Auto-fill nama dokumen dari file PDF yang dipilih (multi-file support)
document.getElementById('crudPdfFile')?.addEventListener('change', function () {
  renderPdfFileList(this.files);
});

function renderPdfFileList(fileList) {
  const fileInput = document.getElementById('crudPdfFile');
  const dropBox = document.getElementById('crudPdfDropzone');
  const preview = document.getElementById('crudPdfPreview');
  const listContainer = document.getElementById('crudPdfFileList');
  const nameInput = document.getElementById('crudJudul');

  if (!fileList || fileList.length === 0) {
    if (dropBox) dropBox.classList.remove('has-file');
    if (preview) { preview.style.display = 'none'; preview.textContent = ''; }
    if (listContainer) { listContainer.style.display = 'none'; listContainer.innerHTML = ''; }
    return;
  }

  // Update dropzone state
  if (dropBox) dropBox.classList.add('has-file');
  if (preview) {
    preview.style.display = 'block';
    preview.innerHTML = `<strong>${fileList.length} file PDF</strong> dipilih`;
  }

  // Auto-fill judul dari file pertama
  if (nameInput) {
    nameInput.value = cleanFileName(fileList[0].name);
  }

  // Render daftar file
  if (listContainer) {
    listContainer.style.display = 'block';
    listContainer.innerHTML = '';

    // Simpan files ke array agar bisa dihapus satu-satu
    window._crudPdfFiles = Array.from(fileList);

    function rebuildFileList() {
      listContainer.innerHTML = '';
      if (window._crudPdfFiles.length === 0) {
        // Reset state jika semua file dihapus
        if (dropBox) dropBox.classList.remove('has-file');
        if (preview) { preview.style.display = 'none'; preview.textContent = ''; }
        listContainer.style.display = 'none';
        // Reset file input
        fileInput.value = '';
        return;
      }
      // Update preview text
      if (preview) {
        preview.innerHTML = `<strong>${window._crudPdfFiles.length} file PDF</strong> dipilih`;
      }
      // Auto-fill judul dari file pertama setelah hapus
      if (nameInput && window._crudPdfFiles.length > 0) {
        nameInput.value = cleanFileName(window._crudPdfFiles[0].name);
      }
      window._crudPdfFiles.forEach((f, i) => {
        const sizeMb = f.size >= 1048576
          ? (f.size / 1048576).toFixed(1) + ' MB'
          : (f.size / 1024).toFixed(0) + ' KB';
        const row = document.createElement('div');
        row.style.cssText = 'display:flex;align-items:center;gap:8px;padding:6px 10px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:7px;margin-bottom:5px;';
        row.innerHTML = `
          <svg style="width:16px;height:16px;fill:#16a34a;flex-shrink:0;" viewBox="0 0 24 24"><path d="M20 2H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-2 5h-3v5.5a2.5 2.5 0 0 1-5 0 2.5 2.5 0 0 1 2.5-2.5c.57 0 1.08.19 1.5.5V5h4v2zm-12 9V6H4v14c0 1.1.9 2 2 2h14v-2H6z"/></svg>
          <span style="flex:1;font-size:12px;font-weight:600;color:#166534;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${f.name}">${f.name}</span>
          <span style="font-size:11px;color:#4ade80;flex-shrink:0;">${sizeMb}</span>
          <button type="button" onclick="removePdfFile(${i})" style="border:none;background:transparent;color:#dc2626;cursor:pointer;padding:2px 5px;font-size:14px;font-weight:700;line-height:1;border-radius:4px;" title="Hapus file ini">&times;</button>
        `;
        listContainer.appendChild(row);
      });
    }

    window._rebuildPdfFileList = rebuildFileList;
    rebuildFileList();
  }
}

function removePdfFile(index) {
  if (!window._crudPdfFiles) return;
  window._crudPdfFiles.splice(index, 1);
  if (window._rebuildPdfFileList) window._rebuildPdfFileList();
}


// Drag & Drop pada form CRUD
const dropZoneCrud = document.getElementById('crudPdfDropzone');
if (dropZoneCrud) {
  ['dragenter', 'dragover'].forEach(name => {
    dropZoneCrud.addEventListener(name, (e) => {
      e.preventDefault();
      dropZoneCrud.classList.add('dragover');
    });
  });

  ['dragleave', 'drop'].forEach(name => {
    dropZoneCrud.addEventListener(name, (e) => {
      e.preventDefault();
      dropZoneCrud.classList.remove('dragover');
      if (name === 'drop') {
        const fileInput = document.getElementById('crudPdfFile');
        if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
          fileInput.files = e.dataTransfer.files;
          const event = new Event('change');
          fileInput.dispatchEvent(event);
        }
      }
    });
  });
}

// Submit Form CRUD (Create & Update ke Backend Laravel)
async function handleCrudSubmit(e) {
  e.preventDefault();
  const btn = document.getElementById('btnCrudSubmit');
  const mode = document.getElementById('crudMode').value;
  const modKey = document.getElementById('crudModuleKey').value;
  const editId = document.getElementById('crudEditId').value;
  const isSurat = modKey.startsWith('surat');

  btn.disabled = true;
  btn.textContent = 'Menyimpan ke Server...';

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const tahun = document.getElementById('crudTahun')?.value || '2026';
  const judul = document.getElementById('crudJudul')?.value?.trim() || '';
  const extra = document.getElementById('crudExtra')?.value?.trim() || '-';
  const status = document.getElementById('crudStatus')?.value || 'Lengkap';
  const linkDrive = document.getElementById('crudLinkDrive')?.value?.trim() || '';

  // Ambil daftar file dari array multi-file (atau dari input langsung)
  const fileInput = document.getElementById('crudPdfFile');
  const selectedFiles = (window._crudPdfFiles && window._crudPdfFiles.length > 0)
    ? window._crudPdfFiles
    : (fileInput?.files?.length > 0 ? Array.from(fileInput.files) : []);

  // â”€â”€ Surat hanya support satu file (edit/create) â”€â”€
  if (isSurat) {
    const formData = new FormData();
    const jenis = modKey === 'surat-masuk' ? 'masuk' : 'keluar';
    const nomor = document.getElementById('crudNomorSurat')?.value.trim() || `001/PEP/${tahun}`;
    const tanggal = document.getElementById('crudTanggalSurat')?.value || new Date().toISOString().split('T')[0];
    const instansi = document.getElementById('crudInstansi')?.value.trim() || (jenis === 'masuk' ? 'Instansi Terkait' : 'Kepala Dinas');

    if (selectedFiles.length > 0) formData.append('file_pdf', selectedFiles[0]);
    if (linkDrive) formData.append('link_drive', linkDrive);
    formData.append('status', status);
    formData.append('jenis', jenis);
    formData.append('nomor_surat', nomor);
    formData.append('tanggal_surat', tanggal);
    formData.append('perihal', judul);
    formData.append('instansi', instansi);
    formData.append('keterangan', extra);

    const url = mode === 'create'
      ? `${window.appUrl || ''}/admin/surat`
      : `${window.appUrl || ''}/admin/surat/${editId}`;
    if (mode === 'edit') formData.append('_method', 'PUT');

    try {
      const res = await fetch(url, { method: 'POST', body: formData, headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
      const json = await res.json();
      if (!res.ok || !json.success) {
        let errMsg = json.message || 'Gagal menyimpan.';
        if (json.errors) errMsg = Object.values(json.errors).flat().join(' ');
        showAdminToast(errMsg, 'error');
        btn.disabled = false;
        btn.textContent = mode === 'create' ? 'Simpan & Tambah Data' : 'Perbarui Data Dokumen';
        return;
      }
      const savedItem = json.data;
      if (mode === 'create') {
        if (!adminDb[modKey]) adminDb[modKey] = [];
        adminDb[modKey].unshift(savedItem);
        showAdminToast(`Surat "${savedItem.judul}" berhasil disimpan!`, 'success');
      } else {
        const idx = (adminDb[modKey] || []).findIndex(x => x.id == editId);
        if (idx !== -1) adminDb[modKey][idx] = savedItem;
        showAdminToast(`Perubahan "${savedItem.judul}" berhasil disimpan!`, 'success');
      }
      closeAdminModal('modalCrud');
      updateDynamicYearFilters();
      renderAdminModuleTable(currentAdminPage);
      renderAdminRecentTable();
      updateAdminStats();
      initAdminChart();
    } catch (err) {
      console.error(err);
      showAdminToast('Terjadi gangguan koneksi saat menyimpan.', 'error');
    } finally {
      btn.disabled = false;
      btn.textContent = mode === 'create' ? 'Simpan & Tambah Data' : 'Perbarui Data Dokumen';
    }
    return;
  }

  // â”€â”€ Dokumen (renja/pk/dpa): support multi-file saat create â”€â”€
  const parts = modKey.split('-');
  const modul = parts[0];
  const kategori = parts[1] || 'murni';
  const url = mode === 'create'
    ? `${window.appUrl || ''}/admin/dokumen`
    : `${window.appUrl || ''}/admin/dokumen/${editId}`;

  if (mode === 'edit' || selectedFiles.length <= 1) {
    // Edit: satu request normal (file opsional)
    const formData = new FormData();
    if (selectedFiles.length > 0) formData.append('file_pdf', selectedFiles[0]);
    if (linkDrive) formData.append('link_drive', linkDrive);
    formData.append('status', status);
    formData.append('modul', modul);
    formData.append('kategori', kategori);
    formData.append('tahun_anggaran', tahun);
    formData.append('judul', judul);
    formData.append('keterangan', extra);
    if (mode === 'edit') formData.append('_method', 'PUT');

    try {
      const res = await fetch(url, { method: 'POST', body: formData, headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
      const json = await res.json();
      if (!res.ok || !json.success) {
        let errMsg = json.message || 'Gagal menyimpan dokumen.';
        if (json.errors) errMsg = Object.values(json.errors).flat().join(' ');
        showAdminToast(errMsg, 'error');
        btn.disabled = false;
        btn.textContent = mode === 'create' ? 'Simpan & Tambah Data' : 'Perbarui Data Dokumen';
        return;
      }
      const savedItem = json.data;
      if (mode === 'create') {
        if (!adminDb[modKey]) adminDb[modKey] = [];
        adminDb[modKey].unshift(savedItem);
        showAdminToast(`Dokumen "${savedItem.judul}" berhasil disimpan & disinkronkan ke Google Drive!`, 'success');
      } else {
        const idx = (adminDb[modKey] || []).findIndex(x => x.id == editId);
        if (idx !== -1) adminDb[modKey][idx] = savedItem;
        showAdminToast(`Perubahan "${savedItem.judul}" berhasil disimpan & disinkronkan ke Google Drive!`, 'success');
      }
      closeAdminModal('modalCrud');
      updateDynamicYearFilters();
      renderAdminModuleTable(currentAdminPage);
      renderAdminRecentTable();
      updateAdminStats();
      initAdminChart();
    } catch (err) {
      console.error(err);
      showAdminToast('Terjadi gangguan koneksi saat menyimpan dokumen.', 'error');
    } finally {
      btn.disabled = false;
      btn.textContent = mode === 'create' ? 'Simpan & Tambah Data' : 'Perbarui Data Dokumen';
    }
  } else {
    // Create multi-file: kirim request terpisah per file, judul otomatis dari nama file
    btn.textContent = `Menyimpan 0/${selectedFiles.length} file...`;
    const savedItems = [];
    let hasError = false;
    for (let i = 0; i < selectedFiles.length; i++) {
      const f = selectedFiles[i];
      btn.textContent = `Menyimpan ${i + 1}/${selectedFiles.length} file...`;
      const fd = new FormData();
      fd.append('file_pdf', f);
      fd.append('modul', modul);
      fd.append('kategori', kategori);
      fd.append('tahun_anggaran', tahun);
      // Judul: file pertama pakai judul yang diisi user, sisanya pakai nama file
      fd.append('judul', i === 0 ? judul : cleanFileName(f.name));
      fd.append('keterangan', extra);
      fd.append('status', status);
      if (linkDrive) fd.append('link_drive', linkDrive);
      try {
        const res = await fetch(url, { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } });
        const json = await res.json();
        if (!res.ok || !json.success) {
          let errMsg = json.message || `Gagal menyimpan file: ${f.name}`;
          if (json.errors) errMsg = Object.values(json.errors).flat().join(' ');
          showAdminToast(errMsg, 'error');
          hasError = true;
          break;
        }
        savedItems.push(json.data);
      } catch (err) {
        console.error(err);
        showAdminToast(`Gangguan koneksi saat menyimpan file: ${f.name}`, 'error');
        hasError = true;
        break;
      }
    }

    if (savedItems.length > 0) {
      if (!adminDb[modKey]) adminDb[modKey] = [];
      // Tambahkan semua di awal (urutan terbalik agar yang pertama di paling atas)
      for (let i = savedItems.length - 1; i >= 0; i--) {
        adminDb[modKey].unshift(savedItems[i]);
      }
      if (!hasError) {
        showAdminToast(`${savedItems.length} dokumen PDF berhasil disimpan & disinkronkan ke Google Drive!`, 'success');
        closeAdminModal('modalCrud');
      } else {
        showAdminToast(`${savedItems.length} dari ${selectedFiles.length} dokumen berhasil disimpan.`, 'info');
      }
      updateDynamicYearFilters();
      renderAdminModuleTable(currentAdminPage);
      renderAdminRecentTable();
      updateAdminStats();
      initAdminChart();
    }

    btn.disabled = false;
    btn.textContent = 'Simpan & Tambah Data';
  }
}


// ==========================================================================
// MODAL DETAIL DOKUMEN (READ)
// ==========================================================================
function openDetailModal(moduleKey, id) {
  const item = (adminDb[moduleKey] || []).find(x => x.id == id);
  if (!item) return;

  const titleEl = document.getElementById('detailModalTitle');
  const bodyEl = document.getElementById('detailModalBody');
  const dlBtn = document.getElementById('btnDetailDownload');

  if (titleEl) titleEl.textContent = `DETAIL DOKUMEN â€” ${ADMIN_PAGES[moduleKey]?.category?.toUpperCase() || 'PEP'}`;

  if (bodyEl) {
    bodyEl.innerHTML = `
      <table class="detail-table">
        <tr>
          <th>Tahun Anggaran</th>
          <td><strong>${item.tahun || '2026'}</strong></td>
        </tr>
        <tr>
          <th>Nama Dokumen</th>
          <td><strong style="color:var(--primary);font-size:13px;">${item.judul}</strong></td>
        </tr>
        ${item.nomor ? `<tr><th>Nomor Surat</th><td><code>${item.nomor}</code></td></tr>` : ''}
        ${item.pengirim ? `<tr><th>Pengirim / Tujuan</th><td>${item.pengirim}</td></tr>` : ''}
        <tr>
          <th>Uraian / Keterangan</th>
          <td>${item.keterangan || '-'}</td>
        </tr>
        <tr>
          <th>Berkas Lampiran</th>
          <td>
            <div style="font-family:monospace;font-weight:700;color:var(--primary);margin-bottom:2px;">
              ${item.file}
            </div>
            <span style="font-size:11px;color:var(--text-muted);">Ukuran: ${item.size}</span>
          </td>
        </tr>
        ${item.link_drive ? `
        <tr>
          <th>Google Drive Cloud</th>
          <td>
            <a href="${item.link_drive}" target="_blank" style="color:var(--primary);font-weight:700;text-decoration:underline;word-break:break-all;display:inline-flex;align-items:center;gap:4px;">
              <svg style="width:14px;height:14px;fill:#ea4335;" viewBox="0 0 24 24"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM19 18H6c-2.21 0-4-1.79-4-4 0-2.05 1.53-3.76 3.56-3.97l1.07-.11.5-.95C8.08 7.14 9.94 6 12 6c2.62 0 4.88 1.86 5.39 4.43l.3 1.5 1.53.11c1.56.1 2.78 1.41 2.78 2.96 0 1.65-1.35 3-3 3z"/></svg>
              Buka Berkas di Google Drive &rarr;
            </a>
          </td>
        </tr>` : ''}
        <tr>
          <th>Tanggal Catat</th>
          <td>${formatDateIndo(item.date || item.tanggal)}</td>
        </tr>
        <tr>
          <th>Status Dokumen</th>
          <td>${getStatusBadge(item.status)}</td>
        </tr>
      </table>
    `;
  }

  if (dlBtn) {
    dlBtn.onclick = () => handleDownloadDoc(moduleKey, item.id);
  }

  document.getElementById('modalDetail')?.classList.add('show');
}

// ==========================================================================
// MODAL HAPUS DOKUMEN (DELETE)
// ==========================================================================
function openDeleteModal(moduleKey, id, title) {
  adminDeleteTarget = { moduleKey, id };
  const msg = document.getElementById('deleteModalMessage');
  if (msg) {
    msg.innerHTML = `Apakah Anda yakin ingin menghapus data dokumen: <strong>"${title}"</strong>?`;
  }
  document.getElementById('modalDelete')?.classList.add('show');
}

async function confirmDeleteDoc() {
  if (!adminDeleteTarget) return;

  // Mode Hapus Massal (Batch Delete dari Baris Terpilih)
  if (adminDeleteTarget.isBatch) {
    const items = adminDeleteTarget.items || [];
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    let successCount = 0;

    for (const item of items) {
      const isSurat = item.moduleKey.startsWith('surat');
      const url = isSurat
        ? `${window.appUrl || ''}/admin/surat/${item.id}`
        : `${window.appUrl || ''}/admin/dokumen/${item.id}`;

      const formData = new FormData();
      formData.append('_method', 'DELETE');

      try {
        const res = await fetch(url, {
          method: 'POST',
          body: formData,
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          }
        });
        const json = await res.json();
        if (res.ok && json.success) {
          successCount++;
          if (adminDb[item.moduleKey]) {
            adminDb[item.moduleKey] = adminDb[item.moduleKey].filter(x => x.id != item.id);
          }
        }
      } catch (err) {
        console.error(err);
      }
    }

    clearAllSelections();
    showAdminToast(`${successCount} dokumen terpilih berhasil dihapus permanen dari sistem & Google Drive.`, 'success');
    renderAdminModuleTable(currentAdminPage);
    renderAdminRecentTable();
    updateAdminStats();
    initAdminChart();
    updateDynamicYearFilters();
    closeAdminModal('modalDelete');
    adminDeleteTarget = null;
    return;
  }

  const { moduleKey, id } = adminDeleteTarget;
  const isSurat = moduleKey.startsWith('surat');
  const url = isSurat
    ? `${window.appUrl || ''}/admin/surat/${id}`
    : `${window.appUrl || ''}/admin/dokumen/${id}`;
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  const formData = new FormData();
  formData.append('_method', 'DELETE');

  try {
    const res = await fetch(url, {
      method: 'POST',
      body: formData,
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      }
    });

    const json = await res.json();
    if (res.ok && json.success) {
      if (adminDb[moduleKey]) {
        adminDb[moduleKey] = adminDb[moduleKey].filter(x => x.id != id);
      }
      showAdminToast('Dokumen berhasil dihapus permanen dari sistem.', 'success');
      renderAdminModuleTable(currentAdminPage);
      renderAdminRecentTable();
      updateAdminStats();
      initAdminChart();
      updateDynamicYearFilters();
    } else {
      showAdminToast(json.message || 'Gagal menghapus data.', 'error');
    }
  } catch (err) {
    console.error(err);
    showAdminToast('Terjadi kesalahan koneksi saat menghapus data.', 'error');
  } finally {
    closeAdminModal('modalDelete');
    adminDeleteTarget = null;
  }
}

// ==========================================================================
// LIVE SEARCH FILTER
// ==========================================================================
function setupAdminSearchFilters() {
  updateDynamicYearFilters();

  document.querySelectorAll('.admin-page').forEach(page => {
    const search = page.querySelector('.table-toolbar .search-box');
    const filterYear = page.querySelector('.table-toolbar .filter-year');
    const table = page.querySelector('table');

    function filterData() {
      if (!table) return;
      const q = search ? search.value.toLowerCase().trim() : '';
      const yr = filterYear ? filterYear.value.trim() : '';

      const rows = table.querySelectorAll('tbody tr');
      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        let match = true;
        if (q && !text.includes(q)) match = false;
        if (yr && !text.includes(yr)) match = false;
        row.style.display = match ? '' : 'none';
      });
    }

    if (search) search.addEventListener('input', filterData);
    if (filterYear) filterYear.addEventListener('change', filterData);
  });
}

// ==========================================================================
// TOAST NOTIFIKASI
// ==========================================================================
function showAdminToast(message, type = 'info') {
  const container = document.getElementById('adminToastContainer');
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
  }, 3500);

  // Tambahkan ke appbar dropdown jika tipe success atau error
  if (type === 'success' || type === 'error') {
    _pushAppbarNotif(message, type);
  }
}

// Tambah notifikasi baru ke appbar dropdown
function _pushAppbarNotif(message, type) {
  const list = document.querySelector('.notif-dropdown-list');
  if (!list) return;

  const iconColor = type === 'success' ? '#10b981' : '#ef4444';
  const iconSvg = type === 'success'
    ? `<svg viewBox="0 0 24 24" width="15" height="15" stroke="${iconColor}" stroke-width="2.5" fill="none"><polyline points="20 6 9 17 4 12"></polyline></svg>`
    : `<svg viewBox="0 0 24 24" width="15" height="15" stroke="${iconColor}" stroke-width="2.5" fill="none"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>`;

  const now = new Date();
  const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

  const item = document.createElement('div');
  item.className = 'notif-dropdown-item unread';
  item.innerHTML = `
    <div class="notif-item-icon" style="background:${type === 'success' ? '#f0fdf4' : '#fef2f2'};">${iconSvg}</div>
    <div class="notif-item-content">
      <div class="notif-item-title">${message}</div>
      <div class="notif-item-meta">Baru saja &bull; ${timeStr}</div>
    </div>
  `;
  list.prepend(item);

  // Update badge count
  const badge = document.getElementById('notifBadge') || document.querySelector('.notif-badge');
  const countPill = document.getElementById('notifCountPill') || document.querySelector('.notif-count-pill');
  const unreadCount = list.querySelectorAll('.notif-dropdown-item.unread').length;
  if (badge) { badge.textContent = unreadCount; badge.style.display = 'flex'; }
  if (countPill) countPill.textContent = `${unreadCount} baru`;
}

// ==========================================================================
// INTERAKTIF WIDGET MATERIAL DASHBOARD (FOLDERS, TASKS, KALENDER)
// ==========================================================================

// 1. Filter Tab Card 2 (Folder Berkas)
function filterFolderTab(el, category) {
  document.querySelectorAll('.files-tab-item').forEach(item => item.classList.remove('active'));
  if (el) el.classList.add('active');

  const folders = document.querySelectorAll('.folder-card');
  folders.forEach(card => {
    if (category === 'all') {
      card.style.display = 'flex';
    } else if (category === 'renja') {
      card.style.display = card.classList.contains('folder-pink') ? 'flex' : 'none';
    } else if (category === 'pk-dpa') {
      card.style.display = (card.classList.contains('folder-teal') || card.classList.contains('folder-amber')) ? 'flex' : 'none';
    } else if (category === 'surat') {
      card.style.display = card.classList.contains('folder-blue') ? 'flex' : 'none';
    }
  });
}

// 2. Tasks & Agenda PEP — Full Implementation
// ─────────────────────────────────────────────

// 2. Tasks & Agenda PEP & Kalender Dinamis — Full Modern Implementation
// ─────────────────────────────────────────────────────────────────────────

const AGENDA_KEY = 'pep_agenda_list';
let agendaNotifTimers = {}; // { id: timerId }

// State Kalender & Filter Tasks
let calCurrentYear = new Date().getFullYear();
let calCurrentMonth = new Date().getMonth(); // 0-11
let calSelectedDate = null; // 'YYYY-MM-DD' atau null
let currentTaskPeriod = 'today'; // 'today', 'week', 'month', 'all', 'custom-date'

function escapeAgendaHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

// Load & Save
function loadAgendaList() {
  try { return JSON.parse(localStorage.getItem(AGENDA_KEY) || '[]'); } catch { return []; }
}
function saveAgendaList(list) {
  localStorage.setItem(AGENDA_KEY, JSON.stringify(list));
}

// Format tanggal untuk display
function formatAgendaDate(tanggal, jam) {
  if (!tanggal) return '';
  const d = new Date(tanggal + (jam ? 'T' + jam : 'T00:00'));
  const now = new Date();
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
  const agDay = new Date(d.getFullYear(), d.getMonth(), d.getDate());
  const diff = Math.round((agDay - today) / 86400000);

  const dayNames = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
  const monthNames = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
  const dayLabel = diff === 0 ? 'Hari ini'
    : diff === 1 ? 'Besok'
    : diff === -1 ? 'Kemarin'
    : (diff > 0 && diff <= 6) ? dayNames[d.getDay()]
    : `${d.getDate()} ${monthNames[d.getMonth()]} ${d.getFullYear()}`;

  return jam ? `${dayLabel}, ${jam.slice(0,5)} WIB` : dayLabel;
}

// Render Dinamis Kalender Kegiatan dengan Deteksi Agenda
function renderAdminCalendar(year, month) {
  if (year !== undefined) calCurrentYear = year;
  if (month !== undefined) calCurrentMonth = month;

  const titleEl = document.getElementById('calMonthYearTitle');
  const gridEl = document.getElementById('calDaysGrid');
  if (!gridEl) return;

  const monthNames = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
  ];

  if (titleEl) {
    titleEl.textContent = `${monthNames[calCurrentMonth]} ${calCurrentYear}`;
  }

  // Kelompokkan seluruh agenda berdasarkan tanggal YYYY-MM-DD
  const list = loadAgendaList();
  const agendaMap = {};
  list.forEach(item => {
    if (!item.tanggal) return;
    if (!agendaMap[item.tanggal]) agendaMap[item.tanggal] = [];
    agendaMap[item.tanggal].push(item);
  });

  const now = new Date();
  const todayStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;

  const firstDay = new Date(calCurrentYear, calCurrentMonth, 1).getDay();
  const totalDays = new Date(calCurrentYear, calCurrentMonth + 1, 0).getDate();
  const prevMonthDays = new Date(calCurrentYear, calCurrentMonth, 0).getDate();

  let html = '';

  // Slot tanggal bulan sebelumnya (pudar)
  for (let i = 0; i < firstDay; i++) {
    const prevDayNum = prevMonthDays - firstDay + 1 + i;
    html += `<div class="cal-day other-month" aria-hidden="true">${prevDayNum}</div>`;
  }

  // Hari-hari aktif bulan ini
  for (let d = 1; d <= totalDays; d++) {
    const dateStr = `${calCurrentYear}-${String(calCurrentMonth + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
    const isToday = (dateStr === todayStr);
    const isSelected = (calSelectedDate === dateStr);
    const dayAgendas = agendaMap[dateStr] || [];
    const hasAgenda = dayAgendas.length > 0;

    let dotHtml = '';
    let tooltipText = `${d} ${monthNames[calCurrentMonth]} ${calCurrentYear}`;
    if (hasAgenda) {
      const hasMendesak = dayAgendas.some(x => x.prioritas === 'mendesak');
      const hasPenting = dayAgendas.some(x => x.prioritas === 'penting');
      const dotCls = hasMendesak ? 'dot-mendesak' : (hasPenting ? 'dot-penting' : 'dot-biasa');
      dotHtml = `<span class="cal-agenda-dot ${dotCls}"></span>`;
      tooltipText += ` — ${dayAgendas.length} Agenda: ${dayAgendas.map(x => x.judul).join(', ')}`;
    }

    let classNames = ['cal-day'];
    if (isToday) classNames.push('is-today');
    if (isSelected) classNames.push('is-selected');
    if (hasAgenda) classNames.push('has-agenda');

    html += `
      <div 
        class="${classNames.join(' ')}" 
        onclick="selectCalDate('${dateStr}')" 
        title="${escapeAgendaHtml(tooltipText)}"
        data-date="${dateStr}"
      >
        <span class="cal-day-num">${d}</span>
        ${dotHtml}
      </div>
    `;
  }

  // Slot tanggal bulan berikutnya penutup
  const totalRendered = firstDay + totalDays;
  const remaining = (7 - (totalRendered % 7)) % 7;
  for (let i = 1; i <= remaining; i++) {
    html += `<div class="cal-day other-month" aria-hidden="true">${i}</div>`;
  }

  gridEl.innerHTML = html;
}
window.renderAdminCalendar = renderAdminCalendar;

// Navigasi Bulan Kalender
function navigateCalendarMonth(delta) {
  calCurrentMonth += delta;
  if (calCurrentMonth < 0) {
    calCurrentMonth = 11;
    calCurrentYear--;
  } else if (calCurrentMonth > 11) {
    calCurrentMonth = 0;
    calCurrentYear++;
  }
  renderAdminCalendar();
}
window.navigateCalendarMonth = navigateCalendarMonth;

// Reset Kalender ke Hari Ini
function resetCalendarToToday() {
  const now = new Date();
  calCurrentYear = now.getFullYear();
  calCurrentMonth = now.getMonth();
  const todayStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
  calSelectedDate = todayStr;
  renderAdminCalendar();
  selectCalDate(todayStr);
  showAdminToast('Kalender disetel ke hari ini.', 'info');
}
window.resetCalendarToToday = resetCalendarToToday;

// Pemilihan Tanggal Kalender (Menyaring Tasks)
function selectCalDate(dateStr) {
  calSelectedDate = dateStr;
  const parts = dateStr.split('-');
  const y = parseInt(parts[0]);
  const m = parseInt(parts[1]) - 1;
  const d = parseInt(parts[2]);

  const monthNames = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
  ];
  const dateFormatted = `${d} ${monthNames[m]} ${y}`;

  renderAdminCalendar();

  currentTaskPeriod = 'custom-date';
  document.querySelectorAll('.tasks-tab-item').forEach(item => item.classList.remove('active'));

  const filterBar = document.getElementById('taskActiveFilterBar');
  const filterText = document.getElementById('taskActiveFilterText');
  if (filterBar && filterText) {
    filterText.innerHTML = `Menampilkan agenda untuk tanggal: <strong>${dateFormatted}</strong>`;
    filterBar.style.display = 'flex';
  }

  renderTaskList('custom-date', dateStr);
}
window.selectCalDate = selectCalDate;

// Kompatibilitas fungsi selectCalDay sebelumnya
function selectCalDay(el, day) {
  const dateStr = `${calCurrentYear}-${String(calCurrentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
  selectCalDate(dateStr);
}
window.selectCalDay = selectCalDay;

// Bersihkan filter tanggal kalender
function clearCalendarFilter() {
  calSelectedDate = null;
  const filterBar = document.getElementById('taskActiveFilterBar');
  if (filterBar) filterBar.style.display = 'none';

  currentTaskPeriod = 'today';
  const todayTab = document.querySelector('.tasks-tab-item');
  if (todayTab) {
    document.querySelectorAll('.tasks-tab-item').forEach(item => item.classList.remove('active'));
    todayTab.classList.add('active');
  }

  renderAdminCalendar();
  renderTaskList('today');
}
window.clearCalendarFilter = clearCalendarFilter;

// Render list agenda berdasarkan tab / tanggal aktif
function renderTaskList(period, customDate) {
  const container = document.getElementById('taskListContainer');
  if (!container) return;

  const list = loadAgendaList();
  const now = new Date();
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());

  const filtered = list.filter(item => {
    if (period === 'custom-date' && customDate) {
      return item.tanggal === customDate;
    }
    if (!item.tanggal) return false;
    const d = new Date(item.tanggal + 'T00:00');
    const agDay = new Date(d.getFullYear(), d.getMonth(), d.getDate());
    const diff = Math.round((agDay - today) / 86400000);

    if (period === 'today') return diff === 0;
    if (period === 'week') return diff >= 0 && diff <= 6;
    if (period === 'month') return diff >= 0 && diff <= 30;
    if (period === 'all') return true;
    return true;
  }).sort((a, b) => {
    if (a.done !== b.done) return a.done ? 1 : -1;
    const da = (a.tanggal || '') + (a.jam || '00:00');
    const db = (b.tanggal || '') + (b.jam || '00:00');
    return da.localeCompare(db);
  });

  // Update badge jumlah agenda belum selesai
  const badgeEl = document.getElementById('agendaCountBadge');
  if (badgeEl) {
    const pendingCount = list.filter(x => !x.done).length;
    badgeEl.textContent = pendingCount;
  }

  if (filtered.length === 0) {
    const labels = {
      today: 'hari ini',
      week: 'minggu ini',
      month: 'bulan ini',
      all: 'dalam sistem'
    };
    const emptyMsg = (period === 'custom-date' && customDate)
      ? `Belum ada agenda kegiatan pada tanggal ini.`
      : `Tidak ada agenda kegiatan terjadwal ${labels[period] || ''}.`;

    const addBtnHtml = (period === 'custom-date' && customDate)
      ? `<button type="button" onclick="openAgendaModal('', '${customDate}')" style="margin-top:4px;padding:6px 16px;background:var(--primary);color:#fff;border:none;border-radius:7px;font-size:12px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:5px;box-shadow:0 2px 6px rgba(59,130,246,0.3);">+ Buat Agenda Tanggal Ini</button>`
      : `<button type="button" onclick="openAgendaModal()" style="margin-top:4px;padding:6px 16px;background:var(--primary);color:#fff;border:none;border-radius:7px;font-size:12px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:5px;box-shadow:0 2px 6px rgba(59,130,246,0.3);">+ Tambah Agenda</button>`;

    container.innerHTML = `
      <div class="empty-state-card" style="padding:30px 18px;text-align:center;color:#8a92a6;background:#f8fafc;border-radius:10px;border:1px dashed #cbd5e1;min-height:185px;display:flex;flex-direction:column;align-items:center;justify-content:center;">
        <svg viewBox="0 0 24 24" width="36" height="36" stroke="currentColor" stroke-width="1.5" fill="none" style="margin:0 auto 10px;opacity:0.55;display:block;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
        <div style="font-size:0.875rem;font-weight:700;color:var(--text-dark);">${emptyMsg}</div>
        <div style="font-size:0.75rem;margin:6px auto 14px;color:var(--text-muted);line-height:1.55;max-width:310px;">Jadwalkan koordinasi berkala, telaah berkas Renja, batas waktu pelaporan e-SAKIP, dan batas waktu dokumen perangkat daerah.</div>
        ${addBtnHtml}
      </div>`;
    return;
  }

  const priorityMeta = {
    mendesak: {
      label: 'Mendesak',
      cls: 'mendesak',
      dotColor: '#ef4444',
      style: 'background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;'
    },
    penting: {
      label: 'Penting',
      cls: 'penting',
      dotColor: '#f59e0b',
      style: 'background:#fef3c7;color:#b45309;border:1px solid #fde68a;'
    },
    biasa: {
      label: 'Biasa',
      cls: 'biasa',
      dotColor: '#3b82f6',
      style: 'background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;'
    }
  };

  container.innerHTML = `
    <div class="table-responsive agenda-table-wrapper" style="width:100%;overflow-x:auto;border-radius:10px;border:1px solid #e2e8f0;background:#ffffff;box-shadow:0 1px 3px rgba(0,0,0,0.02);">
      <table class="agenda-table" style="width:100%;min-width:440px;border-collapse:collapse;font-size:12px;text-align:left;">
        <thead>
          <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
            <th style="width:38px;padding:9px 6px;text-align:center;color:#64748b;font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:0.03em;">#</th>
            <th style="padding:9px 10px;color:#64748b;font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:0.03em;white-space:nowrap;">Agenda</th>
            <th style="padding:9px 8px;color:#64748b;font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:0.03em;white-space:nowrap;">Jadwal</th>
            <th style="width:85px;padding:9px 6px;text-align:center;color:#64748b;font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:0.03em;white-space:nowrap;">Prioritas</th>
            <th style="width:62px;padding:9px 6px;text-align:center;color:#64748b;font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:0.03em;white-space:nowrap;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          ${filtered.map(item => {
            const dateStr = formatAgendaDate(item.tanggal, item.jam);
            const p = priorityMeta[item.prioritas] || priorityMeta.biasa;
            const rowDoneBg = item.done ? '#fafbfc' : '#ffffff';
            const rowDoneOpacity = item.done ? 'opacity:0.65;' : '';
            return `
              <tr class="agenda-row priority-${item.prioritas || 'biasa'} ${item.done ? 'agenda-done' : ''}" data-agenda-id="${item.id}" style="border-bottom:1px solid #f1f5f9;background:${rowDoneBg};${rowDoneOpacity}transition:background 0.15s ease;">
                <td style="padding:8px 6px;text-align:center;vertical-align:middle;width:38px;">
                  <button 
                    type="button" 
                    class="agenda-check ${item.done ? 'checked' : ''}" 
                    onclick="toggleAgendaDone('${item.id}')" 
                    title="${item.done ? 'Tandai belum selesai' : 'Tandai selesai'}"
                    aria-label="Tandai Status"
                    style="width:20px;height:20px;border-radius:5px;border:1.8px solid ${item.done ? '#10b981' : '#cbd5e1'};background:${item.done ? '#10b981' : '#ffffff'};color:#ffffff;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;padding:0;transition:all 0.15s ease;"
                  >
                    ${item.done ? `<svg viewBox="0 0 24 24" width="12" height="12" stroke="#ffffff" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>` : ''}
                  </button>
                </td>
                <td style="padding:8px 10px;vertical-align:middle;">
                  <div class="agenda-judul" style="font-size:12.5px;font-weight:600;color:${item.done ? '#94a3b8' : '#1e293b'};line-height:1.35;word-break:break-word;${item.done ? 'text-decoration:line-through;' : ''}">
                    ${escapeAgendaHtml(item.judul)}
                  </div>
                  ${item.catatan ? `
                    <div class="agenda-catatan-inline" title="${escapeAgendaHtml(item.catatan)}" style="font-size:11px;color:#64748b;margin-top:3px;display:flex;align-items:center;gap:4px;line-height:1.2;">
                      <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;opacity:0.7;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                      <span style="display:inline-block;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${escapeAgendaHtml(item.catatan)}</span>
                    </div>` : ''}
                </td>
                <td style="padding:8px 8px;vertical-align:middle;white-space:nowrap;">
                  <span class="agenda-badge agenda-date-badge" style="display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;padding:3px 7px;border-radius:5px;background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;white-space:nowrap;">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    ${dateStr}
                  </span>
                </td>
                <td style="padding:8px 6px;text-align:center;vertical-align:middle;white-space:nowrap;">
                  <span class="agenda-badge agenda-priority-badge ${p.cls}" style="display:inline-flex;align-items:center;gap:4px;font-size:10.5px;font-weight:700;padding:2.5px 7px;border-radius:5px;text-transform:capitalize;white-space:nowrap;${p.style}">
                    <span class="priority-dot" style="width:5px;height:5px;border-radius:50%;background:${p.dotColor};display:inline-block;"></span>
                    ${p.label}
                  </span>
                </td>
                <td style="padding:8px 6px;text-align:center;vertical-align:middle;white-space:nowrap;">
                  <div class="agenda-actions" style="display:inline-flex;gap:3px;align-items:center;justify-content:center;">
                    <button 
                      type="button" 
                      class="agenda-btn edit" 
                      onclick="openAgendaModal('${item.id}')" 
                      title="Edit Agenda"
                      style="width:24px;height:24px;border-radius:5px;border:1px solid #e2e8f0;background:#ffffff;color:#64748b;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;padding:0;transition:all 0.15s ease;"
                      onmouseover="this.style.background='#eff6ff';this.style.color='#2563eb';this.style.borderColor='#93c5fd';" 
                      onmouseout="this.style.background='#ffffff';this.style.color='#64748b';this.style.borderColor='#e2e8f0';"
                    >
                      <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button 
                      type="button" 
                      class="agenda-btn del" 
                      onclick="deleteAgenda('${item.id}')" 
                      title="Hapus Agenda"
                      style="width:24px;height:24px;border-radius:5px;border:1px solid #e2e8f0;background:#ffffff;color:#64748b;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;padding:0;transition:all 0.15s ease;"
                      onmouseover="this.style.background='#fee2e2';this.style.color='#dc2626';this.style.borderColor='#fca5a5';" 
                      onmouseout="this.style.background='#ffffff';this.style.color='#64748b';this.style.borderColor='#e2e8f0';"
                    >
                      <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                    </button>
                  </div>
                </td>
              </tr>
            `;
          }).join('')}
        </tbody>
      </table>
    </div>
  `;
}
window.renderTaskList = renderTaskList;

// Tab switch
function switchTaskTab(el, period) {
  document.querySelectorAll('.tasks-tab-item').forEach(item => item.classList.remove('active'));
  if (el) el.classList.add('active');

  calSelectedDate = null;
  const filterBar = document.getElementById('taskActiveFilterBar');
  if (filterBar) filterBar.style.display = 'none';

  currentTaskPeriod = period;
  renderTaskList(period);
  renderAdminCalendar();
}
window.switchTaskTab = switchTaskTab;

// Calendar dots update helper
function updateCalendarAgendaDots() {
  renderAdminCalendar();
}
window.updateCalendarAgendaDots = updateCalendarAgendaDots;

// Open modal tambah / edit
function openAgendaModal(editId, defaultDate) {
  const titleEl = document.getElementById('agendaModalTitle');
  const form = document.getElementById('formAgenda');
  if (form) form.reset();

  document.getElementById('agendaEditId').value = '';
  
  const now = new Date();
  const todayStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
  document.getElementById('agendaTanggal').value = defaultDate || todayStr;

  if (editId) {
    const list = loadAgendaList();
    const item = list.find(x => x.id === editId);
    if (item) {
      if (titleEl) titleEl.textContent = 'EDIT AGENDA';
      document.getElementById('agendaEditId').value = item.id;
      document.getElementById('agendaJudul').value = item.judul || '';
      document.getElementById('agendaTanggal').value = item.tanggal || todayStr;
      document.getElementById('agendaJam').value = item.jam || '';
      document.getElementById('agendaPrioritas').value = item.prioritas || 'biasa';
      document.getElementById('agendaCatatan').value = item.catatan || '';
    }
  } else {
    if (titleEl) titleEl.textContent = 'TAMBAH AGENDA';
  }

  updateAgendaNotifInfoText();
  document.getElementById('modalAgenda')?.classList.add('show');
}
window.openAgendaModal = openAgendaModal;

// Update notifikasi info text di modal
function updateAgendaNotifInfoText() {
  const infoEl = document.getElementById('agendaNotifInfo');
  const textEl = document.getElementById('agendaNotifInfoText');
  const btnNotif = document.getElementById('btnNotifPermission');
  if (!infoEl || !textEl) return;

  if (!('Notification' in window)) {
    infoEl.style.background = '#f8fafc';
    infoEl.style.borderColor = '#e2e8f0';
    infoEl.style.color = '#64748b';
    textEl.textContent = 'Browser ini tidak mendukung notifikasi.';
    return;
  }

  const perm = Notification.permission;
  if (perm === 'granted') {
    infoEl.style.background = '#f0fdf4';
    infoEl.style.borderColor = '#bbf7d0';
    infoEl.style.color = '#166534';
    textEl.textContent = '✓ Notifikasi browser aktif — akan muncul saat waktu agenda tiba.';
    if (btnNotif) btnNotif.style.display = 'none';
  } else if (perm === 'denied') {
    infoEl.style.background = '#fff7ed';
    infoEl.style.borderColor = '#fed7aa';
    infoEl.style.color = '#9a3412';
    textEl.textContent = '⚠ Notifikasi diblokir. Aktifkan di pengaturan browser (ikon 🔒 di address bar).';
    if (btnNotif) btnNotif.style.display = 'none';
  } else {
    infoEl.style.background = '#eff6ff';
    infoEl.style.borderColor = '#bfdbfe';
    infoEl.style.color = '#1d4ed8';
    textEl.innerHTML = '🔔 Klik <strong>Izinkan Notifikasi</strong> di bawah agar dapat menerima pengingat.';
    if (btnNotif) btnNotif.style.display = 'flex';
  }
}

// Save agenda
function saveAgenda(e) {
  e.preventDefault();
  const editId = document.getElementById('agendaEditId').value;
  const judul = document.getElementById('agendaJudul').value.trim();
  const tanggal = document.getElementById('agendaTanggal').value;
  const jam = document.getElementById('agendaJam').value || '';
  const prioritas = document.getElementById('agendaPrioritas').value;
  const catatan = document.getElementById('agendaCatatan').value.trim();

  if (!judul || !tanggal) return;

  const list = loadAgendaList();

  if (editId) {
    const idx = list.findIndex(x => x.id === editId);
    if (idx !== -1) {
      if (agendaNotifTimers[editId]) {
        clearTimeout(agendaNotifTimers[editId]);
        delete agendaNotifTimers[editId];
      }
      list[idx] = { ...list[idx], judul, tanggal, jam, prioritas, catatan };
      scheduleAgendaNotif(list[idx]);
    }
    showAdminToast(`Agenda "${judul}" diperbarui.`, 'success');
  } else {
    const newItem = {
      id: 'ag_' + Date.now() + '_' + Math.random().toString(36).slice(2,6),
      judul, tanggal, jam, prioritas, catatan, done: false,
      createdAt: new Date().toISOString()
    };
    list.unshift(newItem);
    scheduleAgendaNotif(newItem);
    showAdminToast(`Agenda "${judul}" ditambahkan.`, 'success');
  }

  saveAgendaList(list);
  closeAdminModal('modalAgenda');
  renderTaskList(currentTaskPeriod, calSelectedDate);
  renderAdminCalendar();
}
window.saveAgenda = saveAgenda;

// Delete agenda
function deleteAgenda(id) {
  if (!confirm('Hapus agenda ini?')) return;
  let list = loadAgendaList();
  const item = list.find(x => x.id === id);
  list = list.filter(x => x.id !== id);
  saveAgendaList(list);
  if (agendaNotifTimers[id]) {
    clearTimeout(agendaNotifTimers[id]);
    delete agendaNotifTimers[id];
  }
  showAdminToast(`Agenda "${item?.judul || ''}" dihapus.`, 'success');
  renderTaskList(currentTaskPeriod, calSelectedDate);
  renderAdminCalendar();
}
window.deleteAgenda = deleteAgenda;

// Toggle done
function toggleAgendaDone(id) {
  const list = loadAgendaList();
  const item = list.find(x => x.id === id);
  if (item) {
    item.done = !item.done;
    saveAgendaList(list);
    renderTaskList(currentTaskPeriod, calSelectedDate);
    renderAdminCalendar();
    showAdminToast(item.done ? `✓ Agenda "${item.judul}" selesai!` : `Agenda "${item.judul}" dibuka kembali.`, 'success');
  }
}
window.toggleAgendaDone = toggleAgendaDone;

// ──────────── NOTIFIKASI BROWSER ────────────

function requestAgendaNotifPermission() {
  if (!('Notification' in window)) {
    showAdminToast('Browser ini tidak mendukung notifikasi.', 'error');
    return;
  }
  Notification.requestPermission().then(perm => {
    if (perm === 'granted') {
      showAdminToast('✓ Notifikasi browser aktif! Anda akan mendapat pengingat saat waktu agenda tiba.', 'success');
      updateAgendaNotifInfoText();
      // Schedule ulang semua agenda
      loadAgendaList().forEach(item => scheduleAgendaNotif(item));
      // Update tombol notif
      const btn = document.getElementById('btnNotifPermission');
      if (btn) btn.style.display = 'none';
    } else {
      showAdminToast('Notifikasi ditolak. Aktifkan di pengaturan browser.', 'error');
      updateAgendaNotifInfoText();
    }
  });
}
window.requestAgendaNotifPermission = requestAgendaNotifPermission;

function scheduleAgendaNotif(item) {
  if (!item.tanggal || item.done) return;
  if (Notification.permission !== 'granted') return;

  const timeStr = item.jam ? `${item.tanggal}T${item.jam}:00` : `${item.tanggal}T08:00:00`;
  const targetMs = new Date(timeStr).getTime();
  const nowMs = Date.now();
  const delay = targetMs - nowMs;

  if (delay <= 0) return; // sudah lewat

  // Max setTimeout ~24.8 hari, skip jika lebih jauh
  if (delay > 2073600000) return;

  if (agendaNotifTimers[item.id]) {
    clearTimeout(agendaNotifTimers[item.id]);
  }

  agendaNotifTimers[item.id] = setTimeout(() => {
    const n = new Notification('📅 Tasks & Agenda PEP', {
      body: `${item.judul}${item.catatan ? '\n' + item.catatan : ''}`,
      icon: '/favicon.ico',
      badge: '/favicon.ico',
      tag: item.id,
      requireInteraction: true
    });
    n.onclick = () => {
      window.focus();
      n.close();
    };
    delete agendaNotifTimers[item.id];
  }, delay);
}

function initAgendaNotifications() {
  // Cek permission & tampilkan tombol jika perlu
  const btn = document.getElementById('btnNotifPermission');
  if (btn && 'Notification' in window && Notification.permission === 'default') {
    btn.style.display = 'flex';
  }

  // Schedule semua agenda yang belum lewat
  if (Notification.permission === 'granted') {
    loadAgendaList().forEach(item => scheduleAgendaNotif(item));
  }
}


// ==========================================================================
// SINKRONISASI TINGGI TABEL DOKUMEN DENGAN KALENDER (PRESISI LURUS SEJAJAR)
// ==========================================================================
function syncTableHeightWithCalendar() {
  const recentCard = document.getElementById('hopeRecentTableCard');
  const calCard = document.getElementById('hopeCalendarCard');
  if (!recentCard || !calCard) return;
  const tableResp = recentCard.querySelector('.table-responsive');
  if (!tableResp) return;

  if (window.innerWidth < 993) {
    tableResp.style.height = '';
    tableResp.style.maxHeight = '480px';
    return;
  }

  // Bersihkan inline style height sementara agar posisi naturally dihitung browser
  tableResp.style.height = '';
  tableResp.style.maxHeight = '';

  // Dapatkan posisi koordinat bounding rect kalender & tabel
  const calBottom = calCard.getBoundingClientRect().bottom;
  const tableRespTop = tableResp.getBoundingClientRect().top;
  
  // Padding & border bagian bawah recentCard
  const recentStyle = window.getComputedStyle(recentCard);
  const paddingBottom = parseFloat(recentStyle.paddingBottom) || 24;
  const borderBottom = parseFloat(recentStyle.borderBottomWidth) || 1;

  // Hitung target tinggi tabel agar sisi bawah kartu tabel presisi sejajar dengan kartu kalender
  const targetHeight = Math.floor(calBottom - paddingBottom - borderBottom - tableRespTop);

  if (targetHeight > 160) {
    tableResp.style.height = targetHeight + 'px';
    tableResp.style.maxHeight = targetHeight + 'px';
    tableResp.style.overflowY = 'auto';
  }
}
window.syncTableHeightWithCalendar = syncTableHeightWithCalendar;

// ==========================================================================
// GRAFIK DIAGRAM CAPAIAN KINERJA (e-SAKIP RESPONSIVE DYNAMIC SIZING)
// Menggunakan dynamic width & scroll horizontal otomatis agar label rapi
// ==========================================================================
// GRAFIK DIAGRAM CAPAIAN KINERJA (e-SAKIP RESPONSIVE DYNAMIC SIZING)
// Pusat Monitoring 4 Triwulan, Dynamic Width, Zero Dummy Data & Filter Cepat
// ==========================================================================
let dashboardChartCapaianTw = 'Semua';
let dashboardChartCapaianYear = (typeof window !== 'undefined' && window.serverCapaianYears && window.serverCapaianYears.length > 0) ? String(window.serverCapaianYears[0]) : '2026';
let dashboardChartCapaianMode = 'kuartal'; // 'kuartal' (default) atau 'tren-tahunan'

// ==========================================================================
// KALKULATOR STATUS 4 TRIWULAN DINAMIS PER TAHUN
// ==========================================================================
function calculateQuarterlySummary(targetYear) {
  const yr = parseInt(targetYear) || 2026;
  const twKeys = ['TW I', 'TW II', 'TW III', 'TW IV'];
  const twMonths = {
    'TW I': `Jan – Mar ${yr}`,
    'TW II': `Apr – Jun ${yr}`,
    'TW III': `Jul – Sep ${yr}`,
    'TW IV': `Okt – Des ${yr}`
  };
  const twFullTitles = {
    'TW I': 'Triwulan I',
    'TW II': 'Triwulan II',
    'TW III': 'Triwulan III',
    'TW IV': 'Triwulan IV'
  };

  const summary = {};
  twKeys.forEach(k => {
    summary[k] = {
      key: k,
      title: twFullTitles[k],
      months: twMonths[k],
      filled: false,
      count: 0,
      avgCapaian: 0,
      avgKeuangan: 0,
      totalPagu: 0,
      totalRealisasi: 0,
      predikat: 'Belum Diisi',
      badgeClass: 'badge-gray',
      color: '#64748b'
    };
  });

  const list = (typeof capaianDb !== 'undefined' && Array.isArray(capaianDb)) ? capaianDb : ((typeof window !== 'undefined' && window.serverCapaianDb) ? window.serverCapaianDb : []);
  const twPercents = { 'TW I': [], 'TW II': [], 'TW III': [], 'TW IV': [] };

  list.forEach(item => {
    const itemYr = parseInt(item.tahun || 2026);
    const k = (item.triwulan || '').trim();
    if (itemYr === yr && summary[k]) {
      summary[k].filled = true;
      summary[k].count++;
      summary[k].totalPagu += parseFloat(item.pagu_anggaran || 0);
      summary[k].totalRealisasi += parseFloat(item.realisasi_keuangan || 0);

      let p = parseFloat(item.capaian_kinerja_persen) || 0;
      if (p <= 0) {
        const target = parseFloat(item.target_tahunan || 0);
        const real = parseFloat(item.realisasi_kinerja || 0);
        if (target > 0) p = (real / target) * 100;
      }
      p = Math.min(100, Math.max(0, p));
      if (p > 0) twPercents[k].push(p);
    }
  });

  let filledTwCount = 0;
  twKeys.forEach(k => {
    const tw = summary[k];
    if (tw.filled) {
      filledTwCount++;
      if (twPercents[k].length > 0) {
        tw.avgCapaian = Math.round((twPercents[k].reduce((a, b) => a + b, 0) / twPercents[k].length) * 10) / 10;
      }
      if (tw.totalPagu > 0) {
        tw.avgKeuangan = Math.round((tw.totalRealisasi / tw.totalPagu) * 1000) / 10;
      }

      if (tw.avgCapaian >= 90) {
        tw.predikat = 'Sangat Baik';
        tw.badgeClass = 'badge-green';
        tw.color = '#10b981';
      } else if (tw.avgCapaian >= 80) {
        tw.predikat = 'Baik';
        tw.badgeClass = 'badge-blue';
        tw.color = '#3b82f6';
      } else if (tw.avgCapaian >= 70) {
        tw.predikat = 'Cukup Baik';
        tw.badgeClass = 'badge-yellow';
        tw.color = '#f59e0b';
      } else {
        tw.predikat = 'Perlu Ditingkatkan';
        tw.badgeClass = 'badge-red';
        tw.color = '#ef4444';
      }
    }
  });

  return { summary, filledTwCount, year: yr };
}
window.calculateQuarterlySummary = calculateQuarterlySummary;

// Perbarui visual 4 kartu monitoring triwulan di atas grafik sesuai tahun yang dipilih
function updateDashboardTwCards(targetYear) {
  const { summary, filledTwCount, year } = calculateQuarterlySummary(targetYear);

  const headerKelengkapan = document.getElementById('twHeaderKelengkapanText');
  if (headerKelengkapan) {
    headerKelengkapan.textContent = `${filledTwCount} dari 4 Triwulan Terisi`;
  }

  ['TW I', 'TW II', 'TW III', 'TW IV'].forEach(k => {
    const tw = summary[k];
    const badge = document.getElementById(`twBadge_${k}`);
    const months = document.getElementById(`twMonths_${k}`);
    const percent = document.getElementById(`twPercent_${k}`);
    const percentLabel = document.getElementById(`twPercentLabel_${k}`);
    const stats = document.getElementById(`twStats_${k}`);
    const actionWrap = document.getElementById(`twActionWrap_${k}`);

    if (badge) {
      badge.className = `badge ${tw.filled ? tw.badgeClass : 'badge-gray'}`;
      badge.textContent = tw.filled ? tw.predikat : 'Belum Diisi';
    }
    if (months) {
      months.textContent = tw.months;
    }
    if (percent) {
      percent.style.color = tw.filled ? tw.color : '#94a3b8';
      percent.textContent = tw.filled ? `${tw.avgCapaian}%` : '0%';
    }
    if (percentLabel) {
      percentLabel.style.color = tw.filled ? '#059669' : '#94a3b8';
      percentLabel.textContent = tw.filled ? 'Fisik' : 'Kosong';
    }
    if (stats) {
      if (tw.filled) {
        stats.innerHTML = `
          <span style="color:#334155;font-weight:600;"><strong id="twCountVal_${k}">${tw.count}</strong> Indikator</span>
          <span style="color:#cbd5e1;margin:0 4px;">•</span>
          <span id="twKeuanganVal_${k}" style="color:#2563eb;font-weight:600;">Keuangan: ${tw.avgKeuangan}%</span>
        `;
      } else {
        stats.innerHTML = `
          <span id="twKeuanganVal_${k}" style="color:#94a3b8;">Belum ada laporan</span>
        `;
      }
    }
    if (actionWrap) {
      if (tw.filled) {
        actionWrap.innerHTML = `
          <span style="font-size:11px;font-weight:700;color:#4f46e5;display:inline-flex;align-items:center;gap:4px;">
            <span>Lihat Rincian</span>
            <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
          </span>
        `;
      } else {
        actionWrap.innerHTML = `
          <span style="font-size:11px;font-weight:700;color:#2563eb;display:inline-flex;align-items:center;gap:4px;">
            <span>+ Input Data</span>
            <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
          </span>
        `;
      }
    }
  });
}
window.updateDashboardTwCards = updateDashboardTwCards;

function openCapaianRincian(twKey, year) {
  const targetYear = parseInt(year || (typeof dashboardChartCapaianYear !== 'undefined' ? dashboardChartCapaianYear : 2026)) || activeCapaianTahun;
  activeCapaianTahun = targetYear;

  if (twKey) {
    activeCapaianTriwulan = twKey;
  }

  navigateAdmin('capaian-kinerja');

  const selectTahun = document.getElementById('capaianFilterTahun');
  if (selectTahun) {
    let exists = false;
    for (let i = 0; i < selectTahun.options.length; i++) {
      if (parseInt(selectTahun.options[i].value) === activeCapaianTahun) {
        selectTahun.selectedIndex = i;
        exists = true;
        break;
      }
    }
    if (!exists) {
      const opt = document.createElement('option');
      opt.value = activeCapaianTahun;
      opt.textContent = activeCapaianTahun;
      opt.selected = true;
      selectTahun.appendChild(opt);
    }
  }

  const selectTW = document.getElementById('capaianFilterTriwulan');
  if (selectTW && twKey) {
    selectTW.value = twKey;
  }

  if (typeof renderCapaianTable === 'function') {
    renderCapaianTable();
  }

  setTimeout(() => {
    const tableEl = document.getElementById('tableCapaianKinerja') || document.querySelector('.table-responsive');
    if (tableEl) {
      tableEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
  }, 100);
}
window.openCapaianRincian = openCapaianRincian;

function openCapaianInput(twKey, year) {
  openCapaianRincian(twKey, year);
  setTimeout(() => {
    if (typeof openModalCapaian === 'function') {
      openModalCapaian('add', null, twKey || 'TW I');
    }
  }, 150);
}
window.openCapaianInput = openCapaianInput;

function setCapaianChartMode(mode) {
  dashboardChartCapaianMode = (mode === 'tren-tahunan') ? 'tren-tahunan' : 'kuartal';
  const btnKuartal = document.getElementById('btnModeKuartal');
  const btnTren = document.getElementById('btnModeTren');

  btnKuartal?.classList.remove('active');
  btnTren?.classList.remove('active');

  if (dashboardChartCapaianMode === 'kuartal') {
    btnKuartal?.classList.add('active');
  } else {
    btnTren?.classList.add('active');
  }

  initHopeActivityChart();
}
window.setCapaianChartMode = setCapaianChartMode;

function filterIndikatorTable() {
  const twKey = dashboardChartCapaianTw || 'Semua';
  const yr = parseInt(dashboardChartCapaianYear) || 2026;
  const rows = document.querySelectorAll('#tableIndikatorTriwulan tbody tr.row-indikator');
  const emptyRow = document.getElementById('rowIndikatorEmpty');
  const emptyTitle = document.getElementById('emptyIndikatorTitle');
  const emptySub = document.getElementById('emptyIndikatorSub');
  const btnInputTw = document.getElementById('btnInputSpecificTw');
  let visibleCount = 0;

  rows.forEach(r => {
    const rowTw = r.getAttribute('data-triwulan');
    const rowYr = parseInt(r.getAttribute('data-tahun') || 2026);
    const matchTw = (!twKey || twKey === 'Semua' || rowTw === twKey);
    const matchYr = (!yr || rowYr === yr);

    if (matchTw && matchYr) {
      r.style.display = '';
      visibleCount++;
    } else {
      r.style.display = 'none';
    }
  });

  if (emptyRow) {
    if (visibleCount === 0) {
      emptyRow.style.display = '';
      let friendlyName = 'periode ini';
      if (twKey === 'TW I') friendlyName = `Triwulan I Tahun ${yr}`;
      else if (twKey === 'TW II') friendlyName = `Triwulan II Tahun ${yr}`;
      else if (twKey === 'TW III') friendlyName = `Triwulan III Tahun ${yr}`;
      else if (twKey === 'TW IV') friendlyName = `Triwulan IV Tahun ${yr}`;
      else friendlyName = `Tahun ${yr}`;

      if (emptyTitle) emptyTitle.textContent = `Belum Ada Data Indikator untuk ${friendlyName}`;
      if (emptySub) emptySub.textContent = `Laporan capaian kinerja belum diinput untuk ${friendlyName}. Anda dapat menambahkannya sekarang.`;
      if (btnInputTw) {
        btnInputTw.style.display = 'inline-flex';
        btnInputTw.innerHTML = `<span>+ Input Data Capaian ${twKey !== 'Semua' ? twKey : ''}</span>`;
      }
    } else {
      emptyRow.style.display = 'none';
    }
  }

  const currentTwTitle = document.getElementById('currentTwTitle');
  if (currentTwTitle) {
    let tTitle = `Semua Triwulan (Tahun ${yr})`;
    if (twKey === 'TW I') tTitle = `Triwulan I (Januari – Maret ${yr})`;
    else if (twKey === 'TW II') tTitle = `Triwulan II (April – Juni ${yr})`;
    else if (twKey === 'TW III') tTitle = `Triwulan III (Juli – September ${yr})`;
    else if (twKey === 'TW IV') tTitle = `Triwulan IV (Oktober – Desember ${yr})`;
    currentTwTitle.textContent = tTitle;
  }
}
window.filterIndikatorTable = filterIndikatorTable;

function selectDashboardTriwulan(twKey) {
  dashboardChartCapaianTw = twKey || 'Semua';

  // 1. Update kartu visual 4 triwulan
  const cards = document.querySelectorAll('.tw-card');
  cards.forEach(c => {
    if (c.getAttribute('data-tw') === twKey) {
      c.classList.add('active');
    } else {
      c.classList.remove('active');
    }
  });

  // 2. Update tombol tab filter tabel
  const tabBtns = document.querySelectorAll('.tw-tab-btn');
  tabBtns.forEach(b => {
    if (b.getAttribute('data-tw') === twKey) {
      b.classList.add('active');
    } else {
      b.classList.remove('active');
    }
  });

  // 3. Filter baris tabel indikator
  filterIndikatorTable();
}
window.selectDashboardTriwulan = selectDashboardTriwulan;

function filterCapaianDashboardChart(tw) {
  selectDashboardTriwulan(tw);
}
window.filterCapaianDashboardChart = filterCapaianDashboardChart;

function filterCapaianDashboardYear(yr) {
  dashboardChartCapaianYear = String(yr || '2026');
  const sel = document.getElementById('filterChartCapaianYear');
  if (sel && sel.value !== dashboardChartCapaianYear) {
    sel.value = dashboardChartCapaianYear;
  }
  updateDashboardTwCards(dashboardChartCapaianYear);
  filterIndikatorTable();
  initHopeActivityChart();
}
window.filterCapaianDashboardYear = filterCapaianDashboardYear;

function scrollCapaianChart(offset) {
  const wrap = document.getElementById('capaianChartScrollWrap');
  if (wrap) {
    wrap.scrollBy({ left: offset, behavior: 'smooth' });
  }
}
window.scrollCapaianChart = scrollCapaianChart;

// Helper: Format nilai angka/teks agar rapi dan tidak merusak tampilan tooltip/label
function formatChartValue(val) {
  if (val === null || val === undefined || val === '') return '-';
  const str = String(val).trim();
  if (str.length > 20) {
    return str.substring(0, 18) + '…';
  }
  return str;
}

// Helper: Memecah teks label indikator panjang menjadi 1-3 baris rapi (font 12px tetap nyaman)
function wrapChartLabel(str, maxCharsPerLine = 15, maxLines = 3) {
  if (!str || str.trim() === '' || str.trim() === '-') return ['Indikator'];
  const text = str.trim();

  // Jika kata tunggal sangat panjang tanpa spasi (misal input uji coba 2222222...)
  if (!text.includes(' ') && text.length > maxCharsPerLine) {
    const chunks = [];
    let rem = text;
    while (rem.length > 0 && chunks.length < maxLines) {
      if (chunks.length === maxLines - 1 && rem.length > maxCharsPerLine) {
        chunks.push(rem.substring(0, maxCharsPerLine - 1) + '…');
        break;
      }
      chunks.push(rem.substring(0, maxCharsPerLine));
      rem = rem.substring(maxCharsPerLine);
    }
    return chunks;
  }

  const words = text.split(/\s+/);
  const lines = [];
  let currentLine = '';

  for (let i = 0; i < words.length; i++) {
    const word = words[i];
    if (word.length > maxCharsPerLine) {
      if (currentLine) { lines.push(currentLine); currentLine = ''; }
      const cut = word.substring(0, maxCharsPerLine - 1) + '…';
      lines.push(cut);
      if (lines.length >= maxLines) break;
      continue;
    }

    const testLine = currentLine ? (currentLine + ' ' + word) : word;
    if (testLine.length <= maxCharsPerLine) {
      currentLine = testLine;
    } else {
      if (currentLine) lines.push(currentLine);
      currentLine = word;
      if (lines.length >= maxLines) break;
    }
  }

  if (currentLine && lines.length < maxLines) {
    lines.push(currentLine);
  }

  return lines.length > 0 ? lines : [text.substring(0, maxCharsPerLine)];
}

// Plugin: Tampilkan persentase presisi tepat di atas setiap batang chart
const chartTopPercentPlugin = {
  id: 'chartTopPercent',
  afterDatasetsDraw(chart) {
    const { ctx, chartArea } = chart;
    if (!chartArea) return;
    ctx.save();
    ctx.font = 'bold 11.5px "Plus Jakarta Sans", sans-serif';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'bottom';

    chart.data.datasets.forEach((dataset, datasetIdx) => {
      const meta = chart.getDatasetMeta(datasetIdx);
      if (!meta.visible) return;

      meta.data.forEach((bar, index) => {
        const val = dataset.data[index];
        if (val !== undefined && val !== null) {
          let col = '#059669'; // hijau (>=90%)
          if (val < 70) col = '#dc2626'; // merah (<70%)
          else if (val < 80) col = '#d97706'; // kuning (70-79%)
          else if (val < 90) col = '#2563eb'; // biru (80-89%)

          ctx.fillStyle = col;
          const posY = Math.max(chartArea.top + 14, bar.y - 6);
          ctx.fillText(val + '%', bar.x, posY);
        }
      });
    });
    ctx.restore();
  }
};

// Plugin: Garis acuan target 100% (Dashed Target Reference Line)
const chartTarget100LinePlugin = {
  id: 'chartTarget100Line',
  afterDraw(chart) {
    const { ctx, chartArea, scales } = chart;
    if (!scales || !scales.y || !chartArea) return;

    const yVal = scales.y.getPixelForValue(100);
    if (isNaN(yVal) || yVal < chartArea.top || yVal > chartArea.bottom) return;

    ctx.save();
    ctx.strokeStyle = 'rgba(16, 185, 129, 0.45)';
    ctx.lineWidth = 1.5;
    ctx.setLineDash([5, 5]);
    ctx.beginPath();
    ctx.moveTo(chartArea.left, yVal);
    ctx.lineTo(chartArea.right, yVal);
    ctx.stroke();

    ctx.fillStyle = '#059669';
    ctx.font = '600 10.5px "Plus Jakarta Sans", sans-serif';
    ctx.textAlign = 'right';
    ctx.fillText('Target Ideal 100%', chartArea.right - 8, yVal - 4);
    ctx.restore();
  }
};

// ==========================================================================
// RENDER GRAFIK 4 TRIWULAN (Sederhana, Bersih, & Sangat Mudah Dilihat)
// ==========================================================================
function renderQuarterlyChart(canvas, innerContainer, scrollWrap, scrollHint, navButtons) {
  if (chartHopeActivityInstance) {
    chartHopeActivityInstance.destroy();
    chartHopeActivityInstance = null;
  }
  if (scrollHint) scrollHint.style.display = 'none';
  if (navButtons) navButtons.style.display = 'none';

  innerContainer.style.width = '100%';
  innerContainer.style.minWidth = '100%';

  const targetYear = parseInt(dashboardChartCapaianYear) || 2026;
  const { summary: twDataMap } = calculateQuarterlySummary(targetYear);
  const twLabels = [
    ['Triwulan I', `Jan – Mar ${targetYear}`],
    ['Triwulan II', `Apr – Jun ${targetYear}`],
    ['Triwulan III', `Jul – Sep ${targetYear}`],
    ['Triwulan IV', `Okt – Des ${targetYear}`]
  ];
  const twKeys = ['TW I', 'TW II', 'TW III', 'TW IV'];

  const values = [];
  const bgColors = [];
  const borderColors = [];

  twKeys.forEach(k => {
    const tw = (twDataMap && twDataMap[k]) ? twDataMap[k] : { avgCapaian: 0, filled: false };
    if (tw.filled && tw.avgCapaian > 0) {
      values.push(tw.avgCapaian);
      if (tw.avgCapaian >= 90) {
        bgColors.push('rgba(16, 185, 129, 0.85)');
        borderColors.push('#10b981');
      } else if (tw.avgCapaian >= 80) {
        bgColors.push('rgba(59, 130, 246, 0.85)');
        borderColors.push('#3b82f6');
      } else if (tw.avgCapaian >= 70) {
        bgColors.push('rgba(245, 158, 11, 0.85)');
        borderColors.push('#f59e0b');
      } else {
        bgColors.push('rgba(239, 68, 68, 0.85)');
        borderColors.push('#ef4444');
      }
    } else {
      values.push(0);
      bgColors.push('rgba(241, 245, 249, 0.6)');
      borderColors.push('#cbd5e1');
    }
  });

  chartHopeActivityInstance = new Chart(canvas, {
    type: 'bar',
    data: {
      labels: twLabels,
      datasets: [{
        label: '% Capaian Kinerja',
        data: values,
        backgroundColor: bgColors,
        borderColor: borderColors,
        borderWidth: 1.5,
        borderRadius: { topLeft: 8, topRight: 8 },
        barPercentage: 0.45,
        categoryPercentage: 0.72,
        maxBarThickness: 68
      }]
    },
    plugins: [{
      id: 'quarterlyTopLabel',
      afterDatasetsDraw(chart) {
        const { ctx, chartArea } = chart;
        if (!chartArea) return;
        ctx.save();
        ctx.textAlign = 'center';
        ctx.textBaseline = 'bottom';

        chart.data.datasets.forEach((dataset, datasetIdx) => {
          const meta = chart.getDatasetMeta(datasetIdx);
          if (!meta.visible) return;

          meta.data.forEach((bar, index) => {
            const val = dataset.data[index];
            const k = twKeys[index];
            const tw = (twDataMap && twDataMap[k]) ? twDataMap[k] : null;

            if (tw && tw.filled && val > 0) {
              ctx.font = 'bold 12px "Plus Jakarta Sans", sans-serif';
              let col = '#059669';
              if (val < 70) col = '#dc2626';
              else if (val < 80) col = '#d97706';
              else if (val < 90) col = '#2563eb';
              ctx.fillStyle = col;
              const posY = Math.max(chartArea.top + 14, bar.y - 6);
              ctx.fillText(val + '%', bar.x, posY);
            } else {
              ctx.font = '600 11px "Plus Jakarta Sans", sans-serif';
              ctx.fillStyle = '#94a3b8';
              ctx.fillText('Belum Diisi', bar.x, chartArea.bottom - 10);
            }
          });
        });
        ctx.restore();
      }
    }, chartTarget100LinePlugin],
    options: {
      responsive: true,
      maintainAspectRatio: false,
      layout: { padding: { top: 28, bottom: 18, left: 14, right: 18 } },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: 'rgba(15, 23, 42, 0.96)',
          titleFont: { family: "'Plus Jakarta Sans', sans-serif", size: 12.5, weight: '700' },
          bodyFont: { family: "'Plus Jakarta Sans', sans-serif", size: 11.5 },
          padding: 12,
          cornerRadius: 10,
          callbacks: {
            label: function(context) {
              const idx = context.dataIndex;
              const k = twKeys[idx];
              const tw = (twDataMap && twDataMap[k]) ? twDataMap[k] : null;
              if (tw && tw.filled) {
                return `Rata-rata Capaian: ${tw.avgCapaian}% (${tw.predikat}) • ${tw.count} Indikator`;
              }
              return 'Status: Belum ada laporan diinput pada periode ini';
            }
          }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: {
            color: '#1e293b',
            font: { family: "'Plus Jakarta Sans', sans-serif", size: 12, weight: '700' },
            padding: 10,
            maxRotation: 0,
            minRotation: 0,
            autoSkip: false
          }
        },
        y: {
          grid: { color: 'rgba(226, 232, 240, 0.8)', borderDash: [4, 4] },
          ticks: {
            color: '#64748b',
            font: { family: "'Plus Jakarta Sans', sans-serif", size: 11.5, weight: '600' },
            callback: function(v) { return v + '%'; },
            stepSize: 20
          },
          beginAtZero: true,
          suggestedMax: 118
        }
      }
    }
  });
}


// ==========================================================================
// RENDER GRAFIK ANALISIS TREN & PERBANDINGAN ANTAR-TAHUN (Bahasa Awam)
// ==========================================================================
function renderTrenTahunanChart(innerContainer, scrollWrap, scrollHint, navButtons) {
  if (chartHopeActivityInstance) {
    chartHopeActivityInstance.destroy();
    chartHopeActivityInstance = null;
  }
  if (scrollHint) scrollHint.style.display = 'none';
  if (navButtons) navButtons.style.display = 'none';

  let rawList = (typeof capaianDb !== 'undefined' && Array.isArray(capaianDb)) ? [...capaianDb] : [];
  if (rawList.length === 0) {
    if (innerContainer) {
      innerContainer.style.width = '100%';
      innerContainer.style.minWidth = '100%';
      innerContainer.innerHTML = `
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:340px;text-align:center;padding:24px;background:#f8fafc;border-radius:12px;border:1.5px dashed #cbd5e1;box-sizing:border-box;">
          <h4 style="font-size:15px;font-weight:700;color:#0f172a;margin:0 0 6px;">Belum Ada Data Tahunan</h4>
          <p style="font-size:13px;color:#64748b;max-width:420px;margin:0 0 16px;line-height:1.5;">Silakan input data capaian kinerja pada modul Capaian Kinerja untuk membandingkan kinerja antar-tahun.</p>
          <button type="button" onclick="navigateAdmin('capaian-kinerja')" class="btn btn-primary btn-sm" style="font-weight:600;font-size:12.5px;border-radius:8px;padding:8px 16px;cursor:pointer;background:#2563eb;color:#ffffff;border:none;">
            + Input Data Capaian
          </button>
        </div>
      `;
    }
    return;
  }

  // Kelompokkan data murni per tahun
  const yearGroups = {};
  rawList.forEach(item => {
    const yr = item.tahun || 2026;
    if (!yearGroups[yr]) yearGroups[yr] = [];
    let rawP = parseFloat(item.capaian_kinerja_persen) || 0;
    if (rawP <= 0) {
      const target = parseFloat(item.target_tahunan) || 0;
      const real = parseFloat(item.realisasi_kinerja) || 0;
      if (target > 0) rawP = (real / target) * 100;
    }
    const persen = Math.min(100, Math.max(0, rawP));
    if (persen > 0) yearGroups[yr].push(persen);
  });

  const years = Object.keys(yearGroups).sort();
  const yearAverages = [];
  const yearColors = [];
  const yearBorders = [];
  const yearDescriptions = [];

  years.forEach((yr) => {
    const arr = yearGroups[yr];
    const avg = arr.length > 0 ? Math.round((arr.reduce((a, b) => a + b, 0) / arr.length) * 10) / 10 : 0;
    yearAverages.push(avg);
    if (avg >= 90) {
      yearColors.push('rgba(16, 185, 129, 0.85)');
      yearBorders.push('#10b981');
      yearDescriptions.push('Target Tercapai Sangat Baik');
    } else if (avg >= 80) {
      yearColors.push('rgba(59, 130, 246, 0.85)');
      yearBorders.push('#3b82f6');
      yearDescriptions.push('Target Tercapai Baik');
    } else if (avg >= 70) {
      yearColors.push('rgba(245, 158, 11, 0.85)');
      yearBorders.push('#f59e0b');
      yearDescriptions.push('Cukup Baik (Sedang)');
    } else {
      yearColors.push('rgba(239, 68, 68, 0.85)');
      yearBorders.push('#ef4444');
      yearDescriptions.push('Perlu Peningkatan Kinerja');
    }
  });

  innerContainer.style.width = '100%';
  innerContainer.style.minWidth = '100%';
  innerContainer.innerHTML = '<canvas id="chartHopeActivity"></canvas>';
  const canvas = document.getElementById('chartHopeActivity');

  chartHopeActivityInstance = new Chart(canvas, {
    type: 'bar',
    data: {
      labels: years.map(y => `Tahun ${y}`),
      datasets: [{
        label: 'Rata-rata Capaian Kinerja',
        data: yearAverages,
        backgroundColor: yearColors,
        borderColor: yearBorders,
        borderWidth: 1.5,
        borderRadius: { topLeft: 8, topRight: 8 },
        barPercentage: years.length === 1 ? 0.35 : 0.48,
        categoryPercentage: 0.65,
        maxBarThickness: 75
      }]
    },
    plugins: [chartTopPercentPlugin, chartTarget100LinePlugin],
    options: {
      responsive: true,
      maintainAspectRatio: false,
      layout: { padding: { top: 28, bottom: 8, left: 14, right: 18 } },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: 'rgba(15, 23, 42, 0.96)',
          titleFont: { family: "'Plus Jakarta Sans', sans-serif", size: 13, weight: '700' },
          bodyFont: { family: "'Plus Jakarta Sans', sans-serif", size: 12 },
          padding: 12,
          cornerRadius: 10,
          callbacks: {
            label: function(context) {
              const idx = context.dataIndex;
              const val = context.parsed.y;
              return `Rata-rata Capaian: ${val}% (${yearDescriptions[idx]})`;
            }
          }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: {
            color: '#1e293b',
            font: { family: "'Plus Jakarta Sans', sans-serif", size: 13, weight: '700' },
            padding: 8
          }
        },
        y: {
          grid: { color: 'rgba(226, 232, 240, 0.8)', borderDash: [4, 4] },
          ticks: {
            color: '#64748b',
            font: { family: "'Plus Jakarta Sans', sans-serif", size: 11.5, weight: '600' },
            callback: function(v) { return v + '%'; },
            stepSize: 20
          },
          beginAtZero: true,
          suggestedMax: 118
        }
      }
    }
  });
}

// ==========================================================================
// RENDER GRAFIK ANALISIS INDIKATOR SASARAN (100% DATA ASLI, ZERO DUMMY)
// ==========================================================================
function initHopeActivityChart() {
  const scrollWrap = document.getElementById('capaianChartScrollWrap');
  const innerContainer = document.getElementById('capaianChartInner');
  const scrollHint = document.getElementById('capaianChartScrollHint');
  const scrollHintText = document.getElementById('capaianChartScrollHintText');
  const navButtons = document.getElementById('capaianNavButtons');

  if (!innerContainer) return;

  // Jika mode yang dipilih adalah 'kuartal' (Perbandingan 4 Triwulan):
  if (dashboardChartCapaianMode === 'kuartal') {
    let canvas = document.getElementById('chartHopeActivity');
    if (!canvas) {
      innerContainer.innerHTML = '<canvas id="chartHopeActivity"></canvas>';
      canvas = document.getElementById('chartHopeActivity');
    }
    renderQuarterlyChart(canvas, innerContainer, scrollWrap, scrollHint, navButtons);
    return;
  }

  // Jika mode yang dipilih adalah 'tren-tahunan':
  if (dashboardChartCapaianMode === 'tren-tahunan') {
    renderTrenTahunanChart(innerContainer, scrollWrap, scrollHint, navButtons);
    return;
  }

  // Bersihkan chart lama jika ada
  if (chartHopeActivityInstance) {
    chartHopeActivityInstance.destroy();
    chartHopeActivityInstance = null;
  }

  // Ambil data murni dari database (capaianDb)
  let list = (typeof capaianDb !== 'undefined' && Array.isArray(capaianDb)) ? [...capaianDb] : [];

  // Filter tahun jika dipilih
  if (dashboardChartCapaianYear && dashboardChartCapaianYear !== 'Semua') {
    list = list.filter(item => String(item.tahun || '').trim() === String(dashboardChartCapaianYear).trim());
  }

  // Filter triwulan jika dipilih
  if (dashboardChartCapaianTw && dashboardChartCapaianTw !== 'Semua') {
    list = list.filter(item => (item.triwulan || '').trim() === dashboardChartCapaianTw);
  }

  // ========================================================================
  // EMPTY STATE ELEGAN & RAMAH PENGGUNA (ZERO DATA DUMMY!)
  // Jika pengguna belum mengisi data triwulan tertentu (misal TW II),
  // tampilkan pesan informatif yang jelas tanpa menampilkan grafik palsu.
  // ========================================================================
  if (list.length === 0) {
    if (scrollHint) scrollHint.style.display = 'none';
    if (navButtons) navButtons.style.display = 'none';

    innerContainer.style.width = '100%';
    innerContainer.style.minWidth = '100%';

    let twFriendly = 'periode ini';
    if (dashboardChartCapaianTw === 'TW I') twFriendly = 'Triwulan 1 (Jan - Mar)';
    else if (dashboardChartCapaianTw === 'TW II') twFriendly = 'Triwulan 2 (Apr - Jun)';
    else if (dashboardChartCapaianTw === 'TW III') twFriendly = 'Triwulan 3 (Jul - Sep)';
    else if (dashboardChartCapaianTw === 'TW IV') twFriendly = 'Triwulan 4 (Okt - Des)';

    innerContainer.innerHTML = `
      <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:340px;text-align:center;padding:24px;background:#f8fafc;border-radius:12px;border:1.5px dashed #cbd5e1;box-sizing:border-box;">
        <div style="width:48px;height:48px;border-radius:50%;background:#e0f2fe;display:flex;align-items:center;justify-content:center;color:#0284c7;margin-bottom:12px;">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <h4 style="font-size:15px;font-weight:700;color:#0f172a;margin:0 0 6px;">Data ${twFriendly} Belum Ada / Belum Diisi</h4>
        <p style="font-size:13px;color:#64748b;max-width:440px;margin:0 0 16px;line-height:1.5;">
          Anda belum menginput data capaian kinerja untuk ${twFriendly}. Sistem sengaja tidak menampilkan data tiruan agar analisis Anda tetap akurat dan sesuai fakta data asli.
        </p>
        <button type="button" onclick="navigateAdmin('capaian-kinerja')" class="btn btn-primary btn-sm" style="display:inline-flex;align-items:center;gap:6px;font-weight:700;font-size:12.5px;border-radius:8px;padding:8px 18px;cursor:pointer;background:#2563eb;color:#ffffff;border:none;box-shadow:0 2px 4px rgba(37,99,235,0.2);">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
          <span>Buka Modul &amp; Input Data Capaian</span>
        </button>
      </div>
    `;
    return;
  }

  // Jika ada data riil, pastikan canvas siap digunakan
  let canvas = document.getElementById('chartHopeActivity');
  if (!canvas) {
    innerContainer.innerHTML = '<canvas id="chartHopeActivity"></canvas>';
    canvas = document.getElementById('chartHopeActivity');
  }
  if (!canvas || typeof Chart === 'undefined') return;

  // ========================================================================
  // KALKULASI LEBAR DINAMIS (DYNAMIC WIDTH & RESPONSIVE SCROLL SIZING)
  // Alokasi lebar 150px per kolom agar font 12px tetap horizontal & rapi
  // ========================================================================
  const minColWidth = 150;
  const numItems = list.length;
  const calculatedWidth = (numItems * minColWidth) + 90;
  const containerWidth = scrollWrap ? (scrollWrap.clientWidth || scrollWrap.getBoundingClientRect().width) : 750;

  if (calculatedWidth > containerWidth) {
    innerContainer.style.width = calculatedWidth + 'px';
    innerContainer.style.minWidth = calculatedWidth + 'px';
    if (scrollHint) scrollHint.style.display = 'flex';
    if (scrollHintText) scrollHintText.textContent = `Geser ke kanan untuk melihat seluruh ${numItems} indikator`;
    if (navButtons) navButtons.style.display = 'inline-flex';
  } else {
    innerContainer.style.width = '100%';
    innerContainer.style.minWidth = '100%';
    if (scrollHint) scrollHint.style.display = 'none';
    if (navButtons) navButtons.style.display = 'none';
  }

  canvas.removeAttribute('width');
  canvas.removeAttribute('height');
  canvas.style.width = '100%';
  canvas.style.height = '100%';

  const labels = [];
  const fullLabels = [];
  const percentages = [];
  const bgColors = [];
  const borderColors = [];
  const itemMetas = [];

  let totalValidPersen = 0;
  let countValid = 0;

  list.forEach((item, idx) => {
    let rawP = parseFloat(item.capaian_kinerja_persen) || 0;
    if (rawP <= 0) {
      const target = parseFloat(item.target_tahunan) || 0;
      const real = parseFloat(item.realisasi_kinerja) || 0;
      if (target > 0) rawP = (real / target) * 100;
    }
    const persen = Math.min(100, Math.max(0, rawP));

    if (persen > 0) {
      totalValidPersen += persen;
      countValid++;
    }

    const rawTitle = item.indikator && item.indikator.trim() !== '' && item.indikator.trim() !== '-'
      ? item.indikator.trim()
      : `Indikator ${idx + 1}`;

    labels.push(wrapChartLabel(rawTitle, 15, 3));
    fullLabels.push(rawTitle);
    percentages.push(Math.round(persen * 10) / 10);

    let pred = (item.predikat_kinerja || '').toLowerCase();
    let ramahText = 'Cukup Baik';
    if (pred.includes('sangat') || persen >= 90) {
      bgColors.push('rgba(16, 185, 129, 0.85)');
      borderColors.push('#10b981');
      ramahText = 'Target Tercapai Sangat Baik';
    } else if (pred.includes('tinggi') || (persen >= 80 && persen < 90)) {
      bgColors.push('rgba(59, 130, 246, 0.85)');
      borderColors.push('#3b82f6');
      ramahText = 'Target Tercapai Baik';
    } else if (pred.includes('sedang') || (persen >= 70 && persen < 80)) {
      bgColors.push('rgba(245, 158, 11, 0.85)');
      borderColors.push('#f59e0b');
      ramahText = 'Cukup Baik (Sedang)';
    } else {
      bgColors.push('rgba(239, 68, 68, 0.85)');
      borderColors.push('#ef4444');
      ramahText = 'Perlu Ditingkatkan';
    }

    itemMetas.push({
      sasaran: item.sasaran || '-',
      target: item.target_tahunan !== null && item.target_tahunan !== undefined ? item.target_tahunan : '-',
      realisasi: item.realisasi_kinerja !== null && item.realisasi_kinerja !== undefined ? item.realisasi_kinerja : '-',
      satuan: item.satuan || '',
      ramahPredikat: ramahText,
      triwulan: item.triwulan || '-'
    });
  });

  const avgVal = countValid > 0 ? Math.round((totalValidPersen / countValid) * 10) / 10 : 0;
  const statAvgEl = document.getElementById('hopeStatCapaianAvg');
  if (statAvgEl) statAvgEl.textContent = avgVal > 0 ? avgVal + '%' : '0%';

  const mCardCapaian = document.getElementById('metricCapaianAvg');
  if (mCardCapaian) mCardCapaian.textContent = avgVal > 0 ? avgVal + '%' : '-';

  chartHopeActivityInstance = new Chart(canvas, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [
        {
          label: '% Capaian Kinerja',
          data: percentages,
          backgroundColor: bgColors,
          borderColor: borderColors,
          borderWidth: 1.5,
          borderRadius: { topLeft: 8, topRight: 8 },
          barPercentage: 0.52,
          categoryPercentage: 0.84,
          maxBarThickness: 56
        }
      ]
    },
    plugins: [chartTopPercentPlugin, chartTarget100LinePlugin],
    options: {
      responsive: true,
      maintainAspectRatio: false,
      layout: {
        padding: {
          top: 28,
          bottom: 6,
          left: 10,
          right: 18
        }
      },
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          backgroundColor: 'rgba(15, 23, 42, 0.96)',
          titleColor: '#ffffff',
          bodyColor: '#cbd5e1',
          borderColor: 'rgba(255, 255, 255, 0.12)',
          borderWidth: 1,
          titleFont: { family: "'Plus Jakarta Sans', sans-serif", size: 12.5, weight: '700' },
          bodyFont: { family: "'Plus Jakarta Sans', sans-serif", size: 11.5, weight: '500' },
          padding: 13,
          boxPadding: 6,
          cornerRadius: 10,
          callbacks: {
            title: function(items) {
              const idx = items[0].dataIndex;
              const raw = fullLabels[idx] || items[0].label;
              return raw.length > 55 ? raw.substring(0, 52) + '…' : raw;
            },
            beforeBody: function(items) {
              const idx = items[0].dataIndex;
              const m = itemMetas[idx];
              const tVal = formatChartValue(m.target);
              const rVal = formatChartValue(m.realisasi);
              return `Sasaran: ${m.sasaran}\nTarget: ${tVal} ${m.satuan} | Hasil Nyata: ${rVal} ${m.satuan} (${m.triwulan})`;
            },
            label: function(context) {
              const idx = context.dataIndex;
              const m = itemMetas[idx];
              return `Capaian: ${context.parsed.y}% (${m.ramahPredikat})`;
            }
          }
        }
      },
      scales: {
        x: {
          grid: {
            display: false,
            drawBorder: false
          },
          ticks: {
            color: '#334155',
            font: {
              family: "'Plus Jakarta Sans', sans-serif",
              size: 12,
              weight: '600'
            },
            autoSkip: false,
            maxRotation: 0,
            minRotation: 0,
            padding: 10
          }
        },
        y: {
          border: {
            dash: [4, 4],
            display: false
          },
          grid: {
            color: 'rgba(226, 232, 240, 0.8)',
            borderDash: [4, 4]
          },
          ticks: {
            color: '#64748b',
            font: { family: "'Plus Jakarta Sans', sans-serif", size: 11.5, weight: '600' },
            callback: function(v) { return v + '%'; },
            stepSize: 20
          },
          beginAtZero: true,
          suggestedMax: 118
        }
      }
    }
  });
}

// Re-evaluasi ukuran dinamis saat resize window
let capaianResizeTimer = null;
window.addEventListener('resize', () => {
  clearTimeout(capaianResizeTimer);
  capaianResizeTimer = setTimeout(() => {
    if (document.getElementById('chartHopeActivity')) {
      initHopeActivityChart();
    }
  }, 180);
});



let chartModuleDistributionInstance = null;

function initModuleDistributionChart() {
  const canvas = document.getElementById('chartModuleDistribution');
  if (!canvas || typeof Chart === 'undefined') return;

  if (chartModuleDistributionInstance) {
    chartModuleDistributionInstance.destroy();
    chartModuleDistributionInstance = null;
  }

  const rjM = (adminDb['renja-murni'] || []).length;
  const rjP = (adminDb['renja-perubahan'] || []).length;
  const pkM = (adminDb['pk-murni'] || []).length;
  const pkP = (adminDb['pk-perubahan'] || []).length;
  const dpaM = (adminDb['dpa-murni'] || []).length;
  const dpaP = (adminDb['dpa-perubahan'] || []).length;
  const sm = (adminDb['surat-masuk'] || []).length;
  const sk = (adminDb['surat-keluar'] || []).length;

  const dataValues = [rjM, rjP, pkM, pkP, dpaM, dpaP, sm, sk];
  const maxVal = Math.max(...dataValues, 5);

  chartModuleDistributionInstance = new Chart(canvas, {
    type: 'bar',
    data: {
      labels: ['RJ-M', 'RJ-P', 'PK-M', 'PK-P', 'DPA-M', 'DPA-P', 'S-Msk', 'S-Klr'],
      datasets: [{
        label: 'Jumlah Dokumen',
        data: dataValues,
        backgroundColor: [
          '#3b82f6', '#60a5fa',
          '#10b981', '#34d399',
          '#f59e0b', '#fbbf24',
          '#8b5cf6', '#a78bfa'
        ],
        borderRadius: 5,
        borderSkipped: false,
        maxBarThickness: 18
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      layout: {
        padding: { top: 4, bottom: 0, left: 0, right: 0 }
      },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#1e293b',
          titleFont: { family: "'Plus Jakarta Sans', sans-serif", size: 12, weight: '700' },
          bodyFont: { family: "'Plus Jakarta Sans', sans-serif", size: 11 },
          padding: 10,
          cornerRadius: 8,
          callbacks: {
            title: function(items) {
              const fullNames = [
                'Renja Murni', 'Renja Perubahan',
                'PK Murni', 'PK Perubahan',
                'DPA Murni', 'DPA Perubahan',
                'Surat Masuk', 'Surat Keluar'
              ];
              const idx = items[0].dataIndex;
              return fullNames[idx] || items[0].label;
            },
            label: function(context) {
              return ` Total: ${context.parsed.y} Dokumen`;
            }
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          suggestedMax: maxVal,
          grid: { color: '#f1f5f9' },
          ticks: {
            stepSize: 1,
            font: { family: "'Plus Jakarta Sans', sans-serif", size: 10, weight: '600' },
            color: '#94a3b8'
          }
        },
        x: {
          grid: { display: false },
          ticks: {
            font: { family: "'Plus Jakarta Sans', sans-serif", size: 8.5, weight: '700' },
            color: '#64748b',
            maxRotation: 0,
            minRotation: 0,
            autoSkip: false
          }
        }
      }
    }
  });
}

// ==========================================================================
// GRAFIK CHART.JS ADMIN (DONUT CHART STATUS DOKUMEN & MODUL DISTRIBUTION)
// ==========================================================================
function initAdminChart() {
  initHopeActivityChart();
  initModuleDistributionChart();

  const ctx = document.getElementById('chartDonutStatus') || document.getElementById('chartAdminPdf');
  if (!ctx || typeof Chart === 'undefined') return;

  if (chartAdminInstance) {
    chartAdminInstance.destroy();
    chartAdminInstance = null;
  }

  // Hitung status riil dari seluruh dokumen di adminDb
  let countLengkap = 0, countDiproses = 0, countPerluUpdate = 0, countTerkirim = 0;
  for (const key in adminDb) {
    adminDb[key].forEach(item => {
      const st = (item.status || '').toLowerCase();
      if (st === 'lengkap' || st === 'selesai') {
        countLengkap++;
      } else if (st === 'diproses') {
        countDiproses++;
      } else if (st === 'perlu update') {
        countPerluUpdate++;
      } else if (st === 'terkirim' || st === 'aktif') {
        countTerkirim++;
      } else {
        countLengkap++;
      }
    });
  }

  const isDonut = ctx.id === 'chartDonutStatus';

  if (isDonut) {
    const totalCount = countLengkap + countDiproses + countPerluUpdate + countTerkirim;
    const isZero = totalCount === 0;

    chartAdminInstance = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: isZero ? ['Belum ada dokumen'] : ['Lengkap', 'Diproses', 'Perlu Update', 'Terkirim'],
        datasets: [{
          data: isZero ? [1] : [countLengkap, countDiproses, countPerluUpdate, countTerkirim],
          backgroundColor: isZero ? ['#e2e8f0'] : ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6'],
          borderWidth: 2,
          borderColor: '#ffffff',
          hoverOffset: isZero ? 0 : 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '72%',
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#1e293b',
            titleFont: { family: "'Plus Jakarta Sans', sans-serif", size: 12, weight: '700' },
            bodyFont: { family: "'Plus Jakarta Sans', sans-serif", size: 11 },
            padding: 10,
            cornerRadius: 8,
            callbacks: {
              label: function(context) {
                if (isZero) return ' Belum ada data dokumen';
                const label = context.label || '';
                const val = context.parsed || 0;
                return ` ${label}: ${val} Dokumen`;
              }
            }
          }
        }
      }
    });
  } else {
    // Fallback jika menggunakan bar chart
    chartAdminInstance = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Renja Murni', 'Renja Perub.', 'PK Murni', 'PK Perub.', 'DPA Murni', 'DPA Perub.', 'Surat Masuk', 'Surat Keluar'],
        datasets: [{
          label: 'Dokumen Terdata (T.A. 2026)',
          data: [
            adminDb['renja-murni'].length,
            adminDb['renja-perubahan'].length,
            adminDb['pk-murni'].length,
            adminDb['pk-perubahan'].length,
            adminDb['dpa-murni'].length,
            adminDb['dpa-perubahan'].length,
            adminDb['surat-masuk'].length,
            adminDb['surat-keluar'].length
          ],
          backgroundColor: '#2b6cb0',
          borderRadius: 4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: {
            beginAtZero: true,
            grid: { color: '#e2e8f0' },
            ticks: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 }, stepSize: 1 }
          },
          x: {
            grid: { display: false },
            ticks: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 11, weight: '600' } }
          }
        }
      }
    });
  }
}

// Toggle Sidebar Nav menggunakan Logo SIM-PEP (Buka / Tutup Menu Navigasi)
function toggleAdminSidebar() {
  const sidebar = document.getElementById('adminSidebar');
  if (!sidebar) return;

  if (window.innerWidth <= 900) {
    sidebar.classList.toggle('open');
  } else {
    const isCollapsed = sidebar.classList.toggle('collapsed');
    document.body.classList.toggle('sidebar-collapsed', isCollapsed);

    const brandHeader = sidebar.querySelector('.sidebar-brand-header');
    if (brandHeader) {
      brandHeader.setAttribute('title', isCollapsed ? 'Klik Logo untuk Buka Menu Navigasi' : 'Klik Logo untuk Tutup Menu Navigasi');
    }

    try {
      localStorage.setItem('adminSidebarCollapsed', isCollapsed ? 'true' : 'false');
    } catch (e) {}

    // Resize chart secara mulus selama dan setelah transisi CSS
    const resizeCharts = () => {
      try {
        if (typeof chartAdminInstance !== 'undefined' && chartAdminInstance) {
          chartAdminInstance.resize();
        }
        if (typeof chartHopeActivityInstance !== 'undefined' && chartHopeActivityInstance) {
          chartHopeActivityInstance.resize();
        }
        if (typeof chartModuleDistributionInstance !== 'undefined' && chartModuleDistributionInstance) {
          chartModuleDistributionInstance.resize();
        }
        if (typeof chartDonutStatusInstance !== 'undefined' && chartDonutStatusInstance) {
          chartDonutStatusInstance.resize();
        }
        if (typeof syncTableHeightWithCalendar === 'function') {
          syncTableHeightWithCalendar();
        }
        window.dispatchEvent(new Event('resize'));
      } catch (err) {}
    };

    requestAnimationFrame(resizeCharts);
    setTimeout(resizeCharts, 50);
    setTimeout(resizeCharts, 120);
    setTimeout(resizeCharts, 200);
    setTimeout(resizeCharts, 300);
    setTimeout(resizeCharts, 450);
  }
}
window.toggleAdminSidebar = toggleAdminSidebar;
window.onSidebarBrandClick = toggleAdminSidebar;

function initAdminSidebar() {
  try {
    const sidebar = document.getElementById('adminSidebar');
    const saved = localStorage.getItem('adminSidebarCollapsed');
    if (saved === 'true' && window.innerWidth > 900) {
      if (sidebar) {
        sidebar.classList.add('collapsed');
        document.body.classList.add('sidebar-collapsed');
        const brandHeader = sidebar.querySelector('.sidebar-brand-header');
        if (brandHeader) brandHeader.setAttribute('title', 'Klik Logo untuk Buka Menu Navigasi');
      }
    } else {
      if (sidebar) {
        sidebar.classList.remove('collapsed');
        document.body.classList.remove('sidebar-collapsed');
        const brandHeader = sidebar.querySelector('.sidebar-brand-header');
        if (brandHeader) brandHeader.setAttribute('title', 'Klik Logo untuk Tutup Menu Navigasi');
      }
    }
  } catch (e) {}
}

window.addEventListener('resize', () => {
  if (window.innerWidth <= 900) {
    document.body.classList.remove('sidebar-collapsed');
    document.getElementById('adminSidebar')?.classList.remove('collapsed');
  } else {
    try {
      if (localStorage.getItem('adminSidebarCollapsed') === 'true') {
        document.body.classList.add('sidebar-collapsed');
        document.getElementById('adminSidebar')?.classList.add('collapsed');
      }
    } catch (e) {}
  }
});

// Inisialisasi Aplikasi Admin SIM-PEP
let isAdminInitialized = false;
function initAdminApplication() {
  if (isAdminInitialized) return;
  isAdminInitialized = true;

  try { initAdminSidebar(); } catch (e) { console.error('initAdminSidebar error:', e); }
  try { renderAllAdminTables(); } catch (e) { console.error('renderAllAdminTables error:', e); }
  try { initAdminChart(); } catch (e) { console.error('initAdminChart error:', e); }
  try { setupAdminSearchFilters(); } catch (e) { console.error('setupAdminSearchFilters error:', e); }
  try { initTableLongPressSelection(); } catch (e) { console.error('initTableLongPressSelection error:', e); }
  try { updateDynamicYearFilters(); } catch (e) { console.error('updateDynamicYearFilters error:', e); }
  try { startAppbarClock(); } catch (e) { console.error('startAppbarClock error:', e); }
  try { renderTaskList('today'); updateCalendarAgendaDots(); } catch (e) { console.error('renderTaskList error:', e); }
  try { initAgendaNotifications(); } catch (e) { console.error('initAgendaNotifications error:', e); }
  try {

    syncTableHeightWithCalendar();
    setTimeout(syncTableHeightWithCalendar, 200);
    setTimeout(syncTableHeightWithCalendar, 600);
  } catch (e) { console.error('syncTableHeightWithCalendar error:', e); }

  try {
    if (window.ResizeObserver) {
      const calCard = document.getElementById('hopeCalendarCard');
      if (calCard) {
        const roCal = new ResizeObserver(() => {
          window.requestAnimationFrame(syncTableHeightWithCalendar);
        });
        roCal.observe(calCard);
      }
      const colRight = document.querySelector('.hope-col-right');
      if (colRight) {
        const roCol = new ResizeObserver(() => {
          window.requestAnimationFrame(syncTableHeightWithCalendar);
        });
        roCol.observe(colRight);
      }
    }
    window.addEventListener('resize', () => {
      window.requestAnimationFrame(syncTableHeightWithCalendar);
    });
  } catch (e) { console.error('resize observer setup error:', e); }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initAdminApplication);
} else {
  initAdminApplication();
}

// ==========================================================================
// APPBAR NOTIFIKASI INTERACTION
// ==========================================================================
function toggleAppbarNotif() {
  const dd = document.getElementById('appbarNotifDropdown');
  if (dd) {
    dd.classList.toggle('show');
  }
}
window.toggleAppbarNotif = toggleAppbarNotif;

function markAllNotifsRead() {
  const items = document.querySelectorAll('.notif-dropdown-item.unread');
  items.forEach(el => el.classList.remove('unread'));
  const badge = document.getElementById('notifBadge') || document.querySelector('.notif-badge');
  if (badge) { badge.textContent = '0'; badge.style.display = 'none'; }
  const countPill = document.getElementById('notifCountPill') || document.querySelector('.notif-count-pill');
  if (countPill) countPill.textContent = 'Semua dibaca';
  // Tutup dropdown setelah ditandai dibaca
  const dd = document.getElementById('appbarNotifDropdown');
  if (dd) dd.classList.remove('show');
}
window.markAllNotifsRead = markAllNotifsRead;

document.addEventListener('click', function (e) {
  const container = document.querySelector('.appbar-notif-container');
  const dropdown = document.getElementById('appbarNotifDropdown');
  if (container && dropdown && dropdown.classList.contains('show')) {
    if (!container.contains(e.target)) {
      dropdown.classList.remove('show');
    }
  }

  // Close search results if clicked outside
  const searchContainer = document.querySelector('.appbar-search-container');
  const searchResults = document.getElementById('appbarSearchResults');
  if (searchContainer && searchResults && searchResults.classList.contains('show')) {
    if (!searchContainer.contains(e.target)) {
      closeAppbarSearch();
    }
  }
});

// ==========================================================================
// APPBAR GLOBAL QUICK SEARCH & COMMAND PALETTE
// ==========================================================================
function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function handleAppbarSearch(query) {
  const q = (query || '').trim().toLowerCase();
  const clearBtn = document.getElementById('appbarSearchClear');
  const resultsContainer = document.getElementById('appbarSearchResults');

  if (clearBtn) {
    clearBtn.style.display = q ? 'block' : 'none';
  }

  if (!resultsContainer) return;

  if (!q) {
    resultsContainer.classList.remove('show');
    resultsContainer.innerHTML = '';
    return;
  }

  // 1. Cari menu & modul navigasi
  const matchedPages = [];
  for (const [key, page] of Object.entries(ADMIN_PAGES)) {
    if (page.title.toLowerCase().includes(q) || (page.category && page.category.toLowerCase().includes(q)) || key.toLowerCase().includes(q)) {
      matchedPages.push({ key, ...page });
    }
  }

  // 2. Cari berkas dokumen dari database adminDb
  const matchedDocs = [];
  for (const [categoryKey, docs] of Object.entries(adminDb)) {
    if (!Array.isArray(docs)) continue;
    const catConfig = ADMIN_PAGES[categoryKey] || { category: categoryKey };
    for (const doc of docs) {
      const title = (doc.judul || '').toLowerCase();
      const ket = (doc.keterangan || '').toLowerCase();
      const file = (doc.file || '').toLowerCase();
      const nomor = (doc.nomor || '').toLowerCase();
      const pengirim = (doc.pengirim || '').toLowerCase();

      if (title.includes(q) || ket.includes(q) || file.includes(q) || nomor.includes(q) || pengirim.includes(q) || String(doc.tahun).includes(q)) {
        matchedDocs.push({
          categoryKey,
          categoryName: catConfig.category || categoryKey,
          ...doc
        });
      }
    }
  }

  if (matchedPages.length === 0 && matchedDocs.length === 0) {
    resultsContainer.innerHTML = `
      <div class="search-empty-state">
        <svg viewBox="0 0 24 24" width="28" height="28" stroke="currentColor" stroke-width="1.8" fill="none"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <div>Tidak ada berkas cocok untuk "<strong>${escapeHtml(query)}</strong>"</div>
        <div style="font-size:11px;margin-top:4px;opacity:0.75;">Coba kata kunci lain: <em>Renja, DPA, Mutu, BKPSDM, 2026</em></div>
      </div>
    `;
    resultsContainer.classList.add('show');
    return;
  }

  let html = '';

  // Render Hasil Dokumen
  if (matchedDocs.length > 0) {
    html += `
      <div class="search-result-group">
        <div class="search-group-title">DOKUMEN & BERKAS (${matchedDocs.length})</div>
    `;

    matchedDocs.slice(0, 6).forEach(doc => {
      let badgeClass = 'badge-renja';
      if (doc.categoryKey.startsWith('pk')) badgeClass = 'badge-pk';
      else if (doc.categoryKey.startsWith('dpa')) badgeClass = 'badge-dpa';
      else if (doc.categoryKey.startsWith('surat')) badgeClass = 'badge-surat';

      const yearMeta = doc.tahun ? `T.A. ${doc.tahun}` : '';
      const fileMeta = doc.file ? `&bull; ${doc.file}` : '';

      html += `
        <div class="search-result-item" onclick="selectSearchResult('${doc.categoryKey}', ${doc.id})">
          <span class="search-item-badge ${badgeClass}">${doc.categoryName}</span>
          <div class="search-item-info">
            <div class="search-item-title">${escapeHtml(doc.judul || doc.file)}</div>
            <div class="search-item-sub">${yearMeta} ${fileMeta}</div>
          </div>
          <span class="search-item-arrow">&rarr;</span>
        </div>
      `;
    });

    html += `</div>`;
  }

  // Render Hasil Menu Halaman
  if (matchedPages.length > 0) {
    html += `
      <div class="search-result-group">
        <div class="search-group-title">MENU & MODUL</div>
    `;

    matchedPages.slice(0, 4).forEach(page => {
      html += `
        <div class="search-result-item" onclick="navigateAdmin('${page.key}'); closeAppbarSearch();">
          <span class="search-item-badge badge-menu">Halaman</span>
          <div class="search-item-info">
            <div class="search-item-title">${escapeHtml(page.title)}</div>
            <div class="search-item-sub">${escapeHtml(page.category || 'Navigasi Sistem')}</div>
          </div>
          <span class="search-item-arrow">&rarr;</span>
        </div>
      `;
    });

    html += `</div>`;
  }

  resultsContainer.innerHTML = html;
  resultsContainer.classList.add('show');
}
window.handleAppbarSearch = handleAppbarSearch;

function handleAppbarSearchFocus() {
  const input = document.getElementById('appbarSearchInput');
  if (input && input.value.trim()) {
    handleAppbarSearch(input.value);
  }
}
window.handleAppbarSearchFocus = handleAppbarSearchFocus;

function clearAppbarSearch() {
  const input = document.getElementById('appbarSearchInput');
  if (input) {
    input.value = '';
    input.focus();
  }
  handleAppbarSearch('');
}
window.clearAppbarSearch = clearAppbarSearch;

function closeAppbarSearch() {
  const resultsContainer = document.getElementById('appbarSearchResults');
  if (resultsContainer) {
    resultsContainer.classList.remove('show');
  }
}
window.closeAppbarSearch = closeAppbarSearch;

function selectSearchResult(pageKey, docId) {
  closeAppbarSearch();
  navigateAdmin(pageKey);

  setTimeout(() => {
    const pageEl = document.getElementById('admin-page-' + pageKey);
    if (!pageEl) return;

    const row = pageEl.querySelector(`tr[data-doc-id="${docId}"]`);
    if (row) {
      row.scrollIntoView({ behavior: 'smooth', block: 'center' });
      row.style.transition = 'all 0.35s ease';
      const originalBg = row.style.backgroundColor;
      row.style.backgroundColor = '#dbeafe';
      row.style.boxShadow = '0 0 0 2px #3b82f6';
      setTimeout(() => {
        row.style.backgroundColor = originalBg;
        row.style.boxShadow = '';
      }, 2500);
    }
    showAdminToast('Dokumen ditemukan dan disorot di tabel', 'success');
  }, 120);
}
window.selectSearchResult = selectSearchResult;

// Keyboard Shortcuts: Ctrl+K / Cmd+K untuk fokus pencarian, Esc untuk tutup
document.addEventListener('keydown', function (e) {
  if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
    e.preventDefault();
    const searchInput = document.getElementById('appbarSearchInput');
    if (searchInput) {
      searchInput.focus();
      searchInput.select();
    }
  } else if (e.key === 'Escape') {
    closeAppbarSearch();
    const notifDd = document.getElementById('appbarNotifDropdown');
    if (notifDd) notifDd.classList.remove('show');
    document.querySelectorAll('.modal-overlay.show').forEach(m => closeAdminModal(m.id));
    if (typeof clearAllSelections === 'function') clearAllSelections();
  }
});

// Jam & Tanggal Operasional Real-time di Appbar
function startAppbarClock() {
  const dateEl = document.getElementById('appbarLiveDate');
  const timeEl = document.getElementById('appbarLiveTime');
  if (!dateEl || !timeEl) return;

  function tick() {
    const now = new Date();
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    dateEl.textContent = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
    timeEl.textContent = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}:${String(now.getSeconds()).padStart(2, '0')} WIB`;
  }
  tick();
  setInterval(tick, 1000);
}

window.startAppbarClock = startAppbarClock;

// ==========================================================================
// FILTER TAHUN DINAMIS (OTOMATIS BERTAMBAH SAAT ADA TAHUN BARU)
// ==========================================================================
function updateDynamicYearFilters() {
  const currentYear = new Date().getFullYear();
  const yearsSet = new Set([currentYear, currentYear - 1, currentYear - 2]);

  // Kumpulkan semua tahun dari dokumen di adminDb
  for (const mod in adminDb) {
    (adminDb[mod] || []).forEach(doc => {
      let y = doc.tahun || doc.tahun_anggaran;
      if (!y && doc.tanggal) {
        y = doc.tanggal.split('-')[0];
      }
      if (!y && doc.date) {
        y = doc.date.split('-')[0];
      }
      const numY = parseInt(y);
      if (numY && !isNaN(numY) && numY >= 1990 && numY <= 2100) {
        yearsSet.add(numY);
      }
    });
  }

  // Urutkan tahun menurun (terbaru di atas, misal: 2027, 2026, 2025, 2024...)
  const sortedYears = Array.from(yearsSet).sort((a, b) => b - a);

  // Perbarui semua elemen <select class="form-control filter-year"> di toolbar setiap halaman
  document.querySelectorAll('.filter-year').forEach(selectEl => {
    const currentVal = selectEl.value;
    selectEl.innerHTML = '<option value="">Semua Tahun</option>';
    sortedYears.forEach(yr => {
      const opt = document.createElement('option');
      opt.value = yr;
      opt.textContent = yr;
      if (currentVal && String(currentVal) === String(yr)) {
        opt.selected = true;
      }
      selectEl.appendChild(opt);
    });
  });

  // Perbarui juga datalist rekomendasi tahun di modal
  const datalist = document.getElementById('listTahunRekomendasi');
  if (datalist) {
    datalist.innerHTML = '';
    sortedYears.forEach(yr => {
      const opt = document.createElement('option');
      opt.value = yr;
      datalist.appendChild(opt);
    });
  }
}
window.updateDynamicYearFilters = updateDynamicYearFilters;

// ==========================================================================
// SELEKSI BARIS TABEL DENGAN TEKAN LAMA (LONG PRESS / LONG CLICK) & CHECKBOX
// ==========================================================================
function toggleRowSelection(tr) {
  if (!tr) return;
  const docId = tr.dataset.docId;
  const modKey = tr.dataset.modKey || currentAdminPage;
  if (!docId) return;

  const key = `${modKey}_${docId}`;
  const cb = tr.querySelector('.gov-checkbox');

  if (selectedDocMap.has(key)) {
    selectedDocMap.delete(key);
    tr.classList.remove('row-selected');
    if (cb) {
      cb.classList.remove('checked');
      if (cb.tagName === 'INPUT') cb.checked = false;
    }
  } else {
    const item = (adminDb[modKey] || []).find(x => x.id == docId);
    selectedDocMap.set(key, {
      id: docId,
      moduleKey: modKey,
      judul: item ? item.judul : (tr.querySelector('strong')?.textContent?.trim() || `Dokumen #${docId}`)
    });
    tr.classList.add('row-selected');
    if (cb) {
      cb.classList.add('checked');
      if (cb.tagName === 'INPUT') cb.checked = true;
    }
  }

  updateSelectionUI();
}
window.toggleRowSelection = toggleRowSelection;

function updateSelectionUI() {
  const bar = document.getElementById('tableSelectionBar');
  const countEl = document.getElementById('selectionCountText');

  if (selectedDocMap.size > 0) {
    document.body.classList.add('selection-mode-active');
    if (bar) bar.classList.add('show');
    if (countEl) countEl.textContent = `${selectedDocMap.size} files selected`;
  } else {
    document.body.classList.remove('selection-mode-active');
    if (bar) bar.classList.remove('show');
    document.querySelectorAll('.row-selected').forEach(el => el.classList.remove('row-selected'));
    document.querySelectorAll('.row-checkbox').forEach(el => {
      el.classList.remove('checked');
      if (el.tagName === 'INPUT') el.checked = false;
    });
  }

  // Sinkronisasi status master checkbox di tabel aktif
  const activePage = document.querySelector('.admin-page.active') || document.querySelector('.admin-page:not([style*="display: none"])') || document.getElementById('admin-page-dashboard');
  if (activePage) {
    const master = activePage.querySelector('.table-master-checkbox') || document.querySelector('.table-master-checkbox');
    const rows = activePage.querySelectorAll('.gov-table tbody tr[data-doc-id]');
    if (master && rows.length > 0) {
      let checkedCount = 0;
      rows.forEach(tr => {
        const key = `${tr.dataset.modKey || currentAdminPage}_${tr.dataset.docId}`;
        const cb = tr.querySelector('.gov-checkbox');
        if (selectedDocMap.has(key)) {
          checkedCount++;
          tr.classList.add('row-selected');
          if (cb) {
            cb.classList.add('checked');
            if (cb.tagName === 'INPUT') cb.checked = true;
          }
        } else {
          tr.classList.remove('row-selected');
          if (cb) {
            cb.classList.remove('checked');
            if (cb.tagName === 'INPUT') cb.checked = false;
          }
        }
      });

      if (checkedCount === 0) {
        master.classList.remove('checked', 'indeterminate');
        if (master.tagName === 'INPUT') {
          master.checked = false;
          master.indeterminate = false;
        }
      } else if (checkedCount === rows.length) {
        master.classList.remove('indeterminate');
        master.classList.add('checked');
        if (master.tagName === 'INPUT') {
          master.checked = true;
          master.indeterminate = false;
        }
      } else {
        master.classList.remove('checked');
        master.classList.add('indeterminate');
        if (master.tagName === 'INPUT') {
          master.checked = false;
          master.indeterminate = true;
        }
      }
    } else if (master) {
      master.classList.remove('checked', 'indeterminate');
      if (master.tagName === 'INPUT') {
        master.checked = false;
        master.indeterminate = false;
      }
    }
  }
}
window.updateSelectionUI = updateSelectionUI;

function clearAllSelections() {
  selectedDocMap.clear();
  updateSelectionUI();
}
window.clearAllSelections = clearAllSelections;

function toggleSelectAllActiveTable(e) {
  if (e) {
    e.stopPropagation();
  }
  const activePage = document.querySelector('.admin-page.active') || document.querySelector('.admin-page:not([style*="display: none"])') || document.getElementById('admin-page-dashboard');
  if (!activePage) return;

  const rows = activePage.querySelectorAll('.gov-table tbody tr[data-doc-id]');
  if (rows.length === 0) return;

  const isAllChecked = Array.from(rows).every(tr => {
    const key = `${tr.dataset.modKey || currentAdminPage}_${tr.dataset.docId}`;
    return selectedDocMap.has(key);
  });

  if (isAllChecked) {
    rows.forEach(tr => {
      const key = `${tr.dataset.modKey || currentAdminPage}_${tr.dataset.docId}`;
      selectedDocMap.delete(key);
      tr.classList.remove('row-selected');
      const cb = tr.querySelector('.gov-checkbox');
      if (cb) {
        cb.classList.remove('checked');
        if (cb.tagName === 'INPUT') cb.checked = false;
      }
    });
  } else {
    rows.forEach(tr => {
      const docId = tr.dataset.docId;
      const modKey = tr.dataset.modKey || currentAdminPage;
      const key = `${modKey}_${docId}`;
      const item = (adminDb[modKey] || []).find(x => x.id == docId);
      selectedDocMap.set(key, {
        id: docId,
        moduleKey: modKey,
        judul: item ? item.judul : (tr.querySelector('strong')?.textContent?.trim() || `Dokumen #${docId}`)
      });
      tr.classList.add('row-selected');
      const cb = tr.querySelector('.gov-checkbox');
      if (cb) {
        cb.classList.add('checked');
        if (cb.tagName === 'INPUT') cb.checked = true;
      }
    });
  }

  updateSelectionUI();
}
window.toggleSelectAllActiveTable = toggleSelectAllActiveTable;

function selectAllRowsInActiveTable() {
  toggleSelectAllActiveTable();
}
window.selectAllRowsInActiveTable = selectAllRowsInActiveTable;

function openBatchDeleteModal() {
  if (selectedDocMap.size === 0) return;

  adminDeleteTarget = {
    isBatch: true,
    items: Array.from(selectedDocMap.values())
  };

  const msg = document.getElementById('deleteModalMessage');
  if (msg) {
    msg.innerHTML = `Apakah Anda yakin ingin menghapus <strong>${selectedDocMap.size} dokumen terpilih</strong> secara permanen dari sistem dan Google Drive?`;
  }
  document.getElementById('modalDelete')?.classList.add('show');
}
window.openBatchDeleteModal = openBatchDeleteModal;

let longPressTimer = null;
let longPressStartPoint = null;
let isLongPressTriggered = false;

function initTableLongPressSelection() {
  const startLongPress = (e) => {
    const tr = e.target.closest('.gov-table tbody tr[data-doc-id]');
    if (!tr) return;

    if (e.target.closest('button') || e.target.closest('a') || e.target.closest('.btn-action-group') || e.target.closest('input[type="text"]') || e.target.closest('select')) {
      return;
    }

    if (e.target.closest('.col-checkbox-cell') || e.target.closest('.gov-checkbox')) {
      return;
    }

    if (e.button !== undefined && e.button !== 0) return;

    isLongPressTriggered = false;
    const clientX = e.clientX || (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
    const clientY = e.clientY || (e.touches && e.touches[0] ? e.touches[0].clientY : 0);
    longPressStartPoint = { x: clientX, y: clientY };

    tr.classList.add('row-selecting');

    if (longPressTimer) clearTimeout(longPressTimer);
    longPressTimer = setTimeout(() => {
      isLongPressTriggered = true;
      tr.classList.remove('row-selecting');

      if (navigator.vibrate) {
        try { navigator.vibrate(50); } catch (err) {}
      }

      toggleRowSelection(tr);
    }, 260); // 260ms - cepat & responsif saat tahan mouse
  };

  const moveLongPress = (e) => {
    if (!longPressTimer) return;
    if (longPressStartPoint) {
      const clientX = e.clientX || (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
      const clientY = e.clientY || (e.touches && e.touches[0] ? e.touches[0].clientY : 0);
      const dist = Math.hypot(clientX - longPressStartPoint.x, clientY - longPressStartPoint.y);
      if (dist > 12) {
        clearTimeout(longPressTimer);
        longPressTimer = null;
        document.querySelectorAll('.row-selecting').forEach(el => el.classList.remove('row-selecting'));
      }
    }
  };

  const clearTimer = () => {
    if (longPressTimer) {
      clearTimeout(longPressTimer);
      longPressTimer = null;
    }
    document.querySelectorAll('.row-selecting').forEach(el => el.classList.remove('row-selecting'));
  };

  document.addEventListener('pointerdown', startLongPress);
  document.addEventListener('pointermove', moveLongPress);
  document.addEventListener('pointerup', clearTimer);
  document.addEventListener('pointercancel', clearTimer);

  document.addEventListener('touchstart', startLongPress, { passive: true });
  document.addEventListener('touchmove', moveLongPress, { passive: true });
  document.addEventListener('touchend', clearTimer);

  // Klik biasa pada baris tabel (selain tombol/link) langsung menyeleksi baris
  // Klik di LUAR tabel saat mode seleksi aktif â†’ clear semua seleksi
  document.addEventListener('click', (e) => {
    if (isLongPressTriggered) {
      isLongPressTriggered = false;
      e.preventDefault();
      e.stopPropagation();
      return;
    }

    const tr = e.target.closest('.gov-table tbody tr[data-doc-id]');

    if (!tr) {
      // Klik di luar tabel â€” clear seleksi jika ada, KECUALI klik di selection bar itu sendiri
      if (selectedDocMap.size > 0) {
        const isInsideSelectionBar = e.target.closest('#tableSelectionBar');
        const isInsideModal = e.target.closest('.modal-overlay');
        const isInsideCheckboxHeader = e.target.closest('.col-checkbox-cell') || e.target.closest('.gov-checkbox');
        if (!isInsideSelectionBar && !isInsideModal && !isInsideCheckboxHeader) {
          clearAllSelections();
        }
      }
      return;
    }

    if (e.target.closest('button') || e.target.closest('a') || e.target.closest('.btn-action-group') || e.target.closest('input') || e.target.closest('select')) {
      return;
    }

    toggleRowSelection(tr);
  });

  document.addEventListener('contextmenu', (e) => {
    if (isLongPressTriggered || (selectedDocMap.size > 0 && e.target.closest('.gov-table tbody tr'))) {
      e.preventDefault();
    }
  });
}
window.initTableLongPressSelection = initTableLongPressSelection;

/* ==========================================================================
   MODUL CAPAIAN KINERJA (e-SAKIP STYLE) & MANAJEMEN BUKTI PENDUKUNG
   ========================================================================== */

let capaianDb = (window.serverCapaianDb && Array.isArray(window.serverCapaianDb)) ? window.serverCapaianDb : [];

let customCapaianYears = new Set([2026]);
let activeCapaianTahun = 2026;
let activeCapaianTriwulan = 'TW I';
let capaianSearchQuery = '';

// Helper parser & format angka/rupiah (Presisi asli tanpa pemotongan / pembulatan paksa)
function parseRupiahInput(val) {
  if (val === null || val === undefined) return 0;
  if (typeof val === 'number') return isNaN(val) ? 0 : val;
  let str = String(val).trim();
  if (!str) return 0;

  str = str.replace(/rp|idr/gi, '').replace(/,-$/g, '').trim();
  str = str.replace(/[^0-9.,\-]/g, '');
  if (!str) return 0;

  if (str.includes(',') && str.includes('.')) {
    if (str.lastIndexOf(',') > str.lastIndexOf('.')) {
      // Format Indo: 60.940.000,50
      str = str.replace(/\./g, '').replace(',', '.');
    } else {
      // Format US: 60,940,000.50
      str = str.replace(/,/g, '');
    }
  } else if (str.includes('.')) {
    const parts = str.split('.');
    if (parts.length > 2) {
      str = str.replace(/\./g, '');
    } else if (parts[1].length === 3) {
      str = str.replace(/\./g, '');
    }
  } else if (str.includes(',')) {
    const parts = str.split(',');
    if (parts.length > 2) {
      str = str.replace(/,/g, '');
    } else if (parts[1].length === 3 && parts[0].length >= 1 && !parts[0].includes('.')) {
      str = str.replace(/,/g, '');
    } else {
      str = str.replace(',', '.');
    }
  }

  const num = parseFloat(str);
  return isNaN(num) ? 0 : num;
}
window.parseRupiahInput = parseRupiahInput;

function parseFlexibleNumber(val) {
  if (val === null || val === undefined) return 0;
  if (typeof val === 'number') return isNaN(val) ? 0 : val;
  let str = String(val).trim();
  if (!str) return 0;
  str = str.replace(/[^0-9.,\-]/g, '');
  if (!str) return 0;

  if (str.includes(',') && str.includes('.')) {
    if (str.lastIndexOf(',') > str.lastIndexOf('.')) {
      str = str.replace(/\./g, '').replace(',', '.');
    } else {
      str = str.replace(/,/g, '');
    }
  } else if (str.includes(',')) {
    str = str.replace(',', '.');
  }

  const num = parseFloat(str);
  return isNaN(num) ? 0 : num;
}
window.parseFlexibleNumber = parseFlexibleNumber;

function formatRupiahCapaian(val) {
  if (val === null || val === undefined || val === '') return 'Rp 0';
  const num = typeof val === 'number' ? val : parseFloat(val);
  if (isNaN(num) || num === 0) return 'Rp 0';

  const isInteger = Math.floor(num) === num;
  return 'Rp ' + num.toLocaleString('id-ID', {
    minimumFractionDigits: isInteger ? 0 : 2,
    maximumFractionDigits: 2
  });
}
window.formatRupiahCapaian = formatRupiahCapaian;

function formatDesimalCapaian(val) {
  if (val === null || val === undefined || val === '') return '0';
  const num = typeof val === 'number' ? val : parseFloat(val);
  if (isNaN(num)) return val;
  if (num === 0) return '0';

  const parts = String(val).split('.');
  const decLen = parts.length > 1 ? parts[1].length : 0;
  const digits = Math.min(Math.max(decLen, (num % 1 === 0 ? 0 : 2)), 4);
  return num.toLocaleString('id-ID', {
    minimumFractionDigits: (num % 1 === 0) ? 0 : 2,
    maximumFractionDigits: 4
  });
}
window.formatDesimalCapaian = formatDesimalCapaian;


// Inisialisasi & Populasi Dropdown Tahun Dinamis (Tidak Terbatas)
function populateCapaianYearFilter(selectedYear) {
  const selectEl = document.getElementById('capaianFilterTahun');
  if (!selectEl) return;

  // Himpun seluruh tahun dari database
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

// Tambah Tahun Bebas Tanpa Batas
function promptAddCustomYear() {
  const userInput = prompt('Ketik Tahun Anggaran Baru yang ingin ditambahkan (contoh: 2027, 2028, 2023):');
  if (!userInput) return;
  const parsedYear = parseInt(userInput.trim());
  if (isNaN(parsedYear) || parsedYear < 1900 || parsedYear > 2200) {
    alert('Harap masukkan angka tahun yang valid.');
    return;
  }

  customCapaianYears.add(parsedYear);
  activeCapaianTahun = parsedYear;
  populateCapaianYearFilter(parsedYear);
  renderCapaianTable();
  if (typeof showAdminToast === 'function') {
    showAdminToast(`Tahun Anggaran ${parsedYear} berhasil ditambahkan ke filter.`, 'success');
  }
}
window.promptAddCustomYear = promptAddCustomYear;

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

// Helper: Render Kolom Bukti Pendukung
function renderBuktiCell(row) {
  const hasLink = Boolean(row.bukti_link);
  const hasFile = Boolean(row.bukti_file_name || row.bukti_file_path);
  const hasKet = Boolean(row.bukti_keterangan);

  if (!hasLink && !hasFile && !hasKet) {
    return `
      <button type="button" class="btn-isi-bukti" onclick="openQuickBuktiModal(${row.id})" title="Klik untuk mengisi bukti pendukung (link / file)">
        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        <span>+ Isi Bukti</span>
      </button>
    `;
  }

  let badges = '';
  if (hasLink) {
    badges += `
      <a href="${row.bukti_link}" target="_blank" rel="noopener noreferrer" class="bukti-badge-link" title="Buka tautan: ${row.bukti_link}">
        <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
        <span>Link</span>
      </a>
    `;
  }
  if (hasFile) {
    const fileName = row.bukti_file_name || 'Berkas';
    const downloadUrl = `/admin/capaian-kinerja/download-bukti/${row.id}`;
    badges += `
      <a href="${downloadUrl}" target="_blank" class="bukti-badge-file" title="Unduh berkas: ${fileName}">
        <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        <span>${fileName}</span>
      </a>
    `;
  }

  return `
    <div class="bukti-cell-container">
      <div class="bukti-badge-group">
        ${badges}
        <button type="button" class="bukti-btn-edit" onclick="openQuickBuktiModal(${row.id})" title="Kelola / Ganti Bukti Pendukung">
          <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
        </button>
      </div>
      ${hasKet ? `<span style="font-size:10px;color:#64748b;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${row.bukti_keterangan}">"${row.bukti_keterangan}"</span>` : ''}
    </div>
  `;
}
window.renderBuktiCell = renderBuktiCell;

// Helper Predikat Badge
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

// Render Tabel Capaian Kinerja (Mendukung Tampilan TW I-IV Tunggal & Matriks 42 Kolom saat Filter Semua Triwulan)
function renderCapaianTable() {
  const tbody = document.getElementById('tbodyCapaianKinerja');
  if (!tbody) return;

  const thead = document.getElementById('theadCapaianKinerja') || document.querySelector('#tableCapaianKinerja thead');
  const isSemua = (activeCapaianTriwulan === 'Semua');

  populateCapaianYearFilter(activeCapaianTahun);

  // Update header thead secara dinamis sesuai pilihan filter Triwulan
  if (thead) {
    if (isSemua) {
      // JIKA MEMILIH "SEMUA TRIWULAN" / TAHUNAN: TAMPILKAN MATRIKS LENGKAP 42 KOLOM RESMI PERSIS FOTO
      thead.innerHTML = `
        <!-- Baris 1: Header Grup Utama -->
        <tr class="head-top">
          <th rowspan="3" style="text-align:center;white-space:nowrap;width:1%;">No</th>
          <th rowspan="3" class="th-left col-text" style="text-align:left;min-width:220px;white-space:normal;word-break:break-word;">Tujuan / Sasaran / Program / Kegiatan / Sub Kegiatan</th>
          <th rowspan="3" class="th-left col-text" style="text-align:left;min-width:200px;white-space:normal;word-break:break-word;">Indikator Kinerja</th>
          <th colspan="6" id="thDataTahun" style="white-space:nowrap;">Data ${activeCapaianTahun}</th>
          <th colspan="4" style="white-space:nowrap;">Target Kinerja</th>
          <th colspan="15" style="white-space:nowrap;">Capaian Kinerja</th>
          <th colspan="10" style="white-space:nowrap;">Capaian Keuangan</th>
          <th colspan="2" style="white-space:nowrap;">Target Akhir RPJMD ${activeCapaianTahun}</th>
          <th colspan="2" style="white-space:nowrap;">Capaian Terhadap Target Akhir Renstra ${activeCapaianTahun}</th>
          <th rowspan="3" style="text-align:center;white-space:nowrap;min-width:140px;">Bukti Pendukung</th>
          <th rowspan="3" style="text-align:center;white-space:nowrap;width:1%;">Aksi</th>
        </tr>

        <!-- Baris 2: Kolom Rincian & Sub Header Triwulan -->
        <tr class="head-sub">
          <!-- Data Tahun -->
          <th rowspan="2" style="text-align:right;white-space:nowrap;min-width:75px;">Target</th>
          <th colspan="4" style="text-align:center;white-space:nowrap;">Rp</th>
          <th rowspan="2" style="text-align:center;white-space:nowrap;min-width:70px;">Satuan</th>
          <!-- Target Kinerja -->
          <th rowspan="2" style="text-align:right;white-space:nowrap;min-width:70px;">TW I</th>
          <th rowspan="2" style="text-align:right;white-space:nowrap;min-width:70px;">TW II</th>
          <th rowspan="2" style="text-align:right;white-space:nowrap;min-width:70px;">TW III</th>
          <th rowspan="2" style="text-align:right;white-space:nowrap;min-width:70px;">TW IV</th>
          <!-- Capaian Kinerja -->
          <th colspan="3" style="text-align:center;background:#fff7ed;color:#9a3412;font-weight:800;white-space:nowrap;">TW I</th>
          <th colspan="3" style="text-align:center;background:#f0fdf4;color:#166534;font-weight:800;white-space:nowrap;">TW II</th>
          <th colspan="3" style="text-align:center;background:#f0f9ff;color:#0369a1;font-weight:800;white-space:nowrap;">TW III</th>
          <th colspan="3" style="text-align:center;background:#faf5ff;color:#6b21a8;font-weight:800;white-space:nowrap;">TW IV</th>
          <th colspan="3" style="text-align:center;background:#fef2f2;color:#991b1b;font-weight:800;white-space:nowrap;">Total ${activeCapaianTahun}</th>
          <!-- Capaian Keuangan -->
          <th colspan="2" style="text-align:center;background:#fff7ed;color:#9a3412;font-weight:800;white-space:nowrap;">TW I</th>
          <th colspan="2" style="text-align:center;background:#f0fdf4;color:#166534;font-weight:800;white-space:nowrap;">TW II</th>
          <th colspan="2" style="text-align:center;background:#f0f9ff;color:#0369a1;font-weight:800;white-space:nowrap;">TW III</th>
          <th colspan="2" style="text-align:center;background:#faf5ff;color:#6b21a8;font-weight:800;white-space:nowrap;">TW IV</th>
          <th colspan="2" style="text-align:center;background:#fef2f2;color:#991b1b;font-weight:800;white-space:nowrap;">Total ${activeCapaianTahun}</th>
          <!-- Target Akhir RPJMD -->
          <th rowspan="2" style="text-align:right;white-space:nowrap;min-width:80px;">Kinerja</th>
          <th rowspan="2" style="text-align:right;white-space:nowrap;min-width:110px;">Rp</th>
          <!-- Capaian Terhadap Target Akhir Renstra -->
          <th rowspan="2" style="text-align:right;white-space:nowrap;min-width:80px;">Kinerja (%)</th>
          <th rowspan="2" style="text-align:right;white-space:nowrap;min-width:80px;">Rp (%)</th>
        </tr>

        <!-- Baris 3: Rincian Realisasi, Persentase, dan Predikat -->
        <tr class="head-detail">
          <!-- Pagu Rp per TW -->
          <th style="text-align:right;white-space:nowrap;min-width:90px;">TW I</th>
          <th style="text-align:right;white-space:nowrap;min-width:90px;">TW II</th>
          <th style="text-align:right;white-space:nowrap;min-width:90px;">TW III</th>
          <th style="text-align:right;white-space:nowrap;min-width:90px;">TW IV</th>

          <!-- Capaian Kinerja TW I -->
          <th style="text-align:right;white-space:nowrap;min-width:80px;">Realisasi</th>
          <th style="text-align:right;white-space:nowrap;min-width:80px;">Capaian (%)</th>
          <th style="text-align:center;white-space:nowrap;min-width:105px;">Predikat</th>
          <!-- Capaian Kinerja TW II -->
          <th style="text-align:right;white-space:nowrap;min-width:80px;">Realisasi</th>
          <th style="text-align:right;white-space:nowrap;min-width:80px;">Capaian (%)</th>
          <th style="text-align:center;white-space:nowrap;min-width:105px;">Predikat</th>
          <!-- Capaian Kinerja TW III -->
          <th style="text-align:right;white-space:nowrap;min-width:80px;">Realisasi</th>
          <th style="text-align:right;white-space:nowrap;min-width:80px;">Capaian (%)</th>
          <th style="text-align:center;white-space:nowrap;min-width:105px;">Predikat</th>
          <!-- Capaian Kinerja TW IV -->
          <th style="text-align:right;white-space:nowrap;min-width:80px;">Realisasi</th>
          <th style="text-align:right;white-space:nowrap;min-width:80px;">Capaian (%)</th>
          <th style="text-align:center;white-space:nowrap;min-width:105px;">Predikat</th>
          <!-- Capaian Kinerja Total -->
          <th style="text-align:right;white-space:nowrap;min-width:80px;">Realisasi</th>
          <th style="text-align:right;white-space:nowrap;min-width:80px;">Capaian (%)</th>
          <th style="text-align:center;white-space:nowrap;min-width:105px;">Predikat</th>

          <!-- Capaian Keuangan TW I -->
          <th style="text-align:right;white-space:nowrap;min-width:105px;">Realisasi</th>
          <th style="text-align:right;white-space:nowrap;min-width:80px;">Capaian (%)</th>
          <!-- Capaian Keuangan TW II -->
          <th style="text-align:right;white-space:nowrap;min-width:105px;">Realisasi</th>
          <th style="text-align:right;white-space:nowrap;min-width:80px;">Capaian (%)</th>
          <!-- Capaian Keuangan TW III -->
          <th style="text-align:right;white-space:nowrap;min-width:105px;">Realisasi</th>
          <th style="text-align:right;white-space:nowrap;min-width:80px;">Capaian (%)</th>
          <!-- Capaian Keuangan TW IV -->
          <th style="text-align:right;white-space:nowrap;min-width:105px;">Realisasi</th>
          <th style="text-align:right;white-space:nowrap;min-width:80px;">Capaian (%)</th>
          <!-- Capaian Keuangan Total -->
          <th style="text-align:right;white-space:nowrap;min-width:105px;">Realisasi</th>
          <th style="text-align:right;white-space:nowrap;min-width:80px;">Capaian (%)</th>
        </tr>

        <!-- Baris 4: Baris Penomoran Rumus Resmi (1) s/d (42) Sesuai Foto -->
        <tr class="head-numbering" style="background:#f1f5f9;font-size:10px;font-weight:700;color:#475569;text-align:center;">
          <th style="text-align:center;padding:4px 2px;">(1)</th>
          <th style="text-align:center;padding:4px 2px;">(2)</th>
          <th style="text-align:center;padding:4px 2px;">(3)</th>
          <th style="text-align:center;padding:4px 2px;">(4)</th>
          <th style="text-align:center;padding:4px 2px;">(5)</th>
          <th style="text-align:center;padding:4px 2px;">(6)</th>
          <th style="text-align:center;padding:4px 2px;">(7)</th>
          <th style="text-align:center;padding:4px 2px;">(8)</th>
          <th style="text-align:center;padding:4px 2px;">(9)</th>
          <th style="text-align:center;padding:4px 2px;">(10)</th>
          <th style="text-align:center;padding:4px 2px;">(11)</th>
          <th style="text-align:center;padding:4px 2px;">(12)</th>
          <th style="text-align:center;padding:4px 2px;">(13)</th>
          <th style="text-align:center;padding:4px 2px;">(14)</th>
          <th style="text-align:center;padding:4px 2px;font-size:9px;">(15=14/10*100)</th>
          <th style="text-align:center;padding:4px 2px;">(16)</th>
          <th style="text-align:center;padding:4px 2px;">(17)</th>
          <th style="text-align:center;padding:4px 2px;font-size:9px;">(18=17/11*100)</th>
          <th style="text-align:center;padding:4px 2px;">(19)</th>
          <th style="text-align:center;padding:4px 2px;">(20)</th>
          <th style="text-align:center;padding:4px 2px;font-size:9px;">(21=20/12*100)</th>
          <th style="text-align:center;padding:4px 2px;">(22)</th>
          <th style="text-align:center;padding:4px 2px;">(23)</th>
          <th style="text-align:center;padding:4px 2px;font-size:9px;">(24=23/13*100)</th>
          <th style="text-align:center;padding:4px 2px;">(25)</th>
          <th style="text-align:center;padding:4px 2px;font-size:8.5px;">(26=14..17..20..23)</th>
          <th style="text-align:center;padding:4px 2px;font-size:9px;">(27=26/4*100)</th>
          <th style="text-align:center;padding:4px 2px;">(28)</th>
          <th style="text-align:center;padding:4px 2px;">(29)</th>
          <th style="text-align:center;padding:4px 2px;font-size:9px;">(30=29/5*100)</th>
          <th style="text-align:center;padding:4px 2px;">(31)</th>
          <th style="text-align:center;padding:4px 2px;font-size:9px;">(32=31/6*100)</th>
          <th style="text-align:center;padding:4px 2px;">(33)</th>
          <th style="text-align:center;padding:4px 2px;font-size:9px;">(34=33/7*100)</th>
          <th style="text-align:center;padding:4px 2px;">(35)</th>
          <th style="text-align:center;padding:4px 2px;font-size:9px;">(36=35/8*100)</th>
          <th style="text-align:center;padding:4px 2px;font-size:8.5px;">(37=29+31+33+35)</th>
          <th style="text-align:center;padding:4px 2px;font-size:8.5px;">(38=37/(5+6+7+8)*100)</th>
          <th style="text-align:center;padding:4px 2px;">(39)</th>
          <th style="text-align:center;padding:4px 2px;">(40)</th>
          <th style="text-align:center;padding:4px 2px;font-size:9px;">(41=26/39*100)</th>
          <th style="text-align:center;padding:4px 2px;font-size:9px;">(42=37/40*100)</th>
          <th style="text-align:center;padding:4px 2px;">Bukti</th>
          <th style="text-align:center;padding:4px 2px;">Aksi</th>
        </tr>
      `;
    } else {
      // JIKA MEMILIH SALAH SATU TRIWULAN (TW I, TW II, TW III, ATAU TW IV): TAMPILKAN FORMAT AWAL
      const twLabel = activeCapaianTriwulan || 'TW I';
      thead.innerHTML = `
        <!-- Baris 1: Header Grup Utama -->
        <tr class="head-top">
          <th rowspan="3" style="width:38px;text-align:center;">No</th>
          <th rowspan="3" class="th-left col-text" style="text-align:left;min-width:220px;white-space:normal;word-break:break-word;">Tujuan / Sasaran / Program / Kegiatan / Sub Kegiatan</th>
          <th rowspan="3" class="th-left col-text" style="text-align:left;min-width:200px;white-space:normal;word-break:break-word;">Indikator Kinerja</th>
          <th colspan="3" id="thDataTahun">Data ${activeCapaianTahun}</th>
          <th colspan="4">Target Kinerja</th>
          <th colspan="3" id="thGroupCapaianKinerja">Capaian Kinerja</th>
          <th colspan="2" id="thGroupCapaianKeuangan">Capaian Keuangan</th>
          <th rowspan="3" style="text-align:center;white-space:nowrap;min-width:130px;">Bukti Pendukung</th>
          <th rowspan="3" style="text-align:center;white-space:nowrap;width:1%;">Aksi</th>
        </tr>

        <!-- Baris 2: Kolom Rincian & Sub Header Triwulan -->
        <tr class="head-sub">
          <!-- Data Tahun (Rowspan 2) -->
          <th rowspan="2" style="text-align:right;white-space:nowrap;min-width:85px;">Target</th>
          <th rowspan="2" style="text-align:right;white-space:nowrap;min-width:125px;">Rp</th>
          <th rowspan="2" style="text-align:center;white-space:nowrap;min-width:80px;">Satuan</th>
          <!-- Target Kinerja (Rowspan 2) -->
          <th rowspan="2" style="text-align:right;white-space:nowrap;min-width:72px;">TW I</th>
          <th rowspan="2" style="text-align:right;white-space:nowrap;min-width:72px;">TW II</th>
          <th rowspan="2" style="text-align:right;white-space:nowrap;min-width:72px;">TW III</th>
          <th rowspan="2" style="text-align:right;white-space:nowrap;min-width:72px;">TW IV</th>
          <!-- Capaian Kinerja (Sub TW Dinamis) -->
          <th colspan="3" id="thSubKinerjaTW" style="text-align:center;background:#fff7ed;color:#9a3412;font-weight:800;white-space:nowrap;">${twLabel}</th>
          <!-- Capaian Keuangan (Sub TW Dinamis) -->
          <th colspan="2" id="thSubKeuanganTW" style="text-align:center;background:#fff7ed;color:#9a3412;font-weight:800;white-space:nowrap;">${twLabel}</th>
        </tr>

        <!-- Baris 3: Rincian Realisasi, Persentase, dan Predikat -->
        <tr class="head-detail">
          <!-- Capaian Kinerja -->
          <th style="text-align:right;white-space:nowrap;min-width:85px;">Realisasi</th>
          <th style="text-align:right;white-space:nowrap;min-width:80px;">Capaian (%)</th>
          <th style="text-align:center;white-space:nowrap;min-width:115px;">Predikat</th>
          <!-- Capaian Keuangan -->
          <th style="text-align:right;white-space:nowrap;min-width:125px;">Realisasi</th>
          <th style="text-align:right;white-space:nowrap;min-width:80px;">Capaian (%)</th>
        </tr>
      `;
    }
  }

  // Filter Data berdasarkan Tahun aktif & kata kunci pencarian
  const filtered = capaianDb.filter(item => {
    const matchTahun = parseInt(item.tahun) === parseInt(activeCapaianTahun);
    let matchQuery = true;
    if (capaianSearchQuery) {
      const combined = `${item.sasaran || ''} ${item.indikator || ''} ${item.satuan || ''} ${item.bukti_keterangan || ''}`.toLowerCase();
      matchQuery = combined.includes(capaianSearchQuery);
    }
    return matchTahun && matchQuery;
  });

  // Update badge count
  const badgeCount = document.getElementById('badge-count-capaian');
  if (badgeCount) badgeCount.textContent = capaianDb.length;

  if (filtered.length === 0) {
    const emptyColspan = isSemua ? 44 : 17;
    tbody.innerHTML = `
      <tr>
        <td colspan="${emptyColspan}" style="text-align:center;padding:48px 24px;background:#ffffff;">
          <div style="font-size:36px;margin-bottom:12px;">📊</div>
          <div style="font-weight:700;font-size:15px;color:#1e293b;">Belum Ada Data Capaian Kinerja (Tahun ${activeCapaianTahun})</div>
          <div style="font-size:13px;color:#64748b;margin-top:6px;max-width:560px;margin-left:auto;margin-right:auto;line-height:1.5;">
            Tabel evaluasi triwulanan ini belum memiliki data. Silakan klik tombol di bawah untuk mulai menambahkan target, realisasi indikator kinerja TW I s/d TW IV, dan serapan anggaran keuangan.
          </div>
          <button type="button" class="btn btn-primary" onclick="openModalCapaian('create')" style="margin-top:16px;display:inline-flex;align-items:center;gap:6px;height:38px;padding:0 18px;font-weight:700;border-radius:8px;background:#00875a;border-color:#00875a;color:#ffffff;cursor:pointer;">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>Tambah Data Capaian</span>
          </button>
        </td>
      </tr>
    `;
    return;
  }

  let html = '';
  filtered.forEach((row, index) => {
    // Pagu TW 1..4 (fallback jika belum diisi: bagi 4 dari pagu tahunan)
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
      // JIKA MEMILIH SALAH SATU TRIWULAN (TW I, TW II, TW III, ATAU TW IV): TAMPILKAN FORMAT AWAL
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
        <tr data-capaian-id="${row.id}" onclick="onCapaianRowClick(event, ${row.id})" style="cursor:pointer;" title="Klik baris ini untuk mengedit data capaian kinerja lengkap">
          <!-- (1) NO -->
          <td style="text-align:center;font-weight:700;color:#64748b;white-space:nowrap;">${index + 1}</td>
          
          <!-- (2) TUJUAN / SASARAN / PROGRAM / KEGIATAN -->
          <td class="col-text" style="font-weight:700;color:#1e293b;line-height:1.45;min-width:220px;">${row.sasaran || '-'}</td>
          
          <!-- (3) INDIKATOR KINERJA -->
          <td class="col-text" style="color:#334155;line-height:1.45;min-width:200px;">${row.indikator || '-'}</td>
          
          <!-- DATA TAHUN: (4) TARGET, (5) RP, (6) SATUAN -->
          <td style="text-align:right;font-weight:600;white-space:nowrap;">${formatDesimalCapaian(row.target_tahunan)}</td>
          <td style="text-align:right;font-weight:600;color:#1e3a8a;white-space:nowrap;">${formatRupiahCapaian(row.pagu_anggaran)}</td>
          <td style="text-align:center;font-weight:600;white-space:nowrap;">${row.satuan || '-'}</td>

          <!-- TARGET KINERJA: (7) TW I, (8) TW II, (9) TW III, (10) TW IV -->
          <td style="text-align:right;white-space:nowrap;">${formatDesimalCapaian(row.target_tw1)}</td>
          <td style="text-align:right;white-space:nowrap;">${formatDesimalCapaian(row.target_tw2)}</td>
          <td style="text-align:right;white-space:nowrap;">${formatDesimalCapaian(row.target_tw3)}</td>
          <td style="text-align:right;white-space:nowrap;">${formatDesimalCapaian(row.target_tw4)}</td>

          <!-- CAPAIAN KINERJA (TRIWULAN TERPILIH): REALISASI, %, PREDIKAT -->
          <td style="text-align:right;font-weight:700;color:#c2410c;white-space:nowrap;">${rkCur > 0 ? formatDesimalCapaian(rkCur) : '-'}</td>
          <td style="text-align:right;font-weight:700;color:#b91c1c;white-space:nowrap;">${(rkCur > 0 || ckCur > 0) ? formatDesimalCapaian(ckCur) + '%' : '-'}</td>
          <td style="text-align:center;white-space:nowrap;">${getPredikatBadgeHtml(pkCur)}</td>

          <!-- CAPAIAN KEUANGAN (TRIWULAN TERPILIH): REALISASI, % -->
          <td style="text-align:right;font-weight:700;color:#1e40af;white-space:nowrap;">${rqCur > 0 ? formatRupiahCapaian(rqCur) : '-'}</td>
          <td style="text-align:right;font-weight:700;color:#1d4ed8;white-space:nowrap;">${(rqCur > 0 || cqCur > 0) ? formatDesimalCapaian(cqCur) + '%' : '-'}</td>

          <!-- BUKTI PENDUKUNG -->
          <td style="text-align:center;white-space:nowrap;padding:6px 8px;" onclick="event.stopPropagation()">
            ${renderBuktiCell(row)}
          </td>

          <!-- AKSI CRUD -->
          <td style="text-align:center;white-space:nowrap;" onclick="event.stopPropagation()">
            <div style="display:inline-flex;gap:4px;align-items:center;">
              <button type="button" class="agenda-btn edit" onclick="openModalCapaian('edit', ${row.id}, '${activeCapaianTriwulan}')" title="Sunting / Isi Rincian Capaian Kinerja">
                <svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" stroke-width="2" fill="none"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
              </button>
              <button type="button" class="agenda-btn del" onclick="deleteCapaianData(${row.id})" title="Hapus Data Capaian">
                <svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" stroke-width="2" fill="none"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
              </button>
            </div>
          </td>
        </tr>
      `;
    } else {
      // JIKA MEMILIH "SEMUA TRIWULAN" / TAHUNAN: TAMPILKAN MATRIKS LENGKAP 42 KOLOM + BUKTI + AKSI
      const rpjmdKin = parseFloat(row.target_rpjmd_kinerja) || 0;
      const rpjmdKeu = parseFloat(row.target_rpjmd_keuangan) || 0;
      const cRenKin = parseFloat(row.capaian_renstra_kinerja !== undefined && row.capaian_renstra_kinerja > 0 ? row.capaian_renstra_kinerja : (rpjmdKin > 0 ? (rkTot / rpjmdKin) * 100 : 0)) || 0;
      const cRenKeu = parseFloat(row.capaian_renstra_keuangan !== undefined && row.capaian_renstra_keuangan > 0 ? row.capaian_renstra_keuangan : (rpjmdKeu > 0 ? (rqTot / rpjmdKeu) * 100 : 0)) || 0;

      html += `
        <tr data-capaian-id="${row.id}" onclick="onCapaianRowClick(event, ${row.id})" style="cursor:pointer;" title="Klik baris ini untuk mengedit data capaian kinerja lengkap">
          <!-- (1) NO -->
          <td style="text-align:center;font-weight:700;color:#64748b;white-space:nowrap;">${index + 1}</td>
          
          <!-- (2) TUJUAN / SASARAN / PROGRAM / KEGIATAN -->
          <td class="col-text" style="font-weight:700;color:#1e293b;line-height:1.45;min-width:220px;">${row.sasaran || '-'}</td>
          
          <!-- (3) INDIKATOR KINERJA -->
          <td class="col-text" style="color:#334155;line-height:1.45;min-width:200px;">${row.indikator || '-'}</td>
          
          <!-- DATA 2026: (4) TARGET, (5-8) RP PER TW, (9) SATUAN -->
          <td style="text-align:right;font-weight:600;white-space:nowrap;">${formatDesimalCapaian(row.target_tahunan)}</td>
          <td style="text-align:right;font-weight:600;color:#1e3a8a;white-space:nowrap;">${p1 > 0 ? formatRupiahCapaian(p1) : '-'}</td>
          <td style="text-align:right;font-weight:600;color:#1e3a8a;white-space:nowrap;">${p2 > 0 ? formatRupiahCapaian(p2) : '-'}</td>
          <td style="text-align:right;font-weight:600;color:#1e3a8a;white-space:nowrap;">${p3 > 0 ? formatRupiahCapaian(p3) : '-'}</td>
          <td style="text-align:right;font-weight:600;color:#1e3a8a;white-space:nowrap;">${p4 > 0 ? formatRupiahCapaian(p4) : '-'}</td>
          <td style="text-align:center;font-weight:600;white-space:nowrap;">${row.satuan || '-'}</td>

          <!-- TARGET KINERJA: (10) TW I, (11) TW II, (12) TW III, (13) TW IV -->
          <td style="text-align:right;white-space:nowrap;">${formatDesimalCapaian(row.target_tw1)}</td>
          <td style="text-align:right;white-space:nowrap;">${formatDesimalCapaian(row.target_tw2)}</td>
          <td style="text-align:right;white-space:nowrap;">${formatDesimalCapaian(row.target_tw3)}</td>
          <td style="text-align:right;white-space:nowrap;">${formatDesimalCapaian(row.target_tw4)}</td>

          <!-- CAPAIAN KINERJA TW I: (14, 15, 16) -->
          <td style="text-align:right;font-weight:600;color:#c2410c;white-space:nowrap;">${rk1 > 0 ? formatDesimalCapaian(rk1) : '-'}</td>
          <td style="text-align:right;font-weight:700;color:#b91c1c;white-space:nowrap;">${(rk1 > 0 || ck1 > 0) ? formatDesimalCapaian(ck1) + '%' : '-'}</td>
          <td style="text-align:center;white-space:nowrap;">${getPredikatBadgeHtml(pk1)}</td>

          <!-- CAPAIAN KINERJA TW II: (17, 18, 19) -->
          <td style="text-align:right;font-weight:600;color:#166534;white-space:nowrap;">${rk2 > 0 ? formatDesimalCapaian(rk2) : '-'}</td>
          <td style="text-align:right;font-weight:700;color:#15803d;white-space:nowrap;">${(rk2 > 0 || ck2 > 0) ? formatDesimalCapaian(ck2) + '%' : '-'}</td>
          <td style="text-align:center;white-space:nowrap;">${getPredikatBadgeHtml(pk2)}</td>

          <!-- CAPAIAN KINERJA TW III: (20, 21, 22) -->
          <td style="text-align:right;font-weight:600;color:#0369a1;white-space:nowrap;">${rk3 > 0 ? formatDesimalCapaian(rk3) : '-'}</td>
          <td style="text-align:right;font-weight:700;color:#0284c7;white-space:nowrap;">${(rk3 > 0 || ck3 > 0) ? formatDesimalCapaian(ck3) + '%' : '-'}</td>
          <td style="text-align:center;white-space:nowrap;">${getPredikatBadgeHtml(pk3)}</td>

          <!-- CAPAIAN KINERJA TW IV: (23, 24, 25) -->
          <td style="text-align:right;font-weight:600;color:#6b21a8;white-space:nowrap;">${rk4 > 0 ? formatDesimalCapaian(rk4) : '-'}</td>
          <td style="text-align:right;font-weight:700;color:#7e22ce;white-space:nowrap;">${(rk4 > 0 || ck4 > 0) ? formatDesimalCapaian(ck4) + '%' : '-'}</td>
          <td style="text-align:center;white-space:nowrap;">${getPredikatBadgeHtml(pk4)}</td>

          <!-- CAPAIAN KINERJA TOTAL 2026: (26, 27, 28) -->
          <td style="text-align:right;font-weight:800;color:#991b1b;background:#fff1f2;white-space:nowrap;">${rkTot > 0 ? formatDesimalCapaian(rkTot) : '-'}</td>
          <td style="text-align:right;font-weight:800;color:#b91c1c;background:#fff1f2;white-space:nowrap;">${(rkTot > 0 || ckTot > 0) ? formatDesimalCapaian(ckTot) + '%' : '-'}</td>
          <td style="text-align:center;background:#fff1f2;white-space:nowrap;">${getPredikatBadgeHtml(pkTot)}</td>

          <!-- CAPAIAN KEUANGAN TW I: (29, 30) -->
          <td style="text-align:right;font-weight:600;white-space:nowrap;">${rq1 > 0 ? formatRupiahCapaian(rq1) : '-'}</td>
          <td style="text-align:right;font-weight:700;color:#c2410c;white-space:nowrap;">${(rq1 > 0 || cq1 > 0) ? formatDesimalCapaian(cq1) + '%' : '-'}</td>

          <!-- CAPAIAN KEUANGAN TW II: (31, 32) -->
          <td style="text-align:right;font-weight:600;white-space:nowrap;">${rq2 > 0 ? formatRupiahCapaian(rq2) : '-'}</td>
          <td style="text-align:right;font-weight:700;color:#166534;white-space:nowrap;">${(rq2 > 0 || cq2 > 0) ? formatDesimalCapaian(cq2) + '%' : '-'}</td>

          <!-- CAPAIAN KEUANGAN TW III: (33, 34) -->
          <td style="text-align:right;font-weight:600;white-space:nowrap;">${rq3 > 0 ? formatRupiahCapaian(rq3) : '-'}</td>
          <td style="text-align:right;font-weight:700;color:#0369a1;white-space:nowrap;">${(rq3 > 0 || cq3 > 0) ? formatDesimalCapaian(cq3) + '%' : '-'}</td>

          <!-- CAPAIAN KEUANGAN TW IV: (35, 36) -->
          <td style="text-align:right;font-weight:600;white-space:nowrap;">${rq4 > 0 ? formatRupiahCapaian(rq4) : '-'}</td>
          <td style="text-align:right;font-weight:700;color:#6b21a8;white-space:nowrap;">${(rq4 > 0 || cq4 > 0) ? formatDesimalCapaian(cq4) + '%' : '-'}</td>

          <!-- CAPAIAN KEUANGAN TOTAL 2026: (37, 38) -->
          <td style="text-align:right;font-weight:800;color:#1e3a8a;background:#eff6ff;white-space:nowrap;">${rqTot > 0 ? formatRupiahCapaian(rqTot) : '-'}</td>
          <td style="text-align:right;font-weight:800;color:#1d4ed8;background:#eff6ff;white-space:nowrap;">${(rqTot > 0 || cqTot > 0) ? formatDesimalCapaian(cqTot) + '%' : '-'}</td>

          <!-- TARGET AKHIR RPJMD: (39, 40) -->
          <td style="text-align:right;font-weight:600;white-space:nowrap;">${rpjmdKin > 0 ? formatDesimalCapaian(rpjmdKin) : '-'}</td>
          <td style="text-align:right;font-weight:600;color:#1e3a8a;white-space:nowrap;">${rpjmdKeu > 0 ? formatRupiahCapaian(rpjmdKeu) : '-'}</td>

          <!-- CAPAIAN AKHIR RENSTRA: (41, 42) -->
          <td style="text-align:right;font-weight:700;color:#0f766e;white-space:nowrap;">${cRenKin > 0 ? formatDesimalCapaian(cRenKin) + '%' : '-'}</td>
          <td style="text-align:right;font-weight:700;color:#0f766e;white-space:nowrap;">${cRenKeu > 0 ? formatDesimalCapaian(cRenKeu) + '%' : '-'}</td>

          <!-- BUKTI PENDUKUNG -->
          <td style="text-align:center;white-space:nowrap;padding:6px 8px;" onclick="event.stopPropagation()">
            ${renderBuktiCell(row)}
          </td>

          <!-- AKSI CRUD -->
          <td style="text-align:center;white-space:nowrap;" onclick="event.stopPropagation()">
            <div style="display:inline-flex;gap:4px;align-items:center;">
              <button type="button" class="agenda-btn edit" onclick="openModalCapaian('edit', ${row.id}, '${activeCapaianTriwulan}')" title="Sunting / Isi Rincian Capaian Kinerja">
                <svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" stroke-width="2" fill="none"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
              </button>
              <button type="button" class="agenda-btn del" onclick="deleteCapaianData(${row.id})" title="Hapus Data Capaian">
                <svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" stroke-width="2" fill="none"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
              </button>
            </div>
          </td>
        </tr>
      `;
    }
  });

  tbody.innerHTML = html;
}
window.renderCapaianTable = renderCapaianTable;

function onCapaianRowClick(event, id) {
  if (event.target.closest('button') || event.target.closest('a')) return;
  openModalCapaian('edit', id, activeCapaianTriwulan);
}
window.onCapaianRowClick = onCapaianRowClick;

// Export ke Excel (.xls)
function exportExcelCapaian() {
  const selectTahun = document.getElementById('capaianFilterTahun');
  const selectTW = document.getElementById('capaianFilterTriwulan');
  const tahun = selectTahun ? selectTahun.value : activeCapaianTahun;
  const tw = selectTW ? selectTW.value : activeCapaianTriwulan;

  window.location.href = `/admin/capaian-kinerja/export-excel?tahun=${tahun}&triwulan=${encodeURIComponent(tw)}`;
}
window.exportExcelCapaian = exportExcelCapaian;

// Bagi Rata Pagu Tahunan ke 4 Triwulan Otomatis
function autoDistributePagu() {
  const paguTotal = parseRupiahInput(document.getElementById('crudCapaianPaguAnggaran')?.value);
  if (paguTotal > 0) {
    const perTw = Math.round(paguTotal / 4);
    const setVal = (id, val) => {
      const el = document.getElementById(id);
      if (el) el.value = val.toLocaleString('id-ID');
    };
    setVal('crudCapaianPaguTw1', perTw);
    setVal('crudCapaianPaguTw2', perTw);
    setVal('crudCapaianPaguTw3', perTw);
    setVal('crudCapaianPaguTw4', (paguTotal - (perTw * 3)));
    recalculateCapaianForm();
  }
}
window.autoDistributePagu = autoDistributePagu;

// Live Formula Calculation di Form Modal (Dihitung Real-time tanpa mengunci input manual)
function recalculateCapaianForm() {
  const triwulan = document.getElementById('crudCapaianTriwulan')?.value || 'TW I';
  const targetTahunan = parseFlexibleNumber(document.getElementById('crudCapaianTargetTahunan')?.value);
  const paguTotal = parseRupiahInput(document.getElementById('crudCapaianPaguAnggaran')?.value);

  // Target per Triwulan (Target TW I s/d TW IV)
  const tw1 = parseFlexibleNumber(document.getElementById('crudCapaianTw1')?.value);
  const tw2 = parseFlexibleNumber(document.getElementById('crudCapaianTw2')?.value);
  const tw3 = parseFlexibleNumber(document.getElementById('crudCapaianTw3')?.value);
  const tw4 = parseFlexibleNumber(document.getElementById('crudCapaianTw4')?.value);

  // Tentukan target untuk triwulan yang aktif
  let targetAktif = 0;
  if (triwulan === 'TW I') targetAktif = tw1 > 0 ? tw1 : (targetTahunan > 0 ? targetTahunan : 0);
  else if (triwulan === 'TW II') targetAktif = tw2 > 0 ? tw2 : (targetTahunan > 0 ? targetTahunan : 0);
  else if (triwulan === 'TW III') targetAktif = tw3 > 0 ? tw3 : (targetTahunan > 0 ? targetTahunan : 0);
  else if (triwulan === 'TW IV') targetAktif = tw4 > 0 ? tw4 : (targetTahunan > 0 ? targetTahunan : 0);

  // Input Realisasi Fisik & Keuangan Triwulan Ini
  const elRealKin = document.getElementById('crudCapaianRealisasiKinerja');
  const elRealKeu = document.getElementById('crudCapaianRealisasiKeuangan');
  const rKin = parseFlexibleNumber(elRealKin?.value);
  const rKeu = parseRupiahInput(elRealKeu?.value);

  const elPersenKin = document.getElementById('crudCapaianPersenKinerja');
  const elPredikat = document.getElementById('crudCapaianPredikat');
  const elPersenKeu = document.getElementById('crudCapaianPersenKeuangan');

  // Hitung % Capaian Fisik
  const cKin = targetAktif > 0 ? (rKin / targetAktif) * 100 : 0;
  if (elPersenKin && document.activeElement !== elPersenKin) {
    elPersenKin.value = (rKin > 0 || targetAktif > 0) ? cKin.toFixed(2) : '';
  }

  // Hitung Predikat
  if (elPredikat && document.activeElement !== elPredikat) {
    if (rKin > 0 || targetAktif > 0) {
      elPredikat.value = getPredikatFromPercent(cKin);
    }
  }

  // Hitung % Capaian Keuangan: (Realisasi Keuangan / Pagu Anggaran) * 100%
  const cKeu = paguTotal > 0 ? (rKeu / paguTotal) * 100 : 0;
  if (elPersenKeu && document.activeElement !== elPersenKeu) {
    elPersenKeu.value = (rKeu > 0 || paguTotal > 0) ? cKeu.toFixed(2) : '';
  }
}
window.recalculateCapaianForm = recalculateCapaianForm;

// Helper: Muat Realisasi dan Capaian Triwulan Tertentu ke Form Modal
function loadTriwulanRealisasiIntoForm(item, tw) {
  const setSafe = (elemId, val) => {
    const el = document.getElementById(elemId);
    if (el) el.value = (val !== null && val !== undefined) ? val : '';
  };
  const fmtRupiah = (val) => {
    if (!val || parseFloat(val) <= 0) return '';
    return parseFloat(val).toLocaleString('id-ID');
  };

  if (tw === 'TW I') {
    const rk = item.realisasi_kinerja_tw1 !== undefined ? item.realisasi_kinerja_tw1 : (item.realisasi_kinerja || '');
    const ck = item.capaian_kinerja_tw1 !== undefined ? item.capaian_kinerja_tw1 : (item.capaian_kinerja_persen || '');
    const pk = item.predikat_kinerja_tw1 || item.predikat_kinerja || '';
    const rq = item.realisasi_keuangan_tw1 !== undefined ? item.realisasi_keuangan_tw1 : (item.realisasi_keuangan || '');
    const cq = item.capaian_keuangan_tw1 !== undefined ? item.capaian_keuangan_tw1 : (item.capaian_keuangan_persen || '');
    setSafe('crudCapaianRealisasiKinerja', rk);
    setSafe('crudCapaianPersenKinerja', ck ? parseFloat(ck).toFixed(2) : '');
    setSafe('crudCapaianPredikat', pk || 'Sangat Berhasil');
    setSafe('crudCapaianRealisasiKeuangan', fmtRupiah(rq));
    setSafe('crudCapaianPersenKeuangan', cq ? parseFloat(cq).toFixed(2) : '');
  } else if (tw === 'TW II') {
    setSafe('crudCapaianRealisasiKinerja', item.realisasi_kinerja_tw2 || '');
    setSafe('crudCapaianPersenKinerja', item.capaian_kinerja_tw2 ? parseFloat(item.capaian_kinerja_tw2).toFixed(2) : '');
    setSafe('crudCapaianPredikat', item.predikat_kinerja_tw2 || 'Sangat Berhasil');
    setSafe('crudCapaianRealisasiKeuangan', fmtRupiah(item.realisasi_keuangan_tw2));
    setSafe('crudCapaianPersenKeuangan', item.capaian_keuangan_tw2 ? parseFloat(item.capaian_keuangan_tw2).toFixed(2) : '');
  } else if (tw === 'TW III') {
    setSafe('crudCapaianRealisasiKinerja', item.realisasi_kinerja_tw3 || '');
    setSafe('crudCapaianPersenKinerja', item.capaian_kinerja_tw3 ? parseFloat(item.capaian_kinerja_tw3).toFixed(2) : '');
    setSafe('crudCapaianPredikat', item.predikat_kinerja_tw3 || 'Sangat Berhasil');
    setSafe('crudCapaianRealisasiKeuangan', fmtRupiah(item.realisasi_keuangan_tw3));
    setSafe('crudCapaianPersenKeuangan', item.capaian_keuangan_tw3 ? parseFloat(item.capaian_keuangan_tw3).toFixed(2) : '');
  } else if (tw === 'TW IV') {
    setSafe('crudCapaianRealisasiKinerja', item.realisasi_kinerja_tw4 || '');
    setSafe('crudCapaianPersenKinerja', item.capaian_kinerja_tw4 ? parseFloat(item.capaian_kinerja_tw4).toFixed(2) : '');
    setSafe('crudCapaianPredikat', item.predikat_kinerja_tw4 || 'Sangat Berhasil');
    setSafe('crudCapaianRealisasiKeuangan', fmtRupiah(item.realisasi_keuangan_tw4));
    setSafe('crudCapaianPersenKeuangan', item.capaian_keuangan_tw4 ? parseFloat(item.capaian_keuangan_tw4).toFixed(2) : '');
  }
}
window.loadTriwulanRealisasiIntoForm = loadTriwulanRealisasiIntoForm;

// Dipanggil saat dropdown Periode Triwulan di modal berubah
function onModalTriwulanChange() {
  const tw = document.getElementById('crudCapaianTriwulan')?.value || 'TW I';
  const labelTriwulan = document.getElementById('labelRealisasiTriwulan');
  const labelFisik = document.getElementById('labelRealFisikTW');
  const labelKeu = document.getElementById('labelRealKeuTW');
  
  if (labelTriwulan) labelTriwulan.textContent = `Input Realisasi & Perhitungan Otomatis (${tw})`;
  if (labelFisik) labelFisik.innerHTML = `Realisasi Kinerja Fisik (${tw}) <span style="font-size:11px;color:#9a3412;font-weight:normal;">(Opsional)</span>`;
  if (labelKeu) labelKeu.textContent = `Realisasi Keuangan Rp (${tw})`;

  const id = document.getElementById('crudCapaianId')?.value;
  if (id) {
    const item = capaianDb.find(x => x.id == id);
    if (item) {
      loadTriwulanRealisasiIntoForm(item, tw);
    }
  }
  recalculateCapaianForm();
}
window.onModalTriwulanChange = onModalTriwulanChange;

// Buka Modal Tambah/Edit Capaian Kinerja (Format Asli Bersih + Bebas Diedit Tanpa Kunci)
function openModalCapaian(mode, id, selectedTw) {
  const modal = document.getElementById('modalCrudCapaian');
  const title = document.getElementById('modalCrudCapaianTitle');
  const idInput = document.getElementById('crudCapaianId');
  const modeInput = document.getElementById('crudCapaianMode');
  const form = document.getElementById('formCrudCapaian');
  if (!modal) {
    alert('Modal dialog belum termuat di halaman. Silakan refresh halaman.');
    return;
  }

  if (modeInput) modeInput.value = mode;
  if (idInput) idInput.value = id || '';

  const setSafe = (elemId, val) => {
    const el = document.getElementById(elemId);
    if (el) el.value = (val !== null && val !== undefined) ? val : '';
  };
  const fmtRupiahField = (val) => {
    if (!val || parseFloat(val) <= 0) return '';
    return parseFloat(val).toLocaleString('id-ID');
  };

  // Tentukan Triwulan yang dipilih
  let currentTw = selectedTw || (activeCapaianTriwulan === 'Semua' ? 'TW I' : activeCapaianTriwulan);
  setSafe('crudCapaianTahun', activeCapaianTahun);
  setSafe('crudCapaianTriwulan', currentTw);

  if (mode === 'create') {
    if (title) title.textContent = 'TAMBAH DATA CAPAIAN KINERJA';
    if (form) form.reset();

    setSafe('crudCapaianTahun', activeCapaianTahun);
    setSafe('crudCapaianTriwulan', currentTw);
    setSafe('crudCapaianSatuan', 'Persentase');

    setSafe('crudCapaianRealisasiKinerja', '');
    setSafe('crudCapaianPersenKinerja', '');
    setSafe('crudCapaianPredikat', 'Sangat Berhasil');
    setSafe('crudCapaianRealisasiKeuangan', '');
    setSafe('crudCapaianPersenKeuangan', '');
    setSafe('crudCapaianBuktiLink', '');
    setSafe('crudCapaianBuktiKeterangan', '');

    const existFile = document.getElementById('crudCapaianExistingFile');
    if (existFile) existFile.style.display = 'none';

    onModalTriwulanChange();
  } else {
    if (title) title.textContent = 'SUNTING DATA CAPAIAN KINERJA';

    const item = capaianDb.find(x => x.id == id);
    if (!item) return;

    setSafe('crudCapaianTahun', item.tahun);
    setSafe('crudCapaianSatuan', item.satuan || 'Persentase');
    setSafe('crudCapaianSasaran', item.sasaran || '');
    setSafe('crudCapaianIndikator', item.indikator || '');
    setSafe('crudCapaianTargetTahunan', item.target_tahunan || '');
    setSafe('crudCapaianPaguAnggaran', fmtRupiahField(item.pagu_anggaran));

    setSafe('crudCapaianTw1', item.target_tw1 || '');
    setSafe('crudCapaianTw2', item.target_tw2 || '');
    setSafe('crudCapaianTw3', item.target_tw3 || '');
    setSafe('crudCapaianTw4', item.target_tw4 || '');

    setSafe('crudCapaianBuktiLink', item.bukti_link || '');
    setSafe('crudCapaianBuktiKeterangan', item.bukti_keterangan || '');

    const existFile = document.getElementById('crudCapaianExistingFile');
    if (existFile) {
      if (item.bukti_file_name) {
        existFile.style.display = 'block';
        existFile.innerHTML = `📁 File tersimpan: <a href="/admin/capaian-kinerja/download-bukti/${item.id}" target="_blank" style="color:#0f766e;text-decoration:underline;font-weight:700;">${item.bukti_file_name}</a> ${item.bukti_file_size ? '(' + item.bukti_file_size + ')' : ''}`;
      } else {
        existFile.style.display = 'none';
      }
    }

    onModalTriwulanChange();
  }

  // Tampilkan popup
  modal.classList.add('show');
  modal.classList.add('active');
  modal.style.display = 'flex';
  modal.style.visibility = 'visible';
  modal.style.opacity = '1';
  modal.style.zIndex = '99999';
}
window.openModalCapaian = openModalCapaian;

// Submit Handler Form Capaian Kinerja (Menyimpan data dan memperbarui capaian triwulan & total)
async function handleCapaianSubmit(event) {
  event.preventDefault();
  const mode = document.getElementById('crudCapaianMode')?.value || 'create';
  const id = document.getElementById('crudCapaianId')?.value;

  const tahun = parseInt(document.getElementById('crudCapaianTahun')?.value) || activeCapaianTahun;
  const triwulan = document.getElementById('crudCapaianTriwulan')?.value || 'TW I';
  const satuan = document.getElementById('crudCapaianSatuan')?.value || 'Persentase';
  const sasaran = document.getElementById('crudCapaianSasaran')?.value || '-';
  const indikator = document.getElementById('crudCapaianIndikator')?.value || '-';
  
  const targetTahunan = parseFlexibleNumber(document.getElementById('crudCapaianTargetTahunan')?.value);
  const pagu = parseRupiahInput(document.getElementById('crudCapaianPaguAnggaran')?.value);

  const tw1 = parseFlexibleNumber(document.getElementById('crudCapaianTw1')?.value);
  const tw2 = parseFlexibleNumber(document.getElementById('crudCapaianTw2')?.value);
  const tw3 = parseFlexibleNumber(document.getElementById('crudCapaianTw3')?.value);
  const tw4 = parseFlexibleNumber(document.getElementById('crudCapaianTw4')?.value);

  const paguTw = pagu > 0 ? (pagu / 4) : 0;

  // Ambil input Realisasi & Capaian Triwulan aktif
  const curRealKin = parseFlexibleNumber(document.getElementById('crudCapaianRealisasiKinerja')?.value);
  const curCapKin = parseFlexibleNumber(document.getElementById('crudCapaianPersenKinerja')?.value);
  const curPredKin = document.getElementById('crudCapaianPredikat')?.value || getPredikatFromPercent(curCapKin);

  const curRealKeu = parseRupiahInput(document.getElementById('crudCapaianRealisasiKeuangan')?.value);
  const curCapKeu = parseFlexibleNumber(document.getElementById('crudCapaianPersenKeuangan')?.value);

  const buktiLink = document.getElementById('crudCapaianBuktiLink')?.value || '';
  const buktiKeterangan = document.getElementById('crudCapaianBuktiKeterangan')?.value || '';

  // Cari existing item di database jika ada
  const existingItem = id ? capaianDb.find(x => x.id == id) : null;

  // Realisasi Kinerja TW 1 - 4
  const rKin1 = triwulan === 'TW I' ? curRealKin : (existingItem ? (parseFloat(existingItem.realisasi_kinerja_tw1) || 0) : 0);
  const cKin1 = triwulan === 'TW I' ? curCapKin : (existingItem ? (parseFloat(existingItem.capaian_kinerja_tw1) || 0) : 0);
  const pKin1 = triwulan === 'TW I' ? curPredKin : (existingItem?.predikat_kinerja_tw1 || '-');

  const rKin2 = triwulan === 'TW II' ? curRealKin : (existingItem ? (parseFloat(existingItem.realisasi_kinerja_tw2) || 0) : 0);
  const cKin2 = triwulan === 'TW II' ? curCapKin : (existingItem ? (parseFloat(existingItem.capaian_kinerja_tw2) || 0) : 0);
  const pKin2 = triwulan === 'TW II' ? curPredKin : (existingItem?.predikat_kinerja_tw2 || '-');

  const rKin3 = triwulan === 'TW III' ? curRealKin : (existingItem ? (parseFloat(existingItem.realisasi_kinerja_tw3) || 0) : 0);
  const cKin3 = triwulan === 'TW III' ? curCapKin : (existingItem ? (parseFloat(existingItem.capaian_kinerja_tw3) || 0) : 0);
  const pKin3 = triwulan === 'TW III' ? curPredKin : (existingItem?.predikat_kinerja_tw3 || '-');

  const rKin4 = triwulan === 'TW IV' ? curRealKin : (existingItem ? (parseFloat(existingItem.realisasi_kinerja_tw4) || 0) : 0);
  const cKin4 = triwulan === 'TW IV' ? curCapKin : (existingItem ? (parseFloat(existingItem.capaian_kinerja_tw4) || 0) : 0);
  const pKin4 = triwulan === 'TW IV' ? curPredKin : (existingItem?.predikat_kinerja_tw4 || '-');

  const rKinTot = rKin1 + rKin2 + rKin3 + rKin4;
  const cKinTot = targetTahunan > 0 ? (rKinTot / targetTahunan) * 100 : 0;
  const pKinTot = rKinTot > 0 ? getPredikatFromPercent(cKinTot) : '-';

  // Realisasi Keuangan TW 1 - 4
  const rKeu1 = triwulan === 'TW I' ? curRealKeu : (existingItem ? (parseFloat(existingItem.realisasi_keuangan_tw1) || 0) : 0);
  const cKeu1 = triwulan === 'TW I' ? curCapKeu : (existingItem ? (parseFloat(existingItem.capaian_keuangan_tw1) || 0) : 0);

  const rKeu2 = triwulan === 'TW II' ? curRealKeu : (existingItem ? (parseFloat(existingItem.realisasi_keuangan_tw2) || 0) : 0);
  const cKeu2 = triwulan === 'TW II' ? curCapKeu : (existingItem ? (parseFloat(existingItem.capaian_keuangan_tw2) || 0) : 0);

  const rKeu3 = triwulan === 'TW III' ? curRealKeu : (existingItem ? (parseFloat(existingItem.realisasi_keuangan_tw3) || 0) : 0);
  const cKeu3 = triwulan === 'TW III' ? curCapKeu : (existingItem ? (parseFloat(existingItem.capaian_keuangan_tw3) || 0) : 0);

  const rKeu4 = triwulan === 'TW IV' ? curRealKeu : (existingItem ? (parseFloat(existingItem.realisasi_keuangan_tw4) || 0) : 0);
  const cKeu4 = triwulan === 'TW IV' ? curCapKeu : (existingItem ? (parseFloat(existingItem.capaian_keuangan_tw4) || 0) : 0);

  const rKeuTot = rKeu1 + rKeu2 + rKeu3 + rKeu4;
  const cKeuTot = pagu > 0 ? (rKeuTot / pagu) * 100 : 0;

  // Kirim ke Backend Laravel MySQL
  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  let savedServerData = null;

  if (csrfMeta && csrfMeta.content) {
    const formData = new FormData();
    formData.append('tahun', tahun);
    formData.append('triwulan', triwulan);
    formData.append('satuan', satuan);
    formData.append('sasaran', sasaran);
    formData.append('indikator', indikator);
    formData.append('target_tahunan', targetTahunan);
    formData.append('pagu_anggaran', pagu);

    formData.append('pagu_tw1', paguTw);
    formData.append('pagu_tw2', paguTw);
    formData.append('pagu_tw3', paguTw);
    formData.append('pagu_tw4', paguTw);

    formData.append('target_tw1', tw1);
    formData.append('target_tw2', tw2);
    formData.append('target_tw3', tw3);
    formData.append('target_tw4', tw4);

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

    formData.append('realisasi_kinerja_total', rKinTot);
    formData.append('capaian_kinerja_total', cKinTot);
    formData.append('predikat_kinerja_total', pKinTot);

    formData.append('realisasi_keuangan_tw1', rKeu1);
    formData.append('capaian_keuangan_tw1', cKeu1);

    formData.append('realisasi_keuangan_tw2', rKeu2);
    formData.append('capaian_keuangan_tw2', cKeu2);

    formData.append('realisasi_keuangan_tw3', rKeu3);
    formData.append('capaian_keuangan_tw3', cKeu3);

    formData.append('realisasi_keuangan_tw4', rKeu4);
    formData.append('capaian_keuangan_tw4', cKeu4);

    formData.append('realisasi_keuangan_total', rKeuTot);
    formData.append('capaian_keuangan_total', cKeuTot);

    formData.append('bukti_link', buktiLink);
    formData.append('bukti_keterangan', buktiKeterangan);

    try {
      const url = (mode === 'create' && !id) ? '/admin/capaian-kinerja' : `/admin/capaian-kinerja/${id}/update`;
      const res = await fetch(url, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfMeta.content,
          'Accept': 'application/json'
        },
        body: formData
      });
      const resData = await res.json();
      if (resData.success && resData.data) {
        savedServerData = resData.data;
      }
    } catch (err) {
      console.warn('Backend API sync notice:', err);
    }
  }

  // Update Data di Memori Lokal
  const itemPayload = {
    id: savedServerData ? savedServerData.id : (id ? parseInt(id) : (capaianDb.length > 0 ? Math.max(...capaianDb.map(x => x.id || 0)) + 1 : 1)),
    tahun: tahun,
    triwulan: triwulan,
    satuan: satuan,
    sasaran: sasaran,
    indikator: indikator,
    target_tahunan: targetTahunan,
    pagu_anggaran: pagu,
    pagu_tw1: paguTw,
    pagu_tw2: paguTw,
    pagu_tw3: paguTw,
    pagu_tw4: paguTw,
    target_tw1: tw1,
    target_tw2: tw2,
    target_tw3: tw3,
    target_tw4: tw4,
    realisasi_kinerja_tw1: rKin1,
    capaian_kinerja_tw1: cKin1,
    predikat_kinerja_tw1: pKin1,
    realisasi_kinerja_tw2: rKin2,
    capaian_kinerja_tw2: cKin2,
    predikat_kinerja_tw2: pKin2,
    realisasi_kinerja_tw3: rKin3,
    capaian_kinerja_tw3: cKin3,
    predikat_kinerja_tw3: pKin3,
    realisasi_kinerja_tw4: rKin4,
    capaian_kinerja_tw4: cKin4,
    predikat_kinerja_tw4: pKin4,
    realisasi_kinerja_total: rKinTot,
    capaian_kinerja_total: cKinTot,
    predikat_kinerja_total: pKinTot,
    realisasi_keuangan_tw1: rKeu1,
    capaian_keuangan_tw1: cKeu1,
    realisasi_keuangan_tw2: rKeu2,
    capaian_keuangan_tw2: cKeu2,
    realisasi_keuangan_tw3: rKeu3,
    capaian_keuangan_tw3: cKeu3,
    realisasi_keuangan_tw4: rKeu4,
    capaian_keuangan_tw4: cKeu4,
    realisasi_keuangan_total: rKeuTot,
    capaian_keuangan_total: cKeuTot,
    bukti_link: buktiLink,
    bukti_keterangan: buktiKeterangan,
    status_bukti: buktiLink ? 'Lengkap' : 'Belum Ada'
  };

  if (mode === 'create' && !id) {
    const existingIdx = capaianDb.findIndex(x => x.id == itemPayload.id);
    if (existingIdx !== -1) {
      capaianDb[existingIdx] = itemPayload;
    } else {
      capaianDb.push(itemPayload);
    }
    customCapaianYears.add(tahun);
    activeCapaianTahun = tahun;
  } else {
    const targetId = id || itemPayload.id;
    const idx = capaianDb.findIndex(x => x.id == targetId);
    if (idx !== -1) {
      capaianDb[idx] = {
        ...capaianDb[idx],
        ...itemPayload
      };
      customCapaianYears.add(tahun);
    }
  }

  closeAdminModal('modalCrudCapaian');
  populateCapaianYearFilter(activeCapaianTahun);
  renderCapaianTable();

  if (typeof showAdminToast === 'function') {
    showAdminToast(`Data Capaian Kinerja ${triwulan} berhasil disimpan!`, 'success');
  }
}
window.handleCapaianSubmit = handleCapaianSubmit;

// Hapus Data Capaian
async function deleteCapaianData(id) {
  if (!confirm('Apakah Anda yakin ingin menghapus data capaian kinerja ini?')) return;

  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  if (csrfMeta && csrfMeta.content) {
    try {
      await fetch(`/admin/capaian-kinerja/${id}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': csrfMeta.content,
          'Accept': 'application/json'
        }
      });
    } catch (err) {
      console.warn('Backend delete sync:', err);
    }
  }

  capaianDb = capaianDb.filter(x => x.id != id);
  renderCapaianTable();

  if (typeof showAdminToast === 'function') {
    showAdminToast('Data Capaian Kinerja berhasil dihapus.', 'info');
  }
}
window.deleteCapaianData = deleteCapaianData;

// Modal Cepat Kirim / Kelola Bukti Pendukung
function openQuickBuktiModal(id) {
  const item = capaianDb.find(x => x.id == id);
  if (!item) return;

  const modal = document.getElementById('modalBuktiCapaian');
  if (!modal) return;

  document.getElementById('quickBuktiId').value = id;
  document.getElementById('quickBuktiIndikatorText').textContent = item.indikator || item.sasaran;
  document.getElementById('quickBuktiMetaText').textContent = `Periode: ${item.triwulan} Tahun ${item.tahun} • Target: ${formatDesimalCapaian(item.target_tahunan)} ${item.satuan}`;
  document.getElementById('quickBuktiLink').value = item.bukti_link || '';
  document.getElementById('quickBuktiKeterangan').value = item.bukti_keterangan || '';
  
  const currentFile = document.getElementById('quickBuktiFileCurrent');
  if (item.bukti_file_name) {
    currentFile.style.display = 'block';
    currentFile.textContent = `📁 File tersimpan: ${item.bukti_file_name} (${item.bukti_file_size || ''})`;
  } else {
    currentFile.style.display = 'none';
  }

  modal.classList.add('active');
}
window.openQuickBuktiModal = openQuickBuktiModal;

// Submit Handler Bukti Pendukung
async function handleQuickBuktiSubmit(event) {
  event.preventDefault();
  const id = document.getElementById('quickBuktiId').value;
  const link = document.getElementById('quickBuktiLink').value;
  const keterangan = document.getElementById('quickBuktiKeterangan').value;
  const fileInput = document.getElementById('quickBuktiFile');

  const item = capaianDb.find(x => x.id == id);
  if (!item) return;

  // Sync dengan Backend Laravel
  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  if (csrfMeta && csrfMeta.content) {
    const formData = new FormData();
    formData.append('bukti_link', link);
    formData.append('bukti_keterangan', keterangan);
    if (fileInput && fileInput.files && fileInput.files[0]) {
      formData.append('bukti_file', fileInput.files[0]);
    }

    try {
      const res = await fetch(`/admin/capaian-kinerja/${id}/bukti`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfMeta.content,
          'Accept': 'application/json'
        },
        body: formData
      });
      const data = await res.json();
      if (data.success && data.data) {
        Object.assign(item, data.data);
      }
    } catch (err) {
      console.warn('Backend bukti sync:', err);
    }
  }

  item.bukti_link = link;
  item.bukti_keterangan = keterangan;
  if (fileInput && fileInput.files && fileInput.files[0]) {
    item.bukti_file_name = fileInput.files[0].name;
    item.bukti_file_size = Math.round(fileInput.files[0].size / 1024) + ' KB';
  }

  closeAdminModal('modalBuktiCapaian');
  renderCapaianTable();

  if (typeof showAdminToast === 'function') {
    showAdminToast('Bukti Pendukung berhasil diperbarui!', 'success');
  }
}
window.handleQuickBuktiSubmit = handleQuickBuktiSubmit;

// Cetak & Ekspor PDF Laporan Resmi Capaian Kinerja (Langsung Stream PDF)
function cetakLaporanCapaian() {
  const selectTahun = document.getElementById('capaianFilterTahun');
  const selectTW = document.getElementById('capaianFilterTriwulan');
  const tahun = selectTahun ? selectTahun.value : (typeof activeCapaianTahun !== 'undefined' ? activeCapaianTahun : '2026');
  const tw = selectTW ? selectTW.value : (typeof activeCapaianTriwulan !== 'undefined' ? activeCapaianTriwulan : 'Semua');
  const baseUrl = (typeof window !== 'undefined' && window.appUrl) ? window.appUrl.replace(/\/$/, '') : '';

  const url = `${baseUrl}/admin/capaian-kinerja/cetak?tahun=${encodeURIComponent(tahun)}&triwulan=${encodeURIComponent(tw)}`;
  window.open(url, '_blank');
}
window.cetakLaporanCapaian = cetakLaporanCapaian;

// Auto inisialisasi modul capaian saat DOM ready
document.addEventListener('DOMContentLoaded', () => {
  populateCapaianYearFilter(activeCapaianTahun);
  if (document.getElementById('tbodyCapaianKinerja')) {
    renderCapaianTable();
  }
});








