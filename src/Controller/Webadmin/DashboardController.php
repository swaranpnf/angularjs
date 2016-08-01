<?php

namespace App\Controller\Webadmin;
use App\Controller\AppController;

class DashboardController extends AppController {

      public function index() {
       $this->viewBuilder()->layout('backend');
            
      }

}
