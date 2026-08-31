<div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="mb-8 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-indigo-100 rounded-xl shadow-inner">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Gestion des Utilisateurs</h1>
                <p class="text-sm text-slate-500 mt-1">Créez des comptes, gérez les rôles et les droits d'accès.</p>
            </div>
        </div>
        <button
            wire:click="openCreateModal"
            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-xl shadow-sm hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Créer un utilisateur
        </button>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="mb-6 flex items-center gap-3 bg-green-50/80 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl shadow-sm">
            <svg class="w-5 h-5 shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 flex items-center gap-3 bg-red-50/80 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl shadow-sm">
            <svg class="w-5 h-5 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Role Permissions Matrix --}}
    <div class="mb-8 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/80 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Matrice des droits d'accès</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-center">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Fonctionnalité</th>
                        <th class="px-6 py-3 text-xs font-bold text-purple-600 uppercase tracking-wider">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-purple-100 border border-purple-200">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                Admin
                            </span>
                        </th>
                        <th class="px-6 py-3 text-xs font-bold text-blue-600 uppercase tracking-wider">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-blue-100 border border-blue-200">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v1h8v-1z"/></svg>
                                Technicien
                            </span>
                        </th>
                        <th class="px-6 py-3 text-xs font-bold text-teal-600 uppercase tracking-wider">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-teal-100 border border-teal-200">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/></svg>
                                Consultant
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @php
                        $ok = '<svg class="w-5 h-5 text-green-500 mx-auto" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>';
                        $no = '<svg class="w-5 h-5 text-slate-300 mx-auto" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>';
                        $matrix = [
                            ['Voir le tableau de bord',       true,  true,  true],
                            ['Voir les agences',              true,  true,  true],
                            ['Voir les équipements',          true,  true,  true],
                            ['Voir les statistiques',         true,  true,  true],
                            ['Exporter des rapports',         true,  true,  true],
                            ['Effectuer un ping manuel',      true,  true,  false],
                            ['Ajouter / modifier une agence', true,  true,  false],
                            ['Ajouter / modifier un appareil',true,  true,  false],
                            ['Résoudre des incidents',        true,  true,  false],
                            ['Scanner le réseau (Nmap)',      true,  true,  false],
                            ['Créer des utilisateurs',        true,  false, false],
                            ['Gérer les rôles d\'accès',     true,  false, false],
                            ['Configurer les alertes email',  true,  false, false],
                            ['Accéder aux diagnostics',       true,  false, false],
                        ];
                    @endphp
                    @foreach($matrix as [$feature, $admin, $tech, $consultant])
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-3 text-left text-slate-700 font-medium">{{ $feature }}</td>
                            <td class="px-6 py-3">{!! $admin ? $ok : $no !!}</td>
                            <td class="px-6 py-3">{!! $tech ? $ok : $no !!}</td>
                            <td class="px-6 py-3">{!! $consultant ? $ok : $no !!}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/80 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Liste des utilisateurs ({{ $users->total() }})
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Utilisateur</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Rôle / Droits</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($users as $user)
                        @php
                            $currentRole = $user->roles->first()?->name ?? null;
                            $roleColors = [
                                'admin'      => 'bg-purple-100 text-purple-700 border-purple-200',
                                'technicien' => 'bg-blue-100 text-blue-700 border-blue-200',
                                'consultant' => 'bg-teal-100 text-teal-700 border-teal-200',
                            ];
                            $badgeClass = $roleColors[$currentRole] ?? 'bg-slate-100 text-slate-600 border-slate-200';
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold shadow-sm text-sm shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                            {{ $user->name }}
                                            @if($user->id === auth()->id())
                                                <span class="text-[10px] font-semibold text-indigo-500 bg-indigo-50 border border-indigo-200 px-1.5 py-0.5 rounded-full">Vous</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    @if($currentRole)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $badgeClass }}">
                                            {{ ucfirst($currentRole) }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Aucun rôle</span>
                                    @endif

                                    @if($user->id !== auth()->id() || $currentRole !== 'admin')
                                        <select
                                            wire:change="updateRole({{ $user->id }}, $event.target.value)"
                                            class="text-xs border-slate-200 rounded-lg text-slate-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-1 pl-2 pr-6"
                                        >
                                            <option value="">-- Changer le rôle --</option>
                                            @foreach($roles as $roleName)
                                                <option value="{{ $roleName }}" @if($roleName === $currentRole) selected @endif>
                                                    {{ ucfirst($roleName) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <span class="text-[10px] text-slate-400 italic">(Votre compte admin)</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        wire:click="openPasswordModal({{ $user->id }})"
                                        title="Changer le mot de passe"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-indigo-600 hover:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all shadow-sm"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                        </svg>
                                        Mot de passe
                                    </button>
                                    @if($user->id !== auth()->id())
                                        <button
                                            wire:click="openDeleteModal({{ $user->id }})"
                                            title="Supprimer l'utilisateur"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-red-200 rounded-lg text-xs font-semibold text-red-600 hover:bg-red-50 hover:border-red-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all shadow-sm"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Supprimer
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    {{-- ========== CREATE USER MODAL ========== --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-indigo-600 to-purple-600">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        Créer un utilisateur
                    </h3>
                    <button wire:click="closeCreateModal" class="text-white/70 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6">
                    <form wire:submit.prevent="createUser" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nom complet <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="create_name" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-sm" placeholder="Ex: Jean Dupont">
                            @error('create_name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Adresse email <span class="text-red-500">*</span></label>
                            <input type="email" wire:model="create_email" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-sm" placeholder="jean.dupont@domaine.com">
                            @error('create_email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-2">Rôle <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach(['admin' => ['purple', 'Administrateur', 'Accès total'], 'technicien' => ['blue', 'Technicien', 'Gestion réseau'], 'consultant' => ['teal', 'Consultant', 'Lecture seule']] as $roleName => $roleInfo)
                                    <label class="relative cursor-pointer">
                                        <input type="radio" wire:model="create_role" value="{{ $roleName }}" class="peer sr-only">
                                        <div class="border-2 rounded-xl p-3 text-center transition-all peer-checked:border-{{ $roleInfo[0] }}-500 peer-checked:bg-{{ $roleInfo[0] }}-50 border-slate-200 hover:border-{{ $roleInfo[0] }}-300">
                                            <div class="text-xs font-bold text-slate-700">{{ $roleInfo[1] }}</div>
                                            <div class="text-[10px] text-slate-400 mt-0.5">{{ $roleInfo[2] }}</div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @error('create_role') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Mot de passe <span class="text-red-500">*</span></label>
                            <input type="password" wire:model="create_password" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-sm" placeholder="8 caractères minimum">
                            @error('create_password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Confirmer le mot de passe <span class="text-red-500">*</span></label>
                            <input type="password" wire:model="create_password_confirmation" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-sm">
                        </div>
                        <div class="pt-4 flex justify-end gap-3 border-t border-slate-100 mt-6">
                            <button type="button" wire:click="closeCreateModal" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors">
                                Annuler
                            </button>
                            <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-indigo-600 border border-transparent rounded-xl hover:bg-indigo-700 shadow-sm transition-all">
                                <span wire:loading.remove wire:target="createUser">Créer l'utilisateur</span>
                                <span wire:loading wire:target="createUser">Création...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- ========== PASSWORD MODAL ========== --}}
    @if($showPasswordModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Modifier le mot de passe
                    </h3>
                    <button wire:click="closePasswordModal" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6">
                    <p class="text-sm text-slate-600 mb-5">Définir un nouveau mot de passe pour <span class="font-bold text-indigo-700">{{ $editingUserName }}</span>.</p>
                    <form wire:submit.prevent="updatePassword" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nouveau mot de passe</label>
                            <input type="password" wire:model="new_password" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-sm" placeholder="8 caractères minimum">
                            @error('new_password') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Confirmer le mot de passe</label>
                            <input type="password" wire:model="new_password_confirmation" class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 text-sm">
                        </div>
                        <div class="pt-4 flex justify-end gap-3 border-t border-slate-100 mt-6">
                            <button type="button" wire:click="closePasswordModal" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors">
                                Annuler
                            </button>
                            <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-indigo-600 border border-transparent rounded-xl hover:bg-indigo-700 shadow-sm transition-all">
                                Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- ========== DELETE MODAL ========== --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden">
                <div class="p-6 text-center">
                    <div class="mx-auto w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mb-4">
                        <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 mb-2">Supprimer l'utilisateur ?</h3>
                    <p class="text-sm text-slate-500 mb-6">
                        Vous êtes sur le point de supprimer définitivement le compte de <span class="font-bold text-slate-700">{{ $deletingUserName }}</span>. Cette action est irréversible.
                    </p>
                    <div class="flex gap-3">
                        <button wire:click="closeDeleteModal" class="flex-1 px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors">
                            Annuler
                        </button>
                        <button wire:click="deleteUser" class="flex-1 px-4 py-2 text-sm font-bold text-white bg-red-600 border border-transparent rounded-xl hover:bg-red-700 shadow-sm transition-all">
                            Oui, supprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
