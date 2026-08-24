<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Inter', -apple-system, sans-serif; line-height: 1.5; color: #1e293b; background-color: #f8fafc; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; }
        .header { background: #0f172a; color: white; padding: 20px; text-align: center; font-weight: bold; font-size: 18px; border-bottom: 4px solid #3b82f6; }
        .content { padding: 20px; }
        .intro { background: #eff6ff; padding: 15px; border-radius: 8px; border-left: 4px solid #3b82f6; margin-bottom: 20px; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        th { background: #f1f5f9; font-weight: bold; color: #475569; }
        .badge-red { background: #fee2e2; color: #dc2626; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; background: #f8fafc; border-top: 1px solid #e2e8f0; }
        .empty-state { text-align: center; padding: 30px; color: #16a34a; font-weight: bold; background: #dcfce3; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            🌐 Connexion Internet Rétablie
        </div>
        <div class="content">
            <div class="intro">
                <strong>Bonjour,</strong><br>
                La connexion Internet du système a été rétablie avec succès.
                Voici l'état actuel des agences qui sont <strong>toujours déconnectées</strong> à {{ $eventTime }} :
            </div>
            
            @if($offlineAgencies->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Agence</th>
                            <th>IP Routeur</th>
                            <th>Statut</th>
                            <th>Diagnostic Principal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($offlineAgencies as $agency)
                        <tr>
                            <td><strong>{{ $agency->name }}</strong></td>
                            <td>{{ $agency->router_ip ?? '-' }}</td>
                            <td><span class="badge-red">HORS LIGNE</span></td>
                            <td>
                                @php
                                    $recentError = $agency->devices->where('type', 'routeur')->first()?->errorLogs->where('is_resolved', false)->first();
                                @endphp
                                {{ $recentError ? $recentError->message : 'Routeur injoignable' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    ✅ Excellente nouvelle : Toutes les agences sont actuellement connectées !
                </div>
            @endif
        </div>
        <div class="footer">
            Généré automatiquement par NetDevice Pro
        </div>
    </div>
</body>
</html>
