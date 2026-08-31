<tr class="{{ $log->is_resolved ? 'bg-gray-50' : '' }}" wire:key="error-log-{{ $log->id }}">
    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
        {{ $log->logged_at->format('d/m/Y H:i') }}
    </td>
    <td class="px-6 py-4 whitespace-nowrap">
        <span class="px-2 py-1 text-[10px] font-bold rounded {{ $log->severity === 'CRITICAL' ? 'bg-red-600 text-white' : ($log->severity === 'ERROR' ? 'bg-red-100 text-red-800' : ($log->severity === 'WARNING' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800')) }}">
            {{ $log->severity }}
        </span>
    </td>
    <td class="px-6 py-4 text-sm text-gray-600">
        {{ Str::limit($log->message, 80) }}
    </td>
    <td class="px-6 py-4 text-sm text-gray-600 min-w-[220px]">
        @unlessrole('consultant')
        @if($editingDiagnostic || !$log->solution_type)
            {{-- Mode saisie --}}
            <div class="flex flex-col gap-2">
                <select
                    wire:model.live="solution_type"
                    class="text-xs rounded-lg border-slate-300 shadow-sm focus:border-indigo-400 focus:ring focus:ring-indigo-100 w-full"
                >
                    <option value="">— Sélectionner un diagnostic —</option>
                    @foreach(\App\Models\ErrorLog::getSolutionTypes() as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>

                @if($solution_type === 'autre')
                    <input
                        type="text"
                        wire:model="custom_solution"
                        placeholder="Décrire le problème..."
                        class="text-xs rounded-lg border-indigo-400 shadow-sm focus:ring focus:ring-indigo-100 w-full"
                    >
                @endif

                <div class="flex items-center gap-2 mt-1">
                    <button
                        wire:click="saveDiagnosticConfirmed"
                        class="inline-flex items-center gap-1 px-3 py-1.5 text-[11px] font-bold rounded-lg shadow-sm transition-all {{ $solution_type ? 'bg-indigo-600 text-white hover:bg-indigo-700 cursor-pointer' : 'bg-slate-200 text-slate-400 cursor-not-allowed' }}"
                        {{ !$solution_type ? 'disabled' : '' }}
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Valider
                    </button>
                    @if($log->solution_type && $editingDiagnostic)
                        <button
                            wire:click="cancelEditDiagnostic"
                            class="text-[11px] text-slate-500 hover:text-slate-700 underline"
                        >Annuler</button>
                    @endif
                </div>

                @if($diagnosticSaved)
                    <div class="flex items-center gap-1 text-[11px] text-green-600 font-semibold animate-pulse">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Enregistré avec succès
                    </div>
                @endif
            </div>
        @else
            {{-- Mode affichage (diagnostic déjà enregistré) --}}
            <div class="flex flex-col gap-1.5">
                <div class="flex items-start gap-2">
                    <span class="shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <span class="text-xs font-semibold text-slate-700 leading-snug">
                        {{ \App\Models\ErrorLog::getSolutionTypes()[$log->solution_type] ?? $log->solution_type }}
                    </span>
                </div>
                @unlessrole('consultant')
                <button
                    wire:click="startEditDiagnostic"
                    class="inline-flex items-center gap-1 text-[10px] text-indigo-500 hover:text-indigo-700 font-semibold underline underline-offset-2 self-start transition"
                >
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Modifier
                </button>
                @endunlessrole
            </div>
        @endif
        @else
            {{-- Consultant : lecture seule --}}
            @if($log->solution_type)
                <span class="text-xs text-slate-600 font-medium">{{ \App\Models\ErrorLog::getSolutionTypes()[$log->solution_type] ?? $log->solution_type }}</span>
            @else
                <span class="text-xs italic text-slate-400">Non défini</span>
            @endif
        @endunlessrole
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-center">
        <span class="px-2 py-1 text-[10px] font-bold rounded {{ $log->mail_sent ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
            {{ $emailDisplay }}
        </span>
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-center">
        @if(!$log->is_resolved)
            @unlessrole('consultant')
            <button wire:click="openResolveModal" class="inline-flex items-center gap-1 px-2 py-1 text-[10px] font-bold rounded bg-red-100 text-red-800 hover:bg-red-200 transition-colors" title="Cliquer pour résoudre">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Non résolu
            </button>
            @else
            <span class="px-2 py-1 text-[10px] font-bold rounded bg-red-100 text-red-800">
                Non résolu
            </span>
            @endunlessrole
        @else
        <div class="flex flex-col items-center gap-1">
            <span class="inline-flex items-center gap-1 px-2 py-1 text-[10px] font-bold rounded bg-green-100 text-green-800">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ $statusDisplay }}
            </span>
            @if($log->resolved_at)
            <span class="text-[9px] text-gray-500 font-medium">
                {{ \Carbon\Carbon::parse($log->resolved_at)->format('d/m/Y H:i') }}
            </span>
            @endif
        </div>
        @endif
    </td>

</tr>

@if($showResolveModal)
<tr class="bg-black bg-opacity-50 fixed inset-0 z-50">
    <td colspan="6" class="p-0">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6">
                <div class="flex items-center gap-3 mb-5">
                    <div class="p-2 bg-indigo-100 rounded-xl">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Résoudre l'erreur #{{ $log->id }}</h3>
                        <p class="text-xs text-slate-500">{{ $log->device->name ?? '' }} — {{ $log->error_type }}</p>
                    </div>
                </div>

                {{-- Sélection du type de solution --}}
                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1.5">
                        Type de solution <span class="text-red-500">*</span>
                    </label>
                    <select wire:model="solution_type"
                        class="w-full rounded-xl border-slate-200 text-sm text-slate-800 bg-slate-50 focus:border-indigo-400 focus:ring focus:ring-indigo-100 transition">
                        <option value="">-- Sélectionner la solution --</option>
                        @foreach(\App\Models\ErrorLog::getSolutionTypes() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('solution_type')
                        <span class="text-red-500 text-xs mt-1 block">Veuillez sélectionner un type de solution.</span>
                    @enderror
                </div>

                {{-- Note complémentaire (optionnelle) --}}
                <div class="mb-5">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1.5">
                        Note complémentaire <span class="text-slate-400 font-normal">(optionnel)</span>
                    </label>
                    <textarea wire:model="resolution_note"
                        class="w-full rounded-xl border-slate-200 text-sm bg-slate-50 focus:border-indigo-400 focus:ring focus:ring-indigo-100 transition"
                        placeholder="Détails supplémentaires sur la résolution..."
                        rows="3"></textarea>
                    @error('resolution_note')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex justify-end gap-3">
                    <button wire:click="closeResolveModal"
                        class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                        Annuler
                    </button>
                    <button wire:click="resolveError"
                        class="px-5 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow transition">
                        ✓ Confirmer la résolution
                    </button>
                </div>
            </div>
        </div>
    </td>
</tr>
@endif


@if($showEmailModal)
<tr class="bg-black bg-opacity-50 fixed inset-0 z-50">
    <td colspan="6" class="p-0">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-bold mb-4">Envoyer un email pour l'erreur #{{ $log->id }}</h3>
                <p class="text-gray-600 mb-4">Êtes-vous sûr de vouloir envoyer un email d'alerte à tous les destinataires configurés pour cette erreur ?</p>
                <div class="flex justify-end gap-2">
                    <button wire:click="closeEmailModal" class="px-4 py-2 text-gray-600 hover:text-gray-800">Annuler</button>
                    <button wire:click="sendEmail" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Envoyer</button>
                </div>
            </div>
        </div>
    </td>
</tr>
@endif
