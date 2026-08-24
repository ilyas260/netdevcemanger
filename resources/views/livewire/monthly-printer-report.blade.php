<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mt-6">
    <div class="px-6 py-4 border-b bg-gradient-to-r from-slate-50 to-white flex justify-between items-center">
        <h3 class="font-black text-slate-800 flex items-center gap-2 uppercase tracking-tighter text-sm">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            Récapitulatif Mensuel (1 au 30/31)
        </h3>
        <div class="flex gap-2">
            <span class="text-[10px] bg-indigo-100 text-indigo-700 font-bold px-2 py-1 rounded">Auto-Snapshot activé</span>
        </div>
    </div>

    <div class="p-6">
        @if(empty($monthlyStats))
            <div class="py-12 text-center bg-slate-50 rounded-xl border border-dashed border-slate-200">
                <p class="text-xs text-slate-400">Aucune donnée mensuelle disponible pour le moment.<br>Le système doit collecter des relevés sur plusieurs jours pour comparer.</p>
            </div>
        @else
            <div class="overflow-x-auto rounded-xl border border-slate-100 shadow-sm">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-4 py-4 text-[10px] font-black text-slate-500 uppercase">Mois</th>
                            <th class="px-4 py-4 text-[10px] font-black text-slate-500 uppercase text-center">Début</th>
                            <th class="px-4 py-4 text-[10px] font-black text-slate-500 uppercase text-center">Fin</th>
                            <th class="px-4 py-4 text-[10px] font-black text-slate-500 uppercase text-center">Conso A4</th>
                            <th class="px-4 py-4 text-[10px] font-black text-slate-500 uppercase text-center">Conso A3</th>
                            <th class="px-4 py-4 text-[10px] font-black text-indigo-600 uppercase text-right">Volume Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($monthlyStats as $stat)
                            <tr class="hover:bg-indigo-50/30 transition-colors {{ $stat['is_current'] ? 'bg-indigo-50/10' : '' }}">
                                <td class="px-4 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-black text-slate-800 capitalize">{{ $stat['month_name'] }}</span>
                                        @if($stat['is_current'])
                                            <span class="text-[9px] text-indigo-500 font-bold uppercase">Mois en cours</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-xs text-slate-500 text-center font-mono">{{ number_format($stat['start_counter'], 0, ',', ' ') }}</td>
                                <td class="px-4 py-4 text-xs text-slate-500 text-center font-mono">{{ number_format($stat['end_counter'], 0, ',', ' ') }}</td>
                                <td class="px-4 py-4 text-center">
                                    <span class="px-2 py-1 bg-slate-100 text-slate-700 rounded text-[11px] font-bold">
                                        {{ number_format($stat['a4'], 0, ',', ' ') }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="px-2 py-1 bg-orange-50 text-orange-700 rounded text-[11px] font-bold">
                                        {{ number_format($stat['a3'], 0, ',', ' ') }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <span class="text-sm font-black text-indigo-700">
                                        +{{ number_format($stat['total'], 0, ',', ' ') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4 p-4 bg-amber-50 rounded-xl border border-amber-100 flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p class="text-[10px] text-amber-800 leading-relaxed">
                    <strong>Note sur l'automatisme :</strong> Pour une précision maximale du 1 au 30, assurez-vous que les imprimantes sont allumées le dernier jour du mois. Le système enregistre automatiquement un relevé de clôture après le 20 de chaque mois pour garantir vos données de facturation.
                </p>
            </div>
        @endif
    </div>
</div>
