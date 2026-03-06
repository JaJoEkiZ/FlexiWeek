<?php

namespace App\Livewire\Components;

use App\Enums\TaskStatus;
use App\Models\BoardConection;
use App\Models\BoardItem;
use App\Models\BoardItemSubtask;
use App\Models\Period;
use App\Models\Subtask;
use App\Models\Task;
use Livewire\Component;

class Pizarra extends Component
{
    public array $items = [];

    public function mount()
    {
        $this->loadItems();
    }

    public function loadItems()
    {
        $this->items = BoardItem::with(['subtasks', 'connectionsFrom', 'connectionsTo'])
            ->where('user_id', auth()->id())
            ->get()
            ->toArray();

        return $this->items;
    }

    public function addItem($x = 100, $y = 100)
    {
        BoardItem::create([
            'user_id' => auth()->id(),
            'title' => 'Nueva idea',
            'notes' => null,
            'pos_x' => $x,
            'pos_y' => $y,
            'width' => 200,
            'height' => 150,
            'color' => '#3B82F6',
        ]);

        $this->loadItems();
    }

    public function updateItem($id, $data)
    {
        $item = BoardItem::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $allowed = ['title', 'notes', 'pos_x', 'pos_y', 'width', 'height', 'color'];

        $filtered = array_intersect_key($data, array_flip($allowed));

        // Forzar que las coordenadas sean floats limpios
        foreach (['pos_x', 'pos_y', 'width', 'height'] as $numField) {
            if (isset($filtered[$numField])) {
                $filtered[$numField] = round((float) $filtered[$numField], 4);
            }
        }

        $item->update($filtered);

        $this->loadItems();
    }

    public function deleteItem($id)
    {
        $item = BoardItem::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Esto dispara el booted() del modelo que elimina subtasks y conexiones
        $item->delete();

        $this->loadItems();
    }

    public function addSubtask($itemId, $title)
    {
        $item = BoardItem::where('id', $itemId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        BoardItemSubtask::create([
            'board_item_id' => $item->id,
            'title' => $title,
            'is_completed' => false,
        ]);

        $this->loadItems();
    }

    public function toggleSubtask($subtaskId)
    {
        $subtask = BoardItemSubtask::whereHas('boardItem', function ($q) {
            $q->where('user_id', auth()->id());
        })
            ->where('id', $subtaskId)
            ->firstOrFail();

        $subtask->update([
            'is_completed' => ! $subtask->is_completed,
        ]);

        $this->loadItems();
    }

    public function deleteSubtask($subtaskId)
    {
        BoardItemSubtask::whereHas('boardItem', function ($q) {
            $q->where('user_id', auth()->id());
        })
            ->where('id', $subtaskId)
            ->delete();

        $this->loadItems();
    }

    public function addConnection($fromId, $toId, $type = 'depends_start')
    {
        // Verificar que ambas cajas pertenezcan al usuario
        $validIds = BoardItem::where('user_id', auth()->id())
            ->whereIn('id', [$fromId, $toId])
            ->pluck('id');

        if ($validIds->count() !== 2) {
            return;
        }

        // Evitar conexiones duplicadas
        $exists = BoardConection::where('from_item_id', $fromId)
            ->where('to_item_id', $toId)
            ->exists();

        if (! $exists) {
            BoardConection::create([
                'from_item_id' => $fromId,
                'to_item_id' => $toId,
                'type' => $type,
            ]);
        }

        $this->loadItems();
    }

    public function deleteConnection($connectionId)
    {
        BoardConection::whereHas('fromItem', function ($q) {
            $q->where('user_id', auth()->id());
        })
            ->where('id', $connectionId)
            ->delete();

        $this->loadItems();
    }

    public function getActivePeriods()
    {
        return Period::where('user_id', auth()->id())
            ->where('end_date', '>=', now()->format('Y-m-d'))
            ->orderBy('start_date', 'asc')
            ->get()
            ->toArray();
    }

    public function promoteToTask($itemId, $periodId, $promotedIds = [])
    {
        // Evitar ciclos infinitos en caso de conexiones circulares
        if (in_array($itemId, $promotedIds)) {
            return $promotedIds;
        }

        $item = BoardItem::with(['subtasks', 'connectionsFrom'])
            ->where('id', $itemId)
            ->where('user_id', auth()->id())
            ->first();

        if (! $item) {
            return $promotedIds;
        }

        $promotedIds[] = $itemId;

        // 1. Obtener el orden máximo en el periodo actual
        $maxOrder = Task::where('period_id', $periodId)->max('sort_order') ?? 0;

        // 2. Crear la Tarea
        $task = Task::create([
            'period_id' => $periodId,
            'title' => $item->title,
            'estimated_minutes' => 0,
            'status' => TaskStatus::Pending,
            'sort_order' => $maxOrder + 1,
        ]);

        // 3. Copiar las Subtareas
        foreach ($item->subtasks as $sub) {
            Subtask::create([
                'task_id' => $task->id,
                'title' => $sub->title,
                'is_completed' => $sub->is_completed,
                'estimated_minutes' => 0,
                'spent_minutes' => 0,
            ]);
        }

        // 4. Procesar dependencias en cascada (ideas que dependen de esta, es decir connectionsFrom donde esta es el origen)
        $connections = $item->connectionsFrom; // Las conexiones que salen de esta caja

        // 5. Eliminar la idea de la pizarra (esto borra subtasks y conexiones por cascade/boot)
        $item->delete();

        // 6. Promover recursivamente los hijos
        foreach ($connections as $conn) {
            $promotedIds = $this->promoteToTask($conn->to_item_id, $periodId, $promotedIds);
        }

        // Si es la primera llamada de la recursión, disparamos recarga
        if (count($promotedIds) === 1 || func_num_args() === 2) {
            $this->loadItems();
            $this->dispatch('taskSaved'); // Actualiza el WeeklyPlanner para que muestre la nueva tarea
        }

        return $promotedIds;
    }

    public function render()
    {
        return view('livewire.components.pizarra');
    }
}
