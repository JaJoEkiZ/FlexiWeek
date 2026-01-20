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
                                        <span class="text-[#007fd4]">{ }</span> {{ $periodId ? 'Editar Semana' : 'Nueva Semana' }}
                                    </h3>
                                    <div class="space-y-4">
                                        <div>
                                            <label for="pName" class="block text-xs font-mono text-[#7b7b7b] mb-1">Semana</label>
                                            <input type="text" wire:model="name" x-on:keydown.enter.prevent="$wire.save()" id="pName" class="block w-full rounded bg-[#3c3c3c] border-[#333] text-[#d4d4d4] focus:border-[#007fd4] focus:ring-[#007fd4] sm:text-sm py-2 px-3 placeholder-[#666]">
                                            @error('name') <span class="text-[#f14c4c] text-xs font-mono mt-1">{{ $message }}</span> @enderror
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label for="pStart" class="block text-xs font-mono text-[#7b7b7b] mb-1">Fecha Inicio</label>
                                                <input type="date" wire:model="startDate" x-on:keydown.enter.prevent="$wire.save()" id="pStart" class="block w-full rounded bg-[#3c3c3c] border-[#333] text-[#d4d4d4] focus:border-[#007fd4] focus:ring-[#007fd4] sm:text-sm py-2 px-3">
                                                @error('startDate') <span class="text-[#f14c4c] text-xs font-mono mt-1">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label for="pEnd" class="block text-xs font-mono text-[#7b7b7b] mb-1">Fecha Fin</label>
                                                <input type="date" wire:model="endDate" x-on:keydown.enter.prevent="$wire.save()" id="pEnd" class="block w-full rounded bg-[#3c3c3c] border-[#333] text-[#d4d4d4] focus:border-[#007fd4] focus:ring-[#007fd4] sm:text-sm py-2 px-3">
                                                @error('endDate') <span class="text-[#f14c4c] text-xs font-mono mt-1">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-[#2d2d2d] px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-[#333]">
                                <button type="submit" class="w-full inline-flex justify-center rounded border border-transparent shadow-sm px-4 py-2 bg-[#007fd4] text-base font-medium text-white hover:bg-[#006cb5] focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                    Guardar Cambios
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