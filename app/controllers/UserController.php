<?php

class UserController extends Controller {

    private $userRepository;

    public function __construct(){

        try{

            $this->userRepository = UserRepositoryFactory::create();    

        }catch(Exception $e){
            error_log("Error initializing the repository: " . $e->getMessage());
            $_SESSION['error_message'] = "Error al iniciar la aplicación";
            header("Location: " . WEB_ROOT . "/index.php/home/login?error=1");
            exit();

        }
    }

    public function registerAction() {

        try{

            $email = $this->_getParam('email');
    
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $newUser = $this->userRepository->save($email);
            }
        
            if (isset($newUser) && $newUser) {
                $_SESSION['success_message'] = "Usuario creado correctamente.";
                header("Location: " . WEB_ROOT . "/index.php/home/login?success=1");
            } elseif ($newUser === false) {
                $_SESSION['success_message'] = "Bienvenido {$email}.";
                header("Location: " . WEB_ROOT . "/index.php/home/login?success=1");
            }
            exit();

        }catch(Exception $e){
            error_log("Error al crear el usuario " . $e->getMessage());
            $_SESSION['error_message'] = "Error al crear el usuario";
            header("Location: " . WEB_ROOT . "/index.php/home/login?error=1");

        }
    }
}
