<?php

namespace App\Console\Commands;

use App\Models\Agency;
use App\Services\NetworkDiscoveryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ScanAgentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scan:agent 
                            {agency_id? : ID ou nom de l\'agence à scanner}
                            {--range= : Plage IP à scanner (ex: 192.168.1.0/24 ou 10.110.82.0/24)}
                            {--server= : URL du serveur Cloud (défaut: Clever Cloud)}
                            {--token= : Jeton secret de l\'API}
                            {--local-only : Sauvegarder uniquement dans la base locale}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Agent de scan réseau local pour découvrir les équipements et les synchroniser avec Clever Cloud';

    public function handle(NetworkDiscoveryService $discoveryService): int
    {
        $this->info("╔════════════════════════════════════════════════════════════════╗");
        $this->info("║        🛰️  NETDEVICE MANAGER - AGENT DE SCAN LOCAL            ║");
        $this->info("╚════════════════════════════════════════════════════════════════╝");
        $this->newLine();

        $defaultServer = env('AGENT_SERVER_URL', 'https://app-59545d29-0afa-49d5-aea7-9e11de966706.cleverapps.io');
        $server = rtrim($this->option('server') ?: $defaultServer, '/');
        $token = $this->option('token') ?: env('SCAN_AGENT_TOKEN', 'netdevice-secret-token-2026');
        $localOnly = $this->option('local-only');

        // 1. Détermination de l'agence
        $agencyId = $this->argument('agency_id');
        $agencyName = null;
        $ipRange = $this->option('range');

        // Tentative de récupération des agences distantes ou locales
        $agencies = [];
        try {
            $response = Http::withHeaders(['X-Agent-Token' => $token])
                ->timeout(5)
                ->get("$server/api/agencies");

            if ($response->successful() && isset($response->json()['agencies'])) {
                $agencies = $response->json()['agencies'];
            }
        } catch (\Exception $e) {
            // Fallback base locale
            $agencies = Agency::all(['id', 'name', 'network_address'])->toArray();
        }

        if (!$agencyId && !empty($agencies)) {
            $choices = [];
            foreach ($agencies as $a) {
                $choices[$a['id']] = "{$a['name']} (" . ($a['network_address'] ?? 'Plage non définie') . ")";
            }
            $selectedChoice = $this->choice("Sélectionnez l'agence à scanner :", $choices);
            $agencyId = array_search($selectedChoice, $choices);
        }

        // Trouver les détails de l'agence sélectionnée
        if ($agencyId) {
            foreach ($agencies as $a) {
                if ($a['id'] == $agencyId || strcasecmp($a['name'], $agencyId) === 0) {
                    $agencyId = $a['id'];
                    $agencyName = $a['name'];
                    if (!$ipRange && !empty($a['network_address'])) {
                        $ipRange = $a['network_address'];
                    } elseif (!$ipRange && !empty($a['router_ip'])) {
                        $ipRange = preg_replace('/\.\d+$/', '.0/24', $a['router_ip']);
                    }
                    break;
                }
            }
        }

        if (!$ipRange) {
            $ipRange = $this->ask("Entrez la plage IP du réseau local à scanner (ex: 192.168.1.0/24 ou 10.110.82.0/24) :", '192.168.1.0/24');
        }

        $this->line("📍 <fg=cyan>Agence ciblée :</> " . ($agencyName ?: "Agence ID #$agencyId"));
        $this->line("🌐 <fg=cyan>Plage IP :</> $ipRange");
        $this->line("☁️ <fg=cyan>Serveur Cloud :</> $server");
        $this->newLine();

        // 2. Calcul des adresses IP
        $ips = $discoveryService->getIpRange($ipRange);
        if (empty($ips)) {
            $this->error("❌ Plage IP invalide ou vide : $ipRange");
            return self::FAILURE;
        }

        $this->info("🔍 Lancement du scan de " . count($ips) . " adresses IP en local...");
        $this->output->progressStart(count($ips));

        $discoveredDevices = [];
        $chunks = array_chunk($ips, 30);

        foreach ($chunks as $chunk) {
            $chunkResults = $this->scanBatch($chunk);
            foreach ($chunkResults as $ip => $data) {
                $discoveredDevices[] = [
                    'ip_address' => $ip,
                    'hostname'   => $data['hostname'] ?? null,
                    'mac'        => $data['mac'] ?? null,
                    'vendor'     => $data['vendor'] ?? null,
                    'type'       => $data['type'] ?? ($data['is_printer'] ? 'Imprimante' : 'Autre'),
                    'is_printer' => $data['is_printer'] ?? false,
                    'status'     => 'Actif',
                ];
            }
            $this->output->progressAdvance(count($chunk));
        }

        $this->output->progressFinish();
        $this->newLine();

        // 3. Affichage du tableau des résultats
        $this->info("✅ Scan local terminé ! " . count($discoveredDevices) . " équipement(s) actif(s) détecté(s).");
        $this->newLine();

        if (empty($discoveredDevices)) {
            $this->warn("⚠️  Aucun équipement actif n'a été détecté sur cette plage.");
            $this->line("Vérifiez que votre PC est bien connecté à ce réseau ou que les pare-feu autorisent le scan.");
            return self::SUCCESS;
        }

        $tableRows = [];
        foreach ($discoveredDevices as $d) {
            $tableRows[] = [
                $d['ip_address'],
                $d['hostname'] ?: '<fg=gray>Inconnu</>',
                $d['mac'] ?: '<fg=gray>N/A</>',
                $d['vendor'] ?: '<fg=gray>Inconnu</>',
                $d['type'],
            ];
        }
        $this->table(['Adresse IP', 'Nom / Hostname', 'Adresse MAC', 'Constructeur / Modèle', 'Type'], $tableRows);
        $this->newLine();

        // 4. Synchronisation avec Clever Cloud
        if ($localOnly) {
            $this->info("ℹ️ Mode local uniquement : les données n'ont pas été envoyées au Cloud.");
            return self::SUCCESS;
        }

        $this->info("🚀 Envoi des résultats vers Clever Cloud ($server)...");

        try {
            $response = Http::withHeaders([
                'X-Agent-Token' => $token,
                'Accept'        => 'application/json',
            ])->timeout(15)->post("$server/api/scan/sync", [
                'agency_id' => $agencyId ?: 1,
                'devices'   => $discoveredDevices,
            ]);

            if ($response->successful()) {
                $json = $response->json();
                $this->info("════════════════════════════════════════════════════════════════");
                $this->info("🎉 SYNCHRONISATION RÉUSSIE !");
                $this->line("   - Agence : <fg=green>" . ($json['agency'] ?? $agencyName) . "</>");
                $this->line("   - Nouveaux équipements créés : <fg=green>" . ($json['created'] ?? 0) . "</>");
                $this->line("   - Équipements mis à jour : <fg=green>" . ($json['updated'] ?? 0) . "</>");
                $this->info("════════════════════════════════════════════════════════════════");
                $this->line("👉 Vous pouvez maintenant consulter vos équipements sur le dashboard Clever Cloud !");
            } else {
                $this->error("❌ Échec de la synchronisation (Code HTTP " . $response->status() . ") : " . $response->body());
            }
        } catch (\Exception $e) {
            $this->error("❌ Erreur de communication avec Clever Cloud : " . $e->getMessage());
        }

        return self::SUCCESS;
    }

    /**
     * Scan d'un lot d'adresses IP en local (Nmap -> ARP -> Fast sockets -> SNMP).
     */
    private function scanBatch(array $ips): array
    {
        $nmap = PHP_OS_FAMILY === 'Windows'
            ? '"C:\\Program Files (x86)\\Nmap\\nmap.exe"'
            : 'nmap';

        $ipList = implode(' ', $ips);
        $cmd = "{$nmap} -R -Pn -sT -T5 -p 22,80,443,135,139,445,161,3389,9100 --script smb-os-discovery --max-rtt-timeout 250ms --max-retries 0 --host-timeout 5s --min-rate 1000 {$ipList} 2>&1";

        $output = @shell_exec($cmd);
        $active = [];

        // Si Nmap échoue ou n'est pas présent, on passe sur socket port check
        if (!$output || stripos($output, 'not recognized') !== false || stripos($output, 'not found') !== false) {
            foreach ($ips as $ip) {
                if ($this->fastPortCheck($ip)) {
                    $dns = @gethostbyaddr($ip);
                    $isPrinter = $this->checkPrinterPorts($ip);
                    $active[$ip] = [
                        'hostname'   => ($dns && $dns !== $ip) ? $dns : null,
                        'mac'        => null,
                        'vendor'     => $isPrinter ? 'Imprimante Réseau' : null,
                        'type'       => $isPrinter ? 'Imprimante' : 'Autre',
                        'is_printer' => $isPrinter,
                    ];
                }
            }
            return $active;
        }

        // Parsing Nmap
        $sections = preg_split('/Nmap scan report for /i', $output);
        $arpTable = $this->getArpTable();

        foreach ($sections as $section) {
            if (empty(trim($section))) continue;

            $lines = explode("\n", trim($section));
            $firstLine = trim($lines[0]);

            $isUp = false;
            foreach ($lines as $line) {
                if (preg_match('/(open|closed)\s+/i', $line)) {
                    $isUp = true;
                    break;
                }
            }
            if (!$isUp) continue;

            $isPrinter = false;
            foreach ($lines as $line) {
                if (preg_match('/9100\/tcp\s+open/i', $line)) {
                    $isPrinter = true;
                    break;
                }
            }

            $hostname = null;
            $ip = null;
            $mac = null;

            if (preg_match('/^(.+?)\s+\((\d{1,3}(?:\.\d{1,3}){3})\)/', $firstLine, $m)) {
                $hostname = trim($m[1]);
                $ip = $m[2];
            } elseif (preg_match('/^(\d{1,3}(?:\.\d{1,3}){3})/', $firstLine, $m)) {
                $ip = $m[1];
            }

            if ($ip && (!$hostname || $hostname === $ip || stripos($hostname, 'inconnu') !== false)) {
                $dns = @gethostbyaddr($ip);
                if ($dns && $dns !== $ip) $hostname = $dns;
            }

            if (preg_match('/nbstat: NetBIOS name: ([^,]+)/i', $section, $m)) {
                $hostname = trim($m[1]);
            }
            if (preg_match('/Computer name: ([^\r\n]+)/i', $section, $m)) {
                $hostname = trim($m[1]);
            }
            if (preg_match('/NetBIOS MAC: ([0-9A-F:]{17})/i', $section, $m)) {
                $mac = strtoupper(str_replace('-', ':', $m[1]));
            }

            if ($ip && in_array($ip, $ips)) {
                $mac = $mac ?: ($arpTable[$ip] ?? null);
                $active[$ip] = [
                    'hostname'   => $hostname,
                    'mac'        => $mac,
                    'vendor'     => $isPrinter ? 'Imprimante Réseau' : null,
                    'type'       => $isPrinter ? 'Imprimante' : ($hostname ? 'PC / Serveur' : 'Autre'),
                    'is_printer' => $isPrinter,
                ];
            }
        }

        return $active;
    }

    private function fastPortCheck(string $ip): bool
    {
        foreach ([161, 80, 443, 9100, 135, 445, 3389, 22] as $port) {
            $fp = @fsockopen($ip, $port, $errno, $errstr, 0.15);
            if ($fp) {
                fclose($fp);
                return true;
            }
        }
        return false;
    }

    private function checkPrinterPorts(string $ip): bool
    {
        foreach ([9100, 515, 631] as $port) {
            $fp = @fsockopen($ip, $port, $errno, $errstr, 0.2);
            if ($fp) {
                fclose($fp);
                return true;
            }
        }
        return false;
    }

    private function getArpTable(): array
    {
        $table = [];
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
}
