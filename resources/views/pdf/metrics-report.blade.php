<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Informe de Métricas – FlexiWeek</title>
    <style>
        /* ── Reset & Base ── */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', 'DejaVu Sans', sans-serif;
            background-color: #1e1e1e;
            color: #d4d4d4;
            font-size: 11px;
            line-height: 1.5;
            padding: 30px 35px;
        }

        /* ── Header ── */
        .header {
            border-bottom: 2px solid #007fd4;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header-title {
            font-size: 22px;
            font-weight: 700;
            color: #007fd4;
            letter-spacing: 1px;
        }
        .header-subtitle {
            font-size: 14px;
            color: #9cdcfe;
            margin-top: 4px;
        }
        .header-meta {
            font-size: 9px;
            color: #7b7b7b;
            margin-top: 8px;
        }

        /* ── Section titles ── */
        .section-title {
            font-size: 12px;
            color: #7b7b7b;
            text-transform: uppercase;
            letter-spacing: 2px;
            border-bottom: 1px solid #333;
            padding-bottom: 5px;
            margin-bottom: 12px;
            margin-top: 22px;
        }

        /* ── Status cards grid ── */
        .cards-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
        }
        .card {
            background-color: #252526;
            border: 1px solid #333;
            border-radius: 4px;
            text-align: center;
            padding: 10px 5px;
            width: 16.66%;
        }
        .card-value {
            font-size: 20px;
            font-weight: 700;
        }
        .card-label {
            font-size: 8px;
            color: #7b7b7b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        /* Card colors */
        .card-total .card-value { color: #ffffff; }
        .card-completed .card-value { color: #4ec9b0; }
        .card-inprogress .card-value { color: #79c0ff; }
        .card-paused .card-value { color: #d29922; }
        .card-pending .card-value { color: #8b949e; }
        .card-cancelled .card-value { color: #f85149; }

        /* ── Time blocks ── */
        .time-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
        }
        .time-block {
            background-color: #252526;
            border: 1px solid #333;
            border-radius: 4px;
            padding: 12px 15px;
            vertical-align: top;
        }
        .time-row {
            margin-bottom: 6px;
        }
        .time-label {
            font-size: 10px;
            color: #8b949e;
        }
        .time-value {
            font-size: 13px;
            font-weight: 600;
            font-family: 'Consolas', 'DejaVu Sans Mono', monospace;
        }

        /* ── Progress bar ── */
        .progress-bar-bg {
            background-color: #3c3c3c;
            border-radius: 3px;
            height: 6px;
            margin-top: 6px;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 6px;
            border-radius: 3px;
        }

        /* ── Completion ring (CSS only) ── */
        .completion-box {
            text-align: center;
            padding: 10px;
        }
        .completion-value {
            font-size: 26px;
            font-weight: 700;
            color: #4ec9b0;
        }
        .completion-label {
            font-size: 9px;
            color: #7b7b7b;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* ── Stats list ── */
        .stat-row {
            display: block;
            margin-bottom: 5px;
            padding: 3px 0;
        }
        .stat-label {
            font-size: 10px;
            color: #8b949e;
        }
        .stat-value {
            font-size: 11px;
            font-weight: 600;
            font-family: 'Consolas', 'DejaVu Sans Mono', monospace;
            float: right;
        }

        /* ── Status distribution bar ── */
        .distribution-bar {
            height: 18px;
            border-radius: 4px;
            overflow: hidden;
            margin: 10px 0;
            background-color: #3c3c3c;
        }
        .distribution-segment {
            height: 18px;
            float: left;
        }
        .distribution-legend {
            margin-top: 8px;
        }
        .legend-item {
            display: inline-block;
            margin-right: 12px;
            font-size: 9px;
        }
        .legend-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 3px;
            vertical-align: middle;
        }

        /* ── Task table ── */
        .task-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 10px;
        }
        .task-table th {
            background-color: #252526;
            color: #7b7b7b;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 6px 8px;
            text-align: left;
            border-bottom: 1px solid #333;
        }
        .task-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #2a2a2a;
            vertical-align: middle;
        }
        .task-table tr:nth-child(even) td {
            background-color: #1a1a1a;
        }

        /* Status badges */
        .badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-completed  { background-color: #1e3a23; color: #4ec9b0; }
        .badge-pending    { background-color: #2a2a2a; color: #8b949e; }
        .badge-inprogress { background-color: #152e42; color: #79c0ff; }
        .badge-paused     { background-color: #3b2e10; color: #d29922; }
        .badge-cancelled  { background-color: #3b1219; color: #f85149; }

        .text-red    { color: #f85149; }
        .text-green  { color: #4ec9b0; }
        .text-blue   { color: #9cdcfe; }
        .text-orange { color: #ce9178; }
        .text-yellow { color: #dcdcaa; }
        .text-grey   { color: #8b949e; }

        /* ── Footer ── */
        .footer {
            margin-top: 25px;
            padding-top: 10px;
            border-top: 1px solid #333;
            text-align: center;
            font-size: 8px;
            color: #4f4f4f;
        }

        /* ── Utilities ── */
        .clearfix::after { content: ''; display: table; clear: both; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .mb-2 { margin-bottom: 8px; }
        .mt-2 { margin-top: 8px; }

        /* Page break helper */
        .page-break { page-break-before: always; }
    </style>
</head>
<body>

    {{-- ══════════════════ HEADER ══════════════════ --}}
    <div class="header">
        <table style="width: 100%; border: none; border-collapse: collapse;">
            <tr>
                <td style="vertical-align: middle; padding: 0;">
                    <span class="header-title">FlexiWeek</span>
                </td>
                <td style="vertical-align: middle; text-align: right; padding: 0;">
                    <img src="{{ public_path('images/flexiweek-Iso.png') }}" style="height: 42px; width: auto;" alt="FlexiWeek">
                </td>
            </tr>
        </table>
        <div class="header-subtitle">{{ $metrics['label'] }}</div>
        <div class="header-meta">
            @if($metrics['startDate'] && $metrics['endDate'])
                📅 {{ \Carbon\Carbon::parse($metrics['startDate'])->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($metrics['endDate'])->format('d/m/Y') }}
                &nbsp;&nbsp;|&nbsp;&nbsp;
            @endif
            👤 {{ $userName }}
            &nbsp;&nbsp;|&nbsp;&nbsp;
            🖨️ Generado: {{ $generatedAt }}
            @if($metrics['periodsCount'] > 1)
                &nbsp;&nbsp;|&nbsp;&nbsp;
                📊 {{ $metrics['periodsCount'] }} período(s)
            @endif
        </div>
    </div>

    {{-- ══════════════════ RESUMEN ══════════════════ --}}
    <div class="section-title">Resumen de Tareas</div>

    <table class="cards-grid">
        <tr>
            <td class="card card-total">
                <div class="card-value">{{ $metrics['total'] }}</div>
                <div class="card-label">Total</div>
            </td>
            <td class="card card-completed">
                <div class="card-value">{{ $metrics['completed'] }}</div>
                <div class="card-label">Completadas</div>
            </td>
            <td class="card card-inprogress">
                <div class="card-value">{{ $metrics['inProgress'] }}</div>
                <div class="card-label">En Curso</div>
            </td>
            <td class="card card-paused">
                <div class="card-value">{{ $metrics['paused'] }}</div>
                <div class="card-label">Pausadas</div>
            </td>
            <td class="card card-pending">
                <div class="card-value">{{ $metrics['pending'] }}</div>
                <div class="card-label">Pendientes</div>
            </td>
            <td class="card card-cancelled">
                <div class="card-value">{{ $metrics['cancelled'] }}</div>
                <div class="card-label">Canceladas</div>
            </td>
        </tr>
    </table>

    {{-- ══════════════════ DISTRIBUCIÓN VISUAL ══════════════════ --}}
    @if($metrics['total'] > 0)
        <div class="distribution-bar clearfix">
            @php
                $segments = [
                    ['value' => $metrics['completed'],  'color' => '#4ec9b0'],
                    ['value' => $metrics['inProgress'], 'color' => '#79c0ff'],
                    ['value' => $metrics['paused'],     'color' => '#d29922'],
                    ['value' => $metrics['pending'],    'color' => '#8b949e'],
                    ['value' => $metrics['cancelled'],  'color' => '#f85149'],
                ];
            @endphp
            @foreach($segments as $seg)
                @if($seg['value'] > 0)
                    <div class="distribution-segment"
                         style="width: {{ ($seg['value'] / $metrics['total']) * 100 }}%;
                                background-color: {{ $seg['color'] }};"></div>
                @endif
            @endforeach
        </div>
        <div class="distribution-legend">
            <span class="legend-item"><span class="legend-dot" style="background:#4ec9b0;"></span> Completadas</span>
            <span class="legend-item"><span class="legend-dot" style="background:#79c0ff;"></span> En Curso</span>
            <span class="legend-item"><span class="legend-dot" style="background:#d29922;"></span> Pausadas</span>
            <span class="legend-item"><span class="legend-dot" style="background:#8b949e;"></span> Pendientes</span>
            <span class="legend-item"><span class="legend-dot" style="background:#f85149;"></span> Canceladas</span>
        </div>
    @endif

    {{-- ══════════════════ TIEMPOS Y STATS ══════════════════ --}}
    <div class="section-title">Análisis de Tiempo</div>

    <table class="time-grid">
        <tr>
            {{-- Tiempo total --}}
            <td class="time-block" style="width: 40%;">
                <div class="mb-2">
                    <span class="time-label">Tiempo Estimado</span>
                    <span class="time-value text-blue" style="float: right;">{{ intdiv($metrics['totalEstimated'], 60) }}h {{ $metrics['totalEstimated'] % 60 }}m</span>
                </div>
                <div class="clearfix"></div>
                <div class="mb-2">
                    <span class="time-label">Tiempo Invertido</span>
                    <span class="time-value {{ $metrics['totalSpent'] > $metrics['totalEstimated'] ? 'text-red' : 'text-green' }}" style="float: right;">{{ intdiv($metrics['totalSpent'], 60) }}h {{ $metrics['totalSpent'] % 60 }}m</span>
                </div>
                <div class="clearfix"></div>

                @if($metrics['totalEstimated'] > 0)
                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill"
                             style="width: {{ min(($metrics['totalSpent'] / $metrics['totalEstimated']) * 100, 100) }}%;
                                    background-color: {{ ($metrics['totalSpent'] / $metrics['totalEstimated']) > 1 ? '#f85149' : '#007fd4' }};"></div>
                    </div>
                @endif

                @if($metrics['totalOvertime'] > 0)
                    <div class="mt-2">
                        <span class="time-label">🔴 Excedido</span>
                        <span class="time-value text-red" style="float: right;">{{ intdiv($metrics['totalOvertime'], 60) }}h {{ $metrics['totalOvertime'] % 60 }}m</span>
                    </div>
                    <div class="clearfix"></div>
                @endif

                @if($metrics['totalGained'] > 0 && $metrics['isPeriodOver'])
                    <div class="mt-2">
                        <span class="time-label">🟢 Ganado</span>
                        <span class="time-value text-green" style="float: right;">{{ intdiv($metrics['totalGained'], 60) }}h {{ $metrics['totalGained'] % 60 }}m</span>
                    </div>
                    <div class="clearfix"></div>
                @endif

                @if($metrics['totalRemaining'] > 0 && !$metrics['isPeriodOver'])
                    <div class="mt-2">
                        <span class="time-label">⏳ Restante</span>
                        <span class="time-value text-blue" style="float: right;">{{ intdiv($metrics['totalRemaining'], 60) }}h {{ $metrics['totalRemaining'] % 60 }}m</span>
                    </div>
                    <div class="clearfix"></div>
                @endif
            </td>

            {{-- Tasa de completación --}}
            <td class="time-block text-center" style="width: 25%;">
                <div class="completion-value">{{ $metrics['completionRate'] }}%</div>
                <div class="completion-label">Completación</div>
            </td>

            {{-- Datos adicionales --}}
            <td class="time-block" style="width: 35%;">
                <div class="mb-2">
                    <span class="time-label">Promedio por tarea</span>
                    <span class="time-value text-orange" style="float: right;">{{ intdiv($metrics['avgTimePerTask'], 60) }}h {{ $metrics['avgTimePerTask'] % 60 }}m</span>
                </div>
                <div class="clearfix"></div>
                <div class="mb-2">
                    <span class="time-label">Excedieron tiempo</span>
                    <span class="time-value {{ $metrics['overTimeCount'] > 0 ? 'text-red' : 'text-green' }}" style="float: right;">{{ $metrics['overTimeCount'] }}</span>
                </div>
                <div class="clearfix"></div>
                <div class="mb-2">
                    <span class="time-label">Subtareas</span>
                    <span class="time-value text-yellow" style="float: right;">{{ $metrics['completedSubtasks'] }} / {{ $metrics['totalSubtasks'] }}</span>
                </div>
                <div class="clearfix"></div>
            </td>
        </tr>
    </table>

    {{-- ══════════════════ TABLA DETALLADA ══════════════════ --}}
    @if($tasks->count() > 0)
        <div class="section-title">Detalle de Tareas</div>

        <table class="task-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 35%;">Tarea</th>
                    <th style="width: 12%;">Estado</th>
                    <th style="width: 12%;" class="text-right">Estimado</th>
                    <th style="width: 12%;" class="text-right">Invertido</th>
                    <th style="width: 12%;" class="text-right">Diferencia</th>
                    <th style="width: 12%;" class="text-right">Progreso</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tasks as $index => $task)
                    @php
                        $est = $task->effective_estimated_minutes;
                        $spt = $task->effective_spent_minutes;
                        $diff = $spt - $est;
                        $progress = $task->progress;
                        $statusClass = match($task->status) {
                            \App\Enums\TaskStatus::Completed  => 'badge-completed',
                            \App\Enums\TaskStatus::Pending    => 'badge-pending',
                            \App\Enums\TaskStatus::InProgress => 'badge-inprogress',
                            \App\Enums\TaskStatus::Paused     => 'badge-paused',
                            \App\Enums\TaskStatus::Cancelled  => 'badge-cancelled',
                            default                           => 'badge-pending',
                        };
                    @endphp
                    <tr>
                        <td class="text-grey">{{ $index + 1 }}</td>
                        <td style="color: #d4d4d4; font-weight: 500;">
                            {{ $task->title }}
                            @if($task->subtasks->count() > 0)
                                <br><span style="font-size: 8px; color: #7b7b7b;">{{ $task->subtasks->where('is_completed', 1)->count() }}/{{ $task->subtasks->count() }} subtareas</span>
                            @endif
                        </td>
                        <td><span class="badge {{ $statusClass }}">{{ $task->status->label() }}</span></td>
                        <td class="text-right text-blue" style="font-family: 'Consolas', monospace;">{{ intdiv($est, 60) }}:{{ str_pad($est % 60, 2, '0', STR_PAD_LEFT) }}</td>
                        <td class="text-right {{ $spt > $est && $est > 0 ? 'text-red' : 'text-green' }}" style="font-family: 'Consolas', monospace;">{{ intdiv($spt, 60) }}:{{ str_pad($spt % 60, 2, '0', STR_PAD_LEFT) }}</td>
                        <td class="text-right {{ $diff > 0 ? 'text-red' : ($diff < 0 ? 'text-green' : 'text-grey') }}" style="font-family: 'Consolas', monospace;">
                            @if($est > 0)
                                {{ $diff > 0 ? '+' : '' }}{{ intdiv(abs($diff), 60) }}:{{ str_pad(abs($diff) % 60, 2, '0', STR_PAD_LEFT) }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-right" style="font-family: 'Consolas', monospace;">
                            @if($progress > 0)
                                <span class="{{ $progress >= 100 ? 'text-green' : 'text-blue' }}">{{ min($progress, 100) }}%</span>
                            @else
                                <span class="text-grey">0%</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- ══════════════════ FOOTER ══════════════════ --}}
    <div class="footer">
        FlexiWeek — Informe generado automáticamente · {{ $generatedAt }}
    </div>

</body>
</html>
