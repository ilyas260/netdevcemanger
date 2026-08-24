<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Parc Informatique & Réseau') }}
            </h2>
            @unlessrole('consultant')
            <a href="{{ route('devices.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                + Ajouter un appareil
            </a>
            @endunlessrole
        </div>
    </x-slot>

    <div class="py-2">
        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 shadow-sm rounded-r-lg">
                {{ session('success') }}
            </div>
        @endif

        @if (session('warning'))
            <div class="mb-4 p-4 bg-orange-100 border-l-4 border-orange-500 text-orange-700 shadow-sm rounded-r-lg">
                {{ session('warning') }}
            </div>
        @endif

        @livewire('device-table')
    </div>
</x-app-layout>
