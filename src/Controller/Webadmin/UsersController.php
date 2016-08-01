<?php

namespace App\Controller\Webadmin;

use App\Controller\AppController;
use Cake\Event\Event;

class UsersController extends AppController {

//    var $name = 'Users';
//    var $uses = 'User';

    public function beforeFilter(Event $event) {
        parent::beforeFilter($event);
        $this->Auth->allow(["add", "manage", 'changeStatus', 'deleteRecord', 'edit']);
    }

    public function login() {
        if (!empty($this->Auth->user('id'))) {
            return $this->redirect(array('controller' => 'dashboard', 'action' => 'index', 'prefix' => 'webadmin', 'webadmin' => TRUE));
        }
        $this->viewBuilder()->layout('login');
        $this->loadModel('Users');
        if ($this->request->is('post')) {
            if (!empty($this->request->data['username']) and ! empty($this->request->data['password'])) {
                $users = $this->Users->find('all')->where(['Users.username' => $this->request->data['username'], 'Users.password' => sha1($this->request->data['password'])])->first();
            }
            if ($users) {
                $users = json_decode(json_encode($users), true);
                $this->Auth->setUser($users);
                return $this->redirect(array('controller' => 'dashboard', 'action' => 'index', 'prefix' => 'webadmin', 'webadmin' => TRUE));
            } else {
                $this->Flash->error('PLease login with credentials.');
                return $this->redirect(array('controller' => 'users', 'action' => 'login'));
            }
        }
    }

    public function logout() {
        $this->Auth->logout();
        $this->redirect(array('controller' => 'users', 'action' => 'login'));
    }

    public function add() {
        if (!empty($_POST)) {
            $users = $this->Users->newEntity();
            if ($this->request->is('post')) {
                $users = $this->Users->patchEntity($users, $_POST['user']);
                if ($this->Users->save($users)) {
                    echo json_encode(array('status' => 'true'));
                    die;
                }
                echo json_encode(array('status' => 'false'));
                die;
            }
        } else {
            $this->viewBuilder()->layout('backend');
        }
    }

    public function manage() {
        $this->loadModel('User');
        $this->viewBuilder()->layout('backend');
        $users = $this->Users->find()->where(['Users.id !=' => 1]);
        $this->set('users', $users);
    }

    public function changeStatus() {
        $this->autoRender = FALSE;
        $users = $this->Users->find()->where(['Users.id' => $_POST['users']])->first();
        if ($users->status == 1) {
            $users->status = 0;
            $status = 'Deactive';
        } else {
            $users->status = 1;
            $status = 'Active';
        }
        if ($this->Users->save($users)) {
            echo json_encode(array('status' => 'true', 'message' => 'User status changed successfully.', 'response' => $status));
        } else {
            echo json_encode(array('status' => 'false', 'message' => 'Error!There is something wrong.'));
        }
    }

    public function deleteRecord() {
        $this->autoRender = FALSE;
        $this->loadModel('User');
        $entity = $this->Users->get($_POST['users']);
        $result = $this->Users->delete($entity);
        if ($result) {
            echo json_encode(array('status' => 'true', 'message' => 'User status changed successfully.'));
        } else {
            echo json_encode(array('status' => 'false', 'message' => 'Error.'));
        }
    }

    public function edit($id) {
        $this->viewBuilder()->layout('backend');
        $data = $this->Users->get($id);
        $this->set('users', $data);
    }

}
