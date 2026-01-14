<div class="flex items-start max-md:flex-col bg-[#1e1e1e] text-[#d4d4d4]">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist aria-label="{{ __('Configuración') }}" class="space-y-1">
            <flux:navlist.item :href="route('profile.edit')" wire:navigate class="hover:bg-[#333] text-[#d4d4d4] rounded px-2 py-1 transition-colors">{{ __('Perfil') }}</flux:navlist.item>
            <flux:navlist.item :href="route('user-password.edit')" wire:navigate class="hover:bg-[#333] text-[#d4d4d4] rounded px-2 py-1 transition-colors">{{ __('Contraseña') }}</flux:navlist.item>
            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <flux:navlist.item :href="route('two-factor.show')" wire:navigate class="hover:bg-[#333] text-[#d4d4d4] rounded px-2 py-1 transition-colors">{{ __('Autenticación de Dos Factores') }}</flux:navlist.item>
            @endif
            <flux:navlist.item :href="route('appearance.edit')" wire:navigate class="hover:bg-[#333] text-[#d4d4d4] rounded px-2 py-1 transition-colors">{{ __('Apariencia') }}</flux:navlist.item>
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden border-[#333]" />

    <div class="flex-1 self-stretch max-md:pt-6 p-6">
        <flux:heading class="text-white text-xl font-light mb-2">{{ $heading ?? '' }}</flux:heading>
        <flux:subheading class="text-[#7b7b7b] text-sm mb-6">{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>
