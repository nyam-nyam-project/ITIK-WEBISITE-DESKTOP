<?php

namespace App\View\Components;

use App\Http\Middleware\RiwayatHalaman;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TombolKembali extends Component
{
    public string $href;

    public function __construct(
        public string $fallback,
        public string $label = 'Kembali',
        public ?string $ariaLabel = null,
        public bool $denganIkon = true,
    ) {
        $this->href = $this->tentukanHref();
    }

    public function render(): View
    {
        return view('components.tombol-kembali');
    }

    private function tentukanHref(): string
    {
        $request = request();

        if (! $request->hasSession()) {
            return $this->fallback;
        }

        $riwayat = $request->session()->get(RiwayatHalaman::KUNCI_SESSION, []);

        if (! is_array($riwayat) || $riwayat === []) {
            return $this->fallback;
        }

        $urlSaatIni = RiwayatHalaman::bersihkanUrl($request);

        foreach (array_reverse($riwayat) as $url) {
            if (! is_string($url) || $url === '') {
                continue;
            }

            if ($url === $urlSaatIni) {
                continue;
            }

            $path = trim((string) parse_url($url, PHP_URL_PATH), '/');

            if (
                $path === 'login'
                || $path === 'admin/login'
                || $path === 'logout'
                || str_ends_with($path, '/kerjakan')
                || str_ends_with($path, '/mulai')
                || str_ends_with($path, '/create')
                || str_ends_with($path, '/edit')
                || str_ends_with($path, '/konfirmasi-arsip')
            ) {
                continue;
            }

            return $url;
        }

        return $this->fallback;
    }
}
