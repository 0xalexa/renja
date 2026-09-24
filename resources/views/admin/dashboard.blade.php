<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Workspace — Manajemen Dokumen & CRUD PEP</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ time() }}">
  <style>
    /* CRITICAL INLINE STYLES FOR CHECKBOX & MODERN SAAS TABLE */
    .col-checkbox-cell {
      position: relative !important;
      text-align: center !important;
      width: 48px !important;
      min-width: 48px !important;
      max-width: 48px !important;
      padding: 11px 8px !important;
      vertical-align: middle !important;
      cursor: pointer !important;
    }
    input[type="checkbox"].gov-checkbox,
    .gov-checkbox {
      appearance: none !important;
      -webkit-appearance: none !important;
      -moz-appearance: none !important;
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      width: 19px !important;
      height: 19px !important;
      min-width: 19px !important;
      min-height: 19px !important;
      border-radius: 5px !important;
      border: 1.5px solid #cbd5e1 !important;
      background-color: #ffffff !important;
      cursor: pointer !important;
      transition: all 0.14s ease-in-out !important;
      box-sizing: border-box !important;
      vertical-align: middle !important;
      user-select: none !important;
      margin: 0 auto !important;
      outline: none !important;
    }
    input[type="checkbox"].gov-checkbox:hover,
    .gov-checkbox:hover {
      border-color: #00875a !important;
    }
    input[type="checkbox"].gov-checkbox:checked,
    input[type="checkbox"].gov-checkbox.checked,
    .gov-checkbox.checked,
    .row-selected .gov-checkbox {
      background-color: #00875a !important;
      border-color: #00875a !important;
    }
    input[type="checkbox"].gov-checkbox:checked::after,
    input[type="checkbox"].gov-checkbox.checked::after,
    .gov-checkbox.checked::after,
    .row-selected .gov-checkbox::after {
      content: '' !important;
      display: block !important;
      width: 5px !important;
      height: 9.5px !important;
      border: solid #ffffff !important;
      border-width: 0 2.2px 2.2px 0 !important;
      transform: rotate(45deg) !important;
      margin-top: -2px !important;
    }
    input[type="checkbox"].gov-checkbox:indeterminate,
    .gov-checkbox.indeterminate {
      background-color: #00875a !important;
      border-color: #00875a !important;
    }
    input[type="checkbox"].gov-checkbox:indeterminate::after,
    .gov-checkbox.indeterminate::after {
      content: '' !important;
      display: block !important;
      width: 9px !important;
      height: 2.2px !important;
      background-color: #ffffff !important;
      border-radius: 1px !important;
    }
    .gov-table tbody tr.row-selected {
      background-color: #f0fdf4 !important;
    }
    .gov-table tbody tr.row-selecting {
      transform: scale(0.995);
      background-color: #f1f5f9 !important;
    }
    /* TABLE CONTAINER DASAR (NATURAL TINGGI PENUH TANPA SCROLL VERTIKAL) */
    .table-responsive {
      border-radius: 12px !important;
      background-color: #ffffff !important;
      border: 1px solid #eef2f6 !important;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03) !important;
      overflow-x: auto !important;
      overflow-y: visible !important;
      max-height: none !important;
      position: relative !important;
    }
    /* HALAMAN MODUL (RENJA, PK, DPA, SURAT) TANPA SCROLL VERTIKAL SEPERTI SEMULA */
    .admin-page .table-responsive {
      overflow-x: auto !important;
      overflow-y: visible !important;
      max-height: none !important;
      height: auto !important;
    }
    .admin-page .gov-table thead th {
      position: static !important;
    }
    /* HANYA TABEL DOKUMEN TERAKHIR DI DASHBOARD YANG DIKASIH STICKY HEADER */
    .hope-card.hope-recent-card .gov-table thead th {
      position: sticky !important;
      top: 0 !important;
      z-index: 15 !important;
      background-color: #ffffff !important;
      box-shadow: 0 1px 0 #edf2f7, inset 0 -1.5px 0 #edf2f7 !important;
    }
    .hope-card.hope-recent-card .gov-table thead th.col-checkbox-cell {
      z-index: 16 !important;
      background-color: #ffffff !important;
    }
    /* MODAL OVERLAY RULES GUARANTEED TO SHOW AND BE ON TOP */
    .modal-overlay {
      display: none;
      position: fixed !important;
      inset: 0 !important;
      background-color: rgba(15, 23, 42, 0.6) !important;
      backdrop-filter: blur(3px) !important;
      z-index: 99999 !important;
      align-items: center !important;
      justify-content: center !important;
      padding: 16px !important;
    }
    .modal-overlay.show,
    .modal-overlay.active {
      display: flex !important;
      visibility: visible !important;
      opacity: 1 !important;
    }
    /* TABLE CAPAIAN KINERJA DENGAN GARIS BORDER LENGKAP & RAPI (DINAMIS MELEBAR SESUAI ISI) */
    .table-capaian-grid {
      width: 100% !important;
      border-collapse: collapse !important;
      table-layout: auto !important;
      font-size: 12.5px !important;
      background-color: #ffffff !important;
      border: 1.5px solid #cbd5e1 !important;
    }
    .table-capaian-grid th,
    .table-capaian-grid td {
      border: 1px solid #cbd5e1 !important;
      padding: 9px 12px !important;
      vertical-align: middle !important;
    }
    .table-capaian-grid td.col-text,
    .table-capaian-grid th.col-text {
      white-space: normal !important;
      word-break: break-word !important;
    }
    .table-capaian-grid td.col-nowrap,
    .table-capaian-grid th.col-nowrap {
      white-space: nowrap !important;
    }
    .table-capaian-grid thead tr.head-top th {
      background-color: #f8fafc !important;
      color: #0f172a !important;
      font-weight: 700 !important;
      text-align: center !important;
      border-bottom: 1.5px solid #cbd5e1 !important;
      font-size: 12px !important;
    }
    .table-capaian-grid thead tr.head-sub th {
      background-color: #f1f5f9 !important;
      color: #1e293b !important;
      font-weight: 700 !important;
      text-align: center !important;
      font-size: 11px !important;
      border-bottom: 1px solid #cbd5e1 !important;
    }
    .table-capaian-grid thead tr.head-detail th {
      background-color: #f8fafc !important;
      color: #334155 !important;
      font-weight: 700 !important;
      text-align: center !important;
      font-size: 11px !important;
      border-bottom: 1.5px solid #cbd5e1 !important;
    }
    .table-capaian-grid tbody tr:hover {
      background-color: #f8fafc !important;
    }
    .table-capaian-grid td.cell-input {
      color: #c2410c !important;
      font-weight: 700 !important;
    }
    .table-capaian-grid td.cell-rumus {
      color: #b91c1c !important;
      font-weight: 800 !important;
    }
    /* PRESISI LURUS DENGAN KALENDER DI SAMPINGNYA & TABEL SCROLL BERSIH */
    @media (min-width: 993px) {
      .hope-content-grid {
        display: grid !important;
        grid-template-columns: 66fr 34fr !important;
        gap: 24px !important;
        align-items: stretch !important;
      }
      .hope-col-left {
        display: flex !important;
        flex-direction: column !important;
        gap: 24px !important;
        min-height: 0 !important;
        height: 100% !important;
      }
      .hope-col-right {
        display: flex !important;
        flex-direction: column !important;
        gap: 24px !important;
      }
      .hope-card.hope-recent-card {
        flex: 1 1 0px !important;
        display: flex !important;
        flex-direction: column !important;
        min-height: 0 !important;
        margin-bottom: 0 !important;
      }
      .hope-card.hope-recent-card .table-responsive {
        flex: 1 1 0px !important;
        min-height: 200px !important;
        max-height: none !important;
        overflow-y: auto !important;
        overflow-x: auto !important;
        scrollbar-width: thin !important;
        scrollbar-color: #cbd5e1 #f8fafc !important;
      }
    }
    /* TOP FLOATING SELECTION BAR (MODERN PILL SAAS DESIGN) */
    .table-selection-bar {
      position: fixed !important;
      top: 24px !important;
      bottom: auto !important;
      left: 50% !important;
      transform: translateX(-50%) translateY(-100px) !important;
      z-index: 3500 !important;
      display: flex !important;
      align-items: center !important;
      gap: 14px !important;
      padding: 7px 12px 7px 18px !important;
      background: rgba(255, 255, 255, 0.98) !important;
      backdrop-filter: blur(12px) !important;
      -webkit-backdrop-filter: blur(12px) !important;
      color: #1e293b !important;
      border-radius: 9999px !important;
      border: 1px solid #e2e8f0 !important;
      box-shadow: 0 16px 36px -4px rgba(15, 23, 42, 0.16), 0 6px 14px -2px rgba(15, 23, 42, 0.08), 0 0 0 1px rgba(0, 0, 0, 0.04) !important;
      transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.22s ease !important;
      opacity: 0 !important;
      pointer-events: none !important;
    }
    .table-selection-bar.show {
      transform: translateX(-50%) translateY(0) !important;
      opacity: 1 !important;
      pointer-events: auto !important;
    }
    .selection-info {
      display: flex !important;
      align-items: center !important;
      gap: 8px !important;
    }
    .selection-dot {
      width: 8px !important;
      height: 8px !important;
      border-radius: 50% !important;
      background-color: #00875a !important;
      box-shadow: 0 0 0 3px rgba(0, 135, 90, 0.2) !important;
      animation: pulseSelectionDot 2s infinite !important;
      display: inline-block !important;
    }
    @keyframes pulseSelectionDot {
      0% { box-shadow: 0 0 0 0 rgba(0, 135, 90, 0.4); }
      70% { box-shadow: 0 0 0 6px rgba(0, 135, 90, 0); }
      100% { box-shadow: 0 0 0 0 rgba(0, 135, 90, 0); }
    }
    .selection-count-text {
      font-size: 13px !important;
      font-weight: 600 !important;
      color: #1e293b !important;
      white-space: nowrap !important;
      font-family: inherit !important;
      letter-spacing: -0.01em !important;
    }
    .selection-divider {
      width: 1px !important;
      height: 20px !important;
      background: #e2e8f0 !important;
      flex-shrink: 0 !important;
    }
    .selection-btn-group {
      display: flex !important;
      align-items: center !important;
      gap: 8px !important;
    }
    .btn-selection-action {
      appearance: none !important;
      -webkit-appearance: none !important;
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      gap: 6px !important;
      padding: 6px 14px !important;
      border-radius: 9999px !important;
      font-size: 12.5px !important;
      font-weight: 600 !important;
      cursor: pointer !important;
      transition: all 0.16s ease !important;
      font-family: inherit !important;
      white-space: nowrap !important;
      line-height: 1.2 !important;
      outline: none !important;
    }
    .btn-selection-action .btn-sel-icon {
      width: 14px !important;
      height: 14px !important;
      stroke-width: 2.2 !important;
      transition: stroke 0.15s ease !important;
    }
    .btn-selection-selectall {
      background: #f8fafc !important;
      color: #334155 !important;
      border: 1.5px solid #cbd5e1 !important;
    }
    .btn-selection-selectall:hover {
      background: #f1f5f9 !important;
      border-color: #94a3b8 !important;
      color: #0f172a !important;
      transform: translateY(-1px) !important;
    }
    .btn-selection-delete {
      background: #fef2f2 !important;
      color: #dc2626 !important;
      border: 1.5px solid #fecaca !important;
    }
    .btn-selection-delete:hover {
      background: #dc2626 !important;
      border-color: #dc2626 !important;
      color: #ffffff !important;
      transform: translateY(-1px) !important;
      box-shadow: 0 4px 12px rgba(220, 38, 38, 0.28) !important;
    }
    .btn-selection-delete:hover .btn-sel-icon {
      stroke: #ffffff !important;
    }
    .btn-selection-close {
      appearance: none !important;
      -webkit-appearance: none !important;
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      width: 28px !important;
      height: 28px !important;
      border-radius: 50% !important;
      border: 1px solid #e2e8f0 !important;
      background: #f8fafc !important;
      color: #64748b !important;
      cursor: pointer !important;
      transition: all 0.18s ease !important;
      padding: 0 !important;
      outline: none !important;
    }
    .btn-selection-close:hover {
      background: #e2e8f0 !important;
      color: #0f172a !important;
      transform: rotate(90deg) scale(1.05) !important;
    }
    /* AGENDA & TASKS TABLE MODERN STYLING */
    .agenda-table-wrapper {
      width: 100% !important;
      overflow-x: auto !important;
      border-radius: 9px !important;
      border: 1px solid #e2e8f0 !important;
      background: #ffffff !important;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02) !important;
    }
    .agenda-table {
      width: 100% !important;
      border-collapse: collapse !important;
      font-size: 12px !important;
      text-align: left !important;
    }
    .agenda-table thead tr {
      background-color: #f8fafc !important;
      border-bottom: 1px solid #e2e8f0 !important;
    }
    .agenda-table thead th {
      color: #475569 !important;
      font-weight: 700 !important;
      font-size: 11px !important;
      text-transform: uppercase !important;
      letter-spacing: 0.04em !important;
      padding: 9px 10px !important;
      white-space: nowrap !important;
    }
    .agenda-table tbody tr.agenda-row {
      border-bottom: 1px solid #f1f5f9 !important;
      transition: background-color 0.15s ease !important;
      background-color: #ffffff !important;
    }
    .agenda-table tbody tr.agenda-row:hover {
      background-color: #f8fafc !important;
    }
    .agenda-table tbody tr.agenda-row:last-child {
      border-bottom: none !important;
    }
    .agenda-table tbody td {
      padding: 9px 10px !important;
      vertical-align: middle !important;
    }

    /* BUKTI PENDUKUNG TABLE CELL & BUTTONS */
    .btn-isi-bukti {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 4px 10px;
      font-size: 11px;
      font-weight: 700;
      color: #0f766e;
      background: #f0fdfa;
      border: 1px dashed #5eead4;
      border-radius: 6px;
      cursor: pointer;
      transition: all 0.15s ease;
      white-space: nowrap;
    }
    .btn-isi-bukti:hover {
      background: #ccfbf1;
      border-color: #0f766e;
      color: #115e59;
      transform: translateY(-1px);
    }
    .bukti-cell-container {
      display: inline-flex;
      flex-direction: column;
      gap: 4px;
      align-items: center;
      justify-content: center;
      max-width: 170px;
    }
    .bukti-badge-group {
      display: flex;
      align-items: center;
      gap: 4px;
      flex-wrap: wrap;
      justify-content: center;
    }
    .bukti-badge-link, .bukti-badge-file {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 3px 8px;
      font-size: 11px;
      font-weight: 700;
      border-radius: 5px;
      text-decoration: none;
      transition: all 0.15s ease;
    }
    .bukti-badge-link {
      background: #eff6ff;
      color: #1d4ed8;
      border: 1px solid #bfdbfe;
    }
    .bukti-badge-link:hover {
      background: #dbeafe;
      border-color: #3b82f6;
    }
    .bukti-badge-file {
      background: #f0fdf4;
      color: #15803d;
      border: 1px solid #bbf7d0;
      max-width: 130px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    .bukti-badge-file:hover {
      background: #dcfce7;
      border-color: #22c55e;
    }
    .bukti-btn-edit {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 22px;
      height: 22px;
      border-radius: 4px;
      border: 1px solid #cbd5e1;
      background: #ffffff;
      color: #64748b;
      cursor: pointer;
      padding: 0;
      transition: all 0.15s ease;
    }
    .bukti-btn-edit:hover {
      background: #f1f5f9;
      color: #0f766e;
      border-color: #0f766e;
    }
    /* HORIZONTAL SCROLLBAR BERSIH & RAPI UNTUK CHART CAPAIAN KINERJA */
    .capaian-chart-scroll-wrap {
      width: 100% !important;
      overflow-x: auto !important;
      overflow-y: hidden !important;
      -webkit-overflow-scrolling: touch !important;
      scrollbar-width: thin;
      scrollbar-color: #94a3b8 #f1f5f9;
      padding-bottom: 8px;
      position: relative;
    }
    .capaian-chart-scroll-wrap::-webkit-scrollbar {
      height: 8px;
    }
    .capaian-chart-scroll-wrap::-webkit-scrollbar-track {
      background: #f1f5f9;
      border-radius: 999px;
    }
    .capaian-chart-scroll-wrap::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 999px;
      transition: background 0.15s ease;
    }
    .capaian-chart-scroll-wrap::-webkit-scrollbar-thumb:hover {
      background: #94a3b8;
    }
    .capaian-chart-inner {
      position: relative;
      height: 380px;
      min-height: 380px;
    }
    .chart-scroll-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 28px;
      height: 28px;
      border-radius: 8px;
      border: 1px solid #cbd5e1;
      background: #ffffff;
      color: #475569;
      cursor: pointer;
      transition: all 0.15s ease;
      font-size: 12px;
      padding: 0;
      box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    }
    .chart-scroll-btn:hover {
      background: #f1f5f9;
      color: #0f172a;
      border-color: #94a3b8;
    }
    .chart-scroll-btn:active {
      transform: scale(0.95);
    }
    .chart-mode-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 13px;
      border-radius: 8px;
      font-size: 12px;
      font-weight: 700;
      border: 1px solid #cbd5e1;
      background: #ffffff;
      color: #475569;
      cursor: pointer;
      transition: all 0.15s ease;
    }
    .chart-mode-pill:hover {
      background: #f8fafc;
      color: #0f172a;
      border-color: #94a3b8;
    }
    .chart-mode-pill.active {
      background: #2563eb;
      color: #ffffff;
      border-color: #2563eb;
      box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
    }

    /* SEGMENTED TAB BUTTONS FOR TRIWULAN (Modern SaaS Pill Group) */
    .tw-tab-group {
      display: inline-flex;
      align-items: center;
      background: #f1f5f9;
      padding: 3px;
      border-radius: 9px;
      border: 1px solid #e2e8f0;
      gap: 3px;
    }

    .tw-tab-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      padding: 6px 13px;
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: 12px;
      font-weight: 600;
      border-radius: 7px;
      border: 1px solid transparent;
      background: transparent;
      color: #64748b;
      cursor: pointer;
      transition: all 0.16s ease;
      line-height: 1.4;
      outline: none;
      white-space: nowrap;
    }

    .tw-tab-btn:hover {
      color: #0f172a;
      background: rgba(255, 255, 255, 0.75);
    }

    .tw-tab-btn.active {
      background: #ffffff !important;
      color: #2563eb !important;
      font-weight: 700 !important;
      border-color: #e2e8f0 !important;
      box-shadow: 0 1px 3px rgba(15, 23, 42, 0.1) !important;
    }

    /* 4-TRIWULAN MONITORING CARDS GRID */
    .tw-cards-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 14px;
    }
    @media (max-width: 1024px) {
      .tw-cards-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }
    @media (max-width: 600px) {
      .tw-cards-grid {
        grid-template-columns: 1fr;
      }
    }

    .tw-card {
      transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
      border: 1.5px solid #e2e8f0;
      border-radius: 12px;
      background: #ffffff;
      position: relative;
      cursor: pointer;
    }
    .tw-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px -4px rgba(15, 23, 42, 0.08);
      border-color: #94a3b8;
    }
    .tw-card.active {
      border-color: #2563eb !important;
      background: #f0f7ff !important;
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.18) !important;
    }

    .btn-tw-action {
      transition: all 0.15s ease;
    }
    .btn-tw-action:hover {
      filter: brightness(0.96);
      transform: translateY(-1px);
    }
  </style>
  <script>
    window.appUrl = "{{ url('/') }}";
    window.serverAdminDb = @json($allDocsGrouped);
    window.googleDriveDefaultFolder = "{{ $googleDriveFolder }}";
    window.serverCapaianDb = @json($capaianKinerjaList);
    window.serverCapaianYears = @json($capaianYears);
    window.serverCapaianPerTahun = @json($capaianPerTahun);
    window.serverPeningkatanTahun = @json($peningkatanTahun);
    window.serverTwSummary = @json($twSummary);
  </script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>
  <script src="{{ asset('assets/js/admin.js') }}?v={{ time() }}" defer></script>
</head>
<body>

<div class="admin-layout">

  <!-- ==================== SIDEBAR KIRI (HOPE UI STYLE) ==================== -->
  <aside class="admin-sidebar" id="adminSidebar">

    <!-- Brand Header di atas Sidebar (Hope UI Style) -->
    <div class="sidebar-brand-header" onclick="toggleAdminSidebar()" style="cursor:pointer;" title="Klik untuk Tutup / Buka Menu Navigasi">
      <div class="sidebar-brand-left">
        <div class="sidebar-brand-icon" title="Tutup / Buka Sidebar">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
            <path d="M2 17l10 5 10-5"></path>
            <path d="M2 12l10 5 10-5"></path>
          </svg>
        </div>
        <div class="sidebar-brand-text">
          <span class="brand-title-text">SIM-PEP</span>
          <span class="brand-sub-badge">DISDIK</span>
        </div>
      </div>
    </div>

    <!-- Navigation Menu (Sesuai Navbar dengan Icon) -->
    <nav class="admin-nav">
      
      <div class="admin-nav-title">Menu Utama</div>
      <a href="#" class="admin-nav-item active" data-page="dashboard" data-tooltip="Dashboard Utama" title="Dashboard Utama" onclick="navigateAdmin('dashboard'); return false;">
        <div class="nav-item-left">
          <svg class="nav-icon" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
          <span class="nav-text">Dashboard</span>
        </div>
        <span class="nav-arrow" style="font-size:11px;opacity:0.7;">&rarr;</span>
      </a>

      <a href="#" class="admin-nav-item" data-page="capaian-kinerja" data-tooltip="Capaian Kinerja (e-SAKIP)" title="Capaian Kinerja (e-SAKIP)" onclick="navigateAdmin('capaian-kinerja'); return false;" style="font-weight:700;">
        <div class="nav-item-left">
          <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20V10"></path><path d="M18 20V4"></path><path d="M6 20v-4"></path></svg>
          <span class="nav-text">Capaian Kinerja</span>
        </div>
        <span class="badge badge-green" id="badge-count-capaian" style="font-weight:800;">{{ $countCapaian }}</span>
      </a>

      <div class="admin-nav-title">Rencana Kerja</div>
      <a href="#" class="admin-nav-item" data-page="renja-murni" data-tooltip="Renja Murni" title="Renja Murni" onclick="navigateAdmin('renja-murni'); return false;">
        <div class="nav-item-left">
          <svg class="nav-icon" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
          <span class="nav-text">Renja Murni</span>
        </div>
        <span class="badge badge-gray" id="badge-count-renja-murni">{{ $countRenjaMurni }}</span>
      </a>
      <a href="#" class="admin-nav-item" data-page="renja-perubahan" data-tooltip="Renja Perubahan" title="Renja Perubahan" onclick="navigateAdmin('renja-perubahan'); return false;">
        <div class="nav-item-left">
          <svg class="nav-icon" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
          <span class="nav-text">Renja Perubahan</span>
        </div>
        <span class="badge badge-gray" id="badge-count-renja-perubahan">{{ $countRenjaPerubahan }}</span>
      </a>

      <div class="admin-nav-title">Perjanjian Kinerja</div>
      <a href="#" class="admin-nav-item" data-page="pk-murni" data-tooltip="PK Murni" title="PK Murni" onclick="navigateAdmin('pk-murni'); return false;">
        <div class="nav-item-left">
          <svg class="nav-icon" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><polyline points="9 12 11 14 15 10"></polyline></svg>
          <span class="nav-text">PK Murni</span>
        </div>
        <span class="badge badge-gray" id="badge-count-pk-murni">{{ $countPkMurni }}</span>
      </a>
      <a href="#" class="admin-nav-item" data-page="pk-perubahan" data-tooltip="PK Perubahan" title="PK Perubahan" onclick="navigateAdmin('pk-perubahan'); return false;">
        <div class="nav-item-left">
          <svg class="nav-icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
          <span class="nav-text">PK Perubahan</span>
        </div>
        <span class="badge badge-gray" id="badge-count-pk-perubahan">{{ $countPkPerubahan }}</span>
      </a>

      <div class="admin-nav-title">Pelaksanaan Anggaran</div>
      <a href="#" class="admin-nav-item" data-page="dpa-murni" data-tooltip="DPA Murni" title="DPA Murni" onclick="navigateAdmin('dpa-murni'); return false;">
        <div class="nav-item-left">
          <svg class="nav-icon" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
          <span class="nav-text">DPA Murni</span>
        </div>
        <span class="badge badge-gray" id="badge-count-dpa-murni">{{ $countDpaMurni }}</span>
      </a>
      <a href="#" class="admin-nav-item" data-page="dpa-perubahan" data-tooltip="DPA Perubahan" title="DPA Perubahan" onclick="navigateAdmin('dpa-perubahan'); return false;">
        <div class="nav-item-left">
          <svg class="nav-icon" viewBox="0 0 24 24"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
          <span class="nav-text">DPA Perubahan</span>
        </div>
        <span class="badge badge-gray" id="badge-count-dpa-perubahan">{{ $countDpaPerubahan }}</span>
      </a>

      <div class="admin-nav-title">Arsip Persuratan</div>
      <a href="#" class="admin-nav-item" data-page="surat-masuk" data-tooltip="Surat Masuk" title="Surat Masuk" onclick="navigateAdmin('surat-masuk'); return false;">
        <div class="nav-item-left">
          <svg class="nav-icon" viewBox="0 0 24 24"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"></polyline><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path></svg>
          <span class="nav-text">Surat Masuk</span>
        </div>
        <span class="badge badge-gray" id="badge-count-surat-masuk">{{ $countSuratMasuk }}</span>
      </a>
      <a href="#" class="admin-nav-item" data-page="surat-keluar" data-tooltip="Surat Keluar" title="Surat Keluar" onclick="navigateAdmin('surat-keluar'); return false;">
        <div class="nav-item-left">
          <svg class="nav-icon" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
          <span class="nav-text">Surat Keluar</span>
        </div>
        <span class="badge badge-gray" id="badge-count-surat-keluar">{{ $countSuratKeluar }}</span>
      </a>

      <div class="admin-nav-title">Pelaporan</div>
      <a href="#" class="admin-nav-item" data-page="laporan" data-tooltip="Laporan Simdapangda" title="Laporan Simdapangda" onclick="navigateAdmin('laporan'); return false;">
        <div class="nav-item-left">
          <svg class="nav-icon" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
          <span class="nav-text">Laporan Simdapangda</span>
        </div>
        <span class="badge badge-gray">Rekap</span>
      </a>

    </nav>

    <!-- Sidebar Footer -->
    <div class="admin-sidebar-footer">
      <div class="sidebar-footer-info" style="font-size:11px;color:#94a3b8;line-height:1.4;">
        <div style="font-weight:700;color:#64748b;">SIM-PEP DISDIK</div>
        <div>T.A. 2026 &bull; Subbag PEP</div>
      </div>
    </div>

  </aside>

  <!-- ==================== AREA UTAMA ADMIN ==================== -->
  <div class="admin-main">    <!-- ==================== TOPBAR PUTIH BERSIH (HOPE UI STYLE) ==================== -->
    <header class="hope-topbar">
      <div class="topbar-left">
        <button class="topbar-mobile-btn" id="btnAdminSidebarToggle" onclick="toggleAdminSidebar();" aria-label="Toggle Menu" title="Tutup / Buka Sidebar">
          <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>

        <!-- Search Bar Rounded (Hope UI Style) -->
        <div class="appbar-search-container">
          <div class="appbar-search-box">
            <svg class="search-icon" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2.2" fill="none" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input 
              type="text" 
              id="appbarSearchInput" 
              class="appbar-search-input" 
              placeholder="Cari dokumen, renja, surat... (Ctrl+K)" 
              autocomplete="off"
              oninput="handleAppbarSearch(this.value)"
              onfocus="handleAppbarSearchFocus()"
            >
            <kbd class="search-kbd">Ctrl K</kbd>
            <button type="button" class="search-clear-btn" id="appbarSearchClear" onclick="clearAppbarSearch()" style="display:none;" title="Hapus pencarian">&times;</button>
          </div>

          <!-- Dropdown Popover Hasil Pencarian -->
          <div class="appbar-search-results" id="appbarSearchResults"></div>
        </div>
      </div>

      <div class="topbar-right">

        <!-- Tombol Notifikasi & Dropdown -->
        <div class="appbar-notif-container">
          <button class="appbar-icon-btn" id="btnNotifToggle" onclick="toggleAppbarNotif();" aria-label="Notifikasi" title="Notifikasi Sistem & Dokumen">
            <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
              <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
            <span class="notif-badge" id="notifBadge">1</span>
          </button>

          <!-- Dropdown Notifikasi -->
          <div class="appbar-notif-dropdown" id="appbarNotifDropdown">
            <div class="notif-dropdown-header">
              <div class="notif-dropdown-title">
                <span>Notifikasi & Aktivitas</span>
                <span class="notif-count-pill" id="notifCountPill">1 baru</span>
              </div>
              <button class="notif-mark-read" onclick="markAllNotifsRead();">Tandai Dibaca</button>
            </div>
            <div class="notif-dropdown-list">
              <div class="notif-dropdown-item unread">
                <div class="notif-item-icon icon-renja">
                  <svg viewBox="0 0 24 24" width="15" height="15" stroke="currentColor" stroke-width="2" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                </div>
                <div class="notif-item-content">
                  <div class="notif-item-title">Sistem SIM-PEP Siap Digunakan</div>
                  <div class="notif-item-meta">Tahun Anggaran 2026 Aktif &bull; Subbag PEP</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="appbar-divider"></div>

        <!-- Status Sinkronisasi Google Drive Desktop -->
        <div class="topbar-drive-status" title="Google Drive for Desktop: Terhubung ke G:\My Drive\SIM-PEP">
          <div class="topbar-drive-icon">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="#16a34a"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM19 18H6c-2.21 0-4-1.79-4-4 0-2.05 1.53-3.76 3.56-3.97l1.07-.11.5-.95C8.08 7.14 9.94 6 12 6c2.62 0 4.88 1.86 5.39 4.43l.3 1.5 1.53.11c1.56.1 2.78 1.41 2.78 2.96 0 1.65-1.35 3-3 3z"/></svg>
            <span class="topbar-pulse-dot"></span>
          </div>
          <div class="topbar-drive-text">
            <span class="drive-title">Google Drive Desktop</span>
            <span class="drive-sub"><span style="color:#16a34a;font-weight:700;">● Terhubung</span> &bull; D:\My Drive\SIM-PEP</span>
          </div>
        </div>

        <div class="appbar-divider"></div>

        <!-- Waktu & Tanggal Operasional Sistem Real-time -->
        <div class="topbar-clock-widget" title="Waktu Operasional Sistem SIM-PEP">
          <svg viewBox="0 0 24 24" width="15" height="15" stroke="#475569" stroke-width="2" fill="none"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
          <div class="topbar-clock-text">
            <span id="appbarLiveDate" class="clock-date">-- --- ----</span>
            <span id="appbarLiveTime" class="clock-time">--:--:-- WIB</span>
          </div>
        </div>

        <div class="appbar-divider"></div>

        <!-- Akun Administrator Ringkas -->
        <div class="topbar-admin-chip" title="Akun Aktif: {{ Auth::user()->name ?? 'Administrator Subbag' }}">
          <div class="admin-chip-avatar">
            <svg viewBox="0 0 24 24" width="16" height="16" stroke="#ffffff" stroke-width="2.2" fill="none"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
          </div>
          <div class="admin-chip-info">
            <span class="admin-chip-name">{{ Auth::user()->name ?? 'Administrator Subbag ..' }}</span>
            <span class="admin-chip-role">Administrator</span>
          </div>
        </div>

        <!-- Tombol Keluar dengan Form POST -->
        <form action="{{ route('logout') }}" method="POST" style="display:inline;margin:0;">
          @csrf
          <button type="submit" class="btn-appbar-logout" style="border:none;cursor:pointer;font-family:inherit;" title="Keluar dari sesi admin">
            <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            <span>Keluar</span>
          </button>
        </form>
      </div>
    </header>

    <!-- Konten Halaman Admin -->
    <div class="admin-content">

      <!-- ==================== 1. DASHBOARD UTAMA (HOPE UI DASHBOARD STYLE) ==================== -->
      <div class="admin-page active" id="admin-page-dashboard">
        
        <div class="hope-dashboard-wrap">

          <!-- ==================== HERO WELCOME BANNER (HOPE UI CURVED BLUE BANNER) ==================== -->
          <div class="hope-hero-banner">
            <div class="hero-banner-content">
              <div class="hero-badge-tag">
                <span class="hero-badge-dot"></span>
                <span>Sistem Informasi Manajemen Dokumen PEP &bull; T.A. 2026</span>
              </div>
              <h1 class="hero-banner-title">Selamat Datang di SIM-PEP!</h1>
              <p class="hero-banner-desc">Pusat Terpadu Perencanaan, Evaluasi, dan Pelaporan Dinas Pendidikan. Kelola Renja, PK, DPA, dan Persuratan secara transparan dan terintegrasi.</p>
            </div>
            <div class="hero-banner-actions">
              <button class="btn-hero-action" onclick="openCrudModal('create', 'renja-murni')" title="Tambah Berkas Dokumen">
                <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2.2" fill="none"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Tambah Dokumen</span>
              </button>
              <button class="btn-hero-action outline" onclick="showAdminToast('Agenda Terdekat: Evaluasi Capaian Renja Triwulan III', 'info')">
                <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2.2" fill="none"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                <span>Pengumuman</span>
              </button>
            </div>
            <!-- Decorative Wave Curve SVG -->
            <div class="hero-wave-shape">
              <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M0,0 C150,90 350,-40 500,60 C650,160 900,10 1200,40 L1200,120 L0,120 Z" fill="#f4f6fb" opacity="1"></path>
              </svg>
            </div>
          </div>

          <!-- ==================== 5 FLOATING METRIC CARDS (OVERLAPPING BANNER) ==================== -->
          <div class="hope-metric-grid" style="grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));">
            
            <!-- Card 1: Renja Murni -->
            <div class="hope-metric-card" onclick="navigateAdmin('renja-murni')" title="Buka Modul Renja Murni">
              <div class="metric-circle-indicator blue">
                <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2.5" fill="none"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg>
              </div>
              <div class="metric-body">
                <div class="metric-label">Renja Murni</div>
                <div class="metric-value"><span id="metricRenjaVal">{{ $countRenjaMurni }}</span> <span class="metric-unit">Berkas</span></div>
                <div class="metric-sub">Pagu Sesuai Dokumen</div>
              </div>
            </div>

            <!-- Card 2: PK Kinerja -->
            <div class="hope-metric-card" onclick="navigateAdmin('pk-murni')" title="Buka Modul Perjanjian Kinerja">
              <div class="metric-circle-indicator teal">
                <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2.5" fill="none"><polyline points="20 6 9 17 4 12"></polyline></svg>
              </div>
              <div class="metric-body">
                <div class="metric-label">PK Kinerja</div>
                <div class="metric-value"><span id="metricPkVal">{{ $countPkMurni + $countPkPerubahan }}</span> <span class="metric-unit">Dokumen</span></div>
                <div class="metric-sub">Sesuai Indikator</div>
              </div>
            </div>

            <!-- Card 3: DPA Belanja -->
            <div class="hope-metric-card" onclick="navigateAdmin('dpa-murni')" title="Buka Modul Pelaksanaan Anggaran">
              <div class="metric-circle-indicator amber">
                <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2.5" fill="none"><line x1="12" y1="20" x2="12" y2="10"></line><line x1="18" y1="20" x2="18" y2="4"></line><line x1="6" y1="20" x2="6" y2="16"></line></svg>
              </div>
              <div class="metric-body">
                <div class="metric-label">DPA Belanja</div>
                <div class="metric-value"><span id="metricDpaVal">{{ $countDpaMurni + $countDpaPerubahan }}</span> <span class="metric-unit">Berkas</span></div>
                <div class="metric-sub">Pagu Sesuai DPA</div>
              </div>
            </div>


            <!-- Card 5: Arsip Surat -->
            <div class="hope-metric-card" onclick="navigateAdmin('surat-masuk')" title="Buka Agenda Persuratan">
              <div class="metric-circle-indicator green">
                <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2.5" fill="none"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"></polyline><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path></svg>
              </div>
              <div class="metric-body">
                <div class="metric-label">Arsip Surat</div>
                <div class="metric-value"><span id="metricSuratVal">{{ $countSuratMasuk + $countSuratKeluar }}</span> <span class="metric-unit">Agenda</span></div>
                <div class="metric-sub">{{ $countSuratMasuk }} Masuk &bull; {{ $countSuratKeluar }} Keluar</div>
              </div>
            </div>

          </div>

          <!-- ==================== TWO-COLUMN MAIN CONTENT GRID (HOPE UI STYLE) ==================== -->
          <div class="hope-content-grid">
            
            <!-- ==================== KOLOM KIRI (WIDE ~66%) ==================== -->
            <div class="hope-col-left">

              <!-- ==================== PUSAT INFORMASI & MONITORING 4 TRIWULAN (e-SAKIP) ==================== -->
              <div class="hope-card" id="cardDiagramCapaianKinerja" style="border:1px solid #e2e8f0;box-shadow:0 4px 16px rgba(15,23,42,0.04);border-radius:14px;overflow:hidden;">
                
                <!-- 1. Header Utama Card -->
                <div class="hope-card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;padding:16px 20px;border-bottom:1px solid #edf2f7;background:#ffffff;">
                  <div>
                    <div style="display:flex;align-items:center;gap:10px;">
                      <span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;background:#eef2ff;color:#4f46e5;">
                        <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2.5" fill="none"><path d="M12 20V10"></path><path d="M18 20V4"></path><path d="M6 20v-4"></path></svg>
                      </span>
                      <div>
                        <div style="display:flex;align-items:center;gap:8px;">
                          <h3 style="margin:0;font-size:16px;font-weight:800;color:#0f172a;letter-spacing:-0.3px;">
                            Monitoring Capaian Kinerja per Triwulan (T.A. {{ $latestYear }})
                          </h3>
                          <span class="badge {{ $overallBadgeClass }}" style="font-weight:700;font-size:11px;">
                            {{ $overallPredikat }}
                          </span>
                        </div>
                        <div class="hope-card-subtitle" style="margin-top:3px;font-size:12px;color:#64748b;">
                          Pantauan realisasi fisik &amp; penyerapan pagu anggaran 4 Triwulan (TW I s.d TW IV)
                        </div>
                      </div>
                    </div>
                  </div>

                  <div style="display:flex;align-items:center;gap:8px;">
                    <button type="button" class="btn btn-outline btn-sm" onclick="navigateAdmin('capaian-kinerja')" title="Buka modul Capaian Kinerja e-SAKIP" style="display:inline-flex;align-items:center;gap:6px;font-weight:700;font-size:12px;border-radius:8px;padding:6px 14px;cursor:pointer;border:1.5px solid #4f46e5;color:#4f46e5;background:#ffffff;">
                      <span>Buka Modul Capaian Kinerja</span>
                      <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </button>
                  </div>
                </div>

                <!-- 2. KARTU MONITORING 4 TRIWULAN (Sederhana, Bersih & Esensial) -->
                <div style="padding:16px 20px;background:#f8fafc;border-bottom:1px solid #edf2f7;">
                  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                    <span style="font-size:12px;font-weight:700;color:#475569;letter-spacing:0.3px;">
                      Ringkasan Capaian Triwulan
                    </span>
                    <span style="font-size:11.5px;color:#64748b;font-weight:600;">
                      Kelengkapan: <strong style="color:#0f172a;" id="twHeaderKelengkapanText">{{ $filledTwCount }} dari 4 Triwulan Terisi</strong>
                    </span>
                  </div>

                  <div class="tw-cards-grid">
                    @foreach($twSummary as $k => $tw)
                      <div class="tw-card" data-tw="{{ $k }}" id="twCard_{{ $k }}" onclick="selectDashboardTriwulan('{{ $k }}')" style="padding:12px 14px;display:flex;flex-direction:column;justify-content:space-between;border-radius:10px;">
                        <div>
                          <!-- Baris Status & Triwulan -->
                          <div style="display:flex;align-items:center;justify-content:space-between;gap:6px;margin-bottom:3px;">
                            <span style="font-size:13.5px;font-weight:800;color:#0f172a;">{{ $tw['title'] }}</span>
                            <span class="badge {{ $tw['filled'] ? $tw['badgeClass'] : 'badge-gray' }}" id="twBadge_{{ $k }}" style="font-size:9.5px;padding:1.5px 6.5px;font-weight:700;">{{ $tw['filled'] ? $tw['predikat'] : 'Belum Diisi' }}</span>
                          </div>

                          <div id="twMonths_{{ $k }}" style="font-size:11px;color:#64748b;margin-bottom:8px;">
                            {{ $tw['months'] }}
                          </div>

                          <!-- Angka Capaian Persentase -->
                          <div style="display:flex;align-items:baseline;gap:6px;margin-bottom:6px;">
                            <span id="twPercent_{{ $k }}" style="font-size:24px;font-weight:800;color:{{ $tw['filled'] ? $tw['color'] : '#94a3b8' }};letter-spacing:-0.5px;">{{ $tw['filled'] ? $tw['avgCapaian'] . '%' : '0%' }}</span>
                            <span id="twPercentLabel_{{ $k }}" style="font-size:11px;font-weight:600;color:{{ $tw['filled'] ? '#059669' : '#94a3b8' }};">{{ $tw['filled'] ? 'Fisik' : 'Kosong' }}</span>
                          </div>

                          <!-- 1 Baris Ringkasan Esensial -->
                          <div id="twStats_{{ $k }}" style="font-size:11px;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            @if($tw['filled'])
                              <span style="color:#334155;font-weight:600;"><strong id="twCountVal_{{ $k }}">{{ $tw['count'] }}</strong> Indikator</span>
                              <span style="color:#cbd5e1;margin:0 4px;">•</span>
                              <span id="twKeuanganVal_{{ $k }}" style="color:#2563eb;font-weight:600;">Keuangan: {{ $tw['avgKeuangan'] }}%</span>
                            @else
                              <span id="twKeuanganVal_{{ $k }}" style="color:#94a3b8;">Belum ada laporan</span>
                            @endif
                          </div>
                        </div>

                        <!-- Action Link Minimalis di Bawah Kartu -->
                        <div id="twActionWrap_{{ $k }}" style="margin-top:10px;padding-top:8px;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
                          @if($tw['filled'])
                            <span style="font-size:11px;font-weight:700;color:#4f46e5;display:inline-flex;align-items:center;gap:4px;">
                              <span>Lihat Rincian</span>
                              <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                            </span>
                          @else
                            <span style="font-size:11px;font-weight:700;color:#2563eb;display:inline-flex;align-items:center;gap:4px;">
                              <span>+ Input Data</span>
                              <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
                            </span>
                          @endif
                        </div>
                      </div>
                    @endforeach
                  </div>
                </div>

                <!-- 3. Sub-Header: Mode Tampilan Grafik & Filter Tahun (Tahun Berapa & Bagaimana per Triwulannya) -->
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;padding:12px 20px;border-bottom:1px solid #edf2f7;background:#ffffff;">
                  
                  <!-- Switcher Mode Grafik & Filter Tahun -->
                  <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <!-- Filter Tahun (Dropdown Utama) -->
                    <div style="display:flex;align-items:center;gap:6px;background:#f8fafc;padding:5px 12px;border-radius:8px;border:1.5px solid #cbd5e1;box-shadow:0 1px 2px rgba(0,0,0,0.03);">
                      <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="#2563eb" stroke-width="2.2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="18" y2="10"></line></svg>
                      <span style="font-size:12px;font-weight:700;color:#334155;">Tahun:</span>
                      <select id="filterChartCapaianYear" onchange="filterCapaianDashboardYear(this.value)" style="border:none;background:transparent;font-size:13px;font-weight:800;color:#0f172a;cursor:pointer;outline:none;padding-right:4px;">
                        @foreach($capaianYears as $yr)
                          <option value="{{ $yr }}" {{ $yr == $latestYear ? 'selected' : '' }}>Tahun {{ $yr }}</option>
                        @endforeach
                      </select>
                    </div>

                    <button type="button" id="btnModeKuartal" class="chart-mode-pill active" onclick="setCapaianChartMode('kuartal')" title="Lihat perbandingan rata-rata capaian 4 Triwulan untuk tahun terpilih">
                      <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                      <span>Perbandingan 4 Triwulan</span>
                    </button>
                    <button type="button" id="btnModeTren" class="chart-mode-pill" onclick="setCapaianChartMode('tren-tahunan')" title="Bandingkan rata-rata capaian antar-tahun">
                      <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                      <span>Tren Antar-Tahun</span>
                    </button>
                  </div>

                  <!-- Legend Kategori Capaian -->
                  <div class="hope-chart-legend-wrap" style="flex-wrap:wrap;gap:8px;">
                    <div class="chart-legend-pill" style="cursor:default;" title="Realisasi >= 90%">
                      <span style="display:inline-block;width:9px;height:9px;border-radius:50%;background:#10b981;margin-right:4px;"></span>
                      <span style="font-size:11px;font-weight:600;">&ge;90% Sangat Baik</span>
                    </div>
                    <div class="chart-legend-pill" style="cursor:default;" title="Realisasi 80% - 89%">
                      <span style="display:inline-block;width:9px;height:9px;border-radius:50%;background:#3b82f6;margin-right:4px;"></span>
                      <span style="font-size:11px;font-weight:600;">80-89% Baik</span>
                    </div>
                    <div class="chart-legend-pill" style="cursor:default;" title="Realisasi 70% - 79%">
                      <span style="display:inline-block;width:9px;height:9px;border-radius:50%;background:#f59e0b;margin-right:4px;"></span>
                      <span style="font-size:11px;font-weight:600;">70-79% Cukup Baik</span>
                    </div>
                    <div class="chart-legend-pill" style="cursor:default;" title="Realisasi < 70%">
                      <span style="display:inline-block;width:9px;height:9px;border-radius:50%;background:#ef4444;margin-right:4px;"></span>
                      <span style="font-size:11px;font-weight:600;">&lt;70% Perlu Ditingkatkan</span>
                    </div>
                  </div>
                </div>

                <!-- 4. Area Kanvas Grafik Responsif (Zero Dummy Data) -->
                <div class="capaian-chart-scroll-wrap" id="capaianChartScrollWrap" style="padding:16px 20px 8px;">
                  <div class="capaian-chart-inner" id="capaianChartInner" style="position:relative;height:350px;min-height:350px;width:100%;">
                    <canvas id="chartHopeActivity"></canvas>
                  </div>
                </div>

                <!-- Petunjuk Geser Horizontal Jika Baris Grafik Panjang -->
                <div id="capaianChartScrollHint" style="display:none;align-items:center;justify-content:space-between;gap:8px;font-size:11.5px;color:#64748b;padding:8px 20px;background:#f8fafc;border-top:1px solid #edf2f7;">
                  <div style="display:flex;align-items:center;gap:6px;font-weight:600;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    <span id="capaianChartScrollHintText">Geser horizontal untuk melihat seluruh indikator lengkap</span>
                  </div>
                  <div style="display:flex;align-items:center;gap:6px;">
                    <button type="button" class="chart-scroll-btn" onclick="scrollCapaianChart(-260)" title="Geser ke kiri" style="width:24px;height:24px;">&lsaquo;</button>
                    <button type="button" class="chart-scroll-btn" onclick="scrollCapaianChart(260)" title="Geser ke kanan" style="width:24px;height:24px;">&rsaquo;</button>
                  </div>
                </div>

                <!-- 5. TAB & TABEL RINCIAN INDIKATOR PER TRIWULAN (Interaktif & Real-Time Filter) -->
                <div style="padding:16px 20px;border-top:1px solid #edf2f7;background:#ffffff;">
                  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:14px;">
                    <div>
                      <div style="font-size:13.5px;font-weight:800;color:#0f172a;">
                        Rincian Capaian Indikator: <span id="currentTwTitle" style="color:#2563eb;">Semua Triwulan (Gabungan Data)</span>
                      </div>
                      <div style="font-size:11.5px;color:#64748b;margin-top:2px;">
                        Data asli realisasi fisik dan serapan pagu anggaran yang telah dilaporkan ke sistem
                      </div>
                    </div>

                    <!-- Tombol Tab Triwulan -->
                    <div class="tw-tab-group">
                      <button type="button" class="tw-tab-btn active" data-tw="Semua" onclick="selectDashboardTriwulan('Semua')">Semua Triwulan</button>
                      <button type="button" class="tw-tab-btn" data-tw="TW I" onclick="selectDashboardTriwulan('TW I')">TW I</button>
                      <button type="button" class="tw-tab-btn" data-tw="TW II" onclick="selectDashboardTriwulan('TW II')">TW II</button>
                      <button type="button" class="tw-tab-btn" data-tw="TW III" onclick="selectDashboardTriwulan('TW III')">TW III</button>
                      <button type="button" class="tw-tab-btn" data-tw="TW IV" onclick="selectDashboardTriwulan('TW IV')">TW IV</button>
                    </div>
                  </div>

                  <div class="table-responsive hope-indikator-scroll" style="margin:0;border:1px solid #e2e8f0;border-radius:10px;overflow-x:auto;overflow-y:auto;max-height:245px;width:100%;">
                    <table class="gov-table" id="tableIndikatorTriwulan" style="margin:0;width:100%;font-size:12px;table-layout:auto;">
                      <thead>
                        <tr style="background:#f8fafc;">
                          <th style="width:36px;text-align:center;padding:9px 4px;">No</th>
                          <th style="width:68px;text-align:center;padding:9px 4px;">Triwulan</th>
                          <th style="padding:9px 8px;">Sasaran &amp; Indikator Kinerja</th>
                          <th style="width:115px;text-align:center;padding:9px 6px;">Realisasi / Target</th>
                          <th style="width:120px;text-align:left;padding:9px 6px;">% Capaian &amp; Predikat</th>
                          <th style="width:135px;text-align:right;padding:9px 8px;">Keuangan</th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse($capaianKinerjaList as $idx => $item)
                        @php
                          $persenK = (float)($item->capaian_kinerja_persen ?? 0);
                          if ($persenK <= 0 && ($item->realisasi_kinerja ?? 0) > 0 && ($item->target_tahunan ?? 0) > 0) {
                              $persenK = ($item->realisasi_kinerja / $item->target_tahunan) * 100;
                          }
                          $persenK = min(100, max(0, $persenK));

                          if ($persenK >= 90) {
                              $bClass = 'badge-green';
                              $bColor = '#10b981';
                              $predLabel = $item->predikat_kinerja ?: 'Sangat Tinggi';
                          } elseif ($persenK >= 80) {
                              $bClass = 'badge-blue';
                              $bColor = '#3b82f6';
                              $predLabel = $item->predikat_kinerja ?: 'Tinggi';
                          } elseif ($persenK >= 70) {
                              $bClass = 'badge-yellow';
                              $bColor = '#f59e0b';
                              $predLabel = $item->predikat_kinerja ?: 'Sedang';
                          } else {
                              $bClass = 'badge-red';
                              $bColor = '#ef4444';
                              $predLabel = $item->predikat_kinerja ?: 'Rendah';
                          }

                          $pagu = (float)($item->pagu_anggaran ?? 0);
                          $realisasiKeu = (float)($item->realisasi_keuangan ?? 0);
                          $keuPersen = $pagu > 0 ? round(($realisasiKeu / $pagu) * 100, 1) : 0;
                        @endphp
                        <tr class="row-indikator" data-triwulan="{{ trim($item->triwulan ?? '') }}" data-tahun="{{ (int)($item->tahun ?? 2026) }}" style="transition:background 0.15s ease;">
                          <td style="text-align:center;font-weight:700;color:#64748b;padding:8px 4px;">{{ $idx + 1 }}</td>
                          <td style="text-align:center;padding:8px 4px;">
                            <span class="badge {{ trim($item->triwulan ?? '') === 'TW I' ? 'badge-blue' : (trim($item->triwulan ?? '') === 'TW III' ? 'badge-green' : 'badge-gray') }}" style="font-size:10.5px;font-weight:700;padding:2px 7px;">
                              {{ $item->triwulan ?: '-' }}
                            </span>
                          </td>
                          <td style="padding:8px 8px;">
                            <strong style="color:#0f172a;display:block;font-size:12px;line-height:1.35;word-break:break-word;">{{ $item->indikator ?: 'Indikator belum diisi' }}</strong>
                            <div style="font-size:11px;color:#64748b;margin-top:2px;line-height:1.3;word-break:break-word;">{{ $item->sasaran ?: '-' }}</div>
                          </td>
                          <td style="text-align:center;padding:8px 6px;">
                            <div style="font-weight:700;color:#0f172a;font-size:12px;line-height:1.2;">
                              {{ $item->realisasi_kinerja !== null ? rtrim(rtrim(number_format($item->realisasi_kinerja, 2, ',', '.'), '0'), ',') : '-' }}
                              <span style="font-size:10px;color:#64748b;font-weight:500;">{{ $item->satuan }}</span>
                            </div>
                            <div style="font-size:10.5px;color:#64748b;margin-top:2px;white-space:nowrap;">
                              Target: {{ $item->target_tahunan !== null ? rtrim(rtrim(number_format($item->target_tahunan, 2, ',', '.'), '0'), ',') : '-' }}
                            </div>
                          </td>
                          <td style="text-align:left;padding:8px 6px;">
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:4px;margin-bottom:3px;font-size:11px;font-weight:700;">
                              <span>{{ $persenK > 0 ? $persenK . '%' : '0%' }}</span>
                              <span class="badge {{ $bClass }}" style="font-size:9px;padding:2px 5px;white-space:nowrap;">{{ $predLabel }}</span>
                            </div>
                            <div style="height:5px;background:#e2e8f0;border-radius:999px;overflow:hidden;">
                              <div style="width:{{ min(100, max(0, $persenK)) }}%;height:100%;background:{{ $bColor }};border-radius:999px;transition:width 0.4s ease;"></div>
                            </div>
                          </td>
                          <td style="text-align:right;padding:8px 8px;">
                            <div style="font-weight:700;color:#0f172a;font-size:11.5px;white-space:nowrap;">Rp {{ number_format($realisasiKeu, 0, ',', '.') }}</div>
                            <div style="font-size:10px;color:#64748b;margin-top:1px;white-space:nowrap;">Pagu: Rp {{ number_format($pagu, 0, ',', '.') }} ({{ $keuPersen }}%)</div>
                          </td>
                        </tr>
                        @empty
                        <!-- Jika capaianKinerjaList kosong total -->
                        @endforelse

                        <!-- Baris Pesan Kosong Khusus Filter Triwulan yang Belum Diisi -->
                        <tr id="rowIndikatorEmpty" style="display:none;">
                          <td colspan="6" style="text-align:center;padding:36px 20px;background:#f8fafc;">
                            <div style="width:48px;height:48px;border-radius:50%;background:#e0f2fe;display:inline-flex;align-items:center;justify-content:center;color:#0284c7;margin-bottom:10px;">
                              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            </div>
                            <h4 id="emptyIndikatorTitle" style="font-size:14px;font-weight:700;color:#1e293b;margin:0 0 4px;">Belum Ada Data Indikator</h4>
                            <p id="emptyIndikatorSub" style="font-size:12px;color:#64748b;margin:0 0 14px;max-width:440px;margin-left:auto;margin-right:auto;line-height:1.5;">
                              Laporan capaian kinerja belum diinput untuk periode ini. Sistem tidak menampilkan data tiruan agar analisis data Anda tetap akurat.
                            </p>
                            <button type="button" id="btnInputSpecificTw" onclick="navigateAdmin('capaian-kinerja')" class="btn btn-primary btn-sm" style="display:none;align-items:center;gap:6px;font-weight:700;font-size:12px;border-radius:8px;padding:7px 18px;background:#2563eb;color:#ffffff;border:none;margin:0 auto;box-shadow:0 2px 4px rgba(37,99,235,0.2);">
                              <span>+ Input Data Capaian</span>
                            </button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <div style="margin-top:12px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                    <div style="font-size:11.5px;color:#64748b;">
                      Menampilkan seluruh indikator terdata di sistem
                    </div>
                    <a href="#" onclick="navigateAdmin('capaian-kinerja'); return false;" style="font-size:12px;font-weight:700;color:#2563eb;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                      <span>Kelola &amp; Tambah Indikator Baru di Modul Capaian Kinerja &rarr;</span>
                    </a>
                  </div>
                </div>

              </div>

            </div>

            <!-- ==================== KOLOM KANAN (~34%) ==================== -->
            <div class="hope-col-right">

              <!-- 1. Distribusi Dokumen per Modul (Bar Chart Modul) -->
              <div class="hope-card">
                <div class="hope-card-header">
                  <div>
                    <h3 class="hope-card-title">Dokumen per Modul</h3>
                    <div class="hope-card-subtitle">Distribusi berkas per kelompok modul</div>
                  </div>
                  <button class="hope-btn-icon-link" onclick="navigateAdmin('laporan')" title="Lihat Rekap">
                    &rarr;
                  </button>
                </div>
                <div class="hope-chart-canvas-wrap" style="position:relative;height:210px;width:100%;max-width:100%;min-width:0;overflow:hidden;">
                  <canvas id="chartModuleDistribution" style="width:100%!important;max-width:100%!important;"></canvas>
                </div>
              </div>

              <!-- 2. Status Dokumen (Donut Chart) -->
              <div class="hope-card">
                <div class="hope-card-header">
                  <h3 class="hope-card-title">Status Dokumen</h3>
                  <button class="hope-btn-icon-link" onclick="navigateAdmin('laporan')" title="Lihat Rekap">
                    &rarr;
                  </button>
                </div>
                <div class="donut-chart-container">
                  <canvas id="chartDonutStatus"></canvas>
                  <div class="donut-center-text">
                    <div class="donut-center-num" id="donutCenterTotal">{{ $totalDokumen }}</div>
                    <div class="donut-center-label">Dokumen</div>
                  </div>
                </div>
                <div class="donut-legend">
                  <div class="legend-item"><span class="legend-dot" style="background:#10b981;"></span><span id="donutLegendLengkap">Lengkap ({{ $statusLengkap }})</span></div>
                  <div class="legend-item"><span class="legend-dot" style="background:#3b82f6;"></span><span id="donutLegendDiproses">Diproses ({{ $statusDiproses }})</span></div>
                  <div class="legend-item"><span class="legend-dot" style="background:#f59e0b;"></span><span id="donutLegendPerluUpdate">Perlu Update ({{ $statusPerluUpdate }})</span></div>
                  <div class="legend-item"><span class="legend-dot" style="background:#8b5cf6;"></span><span id="donutLegendTerkirim">Terkirim ({{ $statusTerkirim }})</span></div>
                </div>
              </div>

              <!-- 3. Tasks & Agenda PEP -->
              <div class="hope-card hope-agenda-card" id="hopeAgendaCard">
                <div class="hope-card-header" style="align-items:flex-start;gap:8px;">
                  <div style="flex:1;min-width:0;">
                    <h3 class="hope-card-title">Tasks &amp; Agenda PEP</h3>
                  </div>
                  <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
                    <button id="btnNotifPermission" onclick="requestAgendaNotifPermission()" title="Aktifkan notifikasi agenda" style="display:none;border:none;background:transparent;cursor:pointer;padding:4px;border-radius:6px;color:var(--text-muted);">
                      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    </button>
                    <div class="tasks-tabs">
                      <span class="tasks-tab-item active" onclick="switchTaskTab(this, 'today')">Hari ini</span>
                      <span class="tasks-tab-item" onclick="switchTaskTab(this, 'week')">Minggu</span>
                      <span class="tasks-tab-item" onclick="switchTaskTab(this, 'month')">Bulan</span>
                    </div>
                    <button onclick="openAgendaModal()" title="Tambah Agenda" style="display:flex;align-items:center;gap:4px;padding:5px 10px;background:var(--primary);color:#fff;border:none;border-radius:7px;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;">
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                      Tambah
                    </button>
                  </div>
                </div>
                <div id="taskActiveFilterBar" style="display:none;align-items:center;justify-content:space-between;padding:6px 12px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:7px;margin-bottom:10px;font-size:12px;color:#1d4ed8;">
                  <span id="taskActiveFilterText"></span>
                  <button type="button" onclick="clearCalendarFilter()" style="background:transparent;border:none;color:#ef4444;font-weight:700;cursor:pointer;font-size:11px;">Reset Filter</button>
                </div>
                <div class="task-list" id="taskListContainer" style="min-height:185px;">
                  <div class="empty-state-card" style="padding:30px 18px;text-align:center;color:#8a92a6;background:#f8fafc;border-radius:10px;border:1px dashed #cbd5e1;min-height:185px;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                    <svg viewBox="0 0 24 24" width="36" height="36" stroke="currentColor" stroke-width="1.5" fill="none" style="margin:0 auto 10px;opacity:0.55;display:block;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    <div style="font-size:0.875rem;font-weight:700;color:var(--text-dark);">Belum Ada Agenda / Tugas Terjadwal</div>
                    <div style="font-size:0.75rem;margin:6px auto 14px;color:var(--text-muted);line-height:1.55;max-width:310px;">
                      Jadwalkan koordinasi berkala, telaah berkas Renja, batas waktu pelaporan e-SAKIP, dan batas waktu dokumen perangkat daerah.
                    </div>
                    <button type="button" onclick="openAgendaModal()" style="padding:6px 16px;background:var(--primary);color:#fff;border:none;border-radius:7px;font-size:12px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:5px;box-shadow:0 2px 6px rgba(59,130,246,0.25);">
                      + Tambah Agenda
                    </button>
                  </div>
                </div>
              </div>


              <!-- 4. Kalender Kegiatan -->
              <div class="hope-card hope-calendar-card" id="hopeCalendarCard">
                <div class="cal-header">
                  <button type="button" class="cal-nav-btn" onclick="navigateCalendarMonth(-1)" title="Bulan Sebelumnya">&lsaquo;</button>
                  <span id="calMonthYearTitle">{{ date('F Y') }}</span>
                  <button type="button" class="cal-nav-btn" onclick="navigateCalendarMonth(1)" title="Bulan Berikutnya">&rsaquo;</button>
                </div>
                <div class="cal-weekdays">
                  <span class="weekday-sun">S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span>
                </div>
                <div class="cal-days-grid" id="calDaysGrid">
                  <!-- Rendered dynamically by renderAdminCalendar() -->
                </div>
                <div style="margin-top:10px;font-size:11px;color:var(--text-muted);display:flex;align-items:center;justify-content:space-between;padding-top:8px;border-top:1px solid var(--border-subtle);">
                  <span>Hari ini: <strong>{{ date('j M Y') }}</strong></span>
                  <button type="button" class="badge badge-blue" style="cursor:pointer;border:none;outline:none;" onclick="resetCalendarToToday()" title="Kembali ke Hari Ini">Hari Ini</button>
                </div>
              </div>

            </div>

          </div>

          <!-- ==================== TABEL DOKUMEN TERAKHIR YANG DITAMBAHKAN / DIPERBARUI (FULL WIDTH DI BAWAH KALENDER & GRID) ==================== -->
          <div class="hope-recent-wrapper">
            <div class="hope-card hope-recent-card" id="hopeRecentTableCard" style="border:1px solid #eef2f6;box-shadow:0 1px 3px rgba(0,0,0,0.04);border-radius:16px;background:#ffffff;margin:0;">
              <div class="hope-card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;padding:16px 20px;border-bottom:1px solid #edf2f7;background:#ffffff;">
                <div>
                  <div style="display:flex;align-items:center;gap:8px;">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:7px;background:#eff6ff;color:#2563eb;">
                      <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2.2" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    </span>
                    <h3 class="hope-card-title" style="font-size:15px;font-weight:800;color:#0f172a;margin:0;">Dokumen Terakhir yang Ditambahkan / Diperbarui</h3>
                  </div>
                  <div class="hope-card-subtitle" style="font-size:12px;color:#64748b;margin-top:3px;">Rekap dokumen terkini dari seluruh kelompok modul perencanaan perangkat daerah</div>
                </div>
                <button class="btn btn-outline btn-sm" onclick="openCrudModal('create', 'renja-murni')" style="font-weight:700;font-size:12px;display:inline-flex;align-items:center;gap:6px;border-radius:8px;padding:6px 14px;cursor:pointer;">
                  <svg class="btn-icon-svg" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                  Tambah Dokumen Baru
                </button>
              </div>
              <div class="table-responsive" style="margin:0;min-height:auto;">
                <table class="gov-table" style="margin:0;font-size:12.5px;width:100%;">
                  <thead>
                    <tr style="background:#f8fafc;">
                      <th style="width:75px;text-align:center;">Tahun</th>
                      <th>Nama Dokumen / Perihal</th>
                      <th style="width:160px;">Kelompok Modul</th>
                      <th style="width:240px;">Berkas Lampiran PDF</th>
                      <th style="width:105px;text-align:center;">Status</th>
                      <th style="width:220px;text-align:center;">Aksi</th>
                    </tr>
                  </thead>
                  <tbody id="adminRecentTable">
                    @forelse($recentDocs as $doc)
                    <tr data-doc-id="{{ $doc->id }}" data-mod-key="{{ $doc->modul ?? 'renja-murni' }}">
                      <td style="text-align:center;"><span class="badge badge-gray">{{ $doc->tahun_anggaran ?? '2026' }}</span></td>
                      <td>
                        <strong>{{ $doc->judul }}</strong>
                        <div style="font-size:11px;color:var(--text-muted);">{{ $doc->keterangan ?? '-' }}</div>
                      </td>
                      <td><span class="badge badge-blue">{{ strtoupper($doc->modul ?? 'RENJA') }}</span></td>
                      <td>
                        @if($doc->link_drive)
                          <span class="badge badge-yellow" style="font-size:10px;margin-right:4px;">Drive</span>
                        @endif
                        <span style="font-family:monospace;font-size:11px;color:var(--primary);">{{ $doc->file_name ?? 'Berkas' }}</span>
                        <span style="font-size:10px;color:var(--text-muted);">({{ $doc->file_size ?? '-' }})</span>
                      </td>
                      <td style="text-align:center;">
                        @if($doc->status === 'Lengkap')
                          <span class="badge badge-green">LENGKAP</span>
                        @elseif($doc->status === 'Diproses')
                          <span class="badge badge-yellow">DIPROSES</span>
                        @else
                          <span class="badge badge-red">{{ strtoupper($doc->status ?? 'DRAF') }}</span>
                        @endif
                      </td>
                      <td style="text-align:center;">
                        <div class="btn-action-group" style="justify-content:center;">
                          <button class="btn btn-outline btn-sm" onclick="openDetailModal('{{ $doc->modul ?? 'renja-murni' }}', {{ $doc->id }})" title="Lihat Detail"><svg class="btn-icon-svg" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="3" r="3"></circle></svg> Detail</button>
                          <button class="btn btn-warning btn-sm" onclick="openCrudModal('edit', '{{ $doc->modul ?? 'renja-murni' }}', {{ $doc->id }})" title="Edit Data"><svg class="btn-icon-svg" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg> Edit</button>
                          <button class="btn btn-primary btn-sm" onclick="handleDownloadDoc('{{ $doc->modul ?? 'renja-murni' }}', {{ $doc->id }})" title="Unduh Berkas / Buka Drive"><svg class="btn-icon-svg" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg> Unduh</button>
                          <button class="btn btn-danger btn-sm" onclick="openDeleteModal('{{ $doc->modul ?? 'renja-murni' }}', {{ $doc->id }}, '{{ addslashes($doc->judul) }}')" title="Hapus Data"><svg class="btn-icon-svg" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg> Hapus</button>
                        </div>
                      </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="text-align:center;padding:32px;color:var(--text-muted);font-weight:500;">Belum ada dokumen yang diunggah. Silakan klik modul pada menu di samping kiri dan klik tombol <strong>"+ Tambah Dokumen"</strong> untuk mulai menambahkan berkas.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>

        </div>

      </div>

      <!-- ==================== 2. RENJA MURNI ==================== -->
      <div class="admin-page" id="admin-page-renja-murni">
        <div class="page-header">
          <div class="page-header-title">
            <h1>Kelola Data — Renja Murni</h1>
            <p>Pengelolaan dokumen Rencana Kerja penetapan APBD murni tahunan.</p>
          </div>
          <div class="page-header-meta">Rencana Kerja</div>
        </div>

        <div class="table-toolbar">
          <div class="toolbar-group">
            <input type="text" class="form-control search-box" placeholder="Cari dokumen Renja...">
            <select class="form-control filter-year">
              <option value="">Semua Tahun</option>
              <option value="2026">2026</option>
              <option value="2025">2025</option>
              <option value="2024">2024</option>
            </select>
          </div>
          <div class="toolbar-group">
            <button class="btn btn-primary" onclick="openCrudModal('create', 'renja-murni')">
              <svg class="btn-icon-svg" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
              Tambah Dokumen Renja
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="gov-table" id="admin-table-renja-murni">
            <thead>
              <tr>
                <th style="width:48px;text-align:center;" class="col-checkbox-cell">
                  <input type="checkbox" class="gov-checkbox table-master-checkbox" onclick="event.stopPropagation(); toggleSelectAllActiveTable(event)" title="Pilih Semua">
                </th>
                <th style="width:75px;text-align:center;">Tahun</th>
                <th>Nama Dokumen & Program</th>
                <th>Uraian / Keterangan</th>
                <th style="width:220px;">Berkas PDF</th>
                <th style="width:95px;text-align:center;">Status</th>
                <th style="width:190px;text-align:center;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($allRenjaMurni as $item)
              <tr data-doc-id="{{ $item->id }}" data-mod-key="renja-murni">
                <td style="text-align:center;" class="col-checkbox-cell">
                  <input type="checkbox" class="gov-checkbox row-checkbox" data-doc-id="{{ $item->id }}" data-mod-key="renja-murni" onclick="event.stopPropagation(); toggleRowSelection(this.closest('tr'))">
                </td>
                <td style="text-align:center;"><span class="badge badge-gray">{{ $item->tahun_anggaran }}</span></td>
                <td><strong>{{ $item->judul }}</strong></td>
                <td><span style="color:var(--text-body);font-size:12px;">{{ $item->keterangan ?? '-' }}</span></td>
                <td>
                  @if($item->link_drive)
                    <span class="badge badge-yellow" style="font-size:10px;margin-right:4px;">Drive</span>
                  @endif
                  <span style="font-family:monospace;font-size:11px;color:var(--primary);">{{ $item->file_name ?? 'Berkas' }}</span>
                  <span style="font-size:10px;color:var(--text-muted);">({{ $item->file_size ?? '-' }})</span>
                </td>
                <td style="text-align:center;">
                  @if($item->status === 'Lengkap')
                    <span class="badge badge-green">LENGKAP</span>
                  @elseif($item->status === 'Diproses')
                    <span class="badge badge-yellow">DIPROSES</span>
                  @else
                    <span class="badge badge-red">{{ strtoupper($item->status) }}</span>
                  @endif
                </td>
                <td style="text-align:center;">
                  <div class="btn-action-group" style="justify-content:center;">
                    <button class="btn btn-outline btn-sm" onclick="openDetailModal('renja-murni', {{ $item->id }})" title="Lihat Detail"><svg class="btn-icon-svg" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg> Detail</button>
                    <button class="btn btn-warning btn-sm" onclick="openCrudModal('edit', 'renja-murni', {{ $item->id }})" title="Edit Data"><svg class="btn-icon-svg" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg> Edit</button>
                    <button class="btn btn-primary btn-sm" onclick="handleDownloadDoc('renja-murni', {{ $item->id }})" title="Unduh Berkas / Buka Drive"><svg class="btn-icon-svg" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg> Unduh</button>
                    <button class="btn btn-danger btn-sm" onclick="openDeleteModal('renja-murni', {{ $item->id }}, '{{ addslashes($item->judul) }}')" title="Hapus Data"><svg class="btn-icon-svg" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg> Hapus</button>
                  </div>
                </td>
              </tr>
              @empty
              <tr><td colspan="7" style="text-align:center;padding:28px;color:var(--text-muted);">Belum ada data dokumen pada modul ini. Klik tombol "+ Tambah Dokumen" di atas untuk menambah data baru.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <!-- ==================== 3. RENJA PERUBAHAN ==================== -->
      <div class="admin-page" id="admin-page-renja-perubahan">
        <div class="page-header">
          <div class="page-header-title">
            <h1>Kelola Data — Renja Perubahan</h1>
            <p>Pengelolaan dokumen penyesuaian program dan kegiatan perubahan APBD.</p>
          </div>
          <div class="page-header-meta">Rencana Kerja</div>
        </div>

        <div class="table-toolbar">
          <div class="toolbar-group">
            <input type="text" class="form-control search-box" placeholder="Cari Renja Perubahan...">
            <select class="form-control filter-year">
              <option value="">Semua Tahun</option>
              <option value="2026">2026</option>
              <option value="2025">2025</option>
            </select>
          </div>
          <div class="toolbar-group">
            <button class="btn btn-primary" onclick="openCrudModal('create', 'renja-perubahan')">
              <svg class="btn-icon-svg" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
              Tambah Renja Perubahan
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="gov-table" id="admin-table-renja-perubahan">
            <thead>
              <tr>
                <th style="width:48px;text-align:center;">
                  <input type="checkbox" class="gov-checkbox table-master-checkbox" onclick="event.stopPropagation(); toggleSelectAllActiveTable(event)" title="Pilih Semua">
                </th>
                <th style="width:75px;text-align:center;">Tahun</th>
                <th>Nama Dokumen & Program</th>
                <th>Uraian Perubahan</th>
                <th style="width:220px;">Berkas PDF</th>
                <th style="width:95px;text-align:center;">Status</th>
                <th style="width:190px;text-align:center;">Aksi</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <!-- ==================== 4. PK MURNI ==================== -->
      <div class="admin-page" id="admin-page-pk-murni">
        <div class="page-header">
          <div class="page-header-title">
            <h1>Kelola Data — PK Murni</h1>
            <p>Pengelolaan berkas penetapan Perjanjian Kinerja awal tahun pejabat dinas.</p>
          </div>
          <div class="page-header-meta">Perjanjian Kinerja</div>
        </div>

        <div class="table-toolbar">
          <div class="toolbar-group">
            <input type="text" class="form-control search-box" placeholder="Cari berkas PK...">
            <select class="form-control filter-year">
              <option value="">Semua Tahun</option>
              <option value="2026">2026</option>
              <option value="2025">2025</option>
            </select>
          </div>
          <div class="toolbar-group">
            <button class="btn btn-primary" onclick="openCrudModal('create', 'pk-murni')">
              <svg class="btn-icon-svg" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
              Tambah Dokumen PK
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="gov-table" id="admin-table-pk-murni">
            <thead>
              <tr>
                <th style="width:48px;text-align:center;">
                  <input type="checkbox" class="gov-checkbox table-master-checkbox" onclick="event.stopPropagation(); toggleSelectAllActiveTable(event)" title="Pilih Semua">
                </th>
                <th style="width:75px;text-align:center;">Tahun</th>
                <th>Dokumen Perjanjian Kinerja</th>
                <th>Sasaran Strategis & Uraian</th>
                <th style="width:220px;">Berkas PDF</th>
                <th style="width:95px;text-align:center;">Status</th>
                <th style="width:190px;text-align:center;">Aksi</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <!-- ==================== 5. PK PERUBAHAN ==================== -->
      <div class="admin-page" id="admin-page-pk-perubahan">
        <div class="page-header">
          <div class="page-header-title">
            <h1>Kelola Data — PK Perubahan</h1>
            <p>Pengelolaan adendum target perjanjian kinerja pejabat struktural dinas.</p>
          </div>
          <div class="page-header-meta">Perjanjian Kinerja</div>
        </div>

        <div class="table-toolbar">
          <div class="toolbar-group">
            <input type="text" class="form-control search-box" placeholder="Cari berkas PK Perubahan...">
            <select class="form-control filter-year">
              <option value="">Semua Tahun</option>
              <option value="2026">2026</option>
              <option value="2025">2025</option>
            </select>
          </div>
          <div class="toolbar-group">
            <button class="btn btn-primary" onclick="openCrudModal('create', 'pk-perubahan')">
              <svg class="btn-icon-svg" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
              Tambah PK Perubahan
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="gov-table" id="admin-table-pk-perubahan">
            <thead>
              <tr>
                <th style="width:48px;text-align:center;">
                  <input type="checkbox" class="gov-checkbox table-master-checkbox" onclick="event.stopPropagation(); toggleSelectAllActiveTable(event)" title="Pilih Semua">
                </th>
                <th style="width:75px;text-align:center;">Tahun</th>
                <th>Dokumen Perjanjian Kinerja</th>
                <th>Sasaran Perubahan & Uraian</th>
                <th style="width:220px;">Berkas PDF</th>
                <th style="width:95px;text-align:center;">Status</th>
                <th style="width:190px;text-align:center;">Aksi</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <!-- ==================== 6. DPA MURNI ==================== -->
      <div class="admin-page" id="admin-page-dpa-murni">
        <div class="page-header">
          <div class="page-header-title">
            <h1>Kelola Data — DPA Murni</h1>
            <p>Pengelolaan berkas Dokumen Pelaksanaan Anggaran (DPA) SKPD penetapan murni.</p>
          </div>
          <div class="page-header-meta">Pelaksanaan Anggaran</div>
        </div>

        <div class="table-toolbar">
          <div class="toolbar-group">
            <input type="text" class="form-control search-box" placeholder="Cari dokumen DPA...">
            <select class="form-control filter-year">
              <option value="">Semua Tahun</option>
              <option value="2026">2026</option>
              <option value="2025">2025</option>
            </select>
          </div>
          <div class="toolbar-group">
            <button class="btn btn-primary" onclick="openCrudModal('create', 'dpa-murni')">
              <svg class="btn-icon-svg" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
              Tambah Dokumen DPA
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="gov-table" id="admin-table-dpa-murni">
            <thead>
              <tr>
                <th style="width:48px;text-align:center;">
                  <input type="checkbox" class="gov-checkbox table-master-checkbox" onclick="event.stopPropagation(); toggleSelectAllActiveTable(event)" title="Pilih Semua">
                </th>
                <th style="width:75px;text-align:center;">Tahun</th>
                <th>Nama Dokumen DPA</th>
                <th>Pagu Anggaran / Uraian Belanja</th>
                <th style="width:220px;">Berkas PDF</th>
                <th style="width:95px;text-align:center;">Status</th>
                <th style="width:190px;text-align:center;">Aksi</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <!-- ==================== 7. DPA PERUBAHAN ==================== -->
      <div class="admin-page" id="admin-page-dpa-perubahan">
        <div class="page-header">
          <div class="page-header-title">
            <h1>Kelola Data — DPA Perubahan</h1>
            <p>Pengelolaan berkas Dokumen Pelaksanaan Perubahan Anggaran (DPPA SKPD).</p>
          </div>
          <div class="page-header-meta">Pelaksanaan Anggaran</div>
        </div>

        <div class="table-toolbar">
          <div class="toolbar-group">
            <input type="text" class="form-control search-box" placeholder="Cari dokumen DPPA...">
            <select class="form-control filter-year">
              <option value="">Semua Tahun</option>
              <option value="2026">2026</option>
              <option value="2025">2025</option>
            </select>
          </div>
          <div class="toolbar-group">
            <button class="btn btn-primary" onclick="openCrudModal('create', 'dpa-perubahan')">
              <svg class="btn-icon-svg" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
              Tambah Dokumen DPPA
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="gov-table" id="admin-table-dpa-perubahan">
            <thead>
              <tr>
                <th style="width:48px;text-align:center;">
                  <input type="checkbox" class="gov-checkbox table-master-checkbox" onclick="event.stopPropagation(); toggleSelectAllActiveTable(event)" title="Pilih Semua">
                </th>
                <th style="width:75px;text-align:center;">Tahun</th>
                <th>Nama Dokumen DPPA</th>
                <th>Pergeseran Anggaran / Uraian</th>
                <th style="width:220px;">Berkas PDF</th>
                <th style="width:95px;text-align:center;">Status</th>
                <th style="width:190px;text-align:center;">Aksi</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <!-- ==================== 8. SURAT MASUK ==================== -->
      <div class="admin-page" id="admin-page-surat-masuk">
        <div class="page-header">
          <div class="page-header-title">
            <h1>Kelola Data — Agenda Surat Masuk</h1>
            <p>Pencatatan agenda surat dinas masuk, disposisi pimpinan, dan berkas scan PDF.</p>
          </div>
          <div class="page-header-meta">Arsip Persuratan</div>
        </div>

        <div class="table-toolbar">
          <div class="toolbar-group">
            <input type="text" class="form-control search-box" placeholder="Cari nomor surat / perihal...">
            <select class="form-control filter-year">
              <option value="">Semua Tahun</option>
              <option value="2026">2026</option>
            </select>
          </div>
          <div class="toolbar-group">
            <button class="btn btn-primary" onclick="openCrudModal('create', 'surat-masuk')">
              <svg class="btn-icon-svg" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
              Catat Surat Masuk
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="gov-table" id="admin-table-surat-masuk">
            <thead>
              <tr>
                <th style="width:48px;text-align:center;">
                  <input type="checkbox" class="gov-checkbox table-master-checkbox" onclick="event.stopPropagation(); toggleSelectAllActiveTable(event)" title="Pilih Semua">
                </th>
                <th style="width:90px;text-align:center;">Tanggal</th>
                <th style="width:220px;">Nomor Surat & Pengirim</th>
                <th>Perihal & Disposisi</th>
                <th style="width:200px;">Berkas Scan PDF</th>
                <th style="width:95px;text-align:center;">Status</th>
                <th style="width:190px;text-align:center;">Aksi</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <!-- ==================== 9. SURAT KELUAR ==================== -->
      <div class="admin-page" id="admin-page-surat-keluar">
        <div class="page-header">
          <div class="page-header-title">
            <h1>Kelola Data — Agenda Surat Keluar</h1>
            <p>Pencatatan nomor surat keluar dinas, instansi tujuan, dan scan arsip tertandatangan.</p>
          </div>
          <div class="page-header-meta">Arsip Persuratan</div>
        </div>

        <div class="table-toolbar">
          <div class="toolbar-group">
            <input type="text" class="form-control search-box" placeholder="Cari surat keluar...">
            <select class="form-control filter-year">
              <option value="">Semua Tahun</option>
              <option value="2026">2026</option>
            </select>
          </div>
          <div class="toolbar-group">
            <button class="btn btn-primary" onclick="openCrudModal('create', 'surat-keluar')">
              <svg class="btn-icon-svg" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
              Catat Surat Keluar
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="gov-table" id="admin-table-surat-keluar">
            <thead>
              <tr>
                <th style="width:48px;text-align:center;">
                  <input type="checkbox" class="gov-checkbox table-master-checkbox" onclick="event.stopPropagation(); toggleSelectAllActiveTable(event)" title="Pilih Semua">
                </th>
                <th style="width:90px;text-align:center;">Tanggal</th>
                <th style="width:220px;">Nomor Surat & Tujuan</th>
                <th>Perihal & Penandatangan</th>
                <th style="width:200px;">Berkas Scan PDF</th>
                <th style="width:95px;text-align:center;">Status</th>
                <th style="width:190px;text-align:center;">Aksi</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <!-- ==================== 10. LAPORAN SIMDAPANGDA ==================== -->
      <div class="admin-page" id="admin-page-laporan">
        <div class="page-header">
          <div class="page-header-title">
            <h1>Laporan Rekapitulasi Simdapangda</h1>
            <p>Monitoring kepatuhan pelaporan seluruh dokumen dinas serta opsi ekspor PDF.</p>
          </div>
          <div class="page-header-meta">Ekspor & Monitoring</div>
        </div>

        <div class="panel">
          <div class="panel-header">
            <span class="panel-title">Tabel Rekapitulasi Realisasi Berkas SIM-PEP (T.A. 2026)</span>
            <button class="btn btn-primary btn-sm" onclick="exportPdf('Laporan_Rekapitulasi_Simdapangda_2026.pdf')">
              <svg class="btn-icon-svg" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
              Export PDF Laporan Lengkap
            </button>
          </div>
          <div class="panel-body">
            <p style="font-size:13px;color:var(--text-body);margin-bottom:12px;">
              Dokumen rekapitulasi merangkum seluruh berkas yang berhasil dikelola ke sistem SIM-PEP selama Tahun Anggaran berjalan.
            </p>
            <table class="gov-table">
              <thead>
                <tr>
                  <th style="width:45px;text-align:center;">No</th>
                  <th>Kelompok Modul Dokumen</th>
                  <th style="width:130px;text-align:center;">Target Dokumen</th>
                  <th style="width:160px;text-align:center;">Dokumen Terdata (PDF)</th>
                  <th style="width:130px;text-align:center;">Kepatuhan</th>
                  <th style="width:140px;text-align:center;">Aksi Rekap</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td style="text-align:center;">1</td>
                  <td><strong>Rencana Kerja (Renja Murni & Perubahan)</strong></td>
                  <td style="text-align:center;">—</td>
                  <td style="text-align:center;"><strong style="color:var(--primary);">{{ $countRenjaMurni + $countRenjaPerubahan }} Berkas</strong></td>
                  <td style="text-align:center;"><span class="badge {{ ($countRenjaMurni + $countRenjaPerubahan) > 0 ? 'badge-green' : 'badge-gray' }}">{{ ($countRenjaMurni + $countRenjaPerubahan) > 0 ? 'Tersedia' : 'Kosong' }}</span></td>
                  <td style="text-align:center;">
                    <button class="btn btn-outline btn-sm" onclick="exportPdf('Rekap_Renja_2026.pdf')">Unduh Rekap</button>
                  </td>
                </tr>
                <tr>
                  <td style="text-align:center;">2</td>
                  <td><strong>Perjanjian Kinerja (PK Murni & Perubahan)</strong></td>
                  <td style="text-align:center;">—</td>
                  <td style="text-align:center;"><strong style="color:var(--primary);">{{ $countPkMurni + $countPkPerubahan }} Berkas</strong></td>
                  <td style="text-align:center;"><span class="badge {{ ($countPkMurni + $countPkPerubahan) > 0 ? 'badge-green' : 'badge-gray' }}">{{ ($countPkMurni + $countPkPerubahan) > 0 ? 'Tersedia' : 'Kosong' }}</span></td>
                  <td style="text-align:center;">
                    <button class="btn btn-outline btn-sm" onclick="exportPdf('Rekap_PK_2026.pdf')">Unduh Rekap</button>
                  </td>
                </tr>
                <tr>
                  <td style="text-align:center;">3</td>
                  <td><strong>Pelaksanaan Anggaran (DPA Murni & Perubahan)</strong></td>
                  <td style="text-align:center;">—</td>
                  <td style="text-align:center;"><strong style="color:var(--primary);">{{ $countDpaMurni + $countDpaPerubahan }} Berkas</strong></td>
                  <td style="text-align:center;"><span class="badge {{ ($countDpaMurni + $countDpaPerubahan) > 0 ? 'badge-green' : 'badge-gray' }}">{{ ($countDpaMurni + $countDpaPerubahan) > 0 ? 'Tersedia' : 'Kosong' }}</span></td>
                  <td style="text-align:center;">
                    <button class="btn btn-outline btn-sm" onclick="exportPdf('Rekap_DPA_2026.pdf')">Unduh Rekap</button>
                  </td>
                </tr>
                <tr>
                  <td style="text-align:center;">4</td>
                  <td><strong>Arsip Persuratan (Surat Masuk & Keluar)</strong></td>
                  <td style="text-align:center;">—</td>
                  <td style="text-align:center;"><strong style="color:var(--primary);">{{ $countSuratMasuk + $countSuratKeluar }} Berkas</strong></td>
                  <td style="text-align:center;"><span class="badge {{ ($countSuratMasuk + $countSuratKeluar) > 0 ? 'badge-blue' : 'badge-gray' }}">{{ ($countSuratMasuk + $countSuratKeluar) > 0 ? 'Tercatat' : 'Kosong' }}</span></td>
                  <td style="text-align:center;">
                    <button class="btn btn-outline btn-sm" onclick="exportPdf('Rekap_Agenda_Surat_2026.pdf')">Unduh Rekap</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>

      <!-- ==================== 11. CAPAIAN KINERJA (e-SAKIP SESUAI FOTO) ==================== -->
      <div class="admin-page" id="admin-page-capaian-kinerja">
        <div class="page-header">
          <div class="page-header-title">
            <h1>Capaian Kinerja Organisasi Perangkat Daerah</h1>
            <p>Pengukuran realisasi fisik indikator kinerja, serapan anggaran keuangan, formula capaian triwulan, dan evaluasi capaian e-SAKIP.</p>
          </div>
          <div class="page-header-meta">e-SAKIP DISDIK</div>
        </div>

        <!-- Filter & Control Card Sesuai Tema SIM-PEP Hope UI -->
        <div class="table-toolbar" style="background:#ffffff;padding:14px 18px;border-radius:12px;border:1px solid #e2e8f0;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;box-shadow:0 1px 3px rgba(0,0,0,0.03);">
          <div class="toolbar-group" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            
            <!-- Filter Tahun Dinamis -->
            <div style="display:flex;align-items:center;gap:8px;">
              <span style="font-size:12px;font-weight:700;color:#475569;white-space:nowrap;">Tahun:</span>
              <select id="capaianFilterTahun" class="form-control" onchange="onCapaianFilterChange()" style="min-width:105px;height:38px;padding:0 10px;font-weight:700;border:1.5px solid #cbd5e1;border-radius:8px;font-size:13px;color:#1e293b;background:#ffffff;">
                @foreach($capaianYears as $yr)
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

          <!-- Action Buttons: Export Excel, Cetak PDF, Tambah Data -->
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

            <button type="button" class="btn btn-primary" onclick="openModalCapaian('create')" title="Tambah Data Capaian Kinerja Baru" style="height:38px;padding:0 16px;border-radius:8px;display:inline-flex;align-items:center;gap:6px;font-size:12.5px;font-weight:700;background:#00875a;border-color:#00875a;color:#ffffff;cursor:pointer;">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
              </svg>
              <span>Tambah Data</span>
            </button>
          </div>
        </div>

        <!-- Tabel Capaian Kinerja Multi-Level Header (Dinamis Melebar & Mengecil Sesuai Kalimat/Angka Asli) -->
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
                <!-- Data dirender otomatis oleh renderCapaianTable() -->
              </tbody>
            </table>
          </div>
        </div>

      </div>

    </div>
  </div>
</div>

<!-- ==================== MODAL FORM TAMBAH / EDIT DATA (CRUD) ==================== -->
<div class="modal-overlay" id="modalCrud">
  <div class="modal-dialog modal-lg">
    <div class="modal-header">
      <h3 id="modalCrudTitle">TAMBAH DATA DOKUMEN</h3>
      <button class="modal-close-btn" onclick="closeAdminModal('modalCrud')">&times;</button>
    </div>
    <form id="formCrud" onsubmit="handleCrudSubmit(event)">
      <div class="modal-body">
        
        <input type="hidden" id="crudMode" value="create">
        <input type="hidden" id="crudModuleKey" value="renja-murni">
        <input type="hidden" id="crudEditId" value="">

        <div class="form-row">
          <div class="form-group" style="margin-bottom:0;">
            <label>Tahun Anggaran <span class="req">*</span></label>
            <input 
              type="number" 
              class="form-control" 
              id="crudTahun" 
              name="tahun_anggaran"
              min="1990" 
              max="2100" 
              step="1"
              placeholder="Contoh: 2026" 
              value="{{ date('Y') }}"
              required
              style="width:100%;height:38px;font-weight:700;font-size:13px;color:var(--primary);box-sizing:border-box;"
            >
          </div>
          <div class="form-group" style="margin-bottom:0;">
            <label id="crudModuleLabel">Jenis Dokumen <span class="req">*</span></label>
            <!-- Saat di dalam fitur: Terkunci otomatis sesuai modul halaman aktif -->
            <input 
              type="text" 
              class="form-control" 
              id="crudModuleFixedDisplay" 
              readonly 
              style="width:100%;height:38px;font-weight:700;background:#f8fafc;color:var(--primary);border:1px solid #cbd5e1;cursor:not-allowed;box-sizing:border-box;"
            >
            <!-- Hanya muncul jika modal dibuka dari Dashboard Utama -->
            <select class="form-control" id="crudModuleSelect" onchange="onCrudModuleChange(this.value)" style="width:100%;height:38px;font-weight:700;color:var(--primary);display:none;box-sizing:border-box;">
              <optgroup label="Rencana Kerja (Renja)">
                <option value="renja-murni">Renja Murni</option>
                <option value="renja-perubahan">Renja Perubahan</option>
              </optgroup>
              <optgroup label="Perjanjian Kinerja (PK)">
                <option value="pk-murni">PK Murni</option>
                <option value="pk-perubahan">PK Perubahan</option>
              </optgroup>
              <optgroup label="Pelaksanaan Anggaran (DPA)">
                <option value="dpa-murni">DPA Murni</option>
                <option value="dpa-perubahan">DPA Perubahan</option>
              </optgroup>
              <optgroup label="Arsip Persuratan">
                <option value="surat-masuk">Surat Masuk</option>
                <option value="surat-keluar">Surat Keluar</option>
              </optgroup>
            </select>
          </div>
        </div>

        <!-- Kolom Upload PDF Khusus dengan Auto-Name & Icon -->
        <div class="form-group">
          <label id="crudFileLabel">Unggah Berkas PDF Lampiran <span class="req">*</span></label>
          <div class="pdf-upload-box" id="crudPdfDropzone" onclick="document.getElementById('crudPdfFile').click()">
            <svg style="width:30px;height:30px;stroke:var(--primary);stroke-width:1.8;fill:none;margin-bottom:6px;" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
            <div style="font-weight:700;color:var(--primary);margin-bottom:2px;">
              Klik atau tarik file PDF ke area ini
            </div>
            <div style="font-size:12px;color:var(--text-muted);">
              Bisa pilih <strong>lebih dari satu</strong> file <strong>.PDF</strong> sekaligus (Maks. 20 MB per file)
            </div>
            <div class="pdf-file-info" id="crudPdfPreview"></div>
          </div>
          <!-- List file PDF yang dipilih (multi) -->
          <div id="crudPdfFileList" style="display:none;margin-top:8px;"></div>
          <input type="file" id="crudPdfFile" accept=".pdf" multiple style="display:none">
        </div>

        <!-- Judul / Nama Dokumen (Otomatis dari PDF jika dipilih) -->
        <div class="form-group">
          <label id="crudTitleLabel">Nama Dokumen / Perihal <span class="req">*</span></label>
          <input 
            type="text" 
            class="form-control" 
            id="crudJudul" 
            placeholder="Otomatis terisi dari nama file PDF yang dipilih..." 
            required 
            style="width:100%;font-weight:600;"
          >
          <div style="font-size:11px;color:var(--success);font-weight:700;margin-top:4px;">
            ✓ Nama dokumen otomatis mengambil nama file PDF (tetap dapat disunting jika diperlukan)
          </div>
        </div>

        <!-- Indikator Auto-Sync Google Drive Desktop -->
        <div style="padding:10px 14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;display:flex;align-items:center;gap:10px;margin-bottom:14px;">
          <svg style="width:20px;height:20px;fill:#16a34a;flex-shrink:0;" viewBox="0 0 24 24"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM19 18H6c-2.21 0-4-1.79-4-4 0-2.05 1.53-3.76 3.56-3.97l1.07-.11.5-.95C8.08 7.14 9.94 6 12 6c2.62 0 4.88 1.86 5.39 4.43l.3 1.5 1.53.11c1.56.1 2.78 1.41 2.78 2.96 0 1.65-1.35 3-3 3z"/></svg>
          <div style="font-size:12px;color:#166534;font-weight:600;">
            Berkas PDF otomatis tersimpan ke server & disinkronkan ke Google Drive (<span style="font-family:monospace;font-size:11px;">G:\My Drive\SIM-PEP</span>)
          </div>
        </div>

        <!-- Bidang Khusus Surat (Hanya Tampil Saat Modul Surat Masuk/Keluar) -->
        <div id="groupSuratFields" style="display:none;margin-bottom:12px;padding:12px;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;">
          <div class="form-row">
            <div class="form-group">
              <label>Nomor Surat <span class="req">*</span></label>
              <input type="text" class="form-control" id="crudNomorSurat" placeholder="Contoh: 005/PEP-DISDIK/IX/2026" style="width:100%;">
            </div>
            <div class="form-group">
              <label>Tanggal Surat <span class="req">*</span></label>
              <input type="date" class="form-control" id="crudTanggalSurat" value="{{ date('Y-m-d') }}" style="width:100%;">
            </div>
          </div>
          <div class="form-group" style="margin-bottom:0;">
            <label id="crudInstansiLabel">Instansi Pengirim / Tujuan <span class="req">*</span></label>
            <input type="text" class="form-control" id="crudInstansi" placeholder="Contoh: Bappeda Litbang / Kepala Dinas" style="width:100%;">
          </div>
        </div>

        <!-- Status Dokumen -->
        <div class="form-group">
          <label>Status Verifikasi Administrasi <span class="req">*</span></label>
          <select class="form-control" id="crudStatus" style="width:100%;" required>
            <option value="Lengkap">Lengkap (Terverifikasi)</option>
            <option value="Diproses">Diproses / Dalam Penelaahan</option>
            <option value="Perlu Update">Perlu Update / Revisi</option>
            <option value="Terkirim">Terkirim / Selesai</option>
          </select>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeAdminModal('modalCrud')">Batal</button>
        <button type="submit" class="btn btn-primary" id="btnCrudSubmit">Simpan Dokumen</button>
      </div>
    </form>
  </div>
</div>

<!-- ==================== MODAL DETAIL DOKUMEN ==================== -->
<div class="modal-overlay" id="modalDetail">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 id="detailModalTitle">DETAIL DOKUMEN</h3>
      <button class="modal-close-btn" onclick="closeAdminModal('modalDetail')">&times;</button>
    </div>
    <div class="modal-body" id="detailModalBody">
      <!-- Diisi oleh JS -->
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-outline" onclick="closeAdminModal('modalDetail')">Tutup</button>
      <button type="button" class="btn btn-primary" id="btnDetailDownload">
        <svg class="btn-icon-svg" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
        Unduh Berkas PDF
      </button>
    </div>
  </div>
</div>

<!-- ==================== MODAL KONFIRMASI HAPUS (DELETE) ==================== -->
<div class="modal-overlay" id="modalDelete">
  <div class="modal-dialog" style="max-width:440px;">
    <div class="modal-header">
      <h3>KONFIRMASI HAPUS DATA</h3>
      <button class="modal-close-btn" onclick="closeAdminModal('modalDelete')">&times;</button>
    </div>
    <div class="modal-body">
      <p id="deleteModalMessage" style="color:var(--text-dark);line-height:1.5;">Apakah Anda yakin ingin menghapus data dokumen ini?</p>
      <p style="font-size:12px;color:var(--text-muted);margin-top:6px;">Data yang dihapus akan langsung hilang dari tabel dan Portal Pengguna.</p>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-outline" onclick="closeAdminModal('modalDelete')">Batal</button>
      <button type="button" class="btn btn-danger" onclick="confirmDeleteDoc()">Hapus Permanen</button>
    </div>
  </div>
</div>

<!-- ==================== MODAL TAMBAH / EDIT AGENDA ==================== -->
<div class="modal-overlay" id="modalAgenda">
  <div class="modal-dialog" style="max-width:480px;">
    <div class="modal-header">
      <h3 id="agendaModalTitle">TAMBAH AGENDA</h3>
      <button class="modal-close-btn" onclick="closeAdminModal('modalAgenda')">&times;</button>
    </div>
    <form id="formAgenda" onsubmit="saveAgenda(event)">
      <div class="modal-body">
        <input type="hidden" id="agendaEditId" value="">

        <!-- Judul -->
        <div class="form-group">
          <label>Judul Agenda <span class="req">*</span></label>
          <input type="text" class="form-control" id="agendaJudul" placeholder="Contoh: Evaluasi Capaian Renja Triwulan III" required style="width:100%;">
        </div>

        <!-- Tanggal & Jam -->
        <div class="form-row">
          <div class="form-group" style="margin-bottom:0;">
            <label>Tanggal <span class="req">*</span></label>
            <input type="date" class="form-control" id="agendaTanggal" required style="width:100%;height:38px;">
          </div>
          <div class="form-group" style="margin-bottom:0;">
            <label>Jam (opsional)</label>
            <input type="time" class="form-control" id="agendaJam" style="width:100%;height:38px;">
          </div>
        </div>

        <!-- Prioritas -->
        <div class="form-group">
          <label>Prioritas</label>
          <select class="form-control" id="agendaPrioritas" style="width:100%;height:38px;">
            <option value="biasa">⚪ Biasa</option>
            <option value="penting">🟡 Penting</option>
            <option value="mendesak">🔴 Mendesak</option>
          </select>
        </div>

        <!-- Catatan -->
        <div class="form-group" style="margin-bottom:0;">
          <label>Catatan / Deskripsi (opsional)</label>
          <textarea class="form-control" id="agendaCatatan" rows="3" placeholder="Deskripsi singkat agenda..." style="width:100%;resize:vertical;"></textarea>
        </div>

        <!-- Info notifikasi -->
        <div id="agendaNotifInfo" style="margin-top:12px;padding:10px 14px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;font-size:12px;color:#1d4ed8;display:flex;align-items:center;gap:8px;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <span id="agendaNotifInfoText">Notifikasi browser akan muncul saat waktu agenda tiba.</span>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeAdminModal('modalAgenda')">Batal</button>
        <button type="submit" class="btn btn-primary" id="btnAgendaSubmit">Simpan Agenda</button>
      </div>
    </form>
  </div>
</div>

<!-- ==================== MODAL FORM CRUD CAPAIAN KINERJA ==================== -->
<div class="modal-overlay" id="modalCrudCapaian">
  <div class="modal-dialog modal-lg" style="max-width:850px;">
    <div class="modal-header">
      <h3 id="modalCrudCapaianTitle">TAMBAH DATA CAPAIAN KINERJA</h3>
      <button class="modal-close-btn" onclick="closeAdminModal('modalCrudCapaian')">&times;</button>
    </div>
    <form id="formCrudCapaian" onsubmit="handleCapaianSubmit(event)" novalidate>
      <div class="modal-body" style="max-height: calc(85vh - 120px); overflow-y: auto;">
        
        <input type="hidden" id="crudCapaianId" value="">
        <input type="hidden" id="crudCapaianMode" value="create">

        <!-- Baris 1: Tahun, Triwulan, Satuan -->
        <div class="form-row">
          <div class="form-group">
            <label>Tahun Anggaran</label>
            <input 
              type="number" 
              class="form-control" 
              id="crudCapaianTahun" 
              placeholder="Contoh: 2026" 
              value="2026"
              style="width:100%;font-weight:700;color:var(--primary);"
            >
          </div>
          <div class="form-group">
            <label>Periode Triwulan</label>
            <select class="form-control" id="crudCapaianTriwulan" onchange="onModalTriwulanChange()" style="width:100%;font-weight:600;">
              <option value="TW I">Triwulan I (TW I)</option>
              <option value="TW II">Triwulan II (TW II)</option>
              <option value="TW III">Triwulan III (TW III)</option>
              <option value="TW IV">Triwulan IV (TW IV)</option>
            </select>
          </div>
          <div class="form-group">
            <label>Satuan Indikator</label>
            <input 
              type="text" 
              class="form-control" 
              id="crudCapaianSatuan" 
              placeholder="Contoh: %, Orang, Dokumen, Ruang Kelas" 
              value="Persentase" 
              style="width:100%;"
            >
          </div>
        </div>


        <!-- Sasaran / Program / Kegiatan -->
        <div class="form-group">
          <label>Tujuan / Sasaran / Program / Kegiatan / Sub Kegiatan</label>
          <textarea 
            class="form-control" 
            id="crudCapaianSasaran" 
            rows="2" 
            placeholder="Ketik uraian tujuan / sasaran strategis / program / kegiatan..." 
            style="width:100%;resize:vertical;"
          ></textarea>
        </div>

        <!-- Indikator Kinerja -->
        <div class="form-group">
          <label>Indikator Kinerja</label>
          <textarea 
            class="form-control" 
            id="crudCapaianIndikator" 
            rows="2" 
            placeholder="Ketik tolok ukur atau rumusan indikator kinerja..." 
            style="width:100%;resize:vertical;font-weight:600;"
          ></textarea>
        </div>

        <!-- Pagu Anggaran & Target Tahunan -->
        <div class="form-row">
          <div class="form-group">
            <label>Target Kinerja Tahunan (Data 2026)</label>
            <input 
              type="text" 
              class="form-control" 
              id="crudCapaianTargetTahunan" 
              placeholder="Contoh: 85.50" 
              style="width:100%;font-weight:600;"
            >
          </div>
          <div class="form-group">
            <label>Pagu Anggaran Rp (Data 2026)</label>
            <input 
              type="text" 
              class="form-control" 
              id="crudCapaianPaguAnggaran" 
              placeholder="Contoh: 60.940.000 atau 60940000" 
              oninput="recalculateCapaianForm()"
              style="width:100%;font-weight:600;"
            >
          </div>
        </div>

        <!-- Rincian Target per Triwulan (Target Kinerja TW I - IV) -->
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px 14px;margin-bottom:14px;">
          <label style="font-size:11.5px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:0.03em;display:block;margin-bottom:8px;">
            Target Kinerja per Triwulan (Target TW I s/d TW IV)
          </label>
          <div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:10px;">
            <div>
              <span style="font-size:11px;font-weight:700;color:#64748b;">Target TW I</span>
              <input type="text" class="form-control" id="crudCapaianTw1" placeholder="0.00" oninput="recalculateCapaianForm()" style="width:100%;margin-top:3px;">
            </div>
            <div>
              <span style="font-size:11px;font-weight:700;color:#64748b;">Target TW II</span>
              <input type="text" class="form-control" id="crudCapaianTw2" placeholder="0.00" oninput="recalculateCapaianForm()" style="width:100%;margin-top:3px;">
            </div>
            <div>
              <span style="font-size:11px;font-weight:700;color:#64748b;">Target TW III</span>
              <input type="text" class="form-control" id="crudCapaianTw3" placeholder="0.00" oninput="recalculateCapaianForm()" style="width:100%;margin-top:3px;">
            </div>
            <div>
              <span style="font-size:11px;font-weight:700;color:#64748b;">Target TW IV</span>
              <input type="text" class="form-control" id="crudCapaianTw4" placeholder="0.00" oninput="recalculateCapaianForm()" style="width:100%;margin-top:3px;">
            </div>
          </div>
        </div>

        <!-- Realisasi Triwulan & Live Formula Calculation -->
        <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:14px;margin-bottom:14px;">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <label style="font-size:12px;font-weight:700;color:#9a3412;text-transform:uppercase;letter-spacing:0.03em;margin:0;" id="labelRealisasiTriwulan">
              Input Realisasi &amp; Perhitungan Otomatis (Triwulan Terpilih)
            </label>
            <span style="font-size:11px;font-weight:700;background:#ffedd5;color:#c2410c;padding:2px 8px;border-radius:4px;">
              Formula Otomatis
            </span>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label style="color:#9a3412;" id="labelRealFisikTW">Realisasi Kinerja Fisik</label>
              <input 
                type="text" 
                class="form-control" 
                id="crudCapaianRealisasiKinerja" 
                placeholder="Contoh: 21.30 (Opsional)" 
                oninput="recalculateCapaianForm()"
                style="width:100%;font-weight:700;background:#ffffff;border-color:#fb923c;"
              >
            </div>
            <div class="form-group">
              <label style="color:#9a3412;" id="labelRealKeuTW">Realisasi Keuangan Rp</label>
              <input 
                type="text" 
                class="form-control" 
                id="crudCapaianRealisasiKeuangan" 
                placeholder="Contoh: 15.000.000 atau 15000000" 
                oninput="recalculateCapaianForm()"
                style="width:100%;font-weight:700;background:#ffffff;border-color:#fb923c;"
              >
            </div>
          </div>

          <!-- Kalkulasi Otomatis & Input Manual Capaian Kinerja -->
          <div style="background:#ffffff;padding:12px 14px;border-radius:8px;border:1.5px dashed #fb923c;margin-top:10px;">
            <div style="font-size:11px;font-weight:700;color:#9a3412;margin-bottom:8px;display:flex;align-items:center;justify-content:space-between;">
              <span>HASIL CAPAIAN (TERHITUNG OTOMATIS &amp; BISA DIUBAH MANUAL)</span>
              <span style="font-size:10.5px;color:#64748b;font-weight:normal;">*Nilai terisi otomatis, bebas Anda sesuaikan</span>
            </div>
            
            <div class="form-row" style="display:grid;grid-template-columns:repeat(3, 1fr);gap:10px;margin-bottom:0;">
              <div class="form-group" style="margin-bottom:0;">
                <label style="font-size:11.5px;color:#ea580c;font-weight:700;">% Capaian Kinerja</label>
                <div style="position:relative;">
                  <input 
                    type="text" 
                    class="form-control" 
                    id="crudCapaianPersenKinerja" 
                    placeholder="0.00" 
                    style="width:100%;font-weight:800;color:#c2410c;padding-right:24px;"
                  >
                  <span style="position:absolute;right:8px;top:50%;transform:translateY(-50%);font-weight:700;color:#9a3412;font-size:12px;">%</span>
                </div>
              </div>

              <div class="form-group" style="margin-bottom:0;">
                <label style="font-size:11.5px;color:#15803d;font-weight:700;">Predikat Kinerja</label>
                <select class="form-control" id="crudCapaianPredikat" style="width:100%;font-weight:700;color:#15803d;background:#ffffff;">
                  <option value="Sangat Berhasil">Sangat Berhasil (≥ 100%)</option>
                  <option value="Berhasil">Berhasil (85% - 99.9%)</option>
                  <option value="Cukup">Cukup (70% - 84.9%)</option>
                  <option value="Sedang">Sedang (55% - 69.9%)</option>
                  <option value="Kurang">Kurang (&lt; 55%)</option>
                </select>
              </div>

              <div class="form-group" style="margin-bottom:0;">
                <label style="font-size:11.5px;color:#2563eb;font-weight:700;">% Capaian Keuangan</label>
                <div style="position:relative;">
                  <input 
                    type="text" 
                    class="form-control" 
                    id="crudCapaianPersenKeuangan" 
                    placeholder="0.00" 
                    style="width:100%;font-weight:800;color:#1d4ed8;padding-right:24px;"
                  >
                  <span style="position:absolute;right:8px;top:50%;transform:translateY(-50%);font-weight:700;color:#1e40af;font-size:12px;">%</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Bukti Pendukung / Tautan Link Biasa (Opsional) -->
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px 14px;margin-bottom:10px;">
          <div class="form-group" style="margin-bottom:0;">
            <label style="font-size:12px;font-weight:700;color:#334155;">Tautan / Link Berkas Pendukung (Opsional)</label>
            <input 
              type="text" 
              class="form-control" 
              id="crudCapaianBuktiLink" 
              placeholder="Contoh: link dokumen atau URL pendukung (jika ada)" 
              style="width:100%;font-size:12.5px;background:#ffffff;"
            >
            <div style="font-size:11px;color:#64748b;margin-top:4px;">*Data langsung tersimpan di database lokal Anda.</div>
          </div>
          <input type="hidden" id="crudCapaianBuktiKeterangan" value="">
          <div id="crudCapaianExistingFile" style="display:none;"></div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeAdminModal('modalCrudCapaian')">Batal</button>
        <button type="submit" class="btn btn-primary" id="btnCapaianSubmit">Simpan Capaian Kinerja</button>
      </div>
    </form>
  </div>
</div>

<!-- ==================== MODAL CEPAT: KIRIM & KELOLA BUKTI PENDUKUNG ==================== -->
<div class="modal-overlay" id="modalBuktiCapaian">
  <div class="modal-dialog" style="max-width:550px;">
    <div class="modal-header" style="background:#0f766e;color:#ffffff;">
      <h3 style="color:#ffffff;">BUKTI PENDUKUNG KINERJA</h3>
      <button class="modal-close-btn" onclick="closeAdminModal('modalBuktiCapaian')" style="color:#ffffff;">&times;</button>
    </div>
    <form id="formBuktiCapaian" onsubmit="handleQuickBuktiSubmit(event)">
      <div class="modal-body">
        <input type="hidden" id="quickBuktiId" value="">

        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px;margin-bottom:14px;">
          <div style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;">Indikator Kinerja</div>
          <div id="quickBuktiIndikatorText" style="font-weight:700;color:#1e293b;font-size:13px;margin-top:2px;">-</div>
          <div id="quickBuktiMetaText" style="font-size:11px;color:#0284c7;margin-top:4px;font-weight:600;">-</div>
        </div>

        <div class="form-group">
          <label>Tautan Link Dokumen / Drive</label>
          <input 
            type="url" 
            class="form-control" 
            id="quickBuktiLink" 
            placeholder="https://drive.google.com/..." 
            style="width:100%;font-size:12.5px;"
          >
        </div>

        <div class="form-group">
          <label>Unggah Berkas Fisik</label>
          <input type="file" class="form-control" id="quickBuktiFile" style="width:100%;background:#ffffff;">
          <div id="quickBuktiFileCurrent" style="display:none;font-size:11px;color:#0f766e;font-weight:600;margin-top:4px;"></div>
        </div>

        <div class="form-group" style="margin-bottom:0;">
          <label>Catatan Keterangan</label>
          <textarea 
            class="form-control" 
            id="quickBuktiKeterangan" 
            rows="3" 
            placeholder="Tuliskan catatan atau keterangan..." 
            style="width:100%;resize:vertical;font-size:12px;"
          ></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeAdminModal('modalBuktiCapaian')">Tutup</button>
        <button type="submit" class="btn btn-primary" style="background:#0f766e;border-color:#0f766e;">Simpan Bukti</button>
      </div>
    </form>
  </div>
</div>

<!-- Top Floating Selection Action Bar (Desain Modern Pill) -->
<div class="table-selection-bar" id="tableSelectionBar">
  <div class="selection-info">
    <span class="selection-dot"></span>
    <span class="selection-count-text" id="selectionCountText">0 files selected</span>
  </div>
  <div class="selection-divider"></div>
  <div class="selection-btn-group">
    <button type="button" class="btn-selection-action btn-selection-selectall" onclick="selectAllRowsInActiveTable()" title="Pilih Seluruh Dokumen">
      <svg class="btn-sel-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
      <span>Pilih Semua</span>
    </button>
    <button type="button" class="btn-selection-action btn-selection-delete" onclick="openBatchDeleteModal()" title="Hapus Dokumen Terpilih">
      <svg class="btn-sel-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
      <span>Delete</span>
    </button>
    <button type="button" class="btn-selection-close" onclick="clearAllSelections()" title="Batalkan Seleksi">
      <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
    </button>
  </div>
</div>

<!-- Toast Notifications -->
<div class="toast-container" id="adminToastContainer"></div>

<script src="{{ asset('assets/js/admin.js') }}?v={{ time() }}"></script>
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
