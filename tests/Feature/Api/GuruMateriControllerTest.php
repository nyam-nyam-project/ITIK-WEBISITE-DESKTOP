<?php

namespace Tests\Feature\Api;

use App\Models\Materi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuruMateriControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_create_material_owned_by_the_authenticated_teacher(): void
    {
        $teacher = $this->createUser('G001', 'guru', 'guru@example.com');

        $this->actingAs($teacher, 'sanctum')
            ->postJson('/api/guru/materi', [
                'judul' => 'Pengenalan API',
                'bab' => '1',
                'kelas' => 'X',
                'deskripsi' => 'Materi untuk kelas X.',
                'isi_materi' => 'materi/api.pdf',
                'link_youtube' => 'https://www.youtube.com/watch?v=example',
                'id_materi' => 'M-FORGED',
                'id_user' => 'G999',
            ])
            ->assertCreated()
            ->assertJsonPath('data.judul', 'Pengenalan API')
            ->assertJsonPath('data.id_user', 'G001')
            ->assertJsonMissingPath('data.password');

        $this->assertDatabaseHas('materis', [
            'judul' => 'Pengenalan API',
            'id_user' => 'G001',
        ]);

        $this->assertDatabaseMissing('materis', [
            'id_materi' => 'M-FORGED',
        ]);
    }

    public function test_teacher_can_list_and_view_materials(): void
    {
        $teacher = $this->createUser('G001', 'guru', 'guru@example.com');
        $materi = $this->createMateri('M001', 'HTML Dasar');

        $this->actingAs($teacher, 'sanctum')
            ->getJson('/api/guru/materi')
            ->assertOk()
            ->assertJsonPath('data.0.id_materi', 'M001')
            ->assertJsonPath('meta.total', 1);

        $this->actingAs($teacher, 'sanctum')
            ->getJson('/api/guru/materi/'.$materi->id_materi)
            ->assertOk()
            ->assertJsonPath('data.judul', 'HTML Dasar');
    }

    public function test_teacher_can_update_material_without_changing_its_owner(): void
    {
        $teacher = $this->createUser('G001', 'guru', 'guru@example.com');
        $materi = $this->createMateri('M001', 'HTML Dasar');

        $this->actingAs($teacher, 'sanctum')
            ->putJson('/api/guru/materi/'.$materi->id_materi, [
                'judul' => 'HTML Lanjutan',
                'id_user' => 'G999',
            ])
            ->assertOk()
            ->assertJsonPath('data.judul', 'HTML Lanjutan')
            ->assertJsonPath('data.id_user', 'G001');

        $this->assertDatabaseHas('materis', [
            'id_materi' => 'M001',
            'judul' => 'HTML Lanjutan',
            'id_user' => 'G001',
        ]);
    }

    public function test_teacher_cannot_create_material_with_a_duplicate_title(): void
    {
        $teacher = $this->createUser('G001', 'guru', 'guru@example.com');
        $this->createMateri('M001', 'HTML Dasar');

        $this->actingAs($teacher, 'sanctum')
            ->postJson('/api/guru/materi', [
                'judul' => 'HTML Dasar',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['judul']);

        $this->assertDatabaseCount('materis', 1);
    }

    public function test_teacher_can_delete_material(): void
    {
        $teacher = $this->createUser('G001', 'guru', 'guru@example.com');
        $materi = $this->createMateri('M001', 'HTML Dasar');

        $this->actingAs($teacher, 'sanctum')
            ->deleteJson('/api/guru/materi/'.$materi->id_materi)
            ->assertNoContent();

        $this->assertDatabaseMissing('materis', [
            'id_materi' => 'M001',
        ]);
    }

    public function test_student_cannot_access_teacher_material_endpoints(): void
    {
        $student = $this->createUser('S001', 'siswa', 'siswa@example.com');

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/guru/materi')
            ->assertForbidden();
    }

    public function test_teacher_material_endpoints_require_authentication(): void
    {
        $this->getJson('/api/guru/materi')->assertUnauthorized();
    }

    private function createUser(string $id, string $role, string $email): User
    {
        return User::create([
            'id_user' => $id,
            'nomor_induk' => $id.'01',
            'nama_lengkap' => ucfirst($role).' Test',
            'role' => $role,
            'kelas' => $role === 'siswa' ? 'X' : null,
            'email' => $email,
            'password' => 'password',
        ]);
    }

    private function createMateri(string $id, string $judul): Materi
    {
        return Materi::create([
            'id_materi' => $id,
            'judul' => $judul,
            'kelas' => 'X',
            'deskripsi' => 'Deskripsi '.$judul,
            'id_user' => 'G001',
        ]);
    }
}
