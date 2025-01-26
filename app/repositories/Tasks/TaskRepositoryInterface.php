<?php

interface TaskRepositoryInterface {
    public function getById($name);
    public function save(array $data);
    public function delete($id);
    public function getAll();
}