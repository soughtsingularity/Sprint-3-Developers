<?php 

class UserRepositoryMySQL implements UserRepositoryInterface {

    private static $_instance = null;
    private $pdo;

    private function __construct() {
        $settings = parse_ini_file(__DIR__ . '/../../../config/settings.ini', true);

        $host = $settings['mysql']['host'];
        $dbname = $settings['mysql']['dbname'];
        $user = $settings['mysql']['user'];
        $password = $settings['mysql']['password'];

        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException | \Exception $e) {
            error_log("Error de conexión a la base de datos: " . $e->getMessage());
            throw new Exception("No se pudo conectar a la base de datos.");
        }
    }

    public static function getInstance() {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function save($email) {
        try {
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);

            if ($stmt->fetch()) {
                return false;  
            }

            $stmt = $this->pdo->prepare("INSERT INTO users (email) VALUES (:email)");
            $stmt->execute(['email' => $email]);
            
            return $this->pdo->lastInsertId();

        } catch (PDOException $e) {
            error_log("Error al guardar usuario: " . $e->getMessage());
            return false;
        }
    }

    public function getAll() {
        try {
            $stmt = $this->pdo->query("SELECT email FROM users");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException | \Exception $e) {
            error_log("Error al obtener usuarios: " . $e->getMessage());
            return null;
        }
    }
}