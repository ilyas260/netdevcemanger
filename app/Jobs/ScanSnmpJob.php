<?php

namespace App\Jobs;

use App\Models\Device;
use App\Models\ScanResult;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScanSnmpJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $ips;
    public $scanId;
    public int $timeout = 300;
    public int $tries   = 1;

    public function __construct(array $ips, $scanId)
    {
        $this->ips    = $ips;
        $this->scanId = $scanId;
    }

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) return;

        putenv('MIBS=none');
        if (function_exists('snmp_set_oid_numeric_print')) {
            @snmp_set_oid_numeric_print(1);
        }

        Log::info("[Scanner] Batch de " . count($this->ips) . " IP(s). Scan ID: {$this->scanId}");

        // ─── Étape 1 : Un seul Nmap pour tout le batch ─────────────────────────
        $activeIps = $this->nmapBatchScan($this->ips);
        Log::info("[Scanner] Nmap batch: " . count($activeIps) . " hôte(s) actif(s) sur " . count($this->ips));

        if (empty($activeIps)) {
            // Aucun hôte actif dans ce batch, on insère tous en offline
            $this->saveAllResults($this->ips, [], []);
            return;
        }

        // ─── Étape 2 : ARP table (une seule fois pour le batch) ────────────────
        $arpTable = $this->getArpTable();

        // ─── Étape 3 : Charger les devices existants ──────────────────────────
        $existingDevices = Device::whereIn('ip_address', $this->ips)->get()->keyBy('ip_address');

        // ─── Étape 4 : Enregistrer tous les résultats (actifs et inactifs) ─────
        $this->saveAllResults($this->ips, $activeIps, $arpTable, $existingDevices);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Scan Nmap en batch : une seule commande pour toutes les IP du lot
    // ────────────────────────────────────────────────────────────────────────
    private function nmapBatchScan(array $ips): array
    {
        $nmap = PHP_OS_FAMILY === 'Windows'
            ? '"C:\\Program Files (x86)\\Nmap\\nmap.exe"'
            : 'nmap';

        // On cherche l'IP source locale dans le réseau 10.110.x.x pour forcer le bon adaptateur
        $sourceIp = $this->getAgencySourceIp();
        $sFlag = $sourceIp ? "-S {$sourceIp}" : "";

        // -Pn : skip host discovery (nécessaire sans privilèges admin sur Windows)
        // -sT : TCP Connect scan (fonctionne sans privilèges admin)
        // -T4 : Profil de scan rapide
        // Ports : SSH (22), web, NetBIOS/SMB, SNMP, RDP (3389) et jetdirect
        $ipList = implode(' ', $ips);
        $cmd = "{$nmap} -R -Pn -sT -T5 -p 22,80,443,135,139,445,161,3389,9100 --max-rtt-timeout 250ms --max-retries 0 --host-timeout 5s --min-rate 1000 {$ipList} 2>&1";

        Log::debug("[Scanner] Nmap cmd: $cmd");
        $output = @shell_exec($cmd);

        $active = [];
        if (!$output || !str_contains($output, 'Nmap scan report for')) {
            Log::warning("[Scanner] Nmap non disponible ou sans rapport valide. Utilisation du port check direct.");
            // Fallback : port check rapide si Nmap échoue
            foreach ($ips as $ip) {
                if ($this->fastPortCheck($ip)) {
                    $dnsName = @gethostbyaddr($ip);
                    
                    // Détection d'imprimante via ports d'impression ouverts (9100, 515, 631)
                    $isPrinter = false;
                    foreach ([9100, 515, 631] as $printPort) {
                        $fp = @fsockopen($ip, $printPort, $errno, $errstr, 0.15);
                        if ($fp) {
                            fclose($fp);
                            $isPrinter = true;
                            break;
                        }
                    }

                    $active[$ip] = [
                        'hostname'   => ($dnsName && $dnsName !== $ip) ? $dnsName : null,
                        'mac'        => null,
                        'is_printer' => $isPrinter
                    ];
                }
            }
            return $active;
        }

        // Parser la sortie Nmap
        // Exemple: "Nmap scan report for HOSTNAME (192.168.1.1)"
        // ou:      "Nmap scan report for 192.168.1.1"
        $sections = preg_split('/Nmap scan report for /i', $output);
        foreach ($sections as $section) {
            if (empty(trim($section))) continue;

            $lines = explode("\n", trim($section));
            $firstLine = trim($lines[0]);

            // Un hôte est considéré comme actif s'il a au moins un port 'open' ou 'closed'
            // (les ports 'filtered' signifient qu'il n'y a pas eu de réponse du tout, donc offline)
            $isUp = false;
            foreach ($lines as $line) {
                if (preg_match('/(open|closed)\s+/i', $line)) {
                    $isUp = true;
                    break;
                }
            }
            if (!$isUp) continue;

            // Détecter si le port 9100 (jetdirect) est open (signature classique d'imprimante réseau)
            $isPrinter = false;
            foreach ($lines as $line) {
                if (preg_match('/9100\/tcp\s+open/i', $line)) {
                    $isPrinter = true;
                    break;
                }
            }

            // Extraire IP et hostname
            $hostname = null;
            $ip = null;
            $mac = null;

            // Format: "HOSTNAME (192.168.1.1)"
            if (preg_match('/^(.+?)\s+\((\d{1,3}(?:\.\d{1,3}){3})\)/', $firstLine, $m)) {
                $hostname = trim($m[1]);
                $ip = $m[2];
            }
            // Format: "192.168.1.1"
            elseif (preg_match('/^(\d{1,3}(?:\.\d{1,3}){3})/', $firstLine, $m)) {
                $ip = $m[1];
            }

            // Fallback reverse DNS lookup si le hostname de Nmap est absent, est égal à l'IP, ou est "Inconnu"
            if ($ip && (!$hostname || $hostname === $ip || stripos($hostname, 'inconnu') !== false)) {
                $dnsName = false; // Désactivé car trop lent: @gethostbyaddr($ip);
                if ($dnsName && $dnsName !== $ip) {
                    $hostname = $dnsName;
                }
            }

            // Script results (NetBIOS name & MAC) si disponibles
            if (preg_match('/nbstat: NetBIOS name: ([^,]+)/i', $section, $m)) {
                $hostname = trim($m[1]);
            }
            if (preg_match('/Computer name: ([^\r\n]+)/i', $section, $m)) {
                $hostname = trim($m[1]);
            }
            if (preg_match('/NetBIOS MAC: ([0-9A-F:]{17})/i', $section, $m)) {
                $mac = strtoupper(str_replace('-', ':', $m[1]));
            }

            if ($ip && in_array($ip, $this->ips)) {
                $active[$ip] = [
                    'hostname'   => $hostname,
                    'mac'        => $mac,
                    'is_printer' => $isPrinter
                ];
                Log::debug("[Scanner] Nmap a trouvé: $ip" . ($hostname ? " ($hostname)" : '') . ($mac ? " [$mac]" : '') . ($isPrinter ? " [Imprimante détectée]" : ""));
            }
        }

        return $active;
    }

    // ────────────────────────────────────────────────────────────────────────
    // SNMP enrichment rapide (timeout 400ms, 0 retry)
    // ────────────────────────────────────────────────────────────────────────
    /**
     * SNMP enrichment robuste (v2c puis v1)
     */
    private function snmpEnrich(string $ip): ?array
    {
        if (!function_exists('snmp2_get')) return null;

        $hostname = null;
        $vendor   = null;
        $mac      = null;

        // On essaie SNMP v2c, puis v1 si échec
        $versions = ['2c', '1'];
        foreach ($versions as $ver) {
            try {
                $getFunc = ($ver === '2c') ? 'snmp2_get' : 'snmpget';
                $walkFunc = ($ver === '2c') ? 'snmp2_real_walk' : 'snmprealwalk';

                // sysName
                $rawName = @$getFunc($ip, 'public', '.1.3.6.1.2.1.1.5.0', 200000, 0);
                if ($rawName) {
                    $hostname = $this->cleanSnmpValue($rawName);
                    
                    // Si on a le nom, on tente le reste
                    $rawDescr = @$getFunc($ip, 'public', '.1.3.6.1.2.1.1.1.0', 200000, 0);
                    if ($rawDescr) $vendor = $this->cleanSnmpValue($rawDescr);

                    $macs = @$walkFunc($ip, 'public', '.1.3.6.1.2.1.2.2.1.6', 200000, 0);
                    if ($macs) {
                        foreach ($macs as $raw) {
                            if (preg_match('/([0-9A-F]{2}[: ]){5}[0-9A-F]{2}/i', $raw, $m)) {
                                $mac = strtoupper(str_replace(' ', ':', $m[0]));
                                break;
                            }
                        }
                    }
                    break; // Succès avec cette version
                }
            } catch (\Exception $e) {}
        }

        if (!$hostname && !$vendor) return null;
        return compact('hostname', 'vendor', 'mac');
    }

    // ────────────────────────────────────────────────────────────────────────
    // Port check rapide (fallback si Nmap échoue)
    // ────────────────────────────────────────────────────────────────────────
    private function fastPortCheck(string $ip): bool
    {
        // On ajoute le port 161 (SNMP) et on augmente légèrement le timeout
        foreach ([161, 80, 443, 9100, 515, 631] as $port) {
            $fp = @fsockopen($ip, $port, $errno, $errstr, 0.1);
            if ($fp) {
                fclose($fp);
                return true;
            }
        }
        return false;
    }

    /**
     * Tente de trouver l'IP locale qui appartient au réseau des agences (10.x.x.x)
     */
    private function getAgencySourceIp(): ?string
    {
        $output = @shell_exec('ipconfig');
        if (!$output) return null;

        // On cherche une ligne "Adresse IPv4" qui commence par 10.
        if (preg_match_all('/Adresse IPv4.*?:\s*(10\.\d{1,3}\.\d{1,3}\.\d{1,3})/', $output, $matches)) {
            // On privilégie celle qui est dans le sous-réseau 10.110 s'il y en a plusieurs
            foreach ($matches[1] as $ip) {
                if (str_starts_with($ip, '10.110.')) return $ip;
            }
            return $matches[1][0]; // Sinon la première en 10.x
        }
        return null;
    }

    // ────────────────────────────────────────────────────────────────────────
    // ARP table (une fois par batch)
    // ────────────────────────────────────────────────────────────────────────
    private function getArpTable(): array
    {
        $table  = [];
        $output = @shell_exec(PHP_OS_FAMILY === 'Windows' ? 'arp -a' : 'arp -an');
        if (!$output) return $table;

        preg_match_all(
            '/(\d{1,3}(?:\.\d{1,3}){3})\s+([0-9a-f]{2}[:\-][0-9a-f]{2}[:\-][0-9a-f]{2}[:\-][0-9a-f]{2}[:\-][0-9a-f]{2}[:\-][0-9a-f]{2})/i',
            $output, $matches, PREG_SET_ORDER
        );
        foreach ($matches as $m) {
            $table[$m[1]] = strtoupper(str_replace('-', ':', $m[2]));
        }
        return $table;
    }

    /**
     * Sauvegarder TOUTES les IP du batch (pour que la barre de progression avance à 100%)
     */
    private function saveAllResults(array $ips, array $activeIps, array $arpTable, $existingDevices = null): void
    {
        if ($existingDevices === null) {
            $existingDevices = Device::whereIn('ip_address', $ips)->get()->keyBy('ip_address');
        }

        $results = [];
        foreach ($ips as $ip) {
            $existing = $existingDevices->get($ip);
            $isActive = isset($activeIps[$ip]);
            
            if ($isActive) {
                $nmapData = $activeIps[$ip];
                $hostname = $nmapData['hostname'] ?? null;
                if (!$hostname || $hostname === $ip || stripos($hostname, 'inconnu') !== false) {
                    $dnsName = @gethostbyaddr($ip);
                    $hostname = ($dnsName && $dnsName !== $ip) ? $dnsName : ($existing?->name ?? 'Inconnu');
                }
                $isPrinter = $nmapData['is_printer'] ?? false;
                $vendor   = $existing ? trim($existing->brand . ' ' . $existing->model) : ($isPrinter ? 'Imprimante Réseau' : 'Appareil Actif');
                $mac      = $nmapData['mac'] ?? ($arpTable[$ip] ?? ($existing?->mac_address ?? 'N/A'));
                $status   = 'online';

                // SNMP enrichment pour les nouveaux
                if (!$existing) {
                    $snmpData = $this->snmpEnrich($ip);
                    if ($snmpData) {
                        if ($snmpData['hostname']) $hostname = $snmpData['hostname'];
                        if ($snmpData['vendor'])   $vendor   = $snmpData['vendor'];
                        if ($snmpData['mac'])      $mac      = $snmpData['mac'];
                        $status = 'snmp_ok';
                    }
                }
            } else {
                // Offline
                $hostname = $existing?->name ?? 'Inconnu';
                $mac      = $existing?->mac_address ?? 'N/A';
                $vendor   = $existing ? trim($existing->brand . ' ' . $existing->model) : 'N/A';
                $status   = 'offline';
            }

            $results[] = [
                'scan_id'       => $this->scanId,
                'ip_address'    => $ip,
                'hostname'      => $hostname ?: 'Inconnu',
                'mac_address'   => $mac ?: 'N/A',
                'vendor'        => $vendor,
                'status'        => $status,
                'exists_in_db'  => $existing !== null,
                'existing_name' => $existing?->name,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }

        if (!empty($results)) {
            ScanResult::upsert($results, ['scan_id', 'ip_address'],
                ['hostname', 'mac_address', 'vendor', 'status', 'exists_in_db', 'existing_name', 'updated_at']
            );
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // Nettoyage valeur SNMP
    // ────────────────────────────────────────────────────────────────────────
    private function cleanSnmpValue($value): string
    {
        if (!$value) return '';
        $value = (string) $value;

        if (stripos($value, 'hex-string') !== false) {
            $hex = trim(substr($value, strpos($value, ':') + 1));
            if (preg_match('/^([0-9A-Fa-f]{2}\s?)+$/', $hex)) {
                return strtoupper(str_replace(' ', ':', trim($hex)));
            }
        }

        if (str_contains($value, ':')) {
            $value = trim(substr($value, strpos($value, ':') + 1));
        }

        return mb_convert_encoding(trim($value, '" '), 'UTF-8', 'UTF-8');
    }
}