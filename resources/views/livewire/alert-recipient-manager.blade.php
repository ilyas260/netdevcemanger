<div class="space-y-6">
    {{-- Formulaire d'ajout --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b bg-gradient-to-r from-slate-50 to-white">
            <h3 class="font-black text-slate-800 flex items-center gap-2 uppercase tracking-tighter">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                Ajouter un destinataire d'alerte
            </h3>
        </div>
        
        <div class="p-6">
            <form wire:submit.prevent="addRecipient" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1 w-full">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nom / Prénom</label>
                    <input type="text" wire:model="name" placeholder="ex: Admin Technique" 
                           class="w-full bg-slate-50 border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    @error('name') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>
                
                <div class="flex-1 w-full">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Adresse Email</label>
                    <input type="email" wire:model="email" placeholder="admin@entreprise.com" 
                           class="w-full bg-slate-50 border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    @error('email') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                </div>
                
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl font-bold text-sm transition-all shadow-lg shadow-indigo-100 flex items-center gap-2 group whitespace-nowrap">
                    <span>Ajouter à la liste</span>
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                </button>
            </form>
            
            @if (session()->has('message'))
                <div class="mt-4 p-3 bg-green-50 text-green-700 rounded-lg text-sm font-medium border border-green-100 animate-fadeIn">
                    {{ session('message') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="mt-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm font-medium border border-red-100 animate-fadeIn">
                    {{ session('error') }}
                </div>
            @endif
        </div>
    </div>

    {{-- Liste des destinataires --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b bg-slate-50 flex justify-between items-center">
            <h3 class="font-black text-slate-800 flex items-center gap-2 uppercase tracking-tighter">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Destinataires Actifs ({{ $recipients->count() }})
            </h3>
            <span class="text-[10px] bg-indigo-100 text-indigo-700 px-2 py-1 rounded font-bold uppercase">Supervision Active</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Administrateur</th>
                        <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Email</th>
                        <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Statut</th>
                        <th class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recipients as $recipient)
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-700">{{ $recipient->name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-slate-500 font-medium">{{ $recipient->email }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button wire:click="toggleStatus({{ $recipient->id }})" 
                                        class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tight transition-all {{ $recipient->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-slate-100 text-slate-400 hover:bg-slate-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full mr-2 {{ $recipient->is_active ? 'bg-green-500 animate-pulse' : 'bg-slate-400' }}"></span>
                                    {{ $recipient->is_active ? 'Activé' : 'Désactivé' }}
                                </button>
                            </td>
                            <td class="px-6 py-4 text-right flex justify-end gap-2">
                                <button wire:click="sendTestEmail({{ $recipient->id }})" 
                                        title="Envoyer un email de test"
                                        class="p-2 text-slate-300 hover:text-indigo-600 transition-colors rounded-lg hover:bg-indigo-50">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                </button>
                                <button wire:click="deleteRecipient({{ $recipient->id }})" 
                                        wire:confirm="Supprimer ce destinataire ?"
                                        class="p-2 text-slate-300 hover:text-red-500 transition-colors rounded-lg hover:bg-red-50">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 mb-3 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                    <p class="text-sm font-medium">Aucun administrateur configuré pour les alertes.</p>
                                    <p class="text-xs">Ajoutez un email ci-dessus pour activer les notifications.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
