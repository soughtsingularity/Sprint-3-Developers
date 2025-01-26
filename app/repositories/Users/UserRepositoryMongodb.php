<?php 

use MongoDB\Client;

class UserRepositoryMongodb implements UserRepositoryInterface{

    private static $_instance = null;
    protected $collection;

    public function __construct(){
        $settings = parse_ini_file(__DIR__ . '/../../../config/settings.ini', true);
        $client = new Client($settings['mongodb']['uri']);
        $this->collection = $client->selectDatabase($settings['mongodb']['dbname'])
        ->selectCollection('users');
    }

    public static function getInstance(){
        if(self::$_instance === null){
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function save($email)
    {

        try{

        }catch(){
            
        }
        if (!$email) {
            return false;
        }
    
        // Verificar si el email ya existe en la colección
        $existingUser = $this->collection->findOne(['email' => $email]);
    
        if ($existingUser) {
            return false; // El email ya está registrado
        }
        
        $lastUser = $this->collection->findOne([], ['sort' => ['id' => -1]]);
        $newId = $lastUser ? $lastUser['id'] + 1 : 1;
    
        // Insertar el nuevo usuario en la colección
        $result = $this->collection->insertOne([
            'id' => $newId,
            'email' => $email
        ]);
    
        return $result->getInsertedCount() > 0;
    }

    public function getAll() {
        try {
            $cursor = $this->collection->find();
            $users = iterator_to_array($cursor);
    
            return $users;
        } catch (Exception $e) {
            error_log("Error fetching users: " . $e->getMessage());
            return [];
        }
    }

}