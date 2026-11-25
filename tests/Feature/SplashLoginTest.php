<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SplashLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        User::create([
            'id_user' => 'S_SPLASH_1',
            'nomor_induk' => '202601',
            'nama_lengkap' => 'Siswa Splash',
            'role' => 'siswa',
            'status' => 'aktif',
            'password' => Hash::make('password123'),
            'wajib_ganti_password' => false,
        ]);

        User::create([
            'id_user' => 'G_SPLASH_1',
            'nomor_induk' => '198601',
            'nama_lengkap' => 'Guru Splash',
            'role' => 'guru',
            'status' => 'aktif',
            'password' => Hash::make('password123'),
            'wajib_ganti_password' => false,
        ]);

        User::create([
            'id_user' => 'A_SPLASH_1',
            'nomor_induk' => 'ADM01',
            'email' => 'admin.splash@sekolah.sch.id',
            'nama_lengkap' => 'Admin Splash',
            'role' => 'admin',
            'status' => 'aktif',
            'password' => Hash::make('password123'),
            'wajib_ganti_password' => false,
        ]);
    }

    public function test_penanda_ada_setelah_login_berhasil_untuk_siswa_guru_dan_admin(): void
    {
        $siswaResponse = $this->post(route('login.process'), [
            'nomor_induk' => '202601',
            'password' => 'password123',
        ]);
        $siswaResponse->assertRedirect(route('siswa.dashboard'));
        $siswaResponse->assertSessionHas('tampilkan_splash_login', true);

        $this->post(route('logout'));

        $guruResponse = $this->post(route('login.process'), [
            'nomor_induk' => '198601',
            'password' => 'password123',
        ]);
        $guruResponse->assertRedirect(route('dashboard'));
        $guruResponse->assertSessionHas('tampilkan_splash_login', true);

        $this->post(route('logout'));

        $adminResponse = $this->post(route('admin.login.process'), [
            'login' => 'admin.splash@sekolah.sch.id',
            'password' => 'password123',
        ]);
        $adminResponse->assertRedirect(route('admin.dashboard'));
        $adminResponse->assertSessionHas('tampilkan_splash_login', true);
    }

    public function test_penanda_tidak_ada_setelah_login_gagal(): void
    {
        $response = $this->post(route('login.process'), [
            'nomor_induk' => '202601',
            'password' => 'password-salah',
        ]);

        $response->assertSessionMissing('tampilkan_splash_login');

        $adminResponse = $this->post(route('admin.login.process'), [
            'login' => 'admin.splash@sekolah.sch.id',
            'password' => 'password-salah',
        ]);

        $adminResponse->assertSessionMissing('tampilkan_splash_login');
    }

    public function test_penanda_dihapus_setelah_dashboard_dibuka_sekali(): void
    {
        $this->post(route('login.process'), [
            'nomor_induk' => '202601',
            'password' => 'password123',
        ])->assertSessionHas('tampilkan_splash_login', true);

        $pertama = $this->get(route('siswa.dashboard'));
        $pertama->assertOk();
        $pertama->assertSee('id="login-splash-overlay"', false);
        $pertama->assertSee('role="status"', false);
        $pertama->assertSee('Memuat dashboard', false);
        $pertama->assertSee('css/splash.css', false);
        $pertama->assertSee('js/splash.js', false);
        $pertama->assertSessionMissing('tampilkan_splash_login');

        $kedua = $this->get(route('siswa.dashboard'));
        $kedua->assertOk();
        $kedua->assertDontSee('id="login-splash-overlay"', false);
        $kedua->assertDontSee('css/splash.css', false);
        $kedua->assertDontSee('js/splash.js', false);
    }

    public function test_tidak_ada_script_atau_style_inline_di_partial_splash_dan_layout(): void
    {
        $files = [
            resource_path('views/partials/splash.blade.php'),
            resource_path('views/layouts/app.blade.php'),
        ];

        foreach ($files as $path) {
            $contents = File::get($path);
            $this->assertDoesNotMatchRegularExpression('/<style\b|\s(?:style|on[a-z]+)\s*=/i', $contents, $path);
            preg_match_all('/<script\b[^>]*>([\s\S]*?)<\/script>/i', $contents, $scripts);
            foreach ($scripts[1] as $script) {
                $this->assertSame('', trim($script), $path);
            }
        }
    }
}
