<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\PingLog;
use App\Models\ErrorLog;
use App\Models\TonerAlert;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;


class DashboardController extends Controller
{
    /**
     * Display the application dashboard.
     */
    public function index(): View
    {
        $stats = [
            'total_devices' => Device::count(),
            'active_devices' => Device::active()->count(),
            'offline_devices' => Device::active()
                ->where('last_seen_at', '<', now()->subMinutes(\App\Models\Setting::get('ping_interval', 5) * 3)) // Seuil de 3 fois l'intervalle pour éviter les faux positifs
                ->count(),
            'unresolved_errors' => ErrorLog::where('is_resolved', false)->count(),
            'active_toner_alerts' => TonerAlert::where('is_resolved', false)->count(),
        ];

        $recent_errors = ErrorLog::whereHas('device')
            ->with('device')
            ->where('is_resolved', false)
            ->latest()
            ->limit(5)
            ->get();

        $top_unstable = PingLog::whereHas('device')
            ->select('device_id', DB::raw('count(*) as offline_total'))
            ->where('status', 'offline')
            ->where('tested_at', '>', now()->subDays(30))
            ->groupBy('device_id')
            ->orderByDesc('offline_total')
            ->with('device')
            ->limit(5)
            ->get();

        // Données pour les diagrammes
        $chartData = [
            'device_types' => Device::select('type', DB::raw('count(*) as total'))
                ->groupBy('type')
                ->get(),
            'status_distribution' => [
                'online' => Device::active()->where('last_seen_at', '>=', now()->subMinutes(\App\Models\Setting::get('ping_interval', 5) * 3))->count(),
                'offline' => $stats['offline_devices'],
                'inactive' => Device::where('is_active', false)->count(),
            ]
        ];

        return view('dashboard', compact('stats', 'recent_errors', 'top_unstable', 'chartData'));
    }

    /**
     * Get count of devices not seen in the last 15 minutes or with last ping offline.
     */
    private function getOfflineCount(): int
    {
        return Device::active()
            ->where(function($query) {
                $query->whereNull('last_seen_at')
                      ->orWhere('last_seen_at', '<', Carbon::now()->subMinutes(\App\Models\Setting::get('ping_interval', 5) * 3));
            })
            ->count();
    }
}
