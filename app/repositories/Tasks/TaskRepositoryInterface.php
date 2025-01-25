<?php

interface TaskRepositoryInterface {
    public function fetchOne($name);
    public function save(array $data);
    public function delete($id);
    public function getAll();
}