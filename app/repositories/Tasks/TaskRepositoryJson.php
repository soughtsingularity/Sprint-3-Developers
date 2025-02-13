<?php 

declare(strict_types=1);

class TaskRepositoryJson implements TaskRepositoryInterface{

    private static $_instance = null;
    private string $filePath;

    public function __construct(){

        $this->filePath = ROOT_PATH . "/app/data/Tasks.json";
    
        try {
            if (!file_exists($this->filePath)) {
                if (file_put_contents($this->filePath, json_encode([])) === false) {
                    throw new Exception("No se pudo crear el archivo de tareas en: " . $this->filePath);
                }
            }

        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return false;
        }
    }
    
    public static function getInstance(){

        if(self::$_instance === null){
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    private function readData(){

        try {
            $data = file_get_contents($this->filePath);
            if ($data === false) {
                throw new Exception("No se pudo leer el archivo de tareas en: " . $this->filePath);
            }
    
            $decodedData = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Error al decodificar JSON: " . json_last_error_msg());
            }
    
            return $decodedData ?? false;

        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return null;
        }
    }
    
    private function writeData($data){

        try {
            if (file_put_contents($this->filePath, json_encode($data, JSON_PRETTY_PRINT)) === false) {
                throw new Exception("No se pudo escribir en el archivo JSON: " . $this->filePath);
            }
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return false; 
        }
    }

    public function save(array $data) {
        try {
            $tasks = $this->readData();

            if ($tasks === null) {
                throw new Exception("No se pudieron cargar los datos para guardar la tarea.");
            }

            if (!empty($data['start_date']) && !empty($data['endDate'])) {
                $startDate = strtotime($data['start_date']);
                $endDate = strtotime($data['end_date']);
    
                if ($startDate > $endDate) {
                    throw new Exception("La fecha de inicio no puede ser posterior a la fecha de finalización.");
                }
            }

            TaskStatus::validate($data['status']);    

            $isUpdate = false;
            $updatedTask = null;

            foreach ($tasks as &$task) {
                if ($task['id'] == $data['id']) {
                    $task = array_merge($task, $data); 
                    $isUpdate = true;
                    $updatedTask = $task;
                    break; 
                }
            }

            if (!$isUpdate) {
                $data['id'] = !empty($tasks) ? max(array_column($tasks, 'id')) + 1 : 1;
                $tasks[] = $data;
                $updatedTask = $data;
            }

            $this->writeData($tasks);

            return $isUpdate ? $updatedTask['id'] : ['id' => $data['id'], 'action' => 'created'];

        } catch (Exception $e) {
            error_log("Error al guardar tarea: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return false;
        }
    }

    public function getAll() {

        try {
            $jsonData = file_get_contents($this->filePath);
            if ($jsonData === false) {
                throw new Exception("No se pudo leer el archivo: " . $this->filePath);
            }
    
            $tasks = json_decode($jsonData, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Error al decodificar JSON: " . json_last_error_msg());
            }

            if (!$tasks) {
                error_log("Error: No se encontraron tareas con el nombre: {$name}");
                return null;
            }
    
            $statusMap = [
                'pending' => 'Pendiente',
                'in_progress' => 'En proceso',
                'completed' => 'Completada'
            ];
    
            return array_map(function($task) use ($statusMap) {
                return [
                    'name' => isset($task['name']) ? $task['name'] : 'Desconocido',
                    'status' => isset($task['status']) && isset($statusMap[$task['status']]) 
                        ? $statusMap[$task['status']] 
                        : 'Desconocido',
                    'start_date' => isset($task['start_date']) ? $task['start_date'] : 'Desconocido',
                    'end_date' => isset($task['end_date']) ? $task['end_date'] : 'Desconocido',
                    'user' => isset($task['user']) ? $task['user'] : 'undefined',
                    'id' => isset($task['id']) ? $task['id'] : 'undefined'
                ];
            }, $tasks);
    
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return null;
        }
    }
    
    public function getById($id) {

        try {

            if (empty($id)) {
                throw new InvalidArgumentException("ID no proporcionado");
            }

            $jsonData = file_get_contents($this->filePath);
            if ($jsonData === false) {
                throw new Exception("No se pudo leer el archivo: " . $this->filePath);
            }
    
            $tasks = json_decode($jsonData, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Error al decodificar JSON: " . json_last_error_msg());
            }
    
            foreach ($tasks as $task) {
                if (isset($task['id']) && $task['id'] == $id) {
                    return $task; 
                }
            }
    
            error_log("Error: No se encontró la tarea con ID: " . $id . " en " . __FILE__ . " línea " . __LINE__);
            return null;
    
        } catch (Exception $e) {
            error_log("Error al obtener tareas por id: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return null;
        }
    }
    
    public function getByName($name) {
        try {
            if (empty($name)) {
                throw new InvalidArgumentException("Nombre no proporcionado");
            }
    
            $jsonData = file_get_contents($this->filePath);
            if ($jsonData === false) {
                throw new Exception("No se pudo leer el archivo: " . $this->filePath);
            }
    
            $tasks = json_decode($jsonData, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Error al decodificar JSON: " . json_last_error_msg());
            }

            if (!$tasks) {
                error_log("Error: No se encontraron tareas con el nombre: {$name}");
                return null;
            }
    
            $statusMap = [
                'pending' => 'Pendiente',
                'in_progress' => 'En proceso',
                'completed' => 'Completada'
            ];
    
            $filteredTasks = array_filter($tasks, function($task) use ($name) {
                return isset($task['name']) && $task['name'] == $name;
            });
            
            return array_map(function($task) use ($statusMap) {
                return [
                    'id' => $task['id'] ?? 'Desconocido',
                    'name' => $task['name'] ?? 'Desconocido',
                    'status' => $statusMap[$task['status']] ?? 'Desconocido',
                    'start_date' => $task['start_date'] ?? 'Desconocido',
                    'end_date' => $task['end_date'] ?? 'Desconocido',
                    'user' => $task['user'] ?? 'Desconocido'
                ];
            }, $filteredTasks);
    
        } catch (Exception $e) {
            error_log("Error al obtener tareas por nombre: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return null;
        }
    }

    public function delete($id) {
        try {
            $tasks = $this->readData();
            if ($tasks === null) {
                throw new Exception("No se pudieron cargar los datos para eliminar la tarea con ID: " . $id);
            }
    
            $indexToRemove = null;
            foreach ($tasks as $index => $task) {
                if ($task['id'] == $id) {
                    $indexToRemove = $index;
                    break;
                }
            }
    
            if ($indexToRemove === null) {
                error_log("Error: No se encontró la tarea con ID: " . $id . " en " . __FILE__ . " línea " . __LINE__);
                return false;
            }
    
            unset($tasks[$indexToRemove]);
    
            $tasks = array_values($tasks); 

            foreach ($tasks as $key => &$task) {
                $task['id'] = $key + 1;  
            }
    
            $this->writeData($tasks);
    
            return true;  
    
        } catch (Exception $e) {
            error_log("Error al eliminar la tarea: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return false;
        }
    }
       
} 