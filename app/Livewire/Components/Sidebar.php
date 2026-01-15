<?php

namespace App\Livewire\Components;

use App\Models\Period;
use Livewire\Component;

class Sidebar extends Component
{
    public $selectedPeriodId;

    public $sidebarOpen = false; // For mobile toggle sync if needed, though mostly handled by Alpine

    public $showPastWeeks = false;

    protected $listeners = [
        'periodSaved' => '$refresh',
        'periodSelected' => 'updateSelectedPeriod',
    ];

    public function mount($selectedPeriodId = null)
    {
        $this->selectedPeriodId = $selectedPeriodId;
    }

    public function updateSelectedPeriod($periodId)
    {
        $this->selectedPeriodId = $periodId;
    }

    public function togglePastWeeks()
    {
        $this->showPastWeeks = ! $this->showPastWeeks;
    }

    public function selectPeriod($periodId)
    {
        $this->selectedPeriodId = $periodId;
        $this->dispatch('periodSelected', $periodId);
        // On mobile, we might want to close the sidebar?
        // We can dispatch an event for Alpine or let the user close it.
        // For now, let's just select.
    }

    public function openPeriodForm($periodId = null)
    {
        $this->dispatch('openPeriodForm', $periodId);
    }

    public function render()
    {
        $today = now()->format('Y-m-d');

        // Logic copied/adapted from WeeklyPlanner
        $activePeriods = Period::where('user_id', auth()->id())
            ->where('end_date', '>=', $today)
            ->orderBy('start_date', 'asc')
            ->get();

        $pastPeriods = Period::where('user_id', auth()->id())
            ->where('end_date', '<', $today)
            ->orderBy('start_date', 'desc')
            ->get();

        return view('livewire.components.sidebar', [
            'activePeriods' => $activePeriods,
            'pastPeriods' => $pastPeriods,
        ]);
    }
}
