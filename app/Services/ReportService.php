<?php

namespace App\Services;

use App\Models\Device;
use App\Models\PingLog;
use App\Models\ErrorLog;
use App\Models\TonerAlert;
use App\Models\PrinterCounter;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Response;

class ReportService
{
    /**
     * Compile report data for a specific period.
     */
    public function generate(Carbon $start, Carbon $end): array
    {
        $data = [
            'period' => [
                'start' => $start->toDateTimeString(),
                'end' => $end->toDateTimeString(),
            ],
            'stats' => [
                'total_devices' => Device::count(),
                'active_devices' => Device::active()->count(),
                'total_errors' => ErrorLog::whereBetween('logged_at', [$start, $end])->count(),
                'unresolved_errors' => ErrorLog::unresolved()->count(),
                'toner_alerts' => TonerAlert::whereBetween('alerted_at', [$start, $end])->count(),
            ],
            'connectivity' => $this->getConnectivityStats($start, $end),
            'printing' => $this->getPrintingStats($start, $end),
            'top_disconnected' => $this->getTopDisconnected($start, $end),
            'current_toner' => $this->getCurrentTonerState(),
        ];

        return $data;
    }

    /**
     * Generate PDF response.
     */
    public function exportPdf(array $data): Response
    {
        $pdf = Pdf::loadView('reports.pdf.full', $data);
        return $pdf->download('report_' . now()->format('Y-m-d_His') . '.pdf');
    }

    /**
     * Send report by email (implementation template).
     */
    public function sendByEmail(array $data, string $email): void
    {
        // Envoi via une Mailable (à définir plus tard)
        // Mail::to($email)->send(new \App\Mail\WeeklyReportMail($data));
    }

    /**
     * Connectivity details.
     */
    private function getConnectivityStats(Carbon $start, Carbon $end): array
    {
        $totalPings = PingLog::whereBetween('tested_at', [$start, $end])->count();
        if ($totalPings === 0) return ['availability_rate' => 100];

        $onlinePings = PingLog::whereBetween('tested_at', [$start, $end])
            ->whereIn('status', ['online', 'slow'])
            ->count();

        return [
            'availability_rate' => round(($onlinePings / $totalPings) * 100, 2),
            'total_pings' => $totalPings,
            'offline_count' => $totalPings - $onlinePings,
        ];
    }

    /**
     * Pagination stats for printers.
     */
    private function getPrintingStats(Carbon $start, Carbon $end): array
    {
        return Device::where('type', 'imprimante')
            ->with(['printerCounters' => function($q) use ($start, $end) {
                $q->whereBetween('recorded_at', [$start, $end])->orderBy('recorded_at', 'desc');
            }])
            ->get()
            ->map(function($device) {
                $counters = $device->printerCounters;
                if ($counters->isEmpty()) return null;
                
                $latest = $counters->first();
                $oldest = $counters->last();

                return [
                    'device_name' => $device->name,
                    'pages_printed' => $latest->total_pages - $oldest->total_pages,
                    'current_total' => $latest->total_pages,
                ];
            })
            ->filter()
            ->toArray();
    }

    /**
     * Top 10 devices with most offline time.
     */
    private function getTopDisconnected(Carbon $start, Carbon $end): array
    {
        return PingLog::whereBetween('tested_at', [$start, $end])
            ->where('status', 'offline')
            ->whereHas('device') // Assure que l'appareil existe encore (non supprimé)
            ->selectRaw('device_id, count(*) as offline_events')
            ->groupBy('device_id')
            ->orderByDesc('offline_events')
            ->limit(10)
            ->with('device:id,name')
            ->get()
            ->toArray();
    }

    /**
     * Current toner levels for all printers.
     */
    private function getCurrentTonerState(): array
    {
        return Device::where('type', 'imprimante')
            ->get()
            ->map(function($device) {
                $lastCounter = $device->printerCounters()->latest('recorded_at')->first();
                return [
                    'device_name' => $device->name,
                    'toner' => $lastCounter ? [
                        'black' => $lastCounter->toner_black_pct,
                        'cyan' => $lastCounter->toner_cyan_pct,
                        'magenta' => $lastCounter->toner_magenta_pct,
                        'yellow' => $lastCounter->toner_yellow_pct,
                    ] : null
                ];
            })
            ->toArray();
    }
}
