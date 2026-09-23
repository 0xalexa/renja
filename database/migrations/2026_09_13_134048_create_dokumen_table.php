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
        Schema::create('dokumen', function (Blueprint $table) {
            $table->id();
            $table->string('modul', 50); // renja, pk, dpa
            $table->string('kategori', 50); // murni, perubahan
            $table->integer('tahun_anggaran')->default(2026);
            $table->string('judul');
            $table->text('keterangan')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_size')->nullable();
            $table->text('link_drive')->nullable();
            $table->string('status')->default('Lengkap'); // Lengkap, Diproses, Perlu Update, Terkirim
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['modul', 'kategori', 'tahun_anggaran']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen');
    }
};
