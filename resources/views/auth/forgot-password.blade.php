<x-guest-layout>
    <div class="mb-8 text-center">
        <div class="flex justify-center mb-4">
            <div class="p-3 bg-indigo-600 rounded-2xl shadow-lg shadow-indigo-200">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
        </div>
        <h2 class="text-2xl font-bold text-slate-900">Mot de passe oublié ?</h2>
        <p class="text-slate-500 mt-2 text-sm">Entrez votre email et nous vous enverrons un lien de réinitialisation.</p>
    </div>

    {{-- ✅ Email sent successfully --}}
    @if (session('status'))
        <div class="mb-6 flex items-center gap-3 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 px-4 py-3 rounded-xl shadow-sm">
            <svg class="w-5 h-5 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="font-medium text-sm">{{ session('status') }}</span>
        </div>
    @endif

    {{-- 🔗 SMTP failed — show link directly --}}
    @if (session('reset_link'))
        <div class="mb-6 bg-amber-50 border border-amber-300 rounded-xl p-4 shadow-sm">
            <div class="flex items-start gap-3 mb-3">
                <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <p class="font-bold text-amber-800 text-sm">L'email n'a pas pu être envoyé</p>
                    <p class="text-amber-700 text-xs mt-1">
                        Le serveur de messagerie est temporairement indisponible.<br>
                        Copiez ce lien et ouvrez-le dans votre navigateur pour réinitialiser votre mot de passe&nbsp;:
                    </p>
                </div>
            </div>

            <div class="bg-white border border-amber-200 rounded-lg p-3 mt-2">
                <p class="text-xs text-slate-500 font-semibold uppercase tracking-wide mb-1.5">Lien de réinitialisation pour {{ session('reset_email') }}</p>
                <a href="{{ session('reset_link') }}"
                   class="text-indigo-600 hover:text-indigo-800 text-xs font-medium break-all underline underline-offset-2"
                >
                    {{ session('reset_link') }}
                </a>
            </div>

            <a href="{{ session('reset_link') }}"
               class="mt-3 w-full flex justify-center items-center gap-2 py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition-all shadow-sm"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
                Cliquez ici pour réinitialiser votre mot de passe
            </a>

            <p class="text-[10px] text-amber-600 text-center mt-2 font-medium">⚠️ Ce lien expire dans 60 minutes.</p>
        </div>
    @endif

    @if (!session('reset_link') && !session('status'))
    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Adresse Email')" class="text-slate-700 font-semibold" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                    </svg>
                </div>
                <x-text-input
                    id="email"
                    class="block w-full pl-10 border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 rounded-xl"
                    type="email"
                    name="email"
                    :value="old('email')"
                    required
                    autofocus
                    placeholder="votre@email.com"
                />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:scale-[1.02]">
                Envoyer le lien de réinitialisation
            </button>
        </div>
    </form>
    @else
        @if(session('status'))
        <div class="mt-4">
            <a href="{{ route('password.request') }}" class="w-full flex justify-center py-2.5 px-4 border border-slate-300 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                Essayer avec un autre email
            </a>
        </div>
        @endif
    @endif

    <div class="text-center mt-6">
        <a href="{{ route('login') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500 transition">
            &larr; Retour à la connexion
        </a>
    </div>
</x-guest-layout>
