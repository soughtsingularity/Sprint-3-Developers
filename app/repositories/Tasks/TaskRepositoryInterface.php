<?php

interface TaskRepositoryInterface {
    public function getById($id);
    public function save(array $data);
    public function delete($id);
    public function getAll();
}