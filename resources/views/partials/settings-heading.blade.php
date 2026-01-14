<div class="relative mb-6 w-full pt-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl" level="1">{{ __('Configuración') }}</flux:heading>
        <a href="{{ route('planner') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            {{ __('Volver al Panel') }}
        </a>
    </div>
    <flux:subheading size="lg" class="mb-6">{{ __('Administra tu perfil y configuración de cuenta') }}</flux:subheading>
    <flux:separator variant="subtle" />
</div>
