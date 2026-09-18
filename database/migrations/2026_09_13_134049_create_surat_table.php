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
        Schema::create('surat', function (Blueprint $table) {
            $table->id();
            $table->string('jenis'); // masuk, keluar
            $table->string('nomor_surat');
            $table->date('tanggal_surat');
            $table->date('tanggal_terima')->nullable();
            $table->string('perihal');
            $table->string('instansi'); // pengirim / tujuan
            $table->text('keterangan')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_size')->nullable();
            $table->text('link_drive')->nullable();
            $table->string('status')->default('Terkirim');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['jenis', 'tanggal_surat']);
            $table->index('nomor_surat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat');
    }
};
