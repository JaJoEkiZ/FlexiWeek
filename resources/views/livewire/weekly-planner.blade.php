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
    <div :class="sidebarOpen ? 'md:ml-64' : ''" class="flex-1 flex flex-col h-full bg-[#1e1e1e] w-full transition-all duration-300 ease-in-out overflow-hidden">
        <!-- Navbar (Fixed) -->
        @if($currentPeriod)
            <livewire:components.task-navbar :selectedPeriodId="$currentPeriod->id" wire:key="navbar-{{ $currentPeriod->id }}" />
            
            <!-- Scrollable Area -->
            <div class="flex-1 overflow-y-auto custom-scrollbar p-8">

            <div class="bg-[#252526] rounded-md shadow-xl overflow-hidden border border-[#333]">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-[#1e1e1e] text-[#7b7b7b] text-xs uppercase font-semibold tracking-wider">
                        <tr>
                            <th class="p-4 border-b border-[#333] w-32">Estado</th>
                            <th class="p-4 border-b border-[#333]">Actividad/Tarea</th>
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
                                wire:key="task-{{ $task->id }}" 
                                wire:click="openTaskForm({{ $task->id }})"
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
                                        <span class="font-medium text-[#d4d4d4] group-hover:text-white transition-colors">{{ $task->title }}</span>
                                        @if($task->description || $task->subtasks->where('description', '!=', '')->count() > 0)
                                            <button 
                                                wire:click="$dispatch('openTaskDetails', { taskId: {{ $task->id }} })"
                                                @click.stop
                                                class="text-[#007fd4] hover:text-white text-xs font-medium transition-colors px-1.5 py-0.5 rounded hover:bg-[#333] border border-[#333]">
                                                Detalles
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-4 text-center font-mono text-sm text-[#d4d4d4]">
                                    @if($task->completion_method === 'subtasks')
                                        <span class="text-[#9cdcfe]">
                                            {{ $task->subtasks->where('is_completed', true)->count() }} / {{ $task->subtasks->count() }}
                                        </span>
                                    @else
                                        @php
                                            $workedHours = intdiv($task->total_spent, 60);
                                            $workedMinutes = $task->total_spent % 60;
                                        @endphp
                                        {{ $workedHours }}h {{ $workedMinutes }}m
                                    @endif
                                </td>
                                <td class="p-4 text-center font-mono text-sm">
                                    @if($task->completion_method === 'subtasks')
                                        @php
                                            $remainingSubtasks = $task->subtasks->where('is_completed', false)->count();
                                        @endphp
                                        <span class="{{ $remainingSubtasks == 0 ? 'text-[#4ec9b0]' : 'text-[#ce9178]' }}">
                                            {{ $remainingSubtasks }} pendientes
                                        </span>
                                    @else
                                        @php
                                            $diffMinutes = $task->estimated_minutes - $task->total_spent;
                                            $isOvertime = $diffMinutes < 0;
                                            $absMinutes = abs($diffMinutes);
                                            $remainingHours = intdiv($absMinutes, 60);
                                            $remMinutes = $absMinutes % 60;
                                        @endphp
                                        <span class="{{ $isOvertime ? 'text-[#f14c4c]' : ($diffMinutes == 0 ? 'text-[#4ec9b0]' : 'text-[#ce9178]') }}">
                                            {{ $isOvertime ? '-' : '' }}{{ $remainingHours }}h {{ $remMinutes }}m
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 align-top">
                                    <div class="mb-3">
                                        <div class="flex justify-between text-xs mb-1 font-mono">
                                            @if($task->completion_method === 'subtasks')
                                                <span class="text-[#9cdcfe]">
                                                    {{ $task->subtasks->where('is_completed', true)->count() }} / {{ $task->subtasks->count() }} Subtasks
                                                </span>
                                            @else
                                                @php
                                                    $spentHours = intdiv($task->total_spent, 60);
                                                    $spentMins = $task->total_spent % 60;
                                                    $estHours = intdiv($task->estimated_minutes, 60);
                                                    $estMins = $task->estimated_minutes % 60;
                                                @endphp
                                                <span class="text-[#9cdcfe]">
                                                    {{ $spentHours }}h {{ $spentMins }}m 
                                                    <span class="text-[#6a9955]">// {{ $estHours }}h {{ $estMins }}m</span>
                                                </span>
                                            @endif
                                            
                                            <span class="{{ $task->progress >= 100 ? 'text-[#4ec9b0]' : 'text-[#7b7b7b]' }}">
                                                {{ $task->progress }}%
                                            </span>
                                        </div>

                                        <div class="w-full bg-[#3c3c3c] rounded-full h-1.5 overflow-hidden">
                                            <div class="h-1.5 rounded-full transition-all duration-500 {{ $task->progress >= 100 ? 'bg-[#4ec9b0]' : 'bg-[#007fd4]' }}" 
                                                 style="width: {{ $task->progress }}%"></div>
                                        </div>
                                    </div>

                                    @if($task->completion_method !== 'subtasks')
                                    <div class="flex gap-2">
                                        <input 
                                            type="number" 
                                            @click.stop
                                            placeholder="0" 
                                            wire:model="minutesInput.{{ $task->id }}"
                                            wire:keydown.enter="addTime({{ $task->id }})"
                                            class="w-full px-2 py-1 text-xs bg-[#3c3c3c] border border-[#333] rounded text-[#d4d4d4] focus:border-[#007fd4] focus:ring-1 focus:ring-[#007fd4] focus:outline-none placeholder-[#666]"
                                        >

                                        <button 
                                            wire:click="addTime({{ $task->id }})"
                                            @click.stop
                                            class="px-3 py-1 bg-[#333] hover:bg-[#444] text-[#d4d4d4] rounded border border-[#333] text-xs font-bold transition-colors"
                                            title="Sumar tiempo">
                                            +
                                        </button>
                                    </div>
                                    @endif
                                </td>
                                <td class="p-4 text-right">
                                    <button 
                                        wire:click="openTaskForm({{ $task->id }})"
                                        @click.stop
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

    <!-- Componente Modal de Tarea -->
    <livewire:modals.task-form />

    <!-- Componente Modal de Periodo -->
    <livewire:modals.period-form />

    <!-- Componente Modal de Detalles -->
    <livewire:modals.task-details />



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

        /* Ghost durante drag - semi-transparente */
        .sortable-ghost {
            opacity: 0.5 !important;
            background-color: #1a1a1a !important;
        }

        /* Transición suave para el reordenamiento */
        #tasks-tbody tr {
            transition: transform 0.3s ease, background-color 0.3s ease !important;
        }

        /* Elemento siendo arrastrado */
        .sortable-drag {
            opacity: 0.8 !important;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3) !important;
        }

        /* Highlight temporal cuando se suelta */
        @keyframes dropHighlight {
            0% { background-color: rgba(0, 127, 212, 0.2); }
            100% { background-color: transparent; }
        }

        /* Ocultar ghost dentro del sidebar para evitar deformación en altura */
        .period-drop-zone > tr,
        .period-drop-zone > .sortable-ghost,
        .period-drop-zone > .sortable-fallback {
            display: none !important;
            height: 0 !important;
            overflow: hidden !important;
        }

        /* Animación de pulso mientras arrastra sobre período */
        @keyframes readyToReceive {
            0%, 100% { 
                background-color: rgba(34, 197, 94, 0.1);
                border-left-color: #22c55e;
            }
            50% { 
                background-color: rgba(34, 197, 94, 0.25);
                border-left-color: #4ade80;
            }
        }

        /* Highlight VERDE pulsante cuando arrastra tarea sobre período */
        .period-drop-zone.drag-over-zone {
            animation: readyToReceive 0.8s ease-in-out infinite !important;
            border-left-width: 3px !important;
            transform: scale(1.05) !important;
            transform-origin: center !important;
            transition: transform 0.2s ease !important;
            z-index: 10 !important;
            position: relative !important;
        }

        /* Animación flash cuando recibe la tarea */
        @keyframes receiveTask {
            0% { background-color: rgba(34, 197, 94, 0.4); }
            50% { background-color: rgba(34, 197, 94, 0.2); }
            100% { background-color: transparent; }
        }

        .period-drop-zone.task-received {
            animation: receiveTask 0.6s ease-out forwards;
        }
    </style>

    <!-- SortableJS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <script>
        document.addEventListener('livewire:navigated', function() {
            initSortable();
        });
        
        document.addEventListener('DOMContentLoaded', function() {
            initSortable();
        });
        
        function initSortable() {
            const tbody = document.getElementById('tasks-tbody');
            const periodDropZones = document.querySelectorAll('.period-drop-zone');
            
            if (!tbody) return;
            
            // Sortable para la tabla principal
            new Sortable(tbody, {
                group: 'tasks',  // Mismo grupo para permitir drag entre elementos
                animation: 300,  // Animación más lenta y fluida
                handle: '.drag-handle',
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                chosenClass: 'sortable-chosen',
                swap: false,  // No intercambiar elementos
                swapThreshold: 0.65,
                invertSwap: false,
                direction: 'vertical',
                onEnd: function(evt) {
                    // Solo reordenar si se quedó en la misma tabla
                    if (evt.to === tbody) {
                        const orderedIds = Array.from(tbody.querySelectorAll('tr[data-task-id]'))
                            .map(row => parseInt(row.dataset.taskId));
                        
                        @this.call('updateTaskOrder', orderedIds);
                    }
                }
            });
            
            // Sortable para cada semana del sidebar
            periodDropZones.forEach(function(zone) {
                new Sortable(zone, {
                    group: {
                        name: 'tasks',
                        put: true,   // Puede recibir tareas
                        pull: false  // No se pueden sacar elementos de aquí
                    },
                    sort: false,  // No permitir reordenar dentro del sidebar
                    animation: 150,
                    onMove: function(evt) {
                        // Limpiar todas las clases primero
                        periodDropZones.forEach(z => z.classList.remove('drag-over-zone'));
                        // Agregar clase cuando está sobre este drop zone
                        if (evt.to) {
                            evt.to.classList.add('drag-over-zone');
                        }
                        return true;
                    },
                    onAdd: function(evt) {
                        // Remover clase de highlight de todos los períodos
                        periodDropZones.forEach(z => z.classList.remove('drag-over-zone'));
                        
                        // Agregar animación de "recibido"
                        evt.to.classList.add('task-received');
                        setTimeout(() => {
                            evt.to.classList.remove('task-received');
                        }, 700);
                        
                        const taskId = parseInt(evt.item.dataset.taskId);
                        const newPeriodId = parseInt(evt.to.dataset.periodId);
                        
                        // Mover la tarea al nuevo período
                        @this.call('moveTaskToPeriod', taskId, newPeriodId);
                        
                        // Remover el elemento del DOM del sidebar
                        evt.item.remove();
                    }
                });
            });
            
            // Limpiar highlight cuando termina cualquier drag
            document.addEventListener('mouseup', function() {
                periodDropZones.forEach(z => z.classList.remove('drag-over-zone'));
            });
            
            // Eventos nativos de drag para highlight visual
            periodDropZones.forEach(function(zone) {
                zone.addEventListener('dragenter', function(e) {
                    e.preventDefault();
                    // Limpiar todos primero
                    periodDropZones.forEach(z => z.classList.remove('drag-over-zone'));
                    // Agregar a este
                    this.classList.add('drag-over-zone');
                });
                
                zone.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    // Mantener la clase mientras está encima
                    if (!this.classList.contains('drag-over-zone')) {
                        periodDropZones.forEach(z => z.classList.remove('drag-over-zone'));
                        this.classList.add('drag-over-zone');
                    }
                });
                
                zone.addEventListener('dragleave', function(e) {
                    // Solo remover si realmente salió del elemento
                    const rect = this.getBoundingClientRect();
                    if (e.clientX < rect.left || e.clientX >= rect.right || 
                        e.clientY < rect.top || e.clientY >= rect.bottom) {
                        this.classList.remove('drag-over-zone');
                    }
                });
            });
        }
    </script>
</div>
