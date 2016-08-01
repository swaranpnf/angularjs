<?php

class RoleController extends AppController {

    var $name = "Role";

    /* Manage role */

    public function company_index() {
        $this->layout = "ajax";
        $allRoles = $this->Role->generateTreeList(
                array('comp_id' => $_SESSION['Auth']['User']['id']), null, null, '&nbsp;&nbsp;'
        );
        $this->set('allRoles', $allRoles);
    }

    public function company_show_role() {
        $this->layout = "ajax";
        $allRoles = $this->Role->generateTreeList(
                array('comp_id' => $_SESSION['Auth']['User']['id']), null, null, '&nbsp;&nbsp;'
        );
        $this->set('allRoles', $allRoles);
    }

    /* Add role or Update Role */

    public function company_new_role($edit_role = null) {
        $this->loadModel('RoleSource');
        $roles = $this->Role->find('all', array('conditions' => array('Role.comp_id' => $this->Auth->user('id'))));
        $this->set('roles', $roles);
        if (!empty($edit_role)) {
            $edit_data = $this->Role->findById($edit_role);
            $this->set('edit_data', $edit_data);
            $role_source = $this->RoleSource->find('all', array('conditions' => array(
                    'RoleSource.role_id' => $edit_role,
                    'RoleSource.status' => 1
            )));
            $this->set('role_source', $role_source);
        }
        if (!empty($_POST)) {
            $findRole = $this->Role->find('first', array('conditions' => array('Role.name' => @$this->request->data['Role']['parent_id'])));

            if (!empty($findRole)) {
                $this->request->data['Role']['parent_id'] = $findRole['Role']['id'];
            }
            $this->request->data['Role']['comp_id'] = $this->Auth->user('id');
            if (!isset($this->request->data['Role']['view_client'])) {
                $this->request->data['Role']['view_client'] = 0;
            }
            if (!isset($this->request->data['Role']['edit_client'])) {
                $this->request->data['Role']['edit_client'] = 0;
            }
            if (!isset($this->request->data['Role']['view_partner'])) {
                $this->request->data['Role']['view_partner'] = 0;
            }
            if (!isset($this->request->data['Role']['edit_partner'])) {
                $this->request->data['Role']['edit_partner'] = 0;
            }
            if (!isset($this->request->data['Role']['view_contact'])) {
                $this->request->data['Role']['view_contact'] = 0;
            }
            if (!isset($this->request->data['Role']['edit_contact'])) {
                $this->request->data['Role']['edit_contact'] = 0;
            }
            if (!isset($this->request->data['Role']['view_leads'])) {
                $this->request->data['Role']['view_leads'] = 0;
            }
            if (!isset($this->request->data['Role']['edit_leads'])) {
                $this->request->data['Role']['edit_leads'] = 0;
            }
            $data = array('Role' => $this->request->data['Role']);
            if (isset($edit_role) and ! empty($edit_role)) {
                $this->request->data['Role']['id'] = $edit_role;
                $parent_id = $this->request->data['Role']['parent_id'];
                if (isset($this->request->data['Role']['access_type']) and $this->request->data['Role']['access_type'] != 'customize_access') {
                    $this->request->data['Role']['view_client'] = 0;
                    $this->request->data['Role']['edit_client'] = 0;
                    $this->request->data['Role']['view_partner'] = 0;
                    $this->request->data['Role']['edit_partner'] = 0;
                    $this->request->data['Role']['view_contact'] = 0;
                    $this->request->data['Role']['edit_contact'] = 0;
                    $this->request->data['Role']['edit_leads'] = 0;
                    $this->request->data['Role']['view_leads'] = 0;
                }
                $data = array('Role' => $this->request->data['Role']);
                $this->Role->save($data);
                if (isset($this->request->data['RoleSource']) and ! empty($this->request->data['RoleSource'])) {
                    foreach ($this->request->data['RoleSource'] as $key => $value) {
                        $this->RoleSource->create();
                        $this->request->data['RoleSource'] = $value;
                        if (!isset($this->request->data['RoleSource']['source_status'])) {
                            $this->request->data['RoleSource']['source_status'] = 0;
                        } else {
                            $this->request->data['RoleSource']['source_status'] = 1;
                            $this->request->data['RoleSource']['role_id'] = $edit_role;
                            $this->RoleSource->save($this->request->data);
                        }
                    }
                }
                $response['status'] = 'true';
                $response['message'] = 'Role uodated successfully.';
                $response['last_insert_id'] = $edit_role;
            } else {
                if (isset($this->request->data['Role']['access_type']) and $this->request->data['Role']['access_type'] != 'customize_access') {
                    $this->request->data['Role']['view_client'] = 0;
                    $this->request->data['Role']['edit_client'] = 0;
                    $this->request->data['Role']['view_partner'] = 0;
                    $this->request->data['Role']['edit_partner'] = 0;
                    $this->request->data['Role']['view_contact'] = 0;
                    $this->request->data['Role']['edit_contact'] = 0;
                    $this->request->data['Role']['edit_leads'] = 0;
                    $this->request->data['Role']['view_leads'] = 0;
                }
                $data = array('Role' => $this->request->data['Role']);
                if ($this->Role->save($data)) {
                    $last_insert_id = $this->Role->getLastInsertID();

                    if (isset($this->request->data['RoleSource']) and ! empty($this->request->data['RoleSource'])) {
                        foreach ($this->request->data['RoleSource'] as $key => $value) {

                            $this->RoleSource->create();
                            $this->request->data['RoleSource'] = $value;
                            if (!isset($this->request->data['RoleSource']['source_status'])) {
                                $this->request->data['RoleSource']['source_status'] = 0;
                            } else {
                                $this->request->data['RoleSource']['source_status'] = 1;
                                $this->request->data['RoleSource']['role_id'] = $last_insert_id;
                                $this->RoleSource->save($this->request->data);
                            }
                        }
                    }
                    $response['status'] = 'true';
                    $response['message'] = 'Role added successfully.';
                    $response['last_insert_id'] = $last_insert_id;
                } else {
                    $errors = $this->Role->validationErrors;
                    $err = array();
                    $response['error'] = $errors;
                    if (!empty($this->request->data['RoleSource'])) {
                        foreach ($this->request->data['RoleSource'] as $key => $value) {
                            if (empty($value['source'])) {
                                $err[$key]['source'] = "Please enter the source.";
                            }
                        }
                        $response['errors'] = $err;
                    }
                    $response['status'] = 'false';
                }
            }

            echo json_encode($response);
            die;
        } else {
            $this->layout = "ajax";
        }
    }

    /* Add More url */

    public function company_addUrl() {
        $this->layout = "ajax";
        if (isset($_POST['child'])) {
            $child = $_POST['child'] + 1;
            $this->set('child', $child);
        }
    }

    public function company_active_source($id = Null) {
        $this->layout = "ajax";
        $this->loadModel('RoleSource');
        $allrole = $this->RoleSource->find('all', array('conditions' => array('RoleSource.role_id' => $id)));
        $this->set('role_source', $allrole);
    }

    /* AutoComplete for Role reports  */

    public function company_all_profiles_json() {
        $this->autoRender = false;
        $profile_pending = $this->Role->find('list', array(
            'fields' => array('name'),
            'conditions' => array(
                'Role.comp_id' => $this->Auth->user('id'),
        )));
        return json_encode($profile_pending);
    }

    /* check availablity */

    public function company_check_availablity() {
        $this->autoRender = FALSE;
        $this->loadModel('RoleSource');
        $findAvailability = $this->RoleSource->find('first', array(
            'conditions' => array(
                'RoleSource.source' => $_POST['text']
            )
        ));
        if (!empty($findAvailability)) {
            $response['status'] = 'false';
        } else {
            $response['status'] = 'true';
        }
        echo json_encode($response);
        die;
    }

    /* Show Role Sources */

    public function company_show_sources($last_insert_id = NULL) {
        $this->layout = 'ajax';
        $this->loadModel('RoleSource');
        $RoleSource = $this->RoleSource->find('all', array(
            'fields' => array('id', 'source'),
            'conditions' => array('RoleSource.role_id' => $last_insert_id)));
        $this->set('RoleSource', $RoleSource);
    }

    /* Assign Role */

    public function company_assign_role($assign_role = null) {
        $this->loadModel('EmployeeDetail');
        $this->loadModel('RoleAssignment');
        $emp = $this->EmployeeDetail->find('all', array(
            'fields' => array('id', 'emp_first_name', 'emp_last_name'),
            'conditions' => array('EmployeeDetail.comp_id' => $this->Auth->user('id'))));
        $allRoles = $this->Role->generateTreeList(
                array('comp_id' => $_SESSION['Auth']['User']['id']), null, null, '&nbsp;&nbsp;'
        );
        $currentRole = $this->Role->findById($assign_role);
        $this->set('allRoles', $allRoles);
        $this->set('currentRole', $currentRole);
        $this->set('employee', $emp);
        if (!empty($_POST)) {
            if (empty($_POST['users'])) {
                echo json_encode(array('status' => 'false', 'message' => 'Please select users.'));
            } else {
                $this->request->data['RoleAssignment'] = $_POST;
                $this->request->data['RoleAssignment']['emp_id'] = $_POST['users'];
                $this->request->data['RoleAssignment']['comp_id'] = $this->Auth->user('id');
                $this->RoleAssignment->save($this->request->data);
                echo json_encode(array('status' => 'true', 'message' => 'Role assigned to selected user(s).'));
            }

            die;
        } else {
            $this->layout = "ajax";
        }
    }

    /* Delete Role */

    public function company_delete($id = NULL) {
        $this->autoRender = FALSE;
        $delete = $this->Role->delete($id);
        if ($delete) {
            echo json_encode(array('status' => true, 'message' => 'Role deleted successfully.'));
            die;
        }
    }

    /* Update Role Source */

    public function company_updateRoleSource() {
        $this->loadModel('RoleSource');
        $this->autoRender = FALSE;
        $this->RoleSource->id = $_POST['id'];
        $this->RoleSource->save($_POST, array('validate' => FALSE));
        echo json_encode(array('status' => 'true'));
        die;
    }

    /* Show lists */

    public function company_changeUsersList() {
        $this->layout = "ajax";
        $this->loadModel('EmployeeDetail');
        $this->loadModel('RoleAssignment');
        $conditions['AND']['EmployeeDetail.comp_id'] = $this->Auth->user('id');
        if ($_POST['userlist']['search']) {
            $conditions['OR']['EmployeeDetail.emp_first_name LIKE'] = $_POST['userlist']['search'] . '%';
        }
        if ($_POST['userlist']['usersList'] == 'unassign_role') {
            $allAssigned = $this->RoleAssignment->find('list', array(
                'fields' => array('emp_id'),
                'conditions' => array('RoleAssignment.comp_id' => $this->Auth->user('id'))
            ));
            $conditions['AND']['EmployeeDetail.id !='] = $allAssigned;
            $query = $this->EmployeeDetail->find('all', array(
                'conditions' => $conditions));
        } else {
            $query = $this->EmployeeDetail->find('all', array('conditions' => $conditions));
        }
        $this->set('Employee', $query);
    }

}

?>
