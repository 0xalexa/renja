<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  @php
    $isSemua = ($triwulan === 'Semua');
    $twLabel = $isSemua ? 'SEMUA TRIWULAN' : $triwulan;
  @endphp
  <title>Laporan Evaluasi Capaian Kinerja {{ $twLabel }} Tahun {{ $tahun }} — SIM-PEP DISDIK</title>
  <style>
    @page {
      size: {{ $isSemua ? 'A3' : 'A4' }} landscape;
      margin: {{ $isSemua ? '6mm 5mm 6mm 5mm' : '7mm 6mm 7mm 6mm' }};
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
      font-size: {{ $isSemua ? '5.5px' : '7px' }};
      width: 100%;
    }
    .kop-surat {
      text-align: center;
      border-bottom: 2px solid #0f172a;
      padding-bottom: 4px;
      margin-bottom: 6px;
    }
    .kop-surat h3 {
      margin: 0;
      font-size: {{ $isSemua ? '10px' : '11px' }};
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .kop-surat h2 {
      margin: 2px 0;
      font-size: {{ $isSemua ? '13px' : '14px' }};
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.8px;
    }
    .kop-surat p {
      margin: 1px 0;
      font-size: {{ $isSemua ? '7.5px' : '9px' }};
      color: #334155;
    }
    .laporan-title {
      text-align: center;
      margin-bottom: 6px;
    }
    .laporan-title h4 {
      margin: 0;
      font-size: {{ $isSemua ? '9.5px' : '11px' }};
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #0f172a;
    }
    .laporan-title p {
      margin: 2px 0 0;
      font-size: {{ $isSemua ? '7.5px' : '9px' }};
      color: #475569;
    }
    
    table.table-laporan {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
      font-size: {{ $isSemua ? '5.5px' : '7px' }};
      word-wrap: break-word;
    }
    table.table-laporan th,
    table.table-laporan td {
      border: 0.5px solid #475569;
      padding: {{ $isSemua ? '1.5px 1px' : '2.5px 1.5px' }};
      vertical-align: middle;
      word-wrap: break-word;
      word-break: break-word;
      overflow: hidden;
    }
    table.table-laporan td a {
      word-break: break-all;
    }
    table.table-laporan thead th {
      background-color: #f1f5f9;
      color: #0f172a;
      font-weight: bold;
      text-align: center;
    }
    .head-numbering th {
      background-color: #e2e8f0;
      font-size: 5px;
      font-weight: bold;
      color: #334155;
      padding: 1px 0.5px;
    }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .text-left { text-align: left; }

    .chip-predikat {
      font-size: {{ $isSemua ? '5.5px' : '7px' }};
      text-align: center;
    }

    .no-print {
      margin-bottom: 8px;
      padding: 8px 12px;
      background: #f8fafc;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .btn-print {
      padding: 5px 12px;
      background: #0284c7;
      color: #ffffff;
      border: none;
      border-radius: 4px;
      font-size: 9px;
      font-weight: bold;
      cursor: pointer;
    }
    @media print {
      .no-print { display: none !important; }
    }
  </style>
</head>
<body>

  @if(empty($isPdf))
  <!-- Tombol Cetak / Simpan PDF Browser (Hanya Tampil di Mode Preview HTML) -->
  <div class="no-print">
    <span style="font-weight: bold; color: #1e293b;">Pratinjau Cetak Evaluasi Capaian Kinerja (Format Landscape)</span>
    <button type="button" class="btn-print" onclick="window.print()">🖨️ Cetak Dokumen / Simpan PDF</button>
  </div>
  @endif

  <!-- Kop Surat Resmi -->
  <div class="kop-surat">
    <h3>PEMERINTAH DAERAH PROVINSI / KABUPATEN</h3>
    <h2>DINAS PENDIDIKAN</h2>
    <p style="font-size: {{ $isSemua ? '8px' : '9.5px' }}; margin: 2px 0; font-weight: 600; color: #334155;">Sub Bagian Perencanaan, Evaluasi, dan Pelaporan (PEP)</p>
    <p style="font-size: {{ $isSemua ? '7px' : '8.5px' }}; margin: 1px 0; color: #64748b;">Jalan Pendidikan No. 01 • Telp. (021) 1234567 • Website: disdik.go.id</p>
  </div>

  <!-- Judul Laporan Resmi e-SAKIP -->
  <div class="laporan-title">
    <h4>LAPORAN PENGUKURAN CAPAIAN KINERJA DAN KEUANGAN (E-SAKIP)</h4>
    <p>Periode Pelaksanaan: {{ strtoupper($twLabel) }} • Tahun Anggaran: {{ $tahun }}</p>
  </div>

@if ($isSemua)
  <!-- ================= TABEL MATRIKS 42 KOLOM RESMI (SEMUA TRIWULAN) ================= -->
  <table class="table-laporan">
    <thead>
      <!-- Baris 1: Header Grup Utama -->
      <tr>
        <th rowspan="3" style="width: 1.5%;">No</th>
        <th rowspan="3" style="width: 8.5%;" class="text-left">Tujuan / Sasaran / Program / Kegiatan</th>
        <th rowspan="3" style="width: 7.5%;" class="text-left">Indikator Kinerja</th>
        <th colspan="6">Data {{ $tahun }}</th>
        <th colspan="4">Target Kinerja</th>
        <th colspan="15">Capaian Kinerja</th>
        <th colspan="10">Capaian Keuangan</th>
        <th colspan="2">Target Akhir RPJMD {{ $tahun }}</th>
        <th colspan="2">Capaian Terhadap Target Akhir Renstra {{ $tahun }}</th>
        <th rowspan="3" style="width: 3.5%;">Bukti</th>
      </tr>

      <!-- Baris 2: Sub-Grup -->
      <tr>
        <th rowspan="2">Target</th>
        <th colspan="4">Rp</th>
        <th rowspan="2">Satuan</th>
        <th rowspan="2">TW I</th>
        <th rowspan="2">TW II</th>
        <th rowspan="2">TW III</th>
        <th rowspan="2">TW IV</th>
        <th colspan="3">TW I</th>
        <th colspan="3">TW II</th>
        <th colspan="3">TW III</th>
        <th colspan="3">TW IV</th>
        <th colspan="3">Total {{ $tahun }}</th>
        <th colspan="2">TW I</th>
        <th colspan="2">TW II</th>
        <th colspan="2">TW III</th>
        <th colspan="2">TW IV</th>
        <th colspan="2">Total {{ $tahun }}</th>
        <th rowspan="2">Kinerja</th>
        <th rowspan="2">Rp</th>
        <th rowspan="2">Kinerja (%)</th>
        <th rowspan="2">Rp (%)</th>
      </tr>

      <!-- Baris 3: Kolom Rincian -->
      <tr>
        <th>TW I</th>
        <th>TW II</th>
        <th>TW III</th>
        <th>TW IV</th>
        <th>Realisasi</th>
        <th>Capaian (%)</th>
        <th>Predikat</th>
        <th>Realisasi</th>
        <th>Capaian (%)</th>
        <th>Predikat</th>
        <th>Realisasi</th>
        <th>Capaian (%)</th>
        <th>Predikat</th>
        <th>Realisasi</th>
        <th>Capaian (%)</th>
        <th>Predikat</th>
        <th>Realisasi</th>
        <th>Capaian (%)</th>
        <th>Predikat</th>
        <th>Realisasi</th>
        <th>Capaian (%)</th>
        <th>Realisasi</th>
        <th>Capaian (%)</th>
        <th>Realisasi</th>
        <th>Capaian (%)</th>
        <th>Realisasi</th>
        <th>Capaian (%)</th>
        <th>Realisasi</th>
        <th>Capaian (%)</th>
      </tr>

      <!-- Baris 4: Penomoran Rumus (1) s/d (42) -->
      <tr class="head-numbering">
        <th>(1)</th>
        <th>(2)</th>
        <th>(3)</th>
        <th>(4)</th>
        <th>(5)</th>
        <th>(6)</th>
        <th>(7)</th>
        <th>(8)</th>
        <th>(9)</th>
        <th>(10)</th>
        <th>(11)</th>
        <th>(12)</th>
        <th>(13)</th>
        <th>(14)</th>
        <th>(15)</th>
        <th>(16)</th>
        <th>(17)</th>
        <th>(18)</th>
        <th>(19)</th>
        <th>(20)</th>
        <th>(21)</th>
        <th>(22)</th>
        <th>(23)</th>
        <th>(24)</th>
        <th>(25)</th>
        <th>(26)</th>
        <th>(27)</th>
        <th>(28)</th>
        <th>(29)</th>
        <th>(30)</th>
        <th>(31)</th>
        <th>(32)</th>
        <th>(33)</th>
        <th>(34)</th>
        <th>(35)</th>
        <th>(36)</th>
        <th>(37)</th>
        <th>(38)</th>
        <th>(39)</th>
        <th>(40)</th>
        <th>(41)</th>
        <th>(42)</th>
        <th>Bukti</th>
      </tr>
    </thead>

    <tbody>
      @php
        $totPagu1 = 0; $totPagu2 = 0; $totPagu3 = 0; $totPagu4 = 0;
        $totKeu1 = 0; $totKeu2 = 0; $totKeu3 = 0; $totKeu4 = 0; $totKeuAll = 0;
      @endphp

      @forelse($data as $index => $row)
        @php
          $p1 = (float)($row->pagu_tw1 ?? 0);
          $p2 = (float)($row->pagu_tw2 ?? 0);
          $p3 = (float)($row->pagu_tw3 ?? 0);
          $p4 = (float)($row->pagu_tw4 ?? 0);
          if ($p1 == 0 && $p2 == 0 && $p3 == 0 && $p4 == 0) {
            $quarter = (float)$row->pagu_anggaran / 4;
            $p1 = $p2 = $p3 = $p4 = $quarter;
          }

          $keu1 = (float)($row->realisasi_keuangan_tw1 ?? $row->realisasi_keuangan ?? 0);
          $keu2 = (float)($row->realisasi_keuangan_tw2 ?? 0);
          $keu3 = (float)($row->realisasi_keuangan_tw3 ?? 0);
          $keu4 = (float)($row->realisasi_keuangan_tw4 ?? 0);
          $keuAll = (float)($row->realisasi_keuangan_total ?: ($keu1 + $keu2 + $keu3 + $keu4));

          $totPagu1 += $p1;
          $totPagu2 += $p2;
          $totPagu3 += $p3;
          $totPagu4 += $p4;
          $totKeu1 += $keu1;
          $totKeu2 += $keu2;
          $totKeu3 += $keu3;
          $totKeu4 += $keu4;
          $totKeuAll += $keuAll;
        @endphp

        <tr>
          <td class="text-center">{{ $index + 1 }}</td>
          <td class="text-left">{{ $row->sasaran }}</td>
          <td class="text-left">{{ $row->indikator }}</td>
          <td class="text-right">{{ number_format($row->target_tahunan, 2, ',', '.') }}</td>
          <td class="text-right">{{ number_format($p1, 0, ',', '.') }}</td>
          <td class="text-right">{{ number_format($p2, 0, ',', '.') }}</td>
          <td class="text-right">{{ number_format($p3, 0, ',', '.') }}</td>
          <td class="text-right">{{ number_format($p4, 0, ',', '.') }}</td>
          <td class="text-center">{{ $row->satuan }}</td>
          <td class="text-right">{{ number_format($row->target_tw1, 2, ',', '.') }}</td>
          <td class="text-right">{{ number_format($row->target_tw2, 2, ',', '.') }}</td>
          <td class="text-right">{{ number_format($row->target_tw3, 2, ',', '.') }}</td>
          <td class="text-right">{{ number_format($row->target_tw4, 2, ',', '.') }}</td>
          <td class="text-right">{{ number_format($row->realisasi_kinerja_tw1 ?? $row->realisasi_kinerja ?? 0, 2, ',', '.') }}</td>
          <td class="text-right">{{ number_format($row->capaian_kinerja_tw1 ?? $row->capaian_kinerja_persen ?? 0, 2, ',', '.') }}%</td>
          <td class="text-center">{{ $row->predikat_kinerja_tw1 ?? $row->predikat_kinerja ?? '-' }}</td>
          <td class="text-right">{{ number_format($row->realisasi_kinerja_tw2 ?? 0, 2, ',', '.') }}</td>
          <td class="text-right">{{ number_format($row->capaian_kinerja_tw2 ?? 0, 2, ',', '.') }}%</td>
          <td class="text-center">{{ $row->predikat_kinerja_tw2 ?? '-' }}</td>
          <td class="text-right">{{ number_format($row->realisasi_kinerja_tw3 ?? 0, 2, ',', '.') }}</td>
          <td class="text-right">{{ number_format($row->capaian_kinerja_tw3 ?? 0, 2, ',', '.') }}%</td>
          <td class="text-center">{{ $row->predikat_kinerja_tw3 ?? '-' }}</td>
          <td class="text-right">{{ number_format($row->realisasi_kinerja_tw4 ?? 0, 2, ',', '.') }}</td>
          <td class="text-right">{{ number_format($row->capaian_kinerja_tw4 ?? 0, 2, ',', '.') }}%</td>
          <td class="text-center">{{ $row->predikat_kinerja_tw4 ?? '-' }}</td>
          <td class="text-right" style="font-weight: bold;">{{ number_format($row->realisasi_kinerja_total ?? 0, 2, ',', '.') }}</td>
          <td class="text-right" style="font-weight: bold;">{{ number_format($row->capaian_kinerja_total ?? 0, 2, ',', '.') }}%</td>
          <td class="text-center">{{ $row->predikat_kinerja_total ?? '-' }}</td>
          <td class="text-right">{{ number_format($keu1, 0, ',', '.') }}</td>
          <td class="text-right">{{ number_format($row->capaian_keuangan_tw1 ?? $row->capaian_keuangan_persen ?? 0, 2, ',', '.') }}%</td>
          <td class="text-right">{{ number_format($keu2, 0, ',', '.') }}</td>
          <td class="text-right">{{ number_format($row->capaian_keuangan_tw2 ?? 0, 2, ',', '.') }}%</td>
          <td class="text-right">{{ number_format($keu3, 0, ',', '.') }}</td>
          <td class="text-right">{{ number_format($row->capaian_keuangan_tw3 ?? 0, 2, ',', '.') }}%</td>
          <td class="text-right">{{ number_format($keu4, 0, ',', '.') }}</td>
          <td class="text-right">{{ number_format($row->capaian_keuangan_tw4 ?? 0, 2, ',', '.') }}%</td>
          <td class="text-right" style="font-weight: bold;">{{ number_format($keuAll, 0, ',', '.') }}</td>
          <td class="text-right" style="font-weight: bold;">{{ number_format($row->capaian_keuangan_total ?? 0, 2, ',', '.') }}%</td>
          <td class="text-right">{{ number_format($row->target_rpjmd_kinerja ?? 0, 2, ',', '.') }}</td>
          <td class="text-right">{{ number_format($row->target_rpjmd_keuangan ?? 0, 0, ',', '.') }}</td>
          <td class="text-right">{{ number_format($row->capaian_renstra_kinerja ?? 0, 2, ',', '.') }}%</td>
          <td class="text-right">{{ number_format($row->capaian_renstra_keuangan ?? 0, 2, ',', '.') }}%</td>
          <td class="text-center" style="font-size: 5.5px;">
            @if($row->bukti_link)
              <a href="{{ $row->bukti_link }}" target="_blank">Link</a>
            @endif
            @if($row->bukti_file_name)
              <div>{{ $row->bukti_file_name }}</div>
            @endif
            @if(!$row->bukti_link && !$row->bukti_file_name)
              -
            @endif
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="43" class="text-center" style="padding: 15px; color: #64748b;">
            Belum ada data capaian kinerja pada periode Tahun {{ $tahun }}.
          </td>
        </tr>
      @endforelse

      @if(count($data) > 0)
        @php
          $totGrandPagu = $totPagu1 + $totPagu2 + $totPagu3 + $totPagu4;
          $totGrandPct = ($totGrandPagu > 0) ? ($totKeuAll / $totGrandPagu) * 100 : 0;
        @endphp
        <tr style="font-weight: bold; background-color: #f8fafc;">
          <td colspan="4" class="text-center">TOTAL REKAPITULASI</td>
          <td class="text-right">{{ number_format($totPagu1, 0, ',', '.') }}</td>
          <td class="text-right">{{ number_format($totPagu2, 0, ',', '.') }}</td>
          <td class="text-right">{{ number_format($totPagu3, 0, ',', '.') }}</td>
          <td class="text-right">{{ number_format($totPagu4, 0, ',', '.') }}</td>
          <td colspan="20"></td>
          <td class="text-right">{{ number_format($totKeu1, 0, ',', '.') }}</td>
          <td></td>
          <td class="text-right">{{ number_format($totKeu2, 0, ',', '.') }}</td>
          <td></td>
          <td class="text-right">{{ number_format($totKeu3, 0, ',', '.') }}</td>
          <td></td>
          <td class="text-right">{{ number_format($totKeu4, 0, ',', '.') }}</td>
          <td></td>
          <td class="text-right">{{ number_format($totKeuAll, 0, ',', '.') }}</td>
          <td class="text-right">{{ number_format($totGrandPct, 2, ',', '.') }}%</td>
          <td colspan="5"></td>
        </tr>
      @endif
    </tbody>
  </table>

@else
  <!-- ================= TABEL FORMAT AWAL 16 KOLOM (TRIWULAN TUNGGAL: TW I / II / III / IV) ================= -->
  <table class="table-laporan">
    <colgroup>
      <col style="width: 2.5%;">
      <col style="width: 17%;">
      <col style="width: 15%;">
      <col style="width: 4.5%;">
      <col style="width: 8%;">
      <col style="width: 4.5%;">
      <col style="width: 4%;">
      <col style="width: 4%;">
      <col style="width: 4%;">
      <col style="width: 4%;">
      <col style="width: 4.5%;">
      <col style="width: 4.5%;">
      <col style="width: 7%;">
      <col style="width: 8%;">
      <col style="width: 4.5%;">
      <col style="width: 4%;">
    </colgroup>
    <thead>
      <!-- Baris 1: Header Grup Utama -->
      <tr>
        <th rowspan="3" style="width: 2.5%;">No</th>
        <th rowspan="3" style="width: 17%;" class="text-left">Tujuan / Sasaran / Program / Kegiatan</th>
        <th rowspan="3" style="width: 15%;" class="text-left">Indikator Kinerja</th>
        <th colspan="3">Data {{ $tahun }}</th>
        <th colspan="4">Target Kinerja</th>
        <th colspan="3">Capaian Kinerja</th>
        <th colspan="2">Capaian Keuangan</th>
        <th rowspan="3" style="width: 4%;">Bukti</th>
      </tr>

      <!-- Baris 2: Sub-Grup -->
      <tr>
        <!-- Data Tahun -->
        <th rowspan="2" style="width: 4.5%;">Target</th>
        <th rowspan="2" style="width: 8%;">Rp</th>
        <th rowspan="2" style="width: 4.5%;">Satuan</th>
        <!-- Target Kinerja -->
        <th rowspan="2" style="width: 4%;">TW I</th>
        <th rowspan="2" style="width: 4%;">TW II</th>
        <th rowspan="2" style="width: 4%;">TW III</th>
        <th rowspan="2" style="width: 4%;">TW IV</th>
        <!-- Capaian Kinerja (Sub TW Dinamis) -->
        <th colspan="3">{{ $triwulan }}</th>
        <!-- Capaian Keuangan (Sub TW Dinamis) -->
        <th colspan="2">{{ $triwulan }}</th>
      </tr>

      <!-- Baris 3: Rincian Realisasi, Persentase, dan Predikat -->
      <tr>
        <th style="width: 4.5%;">Realisasi</th>
        <th style="width: 4.5%;">Capaian (%)</th>
        <th style="width: 7%;">Predikat</th>
        <th style="width: 8%;">Realisasi</th>
        <th style="width: 4.5%;">Capaian (%)</th>
      </tr>
    </thead>

    <tbody>
      @php
        $totPaguTahun = 0;
        $totRealKeuCur = 0;
      @endphp

      @forelse($data as $index => $row)
        @php
          $paguAnggaran = (float)($row->pagu_anggaran ?? 0);
          $totPaguTahun += $paguAnggaran;

          if ($triwulan === 'TW I') {
            $rkCur = (float)($row->realisasi_kinerja_tw1 ?? $row->realisasi_kinerja ?? 0);
            $ckCur = (float)($row->capaian_kinerja_tw1 ?? $row->capaian_kinerja_persen ?? 0);
            $pkCur = $row->predikat_kinerja_tw1 ?: $row->predikat_kinerja ?: '-';
            $rqCur = (float)($row->realisasi_keuangan_tw1 ?? $row->realisasi_keuangan ?? 0);
            $cqCur = (float)($row->capaian_keuangan_tw1 ?? $row->capaian_keuangan_persen ?? 0);
          } elseif ($triwulan === 'TW II') {
            $rkCur = (float)($row->realisasi_kinerja_tw2 ?? 0);
            $ckCur = (float)($row->capaian_kinerja_tw2 ?? 0);
            $pkCur = $row->predikat_kinerja_tw2 ?: '-';
            $rqCur = (float)($row->realisasi_keuangan_tw2 ?? 0);
            $cqCur = (float)($row->capaian_keuangan_tw2 ?? 0);
          } elseif ($triwulan === 'TW III') {
            $rkCur = (float)($row->realisasi_kinerja_tw3 ?? 0);
            $ckCur = (float)($row->capaian_kinerja_tw3 ?? 0);
            $pkCur = $row->predikat_kinerja_tw3 ?: '-';
            $rqCur = (float)($row->realisasi_keuangan_tw3 ?? 0);
            $cqCur = (float)($row->capaian_keuangan_tw3 ?? 0);
          } else { // TW IV
            $rkCur = (float)($row->realisasi_kinerja_tw4 ?? 0);
            $ckCur = (float)($row->capaian_kinerja_tw4 ?? 0);
            $pkCur = $row->predikat_kinerja_tw4 ?: '-';
            $rqCur = (float)($row->realisasi_keuangan_tw4 ?? 0);
            $cqCur = (float)($row->capaian_keuangan_tw4 ?? 0);
          }

          $totRealKeuCur += $rqCur;
        @endphp

        <tr>
          <td class="text-center">{{ $index + 1 }}</td>
          <td class="text-left">{{ $row->sasaran }}</td>
          <td class="text-left">{{ $row->indikator }}</td>
          <td class="text-right">{{ number_format($row->target_tahunan, 2, ',', '.') }}</td>
          <td class="text-right">{{ number_format($paguAnggaran, 0, ',', '.') }}</td>
          <td class="text-center">{{ $row->satuan }}</td>
          <td class="text-right">{{ number_format($row->target_tw1, 2, ',', '.') }}</td>
          <td class="text-right">{{ number_format($row->target_tw2, 2, ',', '.') }}</td>
          <td class="text-right">{{ number_format($row->target_tw3, 2, ',', '.') }}</td>
          <td class="text-right">{{ number_format($row->target_tw4, 2, ',', '.') }}</td>
          <td class="text-right">{{ $rkCur > 0 ? number_format($rkCur, 2, ',', '.') : '-' }}</td>
          <td class="text-right">{{ ($rkCur > 0 || $ckCur > 0) ? number_format($ckCur, 2, ',', '.') . '%' : '-' }}</td>
          <td class="text-center">{{ $pkCur }}</td>
          <td class="text-right">{{ $rqCur > 0 ? number_format($rqCur, 0, ',', '.') : '-' }}</td>
          <td class="text-right">{{ ($rqCur > 0 || $cqCur > 0) ? number_format($cqCur, 2, ',', '.') . '%' : '-' }}</td>
          <td class="text-center" style="font-size: 7px;">
            @if($row->bukti_link)
              <a href="{{ $row->bukti_link }}" target="_blank">Link</a>
            @endif
            @if($row->bukti_file_name)
              <div>{{ $row->bukti_file_name }}</div>
            @endif
            @if(!$row->bukti_link && !$row->bukti_file_name)
              -
            @endif
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="16" class="text-center" style="padding: 15px; color: #64748b;">
            Belum ada data capaian kinerja pada periode Tahun {{ $tahun }}.
          </td>
        </tr>
      @endforelse

      @if(count($data) > 0)
        <tr style="font-weight: bold; background-color: #f8fafc;">
          <td colspan="4" class="text-center">TOTAL REKAPITULASI</td>
          <td class="text-right">{{ number_format($totPaguTahun, 0, ',', '.') }}</td>
          <td colspan="8"></td>
          <td class="text-right">{{ number_format($totRealKeuCur, 0, ',', '.') }}</td>
          <td class="text-right">{{ $totPaguTahun > 0 ? number_format(($totRealKeuCur / $totPaguTahun) * 100, 2, ',', '.') . '%' : '-' }}</td>
          <td></td>
        </tr>
      @endif
    </tbody>
  </table>
@endif

</body>
</html>
