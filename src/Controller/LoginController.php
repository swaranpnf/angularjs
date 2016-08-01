<?php
namespace App\Controller;
use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Validation\Validator;

class LoginController extends AppController {

    public function beforeFilter(Event $Event) {
        $this->Auth->allow(['ajaxLogin']);
    }

    public function ajaxLogin() {
        $this->autoRender = false;
        $this->loadModel('User');
        $this->request->allowMethod('post');
        $validator=new Validator();
        $errors = $validator->errors($this->request->data());

        print_r($errors);
        die;
//        if ($data->errors()) {
//            $this->set([
//                'errors' => $data->errors(),
//                '_serialize' => ['errors']]);
//            return;
//        }


        $contact = new Index();
        if ($this->request->is('post')) {
            if ($contact->execute($this->request->data)) {
                $this->Flash->success('We will get back to you soon.');
            } else {
                $errors = $contact->errors();
                pj($errors);
                die;
                $this->Flash->error('There was a problem submitting your form.');
            }
        }
        echo "<pre>";
        print_r($errors);
        die('schvsd');


        /*  if ($this->request->is('post')) {
          if ($this->User->validates()) {
          //get the subadmin login statically
          $email_super = "superadmin@reverseadvisor.com";
          $password_super = "superadmin";
          if (($email_super == $this->request->data['User']['email1']) && ($password_super == $this->request->data['User']['password'])) {
          $superadmin['User']['email'] = $this->request->data['User']['email1'];
          $superadmin['User']['username'] = "Cory Williams";
          $superadmin['User']['type'] = "superadmin";
          $this->Auth->setUser($superadmin['User']);
          //                    $this->Auth->login($superadmin['User']);
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
          $this->Auth->setUser($record['User']);
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
          } */
    }

    public function index() {
        
    }

    // User Login
    public function ajaxLsadasogin() {
        $this->autoRender = false;
        $this->loadModel('User');
        if ($this->request->is('post')) {
            $this->User->set($this->request->data);
            if ($this->User->validates()) {
                //get the subadmin login statically
                $email_super = "superadmin@reverseadvisor.com";
                $password_super = "superadmin";
                if (($email_super == $this->request->data['User']['email1']) && ($password_super == $this->request->data['User']['password'])) {
                    $superadmin['User']['email'] = $this->request->data['User']['email1'];
                    $superadmin['User']['username'] = "Cory Williams";
                    $superadmin['User']['type'] = "superadmin";
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

}
