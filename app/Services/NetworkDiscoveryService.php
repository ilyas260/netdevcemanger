<?php

namespace App\Services;

class NetworkDiscoveryService
{
    /**
     * Calcule la liste des IPs à partir d'une plage CIDR ou d'une base d'IP.
     */
    public function getIpRange(string $input): array
    {
        $input = trim($input);
        
        // Check if it's already a CIDR range
        if (str_contains($input, '/')) {
            list($ip, $mask) = explode('/', $input);
            $mask = (int)$mask;
        } else {
            // No slash present.
            // If it is a 3-octet base like "192.168.1"
            if (preg_match('/^\d+\.\d+\.\d+$/', $input)) {
                $ip = $input . '.0';
                $mask = 24;
            } elseif (preg_match('/^\d+\.\d+\.\d+\.\d+$/', $input)) {
                // If it is a full IP like "10.110.82.0"
                $ip = $input;
                $mask = 24;
            } else {
                return [];
            }
        }

        $ipLong = ip2long($ip);
        if ($ipLong === false) {
            return [];
        }

        if ($mask < 0 || $mask > 32) {
            return [];
        }

        if ($mask === 32) {
            return [$ip];
        }

        // Calculate network and broadcast addresses
        if ($mask === 0) {
            $network = 0;
            $broadcast = 0xffffffff;
        } else {
            $network = $ipLong & (~((1 << (32 - $mask)) - 1));
            $broadcast = $network | ((1 << (32 - $mask)) - 1);
        }

        $start = $network + 1;
        $end = $broadcast - 1;

        if ($start > $end) {
            return [];
        }

        // Limit the generated IPs to prevent memory exhaustion (e.g. max 2048 IPs)
        $maxIps = 2048;
        $ips = [];
        for ($i = $start; $i <= $end; $i++) {
            $ips[] = long2ip($i);
            if (count($ips) >= $maxIps) {
                break;
            }
        }

        return $ips;
    }

    /**
     * Vérifie si un port UDP est potentiellement ouvert.
     * Note: UDP est sans connexion, donc on ne peut pas être sûr à 100% sans envoyer de données.
     * Mais on peut tester la création du socket.
     */
    public function isUdpPortOpen(string $ip, int $port, int $timeoutMs = 100): bool
    {
        $socket = @fsockopen("udp://$ip", $port, $errno, $errstr, $timeoutMs / 1000);
        if ($socket) {
            fclose($socket);
            return true;
        }
        return false;
    }
}
