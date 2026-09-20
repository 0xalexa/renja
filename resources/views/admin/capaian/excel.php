<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!--[if gte mso 9]>
<xml>
 <x:ExcelWorkbook>
  <x:ExcelWorksheets>
   <x:ExcelWorksheet>
    <x:Name>Capaian Kinerja</x:Name>
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
 </x:ExcelWorkbook>
</xml>
<![endif]-->
<style>
  table { border-collapse: collapse; width: 100%; font-family: Calibri, Arial, sans-serif; font-size: 11pt; }
  th { background-color: #f1f5f9; color: #0f172a; font-weight: bold; border: 1px solid #94a3b8; text-align: center; vertical-align: middle; padding: 6px 8px; }
  td { border: 1px solid #cbd5e1; padding: 5px 8px; vertical-align: middle; }
  .title-header { font-size: 14pt; font-weight: bold; text-align: center; }
  .sub-header { font-size: 11pt; text-align: center; color: #475569; }
  .num-center { text-align: center; }
  .num-right { text-align: right; }
  .text-left { text-align: left; }
  .predikat-sangat { background-color: #dcfce7; color: #166534; font-weight: bold; text-align: center; }
  .predikat-berhasil { background-color: #e0f2fe; color: #0369a1; font-weight: bold; text-align: center; }
  .predikat-cukup { background-color: #fef9c3; color: #854d0e; font-weight: bold; text-align: center; }
  .predikat-sedang { background-color: #ffedd5; color: #9a3412; font-weight: bold; text-align: center; }
  .predikat-kurang { background-color: #fee2e2; color: #991b1b; font-weight: bold; text-align: center; }
</style>
</head>
<body>

<table>
  <tr>
    <td colspan="15" class="title-header">LAPORAN EVALUASI & CAPAIAN KINERJA OPD (e-SAKIP)</td>
  </tr>
  <tr>
    <td colspan="15" class="sub-header">DINAS PENDIDIKAN — TAHUN ANGGARAN <?= $tahun ?> (<?= $triwulan !== 'Semua' ? $triwulan : 'SEMUA TRIWULAN' ?>)</td>
  </tr>
  <tr><td colspan="15"></td></tr>

  <!-- ================= HEADER 3 TIER SESUAI FOTO ================= -->
  <thead>
    <!-- BARIS 1: Header Utama -->
    <tr>
      <th rowspan="3" style="width: 40px;">No</th>
      <th rowspan="3" style="width: 250px;">Tujuan / Sasaran / Program / Kegiatan / Sub Kegiatan</th>
      <th rowspan="3" style="width: 250px;">Indikator Kinerja</th>
      <th colspan="3">Data <?= $tahun ?></th>
      <th colspan="4">Target Kinerja</th>
      <th colspan="3">Capaian Kinerja</th>
      <th colspan="2">Capaian Keuangan</th>
      <th rowspan="3" style="width: 180px;">Bukti Pendukung</th>
    </tr>

    <!-- BARIS 2: Rincian Sub-Grup -->
    <tr>
      <!-- Di bawah Data <?= $tahun ?> -->
      <th rowspan="2" style="width: 90px;">Target</th>
      <th rowspan="2" style="width: 130px;">Rp</th>
      <th rowspan="2" style="width: 80px;">Satuan</th>
      <!-- Di bawah Target Kinerja -->
      <th rowspan="2" style="width: 75px;">TW I</th>
      <th rowspan="2" style="width: 75px;">TW II</th>
      <th rowspan="2" style="width: 75px;">TW III</th>
      <th rowspan="2" style="width: 75px;">TW IV</th>
      <!-- Di bawah Capaian Kinerja -->
      <th colspan="3" style="width: 260px;"><?= $triwulan !== 'Semua' ? $triwulan : 'TW I' ?></th>
      <!-- Di bawah Capaian Keuangan -->
      <th colspan="2" style="width: 200px;"><?= $triwulan !== 'Semua' ? $triwulan : 'TW I' ?></th>
    </tr>

    <!-- BARIS 3: Rincian Capaian Kinerja & Keuangan -->
    <tr>
      <!-- Di bawah Capaian Kinerja -> <?= $triwulan ?> -->
      <th style="width: 90px;">Realisasi</th>
      <th style="width: 80px;">Capaian (%)</th>
      <th style="width: 110px;">Predikat</th>
      <!-- Di bawah Capaian Keuangan -> <?= $triwulan ?> -->
      <th style="width: 120px;">Realisasi</th>
      <th style="width: 80px;">Capaian (%)</th>
    </tr>
  </thead>

  <tbody>
    <?php
      $totalPagu = 0;
      $totalRealisasiKeuangan = 0;
      $count = 0;

      foreach($data as $index => $row):
        $count++;
        $totalPagu += (float)$row->pagu_anggaran;
        $totalRealisasiKeuangan += (float)$row->realisasi_keuangan;

        $predClass = 'predikat-berhasil';
        if ($row->predikat_kinerja === 'Sangat Berhasil') $predClass = 'predikat-sangat';
        elseif ($row->predikat_kinerja === 'Cukup') $predClass = 'predikat-cukup';
        elseif ($row->predikat_kinerja === 'Sedang') $predClass = 'predikat-sedang';
        elseif ($row->predikat_kinerja === 'Kurang') $predClass = 'predikat-kurang';
    ?>
      <tr>
        <td class="num-center"><?= $index + 1 ?></td>
        <td class="text-left"><?= htmlspecialchars($row->sasaran) ?></td>
        <td class="text-left"><?= htmlspecialchars($row->indikator) ?></td>
        <td class="num-right"><?= number_format($row->target_tahunan, 2, ',', '.') ?></td>
        <td class="num-right">Rp <?= number_format($row->pagu_anggaran, 0, ',', '.') ?></td>
        <td class="num-center"><?= htmlspecialchars($row->satuan) ?></td>
        <td class="num-right"><?= number_format($row->target_tw1, 2, ',', '.') ?></td>
        <td class="num-right"><?= number_format($row->target_tw2, 2, ',', '.') ?></td>
        <td class="num-right"><?= number_format($row->target_tw3, 2, ',', '.') ?></td>
        <td class="num-right"><?= number_format($row->target_tw4, 2, ',', '.') ?></td>
        <td class="num-right"><?= number_format($row->realisasi_kinerja, 2, ',', '.') ?></td>
        <td class="num-right"><?= number_format($row->capaian_kinerja_persen, 2, ',', '.') ?>%</td>
        <td class="<?= $predClass ?>"><?= htmlspecialchars($row->predikat_kinerja ?? '-') ?></td>
        <td class="num-right">Rp <?= number_format($row->realisasi_keuangan, 0, ',', '.') ?></td>
        <td class="num-right"><?= number_format($row->capaian_keuangan_persen, 2, ',', '.') ?>%</td>
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
      <?php
        $totalPersenKeu = ($totalPagu > 0) ? ($totalRealisasiKeuangan / $totalPagu) * 100 : 0;
      ?>
      <tr style="font-weight: bold; background-color: #f8fafc;">
        <td colspan="4" class="num-center">TOTAL REKAPITULASI</td>
        <td class="num-right">Rp <?= number_format($totalPagu, 0, ',', '.') ?></td>
        <td colspan="8"></td>
        <td class="num-right">Rp <?= number_format($totalRealisasiKeuangan, 0, ',', '.') ?></td>
        <td class="num-right"><?= number_format($totalPersenKeu, 2, ',', '.') ?>%</td>
        <td></td>
      </tr>
    <?php endif; ?>
  </tbody>
</table>

</body>
</html>
