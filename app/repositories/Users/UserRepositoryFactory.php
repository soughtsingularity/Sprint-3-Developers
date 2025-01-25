<?php

class UserRepositoryFactory {
    
    public static function create() {
        $settings = parse_ini_file(__DIR__ . '/../../../config/settings.ini', true);

        $repositoryType = $settings['database']['user_repository'];

        switch ($repositoryType) {
            case 'mysql':
                return UserRepositoryMysql::getInstance();

            case 'mongodb':
                return UserRepositoryMongodb::getInstance();

            case 'json':
                return UserRepositoryJson::getInstance();
            
            default:
                throw new Exception("Error: Tipo de repositorio no válido en settings.ini");
        }
    }
}