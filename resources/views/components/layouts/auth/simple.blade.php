<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
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
                    
                    <a href="{{ url('/') }}" class="mx-auto block" wire:navigate>
                        <img src="{{ asset('images/flexiweek-Iso.png') }}" alt="Logo Icon" class="w-[200px] h-auto mb-4 drop-shadow-sm z-10 relative">
                    </a>
                    
                    <div class="w-full max-w-sm relative z-50 mx-auto text-left dark:text-zinc-300">
                        {{ $slot }}
                    </div>
                </div>
            </main>
        </div>
        @fluxScripts
    </body>
</html>
