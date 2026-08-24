<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\PingLog;
use App\Models\ErrorLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Carbon\Carbon;

class GlobalStatisticsController extends Controller
{
    public function index(): View
    {
        // 1. Latence Moyenne par Type d'Appareil
        $latencyByType = Device::select('type', DB::raw('AVG(ping_logs.avg_latency_ms) as avg_latency'))
            ->leftJoin('ping_logs', 'devices.id', '=', 'ping_logs.device_id')
            ->where('ping_logs.tested_at', '>', now()->subDays(7))
            ->groupBy('type')
            ->get();

        // 2. Répartition par Marques (Top 5)
        $brandDistribution = Device::select('brand', DB::raw('count(*) as total'))
            ->groupBy('brand')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // 3. Évolution des Incidents (7 derniers jours)
        $incidentsTrend = ErrorLog::select(DB::raw('DATE(logged_at) as date'), DB::raw('count(*) as total'))
            ->where('logged_at', '>', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 4. Statistiques de disponibilité (Uptime global)
        $totalPings = PingLog::where('tested_at', '>', now()->subDays(30))->count();
        $successPings = PingLog::where('tested_at', '>', now()->subDays(30))
            ->where('status', 'online')
            ->count();
        
        $uptimePct = $totalPings > 0 ? round(($successPings / $totalPings) * 100, 2) : 100;

        return view('statistics.global', compact(
            'latencyByType', 
            'brandDistribution', 
            'incidentsTrend',
            'uptimePct'
        ));
    }
}
