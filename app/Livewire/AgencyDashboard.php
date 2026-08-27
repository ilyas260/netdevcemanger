<?php

namespace App\Livewire;

use App\Models\Agency;
use App\Models\Device;
use App\Services\PingService;
use Livewire\Component;

class AgencyDashboard extends Component
{
    public Agency $agency;

    public function mount($id)
    {
        $this->agency = Agency::with('devices')->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.agency-dashboard', [
            'devices' => $this->agency->devices()->whereNotIn('type', ['routeur', 'router'])->latest()->get()
        ])->layout('layouts.app');
    }

    public function pingDevice($deviceId, PingService $pingService)
    {
        abort_if(auth()->user()->hasRole('consultant'), 403);
        $device = Device::findOrFail($deviceId);
        $pingService->executePing($device);
        $this->agency->load('devices'); // Refresh devices status
        session()->flash('message', "Ping effectué pour {$device->name}");
    }

    public function pingRouter(PingService $pingService)
    {
        abort_if(auth()->user()->hasRole('consultant'), 403);
        $pingService->executeAgencyPing($this->agency);
        $this->agency->refresh();
        session()->flash('message', "Ping effectué pour le routeur de {$this->agency->name}");
    }

    public function deleteDevice($deviceId)
    {
        abort_if(auth()->user()->hasRole('consultant'), 403);
        $device = Device::findOrFail($deviceId);
        $device->delete();
        $this->agency->load('devices'); // Refresh list
        session()->flash('message', "L'appareil {$device->name} a été supprimé de l'agence.");
    }
}
