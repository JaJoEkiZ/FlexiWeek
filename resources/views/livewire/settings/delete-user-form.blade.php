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
    <button @click="showModal = true" class="settings-btn-danger">
        🗑️ {{ __('Eliminar cuenta') }}
    </button>

    {{-- Modal --}}
    <div x-show="showModal" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
         @click.self="showModal = false">
        <div class="dark:bg-[#252526] bg-white border dark:border-[#555] border-gray-200 rounded-lg shadow-2xl p-6 w-full max-w-md mx-4"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <h3 class="text-lg font-semibold dark:text-white text-gray-900 mb-2">
                ⚠️ {{ __('¿Estás seguro?') }}
            </h3>
            <p class="text-sm dark:text-[#8b949e] text-gray-500 mb-5">
                {{ __('Una vez eliminada tu cuenta, todos sus recursos y datos se borrarán permanentemente. Ingresa tu contraseña para confirmar.') }}
            </p>

            <form wire:submit="deleteUser" class="space-y-4">
                <div>
                    <label class="settings-label">{{ __('Contraseña') }}</label>
                    <input wire:model="password" type="password" class="settings-input" />
                    @error('password') <p class="mt-1 text-xs text-[#f85149]">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showModal = false"
                            class="px-4 py-2 text-sm font-medium dark:text-[#8b949e] text-gray-600 dark:bg-[#3c3c3c] bg-gray-100 dark:border-[#555] border-gray-300 border rounded-md dark:hover:bg-[#333] hover:bg-gray-200 dark:hover:text-white hover:text-gray-900 transition-all">
                        {{ __('Cancelar') }}
                    </button>
                    <button type="submit" class="settings-btn-danger">
                        {{ __('Eliminar cuenta') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
