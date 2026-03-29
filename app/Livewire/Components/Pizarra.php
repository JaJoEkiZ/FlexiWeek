<?php

namespace App\Livewire\Components;

use App\Enums\TaskStatus;
use App\Models\BoardConection;
use App\Models\BoardItem;
use App\Models\BoardItemSubtask;
use App\Models\Period;
use App\Models\Subtask;
use App\Models\Task;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Pizarra extends Component
{
    public array $items = [];

    public function mount()
    {
        $this->loadItems();
    }
    public function groupItems(array $ids, ?string $title = 'Nuevo Grupo')
    {
        $itemsToGroup = BoardItem::where('user_id', auth()->id())
            ->whereIn('id', $ids)
            ->get();

        if ($itemsToGroup->isEmpty()) return;

        $avgX = $itemsToGroup->avg('pos_x');
        $avgY = $itemsToGroup->avg('pos_y');
        $maxZ = BoardItem::where('user_id', auth()->id())->max('z_index') ?? 0;

        $group = BoardItem::create([
            'user_id' => auth()->id(),
            'title'   => empty($title) ? 'Nuevo Grupo' : $title,
            'pos_x'   => $avgX,
            'pos_y'   => $avgY,
            'width'   => 230,
            'height'  => 50,
            'color'   => '#007fd4', // Color base
            'z_index' => $maxZ + 1,
            'is_group'=> true,
        ]);

        // Asociar las ideas a este nuevo contenedor
        BoardItem::whereIn('id', $ids)
                 ->where('user_id', auth()->id())
                 ->update(['parent_id' => $group->id]);

        $this->loadItems();
    }
    public function ungroupItems($groupId)
    {
        $group = BoardItem::where('user_id', auth()->id())
            ->where('id', $groupId)
            ->where('is_group', true)
            ->first();

        if (!$group) return;

        // Desasociar las ideas a este contenedor
        BoardItem::where('parent_id', $group->id)
                 ->where('user_id', auth()->id())
                 ->update(['parent_id' => null]);

        $group->delete();

        $this->loadItems();
    }


    public function render()
    {
        $today = now()->format('Y-m-d');
        $currentPeriod = Period::where('user_id', auth()->id())
            ->where('end_date', '>=', $today)
            ->orderBy('start_date', 'asc')
            ->first()
            ?? Period::where('user_id', auth()->id())
                ->orderBy('start_date', 'desc')
                ->first();

        return view('livewire.components.pizarra', [
            'currentPeriod' => $currentPeriod,
        ]);
    }

    public function loadItems()
    {
        $this->items = BoardItem::with(['subtasks', 'connectionsFrom', 'connectionsTo', 'children'])
            ->where('user_id', auth()->id())
            ->get()
            ->toArray();

        return $this->items;
    }

    public function addItem($x = 100, $y = 100)
    {
        $maxZ = BoardItem::where('user_id', auth()->id())->max('z_index') ?? 0;

        BoardItem::create([
            'user_id' => auth()->id(),
            'title' => 'Nueva Idea',
            'notes' => null, // Keeping notes as it was in the original code
            'pos_x' => $x,
            'pos_y' => $y,
            'width' => 200,
            'height' => 70,
            'color' => '#3B82F6',
            'z_index' => $maxZ + 1,
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

    public function addChildToGroup($groupId, $title)
    {
        $group = BoardItem::where('id', $groupId)->where('user_id', auth()->id())->firstOrFail();
        
        $maxZ = BoardItem::where('user_id', auth()->id())->max('z_index') ?? 0;

        BoardItem::create([
            'user_id' => auth()->id(),
            'title' => $title,
            'parent_id' => $group->id,
            'pos_x' => $group->pos_x + 20,
            'pos_y' => $group->pos_y + 20,
            'width' => 200,
            'height' => 70,
            'color' => '#3B82F6',
            'z_index' => $maxZ + 1,
        ]);

        $this->loadItems();
    }

    public function extractFromGroup($childId)
    {
        $child = BoardItem::where('id', $childId)->where('user_id', auth()->id())->firstOrFail();
        $child->update(['parent_id' => null]);
        
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
            $this->loadItems();
        }
    }

    public function bringToFront($itemId)
    {
        $maxZ = BoardItem::where('user_id', auth()->id())->max('z_index') ?? 0;
        $item = BoardItem::where('id', $itemId)->where('user_id', auth()->id())->first();

        if ($item) {
            $item->update(['z_index' => $maxZ + 1]);
            $this->loadItems();
        }
    }

    public function sendToBack($itemId)
    {
        $minZ = BoardItem::where('user_id', auth()->id())->min('z_index') ?? 0;
        $item = BoardItem::where('id', $itemId)->where('user_id', auth()->id())->first();

        if ($item) {
            $item->update(['z_index' => $minZ - 1]);
            $this->loadItems();
        }
    }

    public function bringToFrontBulk(array $ids)
    {
        $maxZ = BoardItem::where('user_id', auth()->id())->max('z_index') ?? 0;
        $items = BoardItem::where('user_id', auth()->id())->whereIn('id', $ids)->get();
        foreach ($items as $i => $item) {
            $item->update(['z_index' => $maxZ + $i + 1]);
        }
        $this->loadItems();
    }

    public function sendToBackBulk(array $ids)
    {
        $minZ = BoardItem::where('user_id', auth()->id())->min('z_index') ?? 0;
        $items = BoardItem::where('user_id', auth()->id())->whereIn('id', $ids)->get();
        foreach ($items as $i => $item) {
            $item->update(['z_index' => $minZ - $i - 1]);
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

    public function promoteToTask($itemId, $periodId, $promotedIds = [], $selectedChildIds = null)
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
        if ($item->is_group) {
            $remainingCount = 0;
            // Si mandamos un Grupo a la semana, extraemos las hijas seleccionadas
            foreach ($item->children as $child) {
                if (is_array($selectedChildIds) && !in_array($child->id, $selectedChildIds)) {
                    $remainingCount++;
                    continue; // Se queda en el grupo
                }
                // Si pasa la condición, la promovemos
                $child->update(['parent_id' => null]);
                $promotedIds = $this->promoteToTask($child->id, $periodId, $promotedIds);
            }
            
            if ($remainingCount === 1) {
                // Sólo quedó 1 huérfana, la soltamos y borramos el grupo
                $orphan = BoardItem::where('parent_id', $item->id)->first();
                if ($orphan) {
                    $orphan->update(['parent_id' => null]);
                }
                $item->delete();
            } elseif ($remainingCount === 0) {
                // No quedó nadie, borramos el grupo
                $item->delete();
            }
            // Si quedan > 1, el grupo simplemente continúa existiendo con ellas adentro.

            if (count($promotedIds) === 1 || in_array(func_num_args(), [2, 4])) {
                $this->loadItems();
                $this->dispatch('taskSaved');
            }
            return $promotedIds;
        }
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

        // 5. Promover recursivamente los hijos (se debe hacer ANTES de borrar, porque el boot de delete() elimina las dependencias)
        foreach ($connections as $conn) {
            $promotedIds = $this->promoteToTask($conn->to_item_id, $periodId, $promotedIds);
        }

        // 6. Eliminar la idea de la pizarra (esto borra subtasks y conexiones por cascade/boot)
        $item->delete();

        // Si es la primera llamada de la recursión, disparamos recarga
        if (count($promotedIds) === 1 || in_array(func_num_args(), [2, 4])) {
            $this->loadItems();
            $this->dispatch('taskSaved'); // Actualiza el WeeklyPlanner para que muestre la nueva tarea
        }

        return $promotedIds;
    }
}
