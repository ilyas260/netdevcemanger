<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.5; color: #1e293b; margin: 0; padding: 0; background-color: #f8fafc; }
        .wrapper { width: 100%; padding: 40px 0; background-color: #f1f5f9; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0; }
        
        .srm-header { background-color: #ffffff; padding: 25px; text-align: center; border-bottom: 1px solid #e2e8f0; }
        .srm-logo-img { height: 60px; width: auto; margin-bottom: 4px; }
        .srm-tagline { color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 2px; font-weight: 700; }
        
        .status-banner { padding: 15px; text-align: center; font-weight: 800; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; background-color: #fef2f2; color: #dc2626; border-bottom: 1px solid #fee2e2; }
        
        .content { padding: 40px; }
        .alert-title { font-size: 20px; font-weight: 800; color: #0f172a; margin-bottom: 8px; }
        .alert-desc { color: #64748b; font-size: 14px; margin-bottom: 30px; }
        
        .agencies-list { background-color: #f8fafc; border-radius: 12px; padding: 20px; border: 1px solid #f1f5f9; margin-bottom: 25px; }
        .agency-card { background: white; border-radius: 8px; padding: 16px; border: 1px solid #e2e8f0; margin-bottom: 12px; }
        .agency-card:last-child { margin-bottom: 0; }
        
        .agency-title { font-size: 15px; font-weight: 800; color: #dc2626; margin-bottom: 4px; }
        .agency-meta { font-size: 12px; color: #64748b; font-family: monospace; }
        
        .footer { padding: 30px; text-align: center; font-size: 12px; color: #94a3b8; background-color: #f8fafc; border-top: 1px solid #f1f5f9; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 800; background-color: #ef4444; color: white; margin-left: 8px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <!-- Header SRM -->
            <div class="srm-header">
                @if(file_exists(public_path('images/srm-logo.png')))
                    <img src="{{ $message->embed(public_path('images/srm-logo.png')) }}" alt="SRM Logo" class="srm-logo-img">
                @endif
                <div class="srm-tagline">Network Supervision System</div>
            </div>

            <!-- Banner Statut -->
            <div class="status-banner">
                🚨 ALERTE MULTIPLE : INTERRUPTION DE SERVICE SUR {{ count($agencies) }} AGENCE(S)
            </div>
            
            <div class="content">
                <div class="alert-title">Rapport d'Injoignabilité des Agences</div>
                <div class="alert-desc">Les sondes automatisées ont détecté que les agences suivantes sont injoignables.</div>
                
                <div class="agencies-list">
                    @foreach($agencies as $agency)
                        <div class="agency-card">
                            <div class="agency-title">
                                {{ $agency->name }}
                                <span class="badge">HORS-LIGNE</span>
                            </div>
                            <div class="agency-meta">
                                <strong>IP du Routeur :</strong> {{ $agency->router_ip }} <br>
                                <strong>Dernier Ping :</strong> {{ $agency->last_ping_at ? $agency->last_ping_at->format('d/m/Y H:i:s') : 'Jamais' }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div style="padding: 15px; border-radius: 8px; background-color: #eff6ff; border-left: 4px solid #3b82f6; font-size: 13px; color: #1e40af;">
                    <strong>Actions recommandées :</strong> 
                    <ul style="margin: 8px 0 0 15px; padding: 0;">
                        <li>Vérifiez l'alimentation électrique et la connectivité physique des routeurs listés.</li>
                        <li>Assurez-vous que les liaisons WAN/VPN vers ces agences sont actives au niveau opérateur.</li>
                        <li>Consultez la console de supervision pour suivre l'état de rétablissement en temps réel.</li>
                    </ul>
                </div>
                
                <div style="margin-top: 20px; font-size: 11px; color: #94a3b8; text-align: right;">
                    Généré automatiquement à : {{ $eventTime }}
                </div>
            </div>
            
            <div class="footer">
                &copy; 2026 <strong>SRM</strong> - Département Infrastructure & Réseaux<br>
                Rapport de Supervision NetDevice Pro
            </div>
        </div>
    </div>
</body>
</html>
