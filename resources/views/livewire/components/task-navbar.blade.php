<div class="sticky top-0 z-20 bg-[#1e1e1e] border-b border-[#333] px-8">
    @if($currentPeriod)
        <!-- Row 1: Top Bar (App Context) -->
        <div class="flex justify-between items-center py-2 px-0 border-b border-[#333]/50">
            <!-- Left: Sidebar Toggle -->
            <div class="flex items-center gap-4">
                <div class="flex items-center">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-[#d4d4d4] hover:text-white p-2 -ml-2 rounded hover:bg-[#333] transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <span class="ml-2 text-sm font-medium text-[#7b7b7b]">Semanas</span>
                </div>
            </div>

            <!-- Right: User Profile -->
            <div x-data="{ open: false }" class="relative" wire:ignore>
                <button @click="open = !open" class="flex items-center gap-2 text-[#d4d4d4] hover:text-white transition-colors focus:outline-none bg-[#252526] hover:bg-[#333] px-3 py-1.5 rounded border border-[#333]">
                    <div class="h-6 w-6 rounded-full bg-[#007fd4] flex items-center justify-center text-xs font-bold text-white">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <span class="text-sm font-medium">{{ auth()->user()->name }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="open" 
                    @click.away="open = false"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-48 bg-[#252526] rounded-md shadow-xl py-1 border border-[#333] z-50">
                    
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-[#d4d4d4] hover:bg-[#007fd4] hover:text-white transition-colors">
                        Perfil
                    </a>

                    <div class="border-t border-[#333] my-1"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-[#d4d4d4] hover:bg-[#c53030] hover:text-white transition-colors">
                            Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Row 2: Action Bar (Period Context) -->
        <div class="flex justify-between items-center py-4">
            <!-- Left: Period Info -->
            <div>
                <h1 class="text-xl font-light text-white mb-0 flex items-baseline gap-3">
                    <span class="text-[#007fd4]">{{ $currentPeriod->name }}</span>
                    <span class="text-[#7b7b7b] text-xs font-mono">
                        <span class="text-[#ce9178]">Inicia</span>: "{{ \Carbon\Carbon::parse($currentPeriod->start_date)->format('Y-m-d') }}"
                        <span class="mx-2">|</span>
                        <span class="text-[#ce9178]">Termina</span>: "{{ \Carbon\Carbon::parse($currentPeriod->end_date)->format('Y-m-d') }}"
                    </span>
                </h1>
            </div>

            <!-- Right: Actions -->
            <div class="flex items-center gap-4">
                <button wire:click="openTaskForm" class="bg-[#007fd4] hover:bg-[#006cb5] text-white px-3 py-1.5 rounded-sm text-sm font-medium transition-colors flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tarea
                </button>
                <div class="bg-[#252526] border border-[#333] px-4 py-2 rounded text-xs font-mono text-[#4ec9b0]">
                    Tareas: <span class="text-[#b5cea8]">{{ $currentPeriod->tasks->count() }}</span>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-8 text-[#7b7b7b]">
            <p>No hay período seleccionado</p>
        </div>
    @endif
</div>
