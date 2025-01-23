<?php 

require 'vendor/autoload.php';

use Illuminate\Support\Collection;
use App\Repositories\TaskRepositoryInterface;

class TaskRepositoryJson implements TaskRepositoryInterface{

    private static $_instance = null;
    private $filePath;

    public function __construct(){
        $this->filePath = ROOT_PATH . "/data/Tasks.json";
        if(!file_exists($this->filePath)){
            file_put_contents($this->filePath, json_encode([]));
        }
    }

    public static function getInstance(){
        if(self::$_instance == null){
            self::$_instance = new self();
        }

        return self::$_instance->filePath;
    }

    private function readData(){
        return json_decode(file_get_contents($this->filePath), true);
    }

    private function writeData($data){
        file_put_contents($this->filePath, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function showAll(){
        
    }

    public function fetchOne($id){
        $data = $this->readData();
        return collect($data)->firstWhere('id', $id);
    }
    
    public function save(array $data){
        $dataSet = $this->readData();
        if(isset($data['id'])){
            foreach($dataSet as &$item){
                if($item['id'] == $data['id']){
                    $item = array_merge($item, $data);
                }
            }
        }else{
            $data['id'] = count($dataSet) + 1;
            $dataset[] = $data;
        }
        $this->writeData($dataSet);
        return $data['id'];
    }

    public function delete($id){
        $dataSet = $this->readData();
        $filteredData = array_filter($dataSet, fn($task) => $task['id'] !== $id);
        $this->writeData($filteredData);
        return true;
    }

} 