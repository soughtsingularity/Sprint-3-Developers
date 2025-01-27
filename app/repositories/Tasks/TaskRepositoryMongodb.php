<?php 

use MongoDB\Client;

class TaskRepositoryMongodb implements TaskRepositoryInterface {

    private static $_instance = null;
    protected $collection;

    public function __construct() {
        $settings = parse_ini_file(ROOT_PATH . '/config/settings.ini', true);
        try {
            $client = new Client($settings['mongodb']['uri']);
            $this->collection = $client->selectDatabase($settings['mongodb']['dbname'])
                ->selectCollection('tasks');
        } catch (\MongoDB\Exception\RuntimeException $e) {
            error_log("MongoDB connection error: " . $e->getMessage());
            
        }
        
    }
    
    public static function getInstance(){
        if(self::$_instance === null){
            self::$_instance = new self();
        }

        return self::$_instance;
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
            error_log("Error fetching tasks: " . $e->getMessage());
            return null;
        }
    }

    public function getById($id) {
        try {
            $objectId = new \MongoDB\BSON\ObjectId($id);
            return $this->collection->findOne(['_id' => $objectId]);
        } catch (\MongoDB\Driver\Exception\InvalidArgumentException | \MongoDB\Exception\Exception $e) {
            error_log("Error fetching the _id: " . $e->getMessage());
            return null;
        }
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
                    throw new \Exception("No data provided for update.");
                }                
                
                return (string) $filter['_id'];

            } else {
                unset($data['id']); // Evitar el campo 'id' manual
                $result = $this->collection->insertOne($data);
                return (string) $result->getInsertedId();
            }

        }catch (\MongoDB\Driver\Exception\InvalidArgumentException | \MongoDB\Exception\Exception $e){
            error_log("Error saving task: " . $e->getMessage());
            return false;
        }
    }
    
    public function delete($id)
    {
        try {
            $objectId = new \MongoDB\BSON\ObjectId($id);
            $result = $this->collection->deleteOne(['_id' => $objectId]);
    
            return $result->getDeletedCount() > 0;
        } catch (\MongoDB\Driver\Exception\InvalidArgumentException | \MongoDB\Exception\Exception $e) {
            error_log("Error deleting task: " . $e->getMessage());
            return false;
        }
    }
    
}