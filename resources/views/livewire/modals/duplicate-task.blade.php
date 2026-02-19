<div>
    @if($isOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Overlay -->
                <div class="fixed inset-0 bg-black bg-opacity-70 transition-opacity backdrop-filter backdrop-blur-sm" aria-hidden="true" wire:click="close"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal Panel -->
                <div class="inline-block align-bottom bg-[#252526] rounded text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-[#333]" wire:keydown.window.escape="close">
                    <div class="bg-[#252526] px-4 pt-5 pb-4 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-[#d4d4d4] mb-4 flex items-center gap-2">
                            <span class="text-[#007fd4]">📋</span> Duplicar Tarea
                        </h3>

                        <div class="space-y-4">
                            <!-- Tarea a duplicar -->
                            @if($sourceTask)
                                <div>
                                    <label class="block text-xs font-mono text-[#7b7b7b] mb-1">Tarea a duplicar</label>
                                    <div class="p-3 bg-[#1e1e1e] rounded border border-[#007fd4]/30">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-[#007fd4]">📄</span>
                                            <span class="text-[#d4d4d4] font-medium text-sm">{{ $sourceTask->title }}</span>
                                        </div>
                                        @if($sourceTask->estimated_minutes > 0)
                                            <div class="text-xs text-[#7b7b7b] ml-6">
                                                ⏱ {{ intdiv($sourceTask->estimated_minutes, 60) }}h {{ $sourceTask->estimated_minutes % 60 }}m estimados
                                            </div>
                                        @endif
                                        @if($sourceTask->subtasks && count($sourceTask->subtasks) > 0)
                                            <div class="text-xs text-[#7b7b7b] ml-6 mt-1">
                                                📌 {{ count($sourceTask->subtasks) }} subtarea(s)
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Subtareas a incluir -->
                            @if($sourceTask && $sourceTask->subtasks && count($sourceTask->subtasks) > 0)
                                <div x-data="{ open: true }">
                                    <button type="button" @click="open = !open" class="flex items-center gap-2 text-xs font-mono text-[#7b7b7b] hover:text-[#d4d4d4] transition-colors w-full">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 transition-transform" :class="open ? 'rotate-90' : ''" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        Subtareas a incluir ({{ count($selectedSubtasks) }}/{{ count($sourceTask->subtasks) }})
                                    </button>
                                    <div x-show="open" x-transition class="mt-2 space-y-1.5">
                                        @foreach($sourceTask->subtasks as $subtask)
                                            <label class="flex items-center gap-2 p-2 bg-[#1e1e1e] rounded border border-[#333] cursor-pointer hover:border-[#007fd4]/50 transition-colors">
                                                <input type="checkbox" wire:model.live="selectedSubtasks" value="{{ $subtask->id }}" class="rounded bg-[#3c3c3c] border-[#333] text-[#007fd4] focus:ring-0 focus:ring-offset-0">
                                                <span class="text-xs text-[#d4d4d4]">{{ $subtask->title }}</span>
                                                @if($subtask->estimated_minutes > 0)
                                                    <span class="text-[10px] text-[#7b7b7b] ml-auto">{{ intdiv($subtask->estimated_minutes, 60) }}h {{ $subtask->estimated_minutes % 60 }}m</span>
                                                @endif
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Período destino -->
                            <div>
                                <label for="targetPeriod" class="block text-xs font-mono text-[#7b7b7b] mb-1">Período destino</label>
                                <select wire:model.live="targetPeriodId" id="targetPeriod" class="block w-full rounded bg-[#3c3c3c] border-[#333] text-[#d4d4d4] focus:border-[#007fd4] focus:ring-[#007fd4] sm:text-sm py-2 px-3">
                                    <option value="">-- Seleccionar semana --</option>
                                    @foreach($periods as $period)
                                        <option value="{{ $period->id }}">{{ $period->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Tareas existentes en el período destino -->
                            @if($targetPeriodId && count($targetPeriodTasks) > 0)
                                <div x-data="{ open: false }">
                                    <button type="button" @click="open = !open" class="flex items-center gap-2 text-xs font-mono text-[#7b7b7b] hover:text-[#d4d4d4] transition-colors w-full">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 transition-transform" :class="open ? 'rotate-90' : ''" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        Tareas existentes ({{ count($targetPeriodTasks) }})
                                    </button>
                                    <div x-show="open" x-transition class="mt-2 space-y-1 max-h-40 overflow-y-auto">
                                        @foreach($targetPeriodTasks as $existingTask)
                                            <div class="flex items-center gap-2 p-2 bg-[#1e1e1e] rounded border border-[#333] text-xs">
                                                <span class="text-[#7b7b7b]">•</span>
                                                <span class="text-[#9d9d9d]">{{ $existingTask['title'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @elseif($targetPeriodId)
                                <p class="text-xs text-[#5a5a5a] italic">No hay tareas en este período</p>
                            @endif
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="bg-[#2d2d2d] px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-[#333]">
                        <button 
                            wire:click="duplicate" 
                            @if(!$targetPeriodId) disabled @endif
                            class="w-full inline-flex justify-center rounded border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-colors
                            {{ $targetPeriodId ? 'bg-[#007fd4] hover:bg-[#006cb5]' : 'bg-[#3c3c3c] text-[#7b7b7b] cursor-not-allowed' }}">
                            📋 Duplicar
                        </button>
                        <button type="button" wire:click="close" class="mt-3 w-full inline-flex justify-center rounded border border-[#3e3e42] shadow-sm px-4 py-2 bg-[#3c3c3c] text-base font-medium text-[#d4d4d4] hover:bg-[#4a4a4d] focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
