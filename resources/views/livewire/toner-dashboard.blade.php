<div class="space-y-6" wire:init="updatePrinters" wire:poll.30s>
    <div class="flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <h2 class="text-xl font-bold text-gray-800">État des Consommables</h2>
            <div wire:loading wire:target="updatePrinters" class="flex items-center text-xs text-indigo-600 bg-indigo-50 px-2 py-1 rounded border border-indigo-100 animate-pulse">
                <svg class="animate-spin -ml-1 mr-2 h-3 w-3 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Mise à jour des niveaux...
            </div>
        </div>
        <div class="flex bg-gray-100 p-1 rounded-lg">
            <button wire:click="$set('filter', 'all')" 
                    class="px-3 py-1 text-sm rounded-md shadow-sm transition {{ $filter === 'all' ? 'bg-white text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                Toutes
            </button>
            <button wire:click="$set('filter', 'warning')" 
                    class="px-3 py-1 text-sm rounded-md transition {{ $filter === 'warning' ? 'bg-white text-orange-600' : 'text-gray-500 hover:text-gray-700' }}">
                Attention (&lt;20%)
            </button>
            <button wire:click="$set('filter', 'critical')" 
                    class="px-3 py-1 text-sm rounded-md transition {{ $filter === 'critical' ? 'bg-white text-red-600' : 'text-gray-500 hover:text-gray-700' }}">
                Critique (&lt;10%)
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($printers as $printer)
            <div wire:key="printer-{{ $printer['device']->id }}" class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden hover:shadow-lg transition-all duration-300 group">
                {{-- Header --}}
                <div class="p-5 border-b bg-gradient-to-r from-gray-50 to-white flex justify-between items-center">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-indigo-100 text-indigo-600 rounded-lg group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 leading-tight">{{ $printer['device']->name }}</h3>
                            <p class="text-xs text-gray-400 font-mono">{{ $printer['device']->ip_address }}</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $printer['is_online'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $printer['is_online'] ? 'bg-green-500 animate-pulse' : 'bg-red-500' }}"></span>
                        {{ $printer['is_online'] ? 'En Ligne' : 'Hors Ligne' }}
                    </span>
                </div>
                
                {{-- Content --}}
                <div class="p-5 space-y-5">
                    {{-- Toners --}}
                    <div class="space-y-4">
                        @php $hasLevels = false; @endphp
                        @foreach($printer['levels'] as $color => $data)
                            @if($data['pct'] !== null)
                                @php
                                    $hasLevels = true;
                                    $level = $data['pct'];
                                    $lowerColor = strtolower($color);
                                    $colorClass = match(true) {
                                        str_contains($lowerColor, 'noir') || str_contains($lowerColor, 'black') => 'bg-gray-800',
                                        str_contains($lowerColor, 'cyan') => 'bg-cyan-400',
                                        str_contains($lowerColor, 'magenta') => 'bg-pink-500',
                                        str_contains($lowerColor, 'jaune') || str_contains($lowerColor, 'yellow') => 'bg-yellow-300',
                                        str_contains($lowerColor, 'tambour') || str_contains($lowerColor, 'drum') => 'bg-orange-400',
                                        str_contains($lowerColor, 'fusion') || str_contains($lowerColor, 'fuser') => 'bg-slate-600',
                                        default => 'bg-indigo-400'
                                    };
                                    $isLow = $level < 15;
                                @endphp
                                <div class="relative">
                                    <div class="flex justify-between items-end mb-1">
                                        <span class="text-[10px] font-bold text-gray-600 tracking-tight leading-none max-w-[70%] truncate" title="{{ $color }}">
                                            {{ $color }}
                                        </span>
                                        <span class="text-xs font-black {{ $isLow ? 'text-red-600' : 'text-indigo-600' }}">
                                            {{ $level }}%
                                        </span>
                                    </div>
                                    <div class="h-2.5 w-full bg-gray-100 rounded-full overflow-hidden border border-gray-50">
                                        <div class="h-full {{ $colorClass }} rounded-full transition-all duration-1000 shadow-sm" style="width: {{ $level }}%"></div>
                                    </div>
                                    @if(isset($data['serial']) && $data['serial'] !== 'N/A')
                                        <div class="flex justify-between mt-1 text-[9px]">
                                            <span class="text-gray-400">S/N : <span class="font-mono text-gray-500">{{ $data['serial'] }}</span></span>
                                            <span class="text-indigo-500 font-medium">Type : {{ $data['type'] ?? 'Standard' }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endforeach

                        @if(!$hasLevels)
                            <div class="py-6 text-center border-2 border-dashed border-gray-100 rounded-2xl bg-gray-50/50">
                                <div wire:loading wire:target="updatePrinters" class="flex flex-col items-center">
                                    <svg class="animate-spin h-5 w-5 text-indigo-600 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <p class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Scan en cours...</p>
                                </div>
                                <div wire:loading.remove wire:target="updatePrinters">
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Niveaux non disponibles</p>
                                    <p class="text-[9px] text-gray-300 mt-1">Le scan automatique n'a pas pu récupérer les données</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Carte Consommation 20 jours --}}
                    @php
                        $intervalDays  = $printer['interval_days']  ?? 0;
                        $consA4        = $printer['consumption_a4'] ?? 0;
                        $consA3        = $printer['consumption_a3'] ?? 0;
                        $avgDailyA4    = $printer['avg_daily_a4']   ?? null;
                        $prevSnap      = $printer['prev_snapshot']  ?? null;
                        $lastSnap      = $printer['last_snapshot']  ?? null;
                        $hasInterval   = $lastSnap && $prevSnap && $intervalDays > 0;
                        $isOver20      = $intervalDays >= 20;
                    @endphp
                    <div class="pt-4 border-t border-gray-50">
                        <div class="rounded-xl border overflow-hidden
                            {{ $isOver20 ? 'border-indigo-100 bg-gradient-to-br from-indigo-50/80 to-violet-50/60' : 'border-gray-100 bg-gray-50' }}">
                            {{-- En-tête de la carte --}}
                            <div class="flex items-center justify-between px-3 py-2
                                {{ $isOver20 ? 'bg-indigo-600' : 'bg-gray-400' }}">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                    <span class="text-[10px] font-black text-white uppercase tracking-widest">
                                        Consommation
                                    </span>
                                </div>
                                @if($hasInterval)
                                    <span class="text-[9px] font-black px-2 py-0.5 rounded-full
                                        {{ $isOver20 ? 'bg-white/20 text-white' : 'bg-white/30 text-white' }}">
                                        {{ $intervalDays }} JOURS
                                    </span>
                                @else
                                    <span class="text-[9px] text-white/70">En attente...</span>
                                @endif
                            </div>

                            {{-- Contenu principal --}}
                            <div class="p-3 space-y-2">
                                @if($hasInterval)
                                    @if(!$isOver20)
                                        <div class="bg-amber-50 border border-amber-100 rounded-lg px-2.5 py-2 flex items-center justify-center gap-2 mb-2">
                                            <svg class="w-4 h-4 text-amber-500 shrink-0 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <p class="text-[10px] text-amber-700 font-bold leading-tight">
                                                Cycle de 20 jours en cours ({{ 20 - $intervalDays }}j restants)
                                             </p>
                                        </div>
                                    @endif

                                    {{-- Valeurs A4 & A3 --}}
                                    <div class="grid grid-cols-2 gap-2">
                                        {{-- A4 --}}
                                        <div class="bg-white rounded-lg border border-indigo-100 p-2 shadow-sm text-center">
                                            <p class="text-[9px] font-bold uppercase leading-none mb-1 text-indigo-500">
                                                A4 consommés
                                            </p>
                                            <p class="text-base font-black leading-none text-indigo-700">
                                                {{ number_format($consA4, 0, ',', ' ') }}
                                            </p>
                                            <p class="text-[8px] text-gray-400 mt-0.5">feuilles</p>
                                        </div>
                                        {{-- A3 --}}
                                        <div class="bg-white rounded-lg border border-violet-100 p-2 shadow-sm text-center">
                                            <p class="text-[9px] font-bold uppercase leading-none mb-1 text-violet-500">
                                                A3 consommés
                                            </p>
                                            <p class="text-base font-black leading-none text-violet-700">
                                                {{ number_format($consA3, 0, ',', ' ') }}
                                            </p>
                                            <p class="text-[8px] text-gray-400 mt-0.5">feuilles</p>
                                        </div>
                                    </div>

                                    {{-- Moyenne journalière --}}
                                    @if($intervalDays > 0)
                                        @php $currentAvgA4 = round($consA4 / $intervalDays, 1); @endphp
                                        <div class="bg-gradient-to-r from-indigo-600 to-violet-600 rounded-lg p-2.5 flex items-center justify-between shadow-sm">
                                            <div class="flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                                </svg>
                                                <span class="text-[9px] font-bold text-indigo-100 uppercase tracking-wider">Moy. / jour A4</span>
                                            </div>
                                            <span class="text-sm font-black text-white">
                                                ~{{ number_format($currentAvgA4, 1, ',', ' ') }} <span class="text-[9px] font-normal text-indigo-200">feuilles/j</span>
                                            </span>
                                        </div>
                                    @endif

                                    {{-- Dates de la période --}}
                                    <div class="flex items-center justify-between text-[8px] text-gray-400 font-mono px-0.5 mt-2 border-t border-gray-100 pt-2">
                                        <span>{{ $prevSnap->recorded_at->format('d/m/Y') }}</span>
                                        <span class="flex-1 mx-1 border-t border-dashed border-gray-200"></span>
                                        <span>{{ $lastSnap->recorded_at->format('d/m/Y') }}</span>
                                    </div>
                                @else
                                    <div class="py-3 text-center">
                                        <svg class="w-8 h-8 mx-auto text-gray-200 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                        </svg>
                                        <p class="text-[9px] text-gray-400 font-semibold">Premier relevé en attente</p>
                                        <p class="text-[8px] text-gray-300 mt-0.5">Les données s'affichent après le 1er cycle de 20 jours</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="pt-4 border-t flex items-center justify-between">
                        <div class="flex flex-col">
                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">État système :</span>
                            <span class="text-xs font-bold text-gray-700">
                                {{ $printer['status'] ?: 'Prêt' }}
                            </span>
                        </div>
                        <a href="{{ route('printer.info', ['ip' => $printer['device']->ip_address]) }}" 
                           class="inline-flex items-center px-3 py-1.5 bg-indigo-50 border border-indigo-100 text-indigo-600 text-[10px] font-bold rounded-lg hover:bg-indigo-600 hover:text-white transition-all duration-300">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            INFOS SNMP
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($printers->isEmpty())
        <div class="bg-white p-12 rounded-xl border-2 border-dashed border-gray-200 text-center">
            <p class="text-gray-500">Aucune imprimante ne correspond à ce filtre.</p>
        </div>
    @endif
</div>
