<div>
    @if($isOpen && $task)
        <div class="fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Overlay -->
                <div class="fixed inset-0 bg-black bg-opacity-70 transition-opacity backdrop-filter backdrop-blur-sm" aria-hidden="true" wire:click="close"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal Panel -->
                <div class="inline-block align-bottom bg-[#252526] rounded text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-[#333]" wire:keydown.window.escape="close">
                    <div class="bg-[#252526] px-4 pt-5 pb-4 sm:p-6">
                        <!-- Header -->
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg leading-6 font-medium text-[#d4d4d4] flex items-center gap-2" id="modal-title">
                                    <span class="text-[#007fd4]">📋</span> Detalles de Tarea
                                </h3>
                                <p class="text-sm text-[#7b7b7b] mt-1 font-mono">{{ $task->title }}</p>
                            </div>
                            @if(!$isEditing)
                                <button wire:click="toggleEdit" class="text-[#007fd4] hover:text-white text-sm flex items-center gap-1 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                    Editar
                                </button>
                            @endif
                        </div>

                        @if(session()->has('details-message'))
                            <div class="mb-4 p-2 bg-[#1e3a23] text-[#7ee787] text-sm rounded border border-[#2ea043]">
                                {{ session('details-message') }}
                            </div>
                        @endif

                        <div class="space-y-4">
                            <!-- Task Description -->
                            <div>
                                <label class="block text-xs font-mono text-[#7b7b7b] mb-2">Descripción de la tarea</label>
                                @if($isEditing)
                                    <textarea wire:model="description" rows="4" class="block w-full rounded bg-[#3c3c3c] border-[#333] text-[#d4d4d4] focus:border-[#007fd4] focus:ring-[#007fd4] sm:text-sm py-2 px-3 placeholder-[#666] resize-none" placeholder="Agregar descripción..."></textarea>
                                @else
                                    <div class="bg-[#1e1e1e] rounded p-3 text-sm text-[#d4d4d4] min-h-[80px] border border-[#333]">
                                        @if($description)
                                            {!! nl2br(e($description)) !!}
                                        @else
                                            <span class="text-[#666] italic">Sin descripción</span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <!-- Total Time Summary -->
                            @php
                                $effectiveEstimated = $task->effective_estimated_minutes;
                                $effectiveSpent     = $task->effective_spent_minutes;
                            @endphp
                            @if($effectiveEstimated > 0 || $effectiveSpent > 0)
                                @php
                                    $timeDiff       = $effectiveEstimated - $effectiveSpent;
                                    $isOver         = $timeDiff < 0;
                                    $absDiff        = abs($timeDiff);
                                    $progress       = $effectiveEstimated > 0
                                        ? min(100, round(($effectiveSpent / $effectiveEstimated) * 100))
                                        : 100;
                                    // Restante = tarea en curso | Ganado = tarea finalizada
                                    $taskCompleted = $task->status === \App\Enums\TaskStatus::Completed;
                                @endphp
                                <div class="text-xs p-2.5 bg-[#1a2332] rounded border border-[#264f78] space-y-2">
                                    <div class="flex gap-4 items-center flex-wrap">
                                        <span class="text-[#569cd6] font-medium">📊 Tiempo Total</span>
                                        <div class="flex items-center gap-1">
                                            <span class="text-[#5a5a5a]">⏱ Estimado:</span>
                                            <span class="text-[#ce9178] font-medium">{{ intdiv($effectiveEstimated, 60) }}h {{ $effectiveEstimated % 60 }}m</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <span class="text-[#5a5a5a]">✓ Invertido:</span>
                                            <span class="text-[#4ec9b0] font-medium">{{ intdiv($effectiveSpent, 60) }}h {{ $effectiveSpent % 60 }}m</span>
                                        </div>
                                        @if($effectiveEstimated > 0)
                                            @if($isOver)
                                                <div class="flex items-center gap-1">
                                                    <span class="text-[#5a5a5a]">🔴 Excedido:</span>
                                                    <span class="text-[#f85149] font-semibold">{{ intdiv($absDiff, 60) }}h {{ $absDiff % 60 }}m</span>
                                                </div>
                                            @elseif($taskCompleted)
                                                <div class="flex items-center gap-1">
                                                    <span class="text-[#5a5a5a]">🟢 Ganado:</span>
                                                    <span class="text-[#4ec9b0] font-semibold">{{ intdiv($absDiff, 60) }}h {{ $absDiff % 60 }}m</span>
                                                </div>
                                            @else
                                                <div class="flex items-center gap-1">
                                                    <span class="text-[#5a5a5a]">⏳ Restante:</span>
                                                    <span class="text-[#569cd6] font-semibold">{{ intdiv($absDiff, 60) }}h {{ $absDiff % 60 }}m</span>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                    {{-- Barra de progreso --}}
                                    @if($effectiveEstimated > 0)
                                        <div class="w-full bg-[#1e1e1e] rounded-full h-1.5">
                                            <div class="h-1.5 rounded-full transition-all {{ $isOver ? 'bg-[#f85149]' : 'bg-[#007fd4]' }}"
                                                 style="width: {{ $progress }}%"></div>
                                        </div>
                                        <div class="text-right text-[10px] text-[#5a5a5a] font-mono">{{ $progress }}% utilizado</div>
                                    @endif
                                </div>
                            @endif
                            <!-- General Task Time -->
                            @if($task->estimated_minutes > 0 || $task->timeLogs->count() > 0)
                                <div>
                                    <label class="block text-xs font-mono text-[#7b7b7b] mb-2">Tiempo General de Tarea</label>
                                    
                                    {{-- Individual Log Entries --}}
                                    @if($task->timeLogs->count() > 0)
                                        <div class="space-y-1 max-h-40 overflow-y-auto">
                                            @foreach($task->timeLogs->sortByDesc('created_at') as $log)
                                                <div class="flex items-center justify-between bg-[#1e1e1e] rounded px-3 py-1.5 border border-[#333] text-xs">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-[#007fd4]">🕐</span>
                                                        <div class="flex flex-col">
                                                            <span class="text-[#9d9d9d] font-mono">{{ \Carbon\Carbon::parse($log->log_date)->format('d/m/Y') }}</span>
                                                            <span class="text-[#5a5a5a] font-mono text-[10px]">cargado {{ \Carbon\Carbon::parse($log->created_at)->setTimezone(auth()->user()->timezone ?? config('app.timezone'))->format('H:i') }}</span>
                                                        </div>
                                                    </div>
                                                    <span class="text-[#4ec9b0] font-medium">
                                                        {{ intdiv($log->minutes_spent, 60) }}h {{ $log->minutes_spent % 60 }}m
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <!-- Subtasks Descriptions -->
                            @if($task->subtasks->count() > 0)
                                <div>
                                    <label class="block text-xs font-mono text-[#7b7b7b] mb-2">Subtareas</label>
                                    
                                    {{-- Time Summary --}}
                                    @php
                                        $totalEstimated = $task->subtasks_total_estimated;
                                        $totalSpent = $task->subtasks_total_spent;
                                    @endphp
                                    @if($totalEstimated > 0 || $totalSpent > 0)
                                        @php
                                            $subDiff    = $totalEstimated - $totalSpent;
                                            $subIsOver  = $subDiff < 0;
                                            $subAbsDiff = abs($subDiff);
                                        @endphp
                                        <div class="flex gap-4 mb-3 text-xs p-2 bg-[#1e1e1e] rounded border border-[#333] flex-wrap">
                                            <div class="flex items-center gap-1">
                                                <span class="text-[#5a5a5a]">⏱ Estimado:</span>
                                                <span class="text-[#ce9178]">{{ intdiv($totalEstimated, 60) }}h {{ $totalEstimated % 60 }}m</span>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <span class="text-[#5a5a5a]">✓ Invertido:</span>
                                                <span class="text-[#4ec9b0]">{{ intdiv($totalSpent, 60) }}h {{ $totalSpent % 60 }}m</span>
                                            </div>
                                            @if($totalEstimated > 0)
                                                @if($subIsOver)
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-[#5a5a5a]">🔴 Excedido:</span>
                                                        <span class="text-[#f85149] font-semibold">{{ intdiv($subAbsDiff, 60) }}h {{ $subAbsDiff % 60 }}m</span>
                                                    </div>
                                                @elseif($taskCompleted)
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-[#5a5a5a]">🟢 Ganado:</span>
                                                        <span class="text-[#4ec9b0] font-semibold">{{ intdiv($subAbsDiff, 60) }}h {{ $subAbsDiff % 60 }}m</span>
                                                    </div>
                                                @else
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-[#5a5a5a]">⏳ Restante:</span>
                                                        <span class="text-[#569cd6] font-semibold">{{ intdiv($subAbsDiff, 60) }}h {{ $subAbsDiff % 60 }}m</span>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    @endif
                                    
                                    <div class="space-y-3">
                                        @foreach($task->subtasks as $subtask)
                                            <div class="bg-[#1e1e1e] rounded p-3 border border-[#333]">
                                                <div class="flex items-center justify-between mb-2">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-xs {{ $subtask->is_completed ? 'text-[#4ec9b0]' : 'text-[#ce9178]' }}">
                                                            {{ $subtask->is_completed ? '✓' : '○' }}
                                                        </span>
                                                        <span class="text-sm text-[#d4d4d4] font-medium">{{ $subtask->title }}</span>
                                                    </div>
                                                    {{-- Subtask Time Badge --}}
                                                    @if($subtask->estimated_minutes > 0 || $subtask->spent_minutes > 0)
                                                        <div class="flex flex-col items-end gap-0.5 text-xs">
                                                            <div class="flex gap-2">
                                                                @if($subtask->estimated_minutes > 0)
                                                                    <span class="text-[#ce9178]">{{ intdiv($subtask->estimated_minutes, 60) }}h {{ $subtask->estimated_minutes % 60 }}m</span>
                                                                @endif
                                                                @if($subtask->spent_minutes > 0)
                                                                    <span class="text-[#4ec9b0]">({{ intdiv($subtask->spent_minutes, 60) }}h {{ $subtask->spent_minutes % 60 }}m)</span>
                                                                @endif
                                                            </div>
                                                            @if($subtask->spent_minutes > 0)
                                                                <span class="text-[#5a5a5a] font-mono text-[10px]">cargado {{ \Carbon\Carbon::parse($subtask->updated_at)->setTimezone(auth()->user()->timezone ?? config('app.timezone'))->format('H:i d/m/Y') }}</span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                                @if($isEditing)
                                                    <input type="text" wire:model="subtaskDescriptions.{{ $subtask->id }}" class="block w-full rounded bg-[#3c3c3c] border-[#333] text-[#9d9d9d] focus:border-[#007fd4] focus:ring-[#007fd4] text-xs py-1.5 px-3 placeholder-[#555]" placeholder="Detalles de la subtarea...">
                                                @else
                                                    @if($subtaskDescriptions[$subtask->id] ?? false)
                                                        <p class="text-xs text-[#9d9d9d] pl-5">{{ $subtaskDescriptions[$subtask->id] }}</p>
                                                    @endif
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="bg-[#2d2d2d] px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-[#333]">
                        @if($isEditing)
                            <button wire:click="save" class="w-full inline-flex justify-center rounded border border-transparent shadow-sm px-4 py-2 bg-[#007fd4] text-base font-medium text-white hover:bg-[#006cb5] focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                Guardar
                            </button>
                            <button wire:click="toggleEdit" class="mt-3 w-full inline-flex justify-center rounded border border-[#3e3e42] shadow-sm px-4 py-2 bg-[#3c3c3c] text-base font-medium text-[#d4d4d4] hover:bg-[#4a4a4d] focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancelar
                            </button>
                        @else
                            <button wire:click="close" class="w-full inline-flex justify-center rounded border border-[#3e3e42] shadow-sm px-4 py-2 bg-[#3c3c3c] text-base font-medium text-[#d4d4d4] hover:bg-[#4a4a4d] focus:outline-none sm:w-auto sm:text-sm">
                                Cerrar
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
