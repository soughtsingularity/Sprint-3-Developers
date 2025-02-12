<?php

class Task {
    public $id;
    public $name;
    public $status;
    public $startDate;
    public $endDate;
    public $user;

    public function __construct(array $data) {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? null;
        $this->status = $data['status'] ?? null;
        $this->startDate = $data['startDate'] ?? null;
        $this->endDate = $data['endDate'] ?? null;
        $this->user = $data['user'] ?? null;

        $this->validate();
    }

    private function validate() {
        if (empty($this->name)) {
            throw new Exception("El nombre de la tarea es obligatorio.");
        }

        if (empty($this->status)) {
            throw new Exception("El estado de la tarea es obligatorio.");
        }

        if (!empty($this->startDate) && !empty($this->endDate)) {
            $start = strtotime($this->startDate);
            $end = strtotime($this->endDate);

            if ($start > $end) {
                throw new Exception("La fecha de inicio no puede ser posterior a la fecha de finalización.");
            }
        }
    }

    public static function convertId($id) {
        if (!$id) {
            throw new Exception("ID de tarea no proporcionado.");
        }
    
        if (is_numeric($id)) {
            return (int)$id; 
        }
    
        if (preg_match('/^[0-9a-fA-F]{24}$/', $id)) {
            return new \MongoDB\BSON\ObjectId($id);
        }
    
        throw new Exception("ID de tarea no válido.");
    }
}

