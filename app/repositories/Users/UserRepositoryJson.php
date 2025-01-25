<?php 
class UserRepositoryJson implements UserRepositoryInterface {

    private static $_instance = null;
    private $filePath;

    public function __construct(){
        $this->filePath = ROOT_PATH . "/app/data/Users.json";
        if(!file_exists($this->filePath)){
            file_put_contents($this->filePath, json_encode([]));
        }
    }

    public static function getInstance() {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function readData() {
        $content = file_get_contents($this->filePath);
        return json_decode($content, true);
    }

    private function writeData($data){
        file_put_contents($this->filePath, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function save($email){
        $dataSet = $this->readData();
        
        foreach($dataSet as $user){
            if ($user['email'] === $email) {
                return false; 
            }
        }

        $newUser = [
            'id' => count($dataSet) + 1,
            'email' => $email   
        ];

        $dataSet[] = $newUser;
        $this->writeData($dataSet);
        return $newUser['id'];
    }

    public function getAll() {

        try {

            $jsonData = @file_get_contents($this->filePath);
            
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
            return [];
        }
    }
    

}