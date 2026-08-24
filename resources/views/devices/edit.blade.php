<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Modifier : ') }} {{ $device->name }}
            </h2>
            <form action="{{ route('devices.destroy', $device) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir archiver cet appareil ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:text-red-900 font-bold text-sm uppercase tracking-widest">
                    Archiver l'appareil
                </button>
            </form>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border">
            <div class="p-6 bg-white">
                <form action="{{ route('devices.update', $device) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label for="name" class="block text-sm font-medium text-gray-700">Nom de l'appareil</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $device->name) }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="space-y-1">
                            <label for="type" class="block text-sm font-medium text-gray-700">Type d'équipement</label>
                            <select name="type" id="type" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach(['imprimante', 'routeur', 'switch', 'serveur', 'pc'] as $type)
                                    <option value="{{ $type }}" {{ old('type', $device->type) == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label for="brand" class="block text-sm font-medium text-gray-700">Marque</label>
                            <input type="text" name="brand" id="brand" value="{{ old('brand', $device->brand) }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="space-y-1">
                            <label for="model" class="block text-sm font-medium text-gray-700">Modèle</label>
                            <input type="text" name="model" id="model" value="{{ old('model', $device->model) }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="space-y-1">
                            <label for="ip_address" class="block text-sm font-medium text-gray-700">Adresse IP</label>
                            <input type="text" name="ip_address" id="ip_address" value="{{ old('ip_address', $device->ip_address) }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-700">État</label>
                            <div class="mt-3 flex items-center">
                                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $device->is_active) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <label for="is_active" class="ml-2 text-sm text-gray-600">Actif et supervisé</label>
                            </div>
                        </div>

                        <!-- Agence -->
                        <div class="space-y-1">
                            <label for="agency_id" class="block text-sm font-medium text-gray-700">Agence</label>
                            <select name="agency_id" id="agency_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Aucune agence</option>
                                @foreach($agencies as $agency)
                                    <option value="{{ $agency->id }}" {{ old('agency_id', $device->agency_id) == $agency->id ? 'selected' : '' }}>{{ $agency->name }}</option>
                                @endforeach
                            </select>
                            @error('agency_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100">
                        <label for="notes" class="block text-sm font-medium text-gray-700">Notes & Information complémentaires</label>
                        <textarea name="notes" id="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $device->notes) }}</textarea>
                    </div>

                    <div class="mt-8 flex justify-end gap-3">
                        <a href="{{ route('devices.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition">Annuler</a>
                        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-md font-bold shadow-md hover:bg-indigo-700 transition">
                            Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
