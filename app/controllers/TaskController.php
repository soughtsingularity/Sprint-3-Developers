<?php

class TaskController extends Controller {
    public function listAction() {
        $this->view->title = "Lista de Tareas";
        $this->view->message = "Aquí están todas tus tareas pendientes.";
    }

    public function addAction(){
        $this->view->title = "Añadir Tarea";
        $this->view->message = "Aquí se añaden tareas.";

        require_once __DIR__ . '/../repositories/Users/UserRepositoryJson.php';

        $userRepository = new UserRepositoryJson();
    
        $this->view->users = $userRepository->getAll();
    }

    public function editAction(){
        $this->view->title = "Editar Tarea";
        $this->view->message = "Aqui se editan tareas.";
    }
}