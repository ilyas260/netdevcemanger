<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Génération de Rapports PDF') }}
        </h2>
    </x-slot>

    <div class="max-w-xl mx-auto py-10">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-200">
            <div class="p-8 bg-indigo-700 text-white">
                <h3 class="text-lg font-bold">Exporter un rapport d'activité</h3>
                <p class="text-indigo-100 text-sm opacity-80 mt-1">Générez un document PDF complet de l'état de votre parc réseau.</p>
            </div>
            
            <div class="p-8">
                <form action="{{ route('reports.generate') }}" method="POST">
                    @csrf
                    <div class="space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Date début</label>
                                <input type="date" name="start_date" value="{{ date('Y-m-d', strtotime('-7 days')) }}"
                                       class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Date fin</label>
                                <input type="date" name="end_date" value="{{ date('Y-m-d') }}"
                                       class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Type de rapport</label>
                            <select name="type" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                                <option value="full">Rapport Complet (Connectivité + Impression)</option>
                                <option value="connectivity">Audit Connectivité uniquement</option>
                                <option value="printing">Compteurs d'impression uniquement</option>
                            </select>
                        </div>

                        <div class="pt-4 border-t border-slate-100">
                            <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-lg hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all flex items-center justify-center gap-3">
                                <svg class="w-5 h-5 text-indigo-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                Télécharger le PDF
                            </button>
                        </div>
                    </div>
                </form>

                <div class="mt-8 p-4 bg-slate-50 rounded-lg border border-slate-100">
                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Contenu du rapport</h4>
                    <ul class="text-xs text-slate-600 space-y-2">
                        <li class="flex items-center gap-2">
                            <svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            Taux de disponibilité global du réseau
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            Analyse des erreurs critiques sur la période
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            Consommation papier et niveaux de toner
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
