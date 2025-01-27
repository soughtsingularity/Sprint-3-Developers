<?php 
class UserRepositoryJson implements UserRepositoryInterface {

    private static $_instance = null;
    private $filePath;

    public function __construct(){

        $this->filePath = ROOT_PATH . "/app/data/Users.json";

        try {
            if (!file_exists($this->filePath)) {
                if (file_put_contents($this->filePath, json_encode([])) === false) {
                    throw new Exception("Error creating users JSON file.");
                }
            }
        } catch (Exception $e) {
            error_log("Error initializing users repository: " . $e->getMessage());
        }        
    }

    public static function getInstance() {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function readData() {
        try {
            $content = file_get_contents($this->filePath);
            return json_decode($content, true) ?? [];
        } catch (Exception $e) {
            error_log("Error reading users JSON: " . $e->getMessage());
            return null;
        }
    }

    private function writeData($data) {
        try {
            if (file_put_contents($this->filePath, json_encode($data, JSON_PRETTY_PRINT)) === false) {
                throw new Exception("Error writing users JSON");
            }
        } catch (Exception $e) {
            error_log("Error writing users JSON: " . $e->getMessage());
            return false;
        }
    }

    public function save($email){
        $dataSet = $this->readData();
        
        foreach($dataSet as $user){
            if ($user['email'] === $email) {
                return false; 
            }
        }

        $newId = !empty($dataSet) ? max(array_column($dataSet, 'id')) + 1 : 1;
        $newUser = [
            'id' => $newId,
            'email' => $email   
        ];

        $dataSet[] = $newUser;


        if ($this->writeData($dataSet)) {
            return $newUser['id'];
        } else {
            return false;
        }

        return $newUser['id'];
    }

    public function getAll() {

        try {

            $jsonData = file_get_contents($this->filePath);
            
            if ($jsonData === false) {
                throw new Exception("No se pudo leer el archivo: " . $this->filePath);
            }
    
            $data = json_decode($jsonData, true);
    
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Error al decodificar JSON: " . json_last_error_msg());
            }
    
            return array_map(function($user) {
                return [
                    'email' => isset($user['email']) ? $user['email'] : 'undefined'
                ];
            }, $data);
    
        } catch (Exception $e) {
            error_log("Error fetching users: " . $e->getMessage());
            return null;
        }
    }
}