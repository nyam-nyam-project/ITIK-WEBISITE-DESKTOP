<?php

namespace Tests\Feature;

use App\Models\Kuis;
use App\Models\Mapel;
use App\Models\TahunAjaran;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KuisAutoPublishTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        User::create([
            'id_user' => 'U001',
            'nomor_induk' => '198001',
            'nama_lengkap' => 'Guru Penguji',
            'email' => 'guru@sekolah.sch.id',
            'password' => bcrypt('password123'),
            'role' => 'guru',
            'status' => 'aktif',
            'wajib_ganti_password' => false,
        ]);

        TahunAjaran::create([
            'id_tahun_ajaran' => 'TA01',
            'nama' => '2026/2027 Ganjil',
            'is_aktif' => true,
        ]);

        Mapel::create([
            'id_mapel' => 'MP01',
            'kode_mapel' => 'TIK',
            'nama_mapel' => 'Dasar TIK',
            'is_aktif' => true,
        ]);
    }

    public function test_kuis_otomatis_terbit_saat_waktu_mulai_tiba(): void
    {
        $kuis = Kuis::create([
            'id_kuis' => 'KTEST1',
            'judul' => 'Ulangan Auto Terbit',
            'kategori' => 'ulangan',
            'tingkat_kesulitan' => 'sedang',
            'id_mapel' => 'MP01',
            'id_tahun_ajaran' => 'TA01',
            'id_user' => 'U001',
            'alokasi_waktu' => 60,
            'kkm' => 75,
            'waktu_mulai' => Carbon::now()->subMinutes(5),
            'waktu_selesai' => Carbon::now()->addHour(),
            'status_publikasi' => 'draft',
            'is_aktif' => true,
        ]);

        $this->assertSame('draft', $kuis->status_publikasi);

        $this->artisan('kuis:auto-publish')
            ->assertSuccessful();

        $kuis->refresh();
        $this->assertSame('terbit', $kuis->status_publikasi);
    }

    public function test_kuis_otomatis_kembali_draft_saat_waktu_selesai_lewat(): void
    {
        $kuis = Kuis::create([
            'id_kuis' => 'KTEST2',
            'judul' => 'Ulangan Auto Selesai',
            'kategori' => 'ulangan',
            'tingkat_kesulitan' => 'sedang',
            'id_mapel' => 'MP01',
            'id_tahun_ajaran' => 'TA01',
            'id_user' => 'U001',
            'alokasi_waktu' => 60,
            'kkm' => 75,
            'waktu_mulai' => Carbon::now()->subHours(2),
            'waktu_selesai' => Carbon::now()->subMinutes(10),
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);

        $this->assertSame('terbit', $kuis->status_publikasi);

        $this->artisan('kuis:auto-publish')
            ->assertSuccessful();

        $kuis->refresh();
        $this->assertSame('draft', $kuis->status_publikasi);
    }
}
