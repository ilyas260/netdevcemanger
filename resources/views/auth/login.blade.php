<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-800">Espace Sécurisé</h2>
        <p class="text-sm text-slate-500">Veuillez vous authentifier pour accéder à la supervision.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="text-slate-700 font-bold" />
            <x-text-input id="email" class="block mt-1 w-full bg-slate-50 border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 rounded-xl" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Mot de passe')" class="text-slate-700 font-bold" />
            <x-text-input id="password" class="block mt-1 w-full bg-slate-50 border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 rounded-xl"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-slate-600">{{ __('Se souvenir de moi') }}</span>
            </label>
        </div>

        <div class="flex flex-col gap-4">
            <x-primary-button class="w-full justify-center py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-200 transition-all uppercase tracking-widest text-xs">
                {{ __('Connexion au Réseau') }}
            </x-primary-button>

            @if (Route::has('password.request'))
                <a class="text-center text-sm text-slate-500 hover:text-indigo-600 transition" href="{{ route('password.request') }}">
                    {{ __('Mot de passe oublié ?') }}
                </a>
            @endif
        </div>
    </form>
</x-guest-layout>
