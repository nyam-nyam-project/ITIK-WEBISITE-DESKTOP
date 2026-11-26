<?php

namespace Tests\Feature;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class UiIconsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame('testing', $this->app->environment());
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->assertEmpty(config('database.connections.sqlite.url'));
        $this->assertSame('array', config('session.driver'));
    }

    public static function loginPages(): array
    {
        return [
            'siswa dan guru' => ['login', 'login.process', 'nomor_induk'],
            'administrator' => ['admin.login', 'admin.login.process', 'login'],
        ];
    }

    #[DataProvider('loginPages')]
    public function test_login_mempertahankan_form_dan_ikon_vektor_dengan_toggle_aksesibel(string $route, string $action, string $identifier): void
    {
        $response = $this->get(route($route));

        $response->assertOk();
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        $xpath = new DOMXPath($document);
        $this->assertCount(1, $xpath->query('//form[@method="POST" and @action="'.route($action).'"]'));
        $this->assertCount(1, $xpath->query('//input[@name="'.$identifier.'" and @required]'));
        $this->assertCount(1, $xpath->query('//input[@name="password" and @type="password" and @required]'));
        $this->assertCount(1, $xpath->query('//head/link[contains(@href, "/css/icons.css")]'));
        $this->assertCount(1, $xpath->query('//script[contains(@src, "/js/auth/password.js") and @defer]'));
        $this->assertCount(1, $xpath->query('//button[@data-password-toggle="password" and @aria-label="Tampilkan password" and @aria-pressed="false"]'));
        $this->assertCount(1, $xpath->query('//button[@data-password-toggle]/*[@data-password-icon="hidden" and not(@hidden)]/svg/use[contains(@href, "#eye")]'));
        $this->assertCount(1, $xpath->query('//button[@data-password-toggle]/*[@data-password-icon="visible" and @hidden]/svg/use[contains(@href, "#eye-off")]'));
        $this->assertCount(0, $xpath->query('//svg[contains(@class, "ui-icon") and not(@aria-hidden="true")]'));

        $sprite = new DOMDocument;
        $this->assertTrue($sprite->loadXML(File::get(public_path('images/icons.svg'))));
        $symbols = new DOMXPath($sprite);
        foreach ($xpath->query('//svg[contains(@class, "ui-icon")]/use') as $use) {
            $name = parse_url($use->getAttribute('href'), PHP_URL_FRAGMENT);
            $this->assertCount(1, $symbols->query('//*[@id="'.$name.'"]'), 'Missing symbol '.$name);
        }
    }

    public function test_toggle_password_mengganti_visibilitas_dan_label_tanpa_menghapus_svg(): void
    {
        $script = <<<'JS'
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const password = {type: 'password'};
const icons = ['hidden', 'visible'].map(name => ({
    dataset: {passwordIcon: name},
    hidden: name === 'visible',
    toggleAttribute(attribute, value) {
        assert.equal(attribute, 'hidden');
        this.hidden = value;
    }
}));
const attributes = {'aria-pressed': 'false', 'aria-label': 'Tampilkan password'};
let click;
const button = {
    dataset: {
        passwordToggle: 'password',
        passwordVisibleLabel: 'Sembunyikan password',
        passwordHiddenLabel: 'Tampilkan password'
    },
    querySelectorAll(selector) {
        assert.equal(selector, '[data-password-icon]');
        return icons;
    },
    addEventListener(event, handler) {
        assert.equal(event, 'click');
        click = handler;
    },
    setAttribute(name, value) { attributes[name] = value; },
    set textContent(value) { throw new Error('SVG content was overwritten'); }
};
const document = {
    querySelectorAll(selector) {
        assert.equal(selector, '[data-password-toggle]');
        return [button];
    },
    getElementById(id) {
        assert.equal(id, 'password');
        return password;
    }
};
vm.runInNewContext(fs.readFileSync(process.argv[1], 'utf8'), {document});
assert.equal(typeof click, 'function');
click();
assert.equal(password.type, 'text');
assert.deepEqual(icons.map(icon => icon.hidden), [true, false]);
assert.deepEqual(attributes, {'aria-pressed': 'true', 'aria-label': 'Sembunyikan password'});
click();
assert.equal(password.type, 'password');
assert.deepEqual(icons.map(icon => icon.hidden), [false, true]);
assert.deepEqual(attributes, {'aria-pressed': 'false', 'aria-label': 'Tampilkan password'});
console.log('password SVG toggle passed');
JS;
        $process = new Process(['node', '-e', $script, public_path('js/auth/password.js')], base_path());

        $process->run();

        $this->assertSame(0, $process->getExitCode(), $process->getErrorOutput().$process->getOutput());
        $this->assertStringContainsString('password SVG toggle passed', $process->getOutput());
    }
}
