<div>
    @if($isOpen && $task)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
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

                            <!-- Subtasks Descriptions -->
                            @if($task->subtasks->count() > 0)
                                <div>
                                    <label class="block text-xs font-mono text-[#7b7b7b] mb-2">Subtareas</label>
                                    <div class="space-y-3">
                                        @foreach($task->subtasks as $subtask)
                                            <div class="bg-[#1e1e1e] rounded p-3 border border-[#333]">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <span class="text-xs {{ $subtask->is_completed ? 'text-[#4ec9b0]' : 'text-[#ce9178]' }}">
                                                        {{ $subtask->is_completed ? '✓' : '○' }}
                                                    </span>
                                                    <span class="text-sm text-[#d4d4d4] font-medium">{{ $subtask->title }}</span>
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
