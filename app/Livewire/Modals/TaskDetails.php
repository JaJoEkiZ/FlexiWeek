<?php

namespace App\Livewire\Modals;

use App\Models\Task;
use Livewire\Component;

class TaskDetails extends Component
{
    public $isOpen = false;

    public $isEditing = false;

    public $taskId = null;

    public $task = null;

    // Editable fields
    public $description = '';

    public $subtaskDescriptions = [];

    protected $listeners = ['openTaskDetails'];

    public function openTaskDetails($taskId)
    {
        $this->taskId = $taskId;
        $this->task = Task::with(['subtasks', 'timeLogs', 'period'])->findOrFail($taskId);
        $this->description = $this->task->description ?? '';

        // Load subtask descriptions
        $this->subtaskDescriptions = [];
        foreach ($this->task->subtasks as $subtask) {
            $this->subtaskDescriptions[$subtask->id] = $subtask->description ?? '';
        }

        $this->isEditing = false;
        $this->isOpen = true;
    }

    public function toggleEdit()
    {
        $this->isEditing = ! $this->isEditing;
    }

    public function save()
    {
        // Save task description
        if ($this->task->description !== $this->description) {
            $this->task->update(['description' => $this->description]);
        }

        // Save subtask descriptions
        foreach ($this->subtaskDescriptions as $subtaskId => $desc) {
            $subtask = $this->task->subtasks->firstWhere('id', $subtaskId);
            if ($subtask && $subtask->description !== $desc) {
                $subtask->update(['description' => $desc]);
            }
        }

        $this->isEditing = false;
        $this->dispatch('taskSaved');
        session()->flash('details-message', 'Detalles guardados correctamente.');
    }

    public function close()
    {
        $this->isOpen = false;
        $this->reset(['taskId', 'task', 'description', 'subtaskDescriptions', 'isEditing']);
    }

    public function render()
    {
        return view('livewire.modals.task-details');
    }
}
