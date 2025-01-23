<?php

namespace App\Repositories\Tasks;

interface TaskRepositoryInterface {
    public function fetchOne($id);
    public function save(array $data);
    public function delete($id);
    public function showAll();
}