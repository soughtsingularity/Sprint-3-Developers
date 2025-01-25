<?php

interface UserRepositoryInterface{

    public function save(string $email);
    public function getAll();

}