<?php 

class TaskRepositoryJson implements TaskRepositoryInterface{

    private static $_instance = null;
    private $filePath;

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
    
            return $decodedData ?? [];
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
            $dataSet = $this->readData();
    
            if ($dataSet === null) {
                throw new Exception("No se pudieron cargar los datos para guardar la tarea.");
            }

            if (!isset($data['status'])) {
                throw new Exception("El estado de la tarea es obligatorio.");
            }

            TaskStatus::validate($data['status']);
    
            foreach ($dataSet as $task) {
                if ($task['id'] === $data['id']) {
                    return [
                        'id' => $data['id'],
                        'action' => 'exists'
                    ]; 
                }
            }
    
            $isUpdate = false;
    
            if (isset($data['id'])) {
                foreach ($dataSet as &$item) {
                    if ($item['id'] == $data['id']) {
                        $item = array_merge($item, $data);
                        $isUpdate = true;
                    }
                }
            } else {
                $data['id'] = !empty($dataSet) ? max(array_column($dataSet, 'id')) + 1 : 1;
                $dataSet[] = $data;
            }
    
            $this->writeData($dataSet);
    
            return [
                'id' => $data['id'],
                'action' => $isUpdate ? 'updated' : 'created'
            ];
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return false;
        }
    }
    
    
    
    public function getAll() {

        try {
            $jsonData = file_get_contents($this->filePath);
            if ($jsonData === false) {
                throw new Exception("No se pudo leer el archivo: " . $this->filePath);
            }
    
            $data = json_decode($jsonData, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Error al decodificar JSON: " . json_last_error_msg());
            }
    
            $statusMap = [
                'pending' => 'Pendiente',
                'in_progress' => 'En proceso',
                'completed' => 'Completada'
            ];
    
            $tasks = array_map(function($task) use ($statusMap) {
                return [
                    'name' => isset($task['name']) ? $task['name'] : 'undefined',
                    'status' => isset($task['status']) && isset($statusMap[$task['status']]) 
                        ? $statusMap[$task['status']] 
                        : 'Desconocido',
                    'startDate' => isset($task['startDate']) ? $task['startDate'] : 'undefined',
                    'endDate' => isset($task['endDate']) ? $task['endDate'] : 'undefined',
                    'user' => isset($task['user']) ? $task['user'] : 'undefined',
                    'id' => isset($task['id']) ? $task['id'] : 'undefined'
                ];
            }, $data);
    
            return $tasks;
    
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return null;
        }
    }
    
    public function getById($id) {
        try {
            $jsonData = file_get_contents($this->filePath);
            if ($jsonData === false) {
                throw new Exception("No se pudo leer el archivo: " . $this->filePath);
            }
    
            $data = json_decode($jsonData, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Error al decodificar JSON: " . json_last_error_msg());
            }
    
            foreach ($data as $task) {
                if (isset($task['id']) && $task['id'] == $id) {
                    return [
                        'name' => isset($task['name']) ? $task['name'] : 'undefined',
                        'status' => isset($task['status']) ? $task['status'] : 'undefined',
                        'startDate' => isset($task['startDate']) ? $task['startDate'] : 'undefined',
                        'dueDate' => isset($task['endDate']) ? $task['endDate'] : 'undefined',
                        'user' => isset($task['user']) ? $task['user'] : 'undefined',
                        'id' => isset($task['id']) ? $task['id'] : 'undefined'
                    ];
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
    
            $statusMap = [
                'pending' => 'Pendiente',
                'in_progress' => 'En proceso',
                'completed' => 'Completada'
            ];
    
            foreach ($tasks as $item) {
                if (isset($item['name']) && $item['name'] == $name) {
                    $item['status'] = $statusMap[$item['status']] ?? 'Desconocido';
    
                    return [
                        'name' => $item['name'] ?? 'undefined',
                        'status' => $item['status'],
                        'startDate' => $item['startDate'] ?? 'undefined',
                        'endDate' => $item['endDate'] ?? 'undefined',
                        'user' => $item['user'] ?? 'undefined',
                        'id' => $item['id'] ?? 'undefined'
                    ];
                }
            }
    
            error_log("Error: No se encontró ninguna tarea con el nombre: " . $name . " en " . __FILE__ . " línea " . __LINE__);
            return null;
    
        } catch (Exception $e) {
            error_log("Error al obtener tareas por nombre: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return null;
        }
    }

    public function delete($id) {
        try {
            $dataSet = $this->readData();
            if ($dataSet === null) {
                throw new Exception("No se pudieron cargar los datos para eliminar la tarea con ID: " . $id);
            }
    
            $indexToRemove = null;
            foreach ($dataSet as $index => $task) {
                if ($task['id'] == $id) {
                    $indexToRemove = $index;
                    break;
                }
            }
    
            if ($indexToRemove === null) {
                error_log("Error: No se encontró la tarea con ID: " . $id . " en " . __FILE__ . " línea " . __LINE__);
                return false;
            }
    
            unset($dataSet[$indexToRemove]);
    
            $dataSet = array_values($dataSet); 
            foreach ($dataSet as $key => &$task) {
                $task['id'] = $key + 1;  
            }
    
            $this->writeData($dataSet);
    
            return true;  
    
        } catch (Exception $e) {
            error_log("Error al eliminar la tarea: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return false;
        }
    }
       
} 