<?php

namespace App\Livewire\Modals;

use App\Enums\TaskStatus;
use App\Models\Period;
use App\Models\Task;
use Livewire\Component;

class TaskForm extends Component
{
    // descripcion
    public $description = '';

    public $isOpen = false;

    public $taskId = null;

    // Form fields
    public $periodId;

    public $title = '';

    public $hours = 0;

    public $minutes = 0;

    // Computed properties for view
    public $periods = [];

    protected $listeners = ['openTaskForm'];

    public $completionMethod = 'time'; // 'time' por defecto

    public $subtasks = []; // Array para nuevas subtareas inputs

    public function mount()
    {
        // Load periods for dropdown
        $this->periods = Period::where('user_id', auth()->id())->orderBy('start_date', 'desc')->get();
    }

    public function openTaskForm($payload = [])
    {
        $this->reset(['taskId', 'title', 'description', 'periodId', 'hours', 'minutes', 'subtasks', 'completionMethod']);

        $this->taskId = $payload['taskId'] ?? null;
        $defaultPeriodId = $payload['periodId'] ?? null;

        if ($this->taskId) {
            $task = Task::findOrFail($this->taskId);
            $this->periodId = $task->period_id;
            $this->title = $task->title;
            $this->description = $task->description ?? '';
            $this->completionMethod = $task->completion_method ?? 'time';
            $this->subtasks = $task->subtasks()->get()->map(function ($subtask) {
                return [
                    'title' => $subtask->title,
                    'description' => $subtask->description ?? '',
                    'is_completed' => (bool) $subtask->is_completed,
                ];
            })->toArray();
            $this->hours = intdiv($task->estimated_minutes, 60);
            $this->minutes = $task->estimated_minutes % 60;
        } else {
            // New Task
            $this->periodId = $defaultPeriodId ?? Period::where('user_id', auth()->id())->orderBy('start_date', 'desc')->first()?->id;
            $this->hours = 0;
            $this->minutes = 0;
            $this->completionMethod = 'time';
            $this->subtasks = [];
        }

        $this->isOpen = true;
    }

    public function save()
    {
        $this->validate([
            'periodId' => 'required|exists:periods,id',
            'title' => 'required|min:3',
            'description' => 'nullable|string',
            'hours' => 'required|integer|min:0',
            'minutes' => 'required|integer|min:0|max:59',
            'completionMethod' => 'required|in:time,subtasks',
            'subtasks.*.title' => 'required_if:completionMethod,subtasks|string|max:255',
        ]);

        $totalMinutes = ($this->hours * 60) + $this->minutes;

        if ($this->taskId) {
            $task = Task::findOrFail($this->taskId);
            $task->update([
                'period_id' => $this->periodId,
                'title' => $this->title,
                'description' => $this->description,
                'estimated_minutes' => $totalMinutes,
                'completion_method' => $this->completionMethod,
            ]);
            $message = 'Tarea actualizada correctamente.';
        } else {
            $task = Task::create([
                'period_id' => $this->periodId,
                'title' => $this->title,
                'description' => $this->description,
                'estimated_minutes' => $totalMinutes,
                'status' => TaskStatus::Pending,
                'completion_method' => $this->completionMethod,
            ]);
            $message = 'Tarea creada correctamente.';
        }

        // Guardar Subtareas (siempre, independiente del método de progreso)
        // Para edición: borramos y recreamos
        if ($this->taskId) {
            $task->subtasks()->delete();
        }

        foreach ($this->subtasks as $subtaskData) {
            if (! empty($subtaskData['title'])) {
                $task->subtasks()->create([
                    'title' => $subtaskData['title'],
                    'description' => $subtaskData['description'] ?? '',
                    'is_completed' => $subtaskData['is_completed'] ?? false,
                ]);
            }
        }

        // Verificar y actualizar estado si es por Subtareas
        if ($this->completionMethod === 'subtasks' && $task->subtasks()->count() > 0) {
            $total = $task->subtasks()->count();
            $completed = $task->subtasks()->where('is_completed', true)->count();

            if ($total === $completed) {
                $task->update(['status' => TaskStatus::Completed]);
            } else {
                // Si estaba completada y agregamos una nueva o desmarcamos, volver a en curso
                // Podríamos usar InProgress o Pending. Si ya tiene avance, InProgress.
                if ($task->status === TaskStatus::Completed) {
                    $task->update(['status' => TaskStatus::InProgress]); // o Pending
                }
            }
        }

        $this->isOpen = false;
        $this->dispatch('taskSaved'); // Notify parent to refresh
        session()->flash('message', $message);
    }

    public function addSubtask()
    {
        $this->subtasks[] = ['title' => '', 'description' => '', 'is_completed' => false];
    }

    public function removeSubtask($index)
    {
        unset($this->subtasks[$index]);
        $this->subtasks = array_values($this->subtasks); // Reindexar
    }

    public function close()
    {
        $this->isOpen = false;
        $this->reset(['taskId', 'title', 'hours', 'minutes', 'periodId']);
    }

    public function render()
    {
        return view('livewire.modals.task-form');
    }
}
