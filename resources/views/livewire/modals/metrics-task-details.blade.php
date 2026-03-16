<div>
    @if($isOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Overlay -->
                <div class="fixed inset-0 bg-black bg-opacity-70 transition-opacity backdrop-filter backdrop-blur-sm" aria-hidden="true" wire:click="close"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal Panel -->
                <div class="inline-block align-bottom bg-[#252526] rounded text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-[#333]" wire:keydown.window.escape="close">
                    <div class="bg-[#252526] px-4 pt-5 pb-4 sm:p-6">
                        <!-- Header -->
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg leading-6 font-medium text-[#d4d4d4] flex items-center gap-2" id="modal-title">
                                    @if($type === 'overtime')
                                        <span class="text-[#f85149]">🔴</span> Tareas con Tiempo Excedido
                                    @elseif($type === 'gained')
                                        <span class="text-[#4ec9b0]">🟢</span> Tareas con Tiempo Ganado
                                    @endif
                                </h3>
                                <p class="text-sm text-[#7b7b7b] mt-1 font-mono">
                                    Total {{ $type === 'overtime' ? 'excedido' : 'ganado' }}: 
                                    <span class="font-medium {{ $type === 'overtime' ? 'text-[#f85149]' : 'text-[#4ec9b0]' }}">
                                        {{ intdiv($totalDiff, 60) }}h {{ $totalDiff % 60 }}m
                                    </span>
                                </p>
                            </div>
                            <button wire:click="close" class="text-[#7b7b7b] hover:text-white transition-colors p-1 rounded hover:bg-[#333]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Tasks List -->
                        <div class="space-y-3 max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                            @forelse($tasks as $task)
                                @php
                                    $est = $task->effective_estimated_minutes;
                                    $spent = $task->effective_spent_minutes;
                                    $diff = abs($est - $spent);
                                @endphp
                                <div class="bg-[#1e1e1e] border border-[#333] p-3 rounded-lg hover:border-[#444] transition-colors relative group">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex-1 pr-4">
                                            <h4 class="text-sm font-medium text-[#d4d4d4] group-hover:text-white transition-colors truncate">
                                                {{ $task->title }}
                                                @if($task->is_persistent) <span class="text-[#569cd6] text-xs ml-1" title="Tarea Persistente">🔁</span>@endif
                                                <span class="text-[10px] ml-1 opacity-70" title="{{ $task->completion_method === 'subtasks' ? 'Completado por subtareas' : 'Completado por tiempo' }}">{{ $task->completion_method === 'subtasks' ? '📋' : '⏱️' }}</span>
                                            </h4>
                                            <p class="text-xs text-[#7b7b7b] mt-0.5 font-mono">
                                                Semana: {{ \Carbon\Carbon::parse($task->period->start_date)->format('d/m') }} al {{ \Carbon\Carbon::parse($task->period->end_date)->format('d/m/Y') }}
                                            </p>
                                        </div>
                                        <div class="text-right flex-shrink-0">
                                            <div class="text-xs font-mono font-medium {{ $type === 'overtime' ? 'text-[#f85149]' : 'text-[#4ec9b0]' }}">
                                                {{ $type === 'overtime' ? '+' : '-' }} {{ intdiv($diff, 60) }}h {{ $diff % 60 }}m
                                            </div>
                                            <div class="text-[10px] text-[#7b7b7b] uppercase mt-0.5">Diferencia</div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-4 text-xs font-mono bg-[#252526] rounded p-2 border border-[#333]">
                                        <div class="flex items-center gap-1">
                                            <span class="text-[#5a5a5a]">Estimado:</span>
                                            <span class="text-[#ce9178]">{{ intdiv($est, 60) }}h {{ $est % 60 }}m</span>
                                        </div>
                                        <div class="w-px h-3 bg-[#333]"></div>
                                        <div class="flex items-center gap-1">
                                            <span class="text-[#5a5a5a]">Invertido:</span>
                                            <span class="{{ $spent > $est ? 'text-[#f85149]' : 'text-[#4ec9b0]' }}">{{ intdiv($spent, 60) }}h {{ $spent % 60 }}m</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Ver Detalles Button (shown on hover) -->
                                    <div class="absolute inset-y-0 right-0 top-1/2 -translate-y-1/2 px-3 opacity-0 group-hover:opacity-100 transition-opacity flex items-center bg-gradient-to-l from-[#1e1e1e] via-[#1e1e1e] to-transparent">
                                        <button 
                                            wire:click="$dispatch('openTaskDetails', { taskId: {{ $task->id }} })"
                                            class="text-[#007fd4] hover:text-white text-xs font-medium transition-colors px-2 py-1 rounded bg-[#252526] hover:bg-[#333] border border-[#333] shadow-md">
                                            Ver Detalles
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 text-[#7b7b7b] italic font-mono border border-dashed border-[#333] rounded-lg">
                                    // No hay tareas en esta categoría
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="bg-[#2d2d2d] px-4 py-3 sm:px-6 flex justify-end border-t border-[#333]">
                        <button wire:click="close" class="inline-flex justify-center rounded border border-[#3e3e42] shadow-sm px-4 py-2 bg-[#3c3c3c] text-base font-medium text-[#d4d4d4] hover:bg-[#4a4a4d] focus:outline-none sm:text-sm">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
