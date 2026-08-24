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
        
        .status-banner { padding: 15px; text-align: center; font-weight: 800; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; background-color: #f0fdf4; color: #16a34a; border-bottom: 1px solid #dcfce7; }
        
        .content { padding: 40px; }
        .alert-title { font-size: 20px; font-weight: 800; color: #0f172a; margin-bottom: 8px; }
        .alert-desc { color: #64748b; font-size: 14px; margin-bottom: 30px; }
        
        .data-grid { display: grid; gap: 20px; background-color: #f8fafc; border-radius: 12px; padding: 24px; border: 1px solid #f1f5f9; }
        .data-item { margin-bottom: 16px; }
        .data-label { font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; display: block; }
        .data-value { font-size: 15px; font-weight: 700; color: #1e293b; }
        
        .resolution-box { margin-top: 20px; padding: 15px; border-radius: 8px; background-color: #f0fdf4; border-left: 4px solid #16a34a; }
        .resolution-label { font-size: 10px; font-weight: 800; color: #15803d; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; display: block; }
        .resolution-value { font-size: 14px; color: #166534; line-height: 1.6; }
        
        .footer { padding: 30px; text-align: center; font-size: 12px; color: #94a3b8; background-color: #f8fafc; border-top: 1px solid #f1f5f9; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 6px; font-size: 11px; font-weight: 800; background-color: #22c55e; color: white; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <!-- Header SRM -->
            <div class="srm-header">
                <img src="{{ $message->embed(public_path('images/srm-logo.png')) }}" alt="SRM Logo" class="srm-logo-img">
                <div class="srm-tagline">Network Supervision System</div>
            </div>

            <!-- Banner Statut -->
            <div class="status-banner">
                ✅ Résolution de problème confirmée
            </div>
            
            <div class="content">
                <div class="alert-title">Notification de Résolution</div>
                <div class="alert-desc">Un problème de connectivité a été résolu pour votre agence.</div>
                
                <div class="data-grid">
                    <div class="data-item">
                        <span class="data-label">Agence</span>
                        <span class="data-value" style="color: #3b82f6;">{{ $agencyName }}</span>
                    </div>
                    
                    <div class="data-item">
                        <span class="data-label">Adresse IP Routeur</span>
                        <span class="data-value" style="font-family: 'Courier New', monospace;">{{ $ipAddress }}</span>
                    </div>
                    
                    <div class="data-item">
                        <span class="data-label">Type de Problème</span>
                        <span class="data-value">{{ $errorType }}</span>
                    </div>
                    
                    <div class="data-item">
                        <span class="data-label">Détails du Problème</span>
                        <span class="data-value" style="font-size: 13px; color: #64748b;">{{ $errorMessage }}</span>
                    </div>

                    <div class="data-item" style="margin-bottom: 0;">
                        <span class="data-label">Horodatage de Résolution</span>
                        <span class="data-value" style="font-size: 13px; color: #64748b;">{{ $eventTime }}</span>
                    </div>
                </div>

                <div class="resolution-box">
                    @if(($solutionType ?? '') !== 'retablissement_auto')
                    <span class="resolution-label">Type de solution</span>
                    <span class="resolution-value" style="font-weight:800;">{{ $solutionLabel ?? $resolutionNote }}</span>
                    @if(!empty($resolutionNote) && ($solutionLabel ?? '') !== $resolutionNote)
                    <br><br>
                    <span class="resolution-label">Note complémentaire</span>
                    <span class="resolution-value">{{ $resolutionNote }}</span>
                    @endif
                    <br><br>
                    @endif
                    <span class="resolution-label">Résolue par</span>
                    <span class="resolution-value">{{ $resolvedBy }}</span>
                </div>
            </div>
            
            <div class="footer">
                <p>Ce message a été généré automatiquement par le système de supervision SRM.<br>Veuillez ne pas répondre à cet email.</p>
            </div>
        </div>
    </div>
</body>
</html>
