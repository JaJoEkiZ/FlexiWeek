<div class="bg-[#1e1e1e] border-b border-[#333] px-8 py-2 relative h-56 z-30 flex items-center justify-between">
    @if($currentPeriod)
        <!-- LEFT COLUMN: Toggle & Period -->
        <div class="flex flex-col gap-6 items-start z-10 w-1/3">
            <!-- Sidebar Toggle -->
            <div>
                <button @click="sidebarOpen = !sidebarOpen" class="text-[#d4d4d4] hover:text-white p-2 -ml-2 rounded hover:bg-[#333] transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

            <!-- Period Info -->
            <div class="flex flex-col">
                <h1 class="text-4xl font-light text-white leading-none mb-2">
                    <span class="text-[#007fd4]">{{ $currentPeriod->name }}</span>
                </h1>
                <p class="text-[#7b7b7b] text-sm font-mono leading-none">
                    {{ \Carbon\Carbon::parse($currentPeriod->start_date)->format('d/m') }} - {{ \Carbon\Carbon::parse($currentPeriod->end_date)->format('d/m/Y') }}
                </p>
            </div>
        </div>

        <!-- CENTER: Logo -->
        <div class="absolute left-1/2 top-1/2 transform -translate-x-1/2 -translate-y-1/2 z-0">
             <img src="{{ asset('images/flexiweek-Logo.png') }}" alt="FlexiWeek" class="pt-4 h-48 w-auto opacity-90 hover:opacity-100 transition-all duration-300" style="filter: drop-shadow(5px 5px 15px rgba(1, 36, 68, 0.9));">
        </div>

        <!-- RIGHT COLUMN: Profile & Actions -->
        <div class="flex flex-col gap-6 items-end z-10 w-1/3">
            <!-- User Profile -->
            <div x-data="{ open: false }" class="relative" wire:ignore>
                <button @click="open = !open" class="flex items-center gap-3 text-[#d4d4d4] hover:text-white transition-colors focus:outline-none bg-[#252526] hover:bg-[#333] px-3 py-2 rounded-md border border-[#333] shadow-sm">
                    <div class="flex flex-col items-end">
                        <span class="text-sm font-medium leading-none">{{ auth()->user()->name }}</span>
                        <span class="text-[10px] text-[#7b7b7b] leading-none mt-1">Admin</span>
                    </div>
                    <div class="h-9 w-9 rounded-full bg-[#007fd4] flex items-center justify-center text-sm font-bold text-white ring-2 ring-[#1e1e1e]">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Dropdown -->
                <div x-show="open" 
                    @click.away="open = false"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-48 bg-[#252526] rounded-md shadow-xl py-1 border border-[#333] z-50">
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-[#d4d4d4] hover:bg-[#007fd4] hover:text-white transition-colors">Perfil</a>
                    <div class="border-t border-[#333] my-1"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-[#d4d4d4] hover:bg-[#c53030] hover:text-white transition-colors">Cerrar Sesión</button>
                    </form>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-4">
                <div class="bg-[#252526] border border-[#333] px-4 py-1.5 rounded text-xs font-mono text-[#4ec9b0] shadow-sm">
                    Tareas: <span class="text-[#b5cea8]">{{ $currentPeriod->tasks->count() }}</span>
                </div>
                <button wire:click="openTaskForm" class="bg-[#007fd4] hover:bg-[#006cb5] text-white px-4 py-2 rounded-sm text-sm font-medium transition-colors flex items-center gap-2 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tarea
                </button>
            </div>
        </div>
    @else
        <div class="text-center py-8 text-[#7b7b7b] w-full">
            <p>No hay período seleccionado</p>
        </div>
    @endif
</div>
