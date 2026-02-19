<?php

namespace App\Livewire\Modals;

use App\Enums\TaskStatus;
use App\Models\Period;
use App\Models\Task;
use Livewire\Component;

class DuplicateTask extends Component
{
    public $isOpen = false;

    public $sourceTaskId = null;

    public $sourceTask = null;

    public $targetPeriodId = null;

    public $targetPeriodTasks = [];

    public $selectedSubtasks = [];

    public $periods = [];

    protected $listeners = ['openDuplicateTask'];

    public function openDuplicateTask($taskId)
    {
        $this->sourceTaskId = $taskId;
        $this->sourceTask = Task::with('subtasks')->findOrFail($taskId);
        $this->periods = Period::where('user_id', auth()->id())
            ->orderBy('start_date', 'desc')
            ->get();
        $this->targetPeriodId = null;
        $this->targetPeriodTasks = [];
        $this->selectedSubtasks = $this->sourceTask->subtasks->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        $this->isOpen = true;
    }

    public function updatedTargetPeriodId($value)
    {
        if ($value) {
            $this->targetPeriodTasks = Task::where('period_id', $value)
                ->orderBy('sort_order')
                ->get()
                ->toArray();
        } else {
            $this->targetPeriodTasks = [];
        }
    }

    public function duplicate()
    {
        if (! $this->targetPeriodId || ! $this->sourceTask) {
            return;
        }

        // Obtener último sort_order del período destino
        $maxOrder = Task::where('period_id', $this->targetPeriodId)->max('sort_order') ?? 0;

        $newTask = Task::create([
            'period_id' => $this->targetPeriodId,
            'title' => $this->sourceTask->title,
            'description' => $this->sourceTask->description,
            'estimated_minutes' => $this->sourceTask->estimated_minutes,
            'status' => TaskStatus::Pending,
            'completion_method' => $this->sourceTask->completion_method,
            'is_persistent' => $this->sourceTask->is_persistent,
            'sort_order' => $maxOrder + 1,
        ]);

        // Duplicar subtareas seleccionadas sin progreso
        if (! empty($this->selectedSubtasks)) {
            foreach ($this->sourceTask->subtasks as $subtask) {
                if (in_array((string) $subtask->id, $this->selectedSubtasks)) {
                    $newTask->subtasks()->create([
                        'title' => $subtask->title,
                        'description' => $subtask->description,
                        'estimated_minutes' => $subtask->estimated_minutes,
                        'spent_minutes' => 0,
                        'is_completed' => false,
                    ]);
                }
            }
        }

        $this->isOpen = false;
        $this->dispatch('taskSaved');
        session()->flash('message', "Tarea \"{$this->sourceTask->title}\" duplicada correctamente.");
    }

    public function close()
    {
        $this->isOpen = false;
        $this->reset(['sourceTaskId', 'sourceTask', 'targetPeriodId', 'targetPeriodTasks', 'selectedSubtasks']);
    }

    public function render()
    {
        return view('livewire.modals.duplicate-task');
    }
}
