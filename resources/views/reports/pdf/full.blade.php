<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Rapport NetDevice Manager</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #1e293b;
            background: #fff;
            line-height: 1.5;
        }

        /* ── PAGE FOOTER ── */
        .page-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30px;
            border-top: 1px solid #e2e8f0;
            display: table;
            width: 100%;
        }
        .page-footer td {
            font-size: 9px;
            color: #94a3b8;
            vertical-align: middle;
            padding: 0 16px;
            border: none !important;
        }

        /* ── HEADER BANNER ── */
        .report-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: #fff;
            padding: 24px 28px;
            margin-bottom: 20px;
        }
        .report-header table { border: none; margin: 0; width: 100%; }
        .report-header td   { border: none !important; padding: 0; vertical-align: middle; }
        .report-title       { font-size: 22px; font-weight: bold; letter-spacing: -0.5px; }
        .report-subtitle    { font-size: 11px; color: rgba(255,255,255,0.75); margin-top: 3px; }
        .report-meta        { text-align: right; }
        .report-meta .period{ font-size: 12px; font-weight: bold; }
        .report-meta .sub   { font-size: 9px; color: rgba(255,255,255,0.65); margin-top: 4px; }

        /* ── SECTION TITLE ── */
        .section-title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #4f46e5;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 5px;
            margin: 22px 0 12px 0;
        }
        .section-title-red { color: #dc2626; border-color: #dc2626; }
        .section-title-teal { color: #0d9488; border-color: #0d9488; }

        /* ── KPI CARDS ── */
        .kpi-row { width: 100%; margin-bottom: 18px; }
        .kpi-row td { width: 16.6%; padding: 0 4px; border: none !important; vertical-align: top; }
        .kpi-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 8px;
            text-align: center;
        }
        .kpi-card.highlight { background: #eef2ff; border-color: #c7d2fe; }
        .kpi-card.danger    { background: #fef2f2; border-color: #fecaca; }
        .kpi-card.success   { background: #f0fdf4; border-color: #bbf7d0; }
        .kpi-label { font-size: 8px; text-transform: uppercase; color: #64748b; font-weight: bold; letter-spacing: 0.3px; margin-bottom: 4px; }
        .kpi-value { font-size: 20px; font-weight: bold; color: #1e293b; }
        .kpi-value.green { color: #16a34a; }
        .kpi-value.red   { color: #dc2626; }
        .kpi-value.blue  { color: #4f46e5; }

        /* ── AVAILABILITY BAR ── */
        .avail-bar-wrap {
            background: #f1f5f9;
            border-radius: 4px;
            height: 14px;
            width: 100%;
            overflow: hidden;
            margin: 6px 0;
        }
        .avail-bar {
            height: 14px;
            border-radius: 4px;
            font-size: 8px;
            color: #fff;
            font-weight: bold;
            text-align: right;
            padding-right: 5px;
            line-height: 14px;
        }

        /* ── TABLES ── */
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 10px;
        }
        table.data th {
            background: #4f46e5;
            color: #fff;
            padding: 7px 10px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-weight: bold;
        }
        table.data td {
            padding: 7px 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        table.data tr:nth-child(even) td { background: #fafafa; }
        table.data.red-header th { background: #dc2626; }
        table.data.teal-header th { background: #0d9488; }

        /* ── BADGES ── */
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 9px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-green  { background: #dcfce7; color: #166534; }
        .badge-red    { background: #fee2e2; color: #991b1b; }
        .badge-orange { background: #fff7ed; color: #9a3412; }
        .badge-gray   { background: #f1f5f9; color: #475569; }
        .badge-blue   { background: #eff6ff; color: #1e40af; }
        .badge-critical { background: #7f1d1d; color: #fff; }

        /* ── TONER BARS ── */
        .toner-bar-wrap { background: #e2e8f0; border-radius: 3px; height: 8px; width: 100%; overflow: hidden; }
        .toner-bar      { height: 8px; border-radius: 3px; }

        /* ── MISC ── */
        .text-muted { color: #94a3b8; font-style: italic; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .no-data {
            text-align: center;
            color: #94a3b8;
            font-style: italic;
            padding: 14px;
            font-size: 10px;
        }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>

    {{-- ═══ FOOTER (fixed on all pages) ═══ --}}
    <div class="page-footer">
        <table><tr>
            <td>NetDevice Manager — Rapport d'Activité & Supervision Réseau</td>
            <td class="text-right">Généré le {{ $generated_at }} par {{ $generated_by }}</td>
        </tr></table>
    </div>

    {{-- ═══ HEADER BANNER ═══ --}}
    <div class="report-header">
        <table>
            <tr>
                <td style="width:60%;">
                    <div class="report-title">NetDevice Manager</div>
                    <div class="report-subtitle">Rapport d'activité & supervision réseau</div>
                </td>
                <td class="report-meta">
                    <div class="period">Période analysée</div>
                    <div style="font-size:13px; font-weight:bold; margin-top:3px;">
                        {{ \Carbon\Carbon::parse($period['start'])->format('d/m/Y') }}
                        &nbsp;→&nbsp;
                        {{ \Carbon\Carbon::parse($period['end'])->format('d/m/Y') }}
                    </div>
                    <div class="sub">Rapport généré le {{ $generated_at }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ═══ KPI CARDS ═══ --}}
    <table class="kpi-row">
        <tr>
            <td>
                <div class="kpi-card highlight">
                    <div class="kpi-label">Agences</div>
                    <div class="kpi-value blue">{{ $stats['total_agencies'] }}</div>
                </div>
            </td>
            <td>
                <div class="kpi-card success">
                    <div class="kpi-label">Agences en ligne</div>
                    <div class="kpi-value green">{{ $stats['agencies_online'] }}</div>
                </div>
            </td>
            <td>
                <div class="kpi-card {{ $stats['agencies_offline'] > 0 ? 'danger' : '' }}">
                    <div class="kpi-label">Agences hors ligne</div>
                    <div class="kpi-value {{ $stats['agencies_offline'] > 0 ? 'red' : '' }}">{{ $stats['agencies_offline'] }}</div>
                </div>
            </td>
            <td>
                <div class="kpi-card">
                    <div class="kpi-label">Équipements</div>
                    <div class="kpi-value blue">{{ $stats['total_devices'] }}</div>
                </div>
            </td>
            <td>
                <div class="kpi-card">
                    <div class="kpi-label">Disponibilité</div>
                    <div class="kpi-value {{ $stats['availability_rate'] >= 95 ? 'green' : ($stats['availability_rate'] >= 80 ? '' : 'red') }}">{{ $stats['availability_rate'] }}%</div>
                </div>
            </td>
            <td>
                <div class="kpi-card {{ $stats['total_errors'] > 0 ? 'danger' : '' }}">
                    <div class="kpi-label">Erreurs (période)</div>
                    <div class="kpi-value {{ $stats['total_errors'] > 0 ? 'red' : 'green' }}">{{ $stats['total_errors'] }}</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- ─── Taux de disponibilité ─── --}}
    <div class="section-title">📡 Connectivité & Disponibilité</div>
    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:14px 16px; margin-bottom:12px;">
        <table style="border:none; width:100%;">
            <tr>
                <td style="border:none; width:60%; padding:0 12px 0 0; vertical-align:top;">
                    <div style="font-weight:bold; font-size:11px; margin-bottom:6px; color:#1e293b;">Taux de disponibilité global</div>
                    <div class="avail-bar-wrap">
                        <div class="avail-bar" style="width:{{ $connectivity['availability_rate'] }}%; background:{{ $connectivity['availability_rate'] >= 95 ? '#16a34a' : ($connectivity['availability_rate'] >= 80 ? '#f59e0b' : '#dc2626') }};">
                            {{ $connectivity['availability_rate'] }}%
                        </div>
                    </div>
                    <div style="font-size:9px; color:#64748b; margin-top:4px;">
                        {{ number_format($connectivity['online_count'] ?? 0) }} tests réussis /
                        {{ number_format($connectivity['total_pings'] ?? 0) }} tests totaux
                    </div>
                </td>
                <td style="border:none; padding:0; vertical-align:top;">
                    <table style="border:none; width:100%;">
                        <tr>
                            <td style="border:none; padding:4px 8px; text-align:center;">
                                <div style="font-size:18px; font-weight:bold; color:#16a34a;">{{ $connectivity['online_count'] ?? 0 }}</div>
                                <div style="font-size:8px; color:#64748b; text-transform:uppercase;">Tests OK</div>
                            </td>
                            <td style="border:none; padding:4px 8px; text-align:center;">
                                <div style="font-size:18px; font-weight:bold; color:#dc2626;">{{ $connectivity['offline_count'] ?? 0 }}</div>
                                <div style="font-size:8px; color:#64748b; text-transform:uppercase;">Échecs</div>
                            </td>
                            <td style="border:none; padding:4px 8px; text-align:center;">
                                <div style="font-size:18px; font-weight:bold; color:#4f46e5;">{{ $stats['unresolved_errors'] }}</div>
                                <div style="font-size:8px; color:#64748b; text-transform:uppercase;">Non résolus</div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    {{-- ─── Appareils instables ─── --}}
    @if(count($top_disconnected) > 0)
    <div style="font-size:10px; font-weight:bold; margin-bottom:6px; color:#475569;">Top équipements instables (sur la période)</div>
    <table class="data">
        <thead>
            <tr>
                <th style="width:40%;">Équipement</th>
                <th style="width:20%;">Type</th>
                <th>Déconnexions détectées</th>
            </tr>
        </thead>
        <tbody>
            @foreach($top_disconnected as $i => $item)
            <tr>
                <td class="bold">{{ $item['name'] }}</td>
                <td><span class="badge badge-blue">{{ ucfirst($item['type']) }}</span></td>
                <td>
                    <span class="badge {{ $item['offline_events'] > 50 ? 'badge-critical' : ($item['offline_events'] > 20 ? 'badge-red' : 'badge-orange') }}">
                        {{ $item['offline_events'] }} événements
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-data">✅ Aucun équipement instable détecté sur la période.</div>
    @endif

    {{-- ═══ PAGE 2 : AGENCES ═══ --}}
    <div class="page-break"></div>

    <div class="section-title">🏢 État des Agences</div>
    @if(count($agencies) > 0)
    <table class="data">
        <thead>
            <tr>
                <th style="width:22%;">Agence</th>
                <th style="width:15%;">Localisation</th>
                <th style="width:14%;">IP Routeur</th>
                <th style="width:10%;">Équipements</th>
                <th style="width:14%;">ND Technique</th>
                <th style="width:14%;">Dernier Ping</th>
                <th style="width:11%;">Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($agencies as $agency)
            <tr>
                <td class="bold">{{ $agency['name'] }}</td>
                <td>{{ $agency['location'] }}</td>
                <td style="font-family:monospace; font-size:9px;">{{ $agency['router_ip'] }}</td>
                <td class="text-center">{{ $agency['devices'] }}</td>
                <td>{{ $agency['nd_technique'] }}</td>
                <td style="font-size:9px;">{{ $agency['last_ping'] }}</td>
                <td class="text-center">
                    @if($agency['status'] === 'online')
                        <span class="badge badge-green">En ligne</span>
                    @elseif($agency['status'] === 'offline')
                        <span class="badge badge-red">Hors ligne</span>
                    @else
                        <span class="badge badge-gray">Inconnu</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-data">Aucune agence enregistrée.</div>
    @endif

    {{-- ═══ ERREURS RÉCENTES ═══ --}}
    <div class="section-title section-title-red">⚠️ Journal des Erreurs ({{ count($recent_errors) }} événements)</div>
    @if(count($recent_errors) > 0)
    <table class="data red-header">
        <thead>
            <tr>
                <th style="width:12%;">Date</th>
                <th style="width:10%;">Sévérité</th>
                <th style="width:18%;">Équipement</th>
                <th style="width:35%;">Message</th>
                <th style="width:15%;">Diagnostic</th>
                <th style="width:10%;">Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recent_errors as $error)
            <tr>
                <td style="font-size:9px; white-space:nowrap;">{{ $error['date'] }}</td>
                <td>
                    @if($error['severity'] === 'CRITICAL')
                        <span class="badge badge-critical">{{ $error['severity'] }}</span>
                    @elseif($error['severity'] === 'ERROR')
                        <span class="badge badge-red">{{ $error['severity'] }}</span>
                    @else
                        <span class="badge badge-orange">{{ $error['severity'] }}</span>
                    @endif
                </td>
                <td class="bold">{{ $error['device'] }}</td>
                <td style="font-size:9px; color:#475569;">{{ $error['message'] }}</td>
                <td style="font-size:9px;">{{ $error['solution'] }}</td>
                <td class="text-center">
                    @if($error['is_resolved'])
                        <span class="badge badge-green">Résolu</span>
                    @else
                        <span class="badge badge-red">Non résolu</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-data">✅ Aucune erreur enregistrée sur cette période.</div>
    @endif

    {{-- ═══ PAGE 3 : IMPRESSION & TONER ═══ --}}
    <div class="page-break"></div>

    {{-- ─── Stats d'impression ─── --}}
    <div class="section-title section-title-teal">🖨️ Statistiques d'Impression</div>
    @if(count($printing) > 0)
    <table class="data teal-header">
        <thead>
            <tr>
                <th style="width:50%;">Imprimante</th>
                <th>Pages imprimées (période)</th>
                <th>Compteur total actuel</th>
            </tr>
        </thead>
        <tbody>
            @foreach($printing as $print)
            <tr>
                <td class="bold">{{ $print['device_name'] }}</td>
                <td class="bold" style="color:#0d9488;">{{ number_format($print['pages_printed']) }} pages</td>
                <td>{{ number_format($print['current_total']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-data">Aucune donnée d'impression disponible pour cette période.</div>
    @endif

    {{-- ─── Niveaux de toner ─── --}}
    <div class="section-title section-title-teal" style="margin-top:24px;">🔋 Niveaux de Toner Actuels</div>
    @if(count($current_toner) > 0)
    <table class="data teal-header">
        <thead>
            <tr>
                <th style="width:30%;">Imprimante</th>
                <th>Noir</th>
                <th>Cyan</th>
                <th>Magenta</th>
                <th>Jaune</th>
            </tr>
        </thead>
        <tbody>
            @foreach($current_toner as $toner)
            <tr>
                <td class="bold">{{ $toner['device_name'] }}</td>
                @if($toner['toner'] && ($toner['toner']['black'] !== null || $toner['toner']['cyan'] !== null))
                    @php
                        $colors = [
                            'black'   => ['label' => $toner['toner']['black'],   'bar' => '#1e293b'],
                            'cyan'    => ['label' => $toner['toner']['cyan'],    'bar' => '#0891b2'],
                            'magenta' => ['label' => $toner['toner']['magenta'], 'bar' => '#be185d'],
                            'yellow'  => ['label' => $toner['toner']['yellow'],  'bar' => '#ca8a04'],
                        ];
                    @endphp
                    @foreach($colors as $key => $c)
                    <td>
                        @if($c['label'] !== null)
                            <div style="font-size:9px; margin-bottom:3px; {{ $c['label'] < 10 ? 'color:#dc2626; font-weight:bold;' : '' }}">
                                {{ $c['label'] }}%
                            </div>
                            <div class="toner-bar-wrap">
                                <div class="toner-bar" style="width:{{ min(100,$c['label']) }}%; background:{{ $c['label'] < 10 ? '#dc2626' : ($c['label'] < 25 ? '#f59e0b' : $c['bar']) }};"></div>
                            </div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    @endforeach
                @else
                    <td colspan="4" class="text-muted text-center">Données non disponibles</td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-data">Aucune imprimante connectée ou aucune donnée de toner disponible.</div>
    @endif

</body>
</html>
