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
                    <input wire:model="name" type="text" required autofocus autocomplete="name" class="bg-white/10 backdrop-blur-md rounded-md border border-white/20 shadow-lg" placeholder="Tu nombre..." />
                    @error('name') <p class="mt-1 text-xs text-[#f85149] font-medium">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="settings-label">{{ __('Correo Electrónico') }}</label>
                    <input wire:model="email" type="email" required autocomplete="email" class="bg-white/10 backdrop-blur-md rounded-md border border-white/20 shadow-lg" placeholder="tu@email.com" />
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
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-[#007fd4] border border-[#1a93e3] rounded-md hover:bg-[#006bb3] hover:shadow-lg hover:shadow-[#007fd4]/30 active:scale-95 transition-all">{{ __('Guardar Cambios') }}</button>
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
                    {{-- Custom dropdown de timezone --}}
                    <label class="settings-label">{{ __('Selecciona tu región') }}</label>
                    <div class="relative" x-data="{
                        open: false,
                        search: '',
                        selected: '{{ str_replace('_', ' ', $timezone) }}',
                        selectedValue: '{{ $timezone }}',
                        timezones: {{ json_encode(collect($timezones)->map(fn($list, $region) => ['region' => $region, 'zones' => $list])->values()) }},
                        get filtered() {
                            if (!this.search) return this.timezones;
                            const q = this.search.toLowerCase();
                            return this.timezones.map(g => ({
                                region: g.region,
                                zones: g.zones.filter(z => z.toLowerCase().replace(/_/g,' ').includes(q))
                            })).filter(g => g.zones.length > 0);
                        },
                        select(val) {
                            this.selectedValue = val;
                            this.selected = val.replace(/_/g,' ');
                            this.open = false;
                            this.search = '';
                            this.$refs.hiddenInput.value = val;
                            this.$refs.hiddenInput.dispatchEvent(new Event('input'));
                            this.$refs.hiddenInput.dispatchEvent(new Event('change'));
                        }
                    }" @click.outside="open = false">

                        {{-- Hidden input para Livewire --}}
                        <input type="hidden" wire:model="timezone" x-ref="hiddenInput" :value="selectedValue">

                        {{-- Trigger button --}}
                        <button type="button"
                                @click="open = !open"
                                class="w-full flex items-center justify-between px-3 py-2 text-sm rounded-md border border-white/20 bg-white/10 backdrop-blur-md shadow-lg text-[#d4d4d4] hover:border-white/30 transition-colors">
                            <span x-text="selected" class="truncate"></span>
                            <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 ml-2 shrink-0 text-[#8b949e] transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        {{-- Dropdown panel --}}
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-1"
                             class="absolute top-full left-0 right-0 mt-1 z-50 rounded-md border border-white/20 bg-[#252526]/95 backdrop-blur-md shadow-2xl overflow-hidden">

                            {{-- Search box --}}
                            <div class="p-2 border-b border-white/10">
                                <input type="text"
                                       x-model="search"
                                       placeholder="Buscar zona horaria..."
                                       class="w-full px-3 py-1.5 text-xs bg-white/10 border border-white/20 rounded text-[#d4d4d4] placeholder-[#555] outline-none focus:border-[#007fd4] transition-colors"
                                       @click.stop>
                            </div>

                            {{-- Options list --}}
                            <div class="overflow-y-auto" style="max-height: 220px;">
                                <template x-for="group in filtered" :key="group.region">
                                    <div>
                                        <div class="px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-[#555] bg-black/20 sticky top-0"
                                             x-text="group.region"></div>
                                        <template x-for="tz in group.zones" :key="tz">
                                            <button type="button"
                                                    @click="select(tz)"
                                                    class="w-full text-left px-4 py-1.5 text-xs transition-colors hover:bg-white/10"
                                                    :class="selectedValue === tz ? 'text-[#007fd4] bg-white/5 font-semibold' : 'text-[#c4c4c4]'"
                                                    x-text="tz.replace(/_/g,' ')">
                                            </button>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="filtered.length === 0">
                                    <div class="px-4 py-3 text-xs text-[#555] text-center">Sin resultados</div>
                                </template>
                            </div>
                        </div>
                    </div>
                    @error('timezone') <p class="mt-1 text-xs text-[#f85149] font-medium">{{ $message }}</p> @enderror

                </div>

                <div class="flex items-center gap-4 pt-2">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-[#007fd4] border border-[#1a93e3] rounded-md hover:bg-[#006bb3] hover:shadow-lg hover:shadow-[#007fd4]/30 active:scale-95 transition-all">{{ __('Actualizar Zona Horaria') }}</button>
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

    {{-- Close the content + outer layout containers from settings-heading --}}
        </div>
    </div>
</div>
</section>
