<?php

class ProfilesController extends AppController {

    var $name = 'Profile';
    var $uses = array('Profile', 'Role');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow(array());
    }//
    
    public function company_modal() {
$this->layout = 'ajax';
	}
    /* Manage Profile  */
    public function company_index() {
        $this->loadModel('Profile');
        $this->layout = 'ajax';
        $profiles = $this->Profile->find('all', array('conditions' => array('Profile.user_id' => $_SESSION['Auth']['User']['id'])));
        $this->set('profiles', $profiles);
    }//

    /* Manage Roles */

    public function company_roles() {
        $this->loadModel('Role');
        $this->layout = 'ajax';
        $roles = $this->Role->find('all', array('conditions' => array('Role.user_id' => $_SESSION['Auth']['User']['id'])));
        $this->set('roles', $roles);
    }//

    /* Manage Profile */

    public function company_manage_profile() {
        $this->layout = 'ajax';
    }//

    /* Create New Profile */

    public function company_createProfilePopup($id = Null) {
        if ($this->request->is('post')) {
            $this->layout = '';
            if (!empty($id)) {
                $checkProfileAlreafyExist = $this->Profile->find('first', array('conditions' => array('Profile.id' => $id)));
                $this->Profile->id = $checkProfileAlreafyExist['Profile']['id'];
                if ($this->Profile->save($this->request->data)) {
                    $lastInsertId = $id;
                    $tr = $this->request->data['Profile']['name'];
                    echo json_encode(array('status' => 'success', 'message' => 'Profile added successfully.', 'role' => 'edit', 'tr' => $tr, 'id' => $lastInsertId));
                } else {
                    $error = $this->Profile->validationErrors;
                    echo json_encode(array('status' => 'error', 'error' => $error));
                }
                die;
            } else {
                $checkProfileAlreafyExist = $this->Profile->find('first', array('conditions' => array(
                        'Profile.name' => $this->request->data['Profile']['name']
                )));
                if (!empty($checkProfileAlreafyExist)) {
                    echo json_encode(array('status' => 'error', 'error' => array('name' => array('Profile Name already exists.'))));
                    die;
                } else {
                    $this->request->data['Profile']['user_id'] = $this->Auth->user('id');
                    if ($this->Profile->save($this->request->data)) {
                        $lastInsertId = $this->Profile->getLastInsertID();
                        $openpup = "openPopUp('get','profiles/createProfilePopup/$lastInsertId')";
                        $tr = '<tr><td>' . $this->request->data['Profile']['name'] . '</td>      <td class="cus_pro_td"><a data-ng-click="' . $openpup . '"  href="javascript:void(0);"><img src="' . BASE_URL . 'images/edit.png" alt=""><span class="icons_cls">Edit</span></a></td>
                                        <td class="cus_pro_td"><a data-hidden-id="' . $lastInsertId . '" href="javascript:void(0);"><img src="' . BASE_URL . 'images/mange_icon.png" alt=""/><span class="icons_cls">Manage</span></a></td>
                                        <td class="cus_pro_td"><a href="javascript:void(0);" class="deleteProfile" data-hidden-id="' . $lastInsertId . '"><img src="' . BASE_URL . 'images/trash.png" alt=""><span class="icons_cls">Trash</span></a></td>
       </tr>';
                        echo json_encode(array('status' => 'success', 'role' => 'add', 'message' => 'Profile added successfully.', 'tr' => $tr));
                    } else {
                        $error = $this->Profile->validationErrors;
                        echo json_encode(array('status' => 'error', 'error' => $error));
                    }
                    die;
                }
            }
        } else {
            if (!empty($id)) {
                $this->layout = 'ajax';
                $checkProfileAlreadyExist = $this->Profile->find('first', array('conditions' => array(
                        'Profile.id' => $id
                )));
                $this->set('editData', $checkProfileAlreadyExist);
            } else {
                $this->layout = 'ajax';
            }
        }
    }//

    /* Delete Profile */

    public function company_deleteProfile() {
        $this->autoRender = FALSE;
        $this->Profile->id = $_POST['delId'];
        if ($this->Profile->delete($_POST['delId'])) {
            $response = $this->company_index();
            echo json_encode(array(
                'status' => 'true',
                'message' => 'Profile deleted successfully.',
            ));

            die;
        } else {
            echo json_encode(array('status' => 'false', 'message' => 'There is something wrong.Please try again.'));
            die;
        }
    }//

    /* Assign Permissions */

    public function company_assignPermissions($profile_id = NULL) {
        $this->layout = 'ajax';
        $this->loadModel('Permission');
        $this->loadModel('ProfilePermission');
        $permission = $this->Permission->find('all', array('conditions' => array('Permission.status' => 1)));
        if (!empty($profile_id)) {
            $this->set('profile_id', $profile_id);
            foreach ($permission as $key => $value) {
                $FindProfilePermission = $this->ProfilePermission->find('first', array('conditions' => array('ProfilePermission.permission_id' => $value['Permission']['id'], 'ProfilePermission.user_id' => $this->Auth->user('id'), 'ProfilePermission.profile_id' => $profile_id)));
                $permission[$key]['Permission']['view_status'] = 0;
                $permission[$key]['Permission']['edit_status'] = 0;
                $permission[$key]['Permission']['profile_permission_id'] = '';
                $permission[$key]['Permission']['profile_id'] = $profile_id;
                if (!empty($FindProfilePermission)) {
                    $permission[$key]['Permission']['view_status'] = $FindProfilePermission['ProfilePermission']['view_status'];
                    $permission[$key]['Permission']['profile_permission_id'] = $FindProfilePermission['ProfilePermission']['id'];
                    $permission[$key]['Permission']['edit_status'] = $FindProfilePermission['ProfilePermission']['edit_status'];
                }
            }
            $this->set('Permission', $permission);
        }//
        if (!empty($_POST['data'])) {
            foreach ($_POST['data'] as $key => $value) {
                if (isset($value['ProfilePermission']['view_status']) and $value['ProfilePermission']['view_status'] == 'on') {
                    $value['ProfilePermission']['view_status'] = '1';
                } else {
                    $value['ProfilePermission']['view_status'] = '0';
                }
                if (isset($value['ProfilePermission']['edit_status']) and $value['ProfilePermission']['edit_status'] == 'on') {
                    $value['ProfilePermission']['edit_status'] = '1';
                } else {
                    $value['ProfilePermission']['edit_status'] = '0';
                }
                $this->request->data['ProfilePermission'] = $value['ProfilePermission'];
                $FindProfilePermission = $this->ProfilePermission->find('first', array('conditions' => array('ProfilePermission.user_id' => $this->Auth->user('id'), 'ProfilePermission.profile_id' => $value['ProfilePermission']['profile_id'], 'ProfilePermission.permission_id' => $value['ProfilePermission']['permission_id'])));
                if (!empty($FindProfilePermission)) {
                    $this->ProfilePermission->id = $FindProfilePermission['ProfilePermission']['id'];
                    $this->ProfilePermission->save($this->request->data);
                } else {
                    $this->ProfilePermission->create();
                    $this->ProfilePermission->save($this->request->data);
                }
            }
            echo json_encode(array('status' => 'true', 'message' => 'Permission assigned to Profile successfully.'));
            die;
        }//
    }//

    /* Role create and Edit */

    public function company_createRolePopup($role_id = NULL) {
        if (!empty($role_id)) {
            $this->layout = '';
            $roleData = $this->Role->findById($role_id);
            $this->set('roleData', $roleData);
            $roles_id = $role_id;
        } else {
            $this->layout = 'ajax';
        }
        if (!empty($_POST)) {
            $find_profile_id = $this->Profile->find('first', array('conditions' => array(
                    'Profile.user_id' => $this->Auth->user('id'),
                    'Profile.name' => $_POST['data']['Role']['profile_id']
            )));
            if (!empty($find_profile_id)) {
                $_POST['data']['Role']['profile_id'] = $find_profile_id['Profile']['id'];
            }
            $_POST['data']['Role']['user_id'] = $this->Auth->user('id');
            $_POST['data']['Role']['is_edit'] = 0;
            $_POST['data']['Role']['is_view'] = 0;
            if (isset($_POST['data']['Role']['is_edit'])) {
                $_POST['data']['Role']['is_edit'] = 1;
            }
            if (isset($_POST['data']['Role']['is_view'])) {
                $_POST['data']['Role']['is_view'] = 1;
            }
            if (!empty($_POST['data']['Role']['role_id'])) {
                $this->Role->id = $_POST['data']['Role']['role_id'];
            }
            if ($this->Role->save($_POST['data'])) {
                if (!empty($_POST['data']['Role']['role_id'])) {
                    $roleData = $this->Role->findById($_POST['data']['Role']['role_id']);
                    $response['data'] = $roleData;
                } else {
                    $last_insert_id = $this->Role->getLastInsertID();
                    $roleDataSuccess = $this->Role->findById($last_insert_id);
                    $open = 'openPopUp("get", "profiles/createRolePopup/' . $last_insert_id . '")';
                    $data = '<tr><td id="name' . $last_insert_id . '">' . $roleDataSuccess['Role']['name'] . '</td>
                                        <td id="profilename' . $last_insert_id . '">' . $roleDataSuccess['Profile']['name'] . '</td>
                                        <td class="cus_pro_td"><a data-hidden-id="' . $last_insert_id . '" class="editRole" href="javascript:void(0)" ng-click="' . $open . '"><img src="' . $this->webroot . 'images/edit.png" alt=""><span class="icons_cls">Edit</span></a></td>
                                        <td class="cus_pro_td"><a data-hidden-id="' . $last_insert_id . '" class="editRole" href="javascript:void(0)"><img src="' . $this->webroot . 'images/refresh.png" alt=""><span class="icons_cls">Assign</span></a></td>
                                        <td class="cus_pro_td"><a data-hidden-id="' . $last_insert_id . '" class="deleteRole" href="javascript:void(0)"><img src="' . $this->webroot . 'images/trash.png" alt=""><span class="icons_cls">Trash</span></a></td>

                                    </tr>';
                    $response['addNew'] = $data;
                }
                $response['status'] = 'true';
                $response['message'] = 'Role added successfully.';
                echo json_encode($response);
                die;
            } else {
                $errors = $this->Role->validationErrors;
                echo json_encode(array('status' => 'false', 'error' => $errors));
                die;
            }
        }
    }//

    /* AotoComplete for Role reports  */

    public function company_all_profiles_json() {
        $this->autoRender = false;
        $profile_pending = $this->Role->find('list', array(
            'fields' => array('profile_id'),
            'conditions' => array(
                'Role.user_id' => $this->Auth->user('id'),
        )));
        $all_profiles = $this->Profile->find('list', array(
            'fields' => array('Profile.name'),
            'conditions' => array(
                'Profile.id !=' => @$profile_pending,
                'Profile.status' => 1,
                'Profile.user_id' => $this->Auth->user('id')
        )));
        return json_encode($all_profiles);
    }//

    /* Delete Role */

    public function company_delete_role() {
        $this->autoRender = false;
        $delete = $this->Role->delete($_POST['delId']);
        if ($delete) {
            echo json_encode(array('status' => 'true', 'message' => 'Role deleted successfully.'));
        }
    }//

    public function company_add_profile() {
        $this->layout = 'ajax';
    }//

}
