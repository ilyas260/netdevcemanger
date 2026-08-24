<?php

namespace App\Livewire;

use App\Models\ErrorLog;
use App\Models\Device;
use App\Models\Agency;
use App\Services\ConnectivityIssueService;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ErrorLogTable extends Component
{
    use WithPagination;

    public $type = 'agencies'; // devices ou agencies - PAR DÉFAUT: AGENCES
    public $deviceId = '';
    public $agencyId = '';
    public $severity = '';
    public $status = 'unresolved'; // unresolved, resolved, all

    protected $queryString = ['type', 'deviceId', 'agencyId', 'severity', 'status'];

    public function render()
    {
        $query = ErrorLog::with('device', 'resolver')->latest('logged_at');

        // Filtrer par type
        if ($this->type === 'agencies') {
            // Afficher uniquement les problèmes des agences
            $query->whereIn('error_type', ['Panne Agence', 'Panne Serveur', 'Panne Réseau Central', 'SNMP Inaccessible', 'Network Unavailable']);
        }

        if ($this->type === 'devices' && $this->deviceId) {
            $query->where('device_id', $this->deviceId);
        }

        if ($this->type === 'agencies' && $this->agencyId) {
            $query->whereHas('device', function($q) {
                $q->where('agency_id', $this->agencyId);
            });
        }

        if ($this->severity) {
            $query->where('severity', $this->severity);
        }

        if ($this->status === 'unresolved') {
            $query->where('is_resolved', false);
        } elseif ($this->status === 'resolved') {
            $query->where('is_resolved', true);
        }

        $logs = $query->paginate(15);
        
        return view('livewire.error-log-table', [
            'logs' => $logs,
            'devices' => Device::select('id', 'name', 'ip_address')
                            ->where('type', '!=', 'routeur')
                            ->orderBy('name')
                            ->get(),
            'agencies' => Agency::select('id', 'name')->orderBy('name')->get()
        ]);
    }
}
