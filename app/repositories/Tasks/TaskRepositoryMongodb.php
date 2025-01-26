<?php 

use MongoDB\Client;

class TaskRepositoryMongodb implements TaskRepositoryInterface {

    private static $_instance = null;
    protected $collection;

    public function __construct() {
        $settings = parse_ini_file(ROOT_PATH . '/config/settings.ini', true);
        $client = new Client($settings['mongodb']['uri']);
        $this->collection = $client->selectDatabase($settings['mongodb']['dbname'])
        ->selectCollection('tasks');    
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
    
            return $tasks;
        } catch (Exception $e) {
            error_log("Error fetching users: " . $e->getMessage());
            return [];
        }
    }

    public function getById($id) {
        try {
            $objectId = new \MongoDB\BSON\ObjectId($id);
            return $this->collection->findOne(['_id' => $objectId]);
        } catch (\MongoDB\Driver\Exception\InvalidArgumentException $e) {
            // Si el ID no es válido, devolver null
            return null;
        }
    }
    
    

    public function fetchOne($name) {
        return $this->collection->findOne(['name' => new \MongoDB\BSON\ObjectId($name)]);
    }

    public function save(array $data) {
        if (isset($data['id']) && !empty($data['id'])) {
            // Convertir id a ObjectId si es válido
            $filter = ['_id' => new \MongoDB\BSON\ObjectId($data['id'])];
    
            // Evitar duplicidad de la clave id en MongoDB
            unset($data['id']);
    
            $this->collection->updateOne($filter, ['$set' => $data]);
            return (string) $data['_id'];
        } else {
            unset($data['id']); // Evitar el campo 'id' manual
            $result = $this->collection->insertOne($data);
            return (string) $result->getInsertedId();
        }
    }
    
    

    public function delete($id)
    {
        try {
            $objectId = new \MongoDB\BSON\ObjectId($id);
            $result = $this->collection->deleteOne(['_id' => $objectId]);
    
            return $result->getDeletedCount() > 0;
        } catch (\MongoDB\Driver\Exception\InvalidArgumentException $e) {
            error_log("ID no válido para MongoDB: " . $e->getMessage());
            return false;
        }
    }
    
    
}