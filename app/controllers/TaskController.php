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

        // Validar si el ID es numérico o un ObjectId válido
        try {
            $taskId = new \MongoDB\BSON\ObjectId($id);
        } catch (\Exception $e) {
            if (!is_numeric($id)) {
                echo "ID no válido";
                return;
            }
            $taskId = (int)$id;
        }
        
        $taskRepository = TaskRepositoryFactory::create();
        $task = $taskRepository->getById($taskId);
        
        if (!$task) {
            echo "Tarea no encontrada";
            return;
        }
        
        $this->view->task = $task;
        
        
    
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
            'id' => isset($_POST['id']) ? 
                (preg_match('/^[0-9a-fA-F]{24}$/', $_POST['id']) ? new \MongoDB\BSON\ObjectId($_POST['id']) : $_POST['id']) 
                : null,
            'name' => $_POST['name'] ?? null,
            'status' => $_POST['status'] ?? null,
            'startDate' => $_POST['startDate'] ?? null,
            'endDate' => $_POST['endDate'] ?? null,
            'user' => $_POST['user'] ?? null,
        ];
        
    
        $taskRepository = TaskRepositoryFactory::create();
        $result = $taskRepository->save($taskData);
        
        if ($result !== false && $result !== null) {
            if (isset($taskData['id']) && !empty($taskData['id'])) {
                $_SESSION['success_message'] = "Tarea actualizada correctamente.";
                header("Location: " . WEB_ROOT . "/index.php/tasks/edit?id=" . urlencode($taskData['id']) . "&success=true");
            } else {
                $_SESSION['success_message'] = "Tarea creada correctamente.";
                header("Location: " . WEB_ROOT . "/index.php/tasks/add?success=true");
            }
            exit();
        } else {        
            if (isset($taskData['id']) && !empty($taskData['id'])) {
                $_SESSION['error_message'] = "Hubo un problema al actualizar la tarea.";
                header("Location: " . WEB_ROOT . "/index.php/tasks/edit?id=" . urlencode($taskData['id']) . "&error=true");
            } else {
                $_SESSION['error_message'] = "Hubo un problema al crear la tarea.";
                header("Location: " . WEB_ROOT . "/index.php/tasks/add?error=true");
            }
            exit();
        }
        
    }

    public function deleteAction() {
        $id = $_GET['id'] ?? null;

        try {
            $taskId = new \MongoDB\BSON\ObjectId($id);
        } catch (\Exception $e) {
            if (!is_numeric($id)) {
                $_SESSION['error_message'] = "ID de tarea no válido.";
                header("Location: " . WEB_ROOT . "/index.php/tasks/list");
                exit();
            }
            $taskId = (int)$id;
        }
        
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