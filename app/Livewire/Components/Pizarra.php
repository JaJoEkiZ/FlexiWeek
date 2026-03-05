<?php

namespace App\Livewire\Components;

use App\Models\BoardItem;
use App\Models\BoardItemSubtask;
use App\Models\BoardConection;
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
            'title'   => 'Nueva idea',
            'notes'   => null,
            'pos_x'   => $x,
            'pos_y'   => $y,
            'width'   => 200,
            'height'  => 150,
            'color'   => '#3B82F6',
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
            'title'         => $title,
            'is_completed'  => false,
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
            'is_completed' => !$subtask->is_completed,
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

        if (!$exists) {
            BoardConection::create([
                'from_item_id' => $fromId,
                'to_item_id'   => $toId,
                'type'         => $type,
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

    public function render()
    {
        return view('livewire.components.pizarra');
    }
}