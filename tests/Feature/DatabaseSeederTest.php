<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_demo_data_for_all_roles_idempotently(): void
    {
        $seeder = new DatabaseSeeder;
        $seeder->run();
        $seeder->run();

        $this->assertDatabaseCount('tm_user', 3);
        $this->assertDatabaseCount('tm_tahun_ajaran', 1);
        $this->assertDatabaseCount('tm_mapel', 1);
        $this->assertDatabaseCount('tm_kelas', 2);
        $this->assertDatabaseCount('tm_materi', 3);
        $this->assertDatabaseCount('tm_kuis', 3);
        $this->assertDatabaseCount('tm_soal', 6);

        $admin = DB::table('tm_user')->where('role', 'admin')->first();

        $this->assertSame('U003', $admin->id_user);
        $this->assertTrue(Hash::check('password123', $admin->password));
    }
}
