<?php

namespace Tests\Feature;

use App\Models\DraftJawaban;
use App\Models\Kuis;
use App\Models\Materi;
use App\Models\Mengerjakan;
use App\Models\Progress;
use App\Models\Remedial;
use App\Models\RemedialSiswa;
use App\Models\SesiKuis;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('user', function (Blueprint $table): void {
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
            $table->foreign('dibuat_oleh')->references('id_user')->on('user')->nullOnDelete();
        });
    }

    public function test_mobile_login_issues_a_sanctum_token(): void
    {
        $user = $this->createUser('guru', 'teacher@example.test');
        $user->update(['nomor_induk' => 'GURU001']);

        $response = $this->postJson('/api/login', [
            'nomor_induk' => 'GURU001',
            'password' => 'password',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.id_user', $user->id_user)
            ->assertJsonPath('user.role', 'guru')
            ->assertJsonStructure(['token']);
    }

    public function test_mobile_login_accepts_the_website_nomor_induk_field(): void
    {
        $user = $this->createUser('siswa', 'student@example.test');
        $user->update(['nomor_induk' => '202401']);

        $this->postJson('/api/login', [
            'nomor_induk' => '202401',
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonPath('user.nomor_induk', '202401')
            ->assertJsonPath('user.wajib_ganti_password', false)
            ->assertJsonStructure(['token']);
    }

    public function test_mobile_login_rejects_email_and_legacy_login_aliases(): void
    {
        $user = $this->createUser('siswa', 'student@example.test');
        $user->update(['nomor_induk' => '202401']);

        $this->postJson('/api/login', [
            'login' => '202401',
            'password' => 'password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('nomor_induk');

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('nomor_induk');
    }

    public function test_mobile_login_rejects_admin_accounts(): void
    {
        $user = $this->createUser('admin', 'admin@example.test');
        $user->update(['nomor_induk' => 'ADMIN01']);

        $this->postJson('/api/login', [
            'nomor_induk' => 'ADMIN01',
            'password' => 'password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('nomor_induk')
            ->assertJsonPath('errors.nomor_induk.0', 'Akun Administrator silakan masuk melalui halaman login khusus Admin.');
    }

    public function test_mobile_login_rejects_inactive_accounts(): void
    {
        $user = $this->createUser('siswa', 'inactive@example.test');
        $user->update([
            'nomor_induk' => '202402',
            'status' => 'nonaktif',
        ]);

        $this->postJson('/api/login', [
            'nomor_induk' => '202402',
            'password' => 'password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('nomor_induk')
            ->assertJsonPath('errors.nomor_induk.0', 'Akun Anda sudah tidak aktif. Hubungi admin sekolah.');
    }

    public function test_bearer_token_authenticates_the_current_user(): void
    {
        $user = $this->createUser('siswa', 'student@example.test');
        $token = $user->createToken('test-device')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.id_user', $user->id_user);
    }

    public function test_authenticated_user_can_update_their_profile(): void
    {
        $user = $this->createUser('siswa', 'student@example.test');

        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/me', [
                'nama_lengkap' => 'Nama Baru',
                'email' => 'new@example.test',
                'role' => 'admin',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Profil berhasil diperbarui.')
            ->assertJsonPath('data.nama_lengkap', 'Nama Baru')
            ->assertJsonPath('data.email', 'new@example.test')
            ->assertJsonPath('data.role', 'siswa');

        $this->assertDatabaseHas('user', [
            'id_user' => $user->id_user,
            'nama_lengkap' => 'Nama Baru',
            'email' => 'new@example.test',
            'role' => 'siswa',
        ]);
    }

    public function test_authenticated_user_can_upload_and_delete_profile_photo(): void
    {
        Storage::fake('public');
        $user = $this->createUser('guru', 'teacher@example.test');
        $filename = hash('sha256', $user->id_user).'.png';

        $this->actingAs($user, 'sanctum')
            ->post('/api/me/photo', [
                'foto' => UploadedFile::fake()->image('profil.png'),
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('message', 'Foto profil berhasil diperbarui.')
            ->assertJsonPath('data.foto_url', Storage::disk('public')->url('profile/'.$filename).'?v='.Storage::disk('public')->lastModified('profile/'.$filename));

        Storage::disk('public')->assertExists('profile/'.$filename);

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/me/photo')
            ->assertOk()
            ->assertJsonPath('data.foto_url', null);

        Storage::disk('public')->assertMissing('profile/'.$filename);
    }

    public function test_profile_update_rejects_an_email_used_by_another_user(): void
    {
        $user = $this->createUser('siswa', 'student@example.test');
        $this->createUser('guru', 'teacher@example.test');

        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/me', ['email' => 'teacher@example.test'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email')
            ->assertJsonPath('errors.email.0', 'Email sudah digunakan akun lain.');
    }

    public function test_profile_update_requires_at_least_one_field(): void
    {
        $user = $this->createUser('siswa', 'student@example.test');

        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/me', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('profile');
    }

    public function test_authenticated_user_can_update_their_password(): void
    {
        $user = $this->createUser('siswa', 'student@example.test');
        $user->update(['wajib_ganti_password' => true]);

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/me/password', [
                'password_lama' => 'password',
                'password_baru' => 'new-password',
                'password_baru_confirmation' => 'new-password',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Password berhasil diperbarui.');

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
        $this->assertFalse($user->fresh()->wajib_ganti_password);
    }

    public function test_password_update_rejects_an_incorrect_current_password(): void
    {
        $user = $this->createUser('siswa', 'student@example.test');

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/me/password', [
                'password_lama' => 'wrong-password',
                'password_baru' => 'new-password',
                'password_baru_confirmation' => 'new-password',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password_lama')
            ->assertJsonPath('errors.password_lama.0', 'Password saat ini tidak sesuai.');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_password_update_requires_confirmation(): void
    {
        $user = $this->createUser('siswa', 'student@example.test');

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/me/password', [
                'password_lama' => 'password',
                'password_baru' => 'new-password',
                'password_baru_confirmation' => 'different-password',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password_baru')
            ->assertJsonPath('errors.password_baru.0', 'Konfirmasi password baru tidak cocok.');
    }

    public function test_profile_and_password_endpoints_require_authentication(): void
    {
        $this->patchJson('/api/me', ['nama_lengkap' => 'Nama Baru'])
            ->assertUnauthorized();

        $this->putJson('/api/me/password', [
            'password_lama' => 'password',
            'password_baru' => 'new-password',
            'password_baru_confirmation' => 'new-password',
        ])
            ->assertUnauthorized();
    }

    public function test_login_rejects_an_incorrect_password(): void
    {
        $user = $this->createUser('siswa', 'student@example.test');
        $user->update(['nomor_induk' => '202401']);

        $this->postJson('/api/login', [
            'nomor_induk' => '202401',
            'password' => 'incorrect',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('nomor_induk');
    }

    public function test_mobile_login_requires_an_identifier(): void
    {
        $this->postJson('/api/login', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('nomor_induk')
            ->assertJsonPath('errors.nomor_induk.0', 'NISN atau NIP wajib diisi.');
    }

    public function test_mobile_login_requires_a_password(): void
    {
        $this->postJson('/api/login', ['nomor_induk' => '202401'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password')
            ->assertJsonPath('errors.password.0', 'Password wajib diisi.');
    }

    public function test_student_can_mark_materi_complete(): void
    {
        $student = $this->createUser('siswa', 'student@example.test');
        $otherStudent = $this->createUser('siswa', 'other@example.test');
        $materi = Materi::create([
            'id_materi' => 'M001',
            'judul' => 'Materi API',
            'id_user' => $this->createUser('guru', 'teacher@example.test')->id_user,
        ]);

        Progress::create([
            'id_progress' => 'POTHER0001',
            'status' => 'selesai',
            'tanggal' => now()->toDateString(),
            'id_materi' => $materi->id_materi,
            'id_user' => $otherStudent->id_user,
        ]);

        $response = $this->actingAs($student, 'sanctum')
            ->postJson("/api/siswa/materi/{$materi->id_materi}/selesai");

        $response
            ->assertOk()
            ->assertJsonPath('data.status', 'selesai');

        $this->assertDatabaseHas('progress', [
            'id_materi' => $materi->id_materi,
            'id_user' => $student->id_user,
            'status' => 'selesai',
        ]);

    }

    public function test_student_materi_list_does_not_expose_another_students_progress(): void
    {
        $student = $this->createUser('siswa', 'student@example.test');
        $otherStudent = $this->createUser('siswa', 'other@example.test');
        $materi = Materi::create([
            'id_materi' => 'M001',
            'judul' => 'Materi API',
            'id_user' => $this->createUser('guru', 'teacher@example.test')->id_user,
        ]);

        Progress::create([
            'id_progress' => 'POTHER0001',
            'status' => 'selesai',
            'tanggal' => now()->toDateString(),
            'id_materi' => $materi->id_materi,
            'id_user' => $otherStudent->id_user,
        ]);

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/siswa/materi')
            ->assertOk()
            ->assertJsonPath('data.0.id_materi', $materi->id_materi)
            ->assertJsonPath('data.0.progress', []);
    }

    public function test_student_quiz_response_does_not_include_answer_keys_or_scores(): void
    {
        $student = $this->createUser('siswa', 'student@example.test');
        $kuis = Kuis::create([
            'id_kuis' => 'K001',
            'judul' => 'Kuis API',
            'kategori' => 'kuis harian',
            'alokasi_waktu' => 30,
            'kkm' => 70,
            'id_user' => $this->createUser('guru', 'teacher@example.test')->id_user,
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);
        Soal::create([
            'id_soal' => 'S001',
            'id_kuis' => $kuis->id_kuis,
            'pertanyaan' => 'Pertanyaan?',
            'opsi_a' => 'A',
            'opsi_b' => 'B',
            'opsi_c' => 'C',
            'opsi_d' => 'D',
            'jawaban' => 'A',
            'bobot' => 100,
        ]);
        SesiKuis::create([
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $student->id_user,
            'percobaan_ke' => 1,
            'waktu_mulai' => now(),
        ]);

        $this->actingAs($student, 'sanctum')
            ->getJson("/api/siswa/kuis/{$kuis->id_kuis}")
            ->assertOk()
            ->assertJsonPath('data.soal.0.pertanyaan', 'Pertanyaan?')
            ->assertJsonMissingPath('data.soal.0.jawaban')
            ->assertJsonMissingPath('data.soal.0.bobot');
    }

    public function test_material_and_quiz_lists_are_ordered_newest_first_for_students_and_teachers(): void
    {
        $teacher = $this->createUser('guru', 'teacher@example.test');
        $student = $this->createUser('siswa', 'student@example.test');

        foreach ([
            ['id' => 'M001', 'title' => 'Materi Lama', 'created_at' => '2026-01-01 10:00:00'],
            ['id' => 'M002', 'title' => 'Materi Baru', 'created_at' => '2026-02-01 10:00:00'],
        ] as $item) {
            Materi::create([
                'id_materi' => $item['id'],
                'judul' => $item['title'],
                'id_user' => $teacher->id_user,
            ]);
            DB::table('materis')
                ->where('id_materi', $item['id'])
                ->update(['created_at' => $item['created_at']]);
        }

        foreach ([
            ['id' => 'K001', 'title' => 'Kuis Lama', 'created_at' => '2026-01-01 10:00:00'],
            ['id' => 'K002', 'title' => 'Kuis Baru', 'created_at' => '2026-02-01 10:00:00'],
        ] as $item) {
            Kuis::create([
                'id_kuis' => $item['id'],
                'judul' => $item['title'],
                'kategori' => 'kuis harian',
                'alokasi_waktu' => 30,
                'kkm' => 70,
                'id_user' => $teacher->id_user,
                'status_publikasi' => 'terbit',
                'is_aktif' => true,
            ]);
            DB::table('kuis')
                ->where('id_kuis', $item['id'])
                ->update(['created_at' => $item['created_at']]);
            Soal::create([
                'id_soal' => "S{$item['id']}",
                'id_kuis' => $item['id'],
                'pertanyaan' => 'Pertanyaan?',
                'opsi_a' => 'A',
                'opsi_b' => 'B',
                'opsi_c' => 'C',
                'opsi_d' => 'D',
                'jawaban' => 'A',
                'bobot' => 100,
            ]);
        }

        $this->actingAs($teacher, 'sanctum')
            ->getJson('/api/guru/materi')
            ->assertOk()
            ->assertJsonPath('data.0.id_materi', 'M002');
        $this->actingAs($student, 'sanctum')
            ->getJson('/api/siswa/materi')
            ->assertOk()
            ->assertJsonPath('data.0.id_materi', 'M002');
        $this->actingAs($teacher, 'sanctum')
            ->getJson('/api/guru/kuis')
            ->assertOk()
            ->assertJsonPath('data.0.id_kuis', 'K002');
        $this->actingAs($student, 'sanctum')
            ->getJson('/api/siswa/kuis')
            ->assertOk()
            ->assertJsonPath('data.0.id_kuis', 'K002');
    }

    public function test_student_quiz_list_only_includes_published_active_quizzes_with_questions(): void
    {
        $student = $this->createUser('siswa', 'student@example.test');
        $teacher = $this->createUser('guru', 'teacher@example.test');

        $published = Kuis::create([
            'id_kuis' => 'K001',
            'judul' => 'Kuis Terbit dengan Soal',
            'kategori' => 'kuis harian',
            'alokasi_waktu' => 30,
            'kkm' => 70,
            'id_user' => $teacher->id_user,
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);
        Soal::create([
            'id_soal' => 'S001',
            'id_kuis' => $published->id_kuis,
            'pertanyaan' => 'Pertanyaan?',
            'opsi_a' => 'A',
            'opsi_b' => 'B',
            'opsi_c' => 'C',
            'opsi_d' => 'D',
            'jawaban' => 'A',
            'bobot' => 100,
        ]);

        Kuis::create([
            'id_kuis' => 'K002',
            'judul' => 'Kuis Terbit tanpa Soal',
            'kategori' => 'kuis harian',
            'alokasi_waktu' => 30,
            'kkm' => 70,
            'id_user' => $teacher->id_user,
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);
        Kuis::create([
            'id_kuis' => 'K003',
            'judul' => 'Kuis Draft dengan Soal',
            'kategori' => 'kuis harian',
            'alokasi_waktu' => 30,
            'kkm' => 70,
            'id_user' => $teacher->id_user,
            'status_publikasi' => 'draft',
            'is_aktif' => true,
        ]);

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/siswa/kuis')
            ->assertOk()
            ->assertJsonPath('data.0.id_kuis', 'K001')
            ->assertJsonPath('data.0.soal_count', 1)
            ->assertJsonCount(1, 'data');
    }

    public function test_student_can_submit_a_quiz_and_persist_the_calculated_score(): void
    {
        $student = $this->createUser('siswa', 'student@example.test');
        $kuis = Kuis::create([
            'id_kuis' => 'K001',
            'judul' => 'Kuis API',
            'kategori' => 'kuis harian',
            'alokasi_waktu' => 30,
            'kkm' => 70,
            'id_user' => $this->createUser('guru', 'teacher@example.test')->id_user,
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);
        Soal::create([
            'id_soal' => 'S001',
            'id_kuis' => $kuis->id_kuis,
            'pertanyaan' => 'Pertanyaan?',
            'opsi_a' => 'A',
            'opsi_b' => 'B',
            'opsi_c' => 'C',
            'opsi_d' => 'D',
            'jawaban' => 'A',
            'bobot' => 100,
        ]);
        $sesi = SesiKuis::create([
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $student->id_user,
            'percobaan_ke' => 1,
            'waktu_mulai' => now(),
        ]);
        DraftJawaban::create([
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $student->id_user,
            'id_soal' => 'S001',
            'jawaban_dipilih' => 'A',
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($student, 'sanctum')
            ->postJson("/api/siswa/kuis/{$kuis->id_kuis}/submit");

        $response
            ->assertCreated()
            ->assertJsonPath('data.nilai', '100.00')
            ->assertJsonPath('data.status_validasi', 'sudah divalidasi');

        $this->assertDatabaseHas('mengerjakans', [
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $student->id_user,
            'percobaan_ke' => 1,
        ]);
        $this->assertDatabaseMissing('draft_jawaban', [
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $student->id_user,
        ]);
        $this->assertNotNull($sesi->fresh()->waktu_selesai);
    }

    public function test_student_score_below_kkm_still_requires_teacher_validation(): void
    {
        $student = $this->createUser('siswa', 'student@example.test');
        $teacher = $this->createUser('guru', 'teacher@example.test');
        $kuis = Kuis::create([
            'id_kuis' => 'K001',
            'judul' => 'Kuis di Bawah KKM',
            'kategori' => 'kuis harian',
            'alokasi_waktu' => 30,
            'kkm' => 70,
            'id_user' => $teacher->id_user,
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);
        Soal::create([
            'id_soal' => 'S001',
            'id_kuis' => $kuis->id_kuis,
            'pertanyaan' => 'Pertanyaan?',
            'opsi_a' => 'A',
            'opsi_b' => 'B',
            'opsi_c' => 'C',
            'opsi_d' => 'D',
            'jawaban' => 'A',
            'bobot' => 100,
        ]);
        SesiKuis::create([
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $student->id_user,
            'percobaan_ke' => 1,
            'waktu_mulai' => now(),
        ]);
        DraftJawaban::create([
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $student->id_user,
            'id_soal' => 'S001',
            'jawaban_dipilih' => 'B',
            'updated_at' => now(),
        ]);

        $this->actingAs($student, 'sanctum')
            ->postJson("/api/siswa/kuis/{$kuis->id_kuis}/submit")
            ->assertCreated()
            ->assertJsonPath('data.nilai', '0.00')
            ->assertJsonPath('data.status_validasi', 'belum divalidasi');
    }

    public function test_student_can_load_only_their_own_quiz_scores(): void
    {
        $student = $this->createUser('siswa', 'student@example.test');
        $otherStudent = $this->createUser('siswa', 'other-student@example.test');
        $teacher = $this->createUser('guru', 'teacher@example.test');
        $kuis = Kuis::create([
            'id_kuis' => 'K001',
            'judul' => 'Kuis Riwayat',
            'kategori' => 'kuis harian',
            'alokasi_waktu' => 30,
            'kkm' => 70,
            'id_user' => $teacher->id_user,
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);
        Mengerjakan::create([
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $student->id_user,
            'nilai' => 85,
            'tanggal' => now()->toDateString(),
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
            'status_validasi' => 'belum divalidasi',
        ]);
        Mengerjakan::create([
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $otherStudent->id_user,
            'nilai' => 40,
            'tanggal' => now()->toDateString(),
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
        ]);

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/siswa/nilai')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nilai', '85.00')
            ->assertJsonPath('data.0.kuis.judul', 'Kuis Riwayat')
            ->assertJsonPath('data.0.status_validasi', 'sudah divalidasi');
    }

    public function test_student_score_includes_assigned_and_completed_remedial_status(): void
    {
        $student = $this->createUser('siswa', 'student@example.test');
        $teacher = $this->createUser('guru', 'teacher@example.test');
        $kuis = Kuis::create([
            'id_kuis' => 'K001',
            'judul' => 'Ulangan',
            'kategori' => 'ulangan',
            'alokasi_waktu' => 30,
            'kkm' => 70,
            'id_user' => $teacher->id_user,
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);
        $kuisRemedial = Kuis::create([
            'id_kuis' => 'K002',
            'judul' => 'Remedial Ulangan',
            'kategori' => 'remedial',
            'alokasi_waktu' => 20,
            'kkm' => 70,
            'id_user' => $teacher->id_user,
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);
        $hasil = Mengerjakan::create([
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $student->id_user,
            'nilai' => 55,
            'tanggal' => now()->toDateString(),
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
            'status_validasi' => 'sudah divalidasi',
        ]);
        $tugas = RemedialSiswa::create([
            'id_kuis_remedial' => $kuisRemedial->id_kuis,
            'id_mengerjakan_asal' => $hasil->id_mengerjakan,
            'id_guru' => $teacher->id_user,
            'tanggal_ditugaskan' => now()->toDateString(),
            'status' => 'ditugaskan',
        ]);

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/siswa/nilai')
            ->assertOk()
            ->assertJsonPath('data.0.remedial_siswa.0.id_kuis_remedial', 'K002')
            ->assertJsonPath('data.0.remedial_siswa.0.status', 'ditugaskan')
            ->assertJsonPath('data.0.remedial_siswa.0.kuis_remedial.judul', 'Remedial Ulangan');

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/siswa/remedial')
            ->assertOk()
            ->assertJsonPath('data.0.id_kuis_remedial', 'K002')
            ->assertJsonPath('data.0.mengerjakan_asal.id_mengerjakan', $hasil->id_mengerjakan)
            ->assertJsonPath('data.0.kuis_remedial.status_publikasi', 'terbit');

        $tugas->update(['status' => 'selesai']);

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/siswa/nilai')
            ->assertOk()
            ->assertJsonPath('data.0.remedial_siswa.0.status', 'selesai');

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/siswa/remedial')
            ->assertOk()
            ->assertJsonPath('data.0.status', 'selesai');
    }

    public function test_teacher_assignment_is_saved_and_visible_to_student(): void
    {
        $teacher = $this->createUser('guru', 'teacher@example.test');
        $student = $this->createUser('siswa', 'student@example.test');
        $kuisAsal = Kuis::create([
            'id_kuis' => 'K001',
            'judul' => 'Ulangan CSS',
            'kategori' => 'ulangan',
            'alokasi_waktu' => 30,
            'kkm' => 80,
            'id_user' => $teacher->id_user,
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);
        $kuisRemedial = Kuis::create([
            'id_kuis' => 'K002',
            'judul' => 'Remedial CSS',
            'kategori' => 'remedial',
            'alokasi_waktu' => 20,
            'kkm' => 80,
            'id_user' => $teacher->id_user,
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);
        $remedial = Remedial::create([
            'id_kuis_remedial' => $kuisRemedial->id_kuis,
            'id_kuis_asal' => $kuisAsal->id_kuis,
        ]);
        $hasil = Mengerjakan::create([
            'id_kuis' => $kuisAsal->id_kuis,
            'id_user' => $student->id_user,
            'nilai' => 0,
            'tanggal' => now()->toDateString(),
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
            'status_validasi' => 'belum divalidasi',
        ]);

        $this->actingAs($teacher, 'sanctum')
            ->getJson("/api/guru/remedial/{$remedial->id_remedial}/siswa")
            ->assertOk()
            ->assertJsonPath('data.0.id_mengerjakan', $hasil->id_mengerjakan);

        $this->actingAs($teacher, 'sanctum')
            ->postJson("/api/guru/remedial/{$remedial->id_remedial}/tugaskan", [
                'id_mengerjakan' => [$hasil->id_mengerjakan],
            ])
            ->assertOk()
            ->assertJsonPath('jumlah_ditugaskan', 1);

        $this->assertDatabaseHas('mengerjakans', [
            'id_mengerjakan' => $hasil->id_mengerjakan,
            'status_validasi' => 'belum divalidasi',
        ]);

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/siswa/remedial')
            ->assertOk()
            ->assertJsonPath('data.0.id_kuis_remedial', $kuisRemedial->id_kuis)
            ->assertJsonPath('data.0.mengerjakan_asal.id_mengerjakan', $hasil->id_mengerjakan);

        $this->actingAs($teacher, 'sanctum')
            ->getJson('/api/guru/nilai')
            ->assertOk()
            ->assertJsonPath('data.0.remedial_siswa.0.status', 'ditugaskan');
    }

    public function test_teacher_can_load_scores_and_validate_a_below_kkm_result(): void
    {
        $teacher = $this->createUser('guru', 'teacher@example.test');
        $student = $this->createUser('siswa', 'student@example.test');
        $kuis = Kuis::create([
            'id_kuis' => 'K001',
            'judul' => 'Kuis Validasi Guru',
            'kategori' => 'ulangan',
            'alokasi_waktu' => 30,
            'kkm' => 70,
            'id_user' => $teacher->id_user,
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);
        $hasil = Mengerjakan::create([
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $student->id_user,
            'nilai' => 55,
            'tanggal' => now()->toDateString(),
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
            'status_validasi' => 'belum divalidasi',
        ]);

        $this->actingAs($teacher, 'sanctum')
            ->getJson('/api/guru/nilai')
            ->assertOk()
            ->assertJsonPath('data.0.id_mengerjakan', $hasil->id_mengerjakan)
            ->assertJsonPath('data.0.siswa.nama_lengkap', $student->nama_lengkap);

        $this->actingAs($teacher, 'sanctum')
            ->patchJson("/api/guru/nilai/{$hasil->id_mengerjakan}/validasi")
            ->assertOk()
            ->assertJsonPath('data.status_validasi', 'sudah divalidasi');

        $this->assertDatabaseHas('mengerjakans', [
            'id_mengerjakan' => $hasil->id_mengerjakan,
            'id_validator' => $teacher->id_user,
            'status_validasi' => 'sudah divalidasi',
        ]);
    }

    public function test_teacher_score_includes_duration_for_the_matching_quiz_attempt(): void
    {
        $teacher = $this->createUser('guru', 'teacher@example.test');
        $student = $this->createUser('siswa', 'student@example.test');
        $kuis = Kuis::create([
            'id_kuis' => 'K001',
            'judul' => 'Kuis Durasi',
            'kategori' => 'ulangan',
            'alokasi_waktu' => 30,
            'kkm' => 70,
            'id_user' => $teacher->id_user,
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);
        $hasil = Mengerjakan::create([
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $student->id_user,
            'nilai' => 55,
            'tanggal' => now()->toDateString(),
            'percobaan_ke' => 2,
            'status_pengerjaan' => 'final',
            'status_validasi' => 'belum divalidasi',
        ]);
        $mulai = now()->subMinutes(5);
        SesiKuis::create([
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $student->id_user,
            'percobaan_ke' => 1,
            'waktu_mulai' => now()->subMinutes(20),
            'waktu_selesai' => now()->subMinutes(10),
        ]);
        SesiKuis::create([
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $student->id_user,
            'percobaan_ke' => 2,
            'waktu_mulai' => $mulai,
            'waktu_selesai' => $mulai->copy()->addSeconds(165),
        ]);

        $this->actingAs($teacher, 'sanctum')
            ->getJson('/api/guru/nilai')
            ->assertOk()
            ->assertJsonPath('data.0.id_mengerjakan', $hasil->id_mengerjakan)
            ->assertJsonPath('data.0.durasi_detik', 165);
    }

    public function test_teacher_score_list_marks_old_passing_results_as_auto_validated(): void
    {
        $teacher = $this->createUser('guru', 'teacher@example.test');
        $student = $this->createUser('siswa', 'student@example.test');
        $kuis = Kuis::create([
            'id_kuis' => 'K001',
            'judul' => 'Kuis Lulus',
            'kategori' => 'kuis harian',
            'alokasi_waktu' => 30,
            'kkm' => 70,
            'id_user' => $teacher->id_user,
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);
        $hasil = Mengerjakan::create([
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $student->id_user,
            'nilai' => 85,
            'tanggal' => now()->toDateString(),
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
            'status_validasi' => 'belum divalidasi',
        ]);

        $this->actingAs($teacher, 'sanctum')
            ->getJson('/api/guru/nilai')
            ->assertOk()
            ->assertJsonPath('data.0.id_mengerjakan', $hasil->id_mengerjakan)
            ->assertJsonPath('data.0.status_validasi', 'sudah divalidasi');

        $this->assertDatabaseHas('mengerjakans', [
            'id_mengerjakan' => $hasil->id_mengerjakan,
            'status_validasi' => 'sudah divalidasi',
            'id_validator' => null,
        ]);
    }

    public function test_student_cannot_save_an_answer_for_a_question_from_another_quiz(): void
    {
        $student = $this->createUser('siswa', 'student@example.test');
        $teacherId = $this->createUser('guru', 'teacher@example.test')->id_user;
        $kuis = Kuis::create([
            'id_kuis' => 'K001',
            'judul' => 'Kuis API',
            'kategori' => 'kuis harian',
            'alokasi_waktu' => 30,
            'kkm' => 70,
            'id_user' => $teacherId,
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);
        $otherKuis = Kuis::create([
            'id_kuis' => 'K002',
            'judul' => 'Kuis Lain',
            'kategori' => 'kuis harian',
            'alokasi_waktu' => 30,
            'kkm' => 70,
            'id_user' => $teacherId,
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);
        Soal::create([
            'id_soal' => 'S002',
            'id_kuis' => $otherKuis->id_kuis,
            'pertanyaan' => 'Pertanyaan lain?',
            'opsi_a' => 'A',
            'opsi_b' => 'B',
            'opsi_c' => 'C',
            'opsi_d' => 'D',
            'jawaban' => 'A',
            'bobot' => 100,
        ]);
        SesiKuis::create([
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $student->id_user,
            'percobaan_ke' => 1,
            'waktu_mulai' => now(),
        ]);

        $this->actingAs($student, 'sanctum')
            ->putJson("/api/siswa/kuis/{$kuis->id_kuis}/jawaban", [
                'id_soal' => 'S002',
                'jawaban' => 'A',
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('draft_jawaban', [
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $student->id_user,
        ]);
    }

    public function test_api_routes_enforce_student_and_teacher_roles(): void
    {
        $student = $this->createUser('siswa', 'student@example.test');
        $teacher = $this->createUser('guru', 'teacher@example.test');

        $this->actingAs($teacher, 'sanctum')
            ->getJson('/api/siswa/materi')
            ->assertForbidden();

        $this->actingAs($student, 'sanctum')
            ->postJson('/api/guru/materi', [
                'judul' => 'Materi terlarang',
            ])
            ->assertForbidden();
    }

    public function test_operator_can_manage_content_but_cannot_publish_or_manage_grades_and_remedial(): void
    {
        $operator = $this->createUser('operator', 'operator@example.test');
        $teacher = $this->createUser('guru', 'teacher@example.test');
        $kuis = Kuis::create([
            'id_kuis' => 'K001',
            'judul' => 'Kuis Draft',
            'kategori' => 'kuis harian',
            'alokasi_waktu' => 30,
            'kkm' => 70,
            'id_user' => $teacher->id_user,
            'status_publikasi' => 'draft',
            'is_aktif' => true,
        ]);

        $this->actingAs($operator, 'sanctum')
            ->postJson('/api/guru/materi', [
                'judul' => 'Materi Operator',
            ])
            ->assertCreated();

        $this->actingAs($operator, 'sanctum')
            ->postJson("/api/guru/kuis/{$kuis->id_kuis}/publish")
            ->assertForbidden();

        $this->actingAs($operator, 'sanctum')
            ->getJson('/api/guru/nilai')
            ->assertForbidden();

        $this->actingAs($operator, 'sanctum')
            ->getJson('/api/guru/remedial')
            ->assertForbidden();
    }

    public function test_teacher_can_create_materi_through_the_api(): void
    {
        $teacher = $this->createUser('guru', 'teacher@example.test');

        $this->actingAs($teacher, 'sanctum')
            ->postJson('/api/guru/materi', [
                'judul' => 'Materi Baru',
                'kelas' => 'X',
                'bab' => '1',
            ])
            ->assertCreated()
            ->assertJsonPath('data.judul', 'Materi Baru');

        $this->assertDatabaseHas('materis', [
            'judul' => 'Materi Baru',
            'id_user' => $teacher->id_user,
        ]);
    }

    public function test_teacher_can_create_a_quiz_as_a_draft(): void
    {
        $teacher = $this->createUser('guru', 'teacher@example.test');

        $this->actingAs($teacher, 'sanctum')
            ->postJson('/api/guru/kuis', [
                'judul' => 'Kuis Baru',
                'kategori' => 'kuis harian',
                'alokasi_waktu' => 30,
                'kkm' => 70,
            ])
            ->assertCreated()
            ->assertJsonPath('data.id_kuis', 'K001')
            ->assertJsonPath('data.status_publikasi', 'draft');

        $this->assertDatabaseHas('kuis', [
            'id_kuis' => 'K001',
            'id_user' => $teacher->id_user,
            'status_publikasi' => 'draft',
        ]);
    }

    public function test_teacher_can_publish_a_quiz_with_valid_question_weights(): void
    {
        $teacher = $this->createUser('guru', 'teacher@example.test');
        $kuis = Kuis::create([
            'id_kuis' => 'K001',
            'judul' => 'Kuis Siap Terbit',
            'kategori' => 'kuis harian',
            'alokasi_waktu' => 30,
            'kkm' => 70,
            'id_user' => $teacher->id_user,
            'status_publikasi' => 'draft',
            'is_aktif' => true,
        ]);
        Soal::create([
            'id_soal' => 'S001',
            'id_kuis' => $kuis->id_kuis,
            'pertanyaan' => 'Pertanyaan?',
            'opsi_a' => 'A',
            'opsi_b' => 'B',
            'opsi_c' => 'C',
            'opsi_d' => 'D',
            'jawaban' => 'A',
            'bobot' => 100,
        ]);

        $this->actingAs($teacher, 'sanctum')
            ->postJson("/api/guru/kuis/{$kuis->id_kuis}/publish")
            ->assertOk()
            ->assertJsonPath('data.status_publikasi', 'terbit');

        $this->assertDatabaseHas('kuis', [
            'id_kuis' => $kuis->id_kuis,
            'id_publisher' => $teacher->id_user,
            'status_publikasi' => 'terbit',
        ]);
    }

    public function test_teacher_can_view_students_eligible_for_remedial(): void
    {
        $teacher = $this->createUser('guru', 'teacher@example.test');
        $student = $this->createUser('siswa', 'student@example.test');
        $kuisAsal = Kuis::create([
            'id_kuis' => 'K001',
            'judul' => 'Kuis Asal',
            'kategori' => 'pass-test',
            'kkm' => 70,
            'id_user' => $teacher->id_user,
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);
        $kuisRemedial = Kuis::create([
            'id_kuis' => 'K002',
            'judul' => 'Kuis Remedial',
            'kategori' => 'remedial',
            'id_user' => $teacher->id_user,
            'status_publikasi' => 'draft',
            'is_aktif' => true,
        ]);
        $remedial = Remedial::create([
            'id_kuis_remedial' => $kuisRemedial->id_kuis,
            'id_kuis_asal' => $kuisAsal->id_kuis,
        ]);
        $hasil = Mengerjakan::create([
            'id_kuis' => $kuisAsal->id_kuis,
            'id_user' => $student->id_user,
            'nilai' => 50,
            'tanggal' => now()->toDateString(),
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
            'status_validasi' => 'sudah divalidasi',
        ]);

        $this->actingAs($teacher, 'sanctum')
            ->getJson("/api/guru/remedial/{$remedial->id_remedial}/siswa")
            ->assertOk()
            ->assertJsonPath('data.data.0.id_mengerjakan', $hasil->id_mengerjakan)
            ->assertJsonPath('data.data.0.siswa.id_user', $student->id_user);
    }

    public function test_protected_api_requires_a_token(): void
    {
        $this->getJson('/api/siswa/materi')
            ->assertUnauthorized();
    }

    private function createUser(string $role, string $email): User
    {
        return User::create([
            'id_user' => strtoupper(substr($role, 0, 1)).str_replace(['@', '.'], '', $email),
            'nama_lengkap' => ucfirst($role),
            'role' => $role,
            'email' => $email,
            'password' => 'password',
        ]);
    }
}
