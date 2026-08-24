<?php

namespace App\Services;

use App\Models\Device;
use App\Models\PrinterCounter;
use App\Models\TonerAlert;
use App\Notifications\TonerLowNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Service gérant la communication SNMP pour récupérer les informations des imprimantes (toner, compteurs).
 */
class SnmpService
{
    /**
     * Identifiants d'objets standards (OIDs) pour la MIB d'imprimante (RFC 1213 / HOST-RESOURCES-MIB).
     * Ces codes permettent de demander précisément une information à l'appareil via le réseau.
     */
    private const OIDS = [
        'total_pages'    => '1.3.6.1.2.1.43.10.2.1.4.1.1', // Compteur total de pages imprimées
        'toner_black'    => '1.3.6.1.2.1.43.11.1.1.9.1.1', // Niveau de toner Noir
        'toner_cyan'     => '1.3.6.1.2.1.43.11.1.1.9.1.2', // Niveau de toner Cyan
        'toner_magenta'  => '1.3.6.1.2.1.43.11.1.1.9.1.3', // Niveau de toner Magenta
        'toner_yellow'   => '1.3.6.1.2.1.43.11.1.1.9.1.4', // Niveau de toner Jaune
        'printer_status' => '1.3.6.1.2.1.25.3.5.1.1.1',    // Statut actuel
        'a4_canon'       => '1.3.6.1.4.1.1602.1.11.1.3.1.4.113', // Canon Petit Format
        'a3_canon'       => '1.3.6.1.4.1.1602.1.11.1.3.1.4.112', // Canon Grand Format
    ];

    /**
     * Récupère les compteurs d'une imprimante spécifiée en utilisant le protocole SNMP.
     * 
     * @param Device $device L'appareil cible
     * @return PrinterCounter|null L'enregistrement créé en base ou null en cas d'erreur
     */
    public function fetchCounters(Device $device): ?PrinterCounter
    {
        if ($device->type !== 'imprimante') {
            return null;
        }

        if (!function_exists('snmpget') && !function_exists('snmp2_get')) {
            Log::error("Extension SNMP PHP non activée sur ce serveur.");
            return null;
        }

        try {
            $ip = $device->ip_address;
            $community = $device->snmp_community ?: 'public';
            
            if (function_exists('snmp_set_quick_print')) {
                \snmp_set_quick_print(true);
            }
            if (function_exists('snmp_set_oid_numeric_print')) {
                \snmp_set_oid_numeric_print(1);
            }

            $configuredVersion = (string) $device->snmp_version;
            $versions = [$configuredVersion, '2', '1', '3'];
            $versions = array_unique(array_filter($versions));
            
            $resPages = false;
            $resStatus = false;
            $sysDescr = false;
            $usedVersion = $configuredVersion ?: '2';

            foreach ($versions as $v) {
                $usedVersion = $v;
                if ($v === '2') {
                    $sysDescr = @\snmp2_get($ip, $community, '1.3.6.1.2.1.1.1.0', 500000, 1);
                    $resPages = @\snmp2_get($ip, $community, self::OIDS['total_pages'], 500000, 1);
                    $resStatus = @\snmp2_get($ip, $community, self::OIDS['printer_status'], 500000, 1);
                } elseif ($v === '3') {
                    $sysDescr = @\snmp3_get($ip, 'none', 'noAuthNoPriv', '', '', '', '', '1.3.6.1.2.1.1.1.0', 500000, 1);
                    $resPages = @\snmp3_get($ip, 'none', 'noAuthNoPriv', '', '', '', '', self::OIDS['total_pages'], 500000, 1);
                    $resStatus = @\snmp3_get($ip, 'none', 'noAuthNoPriv', '', '', '', '', self::OIDS['printer_status'], 500000, 1);
                } else {
                    $sysDescr = @\snmpget($ip, $community, '1.3.6.1.2.1.1.1.0', 500000, 1);
                    $resPages = @\snmpget($ip, $community, self::OIDS['total_pages'], 500000, 1);
                    $resStatus = @\snmpget($ip, $community, self::OIDS['printer_status'], 500000, 1);
                }

                if ($resPages !== false) {
                    if ($device->snmp_version != $usedVersion) {
                        $device->update(['snmp_version' => $usedVersion]);
                    }
                    break;
                }
            }

            $sysDescr = $this->cleanSnmpValue($sysDescr);

            $data['total_pages'] = $this->cleanSnmpValue($resPages);
            $data['printer_status'] = $this->cleanSnmpValue($resStatus);

            // Fetch A3/A4 for Canon
            $data['a4_pages'] = null;
            $data['a3_pages'] = null;
            
            $resA4 = $this->snmpGet($ip, $community, self::OIDS['a4_canon'], $usedVersion);
            $resA3 = $this->snmpGet($ip, $community, self::OIDS['a3_canon'], $usedVersion);

            if ($resA4 !== false) $data['a4_pages'] = (int) $this->cleanSnmpValue($resA4);
            if ($resA3 !== false) $data['a3_pages'] = (int) $this->cleanSnmpValue($resA3);

            // If we have total and A3 but no A4, calculate A4 based on formula: TOTAL = A4 + A3*2
            if ($data['total_pages'] && $data['a3_pages'] !== null && $data['a4_pages'] === null) {
                $data['a4_pages'] = (int)$data['total_pages'] - ($data['a3_pages'] * 2);
            }

            // 3. Toners (Détection par Walk pour plus de fiabilité)
            // On parcourt la table des descriptions pour identifier chaque cartouche par son nom
            $supplyNames = $this->snmpRealWalk($ip, $community, '1.3.6.1.2.1.43.11.1.1.6', $usedVersion);
            $supplySerials = $this->snmpRealWalk($ip, $community, '1.3.6.1.2.1.43.11.1.1.7', $usedVersion);

            $toners = ['black' => null, 'cyan' => null, 'magenta' => null, 'yellow' => null];
            $allConsumables = [];

            if ($supplyNames) {
                Log::info("Found " . count($supplyNames) . " supplies for device {$ip}");
                foreach ($supplyNames as $oid => $name) {
                    $cleanName = str_replace('"', '', $this->cleanSnmpValue($name));
                    $lowerName = strtolower($cleanName);
                    
                    // L'index commence après le 11ème segment (1.3.6.1.2.1.43.11.1.1.6.X.Y)
                    $oidParts = explode('.', trim($oid, '.'));
                    $fullIndex = implode('.', array_slice($oidParts, 11));

                    $levelOid = "1.3.6.1.2.1.43.11.1.1.9.$fullIndex";
                    $maxOid   = "1.3.6.1.2.1.43.11.1.1.8.$fullIndex";

                    $lvl = $this->snmpGet($ip, $community, $levelOid, $usedVersion);
                    $mx = $this->snmpGet($ip, $community, $maxOid, $usedVersion);

                    if ($lvl !== false && $mx !== false) {
                        $lVal = (int) $this->cleanSnmpValue($lvl);
                        $mVal = (int) $this->cleanSnmpValue($mx);
                        $pct  = ($mVal > 0) ? round(($lVal / $mVal) * 100) : 0;

                        // Correction spécifique Canon (1=10%, 2=20%...)
                        if ($sysDescr && strpos(strtolower($sysDescr), 'canon') !== false && strpos($lowerName, 'toner') !== false && $mVal == 100 && $lVal > 0 && $lVal <= 10) {
                            $pct = $lVal * 10;
                        }

                        // Récupération du numéro de série par index
                        $cleanSerial = 'N/A';
                        if ($supplySerials) {
                            foreach ($supplySerials as $sOid => $sVal) {
                                if (str_ends_with(trim($sOid, '.'), $fullIndex)) {
                                    $cleanSerial = $this->cleanSnmpValue($sVal);
                                    break;
                                }
                            }
                        }

                        if ($cleanSerial === 'N/A' || strlen($cleanSerial) < 3) {
                            $serialOid = "1.3.6.1.2.1.43.11.1.1.7.$fullIndex";
                            $serialRaw = $this->snmpGet($ip, $community, $serialOid, $usedVersion);
                            if ($serialRaw !== false) {
                                $val = $this->cleanSnmpValue($serialRaw);
                                if (strlen($val) >= 3) $cleanSerial = $val;
                            }
                        }

                        // Fallback sur le Part Number (OID 12) si le S/N est absent ou suspect (trop court)
                        if ($cleanSerial === 'N/A' || strlen($cleanSerial) < 3) {
                            $partOid = "1.3.6.1.2.1.43.11.1.1.12.$fullIndex";
                            $partRaw = $this->snmpGet($ip, $community, $partOid, $usedVersion);
                            if ($partRaw !== false) {
                                $val = $this->cleanSnmpValue($partRaw);
                                if (strlen($val) >= 3) $cleanSerial = $val;
                            }
                        }

                        // Fallback spécifique Canon (OID privé Canon)
                        $isCanon = ($sysDescr && strpos(strtolower($sysDescr), 'canon') !== false);
                        if ($isCanon && ($cleanSerial === 'N/A' || strlen($cleanSerial) < 3)) {
                            $oidParts = explode('.', trim($oid, '.'));
                            $lastIndex = end($oidParts);
                            
                            $canonBranches = [
                                "1.3.6.1.4.1.1602.1.11.1.3.1.4",
                                "1.3.6.1.4.1.1602.1.11.1.4.1.4",
                                "1.3.6.1.4.1.1602.1.11.1.3.1.2",
                                "1.3.6.1.4.1.1602.1.11.1.4.1.2",
                            ];

                            foreach ($canonBranches as $branch) {
                                $canonOid = "$branch.$lastIndex";
                                $canonRaw = $this->snmpGet($ip, $community, $canonOid, $usedVersion);
                                
                                if ($canonRaw !== false) {
                                    $val = $this->cleanSnmpValue($canonRaw);
                                    if (strlen($val) >= 5) {
                                        $cleanSerial = $val;
                                        break;
                                    }
                                }
                            }
                        }

                        $isToner = (strpos($lowerName, 'toner') !== false);
                        $translatedName = self::translateComponent($cleanName);
                        $allConsumables[] = [
                            'name' => $translatedName,
                            'pct' => $pct,
                            'is_toner' => $isToner,
                            'serial' => $cleanSerial ?: 'N/A',
                            'type' => $this->extractTonerType($cleanSerial, $cleanName),
                        ];

                        // Mapping intelligent (uniquement pour les vrais toners)
                        if ($isToner) {
                            if (strpos($lowerName, 'black') !== false || strpos($lowerName, 'noir') !== false) {
                                $toners['black'] = $pct;
                            } elseif (strpos($lowerName, 'cyan') !== false) {
                                $toners['cyan'] = $pct;
                            } elseif (strpos($lowerName, 'magenta') !== false) {
                                $toners['magenta'] = $pct;
                            } elseif (strpos($lowerName, 'yellow') !== false || strpos($lowerName, 'jaune') !== false) {
                                $toners['yellow'] = $pct;
                            }
                        }
                    }
                }
            } else {
                Log::warning("No supply names found for device {$ip}. SNMP Walk failed or returned empty.");
            }


            // 4. Bacs à Papier (Trays)
            $trayNames = $this->snmpRealWalk($ip, $community, '1.3.6.1.2.1.43.8.2.1.13', $usedVersion);

            $paperStatus = [];
            if ($trayNames) {
                foreach ($trayNames as $oid => $name) {
                    // L'index commence après le 11ème segment (1.3.6.1.2.1.43.8.2.1.13.X.Y)
                    $oidParts = explode('.', trim($oid, '.'));
                    $fullIndex = implode('.', array_slice($oidParts, 11));
                    $levelOid = "1.3.6.1.2.1.43.8.2.1.10.$fullIndex";
                    $maxOid = "1.3.6.1.2.1.43.8.2.1.15.$fullIndex";
                    
                    $lvl = $this->snmpGet($ip, $community, $levelOid, $usedVersion);
                    $maxRaw = $this->snmpGet($ip, $community, $maxOid, $usedVersion);
                    
                    if ($lvl !== false) {
                        $lVal = (int) $this->cleanSnmpValue($lvl);
                        $trayName = $this->cleanSnmpValue($name);
                        
                        if ($lVal == -3) $paperStatus[] = self::translateComponent($trayName) . ": OK" . ($maxRaw ? " (max. " . $this->cleanSnmpValue($maxRaw) . ")" : "");
                        elseif ($lVal == 0) $paperStatus[] = self::translateComponent($trayName) . ": Vide" . ($maxRaw ? " (max. " . $this->cleanSnmpValue($maxRaw) . ")" : "");
                        elseif ($lVal > 0) $paperStatus[] = self::translateComponent($trayName) . ": $lVal" . ($maxRaw ? " / " . $this->cleanSnmpValue($maxRaw) : " feuilles");
                        else $paperStatus[] = self::translateComponent($trayName) . ": ?";
                    }
                }
            }
            $data['paper_level'] = !empty($paperStatus) ? substr(implode(', ', $paperStatus), 0, 255) : 'Inconnu';


            // -------------------------------------------------------------------------
            // LOGIQUE DE SAUVEGARDE (Évite la saturation de la base de données)
            // -------------------------------------------------------------------------

            // 1. Gestion du "Snapshot" de consommation (un seul historique tous les 20 jours)
            $lastSnapshot = PrinterCounter::where('device_id', $device->id)
                ->where('is_consumption_snapshot', true)
                ->latest('recorded_at')
                ->first();

            $shouldCreateSnapshot = false;

            if (!$lastSnapshot) {
                // S'il n'y a aucun snapshot, on en crée un initial
                $shouldCreateSnapshot = true;
            } else {
                // On vérifie si 20 jours se sont écoulés depuis le dernier snapshot
                $daysSinceLastSnapshot = Carbon::now()->diffInDays($lastSnapshot->recorded_at);
                // Si l'imprimante était en panne (ex: jour 19) et s'allume au jour 23, 23 >= 20 -> création !
                if ($daysSinceLastSnapshot >= 20) {
                    $shouldCreateSnapshot = true;
                }
            }

            if ($shouldCreateSnapshot) {
                PrinterCounter::create([
                    'device_id' => $device->id,
                    'recorded_at' => Carbon::now(),
                    'total_pages' => (int) ($data['total_pages'] ?: 0),
                    'a3_pages' => $data['a3_pages'],
                    'a4_pages' => null,
                    'toner_black_pct' => $toners['black'],
                    'toner_cyan_pct' => $toners['cyan'],
                    'toner_magenta_pct' => $toners['magenta'],
                    'toner_yellow_pct' => $toners['yellow'],
                    'printer_status' => $data['printer_status'],
                    'paper_level' => $data['paper_level'],
                    'consumables' => $allConsumables,
                    'is_consumption_snapshot' => true,
                ]);
            }

            // 2. Gestion de l'affichage en Temps Réel (Une seule et unique ligne "Live" par imprimante)
            // Plutôt que de créer une ligne toutes les 15 minutes, on met à jour la ligne existante.
            $liveCounter = PrinterCounter::where('device_id', $device->id)
                ->where('is_consumption_snapshot', false)
                ->first();

            if ($liveCounter) {
                $liveCounter->update([
                    'recorded_at' => Carbon::now(),
                    'total_pages' => (int) ($data['total_pages'] ?: 0),
                    'a3_pages' => $data['a3_pages'],
                    'a4_pages' => null,
                    'toner_black_pct' => $toners['black'],
                    'toner_cyan_pct' => $toners['cyan'],
                    'toner_magenta_pct' => $toners['magenta'],
                    'toner_yellow_pct' => $toners['yellow'],
                    'printer_status' => $data['printer_status'],
                    'paper_level' => $data['paper_level'],
                    'consumables' => $allConsumables,
                ]);
                $counter = $liveCounter;
            } else {
                $counter = PrinterCounter::create([
                    'device_id' => $device->id,
                    'recorded_at' => Carbon::now(),
                    'total_pages' => (int) ($data['total_pages'] ?: 0),
                    'a3_pages' => $data['a3_pages'],
                    'a4_pages' => null,
                    'toner_black_pct' => $toners['black'],
                    'toner_cyan_pct' => $toners['cyan'],
                    'toner_magenta_pct' => $toners['magenta'],
                    'toner_yellow_pct' => $toners['yellow'],
                    'printer_status' => $data['printer_status'],
                    'paper_level' => $data['paper_level'],
                    'consumables' => $allConsumables,
                    'is_consumption_snapshot' => false,
                ]);
            }

            $this->checkTonerLevels($device, $counter);

            return $counter;
        } catch (\Exception $e) {
            Log::error("SNMP Error for device {$device->ip_address}: " . $e->getMessage());
            return null;
        }
    }


    /**
     * Vérifie si les niveaux de toner sont bas et crée une alerte si nécessaire.
     */
    public function checkTonerLevels(Device $device, PrinterCounter $counter): void
    {
        $colors = ['black', 'cyan', 'magenta', 'yellow'];
        $threshold = 10.0; // Seuil d'alerte fixé à 10%

        foreach ($colors as $color) {
            $field = "toner_{$color}_pct";
            $level = $counter->$field;

            // Si le niveau est connu et inférieur au seuil, on crée une alerte
            if ($level !== null && $level < $threshold) {
                $alert = TonerAlert::create([
                    'device_id' => $device->id,
                    'toner_color' => ucfirst($color),
                    'level_pct' => $level,
                    'threshold_pct' => $threshold,
                    'alerted_at' => Carbon::now(),
                    'is_sent' => false,
                ]);

                // Emplacement pour futures notifications mail/push
                // $device->notify(new TonerLowNotification($alert));
            }
        }
    }

    /**
     * Nettoie la chaîne de réponse SNMP (ex: "STRING: Ready" ou "INTEGER: 123").
     */
    private function cleanSnmpValue($value)
    {
        if ($value === false || $value === null) return '';
        $value = (string) $value;
        
        // Gère le format "Hex-STRING: 43 45 58 56 ..."
        if (strpos($value, 'Hex-STRING:') !== false) {
            $hex = trim(str_replace('Hex-STRING:', '', $value));
            $hex = str_replace(' ', '', $hex);
            $str = '';
            for ($i=0; $i < strlen($hex)-1; $i+=2) {
                $str .= chr(hexdec($hex[$i].$hex[$i+1]));
            }
            return trim($str);
        }

        // Liste des préfixes SNMP standards à retirer
        $prefixes = [
            'STRING:', 'INTEGER:', 'Counter32:', 'Counter64:', 'Gauge32:', 
            'OID:', 'Timeticks:', 'IpAddress:', 'Network Address:', 'Hex-STRING:'
        ];

        foreach ($prefixes as $prefix) {
            if (str_starts_with($value, $prefix)) {
                $value = trim(substr($value, strlen($prefix)));
                break;
            }
        }
        
        // Si on a encore un format "PREFIX: value" non identifié, 
        // on ne splitte que si c'est manifestement un type (mot court en majuscules)
        if (preg_match('/^[A-Z][A-Za-z0-9]{1,15}: /', $value)) {
            $parts = explode(':', $value, 2);
            $value = trim($parts[1]);
        }
        
        $value = trim($value, '" ');

        // Assure que le résultat est en UTF-8 valide pour éviter les erreurs JSON
        return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }

    /**
     * Traduit les termes techniques SNMP en français.
     */
    public static function translateComponent(?string $text): string
    {
        if (!$text) return '';
        $translations = [
            'C-EXV 60' => 'Canon C-EXV 60',
            'Black Toner' => 'Toner Noir',
            'Cyan Toner' => 'Toner Cyan',
            'Magenta Toner' => 'Toner Magenta',
            'Yellow Toner' => 'Toner Jaune',
            'Waste Toner' => 'Récupérateur de Toner',
            'Drum Unit' => 'Unité de Tambour',
            'Fuser Unit' => 'Unité de Fusion',
            'Multi-purpose Tray' => 'Bac Multi-usages',
            'Tray 1' => 'Bac 1',
            'Tray 2' => 'Bac 2',
            'Tray 3' => 'Bac 3',
            'Tray 4' => 'Bac 4',
            'Ready' => 'Prêt',
            'Printing' => 'Impression en cours',
            'Sleep' => 'Veille',
            'Warning' => 'Attention',
            'Paper Jam' => 'Bourrage papier',
            'Low Toner' => 'Toner bas',
            'No Paper' => 'Plus de papier',
            'Door Open' => 'Porte ouverte',
        ];

        foreach ($translations as $en => $fr) {
            if (stripos($text, $en) !== false) {
                // Pour les toners type "Canon C-EXV 60 Black Toner", on remplace intelligemment
                if (strpos($en, 'Toner') !== false && strpos($en, ' ') !== false) {
                    return str_ireplace($en, $fr, $text);
                }
                return $fr;
            }
        }

        // Cas spécifiques type "Black Drum Unit"
        $text = str_ireplace('Black Drum', 'Tambour Noir', $text);
        $text = str_ireplace('Drum', 'Tambour', $text);
        $text = str_ireplace('Fuser', 'Unité de Fusion', $text);
        $text = str_ireplace('Black', 'Noir', $text);

        return $text;
    }

    /**
     * Normalise le niveau de toner. Certains constructeurs retournent des valeurs négatives si inconnu.
     */
    private function normalizeToner($value): ?float
    {
        // Filtre les valeurs nulles ou négatives qui indiquent une erreur de lecture
        if ($value === null || $value < 0) return null;
        return (float) $value;
    }

    /**
     * Tente d'extraire le modèle de toner à partir du numéro de série ou du nom.
     */
    public function extractTonerType(string $serial, string $name): string
    {
        // Patterns courants (Canon: C-EXV, HP: CF..., Brother: TN..., Kyocera: TK...)
        $pattern = '/(C-EXV\s*\d+|TK-\d+|CF\d+[AX]|TN-\d+|CE\d+[AX]|CRG-\d+|GPR-\d+)/i';
        
        if (preg_match($pattern, $serial, $matches)) {
            return strtoupper(str_replace(' ', '', $matches[1]));
        }
        
        if (preg_match($pattern, $name, $matches)) {
            return strtoupper(str_replace(' ', '', $matches[1]));
        }

        return 'Standard';
    }

    /**
     * Effectue un get SNMP en fonction de la version détectée.
     */
    private function snmpGet(string $ip, string $community, string $oid, string $version)
    {
        if ($version === '2') {
            return @\snmp2_get($ip, $community, $oid, 500000, 1);
        } elseif ($version === '3') {
            return @\snmp3_get($ip, 'none', 'noAuthNoPriv', '', '', '', '', $oid, 500000, 1);
        } else {
            return @\snmpget($ip, $community, $oid, 500000, 1);
        }
    }

    /**
     * Effectue un walk SNMP en fonction de la version détectée.
     */
    private function snmpRealWalk(string $ip, string $community, string $oid, string $version)
    {
        if ($version === '2') {
            return @\snmp2_real_walk($ip, $community, $oid, 1500000, 1);
        } elseif ($version === '3') {
            return @\snmp3_walk($ip, 'none', 'noAuthNoPriv', '', '', '', '', $oid, 1500000, 1);
        } else {
            return @\snmprealwalk($ip, $community, $oid, 1500000, 1);
        }
    }
}
