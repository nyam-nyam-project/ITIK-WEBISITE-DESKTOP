<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminStudentClassPlacementTest extends TestCase
{
    private User $admin;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());

        $this->withoutVite();
        $this->createSchema();
        $this->seedStudentsAndClasses();
    }

    private function createSchema(): void
    {
        Schema::create('tm_user', function (Blueprint $table): void {
            $table->string('id_user', 50)->primary();
            $table->string('nomor_induk', 50)->unique();
            $table->string('nama_lengkap', 100);
            $table->string('role', 20);
            $table->string('status', 20)->default('aktif');
            $table->string('kelas', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('password');
            $table->boolean('wajib_ganti_password')->default(false);
            $table->string('dibuat_oleh', 50)->nullable();
            $table->string('foto')->nullable();
            $table->string('remember_token')->nullable();
            $table->timestamps();
        });

        Schema::create('tm_tahun_ajaran', function (Blueprint $table): void {
            $table->string('id_tahun_ajaran', 50)->primary();
            $table->string('nama', 50);
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('is_aktif')->default(false);
            $table->timestamps();
        });

        Schema::create('tm_kelas', function (Blueprint $table): void {
            $table->string('id_kelas', 50)->primary();
            $table->string('nama_kelas', 50);
            $table->string('tingkat', 10);
            $table->string('id_tahun_ajaran', 50);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('tp_anggota_kelas', function (Blueprint $table): void {
            $table->bigIncrements('id_anggota_kelas');
            $table->string('id_kelas', 50);
            $table->string('id_user', 50);
            $table->timestamp('created_at')->nullable();
            $table->unique(['id_kelas', 'id_user']);
        });
    }

    private function seedStudentsAndClasses(): void
    {
        $this->admin = User::create([
            'id_user' => 'U001',
            'nomor_induk' => '000001',
            'nama_lengkap' => 'Admin Sekolah',
            'role' => 'admin',
            'status' => 'aktif',
            'password' => 'secret123',
        ]);

        $this->student = User::create([
            'id_user' => 'U002',
            'nomor_induk' => '000002',
            'nama_lengkap' => 'Siswa Uji',
            'role' => 'siswa',
            'status' => 'aktif',
            'password' => 'secret123',
        ]);

        TahunAjaran::create([
            'id_tahun_ajaran' => 'TA001',
            'nama' => '2026/2027',
            'is_aktif' => true,
        ]);
        TahunAjaran::create([
            'id_tahun_ajaran' => 'TA000',
            'nama' => '2025/2026',
            'is_aktif' => false,
        ]);

        Kelas::create([
            'id_kelas' => 'K001',
            'nama_kelas' => 'X TKJ 1',
            'tingkat' => 'X',
            'id_tahun_ajaran' => 'TA001',
            'is_aktif' => true,
        ]);
        Kelas::create([
            'id_kelas' => 'K002',
            'nama_kelas' => 'X TKJ 2',
            'tingkat' => 'X',
            'id_tahun_ajaran' => 'TA001',
            'is_aktif' => true,
        ]);
        Kelas::create([
            'id_kelas' => 'K000',
            'nama_kelas' => 'IX TKJ 1',
            'tingkat' => 'IX',
            'id_tahun_ajaran' => 'TA000',
            'is_aktif' => true,
        ]);

        DB::table('tp_anggota_kelas')->insert([
            ['id_kelas' => 'K001', 'id_user' => 'U002'],
            ['id_kelas' => 'K000', 'id_user' => 'U002'],
        ]);
    }

    public function test_student_edit_form_exposes_class_placement_controls(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.siswa.edit', $this->student))
            ->assertOk()
            ->assertSeeText('Penempatan kelas tahun ajaran aktif')
            ->assertSee('name="id_kelas"', false)
            ->assertSee('value="K001" selected', false)
            ->assertDontSee('value="K000"', false);
    }

    public function test_admin_can_update_current_year_student_classes_without_erasing_history(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.siswa.update', $this->student), [
                'nomor_induk' => '000002',
                'nama_lengkap' => 'Siswa Uji',
                'status' => 'aktif',
                'id_kelas' => 'K002',
            ])
            ->assertRedirect(route('admin.siswa.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('tp_anggota_kelas', ['id_user' => 'U002', 'id_kelas' => 'K001']);
        $this->assertDatabaseHas('tp_anggota_kelas', ['id_user' => 'U002', 'id_kelas' => 'K002']);
        $this->assertDatabaseHas('tp_anggota_kelas', ['id_user' => 'U002', 'id_kelas' => 'K000']);
    }

    public function test_admin_cannot_assign_student_to_class_from_another_year(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.siswa.update', $this->student), [
                'nomor_induk' => '000002',
                'nama_lengkap' => 'Siswa Uji',
                'status' => 'aktif',
                'id_kelas' => 'K000',
            ])
            ->assertSessionHasErrors('id_kelas');

        $this->assertDatabaseHas('tp_anggota_kelas', ['id_user' => 'U002', 'id_kelas' => 'K001']);
        $this->assertDatabaseHas('tp_anggota_kelas', ['id_user' => 'U002', 'id_kelas' => 'K000']);
        $this->assertDatabaseCount('tp_anggota_kelas', 2);
    }
}
