<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoardItemSubtask extends Model
{
    protected $table = 'board_item_subtask';

    protected $fillable = [
        'board_item_id',
        'title',
        'is_completed',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    public function boardItem()
    {
        return $this->belongsTo(BoardItem::class);
    }
}
