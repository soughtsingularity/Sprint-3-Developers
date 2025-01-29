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
            error_log("MongoDB connection error: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            throw new \Exception("Error al conectar con la base de datos. Detalles: " . $e->getMessage());
        }
    }
    
    public static function getInstance(){
        
        if(self::$_instance === null){
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function save($email) {

        try {

            $email = strtolower(trim($email));
    
            $existingUser = $this->collection->findOne(['email' => $email]);
    
            if ($existingUser) {
                $_SESSION['newUser'] = $email;
                return false; 
            }
    
            $result = $this->collection->insertOne([
                'email' => $email
            ]);
    
            if ($result->getInsertedId()) {
                $_SESSION['newUser'] = $email;
                return true;
            }
    
            throw new Exception("Error al insertar usuario en MongoDB.");

        } catch (Exception $e) {
            error_log("Error al guardar usuario en MongoDB: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
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
            error_log("Error al obtener usuarios: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return null;
        }
    }
    
}