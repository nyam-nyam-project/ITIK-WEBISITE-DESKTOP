<?php

namespace Tests\Feature;

use Database\Seeders\UserRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserRoleSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_one_hashed_demo_account_per_role_idempotently(): void
    {
        $seeder = new UserRoleSeeder;
        $seeder->run();
        $seeder->run();

        $users = DB::table('tm_user')->orderBy('role')->get();

        $this->assertCount(3, $users);
        $this->assertSame(['admin', 'guru', 'siswa'], $users->pluck('role')->all());
        $this->assertTrue(Hash::check('password123', $users->firstWhere('role', 'admin')->password));
    }
}
