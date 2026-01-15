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

    public $hours = 0;

    public $minutes = 0;

    // Computed properties for view
    public $periods = [];

    protected $listeners = ['openTaskForm'];

    public function mount()
    {
        // Load periods for dropdown
        $this->periods = Period::where('user_id', auth()->id())->orderBy('start_date', 'desc')->get();
    }

    public function openTaskForm($payload = [])
    {
        $this->reset(['taskId', 'title', 'periodId', 'hours', 'minutes']);

        $this->taskId = $payload['taskId'] ?? null;
        $defaultPeriodId = $payload['periodId'] ?? null;

        if ($this->taskId) {
            $task = Task::findOrFail($this->taskId);
            $this->periodId = $task->period_id;
            $this->title = $task->title;

            $this->hours = intdiv($task->estimated_minutes, 60);
            $this->minutes = $task->estimated_minutes % 60;
        } else {
            // New Task
            $this->periodId = $defaultPeriodId ?? Period::where('user_id', auth()->id())->orderBy('start_date', 'desc')->first()?->id;
            $this->hours = 0;
            $this->minutes = 0;
        }

        $this->isOpen = true;
    }

    public function save()
    {
        $this->validate([
            'periodId' => 'required|exists:periods,id',
            'title' => 'required|min:3',
            'hours' => 'required|integer|min:0',
            'minutes' => 'required|integer|min:0|max:59',
        ]);

        $totalMinutes = ($this->hours * 60) + $this->minutes;

        if ($this->taskId) {
            $task = Task::findOrFail($this->taskId);
            $task->update([
                'period_id' => $this->periodId,
                'title' => $this->title,
                'estimated_minutes' => $totalMinutes,
            ]);
            $message = 'Tarea actualizada correctamente.';
        } else {
            Task::create([
                'period_id' => $this->periodId,
                'title' => $this->title,
                'estimated_minutes' => $totalMinutes,
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
        $this->reset(['taskId', 'title', 'hours', 'minutes', 'periodId']);
    }

    public function render()
    {
        return view('livewire.modals.task-form');
    }
}
