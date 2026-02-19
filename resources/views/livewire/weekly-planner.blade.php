<div x-data="{ sidebarOpen: window.innerWidth >= 768, ctxMenu: { show: false, x: 0, y: 0, taskId: null } }" @click="ctxMenu.show = false" class="flex h-screen bg-[#1e1e1e] text-[#d4d4d4] font-sans antialiased relative">
    
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
    
    <div :class="sidebarOpen ? 'translate-x-0 shadow-xl' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-40 w-64 bg-[#252526] border-r border-[#333] p-4 overflow-y-auto custom-scrollbar transform transition-transform duration-300 ease-in-out">
        <livewire:components.sidebar :selectedPeriodId="$selectedPeriodId" wire:key="sidebar-main-component" />
    </div>

    <div :class="sidebarOpen ? 'md:ml-64' : ''" class="flex-1 flex flex-col h-full bg-[#1e1e1e] w-full transition-all duration-300 ease-in-out overflow-hidden">
        
        @if($currentPeriod)
            <div class="flex-shrink-0 z-10 bg-[#1e1e1e]">
                <livewire:components.task-navbar :selectedPeriodId="$currentPeriod->id" wire:key="navbar-{{ $currentPeriod->id }}" />
            </div>
            
            <div class="flex-1 overflow-y-auto custom-scrollbar p-3 lg:p-8 pb-24 lg:pb-8">

                {{-- ===== VISTA MÓVIL: TARJETAS (< md) ===== --}}
                <div id="mobile-tasks-container" class="lg:hidden space-y-3 min-h-[50px]">
                    @forelse($tasks as $task)
                        <div 
                            data-task-id="{{ $task->id }}"
                            wire:key="task-mobile-{{ $task->id }}"
                            @contextmenu.prevent="ctxMenu = { show: true, x: $event.clientX, y: $event.clientY, taskId: {{ $task->id }} }"
                            class="bg-[#252526] rounded-md border border-[#333] p-4 space-y-3 shadow-sm select-none"
                        >
                            {{-- Fila 1: Handle + Estado + Título + Acciones --}}
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2 flex-1 min-w-0">
                                    {{-- Drag Handle Móvil (Área de toque aumentada) --}}
                                    <div class="drag-handle cursor-grab active:cursor-grabbing text-[#7b7b7b] hover:text-[#d4d4d4] p-2 -ml-2 transition-colors flex-shrink-0" @click.stop>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M7 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 2zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 14zm6-8a2 2 0 1 0-.001-4.001A2 2 0 0 0 13 6zm0 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 14z"/>
                                        </svg>
                                    </div>

                                    <button 
                                        wire:click="cycleStatus({{ $task->id }})"
                                        @click.stop
                                        @if($task->progress >= 100) disabled @endif
                                        class="inline-flex items-center px-2 py-1 rounded text-xs font-medium border transition-all select-none flex-shrink-0
                                        {{ $task->progress >= 100 ? 'opacity-75 cursor-not-allowed' : 'cursor-pointer' }}
                                        {{ $task->status->color() === 'green' ? 'bg-[#1e3a23] text-[#7ee787] border-[#2ea043]' : '' }}
                                        {{ $task->status->color() === 'blue'  ? 'bg-[#152e42] text-[#79c0ff] border-[#1f6feb]' : '' }}
                                        {{ $task->status->color() === 'yellow'? 'bg-[#362808] text-[#d29922] border-[#9e6a03]' : '' }}
                                        {{ $task->status->color() === 'gray'  ? 'bg-[#26282e] text-[#8b949e] border-[#30363d]' : '' }}">
                                        <span class="mr-1 opacity-70">
                                            @if($task->status->color() === 'green') ✓ 
                                            @elseif($task->status->color() === 'blue') ▶
                                            @elseif($task->status->color() === 'yellow') ⏸
                                            @else • @endif
                                        </span>
                                        {{ $task->status->label() }}
                                    </button>
                                    <span class="font-medium text-[#d4d4d4] text-sm truncate block">{{ $task->title }}@if($task->is_persistent) <span class="text-[#569cd6]" title="Tarea Persistente">🔁</span>@endif</span>
                                </div>
                                
                                <div class="flex items-center gap-1 flex-shrink-0">
                                    <button 
                                        wire:click="$dispatch('openTaskDetails', { taskId: {{ $task->id }} })"
                                        @click.stop
                                        class="text-[#007fd4] hover:text-white text-xs font-medium transition-colors px-2 py-1 rounded hover:bg-[#333] border border-[#333]">
                                        Detalles
                                    </button>
                                    <button 
                                        wire:click="openTaskForm({{ $task->id }})"
                                        @click.stop
                                        class="text-[#7b7b7b] hover:text-white transition-colors p-1.5 rounded hover:bg-[#333]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Fila 2: Selector Subtareas --}}
                            @if($task->subtasks->count() > 0)
                                <div @click.stop>
                                    <select wire:model="selectedSubtask.{{ $task->id }}" 
                                            class="w-full px-2 py-1.5 text-xs bg-[#3c3c3c] border border-[#333] rounded text-[#d4d4d4] focus:border-[#007fd4] focus:ring-[#007fd4] focus:outline-none">
                                        <option value="">-- Tarea principal --</option>
                                        @foreach($task->subtasks as $subtask)
                                            <option value="{{ $subtask->id }}">{{ $subtask->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            {{-- Fila 3: Métricas --}}
                            <div class="grid grid-cols-2 gap-3 text-xs font-mono">
                                <div class="bg-[#1e1e1e] rounded p-2 text-center border border-[#333]/50">
                                    <div class="text-[#7b7b7b] mb-1">Realizado</div>
                                    @php $effectiveSpent = $task->effective_spent_minutes; @endphp
                                    @if($task->completion_method === 'subtasks')
                                        <div class="text-[#9cdcfe]">
                                            {{ $task->subtasks->where('is_completed', true)->count() }} / {{ $task->subtasks->count() }}
                                        </div>
                                        <div class="text-[#4ec9b0] text-[10px]">{{ intdiv($effectiveSpent, 60) }}h {{ $effectiveSpent % 60 }}m</div>
                                    @else
                                        <div class="text-[#d4d4d4]">{{ intdiv($effectiveSpent, 60) }}h {{ $effectiveSpent % 60 }}m</div>
                                    @endif
                                </div>
                                <div class="bg-[#1e1e1e] rounded p-2 text-center border border-[#333]/50">
                                    <div class="text-[#7b7b7b] mb-1">Restante</div>
                                    @if($task->completion_method === 'subtasks')
                                        @php
                                            $remainingSubtasks = $task->subtasks->where('is_completed', false)->count();
                                            $effectiveEst = $task->effective_estimated_minutes;
                                            $effectiveRemaining = max(0, $effectiveEst - $effectiveSpent);
                                        @endphp
                                        <div class="{{ $remainingSubtasks == 0 ? 'text-[#4ec9b0]' : 'text-[#ce9178]' }}">
                                            {{ $remainingSubtasks }} pend.
                                        </div>
                                        <div class="text-[10px] {{ $effectiveRemaining == 0 ? 'text-[#4ec9b0]' : 'text-[#ce9178]' }}">{{ intdiv($effectiveRemaining, 60) }}h {{ $effectiveRemaining % 60 }}m</div>
                                    @else
                                        @php
                                            $effectiveEst = $task->effective_estimated_minutes;
                                            $diffMinutes = $effectiveEst - $effectiveSpent;
                                            $isOvertime = $diffMinutes < 0;
                                            $absMinutes = abs($diffMinutes);
                                        @endphp
                                        <div class="{{ $isOvertime ? 'text-[#f14c4c]' : ($diffMinutes == 0 ? 'text-[#4ec9b0]' : 'text-[#ce9178]') }}">
                                            {{ $isOvertime ? '-' : '' }}{{ intdiv($absMinutes, 60) }}h {{ $absMinutes % 60 }}m
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Fila 4: Progreso y Tiempo --}}
                            <div>
                                <div class="flex justify-between text-xs mb-1 font-mono">
                                    @php
                                        $effectiveEst = $task->effective_estimated_minutes;
                                        $effectiveSpentVal = $task->effective_spent_minutes;
                                    @endphp
                                    <span class="text-[#9cdcfe]">
                                        {{ intdiv($effectiveSpentVal, 60) }}h {{ $effectiveSpentVal % 60 }}m<span class="text-[#6a9955]"> / {{ intdiv($effectiveEst, 60) }}h {{ $effectiveEst % 60 }}m</span>
                                    </span>
                                    <span class="{{ $task->progress >= 100 ? 'text-[#4ec9b0]' : 'text-[#7b7b7b]' }}">{{ $task->progress }}%</span>
                                </div>
                                <div class="w-full bg-[#3c3c3c] rounded-full h-1.5 overflow-hidden mb-3">
                                    <div class="h-1.5 rounded-full transition-all duration-500 {{ $task->progress >= 100 ? 'bg-[#4ec9b0]' : 'bg-[#007fd4]' }}" 
                                         style="width: {{ min($task->progress, 100) }}%"></div>
                                </div>
                                <div class="flex gap-2" @click.stop>
                                    <input 
                                        type="number" 
                                        placeholder="0" 
                                        wire:model="minutesInput.{{ $task->id }}"
                                        wire:keydown.enter="addTime({{ $task->id }})"
                                        class="w-full px-3 py-2 text-sm bg-[#3c3c3c] border border-[#333] rounded text-[#d4d4d4] focus:border-[#007fd4] focus:ring-1 focus:ring-[#007fd4] focus:outline-none placeholder-[#666]"
                                    >
                                    <button 
                                        wire:click="addTime({{ $task->id }})"
                                        class="px-4 py-2 bg-[#333] hover:bg-[#444] text-[#d4d4d4] rounded border border-[#333] text-sm font-bold transition-colors"
                                        title="Sumar tiempo">
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="bg-[#252526] rounded-md border border-[#333] p-8 text-center text-[#7b7b7b] italic font-mono">
                            // No hay tareas en esta semana
                        </div>
                    @endforelse
                </div>

                {{-- ===== VISTA ESCRITORIO: TABLA (>= md) ===== --}}
                <div class="hidden lg:block bg-[#252526] rounded-md shadow-xl overflow-x-auto border border-[#333]">
                    <table class="w-full min-w-[850px] text-left border-collapse">
                        <thead class="bg-[#1e1e1e] text-[#7b7b7b] text-xs uppercase font-semibold tracking-wider sticky top-0 z-10">
                            <tr>
                                <th class="p-4 border-b border-[#333] w-32">Estado</th>
                                <th class="p-4 border-b border-[#333]">Actividad/Tarea</th>
                                <th class="p-4 border-b border-[#333] w-40 text-left">Asignar a</th>
                                <th class="p-4 border-b border-[#333] w-32 text-center">Trabajo realizado</th>
                                <th class="p-4 border-b border-[#333] w-32 text-center">Trabajo restante</th>
                                <th class="p-4 border-b border-[#333] w-48">Control de Tiempo</th>
                                <th class="p-4 border-b border-[#333] w-20">Editar</th>
                            </tr>
                        </thead>
                        <tbody id="tasks-tbody" class="divide-y divide-[#333]">
                            @forelse($tasks as $task)
                                <tr 
                                    data-task-id="{{ $task->id }}"
                                    wire:key="task-desktop-{{ $task->id }}" 
                                    wire:click="openTaskForm({{ $task->id }})"
                                    @contextmenu.prevent="ctxMenu = { show: true, x: $event.clientX, y: $event.clientY, taskId: {{ $task->id }} }"
                                    class="hover:bg-[#2a2d2e] transition-colors group cursor-pointer"
                                >
                                    <td class="p-4">
                                        <div class="flex items-center gap-2">
                                            <div class="drag-handle cursor-grab active:cursor-grabbing text-[#7b7b7b] hover:text-[#d4d4d4] transition-colors" @click.stop>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M7 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 2zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 14zm6-8a2 2 0 1 0-.001-4.001A2 2 0 0 0 13 6zm0 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 14z"/>
                                                </svg>
                                            </div>
                                            <button 
                                                wire:click="cycleStatus({{ $task->id }})"
                                                @click.stop
                                                @if($task->progress >= 100) disabled @endif
                                                class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium border transition-all select-none
                                                {{ $task->progress >= 100 ? 'opacity-75 cursor-not-allowed' : 'cursor-pointer' }}
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
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-[#d4d4d4] group-hover:text-white transition-colors">{{ $task->title }}@if($task->is_persistent) <span class="text-[#569cd6]" title="Tarea Persistente">🔁</span>@endif</span>
                                            <button 
                                                wire:click="$dispatch('openTaskDetails', { taskId: {{ $task->id }} })"
                                                @click.stop
                                                class="text-[#007fd4] hover:text-white text-xs font-medium transition-colors px-1.5 py-0.5 rounded hover:bg-[#333] border border-[#333]">
                                                Detalles
                                            </button>
                                        </div>
                                    </td>
                                    <td class="p-4 text-left" @click.stop>
                                        @if($task->subtasks->count() > 0)
                                            <select wire:model="selectedSubtask.{{ $task->id }}" 
                                                    class="max-w-xs px-2 py-1.5 text-xs bg-[#3c3c3c] border border-[#333] rounded text-[#d4d4d4] focus:border-[#007fd4] focus:ring-[#007fd4] focus:outline-none">
                                                <option value="">-- Tarea principal --</option>
                                                @foreach($task->subtasks as $subtask)
                                                    <option value="{{ $subtask->id }}">{{ $subtask->title }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <span class="text-[#7b7b7b] text-xs italic">Sin subtareas</span>
                                        @endif
                                    </td>
                                    <td class="p-4 text-center font-mono text-sm text-[#d4d4d4]">
                                         @php $effectiveSpent = $task->effective_spent_minutes; @endphp
                                         {{ intdiv($effectiveSpent, 60) }}h {{ $effectiveSpent % 60 }}m
                                    </td>
                                    <td class="p-4 text-center font-mono text-sm">
                                        @php
                                            $effectiveEst = $task->effective_estimated_minutes;
                                            $effectiveSpent = $task->effective_spent_minutes;
                                            $diffMinutes = $effectiveEst - $effectiveSpent;
                                            $isOvertime = $diffMinutes < 0;
                                            $absMinutes = abs($diffMinutes);
                                        @endphp
                                        <span class="{{ $isOvertime ? 'text-[#f14c4c]' : ($diffMinutes == 0 ? 'text-[#4ec9b0]' : 'text-[#ce9178]') }}">
                                            {{ $isOvertime ? '-' : '' }}{{ intdiv($absMinutes, 60) }}h {{ $absMinutes % 60 }}m
                                        </span>
                                    </td>
                                    <td class="p-4 align-top">
                                        <div class="mb-3">
                                            <div class="flex justify-between text-xs mb-1 font-mono">
                                                <span class="text-[#9cdcfe]">
                                                     {{ intdiv($task->effective_spent_minutes, 60) }}h {{ $task->effective_spent_minutes % 60 }}m<span class="text-[#6a9955]"> / {{ intdiv($task->effective_estimated_minutes, 60) }}h {{ $task->effective_estimated_minutes % 60 }}m</span>
                                                </span>
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
                                            <input type="number" @click.stop placeholder="0" wire:model="minutesInput.{{ $task->id }}" wire:keydown.enter="addTime({{ $task->id }})" class="w-full px-2 py-1 text-xs bg-[#3c3c3c] border border-[#333] rounded text-[#d4d4d4] focus:border-[#007fd4] focus:ring-1 focus:ring-[#007fd4] focus:outline-none placeholder-[#666]">
                                            <button wire:click="addTime({{ $task->id }})" @click.stop class="px-3 py-1 bg-[#333] hover:bg-[#444] text-[#d4d4d4] rounded border border-[#333] text-xs font-bold transition-colors" title="Sumar tiempo">+</button>
                                        </div>
                                    </td>
                                    <td class="p-4 text-right">
                                        <button wire:click="openTaskForm({{ $task->id }})" @click.stop class="text-[#7b7b7b] hover:text-white transition-colors p-1 rounded hover:bg-[#333]">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-12 text-center text-[#7b7b7b] italic font-mono">
                                        // No hay tareas en esta semana
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    {{ $tasks->links() }}
                </div>
            </div>
        @else
            <div class="flex flex-col items-center justify-center h-full text-[#7b7b7b]">
                <div class="text-6xl mb-4 opacity-20">No hay semanas activas</div>
                <p class="font-mono text-sm">Genera una semana para comenzar...</p>
            </div>
        @endif
    </div>

    <livewire:modals.task-form />
    <livewire:modals.period-form />
    <livewire:modals.task-details />
    <livewire:modals.duplicate-task />

    <!-- Context Menu -->
    <div x-show="ctxMenu.show" 
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         :style="`position: fixed; left: ${ctxMenu.x}px; top: ${ctxMenu.y}px; z-index: 100;`"
         @click.away="ctxMenu.show = false"
         class="bg-[#252526] border border-[#333] rounded shadow-xl py-1 min-w-[160px]">
        <button @click="$wire.dispatch('openDuplicateTask', { taskId: ctxMenu.taskId }); ctxMenu.show = false" 
                class="w-full text-left px-4 py-2 text-sm text-[#d4d4d4] hover:bg-[#094771] flex items-center gap-2 transition-colors">
            📋 Duplicar
        </button>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 10px; background-color: #1e1e1e; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #424242; border-radius: 5px; border: 2px solid #1e1e1e; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #4f4f4f; }
        
        /* SortableJS Styles */
        .sortable-ghost { opacity: 0.5 !important; background-color: #1a1a1a !important; }
        .sortable-drag { opacity: 0.8 !important; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3) !important; transform: scale(1.02); }
        
        /* Smooth transitions */
        #tasks-tbody tr, #mobile-tasks-container > div { transition: transform 0.2s ease, background-color 0.2s ease; }

        /* Estilos de Drop Zones del Sidebar */
        .period-drop-zone > tr, .period-drop-zone > .sortable-ghost, .period-drop-zone > .sortable-fallback { display: none !important; }
        
        @keyframes readyToReceive {
            0%, 100% { background-color: rgba(34, 197, 94, 0.1); border-left-color: #22c55e; }
            50% { background-color: rgba(34, 197, 94, 0.25); border-left-color: #4ade80; }
        }
        
        .period-drop-zone.drag-over-zone {
            animation: readyToReceive 0.8s ease-in-out infinite !important;
            border-left-width: 3px !important;
            transform: scale(1.02) !important;
            z-index: 10 !important;
        }

        @keyframes receiveTask {
            0% { background-color: rgba(34, 197, 94, 0.4); }
            100% { background-color: transparent; }
        }
        .period-drop-zone.task-received { animation: receiveTask 0.6s ease-out forwards; }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <script>
        document.addEventListener('livewire:navigated', initSortable);
        document.addEventListener('DOMContentLoaded', initSortable);
        
        function initSortable() {
            // 1. Sortable para ESCRITORIO (Tabla)
            const tbody = document.getElementById('tasks-tbody');
            if (tbody) {
                new Sortable(tbody, {
                    group: { name: 'tasks', pull: true, put: false }, // Permite sacar tareas hacia el sidebar
                    animation: 300,
                    handle: '.drag-handle',
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    onEnd: function(evt) {
                        if (evt.to === tbody) {
                            const orderedIds = Array.from(tbody.querySelectorAll('tr[data-task-id]'))
                                .map(row => parseInt(row.dataset.taskId));
                            @this.call('updateTaskOrder', orderedIds);
                        }
                    }
                });
            }

            // 2. Sortable para MÓVIL (Tarjetas)
            const mobileContainer = document.getElementById('mobile-tasks-container');
            if (mobileContainer) {
                new Sortable(mobileContainer, {
                    group: { name: 'tasks', pull: true, put: false },
                    animation: 300,
                    handle: '.drag-handle',
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    delay: 100, // Evita conflictos con el scroll táctil
                    delayOnTouchOnly: true,
                    onEnd: function(evt) {
                        if (evt.to === mobileContainer) {
                            const orderedIds = Array.from(mobileContainer.querySelectorAll('div[data-task-id]'))
                                .map(card => parseInt(card.dataset.taskId));
                            @this.call('updateTaskOrder', orderedIds);
                        }
                    }
                });
            }
            
            // 3. Drop Zones del Sidebar (Para mover tareas entre semanas)
            const periodDropZones = document.querySelectorAll('.period-drop-zone');
            periodDropZones.forEach(function(zone) {
                new Sortable(zone, {
                    group: { name: 'tasks', put: true, pull: false }, // Solo recibe
                    sort: false, 
                    animation: 150,
                    onAdd: function(evt) {
                        // Animación visual
                        evt.to.classList.remove('drag-over-zone');
                        evt.to.classList.add('task-received');
                        setTimeout(() => evt.to.classList.remove('task-received'), 700);
                        
                        // Datos
                        const taskId = parseInt(evt.item.dataset.taskId);
                        const newPeriodId = parseInt(evt.to.dataset.periodId);
                        
                        // Llamada Livewire
                        @this.call('moveTaskToPeriod', taskId, newPeriodId);
                        
                        // Limpieza DOM
                        evt.item.remove();
                    },
                    // Gestión de highlights visuales
                    onMove: function(evt) {
                        periodDropZones.forEach(z => z.classList.remove('drag-over-zone'));
                        if (evt.to) evt.to.classList.add('drag-over-zone');
                        return true;
                    }
                });
            });

            // Limpieza general de clases al soltar
            document.addEventListener('mouseup', function() {
                periodDropZones.forEach(z => z.classList.remove('drag-over-zone'));
            });
        }
    </script>
</div>