<?php

namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;

class FrontendController extends AppController {

    public function beforeFilter(Event $event) {
        $this->Auth->allow(['login']);
    }

    public function login() {
    $this->viewBuilder()->layout('frontend_without_login');
    }

}
