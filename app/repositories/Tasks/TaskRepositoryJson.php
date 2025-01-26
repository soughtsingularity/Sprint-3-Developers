<?php 

class TaskRepositoryJson implements TaskRepositoryInterface{

    private static $_instance = null;
    private $filePath;

    public function __construct(){
        $this->filePath = ROOT_PATH . "/app/data/Tasks.json";

        if(!file_exists($this->filePath)){
            file_put_contents($this->filePath, json_encode([]));
        }
    }

    public static function getInstance(){
        if(self::$_instance === null){
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    private function readData(){
        return json_decode(file_get_contents($this->filePath), true);
    }

    private function writeData($data){
        file_put_contents($this->filePath, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function getById($id) {
        try {
            // Leer el contenido del archivo JSON
            $jsonData = @file_get_contents($this->filePath);
    
            if ($jsonData === false) {
                throw new Exception("No se pudo leer el archivo: " . $this->filePath);
            }
    
            $data = json_decode($jsonData, true);
    
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Error al decodificar JSON: " . json_last_error_msg());
            }
    
            // Buscar la tarea por ID
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
    
            // Retornar null si no se encuentra la tarea con el ID especificado
            return null;
    
        } catch (Exception $e) {
            error_log("Error fetching task by ID: " . $e->getMessage());
            return null;
        }
    }
    

    public function getAll() {

        try {

            $jsonData = @file_get_contents($this->filePath);
            
            if ($jsonData === false) {
                throw new Exception("No se pudo leer el archivo: " . $this->filePath);
            }
    
            $data = json_decode($jsonData, true);
    
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Error al decodificar JSON: " . json_last_error_msg());
            }
    
            return array_map(function($task) {
                return [
                    'name' => isset($task['name']) ? $task['name'] : 'undefined',
                    'status' => isset($task['status']) ? $task['status'] : 'undefined',
                    'startDate' => isset($task['startDate']) ? $task['startDate'] : 'undefined',
                    'endDate' => isset($task['endDate']) ? $task['endDate'] : 'undefined',
                    'user' => isset($task['user']) ? $task['user'] : 'undefined',
                    'id' => isset($task['id']) ? $task['id'] : 'undefined'
                ];
            }, $data);
    
        } catch (Exception $e) {
            error_log("Error fetching users: " . $e->getMessage());
            return [];
        }
    }
    
    public function save(array $data){
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
        return [
            'id' => $data['id'],
            'action' => $isUpdate ? 'updated' : 'created'
        ];
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
    

} 