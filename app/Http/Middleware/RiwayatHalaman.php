<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RiwayatHalaman
{
    public const KUNCI_SESSION = 'riwayat_halaman';

    private const MAKSIMAL_RIWAYAT = 10;

    /**
     * @var array<int, string>
     */
    private const PARAMETER_SENSITIF = [
        '_token',
        'password',
        'password_confirmation',
        'current_password',
        'token',
        'secret',
    ];

    /**
     * @var array<int, string>
     */
    private const RUTE_DIABAIKAN = [
        'login',
        'login.*',
        'admin.login',
        'admin.login.*',
        'logout',
        'siswa.kuis.kerjakan',
        'siswa.kuis.mulai',
        'materi.pdf',
        '*.create',
        '*.edit',
        'materi.konfirmasi-arsip',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->harusMencatat($request, $response)) {
            return $response;
        }

        $urlSaatIni = self::bersihkanUrl($request);
        $riwayat = $request->session()->get(self::KUNCI_SESSION, []);

        if (! is_array($riwayat)) {
            $riwayat = [];
        }

        $riwayat = array_values(array_filter(
            $riwayat,
            static fn (mixed $item): bool => is_string($item) && $item !== ''
        ));

        if ($riwayat !== [] && end($riwayat) === $urlSaatIni) {
            return $response;
        }

        $indeksAda = array_search($urlSaatIni, $riwayat, true);

        if ($indeksAda !== false) {
            // Jika kembali ke halaman yang sudah ada di stack, potong stack sampai posisi itu
            // agar tombol kembali tidak berputar-putar antar dua halaman.
            $riwayat = array_slice($riwayat, 0, $indeksAda + 1);
        } else {
            $riwayat[] = $urlSaatIni;
        }

        if (count($riwayat) > self::MAKSIMAL_RIWAYAT) {
            $riwayat = array_slice($riwayat, -self::MAKSIMAL_RIWAYAT);
        }

        $request->session()->put(self::KUNCI_SESSION, $riwayat);

        return $response;
    }

    public static function bersihkanUrl(Request $request): string
    {
        $query = $request->query();

        if (! is_array($query) || $query === []) {
            return $request->url();
        }

        $aman = [];

        foreach ($query as $key => $value) {
            if (in_array(strtolower((string) $key), self::PARAMETER_SENSITIF, true)) {
                continue;
            }

            $aman[$key] = $value;
        }

        if ($aman === []) {
            return $request->url();
        }

        return $request->url().'?'.http_build_query($aman);
    }

    private function harusMencatat(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        if ($request->ajax() || $request->expectsJson()) {
            return false;
        }

        if (! $request->hasSession()) {
            return false;
        }

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        $contentType = strtolower((string) $response->headers->get('Content-Type', ''));

        if (! str_contains($contentType, 'text/html')) {
            return false;
        }

        if ($request->routeIs(self::RUTE_DIABAIKAN)) {
            return false;
        }

        $path = trim($request->path(), '/');

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
            return false;
        }

        return true;
    }
}
