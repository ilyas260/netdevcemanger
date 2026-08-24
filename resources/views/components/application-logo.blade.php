<div {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
    <div class="relative flex items-center justify-center">
        <!-- Main Logo Image -->
        <img src="{{ asset('netdevice_logo.png') }}" class="h-full w-full object-contain drop-shadow-md rounded-xl" alt="NetDevice Manager Logo">
        
        <!-- Subtle Pulse Effect -->
        <span class="absolute -top-1 -right-1 flex h-3 w-3">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
        </span>
    </div>
</div>

