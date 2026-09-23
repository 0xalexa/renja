<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CapaianKinerjaController;
use App\Http\Controllers\DokumenController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\SuratController;
use Illuminate\Support\Facades\Route;

// 1. Gerbang Depan (Landing Page): Arahkan sesuai status Login & Role
Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }
    return Auth::user()->role === 'admin'
        ? redirect()->route('admin.dashboard')
        : redirect()->route('portal.index');
});

// 2. Autentikasi (Login & Logout)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 3. Portal Pegawai (Wajib Login & Akses Pegawai)
Route::middleware('auth')->group(function () {
    Route::get('/portal', [PortalController::class, 'index'])->name('portal.index');
    Route::get('/dokumen/download/{id}', [DokumenController::class, 'download'])->name('dokumen.download');
    Route::get('/surat/download/{id}', [SuratController::class, 'download'])->name('surat.download');

    // Capaian Kinerja untuk Pegawai (Hanya input Realisasi Kinerja, Realisasi Keuangan, & Bukti)
    Route::post('/portal/capaian-kinerja/{id}/update', [CapaianKinerjaController::class, 'updateUserCapaian'])->name('portal.capaian.update');
    Route::post('/portal/capaian-kinerja/{id}/bukti', [CapaianKinerjaController::class, 'updateBukti'])->name('portal.capaian.bukti');
    Route::get('/portal/capaian-kinerja/download-bukti/{id}', [CapaianKinerjaController::class, 'downloadBukti'])->name('portal.capaian.download-bukti');
    Route::get('/portal/capaian-kinerja/export-excel', [CapaianKinerjaController::class, 'exportExcel'])->name('portal.capaian.export');
    Route::get('/portal/capaian-kinerja/cetak', [CapaianKinerjaController::class, 'cetak'])->name('portal.capaian.cetak');
});

// 4. Workspace Administrator (Hanya untuk Admin)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/backup', [AdminController::class, 'downloadBackup'])->name('backup');

    // CRUD Dokumen & Surat
    Route::resource('dokumen', DokumenController::class)->only(['store', 'update', 'destroy']);
    Route::resource('surat', SuratController::class)->only(['store', 'update', 'destroy']);

    // CRUD & Export Capaian Kinerja
    Route::get('/capaian-kinerja', [CapaianKinerjaController::class, 'index'])->name('capaian.index');
    Route::post('/capaian-kinerja', [CapaianKinerjaController::class, 'store'])->name('capaian.store');
    Route::post('/capaian-kinerja/{id}/update', [CapaianKinerjaController::class, 'update'])->name('capaian.update');
    Route::post('/capaian-kinerja/{id}/bukti', [CapaianKinerjaController::class, 'updateBukti'])->name('capaian.bukti');
    Route::get('/capaian-kinerja/download-bukti/{id}', [CapaianKinerjaController::class, 'downloadBukti'])->name('capaian.download-bukti');
    Route::delete('/capaian-kinerja/{id}', [CapaianKinerjaController::class, 'destroy'])->name('capaian.destroy');
    Route::get('/capaian-kinerja/export-excel', [CapaianKinerjaController::class, 'exportExcel'])->name('capaian.export');
    Route::get('/capaian-kinerja/cetak', [CapaianKinerjaController::class, 'cetak'])->name('capaian.cetak');
}); 