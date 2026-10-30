<?php

namespace Tests\Feature;

use App\Models\Kuis;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\TahunAjaran;
use App\Models\User;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class KuisMateriMapelTest extends TestCase
{
    private User $guru;

    protected function setUp(): void
    {
        parent::setUp();

        // Fail closed before any Schema call or connection to a configured live DB.
        // Do not use RefreshDatabase: database/itik.sql, not migrations, is authoritative.
        $this->assertTrue($this->app->environment('testing'));
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame('sqlite', config('database.connections.sqlite.driver'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertEmpty(config('database.connections.sqlite.url'));
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->assertSame(1, (int) DB::selectOne('PRAGMA foreign_keys')->foreign_keys);
        $this->assertSame('array', config('cache.default'));
        $this->assertSame('array', config('session.driver'));

        $this->withoutVite();
        $this->travelTo(now()->setDate(2026, 10, 29)->setTime(8, 0));
        $this->createSchema();
        $this->seedScenario();
        Storage::fake('public');
    }

    protected function tearDown(): void
    {
        $this->travelBack();
        parent::tearDown();
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
            $table->foreign('id_mapel')->references('id_mapel')->on('mapel')->restrictOnDelete();
            $table->foreign('id_tahun_ajaran')->references('id_tahun_ajaran')->on('tahun_ajaran')->restrictOnDelete();
            $table->foreign('id_user')->references('id_user')->on('user')->restrictOnDelete();
            $table->foreign('id_publisher')->references('id_user')->on('user')->nullOnDelete();
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
            $table->unique(['kelas', 'bab', 'judul']);
            $table->foreign('id_mapel')->references('id_mapel')->on('mapel')->restrictOnDelete();
            $table->foreign('id_user')->references('id_user')->on('user')->restrictOnDelete();
        });

        Schema::create('materi_kuis', function (Blueprint $table) {
            $table->increments('id_materi_kuis');
            $table->string('id_materi', 50);
            $table->string('id_kuis', 50);
            $table->unique(['id_materi', 'id_kuis']);
            $table->foreign('id_materi')->references('id_materi')->on('materi')->cascadeOnDelete();
            $table->foreign('id_kuis')->references('id_kuis')->on('kuis')->cascadeOnDelete();
        });

        Schema::create('remedial', function (Blueprint $table) {
            $table->increments('id_remedial');
            $table->string('id_kuis_remedial', 50)->unique();
            $table->string('id_kuis_asal', 50);
            $table->foreign('id_kuis_remedial')->references('id_kuis')->on('kuis')->cascadeOnDelete();
            $table->foreign('id_kuis_asal')->references('id_kuis')->on('kuis')->restrictOnDelete();
        });

        Schema::create('soal', function (Blueprint $table) {
            $table->string('id_soal', 50)->primary();
            $table->string('id_kuis', 50);
            $table->text('pertanyaan');
            foreach (['a', 'b', 'c', 'd', 'e'] as $option) {
                $table->string('opsi_'.$option)->nullable();
            }
            $table->string('jawaban', 5)->nullable();
            $table->decimal('bobot', 5, 2)->default(1);
            $table->string('tingkat_kesulitan', 50)->nullable();
            $table->foreign('id_kuis')->references('id_kuis')->on('kuis')->cascadeOnDelete();
        });
    }

    private function seedScenario(): void
    {
        foreach (['U001' => 'admin', 'U002' => 'guru', 'U003' => 'siswa'] as $id => $role) {
            $user = User::create([
                'id_user' => $id, 'nomor_induk' => '000'.substr($id, 1),
                'nama_lengkap' => 'Pengguna '.$role, 'role' => $role, 'status' => 'aktif',
                'email' => strtolower($id).'@example.test', 'password' => 'secret123',
            ]);
            if ($role === 'guru') {
                $this->guru = $user;
            }
        }

        TahunAjaran::create(['id_tahun_ajaran' => 'TA001', 'nama' => '2026/2027', 'is_aktif' => true]);
        TahunAjaran::create(['id_tahun_ajaran' => 'TA002', 'nama' => '2027/2028', 'is_aktif' => false]);
        foreach (['MP001' => true, 'MP002' => true, 'MP003' => false, 'MP004' => false] as $id => $active) {
            Mapel::create([
                'id_mapel' => $id, 'kode_mapel' => $id, 'nama_mapel' => 'Pelajaran '.$id, 'is_aktif' => $active,
            ]);
        }
        $this->makeMateri('M001');
        $this->makeMateri('M002');
        $this->makeKuis('K001')->materi()->attach('M001');
        DB::table('soal')->insert([
            'id_soal' => 'S001', 'id_kuis' => 'K001', 'pertanyaan' => 'Apa itu algoritma?',
            'opsi_a' => 'Langkah logis', 'opsi_b' => 'Monitor', 'opsi_c' => 'Mouse', 'opsi_d' => 'Keyboard',
            'jawaban' => 'A', 'bobot' => 100, 'tingkat_kesulitan' => 'mudah',
        ]);
    }

    private function makeKuis(string $id, array $attributes = []): Kuis
    {
        return Kuis::create(array_replace([
            'id_kuis' => $id, 'judul' => 'Kuis '.$id, 'kategori' => 'pre-test',
            'tingkat_kesulitan' => 'mudah', 'id_mapel' => 'MP001', 'id_tahun_ajaran' => 'TA001',
            'id_user' => 'U002', 'alokasi_waktu' => 20, 'kkm' => 75, 'status_publikasi' => 'draft', 'is_aktif' => true,
        ], $attributes));
    }

    private function makeMateri(string $id, array $attributes = []): Materi
    {
        return Materi::create(array_replace([
            'id_materi' => $id, 'judul' => 'Materi '.$id, 'bab' => 'Bab 1', 'kelas' => 'X',
            'id_mapel' => 'MP001', 'id_user' => 'U002', 'deskripsi' => 'Materi algoritma',
        ], $attributes));
    }

    private function payload(string $resource, array $attributes = []): array
    {
        $defaults = $resource === 'kuis'
            ? ['judul' => 'Kuis baru', 'deskripsi' => 'Latihan algoritma', 'kategori' => 'pre-test',
                'id_mapel' => 'MP001', 'tingkat_kesulitan' => 'sedang', 'alokasi_waktu' => 30, 'kkm' => 80]
            : ['judul' => 'Materi baru', 'bab' => 'Bab 2', 'kelas' => 'XI', 'id_mapel' => 'MP001',
                'deskripsi' => 'Materi pemrograman', 'link_youtube' => 'https://www.youtube.com/watch?v=regression123'];

        return array_replace($defaults, $attributes);
    }

    private function content(string $resource): Model
    {
        return $resource === 'kuis' ? Kuis::findOrFail('K001') : Materi::findOrFail('M001');
    }

    private function snapshot(): array
    {
        $rows = [];
        foreach (['kuis', 'materi', 'materi_kuis', 'remedial', 'soal'] as $table) {
            $rows[$table] = DB::table($table)->orderBy('id_'.$table)->get()
                ->map(fn ($row) => (array) $row)->all();
        }

        return $rows;
    }

    private function select(string $html, string $name): DOMElement
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8">'.$html);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        $nodes = (new DOMXPath($document))->query('//select[@name="'.$name.'"]');
        $this->assertSame(1, $nodes->length, 'Expected one select named '.$name);
        $select = $nodes->item(0);
        $this->assertInstanceOf(DOMElement::class, $select);

        return $select;
    }

    private function assertOptions(string $html, string $name, array $expected, ?string $selected = null): void
    {
        $values = [];
        $selectedValues = [];
        foreach ($this->select($html, $name)->getElementsByTagName('option') as $option) {
            $value = $option->getAttribute('value');
            if ($value !== '') {
                $values[] = $value;
            }
            if ($option->hasAttribute('selected')) {
                $selectedValues[] = $value;
            }
        }
        $this->assertEqualsCanonicalizing($expected, $values, 'Options for '.$name);
        if ($selected !== null) {
            $this->assertSame([$selected], $selectedValues, 'Selected option for '.$name);
        }
    }

    public static function resources(): array
    {
        return ['kuis' => ['kuis'], 'materi' => ['materi']];
    }

    #[DataProvider('resources')]
    public function test_create_forms_offer_only_active_subjects_and_restore_old_selection(string $resource): void
    {
        $this->actingAs($this->guru);
        $response = $this->get(route($resource.'.create'))->assertOk()->assertViewIs($resource.'.create');
        $this->assertOptions($response->getContent(), 'id_mapel', ['MP001', 'MP002']);
        $this->assertTrue($this->select($response->getContent(), 'id_mapel')->hasAttribute('required'));

        $response = $this->withSession(['_old_input' => [
            'id_mapel' => 'MP002', 'kategori' => 'pre-test', 'tingkat_kesulitan' => 'sulit', 'materi' => ['M002'],
        ]])->get(route($resource.'.create'))->assertOk();
        $this->assertOptions($response->getContent(), 'id_mapel', ['MP001', 'MP002'], 'MP002');
        if ($resource === 'kuis') {
            $this->assertOptions($response->getContent(), 'kategori', ['post-test', 'pre-test', 'kuis harian', 'ulangan', 'remedial'], 'pre-test');
            $this->assertOptions($response->getContent(), 'tingkat_kesulitan', ['mudah', 'sedang', 'sulit'], 'sulit');
            $this->assertTrue($this->select($response->getContent(), 'tingkat_kesulitan')->hasAttribute('required'));
            $this->assertOptions($response->getContent(), 'materi[]', ['M001', 'M002'], 'M002');
        }
    }

    #[DataProvider('resources')]
    public function test_edit_forms_include_current_inactive_subject_but_not_other_inactive_subjects(string $resource): void
    {
        $content = $this->content($resource);
        $content->update(['id_mapel' => 'MP003']);
        $response = $this->actingAs($this->guru)->get(route($resource.'.edit', $content->getKey()))
            ->assertOk()->assertViewIs($resource.'.edit');
        $this->assertOptions($response->getContent(), 'id_mapel', ['MP001', 'MP002', 'MP003'], 'MP003');
        if ($resource === 'kuis') {
            $this->assertOptions($response->getContent(), 'kategori', ['post-test', 'pre-test', 'kuis harian', 'ulangan', 'remedial'], 'pre-test');
            $this->assertOptions($response->getContent(), 'tingkat_kesulitan', ['mudah', 'sedang', 'sulit'], 'mudah');
            $this->assertOptions($response->getContent(), 'materi[]', ['M001', 'M002'], 'M001');
        }

        $response = $this->withSession(['_old_input' => [
            'id_mapel' => 'MP002', 'kategori' => 'pre-test', 'tingkat_kesulitan' => 'sulit', 'materi' => ['M002'],
        ]])->get(route($resource.'.edit', $content->getKey()))->assertOk();
        $this->assertOptions($response->getContent(), 'id_mapel', ['MP001', 'MP002', 'MP003'], 'MP002');
        if ($resource === 'kuis') {
            $this->assertOptions($response->getContent(), 'tingkat_kesulitan', ['mudah', 'sedang', 'sulit'], 'sulit');
            $this->assertOptions($response->getContent(), 'materi[]', ['M001', 'M002'], 'M002');
        }
    }

    public static function difficultySuccess(): array
    {
        $cases = [];
        foreach (['store', 'update'] as $action) {
            foreach (['mudah', 'sedang', 'sulit'] as $difficulty) {
                $cases[$action.' '.$difficulty] = [$action, $difficulty];
            }
        }

        return $cases;
    }

    #[DataProvider('difficultySuccess')]
    public function test_kuis_create_and_update_persist_active_subject_pre_test_and_each_difficulty(string $action, string $difficulty): void
    {
        $payload = $this->payload('kuis', ['id_mapel' => 'MP002', 'tingkat_kesulitan' => $difficulty, 'materi' => ['M001', 'M002']]);
        $this->actingAs($this->guru);
        $response = $action === 'store'
            ? $this->post(route('kuis.store'), $payload)
            : $this->put(route('kuis.update', 'K001'), $payload);
        $response->assertRedirect(route('kuis.index'))->assertSessionHasNoErrors();
        $id = $action === 'store' ? 'K002' : 'K001';
        $kuis = Kuis::findOrFail($id);
        $this->assertSame('pre-test', $kuis->kategori);
        $this->assertSame($difficulty, $kuis->tingkat_kesulitan);
        $this->assertSame('MP002', $kuis->mapel->getKey());
        $this->assertSame('TA001', $kuis->tahunAjaran->getKey());
        $this->assertSame('U002', $kuis->pembuat->getKey());
        $this->assertSame('draft', $kuis->status_publikasi);
        $this->assertTrue($kuis->is_aktif);
        $this->assertEqualsCanonicalizing(['M001', 'M002'], $kuis->materi->modelKeys());
        $this->assertTrue(Materi::findOrFail('M002')->kuis->contains('id_kuis', $id));
        $this->assertDatabaseCount('remedial', 0);
        $this->assertDatabaseHas('soal', ['id_soal' => 'S001', 'id_kuis' => 'K001', 'bobot' => 100]);
    }

    public static function clientYears(): array
    {
        return ['omitted' => [[]], 'inactive year' => [['id_tahun_ajaran' => 'TA002']],
            'unknown year' => [['id_tahun_ajaran' => 'TA999']], 'array year' => [['id_tahun_ajaran' => ['TA002']]]];
    }

    #[DataProvider('clientYears')]
    public function test_kuis_store_uses_active_year_and_forces_draft_and_authenticated_creator(array $year): void
    {
        $this->actingAs($this->guru)->post(route('kuis.store'), $this->payload('kuis', array_merge($year, [
            'id_kuis' => 'K999', 'id_user' => 'U001', 'status_publikasi' => 'terbit', 'id_publisher' => 'U001',
            'id_kuis_asal' => 'K001', 'waktu_mulai' => '2026-10-30 09:00:00', 'waktu_selesai' => '2026-10-30 10:00:00',
        ])))->assertRedirect(route('kuis.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('kuis', [
            'id_kuis' => 'K002', 'id_tahun_ajaran' => 'TA001', 'id_user' => 'U002', 'status_publikasi' => 'draft',
            'id_publisher' => null, 'waktu_mulai' => null, 'waktu_selesai' => null,
        ]);
        $this->assertDatabaseMissing('kuis', ['id_kuis' => 'K999']);
        $this->assertDatabaseCount('remedial', 0);
    }

    public static function invalidMapel(): array
    {
        $cases = [];
        foreach (['kuis', 'materi'] as $resource) {
            foreach (['store', 'update'] as $action) {
                foreach (['missing' => [], 'null' => ['id_mapel' => null], 'nonexistent' => ['id_mapel' => 'MP999'],
                    'inactive' => ['id_mapel' => 'MP003'], 'array of active IDs' => ['id_mapel' => ['MP001']],
                    'empty array' => ['id_mapel' => []], 'numeric' => ['id_mapel' => 123]] as $name => $value) {
                    $cases[$resource.' '.$action.' '.$name] = [$resource, $action, $value];
                }
            }
        }

        return $cases;
    }

    #[DataProvider('invalidMapel')]
    public function test_invalid_new_subject_is_a_validation_redirect_without_content_or_file_mutations(string $resource, string $action, array $value): void
    {
        $payload = $this->payload($resource);
        unset($payload['id_mapel']);
        $payload = array_merge($payload, $value);
        $before = $this->snapshot();
        if ($resource === 'materi') {
            Storage::disk('public')->put('materi/original.pdf', '%PDF-1.4 original');
            Materi::findOrFail('M001')->update(['isi_materi' => 'materi/original.pdf']);
            $before = $this->snapshot();
            $payload['file_pdf'] = UploadedFile::fake()->create('replacement.pdf', 32, 'application/pdf');
        }
        $id = $resource === 'kuis' ? 'K001' : 'M001';
        $form = route($resource.'.'.($action === 'store' ? 'create' : 'edit'), $action === 'store' ? [] : [$id]);
        $this->actingAs($this->guru)->from($form);
        $response = $action === 'store'
            ? $this->post(route($resource.'.store'), $payload)
            : $this->put(route($resource.'.update', $id), $payload);
        $response->assertRedirect($form)->assertSessionHasErrors('id_mapel')->assertSessionHasInput('judul', $payload['judul']);
        $this->assertSame($before, $this->snapshot());
        if ($resource === 'materi') {
            $this->assertSame(['materi/original.pdf'], Storage::disk('public')->allFiles());
            $this->assertSame('%PDF-1.4 original', Storage::disk('public')->get('materi/original.pdf'));
        }
    }

    #[DataProvider('resources')]
    public function test_update_can_retain_current_inactive_subject_or_switch_to_active_but_cannot_choose_another_inactive(string $resource): void
    {
        $content = $this->content($resource);
        $content->update(['id_mapel' => 'MP003']);
        $this->actingAs($this->guru);
        $payload = $this->payload($resource, ['id_mapel' => 'MP003']);
        if ($resource === 'kuis') {
            $payload['materi'] = ['M001'];
        }
        $this->put(route($resource.'.update', $content->getKey()), $payload)
            ->assertRedirect(route($resource.'.index'))->assertSessionHasNoErrors();
        $this->assertSame('MP003', $content->refresh()->mapel->getKey());

        $before = $this->snapshot();
        $form = route($resource.'.edit', $content->getKey());
        $this->from($form)->put(route($resource.'.update', $content->getKey()), array_replace($payload, ['id_mapel' => 'MP004']))
            ->assertRedirect($form)->assertSessionHasErrors('id_mapel');
        $this->assertSame($before, $this->snapshot());
        $this->app['session.store']->forget('errors');

        $this->put(route($resource.'.update', $content->getKey()), array_replace($payload, ['id_mapel' => 'MP002']))
            ->assertRedirect(route($resource.'.index'))->assertSessionHasNoErrors();
        $this->assertSame('MP002', $content->refresh()->mapel->getKey());
    }

    public static function invalidKuisFields(): array
    {
        $cases = [];
        foreach (['store', 'update'] as $action) {
            foreach (['missing difficulty' => ['tingkat_kesulitan', []],
                'null difficulty' => ['tingkat_kesulitan', ['tingkat_kesulitan' => null]],
                'unknown difficulty' => ['tingkat_kesulitan', ['tingkat_kesulitan' => 'ekstrem']],
                'uppercase difficulty' => ['tingkat_kesulitan', ['tingkat_kesulitan' => 'Mudah']],
                'array difficulty' => ['tingkat_kesulitan', ['tingkat_kesulitan' => ['mudah']]],
                'obsolete pass-test' => ['kategori', ['kategori' => 'pass-test']]] as $name => [$field, $value]) {
                $cases[$action.' '.$name] = [$action, $field, $value];
            }
        }

        return $cases;
    }

    #[DataProvider('invalidKuisFields')]
    public function test_difficulty_is_required_enum_and_obsolete_category_is_rejected_on_store_and_update(string $action, string $field, array $value): void
    {
        $payload = $this->payload('kuis');
        unset($payload[$field]);
        $payload = array_merge($payload, $value);
        $before = $this->snapshot();
        $form = route($action === 'store' ? 'kuis.create' : 'kuis.edit', $action === 'store' ? [] : ['K001']);
        $this->actingAs($this->guru)->from($form);
        $response = $action === 'store'
            ? $this->post(route('kuis.store'), $payload)
            : $this->put(route('kuis.update', 'K001'), $payload);
        $response->assertRedirect($form)->assertSessionHasErrors($field);
        $this->assertSame($before, $this->snapshot());
    }

    public function test_kuis_store_without_active_year_redirects_with_validation_errors_and_old_input(): void
    {
        TahunAjaran::query()->update(['is_aktif' => false]);
        $before = $this->snapshot();
        $form = route('kuis.create');
        $this->actingAs($this->guru)->from($form)->post(route('kuis.store'), $this->payload('kuis', [
            'id_tahun_ajaran' => 'TA001', 'materi' => ['M001', 'M002'],
        ]))->assertRedirect($form)->assertSessionHasErrors()->assertSessionHasInput('judul', 'Kuis baru');
        $this->assertSame($before, $this->snapshot());
    }

    #[DataProvider('clientYears')]
    public function test_kuis_update_preserves_original_year_when_a_new_year_is_active_regardless_of_client_year(array $year): void
    {
        TahunAjaran::whereKey('TA001')->update(['is_aktif' => false]);
        TahunAjaran::whereKey('TA002')->update(['is_aktif' => true]);
        $this->assertSame('TA002', TahunAjaran::aktif()->getKey());
        $this->actingAs($this->guru)->put(route('kuis.update', 'K001'), $this->payload('kuis', array_merge($year, [
            'id_mapel' => 'MP002', 'tingkat_kesulitan' => 'sulit', 'materi' => ['M001'],
        ])))->assertRedirect(route('kuis.index'))->assertSessionHasNoErrors();
        $kuis = Kuis::findOrFail('K001');
        $this->assertSame('TA001', $kuis->tahunAjaran->getKey());
        $this->assertSame('MP002', $kuis->id_mapel);
        $this->assertSame('sulit', $kuis->tingkat_kesulitan);
        $this->assertDatabaseCount('kuis', 1);
    }

    public function test_kuis_update_does_not_need_an_active_year_and_preserves_creator_and_year(): void
    {
        TahunAjaran::query()->update(['is_aktif' => false]);
        $this->actingAs($this->guru)->put(route('kuis.update', 'K001'), $this->payload('kuis', [
            'id_tahun_ajaran' => 'TA999', 'id_user' => 'U001', 'id_kuis' => 'K999', 'status_publikasi' => 'terbit',
        ]))->assertRedirect(route('kuis.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('kuis', [
            'id_kuis' => 'K001', 'id_tahun_ajaran' => 'TA001', 'id_user' => 'U002', 'status_publikasi' => 'draft',
        ]);
        $this->assertDatabaseCount('kuis', 1);
    }

    #[DataProvider('resources')]
    public function test_generated_ids_use_maximum_existing_suffix_after_deletions_not_row_count(string $resource): void
    {
        if ($resource === 'kuis') {
            $this->makeKuis('K002')->delete();
            $this->makeKuis('K010', ['is_aktif' => false]);
        } else {
            $this->makeMateri('M003')->delete();
            $this->makeMateri('M010');
        }
        $this->actingAs($this->guru)->post(route($resource.'.store'), $this->payload($resource))
            ->assertRedirect(route($resource.'.index'))->assertSessionHasNoErrors();
        $id = $resource === 'kuis' ? 'K011' : 'M011';
        $this->assertDatabaseHas($resource, ['id_'.$resource => $id, 'id_mapel' => 'MP001', 'id_user' => 'U002']);
    }

    public function test_kuis_material_associations_sync_and_clear_without_changing_questions(): void
    {
        $beforeQuestion = (array) DB::table('soal')->where('id_soal', 'S001')->first();
        $this->actingAs($this->guru)->put(route('kuis.update', 'K001'), $this->payload('kuis', ['materi' => ['M002']]))
            ->assertRedirect(route('kuis.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('materi_kuis', ['id_kuis' => 'K001', 'id_materi' => 'M001']);
        $this->assertDatabaseHas('materi_kuis', ['id_kuis' => 'K001', 'id_materi' => 'M002']);
        $this->put(route('kuis.update', 'K001'), $this->payload('kuis'))
            ->assertRedirect(route('kuis.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('materi_kuis', 0);
        $this->assertSame($beforeQuestion, (array) DB::table('soal')->where('id_soal', 'S001')->first());
        $this->assertDatabaseCount('materi', 2);
    }

    public static function invalidMaterials(): array
    {
        return ['store missing material' => ['store', ['M999'], 'materi.0'],
            'update missing material' => ['update', ['M999'], 'materi.0'],
            'store scalar material' => ['store', 'M001', 'materi'],
            'update scalar material' => ['update', 'M001', 'materi']];
    }

    #[DataProvider('invalidMaterials')]
    public function test_invalid_material_associations_are_rejected_without_mutations(string $action, mixed $materials, string $field): void
    {
        $before = $this->snapshot();
        $this->actingAs($this->guru);
        $payload = $this->payload('kuis', ['materi' => $materials]);
        $response = $action === 'store'
            ? $this->post(route('kuis.store'), $payload)
            : $this->put(route('kuis.update', 'K001'), $payload);
        $response->assertRedirect()->assertSessionHasErrors($field);
        $this->assertSame($before, $this->snapshot());
    }

    public function test_remedial_store_links_original_quiz_not_materials_and_update_retains_that_link(): void
    {
        $payload = $this->payload('kuis', ['kategori' => 'remedial', 'id_kuis_asal' => 'K001', 'materi' => ['M001', 'M002']]);
        $this->actingAs($this->guru)->post(route('kuis.store'), $payload)
            ->assertRedirect(route('kuis.index'))->assertSessionHasNoErrors();
        $remedial = Kuis::findOrFail('K002');
        $this->assertSame('draft', $remedial->status_publikasi);
        $this->assertSame('TA001', $remedial->id_tahun_ajaran);
        $this->assertSame('MP001', $remedial->id_mapel);
        $this->assertSame('K001', $remedial->remedial->kuisAsal->getKey());
        $this->assertSame('K002', $remedial->remedial->kuisRemedial->getKey());
        $this->assertCount(0, $remedial->materi);
        $this->assertCount(1, Kuis::findOrFail('K001')->remedialSebagaiAsal);
        $beforeLink = (array) DB::table('remedial')->first();
        $this->makeKuis('K003');
        $payload['id_kuis_asal'] = 'K003';
        $payload['id_mapel'] = 'MP002';
        $payload['tingkat_kesulitan'] = 'sulit';
        $this->put(route('kuis.update', 'K002'), $payload)
            ->assertRedirect(route('kuis.index'))->assertSessionHasNoErrors();
        $this->assertSame($beforeLink, (array) DB::table('remedial')->first());
        $this->assertDatabaseMissing('materi_kuis', ['id_kuis' => 'K002']);
        $this->assertDatabaseHas('materi_kuis', ['id_kuis' => 'K001', 'id_materi' => 'M001']);
    }

    public static function invalidRemedialOrigins(): array
    {
        return ['missing' => [[]], 'nonexistent' => [['id_kuis_asal' => 'K999']]];
    }

    #[DataProvider('invalidRemedialOrigins')]
    public function test_remedial_store_still_requires_an_existing_original_quiz(array $origin): void
    {
        $before = $this->snapshot();
        $this->actingAs($this->guru)->from(route('kuis.create'))->post(route('kuis.store'),
            $this->payload('kuis', array_merge(['kategori' => 'remedial'], $origin)))
            ->assertRedirect(route('kuis.create'))->assertSessionHasErrors('id_kuis_asal');
        $this->assertSame($before, $this->snapshot());
    }

    public function test_ulangan_create_update_and_category_change_keep_schedule_rules_and_material_sync(): void
    {
        $payload = $this->payload('kuis', [
            'kategori' => 'ulangan', 'waktu_mulai' => '2026-10-30 09:00:00',
            'waktu_selesai' => '2026-10-30 10:00:00', 'materi' => ['M001', 'M002'],
        ]);
        $this->actingAs($this->guru)->post(route('kuis.store'), $payload)
            ->assertRedirect(route('kuis.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('kuis', [
            'id_kuis' => 'K002', 'kategori' => 'ulangan', 'status_publikasi' => 'draft',
            'waktu_mulai' => '2026-10-30 09:00:00', 'waktu_selesai' => '2026-10-30 10:00:00',
        ]);
        $this->assertCount(2, Kuis::findOrFail('K002')->materi);
        $payload['waktu_mulai'] = '2026-10-31 09:00:00';
        $payload['waktu_selesai'] = '2026-10-31 11:00:00';
        $this->put(route('kuis.update', 'K002'), $payload)
            ->assertRedirect(route('kuis.index'))->assertSessionHasNoErrors();
        $this->assertSame('2026-10-31 09:00:00', Kuis::findOrFail('K002')->waktu_mulai->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-31 11:00:00', Kuis::findOrFail('K002')->waktu_selesai->format('Y-m-d H:i:s'));
        $payload['kategori'] = 'pre-test';
        $this->put(route('kuis.update', 'K002'), $payload)
            ->assertRedirect(route('kuis.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('kuis', ['id_kuis' => 'K002', 'waktu_mulai' => null, 'waktu_selesai' => null]);
        $this->assertCount(2, Kuis::findOrFail('K002')->materi);
    }

    public static function invalidSchedules(): array
    {
        $cases = [];
        foreach (['store', 'update'] as $action) {
            foreach (['both missing' => [[], 'waktu_mulai'],
                'end missing' => [['waktu_mulai' => '2026-10-30 09:00:00'], 'waktu_mulai'],
                'start in past' => [['waktu_mulai' => '2026-10-28 09:00:00', 'waktu_selesai' => '2026-10-30 10:00:00'], 'waktu_mulai'],
                'equal endpoints' => [['waktu_mulai' => '2026-10-30 09:00:00', 'waktu_selesai' => '2026-10-30 09:00:00'], 'waktu_selesai'],
                'end before start' => [['waktu_mulai' => '2026-10-30 09:00:00', 'waktu_selesai' => '2026-10-30 08:00:00'], 'waktu_selesai']] as $name => [$schedule, $field]) {
                $cases[$action.' '.$name] = [$action, $schedule, $field];
            }
        }

        return $cases;
    }

    #[DataProvider('invalidSchedules')]
    public function test_invalid_ulangan_schedule_remains_a_validation_error_without_mutations(string $action, array $schedule, string $field): void
    {
        $before = $this->snapshot();
        $payload = $this->payload('kuis', array_merge(['kategori' => 'ulangan', 'materi' => ['M002']], $schedule));
        $this->actingAs($this->guru);
        $response = $action === 'store'
            ? $this->post(route('kuis.store'), $payload)
            : $this->put(route('kuis.update', 'K001'), $payload);
        $response->assertRedirect()->assertSessionHasErrors($field);
        $this->assertSame($before, $this->snapshot());
    }

    public static function forbiddenRoles(): array
    {
        return ['admin' => ['U001'], 'siswa' => ['U003']];
    }

    #[DataProvider('forbiddenRoles')]
    public function test_admin_and_siswa_cannot_read_or_mutate_teacher_content(string $id): void
    {
        $before = $this->snapshot();
        $this->actingAs(User::findOrFail($id));
        foreach (['kuis' => 'K001', 'materi' => 'M001'] as $resource => $contentId) {
            foreach (['index', 'create', 'show', 'edit'] as $action) {
                $this->get(route($resource.'.'.$action, in_array($action, ['show', 'edit']) ? [$contentId] : []))->assertForbidden();
            }
            $this->post(route($resource.'.store'), $this->payload($resource))->assertForbidden();
            $this->put(route($resource.'.update', $contentId), $this->payload($resource))->assertForbidden();
            $this->delete(route($resource.'.destroy', $contentId))->assertForbidden();
        }
        $this->patch(route('kuis.publish', 'K001'))->assertForbidden();
        $this->assertSame($before, $this->snapshot());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_published_quiz_edit_and_update_are_blocked_without_changing_rows_or_relations(): void
    {
        Kuis::findOrFail('K001')->update(['status_publikasi' => 'terbit', 'id_publisher' => 'U002']);
        $this->makeKuis('K002', ['kategori' => 'remedial']);
        DB::table('remedial')->insert(['id_kuis_remedial' => 'K002', 'id_kuis_asal' => 'K001']);
        $before = $this->snapshot();
        $origin = route('kuis.show', 'K001');
        $this->actingAs($this->guru)->from($origin)->get(route('kuis.edit', 'K001'))
            ->assertRedirect($origin)->assertSessionHasErrors('kuis');
        $this->put(route('kuis.update', 'K001'), $this->payload('kuis', [
            'id_mapel' => 'MP002', 'id_tahun_ajaran' => 'TA002', 'tingkat_kesulitan' => 'sulit', 'materi' => ['M002'],
        ]))->assertRedirect($origin)->assertSessionHasErrors('kuis');
        $this->assertSame($before, $this->snapshot());
    }

    public function test_materi_store_update_preserve_pdf_youtube_and_quiz_associations(): void
    {
        $pdf = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF\n";
        $payload = $this->payload('materi', [
            'id_mapel' => 'MP002', 'id_user' => 'U001', 'id_materi' => 'M999',
            'file_pdf' => UploadedFile::fake()->createWithContent('algoritma.pdf', $pdf),
        ]);
        $this->actingAs($this->guru)->post(route('materi.store'), $payload)
            ->assertRedirect(route('materi.index'))->assertSessionHasNoErrors();
        $materi = Materi::findOrFail('M003');
        $this->assertSame('MP002', $materi->mapel->getKey());
        $this->assertSame('U002', $materi->user->getKey());
        $this->assertSame($payload['link_youtube'], $materi->link_youtube);
        $this->assertStringStartsWith('materi/', $materi->isi_materi);
        Storage::disk('public')->assertExists($materi->isi_materi);
        $this->assertSame($pdf, Storage::disk('public')->get($materi->isi_materi));
        $this->assertSame(Storage::disk('public')->url($materi->isi_materi), $materi->pdfUrl());
        $this->get(route('materi.show', 'M003'))->assertOk()->assertViewHas('pdfUrl', $materi->pdfUrl());
        Kuis::findOrFail('K001')->materi()->attach('M003');
        $oldPath = $materi->isi_materi;
        unset($payload['file_pdf']);
        $payload['judul'] = 'Materi diperbarui';
        $payload['id_mapel'] = 'MP001';
        $payload['link_youtube'] = 'https://youtu.be/replacement123';
        $this->put(route('materi.update', 'M003'), $payload)
            ->assertRedirect(route('materi.index'))->assertSessionHasNoErrors();
        $materi->refresh();
        $this->assertSame($oldPath, $materi->isi_materi);
        $this->assertSame('MP001', $materi->id_mapel);
        $this->assertSame('U002', $materi->id_user);
        $this->assertSame($payload['link_youtube'], $materi->link_youtube);
        Storage::disk('public')->assertExists($oldPath);
        $payload['file_pdf'] = UploadedFile::fake()->createWithContent('replacement.pdf', $pdf.'% replacement');
        $this->put(route('materi.update', 'M003'), $payload)
            ->assertRedirect(route('materi.index'))->assertSessionHasNoErrors();
        $materi->refresh();
        $this->assertNotSame($oldPath, $materi->isi_materi);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($materi->isi_materi);
        $this->assertSame($pdf.'% replacement', Storage::disk('public')->get($materi->isi_materi));
        $this->assertDatabaseHas('materi_kuis', ['id_kuis' => 'K001', 'id_materi' => 'M003']);
        $this->assertDatabaseMissing('materi', ['id_materi' => 'M999']);
    }

    public function test_materi_can_be_created_with_youtube_without_pdf_or_active_year(): void
    {
        TahunAjaran::query()->update(['is_aktif' => false]);
        $this->actingAs($this->guru)->post(route('materi.store'), $this->payload('materi'))
            ->assertRedirect(route('materi.index'))->assertSessionHasNoErrors();
        $materi = Materi::findOrFail('M003');
        $this->assertNull($materi->isi_materi);
        $this->assertNull($materi->pdfUrl());
        $this->assertSame($this->payload('materi')['link_youtube'], $materi->link_youtube);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public static function invalidMedia(): array
    {
        $cases = [];
        foreach (['store', 'update'] as $action) {
            foreach (['non-PDF', 'oversized PDF', 'invalid URL'] as $case) {
                $cases[$action.' '.$case] = [$action, $case];
            }
        }

        return $cases;
    }

    #[DataProvider('invalidMedia')]
    public function test_invalid_pdf_and_youtube_are_rejected_before_upload_deletion_or_row_changes(string $action, string $case): void
    {
        Storage::disk('public')->put('materi/original.pdf', '%PDF-1.4 original');
        Materi::findOrFail('M001')->update(['isi_materi' => 'materi/original.pdf']);
        $before = $this->snapshot();
        $payload = $this->payload('materi');
        $field = 'file_pdf';
        if ($case === 'non-PDF') {
            $payload['file_pdf'] = UploadedFile::fake()->create('not-pdf.txt', 32, 'text/plain');
        } elseif ($case === 'oversized PDF') {
            $payload['file_pdf'] = UploadedFile::fake()->create('oversized.pdf', 10241, 'application/pdf');
        } else {
            $payload['link_youtube'] = 'not-a-url';
            $payload['file_pdf'] = UploadedFile::fake()->create('replacement.pdf', 32, 'application/pdf');
            $field = 'link_youtube';
        }
        $this->actingAs($this->guru);
        $response = $action === 'store'
            ? $this->post(route('materi.store'), $payload)
            : $this->put(route('materi.update', 'M001'), $payload);
        $response->assertRedirect()->assertSessionHasErrors($field);
        $this->assertSame($before, $this->snapshot());
        $this->assertSame(['materi/original.pdf'], Storage::disk('public')->allFiles());
        $this->assertSame('%PDF-1.4 original', Storage::disk('public')->get('materi/original.pdf'));
    }

    public function test_duplicate_materi_store_and_update_do_not_replace_pdf_or_modify_content(): void
    {
        Storage::disk('public')->put('materi/original.pdf', '%PDF-1.4 original');
        $original = Materi::findOrFail('M001');
        $original->update(['isi_materi' => 'materi/original.pdf']);
        $before = $this->snapshot();
        $payload = $this->payload('materi', [
            'judul' => $original->judul, 'bab' => $original->bab, 'kelas' => $original->kelas,
            'file_pdf' => UploadedFile::fake()->create('replacement.pdf', 32, 'application/pdf'),
        ]);
        $this->actingAs($this->guru)->from(route('materi.create'))->post(route('materi.store'), $payload)
            ->assertRedirect(route('materi.create'))->assertSessionHas('error');
        $this->from(route('materi.edit', 'M002'))->put(route('materi.update', 'M002'), $payload)
            ->assertRedirect(route('materi.edit', 'M002'))->assertSessionHas('error');
        $this->assertSame($before, $this->snapshot());
        $this->assertSame(['materi/original.pdf'], Storage::disk('public')->allFiles());
    }
}
