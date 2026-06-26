<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StatisticController extends Controller
{
    public function index(Request $request)
    {
        // rentang waktu: 7 / 30 / 90 hari (default 30)
        $range = (int) $request->get('range', 30);
        if (! in_array($range, [7, 30, 90], true)) {
            $range = 30;
        }
        $since = Carbon::now()->subDays($range - 1)->startOfDay();

        $base = PageVisit::humans()->where('created_at', '>=', $since);

        // ── Kartu ringkasan ──
        $totalVisits  = (clone $base)->count();
        $uniqueVisitors = (clone $base)->distinct('visitor_hash')->count('visitor_hash');
        $todayVisits  = PageVisit::humans()->whereDate('created_at', Carbon::today())->count();

        // halaman/sesi rata-rata (sederhana): total / sesi unik
        $sessions = (clone $base)->distinct('session_id')->count('session_id');
        $avgPerSession = $sessions > 0 ? round($totalVisits / $sessions, 1) : 0;

        // ── Grafik harian ──
        $daily = (clone $base)
            ->select(DB::raw('DATE(created_at) as d'), DB::raw('COUNT(*) as total'))
            ->groupBy('d')->orderBy('d')->pluck('total', 'd');

        $chartLabels = [];
        $chartData = [];
        for ($i = 0; $i < $range; $i++) {
            $day = $since->copy()->addDays($i)->toDateString();
            $chartLabels[] = Carbon::parse($day)->format('d/m');
            $chartData[] = (int) ($daily[$day] ?? 0);
        }

        // ── Breakdown device ──
        $byDevice = (clone $base)
            ->select('device', DB::raw('COUNT(*) as total'))
            ->groupBy('device')->orderByDesc('total')->pluck('total', 'device')->all();

        // ── Browser ──
        $byBrowser = (clone $base)
            ->select('browser', DB::raw('COUNT(*) as total'))
            ->groupBy('browser')->orderByDesc('total')->limit(6)->pluck('total', 'browser')->all();

        // ── Platform / OS ──
        $byPlatform = (clone $base)
            ->select('platform', DB::raw('COUNT(*) as total'))
            ->groupBy('platform')->orderByDesc('total')->limit(6)->pluck('total', 'platform')->all();

        // ── Halaman populer ──
        $topPages = (clone $base)
            ->select('url', DB::raw('COUNT(*) as total'))
            ->groupBy('url')->orderByDesc('total')->limit(10)->get();

        // ── Sumber traffic (referrer) ──
        $topReferrers = (clone $base)
            ->whereNotNull('referrer')
            ->select('referrer', DB::raw('COUNT(*) as total'))
            ->groupBy('referrer')->orderByDesc('total')->limit(8)->get();
        $directCount = (clone $base)->whereNull('referrer')->count();

        return view('admin.statistics.index', compact(
            'range', 'totalVisits', 'uniqueVisitors', 'todayVisits', 'avgPerSession',
            'chartLabels', 'chartData', 'byDevice', 'byBrowser', 'byPlatform',
            'topPages', 'topReferrers', 'directCount'
        ));
    }
}
