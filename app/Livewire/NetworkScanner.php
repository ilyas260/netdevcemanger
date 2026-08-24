<?php

namespace App\Livewire;

use App\Models\Agency;
use App\Models\Device;
use App\Models\ScanResult;
use Livewire\Component;

class NetworkScanner extends Component
{
    public $ipRange = '192.168.1.0/24';
    public $selectedAgencyId = null;
    public $agencies = [];
    
    public $isScanning = false;
    public $scanResults = [];
    public $selectedDevices = [];
    
    public $scanProgress = 0;
    public $currentIpIndex = 1;
    public $currentScanId = null;

    protected $rules = [
        'ipRange' => 'required|string',
        'selectedAgencyId' => 'required|exists:agencies,id',
    ];

    public function mount()
    {
        $this->agencies = Agency::orderBy('name')->get()->map(function($agency) {
            return [
                'id' => $agency->id,
                'name' => $this->cleanString($agency->name),
            ];
        })->toArray();

        $this->selectedAgencyId = null;
    }

    public function updatedSelectedAgencyId($value)
    {
        $this->updateIpRangeFromAgency($value);
    }

    public function updateIpRangeFromAgency($agencyId = null)
    {
        $id = $agencyId ?: $this->selectedAgencyId;
        if ($id) {
            $agency = Agency::find($id);
            if ($agency && $agency->network_address) {
                $this->ipRange = $agency->network_address;
            }
        }
    }

    public function render()
    {
        return view('livewire.network-scanner')->layout('layouts.app');
    }

    public function scanNetwork()
    {
        abort_if(auth()->user()->hasRole('consultant'), 403);
        $this->validate();

        // Nettoyer la file d'attente pour éviter les blocages avec d'anciens scans
        \Illuminate\Support\Facades\Artisan::call('queue:clear', ['--queue' => 'scan', '--force' => true]);

        $this->isScanning = true;
        $this->scanProgress = 0;
        $this->scanResults = [];
        $this->selectedDevices = [];
        $this->currentScanId = uniqid('scan_');

        // Nettoyage ancien
        ScanResult::where('created_at', '<', now()->subHours(1))->delete();

        $discoveryService = app(\App\Services\NetworkDiscoveryService::class);
        $ips = $discoveryService->getIpRange($this->ipRange);

        if (!empty($ips)) {
            $jobs = [];
            // Group IPs in chunks (e.g., 20 IPs per job) to reduce job overhead and improve parallelism
            $chunks = array_chunk($ips, 10);
            foreach ($chunks as $chunk) {
                $jobs[] = new \App\Jobs\ScanSnmpJob($chunk, $this->currentScanId);
            }

            \Illuminate\Support\Facades\Bus::batch($jobs)
                ->onQueue('scan')
                ->allowFailures()
                ->dispatch();
            
            session(['total_ips_scan_' . $this->currentScanId => count($ips)]);
        }
    }

    public function checkScanStatus()
    {
        if (!$this->isScanning) return;

        // Récupérer tous les résultats du scan courant
        $results = ScanResult::where('scan_id', $this->currentScanId)->get();

        // Afficher tous les appareils online (on exclut les passerelles .1 sur demande)
        $this->scanResults = $results->filter(function ($r) {
            return in_array($r->status, ['online', 'snmp_ok']) && !str_ends_with($r->ip_address, '.1');
        })->map(function ($r) {
            return [
                'ip'            => $r->ip_address,
                'hostname'      => $r->hostname,
                'mac'           => $r->mac_address,
                'vendor'        => $r->vendor,
                'exists'        => $r->exists_in_db,
                'existing_name' => $r->existing_name,
            ];
        })->values()->toArray();

        // Progression basée sur le nombre d'IP scannées vs total attendu
        $totalIps = session('total_ips_scan_' . $this->currentScanId, 254);
        $this->scanProgress = $totalIps > 0
            ? min(99, (int) round(($results->count() / $totalIps) * 100))
            : 0;

        // Terminer quand on a traité toutes les IPs (online + offline)
        if ($results->count() >= $totalIps && $totalIps > 0) {
            $this->scanProgress = 100;
            $this->isScanning   = false;
            
            // Ajout automatique de tous les nouveaux appareils détectés
            $this->selectAllNew();
            $this->addSelectedDevices();
        }
    }

    // L'ancienne méthode processSingleIp n'est plus nécessaire car gérée par le Job


    public function toggleDeviceSelection($ip)
    {
        if (in_array($ip, $this->selectedDevices)) {
            $this->selectedDevices = array_diff($this->selectedDevices, [$ip]);
        } else {
            $this->selectedDevices[] = $ip;
        }
    }

    public function selectAllNew()
    {
        abort_if(auth()->user()->hasRole('consultant'), 403);
        $this->selectedDevices = collect($this->scanResults)
            ->where('exists', false)
            ->pluck('ip')
            ->toArray();
    }

    public function addSelectedDevices()
    {
        abort_if(auth()->user()->hasRole('consultant'), 403);
        $this->validate(['selectedAgencyId' => 'required|exists:agencies,id']);
        if (empty($this->selectedDevices)) return;

        $agency = Agency::find($this->selectedAgencyId);
        $count = 0;

        foreach ($this->selectedDevices as $ip) {
            $info = collect($this->scanResults)->firstWhere('ip', $ip);
            if (!$info || Device::where('ip_address', $ip)->exists()) continue;
            
            $hostname = $info['hostname'] ?? '';
            $vendor = $info['vendor'] ?? '';

            // Détection intelligente du type (pc ou imprimante), de la marque et du modèle
            $type = 'pc';
            $brand = 'Inconnue';
            $model = 'N/A';

            $printerKeywords = ['hp', 'lexmark', 'brother', 'canon', 'epson', 'kyocera', 'ricoh', 'sharp', 'xerox', 'konica', 'toshiba', 'printer', 'imprimante', 'laserjet', 'officejet', 'deskjet'];
            $isPrinter = false;

            if (stripos($vendor, 'imprimante') !== false || stripos($vendor, 'printer') !== false) {
                $isPrinter = true;
            } else {
                foreach ($printerKeywords as $kw) {
                    if (stripos($vendor, $kw) !== false || stripos($hostname, $kw) !== false) {
                        $isPrinter = true;
                        break;
                    }
                }
            }

            if ($isPrinter) {
                $type = 'imprimante';
            }

            // Extraction de la marque et du modèle
            if ($vendor && $vendor !== 'Appareil Actif' && $vendor !== 'Imprimante Réseau') {
                foreach ($printerKeywords as $kw) {
                    if (stripos($vendor, $kw) !== false) {
                        $brand = ucfirst($kw);
                        $model = trim(str_ireplace($kw, '', $vendor));
                        if (strlen($model) > 100) {
                            $model = substr($model, 0, 97) . '...';
                        }
                        break;
                    }
                }
                
                if ($brand === 'Inconnue' && $vendor !== 'N/A') {
                    $model = $vendor;
                    if (strlen($model) > 100) {
                        $model = substr($model, 0, 97) . '...';
                    }
                }
            }

            Device::create([
                'name' => $hostname !== 'Inconnu' ? $hostname : ($isPrinter ? "Imprimante $ip" : "Appareil $ip"),
                'ip_address' => $ip,
                'agency_id' => $agency->id,
                'type' => $type,
                'brand' => $brand,
                'model' => $model,
                'is_active' => true,
                'snmp_community' => 'public',
            ]);
            $count++;
        }

        $this->selectedDevices = [];
        session()->flash('message', "$count appareil(s) ajouté(s).");
    }

    private function cleanString($str)
    {
        return mb_convert_encoding((string)$str, 'UTF-8', 'UTF-8');
    }
}
