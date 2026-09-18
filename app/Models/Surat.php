<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Surat extends Model
{
    protected $table = 'surat';

    protected $fillable = [
        'jenis',
        'nomor_surat',
        'tanggal_surat',
        'tanggal_terima',
        'perihal',
        'instansi',
        'keterangan',
        'file_path',
        'file_name',
        'file_size',
        'link_drive',
        'status',
        'uploaded_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getDownloadUrlAttribute()
    {
        if (!empty($this->link_drive)) {
            return $this->link_drive;
        }

        if (!empty($this->file_path)) {
            return asset($this->file_path);
        }

        return '#';
    }
}
