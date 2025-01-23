<?php

interface UserRepositoryInterface{

    public function save(string $email);
    public function getAll();
    public function findById(int $id);
    public function findByEmail(string $email);

}