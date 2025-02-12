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
            $dataSet = $this->readData();

            if ($dataSet === null) {
                throw new Exception("No se pudieron cargar los datos para guardar la tarea.");
            }

            if (!isset($data['status'])) {
                throw new Exception("El estado de la tarea es obligatorio.");
            }

            TaskStatus::validate($data['status']);

            if (!empty($data['startDate']) && !empty($data['endDate'])) {
                $startDate = strtotime($data['startDate']);
                $endDate = strtotime($data['endDate']);
    
                if ($startDate > $endDate) {
                    throw new Exception("La fecha de inicio no puede ser posterior a la fecha de finalización.");
                }
            }    

            $isUpdate = false;
            $updatedTask = null;

            foreach ($dataSet as &$item) {
                if ($item['id'] == $data['id']) {
                    $item = array_merge($item, $data); 
                    $isUpdate = true;
                    $updatedTask = $item;
                    break; 
                }
            }

            if (!$isUpdate) {
                $data['id'] = !empty($dataSet) ? max(array_column($dataSet, 'id')) + 1 : 1;
                $dataSet[] = $data;
                $updatedTask = $data;
            }

            $this->writeData($dataSet);

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
    
            $statusMap = [
                'pending' => 'Pendiente',
                'in_progress' => 'En proceso',
                'completed' => 'Completada'
            ];
    
            return array_map(function($task) use ($statusMap) {
                return [
                    'id' => isset($task['_id']) ? (string) $task['_id'] : (isset($task['id']) ? (string) $task['id'] : 'Desconocido'),
                    'name' => $task['name'] ?? 'Desconocido',
                    'status' => isset($task['status']) && isset($statusMap[$task['status']]) ? $statusMap[$task['status']] : 'Desconocido',
                    'startDate' => $task['startDate'] ?? 'Desconocido',
                    'endDate' => $task['endDate'] ?? 'Desconocido',
                    'user' => $task['user'] ?? 'Desconocido',
                ];
            }, $tasks);

        } catch (Exception $e) {
            error_log("Error al obtener tareas: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return [];
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
                        'name' => isset($task['name']) ? $task['name'] : 'Desconocido',
                        'status' => isset($task['status']) ? $task['status'] : 'Desconocido',
                        'startDate' => isset($task['startDate']) ? $task['startDate'] : 'Desconocido',
                        'dueDate' => isset($task['endDate']) ? $task['endDate'] : 'Desconocido',
                        'user' => isset($task['user']) ? $task['user'] : 'Desconocido',
                        'id' => isset($task['id']) ? $task['id'] : 'Desconocido'
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
                throw new Exception("Error al decodificar JSON: " . json_last_error_msg() . " en " . __FILE__ . " línea " . __LINE__);
            }
    
            $tasks = array_filter($tasks, function($task) use ($name) {
                return isset($task['name']) && $task['name'] == $name;
            });
    
            if (empty($tasks)) {
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
                    'id' => isset($task['_id']) ? (string) $task['_id'] : (isset($task['id']) ? (string) $task['id'] : 'Desconocido'),
                    'name' => $task['name'] ?? 'Desconocido',
                    'status' => isset($task['status']) && isset($statusMap[$task['status']]) ? $statusMap[$task['status']] : 'Desconocido',
                    'startDate' => $task['startDate'] ?? 'Desconocido',
                    'endDate' => $task['endDate'] ?? 'Desconocido',
                    'user' => $task['user'] ?? 'Desconocido',
                ];
            }, $tasks);
        } catch (Exception $e) {
            error_log("Error al obtener tareas: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
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