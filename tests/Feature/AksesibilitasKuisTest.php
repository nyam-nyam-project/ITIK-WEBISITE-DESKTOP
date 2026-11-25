<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Mapel;
use App\Models\SesiKuis;
use App\Models\Soal;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AksesibilitasKuisTest extends TestCase
{
    use RefreshDatabase;

    private User $siswa;

    private Kuis $kuis;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $guru = User::create([
            'id_user' => 'G_A11Y_1',
            'nomor_induk' => '198001',
            'nama_lengkap' => 'Guru Uji',
            'role' => 'guru',
            'status' => 'aktif',
            'password' => Hash::make('password123'),
            'wajib_ganti_password' => false,
        ]);

        $this->siswa = User::create([
            'id_user' => 'S_A11Y_1',
            'nomor_induk' => '202401',
            'nama_lengkap' => 'Siswa Uji',
            'role' => 'siswa',
            'status' => 'aktif',
            'password' => Hash::make('password123'),
            'wajib_ganti_password' => false,
        ]);

        $ta = TahunAjaran::create([
            'id_tahun_ajaran' => 'TA01',
            'nama' => '2026/2027 Ganjil',
            'is_aktif' => true,
        ]);

        $kelas = Kelas::create([
            'id_kelas' => 'KLS01',
            'id_tahun_ajaran' => $ta->id_tahun_ajaran,
            'nama_kelas' => 'X TKJ 1',
            'tingkat' => '10',
            'is_aktif' => true,
        ]);

        $mapel = Mapel::create([
            'id_mapel' => 'MP01',
            'kode_mapel' => 'TIK',
            'nama_mapel' => 'Dasar TIK',
            'is_aktif' => true,
        ]);

        DB::table('tp_anggota_kelas')->insert([
            'id_kelas' => $kelas->id_kelas,
            'id_user' => $this->siswa->id_user,
        ]);

        DB::table('tp_mapel_kelas')->insert([
            'id_mapel' => $mapel->id_mapel,
            'id_kelas' => $kelas->id_kelas,
        ]);

        $this->kuis = Kuis::create([
            'id_kuis' => 'K_A11Y_1',
            'id_user' => $guru->id_user,
            'id_mapel' => $mapel->id_mapel,
            'id_tahun_ajaran' => $ta->id_tahun_ajaran,
            'judul' => 'Kuis Uji Aksesibilitas',
            'kategori' => 'kuis harian',
            'alokasi_waktu' => 15,
            'kkm' => 75,
            'status' => 'terbit',
        ]);

        DB::table('tp_kuis_kelas')->insert([
            'id_kuis' => $this->kuis->id_kuis,
            'id_kelas' => $kelas->id_kelas,
        ]);

        Soal::create([
            'id_soal' => 'S_A11Y_01',
            'id_kuis' => $this->kuis->id_kuis,
            'id_user' => $guru->id_user,
            'pertanyaan' => 'Apa kepanjangan dari CPU?',
            'tipe' => 'pilihan_ganda',
            'opsi_a' => 'Central Processing Unit',
            'opsi_b' => 'Central Power Unit',
            'opsi_c' => 'Computer Personal Unit',
            'opsi_d' => 'Control Process Unit',
            'kunci_jawaban' => 'A',
        ]);

        SesiKuis::create([
            'id_kuis' => $this->kuis->id_kuis,
            'id_user' => $this->siswa->id_user,
            'percobaan_ke' => 1,
            'waktu_mulai' => now(),
        ]);
    }

    public function test_halaman_kerjakan_tidak_memuat_tombol_mengambang_aksesibilitas(): void
    {
        $response = $this->actingAs($this->siswa)
            ->get(route('siswa.kuis.kerjakan', $this->kuis->id_kuis));

        $response->assertOk();
        $response->assertDontSee('id="a11y-widget"', false);
        $response->assertDontSee('id="a11y-toggle"', false);
        $response->assertDontSee('data-a11y-floating', false);
    }

    public function test_halaman_kerjakan_memuat_skrip_penerapan_pengaturan(): void
    {
        $response = $this->actingAs($this->siswa)
            ->get(route('siswa.kuis.kerjakan', $this->kuis->id_kuis));

        $response->assertOk();
        $response->assertSee('js/aksesibilitas-init.js', false);
        $response->assertSee('js/aksesibilitas.js', false);
    }

    public function test_halaman_kerjakan_tetap_memuat_tombol_bacakan_soal(): void
    {
        $response = $this->actingAs($this->siswa)
            ->get(route('siswa.kuis.kerjakan', $this->kuis->id_kuis));

        $response->assertOk();
        $response->assertSee('class="btn-bacakan-soal"', false);
        $response->assertSee('Bacakan soal', false);
    }

    public function test_tidak_ada_script_atau_style_inline_di_view_kerjakan(): void
    {
        $contents = File::get(resource_path('views/siswa/kuis/kerjakan.blade.php'));

        $this->assertDoesNotMatchRegularExpression('/<style\b|\s(?:style|on[a-z]+)\s*=/i', $contents);
        preg_match_all('/<script\b[^>]*>([\s\S]*?)<\/script>/i', $contents, $scripts);
        foreach ($scripts[1] as $script) {
            $this->assertSame('', trim($script));
        }
    }

    public function test_halaman_login_tidak_memuat_tombol_mengambang_tetapi_memuat_skrip_pengaturan(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('js/aksesibilitas-init.js', false);
        $response->assertDontSee('id="a11y-widget"', false);
        $response->assertDontSee('id="a11y-toggle"', false);
    }

    public function test_tombol_mengambang_tampil_di_portal_biasa_dan_disembunyikan_saat_dibuka_dari_sesi_ujian(): void
    {
        $portalResponse = $this->actingAs($this->siswa)->get(route('profil'));
        $portalResponse->assertOk();
        $portalResponse->assertSee('id="a11y-widget"', false);
        $portalResponse->assertSee('id="a11y-toggle"', false);

        $fromExamResponse = $this->actingAs($this->siswa)
            ->withHeaders(['Referer' => url('/siswa/kuis/'.$this->kuis->id_kuis.'/kerjakan')])
            ->get(route('profil'));
        $fromExamResponse->assertOk();
        $fromExamResponse->assertDontSee('id="a11y-widget"', false);
    }
}
