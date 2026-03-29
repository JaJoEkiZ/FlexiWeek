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

    public function saveTask($draftTask)
    {
        // El frontend envía el objeto $draftTask completo
        $taskId = $draftTask['id'] ?? null;
        
        // Bloquear edición de tareas canceladas
        if ($taskId) {
            $existingTask = Task::find($taskId);
            if ($existingTask && $existingTask->status === TaskStatus::Cancelled) {
                session()->flash('message', 'No se puede editar una tarea cancelada.');
                $this->isOpen = false;
                return;
            }
        }

        // Convertir a enteros para ignorar ceros a la izquierda
        $hours = (int) ($draftTask['hours'] ?? 0);
        $minutes = (int) ($draftTask['minutes'] ?? 0);

        // Simulamos setear las propiedades locales para usar el método validate de Livewire
        // O podemos simplemente usar Validator::make
        $validator = \Illuminate\Support\Facades\Validator::make($draftTask, [
            'periodId' => 'required|exists:periods,id',
            'title' => 'required|min:3',
            'description' => 'nullable|string',
            'hours' => 'required|integer|min:0',
            'minutes' => 'required|integer|min:0|max:59',
            'completionMethod' => 'required|in:time,subtasks',
            'subtasks' => 'array',
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

        if ($validator->fails()) {
            // Pasamos los errores al componente principal de Livewire
            foreach ($validator->errors()->toArray() as $field => $messages) {
                foreach ($messages as $msg) {
                    $this->addError($field, $msg);
                }
            }
            return;
        }

        $completionMethod = $draftTask['completionMethod'] ?? 'time';
        $subtasks = $draftTask['subtasks'] ?? [];

        // Validación adicional: si es por subtareas, debe tener al menos 1
        if ($completionMethod === 'subtasks' && empty($subtasks)) {
            $this->addError('subtasks', 'Debes agregar al menos 1 subtarea para usar el control por subtareas.');
            return;
        }

        $totalMinutes = ($hours * 60) + $minutes;

        // Validación adicional: si es por tiempo, debe tener al menos 10 minutos
        if ($completionMethod === 'time' && $totalMinutes < 10) {
            $this->addError('minutes', 'Las tareas por tiempo deben tener al menos 10 minutos.');
            return;
        }

        if ($taskId) {
            $task = Task::findOrFail($taskId);
            $task->update([
                'period_id' => $draftTask['periodId'],
                'title' => $draftTask['title'],
                'description' => $draftTask['description'] ?? '',
                'estimated_minutes' => $totalMinutes,
                'completion_method' => $completionMethod,
                'is_persistent' => $draftTask['isPersistent'] ?? false,
            ]);
            $message = 'Tarea actualizada correctamente.';
        } else {
            $task = Task::create([
                'period_id' => $draftTask['periodId'],
                'title' => $draftTask['title'],
                'description' => $draftTask['description'] ?? '',
                'estimated_minutes' => $totalMinutes,
                'status' => TaskStatus::Pending,
                'completion_method' => $completionMethod,
                'is_persistent' => $draftTask['isPersistent'] ?? false,
            ]);
            $message = 'Tarea creada correctamente.';
        }

        // Guardar Subtareas
        if ($taskId) {
            $task->subtasks()->delete();
        }

        foreach ($subtasks as $subtaskData) {
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

        // Refrescar modelo y relaciones para recalcular progreso
        $task->refresh();
        $task->load(['subtasks', 'timeLogs']);

        if ($completionMethod === 'subtasks' && $task->subtasks()->count() > 0) {
            $total = $task->subtasks()->count();
            $completed = $task->subtasks()->where('is_completed', true)->count();

            if ($total === $completed) {
                $task->update(['status' => TaskStatus::Completed]);
            } else {
                if ($task->status === TaskStatus::Completed) {
                    $task->update(['status' => TaskStatus::InProgress]);
                }
            }
        }

        if ($completionMethod === 'time' && $task->status === TaskStatus::Completed) {
            if ($task->progress < 100) {
                $task->update(['status' => TaskStatus::InProgress]);
            }
        }

        $task->touch(); // Force updated_at to ensure wire:key causes a total component remount

        $this->isOpen = false;
        $this->dispatch('taskSaved'); // Notify parent to refresh
        $this->dispatch('toast', message: $message, type: 'success');
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
