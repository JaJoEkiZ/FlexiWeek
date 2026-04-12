<?php

use App\Concerns\PasswordValidationRules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    use PasswordValidationRules;

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => $this->currentPasswordRules(),
                'password' => $this->passwordRules(),
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');
            throw $e;
        }

        Auth::user()->update(['password' => $validated['password']]);
        $this->reset('current_password', 'password', 'password_confirmation');
        $this->dispatch('password-updated');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Actualizar Contraseña')" :subheading="__('Asegura tu cuenta con una clave robusta y difícil de adivinar')">
        <form method="POST" wire:submit="updatePassword" class="space-y-5">
            <div>
                <label class="settings-label">{{ __('Contraseña Actual') }}</label>
                <input wire:model="current_password" type="password" required autocomplete="current-password" class="bg-white/10 backdrop-blur-md rounded-md border border-white/20 shadow-lg w-full px-3 py-2 text-sm text-[#d4d4d4] placeholder-[#666] outline-none focus:border-[#007fd4] transition-colors" placeholder="••••••••" />
                @error('current_password') <p class="mt-1 text-xs text-[#f85149] font-medium">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="settings-label">{{ __('Nueva Contraseña') }}</label>
                    <input wire:model="password" type="password" required autocomplete="new-password" class="bg-white/10 backdrop-blur-md rounded-md border border-white/20 shadow-lg w-full px-3 py-2 text-sm text-[#d4d4d4] placeholder-[#666] outline-none focus:border-[#007fd4] transition-colors" placeholder="Nueva clave..." />
                    @error('password') <p class="mt-1 text-xs text-[#f85149] font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="settings-label">{{ __('Confirmar Contraseña') }}</label>
                    <input wire:model="password_confirmation" type="password" required autocomplete="new-password" class="bg-white/10 backdrop-blur-md rounded-md border border-white/20 shadow-lg w-full px-3 py-2 text-sm text-[#d4d4d4] placeholder-[#666] outline-none focus:border-[#007fd4] transition-colors" placeholder="Repite la clave..." />
                </div>
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-[#007fd4] border border-[#1a93e3] rounded-md hover:bg-[#006bb3] hover:shadow-lg hover:shadow-[#007fd4]/30 active:scale-95 transition-all">{{ __('Cambiar Contraseña') }}</button>
                <x-action-message class="text-sm text-[#4ec9b0] font-bold" on="password-updated">
                    {{ __('✓ Contraseña actualizada.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>

    {{-- Close the content + outer layout containers from settings-heading --}}
        </div>
    </div>
</div>
</section>
