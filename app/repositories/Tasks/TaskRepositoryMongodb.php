<?php 

require 'vendor/autoload.php';

use MongoDB\Client;
use App\Repositories\TaskRepositoryInterface;
use MongoDB\BSON\ObjectId;


class TaskRepositoryMongodb implements TaskRepositoryInterface {

    private static $_instance = null;
    protected $collection;

    public function __construct($table) {
        $settings = parse_ini_file(ROOT_PATH . '/config/settings.ini', true);
        $client = new Client($settings['mongodb']['uri']);
        $this->collection = $client->selectDatabase($settings['mongodb']['dbname'])->selectCollection($table);
    }
    
    public function getInstance(){
        if(self::$_instance ==null){
            self::$_instance = new self();
        }

        return self::$_instance->collection;
    }

    public function showAll(){
        
    }

    public function fetchOne($id) {
        return $this->collection->findOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
    }

    public function save(array $data) {
        if (isset($data['id'])) {
            $this->collection->updateOne(
                ['_id' => new \MongoDB\BSON\ObjectId($data['id'])],
                ['$set' => $data]
            );
        } else {
            $this->collection->insertOne($data);
            return (string)$data['_id'];
        }
    }

    public function delete($id) {
        return $this->collection->deleteOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
    }
}