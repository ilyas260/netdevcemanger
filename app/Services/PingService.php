<?php

namespace App\Services;

use App\Models\Device;
use App\Models\PingLog;
use App\Models\Agency;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

/**
 * Service gérant les tests de connectivité (Ping) vers les équipements réseau.
 */
class PingService
{
    const DNS_SERVER = '10.110.103.200';
    const GATEWAY = '10.110.31.1';

    /**
     * Exécute une commande ping vers un appareil
     */
    public function executePing(Device $device, int $packets = 4, bool $isRetry = false): array
    {
        // 1. ON TESTE L'APPAREIL DIRECTEMENT (La hiérarchie est gérée au niveau des alertes agences)
        $ip = trim($device->ip_address);
        $pingPath = "C:\\Windows\\System32\\ping.exe";
        
        $result = Process::timeout(20)->run("$pingPath -n $packets -w 1000 $ip");
        $output = $result->output() ?: $result->errorOutput();

        $metrics = $this->parsePingOutput($output, $packets);
        $status = $this->determineStatus($metrics);
        $openPorts = [];

        // Si ICMP (ping) échoue, on vérifie les ports TCP
        if ($status === 'offline') {
            $ports = [80, 443, 8080, 22, 23, 3389];
            $portResults = $this->checkMultiplePorts($ip, $ports, 1);
            $openPorts = array_keys(array_filter($portResults, fn($s) => $s === 'online'));
            
            if (count($openPorts) > 0) {
                $status = 'online';
                $metrics['loss_pct'] = 0; // On simule qu'il n'y a pas de perte car l'appareil répond sur un port
            }
        }

        // LOGIQUE DE RETRY : Si toujours hors ligne et pas un second essai
        if ($status === 'offline' && !$isRetry) {
            sleep(1);
            return $this->executePing($device, $packets, true);
        }
        
        if ($status === 'offline') {
            $message = "Appareil injoignable (ping et ports fermés).";
        } else {
            if (count($openPorts) > 0) {
                $message = "Appareil opérationnel (Ping bloqué, mais ports ouverts: " . implode(', ', $openPorts) . ").";
            } else {
                $message = "Appareil opérationnel. Latence moyenne : {$metrics['avg_latency']}ms.";
            }
        }

        // Mise à jour de l'appareil
        $device->update([
            'status' => $status,
            'last_seen_at' => ($status !== 'offline') ? Carbon::now() : $device->last_seen_at
        ]);

        // Historique
        $log = PingLog::create([
            'device_id' => $device->id,
            'tested_at' => Carbon::now(),
            'duration_sec' => $packets,
            'packets_sent' => $metrics['sent'],
            'packets_received' => $metrics['received'],
            'packet_loss_pct' => $metrics['loss_pct'],
            'avg_latency_ms' => $metrics['avg_latency'],
            'min_latency_ms' => $metrics['min_latency'],
            'max_latency_ms' => $metrics['max_latency'],
            'status' => $status,
            'message' => $message,
            'triggered_by' => $isRetry ? 'auto-confirmation' : 'manual',
        ]);

        // NOTE : Pas d'email pour les appareils individuels selon demande utilisateur

        return [
            'status' => $status,
            'metrics' => $metrics,
            'message' => $message,
            'log_id' => $log->id
        ];
    }

    /**
     * Exécute un ping vers le routeur principal d'une agence avec diagnostic hiérarchique
     */
    public function executeAgencyPing(Agency $agency, int $packets = 4): array
    {
        $pingPath = "C:\\Windows\\System32\\ping.exe";
        $ip = trim($agency->router_ip);

        $result = Process::timeout(20)->run("$pingPath -n $packets -w 1000 $ip");
        $output = $result->output() ?: $result->errorOutput();
        
        $metrics = $this->parsePingOutput($output, $packets);
        $status = ($metrics['loss_pct'] == 100) ? 'offline' : 'online';
        $openPorts = [];

        // Si ICMP (ping) échoue, on vérifie les ports TCP de l'agence
        if ($status === 'offline') {
            $ports = [80, 443, 8080, 22, 23, 3389];
            $portResults = $this->checkMultiplePorts($ip, $ports, 1);
            $openPorts = array_keys(array_filter($portResults, fn($s) => $s === 'online'));
            
            if (count($openPorts) > 0) {
                $status = 'online';
                $metrics['loss_pct'] = 0;
            }
        }
        
        $oldStatus = $agency->status;
        $agency->update([
            'status' => $status,
            'last_ping_at' => Carbon::now()
        ]);

        // ✅ ENREGISTREMENT DU LOG DE PING EN BASE DE DONNÉES
        if ($status === 'offline') {
            $message = "Agence injoignable - routeur {$ip} ne répond pas au ping et ports fermés.";
        } else {
            if (count($openPorts) > 0) {
                $message = "Agence en ligne (Ping bloqué, mais ports ouverts: " . implode(', ', $openPorts) . ").";
            } else {
                $message = "Agence en ligne. Latence moyenne : {$metrics['avg_latency']}ms.";
            }
        }

        PingLog::create([
            'agency_id'        => $agency->id,
            'device_id'        => null,
            'tested_at'        => Carbon::now(),
            'duration_sec'     => $packets,
            'packets_sent'     => $metrics['sent'],
            'packets_received' => $metrics['received'],
            'packet_loss_pct'  => $metrics['loss_pct'],
            'avg_latency_ms'   => $metrics['avg_latency'],
            'min_latency_ms'   => $metrics['min_latency'],
            'max_latency_ms'   => $metrics['max_latency'],
            'status'           => $status,
            'message'          => $message,
            'triggered_by'     => 'scheduler',
        ]);

        $hasActiveIssue = $agency->hasActiveConnectivityIssues();

        // LOGIQUE D'ALERTE HIÉRARCHIQUE (Déclenchée si hors-ligne et aucune alerte active)
        if ($status === 'offline' && !$hasActiveIssue) {
            $connectivityService = new ConnectivityIssueService();
            $issue = $connectivityService->identifyAndRecord($agency);
            
            // L'email d'alerte n'est plus envoyé ici individuellement. 
            // Il sera envoyé groupé par la commande SendPendingConnectivityAlerts.
        } elseif ($status === 'online' && ($oldStatus === 'offline' || $hasActiveIssue)) {
            // Résoudre le problème quand la connexion revient
            $connectivityService = new ConnectivityIssueService();
            $connectivityService->resolveConnectivityIssue($agency, 'Panne Agence', 'Rétablissement automatique de la connexion.');
            
            // Résoudre aussi les autres types de pannes si elles existent
            $connectivityService->resolveConnectivityIssue($agency, 'Panne Serveur', 'Rétablissement automatique de la connexion.');
            $connectivityService->resolveConnectivityIssue($agency, 'Panne Réseau Central', 'Rétablissement automatique de la connexion.');
        }

        return [
            'status'  => $status,
            'metrics' => $metrics,
            'message' => $message,
        ];
    }

    /**
     * Effectue un diagnostic hiérarchique et envoie l'alerte appropriée
     */
    private function sendHierarchicalAlert(Agency $agency)
    {
        $typeAlerte = "Alerte : Agence {$agency->name} injoignable";
        $diagnostic = "Le routeur de l'agence ({$agency->router_ip}) ne répond pas au ping.";

        // 1. On teste le DNS central
        if ($this->checkConnectivity(self::DNS_SERVER) === 'offline') {
            $typeAlerte = "Panne Réseau Central (DNS/Backbone)";
            $diagnostic = "Le serveur DNS central (".self::DNS_SERVER.") ne répond pas. La coupure semble générale.";

            // 2. On teste la Passerelle locale si le DNS est mort
            if ($this->checkConnectivity(self::GATEWAY) === 'offline') {
                $typeAlerte = "Perte de connexion locale du Serveur";
                $diagnostic = "La passerelle locale (".self::GATEWAY.") ne répond pas. Le serveur est probablement déconnecté.";
            }
        }

        // Envoi de l'alerte aux destinataires
        $recipients = \App\Models\AlertRecipient::where('is_active', true)->get();
        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)
                    ->send(new \App\Mail\DeviceStatusAlert(
                        $agency->name, 
                        $agency->router_ip, 
                        'offline', 
                        'Agence',
                        $diagnostic
                    ));
            } catch (\Exception $e) {
                Log::error("Échec alerte mail vers {$recipient->email}: " . $e->getMessage());
            }
        }
    }

    /**
     * Analyse la sortie texte brute du ping Windows
     */
    private function parsePingOutput(string $output, int $sent): array
    {
        $utf8Output = @mb_convert_encoding($output, 'UTF-8', 'CP850') ?: $output;
        $cleanOutput = preg_replace('/[^\x20-\x7E\s]/', '', $utf8Output);
        
        $lines = explode("\n", $cleanOutput);
        $validReplies = 0;
        $latencies = [];

        foreach ($lines as $line) {
            $hasTTL = preg_match('/TTL\s*=\s*\d+/i', $line);
            $hasTime = preg_match('/(?:temps|time|ms)\s*[<=]\s*(\d+)\s*ms/i', $line, $mTime) || preg_match('/[<=]\s*(\d+)\s*ms/i', $line, $mTime);
            $hasError = preg_match('/(?:impossible|injoignable|expire|echec|failed|unreachable|depasse|perdu)/i', $line);

            if ($hasTTL && $hasTime && !$hasError) {
                $validReplies++;
                $latencies[] = (float) $mTime[1];
            }
        }

        $received = $validReplies;
        $lossPct = ($sent > 0) ? round((($sent - $received) / $sent) * 100, 1) : 100;

        return [
            'sent' => $sent,
            'received' => $received,
            'loss_pct' => $lossPct,
            'avg_latency' => count($latencies) > 0 ? array_sum($latencies) / count($latencies) : null,
            'min_latency' => count($latencies) > 0 ? min($latencies) : null,
            'max_latency' => count($latencies) > 0 ? max($latencies) : null,
        ];
    }

    private function determineStatus(array $metrics): string
    {
        if ($metrics['loss_pct'] == 100) return 'offline';
        if ($metrics['loss_pct'] > 0) return 'unstable';
        if ($metrics['avg_latency'] > 500) return 'slow';
        return 'online';
    }

    public function checkConnectivity(string $ip): string
    {
        $pingPath = "C:\\Windows\\System32\\ping.exe";
        $result = Process::run("$pingPath -n 1 -w 1000 $ip");
        $metrics = $this->parsePingOutput($result->output(), 1);
        $status = ($metrics['loss_pct'] == 100) ? 'offline' : 'online';
        
        if ($status === 'offline') {
            // DNS (53 TCP fallback), Web, etc.
            $ports = [53, 80, 443, 8080, 22, 23, 3389];
            $portResults = $this->checkMultiplePorts($ip, $ports, 1);
            if (in_array('online', $portResults)) {
                $status = 'online';
            }
        }
        
        return $status;
    }

    /**
     * Vérifie si un port spécifique (TCP) est ouvert sur une adresse IP.
     * Exemple : checkPort('192.168.1.10', 8080)
     */
    public function checkPort(string $ip, int $port, int $timeoutSeconds = 2): string
    {
        $connection = @fsockopen($ip, $port, $errno, $errstr, $timeoutSeconds);

        if (is_resource($connection)) {
            fclose($connection);
            return 'online'; // Le port est ouvert et répond
        }

        return 'offline'; // Le port est fermé ou injoignable
    }

    /**
     * Vérifie une liste de ports (TCP) sur une adresse IP.
     * Exemple : checkMultiplePorts('192.168.1.10', [80, 443, 8080, 22, 3389])
     * 
     * @return array Retourne un tableau associatif [port => 'online'|'offline']
     */
    public function checkMultiplePorts(string $ip, array $ports = [80, 443, 8080], int $timeoutSeconds = 1): array
    {
        $results = [];
        
        foreach ($ports as $port) {
            $results[$port] = $this->checkPort($ip, $port, $timeoutSeconds);
        }
        
        return $results;
    }
}
