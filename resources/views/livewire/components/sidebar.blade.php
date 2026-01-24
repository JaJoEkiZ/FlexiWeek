<div class="h-full flex flex-col">
    <!-- Header -->
    <div class="flex justify-between items-center mb-4">
        <div class="flex items-center gap-2">
            <!-- Mobile Close Button -->
            <button @click="sidebarOpen = false" class="md:hidden text-[#7b7b7b] hover:text-white transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <h2 class="text-xs font-bold text-[#7b7b7b] uppercase tracking-wider">EXPLORADOR</h2>
        </div>
        <button wire:click="openPeriodForm" class="text-[#d4d4d4] hover:text-white bg-[#333] hover:bg-[#444] rounded px-2 py-0.5 text-xs font-mono" title="Nueva Semana">
            + Nueva
        </button>
    </div>

    <!-- Period List -->
    <div class="space-y-1 flex-1 overflow-y-auto custom-scrollbar">
        @foreach($activePeriods as $period)
            <div class="period-drop-zone flex items-center group/item w-full px-3 py-2 rounded-sm transition-all border-l-2 text-sm
                {{ $selectedPeriodId == $period->id 
                    ? 'bg-[#37373d] border-[#007fd4] text-white' 
                    : 'border-transparent text-[#969696] hover:bg-[#2a2d2e] hover:text-[#d4d4d4]' }}"
                data-period-id="{{ $period->id }}">
                
                <button wire:click="selectPeriod({{ $period->id }})" 
                        @click="if(window.innerWidth < 768) sidebarOpen = false"
                        class="flex-1 text-left overflow-hidden">
                    <div class="font-medium truncate">{{ $period->name ?? 'Semana sin nombre' }}</div>
                    <div class="text-xs opacity-60 font-mono mt-0.5">
                        {{ \Carbon\Carbon::parse($period->start_date)->format('d/m') }} - 
                        {{ \Carbon\Carbon::parse($period->end_date)->format('d/m') }}
                    </div>
                </button>

                <button wire:click="openPeriodForm({{ $period->id }})" class="text-[#d4d4d4] hover:text-white p-1 rounded hover:bg-[#3c3c3c] transition-colors" title="Editar">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                </button>
            </div>
        @endforeach

        @if($pastPeriods->isNotEmpty())
            <div class="pt-4 border-t border-[#333] mt-4">
                <button wire:click="togglePastWeeks" class="w-full text-left text-xs text-[#7b7b7b] hover:text-[#d4d4d4] flex items-center gap-2 px-2 py-1 transition-colors group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform transition-transform {{ $showPastWeeks ? 'rotate-90' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span>Semanas Anteriores</span>
                    <span class="ml-auto text-[10px] bg-[#333] px-1.5 rounded-full group-hover:bg-[#444]">{{ $pastPeriods->count() }}</span>
                </button>

                @if($showPastWeeks)
                    <div class="mt-2 space-y-1 pl-2 border-l border-[#333] ml-3 transition-all">
                        @foreach($pastPeriods as $period)
                            <div class="period-drop-zone flex items-center group/item w-full px-3 py-2 rounded-sm transition-all text-sm
                                {{ $selectedPeriodId == $period->id 
                                    ? 'bg-[#37373d] text-white font-medium' 
                                    : 'text-[#969696] hover:bg-[#2a2d2e] hover:text-[#d4d4d4]' }}"
                                data-period-id="{{ $period->id }}">
                                
                                <button wire:click="selectPeriod({{ $period->id }})" 
                                        @click="if(window.innerWidth < 768) sidebarOpen = false"
                                        class="flex-1 text-left overflow-hidden">
                                    <div class="truncate">{{ $period->name ?? 'Semana sin nombre' }}</div>
                                    <div class="text-xs opacity-60 font-mono mt-0.5">
                                        {{ \Carbon\Carbon::parse($period->start_date)->format('d/m') }} - 
                                        {{ \Carbon\Carbon::parse($period->end_date)->format('d/m') }}
                                    </div>
                                </button>
    
                                <button wire:click="openPeriodForm({{ $period->id }})" class="text-[#7b7b7b] hover:text-white p-1 rounded hover:bg-[#3c3c3c] transition-colors opacity-0 group-hover/item:opacity-100" title="Editar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>