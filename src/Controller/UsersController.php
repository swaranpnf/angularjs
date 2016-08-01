<?php

App::uses('CakeEmail', 'Network/Email');

class UsersController extends AppController {

//    public $components = array('Cookie');
//    var $uses = array('Admin', 'User');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow(array());
    }

// Webadmin Manage Consumers Users
    public function webadmin_index() {
        $this->layout = 'admin';
        $this->loadModel('User');
        $conditions = array('User.type' => "consumer");
        $records_limit = "10";
        $this->paginate = array('limit' => $records_limit, 'conditions' => $conditions, 'order' => 'User.id DESC');
        $this->Paginator->settings = $this->paginate;
        $data = $this->Paginator->paginate('User');
        $this->set('userData', $data);
    }

// Delete the record of selected Consumer.
    public function webadmin_delete($user_id = null) {
        $this->autoRender = false;
        $this->User->delete($user_id);
        $this->Session->setFlash('User Deleted Successfully.', 'admin_success');
        $this->redirect(array('controller' => 'users', 'action' => 'index', 'prefix' => 'webadmin'));
    }

    // Change the status of selected Consumer user's (Active,Inactive)
    public function webadmin_changestatus($id = null) {
        $this->loadModel('User');
        if (!$id) {
            $this->session->setFlash('Invalid User Id.', 'admin_error');
        }
        $iduser = $this->User->findById($id);
        if (!$iduser) {
            $this->session->setFlash('Invalid User.', 'admin_error');
        }
        if ($iduser['User']['status'] == '0') {
            $this->User->updateAll(
                    array('User.status' => "1"), array('User.id' => $id)
            );
            $this->Session->setFlash('Status Activated Successfully.', 'admin_success');
        } else {
            $this->User->updateAll(
                    array('User.status' => "0"), array('User.id' => $id)
            );
            $this->Session->setFlash('Status Deactivated Successfully.', 'admin_success');
        }
        return $this->redirect(array('action' => 'index'));
    }

    // Perform action to multiple selected records of Consumer users.(Delete,Active,Inactive)
    public function webadmin_multiaction() {
        $this->autoRender = false;
        $this->loadModel('User');
        if ($this->request->is('post')) {
            if (isset($this->request->data['User']['foo']) && !empty($this->request->data['User']['foo'])) {
                $splitId = explode(',', $this->request->data['User']['foo']);
                if ($this->request->data['User']['action'] == 'Delete') {
                    foreach ($splitId as $key => $single) {
                        $this->User->delete($single);
                    }
                    $this->Session->setFlash('Selected User has been deleted Successfuly.', 'admin_success');
                } elseif ($this->request->data['User']['action'] == 'Active') {
                    foreach ($splitId as $d) {
                        $User = $this->User->findById($d);
                        $this->User->updateAll(
                                array('User.status' => "1"), array('User.id' => $d)
                        );
                        $this->Session->setFlash('Selected User has been Activated Successfuly.', 'admin_success');
                    }
                } elseif ($this->request->data['User']['action'] == 'Inactive') {
                    foreach ($splitId as $d) {
                        $User = $this->User->findById($d);
                        $this->User->updateAll(
                                array('User.status' => "0"), array('User.id' => $d)
                        );
                        $this->Session->setFlash('Selected User Has been Deactivated Successfuly.', 'admin_success');
                    }
                }
            } else {
                $this->Session->setFlash('Kindly Select atleast one record.', 'admin_error');
            }
        }
        $this->redirect(array('controller' => 'users', 'action' => 'index', 'prefix' => 'webadmin'));
    }

    /* Company Setting */

    public function company_setting() {
        if (!empty($_POST['data'])) {
            $this->layout = "company";
            $checkOld_password = array();
            if ($this->User->validates()) {
                if (!empty($_POST['data']['User']['old_password']) and ! empty($_POST['data']['User']['new_password']) and ! empty($_POST['data']['User']['confirm_password'])) {
                    $checkOld_password = $this->User->find('first', array('conditions' => array(
                            'User.password' => $this->Auth->password($_POST['data']['User']['old_password']),
                            'User.id' => $_SESSION['Auth']['User']['id']
                    )));
                    if (!empty($checkOld_password)) {
                        $this->User->id = $checkOld_password['User']['id'];
                        $this->request->data['User']['password'] = $this->Auth->password($_POST['data']['User']['new_password']);
                        if ($_POST['data']['User']['new_password'] != $_POST['data']['User']['confirm_password']) {
                            $errors['confirm_password'][] = "Please correct your confirm password.";
                            echo json_encode($errors);
                            die;
                        }
                    } else {
                        $errors['old_password'][] = "Please enter correct old password.";
                        echo json_encode($errors);
                        die;
                    }
                }
                if ($this->User->save($this->request->data, array('validate' => true))) {
                    $errors['status']= "success";
                    $errors['message']= "Password changed successfully.";
                    echo json_encode($errors);
                    die;
                } else {
                    $errors = $this->User->validationErrors;
                    echo json_encode($errors);
                    die;
                }
            } else {
                $errors = $this->User->validationErrors;
                echo json_encode($errors);
                die;
            }
        } else {
            $this->layout = "ajax";
        }
    }

}
