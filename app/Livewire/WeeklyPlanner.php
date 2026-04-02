<?php

namespace App\Livewire;

use App\Enums\TaskStatus;
use App\Models\Period;
use App\Models\Task;
use App\Models\TaskTimeLog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class WeeklyPlanner extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $selectedPeriodId;

    public function mount($period = null)
    {
        if ($period) {
            // Vino un period ID en la URL — verificar que le pertenece al usuario
            $exists = Period::where('id', $period)
                ->where('user_id', auth()->id())
                ->exists();
            $this->selectedPeriodId = $exists ? $period : null;
        }

        // Si no vino period en la URL o no era válido, usar el default
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
    }

    public function selectPeriod($periodId)
    {
        $this->selectedPeriodId = $periodId;
        $this->resetPage();

        // Actualizar la URL para reflejar el período seleccionado
        $this->redirect(route('planner', $periodId), navigate: true);
    }

    #[Renderless]
    public function cycleStatus($taskId)
    {
        $task = Task::findOrFail($taskId);

        if ($task->progress >= 100 || $task->status === TaskStatus::Cancelled) {
            return;
        }

        $statuses = collect(TaskStatus::cases())
            ->filter(fn ($s) => $s !== TaskStatus::Completed && $s !== TaskStatus::Cancelled)
            ->values();

        $currentIndex = $statuses->search($task->status);

        if ($currentIndex === false) {
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
            $this->dispatch('toast', message: 'No se puede finalizar una tarea cancelada.', type: 'error');
            return;
        }
        if ($task->status === TaskStatus::Completed) {
            $this->dispatch('toast', message: 'La tarea ya está finalizada.', type: 'info');
            return;
        }
        $task->update(['status' => TaskStatus::Completed]);
        $this->dispatch('toast', message: '¡Tarea finalizada manualmente!', type: 'success');
    }

    public function cancelTask($taskId)
    {
        $task = Task::findOrFail($taskId);
        $task->update(['status' => TaskStatus::Cancelled]);
        $this->dispatch('toast', message: 'Tarea cancelada.', type: 'info');
    }

    public function reactivateTask($taskId)
    {
        $task = Task::findOrFail($taskId);
        $task->update(['status' => TaskStatus::Pending]);
        $this->dispatch('toast', message: 'Tarea reactivada.', type: 'info');
    }

    public function sendToPizarra($taskId)
    {
        $task = Task::with('subtasks')->findOrFail($taskId);
        
        // Verificar propiedad
        if ($task->period->user_id !== auth()->id()) {
            return;
        }

        $maxZ = \App\Models\BoardItem::where('user_id', auth()->id())->max('z_index') ?? 0;

        $item = \App\Models\BoardItem::create([
            'user_id' => auth()->id(),
            'title'   => $task->title,
            'notes'   => null,
            'pos_x'   => 100, // Posición por defecto
            'pos_y'   => 100,
            'width'   => 200,
            'height'  => 70,
            'color'   => '#3B82F6', // Azul por defecto
            'z_index' => $maxZ + 1,
        ]);

        foreach ($task->subtasks as $st) {
            \App\Models\BoardItemSubtask::create([
                'board_item_id' => $item->id,
                'title' => $st->title,
                'is_completed' => $st->is_completed,
            ]);
        }

        $task->delete(); // Eliminar la tarea original

        $this->dispatch('toast', message: 'Tarea enviada a la pizarra.', type: 'success');
        $this->dispatch('$refresh');
    }

    public function toggleSubtask($taskId, $subtaskId)
    {
        $task = Task::findOrFail($taskId);

        // Security check: ensure task belongs to the user
        if ($task->period->user_id !== auth()->id()) {
            return;
        }

        $subtask = \App\Models\Subtask::find($subtaskId);
        if ($subtask && $subtask->task_id == $taskId) {
            $subtask->update(['is_completed' => ! $subtask->is_completed]);

            $total     = $task->subtasks()->count();
            $completed = $task->subtasks()->where('is_completed', true)->count();

            if ($total === $completed && $total > 0) {
                $task->update(['status' => TaskStatus::Completed]);
                $this->dispatch('toast', message: '¡Todas las subtareas completadas!', type: 'success');
            } else {
                if ($task->status === TaskStatus::Completed) {
                    $task->update(['status' => TaskStatus::InProgress]);
                }
            }

            $task->touch(); // Force updated_at change for wire:key
        }
    }

    #[Renderless]
    public function addTime($taskId, $hours, $mins, $subtaskId = null)
    {
        $task = Task::findOrFail($taskId);

        if ($task->status === TaskStatus::Cancelled) {
            $this->dispatch('toast', message: 'No se puede agregar tiempo a una tarea cancelada.', type: 'error');
            return;
        }

        if (! empty($subtaskId)) {
            return $this->addTimeToSubtask($taskId, $hours, $mins, $subtaskId);
        }

        $hours = (int) $hours;
        $mins  = (int) $mins;
        $minutes = ($hours * 60) + $mins;

        if ($minutes > 0) {
            TaskTimeLog::create([
                'task_id'       => $taskId,
                'minutes_spent' => $minutes,
                'log_date'      => now(),
            ]);

            if ($task->progress >= 100 && $task->status !== TaskStatus::Completed) {
                $task->update(['status' => TaskStatus::Completed]);
                $this->dispatch('toast', message: "¡Tarea completada! Se cargaron {$minutes} minutos.", type: 'success');
            } else {
                if ($task->status === TaskStatus::Pending) {
                    $task->update(['status' => TaskStatus::InProgress]);
                }
                $this->dispatch('toast', message: "¡Se cargaron {$minutes} minutos!", type: 'success');
            }

            $task->touch(); // Ensure wire:key changes
        }
    }

    #[Renderless]
    public function addTimeToSubtask($taskId, $hours, $mins, $subtaskId)
    {
        $task = Task::findOrFail($taskId);

        if ($task->status === TaskStatus::Cancelled) {
            $this->dispatch('toast', message: 'No se puede agregar tiempo a una tarea cancelada.', type: 'error');
            return;
        }

        $hours = (int) $hours;
        $mins  = (int) $mins;
        $minutes = ($hours * 60) + $mins;

        if ($subtaskId && $minutes > 0) {
            $subtask = \App\Models\Subtask::find($subtaskId);

            if ($subtask) {
                $subtask->spent_minutes += $minutes;
                $subtask->save();

                if ($task->progress >= 100 && $task->status !== TaskStatus::Completed) {
                    $task->update(['status' => TaskStatus::Completed]);
                    $this->dispatch('toast', message: "¡Tarea completada! Se cargaron {$minutes} minutos a '{$subtask->title}'.", type: 'success');
                } else {
                    if ($task->status === TaskStatus::Pending) {
                        $task->update(['status' => TaskStatus::InProgress]);
                    }
                    $this->dispatch('toast', message: "¡Se cargaron {$minutes} minutos a '{$subtask->title}'!", type: 'success');
                }

                $task->touch(); // Ensure wire:key changes
            }
        }
    }

    protected $listeners = [
        'taskSaved'      => 'taskSaved',
        'periodSaved'    => '$refresh',
        'periodSelected' => 'selectPeriod',
    ];

    public function taskSaved()
    {
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
        $task        = Task::findOrFail($taskId);
        $oldPeriodId = $task->period_id;
        $maxOrder    = Task::where('period_id', $newPeriodId)->max('sort_order') ?? 0;

        $task->update([
            'period_id'  => $newPeriodId,
            'sort_order' => $maxOrder + 1,
        ]);

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
        $tasks         = collect();

        if ($this->selectedPeriodId) {
            $currentPeriod = Period::find($this->selectedPeriodId);
            $tasks = Task::where('period_id', $this->selectedPeriodId)
                ->with('subtasks')
                ->orderBy('sort_order', 'asc')
                ->paginate(10);
        }

        return view('livewire.weekly-planner', [
            'currentPeriod'    => $currentPeriod,
            'tasks'            => $tasks,
            'selectedPeriodId' => $this->selectedPeriodId,
        ]);
    }
}