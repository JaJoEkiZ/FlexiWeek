<?php

namespace App\Http\Controllers;

use App\Models\Period;
use App\Models\Task;
use App\Services\MetricsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MetricsExportController extends Controller
{
    public function download(Request $request, MetricsService $metricsService)
    {
        $request->validate([
            'period_id'   => 'nullable|integer|exists:periods,id',
            'range_start' => 'nullable|date',
            'range_end'   => 'nullable|date|after_or_equal:range_start',
        ]);

        $userId = auth()->id();

        // Determinar modo y calcular métricas
        if ($request->filled('period_id')) {
            // Verificar que el período pertenece al usuario
            $period = Period::where('id', $request->period_id)
                ->where('user_id', $userId)
                ->firstOrFail();

            $metrics = $metricsService->calculatePeriodMetrics($period->id);

            // Cargar tareas para la tabla detallada
            $tasks = Task::where('period_id', $period->id)
                ->with(['subtasks', 'timeLogs'])
                ->orderBy('title')
                ->get();

            $filename = 'FlexiWeek_Informe_' . str_replace(' ', '_', $period->name) . '.pdf';
        } elseif ($request->filled(['range_start', 'range_end'])) {
            $metrics = $metricsService->calculateRangeMetrics(
                $request->range_start,
                $request->range_end,
                $userId
            );

            // Cargar tareas para la tabla detallada
            $periodIds = Period::where('user_id', $userId)
                ->where('start_date', '<=', $request->range_end)
                ->where('end_date', '>=', $request->range_start)
                ->pluck('id');

            $tasks = Task::whereIn('period_id', $periodIds)
                ->with(['subtasks', 'timeLogs'])
                ->orderBy('title')
                ->get();

            $filename = 'FlexiWeek_Informe_' . $request->range_start . '_a_' . $request->range_end . '.pdf';
        } else {
            abort(400, 'Debe especificar un período o un rango de fechas.');
        }

        $pdf = Pdf::loadView('pdf.metrics-report', [
            'metrics'   => $metrics,
            'tasks'     => $tasks,
            'userName'  => auth()->user()->name,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ]);

        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('defaultFont', 'sans-serif');

        return $pdf->download($filename);
    }
}
