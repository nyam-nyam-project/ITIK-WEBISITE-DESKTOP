<?php

namespace Tests\Feature;

use App\Models\Kuis;
use App\Models\Materi;
use App\Models\Progress;
use App\Models\DraftJawaban;
use App\Models\Mengerjakan;
use App\Models\Remedial;
use App\Models\SesiKuis;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_login_issues_a_sanctum_token(): void
    {
        $user = $this->createUser('siswa', 'student@example.test');

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.id_user', $user->id_user)
            ->assertJsonPath('user.role', 'siswa')
            ->assertJsonStructure(['token']);
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

    public function test_login_rejects_an_incorrect_password(): void
    {
        $user = $this->createUser('siswa', 'student@example.test');

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'incorrect',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
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
            ->assertJsonPath('data.status_validasi', 'belum divalidasi');

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
