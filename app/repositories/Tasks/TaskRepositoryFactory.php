<?php

class TaskRepositoryFactory {
    
    public static function create() {
        $settings = parse_ini_file(__DIR__ . '/../../../config/settings.ini', true);

        $repositoryType = $settings['database']['task_repository'];

        switch ($repositoryType) {
            case 'mysql':
                return TaskRepositoryMysql::getInstance();

            case 'mongodb':
                return TaskRepositoryMongodb::getInstance();

            case 'json':
                return TaskRepositoryJson::getInstance();
            
            default:
                throw new Exception("Error: Tipo de repositorio no válido en settings.ini");
        }
    }
}