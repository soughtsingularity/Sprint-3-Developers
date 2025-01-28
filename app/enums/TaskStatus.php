<?php

class TaskStatus {
    public const IN_PROGRESS = 'in_progress';
    public const COMPLETED = 'completed';
    public const PENDING = 'pending';

    public static function all(): array {
        return [
            self::IN_PROGRESS,
            self::COMPLETED,
            self::PENDING,
        ];
    }

    public static function validate(string $status): void {
        if (!in_array($status, self::all())) {
            throw new Exception("Estado inválido: {$status}");
        }
    }
}