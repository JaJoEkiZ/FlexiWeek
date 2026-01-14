<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Paused = 'paused';
    case Completed = 'completed';

    public function label(): string
    {
        return match($this) {
            self::Pending => 'Pendiente',
            self::InProgress => 'En Curso',
            self::Paused => 'Pausada',
            self::Completed => 'Finalizada',
        };
    }
    
    public function color(): string
    {
        return match($this) {
            self::Pending => 'gray',
            self::InProgress => 'blue',
            self::Paused => 'yellow',
            self::Completed => 'green',
        };
    }
}