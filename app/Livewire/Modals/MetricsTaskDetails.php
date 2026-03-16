<?php

namespace App\Livewire\Modals;

use App\Enums\TaskStatus;
use App\Models\Period;
use App\Models\Task;
use Livewire\Component;

class MetricsTaskDetails extends Component
{
    public $isOpen = false;
    public $type = ''; // 'gained' o 'overtime'
    public $periodId = null;
    public $rangeStart = null;
    public $rangeEnd = null;
    public $tasks = [];
    public $totalDiff = 0;

    protected $listeners = ['openMetricsTaskDetails'];

    public function openMetricsTaskDetails($type = '', $periodId = null, $rangeStart = null, $rangeEnd = null)
    {
        $this->type = $type;
        $this->periodId = $periodId;
        $this->rangeStart = $rangeStart;
        $this->rangeEnd = $rangeEnd;

        $this->loadTasks();
        $this->isOpen = true;
    }

    private function loadTasks()
    {
        $query = Task::with(['period', 'subtasks', 'timeLogs']);

        if ($this->periodId) {
            $query->where('period_id', $this->periodId);
        } elseif ($this->rangeStart && $this->rangeEnd) {
            $periodIds = Period::where('user_id', auth()->id())
                ->where('start_date', '<=', $this->rangeEnd)
                ->where('end_date', '>=', $this->rangeStart)
                ->pluck('id');
            $query->whereIn('period_id', $periodIds);
        } else {
            // Si no hay filtros válidos
            $this->tasks = collect();
            $this->totalDiff = 0;
            return;
        }

        $allTasks = $query->get();

        if ($this->type === 'overtime') {
            $filtered = $allTasks->filter(function ($t) {
                return $t->effective_spent_minutes > $t->effective_estimated_minutes && $t->effective_estimated_minutes > 0;
            });
            $this->totalDiff = $filtered->sum(fn($t) => $t->effective_spent_minutes - $t->effective_estimated_minutes);
        } elseif ($this->type === 'gained') {
            $filtered = $allTasks->filter(function ($t) {
                return $t->status === TaskStatus::Completed && $t->effective_estimated_minutes > $t->effective_spent_minutes;
            });
            $this->totalDiff = $filtered->sum(fn($t) => $t->effective_estimated_minutes - $t->effective_spent_minutes);
        } else {
            $filtered = collect();
            $this->totalDiff = 0;
        }

        // Ordenar de mayor diferencia a menor
        $this->tasks = $filtered->sortByDesc(function ($t) {
            return abs($t->effective_estimated_minutes - $t->effective_spent_minutes);
        })->values();
    }

    public function close()
    {
        $this->isOpen = false;
        $this->reset(['type', 'periodId', 'rangeStart', 'rangeEnd', 'tasks', 'totalDiff']);
    }

    public function render()
    {
        return view('livewire.modals.metrics-task-details');
    }
}
