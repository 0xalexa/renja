<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!--[if gte mso 9]>
<xml>
 <x:ExcelWorkbook>
  <x:ExcelWorksheets>
   <x:ExcelWorksheet>
    <x:Name>Capaian Kinerja e-SAKIP</x:Name>
    <x:WorksheetOptions>
      <x:DisplayGridlines/>
      <x:Print>
        <x:ValidPrinterInfo/>
        <x:PaperSizeIndex>9</x:PaperSizeIndex>
        <x:HorizontalResolution>600</x:HorizontalResolution>
        <x:VerticalResolution>600</x:VerticalResolution>
      </x:Print>
    </x:WorksheetOptions>
   </x:ExcelWorksheet>
  </x:ExcelWorksheets>
 </xml>
<![endif]-->
<style>
  table { border-collapse: collapse; width: 100%; font-family: Calibri, Arial, sans-serif; font-size: 10pt; }
  th { background-color: #f1f5f9; color: #0f172a; font-weight: bold; border: 1px solid #94a3b8; text-align: center; vertical-align: middle; padding: 6px 8px; font-size: 9.5pt; }
  td { border: 1px solid #cbd5e1; padding: 5px 8px; vertical-align: middle; }
  .title-header { font-size: 14pt; font-weight: bold; text-align: center; }
  .sub-header { font-size: 11pt; text-align: center; color: #475569; }
  .num-center { text-align: center; }
  .num-right { text-align: right; }
  .text-left { text-align: left; }
  .head-numbering th { background-color: #e2e8f0; color: #334155; font-size: 8.5pt; font-weight: bold; }
  .predikat-sangat { background-color: #dcfce7; color: #166534; font-weight: bold; text-align: center; }
  .predikat-berhasil { background-color: #e0f2fe; color: #0369a1; font-weight: bold; text-align: center; }
  .predikat-cukup { background-color: #fef9c3; color: #854d0e; font-weight: bold; text-align: center; }
  .predikat-sedang { background-color: #ffedd5; color: #9a3412; font-weight: bold; text-align: center; }
  .predikat-kurang { background-color: #fee2e2; color: #991b1b; font-weight: bold; text-align: center; }
</style>
</head>
<body>

<?php
  $isSemua = ($triwulan === 'Semua');
  $twLabel = $isSemua ? 'SEMUA TRIWULAN' : $triwulan;
  $colspanTop = $isSemua ? 43 : 16;
?>

<table>
  <tr>
    <td colspan="<?= $colspanTop ?>" class="title-header">LAPORAN EVALUASI CAPAIAN KINERJA OPD (e-SAKIP)</td>
  </tr>
  <tr>
    <td colspan="<?= $colspanTop ?>" class="sub-header">DINAS PENDIDIKAN — TAHUN ANGGARAN <?= $tahun ?> (<?= strtoupper($twLabel) ?>)</td>
  </tr>
  <tr><td colspan="<?= $colspanTop ?>"></td></tr>

<?php if ($isSemua): ?>
  <!-- ================= HEADER MATRIKS 42 KOLOM RESMI SESUAI FOTO ================= -->
  <thead>
    <!-- BARIS 1: Header Tingkat Atas -->
    <tr>
      <th rowspan="3" style="width: 35px;">No</th>
      <th rowspan="3" style="width: 250px;">Tujuan / Sasaran / Program / Kegiatan / Sub Kegiatan</th>
      <th rowspan="3" style="width: 230px;">Indikator Kinerja</th>
      <th colspan="6">Data <?= $tahun ?></th>
      <th colspan="4">Target Kinerja</th>
      <th colspan="15">Capaian Kinerja</th>
      <th colspan="10">Capaian Keuangan</th>
      <th colspan="2">Target Akhir RPJMD <?= $tahun ?></th>
      <th colspan="2">Capaian Terhadap Target Akhir Renstra <?= $tahun ?></th>
      <th rowspan="3" style="width: 160px;">Bukti Pendukung</th>
    </tr>

    <!-- BARIS 2: Rincian Sub-Grup -->
    <tr>
      <!-- Data <?= $tahun ?> -->
      <th rowspan="2" style="width: 80px;">Target</th>
      <th colspan="4">Rp</th>
      <th rowspan="2" style="width: 70px;">Satuan</th>
      <!-- Target Kinerja -->
      <th rowspan="2" style="width: 70px;">TW I</th>
      <th rowspan="2" style="width: 70px;">TW II</th>
      <th rowspan="2" style="width: 70px;">TW III</th>
      <th rowspan="2" style="width: 70px;">TW IV</th>
      <!-- Capaian Kinerja -->
      <th colspan="3" style="background:#fff7ed;color:#9a3412;">TW I</th>
      <th colspan="3" style="background:#f0fdf4;color:#166534;">TW II</th>
      <th colspan="3" style="background:#f0f9ff;color:#0369a1;">TW III</th>
      <th colspan="3" style="background:#faf5ff;color:#6b21a8;">TW IV</th>
      <th colspan="3" style="background:#fef2f2;color:#991b1b;">Total <?= $tahun ?></th>
      <!-- Capaian Keuangan -->
      <th colspan="2" style="background:#fff7ed;color:#9a3412;">TW I</th>
      <th colspan="2" style="background:#f0fdf4;color:#166534;">TW II</th>
      <th colspan="2" style="background:#f0f9ff;color:#0369a1;">TW III</th>
      <th colspan="2" style="background:#faf5ff;color:#6b21a8;">TW IV</th>
      <th colspan="2" style="background:#fef2f2;color:#991b1b;">Total <?= $tahun ?></th>
      <!-- Target Akhir RPJMD -->
      <th rowspan="2" style="width: 80px;">Kinerja</th>
      <th rowspan="2" style="width: 110px;">Rp</th>
      <!-- Capaian Renstra -->
      <th rowspan="2" style="width: 80px;">Kinerja (%)</th>
      <th rowspan="2" style="width: 80px;">Rp (%)</th>
    </tr>

    <!-- BARIS 3: Rincian Kolom Triwulan & Total -->
    <tr>
      <!-- Pagu Rp Data <?= $tahun ?> -->
      <th style="width: 95px;">TW I</th>
      <th style="width: 95px;">TW II</th>
      <th style="width: 95px;">TW III</th>
      <th style="width: 95px;">TW IV</th>

      <!-- Capaian Kinerja TW I s/d IV + Total -->
      <th style="width: 75px;">Realisasi</th>
      <th style="width: 75px;">Capaian (%)</th>
      <th style="width: 95px;">Predikat</th>
      <th style="width: 75px;">Realisasi</th>
      <th style="width: 75px;">Capaian (%)</th>
      <th style="width: 95px;">Predikat</th>
      <th style="width: 75px;">Realisasi</th>
      <th style="width: 75px;">Capaian (%)</th>
      <th style="width: 95px;">Predikat</th>
      <th style="width: 75px;">Realisasi</th>
      <th style="width: 75px;">Capaian (%)</th>
      <th style="width: 95px;">Predikat</th>
      <th style="width: 75px;">Realisasi</th>
      <th style="width: 75px;">Capaian (%)</th>
      <th style="width: 95px;">Predikat</th>

      <!-- Capaian Keuangan TW I s/d IV + Total -->
      <th style="width: 95px;">Realisasi</th>
      <th style="width: 75px;">Capaian (%)</th>
      <th style="width: 95px;">Realisasi</th>
      <th style="width: 75px;">Capaian (%)</th>
      <th style="width: 95px;">Realisasi</th>
      <th style="width: 75px;">Capaian (%)</th>
      <th style="width: 95px;">Realisasi</th>
      <th style="width: 75px;">Capaian (%)</th>
      <th style="width: 100px;">Realisasi</th>
      <th style="width: 75px;">Capaian (%)</th>
    </tr>

    <!-- BARIS 4: Baris Penomoran Rumus Resmi (1) s/d (42) Persis Foto -->
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
      <th>(15=14/10*100)</th>
      <th>(16)</th>
      <th>(17)</th>
      <th>(18=17/11*100)</th>
      <th>(19)</th>
      <th>(20)</th>
      <th>(21=20/12*100)</th>
      <th>(22)</th>
      <th>(23)</th>
      <th>(24=23/13*100)</th>
      <th>(25)</th>
      <th>(26=14..17..20..23)</th>
      <th>(27=26/4*100)</th>
      <th>(28)</th>
      <th>(29)</th>
      <th>(30=29/5*100)</th>
      <th>(31)</th>
      <th>(32=31/6*100)</th>
      <th>(33)</th>
      <th>(34=33/7*100)</th>
      <th>(35)</th>
      <th>(36=35/8*100)</th>
      <th>(37=29+31+33+35)</th>
      <th>(38=37/(5+6+7+8)*100)</th>
      <th>(39)</th>
      <th>(40)</th>
      <th>(41=26/39*100)</th>
      <th>(42=37/40*100)</th>
      <th>Bukti</th>
    </tr>
  </thead>

  <tbody>
    <?php
      $count = 0;
      $totPaguTw1 = 0; $totPaguTw2 = 0; $totPaguTw3 = 0; $totPaguTw4 = 0;
      $totKeuTw1 = 0; $totKeuTw2 = 0; $totKeuTw3 = 0; $totKeuTw4 = 0; $totKeuTotal = 0;

      foreach($data as $index => $row):
        $count++;
        
        $paguTw1 = (float)($row->pagu_tw1 ?? 0);
        $paguTw2 = (float)($row->pagu_tw2 ?? 0);
        $paguTw3 = (float)($row->pagu_tw3 ?? 0);
        $paguTw4 = (float)($row->pagu_tw4 ?? 0);
        if ($paguTw1 == 0 && $paguTw2 == 0 && $paguTw3 == 0 && $paguTw4 == 0) {
          $quarter = (float)$row->pagu_anggaran / 4;
          $paguTw1 = $paguTw2 = $paguTw3 = $paguTw4 = $quarter;
        }

        $realKeu1 = (float)($row->realisasi_keuangan_tw1 ?? $row->realisasi_keuangan ?? 0);
        $realKeu2 = (float)($row->realisasi_keuangan_tw2 ?? 0);
        $realKeu3 = (float)($row->realisasi_keuangan_tw3 ?? 0);
        $realKeu4 = (float)($row->realisasi_keuangan_tw4 ?? 0);
        $realKeuTot = (float)($row->realisasi_keuangan_total ?: ($realKeu1 + $realKeu2 + $realKeu3 + $realKeu4));

        $totPaguTw1 += $paguTw1;
        $totPaguTw2 += $paguTw2;
        $totPaguTw3 += $paguTw3;
        $totPaguTw4 += $paguTw4;
        $totKeuTw1 += $realKeu1;
        $totKeuTw2 += $realKeu2;
        $totKeuTw3 += $realKeu3;
        $totKeuTw4 += $realKeu4;
        $totKeuTotal += $realKeuTot;

        $getPredClass = function($pred) {
          if ($pred === 'Sangat Tinggi' || $pred === 'Sangat Berhasil') return 'predikat-sangat';
          if ($pred === 'Tinggi' || $pred === 'Berhasil') return 'predikat-berhasil';
          if ($pred === 'Cukup') return 'predikat-cukup';
          if ($pred === 'Sedang') return 'predikat-sedang';
          if ($pred === 'Rendah' || $pred === 'Kurang') return 'predikat-kurang';
          return '';
        };
    ?>
      <tr>
        <td class="num-center"><?= $index + 1 ?></td>
        <td class="text-left"><?= htmlspecialchars($row->sasaran) ?></td>
        <td class="text-left"><?= htmlspecialchars($row->indikator) ?></td>
        <td class="num-right"><?= number_format((float)$row->target_tahunan, 2, ',', '.') ?></td>
        <td class="num-right">Rp <?= number_format($paguTw1, 0, ',', '.') ?></td>
        <td class="num-right">Rp <?= number_format($paguTw2, 0, ',', '.') ?></td>
        <td class="num-right">Rp <?= number_format($paguTw3, 0, ',', '.') ?></td>
        <td class="num-right">Rp <?= number_format($paguTw4, 0, ',', '.') ?></td>
        <td class="num-center"><?= htmlspecialchars($row->satuan) ?></td>
        <td class="num-right"><?= number_format((float)$row->target_tw1, 2, ',', '.') ?></td>
        <td class="num-right"><?= number_format((float)$row->target_tw2, 2, ',', '.') ?></td>
        <td class="num-right"><?= number_format((float)$row->target_tw3, 2, ',', '.') ?></td>
        <td class="num-right"><?= number_format((float)$row->target_tw4, 2, ',', '.') ?></td>
        <td class="num-right"><?= number_format((float)($row->realisasi_kinerja_tw1 ?? $row->realisasi_kinerja ?? 0), 2, ',', '.') ?></td>
        <td class="num-right"><?= number_format((float)($row->capaian_kinerja_tw1 ?? $row->capaian_kinerja_persen ?? 0), 2, ',', '.') ?>%</td>
        <td class="<?= $getPredClass($row->predikat_kinerja_tw1 ?: $row->predikat_kinerja) ?>"><?= htmlspecialchars($row->predikat_kinerja_tw1 ?: $row->predikat_kinerja ?: '-') ?></td>
        <td class="num-right"><?= number_format((float)($row->realisasi_kinerja_tw2 ?? 0), 2, ',', '.') ?></td>
        <td class="num-right"><?= number_format((float)($row->capaian_kinerja_tw2 ?? 0), 2, ',', '.') ?>%</td>
        <td class="<?= $getPredClass($row->predikat_kinerja_tw2) ?>"><?= htmlspecialchars($row->predikat_kinerja_tw2 ?: '-') ?></td>
        <td class="num-right"><?= number_format((float)($row->realisasi_kinerja_tw3 ?? 0), 2, ',', '.') ?></td>
        <td class="num-right"><?= number_format((float)($row->capaian_kinerja_tw3 ?? 0), 2, ',', '.') ?>%</td>
        <td class="<?= $getPredClass($row->predikat_kinerja_tw3) ?>"><?= htmlspecialchars($row->predikat_kinerja_tw3 ?: '-') ?></td>
        <td class="num-right"><?= number_format((float)($row->realisasi_kinerja_tw4 ?? 0), 2, ',', '.') ?></td>
        <td class="num-right"><?= number_format((float)($row->capaian_kinerja_tw4 ?? 0), 2, ',', '.') ?>%</td>
        <td class="<?= $getPredClass($row->predikat_kinerja_tw4) ?>"><?= htmlspecialchars($row->predikat_kinerja_tw4 ?: '-') ?></td>
        <td class="num-right"><?= number_format((float)($row->realisasi_kinerja_total ?? 0), 2, ',', '.') ?></td>
        <td class="num-right"><?= number_format((float)($row->capaian_kinerja_total ?? 0), 2, ',', '.') ?>%</td>
        <td class="<?= $getPredClass($row->predikat_kinerja_total) ?>"><?= htmlspecialchars($row->predikat_kinerja_total ?: '-') ?></td>
        <td class="num-right">Rp <?= number_format($realKeu1, 0, ',', '.') ?></td>
        <td class="num-right"><?= number_format((float)($row->capaian_keuangan_tw1 ?? $row->capaian_keuangan_persen ?? 0), 2, ',', '.') ?>%</td>
        <td class="num-right">Rp <?= number_format($realKeu2, 0, ',', '.') ?></td>
        <td class="num-right"><?= number_format((float)($row->capaian_keuangan_tw2 ?? 0), 2, ',', '.') ?>%</td>
        <td class="num-right">Rp <?= number_format($realKeu3, 0, ',', '.') ?></td>
        <td class="num-right"><?= number_format((float)($row->capaian_keuangan_tw3 ?? 0), 2, ',', '.') ?>%</td>
        <td class="num-right">Rp <?= number_format($realKeu4, 0, ',', '.') ?></td>
        <td class="num-right"><?= number_format((float)($row->capaian_keuangan_tw4 ?? 0), 2, ',', '.') ?>%</td>
        <td class="num-right">Rp <?= number_format($realKeuTot, 0, ',', '.') ?></td>
        <td class="num-right"><?= number_format((float)($row->capaian_keuangan_total ?? 0), 2, ',', '.') ?>%</td>
        <td class="num-right"><?= number_format((float)($row->target_rpjmd_kinerja ?? 0), 2, ',', '.') ?></td>
        <td class="num-right">Rp <?= number_format((float)($row->target_rpjmd_keuangan ?? 0), 0, ',', '.') ?></td>
        <td class="num-right"><?= number_format((float)($row->capaian_renstra_kinerja ?? 0), 2, ',', '.') ?>%</td>
        <td class="num-right"><?= number_format((float)($row->capaian_renstra_keuangan ?? 0), 2, ',', '.') ?>%</td>
        <td class="text-left"><?= htmlspecialchars($row->bukti_link ? ($row->bukti_link . ($row->bukti_file_name ? ' (' . $row->bukti_file_name . ')' : '')) : ($row->bukti_file_name ?: '-')) ?></td>
      </tr>
    <?php endforeach; ?>

    <?php if($count === 0): ?>
      <tr>
        <td colspan="43" class="num-center" style="padding: 20px; color: #64748b;">
          Tidak ada data capaian kinerja untuk periode ini.
        </td>
      </tr>
    <?php else: ?>
      <?php
        $totPaguGrand = $totPaguTw1 + $totPaguTw2 + $totPaguTw3 + $totPaguTw4;
        $pctKeuGrand = ($totPaguGrand > 0) ? ($totKeuTotal / $totPaguGrand) * 100 : 0;
      ?>
      <tr style="font-weight: bold; background-color: #f8fafc;">
        <td colspan="4" class="num-center">TOTAL REKAPITULASI</td>
        <td class="num-right">Rp <?= number_format($totPaguTw1, 0, ',', '.') ?></td>
        <td class="num-right">Rp <?= number_format($totPaguTw2, 0, ',', '.') ?></td>
        <td class="num-right">Rp <?= number_format($totPaguTw3, 0, ',', '.') ?></td>
        <td class="num-right">Rp <?= number_format($totPaguTw4, 0, ',', '.') ?></td>
        <td colspan="20"></td>
        <td class="num-right">Rp <?= number_format($totKeuTw1, 0, ',', '.') ?></td>
        <td></td>
        <td class="num-right">Rp <?= number_format($totKeuTw2, 0, ',', '.') ?></td>
        <td></td>
        <td class="num-right">Rp <?= number_format($totKeuTw3, 0, ',', '.') ?></td>
        <td></td>
        <td class="num-right">Rp <?= number_format($totKeuTw4, 0, ',', '.') ?></td>
        <td></td>
        <td class="num-right">Rp <?= number_format($totKeuTotal, 0, ',', '.') ?></td>
        <td class="num-right"><?= number_format($pctKeuGrand, 2, ',', '.') ?>%</td>
        <td colspan="5"></td>
      </tr>
    <?php endif; ?>
  </tbody>

<?php else: ?>
  <!-- ================= HEADER FORMAT AWAL 16 KOLOM (TRIWULAN TUNGGAL: TW I / II / III / IV) ================= -->
  <thead>
    <!-- Baris 1: Header Grup Utama -->
    <tr>
      <th rowspan="3" style="width:35px;">No</th>
      <th rowspan="3" style="width:250px;">Tujuan / Sasaran / Program / Kegiatan / Sub Kegiatan</th>
      <th rowspan="3" style="width:230px;">Indikator Kinerja</th>
      <th colspan="3">Data <?= $tahun ?></th>
      <th colspan="4">Target Kinerja</th>
      <th colspan="3">Capaian Kinerja</th>
      <th colspan="2">Capaian Keuangan</th>
      <th rowspan="3" style="width:160px;">Bukti Pendukung</th>
    </tr>

    <!-- Baris 2: Kolom Rincian & Sub Header Triwulan -->
    <tr>
      <!-- Data Tahun -->
      <th rowspan="2" style="width:80px;">Target</th>
      <th rowspan="2" style="width:120px;">Rp</th>
      <th rowspan="2" style="width:70px;">Satuan</th>
      <!-- Target Kinerja -->
      <th rowspan="2" style="width:70px;">TW I</th>
      <th rowspan="2" style="width:70px;">TW II</th>
      <th rowspan="2" style="width:70px;">TW III</th>
      <th rowspan="2" style="width:70px;">TW IV</th>
      <!-- Capaian Kinerja (Sub TW Dinamis) -->
      <th colspan="3" style="background:#fff7ed;color:#9a3412;"><?= $triwulan ?></th>
      <!-- Capaian Keuangan (Sub TW Dinamis) -->
      <th colspan="2" style="background:#fff7ed;color:#9a3412;"><?= $triwulan ?></th>
    </tr>

    <!-- Baris 3: Rincian Realisasi, Persentase, dan Predikat -->
    <tr>
      <!-- Capaian Kinerja -->
      <th style="width:75px;">Realisasi</th>
      <th style="width:75px;">Capaian (%)</th>
      <th style="width:95px;">Predikat</th>
      <!-- Capaian Keuangan -->
      <th style="width:110px;">Realisasi</th>
      <th style="width:75px;">Capaian (%)</th>
    </tr>
  </thead>

  <tbody>
    <?php
      $count = 0;
      $totPaguTahun = 0;
      $totRealKeuCur = 0;

      $getPredClass = function($pred) {
        if ($pred === 'Sangat Tinggi' || $pred === 'Sangat Berhasil') return 'predikat-sangat';
        if ($pred === 'Tinggi' || $pred === 'Berhasil') return 'predikat-berhasil';
        if ($pred === 'Cukup') return 'predikat-cukup';
        if ($pred === 'Sedang') return 'predikat-sedang';
        if ($pred === 'Rendah' || $pred === 'Kurang') return 'predikat-kurang';
        return '';
      };

      foreach($data as $index => $row):
        $count++;
        $paguAnggaran = (float)($row->pagu_anggaran ?? 0);
        $totPaguTahun += $paguAnggaran;

        // Ambil data spesifik sesuai triwulan yang dipilih
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
    ?>
      <tr>
        <td class="num-center"><?= $index + 1 ?></td>
        <td class="text-left"><?= htmlspecialchars($row->sasaran) ?></td>
        <td class="text-left"><?= htmlspecialchars($row->indikator) ?></td>
        <td class="num-right"><?= number_format((float)$row->target_tahunan, 2, ',', '.') ?></td>
        <td class="num-right">Rp <?= number_format($paguAnggaran, 0, ',', '.') ?></td>
        <td class="num-center"><?= htmlspecialchars($row->satuan) ?></td>
        <td class="num-right"><?= number_format((float)$row->target_tw1, 2, ',', '.') ?></td>
        <td class="num-right"><?= number_format((float)$row->target_tw2, 2, ',', '.') ?></td>
        <td class="num-right"><?= number_format((float)$row->target_tw3, 2, ',', '.') ?></td>
        <td class="num-right"><?= number_format((float)$row->target_tw4, 2, ',', '.') ?></td>
        <td class="num-right"><?= $rkCur > 0 ? number_format($rkCur, 2, ',', '.') : '-' ?></td>
        <td class="num-right"><?= ($rkCur > 0 || $ckCur > 0) ? number_format($ckCur, 2, ',', '.') . '%' : '-' ?></td>
        <td class="<?= $getPredClass($pkCur) ?>"><?= htmlspecialchars($pkCur) ?></td>
        <td class="num-right"><?= $rqCur > 0 ? 'Rp ' . number_format($rqCur, 0, ',', '.') : '-' ?></td>
        <td class="num-right"><?= ($rqCur > 0 || $cqCur > 0) ? number_format($cqCur, 2, ',', '.') . '%' : '-' ?></td>
        <td class="text-left"><?= htmlspecialchars($row->bukti_link ? ($row->bukti_link . ($row->bukti_file_name ? ' (' . $row->bukti_file_name . ')' : '')) : ($row->bukti_file_name ?: '-')) ?></td>
      </tr>
    <?php endforeach; ?>

    <?php if($count === 0): ?>
      <tr>
        <td colspan="16" class="num-center" style="padding: 20px; color: #64748b;">
          Tidak ada data capaian kinerja untuk periode ini.
        </td>
      </tr>
    <?php else: ?>
      <tr style="font-weight: bold; background-color: #f8fafc;">
        <td colspan="4" class="num-center">TOTAL REKAPITULASI</td>
        <td class="num-right">Rp <?= number_format($totPaguTahun, 0, ',', '.') ?></td>
        <td colspan="8"></td>
        <td class="num-right">Rp <?= number_format($totRealKeuCur, 0, ',', '.') ?></td>
        <td class="num-right"><?= $totPaguTahun > 0 ? number_format(($totRealKeuCur / $totPaguTahun) * 100, 2, ',', '.') . '%' : '-' ?></td>
        <td></td>
      </tr>
    <?php endif; ?>
  </tbody>
<?php endif; ?>

</table>

</body>
</html>
