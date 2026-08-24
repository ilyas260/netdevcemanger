<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Journaux d\'incidents & Erreurs') }}
        </h2>
    </x-slot>

    <div class="py-2">
        @if (session('success'))
            <div class="mb-4 p-4 bg-indigo-100 border-l-4 border-indigo-500 text-indigo-700 shadow-sm rounded-r-lg">
                {{ session('success') }}
            </div>
        @endif

        @livewire('error-log-table')
    </div>
</x-app-layout>
