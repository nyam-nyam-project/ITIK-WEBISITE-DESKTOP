<?php

namespace Tests\Feature;

use App\Models\User;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class RevealTest extends TestCase
{
    private bool $isolatedDatabase = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame('testing', $this->app->environment());
        $this->assertSame('testing', config('app.env'));
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame('sqlite', config('database.connections.sqlite.driver'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertEmpty(config('database.connections.sqlite.url'));
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->assertSame(':memory:', DB::connection()->getConfig('database'));
        $this->assertSame('array', config('cache.default'));
        $this->assertSame('array', config('session.driver'));
        $this->isolatedDatabase = true;
        $this->withoutVite();
    }

    protected function tearDown(): void
    {
        try {
            if ($this->isolatedDatabase) {
                DB::purge('sqlite');
            }
        } finally {
            parent::tearDown();
        }
    }

    /** @return array<string, array{string}> */
    public static function approvedViews(): array
    {
        $views = [
            'admin/dashboard', 'guru/dashboard', 'siswa/dashboard',
            'admin/admin/index', 'admin/guru/index', 'admin/siswa/index',
            'admin/kelas/index', 'admin/mapel/index', 'admin/tahun-ajaran/index',
            'admin/pengajaran/index', 'admin/rekap/index',
            'guru/materi/index', 'guru/kuis/index', 'guru/soal/index',
            'guru/nilai/index', 'guru/remedial/index', 'guru/arsip/index',
            'siswa/materi/index', 'siswa/kuis/index', 'siswa/nilai/index',
            'siswa/remedial/_tabel',
        ];

        return array_combine($views, array_map(static fn (string $view): array => [$view], $views));
    }

    #[DataProvider('approvedViews')]
    public function test_view_disetujui_memiliki_target_reveal_tanpa_kode_inline_atau_status_tersembunyi(string $view): void
    {
        $path = resource_path('views/'.$view.'.blade.php');
        $source = File::get($path);

        $this->assertMatchesRegularExpression('/<(?:a|article|div|section|tr)\b[^>]*\sdata-reveal(?:\s|=|>)/i', $source, $path);
        $this->assertDoesNotMatchRegularExpression('/<style\b|\s(?:style|on[a-z]+)\s*=/i', $source, $path);
        preg_match_all('/<script\b([^>]*)>([\s\S]*?)<\/script>/i', $source, $scripts, PREG_SET_ORDER);
        foreach ($scripts as $script) {
            $this->assertMatchesRegularExpression('/\bsrc\s*=/', $script[1], $path);
            $this->assertSame('', trim($script[2]), $path);
        }

        preg_match_all('/<[a-z][a-z0-9:-]*\b[^>]*\sdata-reveal(?=\s|=|>)[^>]*>/i', $source, $targets);
        foreach ($targets[0] as $target) {
            $this->assertDoesNotMatchRegularExpression('/\b(?:reveal-pending|hidden|invisible|opacity-0)\b|\s(?:aria-hidden|inert)(?=\s|=|>)/i', $target, $path);
        }
    }

    /** @return array<string, array{string, string, string, string, string}> */
    public static function dashboards(): array
    {
        return [
            'admin' => ['admin', 'admin.dashboard', 'kelasTahunAktif', 'XI TKJ Reveal', 'Siswa aktif'],
            'guru' => ['guru', 'dashboard', 'daftarSiswa', 'Siswa Reveal 001', 'Siswa aktif'],
            'siswa' => ['siswa', 'siswa.dashboard', 'materiTerbaru', 'Materi Reveal', 'Materi Tersedia'],
        ];
    }

    #[DataProvider('dashboards')]
    public function test_dashboard_terautentikasi_merender_statistik_dan_baris_data_reveal(string $role, string $route, string $data, string $text, string $stat): void
    {
        $users = $this->fixtures();

        $response = $this->actingAs($users[$role])->get(route($route));

        $response->assertOk();
        $this->assertNotEmpty($response->viewData($data));
        $xpath = $this->assertRevealAssets($response);
        $this->assertRevealText($xpath, 'tr', $text);
        $this->assertRevealText($xpath, '*[contains(concat(" ", normalize-space(@class), " "), " stat ")]', $stat);
    }

    /** @return array<string, array{string, string, array<string, mixed>, string, string, string, string}> */
    public static function lists(): array
    {
        return [
            'admin admin' => ['admin', 'admin.admin.index', [], 'admin.admin.index', 'admins', 'tr', 'Admin Reveal'],
            'admin guru' => ['admin', 'admin.guru.index', [], 'admin.guru.index', 'guru', 'tr', 'Guru Reveal'],
            'admin siswa' => ['admin', 'admin.siswa.index', [], 'admin.siswa.index', 'siswa', 'tr', 'Siswa Reveal 001'],
            'admin kelas' => ['admin', 'admin.kelas.index', [], 'admin.kelas.index', 'kelas', 'tr', 'XI TKJ Reveal'],
            'admin mapel' => ['admin', 'admin.mapel.index', [], 'admin.mapel.index', 'mapel', 'tr', 'TIK Reveal'],
            'admin tahun ajaran' => ['admin', 'admin.tahun-ajaran.index', [], 'admin.tahun-ajaran.index', 'tahunAjaran', 'tr', '2026/2027 Ganjil'],
            'admin pengajaran' => ['admin', 'admin.pengajaran.index', [], 'admin.pengajaran.index', 'pengajaran', 'tr', 'Guru Reveal'],
            'admin rekap' => ['admin', 'admin.rekap.index', ['mapel' => 'MP_REVEAL', 'kuis' => 'K_REVEAL'], 'admin.rekap.index', 'rekap', 'tr', 'Siswa Reveal 001'],
            'guru materi' => ['guru', 'materi.index', ['mapel' => 'MP_REVEAL'], 'guru.materi.index', 'materi', 'div', 'Materi Reveal'],
            'guru kuis' => ['guru', 'kuis.index', ['mapel' => 'MP_REVEAL'], 'guru.kuis.index', 'kuis', 'tr', 'Ulangan Reveal'],
            'guru soal' => ['guru', 'kuis.soal.index', ['kuis' => 'K_REVEAL'], 'guru.soal.index', 'soal', 'div', 'Pertanyaan Reveal'],
            'guru nilai' => ['guru', 'guru.nilai.index', ['kelas' => 'KLS_REVEAL'], 'guru.nilai.index', 'hasil', 'tr', 'Siswa Reveal 001'],
            'guru arsip kuis' => ['guru', 'guru.arsip.index', [], 'guru.arsip.index', 'kuisArsip', 'tr', 'Kuis Arsip Reveal'],
            'guru arsip materi' => ['guru', 'guru.arsip.index', ['tab' => 'materi'], 'guru.arsip.index', 'materiArsip', 'tr', 'Materi Arsip Reveal'],
            'siswa materi' => ['siswa', 'siswa.materi.index', ['mapel' => 'MP_REVEAL'], 'siswa.materi.index', 'materi', 'div', 'Materi Reveal'],
            'siswa kuis' => ['siswa', 'siswa.kuis.index', ['mapel' => 'MP_REVEAL', 'materi' => 'MAT_REVEAL'], 'siswa.kuis.index', 'kuis', 'div', 'Kuis Reveal Baru'],
            'siswa nilai' => ['siswa', 'siswa.nilai.index', ['mapel' => 'MP_REVEAL', 'materi' => 'MAT_REVEAL'], 'siswa.nilai.index', 'hasil', 'tr', 'Ulangan Reveal'],
            'siswa remedial partial' => ['siswa', 'siswa.remedial.index', [], 'siswa.remedial.index', 'tugas', 'tr', 'Remedial Reveal'],
            'guru kartu mapel materi' => ['guru', 'materi.index', [], 'guru.materi.index', 'mapelAktif', 'a', 'TIK Reveal'],
            'guru kartu mapel kuis' => ['guru', 'kuis.index', [], 'guru.kuis.index', 'mapelAktif', 'a', 'TIK Reveal'],
            'guru kartu kelas nilai' => ['guru', 'guru.nilai.index', [], 'guru.nilai.index', 'kelasList', 'a', 'XI TKJ Reveal'],
            'siswa kartu mapel materi' => ['siswa', 'siswa.materi.index', [], 'siswa.materi.index', 'mapelAktif', 'a', 'TIK Reveal'],
            'siswa kartu mapel kuis' => ['siswa', 'siswa.kuis.index', [], 'siswa.kuis.index', 'mapelAktif', 'a', 'TIK Reveal'],
            'siswa kartu mapel nilai' => ['siswa', 'siswa.nilai.index', [], 'siswa.nilai.index', 'mapelAktif', 'a', 'TIK Reveal'],
            'siswa kartu bab kuis' => ['siswa', 'siswa.kuis.index', ['mapel' => 'MP_REVEAL'], 'siswa.kuis.index', 'daftarMateri', 'a', 'Materi Reveal'],
            'siswa kartu bab nilai' => ['siswa', 'siswa.nilai.index', ['mapel' => 'MP_REVEAL'], 'siswa.nilai.index', 'daftarMateri', 'a', 'Materi Reveal'],
        ];
    }

    #[DataProvider('lists')]
    public function test_daftar_terautentikasi_merender_record_nonkosong_pada_target_reveal(string $role, string $route, array $query, string $view, string $data, string $tag, string $text): void
    {
        $users = $this->fixtures();

        $response = $this->actingAs($users[$role])->get(route($route, $query));

        $response->assertOk()->assertViewIs($view);
        $this->assertNotEmpty($response->viewData($data));
        $this->assertRevealText($this->assertRevealAssets($response), $tag, $text);
    }

    public function test_status_ketersediaan_tingkat_kuis_tetap_memiliki_teks_untuk_pembaca_layar(): void
    {
        $users = $this->fixtures();

        $guru = $this->actingAs($users['guru'])->get(route('kuis.index', ['mapel' => 'MP_REVEAL']));
        $siswa = $this->actingAs($users['siswa'])->get(route('siswa.kuis.index', ['mapel' => 'MP_REVEAL']));

        foreach ([$guru, $siswa] as $response) {
            $response->assertOk();
            $xpath = $this->xpath($response->getContent());
            $this->assertGreaterThan(0, $xpath->query('//span[contains(normalize-space(.), "Mudah")]/span[@class="sr-only" and normalize-space(.)="tersedia"]')->length);
            $this->assertGreaterThan(0, $xpath->query('//span[contains(normalize-space(.), "Sedang")]/span[@class="sr-only" and normalize-space(.)="belum tersedia"]')->length);
            $this->assertGreaterThan(0, $xpath->query('//span[contains(normalize-space(.), "Sulit")]/span[@class="sr-only" and normalize-space(.)="belum tersedia"]')->length);
        }
    }

    // The API shares this route name, so request the web URI explicitly.
    public function test_daftar_remedial_guru_web_merender_record_pada_baris_reveal(): void
    {
        $users = $this->fixtures();

        $response = $this->actingAs($users['guru'])->get('/guru/remedial');

        $response->assertOk()->assertViewIs('guru.remedial.index');
        $this->assertNotEmpty($response->viewData('remedials'));
        $this->assertRevealText($this->assertRevealAssets($response), 'tr', 'Remedial Reveal');
    }

    public function test_tabel_panjang_per_page_50_tetap_merender_semua_record_dan_paginasi_tanpa_mengubah_data(): void
    {
        $users = $this->fixtures(63);
        $before = DB::table('tm_user')->orderBy('id_user')->get()->map(static fn (object $row): array => (array) $row)->all();

        $first = $this->actingAs($users['admin'])->get(route('admin.siswa.index', ['per_page' => 50]));
        $second = $this->get(route('admin.siswa.index', ['per_page' => 50, 'page' => 2]));

        $first->assertOk()->assertSee('menampilkan 1 sampai 50 dari 63 data', false);
        $second->assertOk()->assertSee('menampilkan 51 sampai 63 dari 63 data', false);
        $this->assertSame(50, $first->viewData('siswa')->perPage());
        $this->assertSame(63, $first->viewData('siswa')->total());
        $this->assertCount(50, $first->viewData('siswa'));
        $this->assertCount(13, $second->viewData('siswa'));
        $this->assertCount(50, $this->assertRevealAssets($first)->query('//tbody/tr[@data-reveal]'));
        $this->assertCount(13, $this->assertRevealAssets($second)->query('//tbody/tr[@data-reveal]'));
        $this->assertSame($this->studentIds(1, 63), array_merge(
            $first->viewData('siswa')->pluck('id_user')->all(),
            $second->viewData('siswa')->pluck('id_user')->all()
        ));
        foreach ([$first, $second] as $response) {
            $xpath = $this->xpath($response->getContent());
            foreach ($response->viewData('siswa') as $student) {
                $this->assertRevealText($xpath, 'tr', $student->nama_lengkap);
            }
            $this->assertSame(1, $xpath->query('//nav[@aria-label="Navigasi halaman"]')->length);
            $this->assertSame(1, $xpath->query('//nav[@aria-label="Navigasi halaman"]//*[@aria-current="page"]')->length);
        }
        $this->assertSame($before, DB::table('tm_user')->orderBy('id_user')->get()->map(static fn (object $row): array => (array) $row)->all());
    }

    public function test_filter_dan_tautan_halaman_kedua_tetap_dipertahankan_pada_baris_reveal(): void
    {
        $users = $this->fixtures(63);
        User::create([
            'id_user' => 'S_LUAR_REVEAL', 'nomor_induk' => '999999',
            'nama_lengkap' => 'Siswa Reveal Luar Kelas', 'role' => 'siswa',
            'status' => 'aktif', 'password' => Hash::make('password123'),
            'wajib_ganti_password' => false,
        ]);
        $query = [
            'tahun_ajaran' => 'TA_REVEAL', 'kelas' => 'KLS_REVEAL',
            'status' => 'aktif', 'cari' => 'Siswa Reveal', 'per_page' => 50, 'page' => 2,
        ];
        $before = DB::table('tm_user')->orderBy('id_user')->get()->map(static fn (object $row): array => (array) $row)->all();

        $response = $this->actingAs($users['admin'])->get(route('admin.siswa.index', $query));

        $response->assertOk()->assertSee('menampilkan 51 sampai 58 dari 58 data', false);
        $paginator = $response->viewData('siswa');
        $this->assertSame(58, $paginator->total());
        $this->assertSame($this->studentIds(51, 58), $paginator->pluck('id_user')->all());
        $xpath = $this->assertRevealAssets($response);
        $this->assertCount(8, $xpath->query('//tbody/tr[@data-reveal]'));
        foreach ($paginator as $student) {
            $this->assertSame('aktif', $student->status);
            $this->assertRevealText($xpath, 'tr', $student->nama_lengkap);
        }
        $links = $xpath->query('//nav[@aria-label="Navigasi halaman"]//a[@href]');
        $this->assertGreaterThan(0, $links->length);
        foreach ($links as $link) {
            parse_str(parse_url($link->getAttribute('href'), PHP_URL_QUERY) ?? '', $parameters);
            foreach (array_diff_key($query, ['page' => true]) as $key => $value) {
                $this->assertSame((string) $value, $parameters[$key] ?? null, $key);
            }
        }
        $this->assertSame($before, DB::table('tm_user')->orderBy('id_user')->get()->map(static fn (object $row): array => (array) $row)->all());
    }

    public function test_root_form_detail_login_dan_cbt_tidak_menjadi_target_reveal(): void
    {
        $paths = [resource_path('views/layouts/app.blade.php'), resource_path('views/partials/styles.blade.php')];
        foreach (File::allFiles(resource_path('views')) as $file) {
            if (preg_match('/(?:^|[\\\\\/])(?:create|edit|show|_form|login|admin-login|wajib-ganti-password|kerjakan|hasil)\.blade\.php$/', $file->getPathname())) {
                $paths[] = $file->getPathname();
            }
        }

        $this->assertGreaterThan(20, count($paths));
        foreach ($paths as $path) {
            $source = File::get($path);
            $this->assertDoesNotMatchRegularExpression('/\sdata-reveal(?:\s|=|>)/i', $source, $path);
            $this->assertStringNotContainsString('reveal-pending', $source, $path);
        }
        foreach (['auth/login', 'auth/admin-login', 'auth/wajib-ganti-password', 'siswa/kuis/kerjakan'] as $view) {
            $source = File::get(resource_path('views/'.$view.'.blade.php'));
            $this->assertDoesNotMatchRegularExpression('/(?:js|css)\/reveal(?:-init)?\.(?:js|css)/', $source, $view);
        }
    }

    public function test_css_reveal_hanya_menyembunyikan_target_js_dengan_durasi_dan_delay_terbatas(): void
    {
        $css = preg_replace('/\/\*[\s\S]*?\*\//', '', File::get(public_path('css/reveal.css')));

        $this->assertMatchesRegularExpression('/prefers-reduced-motion\s*:\s*reduce/', $css);
        $this->assertMatchesRegularExpression('/prefers-reduced-motion\s*:\s*no-preference/', $css);
        $this->assertMatchesRegularExpression('/transition\s*:[^;]*opacity[^;]*(?:320ms|0?\.32s)[^;]*(?:transform|translate)/', $css);
        $this->assertMatchesRegularExpression('/(?:transform\s*:\s*translate|translate\s*:)/', $css);
        $this->assertDoesNotMatchRegularExpression('/visibility\s*:\s*hidden|display\s*:\s*none|pointer-events\s*:\s*none/', $css);
        preg_match_all('/([^{}]+)\{([^{}]*)\}/', $css, $rules, PREG_SET_ORDER);
        foreach ($rules as $rule) {
            if (preg_match('/opacity\s*:\s*0(?:\s|;|$)/', $rule[2])) {
                foreach (explode(',', $rule[1]) as $selector) {
                    $this->assertMatchesRegularExpression('/(?:html)?\.js\b/', $selector);
                    $this->assertStringContainsString('[data-reveal]', $selector);
                    $this->assertStringContainsString('.reveal-pending', $selector);
                }
            }
            preg_match_all('/(transition(?:-duration|-delay)?)\s*:\s*([^;]+)/', $rule[2], $transitions, PREG_SET_ORDER);
            foreach ($transitions as $transition) {
                foreach (explode(',', $transition[2]) as $property) {
                    preg_match_all('/([\d.]+)(ms|s)\b/', $property, $times, PREG_SET_ORDER);
                    foreach ($times as $index => $time) {
                        $milliseconds = (float) $time[1] * ($time[2] === 's' ? 1000 : 1);
                        $isDelay = $transition[1] === 'transition-delay' || ($transition[1] === 'transition' && $index > 0);
                        $this->assertLessThanOrEqual($isDelay ? 120 : 320, $milliseconds);
                    }
                }
            }
        }
        $this->assertMatchesRegularExpression('/@media\s*\(\s*prefers-reduced-motion\s*:\s*reduce\s*\)\s*\{[\s\S]*opacity\s*:\s*1\s*;[\s\S]*transition\s*:\s*none/', $css);
    }

    public function test_javascript_reveal_fail_open_reduced_motion_dan_unobserve_tanpa_bergantung_format_sumber(): void
    {
        $script = <<<'JS'
const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const init = fs.readFileSync(process.argv[1], 'utf8');
const source = fs.readFileSync(process.argv[2], 'utf8');
function boot(options = {}) {
    const classes = () => {
        const values = new Set();
        return { add: (...names) => names.forEach(name => values.add(name)),
            remove: (...names) => names.forEach(name => values.delete(name)),
            contains: name => values.has(name) };
    };
    const elements = Array.from({ length: 6 }, () => ({ classList: classes(), dataset: {},
        contains: () => false, removeAttribute(name) { if (name === 'data-reveal-step') delete this.dataset.revealStep; } }));
    const root = { classList: classes() };
    const listeners = {};
    const timers = new Map();
    let timerId = 0;
    const motion = { matches: !!options.reduced,
        addEventListener: (name, callback) => { listeners['motion:' + name] = callback; },
        removeEventListener: name => { delete listeners['motion:' + name]; } };
    let observer;
    class Observer {
        constructor(callback) {
            if (options.constructorError) throw new Error('Observer unavailable');
            this.callback = callback;
            this.observed = new Set();
            this.unobserved = new Set();
            observer = this;
        }
        observe(element) {
            if (options.observeError) throw new Error('Cannot observe');
            this.observed.add(element);
        }
        unobserve(element) { this.observed.delete(element); this.unobserved.add(element); }
        disconnect() { this.observed.clear(); }
    }
    const window = { matchMedia: () => motion,
        setTimeout: (callback, delay) => { timers.set(++timerId, { callback, delay }); return timerId; },
        clearTimeout: id => timers.delete(id), addEventListener: () => {} };
    if (!options.noObserver) window.IntersectionObserver = Observer;
    const document = { documentElement: root, activeElement: null,
        querySelectorAll: () => elements, getElementById: () => null,
        addEventListener: (name, callback) => { listeners[name] = callback; },
        removeEventListener: name => { delete listeners[name]; } };
    const context = vm.createContext({ window, document, IntersectionObserver: Observer, Set, Array, Math });
    if (!options.noInitializer) vm.runInContext(init, context);
    vm.runInContext(source, context);
    const visible = element => !element.classList.contains('reveal-pending') || element.classList.contains('reveal-visible');
    return { elements, root, observer, listeners, motion, timers, visible };
}
for (const options of [{ noObserver: true }, { reduced: true }, { constructorError: true }, { observeError: true }, { noInitializer: true }]) {
    const state = boot(options);
    assert.ok(state.elements.every(state.visible), JSON.stringify(options) + ': content must fail open');
    assert.equal(state.observer?.observed.size ?? 0, 0);
}
const state = boot();
assert.ok(state.root.classList.contains('js'), 'initializer adds html.js');
assert.equal(state.observer.observed.size, 6);
assert.ok(state.elements.every(element => !state.visible(element)), 'only successful observer setup may hide targets');
state.observer.callback(state.elements.map((target, index) => ({ target, isIntersecting: index !== 5, boundingClientRect: { top: index * 100 } })));
assert.ok(state.elements.slice(0, 5).every(state.visible));
assert.ok(state.elements.slice(0, 5).every(element => state.observer.unobserved.has(element)), 'revealed targets must be unobserved');
assert.equal(state.observer.observed.size, 1, 'offscreen target stays observed');
for (const timer of [...state.timers.values()]) timer.callback();
assert.ok(state.elements.slice(0, 5).every(element => !element.classList.contains('reveal-pending')));
assert.ok(state.elements.slice(0, 5).every(element => element.dataset.revealStep === undefined));
state.motion.matches = true;
state.listeners['motion:change']({ matches: true });
assert.ok(state.elements.every(state.visible), 'enabling reduced motion reveals remaining content');
assert.equal(state.observer.observed.size, 0);
const failedCallback = boot();
failedCallback.observer.callback([{ get isIntersecting() { throw new Error('Observer callback failed'); } }]);
assert.ok(failedCallback.elements.every(failedCallback.visible), 'callback failure must reveal all content');
console.log('reveal behavior passed');
JS;
        $process = new Process(['node', '-e', $script, public_path('js/reveal-init.js'), public_path('js/reveal.js')], base_path());
        $process->setTimeout(15);

        $process->run();

        $this->assertSame(0, $process->getExitCode(), $process->getErrorOutput().$process->getOutput());
        $this->assertStringContainsString('reveal behavior passed', $process->getOutput());
    }

    private function xpath(string $html): DOMXPath
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8">'.$html);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        return new DOMXPath($document);
    }

    private function assertRevealAssets(TestResponse $response): DOMXPath
    {
        $xpath = $this->xpath($response->getContent());
        $initializers = $xpath->query('//head/script[contains(@src, "/js/reveal-init.js")]');
        $this->assertCount(1, $initializers);
        $initializer = $initializers->item(0);
        $this->assertFalse($initializer->hasAttribute('defer'));
        $this->assertFalse($initializer->hasAttribute('async'));
        $this->assertCount(0, $xpath->query('preceding-sibling::link[@rel="stylesheet"] | preceding-sibling::style', $initializer));
        $this->assertCount(1, $xpath->query('//head/link[@rel="stylesheet" and contains(@href, "/css/reveal.css")]'));
        $scripts = $xpath->query('//script[contains(@src, "/js/reveal.js")]');
        $this->assertCount(1, $scripts);
        $this->assertTrue($scripts->item(0)->hasAttribute('defer'));
        $this->assertSame('', trim($scripts->item(0)->textContent));
        $this->assertCount(0, $xpath->query('/html[@data-reveal] | //body[@data-reveal] | //main[@data-reveal] | //*[@id="main-content" and @data-reveal]'));
        foreach ($xpath->query('//*[@data-reveal]') as $target) {
            $this->assertInstanceOf(DOMElement::class, $target);
            foreach (['hidden', 'aria-hidden', 'inert', 'style'] as $attribute) {
                $this->assertFalse($target->hasAttribute($attribute), $target->tagName.' '.$attribute);
            }
            $this->assertDoesNotMatchRegularExpression('/\b(?:reveal-pending|hidden|invisible|opacity-0)\b/', $target->getAttribute('class'));
        }

        return $xpath;
    }

    private function assertRevealText(DOMXPath $xpath, string $tag, string $text): void
    {
        $targets = $xpath->query('//*[@id="main-content"]//'.$tag.'[@data-reveal][contains(normalize-space(.), "'.$text.'")]');
        $this->assertGreaterThan(0, $targets->length, 'Missing reveal target '.$tag.' containing '.$text);
    }

    /** @return list<string> */
    private function studentIds(int $first, int $last): array
    {
        return array_map(static fn (int $index): string => 'S_REVEAL_'.str_pad((string) $index, 3, '0', STR_PAD_LEFT), range($first, $last));
    }

    /** @return array{admin: User, guru: User, siswa: User} */
    private function fixtures(int $studentCount = 1): array
    {
        $this->createSchema();
        $password = Hash::make('password123');
        DB::table('tm_user')->insert([
            ['id_user' => 'ADM_REVEAL', 'nomor_induk' => 'admin_reveal', 'nama_lengkap' => 'Admin Reveal', 'role' => 'admin', 'password' => $password],
            ['id_user' => 'G_REVEAL', 'nomor_induk' => '198001', 'nama_lengkap' => 'Guru Reveal', 'role' => 'guru', 'password' => $password],
        ]);
        $students = [];
        $memberships = [];
        foreach ($this->studentIds(1, $studentCount) as $index => $id) {
            $students[] = [
                'id_user' => $id, 'nomor_induk' => '2026'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'nama_lengkap' => 'Siswa Reveal '.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'role' => 'siswa', 'status' => $index < 58 ? 'aktif' : 'nonaktif', 'password' => $password,
            ];
            $memberships[] = ['id_kelas' => 'KLS_REVEAL', 'id_user' => $id];
        }
        DB::table('tm_user')->insert($students);
        DB::table('tm_tahun_ajaran')->insert(['id_tahun_ajaran' => 'TA_REVEAL', 'nama' => '2026/2027 Ganjil', 'is_aktif' => true]);
        DB::table('tm_kelas')->insert(['id_kelas' => 'KLS_REVEAL', 'nama_kelas' => 'XI TKJ Reveal', 'tingkat' => '11', 'id_tahun_ajaran' => 'TA_REVEAL']);
        DB::table('tm_mapel')->insert(['id_mapel' => 'MP_REVEAL', 'kode_mapel' => 'TIK', 'nama_mapel' => 'TIK Reveal']);
        DB::table('tp_anggota_kelas')->insert($memberships);
        DB::table('tp_mapel_kelas')->insert(['id_kelas' => 'KLS_REVEAL', 'id_mapel' => 'MP_REVEAL']);
        DB::table('tr_pengajaran')->insert(['id_pengajaran' => 'PJ_REVEAL', 'id_user' => 'G_REVEAL', 'id_kelas' => 'KLS_REVEAL', 'id_mapel' => 'MP_REVEAL', 'id_tahun_ajaran' => 'TA_REVEAL']);
        DB::table('tm_materi')->insert([
            ['id_materi' => 'MAT_REVEAL', 'id_user' => 'G_REVEAL', 'id_mapel' => 'MP_REVEAL', 'judul' => 'Materi Reveal', 'bab' => 'Bab 1', 'is_aktif' => true],
            ['id_materi' => 'MAT_ARSIP_REVEAL', 'id_user' => 'G_REVEAL', 'id_mapel' => 'MP_REVEAL', 'judul' => 'Materi Arsip Reveal', 'bab' => 'Bab 2', 'is_aktif' => false],
        ]);
        foreach ([
            ['K_REVEAL', 'Ulangan Reveal', 'ulangan', true],
            ['K_BARU_REVEAL', 'Kuis Reveal Baru', 'kuis harian', true],
            ['K_REM_REVEAL', 'Remedial Reveal', 'remedial', true],
            ['K_ARSIP_REVEAL', 'Kuis Arsip Reveal', 'kuis harian', false],
        ] as [$id, $title, $category, $active]) {
            DB::table('tm_kuis')->insert([
                'id_kuis' => $id, 'judul' => $title, 'kategori' => $category,
                'id_user' => 'G_REVEAL', 'id_mapel' => 'MP_REVEAL', 'id_tahun_ajaran' => 'TA_REVEAL',
                'status_publikasi' => 'terbit', 'is_aktif' => $active, 'kkm' => 75,
                'created_at' => '2026-11-25 08:00:00',
            ]);
            DB::table('tp_kuis_kelas')->insert(['id_kuis' => $id, 'id_kelas' => 'KLS_REVEAL']);
        }
        DB::table('tp_materi_kuis')->insert([
            ['id_materi' => 'MAT_REVEAL', 'id_kuis' => 'K_REVEAL'],
            ['id_materi' => 'MAT_REVEAL', 'id_kuis' => 'K_BARU_REVEAL'],
        ]);
        DB::table('tm_soal')->insert([
            'id_soal' => 'Q_REVEAL', 'id_kuis' => 'K_REVEAL', 'pertanyaan' => 'Pertanyaan Reveal',
            'opsi_a' => 'Jawaban A', 'opsi_b' => 'Jawaban B', 'opsi_c' => 'Jawaban C', 'opsi_d' => 'Jawaban D', 'jawaban' => 'A', 'bobot' => 100,
        ]);
        DB::table('tr_mengerjakan')->insert([
            'id_mengerjakan' => 1, 'id_kuis' => 'K_REVEAL', 'id_user' => 'S_REVEAL_001',
            'nilai' => 60, 'status_pengerjaan' => 'final', 'status_validasi' => 'menunggu validasi',
            'tanggal' => '2026-11-25',
        ]);
        DB::table('tr_remedial')->insert(['id_remedial' => 1, 'id_kuis_asal' => 'K_REVEAL', 'id_kuis_remedial' => 'K_REM_REVEAL']);
        DB::table('tr_remedial_siswa')->insert([
            'id_remedial_siswa' => 1, 'id_mengerjakan_asal' => 1, 'id_kuis_remedial' => 'K_REM_REVEAL',
            'id_guru' => 'G_REVEAL', 'tanggal_ditugaskan' => '2026-11-25', 'status' => 'ditugaskan',
        ]);

        return [
            'admin' => User::findOrFail('ADM_REVEAL'),
            'guru' => User::findOrFail('G_REVEAL'),
            'siswa' => User::findOrFail('S_REVEAL_001'),
        ];
    }

    private function createSchema(): void
    {
        $this->assertTrue($this->isolatedDatabase, 'Database guards must pass before creating any table.');
        $this->assertSame('testing', $this->app->environment());
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::create('tm_user', function (Blueprint $table): void {
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
        });
        Schema::create('tm_tahun_ajaran', function (Blueprint $table): void {
            $table->string('id_tahun_ajaran', 50)->primary();
            $table->string('nama', 20);
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('is_aktif')->default(false);
            $table->timestamps();
        });
        Schema::create('tm_kelas', function (Blueprint $table): void {
            $table->string('id_kelas', 50)->primary();
            $table->string('nama_kelas', 50);
            $table->string('tingkat', 10);
            $table->string('id_tahun_ajaran', 50);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });
        Schema::create('tm_mapel', function (Blueprint $table): void {
            $table->string('id_mapel', 50)->primary();
            $table->string('kode_mapel', 20);
            $table->string('nama_mapel', 100);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });
        Schema::create('tp_anggota_kelas', function (Blueprint $table): void {
            $table->string('id_kelas', 50);
            $table->string('id_user', 50);
        });
        Schema::create('tp_mapel_kelas', function (Blueprint $table): void {
            $table->string('id_mapel', 50);
            $table->string('id_kelas', 50);
        });
        Schema::create('tr_pengajaran', function (Blueprint $table): void {
            $table->string('id_pengajaran', 50)->primary();
            $table->string('id_user', 50);
            $table->string('id_kelas', 50)->nullable();
            $table->string('id_mapel', 50);
            $table->string('id_tahun_ajaran', 50)->nullable();
            $table->timestamps();
        });
        Schema::create('tm_materi', function (Blueprint $table): void {
            $table->string('id_materi', 50)->primary();
            $table->string('id_user', 50);
            $table->string('id_mapel', 50);
            $table->string('judul', 150);
            $table->string('bab', 100)->nullable();
            $table->string('kelas', 50)->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });
        Schema::create('tm_kuis', function (Blueprint $table): void {
            $table->string('id_kuis', 50)->primary();
            $table->string('id_user', 50);
            $table->string('id_mapel', 50);
            $table->string('id_tahun_ajaran', 50);
            $table->string('judul', 150);
            $table->text('deskripsi')->nullable();
            $table->string('kategori', 50)->default('kuis harian');
            $table->string('tingkat_kesulitan', 20)->default('mudah');
            $table->integer('alokasi_waktu')->default(15);
            $table->decimal('kkm', 5, 2)->nullable();
            $table->string('status_publikasi', 30)->default('terbit');
            $table->timestamp('waktu_mulai')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
            $table->string('id_publisher', 50)->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });
        Schema::create('tp_kuis_kelas', function (Blueprint $table): void {
            $table->string('id_kuis', 50);
            $table->string('id_kelas', 50);
        });
        Schema::create('tp_materi_kuis', function (Blueprint $table): void {
            $table->string('id_kuis', 50);
            $table->string('id_materi', 50);
        });
        Schema::create('tm_soal', function (Blueprint $table): void {
            $table->string('id_soal', 50)->primary();
            $table->string('id_kuis', 50);
            $table->string('tipe', 30)->default('pilihan_ganda');
            $table->text('pertanyaan');
            foreach (['a', 'b', 'c', 'd', 'e'] as $option) {
                $table->text('opsi_'.$option)->nullable();
            }
            $table->string('jawaban', 1)->nullable();
            $table->decimal('bobot', 5, 2)->default(100);
            $table->string('tingkat_kesulitan', 30)->nullable();
        });
        Schema::create('tr_progress', function (Blueprint $table): void {
            $table->string('id_progress', 50)->primary();
            $table->string('id_user', 50);
            $table->string('id_materi', 50);
            $table->string('status', 30);
            $table->date('tanggal')->nullable();
        });
        Schema::create('tr_mengerjakan', function (Blueprint $table): void {
            $table->id('id_mengerjakan');
            $table->string('id_kuis', 50);
            $table->string('id_user', 50);
            $table->integer('percobaan_ke')->default(1);
            $table->string('status_pengerjaan', 30)->default('final');
            $table->string('status_validasi', 40)->nullable();
            $table->string('id_validator', 50)->nullable();
            $table->decimal('nilai', 5, 2)->nullable();
            $table->date('tanggal')->nullable();
        });
        Schema::create('tr_sesi_kuis', function (Blueprint $table): void {
            $table->id('id_sesi');
            $table->string('id_kuis', 50);
            $table->string('id_user', 50);
            $table->integer('percobaan_ke')->default(1);
            $table->timestamp('waktu_mulai')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
        });
        Schema::create('tr_remedial', function (Blueprint $table): void {
            $table->id('id_remedial');
            $table->string('id_kuis_asal', 50);
            $table->string('id_kuis_remedial', 50);
        });
        Schema::create('tr_remedial_siswa', function (Blueprint $table): void {
            $table->id('id_remedial_siswa');
            $table->unsignedBigInteger('id_mengerjakan_asal');
            $table->string('id_kuis_remedial', 50);
            $table->string('id_guru', 50);
            $table->date('tanggal_ditugaskan')->nullable();
            $table->string('status', 30)->default('ditugaskan');
        });
    }
}
