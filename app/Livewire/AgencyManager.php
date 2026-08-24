<?php

namespace App\Livewire;

use App\Models\Agency;
use App\Jobs\AgencyPingJob;
use App\Services\PingService;
use Livewire\Component;
use Livewire\WithPagination;

class AgencyManager extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editingAgencyId = null;
    public $isCheckingAll = false;

    // Form fields
    public $name;
    public $router_ip;
    public $network_address;
    public $location;
    public $nd_technique;
    public $debit_cible;
    public $hostname;
    public $phone;

    protected $rules = [
        'name' => 'required|string|max:255',
        'router_ip' => 'required|ip',
        'network_address' => 'nullable|string|max:255',
        'location' => 'nullable|string|max:255',
        'nd_technique' => 'nullable|string|max:255',
        'debit_cible' => 'nullable|string|max:255',
        'hostname' => 'nullable|string|max:255',
        'phone' => 'nullable|string|max:20',
    ];

    public function render()
    {
        $query = Agency::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('router_ip', 'like', '%' . $this->search . '%')
                  ->orWhere('hostname', 'like', '%' . $this->search . '%')
                  ->orWhere('status', 'like', '%' . $this->search . '%')
                  ->orWhere('location', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%')
                  ->orWhere('nd_technique', 'like', '%' . $this->search . '%')
                  ->orWhere('debit_cible', 'like', '%' . $this->search . '%');
            });
        }

        $agencies = $query->latest()->paginate(10);

        return view('livewire.agency-manager', [
            'agencies' => $agencies
        ])->layout('layouts.app');
    }

    public function openModal()
    {
        abort_if(auth()->user()->hasRole('consultant'), 403);
        $this->resetErrorBag();
        $this->reset(['name', 'router_ip', 'network_address', 'location', 'phone', 'nd_technique', 'debit_cible', 'hostname', 'editingAgencyId']);
        $this->showModal = true;
    }

    public function editAgency($id)
    {
        $agency = Agency::findOrFail($id);
        $this->editingAgencyId = $id;
        $this->name = $agency->name;
        $this->router_ip = $agency->router_ip;
        $this->network_address = $agency->network_address;
        $this->location = $agency->location;
        $this->phone = $agency->phone;
        $this->nd_technique = $agency->nd_technique;
        $this->debit_cible = $agency->debit_cible;
        $this->hostname = $agency->hostname;
        $this->showModal = true;
    }

    public function updatedRouterIp($value)
    {
        if (filter_var($value, FILTER_VALIDATE_IP)) {
            $parts = explode('.', $value);
            if (count($parts) === 4) {
                if ($parts[0] === '10' && $parts[1] === '0' && $parts[2] === '0') {
                    $this->network_address = '10.0.0.0/8';
                } else {
                    $this->network_address = $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.0/24';
                }
            }
        }
    }

    public function saveAgency()
    {
        abort_if(auth()->user()->hasRole('consultant'), 403);
        $this->validate();

        Agency::updateOrCreate(
            ['id' => $this->editingAgencyId],
            [
                'name' => $this->name,
                'router_ip' => $this->router_ip,
                'network_address' => $this->network_address,
                'location' => $this->location,
                'phone' => $this->phone,
                'nd_technique' => $this->nd_technique,
                'debit_cible' => $this->debit_cible,
                'hostname' => $this->hostname,
            ]
        );

        $this->showModal = false;
        session()->flash('message', $this->editingAgencyId ? 'Agence mise à jour.' : 'Agence créée.');
    }

    public function deleteAgency($id)
    {
        abort_if(auth()->user()->hasRole('consultant'), 403);
        Agency::findOrFail($id)->delete();
        session()->flash('message', 'Agence supprimée.');
    }

    public function pingAgency($id)
    {
        abort_if(auth()->user()->hasRole('consultant'), 403);
        $agency = Agency::findOrFail($id);
        AgencyPingJob::dispatch($agency)->onQueue('scan');
        session()->flash('message', 'Test de connectivité lancé en arrière-plan pour ' . $agency->name);
    }

    public function pingAllAgencies(PingService $pingService)
    {
        abort_if(auth()->user()->hasRole('consultant'), 403);
        $this->isCheckingAll = true;
        
        $query = Agency::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('router_ip', 'like', '%' . $this->search . '%')
                  ->orWhere('hostname', 'like', '%' . $this->search . '%')
                  ->orWhere('status', 'like', '%' . $this->search . '%')
                  ->orWhere('location', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%')
                  ->orWhere('nd_technique', 'like', '%' . $this->search . '%')
                  ->orWhere('debit_cible', 'like', '%' . $this->search . '%');
            });
        }

        $agencies = $query->get();

        foreach ($agencies as $agency) {
            AgencyPingJob::dispatch($agency, 1)->onQueue('scan');
        }

        $this->isCheckingAll = false;
        session()->flash('message', 'Mise à jour automatique des statuts terminée.');
    }
}
