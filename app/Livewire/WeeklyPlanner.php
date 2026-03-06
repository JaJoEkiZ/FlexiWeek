<?php

namespace App\Livewire;

use App\Enums\TaskStatus;
use App\Models\Period;
use App\Models\Task;
use App\Models\TaskTimeLog;
use Livewire\Component;
use Livewire\WithPagination;

class WeeklyPlanner extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $selectedPeriodId;

    public $activeTab = 'tasks';

    public $minutesInput = [];

    public $hoursInput = [];

    public $selectedSubtask = [];

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
        $this->resetPage();
    }

    public function cycleStatus($taskId)
    {
        $task = Task::findOrFail($taskId);

        if ($task->progress >= 100 || $task->status === TaskStatus::Cancelled) {
            return;
        }

        // Excluimos 'Completed' y 'Cancelled' del ciclo manual
        $statuses = collect(TaskStatus::cases())
            ->filter(fn ($s) => $s !== TaskStatus::Completed && $s !== TaskStatus::Cancelled)
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
    public function finishTask($taskId)
    {
        $task = Task::findOrFail($taskId);
        if ($task->status === TaskStatus::Cancelled) {
            session()->flash('message', 'No se puede finalizar una tarea cancelada.');
            return;
        }
        if ($task->status === TaskStatus::Completed) {
            session()->flash('message', 'La tarea ya está finalizada.');
            return;
        }
        $task->update(['status' => TaskStatus::Completed]);
        session()->flash('message', '¡Tarea finalizada manualmente!');
    }

    public function cancelTask($taskId)
    {
        $task = Task::findOrFail($taskId);
        $task->update(['status' => TaskStatus::Cancelled]);
        session()->flash('message', 'Tarea cancelada.');
    }

    public function reactivateTask($taskId)
    {
        $task = Task::findOrFail($taskId);
        $task->update(['status' => TaskStatus::Pending]);
        session()->flash('message', 'Tarea reactivada.');
    }

    public function addTime($taskId)
    {
        $task = Task::findOrFail($taskId);

        if ($task->status === TaskStatus::Cancelled) {
            session()->flash('message', 'No se puede agregar tiempo a una tarea cancelada.');

            return;
        }

        // Si hay una subtarea seleccionada, usar addTimeToSubtask
        if (! empty($this->selectedSubtask[$taskId])) {
            return $this->addTimeToSubtask($taskId);
        }

        $hours = (int) ($this->hoursInput[$taskId] ?? 0);
        $mins = (int) ($this->minutesInput[$taskId] ?? 0);
        $minutes = ($hours * 60) + $mins;

        if ($minutes > 0) {
            TaskTimeLog::create([
                'task_id' => $taskId,
                'minutes_spent' => $minutes,
                'log_date' => now(),
            ]);

            $this->minutesInput[$taskId] = '';
            $this->hoursInput[$taskId] = '';

            // Verificar si llegamos al 100% para completar la tarea
            if ($task->progress >= 100 && $task->status !== TaskStatus::Completed) {
                $task->update(['status' => TaskStatus::Completed]);
                session()->flash('message', "¡Tarea completada! Se cargaron {$minutes} minutos.");
            } else {
                // Auto cambiar a "En Curso" si está pendiente
                if ($task->status === TaskStatus::Pending) {
                    $task->update(['status' => TaskStatus::InProgress]);
                }
                session()->flash('message', "¡Se cargaron {$minutes} minutos!");
            }
        }
    }

    public function addTimeToSubtask($taskId)
    {
        $task = Task::findOrFail($taskId);

        if ($task->status === TaskStatus::Cancelled) {
            session()->flash('message', 'No se puede agregar tiempo a una tarea cancelada.');

            return;
        }

        $subtaskId = $this->selectedSubtask[$taskId] ?? null;
        $hours = (int) ($this->hoursInput[$taskId] ?? 0);
        $mins = (int) ($this->minutesInput[$taskId] ?? 0);
        $minutes = ($hours * 60) + $mins;

        if ($subtaskId && $minutes > 0) {
            $subtask = \App\Models\Subtask::find($subtaskId);

            if ($subtask) {
                $subtask->spent_minutes += $minutes;
                $subtask->save();

                // Verificar si llegamos al 100% para completar la tarea
                if ($task->progress >= 100 && $task->status !== TaskStatus::Completed) {
                    $task->update(['status' => TaskStatus::Completed]);
                    session()->flash('message', "¡Tarea completada! Se cargaron {$minutes} minutos a la subtarea '{$subtask->title}'.");
                } else {
                    session()->flash('message', "¡Se cargaron {$minutes} minutos a la subtarea '{$subtask->title}'!");
                }

                // Limpiar inputs
                $this->minutesInput[$taskId] = '';
                $this->hoursInput[$taskId] = '';
                $this->selectedSubtask[$taskId] = null;
            }
        }
    }

    protected $listeners = [
        'taskSaved' => 'taskSaved',
        'periodSaved' => '$refresh',
        'periodSelected' => 'selectPeriod',
        'setActiveTab' => 'setActiveTab',
    ];

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

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

    public function moveTaskToPeriod($taskId, $newPeriodId)
    {
        $task = Task::findOrFail($taskId);
        $oldPeriodId = $task->period_id;

        // Obtener el orden máximo en la nueva semana
        $maxOrder = Task::where('period_id', $newPeriodId)->max('sort_order') ?? 0;

        // Mover la tarea
        $task->update([
            'period_id' => $newPeriodId,
            'sort_order' => $maxOrder + 1,
        ]);

        // Reordenar tareas en la semana original
        $this->reorderPeriodTasks($oldPeriodId);
    }

    private function reorderPeriodTasks($periodId)
    {
        $tasks = Task::where('period_id', $periodId)->orderBy('sort_order')->get();
        foreach ($tasks as $index => $task) {
            $task->update(['sort_order' => $index + 1]);
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
        $tasks = collect();

        if ($this->selectedPeriodId) {
            $currentPeriod = Period::find($this->selectedPeriodId);

            // Paginación de tareas (10 por página)
            $tasks = Task::where('period_id', $this->selectedPeriodId)
                ->with('subtasks')
                ->orderBy('sort_order', 'asc')
                ->paginate(10);
        }

        $periods = Period::where('user_id', auth()->id())->orderBy('start_date', 'desc')->get();

        return view('livewire.weekly-planner', [
            'currentPeriod' => $currentPeriod,
            'tasks' => $tasks,
            'periods' => $periods,
        ]);
    }
}
