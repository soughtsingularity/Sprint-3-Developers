<?php

class UserController extends Controller {

    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository){
        $this->userRepository = $userRepository;
    }


    public function registerAction() {
        $email = $this->_getParam('email');
    
        // Validar el email
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->userRepository->save($email);
        }
    
        // Redirigir siempre a la lista de tareas
        header("Location: " . WEB_ROOT . "/index.php/tasks/list");
        exit();        exit();
    }
}
