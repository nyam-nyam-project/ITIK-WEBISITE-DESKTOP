<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    private User $admin;

    private User $guru;

    private User $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        // Never run the stale migrations or touch a configured live database.
        $this->assertTrue($this->app->environment('testing'));
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertEmpty(config('database.connections.sqlite.url'));
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->assertSame('array', config('cache.default'));
        $this->assertSame('array', config('session.driver'));

        $this->withoutVite();
        $this->createSchema();
        $this->seedScenario();
    }

    private function createSchema(): void
    {
        Schema::create('user', function (Blueprint $table) {
            $table->string('id_user', 50)->primary();
            $table->string('nomor_induk', 50)->nullable()->unique();
            $table->string('nama_lengkap', 100);
            $table->enum('role', ['admin', 'guru', 'siswa']);
            $table->string('status', 20)->default('aktif');
            $table->string('kelas', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('password');
            $table->boolean('wajib_ganti_password')->default(false);
            $table->string('dibuat_oleh', 50)->nullable();
            $table->timestamps();
            $table->foreign('dibuat_oleh')->references('id_user')->on('user')->nullOnDelete();
        });

        Schema::create('tahun_ajaran', function (Blueprint $table) {
            $table->string('id_tahun_ajaran', 50)->primary();
            $table->string('nama', 20)->unique();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('is_aktif')->default(false);
            $table->timestamps();
        });

        Schema::create('mapel', function (Blueprint $table) {
            $table->string('id_mapel', 50)->primary();
            $table->string('kode_mapel', 20)->unique();
            $table->string('nama_mapel', 100);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('kelas', function (Blueprint $table) {
            $table->string('id_kelas', 50)->primary();
            $table->string('nama_kelas', 50);
            $table->string('tingkat', 10);
            $table->string('id_tahun_ajaran', 50);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
            $table->unique(['nama_kelas', 'id_tahun_ajaran']);
            $table->foreign('id_tahun_ajaran')->references('id_tahun_ajaran')->on('tahun_ajaran')->restrictOnDelete();
        });

        Schema::create('anggota_kelas', function (Blueprint $table) {
            $table->bigIncrements('id_anggota_kelas');
            $table->string('id_kelas', 50);
            $table->string('id_user', 50);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->unique(['id_kelas', 'id_user']);
            $table->foreign('id_kelas')->references('id_kelas')->on('kelas')->restrictOnDelete();
            $table->foreign('id_user')->references('id_user')->on('user')->restrictOnDelete();
        });

        Schema::create('mapel_kelas', function (Blueprint $table) {
            $table->bigIncrements('id_mapel_kelas');
            $table->string('id_mapel', 50);
            $table->string('id_kelas', 50);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->unique(['id_mapel', 'id_kelas']);
            $table->foreign('id_mapel')->references('id_mapel')->on('mapel')->restrictOnDelete();
            $table->foreign('id_kelas')->references('id_kelas')->on('kelas')->restrictOnDelete();
        });

        Schema::create('kuis', function (Blueprint $table) {
            $table->string('id_kuis', 50)->primary();
            $table->string('judul', 150);
            $table->text('deskripsi')->nullable();
            $table->string('kategori', 50);
            $table->enum('tingkat_kesulitan', ['mudah', 'sedang', 'sulit'])->default('mudah');
            $table->string('id_mapel', 50);
            $table->string('id_tahun_ajaran', 50);
            $table->unsignedInteger('alokasi_waktu')->nullable();
            $table->decimal('kkm', 5, 2)->nullable();
            $table->dateTime('waktu_mulai')->nullable();
            $table->dateTime('waktu_selesai')->nullable();
            $table->string('id_user', 50);
            $table->string('status_publikasi', 20)->default('draft');
            $table->string('id_publisher', 50)->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->foreign('id_user')->references('id_user')->on('user')->restrictOnDelete();
            $table->foreign('id_publisher')->references('id_user')->on('user')->nullOnDelete();
            $table->foreign('id_mapel')->references('id_mapel')->on('mapel')->restrictOnDelete();
            $table->foreign('id_tahun_ajaran')->references('id_tahun_ajaran')->on('tahun_ajaran')->restrictOnDelete();
        });

        Schema::create('materi', function (Blueprint $table) {
            $table->string('id_materi', 50)->primary();
            $table->string('judul', 150);
            $table->string('bab', 50)->nullable();
            $table->string('kelas', 50)->nullable();
            $table->string('id_mapel', 50);
            $table->text('deskripsi')->nullable();
            $table->string('isi_materi')->nullable();
            $table->string('link_youtube')->nullable();
            $table->string('id_user', 50);
            $table->timestamps();
            $table->foreign('id_user')->references('id_user')->on('user')->restrictOnDelete();
            $table->foreign('id_mapel')->references('id_mapel')->on('mapel')->restrictOnDelete();
        });

        Schema::create('mengerjakan', function (Blueprint $table) {
            $table->increments('id_mengerjakan');
            $table->string('id_kuis', 50);
            $table->string('id_user', 50);
            $table->decimal('nilai', 5, 2)->nullable();
            $table->date('tanggal')->nullable();
            $table->integer('percobaan_ke')->default(1);
            $table->string('status_pengerjaan', 20)->default('final');
            $table->string('status_validasi', 50)->nullable()->default('belum divalidasi');
            $table->string('id_validator', 50)->nullable();
            // Match the dangerous production cascade: deactivation must preserve these rows.
            $table->foreign('id_user')->references('id_user')->on('user')->cascadeOnDelete();
            $table->foreign('id_kuis')->references('id_kuis')->on('kuis')->restrictOnDelete();
            $table->foreign('id_validator')->references('id_user')->on('user')->nullOnDelete();
        });
    }

    private function seedScenario(): void
    {
        $this->admin = $this->makeUser('U001', 'admin', 'Admin Sekolah');
        $this->guru = $this->makeUser('U002', 'guru', 'Guru TIK');
        $this->siswa = $this->makeUser('U003', 'siswa', 'Siswa Percobaan');

        TahunAjaran::create([
            'id_tahun_ajaran' => 'TA001', 'nama' => '2026/2027', 'is_aktif' => true,
            'tanggal_mulai' => '2026-07-14', 'tanggal_selesai' => '2027-06-20',
        ]);
        Mapel::create([
            'id_mapel' => 'MP001', 'kode_mapel' => 'TIK', 'nama_mapel' => 'Teknologi Informasi', 'is_aktif' => true,
        ]);
        $this->makeKelas('KLS001', 'X TKJ 1');
        $this->makeKelas('KLS002', 'X TKJ 2');
        $this->joinKelas('U003', 'KLS001');
        $this->makeKuis('K001');
        $this->makeKuis('K002');
        DB::table('materi')->insert([
            'id_materi' => 'M001', 'judul' => 'Algoritma', 'kelas' => 'X',
            'id_mapel' => 'MP001', 'id_user' => 'U002',
        ]);
    }

    private function makeUser(string $id, string $role = 'siswa', string $nama = 'Siswa Tambahan', string $status = 'aktif'): User
    {
        return User::create([
            'id_user' => $id, 'nomor_induk' => '000'.substr($id, 1), 'nama_lengkap' => $nama,
            'role' => $role, 'status' => $status, 'email' => strtolower($id).'@example.test',
            'password' => 'secret123', 'kelas' => $role === 'siswa' ? 'X' : null,
        ]);
    }

    private function makeKelas(string $id, string $nama, string $tahun = 'TA001'): Kelas
    {
        $kelas = Kelas::create([
            'id_kelas' => $id, 'nama_kelas' => $nama, 'tingkat' => 'X',
            'id_tahun_ajaran' => $tahun, 'is_aktif' => true,
        ]);
        $kelas->mapel()->attach('MP001');

        return $kelas;
    }

    private function makeKuis(string $id, array $attributes = []): void
    {
        DB::table('kuis')->insert(array_replace([
            'id_kuis' => $id, 'judul' => 'Kuis '.$id, 'kategori' => 'kuis harian',
            'id_mapel' => 'MP001', 'id_tahun_ajaran' => 'TA001', 'id_user' => 'U002',
            'kkm' => 75, 'status_publikasi' => 'terbit', 'is_aktif' => true,
        ], $attributes));
    }

    private function joinKelas(string $user, string $kelas): void
    {
        DB::table('anggota_kelas')->insert(['id_user' => $user, 'id_kelas' => $kelas]);
    }

    private function score(string $user, string $kuis, ?float $nilai, int $attempt = 1, string $status = 'final'): int
    {
        return DB::table('mengerjakan')->insertGetId([
            'id_user' => $user, 'id_kuis' => $kuis, 'nilai' => $nilai,
            'percobaan_ke' => $attempt, 'status_pengerjaan' => $status, 'tanggal' => '2026-10-29',
        ], 'id_mengerjakan');
    }

    public static function adminPages(): array
    {
        return [
            'dashboard' => ['admin.dashboard', [], 'admin.dashboard', 'tahunAktif', 'TA001'],
            'profil' => ['admin.profil', [], 'admin.profil', null, null],
            'tahun index' => ['admin.tahun-ajaran.index', [], 'admin.tahun-ajaran.index', 'tahunAjaran', 'TA001'],
            'tahun create' => ['admin.tahun-ajaran.create', [], 'admin.tahun-ajaran.create', null, null],
            'tahun camel binding edit' => ['admin.tahun-ajaran.edit', ['TA001'], 'admin.tahun-ajaran.edit', 'tahunAjaran', 'TA001'],
            'mapel index' => ['admin.mapel.index', [], 'admin.mapel.index', 'mapel', 'MP001'],
            'mapel create' => ['admin.mapel.create', [], 'admin.mapel.create', null, null],
            'mapel edit' => ['admin.mapel.edit', ['MP001'], 'admin.mapel.edit', 'mapel', 'MP001'],
            'kelas index' => ['admin.kelas.index', [], 'admin.kelas.index', 'kelas', 'KLS001'],
            'kelas create' => ['admin.kelas.create', [], 'admin.kelas.create', null, null],
            'kelas show' => ['admin.kelas.show', ['KLS001'], 'admin.kelas.show', 'kelas', 'KLS001'],
            'kelas edit' => ['admin.kelas.edit', ['KLS001'], 'admin.kelas.edit', 'kelas', 'KLS001'],
            'siswa index' => ['admin.siswa.index', [], 'admin.siswa.index', 'siswa', 'U003'],
            'siswa create' => ['admin.siswa.create', [], 'admin.siswa.create', null, null],
            'siswa edit' => ['admin.siswa.edit', ['U003'], 'admin.siswa.edit', 'siswa', 'U003'],
            'guru index' => ['admin.guru.index', [], 'admin.guru.index', 'guru', 'U002'],
            'guru create' => ['admin.guru.create', [], 'admin.guru.create', null, null],
            'guru edit' => ['admin.guru.edit', ['U002'], 'admin.guru.edit', 'guru', 'U002'],
            'rekap' => ['admin.rekap.index', [], 'admin.rekap.index', 'rekap', null],
        ];
    }

    #[DataProvider('adminPages')]
    public function test_admin_pages_render_with_real_database_data(string $route, array $parameters, string $view, ?string $key, ?string $id): void
    {
        $response = $this->actingAs($this->admin)->get(route($route, $parameters));
        $response->assertOk()->assertViewIs($view);

        if ($key !== null) {
            $response->assertViewHas($key, function ($data) use ($id) {
                if ($id === null) {
                    return $data instanceof Collection && $data->count() === 2;
                }
                if ($data instanceof Model) {
                    return $data->getKey() === $id;
                }

                return collect($data->all())->contains(fn ($row) => $row->getKey() === $id);
            });
        }
    }

    public function test_guests_are_redirected_from_every_admin_page_and_mutations(): void
    {
        foreach (self::adminPages() as [$route, $parameters]) {
            $this->get(route($route, $parameters))->assertRedirect(route('login'));
        }
        foreach ($this->adminMutations() as [$method, $route, $parameters]) {
            $this->{$method}(route($route, $parameters), [])->assertRedirect(route('login'));
        }
        $this->assertDatabaseCount('user', 3);
        $this->assertDatabaseCount('mapel', 1);
    }

    public static function nonAdminRoles(): array
    {
        return ['guru' => ['U002'], 'siswa' => ['U003']];
    }

    #[DataProvider('nonAdminRoles')]
    public function test_non_admin_roles_cannot_read_or_mutate_admin_resources(string $id): void
    {
        $this->actingAs(User::findOrFail($id));
        foreach (self::adminPages() as [$route, $parameters]) {
            $this->get(route($route, $parameters))->assertForbidden();
        }
        foreach ($this->adminMutations() as [$method, $route, $parameters]) {
            $this->{$method}(route($route, $parameters), [])->assertForbidden();
        }
        $this->assertDatabaseHas('tahun_ajaran', ['id_tahun_ajaran' => 'TA001', 'is_aktif' => 1]);
        $this->assertDatabaseHas('user', ['id_user' => 'U003', 'status' => 'aktif']);
    }

    private function adminMutations(): array
    {
        return [
            ['post', 'admin.tahun-ajaran.store', []], ['put', 'admin.tahun-ajaran.update', ['TA001']],
            ['patch', 'admin.tahun-ajaran.aktifkan', ['TA001']],
            ['post', 'admin.mapel.store', []], ['put', 'admin.mapel.update', ['MP001']],
            ['post', 'admin.kelas.store', []], ['put', 'admin.kelas.update', ['KLS001']],
            ['post', 'admin.kelas.anggota.store', ['KLS002']],
            ['post', 'admin.siswa.store', []], ['put', 'admin.siswa.update', ['U003']],
            ['patch', 'admin.siswa.nonaktifkan', ['U003']], ['patch', 'admin.siswa.aktifkan', ['U003']],
            ['post', 'admin.guru.store', []], ['put', 'admin.guru.update', ['U002']],
            ['patch', 'admin.guru.nonaktifkan', ['U002']], ['patch', 'admin.guru.aktifkan', ['U002']],
        ];
    }

    public static function teachingResources(): array
    {
        return ['materi' => ['materi', 'M001'], 'kuis' => ['kuis', 'K001']];
    }

    #[DataProvider('teachingResources')]
    public function test_admin_cannot_manage_teaching_content(string $resource, string $id): void
    {
        $this->actingAs($this->admin);
        foreach (['index', 'create', 'show', 'edit'] as $action) {
            $this->get(route($resource.'.'.$action, in_array($action, ['show', 'edit']) ? [$id] : []))->assertForbidden();
        }
        $this->post(route($resource.'.store'), [])->assertForbidden();
        $this->put(route($resource.'.update', $id), [])->assertForbidden();
        $this->delete(route($resource.'.destroy', $id))->assertForbidden();
        $this->assertDatabaseHas($resource, ['id_'.$resource => $id, 'id_user' => 'U002']);
    }

    public function test_admin_generic_dashboard_and_profile_redirect_to_admin_pages(): void
    {
        $this->actingAs($this->admin)->get(route('dashboard'))->assertRedirect(route('admin.dashboard'));
        $this->get(route('profil'))->assertRedirect(route('admin.profil'));
    }

    public function test_dashboard_counts_active_and_retired_accounts_and_unassigned_students(): void
    {
        $this->makeUser('U004', 'siswa', 'Siswa Keluar', 'nonaktif');
        $this->makeUser('U005', 'guru', 'Guru Keluar', 'nonaktif');
        $this->makeUser('U006');
        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk()
            ->assertViewHas('jumlahSiswaAktif', 2)->assertViewHas('jumlahSiswaNonaktif', 1)
            ->assertViewHas('jumlahGuruAktif', 1)->assertViewHas('jumlahGuruNonaktif', 1)
            ->assertViewHas('jumlahMapel', 1)->assertViewHas('siswaTanpaKelas', 1)
            ->assertViewHas('kelasTahunAktif', fn ($kelas) => $kelas->count() === 2 && $kelas->firstWhere('id_kelas', 'KLS001')->anggota_count === 1);
    }

    public function test_dashboard_handles_no_active_year(): void
    {
        TahunAjaran::query()->update(['is_aktif' => false]);
        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk()
            ->assertViewHas('tahunAktif', fn ($tahun) => $tahun === null)
            ->assertViewHas('kelasTahunAktif', fn ($kelas) => $kelas->isEmpty())
            ->assertViewHas('siswaTanpaKelas', 0);
    }

    public function test_mapel_creation_normalizes_code_and_uses_max_id_not_row_count(): void
    {
        Mapel::create(['id_mapel' => 'MP010', 'kode_mapel' => 'IPA', 'nama_mapel' => 'Sains']);
        $this->actingAs($this->admin)->post(route('admin.mapel.store'), [
            'kode_mapel' => 'b_ind', 'nama_mapel' => 'Bahasa Indonesia', 'deskripsi' => 'Bahasa', 'is_aktif' => 1,
        ])->assertRedirect(route('admin.mapel.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('mapel', [
            'id_mapel' => 'MP011', 'kode_mapel' => 'B_IND', 'nama_mapel' => 'Bahasa Indonesia', 'is_aktif' => 1,
        ]);
    }

    public function test_mapel_duplicate_lowercase_code_is_rejected_before_insert(): void
    {
        $this->actingAs($this->admin)->postJson(route('admin.mapel.store'), [
            'kode_mapel' => 'tik', 'nama_mapel' => 'Duplikat',
        ])->assertUnprocessable()->assertJsonValidationErrors('kode_mapel');
        $this->assertDatabaseCount('mapel', 1);
    }

    public function test_mapel_update_ignores_own_normalized_code_and_can_be_deactivated(): void
    {
        $this->actingAs($this->admin)->put(route('admin.mapel.update', 'MP001'), [
            'kode_mapel' => 'tik', 'nama_mapel' => 'TIK Diperbarui', 'deskripsi' => 'Baru', 'is_aktif' => 0,
        ])->assertRedirect(route('admin.mapel.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('mapel', ['id_mapel' => 'MP001', 'kode_mapel' => 'TIK', 'nama_mapel' => 'TIK Diperbarui', 'is_aktif' => 0]);
        $this->assertDatabaseCount('mapel_kelas', 2);
        $this->assertDatabaseCount('kuis', 2);
    }

    public function test_mapel_update_rejects_another_normalized_code(): void
    {
        Mapel::create(['id_mapel' => 'MP002', 'kode_mapel' => 'IPA', 'nama_mapel' => 'Sains']);
        $this->actingAs($this->admin)->putJson(route('admin.mapel.update', 'MP002'), [
            'kode_mapel' => 'tik', 'nama_mapel' => 'Duplikat',
        ])->assertUnprocessable()->assertJsonValidationErrors('kode_mapel');
        $this->assertDatabaseHas('mapel', ['id_mapel' => 'MP002', 'kode_mapel' => 'IPA']);
    }

    public function test_array_mapel_code_is_a_validation_error_not_a_server_error(): void
    {
        $this->actingAs($this->admin)->postJson(route('admin.mapel.store'), [
            'kode_mapel' => ['TIK'], 'nama_mapel' => 'Invalid',
        ])->assertUnprocessable()->assertJsonValidationErrors('kode_mapel');
    }

    public function test_kelas_creation_and_update_sync_mapel_assignments_without_deleting_membership(): void
    {
        Mapel::create(['id_mapel' => 'MP002', 'kode_mapel' => 'IPA', 'nama_mapel' => 'Sains']);
        $payload = ['nama_kelas' => 'XI TKJ 1', 'tingkat' => 'XI', 'id_tahun_ajaran' => 'TA001', 'is_aktif' => 1, 'mapel' => ['MP001', 'MP002']];
        $this->actingAs($this->admin)->post(route('admin.kelas.store'), $payload)
            ->assertRedirect(route('admin.kelas.show', 'KLS003'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('kelas', ['id_kelas' => 'KLS003', 'nama_kelas' => 'XI TKJ 1', 'tingkat' => 'XI']);
        $this->assertDatabaseHas('mapel_kelas', ['id_kelas' => 'KLS003', 'id_mapel' => 'MP001']);
        $this->assertDatabaseHas('mapel_kelas', ['id_kelas' => 'KLS003', 'id_mapel' => 'MP002']);
        $this->makeUser('U004');
        $this->joinKelas('U004', 'KLS003');
        $payload['mapel'] = ['MP002'];
        $payload['is_aktif'] = 0;
        $this->put(route('admin.kelas.update', 'KLS003'), $payload)
            ->assertRedirect(route('admin.kelas.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('mapel_kelas', ['id_kelas' => 'KLS003', 'id_mapel' => 'MP001']);
        $this->assertDatabaseHas('mapel_kelas', ['id_kelas' => 'KLS003', 'id_mapel' => 'MP002']);
        $this->assertDatabaseHas('anggota_kelas', ['id_kelas' => 'KLS003', 'id_user' => 'U004']);
        $this->assertDatabaseHas('kelas', ['id_kelas' => 'KLS003', 'is_aktif' => 0]);
    }

    public function test_kelas_duplicate_name_in_same_year_is_rejected(): void
    {
        $this->actingAs($this->admin)->postJson(route('admin.kelas.store'), [
            'nama_kelas' => 'X TKJ 1', 'tingkat' => 'X', 'id_tahun_ajaran' => 'TA001',
        ])->assertUnprocessable()->assertJsonValidationErrors('nama_kelas');
        $this->assertDatabaseCount('kelas', 2);
    }

    public function test_existing_class_year_cannot_be_rewritten(): void
    {
        TahunAjaran::create(['id_tahun_ajaran' => 'TA002', 'nama' => '2027/2028']);
        $this->actingAs($this->admin)->putJson(route('admin.kelas.update', 'KLS001'), [
            'nama_kelas' => 'X TKJ 1', 'tingkat' => 'X', 'id_tahun_ajaran' => 'TA002', 'mapel' => ['MP001'],
        ])->assertUnprocessable()->assertJsonValidationErrors('id_tahun_ajaran');
        $this->assertDatabaseHas('kelas', ['id_kelas' => 'KLS001', 'id_tahun_ajaran' => 'TA001']);
        $this->assertDatabaseHas('anggota_kelas', ['id_kelas' => 'KLS001', 'id_user' => 'U003']);
    }

    public static function resources(): array
    {
        return [
            'tahun-ajaran' => ['tahun-ajaran', 'TA001', 'tahun_ajaran', 'id_tahun_ajaran'],
            'mapel' => ['mapel', 'MP001', 'mapel', 'id_mapel'],
            'kelas' => ['kelas', 'KLS001', 'kelas', 'id_kelas'],
            'siswa' => ['siswa', 'U003', 'user', 'id_user'],
            'guru' => ['guru', 'U002', 'user', 'id_user'],
        ];
    }

    #[DataProvider('resources')]
    public function test_admin_resources_have_no_destroy_routes(string $resource, string $id, string $table, string $key): void
    {
        $this->assertFalse(Route::has('admin.'.$resource.'.destroy'));
        $this->actingAs($this->admin)->delete(url('/admin/'.$resource.'/'.$id))->assertStatus(405);
        $this->assertDatabaseHas($table, [$key => $id]);
    }

    #[DataProvider('resources')]
    public function test_resource_binding_rejects_unknown_ids(string $resource, string $id, string $table, string $key): void
    {
        $this->actingAs($this->admin)->get(route('admin.'.$resource.'.edit', 'missing'))->assertNotFound();
        $this->putJson(route('admin.'.$resource.'.update', 'missing'), [])->assertNotFound();
    }

    public static function invalidMembers(): array
    {
        return ['guru' => ['U002'], 'admin' => ['U001'], 'retired student' => ['U004']];
    }

    #[DataProvider('invalidMembers')]
    public function test_class_membership_rejects_non_students_and_retired_students(string $id): void
    {
        $this->makeUser('U004', 'siswa', 'Siswa Keluar', 'nonaktif');
        $this->actingAs($this->admin)->postJson(route('admin.kelas.anggota.store', 'KLS002'), ['id_user' => [$id]])
            ->assertUnprocessable()->assertJsonValidationErrors('id_user.0');
        $this->assertDatabaseMissing('anggota_kelas', ['id_kelas' => 'KLS002', 'id_user' => $id]);
    }

    public function test_inactive_class_cannot_receive_students(): void
    {
        Kelas::whereKey('KLS002')->update(['is_aktif' => false]);
        $this->makeUser('U004');
        $this->actingAs($this->admin)->postJson(route('admin.kelas.anggota.store', 'KLS002'), ['id_user' => ['U004']])->assertUnprocessable();
        $this->assertDatabaseMissing('anggota_kelas', ['id_user' => 'U004']);
    }

    public function test_same_year_membership_conflict_is_rejected_atomically(): void
    {
        $this->makeUser('U004');
        $this->actingAs($this->admin)->postJson(route('admin.kelas.anggota.store', 'KLS002'), ['id_user' => ['U004', 'U003']])
            ->assertUnprocessable()->assertJsonValidationErrors('id_user');
        $this->assertDatabaseCount('anggota_kelas', 1);
        $this->assertDatabaseMissing('anggota_kelas', ['id_kelas' => 'KLS002', 'id_user' => 'U004']);
        $this->assertDatabaseHas('anggota_kelas', ['id_kelas' => 'KLS001', 'id_user' => 'U003']);
    }

    public function test_readding_same_student_to_same_class_is_idempotent(): void
    {
        $this->actingAs($this->admin)->post(route('admin.kelas.anggota.store', 'KLS001'), ['id_user' => ['U003']])
            ->assertRedirect(route('admin.kelas.show', 'KLS001'))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('anggota_kelas', 1);
    }

    public function test_next_year_membership_is_allowed_without_removing_previous_year(): void
    {
        TahunAjaran::create(['id_tahun_ajaran' => 'TA002', 'nama' => '2027/2028']);
        $this->actingAs($this->admin)->post(route('admin.kelas.store'), [
            'nama_kelas' => 'X TKJ 1', 'tingkat' => 'X', 'id_tahun_ajaran' => 'TA002', 'is_aktif' => 1, 'mapel' => ['MP001'],
        ])->assertRedirect(route('admin.kelas.show', 'KLS003'))->assertSessionHasNoErrors();
        $this->get(route('admin.kelas.show', 'KLS003'))->assertOk()
            ->assertViewHas('siswaTersedia', fn ($siswa) => $siswa->contains('id_user', 'U003'));
        $this->post(route('admin.kelas.anggota.store', 'KLS003'), ['id_user' => ['U003']])
            ->assertRedirect(route('admin.kelas.show', 'KLS003'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('anggota_kelas', ['id_user' => 'U003', 'id_kelas' => 'KLS001']);
        $this->assertDatabaseHas('anggota_kelas', ['id_user' => 'U003', 'id_kelas' => 'KLS003']);
        $this->assertDatabaseCount('anggota_kelas', 2);
        $this->get(route('admin.kelas.show', 'KLS002'))->assertOk()
            ->assertViewHas('siswaTersedia', fn ($siswa) => ! $siswa->contains('id_user', 'U003'));
    }

    public static function optionalYearDates(): array
    {
        return ['omitted' => [[]], 'null' => [['tanggal_mulai' => null, 'tanggal_selesai' => null]], 'start only' => [['tanggal_mulai' => '2027-07-01']]];
    }

    #[DataProvider('optionalYearDates')]
    public function test_year_creation_dates_are_optional_and_activation_is_explicit(array $dates): void
    {
        $this->actingAs($this->admin)->post(route('admin.tahun-ajaran.store'), array_merge([
            'nama' => '2027/2028', 'is_aktif' => 1,
        ], $dates))->assertRedirect(route('admin.tahun-ajaran.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tahun_ajaran', [
            'id_tahun_ajaran' => 'TA002', 'nama' => '2027/2028', 'is_aktif' => 0,
        ]);
        $created = TahunAjaran::findOrFail('TA002');
        // SQLite stores Eloquent's date casts as datetime strings, unlike MySQL DATE.
        $this->assertSame($dates['tanggal_mulai'] ?? null, $created->tanggal_mulai?->toDateString());
        $this->assertNull($created->tanggal_selesai);
        $this->assertSame(1, TahunAjaran::where('is_aktif', 1)->count());
    }

    public function test_camel_case_year_binding_updates_the_selected_row_and_activates_only_one_year(): void
    {
        TahunAjaran::create(['id_tahun_ajaran' => 'TA002', 'nama' => '2027/2028']);
        $this->actingAs($this->admin)->put(route('admin.tahun-ajaran.update', ['tahunAjaran' => 'TA002']), [
            'nama' => '2027/2028', 'tanggal_mulai' => '2027-07-01', 'tanggal_selesai' => '2028-06-30',
        ])->assertRedirect(route('admin.tahun-ajaran.index'))->assertSessionHasNoErrors();
        $updated = TahunAjaran::findOrFail('TA002');
        $this->assertSame('2027-07-01', $updated->tanggal_mulai->toDateString());
        $this->assertSame('2028-06-30', $updated->tanggal_selesai->toDateString());
        $this->assertDatabaseHas('tahun_ajaran', ['id_tahun_ajaran' => 'TA001', 'nama' => '2026/2027']);
        foreach (['TA002', 'TA002', 'TA001'] as $id) {
            $this->patch(route('admin.tahun-ajaran.aktifkan', ['tahunAjaran' => $id]))
                ->assertRedirect(route('admin.tahun-ajaran.index'))->assertSessionHasNoErrors();
            $this->assertSame([$id], TahunAjaran::where('is_aktif', 1)->pluck('id_tahun_ajaran')->all());
        }
        $this->patch(route('admin.tahun-ajaran.aktifkan', ['tahunAjaran' => 'missing']))->assertNotFound();
        $this->assertDatabaseHas('tahun_ajaran', ['id_tahun_ajaran' => 'TA001', 'is_aktif' => 1]);
    }

    public function test_year_validation_rejects_duplicate_name_and_reversed_dates(): void
    {
        $this->actingAs($this->admin)->postJson(route('admin.tahun-ajaran.store'), ['nama' => '2026/2027'])
            ->assertUnprocessable()->assertJsonValidationErrors('nama');
        $this->postJson(route('admin.tahun-ajaran.store'), [
            'nama' => '2027/2028', 'tanggal_mulai' => '2028-06-30', 'tanggal_selesai' => '2027-07-01',
        ])->assertUnprocessable()->assertJsonValidationErrors('tanggal_selesai');
        $this->assertDatabaseCount('tahun_ajaran', 1);
    }

    public static function accountResources(): array
    {
        return ['siswa' => ['siswa', 'U003', 'siswa.dashboard'], 'guru' => ['guru', 'U002', 'materi.index']];
    }

    #[DataProvider('accountResources')]
    public function test_account_creation_preserves_leading_zero_number_hashes_password_and_forces_role(string $resource, string $existingId, string $landing): void
    {
        $payload = [
            'nomor_induk' => '001234567890', 'nama_lengkap' => 'Akun Baru', 'email' => 'baru@example.test', 'password' => 'newsecret123',
            'role' => 'admin', 'status' => 'nonaktif', 'dibuat_oleh' => 'U002', 'id_user' => 'U999', 'wajib_ganti_password' => false,
        ];
        if ($resource === 'siswa') {
            $payload['id_kelas'] = 'KLS002';
        }
        $this->actingAs($this->admin)->post(route('admin.'.$resource.'.store'), $payload)
            ->assertRedirect(route('admin.'.$resource.'.index'))->assertSessionHasNoErrors();
        $created = User::where('nomor_induk', '001234567890')->firstOrFail();
        $this->assertSame('U004', $created->getKey());
        $this->assertSame('001234567890', $created->nomor_induk);
        $this->assertSame($resource, $created->role);
        $this->assertSame('aktif', $created->status);
        $this->assertSame('U001', $created->dibuat_oleh);
        $this->assertTrue($created->wajib_ganti_password);
        $this->assertNotSame('newsecret123', $created->password);
        $this->assertTrue(Hash::check('newsecret123', $created->password));
        if ($resource === 'siswa') {
            $this->assertSame('X', $created->kelas);
            $this->assertDatabaseHas('anggota_kelas', ['id_user' => 'U004', 'id_kelas' => 'KLS002']);
        }
    }

    #[DataProvider('accountResources')]
    public function test_account_update_preserves_password_when_blank_and_hashes_replacement(string $resource, string $id, string $landing): void
    {
        $user = User::findOrFail($id);
        $originalHash = $user->password;
        $payload = [
            'nomor_induk' => $user->nomor_induk, 'nama_lengkap' => 'Nama Diperbarui', 'email' => $user->email,
            'password' => '', 'status' => 'aktif', 'role' => 'admin', 'id_user' => 'U999', 'dibuat_oleh' => 'U002',
        ];
        $this->actingAs($this->admin)->put(route('admin.'.$resource.'.update', $id), $payload)
            ->assertRedirect(route('admin.'.$resource.'.index'))->assertSessionHasNoErrors();
        $user->refresh();
        $this->assertSame($originalHash, $user->password);
        $this->assertSame('Nama Diperbarui', $user->nama_lengkap);
        $this->assertSame($resource, $user->role);
        $this->assertNull($user->dibuat_oleh);
        $payload['password'] = 'replacement123';
        $this->put(route('admin.'.$resource.'.update', $id), $payload)
            ->assertRedirect(route('admin.'.$resource.'.index'))->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('replacement123', $user->refresh()->password));
        $this->assertTrue($user->wajib_ganti_password);
        $this->assertDatabaseMissing('user', ['id_user' => 'U999']);
    }

    #[DataProvider('accountResources')]
    public function test_account_number_must_be_numeric_and_unique_across_roles(string $resource, string $id, string $landing): void
    {
        $this->actingAs($this->admin);
        $payload = ['nomor_induk' => $this->admin->nomor_induk, 'nama_lengkap' => 'Duplikat', 'password' => 'secret123'];
        $this->postJson(route('admin.'.$resource.'.store'), $payload)->assertUnprocessable()->assertJsonValidationErrors('nomor_induk');
        $payload['nomor_induk'] = '00ABC123';
        $this->postJson(route('admin.'.$resource.'.store'), $payload)->assertUnprocessable()->assertJsonValidationErrors('nomor_induk');
        $payload['nomor_induk'] = $this->admin->nomor_induk;
        $payload['status'] = 'aktif';
        $this->putJson(route('admin.'.$resource.'.update', $id), $payload)->assertUnprocessable()->assertJsonValidationErrors('nomor_induk');
        $this->assertDatabaseCount('user', 3);
        $this->assertSame('000'.substr($id, 1), User::findOrFail($id)->nomor_induk);
    }

    #[DataProvider('accountResources')]
    public function test_account_deactivation_preserves_scores_content_and_membership_and_can_be_reactivated(string $resource, string $id, string $landing): void
    {
        $scoreId = $this->score('U003', 'K001', 88);
        $before = (array) DB::table('mengerjakan')->where('id_mengerjakan', $scoreId)->first();
        $this->actingAs($this->admin)->patch(route('admin.'.$resource.'.nonaktifkan', $id))
            ->assertRedirect(route('admin.'.$resource.'.index'));
        $this->assertDatabaseHas('user', ['id_user' => $id, 'status' => 'nonaktif']);
        $this->assertSame($before, (array) DB::table('mengerjakan')->where('id_mengerjakan', $scoreId)->first());
        $this->assertDatabaseHas('anggota_kelas', ['id_user' => 'U003', 'id_kelas' => 'KLS001']);
        $this->assertDatabaseHas('kuis', ['id_kuis' => 'K001', 'id_user' => 'U002']);
        $this->assertDatabaseHas('materi', ['id_materi' => 'M001', 'id_user' => 'U002']);

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->from(route('login'))->post(route('login.process'), ['email' => strtolower($id).'@example.test', 'password' => 'secret123'])
            ->assertRedirect(route('login'))->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->actingAs($this->admin)->patch(route('admin.'.$resource.'.aktifkan', $id))
            ->assertRedirect(route('admin.'.$resource.'.index'));
        $this->assertDatabaseHas('user', ['id_user' => $id, 'status' => 'aktif']);
        $this->post(route('logout'));
        $this->post(route('login.process'), ['email' => strtolower($id).'@example.test', 'password' => 'secret123'])
            ->assertRedirect(route($landing))->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs(User::findOrFail($id));
        $this->assertSame($before, (array) DB::table('mengerjakan')->where('id_mengerjakan', $scoreId)->first());
    }

    #[DataProvider('accountResources')]
    public function test_an_already_authenticated_session_is_denied_after_account_becomes_inactive(string $resource, string $id, string $landing): void
    {
        $this->post(route('login.process'), ['email' => strtolower($id).'@example.test', 'password' => 'secret123'])
            ->assertRedirect(route($landing));
        $this->assertAuthenticatedAs(User::findOrFail($id));
        $guardName = $this->app['auth']->guard()->getName();
        $this->assertSame($id, $this->app['session.store']->get($guardName));

        // Simulate another administrator retiring this account between HTTP requests.
        DB::table('user')->where('id_user', $id)->update(['status' => 'nonaktif']);
        $this->app['auth']->forgetGuards();
        $this->get(route($landing))->assertRedirect(route('login'))->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertNull($this->app['session.store']->get($guardName));
    }

    #[DataProvider('accountResources')]
    public function test_role_specific_resource_tampering_cannot_edit_or_retire_admin_or_other_role(string $resource, string $id, string $landing): void
    {
        $this->actingAs($this->admin);
        $otherRoleId = $resource === 'siswa' ? 'U002' : 'U003';
        foreach (['U001', $otherRoleId] as $target) {
            $before = User::findOrFail($target)->getAttributes();
            $this->get(route('admin.'.$resource.'.edit', $target))->assertNotFound();
            $this->putJson(route('admin.'.$resource.'.update', $target), [
                'nomor_induk' => '999999', 'nama_lengkap' => 'Dibajak', 'password' => 'hacked123', 'role' => $resource, 'status' => 'nonaktif',
            ])->assertNotFound();
            $this->patch(route('admin.'.$resource.'.nonaktifkan', $target))->assertNotFound();
            $this->patch(route('admin.'.$resource.'.aktifkan', $target))->assertNotFound();
            $this->assertSame($before, User::findOrFail($target)->getAttributes());
        }
    }

    private function seedRekapScenario(): void
    {
        foreach (['U004' => 'Siswa Tertinggi', 'U005' => 'Siswa Terendah', 'U006' => 'Siswa Belum Dinilai', 'U007' => 'Siswa Kelas Dua'] as $id => $name) {
            $this->makeUser($id, 'siswa', $name);
            $this->joinKelas($id, $id === 'U007' ? 'KLS002' : 'KLS001');
        }
        $this->makeKelas('KLS003', 'X TKJ Kosong');
        foreach ([1, 2, 3] as $attempt) {
            $this->score('U003', 'K001', 100, $attempt);
        }
        $this->score('U003', 'K002', 20);
        $this->score('U004', 'K001', 40);
        $this->score('U004', 'K001', 90, 2);
        $this->score('U004', 'K002', 90);
        $this->score('U005', 'K001', 10);
        $this->score('U005', 'K002', 30);
        $this->score('U006', 'K001', null);
        $this->score('U006', 'K002', 100, 1, 'dibatalkan');
        $this->score('U007', 'K001', 80);
        $this->score('U007', 'K002', 100);
    }

    public function test_rekap_uses_best_score_per_quiz_then_unweighted_student_average_and_separates_classes(): void
    {
        $this->seedRekapScenario();
        $this->actingAs($this->admin)->get(route('admin.rekap.index'))->assertOk()->assertViewHas('rekap', function ($rekap) {
            $this->assertCount(3, $rekap);
            $rows = $rekap->keyBy(fn ($row) => $row['kelas']->id_kelas);
            $first = $rows['KLS001'];
            $this->assertSame(4, $first['jumlah_anggota']);
            $this->assertSame(3, $first['jumlah_dinilai']);
            $this->assertSame(1, $first['belum_dinilai']);
            $this->assertSame(56.67, $first['rata_rata']);
            $this->assertSame(90.0, $first['tertinggi']);
            $this->assertSame(20.0, $first['terendah']);
            $this->assertSame(['Siswa Tertinggi'], $first['siswa_tertinggi']->pluck('nama_lengkap')->all());
            $this->assertSame(['Siswa Terendah'], $first['siswa_terendah']->pluck('nama_lengkap')->all());
            $this->assertSame(1, $first['jumlah_kuat']);
            $this->assertSame(2, $first['jumlah_lemah']);
            $this->assertSame(['U004', 'U003', 'U005'], $first['siswa']->pluck('id_user')->all());
            $repeated = $first['siswa']->firstWhere('id_user', 'U003');
            $this->assertSame(60.0, $repeated->nilai_akhir, 'Three scores of 100 on one quiz and one score of 20 on another must average to 60, not 80.');
            $this->assertSame(2, $repeated->jumlah_kuis);
            $this->assertSame(90.0, $first['siswa']->firstWhere('id_user', 'U004')->nilai_akhir);
            $this->assertSame(['U007'], $rows['KLS002']['siswa']->pluck('id_user')->all());
            $this->assertSame(90.0, $rows['KLS002']['rata_rata']);
            $this->assertSame(1, $rows['KLS002']['jumlah_kuat']);
            $this->assertSame(0, $rows['KLS002']['jumlah_lemah']);
            $this->assertEmpty($rows['KLS003']['siswa']);
            foreach (['rata_rata', 'tertinggi', 'terendah'] as $key) {
                $this->assertNull($rows['KLS003'][$key]);
            }
            foreach (['jumlah_anggota', 'jumlah_dinilai', 'belum_dinilai', 'jumlah_kuat', 'jumlah_lemah'] as $key) {
                $this->assertSame(0, $rows['KLS003'][$key]);
            }

            return true;
        });
    }

    public function test_rekap_single_quiz_uses_its_kkm_and_keeps_best_attempt_only(): void
    {
        $this->seedRekapScenario();
        DB::table('kuis')->where('id_kuis', 'K001')->update(['kkm' => 90]);
        $this->actingAs($this->admin)->get(route('admin.rekap.index', ['tahun_ajaran' => 'TA001', 'mapel' => 'MP001', 'kuis' => 'K001']))
            ->assertOk()->assertViewHas('idKuis', 'K001')
            ->assertViewHas('kuisTerpilih', fn ($kuis) => $kuis->getKey() === 'K001')
            ->assertViewHas('rekap', function ($rekap) {
                $row = $rekap->first(fn ($row) => $row['kelas']->id_kelas === 'KLS001');
                $this->assertSame(90.0, $row['kkm']);
                $this->assertSame(66.67, $row['rata_rata']);
                $this->assertSame(2, $row['jumlah_kuat']);
                $this->assertSame(1, $row['jumlah_lemah']);
                $this->assertSame(1, $row['belum_dinilai']);
                $this->assertSame(100.0, $row['siswa']->firstWhere('id_user', 'U003')->nilai_akhir);
                $this->assertSame(1, $row['siswa']->firstWhere('id_user', 'U003')->jumlah_kuis);

                return true;
            });
    }

    public function test_rekap_retains_archived_quiz_scores_and_retired_students_in_previous_year(): void
    {
        $this->seedRekapScenario();
        TahunAjaran::create(['id_tahun_ajaran' => 'TA002', 'nama' => '2027/2028']);
        $this->makeKelas('KLS004', 'XI TKJ 1', 'TA002');
        $this->actingAs($this->admin)->post(route('admin.kelas.anggota.store', 'KLS004'), ['id_user' => ['U003']])->assertSessionHasNoErrors();
        $this->patch(route('admin.tahun-ajaran.aktifkan', 'TA002'))->assertRedirect();
        $this->makeKuis('K003', ['id_tahun_ajaran' => 'TA002']);
        $this->score('U003', 'K003', 10);
        DB::table('kuis')->where('id_kuis', 'K001')->update(['is_aktif' => false]);
        $this->patch(route('admin.siswa.nonaktifkan', 'U003'))->assertRedirect();

        $this->get(route('admin.rekap.index', ['tahun_ajaran' => 'TA001']))->assertOk()
            ->assertViewHas('daftarKuis', fn ($kuis) => $kuis->contains('id_kuis', 'K001') && ! $kuis->contains('id_kuis', 'K003'))
            ->assertViewHas('rekap', function ($rekap) {
                $row = $rekap->first(fn ($row) => $row['kelas']->id_kelas === 'KLS001');

                return $rekap->count() === 3 && $row['siswa']->firstWhere('id_user', 'U003')->nilai_akhir === 60.0;
            });
        $this->get(route('admin.rekap.index', ['tahun_ajaran' => 'TA001', 'kuis' => 'K001']))->assertOk()
            ->assertViewHas('rekap', fn ($rekap) => $rekap->first(fn ($row) => $row['kelas']->id_kelas === 'KLS001')['siswa']->firstWhere('id_user', 'U003')->nilai_akhir === 100.0);
        $this->get(route('admin.rekap.index'))->assertOk()->assertViewHas('idTahun', 'TA002')
            ->assertViewHas('rekap', fn ($rekap) => $rekap->count() === 1 && $rekap->first()['kelas']->id_kelas === 'KLS004' && $rekap->first()['siswa']->firstWhere('id_user', 'U003')->nilai_akhir === 10.0);
        $this->assertDatabaseHas('anggota_kelas', ['id_user' => 'U003', 'id_kelas' => 'KLS001']);
        $this->assertDatabaseHas('anggota_kelas', ['id_user' => 'U003', 'id_kelas' => 'KLS004']);
        $this->assertDatabaseCount('mengerjakan', 14);
    }

    public function test_rekap_lists_all_students_tied_at_highest_and_lowest_score(): void
    {
        $this->makeUser('U004', 'siswa', 'Siswa Seri');
        $this->joinKelas('U004', 'KLS001');
        $this->score('U003', 'K001', 75);
        $this->score('U004', 'K001', 75);
        $this->actingAs($this->admin)->get(route('admin.rekap.index'))->assertOk()->assertViewHas('rekap', function ($rekap) {
            $row = $rekap->first(fn ($row) => $row['kelas']->id_kelas === 'KLS001');
            $this->assertEqualsCanonicalizing(['U003', 'U004'], $row['siswa_tertinggi']->pluck('id_user')->all());
            $this->assertEqualsCanonicalizing(['U003', 'U004'], $row['siswa_terendah']->pluck('id_user')->all());
            $this->assertSame(2, $row['jumlah_kuat']);
            $this->assertSame(0, $row['jumlah_lemah']);

            return true;
        });
    }

    public function test_rekap_unscored_nonempty_class_is_not_reported_as_zero_average(): void
    {
        $this->score('U003', 'K001', null);
        $this->score('U003', 'K002', 100, 1, 'dibatalkan');
        $this->actingAs($this->admin)->get(route('admin.rekap.index'))->assertOk()->assertViewHas('rekap', function ($rekap) {
            $row = $rekap->first(fn ($row) => $row['kelas']->id_kelas === 'KLS001');

            return $row['jumlah_anggota'] === 1 && $row['jumlah_dinilai'] === 0 && $row['belum_dinilai'] === 1
                && $row['rata_rata'] === null && $row['tertinggi'] === null && $row['terendah'] === null && $row['siswa']->isEmpty();
        });
    }

    public static function invalidRekapFilters(): array
    {
        return ['year' => ['tahun_ajaran'], 'subject' => ['mapel'], 'quiz' => ['kuis']];
    }

    #[DataProvider('invalidRekapFilters')]
    public function test_rekap_invalid_filter_is_rejected_instead_of_showing_aggregate(string $filter): void
    {
        $this->score('U003', 'K001', 100);
        $this->actingAs($this->admin)->getJson(route('admin.rekap.index', [$filter => 'missing']))
            ->assertUnprocessable()->assertJsonValidationErrors($filter);
    }

    public static function mismatchedQuizFilters(): array
    {
        return ['wrong year' => [['tahun_ajaran' => 'TA002']], 'wrong subject' => [['mapel' => 'MP002']]];
    }

    #[DataProvider('mismatchedQuizFilters')]
    public function test_rekap_existing_quiz_outside_selected_filter_cannot_silently_show_aggregate(array $filter): void
    {
        TahunAjaran::create(['id_tahun_ajaran' => 'TA002', 'nama' => '2027/2028']);
        Mapel::create(['id_mapel' => 'MP002', 'kode_mapel' => 'IPA', 'nama_mapel' => 'Sains']);
        $this->score('U003', 'K001', 100);
        $parameters = array_merge(['tahun_ajaran' => 'TA001', 'mapel' => 'MP001', 'kuis' => 'K001'], $filter);
        $this->actingAs($this->admin)->getJson(route('admin.rekap.index', $parameters))
            ->assertUnprocessable()->assertJsonValidationErrors('kuis');
        $this->from(route('admin.rekap.index'))->get(route('admin.rekap.index', $parameters))
            ->assertRedirect(route('admin.rekap.index'))->assertSessionHasErrors('kuis');
    }
}
