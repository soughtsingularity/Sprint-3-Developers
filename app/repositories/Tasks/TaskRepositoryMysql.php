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
            if (!empty($data['start_date']) && !empty($data['end_date'])) {
                $startDate = strtotime($data['start_date']);
                $endDate = strtotime($data['end_date']);
    
                if ($startDate > $endDate) {
                    throw new Exception("La fecha de inicio no puede ser posterior a la fecha de finalización.");
                }
            }
    
            TaskStatus::validate($data['status']);
    
            if (!empty($data['id'])) {
                $stmt = $this->pdo->prepare("UPDATE tasks 
                                             SET name = :name, 
                                                 status = :status, 
                                                 start_date = :start_date, 
                                                 end_date = :end_date, 
                                                 user = :user, 
                                                 user_id = :user_id
                                             WHERE id = :id");
                $stmt->execute([
                    ':id' => $data['id'],
                    ':name' => $data['name'],
                    ':status' => $data['status'],
                    ':start_date' => $data['start_date'],
                    ':end_date' => $data['end_date'],
                    ':user' => $data['user'],
                    ':user_id' => $data['user_id'],
                ]);
    
                return $stmt->rowCount() > 0 ? $data['id'] : false;
            }
    
            $stmt = $this->pdo->prepare("INSERT INTO tasks (name, status, start_date, end_date, user, user_id) 
                                         VALUES (:name, :status, :start_date, :end_date, :user, :user_id)");
            $stmt->execute([
                ':name' => $data['name'],
                ':status' => $data['status'],
                ':start_date' => $data['start_date'],
                ':end_date' => $data['end_date'],
                ':user' => $data['user'],
                ':user_id' => $data['user_id'],
            ]);

    
            return $this->pdo->lastInsertId();
    
        } catch (PDOException $e) {
            error_log("Error al guardar tarea: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return false;
        }
    }
    
    public function getAll() {
        try {

            $stmt = $this->pdo->prepare("SELECT id, name, status, 
                                         DATE_FORMAT(start_date, '%m/%d/%Y') AS start_date, 
                                         DATE_FORMAT(end_date, '%m/%d/%Y') AS end_date, 
                                         user 
                                         FROM tasks");
            $stmt->execute();
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            $statusMap = [
                'pending' => 'Pendiente',
                'in_progress' => 'En proceso',
                'completed' => 'Completada'
            ];
    
            return array_map(function($task) use ($statusMap) {
                return [
                    'id' => $task['id'],
                    'name' => $task['name'] ?? 'Desconocido',
                    'status' => $statusMap[$task['status']] ?? 'Desconocido',
                    'start_date' => $task['start_date'] ?? 'Desconocido',
                    'end_date' => $task['end_date'] ?? 'Desconocido',
                    'user' => $task['user'] ?? 'Desconocido',
                    
                ];
            }, $tasks);
    
        } catch (PDOException $e) {
            error_log("Error al obtener tareas: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return null;
        }
    }
    
    public function getById($id) {
        try {
            if (empty($id)) {
                throw new InvalidArgumentException("ID no proporcionado");
            }
    
            $stmt = $this->pdo->prepare("SELECT * FROM tasks WHERE id = :id");
            $stmt->execute([':id' => $id]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
    
        } catch (PDOException $e) {
            error_log("Error al obtener tarea con ID {$id}: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return false;
        }
    }
    
    public function getByName($name) {
        try {
            if (empty($name)) {
                throw new InvalidArgumentException("Nombre no proporcionado");
            }
    
            $stmt = $this->pdo->prepare("SELECT id, name, status, 
                                         DATE_FORMAT(start_date, '%m/%d/%Y') AS start_date, 
                                         DATE_FORMAT(end_date, '%m/%d/%Y') AS end_date, 
                                         user 
                                         FROM tasks 
                                         WHERE name = :name");
    
            $stmt->execute([':name' => $name]);
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            if (!$tasks) {
                error_log("Error: No se encontraron tareas con el nombre: {$name}");
                
                return null;
            }
    
            $statusMap = [
                'pending' => 'Pendiente',
                'in_progress' => 'En proceso',
                'completed' => 'Completada'
            ];
    
            return array_map(function($task) use ($statusMap) {
                return [
                    'id' => $task['id'] ?? 'Desconocido',
                    'name' => $task['name'] ?? 'Desconocido',
                    'status' => isset($task['status']) ? ($statusMap[$task['status']] ?? 'Desconocido') : 'Desconocido',
                    'start_date' => $task['start_date'] ?? 'Desconocido',
                    'end_date' => $task['end_date'] ?? 'Desconocido',
                    'user' => $task['user'] ?? 'Desconocido',
                ];
            }, $tasks);
    
        } catch (PDOException $e) {
            error_log("Error al obtener tareas por nombre: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return null;
        }
    }
    
    
    public function delete($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM tasks WHERE id = :id");
            $stmt->execute([':id' => $id]);

            return $stmt->rowCount() > 0;
    
        } catch (PDOException $e) {
            error_log("Error al eliminar tarea con ID {$id}: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return false;
        }
    }
    
    
}