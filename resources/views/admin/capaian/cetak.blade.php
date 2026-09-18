<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Laporan Capaian Kinerja {{ $triwulan }} Tahun {{ $tahun }} — SIM-PEP DISDIK</title>
  <style>
    @page {
      size: landscape;
      margin: 12mm 10mm 15mm 10mm;
    }
    * {
      box-sizing: border-box;
      font-family: 'Segoe UI', Arial, sans-serif;
    }
    body {
      margin: 0;
      padding: 16px;
      color: #0f172a;
      background: #ffffff;
      font-size: 10px;
    }
    .kop-surat {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 16px;
      border-bottom: 3px double #0f172a;
      padding-bottom: 10px;
      margin-bottom: 14px;
    }
    .kop-logo {
      width: 58px;
      height: 58px;
      object-fit: contain;
    }
    .kop-teks {
      text-align: center;
    }
    .kop-teks h3 {
      margin: 0;
      font-size: 13px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .kop-teks h2 {
      margin: 2px 0;
      font-size: 16px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.8px;
    }
    .kop-teks p {
      margin: 0;
      font-size: 10px;
      color: #334155;
    }
    .laporan-title {
      text-align: center;
      margin-bottom: 14px;
    }
    .laporan-title h4 {
      margin: 0;
      font-size: 12px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #0f172a;
    }
    .laporan-title p {
      margin: 3px 0 0;
      font-size: 10px;
      color: #475569;
    }
    
    /* TABEL PERSIS SESUAI FOTO */
    table.table-laporan {
      width: 100%;
      border-collapse: collapse;
      font-size: 9.5px;
      page-break-inside: auto;
    }
    table.table-laporan tr {
      page-break-inside: avoid;
      page-break-after: auto;
    }
    table.table-laporan th,
    table.table-laporan td {
      border: 1px solid #475569;
      padding: 4px 6px;
      vertical-align: middle;
    }
    table.table-laporan thead th {
      background-color: #f1f5f9;
      color: #0f172a;
      font-weight: 700;
      text-align: center;
    }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .text-left { text-align: left; }

    .chip-predikat {
      display: inline-block;
      padding: 2px 6px;
      border-radius: 4px;
      font-weight: 700;
      font-size: 8.5px;
    }
    .predikat-sangat { background: #dcfce7; color: #166534; }
    .predikat-berhasil { background: #e0f2fe; color: #0369a1; }
    .predikat-cukup { background: #fef9c3; color: #854d0e; }
    .predikat-sedang { background: #ffedd5; color: #9a3412; }
    .predikat-kurang { background: #fee2e2; color: #991b1b; }

    /* KOLOM TANDA TANGAN */
    .signature-section {
      margin-top: 24px;
      display: flex;
      justify-content: space-between;
      page-break-inside: avoid;
      padding: 0 40px;
    }
    .sig-box {
      text-align: center;
      width: 250px;
    }
    .sig-space {
      height: 55px;
    }
    .sig-name {
      font-weight: 700;
      text-decoration: underline;
    }

    /* TOMBOL PRINT CONTROL (HILANG SAAT CETAK) */
    .print-control {
      position: fixed;
      top: 14px;
      right: 14px;
      display: flex;
      gap: 8px;
      z-index: 9999;
      background: rgba(255, 255, 255, 0.95);
      padding: 6px 10px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .btn-print {
      background: #00875a;
      color: #fff;
      border: none;
      padding: 6px 14px;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
      font-size: 11px;
    }
    .btn-close {
      background: #64748b;
      color: #fff;
      border: none;
      padding: 6px 14px;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
      font-size: 11px;
    }
    @media print {
      .print-control {
        display: none !important;
      }
      body {
        padding: 0;
      }
    }
  </style>
</head>
<body>

  <!-- Floating Print Control -->
  <div class="print-control">
    <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
    <button class="btn-close" onclick="window.close()">Tutup</button>
  </div>

  <!-- Kop Surat Resmi -->
  <div class="kop-surat">
    <div class="kop-teks">
      <h3>PEMERINTAH KABUPATEN / PROVINSI</h3>
      <h2>DINAS PENDIDIKAN</h2>
      <p>Sub Bagian Perencanaan, Evaluasi, dan Pelaporan (PEP)</p>
      <p>Jalan Pendidikan No. 01 &bull; Telp. (021) 1234567 &bull; Website: disdik.go.id</p>
    </div>
  </div>

  <div class="laporan-title">
    <h4>LAPORAN PENGUKURAN CAPAIAN KINERJA DAN KEUANGAN (e-SAKIP)</h4>
    <p>Periode Pelaksanaan: <strong>{{ $triwulan !== 'Semua' ? $triwulan : 'SEMUA TRIWULAN' }}</strong> &bull; Tahun Anggaran: <strong>{{ $tahun }}</strong></p>
  </div>

  <!-- TABEL SESUAI FOTO -->
  <table class="table-laporan">
    <thead>
      <!-- Baris 1: Tingkat Atas -->
      <tr>
        <th rowspan="3" style="width: 25px;">No</th>
        <th rowspan="3" style="width: 170px;">Tujuan / Sasaran / Program / Kegiatan / Sub Kegiatan</th>
        <th rowspan="3" style="width: 160px;">Indikator Kinerja</th>
        <th colspan="3">Data {{ $tahun }}</th>
        <th colspan="4">Target Kinerja</th>
        <th colspan="3">Capaian Kinerja</th>
        <th colspan="2">Capaian Keuangan</th>
        <th rowspan="3" style="width: 110px;">Bukti Pendukung</th>
      </tr>

      <!-- Baris 2: Kolom Rincian -->
      <tr>
        <th style="width: 55px;">Target</th>
        <th style="width: 80px;">Rp</th>
        <th style="width: 50px;">Satuan</th>
        <th style="width: 48px;">TW I</th>
        <th style="width: 48px;">TW II</th>
        <th style="width: 48px;">TW III</th>
        <th style="width: 48px;">TW IV</th>
        <th colspan="3">{{ $triwulan !== 'Semua' ? $triwulan : 'TW I' }}</th>
        <th colspan="2">{{ $triwulan !== 'Semua' ? $triwulan : 'TW I' }}</th>
      </tr>

      <!-- Baris 3: Sub Kolom Capaian Kinerja & Keuangan -->
      <tr>
        <th style="width: 55px;">Realisasi</th>
        <th style="width: 55px;">Capaian (%)</th>
        <th style="width: 75px;">Predikat</th>
        <th style="width: 75px;">Realisasi</th>
        <th style="width: 55px;">Capaian (%)</th>
      </tr>
    </thead>
    <tbody>
      @php
        $totalPagu = 0;
        $totalRealisasiKeu = 0;
      @endphp

      @forelse($data as $index => $row)
        @php
          $totalPagu += (float)$row->pagu_anggaran;
          $totalRealisasiKeu += (float)$row->realisasi_keuangan;

          $predCls = 'predikat-berhasil';
          if ($row->predikat_kinerja === 'Sangat Berhasil') $predCls = 'predikat-sangat';
          elseif ($row->predikat_kinerja === 'Cukup') $predCls = 'predikat-cukup';
          elseif ($row->predikat_kinerja === 'Sedang') $predCls = 'predikat-sedang';
          elseif ($row->predikat_kinerja === 'Kurang') $predCls = 'predikat-kurang';
        @endphp
        <tr>
          <td class="text-center">{{ $index + 1 }}</td>
          <td class="text-left" style="font-weight: 600;">{{ $row->sasaran }}</td>
          <td class="text-left">{{ $row->indikator }}</td>
          <td class="text-right">{{ number_format($row->target_tahunan, 2, ',', '.') }}</td>
          <td class="text-right" style="font-weight: 600;">Rp {{ number_format($row->pagu_anggaran, 0, ',', '.') }}</td>
          <td class="text-center">{{ $row->satuan }}</td>
          <td class="text-right">{{ number_format($row->target_tw1, 2, ',', '.') }}</td>
          <td class="text-right">{{ number_format($row->target_tw2, 2, ',', '.') }}</td>
          <td class="text-right">{{ number_format($row->target_tw3, 2, ',', '.') }}</td>
          <td class="text-right">{{ number_format($row->target_tw4, 2, ',', '.') }}</td>
          <td class="text-right" style="font-weight: 700; color: #c2410c;">{{ number_format($row->realisasi_kinerja, 2, ',', '.') }}</td>
          <td class="text-right" style="font-weight: 700; color: #b91c1c;">{{ number_format($row->capaian_kinerja_persen, 2, ',', '.') }}%</td>
          <td class="text-center">
            <span class="chip-predikat {{ $predCls }}">{{ $row->predikat_kinerja ?? '-' }}</span>
          </td>
          <td class="text-right" style="font-weight: 700;">Rp {{ number_format($row->realisasi_keuangan, 0, ',', '.') }}</td>
          <td class="text-right" style="font-weight: 700; color: #b91c1c;">{{ number_format($row->capaian_keuangan_persen, 2, ',', '.') }}%</td>
          <td class="text-center" style="font-size: 10.5px;">
            @if($row->bukti_link)
              <a href="{{ $row->bukti_link }}" target="_blank" style="color: #0284c7; text-decoration: underline;">Link</a>
            @endif
            @if($row->bukti_file_name)
              <div>{{ $row->bukti_file_name }}</div>
            @endif
            @if(!$row->bukti_link && !$row->bukti_file_name)
              <span style="color: #94a3b8;">-</span>
            @endif
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="16" class="text-center" style="padding: 24px; color: #64748b;">
            Belum ada data capaian kinerja pada periode {{ $triwulan }} Tahun {{ $tahun }}.
          </td>
        </tr>
      @endforelse

      @if(count($data) > 0)
        @php
          $persenTotalKeu = ($totalPagu > 0) ? ($totalRealisasiKeu / $totalPagu) * 100 : 0;
        @endphp
        <tr style="font-weight: 700; background-color: #f8fafc;">
          <td colspan="4" class="text-center">TOTAL REKAPITULASI KEUANGAN</td>
          <td class="text-right">Rp {{ number_format($totalPagu, 0, ',', '.') }}</td>
          <td colspan="8"></td>
          <td class="text-right">Rp {{ number_format($totalRealisasiKeu, 0, ',', '.') }}</td>
          <td class="text-right" style="color: #b91c1c;">{{ number_format($persenTotalKeu, 2, ',', '.') }}%</td>
          <td></td>
        </tr>
      @endif
    </tbody>
  </table>

  <!-- Tanda Tangan Pengesahan -->
  <div class="signature-section">
    <div class="sig-box">
      <div>Mengetahui,</div>
      <div style="font-weight: 700;">Kepala Dinas Pendidikan</div>
      <div class="sig-space"></div>
      <div class="sig-name">Dr. H. AHMAD HIDAYAT, M.Pd.</div>
      <div>NIP. 19680512 199403 1 008</div>
    </div>

    <div class="sig-box">
      <div>Dibuat oleh,</div>
      <div style="font-weight: 700;">Kasubag Perencanaan, Evaluasi, dan Pelaporan</div>
      <div class="sig-space"></div>
      <div class="sig-name">RATNA SARI DEWI, S.AP., M.Si.</div>
      <div>NIP. 19850714 201001 2 015</div>
    </div>
  </div>

</body>
</html>
