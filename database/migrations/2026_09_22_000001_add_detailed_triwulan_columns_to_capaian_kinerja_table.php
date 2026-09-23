<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('capaian_kinerja', function (Blueprint $table) {
            // Pagu per Triwulan (Kolom 5 s/d 8)
            $table->decimal('pagu_tw1', 17, 2)->default(0);
            $table->decimal('pagu_tw2', 17, 2)->default(0);
            $table->decimal('pagu_tw3', 17, 2)->default(0);
            $table->decimal('pagu_tw4', 17, 2)->default(0);

            // Capaian Kinerja Fisik per TW (Kolom 14 s/d 28)
            $table->decimal('realisasi_kinerja_tw1', 14, 2)->default(0);
            $table->decimal('capaian_kinerja_tw1', 8, 2)->default(0);
            $table->string('predikat_kinerja_tw1', 50)->nullable();

            $table->decimal('realisasi_kinerja_tw2', 14, 2)->default(0);
            $table->decimal('capaian_kinerja_tw2', 8, 2)->default(0);
            $table->string('predikat_kinerja_tw2', 50)->nullable();

            $table->decimal('realisasi_kinerja_tw3', 14, 2)->default(0);
            $table->decimal('capaian_kinerja_tw3', 8, 2)->default(0);
            $table->string('predikat_kinerja_tw3', 50)->nullable();

            $table->decimal('realisasi_kinerja_tw4', 14, 2)->default(0);
            $table->decimal('capaian_kinerja_tw4', 8, 2)->default(0);
            $table->string('predikat_kinerja_tw4', 50)->nullable();

            $table->decimal('realisasi_kinerja_total', 14, 2)->default(0);
            $table->decimal('capaian_kinerja_total', 8, 2)->default(0);
            $table->string('predikat_kinerja_total', 50)->nullable();

            // Capaian Keuangan per TW (Kolom 29 s/d 38)
            $table->decimal('realisasi_keuangan_tw1', 17, 2)->default(0);
            $table->decimal('capaian_keuangan_tw1', 8, 2)->default(0);

            $table->decimal('realisasi_keuangan_tw2', 17, 2)->default(0);
            $table->decimal('capaian_keuangan_tw2', 8, 2)->default(0);

            $table->decimal('realisasi_keuangan_tw3', 17, 2)->default(0);
            $table->decimal('capaian_keuangan_tw3', 8, 2)->default(0);

            $table->decimal('realisasi_keuangan_tw4', 17, 2)->default(0);
            $table->decimal('capaian_keuangan_tw4', 8, 2)->default(0);

            $table->decimal('realisasi_keuangan_total', 17, 2)->default(0);
            $table->decimal('capaian_keuangan_total', 8, 2)->default(0);

            // Target Akhir RPJMD & Capaian Akhir Renstra (Kolom 39 s/d 42)
            $table->decimal('target_rpjmd_kinerja', 14, 2)->default(0);
            $table->decimal('target_rpjmd_keuangan', 17, 2)->default(0);
            $table->decimal('capaian_renstra_kinerja', 8, 2)->default(0);
            $table->decimal('capaian_renstra_keuangan', 8, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('capaian_kinerja', function (Blueprint $table) {
            $table->dropColumn([
                'pagu_tw1', 'pagu_tw2', 'pagu_tw3', 'pagu_tw4',
                'realisasi_kinerja_tw1', 'capaian_kinerja_tw1', 'predikat_kinerja_tw1',
                'realisasi_kinerja_tw2', 'capaian_kinerja_tw2', 'predikat_kinerja_tw2',
                'realisasi_kinerja_tw3', 'capaian_kinerja_tw3', 'predikat_kinerja_tw3',
                'realisasi_kinerja_tw4', 'capaian_kinerja_tw4', 'predikat_kinerja_tw4',
                'realisasi_kinerja_total', 'capaian_kinerja_total', 'predikat_kinerja_total',
                'realisasi_keuangan_tw1', 'capaian_keuangan_tw1',
                'realisasi_keuangan_tw2', 'capaian_keuangan_tw2',
                'realisasi_keuangan_tw3', 'capaian_keuangan_tw3',
                'realisasi_keuangan_tw4', 'capaian_keuangan_tw4',
                'realisasi_keuangan_total', 'capaian_keuangan_total',
                'target_rpjmd_kinerja', 'target_rpjmd_keuangan',
                'capaian_renstra_kinerja', 'capaian_renstra_keuangan',
            ]);
        });
    }
};
