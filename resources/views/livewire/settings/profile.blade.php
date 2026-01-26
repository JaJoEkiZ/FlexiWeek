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

        // Agrupar timezones por región para mejor UX
        $allTimezones = timezone_identifiers_list();
        $this->timezones = collect($allTimezones)->groupBy(function ($tz) {
            return explode('/', $tz)[0] ?? 'Otros';
        })->toArray();
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
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

    /**
     * Update the timezone for the currently authenticated user.
     */
    public function updateTimezone(): void
    {
        $validated = $this->validate([
            'timezone' => 'required|timezone',
        ]);

        Auth::user()->update($validated);

        $this->dispatch('timezone-updated');
    }

    /**
     * Send an email verification notification to the current user.
     */
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

    <flux:heading class="sr-only">{{ __('Configuración del Perfil') }}</flux:heading>

    <x-settings.layout :heading="__('Perfil')" :subheading="__('Actualiza tu nombre y correo electrónico')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" :label="__('Nombre')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" :label="__('Correo Electrónico')" type="email" required autocomplete="email" />

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Tu dirección de correo no está verificada.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Haz clic aquí para reenviar el correo de verificación.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('Se ha enviado un nuevo enlace de verificación a tu correo.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full" data-test="update-profile-button">
                        {{ __('Guardar') }}
                    </flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Guardado.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>

    <x-settings.layout :heading="__('Zona Horaria')" :subheading="__('Selecciona tu zona horaria preferida para visualizar fechas y horas')" class="mt-8">
        <form wire:submit="updateTimezone" class="my-6 w-full space-y-6">
            <div>
                <flux:select wire:model="timezone" :label="__('Zona Horaria')" required>
                    @foreach($timezones as $region => $tzList)
                        <optgroup label="{{ $region }}">
                            @foreach($tzList as $tz)
                                <option value="{{ $tz }}">{{ str_replace('_', ' ', $tz) }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </flux:select>
                @error('timezone')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full" data-test="update-timezone-button">
                        {{ __('Actualizar Zona Horaria') }}
                    </flux:button>
                </div>

                <x-action-message class="me-3" on="timezone-updated">
                    {{ __('Zona horaria actualizada.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>

    <x-settings.layout :heading="__('Eliminar Cuenta')" :subheading="__('Esta acción es irreversible')">
        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>
