<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoardConection extends Model
{
    protected $fillable = [
        'from_item_id',
        'to_item_id',
        'type',
    ];

    public function fromItem()
    {
        return $this->belongsTo(BoardItem::class, 'from_item_id');
    }

    public function toItem()
    {
        return $this->belongsTo(BoardItem::class, 'to_item_id');
    }
}
