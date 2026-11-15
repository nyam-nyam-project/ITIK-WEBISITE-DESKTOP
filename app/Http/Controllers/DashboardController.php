<?php

namespace App\Http\Controllers;

use App\Models\Kuis;
use App\Models\User;
use App\Services\GrafikGuruService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private GrafikGuruService $grafikGuruService) {}

    public function index(): View
    {
        Kuis::sinkronisasiStatus();

        $guru = auth()->user();

        abort_unless($guru instanceof User && $guru->role === 'guru', 403);

        $hasilDashboard = $this->grafikGuruService->ambilUntuk($guru);
        $grafik = $hasilDashboard;
        unset($grafik['statistik']);

        return view('guru.dashboard', $hasilDashboard['statistik'] + ['grafik' => $grafik]);
    }
}
