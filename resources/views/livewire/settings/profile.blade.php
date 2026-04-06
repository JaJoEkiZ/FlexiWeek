<?php

use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Volt\Component;

new class extends Component
{
    use ProfileValidationRules;

    public string $name = '';

    public string $email = '';

    public string $timezone = '';

    public array $timezones = [];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->timezone = Auth::user()->timezone ?? 'America/Argentina/Buenos_Aires';

        $allTimezones = timezone_identifiers_list();
        $this->timezones = collect($allTimezones)->groupBy(function ($tz) {
            return explode('/', $tz)[0] ?? 'Otros';
        })->toArray();
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();
        $validated = $this->validate($this->profileRules($user->id));
        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
        $this->dispatch('profile-updated', name: $user->name);
    }

    public function updateTimezone(): void
    {
        $validated = $this->validate([
            'timezone' => 'required|timezone',
        ]);

        Auth::user()->update($validated);
        $this->dispatch('timezone-updated');
    }

    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('planner', absolute: false));
            return;
        }

        $user->sendEmailVerificationNotification();
        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <div class="space-y-6">
        {{-- Perfil --}}
        <x-settings.layout :heading="__('Información del Perfil')" :subheading="__('Actualiza el nombre y correo electrónico de tu cuenta')">
            <form wire:submit="updateProfileInformation" class="space-y-5">
                <div>
                    <label class="settings-label">{{ __('Nombre') }}</label>
                    <input wire:model="name" type="text" required autofocus autocomplete="name" class="settings-input" placeholder="Tu nombre..." />
                    @error('name') <p class="mt-1 text-xs text-[#f85149] font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="settings-label">{{ __('Correo Electrónico') }}</label>
                    <input wire:model="email" type="email" required autocomplete="email" class="settings-input" placeholder="tu@email.com" />
                    @error('email') <p class="mt-1 text-xs text-[#f85149] font-medium">{{ $message }}</p> @enderror

                    @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !auth()->user()->hasVerifiedEmail())
                        <div class="mt-4 p-3 rounded bg-yellow-500/10 border border-yellow-500/20">
                            <p class="text-xs text-yellow-600 dark:text-yellow-500 font-medium">
                                {{ __('Tu dirección de correo no está verificada.') }}
                                <button wire:click.prevent="resendVerificationNotification"
                                        class="text-[#007fd4] hover:underline cursor-pointer font-bold ml-1">
                                    {{ __('Reenviar verificación') }}
                                </button>
                            </p>
                            @if (session('status') === 'verification-link-sent')
                                <p class="mt-2 text-xs text-[#4ec9b0] font-bold">
                                    {{ __('✓ Nuevo enlace enviado.') }}
                                </p>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="flex items-center gap-4 pt-2">
                    <button type="submit" class="settings-btn-primary">{{ __('Guardar Cambios') }}</button>
                    <x-action-message class="text-sm text-[#4ec9b0] font-bold" on="profile-updated">
                        {{ __('✓ Guardado.') }}
                    </x-action-message>
                </div>
            </form>
        </x-settings.layout>

        {{-- Zona Horaria --}}
        <x-settings.layout :heading="__('Zona Horaria')" :subheading="__('Afecta cómo visualizas los horarios en tu planificador')">
            <form wire:submit="updateTimezone" class="space-y-5">
                <div>
                    <label class="settings-label">{{ __('Selecciona tu región') }}</label>
                    <select wire:model="timezone" required class="settings-input cursor-pointer">
                        @foreach($timezones as $region => $tzList)
                            <optgroup label="{{ $region }}" class="bg-[var(--settings-input-bg)]">
                                @foreach($tzList as $tz)
                                    <option value="{{ $tz }}">{{ str_replace('_', ' ', $tz) }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('timezone') <p class="mt-1 text-xs text-[#f85149] font-medium">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-4 pt-2">
                    <button type="submit" class="settings-btn-primary">{{ __('Actualizar Zona Horaria') }}</button>
                    <x-action-message class="text-sm text-[#4ec9b0] font-bold" on="timezone-updated">
                        {{ __('✓ Zona horaria actualizada.') }}
                    </x-action-message>
                </div>
            </form>
        </x-settings.layout>

        {{-- Eliminar Cuenta --}}
        <x-settings.layout :heading="__('Zona de Peligro')" :subheading="__('Acciones irreversibles relacionadas con tu cuenta')">
            <livewire:settings.delete-user-form />
        </x-settings.layout>
    </div>

    {{-- Close the settings-heading containers --}}
        </div>
    </div>
</div>
</section>
