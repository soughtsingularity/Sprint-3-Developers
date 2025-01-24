<?php

class TaskController extends Controller {

    public function listAction() {
        $this->view->title = "Lista de Tareas";
    }

    public function addAction(){

        $userRepository = UserRepositoryJson::getInstance();
        $this->view->users = $userRepository->getAll();
    }

    public function editAction(){
        $this->view->title = "Editar Tarea";
        $this->view->message = "Aqui se editan tareas.";
    }
}