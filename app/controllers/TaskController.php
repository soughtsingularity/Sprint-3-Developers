<?php

class TaskController extends Controller {

    public function listAction() {

        $taskRepository = TaskRepositoryFactory::create();
        $this->view->tasks = $taskRepository->getAll();

    }

    public function addAction(){

        $userRepository = UserRepositoryFactory::create();
        $this->view->users = $userRepository->getAll();
    }

    public function editAction() {

        $userRepository = UserRepositoryFactory::create();
        $this->view->users = $userRepository->getAll();

        $id = $_GET['id'] ?? null;
    
        if (!$id || !is_numeric($id)) {
            echo "ID no válido";
            return;
        }
    
        $taskId = (int)$id;
    
        $taskRepository = TaskRepositoryFactory::create();
        $task = $taskRepository->getById($taskId);
    
        if (!$task) {
            echo "Tarea no encontrada";
            return;
        }
    
        $this->view->task = $task;
    }

    public function saveAction() {
        $taskData = [
            'id' => $_POST['id'] ?? null,
            'name' => $_POST['name'] ?? null,
            'status' => $_POST['status'] ?? null,
            'start_date' => $_POST['start_date'] ?? null,
            'due_date' => $_POST['due_date'] ?? null,
            'assigned_to' => $_POST['assigned_to'] ?? null,
        ];
    
        $taskRepository = TaskRepositoryFactory::create();
        $result = $taskRepository->save($taskData);
        
        if ($result) {
            if (isset($taskData['id']) && !empty($taskData['id'])) {
                $_SESSION['success_message'] = "Tarea actualizada correctamente.";
                header("Location: " . WEB_ROOT . "/index.php/tasks/edit?id=" . $taskData['id'] . "&success=true");
            } else {
                $_SESSION['success_message'] = "Tarea creada correctamente.";
                header("Location: " . WEB_ROOT . "/index.php/tasks/add?success=true");
            }
            exit();
        } else {
            $_SESSION['error_message'] = "Hubo un problema al guardar la tarea.";
            header("Location: " . WEB_ROOT . "/index.php/tasks/edit?id=" . ($taskData['id'] ?? '') . "&error=true");
            exit();
        }
    }

    public function deleteAction() {
        $id = $_GET['id'] ?? null;
    
        if (!$id || !is_numeric($id)) {
            $_SESSION['error_message'] = "ID de tarea no válido.";
            header("Location: " . WEB_ROOT . "/index.php/tasks/list");
            exit();
        }
    
        $taskId = (int)$id;
    
        $taskRepository = TaskRepositoryFactory::create();
        $result = $taskRepository->delete($taskId);
    
        if ($result) {
            $_SESSION['success_message'] = "Tarea eliminada correctamente.";
        } else {
            $_SESSION['error_message'] = "Tarea no encontrada.";
        }
    
        header("Location: " . WEB_ROOT . "/index.php/tasks/list");
        exit();
    }
    
}