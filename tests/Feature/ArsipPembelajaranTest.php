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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArsipPembelajaranTest extends TestCase
{
    use RefreshDatabase;

    private User $guru;

    private User $guruLain;

    private User $siswa;

    private Mapel $mapel;

    private TahunAjaran $tahunAjaran;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tahunAjaran = TahunAjaran::create([
            'id_tahun_ajaran' => 'TA01',
            'nama' => '2026/2027 Ganjil',
            'is_aktif' => true,
        ]);

        $this->guru = User::create([
            'id_user' => 'G001',
            'nama_lengkap' => 'Guru Satu',
            'role' => 'guru',
            'status' => 'aktif',
            'password' => bcrypt('password'),
        ]);

        $this->guruLain = User::create([
            'id_user' => 'G002',
            'nama_lengkap' => 'Guru Dua',
            'role' => 'guru',
            'status' => 'aktif',
            'password' => bcrypt('password'),
        ]);

        $this->siswa = User::create([
            'id_user' => 'S001',
            'nama_lengkap' => 'Siswa Satu',
            'role' => 'siswa',
            'status' => 'aktif',
            'password' => bcrypt('password'),
        ]);

        $this->mapel = Mapel::create([
            'id_mapel' => 'MP01',
            'nama_mapel' => 'Teknologi Jaringan',
            'kode_mapel' => 'TJ',
            'is_aktif' => true,
        ]);

        Kelas::create([
            'id_kelas' => 'K01',
            'nama_kelas' => 'X TKJ 1',
            'tingkat' => 'X',
            'jurusan' => 'TKJ',
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'is_aktif' => true,
        ]);

        DB::table('tp_mapel_kelas')->insert([
            'id_mapel' => $this->mapel->id_mapel,
            'id_kelas' => 'K01',
        ]);

        Pengajaran::create([
            'id_user' => $this->guru->id_user,
            'id_mapel' => $this->mapel->id_mapel,
            'id_kelas' => 'K01',
        ]);
    }

    public function test_guru_dapat_melihat_halaman_arsip(): void
    {
        $response = $this->actingAs($this->guru)->get(route('guru.arsip.index'));

        $response->assertStatus(200);
        $response->assertSee('Kotak Arsip Pembelajaran');
        $response->assertSee('Kuis Diarsipkan');
        $response->assertSee('Materi Diarsipkan');
    }

    public function test_guru_dapat_mengarsipkan_dan_memulihkan_kuis(): void
    {
        $kuis = Kuis::create([
            'id_kuis' => 'K01',
            'judul' => 'Kuis Jaringan Dasar',
            'kategori' => 'kuis harian',
            'id_mapel' => $this->mapel->id_mapel,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'id_user' => $this->guru->id_user,
            'status_publikasi' => 'draft',
            'is_aktif' => true,
        ]);

        // Arsipkan
        $resArsip = $this->actingAs($this->guru)->delete(route('kuis.destroy', $kuis->id_kuis));
        $resArsip->assertRedirect();
        $this->assertFalse((bool) $kuis->fresh()->is_aktif);

        // Pulihkan
        $resPulihkan = $this->actingAs($this->guru)->patch(route('guru.arsip.kuis.pulihkan', $kuis->id_kuis));
        $resPulihkan->assertRedirect();
        $this->assertTrue((bool) $kuis->fresh()->is_aktif);
    }

    public function test_kuis_dengan_riwayat_nilai_tanpa_konfirmasi_diarahkan_ke_halaman_konfirmasi(): void
    {
        $kuis = Kuis::create([
            'id_kuis' => 'K02',
            'judul' => 'Kuis Penting',
            'kategori' => 'kuis harian',
            'id_mapel' => $this->mapel->id_mapel,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'id_user' => $this->guru->id_user,
            'status_publikasi' => 'terbit',
            'is_aktif' => false,
        ]);

        Mengerjakan::create([
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $this->siswa->id_user,
            'nilai' => 85,
            'tanggal' => now(),
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
        ]);

        $response = $this->actingAs($this->guru)->delete(route('guru.arsip.kuis.hapus-permanen', $kuis->id_kuis));
        $response->assertRedirect(route('guru.arsip.kuis.konfirmasi-hapus', $kuis->id_kuis));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('tm_kuis', ['id_kuis' => $kuis->id_kuis]);
        $this->assertDatabaseHas('tr_mengerjakan', ['id_kuis' => $kuis->id_kuis, 'id_user' => $this->siswa->id_user]);
    }

    public function test_halaman_konfirmasi_dan_hapus_permanen_kuis_berriwayat_dengan_cascade_delete(): void
    {
        $kuis = Kuis::create([
            'id_kuis' => 'K02_CASCADE',
            'judul' => 'Kuis Cascade Dihapus',
            'kategori' => 'kuis harian',
            'id_mapel' => $this->mapel->id_mapel,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'id_user' => $this->guru->id_user,
            'status_publikasi' => 'terbit',
            'is_aktif' => false,
        ]);

        $kuisLain = Kuis::create([
            'id_kuis' => 'K02_LAIN',
            'judul' => 'Kuis Lain Aman',
            'kategori' => 'kuis harian',
            'id_mapel' => $this->mapel->id_mapel,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'id_user' => $this->guru->id_user,
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);

        DB::table('tm_soal')->insert([
            ['id_soal' => 'S_DEL_1', 'id_kuis' => $kuis->id_kuis, 'pertanyaan' => 'Soal 1', 'tipe' => 'pilihan_ganda', 'bobot' => 100],
            ['id_soal' => 'S_KEEP_1', 'id_kuis' => $kuisLain->id_kuis, 'pertanyaan' => 'Soal Aman', 'tipe' => 'pilihan_ganda', 'bobot' => 100],
        ]);

        DB::table('tp_kuis_kelas')->insert([
            ['id_kuis' => $kuis->id_kuis, 'id_kelas' => 'K01'],
            ['id_kuis' => $kuisLain->id_kuis, 'id_kelas' => 'K01'],
        ]);

        $mengerjakan = Mengerjakan::create([
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $this->siswa->id_user,
            'nilai' => 85,
            'tanggal' => now(),
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
        ]);

        $mengerjakanLain = Mengerjakan::create([
            'id_kuis' => $kuisLain->id_kuis,
            'id_user' => $this->siswa->id_user,
            'nilai' => 90,
            'tanggal' => now(),
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
        ]);

        DB::table('tr_sesi_kuis')->insert([
            ['id_kuis' => $kuis->id_kuis, 'id_user' => $this->siswa->id_user, 'percobaan_ke' => 1, 'waktu_mulai' => now()],
            ['id_kuis' => $kuisLain->id_kuis, 'id_user' => $this->siswa->id_user, 'percobaan_ke' => 1, 'waktu_mulai' => now()],
        ]);

        DB::table('tr_draft_jawaban')->insert([
            ['id_kuis' => $kuis->id_kuis, 'id_user' => $this->siswa->id_user, 'id_soal' => 'S_DEL_1', 'jawaban_dipilih' => 'A'],
            ['id_kuis' => $kuisLain->id_kuis, 'id_user' => $this->siswa->id_user, 'id_soal' => 'S_KEEP_1', 'jawaban_dipilih' => 'B'],
        ]);

        DB::table('tr_nilai_essay')->insert([
            ['id_mengerjakan' => $mengerjakan->id_mengerjakan, 'id_soal' => 'S_DEL_1', 'jawaban_teks' => 'Jawab 1'],
            ['id_mengerjakan' => $mengerjakanLain->id_mengerjakan, 'id_soal' => 'S_KEEP_1', 'jawaban_teks' => 'Jawab 2'],
        ]);

        // Halaman konfirmasi menampilkan detail kuis & siswa terdampak
        $resKonfirmasi = $this->actingAs($this->guru)->get(route('guru.arsip.kuis.konfirmasi-hapus', $kuis->id_kuis));
        $resKonfirmasi->assertOk();
        $resKonfirmasi->assertSee('Kuis Cascade Dihapus');
        $resKonfirmasi->assertSee('Teknologi Jaringan');
        $resKonfirmasi->assertSee('1 siswa');
        $resKonfirmasi->assertSee('konfirmasi_hapus_nilai');

        // Eksekusi hapus permanen dengan konfirmasi accepted
        $resHapus = $this->actingAs($this->guru)->delete(route('guru.arsip.kuis.hapus-permanen', $kuis->id_kuis), [
            'konfirmasi_hapus_nilai' => '1',
        ]);
        $resHapus->assertRedirect(route('guru.arsip.index', ['tab' => 'kuis']));
        $resHapus->assertSessionHas('success');

        // Semua data kuis yang dihapus hilang bersih
        $this->assertDatabaseMissing('tm_kuis', ['id_kuis' => $kuis->id_kuis]);
        $this->assertDatabaseMissing('tm_soal', ['id_kuis' => $kuis->id_kuis]);
        $this->assertDatabaseMissing('tp_kuis_kelas', ['id_kuis' => $kuis->id_kuis]);
        $this->assertDatabaseMissing('tr_mengerjakan', ['id_kuis' => $kuis->id_kuis]);
        $this->assertDatabaseMissing('tr_sesi_kuis', ['id_kuis' => $kuis->id_kuis]);
        $this->assertDatabaseMissing('tr_draft_jawaban', ['id_kuis' => $kuis->id_kuis]);
        $this->assertDatabaseMissing('tr_nilai_essay', ['id_mengerjakan' => $mengerjakan->id_mengerjakan]);

        // Kuis & nilai lain tetap utuh
        $this->assertDatabaseHas('tm_kuis', ['id_kuis' => $kuisLain->id_kuis]);
        $this->assertDatabaseHas('tm_soal', ['id_soal' => 'S_KEEP_1']);
        $this->assertDatabaseHas('tr_mengerjakan', ['id_mengerjakan' => $mengerjakanLain->id_mengerjakan]);
        $this->assertDatabaseHas('tr_sesi_kuis', ['id_kuis' => $kuisLain->id_kuis]);
        $this->assertDatabaseHas('tr_draft_jawaban', ['id_kuis' => $kuisLain->id_kuis]);
        $this->assertDatabaseHas('tr_nilai_essay', ['id_mengerjakan' => $mengerjakanLain->id_mengerjakan]);

        // Pastikan view konfirmasi bebas CSS/JS inline
        $isiView = file_get_contents(resource_path('views/guru/arsip/konfirmasi-hapus-kuis.blade.php'));
        $this->assertIsString($isiView);
        $this->assertStringNotContainsString('<script', $isiView);
        $this->assertStringNotContainsString('<style', $isiView);
        $this->assertStringNotContainsString('style=', $isiView);
    }

    public function test_kuis_tanpa_riwayat_siswa_dapat_dihapus_permanen(): void
    {
        $kuis = Kuis::create([
            'id_kuis' => 'K03',
            'judul' => 'Kuis Draft Usang',
            'kategori' => 'kuis harian',
            'id_mapel' => $this->mapel->id_mapel,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'id_user' => $this->guru->id_user,
            'status_publikasi' => 'draft',
            'is_aktif' => false,
        ]);

        $response = $this->actingAs($this->guru)->delete(route('guru.arsip.kuis.hapus-permanen', $kuis->id_kuis));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('tm_kuis', ['id_kuis' => $kuis->id_kuis]);
    }

    public function test_guru_dapat_mengarsipkan_memulihkan_dan_menghapus_permanen_materi(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('modul.pdf', 500, 'application/pdf');
        $filePath = $file->store('materi', 'public');

        $materi = Materi::create([
            'id_materi' => 'M01',
            'judul' => 'Modul Routing',
            'bab' => 'Bab 1',
            'kelas' => 'X TKJ 1',
            'id_mapel' => $this->mapel->id_mapel,
            'isi_materi' => $filePath,
            'id_user' => $this->guru->id_user,
            'is_aktif' => true,
        ]);

        // 1. Arsipkan
        $resArsip = $this->actingAs($this->guru)->delete(route('materi.destroy', $materi->id_materi));
        $resArsip->assertRedirect();
        $this->assertFalse((bool) $materi->fresh()->is_aktif);
        Storage::disk('public')->assertExists($filePath); // File fisik masih utuh saat diarsip

        // 2. Pulihkan
        $resPulihkan = $this->actingAs($this->guru)->patch(route('guru.arsip.materi.pulihkan', $materi->id_materi));
        $resPulihkan->assertRedirect();
        $this->assertTrue((bool) $materi->fresh()->is_aktif);

        // 3. Arsipkan lagi lalu Hapus Permanen
        $this->actingAs($this->guru)->delete(route('materi.destroy', $materi->id_materi));
        $resHapus = $this->actingAs($this->guru)->delete(route('guru.arsip.materi.hapus-permanen', $materi->id_materi));
        $resHapus->assertSessionHas('success');

        $this->assertDatabaseMissing('tm_materi', ['id_materi' => $materi->id_materi]);
        Storage::disk('public')->assertMissing($filePath); // File fisik dibersihkan
    }
}
