<?php

class EmployeeController extends AppController {

    public function company_index() {
        $this->layout = 'ajax';
        $this->loadModel('EmployeeDetail');
        $allEmployee = $this->EmployeeDetail->find('all', array(
            'order' => array('EmployeeDetail.id' => 'desc'),
            'conditions' => array('EmployeeDetail.comp_id' => $this->Auth->user('id')
        )));
        $this->set('allEmployee', $allEmployee);
    }

    public function company_addLicensePrice() {
        $this->autoRender = false;
        $license = $this->User->findById($this->Auth->user('id'));
        $comp_license_id = $license['User']['license_id'];
        $this->loadModel('LicenseApplicableFee');
        $this->LicenseApplicableFee->recursive = 1;
        $getThefeesAre = $this->LicenseApplicableFee->find('first', array('order' => array('LicenseApplicableFee.default_fee_id' => 'ASC'), 'conditions' => array('LicenseApplicableFee.license_id' => $comp_license_id, 'LicenseApplicableFee.default_fee_id' => 2)));
        if (!empty($_POST['price'])) {
            $amount = $_POST['price'] * $getThefeesAre['LicenseApplicableFee']['use_fee'];
            $res['status'] = 'true';
            $res['time'] = ucfirst($getThefeesAre['LicenseApplicableFee']['default_fee_field']);
            $res['label'] = 'Amount Billed ' . ucfirst($getThefeesAre['LicenseApplicableFee']['default_fee_field']);
            $res['amount'] = '$' . number_format($amount, 2);
        } else {
            $res['status'] = 'false';
        }
        echo json_encode($res);
        die;
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
        $this->loadModel('EmployeeHiringRequirementRelationship');
        $this->loadModel('EmployeeOnboardingRequirementRelationship');
        $this->loadModel('State');
        $this->loadModel('User');
        $this->loadModel('Role');
        if (!empty($employee_id)) {
            $empData = $this->EmployeeDetail->find('first', array('conditions' => array('EmployeeDetail.id' => $employee_id)));
            $this->set('employeeDetail', $empData);
            $this->request->data['EmployeeDetail']['id'] = $employee_id;
            $branchDetail = $this->EmployeeAssignment->find('first', array('conditions' => array('EmployeeAssignment.emp_id' => $employee_id)));
            $this->set('findBranch', $branchDetail);
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
            if (isset($this->request->data['EmployeeDetail']['business_email']) and ! empty($this->request->data['EmployeeDetail']['business_email'])) {
                $findEmployee = $this->User->find('all', array('conditions' => array('User.email' => $this->request->data['EmployeeDetail']['business_email'])));
                if (!empty($findEmployee)) {
                    $response['status'] = 'error';
                    $response['error']['business_email'] = 'Tes';
                    echo json_encode($response);
                    die;  
                }
            }
            $this->request->data['EmployeeDetail']['comp_id'] = $this->Auth->user('id');
            if (isset($this->request->data['EmployeeDetail']['emp_image']['name']) and ! empty($this->request->data['EmployeeDetail']['emp_image']['name'])) {
                $image = time() . '_' . $this->request->data['EmployeeDetail']['emp_image']['name'];
                move_uploaded_file($this->request->data['EmployeeDetail']['emp_image']['tmp_name'], 'upload/Employee/' . $image);
                $this->request->data['EmployeeDetail']['emp_image'] = $image;
            } 
            else {
                unset($this->request->data['EmployeeDetail']['emp_image']);
            }
            if (isset($this->request->data['EmployeeDetail']['spouse_image']['name']) and ! empty($this->request->data['EmployeeDetail']['spouse_image']['name'])) {
                $spimage = time() . '_' . $this->request->data['EmployeeDetail']['spouse_image']['name'];
                move_uploaded_file($this->request->data['EmployeeDetail']['spouse_image']['tmp_name'], 'upload/Employee/' . $spimage);
                $this->request->data['EmployeeDetail']['spouse_image'] = $spimage;
            }
            else {
                unset($this->request->data['EmployeeDetail']['spouse_image']);
            }
            if (isset($this->request->data['EmployeeDetail']['emp_age'])) {
                $this->request->data['EmployeeDetail']['emp_age'] = date('Y', time()) - date('Y', strtotime(str_replace('/', '-', $this->request->data['EmployeeDetail']['emp_birthday'])));
                $this->request->data['EmployeeDetail']['spouse_age'] = date('Y', time()) - date('Y', strtotime(str_replace('/', '-', $this->request->data['EmployeeDetail']['spouse_birthday'])));
            }
            $this->request->data['EmployeeDetail']['comp_id'] = $this->Auth->user('id');
            if ($this->EmployeeDetail->save($this->request->data)) {
                $veryFirst = 0;
                if (!empty($employee_id)) {
                    $emp_id = $employee_id;
                } elseif ($this->EmployeeDetail->getLastInsertID()) {
                    $veryFirst = 1;
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
                            if ($key == 0) {
                                $delete = $this->EmployeeChildren->deleteAll(array('EmployeeChildren.emp_id' => $emp_id), false);
                            }
                            $this->EmployeeChildren->create();
                            $this->request->data['EmployeeChildren'] = $value;
                            $this->request->data['EmployeeChildren']['emp_id'] = $emp_id;
                            $this->request->data['EmployeeChildren']['age'] = date('Y', time()) - date('Y', strtotime(str_replace('/', '-', $this->request->data['EmployeeChildren']['birthday'])));
                            $this->EmployeeChildren->save($this->request->data);
                        }
                        $response['saved'] = 'EmployeeChildren';
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
                    $findAssignment = $this->EmployeeAssignment->find('first', array('conditions' => array('EmployeeAssignment.emp_id' => $emp_id)));
                    if (!empty($findAssignment)) {
                        $this->EmployeeAssignment->id = $findAssignment['EmployeeAssignment']['id'];
                        $this->request->data['EmployeeAssignment']['title'] = $this->request->data['EmployeeAssignment']['title'];
                        $this->request->data['EmployeeAssignment']['user_type'] = $this->request->data['EmployeeAssignment']['user_type'];
                    }
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
                $EmployeeLicenseModel = "";
                $EmployeeHiringModel = "";
                $EmployeeOnboardingModel = "";
                $EmployeeCreditModel = "";
                $EmployeeLenderModel = "";
                if (isset($this->request->data['EmployeeDetail']['id'])) {
                    unset($this->request->data['EmployeeDetail']);
                }
                foreach ($this->request->data as $keySet => $valSet) {
                    if ($keySet === 'children') {
                        unset($this->request->data['children']);
                        break;
                    }
                    if (isset($valSet['EmployeeLicense']) and ! empty($valSet['EmployeeLicense'])) {
                        $EmployeeLicenseModel = "EmployeeLicense";
                    }
                    if (isset($valSet['EmployeeHiring'])) {
                        $EmployeeHiringModel = "EmployeeHiring";
                    }
                    if (isset($valSet['EmployeeLender'])) {
                        $EmployeeLenderModel = "EmployeeLender";
                    }
                    if (isset($valSet['EmployeeOnboarding'])) {
                        $EmployeeOnboardingModel = "EmployeeOnboarding";
                    }
                    if (isset($valSet['EmployeeCredit'])) {
                        $EmployeeCreditModel = "EmployeeCredit";
                    }
                }
                if (!empty($EmployeeLicenseModel) or ! empty($EmployeeLenderModel) or ! empty($EmployeeHiringModel) or ! empty($EmployeeOnboardingModel) or ! empty($EmployeeCreditModel) or isset($this->request->data['EmployeeCertification'])) {
                    $err = array();
                    $empLicenseStatus = 0;
                    $empLenderStatus = 0;
                    $empCreditStatus = 0;
                    $empHiringStatus = 0;
                    $empCertiStatus = 0;
                    $empOnboardingStatus = 0;
                    $inc = 0;
                    if (isset($EmployeeLicenseModel) && !empty($EmployeeLicenseModel)) {
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
                            $response['saved'] = 'EmployeeLicense';
                        }
                    }
                    if (isset($EmployeeLenderModel) and ! empty($EmployeeLenderModel)) {
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
                        if ($empLenderStatus == 1) {
                            $response['status'] = 'error';
                            $response['errorChild'] = $err;
                            $response['modelname'] = 'EmployeeLender';
                            echo json_encode($response);
                            die;
                        } else {
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
                                $this->request->data[$keys]['EmployeeLender']['enc_password'] = $values['EmployeeLender']['password'];
                                $newdata = array('EmployeeLender' => $this->request->data[$keys]['EmployeeLender']);
                                $this->EmployeeLender->save($newdata);
                            }
                            $response['saved'] = 'EmployeeLender';
                        }
                    }
                    if (isset($EmployeeCreditModel) and ! empty($EmployeeCreditModel)) {
                        unset($this->request->data['EmployeeDetail']);
                        foreach ($this->request->data as $kk => $vv) {
                            if (empty($vv['EmployeeCredit']['credit_bureas'])) {
                                $empCreditStatus = 1;
                                $err[$kk]['credit_bureas'] = $kk;
                            }
                            if (!isset($vv['EmployeeCredit']['corporate'])) {
                                if (empty($vv['EmployeeCredit']['username'])) {
                                    $empCreditStatus = 1;
                                    $err[$kk]['username'] = $kk;
                                }
                                if (empty($vv['EmployeeCredit']['password'])) {
                                    $empCreditStatus = 1;
                                    $err[$kk]['password'] = $kk;
                                }
                                if (empty($vv['EmployeeCredit']['account'])) {
                                    $empCreditStatus = 1;
                                    $err[$kk]['account'] = $kk;
                                }
                            }
                            $inc++;
                        }
                        if ($empCreditStatus == 1) {
                            $response['status'] = 'error';
                            $response['errorChild'] = $err;
                            $response['modelname'] = 'EmployeeCredit';
                            echo json_encode($response);
                            die;
                        } else {
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
                                if (isset($values['EmployeeCredit']['corporate'])) {
                                    $this->request->data[$keys]['EmployeeCredit']['corporate'] = 1;
                                } else {
                                    if (isset($values['EmployeeCredit']['password']) and ! empty($values['EmployeeCredit']['password'])) {
                                        $this->request->data[$keys]['EmployeeCredit']['password'] = $this->Auth->password($values['EmployeeCredit']['password']);
                                        $this->request->data[$keys]['EmployeeCredit']['enc_password'] = @$values['EmployeeCredit']['password'];
                                    }
                                    $this->request->data[$keys]['EmployeeCredit']['corporate'] = 0;
                                }
                                $newdata = array('EmployeeCredit' => $this->request->data[$keys]['EmployeeCredit']);
                                $this->EmployeeCredit->save($newdata);
                            }
                            $response['saved'] = 'EmployeeCredit';
                        }
                    }
                    if (isset($EmployeeHiringModel) and ! empty($EmployeeHiringModel)) {
                        unset($this->request->data['EmployeeDetail']);
                        foreach ($this->request->data as $kk => $vv) {
                            if (isset($vv['EmployeeHiring']['completed_status']) and empty($vv['EmployeeHiring']['selected_date'])) {
                                $empHiringStatus = 1;
                                $err[$inc]['selected_date'] = $kk;
                                $inc++;
                            }
                        }
                        if ($empHiringStatus == 1) {
                            $response['status'] = 'error';
                            $response['errorChild'] = $err;
                            $response['modelname'] = 'EmployeeHiring';
                            echo json_encode($response);
                            die;
                        } else {
                            unset($this->request->data['EmployeeDetail']);
                            foreach ($this->request->data as $kt => $vt) {
                                $hiringDetailsData = array();
                                $newDataValue = array();
                                if ($kt == 0) {
                                    $delete = $this->EmployeeHiring->deleteAll(array('EmployeeHiring.emp_id' => $emp_id), false);
                                    $delete = $this->EmployeeHiringRequirementRelationship->deleteAll(array('EmployeeHiringRequirementRelationship.emp_id' => $emp_id), false);
                                }
                                $this->EmployeeHiring->create();
                                $this->EmployeeHiringRequirementRelationship->create();
                                if (!isset($vt['EmployeeHiring']['completed_status'])) {
                                    $this->request->data[$kt]['EmployeeHiring']['completed_status'] = 0;
                                }
                                if (isset($vt['EmployeeHiring']['hiring_id'])) {
                                    $hiringDetailsData['EmployeeHiringRequirementRelationship']['hiring_id'] = $vt['EmployeeHiring']['hiring_id'];
                                    $hiringDetailsData['EmployeeHiringRequirementRelationship']['emp_id'] = $emp_id;
                                    $hiringDetailsData['EmployeeHiringRequirementRelationship']['comp_id'] = $this->Auth->user('id');
                                }
                                if (!isset($vt['EmployeeHiring']['eligible'])) {
                                    $this->request->data[$kt]['EmployeeHiring']['eligible'] = 0;
                                }
                                if (!isset($vt['EmployeeHiring']['exception'])) {
                                    $this->request->data[$kt]['EmployeeHiring']['exception'] = 0;
                                }
                                if (isset($vt['EmployeeHiring']['requirements'])) {
                                    $this->request->data[$kt]['EmployeeHiring']['requirements'] = $vt['EmployeeHiring']['requirements'];
                                    /*   $this->request->data[$kt]['EmployeeHiring']['requirements'] = implode(',', $vt['EmployeeHiring']['requirements']); */
                                }


                                if (isset($vt['EmployeeHiring']['upload_file']['name']) and ! empty($vt['EmployeeHiring']['upload_file']['name'])) {

                                    $image = time() . '_' . str_replace(' ', '_', $vt['EmployeeHiring']['upload_file']['name']);
                                    move_uploaded_file($vt['EmployeeHiring']['upload_file']['tmp_name'], 'upload/hiring_document/' . $image);
                                    $this->request->data[$kt]['EmployeeHiring']['upload_file'] = $image;
                                } else {
                                    $this->request->data[$kt]['EmployeeHiring']['upload_file'] = @$vt['EmployeeHiring']['new_upload_file'];
                                }
                                $this->request->data[$kt]['EmployeeHiring']['emp_id'] = $emp_id;
                                $this->request->data[$kt]['EmployeeHiring']['comp_id'] = $this->Auth->user('id');
                                $newDataValue = $this->request->data[$kt]['EmployeeHiring'];
                                if (empty($newDataValue['upload_file'])) {
                                    unset($newDataValue['upload_file']);
                                }
                                $newdata = array('EmployeeHiring' => $newDataValue);
                                $this->EmployeeHiring->save($newdata);
                                if (!empty($hiringDetailsData)) {
                                    $this->EmployeeHiringRequirementRelationship->save($hiringDetailsData);
                                }
                            }
                            $response['modelname'] = 'EmployeeHiring';
                            $response['saved'] = 'EmployeeHiring';
                        }
                    }
                    if (isset($EmployeeOnboardingModel) and ! empty($EmployeeOnboardingModel)) {
                        unset($this->request->data['EmployeeDetail']);
                        foreach ($this->request->data as $kt => $vt) {
                            if (isset($vt['EmployeeOnboarding']['completed_status']) and empty($vt['EmployeeOnboarding']['selected_date'])) {
                                $empOnboardingStatus = 1;
                                $err[$inc]['selected_date'] = $kt;
                                $inc++;
                            }
                        }
                        if ($empOnboardingStatus == 1) {
                            $response['status'] = 'error';
                            $response['errorChild'] = $err;
                            $response['modelname'] = 'EmployeeOnboarding';
                            echo json_encode($response);
                            die;
                        } else {
                            foreach ($this->request->data as $keys => $values) {
                                $newDataValue = array();
                                $onboardingDetailsData = array();
                                if ($keys == 0) {
                                    $delete = $this->EmployeeOnboarding->deleteAll(array('EmployeeOnboarding.emp_id' => $emp_id), false);
                                    $delete = $this->EmployeeOnboardingRequirementRelationship->deleteAll(array('EmployeeOnboardingRequirementRelationship.emp_id' => $emp_id), false);
                                }
                                $this->EmployeeOnboarding->create();
                                $this->EmployeeOnboardingRequirementRelationship->create();
                                if (isset($values['EmployeeOnboarding']['hiring_id'])) {
                                    $onboardingDetailsData['EmployeeOnboardingRequirementRelationship']['hiring_id'] = $values['EmployeeOnboarding']['hiring_id'];
                                    $onboardingDetailsData['EmployeeOnboardingRequirementRelationship']['emp_id'] = $emp_id;
                                    $onboardingDetailsData['EmployeeOnboardingRequirementRelationship']['comp_id'] = $this->Auth->user('id');
                                    $this->request->data[$keys]['EmployeeOnboarding']['hiring_id'] = $values['EmployeeOnboarding']['hiring_id'];
                                }
                                if (!isset($values['EmployeeOnboarding']['completed_status'])) {
                                    $this->request->data[$keys]['EmployeeOnboarding']['completed_status'] = 0;
                                }
                                if (isset($values['EmployeeOnboarding']['upload_file']['name']) and ! empty($values['EmployeeOnboarding']['upload_file']['name']) and $values['EmployeeOnboarding']['upload_file']['type'] == 'application/pdf') {
                                    $image = time() . '_' . $values['EmployeeOnboarding']['upload_file']['name'];
                                    move_uploaded_file($values['EmployeeOnboarding']['upload_file']['tmp_name'], 'upload/hiring_document/' . $image);
                                    $this->request->data[$keys]['EmployeeOnboarding']['upload_file'] = $image;
                                } else {
                                    $this->request->data[$keys]['EmployeeOnboarding']['upload_file'] = @$values['EmployeeOnboarding']['new_upload_file'];
                                }
                                $this->request->data[$keys]['EmployeeOnboarding']['emp_id'] = $emp_id;

                                $this->request->data[$keys]['EmployeeOnboarding']['comp_id'] = $this->Auth->user('id');
                                $newDataValue = $this->request->data[$keys]['EmployeeOnboarding'];
                                if (empty($newDataValue['upload_file'])) {
                                    unset($newDataValue['upload_file']);
                                }
                                $newdata = array('EmployeeOnboarding' => $newDataValue);
                                $this->EmployeeOnboarding->save($newdata);
                                $this->EmployeeOnboardingRequirementRelationship->save($onboardingDetailsData);
                            }
                            $response['modelname'] = 'EmployeeOnboarding';
                            $response['saved'] = 'EmployeeOnboarding';
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
                        if ($empCertiStatus == 1) {
                            $response['status'] = 'error';
                            $response['errorChild']['EmployeeCertification'] = $err;
                            $response['modelname'] = 'EmployeeCertification';
                            echo json_encode($response);
                            die;
                        } else {
                            if (isset($this->request->data['EmployeeCertification'])) {
                                foreach ($this->request->data['EmployeeCertification'] as $key => $value) {
                                    if ($key == 0) {
                                        $delete = $this->EmployeeCertification->deleteAll(array('EmployeeCertification.emp_id' => $emp_id), false);
                                    }
                                    $this->EmployeeCertification->create();
                                    $this->request->data['EmployeeCertification'] = $value;
                                    $this->request->data['EmployeeCertification']['emp_id'] = $emp_id;
                                    $this->EmployeeCertification->save($this->request->data);
                                }
                                $response['saved'] = 'EmployeeCertification';
                            }
                        }
                    }
                }
                if (!empty($emp_id)) {
                    $empData = $this->EmployeeDetail->find('first', array('conditions' => array('EmployeeDetail.id' => $employee_id)));
                    if (!empty($empData['EmployeeDetail']['spouse_first_name'])) {
                        $response['spouseStatus'] = 'true';
                    } else {
                        $response['spouseStatus'] = 'false';
                    }
                }
                $response['status'] = 'true';
                $response['employee_id'] = $emp_id;
                $response['very_first'] = $veryFirst;
                $response['message'] = 'Employee added successfully.';
                $empData = $this->EmployeeDetail->find('first', array('conditions' => array('EmployeeDetail.id' => $emp_id)));
                $response['emp_name'] = $empData['EmployeeDetail']['emp_first_name'] . ' ' . $empData['EmployeeDetail']['emp_last_name'];
                $this->set('employeeDetail', $empData);
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
                if (!empty($emp_id)) {
                    $empData = $this->EmployeeDetail->find('first', array('conditions' => array('EmployeeDetail.id' => $employee_id)));
                    if (!empty($empData['EmployeeDetail']['spouse_first_name'])) {
                        $response['spouseStatus'] = 'true';
                    } else {
                        $response['spouseStatus'] = 'false';
                    }
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

    public function company_freshHtml($model = Null, $emp = Null) {
        $this->layout = 'ajax';
        $this->loadModel($model);
        $find = $this->$model->find('all', array(
            'order' => array($model . '.id' => 'asc'),
            'conditions' => array($model . '.emp_id' => $emp)
        ));
        $this->loadModel('EmployeeLender');
        $this->loadModel('Lender');
        $EmployeeDetail = $this->Lender->find('list', array(
            'fields' => array('Lender.id', 'Lender.name'),
            'conditions' => array('Lender.user_id' => $this->Auth->user('id'))));
        $this->set('employeelender', $EmployeeDetail);
        $this->set('model', $model);
        $this->set('data', $find);
    }

    public function company_show_selected_branch($employee_id = NULL) {
        $this->loadModel('EmployeeAssignment');
        $this->loadModel('EmployeeDetail');
        $this->layout = 'ajax';
        if (!empty($employee_id)) {
            $emp = $this->EmployeeDetail->find('first', array('fields' => array('EmployeeDetail.emp_first_name', 'EmployeeDetail.emp_last_name'), 'conditions' => array('EmployeeDetail.id' => $employee_id)));
            $empData = $this->EmployeeAssignment->find('first', array('conditions' => array('EmployeeAssignment.emp_id' => $employee_id)));
            if (!empty($empData)) {
                $empData['Branch']['username'] = $emp['EmployeeDetail']['emp_first_name'] . ' ' . $emp['EmployeeDetail']['emp_last_name'];
            }
            $this->set('branch', $empData);
        }
    }

    /* View Company */

    public function company_view($employee_id = Null) {
        $this->loadModel('EmployeeDetail');
        $this->layout = 'ajax';
        if (!empty($employee_id)) {
            $empData = $this->EmployeeDetail->find('first', array('conditions' => array('EmployeeDetail.id' => $employee_id)));
            $this->set('employeeDetail', $empData);
            $this->request->data['EmployeeDetail']['id'] = $employee_id;
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
        $this->loadModel('EmployeeHiringRequirementRelationship');
        $findRalatedRequirements = $this->EmployeeHiringRequirementRelationship->find('list', array(
            'fields' => array('hiring_id'),
            'conditions' => array('EmployeeHiringRequirementRelationship.emp_id' => $employee_id)));
        $conditions['HiringRequirement.status'] = 1;
        $conditions['HiringRequirement.comp_id'] = $this->Auth->user('id');
        if (!empty($findRalatedRequirements)) {
            $conditions['HiringRequirement.id'] = $findRalatedRequirements;
        }
        $conditions['HiringRequirement.type'] = 'qualification';
        $qualification = $this->HiringRequirement->find('all', array(
            'fields' => array('id', 'name'),
            'order' => array('HiringRequirement.order' => 'asc'),
            'conditions' => $conditions));
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
        $this->loadModel('EmployeeOnboardingRequirementRelationship');
        $findRalatedRequirements = $this->EmployeeOnboardingRequirementRelationship->find('list', array(
            'fields' => array('hiring_id'),
            'conditions' => array('EmployeeOnboardingRequirementRelationship.emp_id' => $employee_id)));
        $conditions['HiringRequirement.status'] = 1;
        $conditions['HiringRequirement.type !='] = 'qualification';
        $conditions['HiringRequirement.comp_id'] = $this->Auth->user('id');
        if (!empty($findRalatedRequirements)) {
            $conditions['HiringRequirement.id'] = $findRalatedRequirements;
        }
        $qualification = $this->HiringRequirement->find('all', array(
            'fields' => array('id', 'name', 'type'),
            'order' => array('HiringRequirement.order' => 'asc'),
            'conditions' => $conditions));
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
        $this->loadModel('EmployeeDetail');
        $conditions = array();
        if (!empty($emp_id)) {
            $empData = $this->EmployeeDetail->find('first', array(
                'conditions' => array('EmployeeDetail.id' => $emp_id)));
            $this->set('employeeDetail', $empData);
            $conditions['EmployeeNote.emp_id'] = $emp_id;
        }
        $EmployeeNote = $this->EmployeeNote->find('all', array(
            'order' => array('EmployeeNote.id' => 'desc'),
            'conditions' => $conditions
        ));
        $this->set('EmployeeNote', $EmployeeNote);
    }

    public function company_compensation($emp_id = Null) {
        $this->layout = 'ajax';
        $this->loadModel('EmployeeDetail');
        if (!empty($emp_id)) {
            $empData = $this->EmployeeDetail->find('first', array(
                'conditions' => array('EmployeeDetail.id' => $emp_id)));
            $this->set('employeeDetail', $empData);
        }
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
            if (!empty($empData['EmployeeDetail']['paid_status'])) {
                if ($_POST['status'] == 'no') {
                    echo json_encode(array('status' => 'active', 'message' => 'show alert'));
                } else {
                    $this->EmployeeDetail->id = $_POST['id'];
                    $this->request->data['EmployeeDetail']['termination_date'] = $_POST['date'];
                    $this->request->data['EmployeeDetail']['paid_status'] = 0;
                    $this->EmployeeDetail->save($this->request->data);
                    echo json_encode(array('status' => 'true', 'message' => 'Termination date added.'));
                }
            } else {
                $this->EmployeeDetail->id = $_POST['id'];
                $this->request->data['EmployeeDetail']['termination_date'] = $_POST['date'];
                $this->EmployeeDetail->save($this->request->data);
                echo json_encode(array('status' => 'true', 'message' => 'Termination date added.'));
            }
            die;
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
                $response['type'] = $this->request->data['HiringRequirement']['type'];
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
        $this->loadModel('EmployeeDetail');
        $this->EmployeeNote->recirsive = -1;
        $view = $this->EmployeeNote->find('first', array('conditions' => array('EmployeeNote.id' => $id)));
        if (!empty($view)) {
            $empDetail = $this->EmployeeDetail->find('first', array('conditions' => array('EmployeeDetail.id' => $view['EmployeeNote']['emp_id'])));
            $view['EmployeeNote']['name'] = $empDetail['EmployeeDetail']['emp_first_name'] . ' ' . $empDetail['EmployeeDetail']['emp_last_name'];
        }
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
        $this->loadModel('EmployeeAssignment');
        $this->loadModel('Branch');
        $allRoles = $this->Role->find('all', array('fields' => array('name', 'id'), 'conditions' => array('Role.comp_id' => $this->Auth->user('id'), 'Role.status' => '1')));
        $allBranches = $this->Branch->find('all', array('fields' => array('office', 'id'), 'conditions' => array('Branch.comp_id' => $this->Auth->user('id'), 'Branch.status' => '1')));
        $this->set('role', $allRoles);
        $this->set('branch', $allBranches);
        if (!empty($emp_id)) {
            $empData = $this->EmployeeDetail->find('first', array(
                'conditions' => array('EmployeeDetail.id' => $emp_id)));
            $branchDetail = $this->EmployeeAssignment->find('first', array('conditions' => array('EmployeeAssignment.emp_id' => $emp_id)));
            $this->set('branchDetail', $branchDetail);
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
        $this->loadModel('Lender');
        $EmployeeDetail = $this->Lender->find('list', array(
            'fields' => array('Lender.id', 'Lender.name'),
            'conditions' => array('Lender.user_id' => $this->Auth->user('id'))));
        $this->set('employeelender', $EmployeeDetail);
        if (!empty($emp_id)) {
            $this->loadModel('EmployeeDetail');
            $empData = $this->EmployeeDetail->find('first', array(
                'conditions' => array('EmployeeDetail.id' => $emp_id)));
            $this->set('employeeDetail', $empData);
        }
    }

    public function company_credit_bureas($emp_id = Null) {
        $this->layout = 'ajax';
        $this->loadModel('EmployeeCreditSetting');
        $EmployeeCreditSetting = $this->EmployeeCreditSetting->find('list', array(
            'fields' => array('EmployeeCreditSetting.id', 'EmployeeCreditSetting.credit'),
            'conditions' => array(
                'EmployeeCreditSetting.status' => 1,
                'EmployeeCreditSetting.comp_id' => $this->Auth->user('id'))));
        $this->set('credit', $EmployeeCreditSetting);
        if (!empty($emp_id)) {
            $this->loadModel('EmployeeDetail');
            $empData = $this->EmployeeDetail->find('first', array(
                'conditions' => array('EmployeeDetail.id' => $emp_id)));
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

    public function company_document_exception($id = null, $record = null) {
        $this->layout = "ajax";
        $this->loadModel('EmployeeHiring');
        $hiringData = $this->EmployeeHiring->findById($record);
        $this->set('recID', $id);
        $this->set('hiringData', $hiringData);
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

    public function company_update_role($role_id = Null, $emp_id = Null) {
        $this->autoRender = false;
        $this->loadModel('EmployeeAssignment');
        $find = $this->EmployeeAssignment->find('first', array('conditions' => array(
                'EmployeeAssignment.emp_id' => $emp_id
        )));
        if (!empty($find)) {
            $this->request->data['EmployeeAssignment']['role_id'] = $role_id;
            $this->EmployeeAssignment->id = $find['EmployeeAssignment']['id'];
            $this->EmployeeAssignment->save($this->request->data, array('validate' => false));
        }
    }

    public function company_all_lookup_branch() {
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
            $this->EmployeeDetail->recursive = -1;
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

    public function company_virtualLogin() {
        $this->autoRender = false;
        $this->loadModel('EmployeeDetail');
        $getDetails = $this->EmployeeDetail->find('first', array(
            'conditions' => array('EmployeeDetail.id' => $_POST['emp_id'])));
        if (!empty($getDetails['EmployeeDetail']['business_email']) and ! empty($getDetails['EmployeeDetail']['enc_password'])) {
            $this->Auth->login($getDetails);
            echo json_encode(array('status' => 'true', 'message' => 'Need to set Username and password.'));
        } else {
            echo json_encode(array('status' => 'false', 'message' => 'Need to set Username and password.'));
        }
        die;
    }

    public function employee_logoutVitual() {
        $this->autoRender = false;
        $this->loadModel('EmployeeDetail');
        $getDetails = $this->EmployeeDetail->find('first', array(
            'fields' => array('comp_id'),
            'conditions' => array('EmployeeDetail.id' => $_POST['employee_id'])));
        $user = $this->User->findById($getDetails['EmployeeDetail']['comp_id']);
        if (!empty($user)) {
            $this->Auth->login($user['User']);
            echo json_encode(array('status' => 'true', 'message' => 'Need to set Username and password.'));
        } else {
            echo json_encode(array('status' => 'false', 'message' => 'Need to set Username and password.'));
        }
        die;
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
        $this->loadModel('EmployeeCreditSetting');
        $EmployeeCreditSetting = $this->EmployeeCreditSetting->find('list', array(
            'fields' => array('EmployeeCreditSetting.id', 'EmployeeCreditSetting.credit'),
            'conditions' => array(
                'EmployeeCreditSetting.status' => 1,
                'EmployeeCreditSetting.comp_id' => $this->Auth->user('id'))));
        $this->set('credit', $EmployeeCreditSetting);
        if (!empty($_POST)) {
            $this->set('child', $_POST['child']);
        }
    }

    public function company_setting() {
        $this->loadModel('EmployeeCreditSetting');
        $findCreditSetting = $this->EmployeeCreditSetting->find('all', array('conditions' => array('EmployeeCreditSetting.comp_id' => $this->Auth->user('id'))));
        $this->set('creditSetting', $findCreditSetting);
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
        $empData = $this->EmployeeDetail->find('first', array(
            'conditions' => array('EmployeeDetail.id' => $_POST['employee_id'])));
        if ($_POST['type'] == 'company_account') {
            $this->request->data['EmployeeDetail']['company_account'] = $_POST['status'];
            $msg = 'Company status active.';
        } else if ($_POST['type'] == 'license_status') {
            $this->request->data['EmployeeDetail']['license_status'] = $_POST['status'];
            $msg = 'License status active.';
        } else {
            $Email = new CakeEmail();
            $Email->from(array('reply@reverseadvisor.com' => 'Account Activation'));
            $Email->to($empData['EmployeeDetail']['business_email']);
            $Email->template('account_activation_email');
            $Email->emailFormat('html');
            $Email->viewVars(array('data' => $empData));
            $Email->subject('Reverse Advisor:Account Activation');
            $Email->send();
            $this->request->data['EmployeeDetail']['paid_status'] = $_POST['status'];
            $this->request->data['EmployeeDetail']['termination_date'] = '';
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

    public function company_delete_credit_setting() {
        $this->autoRender = FALSE;
        $this->loadModel('EmployeeCreditSetting');
        $delete = $this->EmployeeCreditSetting->delete($_POST['recId']);
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

    public function company_view_pdf($filename = Null) {
        $this->layout = "ajax";
        $this->set('filename', $filename);
    }

    public function company_delete_child($id = Null) {
        $this->autoRender = false;
    }

    /* Employement Layout */

    public function employee_index() {
        $this->layout = 'employee';
    }

    public function company_credits() {
        $this->layout = 'ajax';
        $this->loadModel('EmployeeCredit');
        $EmployeeCredit = $this->EmployeeCredit->find('all', array('conditions' => array(
                'EmployeeCredit.comp_id' => $this->Auth->user('id'),
                'EmployeeCredit.credit_status' => 1
        )));
        $this->set('EmployeeCredit', $EmployeeCredit);
    }

    public function company_credit_beureas_com() {
        $this->layout = 'ajax';
    }

    public function company_addCreditSetting() {
        $this->loadModel('EmployeeCreditSetting');
        if (isset($_POST['child'])) {
            $this->layout = 'ajax';
            $this->set('child', $_POST['child']);
        } else {
            $status = 1;
            foreach ($this->request->data as $key => $val) {
                if (empty($val['EmployeeCreditSetting']['credit'])) {
                    $status = 0;
                    $err[$key]['credit'] = 'Please enter';
                }
                if (empty($val['EmployeeCreditSetting']['account'])) {
                    $status = 0;
                    $err[$key]['account'] = 'Please enter';
                }
                if (empty($val['EmployeeCreditSetting']['username'])) {
                    $status = 0;
                    $err[$key]['username'] = 'Please enter';
                }
                if (empty($val['EmployeeCreditSetting']['password'])) {
                    $status = 0;
                    $err[$key]['password'] = 'Please enter';
                }
            }
            if ($status == 1) {
                foreach ($this->request->data as $key => $val) {
                    if ($key == 0) {
                        $delete = $this->EmployeeCreditSetting->deleteAll(array('EmployeeCreditSetting.comp_id' => $this->Auth->user('id')), false);
                    }
                    $this->EmployeeCreditSetting->create();
                    $val['EmployeeCreditSetting']['comp_id'] = $this->Auth->user('id');
                    if (!isset($val['EmployeeCreditSetting']['status'])) {
                        $val['EmployeeCreditSetting']['status'] = 0;
                    }
                    $val['EmployeeCreditSetting']['enc_password'] = $val['EmployeeCreditSetting']['password'];
                    $val['EmployeeCreditSetting']['password'] = $this->Auth->password($val['EmployeeCreditSetting']['password']);
                    $this->EmployeeCreditSetting->save($val);
                }
                $error['status'] = 'true';
                echo json_encode($error);
                die;
            } else {
                $error['status'] = 'false';
                $error['error'] = $err;
                echo json_encode($error);
                die;
            }
        }
    }

    public function company_getAllEmployees() {
        $this->layout = "ajax";
        $this->loadModel('EmployeeDetail');
        $allEmployee = $this->EmployeeDetail->find('all', array(
            'order' => array('EmployeeDetail.id' => 'desc'),
            'conditions' => array('EmployeeDetail.comp_id' => $this->Auth->user('id')
        )));
        $this->set('allEmployee', $allEmployee);
    }

    public function company_add_user_license($employee_id = Null) {
        $this->loadModel('EmployeeDetail');
        $this->loadModel('EmployeeLicenseSeat');
        $comp_id = $this->Auth->user('id');
        if (!empty($employee_id) and $employee_id != 'software_license') {
            $empData = $this->EmployeeDetail->find('first', array('conditions' => array('EmployeeDetail.id' => $employee_id)));
            $this->set('employeeDetail', $empData);
            $this->EmployeeDetail->id = $employee_id;
        } else {
            $empData = $this->EmployeeLicenseSeat->find('first', array('conditions' => array('EmployeeLicenseSeat.comp_id' => $comp_id)));
            $this->set('employeeDetail', $empData);
        }
        if (!empty($_POST)) {
            if ($employee_id == 'software_license') {
                $findData = $this->EmployeeLicenseSeat->find('first', array('conditions' => array('EmployeeLicenseSeat.comp_id' => $this->Auth->user('id'))));
                if (!empty($findData)) {
                    $this->EmployeeLicenseSeat->id = $findData['EmployeeLicenseSeat']['id'];
                }
                $this->request->data['EmployeeLicenseSeat']['timing'] = $this->request->data['EmployeeDetail']['timing'];
                $this->request->data['EmployeeLicenseSeat']['comp_id'] = $this->Auth->user('id');
                $this->request->data['EmployeeLicenseSeat']['amount_billed'] = str_replace('$', '', $this->request->data['EmployeeDetail']['amount_billed']);
                $this->request->data['EmployeeLicenseSeat']['number_of_licenses'] = str_replace('$', '', $this->request->data['EmployeeDetail']['number_of_licenses']);
                if ($this->EmployeeLicenseSeat->save($this->request->data)) {
                    $response['status'] = 'true';
                    $response['message'] = 'Licenses added successfully.';
                } else {
                    $response['status'] = 'false';
                    $response['error'] = $this->EmployeeLicenseSeat->validationErrors;
                }
            } else {
                $this->request->data['EmployeeDetail']['amount_billed'] = str_replace('$', '', $this->request->data['EmployeeDetail']['amount_billed']);
                if ($this->EmployeeDetail->save($this->request->data)) {
                    $response['status'] = 'true';
                    $response['message'] = 'Licenses added successfully.';
                } else {
                    $response['status'] = 'false';
                    $response['error'] = $this->EmployeeDetail->validationErrors;
                }
            }
            echo json_encode($response);
            die;
        } else {
            $this->layout = "ajax";
            $this->set('test', @$employee_id);
        }
    }

}

?>
