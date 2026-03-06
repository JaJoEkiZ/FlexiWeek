<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoardItem extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'notes',
        'pos_x',
        'pos_y',
        'width',
        'height',
        'color',
        'z_index',
    ];

    protected $casts = [
        'pos_x' => 'float',
        'pos_y' => 'float',
        'width' => 'float',
        'height' => 'float',
        'z_index' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subtasks()
    {
        return $this->hasMany(BoardItemSubtask::class);
    }

    public function connectionsFrom()
    {
        return $this->hasMany(BoardConection::class, 'from_item_id');
    }

    public function connectionsTo()
    {
        return $this->hasMany(BoardConection::class, 'to_item_id');
    }

    // Eliminar subtareas y conexiones manualmente (compatible con sqlsrv)
    protected static function booted()
    {
        static::deleting(function ($item) {
            $item->subtasks()->delete();
            $item->connectionsFrom()->delete();
            $item->connectionsTo()->delete();
        });
    }
}
