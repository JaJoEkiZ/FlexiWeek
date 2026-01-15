<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Regístrate - FlexiWeek</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxStyles
    <script>
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.classList.add('dark');
        }
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
            if (event.matches) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        });
    </script>
</head>
<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex min-h-screen flex-col items-center justify-center p-6 lg:p-8">
    <div class="flex w-full grow items-center justify-center">
        
        <main class="w-full max-w-md">
            
            <div class="bg-zinc-100 dark:bg-zinc-900 relative rounded-xl shadow-lg border border-zinc-200 dark:border-zinc-800 w-full overflow-hidden flex flex-col items-center justify-center gap-4 p-8">
                
                <div class="absolute inset-0 rounded-xl pointer-events-none shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.05)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed10]"></div>

                <img src="{{ asset('images/flexiweek-Iso.png') }}" alt="Logo Icon" class="w-[220px] h-auto mb-4 drop-shadow-sm z-10 relative block mx-auto">
                
                <div class="w-full max-w-xs relative z-50 mx-auto text-center">
                    
                    <x-auth-session-status class="text-center mb-4" :status="session('status')" />

                    <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-4 text-left">
                        @csrf
                        <!-- Name -->
                        <flux:input name="name" :label="__('Nombre')" :value="old('name')" type="text" required autofocus autocomplete="name" :placeholder="__('Nombre completo')" />

                        <!-- Email Address -->
                        <flux:input name="email" :label="__('Correo electrónico')" :value="old('email')" type="email" required autocomplete="email" placeholder="email@example.com" />

                        <!-- Password -->
                        <flux:input name="password" :label="__('Contraseña')" type="password" required autocomplete="new-password" :placeholder="__('Contraseña')" viewable />

                        <!-- Confirm Password -->
                        <flux:input name="password_confirmation" :label="__('Confirmar contraseña')" type="password" required autocomplete="new-password" :placeholder="__('Confirmar contraseña')" viewable />

                        <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">{{ __('Crear cuenta') }}</flux:button>
                    </form>

                    <div class="mt-4 space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
                        <span>{{ __('¿Ya tienes una cuenta?') }}</span>
                        <flux:link :href="route('login')" wire:navigate>{{ __('Iniciar sesión') }}</flux:link>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    @fluxScripts
</body>
</html>
