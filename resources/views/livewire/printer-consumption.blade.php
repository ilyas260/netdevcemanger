<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-lg transition-all duration-300">
    <div class="px-6 py-4 border-b bg-slate-50 flex justify-between items-center">
        <h3 class="font-bold text-slate-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            Calcul de Consommation
        </h3>
        @if($periodDays > 20)
            <span class="text-[10px] bg-amber-100 text-amber-700 font-bold px-2 py-1 rounded animate-pulse">Période > 20 jours</span>
        @endif
    </div>

    @if (session()->has('message'))
        <div class="mx-6 mt-4 p-3 bg-green-50 border border-green-200 text-green-700 text-xs rounded-lg font-bold">
            {{ session('message') }}
        </div>
    @endif

    <div class="p-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Date début</label>
                    <input type="date" wire:model.live="startDate" wire:change="calculate" class="w-full border-slate-200 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Heure début</label>
                    <input type="time" wire:model.live="startTime" wire:change="calculate" class="w-full border-slate-200 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Date fin</label>
                    <input type="date" wire:model.live="endDate" wire:change="calculate" class="w-full border-slate-200 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Heure fin</label>
                    <input type="time" wire:model.live="endTime" wire:change="calculate" class="w-full border-slate-200 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
        </div>

        @if($consumption)
            <div class="bg-indigo-50/50 rounded-xl p-4 border border-indigo-100">
                <div class="flex justify-between items-center mb-3">
                    <p class="text-[10px] text-slate-400 font-bold uppercase">Consommation sur {{ $periodDays }} jours</p>
                    @if($startDate === Carbon\Carbon::now()->startOfMonth()->format('Y-m-d'))
                        <span class="text-[9px] bg-green-100 text-green-700 font-black px-2 py-0.5 rounded uppercase tracking-wider">Mois en cours</span>
                    @endif
                </div>
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <p class="text-xs text-slate-500">A4</p>
                        <p class="text-xl font-black text-slate-800">{{ number_format($consumption['a4']) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">A3</p>
                        <p class="text-xl font-black text-slate-800">{{ number_format($consumption['a3']) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-indigo-600 font-bold">TOTAL (Unités)</p>
                        <p class="text-2xl font-black text-indigo-700">{{ number_format($consumption['total']) }}</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-indigo-100/50 flex justify-between items-center text-[10px] text-slate-400 italic">
                    <span>Relevé initial : {{ $consumption['start_date']->format('d/m/Y H:i') }}</span>
                    <span>Relevé final : {{ $consumption['end_date']->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        @else
            <div class="bg-slate-50 rounded-xl p-8 text-center border border-slate-100 border-dashed">
                <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p class="text-xs text-slate-400">Sélectionnez deux dates pour calculer la consommation.<br>Il doit y avoir au moins deux relevés différents dans cette période.</p>
            </div>
        @endif

        <div class="flex justify-between items-center gap-2">
            <div>
                <button wire:click="$toggle('showManualForm')" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-xs font-bold hover:bg-slate-200 transition-colors">
                    {{ $showManualForm ? 'Annuler' : 'Saisie manuelle' }}
                </button>
            </div>
            <div class="flex gap-2">
                @if($periodDays > 20)
                    <button wire:click="saveSnapshot" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-bold hover:bg-indigo-700 transition-colors shadow-sm shadow-indigo-200">
                        Stocker Snapshot Actuel
                    </button>
                @endif
                <button wire:click="calculate" class="px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-lg text-xs font-bold hover:bg-slate-50 transition-colors">
                    Rafraîchir
                </button>
            </div>
        </div>

        @if($showManualForm)
            <div class="mt-6 pt-6 border-t border-slate-100 bg-slate-50 -mx-6 px-6 pb-6 animate-fadeIn">
                <h4 class="text-xs font-black text-slate-700 uppercase tracking-widest mb-4">Préciser un relevé manuel</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Date</label>
                        <input type="date" wire:model="manualDate" class="w-full border-slate-200 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Heure</label>
                        <input type="time" wire:model="manualTime" class="w-full border-slate-200 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Compteur A4</label>
                        <input type="number" wire:model="manualA4" placeholder="0" class="w-full border-slate-200 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Compteur A3</label>
                        <input type="number" wire:model="manualA3" placeholder="0" class="w-full border-slate-200 rounded-lg text-sm">
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button wire:click="addManualRecord" class="px-6 py-2 bg-green-600 text-white rounded-lg text-xs font-black hover:bg-green-700 transition-all shadow-md shadow-green-100">
                        Enregistrer le relevé
                     </button>
                </div>
            </div>
        @endif


    </div>
</div>
