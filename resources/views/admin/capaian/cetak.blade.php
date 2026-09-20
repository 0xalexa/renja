<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Laporan Capaian Kinerja {{ $triwulan }} Tahun {{ $tahun }} — SIM-PEP DISDIK</title>
  <style>
    @page {
      margin: 8mm 7mm 8mm 7mm;
    }
    * {
      box-sizing: border-box;
      font-family: Arial, Helvetica, sans-serif;
    }
    body {
      margin: 0;
      padding: 0;
      color: #0f172a;
      background: #ffffff;
      font-size: 7.5px;
    }
    .kop-surat {
      text-align: center;
      border-bottom: 2px solid #0f172a;
      padding-bottom: 5px;
      margin-bottom: 7px;
    }
    .kop-surat h3 {
      margin: 0;
      font-size: 11px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .kop-surat h2 {
      margin: 2px 0;
      font-size: 14px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.8px;
    }
    .kop-surat p {
      margin: 1px 0;
      font-size: 8.5px;
      color: #334155;
    }
    .laporan-title {
      text-align: center;
      margin-bottom: 7px;
    }
    .laporan-title h4 {
      margin: 0;
      font-size: 10.5px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #0f172a;
    }
    .laporan-title p {
      margin: 2px 0 0;
      font-size: 8.5px;
      color: #475569;
    }
    
    /* TABEL PERSIS PAS LEBAR HALAMAN 100% TANPA TERPOTONG */
    table.table-laporan {
      width: 100%;
      table-layout: fixed;
      border-collapse: collapse;
      font-size: 7.5px;
    }
    table.table-laporan th,
    table.table-laporan td {
      border: 0.5px solid #475569;
      padding: 3px 2px;
      vertical-align: middle;
      word-wrap: break-word;
      overflow: hidden;
    }
    table.table-laporan thead th {
      background-color: #f1f5f9;
      color: #0f172a;
      font-weight: bold;
      text-align: center;
    }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .text-left { text-align: left; }

    .chip-predikat {
      display: inline-block;
      padding: 1px 4px;
      border-radius: 2px;
      font-weight: bold;
      font-size: 7px;
      line-height: 1.2;
    }
    .predikat-sangat { background: #dcfce7; color: #166534; }
    .predikat-berhasil { background: #e0f2fe; color: #0369a1; }
    .predikat-cukup { background: #fef9c3; color: #854d0e; }
    .predikat-sedang { background: #ffedd5; color: #9a3412; }
    .predikat-kurang { background: #fee2e2; color: #991b1b; }
  </style>
</head>
<body>

  <!-- Kop Surat Resmi -->
  <div class="kop-surat">
    <h3>PEMERINTAH DAERAH PROVINSI / KABUPATEN</h3>
    <h2>DINAS PENDIDIKAN</h2>
    <p>Sub Bagian Perencanaan, Evaluasi, dan Pelaporan (PEP)</p>
    <p>Jalan Pendidikan No. 01 &bull; Telp. (021) 1234567 &bull; Website: disdik.go.id</p>
  </div>

  <div class="laporan-title">
    <h4>LAPORAN PENGUKURAN CAPAIAN KINERJA DAN KEUANGAN (e-SAKIP)</h4>
    <p>Periode Pelaksanaan: <strong>{{ $triwulan !== 'Semua' ? $triwulan : 'SEMUA TRIWULAN' }}</strong> &bull; Tahun Anggaran: <strong>{{ $tahun }}</strong></p>
  </div>

  <!-- TABEL SESUAI FOTO PERSIS 100% LEBAR LANDSCAPE -->
  <table class="table-laporan">
    <colgroup>
      <col style="width: 2.5%;">
      <col style="width: 15.5%;">
      <col style="width: 14%;">
      <col style="width: 5%;">
      <col style="width: 7.5%;">
      <col style="width: 4.5%;">
      <col style="width: 4.5%;">
      <col style="width: 4.5%;">
      <col style="width: 4.5%;">
      <col style="width: 4.5%;">
      <col style="width: 6.5%;">
      <col style="width: 5.5%;">
      <col style="width: 6%;">
      <col style="width: 7.5%;">
      <col style="width: 5.5%;">
      <col style="width: 6.5%;">
    </colgroup>
    <thead>
      <!-- Baris 1: Tingkat Atas -->
      <tr>
        <th rowspan="3">No</th>
        <th rowspan="3">Tujuan / Sasaran / Program / Kegiatan / Sub Kegiatan</th>
        <th rowspan="3">Indikator Kinerja</th>
        <th colspan="3">Data {{ $tahun }}</th>
        <th colspan="4">Target Kinerja</th>
        <th colspan="3">Capaian Kinerja</th>
        <th colspan="2">Capaian Keuangan</th>
        <th rowspan="3">Bukti Pendukung</th>
      </tr>

      <!-- Baris 2: Kolom Rincian -->
      <tr>
        <!-- Di bawah Data {{ $tahun }} -->
        <th rowspan="2">Target</th>
        <th rowspan="2">Rp</th>
        <th rowspan="2">Satuan</th>
        <!-- Di bawah Target Kinerja -->
        <th rowspan="2">TW I</th>
        <th rowspan="2">TW II</th>
        <th rowspan="2">TW III</th>
        <th rowspan="2">TW IV</th>
        <!-- Di bawah Capaian Kinerja -->
        <th colspan="3">{{ $triwulan !== 'Semua' ? $triwulan : 'TW I' }}</th>
        <!-- Di bawah Capaian Keuangan -->
        <th colspan="2">{{ $triwulan !== 'Semua' ? $triwulan : 'TW I' }}</th>
      </tr>

      <!-- Baris 3: Sub Kolom Capaian Kinerja & Keuangan -->
      <tr>
        <!-- Di bawah Capaian Kinerja -->
        <th>Realisasi</th>
        <th>Capaian (%)</th>
        <th>Predikat</th>
        <!-- Di bawah Capaian Keuangan -->
        <th>Realisasi</th>
        <th>Capaian (%)</th>
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
          <td class="text-left" style="font-weight: bold;">{{ $row->sasaran }}</td>
          <td class="text-left">{{ $row->indikator }}</td>
          <td class="text-right">{{ number_format($row->target_tahunan, 2, ',', '.') }}</td>
          <td class="text-right" style="font-weight: bold;">Rp {{ number_format($row->pagu_anggaran, 0, ',', '.') }}</td>
          <td class="text-center">{{ $row->satuan }}</td>
          <td class="text-right">{{ number_format($row->target_tw1, 2, ',', '.') }}</td>
          <td class="text-right">{{ number_format($row->target_tw2, 2, ',', '.') }}</td>
          <td class="text-right">{{ number_format($row->target_tw3, 2, ',', '.') }}</td>
          <td class="text-right">{{ number_format($row->target_tw4, 2, ',', '.') }}</td>
          <td class="text-right" style="font-weight: bold; color: #c2410c;">{{ number_format($row->realisasi_kinerja, 2, ',', '.') }}</td>
          <td class="text-right" style="font-weight: bold; color: #b91c1c;">{{ number_format($row->capaian_kinerja_persen, 2, ',', '.') }}%</td>
          <td class="text-center">
            <span class="chip-predikat {{ $predCls }}">{{ $row->predikat_kinerja ?? '-' }}</span>
          </td>
          <td class="text-right" style="font-weight: bold;">Rp {{ number_format($row->realisasi_keuangan, 0, ',', '.') }}</td>
          <td class="text-right" style="font-weight: bold; color: #b91c1c;">{{ number_format($row->capaian_keuangan_persen, 2, ',', '.') }}%</td>
          <td class="text-center" style="font-size: 7px;">
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
          <td colspan="16" class="text-center" style="padding: 20px; color: #64748b;">
            Belum ada data capaian kinerja pada periode {{ $triwulan }} Tahun {{ $tahun }}.
          </td>
        </tr>
      @endforelse

      @if(count($data) > 0)
        @php
          $persenTotalKeu = ($totalPagu > 0) ? ($totalRealisasiKeu / $totalPagu) * 100 : 0;
        @endphp
        <tr style="font-weight: bold; background-color: #f8fafc;">
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

</body>
</html>
