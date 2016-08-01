<?php

//CookieComponent::read();

class FrontendController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow(array('login', 'signup', 'index', 'test', 'forgotPassword', 'resetPassword', 'resetYourPassword'));
    }

//for login view
    public function login() {

        if (isset($_SESSION['Auth']['User']['type']) and ( !empty($_SESSION['Auth']['User']['type']))) {
            $this->layout = 'superadmin_layout';
            if ($_SESSION['Auth']['User']['type'] == "superadmin") {
                $this->redirect(array('controller' => 'superadmin/superadmin', 'action' => 'dashboard'));
            } elseif ($_SESSION['Auth']['User']['type'] == "company_admin") {
                $this->redirect(array('controller' => 'company/softwareLicensing', 'action' => 'index',));
            } else {
                $this->redirect(array('controller' => 'superadmin', 'action' => 'dashboard'));
            }
        } else {
            $this->layout = 'frontend_without_login';
        }
    }

// for signup view
    public function signup() {
        $this->layout = 'frontend_without_login';
    }

    //for forgot password view
    public function forgotPassword() {
        $this->layout = 'frontend_without_login';
    }

    //for reset password view
    public function resetPassword($token = null) {
        $this->layout = 'frontend_without_login';
        $this->loadModel('User');
        //get the details
        if (isset($this->params['pass'][0]) and ( !empty($this->params['pass'][0]))) {
            $token = $this->params['pass'][0];
        }
        $getData = $this->User->find('first', array('conditions' => array('User.token' => $token), 'fields' => array('User.email')));
        if ((isset($getData)) and ( !empty($getData))) {
            $email = $getData['User']['email'];
        } else {
            $email = "";
            $this->Session->setFlash("Your request token has been expired,this action can't be completed.", 'admin_error');
        }
        $this->set('email_to_reset', $email);
    }

    //reset password
    public function resetYourPassword() {
        $this->autoRender = false;
        //set a new password request submit
        $this->loadModel('User');
        if ($this->request->is('post')) {
            $getUserId = $this->User->find('first', array('conditions' => array('User.email' => $this->request->data['User']['email1']), 'fields' => array('User.id')));
            $this->User->set($this->request->data);
            if ($this->User->validates()) {
                $this->set($this->request->data);
                if ($this->request->data['User']['password'] == $this->request->data['User']['con_password']) {
                    $this->request->data['User']['password'] = $this->Auth->password($this->request->data['User']['password']);
                    $this->request->data['User']['token'] = sha1(mt_rand(1, 90000));
                    $this->User->id = $getUserId['User']['id'];
                    if ($this->User->save($this->request->data)) {
                        $response['message'] = "Password changed successfuly.";
                        $response['url'] = BASE_URL . 'login';
                        $response['status'] = 'success';
                        echo json_encode($response);
                        die;
                    }
                } else {
                    $errors['passmatch'] = array("Password and Confirm password doesn't match.");
                    $errors['status'] = 'error';
                    echo json_encode($errors);
                    die;
                }
            } else {
                $errors = $this->User->validationErrors;
                $response['status'] = 'error';
                $response['message'] = "The password hasn't been updated. Please, try again.";
                echo json_encode($errors);
                die;
            }
        }
    }
    
}
