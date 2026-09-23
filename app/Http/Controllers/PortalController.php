<?php

namespace App\Http\Controllers;

use App\Models\CapaianKinerja;
use App\Models\Dokumen;
use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        $search = $request->query('q');
        $tahun = $request->query('tahun');

        $dokumenQuery = Dokumen::orderBy('created_at', 'desc');
        if (!empty($search)) {
            $dokumenQuery->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }
        if (!empty($tahun)) {
            $dokumenQuery->where('tahun_anggaran', $tahun);
        }

        $allDocs = $dokumenQuery->get();

        // Filter per modul
        $renjaMurni = $allDocs->where('modul', 'renja')->where('kategori', 'murni');
        $renjaPerubahan = $allDocs->where('modul', 'renja')->where('kategori', 'perubahan');
        $pkMurni = $allDocs->where('modul', 'pk')->where('kategori', 'murni');
        $pkPerubahan = $allDocs->where('modul', 'pk')->where('kategori', 'perubahan');
        $dpaMurni = $allDocs->where('modul', 'dpa')->where('kategori', 'murni');
        $dpaPerubahan = $allDocs->where('modul', 'dpa')->where('kategori', 'perubahan');

        // Surat
        $suratMasuk = Surat::where('jenis', 'masuk')->orderBy('tanggal_surat', 'desc')->get();
        $suratKeluar = Surat::where('jenis', 'keluar')->orderBy('tanggal_surat', 'desc')->get();

        $totalDoc = $allDocs->count() + $suratMasuk->count() + $suratKeluar->count();

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
            'renja-murni' => $renjaMurni->map($mapDoc)->values()->toArray(),
            'renja-perubahan' => $renjaPerubahan->map($mapDoc)->values()->toArray(),
            'pk-murni' => $pkMurni->map($mapDoc)->values()->toArray(),
            'pk-perubahan' => $pkPerubahan->map($mapDoc)->values()->toArray(),
            'dpa-murni' => $dpaMurni->map($mapDoc)->values()->toArray(),
            'dpa-perubahan' => $dpaPerubahan->map($mapDoc)->values()->toArray(),
            'surat-masuk' => $suratMasuk->map($mapSurat)->values()->toArray(),
            'surat-keluar' => $suratKeluar->map($mapSurat)->values()->toArray(),
        ];

        // Capaian Kinerja Data
        $capaianKinerjaList = CapaianKinerja::orderBy('id', 'asc')->get();
        $countCapaian = $capaianKinerjaList->count();
        $capaianYears = CapaianKinerja::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun')->toArray();
        if (empty($capaianYears)) {
            $capaianYears = [2026];
        }

        return view('portal.index', compact(
            'allDocs',
            'renjaMurni',
            'renjaPerubahan',
            'pkMurni',
            'pkPerubahan',
            'dpaMurni',
            'dpaPerubahan',
            'suratMasuk',
            'suratKeluar',
            'totalDoc',
            'allDocsGrouped',
            'capaianKinerjaList',
            'countCapaian',
            'capaianYears',
            'search',
            'tahun'
        ));
    }
}
