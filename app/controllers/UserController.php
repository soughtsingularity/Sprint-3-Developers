<?php

class UserController extends Controller {

    private $userRepository;

    public function __construct(){
        $this->userRepository = UserRepositoryFactory::create();    }


        public function registerAction() {
            
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
        }
}
