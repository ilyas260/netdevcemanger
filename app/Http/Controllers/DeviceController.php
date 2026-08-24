<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Http\Requests\StoreDeviceRequest;
use App\Http\Requests\UpdateDeviceRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DeviceController extends Controller
{
    /**
     * Display a listing of the devices.
     */
    public function index(Request $request): View
    {
        $query = Device::query();

        // Filtres
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('ip_address', 'like', "%$search%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $devices = $query->withTrashed()->paginate(15)->withQueryString();

        return view('devices.index', compact('devices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        abort_if(auth()->user()->hasRole('consultant'), 403);
        $agencies = \App\Models\Agency::all();
        $selectedAgencyId = $request->query('agency_id');
        return view('devices.create', compact('agencies', 'selectedAgencyId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDeviceRequest $request): RedirectResponse
    {
        abort_if(auth()->user()->hasRole('consultant'), 403);
        $device = Device::create($request->validated());

        if ($device->agency_id) {
            return redirect()->route('agencies.show', $device->agency_id)
                ->with('success', "L'appareil a été ajouté à l'agence avec succès.");
        }

        return redirect()->route('dashboard')
            ->with('success', "L'appareil a été ajouté avec succès.");
    }

    /**
     * Display the specified device details.
     */
    public function show(Device $device): View
    {
        $device->load([
            'pingLogs' => function ($q) {
                $q->latest()->limit(10);
            },
            'printerCounters' => function ($q) {
                $q->latest()->limit(10);
            },
            'agency'
        ]);

        return view('devices.show', compact('device'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Device $device): View
    {
        abort_if(auth()->user()->hasRole('consultant'), 403);
        $agencies = \App\Models\Agency::all();
        return view('devices.edit', compact('device', 'agencies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDeviceRequest $request, Device $device): RedirectResponse
    {
        abort_if(auth()->user()->hasRole('consultant'), 403);
        $device->update($request->validated());

        if ($device->agency_id) {
            return redirect()->route('agencies.show', $device->agency_id)
                ->with('success', "L'appareil a été mis à jour.");
        }

        return redirect()->route('dashboard')
            ->with('success', "L'appareil a été mis à jour avec succès.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Device $device): RedirectResponse
    {
        abort_if(auth()->user()->hasRole('consultant'), 403);
        $agencyId = $device->agency_id;
        $device->delete();

        if ($agencyId) {
            return redirect()->route('agencies.show', $agencyId)
                ->with('warning', "L'appareil a été retiré de l'agence.");
        }

        return redirect()->route('dashboard')
            ->with('warning', "L'appareil a été archivé.");
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(int $id): RedirectResponse
    {
        abort_if(auth()->user()->hasRole('consultant'), 403);
        $device = Device::withTrashed()->findOrFail($id);
        $device->restore();
        return redirect()->route('dashboard')
            ->with('success', "L'appareil a été restauré.");
    }

    /**
     * Synchronize SNMP counters manually (for printers).
     */
    public function syncSnmp(Device $device): RedirectResponse
    {
        if ($device->type !== 'imprimante') {
            return back()->with('error', "Seules les imprimantes supportent la synchronisation SNMP.");
        }

        // Dispatch synchronous to get immediate feedback on the show page
        try {
            \App\Jobs\FetchSnmpJob::dispatchSync($device);
            return back()->with('success', "Les compteurs de l'imprimante ont été mis à jour via SNMP.");
        } catch (\Exception $e) {
            return back()->with('error', "Échec de la communication SNMP : " . $e->getMessage());
        }
    }
}
