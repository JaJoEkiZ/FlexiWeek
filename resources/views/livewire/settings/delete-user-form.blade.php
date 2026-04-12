<?php

use App\Concerns\PasswordValidationRules;
use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    use PasswordValidationRules;

    public string $password = '';

    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => $this->currentPasswordRules(),
        ]);

        tap(Auth::user(), $logout(...))->delete();
        $this->redirect('/', navigate: true);
    }
}; ?>

<div class="space-y-4" x-data="{ showModal: false }">
    <div class="p-4 rounded-lg bg-[#da3633]/5 border border-[#da3633]/20 mb-4">
        <p class="text-sm font-medium" style="color: var(--settings-subheading-text)">
            {{ __('Una vez eliminada la cuenta, todos sus recursos y datos se borrarán permanentemente. Por favor, descarga cualquier dato que desees conservar.') }}
        </p>
    </div>

    <button @click="showModal = true" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-[#f85149] bg-[#3b1219] border border-[#da3633] rounded-md hover:bg-[#da3633] hover:text-white hover:shadow-lg hover:shadow-[#da3633]/30 active:scale-95 transition-all">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
        {{ __('Eliminar Mi Cuenta') }}
    </button>

    {{-- Modal --}}
    <div x-show="showModal" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-md"
         @click.self="showModal = false">
        
        <div class="settings-card w-full max-w-md mx-4 shadow-2xl relative overflow-hidden"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100">
            
            {{-- Danger Header bar --}}
            <div class="absolute top-0 left-0 w-full h-1 bg-[#da3633]"></div>

            <h3 class="text-xl font-bold mb-3 flex items-center gap-2" style="color: var(--settings-heading-text)">
                <span class="text-[#da3633]">⚠️</span> {{ __('¿Estás absolutamente seguro?') }}
            </h3>
            
            <p class="text-sm mb-6" style="color: var(--settings-subheading-text)">
                {{ __('Esta acción no se puede deshacer. Por favor, ingresa tu contraseña para confirmar la eliminación de tu cuenta.') }}
            </p>

            <form wire:submit="deleteUser" class="space-y-5">
                <div>
                    <label class="settings-label">{{ __('Tu Contraseña') }}</label>
                    <input wire:model="password" type="password" required class="settings-input" placeholder="••••••••" />
                    @error('password') <p class="mt-1 text-xs text-[#f85149] font-medium">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showModal = false"
                            class="px-5 py-2 text-sm font-medium transition-all rounded-md border border-transparent hover:bg-[var(--settings-sidebar-item-hover)]"
                            style="color: var(--settings-subheading-text)">
                        {{ __('Cancelar') }}
                    </button>
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-[#f85149] bg-[#3b1219] border border-[#da3633] rounded-md hover:bg-[#da3633] hover:text-white hover:shadow-lg hover:shadow-[#da3633]/30 active:scale-95 transition-all">
                        {{ __('Confirmar Eliminación') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
