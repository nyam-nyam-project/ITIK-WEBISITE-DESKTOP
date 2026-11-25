<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ModeGelapTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertTrue($this->app->environment('testing'));
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame('sqlite', DB::connection()->getDriverName());

        $this->withoutVite();

        Schema::create('tm_user', function (Blueprint $table) {
            $table->string('id_user', 50)->primary();
            $table->string('nomor_induk', 50)->nullable()->unique();
            $table->string('nama_lengkap', 100);
            $table->string('role', 20);
            $table->string('status', 20)->default('aktif');
            $table->string('kelas', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('password');
            $table->boolean('wajib_ganti_password')->default(false);
            $table->string('dibuat_oleh', 50)->nullable();
            $table->timestamps();
        });
        Schema::create('tm_tahun_ajaran', function (Blueprint $table) {
            $table->string('id_tahun_ajaran', 50)->primary();
            $table->string('nama_tahun_ajaran', 20);
            $table->boolean('is_aktif')->default(false);
        });

        Schema::create('tm_kelas', function (Blueprint $table) {
            $table->string('id_kelas', 50)->primary();
            $table->string('id_tahun_ajaran', 50);
            $table->string('nama_kelas', 50);
            $table->string('tingkat', 20)->nullable();
            $table->boolean('is_aktif')->default(true);
        });

        Schema::create('tp_anggota_kelas', function (Blueprint $table) {
            $table->string('id_kelas', 50);
            $table->string('id_user', 50);
            $table->boolean('is_aktif')->default(true);
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('tp_anggota_kelas');
        Schema::dropIfExists('tm_kelas');
        Schema::dropIfExists('tm_tahun_ajaran');
        Schema::dropIfExists('tm_user');

        parent::tearDown();
    }

    public function test_halaman_login_memuat_skrip_dan_css_tema(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('js/theme-init.js', false);
        $response->assertSee('css/theme.css', false);
    }

    public function test_halaman_portal_memuat_tombol_ganti_tema_dan_atribut_aksesibilitas(): void
    {
        $guru = User::create([
            'id_user' => 'U_THEME_1',
            'nomor_induk' => '19800101',
            'nama_lengkap' => 'Guru Tema',
            'role' => 'guru',
            'status' => 'aktif',
            'password' => Hash::make('password123'),
            'wajib_ganti_password' => false,
        ]);

        $response = $this->actingAs($guru)->get(route('profil'));

        $response->assertOk();
        $response->assertSee('js/theme-init.js', false);
        $response->assertSee('css/theme.css', false);
        $response->assertSee('js/theme.js', false);
        $response->assertSee('id="theme-toggle"', false);
        $response->assertSee('aria-pressed="false"', false);
    }

    public function test_file_tema_mendefinisikan_variabel_terang_dan_gelap(): void
    {
        $themeCss = File::get(public_path('css/theme.css'));
        $themeInitJs = File::get(public_path('js/theme-init.js'));
        $themeJs = File::get(public_path('js/theme.js'));

        $this->assertStringContainsString('[data-theme="light"]', $themeCss);
        $this->assertStringContainsString('[data-theme="dark"]', $themeCss);
        $this->assertStringContainsString('--bg:', $themeCss);
        $this->assertStringContainsString('--surface:', $themeCss);
        $this->assertStringContainsString('data-theme', $themeInitJs);
        $this->assertStringContainsString('prefers-color-scheme: dark', $themeInitJs);
        $this->assertStringContainsString('localStorage', $themeJs);
        $this->assertStringContainsString('aria-pressed', $themeJs);
    }

    public function test_tidak_ada_warna_hardcode_atau_inline_style_di_blade(): void
    {
        foreach (File::allFiles(resource_path('views')) as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $contents = $file->getContents();
            $this->assertDoesNotMatchRegularExpression(
                '/(?<!&)#[0-9a-fA-F]{3,8}\b|rgba?\(/i',
                $contents,
                'Warna hardcode ditemukan pada view: '.$file->getRelativePathname()
            );
        }
    }

    public function test_app_css_tidak_memiliki_warna_hardcode(): void
    {
        $appCss = File::get(public_path('css/app.css'));

        $this->assertDoesNotMatchRegularExpression(
            '/#[0-9a-fA-F]{3,8}\b|rgba?\(|(?<![\w-])white(?![\w-])/i',
            $appCss,
            'Masih terdapat warna hardcode di public/css/app.css'
        );
    }
}
