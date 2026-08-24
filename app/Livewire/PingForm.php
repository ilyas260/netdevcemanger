<?php

namespace App\Livewire;

use App\Models\Device;
use App\Models\PingLog;
use App\Jobs\PingDeviceJob;
use Livewire\Component;

class PingForm extends Component
{
    public Device $device;
    public $duration_sec = 4;
    public $is_running = false;
    public $last_log_id = null;

    public function mount(Device $device)
    {
        $this->device = $device;
    }

    public function launchPing()
    {
        $this->validate([
            'duration_sec' => 'required|integer|min:1|max:60',
        ]);

        $this->is_running = true;
        
        // Dispatch Sync pour garantir l'exécution immédiate même sans worker
        try {
            PingDeviceJob::dispatchSync($this->device, (int) $this->duration_sec);
            $this->checkResult();
            session()->flash('info', 'Ping terminé avec succès.');
        } catch (\Exception $e) {
            $this->is_running = false;
            session()->flash('error', 'Erreur lors du ping : ' . $e->getMessage());
        }
    }

    public function checkResult()
    {
        if (!$this->is_running) return;

        $lastLog = PingLog::where('device_id', $this->device->id)
            ->where('tested_at', '>=', now()->subSeconds(30))
            ->latest()
            ->first();

        if ($lastLog) {
            $this->last_log_id = $lastLog->id;
            $this->is_running = false;
        }
    }

    public function render()
    {
        $lastResult = $this->last_log_id ? PingLog::find($this->last_log_id) : null;

        return view('livewire.ping-form', [
            'lastResult' => $lastResult
        ]);
    }
}
