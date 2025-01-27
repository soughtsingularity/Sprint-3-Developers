<?php 

use MongoDB\Client;

class UserRepositoryMongodb implements UserRepositoryInterface{

    private static $_instance = null;
    protected $collection;

    public function __construct(){
        $settings = parse_ini_file(__DIR__ . '/../../../config/settings.ini', true);
        
        try {
            $client = new Client($settings['mongodb']['uri']);
            $this->collection = $client->selectDatabase($settings['mongodb']['dbname'])
                                       ->selectCollection('users');
        } catch (\MongoDB\Exception\Exception $e) {
            error_log("MongoDB connection error: " . $e->getMessage());
            throw new \Exception("Error al conectar con la base de datos.");
        }
        
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

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                error_log("Intento de guardar usuario con email inválido: " . $email);
                throw new Exception("El email no es válido.");
            }

            $existingUser = $this->collection->findOne(['email' => $email]);
    
            if ($existingUser) {
                error_log("Intento de guardar un usuario existente: " . $email);
                throw new Exception("El usuario ya existe."); 
            }
        
            $result = $this->collection->insertOne([
                'email' => $email
            ]);
        
            return ($result->getInsertedCount() > 0) ? true : false;

        }catch(\MongoDB\Exception\Exception | \Exception $e){
            error_log("Error al guardar al usuario " . $e->getMessage());
            return false; 
        }

    }
    


    public function getAll() {
        try {
            $cursor = $this->collection->find();
            $users = iterator_to_array($cursor);
    
            foreach ($users as &$user) {
                $user['id'] = (string)$user['_id']; 
                unset($user['_id']);  
            }
    
            return $users;
        } catch (\MongoDB\Exception\Exception $e) {
            error_log("Error fetching users: " . $e->getMessage());
            return null;
        }
    }
}