<div class="p-6 bg-gray-50 min-h-screen transition-colors duration-500" wire:init="pingAllAgencies" wire:poll.15s>
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6 group/header">
            <h1 class="text-3xl font-bold text-gray-900 transform transition-transform duration-300 group-hover/header:translate-x-1">Gestion des Agences</h1>
            <div class="flex items-center space-x-3">
                <div wire:loading wire:target="pingAllAgencies" class="flex items-center text-sm text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full animate-pulse border border-indigo-100">
                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Actualisation...
                </div>
                @unlessrole('consultant')
                <button wire:click="pingAllAgencies" wire:loading.attr="disabled" class="p-2 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Rafraîchir tous les statuts">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </button>
                <button wire:click="openModal" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 hover:shadow-lg hover:-translate-y-0.5 active:scale-95 transition-all duration-300 ease-in-out shadow-sm flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="12 4v16m8-8H4"></path></svg>
                    Nouvelle Agence
                </button>
                @endunlessrole
            </div>
        </div>

        @if (session()->has('message'))
            <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded shadow-sm">
                {{ session('message') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm hover:shadow-md border border-gray-100 overflow-hidden transition-all duration-300">
            <div class="p-4 border-b border-gray-100 flex items-center bg-gray-50/50">
                <div class="relative flex-1 group">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 group-focus-within:text-indigo-500 transition-colors duration-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>
                    <input wire:model.live="search" type="text" placeholder="Rechercher par nom, IP, nom d'hôte, téléphone, ND, débit ou état..." class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-all duration-300 focus:shadow-md">
                </div>
            </div>

            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="px-6 py-4">Agence & Nom d'hôte</th>
                        <th class="px-6 py-4">Détails Techniques (ND / Débit)</th>
                        <th class="px-6 py-4">IP Routeur / Réseau</th>
                        <th class="px-6 py-4">Statut</th>
                        <th class="px-6 py-4">Dernier Ping</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    @foreach($agencies as $agency)
                        <tr class="hover:bg-gray-50 hover:shadow-[inset_4px_0_0_0_#4f46e5] transition-all duration-200 group">
                            <td class="px-6 py-4">
                                <a href="{{ route('agencies.show', $agency->id) }}" class="block hover:bg-gray-50 rounded-lg p-1 -m-1 transition-colors">
                                    <div class="font-medium text-gray-900 group-hover:text-indigo-600 transition-colors">{{ $agency->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $agency->hostname ?: 'Sans nom d\'hôte' }}</div>
                                    <div class="text-xs text-gray-400 mt-1">{{ $agency->location }}</div>
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 font-mono group-hover:font-bold transition-all">{{ $agency->nd_technique ?: '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $agency->debit_cible ?: '-' }}</div>
                            </td>
                            <td class="px-6 py-4 font-mono text-sm relative overflow-hidden group/ip">
                                <div class="text-indigo-600 font-bold">{{ $agency->router_ip }}</div>
                                <div class="text-gray-400 text-xs">{{ $agency->network_address ?: 'Non défini' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $agency->status_color }}-100 text-{{ $agency->status_color }}-800 transition-colors duration-300 hover:bg-{{ $agency->status_color }}-200">
                                    <span class="w-2 h-2 mr-1.5 rounded-full bg-{{ $agency->status_color }}-500 animate-pulse"></span>
                                    {{ __($agency->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $agency->last_ping_at ? $agency->last_ping_at->diffForHumans() : 'Jamais' }}
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                @unlessrole('consultant')
                                <button wire:click="pingAgency({{ $agency->id }})" class="text-indigo-600 hover:text-indigo-900 hover:scale-110 active:scale-95 transition-transform duration-200" title="Tester la connexion">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                </button>
                                @endunlessrole
                                <a href="{{ route('agencies.show', $agency->id) }}" class="text-green-600 hover:text-green-900 hover:scale-110 active:scale-95 transition-transform duration-200 inline-block" title="Voir le tableau de bord">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2"></path></svg>
                                </a>
                                @unlessrole('consultant')
                                <button wire:click="editAgency({{ $agency->id }})" class="text-blue-600 hover:text-blue-900 hover:scale-110 active:scale-95 transition-transform duration-200">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                <button onclick="confirm('Supprimer cette agence ?') || event.stopImmediatePropagation()" wire:click="deleteAgency({{ $agency->id }})" class="text-red-600 hover:text-red-900 hover:scale-110 active:scale-95 transition-transform duration-200">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                                @endunlessrole
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="p-4 bg-gray-50 border-t border-gray-100">
                {{ $agencies->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity duration-300" aria-hidden="true" wire:click="$set('showModal', false)"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4" id="modal-title">
                            {{ $editingAgencyId ? 'Modifier l\'Agence' : 'Nouvelle Agence' }}
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nom de l'agence</label>
                                <input type="text" wire:model="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-shadow duration-200">
                                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-gray-700">IP du Routeur</label>
                                <input type="text" wire:model.live.debounce.500ms="router_ip" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-shadow duration-200">
                                @error('router_ip') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Adresse Réseau (Nmap)</label>
                                <input type="text" wire:model="network_address" placeholder="ex: 10.110.13.0/24" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-shadow duration-200">
                                @error('network_address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nom d'hôte</label>
                                <input type="text" wire:model="hostname" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-shadow duration-200">
                                @error('hostname') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">ND Technique</label>
                                    <input type="text" wire:model="nd_technique" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-shadow duration-200">
                                    @error('nd_technique') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Débit Cible</label>
                                    <input type="text" wire:model="debit_cible" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-shadow duration-200">
                                    @error('debit_cible') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Téléphone</label>
                                <input type="text" wire:model="phone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-shadow duration-200">
                                @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Localisation</label>
                                <input type="text" wire:model="location" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-shadow duration-200">
                                @error('location') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="saveAgency" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 hover:shadow-md active:scale-95 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Sauvegarder
                        </button>
                        <button wire:click="$set('showModal', false)" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 hover:shadow-sm active:scale-95 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Annuler
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
