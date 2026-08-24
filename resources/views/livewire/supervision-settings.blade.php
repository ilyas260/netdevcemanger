<div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
    <div class="flex items-center gap-3 mb-4">
        <div class="bg-indigo-100 p-2 rounded-lg text-indigo-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <h3 class="font-bold text-slate-800 text-lg">Paramètres de Supervision</h3>
    </div>

    <div class="space-y-4">
        <div>
            <label for="pingInterval" class="block text-sm font-medium text-slate-700 mb-1">
                Fréquence des tests (Ping automatique)
            </label>
            <div class="flex items-center gap-3">
                <select id="pingInterval" wire:model.live="pingInterval" 
                        class="block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="1">Toutes les minutes</option>
                    <option value="2">Toutes les 2 minutes</option>
                    <option value="5">Toutes les 5 minutes (Recommandé)</option>
                    <option value="10">Toutes les 10 minutes</option>
                    <option value="15">Toutes les 15 minutes</option>
                    <option value="30">Toutes les 30 minutes</option>
                </select>
            </div>
            <p class="mt-2 text-xs text-slate-500 italic">
                L'intervalle configuré impacte la réactivité de la détection "Hors Ligne".
            </p>
        </div>

        @if (session()->has('settings_updated'))
            <div class="p-3 bg-green-50 border border-green-200 rounded-lg flex items-center gap-2 text-green-700 text-sm animate-pulse">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                {{ session('settings_updated') }}
            </div>
        @endif
    </div>
</div>
