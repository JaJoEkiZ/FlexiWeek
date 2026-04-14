<?php

namespace App\Livewire\Modals;

use App\Models\Period;
use App\Models\Task;
use Livewire\Component;

class RealityCheck extends Component
{
    public $isOpen = false;
    public $periodId;
    public $period;
    public $draftAction = null;
    public $tasksData = [];
    public $availableMinutes = 0;
    public $assignedMinutes = 0;

    protected $listeners = ['openRealityCheck'];

    public function openRealityCheck($periodId, $draftAction = null)
    {
        $this->periodId = $periodId;
        $this->draftAction = $draftAction;
        $this->loadData();
        $this->isOpen = true;
    }

    public function loadData()
    {
        $this->period = Period::find($this->periodId);
        if ($this->period) {
            $this->availableMinutes = $this->period->available_minutes;
            $this->assignedMinutes = $this->period->assigned_minutes;

            if ($this->draftAction) {
                if ($this->draftAction['type'] === 'create_task' || $this->draftAction['type'] === 'move_task') {
                    $this->assignedMinutes += $this->draftAction['effective_minutes'];
                } elseif ($this->draftAction['type'] === 'edit_task') {
                    $this->assignedMinutes -= $this->draftAction['old_minutes'];
                    $this->assignedMinutes += $this->draftAction['effective_minutes'];
                }
            }
            
            // Load tasks excluding cancelled ones
            $this->tasksData = $this->period->tasks()
                ->where('status', '!=', \App\Enums\TaskStatus::Cancelled)
                ->with('subtasks')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($task) {
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'estimated_minutes' => $task->effective_estimated_minutes,
                        'completion_method' => $task->completion_method,
                        'subtasks' => $task->subtasks->map(function($st) {
                            return [
                                'id' => $st->id,
                                'title' => $st->title,
                                'estimated_minutes' => $st->estimated_minutes
                            ];
                        })->toArray()
                    ];
                })->toArray();
        }
    }

    public function resolveAndExecute($adjustments, $draftActionFinal)
    {
        foreach ($adjustments as $adj) {
            if ($adj['type'] === 'delete_task') {
                $task = Task::find($adj['id']);
                if ($task) $task->delete();
            } elseif ($adj['type'] === 'delete_subtask') {
                $subtask = \App\Models\Subtask::find($adj['id']);
                if ($subtask) $subtask->delete();
            } elseif ($adj['type'] === 'reduce_task') {
                $task = Task::find($adj['id']);
                if ($task && $task->completion_method === 'time') {
                    $newEstimated = max(0, $task->estimated_minutes - $adj['minutes_to_reduce']);
                    $task->update(['estimated_minutes' => $newEstimated]);
                }
            } elseif ($adj['type'] === 'reduce_subtask') {
                $subtask = \App\Models\Subtask::find($adj['id']);
                if ($subtask) {
                    $newEstimated = max(0, $subtask->estimated_minutes - $adj['minutes_to_reduce']);
                    $subtask->update(['estimated_minutes' => $newEstimated]);
                }
            }
        }
        
        if ($draftActionFinal) {
            $this->draftAction = $draftActionFinal;
            
            if ($this->draftAction['type'] === 'create_task' || $this->draftAction['type'] === 'edit_task') {
                $this->dispatch('resumeTaskFormSave', draftTask: $this->draftAction['payload']);
            } elseif ($this->draftAction['type'] === 'move_task') {
                $this->dispatch('resumeTaskMove', taskId: $this->draftAction['taskId'], newPeriodId: $this->draftAction['newPeriodId']);
            }
        } else {
            $this->dispatch('taskSaved');
        }
        
        $this->close();
    }

    public function close()
    {
        $this->isOpen = false;
    }

    public function render()
    {
        return view('livewire.modals.reality-check');
    }
}
