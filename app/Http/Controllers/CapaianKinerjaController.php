<?php

namespace App\Http\Controllers;

use App\Models\CapaianKinerja;
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
        $triwulan = $request->query('triwulan', 'TW I');

        $query = CapaianKinerja::where('tahun', $tahun);

        if ($triwulan !== 'Semua' && !empty($triwulan)) {
            $query->where('triwulan', $triwulan);
        }

        $data = $query->orderBy('id', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Simpan data capaian kinerja baru
     */
    public function store(Request $request)
    {
        $mergeData = [];
        foreach (['pagu_anggaran', 'realisasi_keuangan'] as $field) {
            if ($request->has($field)) {
                $mergeData[$field] = $this->cleanRupiahNumber($request->input($field));
            }
        }
        foreach (['target_tahunan', 'target_tw1', 'target_tw2', 'target_tw3', 'target_tw4', 'realisasi_kinerja', 'capaian_kinerja_persen', 'capaian_keuangan_persen'] as $field) {
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
            'target_tw1' => 'nullable|numeric',
            'target_tw2' => 'nullable|numeric',
            'target_tw3' => 'nullable|numeric',
            'target_tw4' => 'nullable|numeric',
            'realisasi_kinerja' => 'nullable|numeric',
            'realisasi_keuangan' => 'nullable|numeric',
            'bukti_link' => 'nullable|string',
            'bukti_keterangan' => 'nullable|string',
            'bukti_file' => 'nullable|file|max:30720', // max 30MB
        ]);

        $tahun = (int) ($validated['tahun'] ?? date('Y'));
        $triwulan = !empty($validated['triwulan']) ? $validated['triwulan'] : 'TW I';
        $sasaran = !empty(trim($validated['sasaran'] ?? '')) ? $validated['sasaran'] : '-';
        $indikator = !empty(trim($validated['indikator'] ?? '')) ? $validated['indikator'] : '-';
        $satuan = !empty(trim($validated['satuan'] ?? '')) ? $validated['satuan'] : '-';
        $targetTahunan = (float) ($validated['target_tahunan'] ?? 0);
        $paguAnggaran = (float) ($validated['pagu_anggaran'] ?? 0);
        $tw1 = (float) ($validated['target_tw1'] ?? 0);
        $tw2 = (float) ($validated['target_tw2'] ?? 0);
        $tw3 = (float) ($validated['target_tw3'] ?? 0);
        $tw4 = (float) ($validated['target_tw4'] ?? 0);
        $realisasiKinerja = (float) ($validated['realisasi_kinerja'] ?? 0);
        $realisasiKeuangan = (float) ($validated['realisasi_keuangan'] ?? 0);

        // Kalkulasi Capaian Kinerja (%)
        $targetAktif = $tw1;
        if ($triwulan === 'TW II') $targetAktif = $tw2;
        elseif ($triwulan === 'TW III') $targetAktif = $tw3;
        elseif ($triwulan === 'TW IV') $targetAktif = $tw4;

        // Kalkulasi Capaian Kinerja (%) (bisa diisi manual atau otomatis)
        $persenKinerja = 0.0;
        if ($request->filled('capaian_kinerja_persen')) {
            $persenKinerja = (float) $request->input('capaian_kinerja_persen');
        } elseif ($targetAktif > 0) {
            $persenKinerja = round(($realisasiKinerja / $targetAktif) * 100, 2);
        } elseif ($realisasiKinerja == 0 && $targetAktif == 0) {
            $persenKinerja = 100.0;
        }

        // Tentukan Predikat (bisa dipilih manual atau otomatis)
        $predikat = $request->input('predikat_kinerja');
        if (empty($predikat)) {
            if ($persenKinerja >= 100) $predikat = 'Sangat Berhasil';
            elseif ($persenKinerja >= 85) $predikat = 'Berhasil';
            elseif ($persenKinerja >= 70) $predikat = 'Cukup';
            elseif ($persenKinerja >= 55) $predikat = 'Sedang';
            else $predikat = 'Kurang';
        }

        // Kalkulasi Capaian Keuangan (%) (bisa diisi manual atau otomatis)
        $persenKeuangan = 0.0;
        if ($request->filled('capaian_keuangan_persen')) {
            $persenKeuangan = (float) $request->input('capaian_keuangan_persen');
        } elseif ($paguAnggaran > 0) {
            $persenKeuangan = round(($realisasiKeuangan / $paguAnggaran) * 100, 2);
        }

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

            $folderDest = 'uploads/capaian/' . $tahun . '/' . str_replace(' ', '', $triwulan);
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
            'sasaran' => $validated['sasaran'],
            'indikator' => $validated['indikator'],
            'satuan' => $validated['satuan'],
            'target_tahunan' => $targetTahunan,
            'pagu_anggaran' => $paguAnggaran,
            'target_tw1' => $tw1,
            'target_tw2' => $tw2,
            'target_tw3' => $tw3,
            'target_tw4' => $tw4,
            'realisasi_kinerja' => $realisasiKinerja,
            'capaian_kinerja_persen' => $persenKinerja,
            'predikat_kinerja' => $predikat,
            'realisasi_keuangan' => $realisasiKeuangan,
            'capaian_keuangan_persen' => $persenKeuangan,
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

        $mergeData = [];
        foreach (['pagu_anggaran', 'realisasi_keuangan'] as $field) {
            if ($request->has($field)) {
                $mergeData[$field] = $this->cleanRupiahNumber($request->input($field));
            }
        }
        foreach (['target_tahunan', 'target_tw1', 'target_tw2', 'target_tw3', 'target_tw4', 'realisasi_kinerja', 'capaian_kinerja_persen', 'capaian_keuangan_persen'] as $field) {
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
            'target_tw1' => 'nullable|numeric',
            'target_tw2' => 'nullable|numeric',
            'target_tw3' => 'nullable|numeric',
            'target_tw4' => 'nullable|numeric',
            'realisasi_kinerja' => 'nullable|numeric',
            'realisasi_keuangan' => 'nullable|numeric',
            'bukti_link' => 'nullable|string',
            'bukti_keterangan' => 'nullable|string',
            'bukti_file' => 'nullable|file|max:30720',
        ]);

        $tahun = (int) ($validated['tahun'] ?? $record->tahun ?? date('Y'));
        $triwulan = !empty($validated['triwulan']) ? $validated['triwulan'] : ($record->triwulan ?? 'TW I');
        $sasaran = !empty(trim($validated['sasaran'] ?? '')) ? $validated['sasaran'] : ($record->sasaran ?? '-');
        $indikator = !empty(trim($validated['indikator'] ?? '')) ? $validated['indikator'] : ($record->indikator ?? '-');
        $satuan = !empty(trim($validated['satuan'] ?? '')) ? $validated['satuan'] : ($record->satuan ?? '-');
        $targetTahunan = (float) ($validated['target_tahunan'] ?? 0);
        $paguAnggaran = (float) ($validated['pagu_anggaran'] ?? 0);
        $tw1 = (float) ($validated['target_tw1'] ?? 0);
        $tw2 = (float) ($validated['target_tw2'] ?? 0);
        $tw3 = (float) ($validated['target_tw3'] ?? 0);
        $tw4 = (float) ($validated['target_tw4'] ?? 0);
        $realisasiKinerja = (float) ($validated['realisasi_kinerja'] ?? 0);
        $realisasiKeuangan = (float) ($validated['realisasi_keuangan'] ?? 0);

        // Target TW Aktif
        $targetAktif = $tw1;
        if ($triwulan === 'TW II') $targetAktif = $tw2;
        elseif ($triwulan === 'TW III') $targetAktif = $tw3;
        elseif ($triwulan === 'TW IV') $targetAktif = $tw4;

        // Kalkulasi Capaian Kinerja (%) (bisa diisi manual atau otomatis)
        $persenKinerja = 0.0;
        if ($request->filled('capaian_kinerja_persen')) {
            $persenKinerja = (float) $request->input('capaian_kinerja_persen');
        } elseif ($targetAktif > 0) {
            $persenKinerja = round(($realisasiKinerja / $targetAktif) * 100, 2);
        } elseif ($realisasiKinerja == 0 && $targetAktif == 0) {
            $persenKinerja = 100.0;
        }

        // Tentukan Predikat (bisa dipilih manual atau otomatis)
        $predikat = $request->input('predikat_kinerja');
        if (empty($predikat)) {
            if ($persenKinerja >= 100) $predikat = 'Sangat Berhasil';
            elseif ($persenKinerja >= 85) $predikat = 'Berhasil';
            elseif ($persenKinerja >= 70) $predikat = 'Cukup';
            elseif ($persenKinerja >= 55) $predikat = 'Sedang';
            else $predikat = 'Kurang';
        }

        // Kalkulasi Capaian Keuangan (%) (bisa diisi manual atau otomatis)
        $persenKeuangan = 0.0;
        if ($request->filled('capaian_keuangan_persen')) {
            $persenKeuangan = (float) $request->input('capaian_keuangan_persen');
        } elseif ($paguAnggaran > 0) {
            $persenKeuangan = round(($realisasiKeuangan / $paguAnggaran) * 100, 2);
        }

        // Upload Berkas jika ada yang baru
        if ($request->hasFile('bukti_file')) {
            if (!empty($record->bukti_file_path) && File::exists(public_path($record->bukti_file_path))) {
                File::delete(public_path($record->bukti_file_path));
            }

            $file = $request->file('bukti_file');
            $originalName = $file->getClientOriginalName();
            $record->bukti_file_name = $originalName;
            $size = round($file->getSize() / 1024, 1) . ' KB';
            if ($file->getSize() >= 1048576) {
                $size = round($file->getSize() / 1048576, 2) . ' MB';
            }
            $record->bukti_file_size = $size;

            $folderDest = 'uploads/capaian/' . $tahun . '/' . str_replace(' ', '', $triwulan);
            $targetDir = public_path($folderDest);
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            $storedName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);
            $file->move($targetDir, $storedName);
            $record->bukti_file_path = $folderDest . '/' . $storedName;
        }

        $record->tahun = $tahun;
        $record->triwulan = $triwulan;
        $record->sasaran = $validated['sasaran'];
        $record->indikator = $validated['indikator'];
        $record->satuan = $validated['satuan'];
        $record->target_tahunan = $targetTahunan;
        $record->pagu_anggaran = $paguAnggaran;
        $record->target_tw1 = $tw1;
        $record->target_tw2 = $tw2;
        $record->target_tw3 = $tw3;
        $record->target_tw4 = $tw4;
        $record->realisasi_kinerja = $realisasiKinerja;
        $record->capaian_kinerja_persen = $persenKinerja;
        $record->predikat_kinerja = $predikat;
        $record->realisasi_keuangan = $realisasiKeuangan;
        $record->capaian_keuangan_persen = $persenKeuangan;
        $record->bukti_link = $validated['bukti_link'] ?? null;
        $record->bukti_keterangan = $validated['bukti_keterangan'] ?? null;

        $link = $record->bukti_link;
        $fileName = $record->bukti_file_name;
        $record->status_bukti = ($fileName && $link) ? 'Lengkap' : ($fileName ? 'Ada Berkas' : ($link ? 'Ada Link' : (!empty($record->bukti_keterangan) ? 'Catatan' : 'Belum Ada')));

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

        if (!empty($record->bukti_file_path) && File::exists(public_path($record->bukti_file_path))) {
            File::delete(public_path($record->bukti_file_path));
        }

        $record->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Capaian Kinerja berhasil dihapus.',
        ]);
    }

    /**
     * Simpan / Perbarui Bukti Pendukung Kinerja (Link, File, Keterangan)
     */
    public function updateBukti(Request $request, $id)
    {
        $record = CapaianKinerja::findOrFail($id);

        $validated = $request->validate([
            'bukti_link' => 'nullable|string|max:1000',
            'bukti_keterangan' => 'nullable|string|max:1000',
            'bukti_file' => 'nullable|file|max:30720', // max 30MB
        ]);

        if ($request->hasFile('bukti_file')) {
            // Hapus berkas lama jika ada
            if (!empty($record->bukti_file_path) && File::exists(public_path($record->bukti_file_path))) {
                File::delete(public_path($record->bukti_file_path));
            }

            $file = $request->file('bukti_file');
            $originalName = $file->getClientOriginalName();
            $record->bukti_file_name = $originalName;
            $fileSize = round($file->getSize() / 1024, 1) . ' KB';
            if ($file->getSize() >= 1048576) {
                $fileSize = round($file->getSize() / 1048576, 2) . ' MB';
            }
            $record->bukti_file_size = $fileSize;

            $folderDest = 'uploads/capaian/' . $record->tahun . '/' . str_replace(' ', '', $record->triwulan);
            $targetDir = public_path($folderDest);
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            $storedName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);
            $file->move($targetDir, $storedName);
            $record->bukti_file_path = $folderDest . '/' . $storedName;
        }

        if ($request->has('bukti_link')) {
            $record->bukti_link = $validated['bukti_link'] ?? null;
        }
        if ($request->has('bukti_keterangan')) {
            $record->bukti_keterangan = $validated['bukti_keterangan'] ?? null;
        }

        $link = $record->bukti_link;
        $fileName = $record->bukti_file_name;
        $record->status_bukti = ($fileName && $link) ? 'Lengkap' : ($fileName ? 'Ada Berkas' : ($link ? 'Ada Link' : (!empty($record->bukti_keterangan) ? 'Catatan' : 'Belum Ada')));

        $record->save();

        return response()->json([
            'success' => true,
            'message' => 'Bukti pendukung berhasil disimpan.',
            'data' => $record,
        ]);
    }

    /**
     * Unduh Berkas Bukti Pendukung
     */
    public function downloadBukti($id)
    {
        $record = CapaianKinerja::findOrFail($id);

        if (empty($record->bukti_file_path) || !File::exists(public_path($record->bukti_file_path))) {
            abort(404, 'Berkas bukti pendukung tidak ditemukan di server.');
        }

        return response()->download(public_path($record->bukti_file_path), $record->bukti_file_name ?: 'Bukti_Pendukung');
    }

    /**
     * Export ke Excel (.xls) dengan format multi-level header persis sesuai foto
     */
    public function exportExcel(Request $request)
    {
        $tahun = $request->query('tahun', 2026);
        $triwulan = $request->query('triwulan', 'TW I');

        $query = CapaianKinerja::where('tahun', $tahun);
        if ($triwulan !== 'Semua' && !empty($triwulan)) {
            $query->where('triwulan', $triwulan);
        }
        $data = $query->orderBy('id', 'asc')->get();

        $filename = 'Laporan_Capaian_Kinerja_' . str_replace(' ', '_', $triwulan) . '_' . $tahun . '.xls';

        return response()->streamDownload(function () use ($data, $tahun, $triwulan) {
            echo view('admin.capaian.excel', [
                'data' => $data,
                'tahun' => $tahun,
                'triwulan' => $triwulan,
            ])->render();
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Cetak / Tampilan Siap Cetak PDF Resmi (Landscape Kop Dinas)
     */
    public function cetak(Request $request)
    {
        $tahun = $request->query('tahun', 2026);
        $triwulan = $request->query('triwulan', 'TW I');

        $query = CapaianKinerja::where('tahun', $tahun);
        if ($triwulan !== 'Semua' && !empty($triwulan)) {
            $query->where('triwulan', $triwulan);
        }
        $data = $query->orderBy('id', 'asc')->get();

        return view('admin.capaian.cetak', [
            'data' => $data,
            'tahun' => $tahun,
            'triwulan' => $triwulan,
        ]);
    }

    /**
     * Helper pembersih format rupiah (bebas pemotongan, koma/titik Indo atau US)
     */
    private function cleanRupiahNumber($val)
    {
        if (is_null($val) || $val === '') return 0.0;
        if (is_numeric($val)) return (float) $val;
        $str = trim((string) $val);
        $str = preg_replace('/rp|idr/i', '', $str);
        $str = preg_replace('/,-$/', '', $str);
        $str = preg_replace('/[^0-9.,\-]/', '', $str);
        if ($str === '') return 0.0;

        if (strpos($str, ',') !== false && strpos($str, '.') !== false) {
            if (strrpos($str, ',') > strrpos($str, '.')) {
                $str = str_replace('.', '', $str);
                $str = str_replace(',', '.', $str);
            } else {
                $str = str_replace(',', '', $str);
            }
        } elseif (strpos($str, '.') !== false) {
            $parts = explode('.', $str);
            if (count($parts) > 2 || (count($parts) === 2 && strlen($parts[1]) === 3)) {
                $str = str_replace('.', '', $str);
            }
        } elseif (strpos($str, ',') !== false) {
            $parts = explode(',', $str);
            if (count($parts) > 2 || (count($parts) === 2 && strlen($parts[1]) === 3 && strlen($parts[0]) >= 1)) {
                $str = str_replace(',', '', $str);
            } else {
                $str = str_replace(',', '.', $str);
            }
        }

        return is_numeric($str) ? (float) $str : 0.0;
    }

    /**
     * Helper pembersih angka desimal/target/realisasi kinerja
     */
    private function cleanFlexibleNumber($val)
    {
        if (is_null($val) || $val === '') return 0.0;
        if (is_numeric($val)) return (float) $val;
        $str = trim((string) $val);
        $str = preg_replace('/[^0-9.,\-]/', '', $str);
        if ($str === '') return 0.0;

        if (strpos($str, ',') !== false && strpos($str, '.') !== false) {
            if (strrpos($str, ',') > strrpos($str, '.')) {
                $str = str_replace('.', '', $str);
                $str = str_replace(',', '.', $str);
            } else {
                $str = str_replace(',', '', $str);
            }
        } elseif (strpos($str, ',') !== false) {
            $str = str_replace(',', '.', $str);
        }

        return is_numeric($str) ? (float) $str : 0.0;
    }
}

