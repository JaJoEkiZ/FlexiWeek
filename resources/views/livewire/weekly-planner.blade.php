<div x-data="{ sidebarOpen: window.innerWidth >= 768 }" class="flex h-screen bg-[#1e1e1e] text-[#d4d4d4] font-sans antialiased relative">
    
    <!-- Mobile Overlay -->
    <div x-show="sidebarOpen" 
         @click="sidebarOpen = false"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-50 z-30 md:hidden">
    </div>
    
    <!-- Sidebar -->
    <div :class="sidebarOpen ? 'translate-x-0 shadow-xl' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-40 w-64 bg-[#252526] border-r border-[#333] p-4 overflow-y-auto custom-scrollbar transform transition-transform duration-300 ease-in-out">
        <livewire:components.sidebar :selectedPeriodId="$selectedPeriodId" />
    </div>

    <!-- Main Content -->
    <div :class="sidebarOpen ? 'md:ml-64' : ''" class="flex-1 p-8 overflow-y-auto bg-[#1e1e1e] custom-scrollbar w-full transition-all duration-300 ease-in-out">
        <!-- Header Trigger -->
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <!-- Left: Sidebar Toggle -->
            <div class="flex items-center">
                <button @click="sidebarOpen = !sidebarOpen" class="text-[#d4d4d4] hover:text-white p-2 -ml-2 rounded hover:bg-[#333] transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <span class="ml-2 text-sm font-medium text-[#7b7b7b]">Semanas</span>
            </div>

            <!-- Right: User Dropdown -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center gap-2 text-[#d4d4d4] hover:text-white transition-colors focus:outline-none bg-[#252526] hover:bg-[#333] px-3 py-1.5 rounded border border-[#333]">
                    <div class="h-6 w-6 rounded-full bg-[#007fd4] flex items-center justify-center text-xs font-bold text-white">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <span class="text-sm font-medium">{{ Auth::user()->name }}</span>
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
        @if($currentPeriod)
            <div class="flex justify-between items-center mb-8 border-b border-[#333] pb-6">
                <div>
                    <h1 class="text-3xl font-light text-white mb-1"><span class="text-[#007fd4]">{{ $currentPeriod->name }}</span></h1>
                    <p class="text-[#7b7b7b] text-sm font-mono">
                        <span class="text-[#ce9178]">Inicia</span>: "{{ \Carbon\Carbon::parse($currentPeriod->start_date)->format('Y-m-d') }}"
                        <span class="mx-2">|</span>
                        <span class="text-[#ce9178]">Termina</span>: "{{ \Carbon\Carbon::parse($currentPeriod->end_date)->format('Y-m-d') }}"
                    </p>
                </div>
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

            <div class="bg-[#252526] rounded-md shadow-xl overflow-hidden border border-[#333]">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-[#1e1e1e] text-[#7b7b7b] text-xs uppercase font-semibold tracking-wider">
                        <tr>
                            <th class="p-4 border-b border-[#333] w-32">Estado</th>
                            <th class="p-4 border-b border-[#333]">Actividad/Tarea</th>
                            <th class="p-4 border-b border-[#333] w-32 text-center">Hs de trabajo</th>
                            <th class="p-4 border-b border-[#333] w-32 text-center">Horas Restantes</th>
                            <th class="p-4 border-b border-[#333] w-48">Control de Tiempo</th>
                            <th class="p-4 border-b border-[#333] w-20">Editar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#333]">
                        @forelse($currentPeriod->tasks as $task)
                            <tr wire:key="task-{{ $task->id }}" class="hover:bg-[#2a2d2e] transition-colors group">
                                <td class="p-4">
                                        <button 
                                                wire:click="cycleStatus({{ $task->id }})"
                                                class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium border transition-all cursor-pointer select-none
                                                {{ $task->status->color() === 'green' ? 'bg-[#1e3a23] text-[#7ee787] border-[#2ea043]' : '' }}
                                                {{ $task->status->color() === 'blue'  ? 'bg-[#152e42] text-[#79c0ff] border-[#1f6feb]' : '' }}
                                                {{ $task->status->color() === 'yellow'? 'bg-[#362808] text-[#d29922] border-[#9e6a03]' : '' }}
                                                {{ $task->status->color() === 'gray'  ? 'bg-[#26282e] text-[#8b949e] border-[#30363d]' : '' }}">

                                                <span class="mr-1.5 opacity-70">
                                                    @if($task->status->color() === 'green') ✓ 
                                                    @elseif($task->status->color() === 'blue') ▶
                                                    @elseif($task->status->color() === 'yellow') ⏸
                                                    @else • @endif
                                                </span>

                                                {{ $task->status->label() }}
                                        </button>
                                </td>
                                <td class="p-4">
                                    <div class="font-medium text-[#d4d4d4] group-hover:text-white transition-colors">
                                        <span class="text-[#569cd6] opacity-50 mr-2"></span>{{ $task->title }}
                                    </div>
                                    </div>
                                </td>
                                <td class="p-4 text-center font-mono text-sm text-[#d4d4d4]">
                                    @php
                                        $workedHours = intdiv($task->total_spent, 60);
                                        $workedMinutes = $task->total_spent % 60;
                                    @endphp
                                    {{ $workedHours }}h {{ $workedMinutes }}m
                                </td>
                                <td class="p-4 text-center font-mono text-sm">
                                    @php
                                        $remainingMinutes = max(0, $task->estimated_minutes - $task->total_spent);
                                        $remainingHours = intdiv($remainingMinutes, 60);
                                        $remMinutes = $remainingMinutes % 60;
                                        $isOvertime = $task->total_spent > $task->estimated_minutes;
                                    @endphp
                                    <span class="{{ $isOvertime ? 'text-[#f14c4c]' : ($remainingMinutes == 0 ? 'text-[#4ec9b0]' : 'text-[#ce9178]') }}">
                                        {{ $isOvertime ? '-' : '' }}{{ $remainingHours }}h {{ $remMinutes }}m
                                    </span>
                                </td>
                                <td class="p-4 align-top">
                                        <div class="mb-3">
                                            <div class="flex justify-between text-xs mb-1 font-mono">
                                                <span class="text-[#9cdcfe]">{{ $task->total_spent }}m <span class="text-[#6a9955]">// {{ $task->estimated_minutes }}m</span></span>
                                                <span class="{{ $task->progress >= 100 ? 'text-[#4ec9b0]' : 'text-[#7b7b7b]' }}">
                                                    {{ $task->progress }}%
                                                </span>
                                            </div>

                                            <div class="w-full bg-[#3c3c3c] rounded-full h-1.5 overflow-hidden">
                                                <div class="h-1.5 rounded-full transition-all duration-500 {{ $task->progress >= 100 ? 'bg-[#4ec9b0]' : 'bg-[#007fd4]' }}" 
                                                     style="width: {{ $task->progress }}%"></div>
                                            </div>
                                        </div>

                                        <div class="flex gap-2">
                                            <input 
                                                type="number" 
                                                placeholder="0" 
                                                wire:model="minutesInput.{{ $task->id }}"
                                                wire:keydown.enter="addTime({{ $task->id }})"
                                                class="w-full px-2 py-1 text-xs bg-[#3c3c3c] border border-[#333] rounded text-[#d4d4d4] focus:border-[#007fd4] focus:ring-1 focus:ring-[#007fd4] focus:outline-none placeholder-[#666]"
                                            >

                                            <button 
                                                wire:click="addTime({{ $task->id }})"
                                                class="px-3 py-1 bg-[#333] hover:bg-[#444] text-[#d4d4d4] rounded border border-[#333] text-xs font-bold transition-colors"
                                                title="Sumar tiempo">
                                                +
                                            </button>
                                        </div>
                                    </td>
                                <td class="p-4 text-right">
                                    <button 
                                        wire:click="openTaskForm({{ $task->id }})"
                                        class="text-[#7b7b7b] hover:text-white transition-colors p-1 rounded hover:bg-[#333]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-12 text-center text-[#7b7b7b] italic font-mono">
                                    // No tasks found in this period
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <div class="flex flex-col items-center justify-center h-full text-[#7b7b7b]">
                <div class="text-6xl mb-4 opacity-20">code</div>
                <p class="font-mono text-sm">Select a period to start coding...</p>
            </div>
        @endif
    </div>

    <!-- Componente Modal de Tarea -->
    <livewire:modals.task-form />

    <!-- Componente Modal de Periodo -->
    <livewire:modals.period-form />



    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 10px;
            background-color: #1e1e1e;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #424242;
            border-radius: 5px;
            border: 2px solid #1e1e1e;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background-color: #4f4f4f;
        }
    </style>
</div>
