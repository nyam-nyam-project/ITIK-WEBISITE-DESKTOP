<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\Mengerjakan;
use App\Models\Progress;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SiswaDashboardOptimasiTest extends TestCase
{
    use RefreshDatabase;

    private User $siswa;

    private User $guru;

    private Mapel $mapel;

    private TahunAjaran $tahunAjaran;

    private Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siswa = User::create([
            'id_user' => 'S001',
            'nomor_induk' => '1001',
            'nama_lengkap' => 'Siswa Penguji',
            'role' => 'siswa',
            'status' => 'aktif',
            'password' => bcrypt('password123'),
        ]);

        $this->guru = User::create([
            'id_user' => 'G001',
            'nomor_induk' => '2001',
            'nama_lengkap' => 'Guru Penguji',
            'role' => 'guru',
            'status' => 'aktif',
            'password' => bcrypt('password123'),
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

        $this->kelas = Kelas::create([
            'id_kelas' => 'KLS01',
            'nama_kelas' => 'X TKJ 1',
            'tingkat' => 'X',
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'is_aktif' => true,
        ]);

        DB::table('tp_anggota_kelas')->insert([
            'id_kelas' => $this->kelas->id_kelas,
            'id_user' => $this->siswa->id_user,
        ]);

        DB::table('tp_mapel_kelas')->insert([
            'id_mapel' => $this->mapel->id_mapel,
            'id_kelas' => $this->kelas->id_kelas,
        ]);
    }

    public function test_materi_terbaru_hanya_menampilkan_belum_mulai_atau_sedang_belajar(): void
    {
        $materi1 = Materi::create([
            'id_materi' => 'MAT01',
            'judul' => 'Materi Belum Mulai',
            'id_mapel' => $this->mapel->id_mapel,
            'id_user' => $this->guru->id_user,
            'is_aktif' => true,
        ]);

        $materi2 = Materi::create([
            'id_materi' => 'MAT02',
            'judul' => 'Materi Sedang Belajar',
            'id_mapel' => $this->mapel->id_mapel,
            'id_user' => $this->guru->id_user,
            'is_aktif' => true,
        ]);

        $materi3 = Materi::create([
            'id_materi' => 'MAT03',
            'judul' => 'Materi Selesai Dibaca',
            'id_mapel' => $this->mapel->id_mapel,
            'id_user' => $this->guru->id_user,
            'is_aktif' => true,
        ]);

        Progress::create([
            'id_progress' => 'P01',
            'id_materi' => $materi2->id_materi,
            'id_user' => $this->siswa->id_user,
            'status' => 'sedang belajar',
        ]);

        Progress::create([
            'id_progress' => 'P02',
            'id_materi' => $materi3->id_materi,
            'id_user' => $this->siswa->id_user,
            'status' => 'selesai',
        ]);

        $response = $this->actingAs($this->siswa)->get(route('siswa.dashboard'));

        $response->assertOk();
        $response->assertSee('Materi Belum Mulai');
        $response->assertSee('Materi Sedang Belajar');
        // Materi 3 yang sudah selesai harus dikecualikan dari daftar Materi Terbaru
        $response->assertDontSee('Materi Selesai Dibaca');
        // Badge sedang belajar berwarna kuning (badge-warning)
        $response->assertSee('badge badge-warning');
        $response->assertSee('Sedang Belajar');
    }

    public function test_kuis_terbaru_mengecualikan_kuis_yang_sudah_dikerjakan(): void
    {
        $kuisBelum = Kuis::create([
            'id_kuis' => 'K01',
            'judul' => 'Kuis Belum Dikerjakan',
            'kategori' => 'kuis harian',
            'tingkat_kesulitan' => 'mudah',
            'id_mapel' => $this->mapel->id_mapel,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'id_user' => $this->guru->id_user,
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);

        $kuisSudah = Kuis::create([
            'id_kuis' => 'K02',
            'judul' => 'Kuis Sudah Dikerjakan',
            'kategori' => 'kuis harian',
            'tingkat_kesulitan' => 'mudah',
            'id_mapel' => $this->mapel->id_mapel,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'id_user' => $this->guru->id_user,
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);

        DB::table('tp_kuis_kelas')->insert([
            ['id_kuis' => $kuisBelum->id_kuis, 'id_kelas' => $this->kelas->id_kelas],
            ['id_kuis' => $kuisSudah->id_kuis, 'id_kelas' => $this->kelas->id_kelas],
        ]);

        Mengerjakan::create([
            'id_kuis' => $kuisSudah->id_kuis,
            'id_user' => $this->siswa->id_user,
            'nilai' => 85,
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
        ]);

        $response = $this->actingAs($this->siswa)->get(route('siswa.dashboard'));

        $response->assertOk();
        $response->assertSee('Kuis Belum Dikerjakan');
        $response->assertDontSee('Kuis Sudah Dikerjakan');
    }

    public function test_banner_notifikasi_kuning_muncul_jika_ada_materi_atau_kuis_belum_selesai(): void
    {
        Materi::create([
            'id_materi' => 'MAT01',
            'judul' => 'Materi Satu',
            'id_mapel' => $this->mapel->id_mapel,
            'id_user' => $this->guru->id_user,
            'is_aktif' => true,
        ]);

        $kuis = Kuis::create([
            'id_kuis' => 'K01',
            'judul' => 'Kuis Satu',
            'kategori' => 'kuis harian',
            'tingkat_kesulitan' => 'mudah',
            'id_mapel' => $this->mapel->id_mapel,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'id_user' => $this->guru->id_user,
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);

        DB::table('tp_kuis_kelas')->insert([
            'id_kuis' => $kuis->id_kuis,
            'id_kelas' => $this->kelas->id_kelas,
        ]);

        $response = $this->actingAs($this->siswa)->get(route('siswa.dashboard'));

        $response->assertOk();
        $response->assertSee('banner-pengingat');
        $response->assertSee('Pengingat Belajar:');
        $response->assertSee('1 materi');
        $response->assertSee('1 kuis');
    }

    public function test_banner_notifikasi_disembunyikan_jika_seluruh_materi_dan_kuis_selesai(): void
    {
        $materi = Materi::create([
            'id_materi' => 'MAT01',
            'judul' => 'Materi Tuntas',
            'id_mapel' => $this->mapel->id_mapel,
            'id_user' => $this->guru->id_user,
            'is_aktif' => true,
        ]);

        Progress::create([
            'id_progress' => 'P01',
            'id_materi' => $materi->id_materi,
            'id_user' => $this->siswa->id_user,
            'status' => 'selesai',
        ]);

        $kuis = Kuis::create([
            'id_kuis' => 'K01',
            'judul' => 'Kuis Tuntas',
            'kategori' => 'kuis harian',
            'tingkat_kesulitan' => 'mudah',
            'id_mapel' => $this->mapel->id_mapel,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'id_user' => $this->guru->id_user,
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);

        DB::table('tp_kuis_kelas')->insert([
            'id_kuis' => $kuis->id_kuis,
            'id_kelas' => $this->kelas->id_kelas,
        ]);

        Mengerjakan::create([
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $this->siswa->id_user,
            'nilai' => 90,
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
        ]);

        $response = $this->actingAs($this->siswa)->get(route('siswa.dashboard'));

        $response->assertOk();
        // Banner pengingat tidak boleh muncul
        $response->assertDontSee('banner-pengingat');
        // Pesan ramah semua selesai muncul
        $response->assertSee('Semua materi pembelajaran sudah selesai dibaca');
        $response->assertSee('Semua kuis yang tersedia sudah kamu kerjakan');
    }
}
