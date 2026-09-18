<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Administrator Subbag PEP
        User::updateOrCreate(
            ['email' => 'admin@pep.go.id'],
            [
                'name' => 'Administrator Subbag PEP',
                'nip' => '198507142010011012',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'role' => 'admin',
                'unit_kerja' => 'Subbag PEP Dinas Pendidikan',
            ]
        );

        // 2. Akun Pegawai / User
        User::updateOrCreate(
            ['email' => 'user@pep.go.id'],
            [
                'name' => 'Pegawai Dinas Pendidikan',
                'nip' => '199203152018022005',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'role' => 'user',
                'unit_kerja' => 'Dinas Pendidikan',
            ]
        );
    }
}
