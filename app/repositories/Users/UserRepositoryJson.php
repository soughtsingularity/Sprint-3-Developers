<?php 
class UserRepositoryJson implements UserRepositoryInterface {

    private static $_instance = null;
    private $filePath;

    public function __construct(){

        $this->filePath = ROOT_PATH . "/app/data/Users.json";
    
        try {
            if (!file_exists($this->filePath)) {
                if (file_put_contents($this->filePath, json_encode([])) === false) {
                    throw new Exception("No se pudo crear el archivo JSON de usuarios en: " . $this->filePath);
                }
            }
        } catch (Exception $e) {
            error_log("Error inicializando el repositorio de usuarios: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            throw $e;  
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
            $data = file_get_contents($this->filePath);
            if ($data === false) {
                throw new Exception("No se pudo leer el archivo JSON de usuarios en: " . $this->filePath);
            }
    
            $decodedData = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Error al decodificar JSON: " . json_last_error_msg());
            }
    
            return $decodedData ?? [];
        } catch (Exception $e) {
            error_log("Error leyendo el archivo JSON de usuarios: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return null;
        }
    }
    

    private function writeData($data) {
        try {
            if (file_put_contents($this->filePath, json_encode($data, JSON_PRETTY_PRINT)) === false) {
                throw new Exception("No se pudo escribir en el archivo JSON de usuarios: " . $this->filePath);
            }
            return true; // Indica éxito en la escritura
        } catch (Exception $e) {
            error_log("Error escribiendo en el archivo JSON de usuarios: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return false; // Retorna false si ocurre un error
        }
    }
    
    

    public function save($email) {
        try {
            $dataSet = $this->readData();
    
            if ($dataSet === null) {
                throw new Exception("No se pudieron cargar los datos para guardar el usuario.");
            }
    
            error_log("Contenido actual del JSON: " . print_r($dataSet, true));
    
            // Validar si el email ya existe
            foreach ($dataSet as $user) {
                if ($user['email'] === $email) {
                    $_SESSION['newUser'] = $email; // Usuario existente
                    return false; // Usuario ya existe
                }
            }
    
            // Si no encontró el email, crea un nuevo usuario
            $newId = !empty($dataSet) ? max(array_column($dataSet, 'id')) + 1 : 1;
            $newUser = [
                'id' => $newId,
                'email' => $email
            ];
    
            $dataSet[] = $newUser;
    
            // Intentar escribir los datos
            if ($this->writeData($dataSet)) {
                $_SESSION['newUser'] = $email; // Usuario creado
                return true; // Usuario nuevo creado correctamente
            }
    
            // Si falla la escritura
            throw new Exception("Error al guardar el nuevo usuario.");
        } catch (Exception $e) {
            error_log("Error al guardar usuario en JSON: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return false; // Retorna false en caso de error
        }
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
            error_log("Error al obtener usuarios: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return null;
        }
    }
    
}