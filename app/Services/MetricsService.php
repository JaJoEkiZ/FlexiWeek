<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\Period;
use App\Models\Task;
use Carbon\Carbon;

class MetricsService
{
    /**
     * Calcula métricas para un período específico.
     */
    public function calculatePeriodMetrics(int $periodId): array
    {
        $period = Period::find($periodId);
        if (! $period) {
            return $this->emptyMetrics();
        }

        $tasks = Task::where('period_id', $periodId)
            ->with(['subtasks', 'timeLogs'])
            ->get();

        $isPeriodOver = now()->startOfDay()->gt(Carbon::parse($period->end_date));

        return $this->buildMetrics($tasks, $period->name, $period->start_date, $period->end_date, 1, $isPeriodOver);
    }

    /**
     * Calcula métricas para un rango de fechas.
     */
    public function calculateRangeMetrics(string $rangeStart, string $rangeEnd, int $userId): array
    {
        if (! $rangeStart || ! $rangeEnd) {
            return $this->emptyMetrics();
        }

        $periods = Period::where('user_id', $userId)
            ->where('start_date', '<=', $rangeEnd)
            ->where('end_date', '>=', $rangeStart)
            ->get();

        if ($periods->isEmpty()) {
            return $this->emptyMetrics();
        }

        $periodIds = $periods->pluck('id');
        $tasks = Task::whereIn('period_id', $periodIds)
            ->with(['subtasks', 'timeLogs'])
            ->get();

        $label = "Rango: {$rangeStart} a {$rangeEnd}";
        $isPeriodOver = now()->startOfDay()->gt(Carbon::parse($rangeEnd));

        return $this->buildMetrics($tasks, $label, $rangeStart, $rangeEnd, $periods->count(), $isPeriodOver);
    }

    /**
     * Construye el array de métricas a partir de una colección de tareas.
     */
    public function buildMetrics($tasks, string $label, $startDate, $endDate, int $periodsCount = 1, bool $isPeriodOver = false): array
    {
        $total = $tasks->count();
        $completed = $tasks->where('status', TaskStatus::Completed)->count();
        $pending = $tasks->where('status', TaskStatus::Pending)->count();
        $inProgress = $tasks->where('status', TaskStatus::InProgress)->count();
        $paused = $tasks->where('status', TaskStatus::Paused)->count();
        $cancelled = $tasks->where('status', TaskStatus::Cancelled)->count();

        // Tareas canceladas: su estimado sale del total, pero el trabajo realizado sí cuenta
        $cancelledTasks  = $tasks->where('status', TaskStatus::Cancelled);
        $activeTasks     = $tasks->where('status', '!=', TaskStatus::Cancelled);

        $totalEstimated  = $activeTasks->sum('effective_estimated_minutes');
        $totalSpent      = $tasks->sum('effective_spent_minutes'); // incluye trabajo de canceladas

        $overTimeTasks = $tasks->filter(fn ($t) => $t->effective_spent_minutes > $t->effective_estimated_minutes && $t->effective_estimated_minutes > 0);
        $totalOvertime = $overTimeTasks->sum(fn ($t) => $t->effective_spent_minutes - $t->effective_estimated_minutes);

        $gainedTimeTasks   = $tasks->filter(fn ($t) => $t->status === TaskStatus::Completed && $t->effective_estimated_minutes > $t->effective_spent_minutes);
        $totalGained       = $gainedTimeTasks->sum(fn ($t) => $t->effective_estimated_minutes - $t->effective_spent_minutes);

        $remainingTimeTasks = $tasks->filter(fn ($t) => !in_array($t->status, [TaskStatus::Completed, TaskStatus::Cancelled]) && $t->effective_estimated_minutes > $t->effective_spent_minutes);
        $totalRemaining    = $remainingTimeTasks->sum(fn ($t) => $t->effective_estimated_minutes - $t->effective_spent_minutes);

        $avgTimePerTask = $total > 0 ? round($totalSpent / $total) : 0;

        $activeTotal = $total - $cancelled;
        $completionRate = $activeTotal > 0 ? round(($completed / $activeTotal) * 100) : 0;

        $totalSubtasks = $tasks->sum(fn ($t) => $t->subtasks->count());
        $completedSubtasks = $tasks->sum(fn ($t) => $t->subtasks->where('is_completed', 1)->count());

        return [
            'label' => $label,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'periodsCount' => $periodsCount,
            'total' => $total,
            'completed' => $completed,
            'pending' => $pending,
            'inProgress' => $inProgress,
            'paused' => $paused,
            'cancelled' => $cancelled,
            'totalEstimated' => $totalEstimated,
            'totalSpent' => $totalSpent,
            'totalOvertime' => $totalOvertime,
            'totalGained'    => $totalGained,
            'totalRemaining' => $totalRemaining,
            'isPeriodOver'   => $isPeriodOver,
            'overTimeCount' => $overTimeTasks->count(),
            'avgTimePerTask' => $avgTimePerTask,
            'completionRate' => $completionRate,
            'totalSubtasks'  => $totalSubtasks,
            'completedSubtasks' => $completedSubtasks,
            // Datos para Chart.js (solo usado en la vista web)
            'statusChart' => [
                'labels' => ['Completadas', 'Pendientes', 'En Curso', 'Pausadas', 'Canceladas'],
                'data' => [$completed, $pending, $inProgress, $paused, $cancelled],
                'colors' => ['#4ec9b0', '#8b949e', '#79c0ff', '#d29922', '#f85149'],
            ],
            'timeChart' => [
                'labels' => $tasks->pluck('title')->take(15)->toArray(),
                'estimated' => $tasks->take(15)->pluck('effective_estimated_minutes')->toArray(),
                'spent' => $tasks->take(15)->pluck('effective_spent_minutes')->toArray(),
            ],
        ];
    }

    /**
     * Retorna un array de métricas vacío.
     */
    public function emptyMetrics(): array
    {
        return [
            'label' => 'Sin datos',
            'startDate' => null,
            'endDate' => null,
            'periodsCount' => 0,
            'total' => 0,
            'completed' => 0,
            'pending' => 0,
            'inProgress' => 0,
            'paused' => 0,
            'cancelled' => 0,
            'totalEstimated' => 0,
            'totalSpent' => 0,
            'totalOvertime' => 0,
            'totalGained'    => 0,
            'totalRemaining' => 0,
            'isPeriodOver'   => false,
            'overTimeCount' => 0,
            'avgTimePerTask' => 0,
            'completionRate' => 0,
            'totalSubtasks'  => 0,
            'completedSubtasks' => 0,
            'statusChart' => ['labels' => [], 'data' => [], 'colors' => []],
            'timeChart' => ['labels' => [], 'estimated' => [], 'spent' => []],
        ];
    }
}
