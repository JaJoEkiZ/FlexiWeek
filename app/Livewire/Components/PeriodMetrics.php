<?php

namespace App\Livewire\Components;

use App\Models\Period;
use App\Services\MetricsService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PeriodMetrics extends Component
{
    public $selectedPeriodId;

    public $mode = 'period';

    public $rangeStart;

    public $rangeEnd;

    protected $listeners = ['periodSelected' => 'updatePeriod'];

    public function mount($period = null)
    {
        if ($period) {
            $exists = Period::where('id', $period)
                ->where('user_id', auth()->id())
                ->exists();
            $this->selectedPeriodId = $exists ? $period : null;
        }

        // Si no vino period en la URL, usar el default
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

        $this->rangeStart = now()->startOfMonth()->format('Y-m-d');
        $this->rangeEnd = now()->endOfMonth()->format('Y-m-d');
    }

    public function updatePeriod($periodId)
    {
        // Redirigir a la nueva URL con el período seleccionado
        $this->redirect(route('metrics', $periodId), navigate: true);
    }

    public function switchMode($mode)
    {
        $this->mode = $mode;
    }

    public function getMetricsProperty()
    {
        $service = app(MetricsService::class);

        if ($this->mode === 'range') {
            return $service->calculateRangeMetrics($this->rangeStart, $this->rangeEnd, auth()->id());
        }

        if (! $this->selectedPeriodId) {
            return $service->emptyMetrics();
        }

        return $service->calculatePeriodMetrics($this->selectedPeriodId);
    }

    public function render()
    {
        $periods = Period::where('user_id', auth()->id())
            ->orderBy('start_date', 'desc')
            ->get();
    
        $metrics = $this->metrics;
    
        // Cargar el período actual para pasárselo al layout
        $currentPeriod = $this->selectedPeriodId
            ? Period::find($this->selectedPeriodId)
            : null;
    
        if ($metrics['total'] > 0) {
            $this->dispatch('metricsUpdated', $metrics);
        }
    
        return view('livewire.components.period-metrics', [
            'metrics'          => $metrics,
            'periods'          => $periods,
            'selectedPeriodId' => $this->selectedPeriodId,
            'currentPeriod'    => $currentPeriod,
        ]);
    }
}
