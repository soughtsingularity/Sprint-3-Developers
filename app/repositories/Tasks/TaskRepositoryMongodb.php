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
            return false;

        } catch (Exception $e) {
            error_log("Error en el constructor de TaskRepositoryMongodb: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return false;
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

            if (!empty($data['startDate']) && !empty($data['endDate'])) {
                $startDate = strtotime($data['startDate']);
                $endDate = strtotime($data['endDate']);
    
                if ($startDate > $endDate) {
                    throw new Exception("La fecha de inicio no puede ser posterior a la fecha de finalización.");
                }
            }

            if (isset($data['id']) && !empty($data['id'])) {
                $filter = ['_id' => new \MongoDB\BSON\ObjectId($data['id'])];
    
                if (isset($data['id'])) {
                    unset($data['id']); 
                } 
    
                if (!empty($data)) {

                    TaskStatus::validate($data['status']);


                    $result = $this->collection->updateOne($filter, ['$set' => $data]);

                    return $result;

                } else {

                    throw new \Exception("No se proporcionaron datos para actualizar la tarea con ID: " . $filter['_id']);
                }
    
                return (string) $filter['_id'];
    
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
            if (empty($id)) {
                throw new InvalidArgumentException("ID no proporcionado");
            }

            $objectId = new \MongoDB\BSON\ObjectId($id);

            return $this->collection->findOne(['_id' => $objectId]);

        } catch (\MongoDB\Driver\Exception\InvalidArgumentException | \MongoDB\Exception\Exception $e) {
            error_log("Error fetching the _id {$id}: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return false;
        }
    }
    

    public function getByName($name) {
        try {
            $cursor = $this->collection->find(['name' => $name]);
    
            $tasks = iterator_to_array($cursor);
    
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