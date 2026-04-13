<?php

namespace App\Livewire\Components;

use App\Models\Task;
use App\Models\Subtask;
use App\Models\TaskTimeLog;
use App\Models\Period;
use App\Enums\TaskStatus;
use Livewire\Component;
use Livewire\Attributes\Renderless;

class CronSprint extends Component
{
    public $periodId;

    protected $listeners = [
        'periodSelected' => 'updatePeriod',
        'taskSaved' => '$refresh',
        'periodSaved' => '$refresh',
    ];

    public function mount()
    {
        // Ya no necesitamos $periodId pasado. 
    }

    #[Renderless]
    public function saveTimerState($state)
    {
        if (auth()->check()) {
            \Illuminate\Support\Facades\Cache::put('cron_sprint_' . auth()->id(), $state, now()->addDays(7));
        }
    }

    #[Renderless]
    public function clearTimerState()
    {
        if (auth()->check()) {
            \Illuminate\Support\Facades\Cache::forget('cron_sprint_' . auth()->id());
        }
    }

    public function assignTime($taskId, $subtaskId, $minutesDecimal)
    {
        $task = Task::find($taskId);

        if (!$task) {
            $this->dispatch('toast', message: 'La tarea ya no existe.', type: 'error');
            return;
        }

        if ($task->status === TaskStatus::Cancelled) {
            $this->dispatch('toast', message: 'No se puede agregar tiempo a una tarea cancelada.', type: 'error');
            return;
        }

        if ($minutesDecimal <= 0) {
            return;
        }

        if ($subtaskId) {
            $subtask = Subtask::find($subtaskId);
            if ($subtask && $subtask->task_id == $taskId) {
                $subtask->update([
                    'spent_minutes' => $subtask->spent_minutes + $minutesDecimal
                ]);
                
                $task->refresh(); // Refrescar relaciones tras cambiar subtarea
                $this->updateTaskStatus($task, "¡Se sumó tiempo a '{$subtask->title}'!");
            } else {
                $this->dispatch('toast', message: 'La subtarea no pertenece a esta tarea.', type: 'error');
            }
        } else {
            TaskTimeLog::create([
                'task_id'       => $taskId,
                'minutes_spent' => $minutesDecimal,
                'log_date'      => now(),
            ]);

            $task->refresh(); // Refrescar para que el helper cuente el nuevo log
            $this->updateTaskStatus($task, "¡Se sumó tiempo a '{$task->title}'!");
        }

        $this->dispatch('metricsUpdated'); 
        $this->dispatch('taskSaved'); 
        $this->dispatch('$refresh');
    }

    private function updateTaskStatus(Task $task, $successMessage)
    {
        // Pasar aInProgress si estaba pendiente
        if ($task->status === TaskStatus::Pending) {
            $task->update(['status' => TaskStatus::InProgress]);
        }
        
        // Evaluar progreso tras actualizar a InProgress para que si termina, lo cubra
        if ($task->progress >= 100 && $task->status !== TaskStatus::Completed) {
            $task->update(['status' => TaskStatus::Completed]);
            $this->dispatch('toast', message: "¡Tarea completada! {$successMessage}", type: 'success');
        } else {
            $this->dispatch('toast', message: $successMessage, type: 'success');
        }

        $task->touch();
    }

    public function render()
    {
        $tasks = collect();
        $today = now()->format('Y-m-d');
        
        // Cargar siempre el periodo ACTIVO real del usuario
        $currentPeriod = Period::where('user_id', auth()->id())
            ->where('end_date', '>=', $today)
            ->orderBy('start_date', 'asc')
            ->first()
            ?? Period::where('user_id', auth()->id())
                ->orderBy('start_date', 'desc')
                ->first();

        if ($currentPeriod) {
            $tasks = Task::where('period_id', $currentPeriod->id)
                ->with('subtasks')
                ->where('status', '!=', TaskStatus::Cancelled)
                ->orderBy('sort_order', 'asc')
                ->get();
        }

        $timerState = auth()->check() ? \Illuminate\Support\Facades\Cache::get('cron_sprint_' . auth()->id()) : null;

        return view('livewire.components.cron-sprint', [
            'tasks' => $tasks,
            'timerState' => $timerState
        ]);
    }
}
