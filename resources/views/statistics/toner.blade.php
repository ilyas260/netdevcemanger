<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Supervision des Consommables') }}
        </h2>
    </x-slot>

    <div class="py-2">
        @livewire('toner-dashboard')
    </div>
</x-app-layout>
