<?php

use Livewire\Volt\Component;

new class extends Component
{
    //
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Apariencia')" :subheading="__('FlexiWeek está optimizado para una experiencia oscura premium')">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6"
             x-data="{
                 current: 'dark',
                 setAppearance(value) {
                     // Always dark for now
                     this.current = 'dark';
                 }
             }">
            
            {{-- Dark Mode (Selected) --}}
            <button @click="setAppearance('dark')"
                    class="flex flex-col items-center gap-4 p-5 rounded-2xl border-2 transition-all group relative overflow-hidden border-[#007fd4] bg-[#007fd4]/5 ring-4 ring-[#007fd4]/10">
                <div class="w-full aspect-video rounded-xl bg-[#1e1e1e] border border-[#333] flex items-center justify-center shadow-lg transform group-hover:scale-[1.02] transition-transform text-white">
                    <div class="w-16 h-3 bg-[#333] rounded-full"></div>
                </div>
                <span class="text-sm font-bold flex items-center gap-2 text-[#007fd4]" style="color: var(--settings-heading-text)">
                    🌙 {{ __('Oscuro (Predefinido)') }}
                </span>
            </button>

            {{-- Others (Disabled/Placeholder) --}}
            <div class="flex flex-col items-center gap-4 p-5 rounded-2xl border-2 border-[var(--settings-card-border)] opacity-40 grayscale">
                <div class="w-full aspect-video rounded-xl bg-white border border-gray-100 flex items-center justify-center shadow-lg">
                    <div class="w-16 h-3 bg-gray-100 rounded-full text-black">?</div>
                </div>
                <span class="text-sm font-bold flex items-center gap-2" style="color: var(--settings-heading-text)">
                    ☀️ {{ __('Claro') }}
                </span>
            </div>

            <div class="flex flex-col items-center gap-4 p-5 rounded-2xl border-2 border-[var(--settings-card-border)] opacity-40 grayscale">
                <div class="w-full aspect-video rounded-xl bg-gradient-to-br from-white via-gray-100 to-[#1e1e1e] border border-gray-200 dark:border-[#333] flex items-center justify-center shadow-lg">
                    <div class="w-16 h-3 bg-gray-400/30 rounded-full"></div>
                </div>
                <span class="text-sm font-bold flex items-center gap-2" style="color: var(--settings-heading-text)">
                    🖥️ {{ __('Sistema') }}
                </span>
            </div>
        </div>

        <div class="mt-8 p-4 rounded-lg bg-blue-500/10 border border-blue-500/20">
            <p class="text-xs font-medium text-[#007fd4] flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                </svg>
    </div>
</div>
</section>
