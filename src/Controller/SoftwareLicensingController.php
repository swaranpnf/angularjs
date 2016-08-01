<?php

class SoftwareLicensingController extends AppController {

    var $name = "SoftwareLicensing";
    var $uses = array('User');

    /* Main Page */

    public function beforeFilter() {
        parent::beforeFilter();
        /*   if (!isset($_COOKIE['testingTimeOut'])) {
          $this->redirect('/login/logout');
          } */
    }

    public function company_index() {
        if (isset($_POST['test']) and $_POST['test'] == 'true') {
            $this->layout = "ajax";
        } else {
            $this->layout = "company";
        }
        $license = $this->User->findById($this->Auth->user('id'));
        $this->set('license', $license);
        //get the software licenses fees set by superadmin
        $comp_license_id = $license['User']['license_id'];
        $this->loadModel('LicenseApplicableFee');
        $this->LicenseApplicableFee->recursive = 1;
        $getThefeesAre = $this->LicenseApplicableFee->find('all', array('order' => array('LicenseApplicableFee.default_fee_id' => 'ASC'), 'conditions' => array('LicenseApplicableFee.license_id' => $comp_license_id, 'LicenseApplicableFee.default_fee_id' => array(2, 3, 4, 7, 9, 10))));
        if ($getThefeesAre) {
            $this->set('getAllFees', $getThefeesAre);
        }
    }

    public function company_json() {
        $this->layout = "ajax";
        $this->loadModel('LicenseApplicableFee');
        $this->loadModel('EmployeeDetail');
        $this->loadModel('EmployeeLicenseSeat');
        $this->LicenseApplicableFee->recursive = 1;
        $this->EmployeeDetail->recursive = -1;
        $license = $this->User->findById($this->Auth->user('id'));
        $getAllActiveEmployee = $this->EmployeeDetail->find('all', array(
            'fields' => array('EmployeeDetail.id'),
            'conditions' => array(
                'EmployeeDetail.comp_id' => $this->Auth->user('id'),
                'EmployeeDetail.paid_status' => 1
            )
        ));
        $getTotalLicense = $this->EmployeeDetail->find('all', array(
            'fields' => array('sum(EmployeeDetail.number_of_licenses) as total_sum'),
            'conditions' => array('EmployeeDetail.comp_id' => $this->Auth->user('id'))
        ));
        $getTotalLicenseByComp = $this->EmployeeLicenseSeat->find('first', array(
            'fields' => array('EmployeeLicenseSeat.number_of_licenses'),
            'conditions' => array('EmployeeLicenseSeat.comp_id' => $this->Auth->user('id'))
        ));
        $allCount = 0;
        $allCountByComp = 0;
        if (!empty($getTotalLicenseByComp)) {
            $allCountByComp = $getTotalLicenseByComp['EmployeeLicenseSeat']['number_of_licenses'];
        }
        if (isset($getTotalLicense[0][0]['total_sum'])) {
            $allCount = $getTotalLicense[0][0]['total_sum'] + $allCountByComp;
        }
        $allEmployeeCount = count($getAllActiveEmployee);
        /* get the software licenses fees set by superadmin */
        $comp_license_id = $license['User']['license_id'];
        $getThefeesAre = $this->LicenseApplicableFee->find('all', array('order' => array('LicenseApplicableFee.default_fee_id' => 'ASC'), 'conditions' => array('LicenseApplicableFee.license_id' => $comp_license_id, 'LicenseApplicableFee.default_fee_id' => array(2, 3, 4, 7, 9, 10))));
        if ($getThefeesAre) {
            $this->set('getAllFees', $getThefeesAre);
        }
        $this->set('license', $license);
        $this->set('allCount', $allCount);
        $this->set('allEmployeeCount', $allEmployeeCount);
        $this->render('company_index');
    }

    /* function for State Licensing(including Show Branch and State licensing) */

    public function company_state_licensing() {
        $this->layout = 'ajax';
        $this->loadModel('Branch');
        $this->loadModel('EmployeeLicense');
        $this->loadModel('EmployeeDetail');
        $this->loadModel('StateLicense');
        $this->loadModel('LicenseDetail');
        $nmls = $this->LicenseDetail->find('first', array(
            'fields' => array('nmls'), 'conditions' => array('LicenseDetail.id' => $this->Auth->user('license_id'))));

        $stateLicense = $this->StateLicense->find('all', array(
            'conditions' => array(
                'StateLicense.comp_id' => $this->Auth->user('id'),
                'StateLicense.license !=' => '',
                'StateLicense.active_status !=' => 0,
        )));
        if (!empty($stateLicense)) {
            foreach ($stateLicense as $key => $value) {
                $EmployeeLicense = $this->EmployeeLicense->find('count', array(
                    'fields' => array('EmployeeLicense.id'),
                    'group' => array('EmployeeLicense.emp_id'),
                    'conditions' => array(
                        'EmployeeLicense.state' => $value['StateLicense']['state_id'],
                        'EmployeeLicense.license_status' => 'Active',
                        'EmployeeLicense.comp_id' => $this->Auth->user('id'))
                ));
                $stateLicense[$key]['StateLicense']['count'] = $EmployeeLicense;
            }
        }
        $branches = $this->Branch->find('all', array(
            'order' => array('Branch.id' => 'desc'),
            'conditions' => array('Branch.comp_id' => $this->Auth->user('id'))
        ));
        if (!empty($branches)) {
            foreach ($branches as $key => $value) {
                $this->EmployeeDetail->recursive = -1;
                $emp = $this->EmployeeDetail->find('first', array(
                    'fields' => array('EmployeeDetail.emp_first_name', 'EmployeeDetail.emp_last_name', 'EmployeeDetail.emp_image'),
                    'conditions' => array('EmployeeDetail.id' => $value['Branch']['manager'])));
                $branches[$key]['Branch']['user_name'] = $emp['EmployeeDetail']['emp_first_name'] . ' ' . $emp['EmployeeDetail']['emp_last_name'];
                $branches[$key]['Branch']['emp_image'] = $emp['EmployeeDetail']['emp_image'];
            }
        }
        $this->set('branch', $branches);
        $this->set('stateLicense', $stateLicense);
        $this->set('nmls', $nmls);
    }

    public function company_getAllBranches() {
        $this->layout = 'ajax';
        $this->loadModel('Branch');
        $this->loadModel('EmployeeLicense');
        $this->loadModel('EmployeeDetail');
        $this->loadModel('StateLicense');
        $this->loadModel('LicenseDetail');
        $nmls = $this->LicenseDetail->find('first', array(
            'fields' => array('nmls'), 'conditions' => array('LicenseDetail.id' => $this->Auth->user('license_id'))));

        $stateLicense = $this->StateLicense->find('all', array(
            'conditions' => array(
                'StateLicense.comp_id' => $this->Auth->user('id'),
                'StateLicense.license !=' => '',
                'StateLicense.active_status !=' => 0,
        )));
        if (!empty($stateLicense)) {
            foreach ($stateLicense as $key => $value) {
                $EmployeeLicense = $this->EmployeeLicense->find('all', array(
                    'fields' => array('EmployeeLicense.id'),
                    'conditions' => array(
                        'EmployeeLicense.state' => $value['StateLicense']['state_id'],
                        'EmployeeLicense.license_status' => 'Active',
                        'EmployeeLicense.comp_id' => $this->Auth->user('id'))
                ));
                $stateLicense[$key]['StateLicense']['count'] = count($EmployeeLicense);
            }
        }
        $branches = $this->Branch->find('all', array(
            'order' => array('Branch.id' => 'desc'),
            'conditions' => array('Branch.comp_id' => $this->Auth->user('id'))
        ));
        if (!empty($branches)) {
            foreach ($branches as $key => $value) {
                $this->EmployeeDetail->recursive = -1;
                $emp = $this->EmployeeDetail->find('first', array(
                    'fields' => array('EmployeeDetail.emp_first_name', 'EmployeeDetail.emp_last_name', 'EmployeeDetail.emp_image'),
                    'conditions' => array('EmployeeDetail.id' => $value['Branch']['manager'])));
                $branches[$key]['Branch']['user_name'] = $emp['EmployeeDetail']['emp_first_name'] . ' ' . $emp['EmployeeDetail']['emp_last_name'];
                $branches[$key]['Branch']['emp_image'] = $emp['EmployeeDetail']['emp_image'];
            }
        }
        $this->set('branch', $branches);
        $this->set('stateLicense', $stateLicense);
        $this->set('nmls', $nmls);
    }

    /* function for add Branch or Update Branch */

    public function company_add_branch($id = Null) {
        $this->loadModel('State');
        $this->loadModel('Branch');
        $this->loadModel('EmployeeDetail');
        $state = $this->State->find('all', array('State.id', 'State.state'));
        $this->set('state', $state);
        $EmployeeDetail = $this->EmployeeDetail->find('all', array(
            'fields' => array('EmployeeDetail.id', 'EmployeeDetail.emp_first_name', 'EmployeeDetail.emp_last_name'),
            'conditions' => array(
                'EmployeeDetail.license_status' => 'yes',
                'EmployeeDetail.comp_id' => $this->Auth->user('id')
            )
        ));
        $this->set('employee', $EmployeeDetail);
        if (isset($id) and ! empty($id)) {
            $branchEdit = $this->Branch->findById($id);
            $this->set('branchEdit', $branchEdit);
            $this->Branch->id = $id;
        }
        if (!empty($_POST)) {
            $this->layout = '';
            $this->request->data['Branch']['comp_id'] = $this->Auth->user('id');
            if ($this->Branch->save($this->request->data)) {
                $response['message'] = "Branch added successfully.";
                $response['status'] = 'true';
            } else {
                $errors = $this->Branch->validationErrors;
                $response['error'] = $errors;
                $response['status'] = 'false';
            }
            echo json_encode($response);
            die;
        } else {
            $this->layout = 'ajax';
        }
    }

    /* function for Delete Branch */

    public function company_delete_branch($id = Null) {
        $this->loadModel('Branch');
        $this->autoRender = FALSE;
        $delete = $this->Branch->delete($id);
        echo json_encode(array('status' => true));
        die;
    }

    /* Update State licensing Pop-up */

    public function company_update_state_licensing() {
        $this->layout = 'ajax';
        $this->loadModel('State');
        $this->loadModel('StateLicense');
        $this->loadModel('EmployeeDetail');
        $states = $this->State->find('all', array(
            'fields' => array('id', 'state'),
            'conditions' => array(
                'State.status' => 1,
        )));
        $getLicense = $this->StateLicense->find('all', array(
            'order' => array('StateLicense.id' => 'asc'), 'conditions' => array('StateLicense.comp_id' => $this->Auth->user('id'))));
        $this->set('licenseNumbers', $getLicense);
        foreach ($states as $k => $v) {
            $statesNew[$k]['State'] = $v['State'];
            $statesNew[$k]['State']['license'] = "";
            $statesNew[$k]['State']['active_status'] = "";
            foreach ($getLicense as $k_license => $v_license) {
                if ($v['State']['id'] == $v_license['StateLicense']['state_id']) {
                    $statesNew[$k]['State']['license'] = $v_license['StateLicense']['license'];
                    $statesNew[$k]['State']['active_status'] = $v_license['StateLicense']['active_status'];
                }
            }
        }
        $this->set('states', $statesNew);
        $status = 0;
        if (!empty($_POST)) {
            $this->StateLicense->deleteAll(array('StateLicense.comp_id' => $this->Auth->user('id')), false);
            foreach ($this->request->data as $key => $value) {
                if (!empty($value['StateLicense']['active_status']) and empty($value['StateLicense']['license'])) {
                    $status = 1;
                    $err[$key]['license'] = 'Please enter the license';
                }
            }
            if ($status != 1) {
                foreach ($this->request->data as $key => $value) {
                    $this->StateLicense->create();
                    if (isset($value['StateLicense']['active_status']) and $value['StateLicense']['active_status'] == 'on') {
                        $value['StateLicense']['active_status'] = 1;
                    } else {
                        $value['StateLicense']['active_status'] = 0;
                    }
                    if (isset($value['StateLicense']['license_id']) and ! empty($value['StateLicense']['license_id'])) {
                        $this->StateLicense->id = $value['StateLicense']['license_id'];
                    }
                    $formData = $value;
                    $this->StateLicense->save($formData);
                }
                echo json_encode(array('status' => 'true', 'message' => 'State licensing updated.'));
                die;
            } else {
                echo json_encode(array('status' => 'false', 'error' => $err, 'modelname' => 'StateLicense'));
                die;
            }
        }
    }

    /* Show Users/Employee according to the State */

    public function company_show_state_license($state_id = NULL) {
        $this->layout = 'ajax';
        if (!empty($state_id)) {
            $this->loadModel('StateLicense');
            $this->loadModel('EmployeeLicense');
            $this->loadModel('EmployeeDetail');
            $stateLicense = $this->StateLicense->find('first', array(
                'conditions' => array(
                    'StateLicense.state_id' => $state_id,
                    'StateLicense.comp_id' => $this->Auth->user('id'),
            )));
            $employeeLicense = $this->EmployeeLicense->find('list', array(
                'fields' => array('EmployeeLicense.emp_id'),
                'conditions' => array('EmployeeLicense.state' => $state_id)));
            $getEmployeeAccToState = $this->EmployeeDetail->find('all', array(
                'fields' => array('id', 'state', 'emp_first_name', 'emp_last_name', 'emp_image'),
                'recursive' => -1,
                'conditions' => array(
                    'EmployeeDetail.id' => $employeeLicense,
                )
            ));
            if (!empty($getEmployeeAccToState)) {
                foreach ($getEmployeeAccToState as $key => $value) {
                    $license = $this->EmployeeLicense->find('first', array(
                        'conditions' => array(
                            'EmployeeLicense.emp_id' => $value['EmployeeDetail']['id']
                        )
                            )
                    );
                    $getEmployeeAccToState[$key]['EmployeeDetail']['license_number_id'] = "";
                    if (!empty($license)) {
                        $getEmployeeAccToState[$key]['EmployeeDetail']['license_number_id'] = $license['EmployeeLicense']['license'];
                    }
                }
            }
            $this->set('EmployeeDetail', $getEmployeeAccToState);
            $this->set('stateLicense', $stateLicense);
        }
    }

    /* View selected Branch */

    public function company_view_branch($emp_id = Null) {
        $this->layout = 'ajax';
        $this->loadModel('EmployeeDetail');
        $employee = $this->EmployeeDetail->find('first', array('conditions' => array('EmployeeDetail.id' => $emp_id)));
        $this->set('branchDetail', $employee);
    }

    /* Search Employee By State */

    public function company_employeeByState($state = Null) {
        $this->layout = 'ajax';
        $this->loadModel('EmployeeDetail');
        $this->loadModel('EmployeeLicense');
        $this->EmployeeDetail->recursive = -1;
        $select = $this->EmployeeLicense->find('list', array(
            'fields' => array('EmployeeLicense.emp_id'),
            'conditions' => array(
                'EmployeeLicense.comp_id' => $this->Auth->user('id'),
                'EmployeeLicense.state' => $state
        )));
        $StateEmp = $this->EmployeeDetail->find('all', array(
            'fields' => array('EmployeeDetail.id', 'EmployeeDetail.emp_first_name', 'EmployeeDetail.emp_last_name'),
            'conditions' => array('EmployeeDetail.id' => $select)));
        $this->set('employee', $StateEmp);
    }

    /* All active states */

    public function company_getAllActiveState() {
        $this->layout = 'ajax';
        $this->loadModel('EmployeeDetail');
        $this->loadModel('StateLicense');
        $this->loadModel('EmployeeLicense');
        $stateLicense = $this->StateLicense->find('all', array(
            'conditions' => array(
                'StateLicense.comp_id' => $this->Auth->user('id'),
                'StateLicense.license !=' => '',
                'StateLicense.active_status !=' => 0,
        )));
        if (!empty($stateLicense)) {
            foreach ($stateLicense as $key => $value) {
                $EmployeeLicense = $this->EmployeeLicense->find('all', array(
                    'fields' => array('EmployeeLicense.id'),
                    'group' => array('EmployeeLicense.emp_id'),
                    'conditions' => array(
                        'EmployeeLicense.state' => $value['StateLicense']['state_id'],
                        'EmployeeLicense.license_status' => 'Active',
                        'EmployeeLicense.comp_id' => $this->Auth->user('id')
                    )
                ));
                $stateLicense[$key]['StateLicense']['count'] = count($EmployeeLicense);
            }
        }
        $this->set('stateLicense', $stateLicense);
    }

    public function company_updateNmlsId($id = Null) {
        $this->autoRender = FALSE;
        $this->loadModel('User');
        if (!empty($_POST['nmls'])) {
            $this->request->data['User']['nmls_id'] = $_POST['nmls'];
            $this->User->id=$id;
            $this->User->save($this->request->data);
        }
    }

}

?>
