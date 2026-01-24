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

    protected $listeners = [
        'taskSaved' => 'taskSaved',
        'periodSaved' => '$refresh',
        'periodSelected' => 'selectPeriod',
    ];

    public function taskSaved()
    {
        // Asignar orden a tareas sin orden
        Task::whereNull('sort_order')->orWhere('sort_order', 0)->each(function ($task) {
            $maxOrder = Task::where('period_id', $task->period_id)->max('sort_order') ?? 0;
            $task->update(['sort_order' => $maxOrder + 1]);
        });

        $this->dispatch('$refresh');
    }

    public function updateTaskOrder($orderedIds)
    {
        foreach ($orderedIds as $index => $taskId) {
            Task::where('id', $taskId)->update(['sort_order' => $index + 1]);
        }
    }

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
            $currentPeriod = Period::with(['tasks.subtasks', 'tasks' => function ($query) {
                $query->orderBy('sort_order', 'asc');
            }])->find($this->selectedPeriodId);
        }

        $periods = Period::where('user_id', auth()->id())->orderBy('start_date', 'desc')->get(); // Still used for TaskForm dropdown

        return view('livewire.weekly-planner', [
            'currentPeriod' => $currentPeriod,
            'periods' => $periods,
        ]);
    }
}
