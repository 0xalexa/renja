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
        if (!Schema::hasTable('capaian_kinerja')) {
            Schema::create('capaian_kinerja', function (Blueprint $table) {
                $table->id();
                $table->integer('tahun')->default(2026);
                $table->string('triwulan', 20)->default('TW I');
                $table->text('sasaran');
                $table->text('indikator');
                $table->string('satuan', 50)->default('Persentase');
                $table->decimal('target_tahunan', 14, 2)->default(0);
                $table->decimal('pagu_anggaran', 17, 2)->default(0);
                $table->decimal('target_tw1', 14, 2)->default(0);
                $table->decimal('target_tw2', 14, 2)->default(0);
                $table->decimal('target_tw3', 14, 2)->default(0);
                $table->decimal('target_tw4', 14, 2)->default(0);
                $table->decimal('realisasi_kinerja', 14, 2)->default(0);
                $table->decimal('capaian_kinerja_persen', 8, 2)->default(0);
                $table->string('predikat_kinerja', 50)->nullable();
                $table->decimal('realisasi_keuangan', 17, 2)->default(0);
                $table->decimal('capaian_keuangan_persen', 8, 2)->default(0);
                $table->text('bukti_link')->nullable();
                $table->string('bukti_file_path')->nullable();
                $table->string('bukti_file_name')->nullable();
                $table->string('bukti_file_size')->nullable();
                $table->text('bukti_keterangan')->nullable();
                $table->string('status_bukti')->default('Belum Ada');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['tahun', 'triwulan']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capaian_kinerja');
    }
};
