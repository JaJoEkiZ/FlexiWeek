<x-planner-layout
    :selectedPeriodId="$selectedPeriodId"
    :currentPeriod="isset($currentPeriod) ? $currentPeriod : null"
    activeTab="tasks"
>
                {{-- ===== VISTA MÓVIL: TARJETAS (< md) ===== --}}
                <div id="mobile-tasks-container" class="lg:hidden space-y-3 min-h-[50px]">
                    @forelse($tasks as $task)
                        <div 
                            x-data="taskRow({{ json_encode([
                                'id' => $task->id,
                                'progress' => $task->progress,
                                'status' => $task->status->value,
                                'status_color' => $task->status->color(),
                                'status_label' => $task->status->label(),
                                'spent_minutes' => $task->effective_spent_minutes,
                                'estimated_minutes' => $task->effective_estimated_minutes,
                                'completion_method' => $task->completion_method,
                                'subtasks' => $task->subtasks->toArray()
                            ]) }})"
                            data-task-id="{{ $task->id }}"
                            wire:key="task-mobile-{{ $task->id }}-{{ $task->updated_at->timestamp }}"
                            @dblclick="openTaskForm({{ $task->id }})"
                            @contextmenu.prevent="ctxMenu = { show: true, x: $event.clientX, y: $event.clientY, taskId: {{ $task->id }} }"
                            class="bg-[#2a2d2e] rounded border border-[#30363d] p-3 mb-2 transition-all shadow-sm relative overflow-hidden"
                            :class="statusValue === 'cancelled' ? 'opacity-60' : ''"
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
                                        @click.stop="cycleStatus()"
                                        :disabled="progress >= 100 || statusValue === 'cancelled'"
                                        class="inline-flex items-center px-2 py-1 rounded text-xs font-medium border transition-all select-none flex-shrink-0"
                                        :class="{
                                           'opacity-75 cursor-not-allowed': progress >= 100 || statusValue === 'cancelled',
                                           'cursor-pointer': progress < 100 && statusValue !== 'cancelled',
                                           'bg-[#1e3a23] text-[#7ee787] border-[#2ea043]': statusColor === 'green',
                                           'bg-[#152e42] text-[#79c0ff] border-[#1f6feb]': statusColor === 'blue',
                                           'bg-[#362808] text-[#d29922] border-[#9e6a03]': statusColor === 'yellow',
                                           'bg-[#26282e] text-[#8b949e] border-[#30363d]': statusColor === 'gray',
                                           'bg-[#3b1219] text-[#f85149] border-[#da3633]': statusColor === 'red'
                                        }">
                                        <span class="mr-1 opacity-70 flex items-center h-full">
                                            <template x-if="statusColor === 'green'"><span>✓</span></template>
                                            <template x-if="statusColor === 'blue'"><span>▶</span></template>
                                            <template x-if="statusColor === 'yellow'"><span>⏸</span></template>
                                            <template x-if="statusColor === 'red'"><span>✕</span></template>
                                            <template x-if="statusColor === 'gray'"><span>•</span></template>
                                        </span>
                                        <span x-text="statusLabel"></span>
                                    </button>
                                    <span class="font-medium text-[#d4d4d4] text-sm truncate block transition-colors" :class="statusValue === 'cancelled' ? 'line-through' : ''">
                                        {{ $task->title }}@if($task->is_persistent) <span class="text-[#569cd6]" title="Tarea Persistente">🔁</span>@endif<span class="text-xs ml-1" title="{{ $task->completion_method === 'subtasks' ? 'Completado por subtareas' : 'Completado por tiempo' }}">{{ $task->completion_method === 'subtasks' ? '📋' : '⏱️' }}</span>
                                    </span>
                                </div>
                                
                                <div class="flex items-center gap-1 flex-shrink-0">
                                    <button 
                                        wire:click="$dispatch('openTaskDetails', { taskId: {{ $task->id }} })"
                                        @click.stop
                                        class="text-[#007fd4] hover:text-white text-xs font-medium transition-colors px-2 py-1 rounded hover:bg-[#333] border border-[#333]">
                                        Detalles
                                    </button>
                                </div>
                            </div>

                            {{-- Fila 2: Selector Subtareas --}}
                            <template x-if="subtasks.length > 0">
                                <div @click.stop class="mb-3 mt-2 flex gap-1 items-center">
                                    <select x-model="activeSubtaskId"
                                            :disabled="statusValue === 'cancelled'"
                                            class="w-full px-2 py-1.5 text-xs bg-[#3c3c3c] border border-[#333] rounded text-[#d4d4d4] focus:border-[#007fd4] focus:ring-[#007fd4] focus:outline-none"
                                            :class="statusValue === 'cancelled' ? 'opacity-50 cursor-not-allowed' : ''">
                                        <option value="">-- Tarea principal --</option>
                                        <template x-for="st in subtasks" :key="st.id">
                                            <option :value="st.id" :style="st.is_completed ? 'color:#555; text-decoration:line-through;' : ''" x-text="(st.is_completed ? '✓ ' : '') + st.title"></option>
                                        </template>
                                    </select>
                                    
                                    {{-- Botón para completar/desmarcar subtarea --}}
                                    <template x-if="activeSubtaskId !== ''">
                                        <button type="button" 
                                                @click="toggleActiveSubtask()"
                                                class="px-2 py-1.5 rounded border transition-colors flex-shrink-0 relative overflow-hidden group"
                                                :class="getActiveSubtask()?.is_completed ? 'bg-[#152e42] border-[#007fd4] text-[#7ee787]' : 'bg-[#3c3c3c] border-[#333] text-[#d4d4d4] hover:bg-[#444]'"
                                                :title="getActiveSubtask()?.is_completed ? 'Desmarcar subtarea' : 'Completar subtarea'">
                                            <template x-if="!getActiveSubtask()?.is_completed">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                            </template>
                                            <template x-if="getActiveSubtask()?.is_completed">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                            </template>
                                        </button>
                                    </template>
                                </div>
                            </template>

                            {{-- Fila 3: Métricas --}}
                            <div class="grid grid-cols-2 gap-3 text-xs font-mono mb-3">
                                <div class="bg-[#1e1e1e] rounded p-2 text-center border border-[#333]/50">
                                    <div class="text-[#7b7b7b] mb-1">Realizado</div>
                                    <template x-if="completionMethod === 'subtasks'">
                                        <div>
                                            <div class="text-[#9cdcfe]" x-text="subtasks.filter(s => s.is_completed).length + ' / ' + subtasks.length"></div>
                                            <div class="text-[#4ec9b0] text-[10px]" x-text="formatTime(spentMinutes)"></div>
                                        </div>
                                    </template>
                                    <template x-if="completionMethod !== 'subtasks'">
                                        <div class="text-[#d4d4d4]" x-text="formatTime(spentMinutes)"></div>
                                    </template>
                                </div>
                                <div class="bg-[#1e1e1e] rounded p-2 text-center border border-[#333]/50">
                                    <div class="text-[#7b7b7b] mb-1">Restante</div>
                                    <template x-if="completionMethod === 'subtasks'">
                                        <div>
                                            <div :class="subtasks.filter(s => !s.is_completed).length === 0 ? 'text-[#4ec9b0]' : 'text-[#ce9178]'"
                                                 x-text="subtasks.filter(s => !s.is_completed).length + ' pend.'"></div>
                                            <div class="text-[10px]" 
                                                 :class="Math.max(0, estimatedMinutes - spentMinutes) === 0 ? 'text-[#4ec9b0]' : 'text-[#ce9178]'"
                                                 x-text="formatTime(Math.max(0, estimatedMinutes - spentMinutes))"></div>
                                        </div>
                                    </template>
                                    <template x-if="completionMethod !== 'subtasks'">
                                        <div :class="(estimatedMinutes - spentMinutes) < 0 ? 'text-[#f14c4c]' : ((estimatedMinutes - spentMinutes) === 0 ? 'text-[#4ec9b0]' : 'text-[#ce9178]')"
                                             x-text="((estimatedMinutes - spentMinutes) < 0 ? '-' : '') + formatTime(Math.abs(estimatedMinutes - spentMinutes))"></div>
                                    </template>
                                </div>
                            </div>

                            {{-- Fila 4: Progreso y Tiempo --}}
                            <div>
                                <div class="flex justify-between text-[11px] mb-1 font-mono">
                                    <span class="text-[#9cdcfe]">
                                        <span x-text="formatTime(spentMinutes)"></span>
                                        <span class="text-[#6a9955]" x-show="estimatedMinutes > 0" x-text="' / ' + formatTime(estimatedMinutes)"></span>
                                    </span>
                                    <span :class="progress >= 100 ? 'text-[#4ec9b0] font-bold' : 'text-[#7b7b7b]'" x-text="progress + '%'"></span>
                                </div>
                                <div class="w-full bg-[#3c3c3c] rounded-full h-1.5 overflow-hidden mb-3">
                                    <div class="h-1.5 rounded-full transition-all duration-300" 
                                         :class="progress >= 100 ? 'bg-[#4ec9b0]' : 'bg-[#007fd4]'"
                                         :style="`width: ${progress}%`"></div>
                                </div>
                                <div class="flex flex-col gap-2" @click.stop>
                                    <div class="flex gap-2 items-center">
                                        <template x-if="timeMode === 'min'">
                                            <input 
                                                type="number" 
                                                placeholder="min" 
                                                x-model="inputMins"
                                                @keydown.enter="addTime()"
                                                :disabled="statusValue === 'cancelled'"
                                                class="w-full px-3 py-2 text-sm bg-[#3c3c3c] border border-[#333] rounded text-[#d4d4d4] focus:border-[#007fd4] focus:ring-1 focus:ring-[#007fd4] focus:outline-none placeholder-[#666]"
                                                :class="statusValue === 'cancelled' ? 'opacity-50 cursor-not-allowed' : ''"
                                            >
                                        </template>
                                        <template x-if="timeMode === 'hm'">
                                            <div class="flex gap-1 items-center w-full">
                                                <input 
                                                    type="number" 
                                                    placeholder="h" 
                                                    min="0"
                                                    x-model="inputHours"
                                                    @keydown.enter="addTime()"
                                                    :disabled="statusValue === 'cancelled'"
                                                    class="w-1/2 px-2 py-2 text-sm bg-[#3c3c3c] border border-[#333] rounded text-[#d4d4d4] focus:border-[#007fd4] focus:ring-1 focus:ring-[#007fd4] focus:outline-none placeholder-[#666]"
                                                    :class="statusValue === 'cancelled' ? 'opacity-50 cursor-not-allowed' : ''"
                                                >
                                                <span class="text-[#7b7b7b] text-xs">:</span>
                                                <input 
                                                    type="number" 
                                                    placeholder="m" 
                                                    min="0" max="59"
                                                    x-model="inputMins"
                                                    @keydown.enter="addTime()"
                                                    :disabled="statusValue === 'cancelled'"
                                                    class="w-1/2 px-2 py-2 text-sm bg-[#3c3c3c] border border-[#333] rounded text-[#d4d4d4] focus:border-[#007fd4] focus:ring-1 focus:ring-[#007fd4] focus:outline-none placeholder-[#666]"
                                                    :class="statusValue === 'cancelled' ? 'opacity-50 cursor-not-allowed' : ''"
                                                >
                                            </div>
                                        </template>
                                        <button 
                                            @click.stop="addTime()"
                                            :disabled="statusValue === 'cancelled'"
                                            class="px-4 py-2 bg-[#333] text-[#d4d4d4] rounded border border-[#333] text-sm font-bold transition-colors flex-shrink-0"
                                            :class="statusValue === 'cancelled' ? 'opacity-50 cursor-not-allowed hover:bg-[#333]' : 'hover:bg-[#444] hover:text-[#fff]'"
                                            title="Sumar tiempo">
                                            +
                                        </button>
                                    </div>
                                    <div class="flex justify-between items-center w-full">
                                        <button 
                                            @click="timeMode = timeMode === 'min' ? 'hm' : 'min'"
                                            class="px-2 py-0.5 text-[10px] font-medium text-[#ffffff] bg-[#007fd4] border border-[#30363d] rounded hover:bg-[#152e42] hover:border-[#1f6feb] transition-all"
                                            type="button">
                                            <span x-text="timeMode === 'min' ? '⏱ Cargar hora:min' : '⏱ Cargar min'"></span>
                                        </button>
                                        <button wire:click="openTaskForm({{ $task->id }})" @click.stop class="text-[#7b7b7b] hover:text-[#d4d4d4] transition-colors p-1.5 rounded hover:bg-[#333] border border-[#333] bg-[#222]">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                    </div>
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
                                <th class="p-4 border-b border-[#333] w-56">Control de Tiempo</th>
                                <th class="p-4 border-b border-[#333] w-20">Editar</th>
                            </tr>
                        </thead>
                        <tbody id="tasks-tbody" class="divide-y divide-[#333]">
                            @forelse($tasks as $task)
                                <tr 
                                    x-data="taskRow({{ json_encode([
                                        'id' => $task->id,
                                        'progress' => $task->progress,
                                        'status' => $task->status->value,
                                        'status_color' => $task->status->color(),
                                        'status_label' => $task->status->label(),
                                        'spent_minutes' => $task->effective_spent_minutes,
                                        'estimated_minutes' => $task->effective_estimated_minutes,
                                        'completion_method' => $task->completion_method,
                                        'subtasks' => $task->subtasks->toArray()
                                    ]) }})"
                                    data-task-id="{{ $task->id }}"
                                    wire:key="task-desktop-{{ $task->id }}-{{ $task->updated_at->timestamp }}" 
                                    @dblclick="$wire.openTaskForm({{ $task->id }})"
                                    @contextmenu.prevent="ctxMenu = { show: true, x: $event.clientX, y: $event.clientY, taskId: {{ $task->id }} }"
                                    class="hover:bg-[#2a2d2e] transition-colors group cursor-pointer"
                                    :class="statusValue === 'cancelled' ? 'opacity-60' : ''"
                                >
                                    <td class="p-4">
                                        <div class="flex items-center gap-2">
                                            <div class="drag-handle cursor-grab active:cursor-grabbing text-[#7b7b7b] hover:text-[#d4d4d4] transition-colors" @click.stop>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M7 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 2zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 14zm6-8a2 2 0 1 0-.001-4.001A2 2 0 0 0 13 6zm0 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 14z"/>
                                                </svg>
                                            </div>
                                            <button 
                                                @click.stop="cycleStatus()"
                                                :disabled="progress >= 100 || statusValue === 'cancelled'"
                                                :class="{
                                                   'opacity-75 cursor-not-allowed': progress >= 100 || statusValue === 'cancelled',
                                                   'cursor-pointer': progress < 100 && statusValue !== 'cancelled',
                                                   'bg-[#1e3a23] text-[#7ee787] border-[#2ea043]': statusColor === 'green',
                                                   'bg-[#152e42] text-[#79c0ff] border-[#1f6feb]': statusColor === 'blue',
                                                   'bg-[#362808] text-[#d29922] border-[#9e6a03]': statusColor === 'yellow',
                                                   'bg-[#26282e] text-[#8b949e] border-[#30363d]': statusColor === 'gray',
                                                   'bg-[#3b1219] text-[#f85149] border-[#da3633]': statusColor === 'red'
                                                }"
                                                class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium border transition-all select-none">
                                                <span class="mr-1.5 opacity-70 flex items-center h-full">
                                                    <template x-if="statusColor === 'green'"><span>✓</span></template>
                                                    <template x-if="statusColor === 'blue'"><span>▶</span></template>
                                                    <template x-if="statusColor === 'yellow'"><span>⏸</span></template>
                                                    <template x-if="statusColor === 'red'"><span>✕</span></template>
                                                    <template x-if="statusColor === 'gray'"><span>•</span></template>
                                                </span>
                                                <span x-text="statusLabel"></span>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex flex-col gap-2 items-start">
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium text-[#d4d4d4] group-hover:text-white transition-colors" :class="statusValue === 'cancelled' ? 'line-through' : ''">
                                                    {{ $task->title }}@if($task->is_persistent) <span class="text-[#569cd6]" title="Tarea Persistente">🔁</span>@endif<span class="text-xs ml-1" title="{{ $task->completion_method === 'subtasks' ? 'Completado por subtareas' : 'Completado por tiempo' }}">{{ $task->completion_method === 'subtasks' ? '📋' : '⏱️' }}</span>
                                                </span>
                                                <button 
                                                    wire:click="$dispatch('openTaskDetails', { taskId: {{ $task->id }} })"
                                                    @click.stop
                                                    class="text-[#007fd4] hover:text-white text-xs font-medium transition-colors px-1.5 py-0.5 rounded hover:bg-[#333] border border-[#333]">
                                                    Detalles
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4 text-left" @click.stop>
                                        <template x-if="subtasks.length > 0">
                                            <div class="flex gap-1.5 items-center w-full max-w-[160px]">
                                                <select x-model="activeSubtaskId" 
                                                        :disabled="statusValue === 'cancelled'"
                                                        :class="statusValue === 'cancelled' ? 'opacity-50 cursor-not-allowed' : ''"
                                                        class="w-full px-2 py-1.5 text-xs bg-[#3c3c3c] border border-[#333] rounded text-[#d4d4d4] focus:border-[#007fd4] focus:ring-[#007fd4] focus:outline-none truncate">
                                                    <option value="">-- Tarea principal --</option>
                                                    <template x-for="st in subtasks" :key="st.id">
                                                        <option :value="st.id" :style="st.is_completed ? 'color:#555; text-decoration:line-through;' : ''" x-text="(st.is_completed ? '✓ ' : '') + st.title"></option>
                                                    </template>
                                                </select>
                                                
                                                {{-- Botón interactivo para subtarea activa --}}
                                                <template x-if="activeSubtaskId !== ''">
                                                    <button type="button" 
                                                            @click="toggleActiveSubtask()"
                                                            class="px-2 py-1.5 rounded border transition-colors flex-shrink-0 relative overflow-hidden group"
                                                            :class="getActiveSubtask()?.is_completed ? 'bg-[#152e42] border-[#007fd4] text-[#7ee787]' : 'bg-[#3c3c3c] border-[#333] text-[#d4d4d4] hover:bg-[#444]'"
                                                            :title="getActiveSubtask()?.is_completed ? 'Desmarcar subtarea' : 'Completar subtarea'">
                                                        <template x-if="!getActiveSubtask()?.is_completed">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                        </template>
                                                        <template x-if="getActiveSubtask()?.is_completed">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                                        </template>
                                                    </button>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="subtasks.length === 0">
                                            <span class="text-[#5a5a5a] text-xs">-</span>
                                        </template>
                                    </td>
                                    <td class="p-4 text-center font-mono text-sm text-[#d4d4d4]" x-text="formatTime(spentMinutes)">
                                    </td>
                                    <td class="p-4 text-center font-mono text-sm">
                                        <template x-if="estimatedMinutes > 0">
                                            <span :class="estimatedMinutes - spentMinutes < 0 ? 'text-[#f14c4c]' : (estimatedMinutes - spentMinutes === 0 ? 'text-[#4ec9b0]' : 'text-[#ce9178]')"
                                                  x-text="(estimatedMinutes - spentMinutes < 0 ? '-' : '') + formatTime(Math.abs(estimatedMinutes - spentMinutes))">
                                            </span>
                                        </template>
                                        <template x-if="estimatedMinutes <= 0">
                                            <span class="text-[#5a5a5a]">-</span>
                                        </template>
                                    </td>
                                    <td class="p-4 align-top">
                                        <div class="mb-2.5">
                                            <div class="flex justify-between text-[11px] mb-1 font-mono">
                                                <span class="text-[#9cdcfe]">
                                                    <span x-text="formatTime(spentMinutes)"></span>
                                                    <span class="text-[#6a9955]" x-show="estimatedMinutes > 0" x-text="' / ' + formatTime(estimatedMinutes)"></span>
                                                </span>
                                                <span :class="progress >= 100 ? 'text-[#4ec9b0] font-bold' : 'text-[#7b7b7b]'" x-text="progress + '%'"></span>
                                            </div>
                                            <div class="w-full bg-[#3c3c3c] rounded-full h-1.5 overflow-hidden">
                                                <div class="h-1.5 rounded-full transition-all duration-300" 
                                                     :class="progress >= 100 ? 'bg-[#4ec9b0]' : 'bg-[#007fd4]'"
                                                     :style="`width: ${progress}%`"></div>
                                            </div>
                                        </div>
                                        <div class="flex flex-col gap-1.5">
                                            <div class="flex gap-2 w-full">
                                                <template x-if="timeMode === 'min'">
                                                    <input type="number" @click.stop placeholder="min" x-model="inputMins" @keydown.enter="addTime()"
                                                           :disabled="statusValue === 'cancelled'"
                                                           :class="statusValue === 'cancelled' ? 'opacity-50 cursor-not-allowed' : ''"
                                                           class="flex-1 min-w-[70px] px-2 py-1 text-xs bg-[#3c3c3c] border border-[#333] rounded text-[#d4d4d4] focus:border-[#007fd4] focus:ring-1 focus:ring-[#007fd4] focus:outline-none placeholder-[#666]">
                                                </template>
                                                <template x-if="timeMode === 'hm'">
                                                    <div class="flex flex-1 min-w-[70px] gap-1 items-center" @click.stop>
                                                        <input type="number" placeholder="h" min="0" x-model="inputHours" @keydown.enter="addTime()" :disabled="statusValue === 'cancelled'" :class="statusValue === 'cancelled' ? 'opacity-50 cursor-not-allowed' : ''" class="w-full px-1.5 py-1 text-[11px] bg-[#3c3c3c] border border-[#333] rounded text-[#d4d4d4] focus:border-[#007fd4] focus:ring-1 focus:ring-[#007fd4] focus:outline-none placeholder-[#666]">
                                                        <span class="text-[#7b7b7b] text-[10px]">:</span>
                                                        <input type="number" placeholder="m" min="0" max="59" x-model="inputMins" @keydown.enter="addTime()" :disabled="statusValue === 'cancelled'" :class="statusValue === 'cancelled' ? 'opacity-50 cursor-not-allowed' : ''" class="w-full px-1.5 py-1 text-[11px] bg-[#3c3c3c] border border-[#333] rounded text-[#d4d4d4] focus:border-[#007fd4] focus:ring-1 focus:ring-[#007fd4] focus:outline-none placeholder-[#666]">
                                                    </div>
                                                </template>
                                                <button @click.stop="addTime()" :disabled="statusValue === 'cancelled'" :class="statusValue === 'cancelled' ? 'opacity-50 cursor-not-allowed hover:bg-[#333]' : 'hover:bg-[#444] hover:text-[#fff]'" class="px-2.5 py-1 bg-[#333] text-[#d4d4d4] rounded border border-[#333] text-xs font-bold transition-colors select-none">+</button>
                                            </div>
                                            <button @click.stop="timeMode = timeMode === 'min' ? 'hm' : 'min'" class="w-full px-1.5 py-0.5 text-[9px] font-medium text-[#ffffff] bg-transparent border border-[#30363d] rounded hover:bg-[#152e42] hover:border-[#1f6feb] transition-all text-center leading-tight whitespace-nowrap" type="button">
                                                <span x-text="timeMode === 'min' ? '⏱ Cargar hora:min' : '⏱ Cargar min'"></span>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="p-4 text-center">
                                        <button wire:click="openTaskForm({{ $task->id }})" @click.stop class="text-[#7b7b7b] hover:text-white transition-colors p-1.5 rounded hover:bg-[#333] border border-transparent hover:border-[#333]">
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

    @script
    <script>
        Alpine.data('taskRow', (task) => ({
            taskId: task.id,
            progress: task.progress,
            statusValue: task.status,
            statusColor: task.status_color,
            statusLabel: task.status_label,
            spentMinutes: task.spent_minutes,
            estimatedMinutes: task.estimated_minutes,
            completionMethod: task.completion_method,
            subtasks: task.subtasks || [],
            activeSubtaskId: '',
            
            get activeSubtaskTitle() {
                if (!this.activeSubtaskId) return '-- Tarea principal --';
                let st = this.subtasks.find(s => s.id == this.activeSubtaskId);
                return st ? st.title : '-- Tarea principal --';
            },
            
            timeMode: 'min',
            inputHours: '',
            inputMins: '',

            init() {
                // Sincronizar con Livewire si hay un re-render que cambia datos subyacentes
                this.$watch('progress', val => { if(val >= 100) this.markCompleted(); });
            },

            getActiveSubtask() {
                if (!this.activeSubtaskId) return null;
                return this.subtasks.find(s => s.id == this.activeSubtaskId);
            },

            toggleActiveSubtask() {
                if (!this.activeSubtaskId) return;
                let index = this.subtasks.findIndex(s => s.id == this.activeSubtaskId);
                if (index !== -1) {
                    this.toggleSubtask(index);
                }
            },

            markCompleted() {
                this.statusValue = 'completed';
                this.statusColor = 'green';
                this.statusLabel = 'Finalizada';
            },

            markInProgress() {
                this.statusValue = 'in_progress';
                this.statusColor = 'blue';
                this.statusLabel = 'En curso';
            },

            cycleStatus() {
                if (this.progress >= 100 || this.statusValue === 'cancelled') return;
                
                // Rotación optimista simple
                if (this.statusValue === 'pending') {
                    this.markInProgress();
                } else if (this.statusValue === 'in_progress') {
                    this.statusValue = 'paused';
                    this.statusColor = 'yellow';
                    this.statusLabel = 'Pausada';
                } else {
                    this.statusValue = 'pending';
                    this.statusColor = 'gray';
                    this.statusLabel = 'Pendiente';
                }

                this.$wire.call('cycleStatus', this.taskId);
            },

            formatTime(minutes) {
                let h = Math.floor(minutes / 60);
                let m = minutes % 60;
                return `${h}h ${m}m`;
            },

            addTime() {
                if (this.statusValue === 'cancelled') return;
                
                let h = parseInt(this.inputHours || 0);
                let m = parseInt(this.inputMins || 0);
                let total = (h * 60) + m;
                
                if (total > 0) {
                    this.spentMinutes += total;
                    if (this.activeSubtaskId) {
                       let st = this.subtasks.find(s => s.id == this.activeSubtaskId);
                       if(st) st.spent_minutes += total;
                    }
                    if(this.estimatedMinutes > 0) {
                       this.progress = Math.min(100, Math.round((this.spentMinutes / this.estimatedMinutes) * 100));
                    } else {
                       this.progress = 100;
                    }

                    if (this.progress >= 100) {
                        this.markCompleted();
                    } else if (this.statusValue === 'pending') {
                        this.markInProgress();
                    }
                    
                    this.$wire.call('addTime', this.taskId, h, m, this.activeSubtaskId);
                    
                    this.inputHours = '';
                    this.inputMins = '';
                }
            },

            toggleSubtask(subtaskIndex) {
                let st = this.subtasks[subtaskIndex];
                if (st) {
                    st.is_completed = !st.is_completed;
                    
                    if (this.completionMethod === 'subtasks') {
                        let totalCount = this.subtasks.length;
                        let completedCount = this.subtasks.filter(s => s.is_completed).length;
                        this.progress = totalCount > 0 ? Math.round((completedCount / totalCount) * 100) : 0;
                        
                        // Optimistic Status
                        if (this.progress >= 100) {
                            this.markCompleted();
                        } else if (this.statusValue === 'completed' && this.progress < 100) {
                            this.markInProgress();
                        }
                    }

                    this.$wire.call('toggleSubtask', this.taskId, st.id);
                }
            }
        }));

        initSortable();

        Livewire.hook('morph.updated', ({ component, el }) => {
            initSortable();
        });
        
        function initSortable() {
            // 1. Sortable para ESCRITORIO (Tabla)
            const tbody = document.getElementById('tasks-tbody');
            if (tbody) {
                if (Sortable.get(tbody)) Sortable.get(tbody).destroy();
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
                if (Sortable.get(mobileContainer)) Sortable.get(mobileContainer).destroy();
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
                if (Sortable.get(zone)) Sortable.get(zone).destroy();
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
    @endscript
</x-planner-layout>
