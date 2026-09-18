<?php

namespace App\Http\Controllers;

use App\Models\Dokumen;
use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        if (Auth::check() && Auth::user()->role !== 'admin') {
            return redirect()->route('portal.index')->with('error', 'Akses dibatasi. Halaman ini hanya untuk Administrator PEP.');
        }

        // 1. Dokumen Terkini
        $recentDocs = Dokumen::latest()->take(10)->get();

        // 2. Hitungan Modul
        $countRenjaMurni = Dokumen::where('modul', 'renja')->where('kategori', 'murni')->count();
        $countRenjaPerubahan = Dokumen::where('modul', 'renja')->where('kategori', 'perubahan')->count();
        $countPkMurni = Dokumen::where('modul', 'pk')->where('kategori', 'murni')->count();
        $countPkPerubahan = Dokumen::where('modul', 'pk')->where('kategori', 'perubahan')->count();
        $countDpaMurni = Dokumen::where('modul', 'dpa')->where('kategori', 'murni')->count();
        $countDpaPerubahan = Dokumen::where('modul', 'dpa')->where('kategori', 'perubahan')->count();
        $countSuratMasuk = Surat::where('jenis', 'masuk')->count();
        $countSuratKeluar = Surat::where('jenis', 'keluar')->count();

        // 3. Seluruh Dokumen Per Kategori
        $allRenjaMurni = Dokumen::where('modul', 'renja')->where('kategori', 'murni')->latest()->get();
        $allRenjaPerubahan = Dokumen::where('modul', 'renja')->where('kategori', 'perubahan')->latest()->get();
        $allPkMurni = Dokumen::where('modul', 'pk')->where('kategori', 'murni')->latest()->get();
        $allPkPerubahan = Dokumen::where('modul', 'pk')->where('kategori', 'perubahan')->latest()->get();
        $allDpaMurni = Dokumen::where('modul', 'dpa')->where('kategori', 'murni')->latest()->get();
        $allDpaPerubahan = Dokumen::where('modul', 'dpa')->where('kategori', 'perubahan')->latest()->get();
        $allSuratMasuk = Surat::where('jenis', 'masuk')->latest('tanggal_surat')->get();
        $allSuratKeluar = Surat::where('jenis', 'keluar')->latest('tanggal_surat')->get();

        // 4. Hitungan Status (Donut Chart)
        $statusLengkap = Dokumen::where('status', 'Lengkap')->count();
        $statusDiproses = Dokumen::where('status', 'Diproses')->count();
        $statusPerluUpdate = Dokumen::where('status', 'Perlu Update')->count();
        $statusTerkirim = Dokumen::where('status', 'Terkirim')->count();

        $totalDokumen = Dokumen::count() + Surat::count();

        // Default Google Drive Folder yang diberikan pengguna
        $googleDriveFolder = env('GOOGLE_DRIVE_DEFAULT_FOLDER', 'https://drive.google.com/drive/folders/18XtuS2NgJyn8FBHwF_cw09qM4JuhPy7P?usp=sharing');

        $mapDoc = function ($doc) {
            return [
                'id' => $doc->id,
                'tahun' => $doc->tahun_anggaran,
                'judul' => $doc->judul,
                'keterangan' => $doc->keterangan ?? '-',
                'file' => $doc->file_name ?? ($doc->link_drive ? 'Dokumen (Google Drive)' : '-'),
                'size' => $doc->file_size ?? ($doc->link_drive ? 'Cloud Drive' : '-'),
                'date' => $doc->created_at ? $doc->created_at->format('Y-m-d') : date('Y-m-d'),
                'status' => $doc->status,
                'link_drive' => $doc->link_drive,
                'file_path' => $doc->file_path,
                'download_url' => $doc->download_url,
            ];
        };

        $mapSurat = function ($surat) {
            return [
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
            ];
        };

        $allDocsGrouped = [
            'renja-murni' => $allRenjaMurni->map($mapDoc)->values()->toArray(),
            'renja-perubahan' => $allRenjaPerubahan->map($mapDoc)->values()->toArray(),
            'pk-murni' => $allPkMurni->map($mapDoc)->values()->toArray(),
            'pk-perubahan' => $allPkPerubahan->map($mapDoc)->values()->toArray(),
            'dpa-murni' => $allDpaMurni->map($mapDoc)->values()->toArray(),
            'dpa-perubahan' => $allDpaPerubahan->map($mapDoc)->values()->toArray(),
            'surat-masuk' => $allSuratMasuk->map($mapSurat)->values()->toArray(),
            'surat-keluar' => $allSuratKeluar->map($mapSurat)->values()->toArray(),
        ];

        // Capaian Kinerja Data
        $capaianKinerjaList = \App\Models\CapaianKinerja::orderBy('id', 'asc')->get();
        $countCapaian = $capaianKinerjaList->count();
        $capaianYears = \App\Models\CapaianKinerja::select('tahun')->distinct()->pluck('tahun')->toArray();
        if (empty($capaianYears)) {
            $capaianYears = [2026];
        }

        return view('admin.dashboard', compact(
            'recentDocs',
            'countRenjaMurni',
            'countRenjaPerubahan',
            'countPkMurni',
            'countPkPerubahan',
            'countDpaMurni',
            'countDpaPerubahan',
            'countSuratMasuk',
            'countSuratKeluar',
            'allRenjaMurni',
            'allRenjaPerubahan',
            'allPkMurni',
            'allPkPerubahan',
            'allDpaMurni',
            'allDpaPerubahan',
            'allSuratMasuk',
            'allSuratKeluar',
            'statusLengkap',
            'statusDiproses',
            'statusPerluUpdate',
            'statusTerkirim',
            'totalDokumen',
            'googleDriveFolder',
            'allDocsGrouped',
            'capaianKinerjaList',
            'countCapaian',
            'capaianYears'
        ));
    }

    public function downloadBackup()
    {
        $backupName = 'BACKUP_SIMPEP_' . date('Y-m-d_His') . '.json';
        $data = [
            'app' => 'SIM-PEP DISDIK',
            'exported_at' => now()->toIso8601String(),
            'dokumen' => Dokumen::all(),
            'surat' => Surat::all(),
        ];

        return response()->streamDownload(function () use ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }, $backupName, [
            'Content-Type' => 'application/json',
        ]);
    }
}
