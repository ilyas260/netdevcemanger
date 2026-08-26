<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Device;
use App\Models\ScanResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ScanSyncController extends Controller
{
    /**
     * Vérifie le jeton d'authentification de l'agent.
     */
    protected function validateToken(Request $request): bool
    {
        $expectedToken = config('app.agent_token', env('SCAN_AGENT_TOKEN', 'netdevice-secret-token-2026'));
        $providedToken = $request->header('X-Agent-Token') ?? $request->input('token');

        return $providedToken && hash_equals((string) $expectedToken, (string) $providedToken);
    }

    /**
     * Liste des agences pour l'agent local.
     */
    public function agencies(Request $request): JsonResponse
    {
        if (!$this->validateToken($request)) {
            return response()->json(['error' => 'Non autorisé. Jeton invalide.'], 401);
        }

        $agencies = Agency::orderBy('name')->get(['id', 'name', 'network_address', 'router_ip', 'location']);

        return response()->json([
            'success'  => true,
            'agencies' => $agencies,
        ]);
    }

    /**
     * Synchronise les équipements scannés en local par l'agent vers la BDD Cloud.
     */
    public function sync(Request $request): JsonResponse
    {
        if (!$this->validateToken($request)) {
            return response()->json(['error' => 'Non autorisé. Jeton invalide.'], 401);
        }

        $validated = $request->validate([
            'agency_id' => 'required|exists:agencies,id',
            'scan_id'   => 'nullable|string',
            'devices'   => 'required|array',
            'devices.*.ip_address' => 'required|string',
            'devices.*.hostname'   => 'nullable|string',
            'devices.*.mac'        => 'nullable|string',
            'devices.*.mac_address'=> 'nullable|string',
            'devices.*.vendor'     => 'nullable|string',
            'devices.*.type'       => 'nullable|string',
            'devices.*.is_printer' => 'nullable|boolean',
            'devices.*.status'     => 'nullable|string',
        ]);

        $agency = Agency::findOrFail($validated['agency_id']);
        $scanId = $validated['scan_id'] ?? ('agent_scan_' . uniqid());
        $devices = $validated['devices'];

        $createdCount = 0;
        $updatedCount = 0;
        $scanResults  = [];

        foreach ($devices as $dev) {
            $ip = trim($dev['ip_address']);
            $hostname = !empty($dev['hostname']) && $dev['hostname'] !== $ip ? trim($dev['hostname']) : null;
            $mac = $dev['mac'] ?? ($dev['mac_address'] ?? null);
            if ($mac === 'N/A') $mac = null;
            $vendor = !empty($dev['vendor']) && $dev['vendor'] !== 'N/A' ? trim($dev['vendor']) : null;
            $isPrinter = !empty($dev['is_printer']);

            // Déduire le type
            $type = $dev['type'] ?? ($isPrinter ? 'Imprimante' : 'Autre');

            // Recherche si déjà présent dans l'agence ou par IP
            $existing = Device::where('ip_address', $ip)
                ->where('agency_id', $agency->id)
                ->first();

            if ($existing) {
                $existing->update(array_filter([
                    'name'         => $hostname ?: $existing->name,
                    'brand'        => $vendor ?: $existing->brand,
                    'type'         => $type !== 'Autre' ? $type : $existing->type,
                    'mac_address'  => $mac ?: $existing->mac_address,
                    'status'       => 'Actif',
                    'is_active'    => true,
                    'last_seen_at' => now(),
                ]));
                $updatedCount++;
            } else {
                Device::create([
                    'agency_id'    => $agency->id,
                    'name'         => $hostname ?: ($isPrinter ? "Imprimante ($ip)" : "Appareil ($ip)"),
                    'ip_address'   => $ip,
                    'type'         => $type,
                    'brand'        => $vendor ?: ($isPrinter ? 'Imprimante Réseau' : null),
                    'mac_address'  => $mac,
                    'status'       => 'Actif',
                    'is_active'    => true,
                    'last_seen_at' => now(),
                ]);
                $createdCount++;
            }

            // Préparer pour ScanResult
            $scanResults[] = [
                'scan_id'       => $scanId,
                'ip_address'    => $ip,
                'hostname'      => $hostname ?: 'Inconnu',
                'mac_address'   => $mac ?: 'N/A',
                'vendor'        => $vendor ?: ($isPrinter ? 'Imprimante Réseau' : 'Appareil Actif'),
                'status'        => 'online',
                'exists_in_db'  => true,
                'existing_name' => $hostname ?: ($existing?->name ?? 'Appareil ' . $ip),
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }

        // Sauvegarder dans ScanResult pour affichage immédiat dans le scanner
        if (!empty($scanResults)) {
            ScanResult::upsert(
                $scanResults,
                ['scan_id', 'ip_address'],
                ['hostname', 'mac_address', 'vendor', 'status', 'exists_in_db', 'existing_name', 'updated_at']
            );
        }

        // Actualiser la date de dernier scan de l'agence si la colonne existe
        if (\Illuminate\Support\Facades\Schema::hasColumn('agencies', 'last_scan_at')) {
            $agency->update(['last_scan_at' => now()]);
        }

        Log::info("[Scan Agent] Synchronisation agence '{$agency->name}' : $createdCount nouveau(x), $updatedCount mis à jour.");

        return response()->json([
            'success'        => true,
            'message'        => "Scan synchronisé avec succès pour l'agence {$agency->name}",
            'agency'         => $agency->name,
            'total_received' => count($devices),
            'created'        => $createdCount,
            'updated'        => $updatedCount,
            'scan_id'        => $scanId,
        ]);
    }
}
