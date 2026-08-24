<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 md:gap-0 group">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-white rounded-xl shadow-sm border border-slate-200 shrink-0 transition-all duration-500 group-hover:scale-110 group-hover:rotate-3 group-hover:shadow-md group-hover:bg-indigo-50 group-hover:border-indigo-200 text-slate-500 group-hover:text-indigo-600">
                    <x-device-icon :type="$device->type" class="w-8 h-8" />
                </div>
                <div>
                    <h2 class="font-bold text-xl md:text-2xl text-slate-800 leading-tight truncate max-w-[200px] md:max-w-none">{{ $device->name }}</h2>
                    <p class="text-xs md:text-sm text-slate-500 font-mono">{{ $device->ip_address }} • {{ ucfirst($device->brand) }} {{ $device->model }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 w-full md:w-auto">
                @if($device->type === 'imprimante')
                    <form action="{{ route('devices.sync-snmp', $device) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-indigo-50 border border-indigo-100 rounded-lg text-sm font-semibold text-indigo-700 hover:bg-indigo-100 hover:shadow-md hover:-translate-y-0.5 active:scale-95 transition-all duration-200 flex items-center gap-2 group">
                            <svg class="w-4 h-4 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            Synchroniser SNMP
                        </button>
                    </form>
                @endif
                <a href="{{ route('devices.edit', $device) }}" class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:shadow-md hover:-translate-y-0.5 active:scale-95 transition-all duration-200">Modifier</a>
                <a href="{{ route('devices.index') }}" class="px-4 py-2 bg-slate-800 rounded-lg text-sm font-semibold text-white hover:bg-slate-900 hover:shadow-md hover:shadow-slate-800/30 hover:-translate-y-0.5 active:scale-95 transition-all duration-200">Retour liste</a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Diagnostic Panel (Uptime/Ping) -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Live Ping Tool -->
                <div class="transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    @livewire('ping-form', ['device' => $device])
                </div>

                <!-- Quick Stats -->
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-50/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 relative z-10">Informations Système</h3>
                    <div class="space-y-4 text-sm relative z-10">
                        <div class="flex justify-between items-center group/item hover:bg-slate-50 p-2 -mx-2 rounded-lg transition-colors">
                            <span class="text-slate-500">Statut supervision</span>
                            <span class="flex items-center gap-2 {{ $device->is_active ? 'text-green-600 font-bold' : 'text-slate-400' }}">
                                @if($device->is_active)
                                    <span class="relative flex h-2.5 w-2.5">
                                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                      <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                                    </span>
                                @endif
                                {{ $device->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center group/item hover:bg-slate-50 p-2 -mx-2 rounded-lg transition-colors">
                            <span class="text-slate-500">Agence</span>
                            <span class="font-medium">
                                @if($device->agency)
                                    <a href="{{ route('agencies.show', $device->agency_id) }}" class="text-indigo-600 hover:text-indigo-800 hover:underline transition-colors">{{ $device->agency->name }}</a>
                                @else
                                    <span class="text-slate-400 italic">Aucune</span>
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between items-center group/item hover:bg-slate-50 p-2 -mx-2 rounded-lg transition-colors">
                            <span class="text-slate-500">Dernier Ping</span>
                            <span class="text-slate-900 font-medium">{{ $device->last_seen_at ? $device->last_seen_at->format('d/m H:i') : 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between items-center group/item hover:bg-slate-50 p-2 -mx-2 rounded-lg transition-colors">
                            <span class="text-slate-500">Emplacement</span>
                            <span class="text-slate-900 font-medium">{{ $device->location ?? 'Non précisé' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Panel -->
            <div class="lg:col-span-2 space-y-6">
                <!-- SNMP Counters (Only for Printers) -->
                @if($device->type === 'imprimante')
                
                @livewire('printer-consumption', ['device' => $device])



                <!-- Recent Ping Logs -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                    <div class="px-6 py-4 border-b bg-slate-50 flex justify-between items-center group">
                        <h3 class="font-bold text-slate-800">Derniers Diagnostics Réseau</h3>
                        <a href="{{ route('devices.ping-history', $device) }}" class="text-xs font-semibold text-indigo-600 group-hover:text-indigo-800 group-hover:translate-x-1 transition-all">Voir historique complet →</a>
                    </div>
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-[10px] font-bold text-slate-500 uppercase">Heure</th>
                                <th class="px-6 py-3 text-left text-[10px] font-bold text-slate-500 uppercase">Statut</th>
                                <th class="px-6 py-3 text-left text-[10px] font-bold text-slate-500 uppercase">Latence</th>
                                <th class="px-6 py-3 text-left text-[10px] font-bold text-slate-500 uppercase">Perte</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($device->pingLogs as $log)
                                <tr class="hover:bg-indigo-50/30 transition-colors duration-200">
                                    <td class="px-6 py-4 text-slate-500">{{ $log->tested_at->format('H:i:s') }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $log->status === 'online' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $log->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-mono">{{ $log->avg_latency_ms ?? '--' }}ms</td>
                                    <td class="px-6 py-4 text-{{ $log->packet_loss_pct > 0 ? 'red' : 'slate' }}-600">{{ $log->packet_loss_pct }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
