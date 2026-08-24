<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Inter', -apple-system, sans-serif; line-height: 1.5; color: #1e293b; background-color: #f8fafc; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; }
        .header-red { background: #0f172a; color: white; padding: 20px; text-align: center; font-weight: bold; font-size: 18px; }
        .header-green { background: #16a34a; color: white; padding: 20px; text-align: center; font-weight: bold; font-size: 18px; }
        .content { padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        th { background: #f1f5f9; font-weight: bold; color: #475569; }
        .badge-red { background: #fee2e2; color: #dc2626; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-green { background: #dcfce3; color: #16a34a; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; background: #f8fafc; }
    </style>
</head>
<body>
    <div class="container">
        <div class="{{ $isResolved ? 'header-green' : 'header-red' }}">
            {{ $isResolved ? 'Rapport de Rétablissement' : 'Rapport Groupé de Connectivité' }}
        </div>
        <div class="content">
            <p>Bonjour,</p>
            <p>Voici le récapitulatif des agences {{ $isResolved ? 'dont la connexion a été rétablie' : 'actuellement injoignables' }} :</p>
            
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Agence</th>
                        <th>IP Routeur</th>
                        <th>Statut</th>
                        @if(!$isResolved)
                        <th>Problème / Diagnostic</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($issues as $issue)
                    <tr>
                        <td>{{ $issue->is_resolved ? $issue->resolved_at?->format('H:i:s') : $issue->logged_at?->format('H:i:s') }}</td>
                        <td>{{ $issue->device?->agency?->name ?? 'Inconnue' }}</td>
                        <td>{{ $issue->device?->agency?->router_ip ?? '-' }}</td>
                        <td>
                            @if($issue->is_resolved)
                                <span class="badge-green">RÉTABLIE</span>
                            @else
                                <span class="badge-red">EN PANNE</span>
                            @endif
                        </td>
                        @if(!$isResolved)
                        <td>{{ $issue->message }}</td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="footer">
            Généré automatiquement à {{ $eventTime }} par NetDevice Pro
        </div>
    </div>
</body>
</html>
