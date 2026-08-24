<div class="min-h-screen bg-gray-50">
    <div class="max-w-3xl mx-auto py-10 px-4">

        {{-- Header --}}
        <div class="mb-8 flex items-center gap-4">
            <div class="p-3 bg-indigo-100 rounded-xl">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gestion des Diagnostics</h1>
                <p class="text-sm text-gray-500 mt-0.5">Gérez les options de la liste déroulante "Problème / Diagnostic"</p>
            </div>
        </div>

        {{-- Flash --}}
        @if (session()->has('success'))
            <div class="mb-4 flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-lg">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Add form --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">Ajouter un nouveau diagnostic</h2>
            <form wire:submit.prevent="addDiagnostic" class="flex gap-3">
                <input type="text"
                       wire:model="newLabel"
                       placeholder="Ex: Panne fibre optique..."
                       class="flex-1 rounded-lg border-gray-300 shadow-sm text-sm focus:border-indigo-400 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                />
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Ajouter
                </button>
            </form>
            @error('newLabel') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- List --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Liste des diagnostics</h2>
                <span class="text-xs bg-indigo-100 text-indigo-700 font-semibold px-2 py-0.5 rounded-full">{{ count($diagnostics) }}</span>
            </div>
            <ul class="divide-y divide-gray-100">
                @forelse($diagnostics as $key => $label)
                    <li class="flex items-center gap-3 px-6 py-3 hover:bg-gray-50 transition-colors">
                        @if($editingKey === $key)
                            <input type="text"
                                   wire:model="editingLabel"
                                   wire:keydown.enter="saveEdit"
                                   wire:keydown.escape="cancelEdit"
                                   class="flex-1 rounded-lg border-indigo-400 shadow-sm text-sm focus:border-indigo-400 focus:ring focus:ring-indigo-200"
                                   autofocus
                            />
                            <button wire:click="saveEdit" class="text-xs px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors">
                                Enregistrer
                            </button>
                            <button wire:click="cancelEdit" class="text-xs px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors">
                                Annuler
                            </button>
                        @else
                            <div class="flex-1 min-w-0">
                                <span class="text-sm text-gray-800">{{ $label }}</span>
                                <span class="ml-2 text-xs text-gray-400 font-mono">{{ $key }}</span>
                            </div>
                            <button wire:click="startEdit('{{ $key }}')" class="text-indigo-500 hover:text-indigo-700 transition-colors" title="Modifier">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button wire:click="deleteDiagnostic('{{ $key }}')"
                                    wire:confirm="Voulez-vous vraiment supprimer ce diagnostic ?"
                                    class="text-red-400 hover:text-red-600 transition-colors" title="Supprimer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        @endif
                    </li>
                @empty
                    <li class="px-6 py-10 text-center text-sm text-gray-400">
                        Aucun diagnostic configuré. Ajoutez-en un ci-dessus.
                    </li>
                @endforelse
            </ul>
        </div>

    </div>
</div>
