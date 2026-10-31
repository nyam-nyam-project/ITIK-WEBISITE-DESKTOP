<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Tes Fase 5:
 *   - Login via NISN (siswa) dan NIP (guru) di portal siswa/guru.
 *   - Login via Email & Password khusus administrator di /admin/login.
 *   - Pemisahan portal: admin ditolak di portal siswa/guru; non-admin ditolak di portal admin.
 *   - Middleware & alur paksa ganti password (wajib_ganti_password).
 *   - Penggantian password via modal profil (siswa & guru).
 */
class LoginNisnNipTest extends TestCase
{
    private User $admin;
    private User $guru;
    private User $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertTrue($this->app->environment('testing'));
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame('sqlite', config('database.connections.sqlite.driver'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertEmpty(config('database.connections.sqlite.url'));
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->assertSame(1, (int) DB::selectOne('PRAGMA foreign_keys')->foreign_keys);
        $this->assertSame('array', config('cache.default'));
        $this->assertSame('array', config('session.driver'));

        $this->withoutVite();
        $this->createSchema();
        $this->seedScenario();
    }

    private function createSchema(): void
    {
        Schema::create('user', function (Blueprint $table) {
            $table->string('id_user', 50)->primary();
            $table->string('nomor_induk', 50)->nullable()->unique();
            $table->string('nama_lengkap', 100);
            $table->enum('role', ['admin', 'guru', 'siswa']);
            $table->string('status', 20)->default('aktif');
            $table->string('kelas', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('password');
            $table->boolean('wajib_ganti_password')->default(false);
            $table->string('dibuat_oleh', 50)->nullable();
            $table->timestamps();
            $table->foreign('dibuat_oleh')->references('id_user')->on('user')->nullOnDelete();
        });
    }

    private function seedScenario(): void
    {
        $this->admin = User::create([
            'id_user' => 'U001',
            'nomor_induk' => '199001',
            'nama_lengkap' => 'Andi Admin',
            'role' => 'admin',
            'status' => 'aktif',
            'email' => 'andi@sekolah.sch.id',
            'password' => 'secret123',
            'wajib_ganti_password' => false,
        ]);

        $this->guru = User::create([
            'id_user' => 'U002',
            'nomor_induk' => '198001',
            'nama_lengkap' => 'Budi Guru',
            'role' => 'guru',
            'status' => 'aktif',
            'email' => 'budi@guru.sch.id',
            'password' => 'password123',
            'wajib_ganti_password' => false,
        ]);

        $this->siswa = User::create([
            'id_user' => 'U003',
            'nomor_induk' => '202401',
            'nama_lengkap' => 'Siti Siswa',
            'role' => 'siswa',
            'status' => 'aktif',
            'email' => 'siti@siswa.sch.id',
            'password' => 'password123',
            'wajib_ganti_password' => false,
        ]);
    }

    public function test_login_page_renders_with_nisn_nip_input_and_admin_portal_button(): void
    {
        $response = $this->get(route('login'));
        $response->assertOk();
        $response->assertSee('NISN / NIP');
        $response->assertSee('name="nomor_induk"', false);
        $response->assertSee('Login sebagai Administrator');
        $response->assertSee('198001'); // Guru NIP
        $response->assertSee('202401'); // Siswa NISN
        // Akun demo admin tidak boleh ada di halaman login siswa & guru
        $response->assertDontSee('andi@sekolah.sch.id');
    }

    public function test_admin_login_page_renders_with_email_and_admin_demo(): void
    {
        $response = $this->get(route('admin.login'));
        $response->assertOk();
        $response->assertSee('Login Admin');
        $response->assertSee('Email Administrator');
        $response->assertSee('andi@sekolah.sch.id');
        $response->assertSee('Kembali ke Login Siswa', false);
    }

    public function test_siswa_can_login_using_nisn(): void
    {
        $response = $this->post(route('login.process'), [
            'nomor_induk' => '202401',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('siswa.dashboard'));
        $this->assertAuthenticatedAs($this->siswa);
    }

    public function test_guru_can_login_using_nip(): void
    {
        $response = $this->post(route('login.process'), [
            'nomor_induk' => '198001',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('materi.index'));
        $this->assertAuthenticatedAs($this->guru);
    }

    public function test_admin_cannot_login_through_regular_login_portal(): void
    {
        $response = $this->from(route('login'))->post(route('login.process'), [
            'nomor_induk' => '199001',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['nomor_induk']);
        $this->assertGuest();
    }

    public function test_admin_can_login_through_admin_portal(): void
    {
        $response = $this->post(route('admin.login.process'), [
            'email' => 'andi@sekolah.sch.id',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_non_admin_cannot_login_through_admin_portal(): void
    {
        $response = $this->from(route('admin.login'))->post(route('admin.login.process'), [
            'email' => 'budi@guru.sch.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_inactive_admin_cannot_login_through_admin_portal(): void
    {
        $this->admin->update(['status' => 'nonaktif']);

        $response = $this->from(route('admin.login'))->post(route('admin.login.process'), [
            'email' => 'andi@sekolah.sch.id',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->from(route('login'))->post(route('login.process'), [
            'nomor_induk' => '202401',
            'password' => 'salah123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['nomor_induk', 'login', 'email']);
        $this->assertGuest();
    }

    public function test_inactive_account_cannot_login(): void
    {
        $this->siswa->update(['status' => 'nonaktif']);

        $response = $this->from(route('login'))->post(route('login.process'), [
            'nomor_induk' => '202401',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['nomor_induk', 'login', 'email']);
        $this->assertGuest();
    }

    public function test_user_with_wajib_ganti_password_redirected_to_wajib_ganti_page_on_login(): void
    {
        $this->siswa->update(['wajib_ganti_password' => true]);

        $response = $this->post(route('login.process'), [
            'nomor_induk' => '202401',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('password.wajib'));
        $this->assertAuthenticatedAs($this->siswa);
    }

    public function test_admin_with_wajib_ganti_password_redirected_to_wajib_ganti_page(): void
    {
        $this->admin->update(['wajib_ganti_password' => true]);

        $response = $this->post(route('admin.login.process'), [
            'email' => 'andi@sekolah.sch.id',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('password.wajib'));
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_middleware_blocks_access_to_dashboard_when_wajib_ganti_password_is_true(): void
    {
        $this->siswa->update(['wajib_ganti_password' => true]);

        $response = $this->actingAs($this->siswa)->get(route('siswa.dashboard'));
        $response->assertRedirect(route('password.wajib'));
    }

    public function test_successful_wajib_ganti_password_clears_flag_and_redirects(): void
    {
        $this->siswa->update(['wajib_ganti_password' => true]);

        $this->actingAs($this->siswa);

        $response = $this->post(route('password.wajib.update'), [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('siswa.dashboard'));
        $this->siswa->refresh();
        $this->assertFalse($this->siswa->wajib_ganti_password);
        $this->assertTrue(Hash::check('newpassword123', $this->siswa->password));
    }

    public function test_wajib_ganti_password_requires_min_6_and_confirmation(): void
    {
        $this->siswa->update(['wajib_ganti_password' => true]);

        $this->actingAs($this->siswa);

        $response = $this->from(route('password.wajib'))->post(route('password.wajib.update'), [
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertRedirect(route('password.wajib'));
        $response->assertSessionHasErrors('password');
        $this->assertTrue($this->siswa->fresh()->wajib_ganti_password);
    }

    public function test_profil_modal_password_change_succeeds_with_correct_current_password(): void
    {
        $this->actingAs($this->guru);

        $response = $this->put(route('profil.password'), [
            'password_lama' => 'password123',
            'password_baru' => 'newguru123',
            'password_baru_confirmation' => 'newguru123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->guru->refresh();
        $this->assertTrue(Hash::check('newguru123', $this->guru->password));
        $this->assertFalse($this->guru->wajib_ganti_password);
    }

    public function test_profil_modal_password_change_fails_when_current_password_is_wrong(): void
    {
        $this->actingAs($this->guru);

        $response = $this->put(route('profil.password'), [
            'password_lama' => 'wrongpass',
            'password_baru' => 'newguru123',
            'password_baru_confirmation' => 'newguru123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('password_lama');
        $this->guru->refresh();
        $this->assertTrue(Hash::check('password123', $this->guru->password));
    }

    public function test_profil_modal_password_change_fails_when_new_password_is_same_as_old(): void
    {
        $this->actingAs($this->guru);

        $response = $this->put(route('profil.password'), [
            'password_lama' => 'password123',
            'password_baru' => 'password123',
            'password_baru_confirmation' => 'password123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('password_baru');
        $this->guru->refresh();
        $this->assertTrue(Hash::check('password123', $this->guru->password));
    }

    public function test_profil_view_displays_ganti_password_button_for_siswa_and_guru(): void
    {
        $responseSiswa = $this->actingAs($this->siswa)->get(route('profil'));
        $responseSiswa->assertOk();
        $responseSiswa->assertSee('Ganti Password');
        $responseSiswa->assertSee('modalPassword');

        $responseGuru = $this->actingAs($this->guru)->get(route('profil'));
        $responseGuru->assertOk();
        $responseGuru->assertSee('Ganti Password');
        $responseGuru->assertSee('modalPassword');
    }
}
