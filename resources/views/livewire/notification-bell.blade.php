<div class="relative" x-data="{ panelOpen: @entangle('open') }" @click.away="panelOpen = false" wire:poll.30s="loadNotifications">

    {{-- Bouton cloche --}}
    <button
        wire:click="toggle"
        class="relative flex items-center justify-center w-9 h-9 rounded-xl border transition-all duration-200
               {{ $unreadCount > 0 ? 'bg-red-50 border-red-200 hover:bg-red-100' : 'bg-slate-50 border-slate-200 hover:bg-white hover:shadow-sm' }}"
        title="Notifications réseau"
    >
        {{-- Icône cloche --}}
        <svg class="w-4.5 h-4.5 {{ $unreadCount > 0 ? 'text-red-600' : 'text-slate-500' }}" style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>

        {{-- Badge compteur --}}
        @if($unreadCount > 0)
        <span class="absolute -top-1.5 -right-1.5 inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-black text-white bg-red-500 rounded-full shadow ring-2 ring-white animate-pulse">
            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
        </span>
        @endif
    </button>

    {{-- Panneau déroulant --}}
    <div
        x-show="panelOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-1"
        class="absolute right-0 mt-2 w-96 z-50 origin-top-right"
        style="display:none"
    >
        <div class="bg-white rounded-2xl shadow-2xl ring-1 ring-black/5 overflow-hidden">

            {{-- En-tête --}}
            <div class="flex items-center justify-between px-4 py-3 bg-gradient-to-r from-slate-800 to-slate-700 text-white">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span class="text-sm font-bold tracking-wide">Alertes Réseau</span>
                    @if($unreadCount > 0)
                    <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold bg-red-500 text-white rounded-full">
                        {{ $unreadCount }}
                    </span>
                    @endif
                </div>
                @if($unreadCount > 0)
                <button
                    wire:click="dismissAll"
                    class="text-xs text-slate-300 hover:text-white transition-colors font-medium underline underline-offset-2"
                >
                    Tout résoudre
                </button>
                @endif
            </div>

            {{-- Liste des notifications --}}
            <div class="overflow-y-auto max-h-96">
                @forelse ($notifications as $notif)
                <div class="flex items-start gap-3 px-4 py-3 border-b border-slate-100 hover:bg-slate-50 transition-colors group">

                    {{-- Icône de sévérité --}}
                    <div class="shrink-0 mt-0.5">
                        @if(in_array(strtolower($notif['severity'] ?? ''), ['critical', 'haute', 'high']))
                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-red-100">
                                <svg class="w-3.5 h-3.5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                        @else
                            <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-amber-100">
                                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </span>
                        @endif
                    </div>

                    {{-- Contenu --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold text-slate-800 truncate">{{ $notif['device'] }}</span>
                            <span class="text-[10px] text-slate-400 shrink-0">{{ $notif['time'] }}</span>
                        </div>
                        @if($notif['ip'])
                        <span class="text-[10px] text-indigo-500 font-mono">{{ $notif['ip'] }}</span>
                        @endif
                        <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">{{ $notif['message'] }}</p>
                        <span class="inline-block mt-1 px-1.5 py-0.5 text-[10px] font-semibold rounded-md
                            {{ in_array(strtolower($notif['severity'] ?? ''), ['critical','haute','high']) ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $notif['type'] }}
                        </span>
                    </div>

                    {{-- Bouton résoudre --}}
                    <button
                        wire:click="dismiss({{ $notif['id'] }})"
                        class="shrink-0 opacity-0 group-hover:opacity-100 flex items-center justify-center w-6 h-6 rounded-lg bg-slate-100 hover:bg-green-100 hover:text-green-700 text-slate-400 transition-all"
                        title="Marquer comme résolu"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </button>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center py-10 text-slate-400">
                    <svg class="w-10 h-10 mb-3 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm font-medium text-slate-500">Aucune alerte active</span>
                    <span class="text-xs text-slate-400 mt-1">Le réseau fonctionne normalement</span>
                </div>
                @endforelse
            </div>

            {{-- Pied de page --}}
            @if(count($notifications) > 0)
            <div class="px-4 py-2.5 bg-slate-50 border-t border-slate-100 text-center">
                <a href="{{ route('error-logs.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">
                    Voir tous les journaux d'erreurs →
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
