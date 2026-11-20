<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminAccountTest extends TestCase
{
    private User $admin;

    private User $teacher;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());

        $this->withoutVite();
        $this->createSchema();
        $this->seedUsers();
    }

    private function createSchema(): void
    {
        Schema::create('tm_user', function (Blueprint $table): void {
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
            $table->foreign('dibuat_oleh')->references('id_user')->on('tm_user')->nullOnDelete();
        });
    }

    private function seedUsers(): void
    {
        $this->admin = $this->makeUser('U001', 'admin', 'admin_induk', 'Admin Utama', 'admin@example.test');
        $this->teacher = $this->makeUser('U002', 'guru', '198001', 'Guru Uji');
        $this->student = $this->makeUser('U003', 'siswa', '202401', 'Siswa Uji');
    }

    private function makeUser(
        string $id,
        string $role,
        string $username,
        string $name,
        ?string $email = null,
        string $status = 'aktif'
    ): User {
        return User::create([
            'id_user' => $id,
            'nomor_induk' => $username,
            'nama_lengkap' => $name,
            'role' => $role,
            'status' => $status,
            'email' => $email,
            'password' => 'secret123',
        ]);
    }

    public function test_admin_pages_are_forbidden_for_guru_and_siswa(): void
    {
        foreach ([$this->teacher, $this->student] as $user) {
            $this->actingAs($user)
                ->get(route('admin.admin.index'))
                ->assertForbidden();

            $this->get(route('admin.admin.create'))->assertForbidden();
        }
    }

    public function test_admin_can_create_another_admin_with_hashed_username_and_creator(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.admin.store'), [
                'username' => 'admin_baru',
                'nama_lengkap' => 'Admin Baru',
            ])
            ->assertRedirect(route('admin.admin.index'))
            ->assertSessionHasNoErrors();

        $createdAdmin = User::where('nomor_induk', 'admin_baru')->firstOrFail();

        $this->assertSame('admin', $createdAdmin->role);
        $this->assertSame('aktif', $createdAdmin->status);
        $this->assertSame('admin_baru', $createdAdmin->nomor_induk);
        $this->assertNull($createdAdmin->email);
        $this->assertTrue(Hash::check('admin_baru', $createdAdmin->password));
        $this->assertNotSame('admin_baru', $createdAdmin->password);
        $this->assertSame(1, (int) $createdAdmin->wajib_ganti_password);
        $this->assertSame($this->admin->id_user, $createdAdmin->dibuat_oleh);
    }

    public static function existingUsernames(): array
    {
        return [
            'existing NIP' => ['198001'],
            'existing NISN' => ['202401'],
        ];
    }

    #[DataProvider('existingUsernames')]
    public function test_username_must_be_unique_across_all_user_roles(string $username): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.admin.create'))
            ->post(route('admin.admin.store'), [
                'username' => $username,
                'nama_lengkap' => 'Admin Duplikat',
            ])
            ->assertRedirect(route('admin.admin.create'))
            ->assertSessionHasErrors('username');
    }

    public function test_optional_email_is_validated_for_format_and_uniqueness(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.admin.create'))
            ->post(route('admin.admin.store'), [
                'username' => 'admin_baru',
                'nama_lengkap' => 'Admin Baru',
                'email' => 'bukan-email',
            ])
            ->assertSessionHasErrors('email');

        $this->from(route('admin.admin.create'))
            ->post(route('admin.admin.store'), [
                'username' => 'admin_lain',
                'nama_lengkap' => 'Admin Lain',
                'email' => 'admin@example.test',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_admin_list_shows_system_for_accounts_without_a_creator(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.admin.index'))
            ->assertOk()
            ->assertSeeText('Admin Utama')
            ->assertSeeText('admin_induk')
            ->assertSeeText('Sistem');
    }

    public function test_admin_cannot_deactivate_their_own_account(): void
    {
        $this->makeUser('U004', 'admin', 'admin_lain', 'Admin Lain');

        $this->actingAs($this->admin)
            ->from(route('admin.admin.index'))
            ->patch(route('admin.admin.toggle-status', $this->admin))
            ->assertRedirect(route('admin.admin.index'))
            ->assertSessionHasErrors('admin');

        $this->assertDatabaseHas('tm_user', ['id_user' => $this->admin->id_user, 'status' => 'aktif']);
    }

    public function test_last_active_admin_cannot_be_deactivated(): void
    {
        $onlyAdmin = User::where('role', 'admin')->firstOrFail();

        $this->actingAs($onlyAdmin)
            ->from(route('admin.admin.index'))
            ->patch(route('admin.admin.toggle-status', $onlyAdmin))
            ->assertRedirect(route('admin.admin.index'))
            ->assertSessionHasErrors('admin');

        $this->assertDatabaseHas('tm_user', ['id_user' => $onlyAdmin->id_user, 'status' => 'aktif']);
    }

    public function test_admin_can_deactivate_and_reactivate_another_admin(): void
    {
        $otherAdmin = $this->makeUser('U004', 'admin', 'admin_lain', 'Admin Lain');

        $this->actingAs($this->admin)
            ->patch(route('admin.admin.toggle-status', $otherAdmin))
            ->assertRedirect(route('admin.admin.index'))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tm_user', ['id_user' => $otherAdmin->id_user, 'status' => 'nonaktif']);

        $this->patch(route('admin.admin.toggle-status', $otherAdmin))
            ->assertRedirect(route('admin.admin.index'))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tm_user', ['id_user' => $otherAdmin->id_user, 'status' => 'aktif']);
    }

    public function test_nonaktif_admin_cannot_login_with_username(): void
    {
        $inactiveAdmin = $this->makeUser('U004', 'admin', 'admin_nonaktif', 'Admin Nonaktif', null, 'nonaktif');

        $this->from(route('admin.login'))
            ->post(route('admin.login.process'), [
                'login' => 'admin_nonaktif',
                'password' => 'secret123',
            ])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('login');

        $this->assertGuest();
        $this->assertSame('nonaktif', $inactiveAdmin->fresh()->status);
    }

    public function test_active_admin_without_email_can_login_with_username(): void
    {
        $newAdmin = $this->makeUser('U004', 'admin', 'admin_baru', 'Admin Baru');
        $newAdmin->update(['wajib_ganti_password' => true]);

        $this->from(route('admin.login'))
            ->post(route('admin.login.process'), [
                'login' => 'admin_baru',
                'password' => 'secret123',
            ])
            ->assertRedirect(route('password.wajib'));

        $this->assertAuthenticatedAs($newAdmin);
    }

    public function test_admin_account_has_no_delete_route(): void
    {
        $this->assertFalse(Route::has('admin.admin.destroy'));
    }
}
