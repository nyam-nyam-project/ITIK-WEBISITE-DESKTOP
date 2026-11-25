<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityNfrTest extends TestCase
{
    use RefreshDatabase;

    private function akun(string $role, array $extra = []): User
    {
        return User::factory()->create(array_merge([
            'role' => $role,
            'status' => 'aktif',
            'password' => Hash::make('Rahasia123'),
        ], $extra));
    }

    /** SEC-04 / NFR-S02: siswa tidak boleh membuka halaman admin atau guru */
    public function test_siswa_ditolak_mengakses_rute_admin_dan_guru(): void
    {
        $siswa = $this->akun('siswa');

        $this->assertNotSame(200, $this->actingAs($siswa)->get('/admin')->getStatusCode());
        $this->assertNotSame(200, $this->actingAs($siswa)->get('/guru')->getStatusCode());
    }

    /** SEC-03 / NFR-S01: login API maksimal 5 percobaan per menit */
    public function test_login_api_diblokir_setelah_5_percobaan(): void
    {
        $this->akun('siswa', ['nomor_induk' => '2222']);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', ['nomor_induk' => '2222', 'password' => 'salah']);
        }

        $this->postJson('/api/login', ['nomor_induk' => '2222', 'password' => 'salah'])
            ->assertStatus(429);
    }

    /** SEC-03 / NFR-S01: login web juga harus dibatasi */
    public function test_login_web_diblokir_setelah_banyak_percobaan_salah(): void
    {
        $this->akun('siswa', ['nomor_induk' => '1111']);

        for ($i = 0; $i < 10; $i++) {
            $this->post('/login', ['nomor_induk' => '1111', 'password' => 'salah']);
        }

        $this->post('/login', ['nomor_induk' => '1111', 'password' => 'salah'])
            ->assertStatus(429);
    }

    /** NFR-S03: akun nonaktif tidak bisa login */
    public function test_akun_nonaktif_tidak_bisa_login_api(): void
    {
        $this->akun('siswa', ['nomor_induk' => '3333', 'status' => 'nonaktif']);

        $this->postJson('/api/login', ['nomor_induk' => '3333', 'password' => 'Rahasia123'])
            ->assertStatus(422);
    }
}