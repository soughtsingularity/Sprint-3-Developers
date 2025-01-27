<?php 

class TaskRepositoryJson implements TaskRepositoryInterface{

    private static $_instance = null;
    private $filePath;

    public function __construct(){
        $this->filePath = ROOT_PATH . "/app/data/Tasks.json";

        try{

            if(!file_exists($this->filePath)){
                file_put_contents($this->filePath, json_encode([]));
            }

        }catch (Exception $e) {
            error_log("Error fetching the JSON data " . $e->getMessage());
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
            return json_decode($data, true) ?? [];
        } catch (Exception $e) {
            error_log("Error reading data from JSON: " . $e->getMessage());
            return null;
        }
    }
    
    private function writeData($data){

        try{

            if(file_put_contents($this->filePath, json_encode($data, JSON_PRETTY_PRINT)) === false){
                throw new Exception("Failed to write data to JSON file.");
            }

        }catch(Exception $e){
            error_log("Error writing data in JSON: " . $e->getMessage());
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
            error_log("Error fetching users: " . $e->getMessage());
            return null;
        }
    }
    
    public function save(array $data){

        try{

            $dataSet = $this->readData();
            if(isset($data['id'])){
                foreach($dataSet as &$item){
                    if($item['id'] == $data['id']){
                        $item = array_merge($item, $data);
                        $isUpdate = true;
                    }
                }
            }else{
                $data['id'] = !empty($dataSet) ? max(array_column($dataSet, 'id')) + 1 : 1;            
                $dataSet[] = $data;
            }
            $this->writeData($dataSet);

            $isUpdate = false;

            return [
                'id' => $data['id'],
                'action' => $isUpdate ? 'updated' : 'created'
            ];

        }catch(Exception $e){
            error_log("Error saving task: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        try {

            $dataSet = $this->readData();
    
            $indexToRemove = null;
            foreach ($dataSet as $index => $task) {
                if ($task['id'] == $id) {
                    $indexToRemove = $index;
                    break;
                }
            }
    
            if ($indexToRemove === null) {
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
            error_log("Error al eliminar la tarea: " . $e->getMessage());
            return false;
            
        }
    }

    public function getById($id) {
        try {

            $jsonData = file_get_contents($this->filePath);
    
            if ($jsonData === false) {
                throw new Exception("Error reading data from: " . $this->filePath);
            }
    
            $data = json_decode($jsonData, true);
    
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Error decoding JSON: " . json_last_error_msg());
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
    
            return null;
    
        } catch (Exception $e) {
            error_log("Error fetching task by id: " . $e->getMessage());
            return null;
        }
    }

    public function getByName($name){

        try{

            $jsonData = file_get_contents($this->filePath);

            if($jsonData === false){
                throw new Exception("Error reading data from: " . $this->filePath);
            }

            $data = json_decode($jsonData, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Error decoding JSON: " . json_last_error_msg());
            }

            foreach ($data as $task) {
                if (isset($task['name']) && $task['name'] == $name) {
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

            return null;

        }catch(Exception $e){

            error_log("Error fetching task by id: " . $e->getMessage());
            return null;

        }
    }
} 