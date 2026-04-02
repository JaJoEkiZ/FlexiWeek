@props([
    'selectedPeriodId' => null,
    'currentPeriod'    => null,
    'activeTab'        => 'tasks',
])

<div
    x-data="{ sidebarOpen: window.innerWidth >= 768, ctxMenu: { show: false, x: 0, y: 0, taskId: null } }"
    @click="ctxMenu.show = false"
    class="flex h-screen bg-[#1e1e1e] text-[#d4d4d4] font-sans antialiased relative"
>

    {{-- Overlay móvil --}}
    <div x-show="sidebarOpen"
         @click="sidebarOpen = false"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-50 z-30 md:hidden">
    </div>

    {{-- Sidebar --}}
    <div :class="sidebarOpen ? 'translate-x-0 shadow-xl' : '-translate-x-full'"
         class="fixed inset-y-0 left-0 z-40 w-64 bg-[#252526] border-r border-[#333] p-4 overflow-y-auto custom-scrollbar transform transition-transform duration-300 ease-in-out">
        <livewire:components.sidebar :selectedPeriodId="$selectedPeriodId" wire:key="sidebar-main-component" />
    </div>

    {{-- Contenido principal --}}
    <div :class="sidebarOpen ? 'md:ml-64' : ''"
         class="flex-1 flex flex-col h-full bg-[#1e1e1e] w-full transition-all duration-300 ease-in-out overflow-hidden">

        @if($currentPeriod)

            {{-- Navbar --}}
            <div class="flex-shrink-0 z-10 bg-[#1e1e1e]">
                <livewire:components.task-navbar :selectedPeriodId="$currentPeriod->id" wire:key="navbar-{{ $currentPeriod->id }}" />
            </div>

            {{-- Tabs — ahora son links normales con wire:navigate --}}
            <div class="flex-shrink-0 bg-[#1e1e1e] px-3 lg:px-8 pt-3">
                <div class="inline-flex bg-[#252526] rounded border border-[#333] overflow-hidden shadow-sm">
                    <a href="{{ route('planner', $currentPeriod->id) }}"
                       wire:navigate
                       class="px-3 md:px-4 py-1.5 md:py-2 text-[10px] md:text-xs font-medium transition-all flex items-center gap-1
                       {{ $activeTab === 'tasks' ? 'bg-[#007fd4] text-white' : 'text-[#8b949e] hover:text-white hover:bg-[#333]' }}">
                        📋 Tareas
                    </a>
                    <a href="{{ route('metrics', $currentPeriod->id) }}"
                       wire:navigate
                       class="px-3 md:px-4 py-1.5 md:py-2 text-[10px] md:text-xs font-medium transition-all flex items-center gap-1
                       {{ $activeTab === 'metrics' ? 'bg-[#007fd4] text-white' : 'text-[#8b949e] hover:text-white hover:bg-[#333]' }}">
                        📊 Métricas
                    </a>
                    <a href="{{ route('pizarra') }}"
                       wire:navigate
                       class="px-3 md:px-4 py-1.5 md:py-2 text-[10px] md:text-xs font-medium transition-all flex items-center gap-1
                       {{ $activeTab === 'board' ? 'bg-[#007fd4] text-white' : 'text-[#8b949e] hover:text-white hover:bg-[#333]' }}">
                        🎨 Pizarra
                    </a>
                </div>
            </div>

            {{-- Slot: acá entra el contenido de cada vista --}}
            <div class="flex-1 {{ $activeTab === 'board' ? 'overflow-hidden' : 'overflow-y-auto custom-scrollbar p-3 lg:p-8 pb-24 lg:pb-8' }}">
                {{ $slot }}
            </div>

        @else
            <div class="flex flex-col items-center justify-center h-full text-[#7b7b7b]">
                <div class="text-6xl mb-4 opacity-20">No hay semanas activas</div>
                <p class="font-mono text-sm">Genera una semana para comenzar...</p>
            </div>
        @endif

    </div>

    {{-- Notificaciones Toast Globales --}}
    <div x-data="{ toasts: [] }"
         @toast.window="
            let t = { id: Date.now(), message: $event.detail.message, type: $event.detail.type || 'info' };
            toasts.push(t);
            setTimeout(() => { toasts = toasts.filter(toast => toast.id !== t.id) }, 3000);
         "
         class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 pointer-events-none"
         style="z-index: 1000;">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="translate-y-4 opacity-0"
                 x-transition:enter-end="translate-y-0 opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="min-w-[250px] bg-[#252526] border border-l-4 shadow-xl rounded px-4 py-3 flex items-start gap-2 pointer-events-auto"
                 :class="{
                     'border-l-[#007fd4] border-y-[#333] border-r-[#333]': toast.type === 'info',
                     'border-l-[#2ea043] border-y-[#333] border-r-[#333]': toast.type === 'success',
                     'border-l-[#d29922] border-y-[#333] border-r-[#333]': toast.type === 'warning',
                     'border-l-[#da3633] border-y-[#333] border-r-[#333]': toast.type === 'error'
                 }">
                 <div class="mt-0.5">
                    <template x-if="toast.type === 'success'">
                        <svg class="w-4 h-4 text-[#2ea043]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </template>
                    <template x-if="toast.type === 'info'">
                        <svg class="w-4 h-4 text-[#007fd4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </template>
                    <template x-if="toast.type === 'warning'">
                        <svg class="w-4 h-4 text-[#d29922]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </template>
                    <template x-if="toast.type === 'error'">
                        <svg class="w-4 h-4 text-[#da3633]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </template>
                 </div>
                <span class="text-sm font-medium text-[#d4d4d4]" x-text="toast.message"></span>
            </div>
        </template>
    </div>

    {{-- Modales globales --}}
    <livewire:modals.task-form />
    <livewire:modals.period-form />
    <livewire:modals.task-details />
    <livewire:modals.duplicate-task />
    <livewire:modals.metrics-task-details />

    {{-- Menú contextual --}}
    <div x-show="ctxMenu.show"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         :style="`position: fixed; left: ${ctxMenu.x}px; top: ${ctxMenu.y}px; z-index: 100;`"
         @click.away="ctxMenu.show = false"
         class="bg-[#252526] border border-[#333] rounded shadow-xl py-1 min-w-[160px]">
        <button @click="$dispatch('openDuplicateTask', { taskId: ctxMenu.taskId }); ctxMenu.show = false"
                class="w-full text-left px-4 py-2 text-sm text-[#d4d4d4] hover:bg-[#094771] flex items-center gap-2 transition-colors">
            📋 Duplicar
        </button>
        <button @click="$wire.sendToPizarra(ctxMenu.taskId); ctxMenu.show = false"
                class="w-full text-left px-4 py-2 text-sm text-[#007fd4] hover:bg-[#152e42] flex items-center gap-2 transition-colors">
            🎨 Enviar a pizarra
        </button>
        <button @click="$wire.finishTask(ctxMenu.taskId); ctxMenu.show = false"
                class="w-full text-left px-4 py-2 text-sm text-[#7ee787] hover:bg-[#1e3a23] flex items-center gap-2 transition-colors">
            ✓ Finalizar tarea
        </button>
        <div class="border-t border-[#333] my-1"></div>
        <button @click="$wire.cancelTask(ctxMenu.taskId); ctxMenu.show = false"
                class="w-full text-left px-4 py-2 text-sm text-[#f85149] hover:bg-[#3b1219] flex items-center gap-2 transition-colors">
            ✕ Cancelar tarea
        </button>
        <button @click="$wire.reactivateTask(ctxMenu.taskId); ctxMenu.show = false"
                class="w-full text-left px-4 py-2 text-sm text-[#4ec9b0] hover:bg-[#1e3a23] flex items-center gap-2 transition-colors">
            ↩ Reactivar tarea
        </button>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 10px; background-color: #1e1e1e; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #424242; border-radius: 5px; border: 2px solid #1e1e1e; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #4f4f4f; }
        .sortable-ghost { opacity: 0.5 !important; background-color: #1a1a1a !important; }
        .sortable-drag { opacity: 0.8 !important; box-shadow: 0 5px 15px rgba(0,0,0,0.3) !important; transform: scale(1.02); }
        #tasks-tbody tr, #mobile-tasks-container > div { transition: transform 0.2s ease, background-color 0.2s ease; }
        .period-drop-zone > tr, .period-drop-zone > .sortable-ghost, .period-drop-zone > .sortable-fallback { display: none !important; }
        @keyframes readyToReceive {
            0%, 100% { background-color: rgba(34,197,94,0.1); border-left-color: #22c55e; }
            50% { background-color: rgba(34,197,94,0.25); border-left-color: #4ade80; }
        }
        .period-drop-zone.drag-over-zone { animation: readyToReceive 0.8s ease-in-out infinite !important; border-left-width: 3px !important; transform: scale(1.02) !important; z-index: 10 !important; }
        @keyframes receiveTask {
            0% { background-color: rgba(34,197,94,0.4); }
            100% { background-color: transparent; }
        }
        .period-drop-zone.task-received { animation: receiveTask 0.6s ease-out forwards; }
    </style>

</div>