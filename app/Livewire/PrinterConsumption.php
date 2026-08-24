<?php

namespace App\Livewire;

use App\Models\Device;
use App\Models\PrinterCounter;
use Livewire\Component;
use Carbon\Carbon;

class PrinterConsumption extends Component
{
    public Device $device;
    public $startDate;
    public $startTime;
    public $endDate;
    public $endTime;
    public $consumption = null;
    public $periodDays = 0;
    public $dailyHistory = [];

    // Manual Entry Fields
    public $manualDate;
    public $manualTime;
    public $manualA4;
    public $manualA3;
    public $showManualForm = false;

    public function mount(Device $device)
    {
        $this->device = $device;
        $this->endDate = Carbon::now()->format('Y-m-d');
        
        // Par défaut, on affiche du 1er du mois actuel à aujourd'hui
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        
        $this->startTime = '00:00';
        $this->endTime = Carbon::now()->format('H:i');
            
        $this->manualDate = Carbon::now()->format('Y-m-d');
        $this->manualTime = Carbon::now()->format('H:i');
            
        $this->calculate();
    }

    public function calculate()
    {
        if (!$this->startDate || !$this->endDate) return;
        if (!$this->startTime) $this->startTime = '00:00';
        if (!$this->endTime) $this->endTime = '23:59';

        $start = Carbon::parse($this->startDate . ' ' . $this->startTime);
        $end = Carbon::parse($this->endDate . ' ' . $this->endTime);
        
        $this->periodDays = (int) $start->diffInDays($end);
        if ($this->periodDays == 0 && $start->diffInHours($end) > 0) {
            $this->periodDays = 1; // Minimum 1 jour pour l'affichage si l'intervalle est court
        }

        // Find closest records (en ignorant les relevés à 0 qui sont des erreurs)
        $startRecord = PrinterCounter::where('device_id', $this->device->id)
            ->where('total_pages', '>', 0)
            ->where('recorded_at', '>=', $start)
            ->orderBy('recorded_at', 'asc')
            ->first();

        $endRecord = PrinterCounter::where('device_id', $this->device->id)
            ->where('total_pages', '>', 0)
            ->where('recorded_at', '<=', $end)
            ->orderBy('recorded_at', 'desc')
            ->first();

        if ($startRecord && $endRecord && $startRecord->id !== $endRecord->id) {
            $endA3   = (int)($endRecord->a3_pages   ?? 0);
            $startA3 = (int)($startRecord->a3_pages ?? 0);
            $endA4   = $endRecord->total_pages   - $endA3;
            $startA4 = $startRecord->total_pages  - $startA3;
            $this->consumption = [
                'total' => $endRecord->total_pages - $startRecord->total_pages,
                'a4'    => max(0, $endA4 - $startA4),
                'a3'    => max(0, $endA3 - $startA3),
                'start_date' => $startRecord->recorded_at,
                'end_date'   => $endRecord->recorded_at,
            ];
        } else {
            $this->consumption = null;
        }

        // Calcul de l'historique quotidien (7 derniers jours)
        $this->dailyHistory = $this->calculateDailyHistory();
    }

    private function calculateDailyHistory()
    {
        // On récupère tous les snapshots du mois en cours pour un historique complet
        $snapshots = PrinterCounter::where('device_id', $this->device->id)
            ->where('is_consumption_snapshot', true)
            ->where('total_pages', '>', 0)
            ->whereMonth('recorded_at', Carbon::now()->month)
            ->whereYear('recorded_at', Carbon::now()->year)
            ->orderBy('recorded_at', 'asc')
            ->get();

        $history = [];
        for ($i = 1; $i < $snapshots->count(); $i++) {
            $prev = $snapshots[$i - 1];
            $curr = $snapshots[$i];

            $currA3 = (int)($curr->a3_pages ?? 0);
            $prevA3 = (int)($prev->a3_pages ?? 0);
            $currA4 = $curr->total_pages - $currA3;
            $prevA4 = $prev->total_pages - $prevA3;

            $history[] = [
                'date'  => $curr->recorded_at->format('d/m/Y'),
                'a4'    => max(0, $currA4 - $prevA4),
                'a3'    => max(0, $currA3 - $prevA3),
                'total' => $curr->total_pages - $prev->total_pages,
            ];
        }

        return array_reverse($history);
    }

    public function saveSnapshot()
    {
        // This is usually handled by SnmpService, but we could force one here
        \App\Jobs\FetchSnmpJob::dispatchSync($this->device);
        session()->flash('message', 'Snapshot de consommation enregistré.');
        $this->calculate();
    }

    public function addManualRecord()
    {
        $this->validate([
            'manualDate' => 'required|date',
            'manualTime' => 'required',
            'manualA4' => 'required|integer|min:0',
            'manualA3' => 'required|integer|min:0',
        ]);

        $recordedAt = Carbon::parse($this->manualDate . ' ' . $this->manualTime);

        PrinterCounter::create([
            'device_id' => $this->device->id,
            'recorded_at' => $recordedAt,
            'total_pages' => $this->manualA4 + ($this->manualA3 * 2),
            'a4_pages' => $this->manualA4,
            'a3_pages' => $this->manualA3,
            'is_consumption_snapshot' => true,
            'printer_status' => 'Saisie manuelle',
            'paper_level' => 'N/A',
        ]);

        $this->showManualForm = false;
        $this->manualA4 = null;
        $this->manualA3 = null;
        
        session()->flash('message', 'Relevé manuel enregistré avec succès.');
        $this->calculate();
    }

    public function render()
    {
        return view('livewire.printer-consumption');
    }
}
