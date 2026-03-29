<?php

namespace App\Livewire\Components;

use App\Enums\TaskStatus;
use App\Models\Period;
use App\Models\Task;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PeriodMetrics extends Component
{
    public $selectedPeriodId;

    public $mode = 'period';

    public $rangeStart;

    public $rangeEnd;

    protected $listeners = ['periodSelected' => 'updatePeriod'];

    public function mount($period = null)
    {
        if ($period) {
            $exists = Period::where('id', $period)
                ->where('user_id', auth()->id())
                ->exists();
            $this->selectedPeriodId = $exists ? $period : null;
        }

        // Si no vino period en la URL, usar el default
        if (! $this->selectedPeriodId) {
            $today = now()->format('Y-m-d');
            $this->selectedPeriodId = Period::where('user_id', auth()->id())
                ->where('end_date', '>=', $today)
                ->orderBy('start_date', 'asc')
                ->first()?->id
                ?? Period::where('user_id', auth()->id())
                    ->orderBy('start_date', 'desc')
                    ->first()?->id;
        }

        $this->rangeStart = now()->startOfMonth()->format('Y-m-d');
        $this->rangeEnd = now()->endOfMonth()->format('Y-m-d');
    }

    public function updatePeriod($periodId)
    {
        // Redirigir a la nueva URL con el período seleccionado
        $this->redirect(route('metrics', $periodId), navigate: true);
    }

    public function switchMode($mode)
    {
        $this->mode = $mode;
    }

    // --- todo lo demás sin cambios ---

    public function getMetricsProperty()
    {
        if ($this->mode === 'range') {
            return $this->calculateRangeMetrics();
        }

        return $this->calculatePeriodMetrics();
    }

    private function calculatePeriodMetrics()
    {
        if (! $this->selectedPeriodId) {
            return $this->emptyMetrics();
        }

        $period = Period::find($this->selectedPeriodId);
        if (! $period) {
            return $this->emptyMetrics();
        }

        $tasks = Task::where('period_id', $this->selectedPeriodId)
            ->with(['subtasks', 'timeLogs'])
            ->get();

        return $this->buildMetrics($tasks, $period->name, $period->start_date, $period->end_date);
    }

    private function calculateRangeMetrics()
    {
        if (! $this->rangeStart || ! $this->rangeEnd) {
            return $this->emptyMetrics();
        }

        // Buscar períodos que se solapan con el rango
        $periods = Period::where('user_id', auth()->id())
            ->where('start_date', '<=', $this->rangeEnd)
            ->where('end_date', '>=', $this->rangeStart)
            ->get();

        if ($periods->isEmpty()) {
            return $this->emptyMetrics();
        }

        // Obtener todas las tareas de esos períodos
        $periodIds = $periods->pluck('id');
        $tasks = Task::whereIn('period_id', $periodIds)
            ->with(['subtasks', 'timeLogs'])
            ->get();

        $label = "Rango: {$this->rangeStart} a {$this->rangeEnd}";

        return $this->buildMetrics($tasks, $label, $this->rangeStart, $this->rangeEnd, $periods->count());
    }

    private function buildMetrics($tasks, $label, $startDate, $endDate, $periodsCount = 1)
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

        $completionRate = $total > 0 ? round(($completed / $total) * 100) : 0;

        $totalSubtasks = $tasks->sum(fn ($t) => $t->subtasks->count());
        $completedSubtasks = $tasks->sum(fn ($t) => $t->subtasks->where('is_completed', true)->count());

        // Determinar si el período ha terminado para mostrar tiempo ganado
        $isPeriodOver = false;
        if ($this->mode === 'period') {
            $period = Period::find($this->selectedPeriodId);
            if ($period && now()->startOfDay()->gt(\Carbon\Carbon::parse($period->end_date))) {
                $isPeriodOver = true;
            }
        } elseif ($this->mode === 'range') {
            if (now()->startOfDay()->gt(\Carbon\Carbon::parse($this->rangeEnd))) {
                $isPeriodOver = true;
            }
        }

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
            // Datos para Chart.js
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

    private function emptyMetrics()
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

    public function render()
    {
        $periods = Period::where('user_id', auth()->id())
            ->orderBy('start_date', 'desc')
            ->get();
    
        $metrics = $this->metrics;
    
        // Cargar el período actual para pasárselo al layout
        $currentPeriod = $this->selectedPeriodId
            ? Period::find($this->selectedPeriodId)
            : null;
    
        if ($metrics['total'] > 0) {
            $this->dispatch('metricsUpdated', $metrics);
        }
    
        return view('livewire.components.period-metrics', [
            'metrics'          => $metrics,
            'periods'          => $periods,
            'selectedPeriodId' => $this->selectedPeriodId,
            'currentPeriod'    => $currentPeriod,
        ]);
    }
}
