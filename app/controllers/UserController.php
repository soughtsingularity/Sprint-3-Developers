<?php

class UserController extends Controller {

    private $userRepository;

    public function __construct(){
        $this->userRepository = UserRepositoryFactory::create();    }


    public function registerAction() {
        $email = $this->_getParam('email');
    
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->userRepository->save($email);
        }
    
        header("Location: " . WEB_ROOT . "/index.php/tasks/list");
        exit();
    }
}
