<?php

class EmployeeController extends AppController {

    public function company_index() {
        $this->layout = 'ajax';
        $this->loadModel('EmployeeDetail');
        $allEmployee = $this->EmployeeDetail->find('all', array('conditions' => array('EmployeeDetail.comp_id' => $this->Auth->user('id'))));
        $this->set('allEmployee', $allEmployee);
    }

    public function company_add($employee_id = Null) {
        $this->layout = 'ajax';
        if (!empty($employee_id)) {
            $this->set('employee_id', $employee_id);
        }
    }

    public function company_personel_info($employee_id = NULL) {
        $this->loadModel('EmployeeDetail');
        $this->loadModel('EmployeeChildren');
        $this->loadModel('EmployeeCertification');
        $this->loadModel('EmployeeHiring');
        $this->loadModel('EmployeeLicense');
        $this->loadModel('EmployeeLender');
        $this->loadModel('EmployeeCredit');
        $this->loadModel('EmployeeAssignment');
        $this->loadModel('EmployeeOnboarding');
        $this->loadModel('State');
        $this->loadModel('Role');
        if (!empty($employee_id)) {
            $empData = $this->EmployeeDetail->find('first', array('conditions' => array('EmployeeDetail.id' => $employee_id)));
            $this->set('employeeDetail', $empData);
            $this->request->data['EmployeeDetail']['id'] = $employee_id;
        }
        $state = $this->State->find('list', array('fields' => array('id', 'state'), 'conditions' => array('State.status' => 1)));
        $this->set('state', $state);
        $allRoles = $this->Role->find('all', array(
            'fields' => array('id', 'name'),
            'conditions' => array('Role.comp_id' => $this->Auth->user('id'))));
        $this->set('allRoles', $allRoles);
        $this->layout = 'ajax';
        if (!empty($_POST['data'])) {
            $err = array();
            $this->layout = '';
            $this->request->data['EmployeeDetail']['comp_id'] = $this->Auth->user('id');
            if (isset($this->request->data['EmployeeDetail']['emp_image']['name']) and ! empty($this->request->data['EmployeeDetail']['emp_image']['name'])) {
                $image = time() . '_' . $this->request->data['EmployeeDetail']['emp_image']['name'];
                move_uploaded_file($this->request->data['EmployeeDetail']['emp_image']['tmp_name'], 'upload/Employee/' . $image);
                $this->request->data['EmployeeDetail']['emp_image'] = $image;
            }
            if (isset($this->request->data['EmployeeDetail']['spouse_image']['name']) and ! empty($this->request->data['EmployeeDetail']['spouse_image']['name'])) {
                $spimage = time() . '_' . $this->request->data['EmployeeDetail']['spouse_image']['name'];
                move_uploaded_file($this->request->data['EmployeeDetail']['spouse_image']['tmp_name'], 'upload/Employee/' . $spimage);
                $this->request->data['EmployeeDetail']['spouse_image'] = $spimage;
            }
            if (isset($this->request->data['EmployeeDetail']['emp_age'])) {
                $this->request->data['EmployeeDetail']['emp_age'] = date('Y', time()) - date('Y', strtotime(str_replace('/', '-', $this->request->data['EmployeeDetail']['emp_birthday'])));
                $this->request->data['EmployeeDetail']['spouse_age'] = date('Y', time()) - date('Y', strtotime(str_replace('/', '-', $this->request->data['EmployeeDetail']['spouse_birthday'])));
            }
            $this->request->data['EmployeeDetail']['comp_id'] = $this->Auth->user('id');
            if ($this->EmployeeDetail->save($this->request->data)) {
                if (!empty($employee_id)) {
                    $emp_id = $employee_id;
                } elseif ($this->EmployeeDetail->getLastInsertID()) {
                    $emp_id = $this->EmployeeDetail->getLastInsertID();
                } else {

                    $emp_id = $this->request->data['EmployeeDetail']['id'];
                }
                $childStatues = 0;
                if (!empty($this->request->data['EmployeeChildren'])) {
                    foreach ($this->request->data['EmployeeChildren'] as $key => $value) {
                        if (empty($value['first_name'])) {
                            $childStatues = 1;
                            $err[$key]['first_name'] = "Please enter first name";
                        }
                        if (empty($value['last_name'])) {
                            $childStatues = 1;
                            $err[$key]['last_name'] = "Please enter last name";
                        }
                    }

                    if ($childStatues == 1) {
                        $response['status'] = 'error';
                        $response['errorChild']['EmployeeChildren'] = $err;
                        $response['modelname'] = 'EmployeeChildren';
                        echo json_encode($response);
                        die;
                    } else {
                        foreach ($this->request->data['EmployeeChildren'] as $key => $value) {
                            $this->EmployeeChildren->create();
                            $this->request->data['EmployeeChildren'] = $value;
                            $this->request->data['EmployeeChildren']['emp_id'] = $emp_id;
                            $this->request->data['EmployeeChildren']['age'] = date('Y', time()) - date('Y', strtotime(str_replace('/', '-', $this->request->data['EmployeeChildren']['birthday'])));
                            $this->EmployeeChildren->save($this->request->data);
                        }
                    }
                }
                if (!empty($this->request->data['EmployeeCertification'])) {
                    $i = 0;
                    foreach ($this->request->data['EmployeeCertification'] as $key => $value) {
                        if (empty($value['name'])) {
                            $err[$i]['name'] = "Please enter the name.";
                        }
                        if (empty($value['acronym'])) {
                            $err[$i]['acronym'] = "Please enter the name.";
                        }
                        if (empty($value['since'])) {
                            $err[$i]['since'] = "Please select the date.";
                        }
                        $i++;
                    }
                    $response['errorChild']['EmployeeCertification'] = $err;
                }
                if (!empty($this->request->data['EmployeeAssignment'])) {
                    $this->request->data['EmployeeAssignment']['emp_id'] = $emp_id;
                    $this->request->data['EmployeeAssignment']['comp_id'] = $this->Auth->user('id');
                    if ($this->EmployeeAssignment->save($this->request->data, array('validate' => true))) {
                        
                    } else {
                        $error = $this->EmployeeAssignment->validationErrors;
                        $response['status'] = 'error';
                        $response['error'] = $error;
                        echo json_encode($response);
                        die;
                    }
                }
                unset($this->request->data['EmployeeDetail']);
                $EmployeeLicenseModel="";
                foreach($this->request->data as $keySet=>$valSet){
                    if(isset($valSet['EmployeeLicense'])){
                       $EmployeeLicenseModel="EmployeeLicense"; 
                    }
                    if(isset($valSet['EmployeeLicense'])){
                       $EmployeeLicenseModel="EmployeeLicense"; 
                    }
                }
                echo $test;
                die;
                
                if (isset($this->request->data[0]['EmployeeLicense']) or isset($this->request->data[0]['EmployeeLender']) or isset($this->request->data[0]['EmployeeHiring']) or isset($this->request->data[0]['EmployeeOnboarding']) or isset($this->request->data[0]['EmployeeCredit']) or isset($this->request->data['EmployeeCertification'])) {
                    $err = array();
                    $empLicenseStatus = 0;
                    $empLenderStatus = 0;
                    $empCreditStatus = 0;
                    $empHiringStatus = 0;
                    $empCertiStatus = 0;
                    $empOnboardingStatus = 0;
                    $inc = 0;
                    /*  foreach ($this->request->data as $keys => $values) {
                      if (isset($values['EmployeeOnboarding']) and ! empty($values['EmployeeHiring'])) {
                      if (isset($values['EmployeeOnboarding']['completed_status']) and empty($values['EmployeeOnboarding']['selected_date'])) {
                      $empOnboardingStatus = 1;
                      $err[$inc]['selected_date'] = $keys;
                      $inc++;
                      }
                      }
                      if (isset($values['EmployeeCredit']) and ! empty($values['EmployeeCredit'])) {
                      if (empty($values['EmployeeCredit']['credit_status'])) {
                      $empCreditStatus = 1;
                      $err[$keys]['credit_status'] = "Please select the Status.";
                      }
                      if (empty($values['EmployeeCredit']['username'])) {
                      $empCreditStatus = 1;
                      $err[$keys]['username'] = "Please enter the Username.";
                      }
                      if (empty($values['EmployeeCredit']['password'])) {
                      $empCreditStatus = 1;
                      $err[$keys]['password'] = "Please enter the password.";
                      }
                      }
                      } */
                    if (isset($this->request->data[0]['EmployeeLicense']) && !empty($this->request->data[0]['EmployeeLicense']) && !empty($this->request->data[0]['EmployeeLicense'])) {
                        unset($this->request->data['EmployeeDetail']);
                        foreach ($this->request->data as $keys => $value) {

                            if (empty($value['EmployeeLicense']['state'])) {
                                $empLicenseStatus = 1;
                                $err[$keys]['state'] = "Please select the State.";
                            }
                            if (empty($value['EmployeeLicense']['license'])) {
                                $empLicenseStatus = 1;
                                $err[$keys]['license'] = "Please enter the license.";
                            }
                            if (empty($value['EmployeeLicense']['license_status'])) {
                                $empLicenseStatus = 1;
                                $err[$keys]['license_status'] = "Please  select the status.";
                            }
                            if (empty($value['EmployeeLicense']['through'])) {
                                $empLicenseStatus = 1;
                                $err[$keys]['through'] = "Please enter the through.";
                            }
                        }
                    }
                    if (isset($this->request->data[0]['EmployeeLender']) and ! empty($this->request->data[0]['EmployeeLender'])) {
                        unset($this->request->data['EmployeeDetail']);
                        foreach ($this->request->data as $kk => $values) {
                            if (empty($values['EmployeeLender']['lender'])) {
                                $empLenderStatus = 1;
                                $err[$kk]['lender'] = "Please select the Lender.";
                            }
                            if (empty($values['EmployeeLender']['username'])) {
                                $empLenderStatus = 1;
                                $err[$kk]['username'] = "Please Enter the Username.";
                            }
                            if (empty($values['EmployeeLender']['password'])) {
                                $empLenderStatus = 1;
                                $err[$kk]['password'] = "Please Enter the Password.";
                            }
                        }
                    }
                    if (isset($this->request->data[0]['EmployeeCredit']) and ! empty($this->request->data[0]['EmployeeCredit'])) {
                        unset($this->request->data['EmployeeDetail']);
                        foreach ($this->request->data as $kk => $vv) {
                            if (empty($vv['EmployeeCredit']['credit_bureas'])) {
                                $empCreditStatus = 1;
                                $err[$kk]['credit_bureas'] = $kk;
                            }
                            if (empty($vv['EmployeeCredit']['username'])) {
                                $empCreditStatus = 1;
                                $err[$kk]['username'] = $kk;
                            }
                            if (empty($vv['EmployeeCredit']['password'])) {
                                $empCreditStatus = 1;
                                $err[$kk]['password'] = $kk;
                            }
                            $inc++;
                        }
                    }

                    if (isset($this->request->data[0]['EmployeeHiring']) and ! empty($this->request->data[0]['EmployeeHiring'])) {
                        unset($this->request->data['EmployeeDetail']);
                        foreach ($this->request->data as $kk => $vv) {

                            if (isset($vv['EmployeeHiring']['completed_status']) and empty($vv['EmployeeHiring']['selected_date'])) {
                                $empHiringStatus = 1;
                                $err[$inc]['selected_date'] = $kk;
                                $inc++;
                            }
                        }
                    }
                    if (isset($this->request->data[0]['EmployeeOnboarding']) and ! empty($this->request->data[0]['EmployeeOnboarding'])) {
                        unset($this->request->data['EmployeeDetail']);
                        foreach ($this->request->data as $kt => $vt) {
                            if (isset($vt['EmployeeOnboarding']['completed_status']) and empty($vt['EmployeeOnboarding']['selected_date'])) {
                                $empOnboardingStatus = 1;
                                $err[$inc]['selected_date'] = $kt;
                                $inc++;
                            }
                        }
                    }

                    if (isset($this->request->data['EmployeeCertification'])) {
                        $i = 0;
                        foreach ($this->request->data['EmployeeCertification'] as $keyy => $val) {
                            if (empty($val['name'])) {
                                $empCertiStatus = 1;
                                $err[$keyy]['name'] = "Please enter the name.";
                            }
                            if (empty($val['acronym'])) {
                                $empCertiStatus = 1;
                                $err[$keyy]['acronym'] = "Please enter the name.";
                            }
                            if (empty($val['since'])) {
                                $empCertiStatus = 1;
                                $err[$keyy]['since'] = "Please select the date.";
                            }
                            $i++;
                        }
                        $response['errorChild']['EmployeeCertification'] = $err;
                    }
                    if ($empCertiStatus == 1) {
                        $response['status'] = 'error';
                        $response['errorChild']['EmployeeCertification'] = $err;
                        $response['modelname'] = 'EmployeeCertification';
                        echo json_encode($response);
                        die;
                    } else {
                        if (isset($this->request->data['EmployeeCertification'])) {
                            foreach ($this->request->data['EmployeeCertification'] as $key => $value) {
                                $this->EmployeeCertification->create();
                                $this->request->data['EmployeeCertification'] = $value;
                                $this->request->data['EmployeeCertification']['emp_id'] = $this->request->data['EmployeeDetail']['id'];
                                $this->EmployeeCertification->save($this->request->data);
                            }
                        }
                    }
                    if (isset($this->request->data[0]['EmployeeLicense'])) {
                        if ($empLicenseStatus == 1) {
                            $response['status'] = 'error';
                            $response['errorChild'] = $err;
                            $response['modelname'] = 'EmployeeLicense';
                            echo json_encode($response);
                            die;
                        } else {
                            unset($this->request->data['EmployeeDetail']);
                            foreach ($this->request->data as $keys => $values) {

                                if ($keys == 0) {
                                    $delete = $this->EmployeeLicense->deleteAll(array('EmployeeLicense.emp_id' => $emp_id), false);
                                }
                                $this->EmployeeLicense->create();
                                if (!isset($values['EmployeeLicense']['approve'])) {
                                    $this->request->data[$keys]['EmployeeLicense']['approve'] = 0;
                                } else {
                                    $this->request->data[$keys]['EmployeeLicense']['approve'] = 1;
                                }
                                $this->request->data[$keys]['EmployeeLicense']['emp_id'] = @$emp_id;
                                $this->request->data[$keys]['EmployeeLicense']['comp_id'] = $this->Auth->user('id');
                                $newdata = array('EmployeeLicense' => $this->request->data[$keys]['EmployeeLicense']);
                                $this->EmployeeLicense->save($newdata);
                            }
                        }
                    } if ($empHiringStatus == 1) {
                        $response['status'] = 'error';
                        $response['errorChild'] = $err;
                        $response['modelname'] = 'EmployeeHiring';
                        echo json_encode($response);
                        die;
                    } else {
                        if (isset($this->request->data[0]['EmployeeHiring']) and ! empty($this->request->data[0]['EmployeeHiring'])) {
                            unset($this->request->data['EmployeeDetail']);
                            foreach ($this->request->data as $kt => $vt) {
                                if ($kt == 0) {
                                    $delete = $this->EmployeeHiring->deleteAll(array('EmployeeHiring.emp_id' => $emp_id), false);
                                }
                                $this->EmployeeHiring->create();
                                if (!isset($vt['EmployeeHiring']['completed_status'])) {
                                    $this->request->data[$kt]['EmployeeHiring']['completed_status'] = 0;
                                }
                                if (!isset($vt['EmployeeHiring']['eligible'])) {
                                    $this->request->data[$kt]['EmployeeHiring']['eligible'] = 0;
                                }
                                if (!isset($vt['EmployeeHiring']['exception'])) {
                                    $this->request->data[$kt]['EmployeeHiring']['exception'] = 0;
                                }
                                if (isset($vt['EmployeeHiring']['requirements'])) {
                                    $this->request->data[$kt]['EmployeeHiring']['requirements'] = implode(',', $vt['EmployeeHiring']['requirements']);
                                }
                                if (isset($vt['EmployeeHiring']['upload_file']['name']) and $vt['EmployeeHiring']['upload_file']['type'] == 'application/pdf') {
                                    $image = time() . '_' . $vt['EmployeeHiring']['upload_file']['name'];
                                    move_uploaded_file($vt['EmployeeHiring']['upload_file']['tmp_name'], 'upload/hiring_document/' . $image);
                                    $this->request->data[$kt]['EmployeeHiring']['upload_file'] = $image;
                                }
                                $this->request->data[$kt]['EmployeeHiring']['emp_id'] = $emp_id;
                                $this->request->data[$kt]['EmployeeHiring']['comp_id'] = $this->Auth->user('id');
                                $newdata = array('EmployeeHiring' => $this->request->data[$kt]['EmployeeHiring']);
                               $this->EmployeeHiring->save($newdata);
                            }
                        }
                    }
                    if ($empOnboardingStatus == 1) {
                        $response['status'] = 'error';
                        $response['errorChild'] = $err;
                        $response['modelname'] = 'EmployeeOnboarding';
                        echo json_encode($response);
                        die;
                    } else {
                        if (isset($this->request->data[0]['EmployeeOnboarding']) and ! empty($this->request->data[0]['EmployeeOnboarding'])) {
                            foreach ($this->request->data as $keys => $values) {
                                if ($keys == 0) {
                                    $delete = $this->EmployeeOnboarding->deleteAll(array('EmployeeOnboarding.emp_id' => $emp_id), false);
                                }
                                $this->EmployeeOnboarding->create();
                                if (!isset($values['EmployeeOnboarding']['completed_status'])) {
                                    $this->request->data[$keys]['EmployeeOnboarding']['completed_status'] = 0;
                                }

                                if (isset($values['EmployeeOnboarding']['upload_file']['name']) and ! empty($values['EmployeeOnboarding']['upload_file']['name']) and $values['EmployeeOnboarding']['upload_file']['type'] == 'application/pdf') {
                                    $image = time() . '_' . $values['EmployeeOnboarding']['upload_file']['name'];
                                    move_uploaded_file($values['EmployeeOnboarding']['upload_file']['tmp_name'], 'upload/hiring_document/' . $image);
                                    $this->request->data[$keys]['EmployeeOnboarding']['upload_file'] = $image;
                                }
                                $this->request->data[$keys]['EmployeeOnboarding']['emp_id'] = $emp_id;
                                $this->request->data[$keys]['EmployeeOnboarding']['comp_id'] = $this->Auth->user('id');
                                $newdata = array('EmployeeOnboarding' => $this->request->data[$keys]['EmployeeOnboarding']);
                                $this->EmployeeOnboarding->save($newdata);
                            }
                        }
                    }
                    if ($empLenderStatus == 1) {
                        $response['status'] = 'error';
                        $response['errorChild'] = $err;
                        $response['modelname'] = 'EmployeeLender';
                        echo json_encode($response);
                        die;
                    } else {

                        if (isset($this->request->data[0]['EmployeeLender']) and ! empty($this->request->data[0]['EmployeeLender'])) {
                            unset($this->request->data['EmployeeDetail']);
                            foreach ($this->request->data as $keys => $values) {
                                if ($keys == 0) {
                                    $delete = $this->EmployeeLender->deleteAll(array('EmployeeLender.emp_id' => $emp_id), false);
                                }
                                $this->EmployeeLender->create();

                                if (!isset($values['EmployeeLender']['lender_status'])) {
                                    $this->request->data[$keys]['EmployeeLender']['lender_status'] = 0;
                                }
                                $this->request->data[$keys]['EmployeeLender']['emp_id'] = $emp_id;
                                $this->request->data[$keys]['EmployeeLender']['comp_id'] = $this->Auth->user('id');
                                $this->request->data[$keys]['EmployeeLender']['password'] = $this->Auth->password($values['EmployeeLender']['password']);
                                $newdata = array('EmployeeLender' => $this->request->data[$keys]['EmployeeLender']);
                                $this->EmployeeLender->save($newdata);
                            }
                        }
                    }
                    if (isset($this->request->data[0]['EmployeeCredit'])) {
                        if ($empCreditStatus == 1) {
                            $response['status'] = 'error';
                            $response['errorChild'] = $err;
                            $response['modelname'] = 'EmployeeCredit';
                            echo json_encode($response);
                            die;
                        } else {
                            if (isset($this->request->data[0]['EmployeeCredit'])) {
                                unset($this->request->data['EmployeeDetail']);
                                foreach ($this->request->data as $keys => $values) {
                                    if ($keys == 0) {
                                        $delete = $this->EmployeeCredit->deleteAll(array('EmployeeCredit.emp_id' => $emp_id), false);
                                    }
                                    $this->EmployeeCredit->create();
                                    if (!isset($values['EmployeeCredit']['credit_status'])) {
                                        $this->request->data[$keys]['EmployeeCredit']['credit_status'] = 0;
                                    }
                                    $this->request->data[$keys]['EmployeeCredit']['emp_id'] = $emp_id;
                                    $this->request->data[$keys]['EmployeeCredit']['comp_id'] = $this->Auth->user('id');
                                    $this->request->data[$keys]['EmployeeCredit']['password'] = $this->Auth->password($values['EmployeeCredit']['password']);
                                    $newdata = array('EmployeeCredit' => $this->request->data[$keys]['EmployeeCredit']);
                                    $this->EmployeeCredit->save($newdata);
                                }
                            }
                        }
                    }
                }
                $response['status'] = 'true';
                $response['employee_id'] = $emp_id;
                $response['message'] = 'Employee added successfully.';
            } else {
                if (!empty($this->request->data['EmployeeChildren'])) {
                    foreach ($this->request->data['EmployeeChildren'] as $key => $value) {
                        if (empty($value['first_name'])) {
                            $err[$key]['first_name'] = "Please enter first name";
                        }
                        if (empty($value['last_name'])) {
                            $err[$key]['last_name'] = "Please enter last name";
                        }
                    }
                    $response['errorChild']['EmployeeChildren'] = $err;
                }
                if (!empty($this->request->data['EmployeeCertification'])) {
                    $i = 0;
                    foreach ($this->request->data['EmployeeCertification'] as $key => $value) {
                        if (empty($value['name'])) {
                            $err[$key]['name'] = "Please enter the name.";
                        }
                        if (empty($value['acronym'])) {
                            $err[$key]['acronym'] = "Please enter the name.";
                        }
                        if (empty($value['since'])) {
                            $err[$key]['since'] = "Please select the date.";
                        }
                        $i++;
                    }
                    $response['errorChild']['EmployeeCertification'] = $err;
                }

                if ($this->EmployeeDetail->validationErrors) {
                    $error = $this->EmployeeDetail->validationErrors;
                }
                $response['status'] = 'error';
                $response['error'] = $error;
            }
            echo json_encode($response);
            die;
        } else {
            if (isset($_POST['type'])) {
                $this->render('company_' . $_POST['type']);
            }
        }
    }

    public function company_contact_info() {
        $this->layout = 'ajax';
    }

    public function company_activity() {
        $this->layout = 'ajax';
    }

    public function company_addChild() {
        $this->layout = 'ajax';
        if (isset($_POST['child'])) {
            $this->set('child', $_POST['child']);
//            $this->set('child', $_POST['child'] + 1);
        } else {
            
        }
    }

    public function company_getAge() {
        $this->autoRender = FALSE;
        if (!empty($_POST['getDate'])) {
            $year = date('Y', strtotime($_POST['getDate']));
            $crnt_year = date('Y', time()) - $year;
            return $crnt_year;
        }
    }

    public function company_add_certificate() {
        $this->layout = 'ajax';
        if (isset($_POST['certificate'])) {
            $this->set('child', $_POST['certificate'] + 1);
        } else {
            
        }
    }

    /* Address->Hiring Decision */

    public function company_humanresourse_tab($employee_id = Null) {
        $this->layout = "ajax";
        $this->loadModel('EmployeeDetail');
        $this->loadModel('HiringRequirement');
        $qualification = $this->HiringRequirement->find('all', array(
            'fields' => array('id', 'name'),
            'order' => array('HiringRequirement.order' => 'asc'),
            'conditions' => array('HiringRequirement.status' => 1, 'HiringRequirement.comp_id' => $this->Auth->user('id'))));
        $this->set('qualification', $qualification);
        if (!empty($employee_id)) {
            $empData = $this->EmployeeDetail->find('first', array('conditions' => array('EmployeeDetail.id' => $employee_id)));
            $this->set('employeeDetail', $empData);
        }
    }

    public function company_generalinfo_tab($emp_id = Null) {
        $this->layout = "ajax";
        $this->loadModel('EmployeeDetail');
        if (!empty($emp_id)) {
            $empData = $this->EmployeeDetail->find('first', array(
                'conditions' => array('EmployeeDetail.id' => $emp_id)));
            $this->set('employeeDetail', $empData);
        }
        $this->render('company_personel_info');
    }

    public function company_onboarding($employee_id = Null) {
        $this->layout = "ajax";
        $this->loadModel('HiringRequirement');
        $this->loadModel('EmployeeDetail');
        $qualification = $this->HiringRequirement->find('all', array(
            'fields' => array('id', 'name', 'type'),
            'order' => array('HiringRequirement.order' => 'asc'),
            'conditions' => array(
                'HiringRequirement.status' => 1,
                'HiringRequirement.type !=' => 'qualification',
                'HiringRequirement.comp_id' => $this->Auth->user('id')
        )));
        $this->set('qualification', $qualification);
        if (!empty($employee_id)) {
            $empData = $this->EmployeeDetail->find('first', array('fields' => array('id'),
                'conditions' => array('EmployeeDetail.id' => $employee_id)));
            $this->set('employeeDetail', $empData);
        }
    }

    public function company_payroll($emp_id = Null) {
        $this->layout = 'ajax';
        $this->loadModel('EmployeeDetail');
        if (!empty($emp_id)) {
            $empData = $this->EmployeeDetail->find('first', array(
                'conditions' => array('EmployeeDetail.id' => $emp_id)));
            $this->set('employeeDetail', $empData);
        }
    }

    public function company_notes($emp_id = Null) {
        $this->layout = 'ajax';
        $this->loadModel('EmployeeNote');
        $EmployeeNote = $this->EmployeeNote->find('all');
        $this->set('EmployeeNote', $EmployeeNote);
        $this->loadModel('EmployeeDetail');
        if (!empty($emp_id)) {
            $empData = $this->EmployeeDetail->find('first', array(
                'conditions' => array('EmployeeDetail.id' => $emp_id)));
            $this->set('employeeDetail', $empData);
        }
    }

    public function company_compensation() {
        $this->layout = 'ajax';
    }

    public function company_tenture() {
        $this->layout = 'ajax';
    }

    public function company_termination($emp_id = Null) {
        $this->loadModel('EmployeeDetail');
        if (!empty($emp_id)) {
            $empData = $this->EmployeeDetail->find('first', array(
                'conditions' => array('EmployeeDetail.id' => $emp_id)));
            $this->set('employeeDetail', $empData);
        }
        if (!empty($_POST['date'])) {
            $this->autoRender = FALSE;
            $this->EmployeeDetail->id = $_POST['id'];
            $this->request->data['EmployeeDetail']['termination_date'] = $_POST['date'];
            $this->EmployeeDetail->save($this->request->data);
            echo json_encode(array('status' => 'true', 'message' => 'Termination date added.'));
        } else {
            $this->layout = 'ajax';
        }
    }

    public function company_new_notes($emp_id = Null) {
        $this->loadModel('EmployeeNote');
        $this->loadModel('EmployeeDetail');
        if (!empty($_POST)) {
            $this->request->data['EmployeeNote']['emp_id'] = @$this->request->data['EmployeeDetail']['id'];
            if ($this->EmployeeNote->save($this->request->data)) {
                $response['status'] = "true";
                $response['message'] = "Note added successfully.";
            } else {
                $error = $this->EmployeeNote->validationErrors;
                $response['status'] = 'false';
                $response['error'] = $error;
            }
            echo json_encode($response);
            die;
        } else {
            $this->layout = 'ajax';
        }
        if (!empty($emp_id)) {
            $empData = $this->EmployeeDetail->find('first', array(
                'conditions' => array('EmployeeDetail.id' => $emp_id)));
            $this->set('employeeDetail', $empData);
        }
    }

    public function company_qualification($type = NULL) {
        $this->layout = 'ajax';
        $this->loadModel('HiringRequirement');
        $qualification = $this->HiringRequirement->find('all', array(
            'fields' => array('id', 'name'),
            'order' => array('HiringRequirement.order' => 'asc'),
            'conditions' => array('HiringRequirement.status' => 1,
                'HiringRequirement.type' => $type,
                'HiringRequirement.comp_id' => $this->Auth->user('id'))));
        $this->set('qualification', $qualification);
        $this->set('type', $type);
    }

    public function company_add_qualification($type = Null) {
        $this->loadModel('HiringRequirement');
        if (!empty($_POST)) {
            $this->request->data['HiringRequirement']['status'] = 1;
            $this->request->data['HiringRequirement']['comp_id'] = $this->Auth->user('id');
            if ($this->HiringRequirement->save($this->request->data)) {
                $response['status'] = "true";
                $response['message'] = "Requirement added successfully.";
            } else {
                $error = $this->HiringRequirement->validationErrors;
                $response['status'] = 'false';
                $response['error'] = $error;
            }
            echo json_encode($response);
            die;
        } else {
            $this->layout = "ajax";
            $this->set('type', $type);
        }
    }

    public function company_change_order() {
        $this->autoRender = false;
        $this->loadModel('HiringRequirement');
        if ($_POST['list_order']) {
            $array = explode(',', $_POST['list_order']);
            foreach ($array as $key => $value) {
                $this->request->data['HiringRequirement']['order'] = $key;
                $this->HiringRequirement->id = $value;
                $this->HiringRequirement->save($this->request->data);
            }
        }
        echo json_encode(array('status' => 'true'));
        die;
    }

    public function company_deleteQualification() {
        $this->autoRender = false;
        $this->loadModel('HiringRequirement');
        $this->HiringRequirement->id = $_POST['id'];
        $this->request->data['HiringRequirement']['status'] = 0;
        if ($this->HiringRequirement->save($this->request->data)) {
            echo json_encode(array('status' => 'true', 'message' => 'Requirement removed from the list.'));
            die;
        }
    }

    public function company_view_notes($id = Null) {
        $this->layout = 'ajax';
        $this->loadModel('EmployeeNote');
        $view = $this->EmployeeNote->findById($id);
        $this->set('view', $view);
    }

    public function company_admin_tab($emp_id = Null) {
        $this->loadModel('EmployeeDetail');
        $this->loadModel('User');
        $this->layout = 'ajax';
        $user = $this->User->findById($this->Auth->user('id'));
        $this->set('user', $user);
        if (!empty($emp_id)) {
            $empData = $this->EmployeeDetail->find('first', array(
                'conditions' => array('EmployeeDetail.id' => $emp_id)));
            $this->set('employeeDetail', $empData);
        }
    }

    public function company_delete_permanently($emp_id = Null) {
        $this->loadModel('EmployeeDetail');
        $this->EmployeeDetail->delete($emp_id);
        echo json_encode(array('status' => 'true', 'message' => ''));
        die;
    }

    public function company_role_and_assignment($emp_id = Null) {
        $this->layout = 'ajax';
        $this->loadModel('Role');
        $this->loadModel('EmployeeDetail');
        $this->loadModel('Branch');
        $allRoles = $this->Role->find('all', array('fields' => array('name', 'id'), 'conditions' => array('Role.comp_id' => $this->Auth->user('id'), 'Role.status' => '1')));
        $allBranches = $this->Branch->find('all', array('fields' => array('office', 'id'), 'conditions' => array('Branch.comp_id' => $this->Auth->user('id'), 'Branch.status' => '1')));
        $this->set('role', $allRoles);
        $this->set('branch', $allBranches);
        if (!empty($emp_id)) {
            $empData = $this->EmployeeDetail->find('first', array(
                'conditions' => array('EmployeeDetail.id' => $emp_id)));
            $this->set('employeeDetail', $empData);
        }
    }

    public function company_licenses($emp_id = Null) {
        $this->layout = 'ajax';
        $this->loadModel('LicenseDetail');
        $this->loadModel('EmployeeDetail');
        $LicenseDetail = $this->LicenseDetail->find('first', array('fields' => array('nmls'), 'conditions' => array('LicenseDetail.id' => $this->Auth->user('license_id'))));
        $this->set('LicenseDetail', $LicenseDetail);
        $this->loadModel('State');
        $state = $this->State->find('list', array('fields' => array('id', 'state'), 'conditions' => array('State.status' => 1)));
        $this->set('state', $state);
        if (!empty($emp_id)) {
            $empData = $this->EmployeeDetail->find('first', array(
                'conditions' => array('EmployeeDetail.id' => $emp_id)));
            $this->set('employeeDetail', $empData);
        }
    }

    public function company_lenders($emp_id = Null) {
        $this->layout = 'ajax';
        if (!empty($emp_id)) {
            $this->loadModel('EmployeeDetail');
            $empData = $this->EmployeeDetail->find('first', array(
                'conditions' => array('EmployeeDetail.id' => $emp_id)));
            $this->set('employeeDetail', $empData);
        }
    }

    public function company_credit_bureas($emp_id = Null) {
        $this->layout = 'ajax';
        if (!empty($emp_id)) {
            $this->loadModel('EmployeeDetail');
            $empData = $this->EmployeeDetail->find('first', array(
                'conditions' => array('EmployeeDetail.id' => $emp_id)));
//            pr($empData);die;
            $this->set('employeeDetail', $empData);
        }
    }

    /* Delete Employee function */

    public function company_delete_emploee($id = NULL) {
        $this->autoRender = FALSE;
        $this->loadModel('EmployeeDetail');
        $empDetail = $this->EmployeeDetail->delete($id);
        if ($empDetail) {
            echo json_encode(array('status' => true, 'message' => 'Employee deleted successfully.'));
            die;
        }
    }

    public function company_deleteOnboarding() {
        $this->autoRender = FALSE;
        if (!empty($_POST['id'])) {
            $this->loadModel('EmployeeOnboarding');
            $empDetail = $this->EmployeeOnboarding->delete($_POST['id']);
            echo json_encode(array('status' => true, 'message' => 'Employee deleted successfully.'));
            die;
        }
    }

    /* Pop-up for Committee Recommendation */

    public function company_committee_recomendation($id = NULL) {
        $this->layout = "ajax";
        if (!empty($id)) {
            $this->loadModel('EmployeeHiring');
            $EmployeeHiring = $this->EmployeeHiring->findById($id);
            $this->set('EmployeeHiring', $EmployeeHiring);
        }
    }

    /* Pop-up for Document Exception */

    public function company_document_exception($id = null) {
        $this->layout = "ajax";
        $this->set('recID', $id);
    }

    /* Pop-up for Final Recommendation */

    public function company_final_recomendation($id = Null) {
        $this->layout = "ajax";
        if (!empty($id)) {
            $this->loadModel('EmployeeHiring');
            $EmployeeHiring = $this->EmployeeHiring->findById($id);
            $this->set('EmployeeHiring', $EmployeeHiring);
        }
    }

    /* Decision notes Pop-up */

    public function company_decision_notes($id = Null) {
        $this->layout = "ajax";
        if (!empty($id)) {
            $this->loadModel('EmployeeHiring');
            $EmployeeHiring = $this->EmployeeHiring->findById($id);
            $this->set('EmployeeHiring', $EmployeeHiring);
        }
    }

    /* Pop-up for lookup branches */

    public function company_lookup_branch() {
        $this->layout = 'ajax';
        $this->loadModel('Branch');
        $branches = $this->Branch->find('all', array(
            'conditions' => array('Branch.comp_id' => $this->Auth->user('id'))
        ));
        $this->set('branch', $branches);
    }

    /* Show lookup branch */

    public function company_showLookupBranch() {
        $this->layout = 'ajax';
        $this->loadModel('Branch');
        if (!empty($_POST)) {
            $findBranch = $this->Branch->findById($_POST['data']['Branch']['check']);
            $this->set('findBranch', $findBranch);
        }
    }

    /* Add Spouse */

    public function company_add_spouse($employee_id = NULL) {
        $this->layout = "ajax";
        if (!empty($employee_id)) {
            $this->loadModel('EmployeeDetail');
            $getSpouseInfo = $this->EmployeeDetail->find('first', array(
                'fields' => array('id', 'spouse_first_name', 'spouse_middle_name', 'spouse_last_name', 'spouse_birthday', 'spouse_age', 'spouse_married_on', 'spouse_image', 'spouse_suffix'),
                'conditions' => array('EmployeeDetail.id' => $employee_id)));
            $this->set('spouseInfo', $getSpouseInfo);
        }
    }

    /* Remove Spouse */

    public function company_removeSpouse($emp_id = Null) {
        $this->autoRender = FALSE;
        if (!empty($emp_id)) {
            $this->loadModel('EmployeeDetail');
            $this->request->data['EmployeeDetail']['spouse_first_name'] = '';
            $this->request->data['EmployeeDetail']['spouse_last_name'] = '';
            $this->request->data['EmployeeDetail']['spouse_middle_name'] = '';
            $this->request->data['EmployeeDetail']['spouse_birthday'] = '';
            $this->request->data['EmployeeDetail']['spouse_married_on'] = '';
            $this->request->data['EmployeeDetail']['spouse_image'] = '';
            $this->request->data['EmployeeDetail']['spouse_suffix'] = '';
            $data = array('EmployeeDetail' => $this->request->data['EmployeeDetail']);
            $this->EmployeeDetail->id = $emp_id;
            $this->EmployeeDetail->save($data, array('validate' => false));
        }
    }

    /* Add More license Tab */

    public function company_addLicense() {
        $this->layout = "ajax";
        $this->loadModel('State');
        $this->loadModel('StateLicense');
        $findState = $this->StateLicense->find('list', array(
            'fields' => array('state_id'),
            'conditions' => array(
                'StateLicense.comp_id' => $this->Auth->user('id'),
                'StateLicense.active_status' => 1
        )));
        $state = $this->State->find('list', array('fields' => array('id', 'state'), 'conditions' => array('State.id' => $findState)));
        $this->set('state', $state);
        if (!empty($_POST)) {
            $this->set('child', $_POST['child']);
        }
    }

    public function company_addLender() {
        $this->layout = "ajax";
        $this->loadModel('Lender');
        $lenders = $this->Lender->find('list', array(
            'fields' => array('id', 'name'),
            'conditions' => array('Lender.user_id' => $this->Auth->user('id'))));
        $this->set('lender', $lenders);
        if (!empty($_POST)) {
            $this->set('child', $_POST['child']);
        }
    }

    public function company_addCredit() {
        $this->layout = "ajax";
        if (!empty($_POST)) {
            $this->set('child', $_POST['child']);
        }
    }

    public function company_setting() {
        $this->layout = "ajax";
    }

    public function company_edit_email($email = Null) {
        $this->loadModel('EmployeeDetail');
        $employee = $this->EmployeeDetail->find('first', array('conditions' => array('EmployeeDetail.business_email' => $email)));
        if (isset($this->request->data['EmployeeDetail']['email'])) {
            $this->request->data['EmployeeDetail']['business_email'] = $this->request->data['EmployeeDetail']['email'];
            $this->EmployeeDetail->id = @$employee['EmployeeDetail']['id'];
            if ($this->EmployeeDetail->save($this->request->data)) {
                echo json_encode(array('status' => 'true', 'message' => 'Email updated.'));
            } else {
                $err['error'] = 'Please enter the email.';
                $err['status'] = 'false';
                echo json_encode($err);
                die;
            }
            $this->set('email', $this->request->data['EmployeeDetail']['email']);
            echo json_encode(array('status' => 'true', 'message' => 'Email updated.'));
            die;
        } else {
            $this->layout = "ajax";
            $this->set('email', $email);
        }
    }

    public function company_add_license_status() {
        $this->autoRender = FALSE;
        $this->loadModel('EmployeeDetail');
        if ($_POST['type'] == 'company_account') {
            $this->request->data['EmployeeDetail']['company_account'] = $_POST['status'];
            $msg = 'Company status active.';
        } else if ($_POST['type'] == 'license_status') {
            $this->request->data['EmployeeDetail']['license_status'] = $_POST['status'];
            $msg = 'License status active.';
        } else {
            $this->request->data['EmployeeDetail']['paid_status'] = $_POST['status'];
            $msg = 'Status changed successfully.';
        }

        $this->EmployeeDetail->id = $_POST['employee_id'];
        $this->EmployeeDetail->save($this->request->data);
        echo json_encode(array('status' => 'true', 'message' => $msg));
        die;
    }

    /* Delete Certificate */

    public function company_delete_certificate() {
        $this->autoRender = FALSE;
        $this->loadModel('EmployeeCertification');
        $delete = $this->EmployeeCertification->delete($_POST['recId']);
        echo json_encode(array('status' => 'true', 'message' => 'delted'));
        die;
    }

    /* Delete License */

    public function company_delete_license() {
        $this->autoRender = FALSE;
        $this->loadModel('EmployeeLicense');
        $delete = $this->EmployeeLicense->delete($_POST['recId']);
        echo json_encode(array('status' => 'true', 'message' => 'delted'));
        die;
    }

    /* Delete Lender */

    public function company_delete_lender() {
        $this->autoRender = FALSE;
        $this->loadModel('EmployeeLender');
        $delete = $this->EmployeeLender->delete($_POST['recId']);
        echo json_encode(array('status' => 'true', 'message' => 'delted'));
        die;
    }

    /* Delete Credit */

    public function company_delete_credit() {
        $this->autoRender = FALSE;
        $this->loadModel('EmployeeCredit');
        $delete = $this->EmployeeCredit->delete($_POST['recId']);
        echo json_encode(array('status' => 'true', 'message' => 'delted'));
        die;
    }

    /* Set Password */

    public function company_set_password($emp_id = Null) {
        $this->loadModel('EmployeeDetail');
        if (!empty($emp_id)) {
            $empData = $this->EmployeeDetail->find('first', array(
                'conditions' => array('EmployeeDetail.id' => $emp_id)));
            $this->set('employeeDetail', $empData);
        }
        if (!empty($this->request->data)) {
            $this->autoRender = false;
            $this->request->data['EmployeeDetail']['password'] = $this->Auth->password($this->request->data['EmployeeDetail']['new_password']);
            $this->request->data['EmployeeDetail']['enc_password'] = $this->request->data['EmployeeDetail']['new_password'];
            $this->EmployeeDetail->id = $emp_id;
            if ($this->EmployeeDetail->save($this->request->data)) {
                $Email = new CakeEmail();
                $Email->from(array('reply@reverseadvisor.com' => 'New Password setup for your account'));
                $Email->to(@$empData['EmployeeDetail']['business_email']);
                $Email->template('employee_force_change');
                $Email->emailFormat('html');
                $Email->viewVars(array('EmployeeDetail' => @$empData['EmployeeDetail']));
                $Email->subject('Reverse Advisor: New Password Setup');
                $Email->send();
                $response = array('status' => 'true', 'message' => 'Your password has been set successfully.');
                echo json_encode($response);
                die;
            } else {
                $errors['error'] = $this->EmployeeDetail->validationErrors;
                $errors['status'] = 'false';
                $errors['modelname'] = 'EmployeeDetail';
                echo json_encode($errors);
                die;
            }
        } else {
            $this->layout = "ajax";
        }
    }

    public function company_actual_set_password($emp_id = Null) {
        $this->autoRender = false;
        $this->loadModel('EmployeeDetail');
        if (!empty($emp_id)) {
            $empData = $this->EmployeeDetail->find('first', array(
                'conditions' => array('EmployeeDetail.id' => $emp_id)));
            $this->set('employeeDetail', $empData);
            $Email = new CakeEmail();
            $Email->from(array('reply@reverseadvisor.com' => 'New Password Setup'));
            $Email->to($empData['EmployeeDetail']['business_email']);
            $Email->template('employee_set_password');
            $Email->emailFormat('html');
            $Email->viewVars(array('EmployeeDetail' => $empData['EmployeeDetail']));
            $Email->subject('Reverse Advisor:New Password Setup');
            if ($Email->send()) {
                
            }
            echo json_encode(array('status' => 'true', 'message' => 'Request for password change has been sent.'));
            die;
        }
    }

    public function company_policy($emp_id = Null) {
        $this->loadModel('EmployeeDetail');
        if (!empty($_POST)) {
            $this->request->data['EmployeeDetail']['company_policy'] = $_POST['description'];
            $this->EmployeeDetail->id = $_POST['employee_id'];
            if ($this->EmployeeDetail->save($this->request->data)) {
                echo json_encode(array('status' => 'true'));
                die;
            }
        } else {
            $this->layout = "ajax";
            if (!empty($emp_id)) {
                $empData = $this->EmployeeDetail->find('first', array(
                    'conditions' => array('EmployeeDetail.id' => $emp_id)));
                $this->set('employeeDetail', $empData);
            }
        }
    }

    public function company_termination_policy() {
        $this->loadModel('EmployeeDetail');
        if (!empty($_POST)) {
            $this->request->data['EmployeeDetail']['termination_policy'] = $_POST['description'];
            $this->EmployeeDetail->id = $_POST['employee_id'];
            if ($this->EmployeeDetail->save($this->request->data)) {
                echo json_encode(array('status' => 'true'));
                die;
            }
        } else {
            $this->layout = "ajax";
            if (!empty($emp_id)) {
                $empData = $this->EmployeeDetail->find('first', array(
                    'conditions' => array('EmployeeDetail.id' => $emp_id)));
                $this->set('employeeDetail', $empData);
            }
        }
    }

    public function company_send_message($emp_id = Null) {
        $this->loadModel('EmployeeDetail');
        $this->loadModel('SendMessage');
        if (!empty($emp_id)) {
            $empData = $this->EmployeeDetail->find('first', array(
                'conditions' => array('EmployeeDetail.id' => $emp_id)));
            $this->set('employeeDetail', $empData);
        }
        if (!empty($_POST)) {
            $this->request->data['SendMessage']['emp_id'] = $emp_id;
            if ($this->SendMessage->save($this->request->data)) {
                $this->set('employeeDetail', $empData);
                $Email = new CakeEmail();
                $Email->from(array('reply@reverseadvisor.com' => 'New Message'));
                $Email->to($empData['EmployeeDetail']['business_email']);
                $Email->template('send_message_employee');
                $Email->emailFormat('html');
                $Email->viewVars(array('EmployeeDetail' => $empData['EmployeeDetail'], 'messageData' => $this->request->data));
                $Email->subject('Reverse Advisor: ' . $this->request->data['SendMessage']['subject']);
                if ($Email->send()) {
                    
                }
                echo json_encode(array('status' => 'true', 'message' => 'Message sent successfully.'));
                die;
            } else {
                $err = $this->SendMessage->validationErrors;
                $error['status'] = 'false';
                echo json_encode(array('status' => 'false', 'error' => $err));
                die;
            }
        } else {
            $this->layout = "ajax";
        }
    }

    public function company_download($filename = Null) {
        //  $this->autoRender = false;
        $path = BASE_URL . "upload/hiring_document/"; // change the path to fit your websites document structure
        $dl_file = preg_replace("([^\w\s\d\-_~,;:\[\]\(\).]|[\.]{2,})", '', $filename); // simple file name validation
        $dl_file = filter_var($dl_file, FILTER_SANITIZE_URL); // Remove (more) invalid characters
        $fullPath = $path . $dl_file;

        if ($fd = fopen($fullPath, "r")) {
            $fsize = filesize($fullPath);
            $path_parts = pathinfo($fullPath);
            $ext = strtolower($path_parts["extension"]);
            switch ($ext) {
                case "pdf":
                    header("Content-type: application/pdf");
                    header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\""); // use 'attachment' to force a file download
                    break;
                // add more headers for other content types here
                default;
                    header("Content-type: application/octet-stream");
                    header("Content-Disposition: filename=\"" . $path_parts["basename"] . "\"");
                    break;
            }
            header("Content-length: $fsize");
            header("Cache-control: private"); //use this to open files directly
            while (!feof($fd)) {
                $buffer = fread($fd, 2048);
                echo $buffer;
            }
        }
        fclose($fd);
        exit;
    }

    public function company_delete_child($id = Null) {
        $this->autoRender = false;
    }

    /* Employement Layout */

    public function employee_index() {
        $this->layout = 'employee';
    }

}
