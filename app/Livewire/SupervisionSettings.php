<?php

namespace App\Livewire;

use App\Models\Setting;
use Livewire\Component;

class SupervisionSettings extends Component
{
    public $pingInterval;

    public function mount()
    {
        $this->pingInterval = Setting::get('ping_interval', 5);
    }

    public function updatedPingInterval($value)
    {
        Setting::set('ping_interval', $value);
        session()->flash('settings_updated', 'L\'intervalle de supervision a été mis à jour.');
    }

    public function render()
    {
        return view('livewire.supervision-settings');
    }
}
