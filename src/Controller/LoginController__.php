<?php

App::uses('CakeEmail', 'Network/Email');

class LoginController extends AppController {

    public $components = array('Cookie');
    var $uses = array('Admin', 'User');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow(array('ajax_signup', 'ajax_login', 'activate', 'account_verify', 'ajax_forgot_password', 'resetYourPassword'));
    }

    public function webadmin_index() {
        $this->layout = "admin_login";
        if ($this->request->is('post')) {
            $this->Admin->set($this->request->data);
            if ($this->Admin->validates()) {
                $conds = array(
                    'Admin.username' => $this->request->data['Admin']['username'],
                    'Admin.password' => Security::hash($this->request->data['Admin']['password'], null, TRUE),
                );
                $admin = $this->Admin->find('first', array('conditions' => $conds));
                if ($admin) {
                    $this->Auth->login($admin['Admin']);
                    return $this->redirect($this->Auth->redirect());
                } else {
                    $message = $this->Session->setFlash($this->Auth->loginError, 'admin_loginerror');
                }
            } else {
                $this->Admin->validationErrors;
            }
        }
    }

    public function webadmin_logout() {
        return $this->redirect($this->Auth->logout());
    }

    // User Login
    public function ajax_login_bkup_22june() {
        $this->autoRender = false;
        $this->loadModel('User');
        if ($this->request->is('post')) {
            $this->User->set($this->request->data);
            if ($this->User->validates()) {
                //get the subadmin login statically
                $email_super = "superadmin@reverseadvisor.com";
                $password_super = "superadmin";
                setcookie('testingTimeOut', 'true', time()+600 , '/');
                
                if (($email_super == $this->request->data['User']['email1']) && ($password_super == $this->request->data['User']['password'])) {
                    $superadmin['User']['id'] = "1";
                    $superadmin['User']['email'] = $this->request->data['User']['email1'];
                    $superadmin['User']['username'] = "Cory Williams";
                    $superadmin['User']['type'] = "superadmin";
                    $superadminpass = AuthComponent::password($this->request->data['User']['password']);
                    $superadmin['User']['password'] = $superadminpass;
                    $this->Auth->login($superadmin['User']);
                    $response['url'] = BASE_URL . "superadmin/superadmin/dashboard";
                    // $response['url'] = 'company/software_licensing';
                    if (!empty($this->request->data['User']['remember_me']) && $this->request->data['User']['remember_me'] == "on") {
                        $response['email_remember'] = $this->request->data['User']['email1'];
                        $response['password_remember'] = $this->request->data['User']['password'];
                    }
                    $response['status'] = 'success';
                    echo json_encode($response);
                    die;
                }
                //login other users
                else {
                    $pass = AuthComponent::password($this->request->data['User']['password']);
                    $conditions = array(
                        'User.email' => $this->request->data['User']['email1'],
                        'User.password' => $pass,
//                    'User.status' => 1
                    );
                    $check_email = $this->User->find('first', array(
                        'fields' => array('User.status'),
                        'conditions' => array(
                            'User.email' => $this->request->data['User']['email1'],
                    )));
                    $records_limit = $this->User->find('count', array('conditions' => $conditions));
                    $record = $this->User->find('first', array('conditions' => $conditions, 'fields' => array('User.id', 'User.username', 'User.email', 'User.type', 'User.password')));
                    if (!isset($check_email) || empty($check_email)) {
                        $errors = $this->User->validationErrors;
                        $errors['notexistemail'] = array('Please enter a valid registered email.');
                        $errors['status'] = 'error';
                        echo json_encode($errors);
                        die;
                    }
                    if (isset($record) && (!empty($record)) && $check_email['User']['status'] != '1') {
                        $errors = $this->User->validationErrors;
                        $errors['disablestatus'] = array('Your account is currently disable. Please check your email to activate your account.');
                        $errors['status'] = 'error';
                        $errors['url'] = 'login';
                        echo json_encode($errors);
                        die;
                    }
                    if (!empty($record)) {
                        $this->loadModel('Activity');
                        if ($records_limit == 1) {
                            $response = array();
                            if ($record['User']['type'] == "consumer") {
                                $user_type['User']['login_type'] = 'user';
                                $response['url'] = 'dashboard';
                            } else {
                                $user_type['User']['login_type'] = 'company_license';
                                $response['url'] = 'company/software_licensing';
                            }
                            $user_type['User']['password'] = $record['User']['password'];
                            $this->User->id = $record['User']['id'];
                            $this->User->save($user_type);
                            $lastActivity = $this->Activity->find('first', array(
                                'conditions' => array(
                                    'Activity.user_id' => $record['User']['id']
                                ),
                                'order' => array('id' => 'desc')
                            ));
                            if (!empty($lastActivity) and empty($lastActivity['Activity']['logout_time'])) {
                                $this->Activity->id = $lastActivity['Activity']['id'];
                                $this->request->data['Activity']['logout_time'] = time();
                                $this->request->data['Activity']['total_spent'] = time() - $lastActivity['Activity']['login_time'];
                                $this->Activity->save($this->request->data);
                            }
                            $this->Activity->create();
                            $userActivity['Activity']['user_id'] = $record['User']['id'];
                            $userActivity['Activity']['login_time'] = time();
                            $this->Activity->save($userActivity);
                            $this->Auth->login($record['User']);
                            if (!empty($this->request->data['User']['remember_me']) && $this->request->data['User']['remember_me'] == "on") {
                                $response['email_remember'] = $record['User']['email'];
                                $response['password_remember'] = $this->request->data['User']['password'];
                            }

                            $response['status'] = 'success';
                            echo json_encode($response);
                            die;
                        } else {
                            $message = $this->Session->setFlash(__('Email or password is incorrect'), 'error', array(), 'auth');
                        }
                    } else {
                        $errors = $this->User->validationErrors;
                        $errors['match'] = array('Please provide the valid login credentials');
                        $errors['status'] = 'error';
                        $errors['url'] = 'login';
                        echo json_encode($errors);
                        die;
                    }
                }
            } else {
                $errors = $this->User->validationErrors;
                $errors['status'] = 'error';
                $errors['url'] = 'login';
                echo json_encode($errors);
                die;
            }
        }
    }
    // User Login
    public function ajax_login() {
        $this->autoRender = false;
        $this->loadModel('User');
        if ($this->request->is('post') or($this->request->is('put'))) {
            //pr($this->request->data);
            
             $this->User->set($this->request->data);
            if ($this->User->validates()) {
              $pass = AuthComponent::password($this->request->data['User']['password']);
//               echo $pass;
//                die("hererss");
                    $conditions = array(
                        'User.email' => $this->request->data['User']['email1'],
                        'User.password' => $pass,
//                    'User.status' => 1
                    );
                    $check_email = $this->User->find('first', array(
                        'fields' => array('User.status'),
                        'conditions' => array(
                            'User.email' => $this->request->data['User']['email1'],
                    )));
                    $records_limit = $this->User->find('count', array('conditions' => $conditions));
                    $record = $this->User->find('first', array('conditions' => $conditions, 'fields' => array('User.id', 'User.username', 'User.email', 'User.type', 'User.password')));
                    if (!isset($check_email) || empty($check_email)) {
                        $errors = $this->User->validationErrors;
                        $errors['notexistemail'] = array('Please enter a valid registered email.');
                        $errors['status'] = 'error';
                        echo json_encode($errors);
                        die;
                    }
                    if (isset($record) && (!empty($record)) && $check_email['User']['status'] != '1') {
                        $errors = $this->User->validationErrors;
                        $errors['disablestatus'] = array('Your account is currently disable. Please check your email to activate your account.');
                        $errors['status'] = 'error';
                        $errors['url'] = 'login';
                        echo json_encode($errors);
                        die;
                    }
                    if (!empty($record)) {
                        $this->loadModel('Activity');
                        if ($records_limit == 1) {
                            $response = array();
                            if ($record['User']['type'] == "superadmin") {
                                 
                                $user_type['User']['login_type'] = 'superadmin';
                              $response['url'] = BASE_URL . "superadmin/superadmin/dashboard";
                            }
                            elseif ($record['User']['type'] == "consumer") {
                               
                                $user_type['User']['login_type'] = 'user';
                                $response['url'] = 'dashboard';
                            } else {
                             
                                $user_type['User']['login_type'] = 'company_license';
                                $response['url'] =  BASE_URL . 'company/software_licensing';
                            }
                            $user_type['User']['password'] = $record['User']['password'];
                            $this->User->id = $record['User']['id'];
                            $this->User->save($user_type);
                            
                            
                            $lastActivity = $this->Activity->find('first', array(
                                'conditions' => array(
                                    'Activity.user_id' => $record['User']['id']
                                ),
                                'order' =>array('Activity.id DESC'),
                                'limit' => '1',
                                'fields'=>array('Activity.id','Activity.login_time','Activity.logout_time','Activity.total_spent')
                            ));
                           
                            if (!empty($lastActivity) and empty($lastActivity['Activity']['logout_time'])) {
                                $this->Activity->id = $lastActivity['Activity']['id'];
                                $this->request->data['Activity']['logout_time'] = time();
                                $this->request->data['Activity']['total_spent'] = time() - $lastActivity['Activity']['login_time'];
                                $this->Activity->save($this->request->data);
                            }
                            $this->Activity->create();
                            $userActivity['Activity']['user_id'] = $record['User']['id'];
                            $userActivity['Activity']['login_time'] = time();
                             $this->Activity->save($userActivity);
                            $this->Auth->login($record['User']);
                            if (!empty($this->request->data['User']['remember_me']) && $this->request->data['User']['remember_me'] == "on") {
                                $response['email_remember'] = $record['User']['email'];
                                $response['password_remember'] = $this->request->data['User']['password'];
                            }

                            $response['status'] = 'success';
                            echo json_encode($response);
                            die;
                        } else {
                            $message = $this->Session->setFlash(__('Email or password is incorrect'), 'error', array(), 'auth');
                        }
                    } 
                    else {
                        $errors = $this->User->validationErrors;
                        $errors['match'] = array('Please provide the valid login credentials');
                        $errors['status'] = 'error';
                        $errors['url'] = 'login';
                        echo json_encode($errors);
                        die;
                    }
                //}
            } else {
                $errors = $this->User->validationErrors;
                $errors['status'] = 'error';
                $errors['url'] = 'login';
                echo json_encode($errors);
                die;
            }
        }
    }

    // Register New User
    public function ajax_signup() {
        $this->autoRender = false;
        if ($this->request->is('post')) {
            $this->User->set($this->request->data);
            if ($this->User->validates()) {
                $this->set($this->request->data);
                //$this->User->create();
                $this->request->data['User']['password'] = $this->Auth->password($this->request->data['User']['password']);
                $this->request->data['User']['last_active'] = time();
                $this->request->data['User']['type'] = "consumer";
                $this->request->data['User']['token'] = sha1(mt_rand(1, 90000));
                if (isset($this->request->data['User']['terms']) && ($this->request->data['User']['terms'] == "on")) {

                    if ($this->User->save($this->request->data)) {
                        $Last_id = $this->User->getLastInsertID();
                        $last_signup = $this->User->findById($Last_id);
                        $messageToUser = "Thanks,you are successfully subscribed with ReverseAdvisor,confirm your registraion by clicking below.";
                        $Email = new CakeEmail();
                        $Email->from(array('reply@reverseadvisor.com' => 'Registration Confirmation'));
                        $Email->to($this->request->data['User']['email']);
                        $Email->template('register_user');
                        $Email->emailFormat('html');
                        $Email->viewVars(array('userName' => $this->request->data['User']['username'], 'usertoken' => $last_signup['User']['token'], 'messageToUser' => $messageToUser));
                        $Email->subject('ReverseAdvisor:Registraion Confirmation');
                        $Email->send();
                        $response['message'] = "Registration successfull,please check your mail for activate your account.";
                        $response['url'] = BASE_URL;
                        $response['status'] = 'success';
                        echo json_encode($response);
                        die;
                    }
                } else {
                    $response['status'] = 'error';
                    $response['terms'] = 'Please agree to ReverseAdvisor\'s
Terms of Service.';
                    echo json_encode($response);
                    die;
                }
            } else {
                $errors = $this->User->validationErrors;
                $response['status'] = 'error';
                $response['message'] = 'The Data could not be saved. Please, try again.';
                echo json_encode($errors);
                die;
            }
        }
    }

    // Logout
    public function logout() {
//        pr($_SERVER["HTTP_REFERER"]); exit;
        //update the last login activity before logout
        $this->loadModel('Activity');
        $this->loadModel('User');
        $loggedInId = $this->Auth->user('id');
        $this->User->id = $loggedInId;
        $currentTime = time();
        $this->User->saveField('last_active', $currentTime);
        $lastActivity = $this->Activity->find('first', array(
            'conditions' => array(
                'Activity.user_id' => $loggedInId
            ),
            'order' => array('id' => 'desc')
        ));
        if (!empty($lastActivity) and empty($lastActivity['Activity']['logout_time'])) {
            $this->Activity->id = $lastActivity['Activity']['id'];
            $this->request->data['Activity']['logout_time'] = time();
            $this->request->data['Activity']['total_spent'] = time() - $lastActivity['Activity']['login_time'];
            $this->Activity->save($this->request->data);
        }
        return $this->redirect($this->Auth->logout());
    }

    /* Company Logout */

    public function company_logout() {
        //update the last login activity before logout
        $this->loadModel('User');
        $this->loadModel('Activity');
        $loggedInId = $this->Auth->user('id');
        $this->User->id = $loggedInId;
        $currentTime = time();
        $this->User->saveField('last_active', $currentTime);
        $lastActivity = $this->Activity->find('first', array(
            'conditions' => array(
                'Activity.user_id' => $loggedInId
            ),
            'order' => array('id' => 'desc')
        ));
        if (!empty($lastActivity) and empty($lastActivity['Activity']['logout_time'])) {
            $this->Activity->id = $lastActivity['Activity']['id'];
            $this->request->data['Activity']['logout_time'] = time();
            $this->request->data['Activity']['total_spent'] = time() - $lastActivity['Activity']['login_time'];
            $this->Activity->save($this->request->data);
        }
        return $this->redirect($this->Auth->logout());
    }

    /* acount verification */

    public function account_verify($token = null) {
        if (isset($token) and ( !empty($token))) {
            $getTheUser = $this->User->find('first', array('conditions' => array('User.token' => $token), 'fields' => array('User.id')));
            $userId = $getTheUser['User']['id'];
            $this->User->id = $userId;
            $updateStatus = $this->User->saveField('status', '1');
            if ($updateStatus) {
                $this->Session->setFlash('Account Activated Successfully.', 'admin_success');
            }
            return $this->redirect(array('controller' => 'Frontend', 'action' => 'login', 'home'));
        }
    }

    //forgot password
    public function ajax_forgot_password() {
        $this->autoRender = false;
        $this->loadModel('User');
        if ($this->request->is('post')) {
            $this->User->set($this->request->data);
            if ($this->User->validates()) {
                $conditions = array('User.email' => $this->request->data['User']['email1']);
                $record = $this->User->find('first', array('conditions' => $conditions, 'fields' => array('User.id', 'User.email', 'User.token', 'User.username', 'User.first_name', 'User.last_name')));
                if (isset($record) && (empty($record))) {
                    $errors = $this->User->validationErrors;
                    $errors['notexistemail'] = array("Please enter a valid registered email,we couldn't find this in our records.");
                    $errors['status'] = 'error';
                    echo json_encode($errors);
                    die;
                } else {
                    //send the mail to user
                    $messageToUser = "Here is the link you’ll need to change your ReverseAdvisor password.  If you did not make this request, please contact your Administrator.  You may also want to still change your password for security purposes.";
                    $Email = new CakeEmail();
                    $Email->from(array('reply@reverseadvisor.com' => 'Forgot password Request'));
                    $Email->to($record['User']['email']);
                    $Email->template('forgot_password');
                    $Email->emailFormat('html');
                    $Email->viewVars(array('userName' => $record['User']['first_name'], 'usertoken' => $record['User']['token'], 'messageToUser' => $messageToUser));
                    $Email->subject('ReverseAdvisor:Forgot Password');
                    $Email->send();
                    $response['message'] = "A request has been sent to your email,follow the instruction to set a new password.";
                    $response['url'] = BASE_URL . "frontend/forgotPassword";
                    $response['status'] = 'success';
                    echo json_encode($response);
                    die;
                }
            } else {
                $errors = $this->User->validationErrors;
                $errors['status'] = 'error';
                $errors['url'] = 'login';
                echo json_encode($errors);
                die;
            }
        }
    }

}
