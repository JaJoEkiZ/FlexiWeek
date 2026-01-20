<?php

namespace App\Livewire\Modals;

use App\Models\Period;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
    public $pendingPeriodData = [];

    protected $listeners = ['openPeriodForm'];

    public function openPeriodForm($periodId = null)
    {
        $this->reset(['periodId', 'name', 'startDate', 'endDate', 'pendingPeriodData']);

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

        Log::info('PeriodForm save called', [
            'periodId' => $this->periodId,
            'start' => $this->startDate,
            'end' => $this->endDate,
        ]);

        // Check for overlaps and resolve automatically
        $this->resolveOverlaps();

        $this->savePeriodChanges([
            'name' => $this->name,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ]);

        $this->close();
        session()->flash('message', 'Semana guardada correctamente.');
    }

    public function resolveOverlaps()
    {
        $newStart = Carbon::parse($this->startDate);
        $newEnd = Carbon::parse($this->endDate);
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

            Log::info('Conflicting periods found and fixing', ['count' => $conflictingPeriods->count()]);

            foreach ($conflictingPeriods as $conflict) {
                $cStart = Carbon::parse($conflict->start_date);
                $cEnd = Carbon::parse($conflict->end_date);

                // Case 1: New period completely covers old one -> Delete old one
                if ($cStart->gte($newStart) && $cEnd->lte($newEnd)) {
                    $conflict->delete();

                    continue;
                }

                // Case 2: Overlap at the end of old period -> Shorten end
                if ($cStart->lt($newStart) && $cEnd->gte($newStart)) {
                    $conflict->update(['end_date' => $newStart->copy()->subDay()]);
                }

                // Case 3: Overlap at the start of old period -> Shorten start
                if ($cStart->lte($newEnd) && $cEnd->gt($newEnd)) {
                    $conflict->update(['start_date' => $newEnd->copy()->addDay()]);
                }
            }
        });
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
        $this->reset(['periodId', 'name', 'startDate', 'endDate', 'pendingPeriodData']);
    }

    public function render()
    {
        return view('livewire.modals.period-form');
    }
}
