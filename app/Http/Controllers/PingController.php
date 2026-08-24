<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Http\Requests\LaunchPingRequest;
use App\Jobs\PingDeviceJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PingController extends Controller
{
    /**
     * Dispatch a ping job for a device.
     */
    public function launch(Device $device, LaunchPingRequest $request): RedirectResponse
    {
        $duration = $request->input('duration_sec', 4);

        // Dispatch le Job en file d'attente
        PingDeviceJob::dispatch($device, (int) $duration);

        return back()->with('info', "Test de connectivité lancé pour {$device->name} (IP: {$device->ip_address}).");
    }

    /**
     * Display ping history for a device.
     */
    public function history(Device $device): View
    {
        $logs = $device->pingLogs()->latest('tested_at')->paginate(20);

        return view('ping.history', compact('device', 'logs'));
    }
}
