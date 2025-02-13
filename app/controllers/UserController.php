<?php

class UserController extends Controller {

    private $userRepository;

    public function __construct()
    {

        $this->userRepository = UserRepositoryFactory::create();
    }

    public function registerAction() {

        try{

            $email = $_POST['email'] ?? null;

            if ($email === null || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error_message'] = $email === null ? "El email es requerido" : "El email no es válido";
                header("Location: " . WEB_ROOT . "/index.php/home/login?error=1");
                exit();
            }

            $userSaved = $this->userRepository->save($email); 

            $_SESSION['success_message'] = $userSaved
                ? "Usuario {$email} creado correctamente."
                : "Bienvenido {$email}";
    
            header("Location: " . WEB_ROOT . "/index.php/home/login?success=1");
            exit();

        }catch(Exception $e){
            error_log("Error al crear el usuario " . $e->getMessage() . " en " . __FILE__ . " línea " . __LINE__);
            $_SESSION['error_message'] = "Error al crear el usuario";
            header("Location: " . WEB_ROOT . "/index.php/home/login?error=1");
            exit();

        }
    }
}
