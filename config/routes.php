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
    '/home/login' => 'Home#login',
    '/user/register' => 'User#register', // Procesar login
    '/tasks/list' => 'Task#list', // Mostrar lista de tareas o tarea por nombre
    '/tasks/add' => 'Task#add', // Mostrar formulario de creación de tareas
    '/tasks/edit' => 'Task#edit',  // Mostrar formulario de edición de tareas
    '/tasks/save' => 'Task#save', // Crear o actualizar la tarea (POST)
    '/tasks/delete/' => 'Task#delete', // Mostrar confirmación de eliminación

    // Rutas para manejar errores
    '/error/notfound' => 'Error#notFound',  // Muestra una vista con la página para recursos no encontrados (404)
    '/error/server' => 'Error#serverError'  // Muestra una vista con el error interno del servidor
);


