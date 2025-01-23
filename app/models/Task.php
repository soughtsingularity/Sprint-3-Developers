<?php

namespace App\Models;

use DateTime;
use App\Models\Enums\TaskStatus;

class Task {
    public int $id;
    public string $titulo;
    public TaskStatus $estado;
    public User $creador;
    public DateTime $fecha_inicio;
    public DateTime $fecha_fin;

    public function __construct(int $id, string $titulo, TaskStatus $estado, User $creador, DateTime $fecha_inicio, DateTime $fecha_fin) {
        $this->id = $id;
        $this->titulo = $titulo;
        $this->estado = $estado;
        $this->creador = $creador;
        $this->fecha_inicio = $fecha_inicio;
        $this->fecha_fin = $fecha_fin;
    }
}