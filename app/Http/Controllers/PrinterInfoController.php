<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SnmpService;

class PrinterInfoController extends Controller
{
    public function index(Request $request)
    {
        set_time_limit(120);
        $ip        = $request->query('ip', '10.110.31.150');
        $community = $request->query('community', 'public');

        $data = [
            'ip'          => $ip,
            'community'   => $community,
            'status_online' => false,
            'status_msg'  => 'Inconnu',
            'pages'       => 'N/A',
            'pages_a4'    => 'N/A',
            'pages_a3'    => 'N/A',
            'toner'       => 'N/A',
            'toner_levels' => [],
            'paper_levels' => [],
            'errors'      => 'Aucune information',
            'snmp_ok'     => false,
        ];

        // --- 1. Test de connectivité via ping ---
        exec("ping -n 1 -w 1000 " . escapeshellarg($ip), $pingOutput, $pingCode);
        $data['status_online'] = ($pingCode === 0);

        // --- 2. Test SNMP ---
        // Vérifie si l'extension PHP SNMP est installée sur le serveur
        if (!function_exists('snmpget')) {
            $data['errors'] = "Extension SNMP PHP non activée.";
            return view('printer_info', $data);
        }

        // Configuration de la sortie SNMP pour un parsing propre
        error_reporting(0);
        if (function_exists('snmp_set_quick_print')) {
            \snmp_set_quick_print(true); // Supprime les types (ex: "INTEGER: ")
        }
        if (function_exists('snmp_set_enum_print')) {
            \snmp_set_enum_print(true);  // Convertit les énumérations en texte lisible
        }
        if (function_exists('snmp_set_oid_numeric_print')) {
            \snmp_set_oid_numeric_print(1); // Force les OIDs en format numérique (.1.3.6...)
        }

        // ÉTAPE A : Récupération de l'identité de l'imprimante (Model/SysDescr)
        // On tente en SNMP v2c (plus rapide), puis v1 en cas d'échec (cas fréquent sur Canon)
        $sysDescr = @\snmp2_get($ip, $community, '1.3.6.1.2.1.1.1.0', 300000, 1);
        if ($sysDescr === false) {
            $sysDescr = @\snmpget($ip, $community, '1.3.6.1.2.1.1.1.0', 300000, 1);
        }

        if ($sysDescr === false) {
            $data['errors'] = $data['status_online']
                ? "Imprimante joignable (ping OK) mais SNMP ne répond pas.\n\nCauses possibles :\n• Community incorrecte (actuelle : '$community')\n• SNMP v1/v2 désactivé sur l'imprimante\n• Firewall bloque le port UDP/161"
                : "Imprimante injoignable (ping et SNMP échouent).";
            return view('printer_info', $data);
        }

        // Si on arrive ici, SNMP est actif
        $data['snmp_ok'] = true;
        $data['status_online'] = true;
        $data['status_msg'] = $this->cleanSnmpValue($sysDescr);

        // Récupération du numéro de série physique de l'imprimante
        $snOid = '1.3.6.1.2.1.43.5.1.1.17.1'; // Standard
        $snRaw = @\snmp2_get($ip, $community, $snOid, 500000, 1);
        if ($snRaw === false) $snRaw = @\snmpget($ip, $community, $snOid, 500000, 1);
        
        // Fallback Canon
        if ($snRaw === false && strpos(strtolower($data['status_msg']), 'canon') !== false) {
            $snRaw = @\snmp2_get($ip, $community, '1.3.6.1.4.1.1602.1.2.1.1.0', 500000, 1);
        }
        
        $data['device_serial'] = ($snRaw !== false) ? $this->cleanSnmpValue($snRaw) : 'Inconnu';

        // ÉTAPE B : Récupération du compteur de pages (OID standard prtMarkerLifeCount)
        $pagesOid = '1.3.6.1.2.1.43.10.2.1.4.1.1';
        $pagesRaw = @\snmp2_get($ip, $community, $pagesOid, 500000, 1);
        if ($pagesRaw === false) {
            $pagesRaw = @\snmpget($ip, $community, $pagesOid, 500000, 1);
        }
        if ($pagesRaw !== false) {
            $data['pages'] = (int) $this->cleanSnmpValue($pagesRaw);
        }

        // ÉTAPE B.2 : Compteurs spécifiques Canon (A4 / A3)
        // OID 113 = Petit Format (A4), OID 112 = Grand Format (A3)
        $a4Oid = '1.3.6.1.4.1.1602.1.11.1.3.1.4.113';
        $a3Oid = '1.3.6.1.4.1.1602.1.11.1.3.1.4.112';
        
        $a4Raw = @\snmp2_get($ip, $community, $a4Oid, 500000, 1);
        if ($a4Raw === false) $a4Raw = @\snmpget($ip, $community, $a4Oid, 500000, 1);
        
        $a3Raw = @\snmp2_get($ip, $community, $a3Oid, 500000, 1);
        if ($a3Raw === false) $a3Raw = @\snmpget($ip, $community, $a3Oid, 500000, 1);

        if ($a4Raw !== false) $data['pages_a4'] = (int) $this->cleanSnmpValue($a4Raw);
        if ($a3Raw !== false) $data['pages_a3'] = (int) $this->cleanSnmpValue($a3Raw);
        
        // Si on a les deux, on peut recalculer le total (A3 compte double : A4 + A3*2)
        if (is_numeric($data['pages_a4']) && is_numeric($data['pages_a3'])) {
            $data['pages_total_calc'] = $data['pages_a4'] + ($data['pages_a3'] * 2);
        }

        // ÉTAPE C : Détection dynamique des niveaux de Toner
        // Au lieu d'utiliser des OID fixes, on parcourt la table des noms de fournitures
        $supplyNames = @\snmp2_real_walk($ip, $community, '1.3.6.1.2.1.43.11.1.1.6', 800000, 1);
        if ($supplyNames === false) {
            $supplyNames = @\snmprealwalk($ip, $community, '1.3.6.1.2.1.43.11.1.1.6', 800000, 1);
        }

        $supplySerials = @\snmp2_real_walk($ip, $community, '1.3.6.1.2.1.43.11.1.1.7', 2000000, 1);
        if ($supplySerials === false) {
            $supplySerials = @\snmprealwalk($ip, $community, '1.3.6.1.2.1.43.11.1.1.7', 2000000, 1);
        }

        if ($supplyNames) {
            foreach ($supplyNames as $oid => $name) {
                // L'index commence après le 11ème segment de l'OID (1.3.6.1.2.1.43.11.1.1.6.X.Y)
                $oidParts = explode('.', trim($oid, '.'));
                $fullIndex = implode('.', array_slice($oidParts, 11));

                // OID pour le niveau actuel et le niveau maximum (capacité)
                $levelOid = "1.3.6.1.2.1.43.11.1.1.9.$fullIndex";
                $maxOid   = "1.3.6.1.2.1.43.11.1.1.8.$fullIndex";

                // Lecture du niveau actuel
                $levelRaw = @\snmp2_get($ip, $community, $levelOid, 500000, 1);
                if ($levelRaw === false) $levelRaw = @\snmpget($ip, $community, $levelOid, 500000, 1);

                // Lecture du maximum (nécessaire pour calculer le %)
                $maxRaw = @\snmp2_get($ip, $community, $maxOid, 500000, 1);
                if ($maxRaw === false) $maxRaw = @\snmpget($ip, $community, $maxOid, 500000, 1);

                if ($levelRaw !== false && $maxRaw !== false) {
                    $level = (int) $this->cleanSnmpValue($levelRaw);
                    $max   = (int) $this->cleanSnmpValue($maxRaw);
                    // Calcul du pourcentage (ex: 50/100 * 100 = 50%)
                    $pct   = ($max > 0) ? round(($level / $max) * 100) : 0;

                    // Correction spécifique pour Canon : si le niveau est entre 1 et 10 sur un max de 100
                    // cela correspond souvent à des paliers de 10% (1=10%, 2=20%, etc.)
                    $isCanon = (strpos(strtolower($data['status_msg']), 'canon') !== false);
                    $isToner = (strpos(strtolower($name), 'toner') !== false);
                    
                    if ($isCanon && $isToner && $max == 100 && $level > 0 && $level <= 10) {
                        $pct = $level * 10;
                    }

                    $cleanName = str_replace('"', '', $this->cleanSnmpValue($name));
                    
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
                        $serialRaw = @\snmp2_get($ip, $community, $serialOid, 500000, 1);
                        if ($serialRaw === false) $serialRaw = @\snmpget($ip, $community, $serialOid, 500000, 1);
                        if ($serialRaw !== false) {
                            $val = $this->cleanSnmpValue($serialRaw);
                            if (strlen($val) >= 3) $cleanSerial = $val;
                        }
                    }

                    // Fallback sur le Part Number (OID 12) si le S/N est absent ou suspect (trop court)
                    if ($cleanSerial === 'N/A' || strlen($cleanSerial) < 3) {
                        $partOid = "1.3.6.1.2.1.43.11.1.1.12.$fullIndex";
                        $partRaw = @\snmp2_get($ip, $community, $partOid, 500000, 1);
                        if ($partRaw === false) $partRaw = @\snmpget($ip, $community, $partOid, 500000, 1);
                        if ($partRaw !== false) {
                            $val = $this->cleanSnmpValue($partRaw);
                            if (strlen($val) >= 3) $cleanSerial = $val;
                        }
                    }

                    // Fallback spécifique Canon (OID privé Canon)
                    if ($isCanon && ($cleanSerial === 'N/A' || strlen($cleanSerial) < 3)) {
                        $lastIndex = end($oidParts); 
                        
                        // On tente plusieurs branches Canon connues (.3.1.2, .3.1.4, .4.1.2, .4.1.4)
                        $canonBranches = [
                            "1.3.6.1.4.1.1602.1.11.1.3.1.4",
                            "1.3.6.1.4.1.1602.1.11.1.4.1.4",
                            "1.3.6.1.4.1.1602.1.11.1.3.1.2",
                            "1.3.6.1.4.1.1602.1.11.1.4.1.2",
                        ];

                        foreach ($canonBranches as $branch) {
                            $canonOid = "$branch.$lastIndex";
                            $canonRaw = @\snmp2_get($ip, $community, $canonOid, 500000, 1);
                            if ($canonRaw === false) $canonRaw = @\snmpget($ip, $community, $canonOid, 500000, 1);
                            
                            if ($canonRaw !== false) {
                                $val = $this->cleanSnmpValue($canonRaw);
                                if (strlen($val) >= 5) { // Un vrai S/N Canon fait souvent > 5 chars
                                    $cleanSerial = $val;
                                    break;
                                }
                            }
                        }
                    }

                    
                    $snmpService = new SnmpService();
                    $data['toner_levels'][] = [
                        'name'   => SnmpService::translateComponent($cleanName),
                        'pct'    => $pct,
                        'level'  => $level,
                        'max'    => $max,
                        'serial' => $cleanSerial ?: 'N/A',
                        'raw_serial' => $serialRaw ?? 'N/A',
                        'type'   => $snmpService->extractTonerType($cleanSerial, $cleanName),
                    ];
                }
            }
            // Utilise le premier toner trouvé comme valeur principale
            if (count($data['toner_levels']) > 0) {
                $data['toner'] = $data['toner_levels'][0]['pct'];
            }
        }

        // ÉTAPE D : Détection des bacs à papier (Trays)
        $trayNames = @\snmp2_real_walk($ip, $community, '1.3.6.1.2.1.43.8.2.1.13', 2000000, 1);
        if ($trayNames === false) {
            $trayNames = @\snmprealwalk($ip, $community, '1.3.6.1.2.1.43.8.2.1.13', 2000000, 1);
        }

        if ($trayNames) {
            foreach ($trayNames as $oid => $name) {
                // L'index commence après le 11ème segment (1.3.6.1.2.1.43.8.2.1.13.X.Y)
                $oidParts = explode('.', trim($oid, '.'));
                $fullIndex = implode('.', array_slice($oidParts, 11));
                
                $levelOid = "1.3.6.1.2.1.43.8.2.1.10.$fullIndex";
                $maxOid   = "1.3.6.1.2.1.43.8.2.1.9.$fullIndex";

                $levelRaw = @\snmp2_get($ip, $community, $levelOid, 500000, 1);
                if ($levelRaw === false) $levelRaw = @\snmpget($ip, $community, $levelOid, 500000, 1);

                $maxRaw = @\snmp2_get($ip, $community, $maxOid, 500000, 1);
                if ($maxRaw === false) $maxRaw = @\snmpget($ip, $community, $maxOid, 500000, 1);

                if ($levelRaw !== false && $maxRaw !== false) {
                    $level = (int) $this->cleanSnmpValue($levelRaw);
                    $max   = (int) $this->cleanSnmpValue($maxRaw);
                    
                    $pct = 0;
                    $status_text = "Inconnu";

                    if ($level == -3) {
                        $pct = 50; 
                        $status_text = "Papier présent" . ($max > 0 ? " (max. $max)" : "");
                    } elseif ($level == -2) {
                        $pct = 0;
                        $status_text = "Inconnu";
                    } elseif ($level == 0) {
                        $pct = 0;
                        $status_text = "Vide" . ($max > 0 ? " (max. $max)" : "");
                    } elseif ($level > 0) {
                        $pct = ($max > 0) ? round(($level / $max) * 100) : 100;
                        $status_text = "$level" . ($max > 0 ? " / $max" : " feuilles");
                    } elseif ($max > 0) {
                        $pct = 0;
                        $status_text = "0 / $max";
                    }
                    

                    $data['paper_levels'][] = [
                        'name' => SnmpService::translateComponent(str_replace('"', '', $this->cleanSnmpValue($name))),
                        'pct'  => $pct,
                        'level' => $level,
                        'max' => $max,
                        'status_text' => $status_text
                    ];
                }
            }
        }

        // ÉTAPE E : Récupération des erreurs/alertes en cours sur la machine
        $errorsRaw = @\snmp2_walk($ip, $community, '1.3.6.1.2.1.43.18.1.1.8', 1000000, 1);
        if ($errorsRaw === false) $errorsRaw = @\snmpwalk($ip, $community, '1.3.6.1.2.1.43.18.1.1.8', 1000000, 1);

        if ($errorsRaw) {
            $data['errors'] = implode(', ', array_map(function($val) {
                return SnmpService::translateComponent($this->cleanSnmpValue($val));
            }, $errorsRaw));
        } else {
            $data['errors'] = 'Aucune erreur détectée';
        }
        
        // --- 4. Déclenchement de l'enregistrement automatique en arrière-plan ---
        $device = \App\Models\Device::where('ip_address', $ip)->first();
        if ($device && $data['snmp_ok']) {
            // On utilise un Job pour éviter de bloquer la page (Timeout 60s)
            \App\Jobs\FetchSnmpJob::dispatch($device);
        }

        return view('printer_info', $data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ip' => 'required|ip',
            'recorded_at_date' => 'required|date',
            'recorded_at_time' => 'required',
            'pages_a4' => 'required|integer|min:0',
            'pages_a3' => 'required|integer|min:0',
            'total_pages' => 'required|integer|min:0',
            'status' => 'nullable|string|max:100',
        ]);

        $recordedAt = \Carbon\Carbon::parse($validated['recorded_at_date'] . ' ' . $validated['recorded_at_time']);
        
        // Trouver l'appareil correspondant à cette IP pour lier le relevé
        $device = \App\Models\Device::where('ip_address', $validated['ip'])->first();

        if (!$device) {
            return back()->with('error', "L'appareil avec l'IP {$validated['ip']} n'est pas enregistré dans l'inventaire. Veuillez l'ajouter d'abord.");
        }

        \App\Models\PrinterCounter::create([
            'device_id' => $device->id,
            'recorded_at' => $recordedAt,
            'total_pages' => $validated['total_pages'],
            'a4_pages' => $validated['pages_a4'],
            'a3_pages' => $validated['pages_a3'],
            'is_consumption_snapshot' => true,
            'printer_status' => $validated['status'] ?? 'Enregistrement manuel',
        ]);

        return back()->with('success', "Relevé de consommation enregistré avec succès pour le {$recordedAt->format('d/m/Y à H:i')}.");
    }

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
        
        return trim($value, '" ');
    }
}

