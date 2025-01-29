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
            // Validar fechas
            if (!empty($data['startDate']) && !empty($data['endDate'])) {
                $startDate = strtotime($data['startDate']);
                $endDate = strtotime($data['endDate']);
    
                if ($startDate > $endDate) {
                    throw new Exception("La fecha de inicio no puede ser posterior a la fecha de finalización.");
                }
            }
    
            // Validar estado de la tarea
            TaskStatus::validate($data['status']);
    
            // Si la tarea existe, actualizarla
            if (!empty($data['id'])) {
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
    
                return $stmt->rowCount() > 0 ? $data['id'] : false;
            }
    
            // Si la tarea no existe, insertarla
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
            error_log("Error al guardar tarea: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return false;
        }
    }
    
    public function getAll() {
        try {
            $stmt = $this->pdo->prepare("SELECT id, name, status, 
                                         DATE_FORMAT(startDate, '%m/%d/%Y') AS startDate, 
                                         DATE_FORMAT(endDate, '%m/%d/%Y') AS endDate, 
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
                    'startDate' => $task['startDate'] ?? 'Desconocido',
                    'endDate' => $task['endDate'] ?? 'Desconocido',
                    'user' => $task['user'] ?? 'Desconocido',
                ];
            }, $tasks);
    
        } catch (PDOException $e) {
            error_log("Error al obtener tareas: " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            return [];
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
                                         DATE_FORMAT(startDate, '%m/%d/%Y') AS startDate, 
                                         DATE_FORMAT(endDate, '%m/%d/%Y') AS endDate, 
                                         user 
                                         FROM tasks 
                                         WHERE name = :name");
    
            $stmt->execute([':name' => $name]);
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            if (!$tasks) {
                error_log("Error: No se encontraron tareas con el nombre: {$name}");
                return [];
            }
    
            $statusMap = [
                'pending' => 'Pendiente',
                'in_progress' => 'En proceso',
                'completed' => 'Completada'
            ];
    
            return array_map(function($task) use ($statusMap) {
                return [
                    'id' => $task['id'] ?? 'undefined',
                    'name' => $task['name'] ?? 'Desconocido',
                    'status' => isset($task['status']) ? ($statusMap[$task['status']] ?? 'Desconocido') : 'Desconocido',
                    'startDate' => $task['startDate'] ?? 'Desconocido',
                    'endDate' => $task['endDate'] ?? 'Desconocido',
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