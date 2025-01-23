<?php

namespace App\Models\Enums;

enum TaskStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';

    public function getColor(): string
    {
        return match ($this) {
            self::PENDING => 'text-red-500',
            self::IN_PROGRESS => 'text-blue-500',
            self::COMPLETED => 'text-green-500',
        };
    }
}