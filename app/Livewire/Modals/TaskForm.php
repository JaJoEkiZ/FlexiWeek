<?php

namespace App\Livewire\Modals;

use App\Enums\TaskStatus;
use App\Models\Period;
use App\Models\Task;
use Livewire\Component;

class TaskForm extends Component
{
    public $isOpen = false;

    public $taskId = null;

    // Form fields
    public $periodId;

    public $title = '';

    public $estimatedMinutes = 0;

    // Computed properties for view
    public $periods = [];

    protected $listeners = ['openTaskForm'];

    public function mount()
    {
        // Load periods for dropdown
        // Assuming we want active periods or all? Let's load future/current ones primarily,
        // or just all since a task could theoretically be backdated?
        // For now, let's just grab them all to be safe, or optimize later.
        $this->periods = Period::orderBy('start_date', 'desc')->get();
    }

    public function openTaskForm($payload = [])
    {
        $this->reset(['taskId', 'title', 'estimatedMinutes', 'periodId']);

        $this->taskId = $payload['taskId'] ?? null;
        $defaultPeriodId = $payload['periodId'] ?? null;

        if ($this->taskId) {
            $task = Task::findOrFail($this->taskId);
            $this->periodId = $task->period_id;
            $this->title = $task->title;
            $this->estimatedMinutes = $task->estimated_minutes;
        } else {
            // New Task
            $this->periodId = $defaultPeriodId ?? Period::orderBy('start_date', 'desc')->first()?->id;
        }

        $this->isOpen = true;
    }

    public function save()
    {
        $this->validate([
            'periodId' => 'required|exists:periods,id',
            'title' => 'required|min:3',
            'estimatedMinutes' => 'required|integer|min:0',
        ]);

        if ($this->taskId) {
            $task = Task::findOrFail($this->taskId);
            $task->update([
                'period_id' => $this->periodId,
                'title' => $this->title,
                'estimated_minutes' => $this->estimatedMinutes,
            ]);
            $message = 'Tarea actualizada correctamente.';
        } else {
            Task::create([
                'period_id' => $this->periodId,
                'title' => $this->title,
                'estimated_minutes' => $this->estimatedMinutes,
                'status' => TaskStatus::Pending,
            ]);
            $message = 'Tarea creada correctamente.';
        }

        $this->isOpen = false;
        $this->dispatch('taskSaved'); // Notify parent to refresh
        session()->flash('message', $message);
    }

    public function close()
    {
        $this->isOpen = false;
        $this->reset(['taskId', 'title', 'estimatedMinutes', 'periodId']);
    }

    public function render()
    {
        return view('livewire.modals.task-form');
    }
}
