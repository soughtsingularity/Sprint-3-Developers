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

        return self::$_instance->collection;
    }

    public function getAll(){
        
    }

    public function fetchOne($name) {
        return $this->collection->findOne(['name' => new \MongoDB\BSON\ObjectId($name)]);
    }

    public function save(array $data) {
        if (isset($data['id'])) {
            $this->collection->updateOne(
                ['id' => new \MongoDB\BSON\ObjectId($data['id'])],
                ['$set' => $data]
            );
        } else {
            $this->collection->insertOne($data);
            return (string)$data['id'];
        }
    }

    public function delete($id) {
        return $this->collection->deleteOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
    }
}