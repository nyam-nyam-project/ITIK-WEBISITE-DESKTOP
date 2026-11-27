<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DuplikasiCrudTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());

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
        });

        Schema::create('tm_tahun_ajaran', function (Blueprint $table): void {
            $table->string('id_tahun_ajaran', 50)->primary();
            $table->string('nama', 20)->unique();
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

        Schema::create('tm_mapel', function (Blueprint $table): void {
            $table->string('id_mapel', 50)->primary();
            $table->string('kode_mapel', 20)->unique();
            $table->string('nama_mapel', 100);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('tp_mapel_kelas', function (Blueprint $table): void {
            $table->string('id_mapel', 50);
            $table->string('id_kelas', 50);
            $table->primary(['id_mapel', 'id_kelas']);
        });

        Schema::create('tp_anggota_kelas', function (Blueprint $table): void {
            $table->bigIncrements('id_anggota_kelas');
            $table->string('id_kelas', 50);
            $table->string('id_user', 50);
            $table->timestamp('created_at')->nullable();
            $table->unique(['id_kelas', 'id_user']);
        });

        Schema::create('tr_pengajaran', function (Blueprint $table): void {
            $table->increments('id_pengajaran');
            $table->string('id_user', 50);
            $table->string('id_kelas', 50);
            $table->string('id_mapel', 50);
            $table->timestamp('created_at')->nullable();
            $table->unique(['id_user', 'id_kelas', 'id_mapel']);
        });
    }

    public function test_pengajaran_duplikat_ditolak_dengan_error_bukan_berhasil(): void
    {
        $admin = $this->seedMasterData();

        DB::table('tr_pengajaran')->insert([
            'id_user' => 'G001',
            'id_kelas' => 'KLS001',
            'id_mapel' => 'MP001',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.pengajaran.store'), [
            'id_user' => 'G001',
            'id_kelas' => ['KLS001'],
            'id_mapel' => ['MP001'],
        ]);

        $response->assertSessionHasErrors('id_mapel');
        $response->assertSessionMissing('success');
        $this->assertDatabaseCount('tr_pengajaran', 1);
    }

    public function test_pengajaran_sebagian_baru_tetap_menambahkan_yang_belum_ada(): void
    {
        $admin = $this->seedMasterData();

        DB::table('tr_pengajaran')->insert([
            'id_user' => 'G001',
            'id_kelas' => 'KLS001',
            'id_mapel' => 'MP001',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.pengajaran.store'), [
            'id_user' => 'G001',
            'id_kelas' => ['KLS001'],
            'id_mapel' => ['MP001', 'MP002'],
        ]);

        $response->assertRedirect(route('admin.pengajaran.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseCount('tr_pengajaran', 2);
    }

    public function test_mapel_menolak_nama_mapel_duplikat(): void
    {
        $admin = $this->seedMasterData();

        $response = $this->actingAs($admin)->post(route('admin.mapel.store'), [
            'kode_mapel' => 'TIK_BARU',
            'nama_mapel' => 'Teknologi Informasi',
            'is_aktif' => '1',
        ]);

        $response->assertSessionHasErrors('nama_mapel');
        $this->assertDatabaseCount('tm_mapel', 2);
    }

    public function test_tambah_anggota_kelas_yang_sudah_terdaftar_mengembalikan_error(): void
    {
        $admin = $this->seedMasterData();

        DB::table('tp_anggota_kelas')->insert([
            'id_kelas' => 'KLS001',
            'id_user' => 'S001',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.kelas.anggota.store', 'KLS001'), [
            'id_user' => ['S001'],
        ]);

        $response->assertRedirect(route('admin.kelas.show', 'KLS001'));
        $response->assertSessionHas('error');
        $response->assertSessionMissing('success');
        $this->assertDatabaseCount('tp_anggota_kelas', 1);
    }

    private function seedMasterData(): User
    {
        $admin = User::query()->create([
            'id_user' => 'A001',
            'nama_lengkap' => 'Admin Test',
            'email' => 'admin@test.local',
            'role' => 'admin',
            'status' => 'aktif',
            'password' => Hash::make('rahasia123'),
        ]);

        User::query()->create([
            'id_user' => 'G001',
            'nomor_induk' => '198001',
            'nama_lengkap' => 'Guru Satu',
            'role' => 'guru',
            'status' => 'aktif',
            'password' => Hash::make('rahasia123'),
        ]);

        User::query()->create([
            'id_user' => 'S001',
            'nomor_induk' => '202401',
            'nama_lengkap' => 'Siswa Satu',
            'role' => 'siswa',
            'status' => 'aktif',
            'password' => Hash::make('rahasia123'),
        ]);

        DB::table('tm_tahun_ajaran')->insert([
            'id_tahun_ajaran' => 'TA001',
            'nama' => '2026/2027',
            'is_aktif' => true,
        ]);

        DB::table('tm_kelas')->insert([
            'id_kelas' => 'KLS001',
            'nama_kelas' => 'X TKJ 1',
            'tingkat' => 'X',
            'id_tahun_ajaran' => 'TA001',
            'is_aktif' => true,
        ]);

        DB::table('tm_mapel')->insert([
            [
                'id_mapel' => 'MP001',
                'kode_mapel' => 'TIK1',
                'nama_mapel' => 'Teknologi Informasi',
                'is_aktif' => true,
            ],
            [
                'id_mapel' => 'MP002',
                'kode_mapel' => 'JARKOM',
                'nama_mapel' => 'Jaringan Komputer',
                'is_aktif' => true,
            ],
        ]);

        DB::table('tp_mapel_kelas')->insert([
            ['id_mapel' => 'MP001', 'id_kelas' => 'KLS001'],
            ['id_mapel' => 'MP002', 'id_kelas' => 'KLS001'],
        ]);

        return $admin;
    }
}
