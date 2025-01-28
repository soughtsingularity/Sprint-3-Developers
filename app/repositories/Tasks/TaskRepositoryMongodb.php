<?php 

use MongoDB\Client;

class TaskRepositoryMongodb implements TaskRepositoryInterface {

    private static $_instance = null;
    protected $collection;

    public function __construct() {
        $settings = parse_ini_file(ROOT_PATH . '/config/settings.ini', true);
        try {
            if (!isset($settings['mongodb']['uri'], $settings['mongodb']['dbname'])) {
                throw new Exception("Configuración de MongoDB incompleta en settings.ini");
            }
    
            $client = new Client($settings['mongodb']['uri']);
            $this->collection = $client->selectDatabase($settings['mongodb']['dbname'])
                                       ->selectCollection('tasks');
    
        } catch (\MongoDB\Exception\RuntimeException $e) {
            error_log("MongoDB connection error: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            throw $e;
        } catch (Exception $e) {
            error_log("Error en el constructor de TaskRepositoryMongodb: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            throw $e;
        }
    }
    
    
    public static function getInstance(){
        if(self::$_instance === null){
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function save(array $data) {

        try {
            if (isset($data['id']) && !empty($data['id'])) {
                $filter = ['_id' => new \MongoDB\BSON\ObjectId($data['id'])];
    
                if (isset($data['id'])) {
                    unset($data['id']); 
                }
    
                if (!empty($data)) {
                    $this->collection->updateOne($filter, ['$set' => $data]);
                } else {
                    throw new \Exception("No se proporcionaron datos para actualizar la tarea con ID: " . $filter['_id']);
                }
    
                return (string) $filter['_id'];

                TaskStatus::validate($data['status']);
    
            } else {
                unset($data['id']); 
                $result = $this->collection->insertOne($data);
                return (string) $result->getInsertedId();
            }
        } catch (\MongoDB\Driver\Exception\InvalidArgumentException | \MongoDB\Exception\Exception $e) {
            error_log("Error al guardar tareas: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return false;
        }
    }

    public function getAll() {
        try {
            $cursor = $this->collection->find();
            $tasks = iterator_to_array($cursor);
    
            $statusMap = [
                'pending' => 'Pendiente',
                'in_progress' => 'En proceso',
                'completed' => 'Completada'
            ];
    
            foreach ($tasks as &$task) {
                $task['status'] = $statusMap[$task['status']] ?? 'Desconocido';
            }
    
            return $tasks;
        } catch (\MongoDB\Driver\Exception\InvalidArgumentException | \MongoDB\Exception\Exception $e) {
            error_log("Error al obtener tareas: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return null;
        }
    }

    public function getById($id) {
        try {
            $objectId = new \MongoDB\BSON\ObjectId($id);
            return $this->collection->findOne(['_id' => $objectId]);
        } catch (\MongoDB\Driver\Exception\InvalidArgumentException | \MongoDB\Exception\Exception $e) {
            error_log("Error fetching the _id {$id}: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return null;
        }
    }
    

    public function getByName($name) {
        try {
            $task = $this->collection->findOne(['name' => $name]);
    
            if ($task) {
                $statusMap = [
                    'pending' => 'Pendiente',
                    'in_progress' => 'En proceso',
                    'completed' => 'Completada'
                ];
    
                $task['status'] = $statusMap[$task['status']] ?? 'Desconocido';
    
                return $task;
            } else {
                error_log("Error: No se encontró la tarea con el nombre: {$name} en " . __FILE__ . " línea " . __LINE__);
                return null;
            }
    
        } catch (\MongoDB\Driver\Exception\InvalidArgumentException | \MongoDB\Exception\Exception $e) {
            error_log("Error al obtener tareas por nombre: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return null;
        }
    }
    
    
    public function delete($id)
    {
        try {
            $objectId = new \MongoDB\BSON\ObjectId($id);
            $result = $this->collection->deleteOne(['_id' => $objectId]);
    
            return $result->getDeletedCount() > 0;
        } catch (\MongoDB\Driver\Exception\InvalidArgumentException | \MongoDB\Exception\Exception $e) {
            error_log("Error deleting task with ID {$id}: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return false;
        }
    }
}