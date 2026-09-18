<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('check:db', function () {
    $this->info('Dokumen: ' . \App\Models\Dokumen::count());
    foreach (\App\Models\Dokumen::all() as $d) {
        $this->line("ID: {$d->id} | {$d->judul} | file: {$d->file_name} | drive: {$d->link_drive}");
    }
    $this->info('Surat: ' . \App\Models\Surat::count());
    foreach (\App\Models\Surat::all() as $s) {
        $this->line("ID: {$s->id} | {$s->perihal} | file: {$s->file_name} | drive: {$s->link_drive}");
    }
});
