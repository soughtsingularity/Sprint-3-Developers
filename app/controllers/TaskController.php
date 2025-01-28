<?php

class TaskController extends Controller {

    private $taskRepository;

    public function __construct(){
  
        $this->taskRepository = TaskRepositoryFactory::create();

    }

    public function listAction() {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['taskName']) && !empty(trim($_POST['taskName']))) {
                $taskName = trim($_POST['taskName']);
                $tasks = $this->taskRepository->getByName($taskName);
    
                if ($tasks) {
                    $this->view->tasks = [$tasks];
                } else {
                    $_SESSION['error_message'] = "No se encontraron tareas con el nombre: " . htmlspecialchars($taskName);
                    header("Location: " . WEB_ROOT . "/index.php/tasks/list?error=true");
                    exit();
                }
            } else {
                $this->view->tasks = $this->taskRepository->getAll();
            }
        } catch (Exception $e) {
            error_log("Error loading tasks list: " . $e->getMessage());
            $_SESSION['error_message'] = "Error cargando la lista de tareas.";
            header("Location: " . WEB_ROOT . "/index.php/tasks/list?error=true");
            exit();
        }
    }
    
    public function addAction(){

        try{

            $userRepository = UserRepositoryFactory::create();

            if($userRepository){
                
                $this->view->users = $userRepository->getAll();
            }else{
                throw new Exception("Error inicializating the repo");
            }

        }catch(Exception $e){
            error_log("Error loading the add task form" . $e->getMessage());
            $_SESSION['error_message'] = "Error loading the task form";
            header("Location: " . WEB_ROOT . "/index.php/tasks/add?error=true");
            return null;
        }
    }

    public function editAction() {
        try {
            $userRepository = UserRepositoryFactory::create();
            $this->view->users = $userRepository->getAll();
            
            $id = $_GET['id'] ?? null;
    
            if (!$id) {
                throw new \Exception("ID de tarea no proporcionado.");
            }
    
            // Validar y convertir el ID (ObjectId para MongoDB o entero para MySQL)
            if (preg_match('/^[0-9a-fA-F]{24}$/', $id)) {
                $taskId = new \MongoDB\BSON\ObjectId($id);
            } elseif (is_numeric($id)) {
                $taskId = (int)$id;
            } else {
                throw new \Exception("ID de tarea no válido.");
            }
    
            $taskRepository = TaskRepositoryFactory::create();
            $task = $taskRepository->getById($taskId);
    
            if (!$task) {
                throw new \Exception("No se encontró la tarea.");
            }
    
            $this->view->task = $task;
    
        } catch (\Exception $e) {
            error_log("Error al obtener la tarea: " . $e->getMessage());
            $_SESSION['error_message'] = "Hubo un problema al obtener la tarea.";
            header("Location: " . WEB_ROOT . "/index.php/tasks/list?error=true");
            exit();
        }
    }
    

    public function saveAction() {
        try {
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
    
            $isUpdate = isset($taskData['id']) && !empty($taskData['id']);
    
            if ($result !== false && $result !== null) {
                $_SESSION['success_message'] = $isUpdate 
                    ? "Tarea actualizada correctamente."
                    : "Tarea creada correctamente.";
                
                $redirectUrl = $isUpdate
                    ? WEB_ROOT . "/index.php/tasks/edit?id=" . urlencode($taskData['id']) . "&success=true"
                    : WEB_ROOT . "/index.php/tasks/add?success=true";
    
            } else {
                $_SESSION['error_message'] = $isUpdate 
                    ? "Hubo un problema al actualizar la tarea."
                    : "Hubo un problema al crear la tarea.";
                
                $redirectUrl = $isUpdate
                    ? WEB_ROOT . "/index.php/tasks/edit?id=" . urlencode($taskData['id']) . "&error=true"
                    : WEB_ROOT . "/index.php/tasks/add?error=true";
            }
    
        } catch (Exception $e) {
            error_log("Error saving task: " . $e->getMessage());
    
            $_SESSION['error_message'] = isset($taskData['id']) && !empty($taskData['id'])
                ? "Hubo un problema inesperado al actualizar la tarea."
                : "Hubo un problema inesperado al crear la tarea.";
    
            $redirectUrl = isset($taskData['id']) && !empty($taskData['id'])
                ? WEB_ROOT . "/index.php/tasks/edit?id=" . urlencode($taskData['id']) . "&error=true"
                : WEB_ROOT . "/index.php/tasks/add?error=true";
        }
    
        header("Location: " . $redirectUrl);
        exit();
    }
    
    
    public function deleteAction() {

        try {
            $id = $_GET['id'] ?? null;
    
            if (!$id) {
                throw new Exception("ID de tarea no proporcionado");
            }

            if (preg_match('/^[0-9a-fA-F]{24}$/', $id)) {
                $taskId = new \MongoDB\BSON\ObjectId($id);
            } elseif (is_numeric($id)) {
                $taskId = (int)$id;
            } else {
                throw new \Exception("ID de tarea no válido.");
            }
    
            $taskRepository = TaskRepositoryFactory::create();
            $result = $taskRepository->delete($taskId);
    
            if ($result) {
                $_SESSION['success_message'] = "Tarea eliminada correctamente.";
                header("Location: " . WEB_ROOT . "/index.php/tasks/list?success=true");
                exit();
            } else {
                throw new \Exception("No se pudo eliminar la tarea.");
            }
    
        } catch (\Exception $e) {
            error_log("Error al eliminar la tarea: " . $e->getMessage());
            $_SESSION['error_message'] = "No se pudo eliminar la tarea.";
            header("Location: " . WEB_ROOT . "/index.php/tasks/list?error=true");
            exit();
        }
    }
}
    