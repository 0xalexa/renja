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

        // Capaian Kinerja Data (Analisis Komprehensif Antar-Tahun & Kelengkapan Triwulan)
        $capaianKinerjaList = \App\Models\CapaianKinerja::orderBy('id', 'asc')->get();
        $countCapaian = $capaianKinerjaList->count();
        $capaianYears = \App\Models\CapaianKinerja::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun')->toArray();
        if (empty($capaianYears)) {
            $capaianYears = [2026];
        }

        // Kalkulasi Statistik Rata-rata Tahunan Murni (e-SAKIP Standard)
        $normalizedPercentages = $capaianKinerjaList->map(function ($item) {
            $rawP = (float)($item->capaian_kinerja_persen ?? 0);
            if ($rawP <= 0 && ($item->realisasi_kinerja ?? 0) > 0 && ($item->target_tahunan ?? 0) > 0) {
                $rawP = ($item->realisasi_kinerja / $item->target_tahunan) * 100;
            }
            return min(100, max(0, $rawP));
        })->filter(function ($p) {
            return $p > 0;
        });

        $capaianAvgKinerja = $normalizedPercentages->count() > 0 
            ? round($normalizedPercentages->avg(), 1) 
            : 0;

        $capaianTotalPagu = (float) $capaianKinerjaList->sum('pagu_anggaran');
        $capaianTotalRealisasi = (float) $capaianKinerjaList->sum('realisasi_keuangan');
        $capaianAvgKeuangan = $capaianTotalPagu > 0 
            ? round(($capaianTotalRealisasi / $capaianTotalPagu) * 100, 1) 
            : round(($capaianKinerjaList->avg('capaian_keuangan_persen') ?: 0), 1);

        $predikatCounts = [
            'sangat_tinggi' => 0,
            'tinggi' => 0,
            'sedang' => 0,
            'rendah' => 0,
        ];

        foreach ($capaianKinerjaList as $item) {
            $rawP = (float)($item->capaian_kinerja_persen ?? 0);
            if ($rawP <= 0 && ($item->realisasi_kinerja ?? 0) > 0 && ($item->target_tahunan ?? 0) > 0) {
                $rawP = ($item->realisasi_kinerja / $item->target_tahunan) * 100;
            }
            $persen = min(100, max(0, $rawP));
            $predikat = strtolower($item->predikat_kinerja ?? '');

            if (str_contains($predikat, 'sangat') || $persen >= 90) {
                $predikatCounts['sangat_tinggi']++;
            } elseif (str_contains($predikat, 'tinggi') || ($persen >= 80 && $persen < 90)) {
                $predikatCounts['tinggi']++;
            } elseif (str_contains($predikat, 'sedang') || ($persen >= 70 && $persen < 80)) {
                $predikatCounts['sedang']++;
            } else {
                $predikatCounts['rendah']++;
            }
        }

        // Bahasa awam deskripsi capaian
        if ($capaianAvgKinerja >= 90) {
            $overallPredikat = 'Sangat Baik (Tercapai Penuh)';
            $overallBadgeClass = 'badge-green';
            $overallColor = '#10b981';
        } elseif ($capaianAvgKinerja >= 80) {
            $overallPredikat = 'Baik (Tercapai)';
            $overallBadgeClass = 'badge-blue';
            $overallColor = '#3b82f6';
        } elseif ($capaianAvgKinerja >= 70) {
            $overallPredikat = 'Cukup Baik (Sedang)';
            $overallBadgeClass = 'badge-yellow';
            $overallColor = '#f59e0b';
        } elseif ($capaianAvgKinerja > 0) {
            $overallPredikat = 'Perlu Ditingkatkan';
            $overallBadgeClass = 'badge-red';
            $overallColor = '#ef4444';
        } else {
            $overallPredikat = 'Belum Ada Isian';
            $overallBadgeClass = 'badge-gray';
            $overallColor = '#64748b';
        }

        // Analisis Perkembangan Antar-Tahun (Year-over-Year Trend)
        $capaianPerTahun = [];
        $groupedByYear = $capaianKinerjaList->groupBy('tahun');
        foreach ($groupedByYear as $yr => $items) {
            $norm = $items->map(function ($it) {
                $rawP = (float)($it->capaian_kinerja_persen ?? 0);
                if ($rawP <= 0 && ($it->realisasi_kinerja ?? 0) > 0 && ($it->target_tahunan ?? 0) > 0) {
                    $rawP = ($it->realisasi_kinerja / $it->target_tahunan) * 100;
                }
                return min(100, max(0, $rawP));
            })->filter(function ($p) { return $p > 0; });

            $capaianPerTahun[$yr] = [
                'tahun' => (int) $yr,
                'avg' => $norm->count() > 0 ? round($norm->avg(), 1) : 0,
                'count' => $items->count(),
                'triwulans' => $items->pluck('triwulan')->unique()->filter()->values()->toArray()
            ];
        }
        ksort($capaianPerTahun);

        $latestYear = !empty($capaianYears) ? (int) $capaianYears[0] : 2026;
        $prevYear = $latestYear - 1;
        $peningkatanTahun = null;
        if (isset($capaianPerTahun[$prevYear]) && isset($capaianPerTahun[$latestYear])) {
            $delta = round($capaianPerTahun[$latestYear]['avg'] - $capaianPerTahun[$prevYear]['avg'], 1);
            $peningkatanTahun = [
                'delta' => $delta,
                'status' => $delta > 0 ? 'Meningkat' : ($delta < 0 ? 'Menurun' : 'Stabil'),
                'prevTahun' => $prevYear,
                'prevAvg' => $capaianPerTahun[$prevYear]['avg'],
                'currTahun' => $latestYear,
                'currAvg' => $capaianPerTahun[$latestYear]['avg']
            ];
        }

        // Analisis Komprehensif per Triwulan (4 Kuartal SIM-PEP / e-SAKIP)
        $twSummary = [
            'TW I' => [
                'key' => 'TW I',
                'title' => 'Triwulan I',
                'shortTitle' => 'TW 1',
                'months' => 'Jan – Mar ' . $latestYear,
                'fullPeriod' => 'Januari – Maret ' . $latestYear,
                'filled' => false,
                'count' => 0,
                'avgCapaian' => 0,
                'avgKeuangan' => 0,
                'totalPagu' => 0,
                'totalRealisasi' => 0,
                'predikat' => 'Belum Diisi',
                'badgeClass' => 'badge-gray',
                'color' => '#64748b',
                'items' => []
            ],
            'TW II' => [
                'key' => 'TW II',
                'title' => 'Triwulan II',
                'shortTitle' => 'TW 2',
                'months' => 'Apr – Jun ' . $latestYear,
                'fullPeriod' => 'April – Juni ' . $latestYear,
                'filled' => false,
                'count' => 0,
                'avgCapaian' => 0,
                'avgKeuangan' => 0,
                'totalPagu' => 0,
                'totalRealisasi' => 0,
                'predikat' => 'Belum Diisi',
                'badgeClass' => 'badge-gray',
                'color' => '#64748b',
                'items' => []
            ],
            'TW III' => [
                'key' => 'TW III',
                'title' => 'Triwulan III',
                'shortTitle' => 'TW 3',
                'months' => 'Jul – Sep ' . $latestYear,
                'fullPeriod' => 'Juli – September ' . $latestYear,
                'filled' => false,
                'count' => 0,
                'avgCapaian' => 0,
                'avgKeuangan' => 0,
                'totalPagu' => 0,
                'totalRealisasi' => 0,
                'predikat' => 'Belum Diisi',
                'badgeClass' => 'badge-gray',
                'color' => '#64748b',
                'items' => []
            ],
            'TW IV' => [
                'key' => 'TW IV',
                'title' => 'Triwulan IV',
                'shortTitle' => 'TW 4',
                'months' => 'Okt – Des ' . $latestYear,
                'fullPeriod' => 'Oktober – Desember ' . $latestYear,
                'filled' => false,
                'count' => 0,
                'avgCapaian' => 0,
                'avgKeuangan' => 0,
                'totalPagu' => 0,
                'totalRealisasi' => 0,
                'predikat' => 'Belum Diisi',
                'badgeClass' => 'badge-gray',
                'color' => '#64748b',
                'items' => []
            ],
        ];

        foreach ($capaianKinerjaList as $item) {
            $twKey = trim($item->triwulan ?? '');
            if ((int)($item->tahun ?? $latestYear) === (int)$latestYear && isset($twSummary[$twKey])) {
                $twSummary[$twKey]['filled'] = true;
                $twSummary[$twKey]['count']++;
                $twSummary[$twKey]['items'][] = $item;
                $twSummary[$twKey]['totalPagu'] += (float)($item->pagu_anggaran ?? 0);
                $twSummary[$twKey]['totalRealisasi'] += (float)($item->realisasi_keuangan ?? 0);
            }
        }

        foreach ($twSummary as $k => &$tw) {
            if ($tw['filled'] && count($tw['items']) > 0) {
                $scores = collect($tw['items'])->map(function($it) {
                    $p = (float)($it->capaian_kinerja_persen ?? 0);
                    if ($p <= 0 && ($it->realisasi_kinerja ?? 0) > 0 && ($it->target_tahunan ?? 0) > 0) {
                        $p = ($it->realisasi_kinerja / $it->target_tahunan) * 100;
                    }
                    return min(100, max(0, $p));
                })->filter(function($val) { return $val > 0; });

                $avg = $scores->count() > 0 ? round($scores->avg(), 1) : 0;
                $tw['avgCapaian'] = $avg;
                if ($tw['totalPagu'] > 0) {
                    $tw['avgKeuangan'] = round(($tw['totalRealisasi'] / $tw['totalPagu']) * 100, 1);
                }

                if ($avg >= 90) {
                    $tw['predikat'] = 'Sangat Baik';
                    $tw['badgeClass'] = 'badge-green';
                    $tw['color'] = '#10b981';
                } elseif ($avg >= 80) {
                    $tw['predikat'] = 'Baik';
                    $tw['badgeClass'] = 'badge-blue';
                    $tw['color'] = '#3b82f6';
                } elseif ($avg >= 70) {
                    $tw['predikat'] = 'Cukup Baik';
                    $tw['badgeClass'] = 'badge-yellow';
                    $tw['color'] = '#f59e0b';
                } else {
                    $tw['predikat'] = 'Perlu Ditingkatkan';
                    $tw['badgeClass'] = 'badge-red';
                    $tw['color'] = '#ef4444';
                }
            }
        }
        unset($tw);

        $filledTwCount = count(array_filter($twSummary, function($v) { return $v['filled']; }));

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
            'capaianYears',
            'capaianAvgKinerja',
            'capaianAvgKeuangan',
            'capaianTotalPagu',
            'capaianTotalRealisasi',
            'predikatCounts',
            'overallPredikat',
            'overallBadgeClass',
            'overallColor',
            'capaianPerTahun',
            'peningkatanTahun',
            'twSummary',
            'filledTwCount',
            'latestYear'
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
