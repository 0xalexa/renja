<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dokumen extends Model
{
    protected $table = 'dokumen';

    protected $fillable = [
        'modul',
        'kategori',
        'tahun_anggaran',
        'judul',
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
