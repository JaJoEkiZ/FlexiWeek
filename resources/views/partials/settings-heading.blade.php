<div class="p-6 lg:p-8">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8 pb-6 border-b" style="border-color: var(--settings-card-border)">
        <div class="flex items-center gap-4">
            <div class="p-1.5 bg-white rounded-xl shadow-lg shadow-black/5">
                <img src="{{ asset('images/flexiweek-Iso.png') }}" class="size-10 object-contain" alt="FlexiWeek Logo">
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight" style="color: var(--settings-heading-text)">Flexi<span class="text-[#007fd4]">Week</span></h1>
                <p class="text-sm font-medium" style="color: var(--settings-subheading-text)">{{ __('Configuración de cuenta') }}</p>
            </div>
        </div>

        <a href="{{ route('planner') }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white transition-all bg-[#007fd4] rounded-lg hover:bg-[#006bb3] shadow-md shadow-[#007fd4]/20 active:scale-95">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            {{ __('Volver al Panel') }}
        </a>
    </div>

    {{-- Layout: Sidebar + Content --}}
    <div class="flex gap-10 max-md:flex-col">
        {{-- Sidebar Navigation --}}
        <nav class="w-full md:w-[220px] shrink-0 space-y-1">
            @php
                $links = [
                    ['route' => 'profile.edit', 'label' => '👤 Perfil'],
                    ['route' => 'user-password.edit', 'label' => '🔒 Contraseña'],
                ];
                if (Laravel\Fortify\Features::canManageTwoFactorAuthentication()) {
                    $links[] = ['route' => 'two-factor.show', 'label' => '🛡️ Seguridad'];
                }
                $links[] = ['route' => 'appearance.edit', 'label' => '🎨 Apariencia'];
            @endphp
            @foreach($links as $link)
                <a href="{{ route($link['route']) }}" wire:navigate
                   class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 group
                          {{ request()->routeIs($link['route'])
                              ? 'bg-[#007fd4] text-white shadow-lg shadow-[#007fd4]/20'
                              : 'hover:bg-[var(--settings-sidebar-item-hover)] group-hover:translate-x-1' }}"
                   style="{{ !request()->routeIs($link['route']) ? 'color: var(--settings-sidebar-item-text)' : '' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        {{-- Content --}}
        <div class="flex-1 min-w-0">
