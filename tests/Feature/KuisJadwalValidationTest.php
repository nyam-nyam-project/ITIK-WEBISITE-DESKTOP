<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Mapel;
use App\Models\Pengajaran;
use App\Models\TahunAjaran;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class KuisJadwalValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $guru;

    private Mapel $mapel;

    private TahunAjaran $tahunAjaran;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guru = User::create([
            'id_user' => 'U001',
            'nomor_induk' => '198001',
            'nama_lengkap' => 'Guru Penguji',
            'email' => 'guru@sekolah.sch.id',
            'password' => bcrypt('password123'),
            'role' => 'guru',
            'status' => 'aktif',
            'wajib_ganti_password' => false,
        ]);

        $this->tahunAjaran = TahunAjaran::create([
            'id_tahun_ajaran' => 'TA01',
            'nama' => '2026/2027 Ganjil',
            'is_aktif' => true,
        ]);

        $this->mapel = Mapel::create([
            'id_mapel' => 'MP01',
            'kode_mapel' => 'TIK',
            'nama_mapel' => 'Dasar TIK',
            'is_aktif' => true,
        ]);

        $kelas = Kelas::create([
            'id_kelas' => 'KLS01',
            'nama_kelas' => 'VII-A',
            'tingkat' => '7',
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'is_aktif' => true,
        ]);

        DB::table('tp_mapel_kelas')->insert([
            'id_mapel' => $this->mapel->id_mapel,
            'id_kelas' => $kelas->id_kelas,
        ]);

        Pengajaran::create([
            'id_pengajaran' => 'P001',
            'id_user' => $this->guru->id_user,
            'id_kelas' => $kelas->id_kelas,
            'id_mapel' => $this->mapel->id_mapel,
        ]);
    }

    public function test_store_kuis_menolak_waktu_mulai_di_masa_lalu(): void
    {
        $response = $this->actingAs($this->guru)->post(route('kuis.store'), [
            'judul' => 'Kuis Harian 1',
            'kategori' => 'kuis harian',
            'id_mapel' => $this->mapel->id_mapel,
            'tingkat_kesulitan' => 'mudah',
            'waktu_mulai' => Carbon::now()->subMinutes(10)->format('Y-m-d H:i'),
        ]);

        $response->assertSessionHasErrors(['waktu_mulai']);
    }

    public function test_store_kuis_menolak_waktu_selesai_di_masa_lalu(): void
    {
        $response = $this->actingAs($this->guru)->post(route('kuis.store'), [
            'judul' => 'Kuis Harian 2',
            'kategori' => 'kuis harian',
            'id_mapel' => $this->mapel->id_mapel,
            'tingkat_kesulitan' => 'mudah',
            'waktu_selesai' => Carbon::now()->subMinutes(5)->format('Y-m-d H:i'),
        ]);

        $response->assertSessionHasErrors(['waktu_selesai']);
    }

    public function test_store_kuis_menolak_waktu_selesai_sebelum_atau_sama_dengan_waktu_mulai(): void
    {
        $waktuMulai = Carbon::now()->addHours(2)->format('Y-m-d H:i');
        $waktuSelesai = Carbon::now()->addHour()->format('Y-m-d H:i');

        $response = $this->actingAs($this->guru)->post(route('kuis.store'), [
            'judul' => 'Kuis Harian 3',
            'kategori' => 'kuis harian',
            'id_mapel' => $this->mapel->id_mapel,
            'tingkat_kesulitan' => 'mudah',
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
        ]);

        $response->assertSessionHasErrors(['waktu_selesai']);
    }

    public function test_store_kuis_menerima_jadwal_valid(): void
    {
        $waktuMulai = Carbon::now()->addHour()->format('Y-m-d H:i');
        $waktuSelesai = Carbon::now()->addHours(3)->format('Y-m-d H:i');

        $response = $this->actingAs($this->guru)->post(route('kuis.store'), [
            'judul' => 'Ulangan Tengah Semester',
            'kategori' => 'ulangan',
            'id_mapel' => $this->mapel->id_mapel,
            'tingkat_kesulitan' => 'sedang',
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tm_kuis', [
            'judul' => 'Ulangan Tengah Semester',
            'kategori' => 'ulangan',
        ]);
    }

    public function test_update_kuis_menolak_waktu_mulai_di_masa_lalu(): void
    {
        $kuis = Kuis::create([
            'id_kuis' => 'KVAL01',
            'judul' => 'Kuis Asli',
            'kategori' => 'kuis harian',
            'tingkat_kesulitan' => 'mudah',
            'id_mapel' => $this->mapel->id_mapel,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'id_user' => $this->guru->id_user,
            'status_publikasi' => 'draft',
            'is_aktif' => true,
        ]);

        $response = $this->actingAs($this->guru)->put(route('kuis.update', $kuis->id_kuis), [
            'judul' => 'Kuis Asli Diupdate',
            'kategori' => 'kuis harian',
            'id_mapel' => $this->mapel->id_mapel,
            'tingkat_kesulitan' => 'mudah',
            'waktu_mulai' => Carbon::now()->subMinutes(15)->format('Y-m-d H:i'),
        ]);

        $response->assertSessionHasErrors(['waktu_mulai']);
    }

    public function test_update_kuis_menolak_waktu_selesai_kurang_dari_waktu_mulai(): void
    {
        $kuis = Kuis::create([
            'id_kuis' => 'KVAL02',
            'judul' => 'Kuis Jadwal Terbalik',
            'kategori' => 'kuis harian',
            'tingkat_kesulitan' => 'mudah',
            'id_mapel' => $this->mapel->id_mapel,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'id_user' => $this->guru->id_user,
            'status_publikasi' => 'draft',
            'is_aktif' => true,
        ]);

        $response = $this->actingAs($this->guru)->put(route('kuis.update', $kuis->id_kuis), [
            'judul' => 'Kuis Jadwal Terbalik Diupdate',
            'kategori' => 'kuis harian',
            'id_mapel' => $this->mapel->id_mapel,
            'tingkat_kesulitan' => 'mudah',
            'waktu_mulai' => Carbon::now()->addHours(2)->format('Y-m-d H:i'),
            'waktu_selesai' => Carbon::now()->addHours(1)->format('Y-m-d H:i'),
        ]);

        $response->assertSessionHasErrors(['waktu_selesai']);
    }
}
