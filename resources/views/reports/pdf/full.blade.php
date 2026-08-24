<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport NetDevice Manager</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #4f46e5; padding-bottom: 20px; margin-bottom: 30px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #777; border-top: 1px solid #ddd; padding-top: 10px; }
        h1 { color: #4f46e5; margin-bottom: 5px; }
        h2 { border-left: 4px solid #4f46e5; padding-left: 10px; margin-top: 30px; background: #f8fafc; padding-top: 5px; padding-bottom: 5px; font-size: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background-color: #f1f5f9; text-align: left; padding: 10px; border: 1px solid #e2e8f0; font-size: 10px; text-transform: uppercase; }
        td { padding: 10px; border: 1px solid #e2e8f0; vertical-align: top; }
        .stats-grid { margin-bottom: 20px; }
        .stats-box { width: 23%; display: inline-block; background: #fff; border: 1px solid #e2e8f0; padding: 10px; margin-right: 1%; text-align: center; }
        .stats-label { font-size: 9px; color: #64748b; text-transform: uppercase; margin-bottom: 5px; }
        .stats-value { font-size: 18px; font-weight: bold; color: #1e293b; }
        .badge { padding: 3px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-green { background: #dcfce7; color: #166534; }
    </style>
</head>
<body>
    <div class="header">
        <table style="border: none; margin-bottom: 0;">
            <tr>
                <td style="border: none; width: 150px; text-align: left; padding: 0;">
                    <img src="{{ public_path('logo.jpg') }}" style="height: 120px; object-fit: contain;">
                </td>
                <td style="border: none; text-align: right; padding: 0;">
                    <h1 style="margin: 0;">NetDevice Manager</h1>
                    <p style="margin: 5px 0 0 0; color: #64748b; font-size: 14px;">Rapport d'activité & supervision</p>
                </td>
            </tr>
        </table>
        <p style="margin-top: 15px; font-size: 11px; color: #94a3b8;">Généré pour la période : {{ $period['start'] }} au {{ $period['end'] }}</p>
    </div>

    <div class="stats-grid">
        <div class="stats-box">
            <div class="stats-label">Appareils</div>
            <div class="stats-value">{{ $stats['total_devices'] }}</div>
        </div>
        <div class="stats-box">
            <div class="stats-label">Taux Dispo.</div>
            <div class="stats-value">{{ $connectivity['availability_rate'] }}%</div>
        </div>
        <div class="stats-box">
            <div class="stats-label">Erreurs</div>
            <div class="stats-value" style="color: #ef4444;">{{ $stats['total_errors'] }}</div>
        </div>
        <div class="stats-box">
            <div class="stats-label">Alertes Toner</div>
            <div class="stats-value">{{ $stats['toner_alerts'] }}</div>
        </div>
    </div>

    <h2>Connectivité & Disponibilité</h2>
    <table>
        <thead>
            <tr>
                <th>Appareils les plus instables</th>
                <th>Nombre de déconnexions détectées</th>
            </tr>
        </thead>
        <tbody>
            @foreach($top_disconnected as $item)
                <tr>
                    <td>{{ $item['device']['name'] ?? 'Appareil supprimé' }}</td>
                    <td style="color: #ef4444; font-weight: bold;">{{ $item['offline_events'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Statistiques d'Impression</h2>
    <table>
        <thead>
            <tr>
                <th>Imprimante</th>
                <th>Pages imprimées sur la période</th>
                <th>Compteur Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($printing as $print)
                <tr>
                    <td>{{ $print['device_name'] }}</td>
                    <td style="font-weight: bold;">{{ $print['pages_printed'] }}</td>
                    <td>{{ $print['current_total'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Niveaux de Toner Actuels</h2>
    <table>
        <thead>
            <tr>
                <th>Appareil</th>
                <th>Noir</th>
                <th>Cyan</th>
                <th>Magenta</th>
                <th>Jaune</th>
            </tr>
        </thead>
        <tbody>
            @foreach($current_toner as $toner)
                <tr>
                    <td>{{ $toner['device_name'] }}</td>
                    @if($toner['toner'])
                        <td style="{{ $toner['toner']['black'] < 10 ? 'color: red; font-weight: bold;' : '' }}">{{ $toner['toner']['black'] }}%</td>
                        <td>{{ $toner['toner']['cyan'] }}%</td>
                        <td>{{ $toner['toner']['magenta'] }}%</td>
                        <td>{{ $toner['toner']['yellow'] }}%</td>
                    @else
                        <td colspan="4" style="color: #94a3b8; font-style: italic;">Données non connectées</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Généré le {{ date('d/m/Y H:i:s') }} par NetDevice Manager - Supervision Réseau
    </div>
</body>
</html>
