<?php

namespace App\Livewire;

use App\Models\Device;
use App\Models\PrinterCounter;
use App\Services\PingService;
use App\Services\SnmpService;
use Livewire\Component;
use Carbon\Carbon;

class TonerDashboard extends Component
{
    public $filter = 'all'; // all, critical, warning
    public $isUpdating = false;

    public function render()
    {
        $devices = Device::active()
            ->where('type', 'imprimante')
            ->with(['printerCounters' => function($q) {
                $q->latest('recorded_at')->limit(30);
            }])
            ->get();

        $printersData = $devices->map(function($device) {
            // On cherche le dernier relevé qui contient réellement des données de consommables
            $latest = $device->printerCounters->whereNotNull('consumables')->filter(fn($c) => !empty($c->consumables))->first() 
                      ?? $device->printerCounters->first();

            $levels = [];
            if ($latest && !empty($latest->consumables)) {
                foreach ($latest->consumables as $item) {
                    $levels[$item['name']] = [
                        'pct' => $item['pct'],
                        'serial' => $item['serial'] ?? 'N/A',
                        'type' => $item['type'] ?? 'Standard'
                    ];
                }
            } else {
                $levels = [
                    'Noir' => ['pct' => $latest ? $latest->toner_black_pct : null, 'serial' => 'N/A', 'type' => 'Standard'],
                    'Cyan' => ['pct' => $latest ? $latest->toner_cyan_pct : null, 'serial' => 'N/A', 'type' => 'Standard'],
                    'Magenta' => ['pct' => $latest ? $latest->toner_magenta_pct : null, 'serial' => 'N/A', 'type' => 'Standard'],
                    'Jaune' => ['pct' => $latest ? $latest->toner_yellow_pct : null, 'serial' => 'N/A', 'type' => 'Standard'],
                ];
            }

            // Récupération des 2 derniers snapshots pour calculer l'intervalle le plus récent
            $twoLatestSnapshots = $device->printerCounters()
                ->where('is_consumption_snapshot', true)
                ->whereNotNull('total_pages')
                ->where('total_pages', '>', 0)
                ->latest('recorded_at')
                ->limit(2)
                ->get();

            $lastSnapshot   = $twoLatestSnapshots->first();  // le plus récent
            $prevSnapshot   = $twoLatestSnapshots->count() >= 2 ? $twoLatestSnapshots->last() : null; // le précédent

            $consumptionA4  = 0;
            $consumptionA3  = 0;
            $intervalDays   = 0;
            $avgDailyA4     = null; // Affiché seulement si >= 20 jours

            if ($lastSnapshot && $prevSnapshot) {
                $intervalDays  = max(1, (int) $prevSnapshot->recorded_at->diffInDays($lastSnapshot->recorded_at));
                $lastA4        = $lastSnapshot->total_pages - ($lastSnapshot->a3_pages ?: 0);
                $prevA4        = $prevSnapshot->total_pages - ($prevSnapshot->a3_pages ?: 0);
                $consumptionA4 = max(0, $lastA4 - $prevA4);
                $consumptionA3 = max(0, ($lastSnapshot->a3_pages ?: 0) - ($prevSnapshot->a3_pages ?: 0));

                // Moyenne journalière uniquement si l'intervalle est >= 20 jours
                if ($intervalDays >= 20) {
                    $avgDailyA4 = round($consumptionA4 / $intervalDays, 1);
                }
            }

            $stats = [
                'device'           => $device,
                'levels'           => $levels,
                'eta'              => $this->calculateETA($device),
                'status'           => $latest ? SnmpService::translateComponent($latest->printer_status ?? '') : 'Aucune donnée',
                'total_pages'      => $latest ? $latest->total_pages : 0,
                'paper_level'      => $latest ? SnmpService::translateComponent($latest->paper_level ?? '') : 'N/A',
                'is_online'        => $device->last_seen_at && $device->last_seen_at->gt(now()->subMinutes(10)),
                'last_seen'        => $device->last_seen_at ? $device->last_seen_at->diffForHumans() : 'Jamais',
                'last_snapshot'    => $lastSnapshot,
                'prev_snapshot'    => $prevSnapshot,
                'interval_days'    => $intervalDays,
                'consumption_a4'   => $consumptionA4,
                'consumption_a3'   => $consumptionA3,
                'avg_daily_a4'     => $avgDailyA4,
            ];

            return $stats;
        })->filter();

        // Application du filtre
        if ($this->filter === 'critical') {
            $printersData = $printersData->filter(function($p) {
                if (empty($p['levels'])) return false;
                $min = collect($p['levels'])->pluck('pct')->min();
                return $min !== null && (float)$min < 10;
            });
        } elseif ($this->filter === 'warning') {
            $printersData = $printersData->filter(function($p) {
                if (empty($p['levels'])) return false;
                $min = collect($p['levels'])->pluck('pct')->min();
                return $min !== null && (float)$min < 20;
            });
        }

        return view('livewire.toner-dashboard', [
            'printers' => $printersData
        ]);
    }

    /**
     * Calcule l'estimation de jours restants basée sur les 30 derniers relevés.
     */
    private function calculateETA(Device $device)
    {
        $counters = $device->printerCounters;
        if ($counters->count() < 2) return '?';

        $latest = $counters->first();
        $oldest = $counters->last();
        
        $days = $oldest->recorded_at->diffInDays($latest->recorded_at);
        if ($days <= 0) return '?';

        // Moyenne de pages par jour
        $pagesUsed = $latest->total_pages - $oldest->total_pages;
        $avgPagesPerDay = $pagesUsed / $days;

        if ($avgPagesPerDay <= 0) return '∞';

        // Estimation très simplifiée basée sur le niveau de toner noir (le plus utilisé)
        // Supposons 100% = 5000 pages
        $remainingPages = ($latest->toner_black_pct / 100) * 5000;
        
        return round($remainingPages / $avgPagesPerDay);
    }

    public function updatePrinters(PingService $pingService, SnmpService $snmpService)
    {
        $this->isUpdating = true;
        
        $devices = Device::active()->where('type', 'imprimante')->get();
        
        foreach ($devices as $device) {
            // Un ping rapide avant SNMP
            $ping = $pingService->executePing($device, 1);
            
            if ($ping['status'] !== 'offline') {
                $snmpService->fetchCounters($device);
            }
        }

        $this->isUpdating = false;
        session()->flash('message', 'Niveaux de toner actualisés.');
    }
}
