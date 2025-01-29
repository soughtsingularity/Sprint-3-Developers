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
            error_log("Error de conexión a la base de datos: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return false;
        }
    }

    public static function getInstance(){

        if(self::$_instance === null){
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function save(array $data) {

        try {
            if (isset($data['id']) && !empty($data['id'])) {

                TaskStatus::validate($data['status']);
                
                $stmt = $this->pdo->prepare("SELECT id FROM tasks WHERE id = :id");
                $stmt->execute([':id' => $data['id']]);
    
                if ($stmt->fetch()) {
                    
                    $stmt = $this->pdo->prepare("UPDATE tasks 
                                                 SET name = :name, 
                                                     status = :status, 
                                                     startDate = :startDate, 
                                                     endDate = :endDate, 
                                                     user = :user,
                                                     userId = :userId
                                                 WHERE id = :id");
    
                    $stmt->execute([
                        ':id' => $data['id'],
                        ':name' => $data['name'],
                        ':status' => $data['status'],
                        ':startDate' => $data['startDate'],
                        ':endDate' => $data['endDate'],
                        ':user' => $data['user'],
                        ':userId' => $data['userId'],
                    ]);
    
                    if ($stmt->rowCount() > 0) {
                        return $data['id'];
                    } else {
                        return false; 
                    }
                }
            }
    
            
            $stmt = $this->pdo->prepare("INSERT INTO tasks (name, status, startDate, endDate, user, userId) 
                                         VALUES (:name, :status, :startDate, :endDate, :user, :userId)");
    
            $stmt->execute([
                ':name' => $data['name'],
                ':status' => $data['status'],
                ':startDate' => $data['startDate'],
                ':endDate' => $data['endDate'],
                ':user' => $data['user'],
                ':userId' => $data['userId'],
            ]);
    
            return $this->pdo->lastInsertId();  
    
        } catch (PDOException $e) {
            error_log("Error al guardar la tarea: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return false;
        }
    }

    public function getAll() {

        try {
            $stmt = $this->pdo->query("SELECT id, name, status, 
                              DATE_FORMAT(startDate, '%m/%d/%Y') AS startDate, 
                              DATE_FORMAT(endDate, '%m/%d/%Y') AS endDate, 
                              user FROM tasks");  
    
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            $statusMap = [
                'pending' => 'Pendiente',
                'in_progress' => 'En proceso',
                'completed' => 'Completada'
            ];
    
            $tasks = array_map(function($task) use ($statusMap) {
                return [
                    'name' => isset($task['name']) ? $task['name'] : 'Desconocido',
                    'status' => isset($task['status']) && isset($statusMap[$task['status']]) 
                        ? $statusMap[$task['status']] 
                        : 'Desconocido',
                    'startDate' => isset($task['startDate']) ? $task['startDate'] : 'Desconocido',
                    'endDate' => isset($task['endDate']) ? $task['endDate'] : 'Desconocido',
                    'user' => isset($task['user']) ? $task['user'] : 'Desconocido',
                    'id' => isset($task['id']) ? $task['id'] : 'Desconocido'
                ];
            }, $data);
    
            return $tasks;

            } catch (PDOException $e) {
            error_log("Error al obtener tareas: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return null;
        }
    }

    public function getById($id){

        try {
            if (empty($id)) {
                throw new InvalidArgumentException("ID no proporcionado");
            }
            
            $sql = 'SELECT * FROM tasks WHERE id = ?';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
    
            return $stmt->fetch(PDO::FETCH_ASSOC); 
    
        } catch (PDOException $e) {
            error_log("Error al obtener el id {$id}: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return false;
        }
    }

    public function getByName($name) {

        try {
            if (empty($name)) {
                throw new InvalidArgumentException("Nombre no proporcionado");
            }
            
            $query = 'SELECT * FROM tasks WHERE name = ?';
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$name]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC); 
    
            if ($task) {
                $statusMap = [
                    'pending' => 'Pendiente',
                    'in_progress' => 'En proceso',
                    'completed' => 'Completada'
                ];
                
                $task['status'] = $statusMap[$task['status']] ?? 'Desconocido';
    
                return $task;
            } else {
                error_log("Error: No se encontró la tarea con el nombre: {$name} en " . __FILE__ . " línea " . __LINE__);
                return null; 
            }
    
        } catch (PDOException $e) {
            error_log("Error al obtener tarea por nombre: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return false;
        }
    }

    public function delete($id) {

        try {
            $stmt = $this->pdo->prepare("DELETE FROM tasks WHERE id = :id");
            $stmt->execute([':id' => $id]);
    
            return $stmt->rowCount() > 0;
    
        } catch (PDOException $e) {
            error_log("Error al eliminar la tarea con ID {$id}: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return false;
        }
    }
    
}