<div class="p-6 lg:p-8">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-2">
        <h1 class="text-2xl font-bold dark:text-white text-gray-900">⚙️ Configuración</h1>
        <a href="{{ route('planner') }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-xs font-medium text-white transition-all bg-[#007fd4] rounded-lg hover:bg-[#006bb3] focus:outline-none focus:ring-2 focus:ring-[#007fd4]/50">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver al Panel
        </a>
    </div>
    <p class="text-sm dark:text-[#7b7b7b] text-gray-500 mb-6">Administra tu perfil y configuración de cuenta</p>

    {{-- Separator --}}
    <div class="border-t dark:border-[#333] border-gray-200 mb-6"></div>

    {{-- Layout: Sidebar + Content --}}
    <div class="flex gap-8 max-md:flex-col">
        {{-- Sidebar Navigation --}}
        <nav class="w-full md:w-[200px] shrink-0 space-y-1">
            @php
                $links = [
                    ['route' => 'profile.edit', 'label' => '👤 Perfil'],
                    ['route' => 'user-password.edit', 'label' => '🔒 Contraseña'],
                ];
                if (Laravel\Fortify\Features::canManageTwoFactorAuthentication()) {
                    $links[] = ['route' => 'two-factor.show', 'label' => '🛡️ Dos Factores'];
                }
                $links[] = ['route' => 'appearance.edit', 'label' => '🎨 Apariencia'];
            @endphp
            @foreach($links as $link)
                <a href="{{ route($link['route']) }}" wire:navigate
                   class="block px-3 py-2 text-sm rounded-md transition-all duration-200
                          {{ request()->routeIs($link['route'])
                              ? 'dark:bg-[#007fd4]/20 bg-blue-50 dark:text-[#007fd4] text-blue-600 border-l-2 border-[#007fd4]'
                              : 'dark:text-[#8b949e] text-gray-500 dark:hover:bg-[#333] hover:bg-gray-100 dark:hover:text-white hover:text-gray-900' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        {{-- Content --}}
        <div class="flex-1 min-w-0">
