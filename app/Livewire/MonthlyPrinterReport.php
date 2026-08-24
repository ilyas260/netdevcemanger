<?php

namespace App\Livewire;

use App\Models\Device;
use App\Models\PrinterCounter;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MonthlyPrinterReport extends Component
{
    public Device $device;
    public $monthlyStats = [];

    public function mount(Device $device)
    {
        $this->device = $device;
        $this->calculateMonthlyStats();
    }

    public function calculateMonthlyStats()
    {
        // On récupère les snapshots groupés par mois (en ignorant les erreurs à 0)
        $snapshots = PrinterCounter::where('device_id', $this->device->id)
            ->where('is_consumption_snapshot', true)
            ->where('total_pages', '>', 0)
            ->orderBy('recorded_at', 'asc')
            ->get()
            ->groupBy(function($item) {
                return $item->recorded_at->format('Y-m');
            });

        $stats = [];
        foreach ($snapshots as $month => $monthRecords) {
            $first = $monthRecords->first();
            $last = $monthRecords->last();
            
            // Pour avoir la conso du mois, on compare le premier relevé du mois 
            // avec le premier relevé du mois SUIVANT (si dispo) ou le dernier du mois
            
            // Calcul simplifié : Last - First du même mois
            // Note: Pour être ultra précis du 1 au 30, il faudrait comparer avec le mois précédent
            $lastA3  = (int)($last->a3_pages  ?? 0);
            $firstA3 = (int)($first->a3_pages ?? 0);
            $a4Conso    = max(0, ($last->total_pages  - $lastA3)  - ($first->total_pages - $firstA3));
            $a3Conso    = max(0, $lastA3 - $firstA3);
            $totalConso = $last->total_pages - $first->total_pages;

            $stats[] = [
                'month_name' => Carbon::parse($month . '-01')->translatedFormat('F Y'),
                'a4' => $a4Conso,
                'a3' => $a3Conso,
                'total' => $totalConso,
                'start_counter' => $first->total_pages,
                'end_counter' => $last->total_pages,
                'is_current' => $month === Carbon::now()->format('Y-m'),
            ];
        }

        $this->monthlyStats = array_reverse($stats);
    }

    public function render()
    {
        return view('livewire.monthly-printer-report');
    }
}
