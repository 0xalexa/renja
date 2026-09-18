<?php

namespace App\Http\Controllers;

use App\Models\Dokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class DokumenController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'modul' => 'required|string',
            'kategori' => 'required|string',
            'tahun_anggaran' => 'required|integer',
            'judul' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'status' => 'required|string',
            'link_drive' => 'nullable|url',
            'file_pdf' => 'nullable|file|mimes:pdf|max:25600', // max 25MB
        ]);

        $filePath = null;
        $fileName = null;
        $fileSize = null;
        $linkDrive = $request->input('link_drive');

        // Jika user mengunggah berkas fisik lokal
        if ($request->hasFile('file_pdf')) {
            $file = $request->file('file_pdf');
            $originalName = $file->getClientOriginalName();
            $fileName = $originalName;
            $fileSize = round($file->getSize() / 1024, 1) . ' KB';
            if ($file->getSize() >= 1048576) {
                $fileSize = round($file->getSize() / 1048576, 2) . ' MB';
            }

            $folderDest = 'uploads/dokumen/' . $validated['modul'] . '/' . $validated['kategori'] . '/' . $validated['tahun_anggaran'];
            $targetDir = public_path($folderDest);
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            $fileStoredName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);
            
            $file->move($targetDir, $fileStoredName);
            $filePath = $folderDest . '/' . $fileStoredName;

            // Otomatis sinkronkan salinan ke Google Drive Desktop sesuai navbar
            $subfolder = $this->getNavbarFolderName($validated['modul'], $validated['kategori']);
            $this->syncToGoogleDrive($filePath, $subfolder, $originalName);
        } elseif (!empty($linkDrive)) {
            $fileName = $validated['judul'] . ' (Google Drive)';
            $fileSize = 'Cloud Drive';
        }

        $dokumen = Dokumen::create([
            'modul' => $validated['modul'],
            'kategori' => $validated['kategori'],
            'tahun_anggaran' => $validated['tahun_anggaran'],
            'judul' => $validated['judul'],
            'keterangan' => $validated['keterangan'] ?? null,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_size' => $fileSize,
            'link_drive' => $linkDrive,
            'status' => $validated['status'],
            'uploaded_by' => Auth::id(),
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil disimpan ke sistem.',
                'data' => [
                    'id' => $dokumen->id,
                    'tahun' => $dokumen->tahun_anggaran,
                    'judul' => $dokumen->judul,
                    'keterangan' => $dokumen->keterangan ?? '-',
                    'file' => $dokumen->file_name ?? ($dokumen->link_drive ? 'Dokumen (Google Drive)' : '-'),
                    'size' => $dokumen->file_size ?? ($dokumen->link_drive ? 'Cloud Drive' : '-'),
                    'date' => $dokumen->created_at ? $dokumen->created_at->format('Y-m-d') : date('Y-m-d'),
                    'status' => $dokumen->status,
                    'link_drive' => $dokumen->link_drive,
                    'file_path' => $dokumen->file_path,
                    'download_url' => $dokumen->download_url,
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Dokumen berhasil disimpan ke sistem.');
    }

    public function update(Request $request, $id)
    {
        $dokumen = Dokumen::findOrFail($id);

        $validated = $request->validate([
            'modul' => 'required|string',
            'kategori' => 'required|string',
            'tahun_anggaran' => 'required|integer',
            'judul' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'status' => 'required|string',
            'link_drive' => 'nullable|url',
            'file_pdf' => 'nullable|file|mimes:pdf|max:25600',
        ]);

        $dokumen->modul = $validated['modul'];
        $dokumen->kategori = $validated['kategori'];
        $dokumen->tahun_anggaran = $validated['tahun_anggaran'];
        $dokumen->judul = $validated['judul'];
        $dokumen->keterangan = $validated['keterangan'] ?? null;
        $dokumen->status = $validated['status'];

        if ($request->filled('link_drive')) {
            $dokumen->link_drive = $request->input('link_drive');
            if (empty($dokumen->file_size)) {
                $dokumen->file_size = 'Cloud Drive';
            }
        }

        if ($request->hasFile('file_pdf')) {
            // Hapus file lama fisik lokal jika ada
            if (!empty($dokumen->file_path) && File::exists(public_path($dokumen->file_path))) {
                File::delete(public_path($dokumen->file_path));
            }

            // Hapus juga file lama di Google Drive Desktop jika ada
            $oldSubfolder = $this->getNavbarFolderName($dokumen->modul, $dokumen->kategori);
            $this->deleteFromGoogleDrive($oldSubfolder, $dokumen->file_name);

            $file = $request->file('file_pdf');
            $originalName = $file->getClientOriginalName();
            $dokumen->file_name = $originalName;
            $size = round($file->getSize() / 1024, 1) . ' KB';
            if ($file->getSize() >= 1048576) {
                $size = round($file->getSize() / 1048576, 2) . ' MB';
            }
            $dokumen->file_size = $size;

            $folderDest = 'uploads/dokumen/' . $validated['modul'] . '/' . $validated['kategori'] . '/' . $validated['tahun_anggaran'];
            $targetDir = public_path($folderDest);
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            $fileStoredName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);
            $file->move($targetDir, $fileStoredName);
            $dokumen->file_path = $folderDest . '/' . $fileStoredName;

            // Otomatis sinkronkan salinan ke Google Drive Desktop sesuai navbar
            $subfolder = $this->getNavbarFolderName($validated['modul'], $validated['kategori']);
            $this->syncToGoogleDrive($dokumen->file_path, $subfolder, $originalName);
        }

        $dokumen->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil diperbarui & disinkronkan ke Google Drive.',
                'data' => [
                    'id' => $dokumen->id,
                    'tahun' => $dokumen->tahun_anggaran,
                    'judul' => $dokumen->judul,
                    'keterangan' => $dokumen->keterangan ?? '-',
                    'file' => $dokumen->file_name ?? ($dokumen->link_drive ? 'Dokumen (Google Drive)' : '-'),
                    'size' => $dokumen->file_size ?? ($dokumen->link_drive ? 'Cloud Drive' : '-'),
                    'date' => $dokumen->created_at ? $dokumen->created_at->format('Y-m-d') : date('Y-m-d'),
                    'status' => $dokumen->status,
                    'link_drive' => $dokumen->link_drive,
                    'file_path' => $dokumen->file_path,
                    'download_url' => $dokumen->download_url,
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Dokumen berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $dokumen = Dokumen::findOrFail($id);

        // Hapus file fisik lokal jika ada
        if (!empty($dokumen->file_path) && File::exists(public_path($dokumen->file_path))) {
            File::delete(public_path($dokumen->file_path));
        }

        // Hapus salinan berkas di Google Drive Desktop jika ada
        $subfolder = $this->getNavbarFolderName($dokumen->modul, $dokumen->kategori);
        $this->deleteFromGoogleDrive($subfolder, $dokumen->file_name);

        $dokumen->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil dihapus permanen dari sistem dan Google Drive.'
            ]);
        }

        return redirect()->back()->with('success', 'Dokumen berhasil dihapus permanen dari sistem dan Google Drive.');
    }

    public function download($id)
    {
        $dokumen = Dokumen::findOrFail($id);

        // Jika dokumen tersimpan di Google Drive / Cloud
        if (!empty($dokumen->link_drive)) {
            return redirect()->away($dokumen->link_drive);
        }

        // Jika berkas tersimpan lokal di server
        if (!empty($dokumen->file_path) && File::exists(public_path($dokumen->file_path))) {
            return response()->download(public_path($dokumen->file_path), $dokumen->file_name ?? 'Dokumen.pdf');
        }

        return redirect()->back()->with('error', 'Berkas PDF belum tersedia untuk dokumen ini.');
    }

    /**
     * Memetakan nama subfolder Google Drive sesuai modul navbar
     */
    private function getNavbarFolderName(string $modul, string $kategori): string
    {
        $m = strtolower($modul);
        $k = strtolower($kategori);

        if ($m === 'renja') {
            return $k === 'perubahan' ? 'Renja Perubahan' : 'Renja Murni';
        }
        if ($m === 'pk') {
            return $k === 'perubahan' ? 'PK Perubahan' : 'PK Murni';
        }
        if ($m === 'dpa') {
            return $k === 'perubahan' ? 'DPA Perubahan' : 'DPA Murni';
        }

        return ucfirst($m) . ' ' . ucfirst($k);
    }

    /**
     * Menyalin berkas PDF ke folder Google Drive for Desktop (G:\My Drive\SIM-PEP\...)
     */
    private function syncToGoogleDrive(string $localRelativePath, string $subfolder, string $fileName): void
    {
        $gdriveBase = env('GOOGLE_DRIVE_DESKTOP_PATH', 'G:/My Drive/SIM-PEP');
        if (empty($gdriveBase)) return;

        try {
            $destDir = rtrim($gdriveBase, '/\\') . DIRECTORY_SEPARATOR . $subfolder;
            if (!file_exists($destDir)) {
                mkdir($destDir, 0755, true);
            }
            $sourceFile = public_path($localRelativePath);
            if (file_exists($sourceFile)) {
                copy($sourceFile, $destDir . DIRECTORY_SEPARATOR . $fileName);
            }
        } catch (\Throwable $e) {
            \Log::warning('Google Drive sync error: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus salinan berkas di Google Drive for Desktop (G:\My Drive\SIM-PEP\...)
     */
    private function deleteFromGoogleDrive(string $subfolder, ?string $fileName): void
    {
        if (empty($fileName)) return;

        $gdriveBase = env('GOOGLE_DRIVE_DESKTOP_PATH', 'G:/My Drive/SIM-PEP');
        if (empty($gdriveBase)) return;

        try {
            $destDir = rtrim($gdriveBase, '/\\') . DIRECTORY_SEPARATOR . $subfolder;
            $filePath = $destDir . DIRECTORY_SEPARATOR . $fileName;
            if (file_exists($filePath)) {
                @unlink($filePath);
                return;
            }

            // Fallback: jika posisi subfolder berubah, telusuri di bawah SIM-PEP
            if (is_dir($gdriveBase)) {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($gdriveBase, \FilesystemIterator::SKIP_DOTS)
                );
                foreach ($iterator as $file) {
                    if ($file->isFile() && $file->getFilename() === $fileName) {
                        @unlink($file->getRealPath());
                        break;
                    }
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('Google Drive delete error: ' . $e->getMessage());
        }
    }
}

