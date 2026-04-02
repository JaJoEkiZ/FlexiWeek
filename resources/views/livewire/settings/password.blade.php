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

    <x-settings.layout :heading="__('Actualizar Contraseña')" :subheading="__('Asegúrate de que tu cuenta utilice una contraseña larga y aleatoria para mantenerse segura')">
        <form method="POST" wire:submit="updatePassword" class="space-y-5">
            <div>
                <label class="settings-label">{{ __('Contraseña Actual') }}</label>
                <input wire:model="current_password" type="password" required autocomplete="current-password" class="settings-input" />
                @error('current_password') <p class="mt-1 text-xs text-[#f85149]">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="settings-label">{{ __('Nueva Contraseña') }}</label>
                <input wire:model="password" type="password" required autocomplete="new-password" class="settings-input" />
                @error('password') <p class="mt-1 text-xs text-[#f85149]">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="settings-label">{{ __('Confirmar Contraseña') }}</label>
                <input wire:model="password_confirmation" type="password" required autocomplete="new-password" class="settings-input" />
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button type="submit" class="settings-btn-primary">{{ __('Guardar') }}</button>
                <x-action-message class="text-sm text-[#4ec9b0]" on="password-updated">
                    {{ __('✓ Guardado.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>

    {{-- Close the settings-heading containers --}}
        </div>
    </div>
</div>
</section>
