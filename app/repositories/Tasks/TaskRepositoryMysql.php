<?php

use App\Repositories\TaskRepositoryInterface;


class TaskRepositoryMysql implements TaskRepositoryInterface{

    private static $_instance = null;
    protected $_dbh;
    protected $_table;
    protected $_dbType = 'mysql'; 


    public function __construct($table){

        $settings = parse_ini_file(ROOT_PATH . '/config/settings.ini', true);

        if(!isset($settings[$this->_dbType])){
            throw new Exception("Database configuration for {$this->_dbType} not found.");
        }

        $dbConfig = $settings[$this->_dbType];

        $this->_dbh = new PDO(
            sprintf(
            "%s:host=%s;dbname=%s",
            $dbConfig['driver'],
            $dbConfig['host'],
            $dbConfig['dbname']
            ),
        $dbConfig['user'],
        $dbConfig['password'],
        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
        );

        $this->_table = $table;
    }

    public static function getInstance(){
        if(self::$_instance == null){
            self::$_instance = new self();
        }

        return self::$_instance->_dbh;
    }

    public function fetchOne($id){
        $sql = 'SELECT * FROM ' . $this->_table . ' WHERE id = ?';
        $statement = $this->_dbh->prepare($sql);
        $statement->execute([$id]);
        return $statement->fetch(PDO::FETCH_OBJ); 
    }

    public function showAll(){
        
    }

    public function save(array $data){
        if(isset($data['id'])){
            $sql = 'UPDATE ' . $this->_table . ' SET ';
            $columns = [];
            foreach($data as $key => $value){
                if($key !== 'id'){
                    $columns[] = "$key = ?";
                }
            }
            $sql .= implode(', ', $columns) . ' WHERE id =?';
            $data['id'] = $data['id'];
            $statement = $this->_dbh->prepare($sql);
            return $statement->execute(array_values($data));
        }
    }

    public function delete($id){
        $statement = $this->_dbh->prepare("DELETE FROM " . $this->_table . " WHERE id = ?");
        return $statement->execute([$id]);
    }

}