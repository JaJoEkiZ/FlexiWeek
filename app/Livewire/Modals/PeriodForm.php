<?php

namespace App\Livewire\Modals;

use App\Models\Period;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PeriodForm extends Component
{
    public $isOpen = false;

    public $periodId = null;

    // Form fields
    public $name = '';

    public $startDate = '';

    public $endDate = '';

    // Conflict handling
    public $isOverlapModalOpen = false;

    public $overlapMessage = '';

    public $pendingPeriodData = [];

    protected $listeners = ['openPeriodForm'];

    public function openPeriodForm($periodId = null)
    {
        $this->reset(['periodId', 'name', 'startDate', 'endDate', 'isOverlapModalOpen', 'pendingPeriodData']);

        $this->periodId = $periodId;

        if ($this->periodId) {
            $period = Period::findOrFail($this->periodId);
            $this->name = $period->name;
            $this->startDate = $period->start_date;
            $this->endDate = $period->end_date;
        }

        $this->isOpen = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'nullable|string|max:255',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ]);

        // Check for overlaps
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);

        $query = Period::query()->where('user_id', auth()->id());
        if ($this->periodId) {
            $query->where('id', '!=', $this->periodId);
        }

        $conflictingPeriods = $query->where(function ($query) use ($start, $end) {
            $query->whereBetween('start_date', [$start, $end])
                ->orWhereBetween('end_date', [$start, $end])
                ->orWhere(function ($q) use ($start, $end) {
                    $q->where('start_date', '<', $start)
                        ->where('end_date', '>', $end);
                });
        })->get();

        if ($conflictingPeriods->isNotEmpty()) {
            $this->isOverlapModalOpen = true;
            $this->overlapMessage = 'El periodo seleccionado se superpone con '.$conflictingPeriods->count().' periodo(s) existente(s).';

            $this->pendingPeriodData = [
                'name' => $this->name,
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
            ];

            return;
        }

        $this->savePeriodChanges([
            'name' => $this->name,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ]);

        $this->close();
        session()->flash('message', 'Semana guardada correctamente.');
    }

    public function confirmOverlapAdjustment()
    {
        if (empty($this->pendingPeriodData)) {
            return;
        }

        $newStart = Carbon::parse($this->pendingPeriodData['start_date']);
        $newEnd = Carbon::parse($this->pendingPeriodData['end_date']);
        $currentId = $this->periodId;

        DB::transaction(function () use ($newStart, $newEnd, $currentId) {
            $query = Period::query()->where('user_id', auth()->id());
            if ($currentId) {
                $query->where('id', '!=', $currentId);
            }

            $conflictingPeriods = $query->where(function ($query) use ($newStart, $newEnd) {
                $query->whereBetween('start_date', [$newStart, $newEnd])
                    ->orWhereBetween('end_date', [$newStart, $newEnd])
                    ->orWhere(function ($q) use ($newStart, $newEnd) {
                        $q->where('start_date', '<', $newStart)
                            ->where('end_date', '>', $newEnd);
                    });
            })->get();

            foreach ($conflictingPeriods as $conflict) {
                $cStart = Carbon::parse($conflict->start_date);
                $cEnd = Carbon::parse($conflict->end_date);

                if ($cStart->gte($newStart) && $cEnd->lte($newEnd)) {
                    $conflict->delete();

                    continue;
                }

                if ($cStart->lt($newStart) && $cEnd->gte($newStart)) {
                    $conflict->update(['end_date' => $newStart->copy()->subDay()]);
                }

                if ($cStart->lte($newEnd) && $cEnd->gt($newEnd)) {
                    $conflict->update(['start_date' => $newEnd->copy()->addDay()]);
                }
            }

            $this->savePeriodChanges($this->pendingPeriodData);
        });

        $this->isOverlapModalOpen = false;
        $this->close();
        session()->flash('message', 'Semana y conflictos ajustados correctamente.');
    }

    public function cancelOverlap()
    {
        $this->isOverlapModalOpen = false;
        $this->pendingPeriodData = [];
    }

    protected function savePeriodChanges($data)
    {
        if ($this->periodId) {
            $period = Period::findOrFail($this->periodId);
            $period->update($data);
        } else {
            $data['user_id'] = auth()->id();
            $period = Period::create($data);
            $this->dispatch('periodSaved'); // Refresh parent
        }
        $this->dispatch('periodSaved');
    }

    public function close()
    {
        $this->isOpen = false;
        $this->isOverlapModalOpen = false;
        $this->reset(['periodId', 'name', 'startDate', 'endDate', 'pendingPeriodData']);
    }

    public function render()
    {
        return view('livewire.modals.period-form');
    }
}
