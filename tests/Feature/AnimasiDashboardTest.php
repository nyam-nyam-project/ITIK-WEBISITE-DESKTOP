<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\Mengerjakan;
use App\Models\Pengajaran;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\DashboardKelasService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AnimasiDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $guru;

    private Kelas $kelas;

    private Mapel $mapel;

    private TahunAjaran $tahunAjaran;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->guru = User::create([
            'id_user' => 'G_ANIM_1',
            'nomor_induk' => '198501',
            'nama_lengkap' => 'Guru Animasi',
            'role' => 'guru',
            'status' => 'aktif',
            'password' => Hash::make('password123'),
            'wajib_ganti_password' => false,
        ]);

        $siswa1 = User::create([
            'id_user' => 'S_ANIM_1',
            'nomor_induk' => '202501',
            'nama_lengkap' => 'Siswa Satu',
            'role' => 'siswa',
            'status' => 'aktif',
            'password' => Hash::make('password123'),
            'wajib_ganti_password' => false,
        ]);

        $siswa2 = User::create([
            'id_user' => 'S_ANIM_2',
            'nomor_induk' => '202502',
            'nama_lengkap' => 'Siswa Dua',
            'role' => 'siswa',
            'status' => 'aktif',
            'password' => Hash::make('password123'),
            'wajib_ganti_password' => false,
        ]);

        $this->tahunAjaran = TahunAjaran::create([
            'id_tahun_ajaran' => 'TA_ANIM',
            'nama' => '2026/2027 Ganjil',
            'is_aktif' => true,
        ]);

        $this->kelas = Kelas::create([
            'id_kelas' => 'KLS_ANIM',
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'nama_kelas' => 'XI TKJ 1',
            'tingkat' => '11',
            'is_aktif' => true,
        ]);

        $this->mapel = Mapel::create([
            'id_mapel' => 'MP_ANIM',
            'kode_mapel' => 'TIK',
            'nama_mapel' => 'Dasar TIK',
            'is_aktif' => true,
        ]);

        DB::table('tp_anggota_kelas')->insert([
            ['id_kelas' => $this->kelas->id_kelas, 'id_user' => $siswa1->id_user],
            ['id_kelas' => $this->kelas->id_kelas, 'id_user' => $siswa2->id_user],
        ]);

        DB::table('tp_mapel_kelas')->insert([
            'id_mapel' => $this->mapel->id_mapel,
            'id_kelas' => $this->kelas->id_kelas,
        ]);

        Pengajaran::create([
            'id_pengajaran' => 'PJ_ANIM',
            'id_user' => $this->guru->id_user,
            'id_kelas' => $this->kelas->id_kelas,
            'id_mapel' => $this->mapel->id_mapel,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
        ]);

        Materi::create([
            'id_materi' => 'MAT_ANIM',
            'id_user' => $this->guru->id_user,
            'id_mapel' => $this->mapel->id_mapel,
            'judul' => 'Pengenalan Jaringan',
            'bab' => 'Bab 1',
            'is_aktif' => true,
        ]);

        $kuis = Kuis::create([
            'id_kuis' => 'KUIS_ANIM',
            'id_user' => $this->guru->id_user,
            'id_mapel' => $this->mapel->id_mapel,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'judul' => 'Kuis Jaringan Dasar',
            'kategori' => 'kuis harian',
            'alokasi_waktu' => 15,
            'kkm' => 75,
            'status' => 'terbit',
        ]);

        DB::table('tp_kuis_kelas')->insert([
            'id_kuis' => $kuis->id_kuis,
            'id_kelas' => $this->kelas->id_kelas,
        ]);

        Mengerjakan::create([
            'id_mengerjakan' => 'MK_ANIM_1',
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $siswa1->id_user,
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
            'nilai' => 85,
            'mulai_pada' => now()->subMinutes(10),
            'selesai_pada' => now(),
        ]);

        Mengerjakan::create([
            'id_mengerjakan' => 'MK_ANIM_2',
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $siswa2->id_user,
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
            'nilai' => 60,
            'mulai_pada' => now()->subMinutes(10),
            'selesai_pada' => now(),
        ]);
    }

    public function test_dashboard_guru_memuat_atribut_count_up_dan_nilai_sama_dengan_service(): void
    {
        $service = app(DashboardKelasService::class);
        $ringkasan = $service->ringkasanKelas(
            $this->guru,
            $this->kelas->id_kelas,
            $this->mapel->id_mapel
        );
        $kategoriKelulusan = $service->kelulusanPerKategori(
            $this->guru,
            $this->kelas->id_kelas,
            $this->mapel->id_mapel
        );

        $response = $this->actingAs($this->guru)
            ->get(route('dashboard', ['kelas' => $this->kelas->id_kelas, 'mapel' => $this->mapel->id_mapel]));

        $response->assertOk();
        $response->assertSee('css/animasi.css', false);
        $response->assertSee('js/animasi.js', false);

        $progresKelas = $response->viewData('progresKelas');

        $response->assertSee('data-count-up="'.$ringkasan['jumlah_siswa'].'"', false);
        $response->assertSee('data-count-up="'.number_format($ringkasan['rata_rata_nilai'], 1, '.', '').'"', false);
        $response->assertSee('data-count-up="'.$ringkasan['jumlah_perlu_dibantu'].'"', false);
        $response->assertSee('data-ring-target="'.$progresKelas['offset_cincin'].'"', false);

        foreach ($kategoriKelulusan as $kategori) {
            $response->assertSee('data-progress-target="'.$kategori->jumlah_dikerjakan.'"', false);
        }
    }

    public function test_tidak_ada_script_atau_style_inline_di_view_dashboard(): void
    {
        $views = [
            resource_path('views/guru/dashboard.blade.php'),
            resource_path('views/siswa/dashboard.blade.php'),
            resource_path('views/admin/dashboard.blade.php'),
        ];

        foreach ($views as $path) {
            $contents = File::get($path);
            $this->assertDoesNotMatchRegularExpression('/<style\b|\s(?:style|on[a-z]+)\s*=/i', $contents, $path);
            preg_match_all('/<script\b[^>]*>([\s\S]*?)<\/script>/i', $contents, $scripts);
            foreach ($scripts[1] as $script) {
                $this->assertSame('', trim($script), $path);
            }
        }
    }

    public function test_aset_animasi_menghormati_prefers_reduced_motion(): void
    {
        $css = File::get(public_path('css/animasi.css'));
        $js = File::get(public_path('js/animasi.js'));

        $this->assertStringContainsString('prefers-reduced-motion: reduce', $css);
        $this->assertStringContainsString('prefers-reduced-motion: reduce', $js);
    }
}
