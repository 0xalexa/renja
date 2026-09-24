<?php

namespace App\Http\Controllers;

use App\Models\CapaianKinerja;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class CapaianKinerjaController extends Controller
{
    /**
     * Ambil data capaian kinerja berdasarkan filter tahun & triwulan (JSON API)
     */
    public function index(Request $request)
    {
        $tahun = $request->query('tahun', 2026);
        $data = CapaianKinerja::where('tahun', $tahun)->orderBy('id', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Helper pembersih angka desimal fleksibel
     */
    private function cleanFlexibleNumber($val)
    {
        if ($val === null || $val === '') return 0.0;
        if (is_numeric($val)) return (float) $val;
        $clean = trim((string) $val);
        $clean = preg_replace('/[^\d,\.-]/', '', $clean);
        if (strpos($clean, '.') !== false && strpos($clean, ',') !== false) {
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        } else {
            $clean = str_replace(',', '.', $clean);
        }
        return is_numeric($clean) ? (float) $clean : 0.0;
    }

    /**
     * Helper pembersih format mata uang Rupiah
     */
    private function cleanRupiahNumber($val)
    {
        if ($val === null || $val === '') return 0.0;
        if (is_numeric($val)) return (float) $val;
        $clean = trim((string) $val);
        $clean = preg_replace('/[^\d]/', '', $clean);
        return is_numeric($clean) ? (float) $clean : 0.0;
    }

    /**
     * Helper penentu predikat kinerja otomatis (Permendagri 86 / e-SAKIP)
     */
    private function determinePredikat($persen)
    {
        if ($persen >= 90.0) return 'Sangat Tinggi';
        if ($persen >= 75.0) return 'Tinggi';
        if ($persen >= 60.0) return 'Sedang';
        if ($persen >= 50.0) return 'Kurang';
        return 'Sangat Kurang';
    }

    /**
     * Simpan data capaian kinerja baru
     */
    public function store(Request $request)
    {
        // Bersihkan seluruh field keuangan
        $rupiahFields = [
            'pagu_anggaran', 'pagu_tw1', 'pagu_tw2', 'pagu_tw3', 'pagu_tw4',
            'realisasi_keuangan', 'realisasi_keuangan_tw1', 'realisasi_keuangan_tw2',
            'realisasi_keuangan_tw3', 'realisasi_keuangan_tw4', 'realisasi_keuangan_total',
            'target_rpjmd_keuangan'
        ];
        $mergeData = [];
        foreach ($rupiahFields as $field) {
            if ($request->has($field)) {
                $mergeData[$field] = $this->cleanRupiahNumber($request->input($field));
            }
        }

        // Bersihkan field numerik desimal
        $decimalFields = [
            'target_tahunan', 'target_tw1', 'target_tw2', 'target_tw3', 'target_tw4',
            'realisasi_kinerja', 'capaian_kinerja_persen', 'capaian_keuangan_persen',
            'realisasi_kinerja_tw1', 'capaian_kinerja_tw1',
            'realisasi_kinerja_tw2', 'capaian_kinerja_tw2',
            'realisasi_kinerja_tw3', 'capaian_kinerja_tw3',
            'realisasi_kinerja_tw4', 'capaian_kinerja_tw4',
            'realisasi_kinerja_total', 'capaian_kinerja_total',
            'capaian_keuangan_tw1', 'capaian_keuangan_tw2',
            'capaian_keuangan_tw3', 'capaian_keuangan_tw4', 'capaian_keuangan_total',
            'target_rpjmd_kinerja', 'capaian_renstra_kinerja', 'capaian_renstra_keuangan'
        ];
        foreach ($decimalFields as $field) {
            if ($request->has($field)) {
                $mergeData[$field] = $this->cleanFlexibleNumber($request->input($field));
            }
        }
        if (!empty($mergeData)) {
            $request->merge($mergeData);
        }

        $validated = $request->validate([
            'tahun' => 'nullable|integer',
            'triwulan' => 'nullable|string',
            'sasaran' => 'nullable|string',
            'indikator' => 'nullable|string',
            'satuan' => 'nullable|string',
            'target_tahunan' => 'nullable|numeric',
            'pagu_anggaran' => 'nullable|numeric',
            'pagu_tw1' => 'nullable|numeric',
            'pagu_tw2' => 'nullable|numeric',
            'pagu_tw3' => 'nullable|numeric',
            'pagu_tw4' => 'nullable|numeric',
            'target_tw1' => 'nullable|numeric',
            'target_tw2' => 'nullable|numeric',
            'target_tw3' => 'nullable|numeric',
            'target_tw4' => 'nullable|numeric',
            'realisasi_kinerja_tw1' => 'nullable|numeric',
            'capaian_kinerja_tw1' => 'nullable|numeric',
            'predikat_kinerja_tw1' => 'nullable|string',
            'realisasi_kinerja_tw2' => 'nullable|numeric',
            'capaian_kinerja_tw2' => 'nullable|numeric',
            'predikat_kinerja_tw2' => 'nullable|string',
            'realisasi_kinerja_tw3' => 'nullable|numeric',
            'capaian_kinerja_tw3' => 'nullable|numeric',
            'predikat_kinerja_tw3' => 'nullable|string',
            'realisasi_kinerja_tw4' => 'nullable|numeric',
            'capaian_kinerja_tw4' => 'nullable|numeric',
            'predikat_kinerja_tw4' => 'nullable|string',
            'realisasi_kinerja_total' => 'nullable|numeric',
            'capaian_kinerja_total' => 'nullable|numeric',
            'predikat_kinerja_total' => 'nullable|string',
            'realisasi_keuangan_tw1' => 'nullable|numeric',
            'capaian_keuangan_tw1' => 'nullable|numeric',
            'realisasi_keuangan_tw2' => 'nullable|numeric',
            'capaian_keuangan_tw2' => 'nullable|numeric',
            'realisasi_keuangan_tw3' => 'nullable|numeric',
            'capaian_keuangan_tw3' => 'nullable|numeric',
            'realisasi_keuangan_tw4' => 'nullable|numeric',
            'capaian_keuangan_tw4' => 'nullable|numeric',
            'realisasi_keuangan_total' => 'nullable|numeric',
            'capaian_keuangan_total' => 'nullable|numeric',
            'target_rpjmd_kinerja' => 'nullable|numeric',
            'target_rpjmd_keuangan' => 'nullable|numeric',
            'capaian_renstra_kinerja' => 'nullable|numeric',
            'capaian_renstra_keuangan' => 'nullable|numeric',
            'bukti_link' => 'nullable|string',
            'bukti_keterangan' => 'nullable|string',
            'bukti_file' => 'nullable|file|max:30720',
        ]);

        $tahun = (int) ($validated['tahun'] ?? date('Y'));
        $triwulan = !empty($validated['triwulan']) ? $validated['triwulan'] : 'Semua';
        $sasaran = !empty(trim($validated['sasaran'] ?? '')) ? $validated['sasaran'] : '-';
        $indikator = !empty(trim($validated['indikator'] ?? '')) ? $validated['indikator'] : '-';
        $satuan = !empty(trim($validated['satuan'] ?? '')) ? $validated['satuan'] : '-';
        $targetTahunan = (float) ($validated['target_tahunan'] ?? 0);
        $paguAnggaran = (float) ($validated['pagu_anggaran'] ?? 0);

        // Pagu per TW
        $paguTw1 = (float) ($validated['pagu_tw1'] ?? 0);
        $paguTw2 = (float) ($validated['pagu_tw2'] ?? 0);
        $paguTw3 = (float) ($validated['pagu_tw3'] ?? 0);
        $paguTw4 = (float) ($validated['pagu_tw4'] ?? 0);
        if ($paguAnggaran <= 0 && ($paguTw1 + $paguTw2 + $paguTw3 + $paguTw4) > 0) {
            $paguAnggaran = $paguTw1 + $paguTw2 + $paguTw3 + $paguTw4;
        }

        // Target Kinerja Fisik
        $tw1 = (float) ($validated['target_tw1'] ?? 0);
        $tw2 = (float) ($validated['target_tw2'] ?? 0);
        $tw3 = (float) ($validated['target_tw3'] ?? 0);
        $tw4 = (float) ($validated['target_tw4'] ?? 0);

        // Realisasi & Capaian Kinerja Fisik TW 1 - 4
        $rKin1 = (float) ($validated['realisasi_kinerja_tw1'] ?? 0);
        $cKin1 = $request->filled('capaian_kinerja_tw1') ? (float)$request->input('capaian_kinerja_tw1') : ($tw1 > 0 ? round(($rKin1 / $tw1) * 100, 2) : 0);
        $pKin1 = $request->filled('predikat_kinerja_tw1') ? $request->input('predikat_kinerja_tw1') : ($rKin1 > 0 ? $this->determinePredikat($cKin1) : '-');

        $rKin2 = (float) ($validated['realisasi_kinerja_tw2'] ?? 0);
        $cKin2 = $request->filled('capaian_kinerja_tw2') ? (float)$request->input('capaian_kinerja_tw2') : ($tw2 > 0 ? round(($rKin2 / $tw2) * 100, 2) : 0);
        $pKin2 = $request->filled('predikat_kinerja_tw2') ? $request->input('predikat_kinerja_tw2') : ($rKin2 > 0 ? $this->determinePredikat($cKin2) : '-');

        $rKin3 = (float) ($validated['realisasi_kinerja_tw3'] ?? 0);
        $cKin3 = $request->filled('capaian_kinerja_tw3') ? (float)$request->input('capaian_kinerja_tw3') : ($tw3 > 0 ? round(($rKin3 / $tw3) * 100, 2) : 0);
        $pKin3 = $request->filled('predikat_kinerja_tw3') ? $request->input('predikat_kinerja_tw3') : ($rKin3 > 0 ? $this->determinePredikat($cKin3) : '-');

        $rKin4 = (float) ($validated['realisasi_kinerja_tw4'] ?? 0);
        $cKin4 = $request->filled('capaian_kinerja_tw4') ? (float)$request->input('capaian_kinerja_tw4') : ($tw4 > 0 ? round(($rKin4 / $tw4) * 100, 2) : 0);
        $pKin4 = $request->filled('predikat_kinerja_tw4') ? $request->input('predikat_kinerja_tw4') : ($rKin4 > 0 ? $this->determinePredikat($cKin4) : '-');

        $rKinTot = $request->filled('realisasi_kinerja_total') ? (float)$request->input('realisasi_kinerja_total') : ($rKin1 + $rKin2 + $rKin3 + $rKin4);
        $cKinTot = $request->filled('capaian_kinerja_total') ? (float)$request->input('capaian_kinerja_total') : ($targetTahunan > 0 ? round(($rKinTot / $targetTahunan) * 100, 2) : 0);
        $pKinTot = $request->filled('predikat_kinerja_total') ? $request->input('predikat_kinerja_total') : ($rKinTot > 0 ? $this->determinePredikat($cKinTot) : '-');

        // Realisasi & Capaian Keuangan TW 1 - 4: (Realisasi Keuangan / Pagu Anggaran) * 100%
        $rKeu1 = (float) ($validated['realisasi_keuangan_tw1'] ?? 0);
        $cKeu1 = $request->filled('capaian_keuangan_tw1') ? (float)$request->input('capaian_keuangan_tw1') : ($paguAnggaran > 0 ? round(($rKeu1 / $paguAnggaran) * 100, 2) : 0);

        $rKeu2 = (float) ($validated['realisasi_keuangan_tw2'] ?? 0);
        $cKeu2 = $request->filled('capaian_keuangan_tw2') ? (float)$request->input('capaian_keuangan_tw2') : ($paguAnggaran > 0 ? round(($rKeu2 / $paguAnggaran) * 100, 2) : 0);

        $rKeu3 = (float) ($validated['realisasi_keuangan_tw3'] ?? 0);
        $cKeu3 = $request->filled('capaian_keuangan_tw3') ? (float)$request->input('capaian_keuangan_tw3') : ($paguAnggaran > 0 ? round(($rKeu3 / $paguAnggaran) * 100, 2) : 0);

        $rKeu4 = (float) ($validated['realisasi_keuangan_tw4'] ?? 0);
        $cKeu4 = $request->filled('capaian_keuangan_tw4') ? (float)$request->input('capaian_keuangan_tw4') : ($paguAnggaran > 0 ? round(($rKeu4 / $paguAnggaran) * 100, 2) : 0);

        $rKeuTot = $request->filled('realisasi_keuangan_total') ? (float)$request->input('realisasi_keuangan_total') : ($rKeu1 + $rKeu2 + $rKeu3 + $rKeu4);
        $cKeuTot = $request->filled('capaian_keuangan_total') ? (float)$request->input('capaian_keuangan_total') : ($paguAnggaran > 0 ? round(($rKeuTot / $paguAnggaran) * 100, 2) : 0);

        // RPJMD & Renstra
        $rpjmdKin = (float) ($validated['target_rpjmd_kinerja'] ?? 0);
        $rpjmdKeu = (float) ($validated['target_rpjmd_keuangan'] ?? 0);
        $cRenKin = $request->filled('capaian_renstra_kinerja') ? (float)$request->input('capaian_renstra_kinerja') : ($rpjmdKin > 0 ? round(($rKinTot / $rpjmdKin) * 100, 2) : 0);
        $cRenKeu = $request->filled('capaian_renstra_keuangan') ? (float)$request->input('capaian_renstra_keuangan') : ($rpjmdKeu > 0 ? round(($rKeuTot / $rpjmdKeu) * 100, 2) : 0);

        // Handle File Upload
        $fileName = null;
        $filePath = null;
        $fileSize = null;

        if ($request->hasFile('bukti_file')) {
            $file = $request->file('bukti_file');
            $originalName = $file->getClientOriginalName();
            $fileName = $originalName;
            $fileSize = round($file->getSize() / 1024, 1) . ' KB';
            if ($file->getSize() >= 1048576) {
                $fileSize = round($file->getSize() / 1048576, 2) . ' MB';
            }

            $folderDest = 'uploads/capaian/' . $tahun;
            $targetDir = public_path($folderDest);
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            $storedName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);
            $file->move($targetDir, $storedName);
            $filePath = $folderDest . '/' . $storedName;
        }

        $link = $validated['bukti_link'] ?? null;
        $statusBukti = ($fileName && $link) ? 'Lengkap' : ($fileName ? 'Ada Berkas' : ($link ? 'Ada Link' : (!empty($validated['bukti_keterangan']) ? 'Catatan' : 'Belum Ada')));

        $record = CapaianKinerja::create([
            'tahun' => $tahun,
            'triwulan' => $triwulan,
            'sasaran' => $sasaran,
            'indikator' => $indikator,
            'satuan' => $satuan,
            'target_tahunan' => $targetTahunan,
            'pagu_anggaran' => $paguAnggaran,
            'pagu_tw1' => $paguTw1,
            'pagu_tw2' => $paguTw2,
            'pagu_tw3' => $paguTw3,
            'pagu_tw4' => $paguTw4,
            'target_tw1' => $tw1,
            'target_tw2' => $tw2,
            'target_tw3' => $tw3,
            'target_tw4' => $tw4,
            // Fallback backward compatibility
            'realisasi_kinerja' => $rKinTot,
            'capaian_kinerja_persen' => $cKinTot,
            'predikat_kinerja' => $pKinTot,
            'realisasi_keuangan' => $rKeuTot,
            'capaian_keuangan_persen' => $cKeuTot,
            // Detail Kolom Sesuai Foto
            'realisasi_kinerja_tw1' => $rKin1,
            'capaian_kinerja_tw1' => $cKin1,
            'predikat_kinerja_tw1' => $pKin1,
            'realisasi_kinerja_tw2' => $rKin2,
            'capaian_kinerja_tw2' => $cKin2,
            'predikat_kinerja_tw2' => $pKin2,
            'realisasi_kinerja_tw3' => $rKin3,
            'capaian_kinerja_tw3' => $cKin3,
            'predikat_kinerja_tw3' => $pKin3,
            'realisasi_kinerja_tw4' => $rKin4,
            'capaian_kinerja_tw4' => $cKin4,
            'predikat_kinerja_tw4' => $pKin4,
            'realisasi_kinerja_total' => $rKinTot,
            'capaian_kinerja_total' => $cKinTot,
            'predikat_kinerja_total' => $pKinTot,
            'realisasi_keuangan_tw1' => $rKeu1,
            'capaian_keuangan_tw1' => $cKeu1,
            'realisasi_keuangan_tw2' => $rKeu2,
            'capaian_keuangan_tw2' => $cKeu2,
            'realisasi_keuangan_tw3' => $rKeu3,
            'capaian_keuangan_tw3' => $cKeu3,
            'realisasi_keuangan_tw4' => $rKeu4,
            'capaian_keuangan_tw4' => $cKeu4,
            'realisasi_keuangan_total' => $rKeuTot,
            'capaian_keuangan_total' => $cKeuTot,
            'target_rpjmd_kinerja' => $rpjmdKin,
            'target_rpjmd_keuangan' => $rpjmdKeu,
            'capaian_renstra_kinerja' => $cRenKin,
            'capaian_renstra_keuangan' => $cRenKeu,
            'bukti_link' => $link,
            'bukti_file_name' => $fileName,
            'bukti_file_path' => $filePath,
            'bukti_file_size' => $fileSize,
            'bukti_keterangan' => $validated['bukti_keterangan'] ?? null,
            'status_bukti' => $statusBukti,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Capaian Kinerja berhasil disimpan.',
            'data' => $record,
        ]);
    }

    /**
     * Perbarui data capaian kinerja
     */
    public function update(Request $request, $id)
    {
        $record = CapaianKinerja::findOrFail($id);

        $rupiahFields = [
            'pagu_anggaran', 'pagu_tw1', 'pagu_tw2', 'pagu_tw3', 'pagu_tw4',
            'realisasi_keuangan', 'realisasi_keuangan_tw1', 'realisasi_keuangan_tw2',
            'realisasi_keuangan_tw3', 'realisasi_keuangan_tw4', 'realisasi_keuangan_total',
            'target_rpjmd_keuangan'
        ];
        $mergeData = [];
        foreach ($rupiahFields as $field) {
            if ($request->has($field)) {
                $mergeData[$field] = $this->cleanRupiahNumber($request->input($field));
            }
        }

        $decimalFields = [
            'target_tahunan', 'target_tw1', 'target_tw2', 'target_tw3', 'target_tw4',
            'realisasi_kinerja', 'capaian_kinerja_persen', 'capaian_keuangan_persen',
            'realisasi_kinerja_tw1', 'capaian_kinerja_tw1',
            'realisasi_kinerja_tw2', 'capaian_kinerja_tw2',
            'realisasi_kinerja_tw3', 'capaian_kinerja_tw3',
            'realisasi_kinerja_tw4', 'capaian_kinerja_tw4',
            'realisasi_kinerja_total', 'capaian_kinerja_total',
            'capaian_keuangan_tw1', 'capaian_keuangan_tw2',
            'capaian_keuangan_tw3', 'capaian_keuangan_tw4', 'capaian_keuangan_total',
            'target_rpjmd_kinerja', 'capaian_renstra_kinerja', 'capaian_renstra_keuangan'
        ];
        foreach ($decimalFields as $field) {
            if ($request->has($field)) {
                $mergeData[$field] = $this->cleanFlexibleNumber($request->input($field));
            }
        }
        if (!empty($mergeData)) {
            $request->merge($mergeData);
        }

        $validated = $request->validate([
            'tahun' => 'nullable|integer',
            'triwulan' => 'nullable|string',
            'sasaran' => 'nullable|string',
            'indikator' => 'nullable|string',
            'satuan' => 'nullable|string',
            'target_tahunan' => 'nullable|numeric',
            'pagu_anggaran' => 'nullable|numeric',
            'pagu_tw1' => 'nullable|numeric',
            'pagu_tw2' => 'nullable|numeric',
            'pagu_tw3' => 'nullable|numeric',
            'pagu_tw4' => 'nullable|numeric',
            'target_tw1' => 'nullable|numeric',
            'target_tw2' => 'nullable|numeric',
            'target_tw3' => 'nullable|numeric',
            'target_tw4' => 'nullable|numeric',
            'realisasi_kinerja_tw1' => 'nullable|numeric',
            'capaian_kinerja_tw1' => 'nullable|numeric',
            'predikat_kinerja_tw1' => 'nullable|string',
            'realisasi_kinerja_tw2' => 'nullable|numeric',
            'capaian_kinerja_tw2' => 'nullable|numeric',
            'predikat_kinerja_tw2' => 'nullable|string',
            'realisasi_kinerja_tw3' => 'nullable|numeric',
            'capaian_kinerja_tw3' => 'nullable|numeric',
            'predikat_kinerja_tw3' => 'nullable|string',
            'realisasi_kinerja_tw4' => 'nullable|numeric',
            'capaian_kinerja_tw4' => 'nullable|numeric',
            'predikat_kinerja_tw4' => 'nullable|string',
            'realisasi_kinerja_total' => 'nullable|numeric',
            'capaian_kinerja_total' => 'nullable|numeric',
            'predikat_kinerja_total' => 'nullable|string',
            'realisasi_keuangan_tw1' => 'nullable|numeric',
            'capaian_keuangan_tw1' => 'nullable|numeric',
            'realisasi_keuangan_tw2' => 'nullable|numeric',
            'capaian_keuangan_tw2' => 'nullable|numeric',
            'realisasi_keuangan_tw3' => 'nullable|numeric',
            'capaian_keuangan_tw3' => 'nullable|numeric',
            'realisasi_keuangan_tw4' => 'nullable|numeric',
            'capaian_keuangan_tw4' => 'nullable|numeric',
            'realisasi_keuangan_total' => 'nullable|numeric',
            'capaian_keuangan_total' => 'nullable|numeric',
            'target_rpjmd_kinerja' => 'nullable|numeric',
            'target_rpjmd_keuangan' => 'nullable|numeric',
            'capaian_renstra_kinerja' => 'nullable|numeric',
            'capaian_renstra_keuangan' => 'nullable|numeric',
            'bukti_link' => 'nullable|string',
            'bukti_keterangan' => 'nullable|string',
            'bukti_file' => 'nullable|file|max:30720',
        ]);

        $tahun = (int) ($validated['tahun'] ?? $record->tahun ?? date('Y'));
        $triwulan = !empty($validated['triwulan']) ? $validated['triwulan'] : ($record->triwulan ?? 'Semua');
        $sasaran = !empty(trim($validated['sasaran'] ?? '')) ? $validated['sasaran'] : ($record->sasaran ?? '-');
        $indikator = !empty(trim($validated['indikator'] ?? '')) ? $validated['indikator'] : ($record->indikator ?? '-');
        $satuan = !empty(trim($validated['satuan'] ?? '')) ? $validated['satuan'] : ($record->satuan ?? '-');
        $targetTahunan = (float) ($validated['target_tahunan'] ?? $record->target_tahunan ?? 0);
        $paguAnggaran = (float) ($validated['pagu_anggaran'] ?? $record->pagu_anggaran ?? 0);

        // Pagu per TW
        $paguTw1 = (float) ($validated['pagu_tw1'] ?? $record->pagu_tw1 ?? 0);
        $paguTw2 = (float) ($validated['pagu_tw2'] ?? $record->pagu_tw2 ?? 0);
        $paguTw3 = (float) ($validated['pagu_tw3'] ?? $record->pagu_tw3 ?? 0);
        $paguTw4 = (float) ($validated['pagu_tw4'] ?? $record->pagu_tw4 ?? 0);
        if ($paguAnggaran <= 0 && ($paguTw1 + $paguTw2 + $paguTw3 + $paguTw4) > 0) {
            $paguAnggaran = $paguTw1 + $paguTw2 + $paguTw3 + $paguTw4;
        }

        // Target Kinerja Fisik
        $tw1 = (float) ($validated['target_tw1'] ?? $record->target_tw1 ?? 0);
        $tw2 = (float) ($validated['target_tw2'] ?? $record->target_tw2 ?? 0);
        $tw3 = (float) ($validated['target_tw3'] ?? $record->target_tw3 ?? 0);
        $tw4 = (float) ($validated['target_tw4'] ?? $record->target_tw4 ?? 0);

        // Realisasi & Capaian Kinerja Fisik TW 1 - 4
        $rKin1 = (float) ($validated['realisasi_kinerja_tw1'] ?? $record->realisasi_kinerja_tw1 ?? 0);
        $cKin1 = $request->filled('capaian_kinerja_tw1') ? (float)$request->input('capaian_kinerja_tw1') : ($tw1 > 0 ? round(($rKin1 / $tw1) * 100, 2) : 0);
        $pKin1 = $request->filled('predikat_kinerja_tw1') ? $request->input('predikat_kinerja_tw1') : ($rKin1 > 0 ? $this->determinePredikat($cKin1) : '-');

        $rKin2 = (float) ($validated['realisasi_kinerja_tw2'] ?? $record->realisasi_kinerja_tw2 ?? 0);
        $cKin2 = $request->filled('capaian_kinerja_tw2') ? (float)$request->input('capaian_kinerja_tw2') : ($tw2 > 0 ? round(($rKin2 / $tw2) * 100, 2) : 0);
        $pKin2 = $request->filled('predikat_kinerja_tw2') ? $request->input('predikat_kinerja_tw2') : ($rKin2 > 0 ? $this->determinePredikat($cKin2) : '-');

        $rKin3 = (float) ($validated['realisasi_kinerja_tw3'] ?? $record->realisasi_kinerja_tw3 ?? 0);
        $cKin3 = $request->filled('capaian_kinerja_tw3') ? (float)$request->input('capaian_kinerja_tw3') : ($tw3 > 0 ? round(($rKin3 / $tw3) * 100, 2) : 0);
        $pKin3 = $request->filled('predikat_kinerja_tw3') ? $request->input('predikat_kinerja_tw3') : ($rKin3 > 0 ? $this->determinePredikat($cKin3) : '-');

        $rKin4 = (float) ($validated['realisasi_kinerja_tw4'] ?? $record->realisasi_kinerja_tw4 ?? 0);
        $cKin4 = $request->filled('capaian_kinerja_tw4') ? (float)$request->input('capaian_kinerja_tw4') : ($tw4 > 0 ? round(($rKin4 / $tw4) * 100, 2) : 0);
        $pKin4 = $request->filled('predikat_kinerja_tw4') ? $request->input('predikat_kinerja_tw4') : ($rKin4 > 0 ? $this->determinePredikat($cKin4) : '-');

        $rKinTot = $request->filled('realisasi_kinerja_total') ? (float)$request->input('realisasi_kinerja_total') : ($rKin1 + $rKin2 + $rKin3 + $rKin4);
        $cKinTot = $request->filled('capaian_kinerja_total') ? (float)$request->input('capaian_kinerja_total') : ($targetTahunan > 0 ? round(($rKinTot / $targetTahunan) * 100, 2) : 0);
        $pKinTot = $request->filled('predikat_kinerja_total') ? $request->input('predikat_kinerja_total') : ($rKinTot > 0 ? $this->determinePredikat($cKinTot) : '-');

        // Realisasi & Capaian Keuangan TW 1 - 4: (Realisasi Keuangan / Pagu Anggaran) * 100%
        $rKeu1 = (float) ($validated['realisasi_keuangan_tw1'] ?? $record->realisasi_keuangan_tw1 ?? 0);
        $cKeu1 = $request->filled('capaian_keuangan_tw1') ? (float)$request->input('capaian_keuangan_tw1') : ($paguAnggaran > 0 ? round(($rKeu1 / $paguAnggaran) * 100, 2) : 0);

        $rKeu2 = (float) ($validated['realisasi_keuangan_tw2'] ?? $record->realisasi_keuangan_tw2 ?? 0);
        $cKeu2 = $request->filled('capaian_keuangan_tw2') ? (float)$request->input('capaian_keuangan_tw2') : ($paguAnggaran > 0 ? round(($rKeu2 / $paguAnggaran) * 100, 2) : 0);

        $rKeu3 = (float) ($validated['realisasi_keuangan_tw3'] ?? $record->realisasi_keuangan_tw3 ?? 0);
        $cKeu3 = $request->filled('capaian_keuangan_tw3') ? (float)$request->input('capaian_keuangan_tw3') : ($paguAnggaran > 0 ? round(($rKeu3 / $paguAnggaran) * 100, 2) : 0);

        $rKeu4 = (float) ($validated['realisasi_keuangan_tw4'] ?? $record->realisasi_keuangan_tw4 ?? 0);
        $cKeu4 = $request->filled('capaian_keuangan_tw4') ? (float)$request->input('capaian_keuangan_tw4') : ($paguAnggaran > 0 ? round(($rKeu4 / $paguAnggaran) * 100, 2) : 0);

        $rKeuTot = $request->filled('realisasi_keuangan_total') ? (float)$request->input('realisasi_keuangan_total') : ($rKeu1 + $rKeu2 + $rKeu3 + $rKeu4);
        $cKeuTot = $request->filled('capaian_keuangan_total') ? (float)$request->input('capaian_keuangan_total') : ($paguAnggaran > 0 ? round(($rKeuTot / $paguAnggaran) * 100, 2) : 0);

        // RPJMD & Renstra
        $rpjmdKin = (float) ($validated['target_rpjmd_kinerja'] ?? $record->target_rpjmd_kinerja ?? 0);
        $rpjmdKeu = (float) ($validated['target_rpjmd_keuangan'] ?? $record->target_rpjmd_keuangan ?? 0);
        $cRenKin = $request->filled('capaian_renstra_kinerja') ? (float)$request->input('capaian_renstra_kinerja') : ($rpjmdKin > 0 ? round(($rKinTot / $rpjmdKin) * 100, 2) : 0);
        $cRenKeu = $request->filled('capaian_renstra_keuangan') ? (float)$request->input('capaian_renstra_keuangan') : ($rpjmdKeu > 0 ? round(($rKeuTot / $rpjmdKeu) * 100, 2) : 0);

        // Handle File Upload
        if ($request->hasFile('bukti_file')) {
            if (!empty($record->bukti_file_path)) {
                $oldPath = public_path($record->bukti_file_path);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $file = $request->file('bukti_file');
            $originalName = $file->getClientOriginalName();
            $fileSize = round($file->getSize() / 1024, 1) . ' KB';
            if ($file->getSize() >= 1048576) {
                $fileSize = round($file->getSize() / 1048576, 2) . ' MB';
            }

            $folderDest = 'uploads/capaian/' . $tahun;
            $targetDir = public_path($folderDest);
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            $storedName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);
            $file->move($targetDir, $storedName);

            $record->bukti_file_name = $originalName;
            $record->bukti_file_path = $folderDest . '/' . $storedName;
            $record->bukti_file_size = $fileSize;
        }

        if ($request->has('bukti_link')) {
            $record->bukti_link = $validated['bukti_link'];
        }
        if ($request->has('bukti_keterangan')) {
            $record->bukti_keterangan = $validated['bukti_keterangan'];
        }

        $link = $record->bukti_link;
        $hasFile = !empty($record->bukti_file_path);
        $statusBukti = ($hasFile && $link) ? 'Lengkap' : ($hasFile ? 'Ada Berkas' : ($link ? 'Ada Link' : (!empty($record->bukti_keterangan) ? 'Catatan' : 'Belum Ada')));

        $record->tahun = $tahun;
        $record->triwulan = $triwulan;
        $record->sasaran = $sasaran;
        $record->indikator = $indikator;
        $record->satuan = $satuan;
        $record->target_tahunan = $targetTahunan;
        $record->pagu_anggaran = $paguAnggaran;
        $record->pagu_tw1 = $paguTw1;
        $record->pagu_tw2 = $paguTw2;
        $record->pagu_tw3 = $paguTw3;
        $record->pagu_tw4 = $paguTw4;
        $record->target_tw1 = $tw1;
        $record->target_tw2 = $tw2;
        $record->target_tw3 = $tw3;
        $record->target_tw4 = $tw4;

        $record->realisasi_kinerja = $rKinTot;
        $record->capaian_kinerja_persen = $cKinTot;
        $record->predikat_kinerja = $pKinTot;
        $record->realisasi_keuangan = $rKeuTot;
        $record->capaian_keuangan_persen = $cKeuTot;

        $record->realisasi_kinerja_tw1 = $rKin1;
        $record->capaian_kinerja_tw1 = $cKin1;
        $record->predikat_kinerja_tw1 = $pKin1;
        $record->realisasi_kinerja_tw2 = $rKin2;
        $record->capaian_kinerja_tw2 = $cKin2;
        $record->predikat_kinerja_tw2 = $pKin2;
        $record->realisasi_kinerja_tw3 = $rKin3;
        $record->capaian_kinerja_tw3 = $cKin3;
        $record->predikat_kinerja_tw3 = $pKin3;
        $record->realisasi_kinerja_tw4 = $rKin4;
        $record->capaian_kinerja_tw4 = $cKin4;
        $record->predikat_kinerja_tw4 = $pKin4;
        $record->realisasi_kinerja_total = $rKinTot;
        $record->capaian_kinerja_total = $cKinTot;
        $record->predikat_kinerja_total = $pKinTot;

        $record->realisasi_keuangan_tw1 = $rKeu1;
        $record->capaian_keuangan_tw1 = $cKeu1;
        $record->realisasi_keuangan_tw2 = $rKeu2;
        $record->capaian_keuangan_tw2 = $cKeu2;
        $record->realisasi_keuangan_tw3 = $rKeu3;
        $record->capaian_keuangan_tw3 = $cKeu3;
        $record->realisasi_keuangan_tw4 = $rKeu4;
        $record->capaian_keuangan_tw4 = $cKeu4;
        $record->realisasi_keuangan_total = $rKeuTot;
        $record->capaian_keuangan_total = $cKeuTot;

        $record->target_rpjmd_kinerja = $rpjmdKin;
        $record->target_rpjmd_keuangan = $rpjmdKeu;
        $record->capaian_renstra_kinerja = $cRenKin;
        $record->capaian_renstra_keuangan = $cRenKeu;
        $record->status_bukti = $statusBukti;

        $record->save();

        return response()->json([
            'success' => true,
            'message' => 'Data Capaian Kinerja berhasil diperbarui.',
            'data' => $record,
        ]);
    }

    /**
     * Hapus data capaian kinerja
     */
    public function destroy($id)
    {
        $record = CapaianKinerja::findOrFail($id);
        if (!empty($record->bukti_file_path)) {
            $path = public_path($record->bukti_file_path);
            if (file_exists($path)) {
                @unlink($path);
            }
        }
        $record->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Capaian Kinerja berhasil dihapus.',
        ]);
    }

    /**
     * Update Bukti Cepat via Modal Bukti
     */
    public function updateBukti(Request $request, $id)
    {
        $record = CapaianKinerja::findOrFail($id);

        $validated = $request->validate([
            'bukti_link' => 'nullable|string',
            'bukti_keterangan' => 'nullable|string',
            'bukti_file' => 'nullable|file|max:30720',
        ]);

        if ($request->hasFile('bukti_file')) {
            if (!empty($record->bukti_file_path)) {
                $oldPath = public_path($record->bukti_file_path);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $file = $request->file('bukti_file');
            $originalName = $file->getClientOriginalName();
            $fileSize = round($file->getSize() / 1024, 1) . ' KB';
            if ($file->getSize() >= 1048576) {
                $fileSize = round($file->getSize() / 1048576, 2) . ' MB';
            }

            $folderDest = 'uploads/capaian/' . $record->tahun;
            $targetDir = public_path($folderDest);
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            $storedName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);
            $file->move($targetDir, $storedName);

            $record->bukti_file_name = $originalName;
            $record->bukti_file_path = $folderDest . '/' . $storedName;
            $record->bukti_file_size = $fileSize;
        }

        if ($request->has('bukti_link')) {
            $record->bukti_link = $validated['bukti_link'];
        }
        if ($request->has('bukti_keterangan')) {
            $record->bukti_keterangan = $validated['bukti_keterangan'];
        }

        $link = $record->bukti_link;
        $hasFile = !empty($record->bukti_file_path);
        $record->status_bukti = ($hasFile && $link) ? 'Lengkap' : ($hasFile ? 'Ada Berkas' : ($link ? 'Ada Link' : (!empty($record->bukti_keterangan) ? 'Catatan' : 'Belum Ada')));
        $record->save();

        return response()->json([
            'success' => true,
            'message' => 'Bukti pendukung berhasil diperbarui.',
            'data' => $record,
        ]);
    }

    /**
     * Unduh / Download Berkas Bukti Pendukung
     */
    public function downloadBukti($id)
    {
        $record = CapaianKinerja::findOrFail($id);
        if (empty($record->bukti_file_path) || !file_exists(public_path($record->bukti_file_path))) {
            return response()->json(['success' => false, 'message' => 'Berkas bukti tidak ditemukan di server.'], 404);
        }
        return response()->download(public_path($record->bukti_file_path), $record->bukti_file_name ?: 'bukti_pendukung.pdf');
    }

    /**
     * Ekspor Laporan Capaian Kinerja ke Excel (.xls) Matriks Komprehensif
     */
    public function exportExcel(Request $request)
    {
        $tahun = $request->query('tahun', 2026);
        $triwulan = $request->query('triwulan', 'Semua');

        $data = CapaianKinerja::where('tahun', $tahun)->orderBy('id', 'asc')->get();

        $filename = 'Laporan_Evaluasi_Renja_' . $tahun . '.xls';
        return response()->streamDownload(function () use ($data, $tahun, $triwulan) {
            echo view('admin.capaian.excel', [
                'data' => $data,
                'tahun' => $tahun,
                'triwulan' => $triwulan,
            ])->render();
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Update Capaian Kinerja & Keuangan khusus Pegawai / User Portal
     * Hanya memperbarui Realisasi Fisik, Realisasi Keuangan, dan Bukti Pendukung.
     * Sasaran, Indikator, Satuan, Target Tahunan, Pagu, dan Target TW dikunci tidak diubah.
     */
    public function updateUserCapaian(Request $request, $id)
    {
        $record = CapaianKinerja::findOrFail($id);

        $rupiahFields = [
            'realisasi_keuangan', 'realisasi_keuangan_tw1', 'realisasi_keuangan_tw2',
            'realisasi_keuangan_tw3', 'realisasi_keuangan_tw4', 'realisasi_keuangan_total'
        ];
        $mergeData = [];
        foreach ($rupiahFields as $field) {
            if ($request->has($field)) {
                $mergeData[$field] = $this->cleanRupiahNumber($request->input($field));
            }
        }

        $decimalFields = [
            'realisasi_kinerja', 'capaian_kinerja_persen', 'capaian_keuangan_persen',
            'realisasi_kinerja_tw1', 'capaian_kinerja_tw1',
            'realisasi_kinerja_tw2', 'capaian_kinerja_tw2',
            'realisasi_kinerja_tw3', 'capaian_kinerja_tw3',
            'realisasi_kinerja_tw4', 'capaian_kinerja_tw4',
            'realisasi_kinerja_total', 'capaian_kinerja_total',
            'capaian_keuangan_tw1', 'capaian_keuangan_tw2',
            'capaian_keuangan_tw3', 'capaian_keuangan_tw4', 'capaian_keuangan_total'
        ];
        foreach ($decimalFields as $field) {
            if ($request->has($field)) {
                $mergeData[$field] = $this->cleanFlexibleNumber($request->input($field));
            }
        }
        if (!empty($mergeData)) {
            $request->merge($mergeData);
        }

        $validated = $request->validate([
            'triwulan' => 'nullable|string',
            'realisasi_kinerja_tw1' => 'nullable|numeric',
            'capaian_kinerja_tw1' => 'nullable|numeric',
            'predikat_kinerja_tw1' => 'nullable|string',
            'realisasi_kinerja_tw2' => 'nullable|numeric',
            'capaian_kinerja_tw2' => 'nullable|numeric',
            'predikat_kinerja_tw2' => 'nullable|string',
            'realisasi_kinerja_tw3' => 'nullable|numeric',
            'capaian_kinerja_tw3' => 'nullable|numeric',
            'predikat_kinerja_tw3' => 'nullable|string',
            'realisasi_kinerja_tw4' => 'nullable|numeric',
            'capaian_kinerja_tw4' => 'nullable|numeric',
            'predikat_kinerja_tw4' => 'nullable|string',
            'realisasi_keuangan_tw1' => 'nullable|numeric',
            'capaian_keuangan_tw1' => 'nullable|numeric',
            'realisasi_keuangan_tw2' => 'nullable|numeric',
            'capaian_keuangan_tw2' => 'nullable|numeric',
            'realisasi_keuangan_tw3' => 'nullable|numeric',
            'capaian_keuangan_tw3' => 'nullable|numeric',
            'realisasi_keuangan_tw4' => 'nullable|numeric',
            'capaian_keuangan_tw4' => 'nullable|numeric',
            'bukti_link' => 'nullable|string',
            'bukti_keterangan' => 'nullable|string',
            'bukti_file' => 'nullable|file|max:30720',
        ]);

        $triwulan = !empty($validated['triwulan']) ? $validated['triwulan'] : ($record->triwulan ?? 'Semua');

        // Master Target & Pagu tetap dari record
        $targetTahunan = (float)($record->target_tahunan ?? 0);
        $paguAnggaran = (float)($record->pagu_anggaran ?? 0);
        $tw1 = (float)($record->target_tw1 ?? 0);
        $tw2 = (float)($record->target_tw2 ?? 0);
        $tw3 = (float)($record->target_tw3 ?? 0);
        $tw4 = (float)($record->target_tw4 ?? 0);
        $paguTw1 = (float)($record->pagu_tw1 > 0 ? $record->pagu_tw1 : ($paguAnggaran > 0 ? $paguAnggaran / 4 : 0));
        $paguTw2 = (float)($record->pagu_tw2 > 0 ? $record->pagu_tw2 : ($paguAnggaran > 0 ? $paguAnggaran / 4 : 0));
        $paguTw3 = (float)($record->pagu_tw3 > 0 ? $record->pagu_tw3 : ($paguAnggaran > 0 ? $paguAnggaran / 4 : 0));
        $paguTw4 = (float)($record->pagu_tw4 > 0 ? $record->pagu_tw4 : ($paguAnggaran > 0 ? $paguAnggaran / 4 : 0));

        // Realisasi Fisik
        $rKin1 = (float) ($validated['realisasi_kinerja_tw1'] ?? $record->realisasi_kinerja_tw1 ?? 0);
        $cKin1 = $request->filled('capaian_kinerja_tw1') ? (float)$request->input('capaian_kinerja_tw1') : ($tw1 > 0 ? round(($rKin1 / $tw1) * 100, 2) : 0);
        $pKin1 = $request->filled('predikat_kinerja_tw1') ? $request->input('predikat_kinerja_tw1') : ($rKin1 > 0 ? $this->determinePredikat($cKin1) : '-');

        $rKin2 = (float) ($validated['realisasi_kinerja_tw2'] ?? $record->realisasi_kinerja_tw2 ?? 0);
        $cKin2 = $request->filled('capaian_kinerja_tw2') ? (float)$request->input('capaian_kinerja_tw2') : ($tw2 > 0 ? round(($rKin2 / $tw2) * 100, 2) : 0);
        $pKin2 = $request->filled('predikat_kinerja_tw2') ? $request->input('predikat_kinerja_tw2') : ($rKin2 > 0 ? $this->determinePredikat($cKin2) : '-');

        $rKin3 = (float) ($validated['realisasi_kinerja_tw3'] ?? $record->realisasi_kinerja_tw3 ?? 0);
        $cKin3 = $request->filled('capaian_kinerja_tw3') ? (float)$request->input('capaian_kinerja_tw3') : ($tw3 > 0 ? round(($rKin3 / $tw3) * 100, 2) : 0);
        $pKin3 = $request->filled('predikat_kinerja_tw3') ? $request->input('predikat_kinerja_tw3') : ($rKin3 > 0 ? $this->determinePredikat($cKin3) : '-');

        $rKin4 = (float) ($validated['realisasi_kinerja_tw4'] ?? $record->realisasi_kinerja_tw4 ?? 0);
        $cKin4 = $request->filled('capaian_kinerja_tw4') ? (float)$request->input('capaian_kinerja_tw4') : ($tw4 > 0 ? round(($rKin4 / $tw4) * 100, 2) : 0);
        $pKin4 = $request->filled('predikat_kinerja_tw4') ? $request->input('predikat_kinerja_tw4') : ($rKin4 > 0 ? $this->determinePredikat($cKin4) : '-');

        $rKinTot = $rKin1 + $rKin2 + $rKin3 + $rKin4;
        $cKinTot = $targetTahunan > 0 ? round(($rKinTot / $targetTahunan) * 100, 2) : 0;
        $pKinTot = $rKinTot > 0 ? $this->determinePredikat($cKinTot) : '-';

        // Realisasi Keuangan: (Realisasi Keuangan / Pagu Anggaran) * 100%
        $rKeu1 = (float) ($validated['realisasi_keuangan_tw1'] ?? $record->realisasi_keuangan_tw1 ?? 0);
        $cKeu1 = $request->filled('capaian_keuangan_tw1') ? (float)$request->input('capaian_keuangan_tw1') : ($paguAnggaran > 0 ? round(($rKeu1 / $paguAnggaran) * 100, 2) : 0);

        $rKeu2 = (float) ($validated['realisasi_keuangan_tw2'] ?? $record->realisasi_keuangan_tw2 ?? 0);
        $cKeu2 = $request->filled('capaian_keuangan_tw2') ? (float)$request->input('capaian_keuangan_tw2') : ($paguAnggaran > 0 ? round(($rKeu2 / $paguAnggaran) * 100, 2) : 0);

        $rKeu3 = (float) ($validated['realisasi_keuangan_tw3'] ?? $record->realisasi_keuangan_tw3 ?? 0);
        $cKeu3 = $request->filled('capaian_keuangan_tw3') ? (float)$request->input('capaian_keuangan_tw3') : ($paguAnggaran > 0 ? round(($rKeu3 / $paguAnggaran) * 100, 2) : 0);

        $rKeu4 = (float) ($validated['realisasi_keuangan_tw4'] ?? $record->realisasi_keuangan_tw4 ?? 0);
        $cKeu4 = $request->filled('capaian_keuangan_tw4') ? (float)$request->input('capaian_keuangan_tw4') : ($paguAnggaran > 0 ? round(($rKeu4 / $paguAnggaran) * 100, 2) : 0);

        $rKeuTot = $rKeu1 + $rKeu2 + $rKeu3 + $rKeu4;
        $cKeuTot = $paguAnggaran > 0 ? round(($rKeuTot / $paguAnggaran) * 100, 2) : 0;

        // Handle File Upload Bukti
        if ($request->hasFile('bukti_file')) {
            if (!empty($record->bukti_file_path)) {
                $oldPath = public_path($record->bukti_file_path);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $file = $request->file('bukti_file');
            $originalName = $file->getClientOriginalName();
            $fileSize = round($file->getSize() / 1024, 1) . ' KB';
            if ($file->getSize() >= 1048576) {
                $fileSize = round($file->getSize() / 1048576, 2) . ' MB';
            }

            $folderDest = 'uploads/capaian/' . ($record->tahun ?? date('Y'));
            $targetDir = public_path($folderDest);
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            $storedName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);
            $file->move($targetDir, $storedName);

            $record->bukti_file_name = $originalName;
            $record->bukti_file_path = $folderDest . '/' . $storedName;
            $record->bukti_file_size = $fileSize;
        }

        if ($request->has('bukti_link')) {
            $record->bukti_link = $validated['bukti_link'];
        }
        if ($request->has('bukti_keterangan')) {
            $record->bukti_keterangan = $validated['bukti_keterangan'];
        }

        $link = $record->bukti_link;
        $hasFile = !empty($record->bukti_file_path);
        $statusBukti = ($hasFile && $link) ? 'Lengkap' : ($hasFile ? 'Ada Berkas' : ($link ? 'Ada Link' : (!empty($record->bukti_keterangan) ? 'Catatan' : 'Belum Ada')));

        // Simpan hanya realisasi dan capaian, data master tidak diubah
        $record->realisasi_kinerja_tw1 = $rKin1;
        $record->capaian_kinerja_tw1 = $cKin1;
        $record->predikat_kinerja_tw1 = $pKin1;
        $record->realisasi_kinerja_tw2 = $rKin2;
        $record->capaian_kinerja_tw2 = $cKin2;
        $record->predikat_kinerja_tw2 = $pKin2;
        $record->realisasi_kinerja_tw3 = $rKin3;
        $record->capaian_kinerja_tw3 = $cKin3;
        $record->predikat_kinerja_tw3 = $pKin3;
        $record->realisasi_kinerja_tw4 = $rKin4;
        $record->capaian_kinerja_tw4 = $cKin4;
        $record->predikat_kinerja_tw4 = $pKin4;
        $record->realisasi_kinerja_total = $rKinTot;
        $record->capaian_kinerja_total = $cKinTot;
        $record->predikat_kinerja_total = $pKinTot;

        $record->realisasi_keuangan_tw1 = $rKeu1;
        $record->capaian_keuangan_tw1 = $cKeu1;
        $record->realisasi_keuangan_tw2 = $rKeu2;
        $record->capaian_keuangan_tw2 = $cKeu2;
        $record->realisasi_keuangan_tw3 = $rKeu3;
        $record->capaian_keuangan_tw3 = $cKeu3;
        $record->realisasi_keuangan_tw4 = $rKeu4;
        $record->capaian_keuangan_tw4 = $cKeu4;
        $record->realisasi_keuangan_total = $rKeuTot;
        $record->capaian_keuangan_total = $cKeuTot;

        $record->realisasi_kinerja = $rKinTot;
        $record->capaian_kinerja_persen = $cKinTot;
        $record->predikat_kinerja = $pKinTot;
        $record->realisasi_keuangan = $rKeuTot;
        $record->capaian_keuangan_persen = $cKeuTot;
        $record->status_bukti = $statusBukti;

        $record->save();

        return response()->json([
            'success' => true,
            'message' => 'Capaian Kinerja & Keuangan berhasil disimpan oleh Pengguna.',
            'data' => $record,
        ]);
    }

    /**
     * Cetak / Ekspor PDF Resmi Laporan Capaian Kinerja (Format Landscape Lebar)
     */
    public function cetak(Request $request)
    {
        $tahun = (int) $request->query('tahun', 2026);
        $triwulan = $request->query('triwulan', 'Semua');

        $data = CapaianKinerja::where('tahun', $tahun)->orderBy('id', 'asc')->get();

        // Hanya jika secara eksplisit diminta format=html (untuk preview/debug), kembalikan view web
        if ($request->query('format') === 'html') {
            return view('admin.capaian.cetak', [
                'data' => $data,
                'tahun' => $tahun,
                'triwulan' => $triwulan,
                'isPdf' => false,
            ]);
        }

        // Default & Otomatis: LANGSUNG GENERATE & STREAM PDF MURNI (Save As / Print langsung di PDF Viewer)
        $paperSize = ($triwulan === 'Semua') ? 'a3' : 'a4';
        $twSlug = str_replace(' ', '_', $triwulan);
        $filename = 'Laporan_Capaian_Kinerja_' . $twSlug . '_' . $tahun . '.pdf';

        $pdf = Pdf::loadView('admin.capaian.cetak', [
            'data' => $data,
            'tahun' => $tahun,
            'triwulan' => $triwulan,
            'isPdf' => true,
        ])->setPaper($paperSize, 'landscape');

        return $pdf->stream($filename)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function cetakPdf(Request $request)
    {
        return $this->cetak($request);
    }
}
