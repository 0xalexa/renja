<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portal Dokumen PEP — Sistem Informasi Manajemen Dokumen</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ time() }}">
</head>

<body>

  <!-- ==================== TOP BAR PEMERINTAHAN ==================== -->
  <div class="gov-topbar">
    <div class="gov-topbar-inner">
      <div class="gov-topbar-left">
        <span>PEMERINTAH DAERAH &bull; DINAS PENDIDIKAN</span>
        <span style="opacity:0.4;">|</span>
        <span>SUBBAGIAN PERENCANAAN, EVALUASI & PELAPORAN (PEP)</span>
      </div>
      <div class="gov-topbar-right">
        <span id="topbarDate">Rabu, 9 September 2026</span>
      </div>
    </div>
  </div>

  <!-- ==================== NAVBAR HORIZONTAL UTAMA ==================== -->
  <header class="main-navbar">
    <div class="navbar-inner">

      <!-- Brand -->
      <a href="#" class="nav-brand" onclick="navigate('dashboard'); return false;">
        <span class="brand-badge">PEP</span>
        <span class="brand-title">SIM-PEP PORTAL</span>
      </a>

      <button class="nav-mobile-toggle" id="mobileNavToggle" aria-label="Menu Navigasi">☰</button>

      <!-- Menu Horizontal -->
      <ul class="nav-menu" id="navMenu">

        <!-- Beranda -->
        <li class="nav-item active" data-nav="dashboard">
          <a href="#" class="nav-link" onclick="navigate('dashboard'); return false;">
            Beranda
          </a>
        </li>

        <!-- Dropdown Rencana Kerja -->
        <li class="nav-item" data-nav="renja">
          <a href="#" class="nav-link" onclick="event.preventDefault(); toggleDropdown(this);">
            Rencana Kerja <span class="nav-caret">▼</span>
          </a>
          <ul class="nav-dropdown">
            <li class="nav-dropdown-item">
              <a href="#" class="nav-dropdown-link" onclick="navigate('renja-murni'); return false;">Renja Murni</a>
            </li>
            <li class="nav-dropdown-item">
              <a href="#" class="nav-dropdown-link" onclick="navigate('renja-perubahan'); return false;">Renja
                Perubahan</a>
            </li>
          </ul>
        </li>

        <!-- Dropdown Perjanjian Kinerja -->
        <li class="nav-item" data-nav="pk">
          <a href="#" class="nav-link" onclick="event.preventDefault(); toggleDropdown(this);">
            Perjanjian Kinerja <span class="nav-caret">▼</span>
          </a>
          <ul class="nav-dropdown">
            <li class="nav-dropdown-item">
              <a href="#" class="nav-dropdown-link" onclick="navigate('pk-murni'); return false;">PK Murni</a>
            </li>
            <li class="nav-dropdown-item">
              <a href="#" class="nav-dropdown-link" onclick="navigate('pk-perubahan'); return false;">PK Perubahan</a>
            </li>
          </ul>
        </li>

        <!-- Dropdown Anggaran -->
        <li class="nav-item" data-nav="dpa">
          <a href="#" class="nav-link" onclick="event.preventDefault(); toggleDropdown(this);">
            Pelaksanaan Anggaran <span class="nav-caret">▼</span>
          </a>
          <ul class="nav-dropdown">
            <li class="nav-dropdown-item">
              <a href="#" class="nav-dropdown-link" onclick="navigate('dpa-murni'); return false;">DPA Murni</a>
            </li>
            <li class="nav-dropdown-item">
              <a href="#" class="nav-dropdown-link" onclick="navigate('dpa-perubahan'); return false;">DPA Perubahan</a>
            </li>
          </ul>
        </li>

        <!-- Dropdown Arsip Surat -->
        <li class="nav-item" data-nav="surat">
          <a href="#" class="nav-link" onclick="event.preventDefault(); toggleDropdown(this);">
            Arsip Surat <span class="nav-caret">▼</span>
          </a>
          <ul class="nav-dropdown">
            <li class="nav-dropdown-item">
              <a href="#" class="nav-dropdown-link" onclick="navigate('surat-masuk'); return false;">Surat Masuk</a>
            </li>
            <li class="nav-dropdown-item">
              <a href="#" class="nav-dropdown-link" onclick="navigate('surat-keluar'); return false;">Surat Keluar</a>
            </li>
          </ul>
        </li>

        <!-- Laporan Simdapangda -->
        <li class="nav-item" data-nav="laporan">
          <a href="#" class="nav-link" onclick="navigate('laporan'); return false;">
            Laporan Simdapangda
          </a>
        </li>

        <!-- Capaian Kinerja OPD (Triwulan & Evaluasi) -->
        <li class="nav-item" data-nav="capaian-kinerja">
          <a href="javascript:void(0)" class="nav-link" onclick="navigate('capaian-kinerja');">
            Capaian Kinerja
          </a>
        </li>

      </ul>

      <!-- User / Profile Section (Read-Only User) -->
      <div class="nav-right">
        @auth
          <span class="nav-user-text">
            <strong>{{ Auth::user()->name }}</strong> &bull; {{ (Auth::user()->role ?? 'user') === 'admin' ? 'Administrator' : 'Pegawai' }}
          </span>
          <form action="{{ route('logout') }}" method="POST" style="display:inline;margin:0;">
            @csrf
            <button type="submit" class="btn-nav-logout" style="background:none;border:none;cursor:pointer;font-family:inherit;" title="Keluar dari sesi portal">
              Keluar
            </button>
          </form>
        @else
          <a href="{{ route('login') }}" class="btn btn-outline btn-sm" style="font-size:11px;font-weight:700;color:var(--primary);padding:4px 8px;">
            Masuk / Login &rarr;
          </a>
        @endauth
      </div>

    </div>
  </header>

  <!-- ==================== MAIN PAGE CONTAINER ==================== -->
  <main class="page-container">

    <!-- ==================== 1. BERANDA (DASHBOARD PENGGUNA) ==================== -->
    <div class="page active" id="page-dashboard">

      <!-- Page Header -->
      <div class="page-header">
        <div class="page-header-title">
          <h1>Portal Penelusuran Dokumen & Arsip PEP</h1>
          <p>Layanan akses informasi dan unduh berkas resmi perencanaan, kinerja, anggaran, dan persuratan dinas.</p>
        </div>
        <div class="page-header-meta">
          T.A. 2026 &bull; Mode Pengguna (Read-Only)
        </div>
      </div>

      <!-- 4 Stats Boxes -->
      <div class="stats-row">
        <div class="stat-box">
          <div class="stat-label">Dokumen Renja Tersedia</div>
          <div class="stat-value" id="kpiRenja">{{ $renjaMurni->count() + $renjaPerubahan->count() }}</div>
          <div class="stat-desc">Rencana Kerja Murni & Perubahan</div>
        </div>
        <div class="stat-box border-emerald">
          <div class="stat-label">Dokumen PK & DPA</div>
          <div class="stat-value" id="kpiPkDpa">{{ $pkMurni->count() + $pkPerubahan->count() + $dpaMurni->count() + $dpaPerubahan->count() }}</div>
          <div class="stat-desc">Perjanjian Kinerja & Dokumen DPA</div>
        </div>
        <div class="stat-box border-amber">
          <div class="stat-label">Agenda Surat Masuk</div>
          <div class="stat-value" id="kpiSuratMasuk">{{ $suratMasuk->count() }}</div>
          <div class="stat-desc">{{ date('F Y') }} &bull; Tercatat Resmi</div>
        </div>
        <div class="stat-box border-red">
          <div class="stat-label">Agenda Surat Keluar</div>
          <div class="stat-value" id="kpiSuratKeluar">{{ $suratKeluar->count() }}</div>
          <div class="stat-desc">{{ date('F Y') }} &bull; Terarsip</div>
        </div>
      </div>

      <!-- Chart & Quick Links -->
      <div class="dash-columns">

        <!-- Chart Panel -->
        <div class="panel">
          <div class="panel-header">
            <span class="panel-title">Statistik Ketersediaan Dokumen per Modul (T.A. 2026)</span>
            <span class="badge badge-blue">Tahun Berjalan</span>
          </div>
          <div class="panel-body">
            <div class="chart-wrap">
              <canvas id="chartAnggaran"></canvas>
            </div>
          </div>
        </div>

        <!-- Quick Links Panel -->
        <div class="panel">
          <div class="panel-header">
            <span class="panel-title">Pintasan Kategori Berkas</span>
          </div>
          <div class="panel-body">
            <div class="quick-menu-list">
              <a href="#" class="quick-menu-item" onclick="navigate('renja-murni'); return false;">
                <span>Rencana Kerja (Renja Murni)</span>
                <span>&rarr;</span>
              </a>
              <a href="#" class="quick-menu-item" onclick="navigate('pk-murni'); return false;">
                <span>Perjanjian Kinerja (PK Murni)</span>
                <span>&rarr;</span>
              </a>
              <a href="#" class="quick-menu-item" onclick="navigate('dpa-murni'); return false;">
                <span>Pelaksanaan Anggaran (DPA)</span>
                <span>&rarr;</span>
              </a>
              <a href="#" class="quick-menu-item" onclick="navigate('surat-masuk'); return false;">
                <span>Buku Agenda Surat Masuk</span>
                <span>&rarr;</span>
              </a>
              <a href="#" class="quick-menu-item" onclick="navigate('surat-keluar'); return false;">
                <span>Buku Agenda Surat Keluar</span>
                <span>&rarr;</span>
              </a>
              <a href="#" class="quick-menu-item" onclick="navigate('laporan'); return false;">
                <span>Laporan Rekapitulasi Simdapangda</span>
                <span>&rarr;</span>
              </a>
              <a href="javascript:void(0)" class="quick-menu-item" onclick="navigate('capaian-kinerja');" style="background:#f0fdf4;border-color:#bbf7d0;">
                <span style="font-weight:700;color:#15803d;">Evaluasi & Capaian Kinerja</span>
                <span class="badge badge-green" id="badgeCountCapaian" style="font-size:11px;margin-left:auto;margin-right:8px;">{{ $countCapaian ?? 0 }} Data</span>
                <span style="color:#15803d;">&rarr;</span>
              </a>
            </div>
          </div>
        </div>

      </div>

      <!-- Recent Documents Table -->
      <div class="panel">
        <div class="panel-header">
          <span class="panel-title">Daftar Dokumen dan Arsip Terkini</span>
          <button class="btn btn-outline btn-sm" onclick="navigate('renja-murni')">Lihat Semua Berkas Renja</button>
        </div>
        <div class="table-responsive">
          <table class="gov-table">
            <thead>
              <tr>
                <th style="width:45px;text-align:center;">No</th>
                <th style="width:75px;text-align:center;">Tahun</th>
                <th>Nama Dokumen / Subjek Surat</th>
                <th style="width:160px;">Kelompok Modul</th>
                <th style="width:230px;">Berkas Terlampir</th>
                <th style="width:115px;text-align:center;">Tanggal</th>
                <th style="width:105px;text-align:center;">Status</th>
                <th style="width:130px;text-align:center;">Aksi Pengguna</th>
              </tr>
            </thead>
            <tbody id="dashboardRecentTable">
              <!-- Rendered by JS -->
            </tbody>
          </table>
        </div>
      </div>

    </div>

    <!-- ==================== 2. RENJA MURNI ==================== -->
    <div class="page" id="page-renja-murni">
      <div class="page-header">
        <div class="page-header-title">
          <h1>Dokumen Rencana Kerja — Renja Murni</h1>
          <p>Penelusuran dan unduh berkas dokumen rencana kerja tahunan penetapan awal.</p>
        </div>
        <div class="page-header-meta">Modul Rencana Kerja</div>
      </div>

      <div class="table-toolbar">
        <div class="toolbar-group">
          <input type="text" class="form-control search-box" placeholder="Cari nama dokumen atau program kegiatan...">
          <select class="form-control filter-year">
            <option value="">Semua Tahun</option>
            <option value="2026">2026</option>
            <option value="2025">2025</option>
            <option value="2024">2024</option>
          </select>
        </div>
        <div class="toolbar-group">
          <span style="font-size:12px;color:var(--text-muted);">Klik <strong>Unduh</strong> untuk mengunduh dokumen
            resmi</span>
        </div>
      </div>

      <div class="table-responsive">
        <table class="gov-table" id="table-renja-murni">
          <thead>
            <tr>
              <th style="width:45px;text-align:center;">No</th>
              <th style="width:75px;text-align:center;">Tahun</th>
              <th>Nama Dokumen / Program Kegiatan</th>
              <th>Keterangan</th>
              <th style="width:240px;">Berkas Lampiran</th>
              <th style="width:110px;text-align:center;">Status</th>
              <th style="width:130px;text-align:center;">Aksi</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
      <div class="pagination-bar">
        <span>Menampilkan data dokumen Renja Murni</span>
        <span>Halaman 1 dari 1</span>
      </div>
    </div>

    <!-- ==================== 3. RENJA PERUBAHAN ==================== -->
    <div class="page" id="page-renja-perubahan">
      <div class="page-header">
        <div class="page-header-title">
          <h1>Dokumen Rencana Kerja — Renja Perubahan</h1>
          <p>Penelusuran dan unduh berkas perubahan rencana kerja tahun berjalan.</p>
        </div>
        <div class="page-header-meta">Modul Rencana Kerja</div>
      </div>

      <div class="table-toolbar">
        <div class="toolbar-group">
          <input type="text" class="form-control search-box" placeholder="Cari dokumen perubahan...">
          <select class="form-control filter-year">
            <option value="">Semua Tahun</option>
            <option value="2026">2026</option>
            <option value="2025">2025</option>
          </select>
        </div>
        <div class="toolbar-group">
          <span style="font-size:12px;color:var(--text-muted);">Dokumen resmi hasil revisi APBD</span>
        </div>
      </div>

      <div class="table-responsive">
        <table class="gov-table" id="table-renja-perubahan">
          <thead>
            <tr>
              <th style="width:45px;text-align:center;">No</th>
              <th style="width:75px;text-align:center;">Tahun</th>
              <th>Nama Dokumen / Revisi Program</th>
              <th>Keterangan Perubahan</th>
              <th style="width:240px;">Berkas Lampiran</th>
              <th style="width:110px;text-align:center;">Status</th>
              <th style="width:130px;text-align:center;">Aksi</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
      <div class="pagination-bar">
        <span>Menampilkan data dokumen Renja Perubahan</span>
        <span>Halaman 1 dari 1</span>
      </div>
    </div>

    <!-- ==================== 4. PK MURNI ==================== -->
    <div class="page" id="page-pk-murni">
      <div class="page-header">
        <div class="page-header-title">
          <h1>Perjanjian Kinerja (PK Murni)</h1>
          <p>Penetapan sasaran dan perjanjian kinerja awal tahun pejabat/satuan kerja dinas.</p>
        </div>
        <div class="page-header-meta">Modul Kinerja</div>
      </div>

      <div class="table-toolbar">
        <div class="toolbar-group">
          <input type="text" class="form-control search-box"
            placeholder="Cari perihal atau jabatan perjanjian kinerja...">
          <select class="form-control filter-year">
            <option value="">Semua Tahun</option>
            <option value="2026">2026</option>
            <option value="2025">2025</option>
          </select>
        </div>
        <div class="toolbar-group">
          <span style="font-size:12px;color:var(--text-muted);">Format dokumen PDF telah ditandatangani</span>
        </div>
      </div>

      <div class="table-responsive">
        <table class="gov-table" id="table-pk-murni">
          <thead>
            <tr>
              <th style="width:45px;text-align:center;">No</th>
              <th style="width:75px;text-align:center;">Tahun</th>
              <th>Jabatan / Nama Perjanjian Kinerja</th>
              <th>Uraian Target</th>
              <th style="width:240px;">Berkas Lampiran</th>
              <th style="width:110px;text-align:center;">Status</th>
              <th style="width:130px;text-align:center;">Aksi</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
      <div class="pagination-bar">
        <span>Menampilkan data dokumen PK Murni</span>
        <span>Halaman 1 dari 1</span>
      </div>
    </div>

    <!-- ==================== 5. PK PERUBAHAN ==================== -->
    <div class="page" id="page-pk-perubahan">
      <div class="page-header">
        <div class="page-header-title">
          <h1>Perjanjian Kinerja (PK Perubahan)</h1>
          <p>Dokumen adendum dan penyesuaian target kinerja tahun berjalan.</p>
        </div>
        <div class="page-header-meta">Modul Kinerja</div>
      </div>

      <div class="table-toolbar">
        <div class="toolbar-group">
          <input type="text" class="form-control search-box" placeholder="Cari dokumen PK perubahan...">
          <select class="form-control filter-year">
            <option value="">Semua Tahun</option>
            <option value="2026">2026</option>
            <option value="2025">2025</option>
          </select>
        </div>
        <div class="toolbar-group">
          <span style="font-size:12px;color:var(--text-muted);">Adendum target kinerja disahkan</span>
        </div>
      </div>

      <div class="table-responsive">
        <table class="gov-table" id="table-pk-perubahan">
          <thead>
            <tr>
              <th style="width:45px;text-align:center;">No</th>
              <th style="width:75px;text-align:center;">Tahun</th>
              <th>Jabatan / Nama Perjanjian Kinerja</th>
              <th>Keterangan Revisi Target</th>
              <th style="width:240px;">Berkas Lampiran</th>
              <th style="width:110px;text-align:center;">Status</th>
              <th style="width:130px;text-align:center;">Aksi</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
      <div class="pagination-bar">
        <span>Menampilkan data dokumen PK Perubahan</span>
        <span>Halaman 1 dari 1</span>
      </div>
    </div>

    <!-- ==================== 6. DPA MURNI ==================== -->
    <div class="page" id="page-dpa-murni">
      <div class="page-header">
        <div class="page-header-title">
          <h1>Pelaksanaan Anggaran — DPA Murni</h1>
          <p>Dokumen Pelaksanaan Anggaran (DPA) SKPD Dinas Pendidikan versi murni.</p>
        </div>
        <div class="page-header-meta">Modul Anggaran</div>
      </div>

      <div class="table-toolbar">
        <div class="toolbar-group">
          <input type="text" class="form-control search-box" placeholder="Cari sub-kegiatan atau dokumen anggaran...">
          <select class="form-control filter-year">
            <option value="">Semua Tahun</option>
            <option value="2026">2026</option>
            <option value="2025">2025</option>
          </select>
        </div>
        <div class="toolbar-group">
          <span style="font-size:12px;color:var(--text-muted);">Pagu alokasi anggaran belanja dinas</span>
        </div>
      </div>

      <div class="table-responsive">
        <table class="gov-table" id="table-dpa-murni">
          <thead>
            <tr>
              <th style="width:45px;text-align:center;">No</th>
              <th style="width:75px;text-align:center;">Tahun</th>
              <th>Nama Sub-Kegiatan / Dokumen DPA</th>
              <th>Pagu Anggaran & Uraian</th>
              <th style="width:240px;">Berkas Lampiran</th>
              <th style="width:110px;text-align:center;">Status</th>
              <th style="width:130px;text-align:center;">Aksi</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
      <div class="pagination-bar">
        <span>Menampilkan data dokumen DPA Murni</span>
        <span>Halaman 1 dari 1</span>
      </div>
    </div>

    <!-- ==================== 7. DPA PERUBAHAN ==================== -->
    <div class="page" id="page-dpa-perubahan">
      <div class="page-header">
        <div class="page-header-title">
          <h1>Pelaksanaan Anggaran — DPA Perubahan</h1>
          <p>Dokumen Pelaksanaan Perubahan Anggaran (DPPA) SKPD tahun anggaran berjalan.</p>
        </div>
        <div class="page-header-meta">Modul Anggaran</div>
      </div>

      <div class="table-toolbar">
        <div class="toolbar-group">
          <input type="text" class="form-control search-box" placeholder="Cari dokumen DPPA perubahan...">
          <select class="form-control filter-year">
            <option value="">Semua Tahun</option>
            <option value="2026">2026</option>
            <option value="2025">2025</option>
          </select>
        </div>
        <div class="toolbar-group">
          <span style="font-size:12px;color:var(--text-muted);">Penyesuaian pergeseran belanja program</span>
        </div>
      </div>

      <div class="table-responsive">
        <table class="gov-table" id="table-dpa-perubahan">
          <thead>
            <tr>
              <th style="width:45px;text-align:center;">No</th>
              <th style="width:75px;text-align:center;">Tahun</th>
              <th>Nama Sub-Kegiatan / Dokumen DPPA</th>
              <th>Keterangan Perubahan Pagu</th>
              <th style="width:240px;">Berkas Lampiran</th>
              <th style="width:110px;text-align:center;">Status</th>
              <th style="width:130px;text-align:center;">Aksi</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
      <div class="pagination-bar">
        <span>Menampilkan data dokumen DPA Perubahan</span>
        <span>Halaman 1 dari 1</span>
      </div>
    </div>

    <!-- ==================== 8. SURAT MASUK ==================== -->
    <div class="page" id="page-surat-masuk">
      <div class="page-header">
        <div class="page-header-title">
          <h1>Buku Agenda Surat Masuk</h1>
          <p>Penelusuran arsip surat dinas yang diterima dari instansi dan lembaga mitra.</p>
        </div>
        <div class="page-header-meta">Arsip Persuratan</div>
      </div>

      <div class="table-toolbar">
        <div class="toolbar-group">
          <input type="text" class="form-control search-box"
            placeholder="Cari nomor surat, instansi pengirim, atau perihal...">
          <input type="date" class="form-control filter-date-from" title="Dari tanggal">
          <input type="date" class="form-control filter-date-to" title="Sampai tanggal">
        </div>
        <div class="toolbar-group">
          <button class="btn btn-outline" onclick="showToast('Mencetak rekapitulasi agenda surat masuk...', 'info')">
            Cetak Rekapitulasi
          </button>
        </div>
      </div>

      <div class="table-responsive">
        <table class="gov-table" id="table-surat-masuk">
          <thead>
            <tr>
              <th style="width:45px;text-align:center;">No</th>
              <th style="width:160px;">No. Surat</th>
              <th style="width:105px;text-align:center;">Tanggal Surat</th>
              <th>Perihal Surat</th>
              <th>Asal Instansi</th>
              <th style="width:180px;">Scan Berkas</th>
              <th style="width:110px;text-align:center;">Status</th>
              <th style="width:130px;text-align:center;">Aksi</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
      <div class="pagination-bar">
        <span>Menampilkan arsip agenda surat masuk</span>
        <span>Halaman 1 dari 1</span>
      </div>
    </div>

    <!-- ==================== 9. SURAT KELUAR ==================== -->
    <div class="page" id="page-surat-keluar">
      <div class="page-header">
        <div class="page-header-title">
          <h1>Buku Agenda Surat Keluar</h1>
          <p>Penelusuran arsip surat dinas yang diterbitkan oleh Dinas Pendidikan / Subbag PEP.</p>
        </div>
        <div class="page-header-meta">Arsip Persuratan</div>
      </div>

      <div class="table-toolbar">
        <div class="toolbar-group">
          <input type="text" class="form-control search-box"
            placeholder="Cari nomor surat, tujuan instansi, atau perihal...">
          <input type="date" class="form-control filter-date-from" title="Dari tanggal">
          <input type="date" class="form-control filter-date-to" title="Sampai tanggal">
        </div>
        <div class="toolbar-group">
          <button class="btn btn-outline" onclick="showToast('Mencetak rekapitulasi agenda surat keluar...', 'info')">
            Cetak Rekapitulasi
          </button>
        </div>
      </div>

      <div class="table-responsive">
        <table class="gov-table" id="table-surat-keluar">
          <thead>
            <tr>
              <th style="width:45px;text-align:center;">No</th>
              <th style="width:160px;">No. Surat</th>
              <th style="width:105px;text-align:center;">Tanggal Surat</th>
              <th>Perihal Surat</th>
              <th>Tujuan Instansi</th>
              <th style="width:180px;">Scan Berkas</th>
              <th style="width:110px;text-align:center;">Status</th>
              <th style="width:130px;text-align:center;">Aksi</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
      <div class="pagination-bar">
        <span>Menampilkan arsip agenda surat keluar</span>
        <span>Halaman 1 dari 1</span>
      </div>
    </div>

    <!-- ==================== 10. LAPORAN SIMDAPANGDA ==================== -->
    <div class="page" id="page-laporan">
      <div class="page-header">
        <div class="page-header-title">
          <h1>Pelaporan Terpadu Simdapangda</h1>
          <p>Matriks evaluasi kepatuhan dokumen perencanaan, kinerja, anggaran, dan persuratan dinas.</p>
        </div>
        <div class="page-header-meta">Pelaporan Terpadu</div>
      </div>

      <!-- Filter Panel -->
      <div class="panel">
        <div class="panel-header">
          <span class="panel-title">Filter Matriks Rekapitulasi</span>
        </div>
        <div class="panel-body">
          <div
            style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:12px;align-items:flex-end;">
            <div class="form-group" style="margin:0;">
              <label>Tahun Anggaran</label>
              <select class="form-control" id="repYear" style="width:100%;">
                <option value="2026">2026 (Tahun Berjalan)</option>
                <option value="2025">2025</option>
                <option value="2024">2024</option>
              </select>
            </div>
            <div class="form-group" style="margin:0;">
              <label>Periode Rekapitulasi</label>
              <select class="form-control" id="repPeriod" style="width:100%;">
                <option>Tahunan (Januari – Desember)</option>
                <option>Triwulan I (Jan – Mar)</option>
                <option>Triwulan II (Apr – Jun)</option>
                <option>Triwulan III (Jul – Sep)</option>
                <option>Triwulan IV (Okt – Des)</option>
              </select>
            </div>
            <div class="form-group" style="margin:0;">
              <label>Lingkup Dokumen</label>
              <select class="form-control" id="repScope" style="width:100%;">
                <option>Semua Modul Terpadu</option>
                <option>Rencana Kerja (Renja)</option>
                <option>Perjanjian Kinerja (PK)</option>
                <option>Pelaksanaan Anggaran (DPA)</option>
                <option>Arsip Persuratan</option>
              </select>
            </div>
            <div>
              <button class="btn btn-primary" style="width:100%;height:33px;"
                onclick="showToast('Memuat matriks rekapitulasi data...', 'success')">
                Tampilkan Data
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Summary Table Panel -->
      <div class="panel">
        <div class="panel-header">
          <span class="panel-title">Matriks Kepatuhan Administrasi PEP — T.A. 2026</span>
          <div style="display:flex;gap:6px;">
            <button class="btn btn-outline btn-sm" onclick="showToast('Menyiapkan dokumen format PDF...', 'info')">
              Export PDF
            </button>
            <button class="btn btn-success btn-sm" onclick="showToast('Menyiapkan berkas Excel (.xlsx)...', 'success')">
              Export Excel (.xlsx)
            </button>
          </div>
        </div>
        <div class="table-responsive">
          <table class="gov-table">
            <thead>
              <tr>
                <th style="width:45px;text-align:center;">No</th>
                <th>Kelompok Administrasi</th>
                <th>Kategori / Versi</th>
                <th style="width:130px;text-align:center;">Target Berkas</th>
                <th style="width:140px;text-align:center;">Realisasi Berkas</th>
                <th style="width:120px;text-align:center;">Kepatuhan (%)</th>
                <th style="width:130px;text-align:center;">Pembaruan</th>
                <th style="width:110px;text-align:center;">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td style="text-align:center;">1</td>
                <td><strong>Rencana Kerja</strong></td>
                <td>Renja Murni</td>
                <td style="text-align:center;">—</td>
                <td style="text-align:center;">{{ $renjaMurni->count() }} Dokumen</td>
                <td style="text-align:center;"><strong>{{ $renjaMurni->count() > 0 ? '100%' : '0%' }}</strong></td>
                <td style="text-align:center;">{{ $renjaMurni->first()?->created_at?->format('d/m/Y') ?? '-' }}</td>
                <td style="text-align:center;"><span class="badge {{ $renjaMurni->count() > 0 ? 'badge-green' : 'badge-gray' }}">{{ $renjaMurni->count() > 0 ? 'Lengkap' : 'Kosong' }}</span></td>
              </tr>
              <tr>
                <td style="text-align:center;">2</td>
                <td><strong>Rencana Kerja</strong></td>
                <td>Renja Perubahan</td>
                <td style="text-align:center;">—</td>
                <td style="text-align:center;">{{ $renjaPerubahan->count() }} Dokumen</td>
                <td style="text-align:center;"><strong>{{ $renjaPerubahan->count() > 0 ? '100%' : '0%' }}</strong></td>
                <td style="text-align:center;">{{ $renjaPerubahan->first()?->created_at?->format('d/m/Y') ?? '-' }}</td>
                <td style="text-align:center;"><span class="badge {{ $renjaPerubahan->count() > 0 ? 'badge-green' : 'badge-gray' }}">{{ $renjaPerubahan->count() > 0 ? 'Lengkap' : 'Kosong' }}</span></td>
              </tr>
              <tr>
                <td style="text-align:center;">3</td>
                <td><strong>Perjanjian Kinerja</strong></td>
                <td>PK Murni</td>
                <td style="text-align:center;">—</td>
                <td style="text-align:center;">{{ $pkMurni->count() }} Dokumen</td>
                <td style="text-align:center;"><strong>{{ $pkMurni->count() > 0 ? '100%' : '0%' }}</strong></td>
                <td style="text-align:center;">{{ $pkMurni->first()?->created_at?->format('d/m/Y') ?? '-' }}</td>
                <td style="text-align:center;"><span class="badge {{ $pkMurni->count() > 0 ? 'badge-green' : 'badge-gray' }}">{{ $pkMurni->count() > 0 ? 'Lengkap' : 'Kosong' }}</span></td>
              </tr>
              <tr>
                <td style="text-align:center;">4</td>
                <td><strong>Perjanjian Kinerja</strong></td>
                <td>PK Perubahan</td>
                <td style="text-align:center;">—</td>
                <td style="text-align:center;">{{ $pkPerubahan->count() }} Dokumen</td>
                <td style="text-align:center;"><strong>{{ $pkPerubahan->count() > 0 ? '100%' : '0%' }}</strong></td>
                <td style="text-align:center;">{{ $pkPerubahan->first()?->created_at?->format('d/m/Y') ?? '-' }}</td>
                <td style="text-align:center;"><span class="badge {{ $pkPerubahan->count() > 0 ? 'badge-green' : 'badge-gray' }}">{{ $pkPerubahan->count() > 0 ? 'Lengkap' : 'Kosong' }}</span></td>
              </tr>
              <tr>
                <td style="text-align:center;">5</td>
                <td><strong>Pelaksanaan Anggaran</strong></td>
                <td>DPA Murni</td>
                <td style="text-align:center;">—</td>
                <td style="text-align:center;">{{ $dpaMurni->count() }} Dokumen</td>
                <td style="text-align:center;"><strong>{{ $dpaMurni->count() > 0 ? '100%' : '0%' }}</strong></td>
                <td style="text-align:center;">{{ $dpaMurni->first()?->created_at?->format('d/m/Y') ?? '-' }}</td>
                <td style="text-align:center;"><span class="badge {{ $dpaMurni->count() > 0 ? 'badge-green' : 'badge-gray' }}">{{ $dpaMurni->count() > 0 ? 'Lengkap' : 'Kosong' }}</span></td>
              </tr>
              <tr>
                <td style="text-align:center;">6</td>
                <td><strong>Pelaksanaan Anggaran</strong></td>
                <td>DPA Perubahan</td>
                <td style="text-align:center;">—</td>
                <td style="text-align:center;">{{ $dpaPerubahan->count() }} Dokumen</td>
                <td style="text-align:center;"><strong>{{ $dpaPerubahan->count() > 0 ? '100%' : '0%' }}</strong></td>
                <td style="text-align:center;">{{ $dpaPerubahan->first()?->created_at?->format('d/m/Y') ?? '-' }}</td>
                <td style="text-align:center;"><span class="badge {{ $dpaPerubahan->count() > 0 ? 'badge-green' : 'badge-gray' }}">{{ $dpaPerubahan->count() > 0 ? 'Lengkap' : 'Kosong' }}</span></td>
              </tr>
              <tr>
                <td style="text-align:center;">7</td>
                <td><strong>Arsip Persuratan</strong></td>
                <td>Surat Masuk</td>
                <td style="text-align:center;">—</td>
                <td style="text-align:center;">{{ $suratMasuk->count() }} Berkas</td>
                <td style="text-align:center;"><strong>{{ $suratMasuk->count() > 0 ? 'Aktif' : '-' }}</strong></td>
                <td style="text-align:center;">{{ $suratMasuk->first()?->tanggal_surat ? date('d/m/Y', strtotime($suratMasuk->first()->tanggal_surat)) : '-' }}</td>
                <td style="text-align:center;"><span class="badge {{ $suratMasuk->count() > 0 ? 'badge-blue' : 'badge-gray' }}">{{ $suratMasuk->count() > 0 ? 'Tercatat' : 'Kosong' }}</span></td>
              </tr>
              <tr>
                <td style="text-align:center;">8</td>
                <td><strong>Arsip Persuratan</strong></td>
                <td>Surat Keluar</td>
                <td style="text-align:center;">—</td>
                <td style="text-align:center;">{{ $suratKeluar->count() }} Berkas</td>
                <td style="text-align:center;"><strong>{{ $suratKeluar->count() > 0 ? 'Aktif' : '-' }}</strong></td>
                <td style="text-align:center;">{{ $suratKeluar->first()?->tanggal_surat ? date('d/m/Y', strtotime($suratKeluar->first()->tanggal_surat)) : '-' }}</td>
                <td style="text-align:center;"><span class="badge {{ $suratKeluar->count() > 0 ? 'badge-blue' : 'badge-gray' }}">{{ $suratKeluar->count() > 0 ? 'Tercatat' : 'Kosong' }}</span></td>
              </tr>
              <tr>
                <td style="text-align:center;">9</td>
                <td><strong>Evaluasi & Pelaporan</strong></td>
                <td>Capaian Kinerja Program</td>
                <td style="text-align:center;">—</td>
                <td style="text-align:center;">{{ $countCapaian }} Indikator</td>
                <td style="text-align:center;"><strong>{{ $countCapaian > 0 ? '100%' : '0%' }}</strong></td>
                <td style="text-align:center;">{{ $capaianKinerjaList->first()?->updated_at?->format('d/m/Y') ?? '-' }}</td>
                <td style="text-align:center;"><span class="badge {{ $countCapaian > 0 ? 'badge-green' : 'badge-gray' }}">{{ $countCapaian > 0 ? 'Aktif' : 'Kosong' }}</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>

    <!-- ==================== CAPAIAN KINERJA (USER VIEW & INPUT REALISASI) ==================== -->
    <div class="page" id="page-capaian-kinerja">
      <div class="page-header">
        <div class="page-header-title">
          <h1>Evaluasi & Capaian Kinerja Program / Kegiatan</h1>
          <p>Pemantauan realisasi kinerja fisik, realisasi keuangan, dan kelengkapan bukti dukung.</p>
        </div>
        <div class="page-header-meta">
          T.A. 2026 &bull; Mode Pengguna (Input Realisasi & Bukti Dukung)
        </div>
      </div>

      <!-- Info Alert Box For User -->
      <div style="background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:10px;padding:12px 16px;margin-bottom:16px;display:flex;align-items:center;gap:12px;">
        <span style="font-size:20px;color:#2563eb;">ℹ️</span>
        <div style="font-size:12.5px;color:#1e40af;line-height:1.5;">
          <strong>Hak Akses Pengguna:</strong> Data Sasaran, Indikator, Target Kinerja, dan Pagu Anggaran telah ditetapkan secara resmi oleh Administrator (terkunci). Anda dapat mengisi <strong>Realisasi Kinerja Fisik</strong>, <strong>Realisasi Keuangan (Rp)</strong>, serta mengunggah <strong>Bukti Pendukung</strong> dengan menekan tombol <strong>Edit</strong> (<svg viewBox="0 0 24 24" width="12" height="12" stroke="currentColor" stroke-width="2" fill="none" style="display:inline;vertical-align:middle;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>) atau <strong>+ Isi Bukti</strong>.
        </div>
      </div>

      <!-- Filter Toolbar -->
      <div class="table-toolbar" style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px;background:#f8fafc;padding:12px 16px;border-radius:10px;border:1px solid #e2e8f0;">
        <div class="toolbar-group" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
          
          <!-- Filter Tahun -->
          <div style="display:flex;align-items:center;gap:8px;">
            <span style="font-size:12px;font-weight:700;color:#475569;white-space:nowrap;">Tahun:</span>
            <select id="capaianFilterTahun" class="form-control" onchange="onCapaianFilterChange()" style="min-width:110px;height:38px;padding:0 10px;font-weight:700;border:1.5px solid #cbd5e1;border-radius:8px;font-size:13px;color:#1e293b;background:#ffffff;">
              @foreach($capaianYears ?? [2026] as $yr)
                <option value="{{ $yr }}" {{ $yr == 2026 ? 'selected' : '' }}>{{ $yr }}</option>
              @endforeach
            </select>
          </div>

          <!-- Filter Triwulan -->
          <div style="display:flex;align-items:center;gap:8px;">
            <span style="font-size:12px;font-weight:700;color:#475569;white-space:nowrap;">Triwulan:</span>
            <select id="capaianFilterTriwulan" class="form-control" onchange="onCapaianFilterChange()" style="min-width:165px;height:38px;padding:0 10px;font-weight:700;border:1.5px solid #cbd5e1;border-radius:8px;font-size:13px;color:#1e293b;background:#ffffff;">
              <option value="TW I" selected>Triwulan I (TW I)</option>
              <option value="TW II">Triwulan II (TW II)</option>
              <option value="TW III">Triwulan III (TW III)</option>
              <option value="TW IV">Triwulan IV (TW IV)</option>
              <option value="Semua">Semua / Tahunan</option>
            </select>
          </div>

          <!-- Cari Sasaran / Indikator -->
          <div style="display:flex;align-items:center;">
            <input 
              type="text" 
              id="capaianSearchInput" 
              class="form-control" 
              placeholder="Cari Sasaran / Indikator..." 
              oninput="onCapaianSearch(this.value)"
              style="min-width:230px;height:38px;padding:0 12px;border:1.5px solid #cbd5e1;border-radius:8px;font-size:12.5px;"
            >
          </div>

        </div>

        <!-- Action Buttons: Export Excel & Cetak PDF -->
        <div class="toolbar-group" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
          <button type="button" class="btn btn-outline" onclick="exportExcelCapaian()" title="Export Laporan Lengkap ke Berkas Excel (.xls)" style="height:38px;padding:0 14px;border:1.5px solid #16a34a;color:#15803d;background:#f0fdf4;border-radius:8px;display:inline-flex;align-items:center;gap:6px;font-size:12.5px;font-weight:700;cursor:pointer;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
              <polyline points="14 2 14 8 20 8"></polyline>
              <line x1="8" y1="13" x2="16" y2="13"></line>
              <line x1="8" y1="17" x2="16" y2="17"></line>
              <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            <span>Export Excel</span>
          </button>

          <button type="button" class="btn btn-outline" onclick="cetakLaporanCapaian()" title="Cetak atau Unduh Laporan Resmi PDF (Landscape)" style="height:38px;padding:0 14px;border:1.5px solid #0284c7;color:#0369a1;background:#f0f9ff;border-radius:8px;display:inline-flex;align-items:center;gap:6px;font-size:12.5px;font-weight:700;cursor:pointer;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="6 9 6 2 18 2 18 9"></polyline>
              <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
              <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            <span>Cetak / PDF</span>
          </button>
        </div>
      </div>

      <!-- Tabel Capaian Kinerja Multi-Level Header (Dinamis Sesuai Tampilan Screenshot User) -->
      <div class="table-responsive" style="border:1.5px solid #cbd5e1;border-radius:12px;background:#ffffff;box-shadow:0 1px 3px rgba(0,0,0,0.03);overflow-x:auto;width:100%;">
        <div style="overflow-x:auto;width:100%;">
          <table class="gov-table table-capaian-grid" id="tableCapaianKinerja" style="width:100%;border-collapse:collapse;table-layout:auto;">
            <thead id="theadCapaianKinerja">
              <!-- Baris 1: Header Grup Utama -->
              <tr class="head-top">
                <th rowspan="3" style="width:38px;text-align:center;">No</th>
                <th rowspan="3" class="th-left col-text" style="text-align:left;min-width:220px;white-space:normal;word-break:break-word;">Tujuan / Sasaran / Program / Kegiatan / Sub Kegiatan</th>
                <th rowspan="3" class="th-left col-text" style="text-align:left;min-width:200px;white-space:normal;word-break:break-word;">Indikator Kinerja</th>
                <th colspan="3" id="thDataTahun">Data 2026</th>
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
                <th colspan="3" id="thSubKinerjaTW" style="text-align:center;background:#fff7ed;color:#9a3412;font-weight:800;white-space:nowrap;">TW I</th>
                <!-- Capaian Keuangan (Sub TW Dinamis) -->
                <th colspan="2" id="thSubKeuanganTW" style="text-align:center;background:#fff7ed;color:#9a3412;font-weight:800;white-space:nowrap;">TW I</th>
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
            </thead>
            <tbody id="tbodyCapaianKinerja">
              <!-- Rendered automatically by renderCapaianTable() -->
            </tbody>
          </table>
        </div>
      </div>

    </div>

  </main>

  <!-- ==================== MODAL: DETAIL PREVIEW ==================== -->
  <div class="modal-overlay" id="modalDetail">
    <div class="modal-dialog">
      <div class="modal-header">
        <h3 id="detailModalTitle">DETAIL INFORMASI BERKAS</h3>
        <button class="modal-close-btn" onclick="closeModal('modalDetail')">&times;</button>
      </div>
      <div class="modal-body" id="detailListContent" style="font-size:13px;line-height:1.8;">
        <!-- Dynamic detail content -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalDetail')">Tutup</button>
        <button type="button" class="btn btn-primary" id="btnDetailDownload">Unduh Berkas Resmi</button>
      </div>
    </div>
  </div>

  <!-- ==================== MODAL: INPUT CAPAIAN KINERJA (PENGGUNA) ==================== -->
  <div class="modal-overlay" id="modalUserCapaian" style="display:none;align-items:center;justify-content:center;z-index:9999;">
    <div class="modal-dialog" style="max-width:750px;width:95%;max-height:90vh;overflow-y:auto;border-radius:14px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);background:#ffffff;">
      <div class="modal-header" style="background:#f8fafc;padding:16px 20px;border-bottom:1.5px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;border-radius:14px 14px 0 0;">
        <div style="display:flex;align-items:center;gap:10px;">
          <span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;background:#e0f2fe;color:#0369a1;font-weight:800;font-size:15px;">📊</span>
          <div>
            <h3 style="margin:0;font-size:16px;font-weight:800;color:#0f172a;">Input Capaian Kinerja & Realisasi Keuangan</h3>
            <span style="font-size:11.5px;color:#64748b;">Pengisian khusus Realisasi Capaian Kinerja, Keuangan, dan Bukti Pendukung</span>
          </div>
        </div>
        <button type="button" class="modal-close-btn" onclick="closeModal('modalUserCapaian')" style="background:none;border:none;font-size:26px;cursor:pointer;color:#64748b;line-height:1;padding:4px 8px;border-radius:6px;transition:all 0.15s;" title="Tutup Modal (Esc)" aria-label="Tutup Modal">&times;</button>
      </div>

      <form onsubmit="handleUserCapaianSubmit(event)">
        <input type="hidden" id="userCrudId">

        <div class="modal-body" style="padding:20px;display:flex;flex-direction:column;gap:16px;">

          <!-- Info Read-Only Data Sasaran (Terkunci) -->
          <div style="background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:10px;padding:14px 16px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
              <span style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:0.5px;color:#64748b;">Informasi Kegiatan (Ditetapkan Admin & Terkunci)</span>
              <span class="badge badge-gray" style="font-size:10.5px;font-weight:700;">🔒 Read-Only</span>
            </div>
            <div style="display:grid;grid-template-columns:1fr;gap:8px;font-size:12.5px;">
              <div>
                <strong style="color:#334155;">Tujuan / Sasaran / Program:</strong>
                <div id="userViewSasaran" style="color:#0f172a;font-weight:600;margin-top:2px;">-</div>
              </div>
              <div>
                <strong style="color:#334155;">Indikator Kinerja:</strong>
                <div id="userViewIndikator" style="color:#0f172a;margin-top:2px;">-</div>
              </div>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:4px;padding-top:8px;border-top:1px dashed #cbd5e1;">
                <div>
                  <span style="color:#64748b;font-size:11.5px;">Target Tahunan:</span>
                  <div id="userViewTargetTahunan" style="font-weight:700;color:#0f172a;">-</div>
                </div>
                <div>
                  <span style="color:#64748b;font-size:11.5px;">Pagu Anggaran:</span>
                  <div id="userViewPagu" style="font-weight:700;color:#1e3a8a;">-</div>
                </div>
              </div>
              <div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:8px;background:#ffffff;padding:8px 12px;border-radius:8px;border:1px solid #e2e8f0;margin-top:4px;text-align:center;">
                <div><span style="font-size:10.5px;color:#64748b;">Target TW I</span><div id="userViewTw1" style="font-weight:700;font-size:12px;color:#334155;">-</div></div>
                <div><span style="font-size:10.5px;color:#64748b;">Target TW II</span><div id="userViewTw2" style="font-weight:700;font-size:12px;color:#334155;">-</div></div>
                <div><span style="font-size:10.5px;color:#64748b;">Target TW III</span><div id="userViewTw3" style="font-weight:700;font-size:12px;color:#334155;">-</div></div>
                <div><span style="font-size:10.5px;color:#64748b;">Target TW IV</span><div id="userViewTw4" style="font-weight:700;font-size:12px;color:#334155;">-</div></div>
              </div>
            </div>
          </div>

          <!-- Pilihan Triwulan Pelaporan -->
          <div>
            <label style="display:block;font-size:12px;font-weight:700;color:#1e293b;margin-bottom:6px;">
              Pilih Periode Triwulan yang Diisi / Diedit <span style="color:#e11d48;">*</span>
            </label>
            <select id="userCrudTriwulan" class="form-control" onchange="onModalUserTriwulanChange()" style="width:100%;height:40px;font-weight:700;font-size:13px;border:1.5px solid #00875a;border-radius:8px;background:#f0fdf4;color:#166534;">
              <option value="TW I">Triwulan I (TW I)</option>
              <option value="TW II">Triwulan II (TW II)</option>
              <option value="TW III">Triwulan III (TW III)</option>
              <option value="TW IV">Triwulan IV (TW IV)</option>
            </select>
          </div>

          <!-- Section: Capaian Kinerja (Fisik) -->
          <div style="background:#fff7ed;border:1.5px solid #fed7aa;border-radius:10px;padding:14px 16px;">
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:12px;">
              <span style="color:#c2410c;font-weight:800;font-size:14px;">🎯</span>
              <strong style="color:#9a3412;font-size:13px;" id="labelUserTriwulanHeader">Input Realisasi & Perhitungan Otomatis (TW I)</strong>
            </div>

            <div style="display:grid;grid-template-columns:1.2fr 1fr 1.3fr;gap:12px;">
              <div>
                <label id="labelUserRealFisik" style="display:block;font-size:12px;font-weight:700;color:#7c2d12;margin-bottom:4px;">
                  Realisasi Fisik (TW I)
                </label>
                <input 
                  type="text" 
                  id="userCrudRealisasiKinerja" 
                  class="form-control" 
                  placeholder="Contoh: 11 (Opsional)" 
                  oninput="recalculateUserCapaianForm()"
                  style="font-weight:700;color:#c2410c;font-size:13.5px;height:38px;border:1.5px solid #fb923c;background:#ffffff;"
                >
              </div>
              <div>
                <label style="display:block;font-size:12px;font-weight:700;color:#7c2d12;margin-bottom:4px;">
                  Capaian (%)
                </label>
                <input 
                  type="text" 
                  id="userCrudPersenKinerja" 
                  class="form-control" 
                  placeholder="100%" 
                  readonly 
                  style="font-weight:800;color:#b91c1c;background:#ffedd5;border:1.5px solid #fdba74;height:38px;text-align:right;"
                >
              </div>
              <div>
                <label style="display:block;font-size:12px;font-weight:700;color:#7c2d12;margin-bottom:4px;">
                  Predikat Kinerja
                </label>
                <input 
                  type="text" 
                  id="userCrudPredikat" 
                  class="form-control" 
                  placeholder="Sangat Berhasil" 
                  readonly 
                  style="font-weight:800;color:#15803d;background:#ffedd5;border:1.5px solid #fdba74;height:38px;text-align:center;"
                >
              </div>
            </div>
          </div>

          <!-- Section: Capaian Keuangan -->
          <div style="background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:10px;padding:14px 16px;">
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:12px;">
              <span style="color:#1d4ed8;font-weight:800;font-size:14px;">💰</span>
              <strong style="color:#1e40af;font-size:13px;">Capaian Keuangan</strong>
            </div>

            <div style="display:grid;grid-template-columns:1.5fr 1fr;gap:12px;">
              <div>
                <label id="labelUserRealKeu" style="display:block;font-size:12px;font-weight:700;color:#1e3a8a;margin-bottom:4px;">
                  Realisasi Keuangan Rp (TW I)
                </label>
                <input 
                  type="text" 
                  id="userCrudRealisasiKeuangan" 
                  class="form-control" 
                  placeholder="Contoh: Rp 1.100" 
                  oninput="formatRupiahInputThis(this); recalculateUserCapaianForm();"
                  style="font-weight:700;color:#1e40af;font-size:13.5px;height:38px;border:1.5px solid #93c5fd;background:#ffffff;"
                >
              </div>
              <div>
                <label style="display:block;font-size:12px;font-weight:700;color:#1e3a8a;margin-bottom:4px;">
                  Capaian Keuangan (%)
                </label>
                <input 
                  type="text" 
                  id="userCrudPersenKeuangan" 
                  class="form-control" 
                  placeholder="4,40%" 
                  readonly 
                  style="font-weight:800;color:#1d4ed8;background:#dbeafe;border:1.5px solid #93c5fd;height:38px;text-align:right;"
                >
              </div>
            </div>
          </div>

          <!-- Section: Bukti Pendukung -->
          <div style="background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:10px;padding:14px 16px;">
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:12px;">
              <span style="color:#15803d;font-weight:800;font-size:14px;">📎</span>
              <strong style="color:#166534;font-size:13px;">Bukti Pendukung</strong>
            </div>

            <div style="display:flex;flex-direction:column;gap:10px;">
              <div>
                <label style="display:block;font-size:12px;font-weight:700;color:#166534;margin-bottom:4px;">
                  Link Dokumen Bukti (Google Drive / Cloud URL)
                </label>
                <input 
                  type="url" 
                  id="userCrudBuktiLink" 
                  class="form-control" 
                  placeholder="https://drive.google.com/..." 
                  style="font-size:12.5px;height:38px;border:1.5px solid #86efac;background:#ffffff;"
                >
              </div>

              <div>
                <label style="display:block;font-size:12px;font-weight:700;color:#166534;margin-bottom:4px;">
                  Unggah Berkas Bukti Dukung (PDF, JPG, PNG, DOCX)
                </label>
                <input 
                  type="file" 
                  id="userCrudBuktiFile" 
                  class="form-control" 
                  accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip,.rar" 
                  style="font-size:12px;padding:5px 8px;border:1.5px dashed #86efac;background:#ffffff;"
                >
                <div id="userCrudExistingFile" style="display:none;margin-top:6px;font-size:11.5px;color:#15803d;background:#dcfce7;padding:6px 10px;border-radius:6px;"></div>
              </div>

              <div>
                <label style="display:block;font-size:12px;font-weight:700;color:#166534;margin-bottom:4px;">
                  Catatan / Keterangan Bukti
                </label>
                <input 
                  type="text" 
                  id="userCrudBuktiKeterangan" 
                  class="form-control" 
                  placeholder="Contoh: Laporan kegiatan dan dokumentasi foto terlampir" 
                  style="font-size:12.5px;height:38px;border:1.5px solid #86efac;background:#ffffff;"
                >
              </div>
            </div>
          </div>

        </div>

        <div class="modal-footer" style="background:#f8fafc;padding:14px 20px;border-top:1.5px solid #e2e8f0;display:flex;justify-content:flex-end;gap:10px;border-radius:0 0 14px 14px;">
          <button type="button" class="btn btn-outline" onclick="closeModal('modalUserCapaian')">Batal</button>
          <button type="submit" class="btn btn-primary" style="background:#00875a;border-color:#00875a;display:inline-flex;align-items:center;gap:6px;font-weight:700;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
              <polyline points="17 21 17 13 7 13 7 21"></polyline>
              <polyline points="7 3 7 8 15 8"></polyline>
            </svg>
            <span>Simpan Capaian</span>
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- ==================== TOAST NOTIFIKASI ==================== -->
  <div class="toast-container" id="toastContainer"></div>

  <!-- Scripts Server Data & Application Logic -->
  <script>
    window.appUrl = "{{ url('/') }}";
    window.serverDb = @json($allDocsGrouped);
    window.serverCapaianDb = @json($capaianKinerjaList);
    window.serverCapaianYears = @json($capaianYears);
  </script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script src="{{ asset('assets/js/app.js') }}?v={{ time() }}"></script>
  <script>
    // Cegah pemulihan halaman tanpa otentikasi saat tab dipulihkan (Ctrl+Shift+T / Back / Forward)
    window.addEventListener('pageshow', function (event) {
      if (event.persisted) {
        window.location.reload();
      }
    });
  </script>
</body>

</html>