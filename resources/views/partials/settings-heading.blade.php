<div class="min-h-screen" style="background: #1e1e1e;">

    {{-- Top bar --}}
    <div class="border-b border-[#333] bg-[#252526] px-6 py-3 flex items-center justify-between sticky top-0 z-30 shadow-lg">
        <div class="flex items-center gap-3">
            {{-- Logo --}}
            <div class="bg-white/10 backdrop-blur-md rounded-lg border border-white/15 p-1 shadow">
                <img src="{{ asset('images/flexiweek-Iso.png') }}" class="h-7 w-7 object-contain" alt="FlexiWeek Logo">
            </div>
            <div>
                <span class="text-[15px] font-bold tracking-tight text-[#d4d4d4]">Flexi<span class="text-[#007fd4]">Week</span></span>
                <span class="text-[#555] mx-2 text-xs font-mono">/</span>
                <span class="text-xs text-[#8b949e] font-medium">Configuración</span>
            </div>
        </div>

        <a href="{{ route('planner') }}" wire:navigate
           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-[#007fd4] rounded-md hover:bg-[#006bb3] transition-colors shadow shadow-[#007fd4]/20 active:scale-95">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver al Panel
        </a>
    </div>

    {{-- Body: Sidebar + Content --}}
    <div class="flex gap-0 max-w-5xl mx-auto px-4 py-8 max-md:flex-col max-md:gap-6">

        {{-- Sidebar Navigation --}}
        <aside class="w-full md:w-52 shrink-0 md:mr-8">
            <div class="bg-[#252526] border border-[#333] rounded-lg overflow-hidden">
                <div class="px-3 py-2 border-b border-[#333]">
                    <span class="text-[10px] font-semibold uppercase tracking-widest text-[#555]">Menú</span>
                </div>
                <nav class="p-2 space-y-0.5">
                    @php
                        $links = [
                            ['route' => 'profile.edit',      'icon' => '👤', 'label' => 'Perfil'],
                            ['route' => 'user-password.edit','icon' => '🔒', 'label' => 'Contraseña'],
                        ];
                        if (Laravel\Fortify\Features::canManageTwoFactorAuthentication()) {
                            $links[] = ['route' => 'two-factor.show', 'icon' => '🛡️', 'label' => 'Seguridad'];
                        }
                        $links[] = ['route' => 'appearance.edit', 'icon' => '🎨', 'label' => 'Apariencia'];
                    @endphp
                    @foreach($links as $link)
                        <a href="{{ route($link['route']) }}" wire:navigate
                           class="flex items-center gap-2.5 px-3 py-2 text-sm font-medium rounded-md transition-all duration-150
                                  {{ request()->routeIs($link['route'])
                                        ? 'bg-[#007fd4] text-white shadow shadow-[#007fd4]/20'
                                        : 'text-[#8b949e] hover:text-[#d4d4d4] hover:bg-white/5' }}">
                            <span class="text-base leading-none">{{ $link['icon'] }}</span>
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </nav>

                {{-- User info at bottom --}}
                <div class="border-t border-[#333] px-3 py-3 mt-1">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-[#007fd4]/20 border border-[#007fd4]/40 flex items-center justify-center text-[10px] font-bold text-[#007fd4] uppercase">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-semibold text-[#d4d4d4] truncate">{{ auth()->user()->name }}</div>
                            <div class="text-[10px] text-[#555] truncate">{{ auth()->user()->email }}</div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="w-full text-left text-[11px] text-[#555] hover:text-[#f85149] transition-colors px-1 py-0.5 flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Content --}}
        <div class="flex-1 min-w-0">
