<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CapaianKinerja extends Model
{
    protected $table = 'capaian_kinerja';

    protected $fillable = [
        'tahun',
        'triwulan',
        'sasaran',
        'indikator',
        'satuan',
        'target_tahunan',
        'pagu_anggaran',
        'target_tw1',
        'target_tw2',
        'target_tw3',
        'target_tw4',
        'realisasi_kinerja',
        'capaian_kinerja_persen',
        'predikat_kinerja',
        'realisasi_keuangan',
        'capaian_keuangan_persen',
        'bukti_link',
        'bukti_file_path',
        'bukti_file_name',
        'bukti_file_size',
        'bukti_keterangan',
        'status_bukti',
        'created_by',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'target_tahunan' => 'float',
        'pagu_anggaran' => 'float',
        'target_tw1' => 'float',
        'target_tw2' => 'float',
        'target_tw3' => 'float',
        'target_tw4' => 'float',
        'realisasi_kinerja' => 'float',
        'capaian_kinerja_persen' => 'float',
        'realisasi_keuangan' => 'float',
        'capaian_keuangan_persen' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
