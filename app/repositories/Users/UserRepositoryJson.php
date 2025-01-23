<?php 

include_once __DIR__ . "/UserRepositoryInterface.php";

use Illuminate\Support\Collection;

class UserRepositoryJson implements UserRepositoryInterface {

    private static $_instance = null;
    private $filePath;

    public function __construct(){
        $this->filePath = ROOT_PATH . "/app/data/Users.json";
        if(!file_exists($this->filePath)){
            file_put_contents($this->filePath, json_encode([]));
        }
    }

    public static function getInstance(){
        if(self::$_instance == null){
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    private function readData(){
        return json_decode(file_get_contents($this->filePath), true);
    }

    private function writeData($data){
        file_put_contents($this->filePath, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function save($email){
        $dataSet = $this->readData();
        
        // Verificar si el email ya existe
        foreach($dataSet as $user){
            if ($user['email'] === $email) {
                return false; // Email ya registrado
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

    public function getAll(){
        return $this->readData();
    }

    public function findById($id){
        $data = $this->readData();
        return collect($data)->firstWhere('id', $id);
    }

    public function findByEmail($email){
        $data = $this->readData();
        return collect($data)->firstWhere('email', $email);
    }
}