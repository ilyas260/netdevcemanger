<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Analyse de Connectivité') }}
        </h2>
    </x-slot>

    <div class="space-y-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <h3 class="text-lg font-bold text-slate-800 mb-4">État global du réseau</h3>
            <p class="text-sm text-slate-500 italic">Cette page compile les données de latence de tous les équipements supervisés.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Latence Moyenne -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Latences Critiques (> 500ms)</h4>
                @php
                    $slowDevices = \App\Models\PingLog::where('status', 'slow')
                        ->where('tested_at', '>', now()->subDay())
                        ->with('device')
                        ->latest()
                        ->get();
                @endphp
                
                <div class="space-y-3">
                    @forelse($slowDevices as $log)
                        <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
                            <span class="text-sm font-semibold text-slate-700">{{ $log->device->name }}</span>
                            <span class="text-sm font-bold text-orange-600">{{ $log->avg_latency_ms }} ms</span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400 text-center py-4">Aucune latence excessive détectée.</p>
                    @endforelse
                </div>
            </div>

            <!-- Historique Offline -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Dernières Déconnexions</h4>
                @php
                    $offlineLogs = \App\Models\PingLog::where('status', 'offline')
                        ->where('tested_at', '>', now()->subDays(3))
                        ->with('device')
                        ->latest()
                        ->limit(10)
                        ->get();
                @endphp
                
                <div class="space-y-3">
                    @forelse($offlineLogs as $log)
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                            <div>
                                <span class="text-sm font-semibold text-slate-700 block">{{ $log->device->name }}</span>
                                <span class="text-[10px] text-slate-400">{{ $log->tested_at->diffForHumans() }}</span>
                            </div>
                            <span class="text-xs font-bold text-red-600 bg-white px-2 py-1 rounded">HORLINE</span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400 text-center py-4">Réseau 100% opérationnel ces 3 derniers jours.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
