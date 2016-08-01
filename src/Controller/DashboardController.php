<?php
namespace App\Controller;

use App\Controller\AppController;

class DashboardController extends AppController
{


    public function index() {
        $this->viewBuilder()->layout('frontend_without_login');
    }

    public function companyLicense() {
        $this->layout = 'frontend_without_login';
    }
    /* Company Dashboard */
    public function company_index() {
        $this->layout = 'frontend_without_login';
    }

}
