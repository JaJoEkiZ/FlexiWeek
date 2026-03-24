<div class="p-4 lg:p-8 space-y-6" wire:key="metrics-view">

    {{-- Selector de modo: Por Período / Por Rango --}}
    <div class="flex flex-col md:flex-row gap-4 items-start md:items-end">
        {{-- Toggle de modo --}}
        <div class="flex bg-[#252526] rounded border border-[#333] overflow-hidden">
            <button wire:click="switchMode('period')"
                    class="px-4 py-2 text-xs font-medium transition-all {{ $mode === 'period' ? 'bg-[#007fd4] text-white' : 'text-[#8b949e] hover:text-white hover:bg-[#333]' }}">
                📅 Por Período
            </button>
            <button wire:click="switchMode('range')"
                    class="px-4 py-2 text-xs font-medium transition-all {{ $mode === 'range' ? 'bg-[#007fd4] text-white' : 'text-[#8b949e] hover:text-white hover:bg-[#333]' }}">
                📊 Por Rango
            </button>
        </div>

        {{-- Selector según modo --}}
        @if($mode === 'period')
            <div class="flex items-center gap-2">
                <label class="text-xs text-[#7b7b7b]">Período:</label>
                <select wire:model.live="selectedPeriodId"
                        class="px-3 py-2 text-sm bg-[#3c3c3c] border border-[#333] rounded text-[#d4d4d4] focus:border-[#007fd4] focus:ring-[#007fd4] focus:outline-none">
                    <option value="">-- Seleccionar --</option>
                    @foreach($periods as $period)
                        <option value="{{ $period->id }}">{{ $period->name }} ({{ \Carbon\Carbon::parse($period->start_date)->format('d/m') }} - {{ \Carbon\Carbon::parse($period->end_date)->format('d/m/Y') }})</option>
                    @endforeach
                </select>
            </div>
        @else
            <div class="flex items-center gap-2 flex-wrap">
                <label class="text-xs text-[#7b7b7b]">Desde:</label>
                <input type="date" wire:model.live="rangeStart"
                       class="px-3 py-2 text-sm bg-[#3c3c3c] border border-[#333] rounded text-[#d4d4d4] focus:border-[#007fd4] focus:ring-[#007fd4] focus:outline-none">
                <label class="text-xs text-[#7b7b7b]">Hasta:</label>
                <input type="date" wire:model.live="rangeEnd"
                       class="px-3 py-2 text-sm bg-[#3c3c3c] border border-[#333] rounded text-[#d4d4d4] focus:border-[#007fd4] focus:ring-[#007fd4] focus:outline-none">
                @if($mode === 'range' && $metrics['periodsCount'] > 0)
                    <span class="text-[10px] text-[#4ec9b0] bg-[#1e3a23] px-2 py-1 rounded border border-[#2ea043]">
                        {{ $metrics['periodsCount'] }} período(s) encontrado(s)
                    </span>
                @endif
            </div>
        @endif
    </div>

    {{-- Cards de resumen --}}
    @if($metrics['total'] > 0)
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
            {{-- Total --}}
            <div class="bg-[#252526] border border-[#333] rounded-lg p-4 text-center transition-all duration-300 hover:border-[#007fd4] hover:shadow-lg hover:shadow-[#007fd4]/10">
                <div class="text-2xl font-bold text-white transition-all duration-500">{{ $metrics['total'] }}</div>
                <div class="text-[10px] text-[#7b7b7b] mt-1 uppercase tracking-wider">Total</div>
            </div>
            {{-- Completadas --}}
            <div class="bg-[#252526] border border-[#333] rounded-lg p-4 text-center transition-all duration-300 hover:border-[#4ec9b0] hover:shadow-lg hover:shadow-[#4ec9b0]/10">
                <div class="text-2xl font-bold text-[#4ec9b0] transition-all duration-500">{{ $metrics['completed'] }}</div>
                <div class="text-[10px] text-[#7b7b7b] mt-1 uppercase tracking-wider">Completadas</div>
            </div>
            {{-- En Curso --}}
            <div class="bg-[#252526] border border-[#333] rounded-lg p-4 text-center transition-all duration-300 hover:border-[#79c0ff] hover:shadow-lg hover:shadow-[#79c0ff]/10">
                <div class="text-2xl font-bold text-[#79c0ff] transition-all duration-500">{{ $metrics['inProgress'] }}</div>
                <div class="text-[10px] text-[#7b7b7b] mt-1 uppercase tracking-wider">En Curso</div>
            </div>
            {{-- Pausadas --}}
            <div class="bg-[#252526] border border-[#333] rounded-lg p-4 text-center transition-all duration-300 hover:border-[#d29922] hover:shadow-lg hover:shadow-[#d29922]/10">
                <div class="text-2xl font-bold text-[#d29922] transition-all duration-500">{{ $metrics['paused'] }}</div>
                <div class="text-[10px] text-[#7b7b7b] mt-1 uppercase tracking-wider">Pausadas</div>
            </div>
            {{-- Pendientes --}}
            <div class="bg-[#252526] border border-[#333] rounded-lg p-4 text-center transition-all duration-300 hover:border-[#8b949e] hover:shadow-lg hover:shadow-[#8b949e]/10">
                <div class="text-2xl font-bold text-[#8b949e] transition-all duration-500">{{ $metrics['pending'] }}</div>
                <div class="text-[10px] text-[#7b7b7b] mt-1 uppercase tracking-wider">Pendientes</div>
            </div>
            {{-- Canceladas --}}
            <div class="bg-[#252526] border border-[#333] rounded-lg p-4 text-center transition-all duration-300 hover:border-[#f85149] hover:shadow-lg hover:shadow-[#f85149]/10">
                <div class="text-2xl font-bold text-[#f85149] transition-all duration-500">{{ $metrics['cancelled'] }}</div>
                <div class="text-[10px] text-[#7b7b7b] mt-1 uppercase tracking-wider">Canceladas</div>
            </div>
        </div>

        {{-- Tiempo y Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Tiempo estimado vs invertido --}}
            <div class="bg-[#252526] border border-[#333] rounded-lg p-5">
                <h3 class="text-xs text-[#7b7b7b] uppercase tracking-wider mb-3">Tiempo Total</h3>
                <div class="space-y-3">
                    <div class="space-y-1">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-[#8b949e]">Estimado</span>
                            <span class="text-sm font-mono text-[#9cdcfe]">{{ intdiv($metrics['totalEstimated'], 60) }}h {{ $metrics['totalEstimated'] % 60 }}m</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-[#8b949e]">Invertido</span>
                            <span class="text-sm font-mono {{ $metrics['totalSpent'] > $metrics['totalEstimated'] ? 'text-[#f85149]' : 'text-[#4ec9b0]' }}">{{ intdiv($metrics['totalSpent'], 60) }}h {{ $metrics['totalSpent'] % 60 }}m</span>
                        </div>
                        @if($metrics['totalEstimated'] > 0)
                            <div class="w-full bg-[#3c3c3c] rounded-full h-1.5 mt-2 mb-1 overflow-hidden">
                                <div class="h-1.5 rounded-full transition-all duration-700 {{ ($metrics['totalSpent'] / $metrics['totalEstimated']) > 1 ? 'bg-[#f85149]' : 'bg-[#007fd4]' }}"
                                     style="width: {{ min(($metrics['totalSpent'] / $metrics['totalEstimated']) * 100, 100) }}%"></div>
                            </div>
                        @endif
                    </div>
                    
                    @if($metrics['totalOvertime'] > 0 || $metrics['totalRemaining'] > 0 || ($metrics['totalGained'] > 0 && $metrics['isPeriodOver']))
                        <div class="pt-2 border-t border-[#333] space-y-2">
                            @if($metrics['totalOvertime'] > 0)
                                <div class="flex justify-between items-center group cursor-pointer hover:bg-[#333] -mx-2 px-2 py-1 rounded transition-colors"
                                     wire:click="$dispatch('openMetricsTaskDetails', { type: 'overtime', periodId: {{ $mode === 'period' ? $selectedPeriodId ?? 'null' : 'null' }}, rangeStart: '{{ $mode === 'range' ? $rangeStart : '' }}', rangeEnd: '{{ $mode === 'range' ? $rangeEnd : '' }}' })">
                                    <span class="text-[11px] text-[#7b7b7b] uppercase group-hover:text-white transition-colors">🔴 Tiempo Excedido</span>
                                    <span class="text-sm font-mono font-medium text-[#f85149]">{{ intdiv($metrics['totalOvertime'], 60) }}h {{ $metrics['totalOvertime'] % 60 }}m</span>
                                </div>
                            @endif
                            @if($metrics['totalRemaining'] > 0 && !$metrics['isPeriodOver'])
                                <div class="flex justify-between items-center -mx-2 px-2 py-1 rounded">
                                    <span class="text-[11px] text-[#7b7b7b] uppercase">⏳ Tiempo Restante</span>
                                    <span class="text-sm font-mono font-medium text-[#569cd6]">{{ intdiv($metrics['totalRemaining'], 60) }}h {{ $metrics['totalRemaining'] % 60 }}m</span>
                                </div>
                            @endif
                            @if($metrics['totalGained'] > 0 && $metrics['isPeriodOver'])
                                <div class="flex justify-between items-center group cursor-pointer hover:bg-[#333] -mx-2 px-2 py-1 rounded transition-colors"
                                     wire:click="$dispatch('openMetricsTaskDetails', { type: 'gained', periodId: {{ $mode === 'period' ? $selectedPeriodId ?? 'null' : 'null' }}, rangeStart: '{{ $mode === 'range' ? $rangeStart : '' }}', rangeEnd: '{{ $mode === 'range' ? $rangeEnd : '' }}' })">
                                    <span class="text-[11px] text-[#7b7b7b] uppercase group-hover:text-white transition-colors">🟢 Tiempo Ganado</span>
                                    <span class="text-sm font-mono font-medium text-[#4ec9b0]">{{ intdiv($metrics['totalGained'], 60) }}h {{ $metrics['totalGained'] % 60 }}m</span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Tasa de completación --}}
            <div class="bg-[#252526] border border-[#333] rounded-lg p-5">
                <h3 class="text-xs text-[#7b7b7b] uppercase tracking-wider mb-3">Tasa de Completación</h3>
                <div class="flex items-center justify-center">
                    <div class="relative w-24 h-24">
                        <svg class="w-24 h-24 transform -rotate-90" viewBox="0 0 36 36">
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                  fill="none" stroke="#3c3c3c" stroke-width="3"/>
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                  fill="none" stroke="#4ec9b0" stroke-width="3"
                                  stroke-dasharray="{{ $metrics['completionRate'] }}, 100"
                                  class="transition-all duration-1000"/>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-lg font-bold text-white">{{ $metrics['completionRate'] }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stats adicionales --}}
            <div class="bg-[#252526] border border-[#333] rounded-lg p-5">
                <h3 class="text-xs text-[#7b7b7b] uppercase tracking-wider mb-3">Datos Adicionales</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-[#8b949e]">Promedio por tarea</span>
                        <span class="text-sm font-mono text-[#ce9178]">{{ intdiv($metrics['avgTimePerTask'], 60) }}h {{ $metrics['avgTimePerTask'] % 60 }}m</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-[#8b949e]">Excedieron tiempo</span>
                        <span class="text-sm font-mono {{ $metrics['overTimeCount'] > 0 ? 'text-[#f85149]' : 'text-[#4ec9b0]' }}">{{ $metrics['overTimeCount'] }}</span>
                    </div>
                    @if($mode === 'range')
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-[#8b949e]">Períodos incluidos</span>
                            <span class="text-sm font-mono text-[#79c0ff]">{{ $metrics['periodsCount'] }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Gráficos --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            {{-- Doughnut: Estado de tareas --}}
            <div class="bg-[#252526] border border-[#333] rounded-lg p-5">
                <h3 class="text-xs text-[#7b7b7b] uppercase tracking-wider mb-4">Distribución de Estados</h3>
                <div class="flex justify-center" style="height: 280px;">
                    <canvas id="statusChart" wire:ignore></canvas>
                </div>
            </div>

            {{-- Barras: Estimado vs Invertido --}}
            <div class="bg-[#252526] border border-[#333] rounded-lg p-5">
                <h3 class="text-xs text-[#7b7b7b] uppercase tracking-wider mb-4">Estimado vs Invertido (min)</h3>
                <div style="height: 280px;">
                    <canvas id="timeChart" wire:ignore></canvas>
                </div>
            </div>
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-20 text-[#7b7b7b]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <p class="text-sm">Selecciona un período o rango de fechas para ver las métricas</p>
        </div>
    @endif



    @script
    <script>
        (() => {
            let statusChartInstance = null;
            let timeChartInstance = null;

            function renderCharts(metrics) {
                if (typeof Chart === 'undefined') return;
                if (!metrics || !metrics.statusChart || !metrics.timeChart) return;

                try {
                    const statusCtx = document.getElementById('statusChart');
                    if (statusCtx && metrics.statusChart.data && metrics.statusChart.data.some(v => v > 0)) {
                        if (statusChartInstance) statusChartInstance.destroy();
                        statusChartInstance = new Chart(statusCtx, {
                            type: 'doughnut',
                            data: {
                                labels: metrics.statusChart.labels,
                                datasets: [{
                                    data: metrics.statusChart.data,
                                    backgroundColor: metrics.statusChart.colors,
                                    borderColor: '#1e1e1e',
                                    borderWidth: 3,
                                    hoverBorderWidth: 0,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cutout: '60%',
                                animation: { animateScale: true, animateRotate: true, duration: 800, easing: 'easeOutQuart' },
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: { color: '#8b949e', padding: 15, font: { size: 11 }, usePointStyle: true, pointStyleWidth: 10 }
                                    }
                                }
                            }
                        });
                    }

                    const timeCtx = document.getElementById('timeChart');
                    if (timeCtx && metrics.timeChart.labels && metrics.timeChart.labels.length > 0) {
                        if (timeChartInstance) timeChartInstance.destroy();
                        timeChartInstance = new Chart(timeCtx, {
                            type: 'bar',
                            data: {
                                labels: metrics.timeChart.labels.map(l => l.length > 15 ? l.substring(0, 15) + '...' : l),
                                datasets: [
                                    { label: 'Estimado', data: metrics.timeChart.estimated, backgroundColor: 'rgba(0, 127, 212, 0.6)', borderColor: '#007fd4', borderWidth: 1, borderRadius: 3 },
                                    { label: 'Invertido', data: metrics.timeChart.spent, backgroundColor: 'rgba(78, 201, 176, 0.6)', borderColor: '#4ec9b0', borderWidth: 1, borderRadius: 3 }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                animation: { duration: 800, easing: 'easeOutQuart' },
                                scales: {
                                    x: { ticks: { color: '#7b7b7b', font: { size: 10 } }, grid: { color: 'rgba(51, 51, 51, 0.5)' } },
                                    y: { ticks: { color: '#7b7b7b', font: { size: 10 } }, grid: { color: 'rgba(51, 51, 51, 0.5)' }, beginAtZero: true }
                                },
                                plugins: {
                                    legend: {
                                        labels: { color: '#8b949e', font: { size: 11 }, usePointStyle: true, pointStyleWidth: 10 }
                                    }
                                }
                            }
                        });
                    }
                } catch (e) {
                    console.warn('Error rendering charts:', e);
                }
            }

            const initialMetrics = @json($metrics);
            $wire.on('metricsUpdated', (data) => {
                setTimeout(() => renderCharts(data[0]), 100);
            });
            setTimeout(() => renderCharts(initialMetrics), 200);
        })()
    </script>
    @endscript
</div>
