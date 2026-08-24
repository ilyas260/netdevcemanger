<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Ajouter un nouvel équipement') }}
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border">
            <div class="p-6 bg-white border-b border-gray-200">
                <form action="{{ route('devices.store') }}" method="POST">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nom -->
                        <div class="space-y-1">
                            <label for="name" class="block text-sm font-medium text-gray-700">Nom de l'appareil</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Type -->
                        @php
                            $isCustomType = old('type') && !in_array(old('type'), ['imprimante', 'routeur', 'switch', 'serveur', 'pc']);
                        @endphp
                        <div class="space-y-1">
                            <label for="type" class="block text-sm font-medium text-gray-700">Type d'équipement</label>
                            <div class="flex gap-2">
                                <div id="type-select-container" class="flex-1 {{ $isCustomType ? 'hidden' : '' }}">
                                    <select name="{{ $isCustomType ? '' : 'type' }}" id="type" {{ $isCustomType ? 'disabled' : '' }} required
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Choisir un type...</option>
                                        <option value="imprimante" {{ old('type') == 'imprimante' ? 'selected' : '' }}>Imprimante</option>
                                        <option value="routeur" {{ old('type') == 'routeur' ? 'selected' : '' }}>Routeur</option>
                                        <option value="switch" {{ old('type') == 'switch' ? 'selected' : '' }}>Switch</option>
                                        <option value="serveur" {{ old('type') == 'serveur' ? 'selected' : '' }}>Serveur</option>
                                        <option value="pc" {{ old('type') == 'pc' ? 'selected' : '' }}>PC</option>
                                    </select>
                                </div>
                                <div id="type-input-container" class="flex-1 flex gap-2 {{ $isCustomType ? '' : 'hidden' }}">
                                    <input type="text" id="type-new" placeholder="Ex: Firewall, NAS..."
                                           name="{{ $isCustomType ? 'type' : '' }}"
                                           value="{{ $isCustomType ? old('type') : '' }}"
                                           {{ $isCustomType ? '' : 'disabled' }}
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <button type="button" id="btn-cancel-type" class="text-gray-400 hover:text-gray-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                <button type="button" id="btn-add-type" class="px-3 bg-indigo-50 text-indigo-700 rounded-md border border-indigo-200 hover:bg-indigo-100 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </button>
                            </div>
                            @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Marque -->
                        <div class="space-y-1">
                            <label for="brand" class="block text-sm font-medium text-gray-700">Marque (Fabricant)</label>
                            <input type="text" name="brand" id="brand" value="{{ old('brand') }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('brand') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Modèle -->
                        <div class="space-y-1">
                            <label for="model" class="block text-sm font-medium text-gray-700">Modèle exact</label>
                            <input type="text" name="model" id="model" value="{{ old('model') }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('model') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- IP Address -->
                        <div class="space-y-1">
                            <label for="ip_address" class="block text-sm font-medium text-gray-700">Adresse IP</label>
                            <input type="text" name="ip_address" id="ip_address" value="{{ old('ip_address') }}" required placeholder="192.168.1.50"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('ip_address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Emplacement -->
                        <div class="space-y-1">
                            <label for="location" class="block text-sm font-medium text-gray-700">Emplacement (Bureau/Rack)</label>
                            <input type="text" name="location" id="location" value="{{ old('location') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <!-- Agence -->
                        <div class="space-y-1">
                            <label for="agency_id" class="block text-sm font-medium text-gray-700">Agence</label>
                            <div class="flex gap-2">
                                <select name="agency_id" id="agency_id"
                                        class="flex-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Aucune agence</option>
                                    @foreach($agencies as $agency)
                                        <option value="{{ $agency->id }}" {{ old('agency_id', $selectedAgencyId) == $agency->id ? 'selected' : '' }}>{{ $agency->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" onclick="openAgencyModal()" class="px-3 bg-indigo-50 text-indigo-700 rounded-md border border-indigo-200 hover:bg-indigo-100 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </button>
                            </div>
                            @error('agency_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Modal Agence Étendu --}}
                    <div id="modal-agency" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
                        <div class="relative top-10 mx-auto p-6 border w-[500px] shadow-2xl rounded-xl bg-white">
                            <div class="flex justify-between items-center mb-4 pb-2 border-b">
                                <h3 class="text-xl font-bold text-gray-800">Nouvelle Agence</h3>
                                <button type="button" onclick="closeAgencyModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <label class="block text-xs font-bold text-gray-500 uppercase">Nom de l'agence *</label>
                                    <input type="text" id="new-agency-name" class="w-full border rounded-md px-3 py-2 mt-1 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase">IP Routeur *</label>
                                    <input type="text" id="new-agency-ip" placeholder="10.110.X.1" class="w-full border rounded-md px-3 py-2 mt-1 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase">Ville / Localisation</label>
                                    <input type="text" id="new-agency-location" class="w-full border rounded-md px-3 py-2 mt-1 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase">Téléphone</label>
                                    <input type="text" id="new-agency-phone" class="w-full border rounded-md px-3 py-2 mt-1 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase">ND Technique</label>
                                    <input type="text" id="new-agency-nd" class="w-full border rounded-md px-3 py-2 mt-1 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase">Débit Cible</label>
                                    <input type="text" id="new-agency-debit" placeholder="Ex: 20 Mbps" class="w-full border rounded-md px-3 py-2 mt-1 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase">Hostname Routeur</label>
                                    <input type="text" id="new-agency-hostname" class="w-full border rounded-md px-3 py-2 mt-1 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>

                            <div id="agency-modal-error" class="mt-4 text-red-500 text-xs hidden p-2 bg-red-50 rounded"></div>
                            
                            <div class="mt-6 flex gap-3">
                                <button type="button" onclick="closeAgencyModal()" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-bold rounded-md hover:bg-gray-200 transition">
                                    Annuler
                                </button>
                                <button type="button" id="btn-save-agency" class="flex-1 px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-md hover:bg-indigo-700 shadow-md transition">
                                    Créer l'agence
                                </button>
                            </div>
                        </div>
                    </div>



                    <div class="mt-8 flex justify-end gap-3">
                        <a href="{{ route('devices.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition">Annuler</a>
                        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-md font-bold shadow-md hover:bg-indigo-700 transition">
                            Enregistrer l'appareil
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('btn-add-type').addEventListener('click', function() {
            document.getElementById('type-select-container').classList.add('hidden');
            document.getElementById('type-input-container').classList.remove('hidden');
            
            const select = document.getElementById('type');
            const input = document.getElementById('type-new');
            
            // Désactiver le select pour qu'il ne soit pas envoyé
            select.disabled = true;
            select.name = '';
            
            // Activer l'input de type personnalisé
            input.disabled = false;
            input.name = 'type';
            input.focus();
        });

        document.getElementById('btn-cancel-type').addEventListener('click', function() {
            document.getElementById('type-select-container').classList.remove('hidden');
            document.getElementById('type-input-container').classList.add('hidden');
            
            const select = document.getElementById('type');
            const input = document.getElementById('type-new');
            
            // Réactiver le select
            select.disabled = false;
            select.name = 'type';
            
            // Désactiver l'input personnalisé
            input.disabled = true;
            input.name = '';
        });

        // Modal Agence
        function openAgencyModal() {
            document.getElementById('modal-agency').classList.remove('hidden');
        }

        function closeAgencyModal() {
            document.getElementById('modal-agency').classList.add('hidden');
            document.getElementById('agency-modal-error').classList.add('hidden');
        }

        document.getElementById('btn-save-agency').addEventListener('click', function() {
            const name = document.getElementById('new-agency-name').value;
            const ip = document.getElementById('new-agency-ip').value;
            const location = document.getElementById('new-agency-location').value;
            const phone = document.getElementById('new-agency-phone').value;
            const nd = document.getElementById('new-agency-nd').value;
            const debit = document.getElementById('new-agency-debit').value;
            const hostname = document.getElementById('new-agency-hostname').value;
            
            const errorDiv = document.getElementById('agency-modal-error');
            const btn = this;

            if (!name || !ip) {
                errorDiv.innerText = "Nom et IP requis.";
                errorDiv.classList.remove('hidden');
                return;
            }

            btn.disabled = true;
            btn.innerText = "Création...";

            fetch('/api/agencies/quick-create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ 
                    name: name, 
                    router_ip: ip,
                    location: location,
                    phone: phone,
                    nd_technique: nd,
                    debit_cible: debit,
                    hostname: hostname
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Ajouter au select
                    const select = document.getElementById('agency_id');
                    const option = new Option(data.agency.name, data.agency.id);
                    select.add(option);
                    select.value = data.agency.id;
                    closeAgencyModal();
                } else {
                    errorDiv.innerText = data.message || "Erreur lors de la création.";
                    errorDiv.classList.remove('hidden');
                }
            })
            .catch(error => {
                errorDiv.innerText = "Erreur réseau.";
                errorDiv.classList.remove('hidden');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerText = "Créer";
            });
        });
    </script>
</x-app-layout>
