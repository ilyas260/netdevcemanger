<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Informations Imprimante') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- En-tête --}}
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800">Imprimante : <span class="font-mono text-indigo-700">{{ $ip }}</span></h3>
                    <p class="text-xs text-gray-400 mt-0.5">Community SNMP : <span class="font-mono">{{ $community }}</span></p>
                </div>
                <a href="{{ route('printer.info', ['ip' => $ip, 'community' => $community]) }}"
                   class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md transition shadow-sm flex items-center text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Actualiser
                </a>
            </div>

            {{-- Formulaire pour changer l'IP/community --}}
            <form method="GET" action="{{ route('printer.info') }}" class="bg-white border border-gray-200 rounded-xl p-4 flex gap-3 items-end shadow-sm">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Adresse IP</label>
                    <input type="text" name="ip" value="{{ $ip }}"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm font-mono focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="w-48">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Community SNMP</label>
                    <input type="text" name="community" value="{{ $community }}"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm font-mono focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md hover:bg-gray-700 transition">
                    Tester
                </button>
            </form>
            
            @if(session('success'))
                <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm font-bold shadow-sm animate-pulse">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm font-bold shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Cartes d'état --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Connexion --}}
                <div class="bg-white border {{ $status_online ? 'border-green-200' : 'border-red-200' }} rounded-xl p-5 shadow-sm">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">Connexion réseau</p>
                    @if($status_online)
                        <div class="flex items-center text-green-600 font-bold text-lg">
                            <span class="relative flex h-3 w-3 mr-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                            </span>
                            En ligne
                        </div>
                    @else
                        <div class="flex items-center text-red-600 font-bold text-lg">
                            <span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span>
                            Hors ligne
                        </div>
                    @endif
                </div>

                {{-- Statut SNMP --}}
                <div class="bg-white border {{ $snmp_ok ? 'border-green-200' : 'border-orange-200' }} rounded-xl p-5 shadow-sm">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">SNMP</p>
                    @if($snmp_ok)
                        <div class="text-green-700 font-bold text-base">✓ Actif</div>
                        <div class="text-gray-600 text-sm mt-1">{{ $status_msg }}</div>
                        <div class="text-[10px] text-gray-400 mt-2">S/N Machine : <span class="font-mono text-gray-500">{{ $device_serial ?? 'N/A' }}</span></div>
                    @else
                        <div class="text-orange-600 font-bold text-base">✗ Non disponible</div>
                        <div class="text-gray-500 text-xs mt-1">Community "{{ $community }}" refusée</div>
                    @endif
                </div>

                {{-- Pages --}}
                <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">Compteurs de Pages</p>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Petit Format (A4)</span>
                            <span class="font-bold text-gray-800">{{ is_numeric($pages_a4) ? number_format((int)$pages_a4, 0, ',', ' ') : $pages_a4 }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Grand Format (A3)</span>
                            <span class="font-bold text-gray-800">{{ is_numeric($pages_a3) ? number_format((int)$pages_a3, 0, ',', ' ') : $pages_a3 }}</span>
                        </div>
                        <div class="pt-2 border-t border-gray-100 flex justify-between items-center">
                            <span class="text-xs font-bold text-indigo-600 uppercase">Total (A4 + A3*2)</span>
                            <span class="text-xl font-black text-indigo-700">
                                {{ isset($pages_total_calc) ? number_format($pages_total_calc, 0, ',', ' ') : (is_numeric($pages) ? number_format((int)$pages, 0, ',', ' ') : $pages) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Niveaux de toner et papier --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Toner --}}
                @if(!empty($toner_levels))
                    <div class="space-y-6">
                        {{-- Visual Levels --}}
                        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                            <h4 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                                Niveaux de Consommables
                            </h4>
                            <div class="space-y-4">
                                @foreach($toner_levels as $t)
                                    @php
                                        $pct = (int)$t['pct'];
                                        $barColor = $pct <= 10 ? 'bg-red-500' : ($pct <= 20 ? 'bg-orange-400' : (str_contains(strtolower($t['name']), 'cyan') ? 'bg-cyan-400' : (str_contains(strtolower($t['name']), 'magenta') ? 'bg-pink-500' : (str_contains(strtolower($t['name']), 'yellow') ? 'bg-yellow-400' : 'bg-gray-800'))));
                                        $textColor = $pct <= 10 ? 'text-red-600 font-bold' : ($pct <= 20 ? 'text-orange-500' : 'text-gray-700');
                                    @endphp
                                    <div>
                                        <div class="flex justify-between text-sm mb-1">
                                            <span class="text-gray-600 truncate mr-2" title="{{ $t['name'] }}">{{ $t['name'] }}</span>
                                            <span class="{{ $textColor }}">{{ $pct }}%</span>
                                        </div>
                                        <div class="text-[9px] text-gray-400 mb-1">
                                            Valeur brute : {{ $t['level'] ?? '?' }} / {{ $t['max'] ?? '?' }}
                                        </div>
                                        <div class="w-full bg-gray-100 rounded-full h-2">
                                            <div class="{{ $barColor }} h-2 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Dedicated Serial Numbers Card --}}
                        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                            <h4 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                                Inventaire (Numéros de Série)
                            </h4>
                            <div class="overflow-hidden border border-gray-100 rounded-lg">
                                <table class="min-w-full divide-y divide-gray-100">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Composant</th>
                                            <th class="px-3 py-2 text-left text-[10px] font-bold text-gray-700 uppercase tracking-wider">Référence (Modèle)</th>
                                            <th class="px-3 py-2 text-left text-[10px] font-bold text-gray-700 uppercase tracking-wider">Numéro de Série (Unique)</th>
                                            <th class="px-3 py-2 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider italic">DEBUG RAW</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-100">
                                        @foreach($toner_levels as $t)
                                            <tr>
                                                <td class="px-3 py-2 text-xs text-gray-600 font-medium">{{ $t['name'] }}</td>
                                                <td class="px-3 py-2 text-xs">
                                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-700 rounded text-[10px] font-bold border border-gray-200">
                                                        {{ $t['type'] ?? 'Standard' }}
                                                    </span>
                                                </td>
                                                <td class="px-3 py-2 text-xs font-mono text-indigo-700 font-bold">
                                                    {{ $t['serial'] ?? 'N/A' }}
                                                </td>
                                                <td class="px-3 py-2 text-[9px] text-gray-300 font-mono italic">
                                                    {{ $t['raw_serial'] ?? '?' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @elseif($snmp_ok)
                    <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                        <p class="text-sm text-gray-500">Aucune cartouche de toner détectée.</p>
                    </div>
                @endif

                {{-- Papier --}}
                @if(!empty($paper_levels))
                    <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                        <h4 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Bacs à Papier
                        </h4>
                        <div class="space-y-4">
                            @foreach($paper_levels as $p)
                                @php
                                    $pct = (int)$p['pct'];
                                    $barColor = $pct <= 5 ? 'bg-red-500' : ($pct <= 15 ? 'bg-orange-400' : 'bg-blue-500');
                                    $textColor = $pct <= 5 ? 'text-red-600 font-bold' : ($pct <= 15 ? 'text-orange-500' : 'text-gray-700');
                                @endphp
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        {{-- Affiche le nom du bac (ex: Tray 1) --}}
                                        <span class="text-gray-600 truncate mr-2" title="{{ $p['name'] }}">{{ $p['name'] }}</span>
                                        
                                        {{-- Affiche l'état ou le nombre de feuilles --}}
                                        <span class="{{ $textColor }}">{{ $p['status_text'] }}</span>
                                    </div>

                                    {{-- TEST : Affiche la variable brute --}}
                                    <div class="text-[9px] text-gray-400 mb-1">Valeur brute : {{ $p['level'] }}</div>

                                    <div class="w-full bg-gray-100 rounded-full h-2">
                                        {{-- Barre de progression visuelle --}}
                                        <div class="{{ $barColor }} h-2 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                                    </div>
                                    
                                </div>
                            @endforeach
                        </div>
                    </div>
                @elseif($snmp_ok)
                    <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
                        <p class="text-sm text-gray-500">Aucun bac à papier détecté.</p>
                    </div>
                @endif
            </div>

            {{-- Messages / Erreurs --}}
            <div class="rounded-xl p-5 border shadow-sm {{ $errors === 'Aucune erreur détectée' ? 'bg-green-50 border-green-200' : 'bg-orange-50 border-orange-200' }}">
                <div class="flex items-start">
                    @if($errors === 'Aucune erreur détectée')
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-orange-500 mr-2 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @endif
                    <div>
                        <p class="font-semibold text-sm {{ $errors === 'Aucune erreur détectée' ? 'text-green-700' : 'text-orange-700' }}">
                            {{ $errors === 'Aucune erreur détectée' ? 'Système OK' : 'Information' }}
                        </p>
                        <p class="text-sm mt-1 {{ $errors === 'Aucune erreur détectée' ? 'text-green-600' : 'text-orange-700' }} whitespace-pre-line">
                            {{ $errors }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Historique des Consommations --}}
            @php
                $device = \App\Models\Device::where('ip_address', $ip)->first();
            @endphp
            @if($device)
                @php
                    // Récupérer le relevé en direct (pour le cycle en cours)
                    $liveSnapshot = \App\Models\PrinterCounter::where('device_id', $device->id)
                        ->where('is_consumption_snapshot', false)
                        ->whereNotNull('total_pages')
                        ->where('total_pages', '>', 0)
                        ->latest('recorded_at')
                        ->first();

                    // Récupérer tous les snapshots enregistrés (débuts/fins de cycles)
                    $historySnapshots = \App\Models\PrinterCounter::where('device_id', $device->id)
                        ->where('is_consumption_snapshot', true)
                        ->whereNotNull('total_pages')
                        ->where('total_pages', '>', 0)
                        ->orderBy('recorded_at', 'desc')
                        ->get();

                    $allPoints = collect();
                    
                    // Si on a un live et qu'il est plus récent que le dernier snapshot, on l'ajoute comme point final du cycle en cours
                    if ($liveSnapshot && (!$historySnapshots->first() || $liveSnapshot->recorded_at->gt($historySnapshots->first()->recorded_at))) {
                        $allPoints->push($liveSnapshot);
                    }
                    
                    // On ajoute tous les snapshots d'historique
                    foreach ($historySnapshots as $snap) {
                        $allPoints->push($snap);
                    }

                    // On trie tous les points chronologiquement (du plus ancien au plus récent)
                    $sortedPoints = $allPoints->sortBy('recorded_at')->values();
                    $periods = collect();

                    if ($sortedPoints->count() > 0) {
                        $currentStart = $sortedPoints->first();
                        $currentEnd = $currentStart;

                        for ($i = 1; $i < $sortedPoints->count(); $i++) {
                            $point = $sortedPoints[$i];
                            $diff = $point->recorded_at->diffInDays($currentStart->recorded_at);

                            if ($diff >= 20) {
                                // On a atteint ou dépassé les 20 jours, on ferme cette période
                                $periods->prepend([
                                    'start' => $currentStart,
                                    'end' => $point
                                ]);
                                // Le point actuel devient le début de la nouvelle période
                                $currentStart = $point;
                                $currentEnd = $point;
                            } else {
                                // Toujours dans la même période de 20 jours
                                $currentEnd = $point;
                            }
                        }

                        // Si la dernière période n'a pas atteint 20 jours, on l'ajoute comme cycle en cours
                        // (seulement si le début et la fin sont différents)
                        if ($currentStart->id !== $currentEnd->id || $currentStart->recorded_at->ne($currentEnd->recorded_at)) {
                            $periods->prepend([
                                'start' => $currentStart,
                                'end' => $currentEnd
                            ]);
                        }
                    }
                @endphp

                @if($periods->count() > 0)
                    <div class="mt-8 space-y-6">
                        <h3 class="text-lg font-bold text-gray-800 border-b pb-2">Historique des Périodes de Consommation</h3>
                        
                        @foreach($periods as $index => $period)
                            @php
                                $startSnap = $period['start'];
                                $endSnap = $period['end'];

                                $intervalDays = max(1, (int) $startSnap->recorded_at->diffInDays($endSnap->recorded_at));
                                $isOver20 = $intervalDays >= 20;
                                $isCurrentCycle = ($index === 0 && !$isOver20);

                                // Compteurs Début
                                $startA3 = $startSnap->a3_pages ?: 0;
                                $startA4 = $startSnap->total_pages - $startA3;

                                // Compteurs Fin
                                $endA3 = $endSnap->a3_pages ?: 0;
                                $endA4 = $endSnap->total_pages - $endA3;

                                // Différence (Consommation de la période)
                                $diffA4 = max(0, $endA4 - $startA4);
                                $diffA3 = max(0, $endA3 - $startA3);
                            @endphp

                            <div class="rounded-2xl border {{ $isOver20 ? 'border-indigo-200' : 'border-amber-200' }} overflow-hidden shadow-sm mb-6">
                                {{-- En-tête --}}
                                <div class="flex items-center justify-between px-5 py-3.5 {{ $isOver20 ? 'bg-gradient-to-r from-indigo-600 to-violet-600' : 'bg-amber-500' }}">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="text-sm font-black text-white uppercase tracking-widest">
                                            Période du {{ $startSnap->recorded_at->format('d/m/Y') }} au {{ $endSnap->recorded_at->format('d/m/Y') }}
                                        </span>
                                    </div>
                                    <span class="text-xs font-black px-2.5 py-0.5 rounded-full bg-white/25 text-white">
                                        {{ $intervalDays }} JOURS
                                    </span>
                                </div>

                                {{-- Corps --}}
                                <div class="p-6 bg-white space-y-6">
                                    {{-- Comparatif Début / Fin --}}
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        {{-- Début --}}
                                        <div class="border border-gray-100 rounded-xl p-4 bg-gray-50">
                                            <p class="text-xs font-bold text-gray-500 uppercase mb-2">🔴 Début des 20 jours</p>
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <p class="text-[10px] text-gray-400">Compteur A4</p>
                                                    <p class="font-mono font-bold text-gray-700">{{ number_format($startA4, 0, ',', ' ') }}</p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-[10px] text-gray-400">Compteur A3</p>
                                                    <p class="font-mono font-bold text-gray-700">{{ number_format($startA3, 0, ',', ' ') }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Fin --}}
                                        <div class="border border-gray-100 rounded-xl p-4 bg-gray-50">
                                            <p class="text-xs font-bold text-gray-500 uppercase mb-2">🟢 Fin des 20 jours</p>
                                            <div class="flex justify-between items-center">
                                                <div>
                                                    <p class="text-[10px] text-gray-400">Compteur A4</p>
                                                    <p class="font-mono font-bold text-gray-700">{{ number_format($endA4, 0, ',', ' ') }}</p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-[10px] text-gray-400">Compteur A3</p>
                                                    <p class="font-mono font-bold text-gray-700">{{ number_format($endA3, 0, ',', ' ') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Différence --}}
                                    <div class="pt-4 border-t border-gray-100">
                                        <p class="text-center text-[10px] font-bold uppercase text-indigo-400 tracking-widest mb-3">
                                            Consommation totale sur la période
                                        </p>
                                        <div class="flex justify-center gap-8">
                                            <div class="text-center">
                                                <p class="text-3xl font-black text-indigo-700 leading-none">
                                                    +{{ number_format($diffA4, 0, ',', ' ') }}
                                                </p>
                                                <p class="text-[10px] text-gray-400 mt-1">A4 consommés</p>
                                            </div>
                                            <div class="text-center">
                                                <p class="text-3xl font-black text-violet-700 leading-none">
                                                    +{{ number_format($diffA3, 0, ',', ' ') }}
                                                </p>
                                                <p class="text-[10px] text-gray-400 mt-1">A3 consommés</p>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="mt-8 py-10 text-center space-y-2 rounded-2xl border border-gray-200 bg-white">
                        <svg class="w-12 h-12 mx-auto text-gray-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <p class="text-sm font-semibold text-gray-400">Premier relevé en attente</p>
                        <p class="text-xs text-gray-300">Les données s'afficheront après le 1ᵉʳ cycle automatique de 20 jours.</p>
                    </div>
                @endif
            @endif

        </div>
    </div>
</x-app-layout>
