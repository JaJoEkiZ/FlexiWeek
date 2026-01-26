<div>
    @if($isOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Overlay -->
                <div class="fixed inset-0 bg-black bg-opacity-70 transition-opacity backdrop-filter backdrop-blur-sm" aria-hidden="true" wire:click="close"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal Panel -->
                <div class="inline-block align-bottom bg-[#252526] rounded text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-[#333]" wire:keydown.window.escape="close">
                    <form wire:submit.prevent="save">
                        <div class="bg-[#252526] px-4 pt-5 pb-4 sm:p-6">
                            <div>
                                <h3 class="text-lg leading-6 font-medium text-[#d4d4d4] mb-4 flex items-center gap-2" id="modal-title">
                                    <span class="text-[#007fd4]">{ }</span> {{ $taskId ? 'Editar Tarea' : 'Nueva Tarea' }}
                                </h3>
                                <div class="space-y-4">
                                    <div>
                                        <label for="period" class="block text-xs font-mono text-[#7b7b7b] mb-1">Semana</label>
                                        <select wire:model="periodId" id="period" class="block w-full rounded bg-[#3c3c3c] border-[#333] text-[#d4d4d4] focus:border-[#007fd4] focus:ring-[#007fd4] sm:text-sm py-2 px-3">
                                            @foreach($periods as $period)
                                                <option value="{{ $period->id }}">{{ $period->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('periodId') <span class="text-[#f14c4c] text-xs font-mono mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label for="title" class="block text-xs font-mono text-[#7b7b7b] mb-1">Nombre de la tarea</label>
                                        <input type="text" wire:model="title" x-on:keydown.enter.prevent="$wire.save()" id="title" class="block w-full rounded bg-[#3c3c3c] border-[#333] text-[#d4d4d4] focus:border-[#007fd4] focus:ring-[#007fd4] sm:text-sm py-2 px-3 placeholder-[#666]">
                                        @error('title') <span class="text-[#f14c4c] text-xs font-mono mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label for="description" class="block text-xs font-mono text-[#7b7b7b] mb-1">Descripción / Detalles</label>
                                        <textarea wire:model="description" id="description" rows="3" placeholder="Notas, detalles, contexto..." class="block w-full rounded bg-[#3c3c3c] border-[#333] text-[#d4d4d4] focus:border-[#007fd4] focus:ring-[#007fd4] sm:text-sm py-2 px-3 placeholder-[#666] resize-none"></textarea>
                                        @error('description') <span class="text-[#f14c4c] text-xs font-mono mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label for="completionMethod" class="block text-xs font-mono text-[#7b7b7b] mb-1">Tipo de Tarea</label>
                                        <select wire:model.live="completionMethod" id="completionMethod" class="block w-full rounded bg-[#3c3c3c] border-[#333] text-[#d4d4d4] focus:border-[#007fd4] focus:ring-[#007fd4] sm:text-sm py-2 px-3">
                                            <option value="time">Por Tiempo</option>
                                            <option value="subtasks">Por Subtareas</option>
                                        </select>
                                        @error('completionMethod') <span class="text-[#f14c4c] text-xs font-mono mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    @if($completionMethod === 'time')
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label for="hours" class="block text-xs font-mono text-[#7b7b7b] mb-1">Horas</label>
                                            <input type="number" 
                                                   wire:model="hours" 
                                                   x-on:keydown.enter.prevent="$wire.save()" 
                                                   x-on:focus="$el.select()"
                                                   id="hours" 
                                                   min="0" 
                                                   pattern="[0-9]*"
                                                   inputmode="numeric"
                                                   class="block w-full rounded bg-[#3c3c3c] border-[#333] text-[#d4d4d4] focus:border-[#007fd4] focus:ring-[#007fd4] sm:text-sm py-2 px-3 placeholder-[#666]">
                                            @error('hours') <span class="text-[#f14c4c] text-xs font-mono mt-1">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label for="minutes" class="block text-xs font-mono text-[#7b7b7b] mb-1">Minutos</label>
                                            <input type="number" 
                                                   wire:model="minutes" 
                                                   x-on:keydown.enter.prevent="$wire.save()" 
                                                   x-on:focus="$el.select()"
                                                   id="minutes" 
                                                   min="0" 
                                                   max="59"
                                                   pattern="[0-9]*"
                                                   inputmode="numeric"
                                                   class="block w-full rounded bg-[#3c3c3c] border-[#333] text-[#d4d4d4] focus:border-[#007fd4] focus:ring-[#007fd4] sm:text-sm py-2 px-3 placeholder-[#666]">
                                            @error('minutes') <span class="text-[#f14c4c] text-xs font-mono mt-1">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="text-xs text-[#7b7b7b] -mt-2">
                                        <span class="text-[#f14c4c]">*</span> Mínimo 10 minutos requeridos
                                    </div>
                                    @endif

                                    <div class="space-y-2">
                                        <label class="block text-xs font-mono text-[#7b7b7b] mb-1">Subtareas @if($completionMethod === 'subtasks')<span class="text-[#f14c4c]">*</span>@endif</label>
                                        
                                        @if($completionMethod === 'subtasks' && empty($subtasks))
                                            <div class="p-3 bg-[#2d2d2d] rounded border border-[#f14c4c]/30 text-xs text-[#f14c4c]">
                                                <p class="flex items-start gap-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mt-0.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                    </svg>
                                                    <span>
                                                        <strong>Requerido:</strong> Debes agregar al menos 1 subtarea para poder usar el control por subtareas.
                                                    </span>
                                                </p>
                                            </div>
                                        @endif
                                        
                                        @foreach($subtasks as $index => $subtask)
                                            <div class="space-y-2 p-2 bg-[#2d2d2d] rounded border border-[#3c3c3c]">
                                                <div class="flex gap-2 items-center">
                                                    <input type="checkbox" wire:model="subtasks.{{ $index }}.is_completed" class="rounded bg-[#3c3c3c] border-[#333] text-[#007fd4] focus:ring-0 focus:ring-offset-0">
                                                    <input type="text" wire:model="subtasks.{{ $index }}.title" placeholder="Título de la subtarea" class="block w-full rounded bg-[#3c3c3c] border-[#333] text-[#d4d4d4] focus:border-[#007fd4] focus:ring-[#007fd4] sm:text-sm py-2 px-3 placeholder-[#666]">
                                                    <button type="button" wire:click="removeSubtask({{ $index }})" class="text-[#f14c4c] hover:text-[#c43e3e]">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                          <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </div>
                                                <input type="text" wire:model="subtasks.{{ $index }}.description" placeholder="Detalles de la subtarea (opcional)" class="block w-full rounded bg-[#3c3c3c] border-[#333] text-[#9d9d9d] focus:border-[#007fd4] focus:ring-[#007fd4] text-xs py-1.5 px-3 placeholder-[#555]">
                                                
                                                {{-- Time Field: Solo Invertido --}}
                                                <div class="flex items-center gap-2 mt-2">
                                                    <label class="text-xs font-mono text-[#5a5a5a]">⏱ Tiempo:</label>
                                                    <div class="flex gap-1 items-center">
                                                        <input type="number" wire:model="subtasks.{{ $index }}.spent_hours" min="0" placeholder="0" class="w-12 rounded bg-[#3c3c3c] border-[#333] text-[#9d9d9d] focus:border-[#007fd4] focus:ring-[#007fd4] text-xs py-1 px-2 placeholder-[#555]">
                                                        <span class="text-[#5a5a5a] text-xs">h</span>
                                                        <input type="number" wire:model="subtasks.{{ $index }}.spent_minutes" min="0" max="59" placeholder="0" class="w-12 rounded bg-[#3c3c3c] border-[#333] text-[#9d9d9d] focus:border-[#007fd4] focus:ring-[#007fd4] text-xs py-1 px-2 placeholder-[#555]">
                                                        <span class="text-[#5a5a5a] text-xs">m</span>
                                                    </div>
                                                </div>
                                                
                                                @error('subtasks.'.$index.'.title') <span class="text-[#f14c4c] text-xs font-mono mt-1">{{ $message }}</span> @enderror
                                            </div>
                                        @endforeach
                                        <button type="button" wire:click="addSubtask" class="text-xs text-[#007fd4] hover:underline flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                            </svg>
                                            Agregar Subtarea
                                        </button>
                                        @error('subtasks') <span class="text-[#f14c4c] text-xs font-mono mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-[#2d2d2d] px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-[#333]">
                            <button type="submit" class="w-full inline-flex justify-center rounded border border-transparent shadow-sm px-4 py-2 bg-[#007fd4] text-base font-medium text-white hover:bg-[#006cb5] focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                Guardar
                            </button>
                            <button type="button" wire:click="close" class="mt-3 w-full inline-flex justify-center rounded border border-[#3e3e42] shadow-sm px-4 py-2 bg-[#3c3c3c] text-base font-medium text-[#d4d4d4] hover:bg-[#4a4a4d] focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
