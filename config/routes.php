<?php 

/**
 * Used to define the routes in the system.
 * 
 * A route should be defined with a key matching the URL and an
 * controller#action-to-call method. E.g.:
 * 
 * '/' => 'index#index',
 * '/calendar' => 'calendar#index'
 */
$routes = array(
    '/' => 'Home#login', // Mostrar formulario de login
    '/index.php/user/register' => 'User#register', // Procesar login
    '/index.php/tasks/list' => 'Task#list', // Mostrar lista de tareas
    '/index.php/tasks/add' => 'Task#add', // Mostrar formulario de nueva tarea
    '/tasks/store' => 'Task#store', // Procesar la nueva tarea (POST)
    '/index.php/tasks/edit/:id' => 'Task#edit',  // Mostrar formulario de edición
    '/tasks/index.php/update/:id' => 'Task#update', // Procesar edición (POST)
    '/tasks/delete/:id' => 'Task#deleteConfirm', // Mostrar confirmación de eliminación
    '/tasks/destroy/:id' => 'Task#destroy', // Procesar la eliminación (POST)
    '/tasks/search' => 'Task#search', // Buscar tareas por nombre

    // Rutas para manejar errores
    '/error/notfound' => 'Error#notFound',  // Muestra una vista con la página para recursos no encontrados (404)
    '/error/server' => 'Error#serverError'  // Muestra una vista con el error interno del servidor
);


