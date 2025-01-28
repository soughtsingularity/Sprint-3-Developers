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
            // Leer datos del archivo JSON
            $dataSet = $this->readData();
    
            // Asegurarse de que el archivo no sea nulo
            if ($dataSet === null) {
                $dataSet = []; // Si está vacío, inicializar como arreglo vacío
            }
    
            // Validar si el email ya existe
            foreach ($dataSet as $user) {
                if (isset($user['email']) && strtolower(trim($user['email'])) === strtolower(trim($email))) {
                    // Si el email ya existe
                    $_SESSION['newUser'] = $email;
                    return false; // Usuario ya existe
                }
            }
    
            // Si no encontró el email, crea un nuevo usuario
            $newId = !empty($dataSet) ? max(array_column($dataSet, 'id')) + 1 : 1;
            $newUser = [
                'id' => $newId,
                'email' => trim($email)
            ];
    
            $dataSet[] = $newUser;
    
            // Escribir datos en el archivo JSON
            if ($this->writeData($dataSet)) {
                $_SESSION['newUser'] = $email;
                return true; // Usuario nuevo creado correctamente
            }
    
            // Si no se puede escribir en el archivo
            throw new Exception("Error al guardar el nuevo usuario.");
        } catch (Exception $e) {
            error_log("Error al guardar usuario en JSON: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return false; // Retorna false en caso de error
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