<?php

use Livewire\Volt\Component;

new class extends Component
{
    //
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Apariencia')" :subheading="__('Actualiza la configuración de apariencia de tu cuenta')">
        <div class="flex gap-3"
             x-data="{
                 current: localStorage.getItem('flux.appearance') ?? 'system',
                 setAppearance(value) {
                     this.current = value;
                     if (window.Flux) {
                         window.Flux.appearance = value;
                     } else {
                         localStorage.setItem('flux.appearance', value);
                         const html = document.documentElement;
                         if (value === 'dark') {
                             html.classList.add('dark');
                         } else if (value === 'light') {
                             html.classList.remove('dark');
                         } else {
                             window.matchMedia('(prefers-color-scheme: dark)').matches
                                 ? html.classList.add('dark')
                                 : html.classList.remove('dark');
                         }
                     }
                 }
             }">
            <button @click="setAppearance('light')"
                    :class="current === 'light'
                        ? 'bg-[#007fd4] text-white border-[#007fd4]'
                        : 'dark:bg-[#3c3c3c] bg-gray-100 dark:text-[#8b949e] text-gray-600 dark:border-[#555] border-gray-300 dark:hover:bg-[#333] hover:bg-gray-200 dark:hover:text-white hover:text-gray-900'"
                    class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium border rounded-md transition-all">
                ☀️ {{ __('Claro') }}
            </button>
            <button @click="setAppearance('dark')"
                    :class="current === 'dark'
                        ? 'bg-[#007fd4] text-white border-[#007fd4]'
                        : 'dark:bg-[#3c3c3c] bg-gray-100 dark:text-[#8b949e] text-gray-600 dark:border-[#555] border-gray-300 dark:hover:bg-[#333] hover:bg-gray-200 dark:hover:text-white hover:text-gray-900'"
                    class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium border rounded-md transition-all">
                🌙 {{ __('Oscuro') }}
            </button>
            <button @click="setAppearance('system')"
                    :class="current === 'system'
                        ? 'bg-[#007fd4] text-white border-[#007fd4]'
                        : 'dark:bg-[#3c3c3c] bg-gray-100 dark:text-[#8b949e] text-gray-600 dark:border-[#555] border-gray-300 dark:hover:bg-[#333] hover:bg-gray-200 dark:hover:text-white hover:text-gray-900'"
                    class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium border rounded-md transition-all">
                🖥️ {{ __('Sistema') }}
            </button>
        </div>

        <p class="mt-4 text-xs dark:text-[#7b7b7b] text-gray-500">
            <span class="text-[#d29922]">⚠️</span>
            {{ __('Nota: El panel principal siempre mantiene el tema oscuro.') }}
        </p>
    </x-settings.layout>

    {{-- Close the settings-heading containers --}}
        </div>
    </div>
</div>
</section>
