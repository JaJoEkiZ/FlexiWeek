<?php

namespace App\Livewire;

use App\Enums\TaskStatus;
use App\Models\Period;
use App\Models\Task;
use App\Models\TaskTimeLog;
use Livewire\Component;

class WeeklyPlanner extends Component
{
    public $selectedPeriodId;

    public $minutesInput = [];

    // Estado del Modal de Edición (Ahora manejado por TaskForm)

    public function mount()
    {
        // Seleccionar por defecto la semana actual o la siguiente más próxima
        $today = now()->format('Y-m-d');
        $this->selectedPeriodId = Period::where('user_id', auth()->id())
            ->where('end_date', '>=', $today)
            ->orderBy('start_date', 'asc')
            ->first()?->id
            ?? Period::where('user_id', auth()->id())->orderBy('start_date', 'desc')->first()?->id; // Fallback a la última si no hay futuras
    }

    public function selectPeriod($periodId)
    {
        $this->selectedPeriodId = $periodId;
    }

    public function cycleStatus($taskId)
    {
        $task = Task::findOrFail($taskId);

        if ($task->progress >= 100) {
            return;
        }

        // Excluimos 'Completed' del ciclo manual
        $statuses = collect(TaskStatus::cases())
            ->filter(fn ($s) => $s !== TaskStatus::Completed)
            ->values();

        $currentIndex = $statuses->search($task->status);

        if ($currentIndex === false) {
            // Si está en un estado que no está en la lista (ej: Completed), vuelve al primero
            $nextStatus = $statuses->first();
        } else {
            $nextIndex = ($currentIndex + 1) % $statuses->count();
            $nextStatus = $statuses[$nextIndex];
        }

        $task->update(['status' => $nextStatus]);
    }

    public function addTime($taskId)
    {
        $minutes = (int) ($this->minutesInput[$taskId] ?? 0);

        if ($minutes > 0) {
            TaskTimeLog::create([
                'task_id' => $taskId,
                'minutes_spent' => $minutes,
                'log_date' => now(),
            ]);

            $this->minutesInput[$taskId] = ''; // Limpiar input

            // Verificar si llegamos al 100% para completar la tarea
            $task = Task::find($taskId);

            if ($task->progress >= 100 && $task->status !== TaskStatus::Completed) {
                $task->update(['status' => TaskStatus::Completed]);
                session()->flash('message', "¡Tarea completada! Se cargaron {$minutes} minutos.");
            } else {
                session()->flash('message', "¡Se cargaron {$minutes} minutos!");
            }
        }
    }

    protected $listeners = ['taskSaved' => '$refresh', 'periodSaved' => '$refresh', 'periodSelected' => 'selectPeriod'];

    public function openTaskForm($taskId = null)
    {
        $this->dispatch('openTaskForm', ['taskId' => $taskId, 'periodId' => $this->selectedPeriodId]);
    }

    public function openPeriodForm($periodId = null)
    {
        $this->dispatch('openPeriodForm', $periodId);
    }

    public function render()
    {
        $currentPeriod = null;
        if ($this->selectedPeriodId) {
            $currentPeriod = Period::with(['tasks' => function ($query) {
                // ... (ordering logic remains same if complicated, or simplified)
                $query->orderByRaw("
                    CASE status
                        WHEN 'pending' THEN 1
                        WHEN 'in_progress' THEN 2
                        WHEN 'completed' THEN 3
                        ELSE 4
                    END
                ");
            }])->find($this->selectedPeriodId);
        }

        $periods = Period::where('user_id', auth()->id())->orderBy('start_date', 'desc')->get(); // Still used for TaskForm dropdown

        return view('livewire.weekly-planner', [
            'currentPeriod' => $currentPeriod,
            'periods' => $periods,
        ]);
    }
}
