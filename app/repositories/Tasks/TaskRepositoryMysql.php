<?php

class TaskRepositoryMysql implements TaskRepositoryInterface{

    private static $_instance = null;
    private $pdo;

    public function __construct(){

        $settings = parse_ini_file(__DIR__ . '/../../../config/settings.ini', true);

        $host = $settings['mysql']['host'];
        $dbname = $settings['mysql']['dbname'];
        $user = $settings['mysql']['user'];
        $password = $settings['mysql']['password'];

        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }

    public static function getInstance(){
        if(self::$_instance === null){
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function getById($id){
        $sql = 'SELECT * FROM tasks WHERE id = ?';
        $statement = $this->pdo->prepare($sql);
        $statement->execute([$id]);
        return $statement->fetch(PDO::FETCH_ASSOC); 
    }
    

    public function getAll() {
        try {
            // Obtener todos los campos relevantes de la tabla 'tasks'
            $stmt = $this->pdo->query("SELECT id, name, status, 
                          DATE_FORMAT(startDate, '%m/%d/%Y') AS startDate, 
                          DATE_FORMAT(endDate, '%m/%d/%Y') AS endDate, 
                          user FROM tasks");            
            // Obtener todas las filas como un array asociativo
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // Asegurar siempre que se devuelve un array válido
            return is_array($tasks) ? $tasks : [];
            
        } catch (PDOException $e) {
            error_log("Error al obtener tareas: " . $e->getMessage());
            return [];
        }
    }
    

    public function save(array $data) {
        try {
            if (isset($data['id']) && !empty($data['id'])) {
                $stmt = $this->pdo->prepare("SELECT id FROM tasks WHERE id = :id");
                $stmt->execute([':id' => $data['id']]);
    
                if ($stmt->fetch()) {
                    // Si la tarea existe, actualizarla
                    $stmt = $this->pdo->prepare("UPDATE tasks 
                                                 SET name = :name, 
                                                     status = :status, 
                                                     startDate = :startDate, 
                                                     endDate = :endDate, 
                                                     user = :user 
                                                 WHERE id = :id");
    
                    $stmt->execute([
                        ':id' => $data['id'],
                        ':name' => $data['name'],
                        ':status' => $data['status'],
                        ':startDate' => $data['startDate'],
                        ':endDate' => $data['endDate'],
                        ':user' => $data['user']
                    ]);
    
                    return $data['id'];  // Retornar el ID de la tarea actualizada
                }
            }
    
            // Si no hay ID, se inserta como una nueva tarea
            $stmt = $this->pdo->prepare("INSERT INTO tasks (name, status, startDate, endDate, user) 
                                         VALUES (:name, :status, :startDate, :endDate, :user)");
    
            $stmt->execute([
                ':name' => $data['name'],
                ':status' => $data['status'],
                ':startDate' => $data['startDate'],
                ':endDate' => $data['endDate'],
                ':user' => $data['user']
            ]);
    
            return $this->pdo->lastInsertId();  // Retornar el nuevo ID generado
    
        } catch (PDOException $e) {
            error_log("Error al guardar la tarea: " . $e->getMessage());
            return false;
        }
    }
    
    
    public function delete($id) {
        try {
            // Preparar la consulta para eliminar la tarea por ID
            $stmt = $this->pdo->prepare("DELETE FROM tasks WHERE id = :id");
    
            // Ejecutar la consulta con el ID proporcionado
            $stmt->execute(['id' => $id]);
    
            // Retornar true si se eliminó al menos una fila, false si no se encontró el ID
            return $stmt->rowCount() > 0;
            
        } catch (PDOException $e) {
            // Registrar el error en el log para depuración
            error_log("Error al eliminar la tarea con ID {$id}: " . $e->getMessage());
            return false;
        }
    }
    

}