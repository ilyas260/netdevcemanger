<?php

namespace App\Livewire;

use App\Models\Device;
use App\Services\PingService;
use Livewire\Component;
use Livewire\WithPagination;

class DeviceTable extends Component
{
    use WithPagination;

    public $search = '';
    public $type = '';
    public $status = '';
    public $selectedAgencyId = '';
    public $perPage = 15;
    public $isCheckingAll = false;

    protected $queryString = ['search', 'type', 'status', 'selectedAgencyId'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedAgencyId()
    {
        $this->resetPage();
        if ($this->selectedAgencyId) {
            $this->pingAllDevices(app(PingService::class));
        }
    }

    public function render()
    {
        $query = Device::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('ip_address', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->type) {
            $query->where('type', $this->type);
        }

        if ($this->status) {
            $query->where('is_active', $this->status === 'active');
        }

        if ($this->selectedAgencyId) {
            $query->where('agency_id', $this->selectedAgencyId);
        }

        return view('livewire.device-table', [
            'devices' => $query->latest()->paginate($this->perPage),
            'agencies' => \App\Models\Agency::orderBy('name')->get(),
        ]);
    }

    public function toggleStatus($deviceId)
    {
        abort_if(auth()->user()->hasRole('consultant'), 403);
        $device = Device::findOrFail($deviceId);
        $device->is_active = !$device->is_active;
        $device->save();
        
        session()->flash('message', 'Statut de l\'appareil mis à jour.');
    }

    public function deleteDevice($deviceId)
    {
        abort_if(auth()->user()->hasRole('consultant'), 403);
        $device = Device::findOrFail($deviceId);
        $device->delete(); // Soft delete
        
        session()->flash('warning', 'L\'appareil a été archivé.');
    }

    public function pingAllDevices()
    {
        abort_if(auth()->user()->hasRole('consultant'), 403);
        $query = Device::query();
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('ip_address', 'like', '%' . $this->search . '%');
            });
        }
        if ($this->type) $query->where('type', $this->type);
        if ($this->status) $query->where('is_active', $this->status === 'active');
        if ($this->selectedAgencyId) $query->where('agency_id', $this->selectedAgencyId);

        $devices = $query->latest()->get();

        foreach ($devices as $device) {
            \App\Jobs\PingDeviceJob::dispatch($device, 1)->onQueue('scan');
        }

        $this->isCheckingAll = true;
        session()->flash('message', 'Actualisation automatique lancée pour ' . $devices->count() . ' appareils.');
    }

    public function stopChecking()
    {
        $this->isCheckingAll = false;
    }

    public function checkScanProgress()
    {
        $jobsCount = \Illuminate\Support\Facades\DB::table('jobs')
            ->where('payload', 'like', '%PingDeviceJob%')
            ->count();

        if ($jobsCount === 0) {
            $this->isCheckingAll = false;
            session()->flash('message', 'Actualisation terminée.');
        }
    }
}
