<?php

namespace App\Http\Controllers;

use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class SuratController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis' => 'required|in:masuk,keluar',
            'nomor_surat' => 'required|string|max:100',
            'tanggal_surat' => 'required|date',
            'tanggal_terima' => 'nullable|date',
            'perihal' => 'required|string|max:255',
            'instansi' => 'required|string|max:200',
            'keterangan' => 'nullable|string',
            'status' => 'required|string',
            'link_drive' => 'nullable|url',
            'file_pdf' => 'nullable|file|mimes:pdf|max:25600',
        ]);

        $filePath = null;
        $fileName = null;
        $fileSize = null;
        $linkDrive = $request->input('link_drive');

        if ($request->hasFile('file_pdf')) {
            $file = $request->file('file_pdf');
            $originalName = $file->getClientOriginalName();
            $fileName = $originalName;
            $fileSize = round($file->getSize() / 1024, 1) . ' KB';
            if ($file->getSize() >= 1048576) {
                $fileSize = round($file->getSize() / 1048576, 2) . ' MB';
            }

            $folderDest = 'uploads/surat/' . $validated['jenis'] . '/' . date('Y', strtotime($validated['tanggal_surat']));
            $targetDir = public_path($folderDest);
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            $fileStoredName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);
            
            $file->move($targetDir, $fileStoredName);
            $filePath = $folderDest . '/' . $fileStoredName;

            // Otomatis sinkronkan salinan ke Google Drive Desktop sesuai navbar
            $subfolder = $this->getNavbarFolderName($validated['jenis']);
            $this->syncToGoogleDrive($filePath, $subfolder, $originalName);
        } elseif (!empty($linkDrive)) {
            $fileName = $validated['perihal'] . ' (Google Drive)';
            $fileSize = 'Cloud Drive';
        }

        $surat = Surat::create([
            'jenis' => $validated['jenis'],
            'nomor_surat' => $validated['nomor_surat'],
            'tanggal_surat' => $validated['tanggal_surat'],
            'tanggal_terima' => $validated['tanggal_terima'] ?? null,
            'perihal' => $validated['perihal'],
            'instansi' => $validated['instansi'],
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
                'message' => 'Arsip surat berhasil disimpan.',
                'data' => [
                    'id' => $surat->id,
                    'nomor' => $surat->nomor_surat,
                    'tanggal' => $surat->tanggal_surat,
                    'pengirim' => $surat->instansi,
                    'judul' => $surat->perihal,
                    'keterangan' => $surat->keterangan ?? '-',
                    'file' => $surat->file_name ?? ($surat->link_drive ? 'Berkas (Google Drive)' : '-'),
                    'size' => $surat->file_size ?? ($surat->link_drive ? 'Cloud Drive' : '-'),
                    'date' => $surat->tanggal_surat,
                    'status' => $surat->status,
                    'link_drive' => $surat->link_drive,
                    'file_path' => $surat->file_path,
                    'download_url' => $surat->download_url,
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Arsip surat berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $surat = Surat::findOrFail($id);

        $validated = $request->validate([
            'jenis' => 'required|in:masuk,keluar',
            'nomor_surat' => 'required|string|max:100',
            'tanggal_surat' => 'required|date',
            'tanggal_terima' => 'nullable|date',
            'perihal' => 'required|string|max:255',
            'instansi' => 'required|string|max:200',
            'keterangan' => 'nullable|string',
            'status' => 'required|string',
            'link_drive' => 'nullable|url',
            'file_pdf' => 'nullable|file|mimes:pdf|max:25600',
        ]);

        $surat->jenis = $validated['jenis'];
        $surat->nomor_surat = $validated['nomor_surat'];
        $surat->tanggal_surat = $validated['tanggal_surat'];
        $surat->tanggal_terima = $validated['tanggal_terima'] ?? null;
        $surat->perihal = $validated['perihal'];
        $surat->instansi = $validated['instansi'];
        $surat->keterangan = $validated['keterangan'] ?? null;
        $surat->status = $validated['status'];

        if ($request->filled('link_drive')) {
            $surat->link_drive = $request->input('link_drive');
            if (empty($surat->file_size)) {
                $surat->file_size = 'Cloud Drive';
            }
        }

        if ($request->hasFile('file_pdf')) {
            if (!empty($surat->file_path) && File::exists(public_path($surat->file_path))) {
                File::delete(public_path($surat->file_path));
            }

            // Hapus berkas lama di Google Drive jika ada
            $oldSubfolder = $this->getNavbarFolderName($surat->jenis);
            $this->deleteFromGoogleDrive($oldSubfolder, $surat->file_name);

            $file = $request->file('file_pdf');
            $originalName = $file->getClientOriginalName();
            $surat->file_name = $originalName;
            $size = round($file->getSize() / 1024, 1) . ' KB';
            if ($file->getSize() >= 1048576) {
                $size = round($file->getSize() / 1048576, 2) . ' MB';
            }
            $surat->file_size = $size;

            $folderDest = 'uploads/surat/' . $validated['jenis'] . '/' . date('Y', strtotime($validated['tanggal_surat']));
            $targetDir = public_path($folderDest);
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            $fileStoredName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);
            $file->move($targetDir, $fileStoredName);
            $surat->file_path = $folderDest . '/' . $fileStoredName;

            // Otomatis sinkronkan salinan ke Google Drive Desktop sesuai navbar
            $subfolder = $this->getNavbarFolderName($validated['jenis']);
            $this->syncToGoogleDrive($surat->file_path, $subfolder, $originalName);
        }

        $surat->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Arsip surat berhasil diperbarui & disinkronkan ke Google Drive.',
                'data' => [
                    'id' => $surat->id,
                    'nomor' => $surat->nomor_surat,
                    'tanggal' => $surat->tanggal_surat,
                    'pengirim' => $surat->instansi,
                    'judul' => $surat->perihal,
                    'keterangan' => $surat->keterangan ?? '-',
                    'file' => $surat->file_name ?? ($surat->link_drive ? 'Berkas (Google Drive)' : '-'),
                    'size' => $surat->file_size ?? ($surat->link_drive ? 'Cloud Drive' : '-'),
                    'date' => $surat->tanggal_surat,
                    'status' => $surat->status,
                    'link_drive' => $surat->link_drive,
                    'file_path' => $surat->file_path,
                    'download_url' => $surat->download_url,
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Arsip surat berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $surat = Surat::findOrFail($id);

        if (!empty($surat->file_path) && File::exists(public_path($surat->file_path))) {
            File::delete(public_path($surat->file_path));
        }

        // Hapus salinan berkas di Google Drive Desktop jika ada
        $subfolder = $this->getNavbarFolderName($surat->jenis);
        $this->deleteFromGoogleDrive($subfolder, $surat->file_name);

        $surat->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Arsip surat berhasil dihapus permanen dari sistem dan Google Drive.'
            ]);
        }

        return redirect()->back()->with('success', 'Arsip surat berhasil dihapus permanen dari sistem dan Google Drive.');
    }

    public function download($id)
    {
        $surat = Surat::findOrFail($id);

        if (!empty($surat->link_drive)) {
            return redirect()->away($surat->link_drive);
        }

        if (!empty($surat->file_path) && File::exists(public_path($surat->file_path))) {
            return response()->download(public_path($surat->file_path), $surat->file_name ?? 'Surat.pdf');
        }

        return redirect()->back()->with('error', 'Berkas PDF belum tersedia untuk surat ini.');
    }

    /**
     * Memetakan nama subfolder Google Drive sesuai modul navbar persuratan
     */
    private function getNavbarFolderName(string $jenis): string
    {
        return strtolower($jenis) === 'keluar' ? 'Surat Keluar' : 'Surat Masuk';
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

