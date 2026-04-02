<?php

use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Symfony\Component\HttpFoundation\Response;

new class extends Component
{
    #[Locked]
    public bool $twoFactorEnabled;

    #[Locked]
    public bool $requiresConfirmation;

    #[Locked]
    public string $qrCodeSvg = '';

    #[Locked]
    public string $manualSetupKey = '';

    public bool $showModal = false;

    public bool $showVerificationStep = false;

    #[Validate('required|string|size:6', onUpdate: false)]
    public string $code = '';

    /**
     * Mount the component.
     */
    public function mount(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        abort_unless(Features::enabled(Features::twoFactorAuthentication()), Response::HTTP_FORBIDDEN);

        if (Fortify::confirmsTwoFactorAuthentication() && is_null(auth()->user()->two_factor_confirmed_at)) {
            $disableTwoFactorAuthentication(auth()->user());
        }

        $this->twoFactorEnabled = auth()->user()->hasEnabledTwoFactorAuthentication();
        $this->requiresConfirmation = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
    }

    /**
     * Enable two-factor authentication for the user.
     */
    public function enable(EnableTwoFactorAuthentication $enableTwoFactorAuthentication): void
    {
        $enableTwoFactorAuthentication(auth()->user());

        if (! $this->requiresConfirmation) {
            $this->twoFactorEnabled = auth()->user()->hasEnabledTwoFactorAuthentication();
        }

        $this->loadSetupData();

        $this->showModal = true;
    }

    /**
     * Load the two-factor authentication setup data for the user.
     */
    private function loadSetupData(): void
    {
        $user = auth()->user();

        try {
            $this->qrCodeSvg = $user?->twoFactorQrCodeSvg();
            $this->manualSetupKey = decrypt($user->two_factor_secret);
        } catch (Exception) {
            $this->addError('setupData', 'Failed to fetch setup data.');

            $this->reset('qrCodeSvg', 'manualSetupKey');
        }
    }

    /**
     * Show the two-factor verification step if necessary.
     */
    public function showVerificationIfNecessary(): void
    {
        if ($this->requiresConfirmation) {
            $this->showVerificationStep = true;

            $this->resetErrorBag();

            return;
        }

        $this->closeModal();
    }

    /**
     * Confirm two-factor authentication for the user.
     */
    public function confirmTwoFactor(ConfirmTwoFactorAuthentication $confirmTwoFactorAuthentication): void
    {
        $this->validate();

        $confirmTwoFactorAuthentication(auth()->user(), $this->code);

        $this->closeModal();

        $this->twoFactorEnabled = true;
    }

    /**
     * Reset two-factor verification state.
     */
    public function resetVerification(): void
    {
        $this->reset('code', 'showVerificationStep');

        $this->resetErrorBag();
    }

    /**
     * Disable two-factor authentication for the user.
     */
    public function disable(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $disableTwoFactorAuthentication(auth()->user());

        $this->twoFactorEnabled = false;
    }

    /**
     * Close the two-factor authentication modal.
     */
    public function closeModal(): void
    {
        $this->reset(
            'code',
            'manualSetupKey',
            'qrCodeSvg',
            'showModal',
            'showVerificationStep',
        );

        $this->resetErrorBag();

        if (! $this->requiresConfirmation) {
            $this->twoFactorEnabled = auth()->user()->hasEnabledTwoFactorAuthentication();
        }
    }

    /**
     * Get the current modal configuration state.
     */
    public function getModalConfigProperty(): array
    {
        if ($this->twoFactorEnabled) {
            return [
                'title' => __('Autenticación de dos factores habilitada'),
                'description' => __('La autenticación de dos factores está habilitada. Escanea el código QR o ingresa la clave de configuración en tu aplicación de autenticación.'),
                'buttonText' => __('Cerrar'),
            ];
        }

        if ($this->showVerificationStep) {
            return [
                'title' => __('Verificar código de autenticación'),
                'description' => __('Ingresa el código de 6 dígitos de tu aplicación de autenticación.'),
                'buttonText' => __('Continuar'),
            ];
        }

        return [
            'title' => __('Habilitar autenticación de dos factores'),
            'description' => __('Para terminar de habilitar la autenticación de dos factores, escanea el código QR o ingresa la clave de configuración en tu aplicación de autenticación.'),
            'buttonText' => __('Continuar'),
        ];
    }
} ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout
        :heading="__('Autenticación de dos factores')"
        :subheading="__('Administra tu configuración de autenticación de dos factores')"
    >
        <div class="flex flex-col w-full mx-auto space-y-6 text-sm" wire:cloak>
            @if ($twoFactorEnabled)
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-[#2ea043]/20 text-[#4ec9b0] border border-[#2ea043]/30">{{ __('Habilitado') }}</span>
                    </div>

                    <p class="text-sm text-[#8b949e]">
                        {{ __('Con la autenticación de dos factores habilitada, se te solicitará un pin seguro y aleatorio durante el inicio de sesión, que puedes obtener de la aplicación compatible con TOTP en tu teléfono.') }}
                    </p>

                    <livewire:settings.two-factor.recovery-codes :$requiresConfirmation/>

                    <div class="flex justify-start">
                        <button wire:click="disable"
                                class="px-4 py-2 text-sm font-medium text-white bg-[#da3633] rounded-md hover:bg-[#f85149] transition-all">
                            🛡️ {{ __('Deshabilitar 2FA') }}
                        </button>
                    </div>
                </div>
            @else
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-[#da3633]/20 text-[#f85149] border border-[#da3633]/30">{{ __('Deshabilitado') }}</span>
                    </div>

                    <p class="text-sm text-[#7b7b7b]">
                        {{ __('Cuando habilitas la autenticación de dos factores, se te solicitará un pin seguro durante el inicio de sesión. Este pin se puede obtener de una aplicación compatible con TOTP en tu teléfono.') }}
                    </p>

                     <button wire:click="enable"
                             class="px-4 py-2 text-sm font-medium text-white bg-[#007fd4] rounded-md hover:bg-[#006bb3] transition-all">
                        🛡️ {{ __('Habilitar 2FA') }}
                    </button>
                </div>
            @endif
        </div>
    </x-settings.layout>

    <flux:modal
        name="two-factor-setup-modal"
        class="max-w-md md:min-w-md"
        @close="closeModal"
        wire:model="showModal"
    >
        <div class="space-y-6">
            <div class="flex flex-col items-center space-y-4">
                <div class="p-0.5 w-auto rounded-full border border-stone-100 dark:border-stone-600 bg-white dark:bg-stone-800 shadow-sm">
                    <div class="p-2.5 rounded-full border border-stone-200 dark:border-stone-600 overflow-hidden bg-stone-100 dark:bg-stone-200 relative">
                        <div class="flex items-stretch absolute inset-0 w-full h-full divide-x [&>div]:flex-1 divide-stone-200 dark:divide-stone-300 justify-around opacity-50">
                            @for ($i = 1; $i <= 5; $i++)
                                <div></div>
                            @endfor
                        </div>

                        <div class="flex flex-col items-stretch absolute w-full h-full divide-y [&>div]:flex-1 inset-0 divide-stone-200 dark:divide-stone-300 justify-around opacity-50">
                            @for ($i = 1; $i <= 5; $i++)
                                <div></div>
                            @endfor
                        </div>

                        <flux:icon.qr-code class="relative z-20 dark:text-accent-foreground"/>
                    </div>
                </div>

                <div class="space-y-2 text-center">
                    <flux:heading size="lg">{{ $this->modalConfig['title'] }}</flux:heading>
                    <flux:text>{{ $this->modalConfig['description'] }}</flux:text>
                </div>
            </div>

            @if ($showVerificationStep)
                <div class="space-y-6">
                    <div class="flex flex-col items-center space-y-3 justify-center">
                        <flux:otp
                            name="code"
                            wire:model="code"
                            length="6"
                            label="Código OTP"
                            label:sr-only
                            class="mx-auto"
                        />
                    </div>

                    <div class="flex items-center space-x-3">
                        <flux:button
                            variant="outline"
                            class="flex-1"
                            wire:click="resetVerification"
                        >
                            {{ __('Atrás') }}
                        </flux:button>

                        <flux:button
                            variant="primary"
                            class="flex-1"
                            wire:click="confirmTwoFactor"
                            x-bind:disabled="$wire.code.length < 6"
                        >
                            {{ __('Confirmar') }}
                        </flux:button>
                    </div>
                </div>
            @else
                @error('setupData')
                    <flux:callout variant="danger" icon="x-circle" heading="{{ $message }}"/>
                @enderror

                <div class="flex justify-center">
                    <div class="relative w-64 overflow-hidden border rounded-lg border-stone-200 dark:border-stone-700 aspect-square">
                        @empty($qrCodeSvg)
                            <div class="absolute inset-0 flex items-center justify-center bg-white dark:bg-stone-700 animate-pulse">
                                <flux:icon.loading/>
                            </div>
                        @else
                            <div x-data class="flex items-center justify-center h-full p-4">
                                <div
                                    class="bg-white p-3 rounded"
                                    :style="($flux.appearance === 'dark' || ($flux.appearance === 'system' && $flux.dark)) ? 'filter: invert(1) brightness(1.5)' : ''"
                                >
                                    {!! $qrCodeSvg !!}
                                </div>
                            </div>
                        @endempty
                    </div>
                </div>

                <div>
                    <flux:button
                        :disabled="$errors->has('setupData')"
                        variant="primary"
                        class="w-full"
                        wire:click="showVerificationIfNecessary"
                    >
                        {{ $this->modalConfig['buttonText'] }}
                    </flux:button>
                </div>

                <div class="space-y-4">
                    <div class="relative flex items-center justify-center w-full">
                        <div class="absolute inset-0 w-full h-px top-1/2 bg-stone-200 dark:bg-stone-600"></div>
                        <span class="relative px-2 text-sm bg-white dark:bg-stone-800 text-stone-600 dark:text-stone-400">
                            {{ __('o ingresa el código manualmente') }}
                        </span>
                    </div>

                    <div
                        class="flex items-center space-x-2"
                        x-data="{
                            copied: false,
                            async copy() {
                                try {
                                    await navigator.clipboard.writeText('{{ $manualSetupKey }}');
                                    this.copied = true;
                                    setTimeout(() => this.copied = false, 1500);
                                } catch (e) {
                                    console.warn('Could not copy to clipboard');
                                }
                            }
                        }"
                    >
                        <div class="flex items-stretch w-full border rounded-xl dark:border-stone-700">
                            @empty($manualSetupKey)
                                <div class="flex items-center justify-center w-full p-3 bg-stone-100 dark:bg-stone-700">
                                    <flux:icon.loading variant="mini"/>
                                </div>
                            @else
                                <input
                                    type="text"
                                    readonly
                                    value="{{ $manualSetupKey }}"
                                    class="w-full p-3 bg-transparent outline-none text-stone-900 dark:text-stone-100"
                                />

                                <button
                                    @click="copy()"
                                    class="px-3 transition-colors border-l cursor-pointer border-stone-200 dark:border-stone-600"
                                >
                                    <flux:icon.document-duplicate x-show="!copied" variant="outline"></flux:icon>
                                    <flux:icon.check
                                        x-show="copied"
                                        variant="solid"
                                        class="text-green-500"
                                    ></flux:icon>
                                </button>
                            @endempty
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </flux:modal>

    {{-- Close the settings-heading containers --}}
        </div>
    </div>
</div>
</section>
