<?php

namespace App\Livewire\Components;

use App\Models\Period;
use Livewire\Component;

class TaskNavbar extends Component
{
    public $selectedPeriodId;

    public $currentPeriod;

    protected $listeners = ['periodSelected' => 'updatePeriod', 'taskSaved' => '$refresh'];

    public function mount($selectedPeriodId = null)
    {
        $this->selectedPeriodId = $selectedPeriodId;
        $this->loadPeriod();
    }

    public function loadPeriod()
    {
        $this->currentPeriod = Period::with(['tasks' => function ($query) {
            $query->orderBy('sort_order', 'asc');
        }])->find($this->selectedPeriodId);
    }

    public function updatePeriod($periodId)
    {
        $this->selectedPeriodId = $periodId;
        $this->loadPeriod();
    }

    public function openTaskForm()
    {
        $this->dispatch('openTaskForm', taskId: null, defaultPeriodId: $this->selectedPeriodId);
    }

    public function render()
    {
        return view('livewire.components.task-navbar');
    }
}
