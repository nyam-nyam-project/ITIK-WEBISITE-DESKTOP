<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    /**
     * Seed one demo account for each application role.
     */
    public function run(): void
    {
        $now = now();

        DB::table('tm_user')->insertOrIgnore([
            [
                'id_user' => 'U001',
                'nomor_induk' => '198001',
                'nama_lengkap' => 'Budi Guru',
                'role' => 'guru',
                'kelas' => null,
                'email' => 'budi@guru.sch.id',
                'password' => Hash::make('password123'),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_user' => 'U002',
                'nomor_induk' => '202401',
                'nama_lengkap' => 'Siti Siswa',
                'role' => 'siswa',
                'kelas' => 'X',
                'email' => 'siti@siswa.sch.id',
                'password' => Hash::make('password123'),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_user' => 'U003',
                'nomor_induk' => '199001',
                'nama_lengkap' => 'Andi Operator',
                'role' => 'admin',
                'kelas' => null,
                'email' => 'andi@sekolah.sch.id',
                'password' => Hash::make('password123'),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
