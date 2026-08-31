<?php

namespace App\Services;

use App\Models\Agency;
use App\Models\Device;
use App\Models\PingLog;
use App\Models\ErrorLog;
use App\Models\TonerAlert;
use App\Models\PrinterCounter;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Response;

class ReportService
{
    /**
     * Compile report data for a specific period.
     */
    public function generate(Carbon $start, Carbon $end): array
    {
        return [
            'period'           => [
                'start' => $start->toDateTimeString(),
                'end'   => $end->toDateTimeString(),
            ],
            'generated_at'     => now()->format('d/m/Y H:i:s'),
            'generated_by'     => auth()->user()?->name ?? 'Système',
            'stats'            => $this->getGlobalStats($start, $end),
            'agencies'         => $this->getAgenciesStatus(),
            'connectivity'     => $this->getConnectivityStats($start, $end),
            'top_disconnected' => $this->getTopDisconnected($start, $end),
            'recent_errors'    => $this->getRecentErrors($start, $end),
            'printing'         => $this->getPrintingStats($start, $end),
            'current_toner'    => $this->getCurrentTonerState(),
        ];
    }

    /**
     * Generate PDF response.
     */
    public function exportPdf(array $data): Response
    {
        $pdf = Pdf::loadView('reports.pdf.full', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'    => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'=> false,
            ]);

        return $pdf->download('rapport_netdevice_' . now()->format('Y-m-d_His') . '.pdf');
    }

    // ──────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────

    private function getGlobalStats(Carbon $start, Carbon $end): array
    {
        $totalPings   = PingLog::whereBetween('tested_at', [$start, $end])->count();
        $onlinePings  = PingLog::whereBetween('tested_at', [$start, $end])
            ->whereIn('status', ['online', 'slow'])->count();

        $availRate = $totalPings > 0
            ? round(($onlinePings / $totalPings) * 100, 1)
            : 100;

        return [
            'total_agencies'    => Agency::count(),
            'agencies_online'   => Agency::where('status', 'online')->count(),
            'agencies_offline'  => Agency::where('status', 'offline')->count(),
            'total_devices'     => Device::count(),
            'active_devices'    => Device::active()->count(),
            'total_errors'      => ErrorLog::whereBetween('logged_at', [$start, $end])->count(),
            'resolved_errors'   => ErrorLog::whereBetween('logged_at', [$start, $end])->where('is_resolved', true)->count(),
            'unresolved_errors' => ErrorLog::unresolved()->count(),
            'toner_alerts'      => TonerAlert::whereBetween('alerted_at', [$start, $end])->count(),
            'availability_rate' => $availRate,
        ];
    }

    private function getAgenciesStatus(): array
    {
        return Agency::withCount('devices')
            ->orderByRaw("FIELD(status, 'offline', 'unknown', 'online')")
            ->get()
            ->map(fn($a) => [
                'name'        => $a->name,
                'location'    => $a->location ?? '—',
                'router_ip'   => $a->router_ip,
                'status'      => $a->status ?? 'unknown',
                'last_ping'   => $a->last_ping_at ? Carbon::parse($a->last_ping_at)->format('d/m/Y H:i') : '—',
                'devices'     => $a->devices_count,
                'nd_technique'=> $a->nd_technique ?? '—',
            ])
            ->toArray();
    }

    private function getConnectivityStats(Carbon $start, Carbon $end): array
    {
        $totalPings  = PingLog::whereBetween('tested_at', [$start, $end])->count();
        if ($totalPings === 0) {
            return ['availability_rate' => 100, 'total_pings' => 0, 'offline_count' => 0];
        }

        $onlinePings = PingLog::whereBetween('tested_at', [$start, $end])
            ->whereIn('status', ['online', 'slow'])->count();

        return [
            'availability_rate' => round(($onlinePings / $totalPings) * 100, 1),
            'total_pings'       => $totalPings,
            'online_count'      => $onlinePings,
            'offline_count'     => $totalPings - $onlinePings,
        ];
    }

    private function getTopDisconnected(Carbon $start, Carbon $end): array
    {
        return PingLog::whereBetween('tested_at', [$start, $end])
            ->where('status', 'offline')
            ->whereHas('device')
            ->selectRaw('device_id, count(*) as offline_events')
            ->groupBy('device_id')
            ->orderByDesc('offline_events')
            ->limit(10)
            ->with('device:id,name,type')
            ->get()
            ->map(fn($row) => [
                'name'           => $row->device->name ?? 'Appareil supprimé',
                'type'           => $row->device->type ?? '—',
                'offline_events' => $row->offline_events,
            ])
            ->toArray();
    }

    private function getRecentErrors(Carbon $start, Carbon $end): array
    {
        return ErrorLog::whereBetween('logged_at', [$start, $end])
            ->with('device:id,name')
            ->orderByDesc('logged_at')
            ->limit(20)
            ->get()
            ->map(fn($e) => [
                'date'          => Carbon::parse($e->logged_at)->format('d/m/Y H:i'),
                'severity'      => $e->severity ?? 'ERROR',
                'device'        => $e->device->name ?? 'Inconnu',
                'message'       => \Illuminate\Support\Str::limit($e->message, 90),
                'solution'      => $e->solution_type
                    ? (\App\Models\ErrorLog::getSolutionTypes()[$e->solution_type] ?? $e->solution_type)
                    : '—',
                'is_resolved'   => $e->is_resolved,
            ])
            ->toArray();
    }

    private function getPrintingStats(Carbon $start, Carbon $end): array
    {
        return Device::where('type', 'imprimante')
            ->with(['printerCounters' => fn($q) => $q
                ->whereBetween('recorded_at', [$start, $end])
                ->orderBy('recorded_at', 'desc')])
            ->get()
            ->map(function ($device) {
                $counters = $device->printerCounters;
                if ($counters->isEmpty()) return null;

                $latest = $counters->first();
                $oldest = $counters->last();

                return [
                    'device_name'   => $device->name,
                    'pages_printed' => max(0, $latest->total_pages - $oldest->total_pages),
                    'current_total' => $latest->total_pages,
                ];
            })
            ->filter()
            ->values()
            ->toArray();
    }

    private function getCurrentTonerState(): array
    {
        return Device::where('type', 'imprimante')
            ->get()
            ->map(function ($device) {
                $last = $device->printerCounters()->latest('recorded_at')->first();
                return [
                    'device_name' => $device->name,
                    'toner'       => $last ? [
                        'black'   => $last->toner_black_pct   ?? null,
                        'cyan'    => $last->toner_cyan_pct    ?? null,
                        'magenta' => $last->toner_magenta_pct ?? null,
                        'yellow'  => $last->toner_yellow_pct  ?? null,
                    ] : null,
                ];
            })
            ->toArray();
    }
}
