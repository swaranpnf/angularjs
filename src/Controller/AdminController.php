<?php
//session_start();
class AdminController extends AppController {

    var $uses = array('Admin');

    public function beforeFilter() {
        parent::beforeFilter();
    }

    public function webadmin_index() {
        $this->layout = 'admin';
    }

    public function webadmin_profile() {
        $this->layout = 'admin';
        $id = $_SESSION['Auth']['Admin']['id'];
        $admin = $this->Admin->findById($id);
        if ($this->request->is('post') || $this->request->is('put')) {
            $this->Admin->set($this->request->data);
            if ($this->Admin->validates()) {
            if (isset($this->request->data['Admin']['image']['name']) && !empty($this->request->data['Admin']['image']['name'])) {
                $time = time();
                $filedata = WWW_ROOT . 'admin/img' . $time . '_' . $this->request->data['Admin']['image']['name'];
                move_uploaded_file($this->request->data['Admin']['image']['tmp_name'], $filedata);
                $this->request->data['Admin']['image'] = $time . '_' . $this->request->data['Admin']['image']['name'];
            } 
            else {
                $this->request->data['Admin']['image'] = $admin['Admin']['image'];
            }
            $this->Admin->id = $id;
            if ($this->Admin->save($this->request->data)) {
                $message = $this->Session->setFlash(_('Profile has been updated successfully.'), 'admin_success');
                $this->redirect(array('action' => 'profile'));
            }
            }
            else
            {
               $errors = $this->Admin->validationErrors; 
              
            }
        }
        if (!$this->request->data) {
            $this->request->data = $admin;
            $this->set('admin', $admin);
        }
    }

    public function webadmin_change_password() {
        $this->layout = 'admin';
        $id = $_SESSION['Auth']['Admin']['id'];
        $admin = $this->Admin->findById($id);
        if ($this->request->is('post') || $this->request->is('put')) {
            if (!empty($this->request->data['Admin']['old_password'])) {
                if ($this->Auth->password($this->request->data['Admin']['old_password']) == $admin['Admin']['password']) {
                    if ($this->request->data['Admin']['new_password'] == $this->request->data['Admin']['confrm_password']) {
                        $this->request->data['Admin']['password'] = $this->Auth->password($this->request->data['Admin']['new_password']);
                        $this->Admin->id = $id;
                        if ($this->Admin->save($this->request->data)) {
                            $message = $this->Session->setFlash(_('Password has been updated successfully.'), 'admin_success');
                            $this->redirect(array('action' => 'change_password'));
                        }
                    } else {
                        $message = $this->Session->setFlash(_('Password doesn\'t match.'), 'admin_error');
                    }
                } else {
                    $message = $this->Session->setFlash(_('Password is incorrect.'), 'admin_error');
                }
            } else {
                $message = $this->Session->setFlash(_('Please fill empty fields.'), 'admin_error');
            }
        }
    }

}
