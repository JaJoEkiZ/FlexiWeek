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

    public $isPersistent = false;

    public $subtasks = []; // Array para nuevas subtareas inputs

    public function mount()
    {
        // Load periods for dropdown
        $this->periods = Period::where('user_id', auth()->id())->orderBy('start_date', 'desc')->get();
    }

    public function openTaskForm($payload = [])
    {
        $this->reset(['taskId', 'title', 'description', 'periodId', 'hours', 'minutes', 'subtasks', 'completionMethod', 'isPersistent']);

        $this->taskId = $payload['taskId'] ?? null;
        $defaultPeriodId = $payload['periodId'] ?? null;

        if ($this->taskId) {
            $task = Task::findOrFail($this->taskId);
            $this->periodId = $task->period_id;
            $this->title = $task->title;
            $this->description = $task->description ?? '';
            $this->completionMethod = $task->completion_method ?? 'time';
            $this->isPersistent = (bool) $task->is_persistent;
            $this->subtasks = $task->subtasks()->get()->map(function ($subtask) {
                return [
                    'title' => $subtask->title,
                    'description' => $subtask->description ?? '',
                    'is_completed' => (bool) $subtask->is_completed,
                    'estimated_hours' => intdiv($subtask->estimated_minutes, 60),
                    'estimated_minutes' => $subtask->estimated_minutes % 60,
                    'spent_hours' => intdiv($subtask->spent_minutes, 60),
                    'spent_minutes' => $subtask->spent_minutes % 60,
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
            $this->isPersistent = false;
            $this->subtasks = [];
        }

        $this->isOpen = true;
    }

    public function save()
    {
        // Convertir a enteros para ignorar ceros a la izquierda
        $this->hours = (int) $this->hours;
        $this->minutes = (int) $this->minutes;

        $this->validate([
            'periodId' => 'required|exists:periods,id',
            'title' => 'required|min:3',
            'description' => 'nullable|string',
            'hours' => 'required|integer|min:0',
            'minutes' => 'required|integer|min:0|max:59',
            'completionMethod' => 'required|in:time,subtasks',
            'subtasks.*.title' => 'required_if:completionMethod,subtasks|string|max:255',
        ], [
            'periodId.required' => 'Debes seleccionar una semana.',
            'periodId.exists' => 'La semana seleccionada no es válida.',
            'title.required' => 'El título de la tarea es obligatorio.',
            'title.min' => 'El título debe tener al menos 3 caracteres.',
            'hours.required' => 'Las horas son obligatorias.',
            'hours.integer' => 'Las horas deben ser un número entero.',
            'hours.min' => 'Las horas no pueden ser negativas.',
            'minutes.required' => 'Los minutos son obligatorios.',
            'minutes.integer' => 'Los minutos deben ser un número entero.',
            'minutes.min' => 'Los minutos no pueden ser negativos.',
            'minutes.max' => 'Los minutos no pueden exceder 59.',
            'subtasks.*.title.required_if' => 'El título de la subtarea es obligatorio.',
            'subtasks.*.title.max' => 'El título de la subtarea es muy largo.',
        ]);

        // Validación adicional: si es por subtareas, debe tener al menos 1
        if ($this->completionMethod === 'subtasks' && empty($this->subtasks)) {
            $this->addError('subtasks', 'Debes agregar al menos 1 subtarea para usar el control por subtareas.');

            return;
        }

        $totalMinutes = ($this->hours * 60) + $this->minutes;

        // Validación adicional: si es por tiempo, debe tener al menos 10 minutos
        if ($this->completionMethod === 'time' && $totalMinutes < 10) {
            $this->addError('minutes', 'Las tareas por tiempo deben tener al menos 10 minutos.');

            return;
        }

        if ($this->taskId) {
            $task = Task::findOrFail($this->taskId);
            $task->update([
                'period_id' => $this->periodId,
                'title' => $this->title,
                'description' => $this->description,
                'estimated_minutes' => $totalMinutes,
                'completion_method' => $this->completionMethod,
                'is_persistent' => $this->isPersistent,
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
                'is_persistent' => $this->isPersistent,
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
                $estimatedMinutes = ((int) ($subtaskData['estimated_hours'] ?? 0) * 60) + (int) ($subtaskData['estimated_minutes'] ?? 0);
                $spentMinutes = ((int) ($subtaskData['spent_hours'] ?? 0) * 60) + (int) ($subtaskData['spent_minutes'] ?? 0);

                $task->subtasks()->create([
                    'title' => $subtaskData['title'],
                    'description' => $subtaskData['description'] ?? '',
                    'is_completed' => $subtaskData['is_completed'] ?? false,
                    'estimated_minutes' => $estimatedMinutes,
                    'spent_minutes' => $spentMinutes,
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
        $this->subtasks[] = [
            'title' => '',
            'description' => '',
            'is_completed' => false,
            'estimated_hours' => 0,
            'estimated_minutes' => 0,
            'spent_hours' => 0,
            'spent_minutes' => 0,
        ];
    }

    public function removeSubtask($index)
    {
        unset($this->subtasks[$index]);
        $this->subtasks = array_values($this->subtasks); // Reindexar
    }

    public function close()
    {
        $this->isOpen = false;
        $this->reset(['taskId', 'title', 'hours', 'minutes', 'periodId', 'isPersistent']);
    }

    public function render()
    {
        return view('livewire.modals.task-form');
    }
}
