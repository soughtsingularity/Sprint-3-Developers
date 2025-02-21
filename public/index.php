<?php

error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/app_errors.log');
date_default_timezone_set('Europe/Madrid');

// Definir la ruta del archivo de logs
define('LOG_FILE', __DIR__ . '/../logs/app_errors.log');

// Función para registrar errores
function logError($message, $file, $line) {
    error_log("[" . date('Y-m-d H:i:s') . "] ERROR en $file línea $line: $message");
}

// defines the web root
define('WEB_ROOT', substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], '/index.php')));
// defindes the path to the files
define('ROOT_PATH', realpath(dirname(__FILE__) . '/../'));
// defines the cms path
define('CMS_PATH', ROOT_PATH . '/lib/base/');

// starts the session
session_start();

// includes the system routes. Define your own routes in this file
include(ROOT_PATH . '/config/routes.php');

spl_autoload_register(function ($className) {

    if (strlen($className) > 10 && substr($className, -10) == 'Controller') {
        $file = __DIR__ . '/../app/controllers/' . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        } else {
            die("Error: No se encontró el archivo del controlador en $file");
        }
    }

    $file = __DIR__ . '/../lib/base/' . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
        return;
    }

    $file = __DIR__ . '/../app/enums/' . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
        return;
    }

	$repositoryPaths = [
        __DIR__ . '/../app/repositories/' . $className . '.php',
        __DIR__ . '/../app/repositories/Users/' . $className . '.php', 
        __DIR__ . '/../app/repositories/Tasks/' . $className . '.php', 

    ];

    foreach ($repositoryPaths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }

    die("Error: No se encontró la clase '$className'.");
});


/**
 * Standard framework autoloader
 * @param string $className
 */
function autoloader($className) {
	// controller autoloading
	if (strlen($className) > 10 && substr($className, -10) == 'Controller') {
		if (file_exists(ROOT_PATH . '/app/controllers/' . $className . '.php') == 1) {
			require_once ROOT_PATH . '/app/controllers/' . $className . '.php';
		}
	}
	else {
		if (file_exists(CMS_PATH . $className . '.php')) {
			require_once CMS_PATH . $className . '.php';
		}
		else if (file_exists(ROOT_PATH . '/lib/' . $className . '.php')) {
			require_once ROOT_PATH . '/lib/' . $className . '.php';
		}
		else {
			require_once ROOT_PATH . '/app/models/'.$className.'.php';
		}
	}
}

require_once __DIR__ . '/../config/routes.php';

require_once __DIR__ . '/../lib/base/Router.php';

require_once __DIR__ . '/../vendor/autoload.php';


$router = new Router();
$router->execute($routes);



