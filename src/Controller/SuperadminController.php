<?php

//session_start(); 
class SuperadminController extends AppController {

    var $uses = array('User');

    public function beforeFilter() {
        parent::beforeFilter();
        //        $this->Auth->allow(array('addCompanyLicense','ajaxdashboard'));
//        if(!isset($_COOKIE['testingTimeOut'])){
//            $this->redirect('/login/logout');
//        }
    }

    //public funtion for generating key
    function generateKey() {
        $hashkey = mt_rand(1000, 9999);
        return $hashkey;
    }

//home page  
    public function superadmin_dashboard() {
        $usertype = $this->Auth->user('type');
        $this->layout = 'superadmin_layout';
        //get all the comapny licensees
        $this->loadModel('User');
        $getAllLicensees = $this->User->find('all', array('order' => array('User.id' => 'DESC'), 'conditions' => array('User.type' => 'company_license'), 'recursive' => 0));
        if ($getAllLicensees) {
            $this->set('allLicenseComapnies', $getAllLicensees);
        }
    }

    public function superadmin_ajaxdashboard() {
        $usertype = $this->Auth->user('type');
        $this->layout = 'ajax';
        //get all the comapny licensees
        $this->loadModel('User');
        $this->loadModel('LicenseDetail');
        $getAllLicenses = $this->LicenseDetail->find('all', array(
            'order' => array('LicenseDetail.id' => 'DESC'),
            'recursive' => 0,
            'fields' => array('LicenseDetail.id', 'LicenseDetail.company_name', 'LicenseDetail.created', 'LicenseDetail.status')
        ));
        $customLicensesArray = array();
        $EmployeeCount="0";
        foreach ($getAllLicenses as $key => $value) {
            //get the company admins under the compnies based on the license id
            $compAdmins = $this->User->find('first', array('conditions' => array('User.license_id' => $value['LicenseDetail']['id']), 'fields' => array('User.id')));
            if(!empty($compAdmins)){
                //get the count of paid employee based on the company admin id
                $EmployeeCount = $this->EmployeeDetail->find('count', array('conditions' => array('EmployeeDetail.comp_id' => $compAdmins['User']['id'], 'EmployeeDetail.paid_status' => 1)));if(isset($EmployeeCount))
              {
                  $EmployeeCount=$EmployeeCount;
              }
              
        }
                $getAllLicenses[$key]['LicenseDetail']['id'] = $value['LicenseDetail']['id'];
                $getAllLicenses[$key]['LicenseDetail']['company_name'] = $value['LicenseDetail']['company_name'];
                $getAllLicenses[$key]['LicenseDetail']['created'] = $value['LicenseDetail']['created'];
                $getAllLicenses[$key]['LicenseDetail']['status'] = $value['LicenseDetail']['status'];
                 $getAllLicenses[$key]['LicenseDetail']['paid_licensee_count'] =@$EmployeeCount;
 }
        if ($getAllLicenses) {
            $this->set('allLicenses', $getAllLicenses);
        }

    }

    //create license
    public function superadmin_create_license() {
        $this->layout = 'ajax';
        $haskKey = "USR" . $this->generateKey();
        $this->loadModel('User');
        $checkIfAlready = $this->User->find('first', array('conditions' => array('User.company_id' => $haskKey, 'type' => 'company_license')));
        if ($checkIfAlready) {
            $haskKey = $this->generateKey();
        } else {
            $haskKey = $haskKey;
        }

        $this->set('rand_user_id', $haskKey);
    }

    //add company lincensee
    public function superadmin_addCompanyLicenseLast() {
        $this->autoRender = false;
        //set a new password request submit
        $this->loadModel('User');
        if ($this->request->is('post')) {

            //pr($this->request->data);
            $this->User->set($this->request->data);
            if ($this->User->validates()) {
                $this->set($this->request->data);
                $tokenPasswordIs = mt_rand(1, 99999999);
                if (isset($this->request->data['User']['logo']) and ( !empty($this->request->data['User']['logo']))) {

                    $type = substr($this->request->data['User']['logo'], 5, strpos($this->request->data['User']['logo'], ';') - 5);
                    $gettype = getimagesize($this->request->data['User']['logo']);
                    $type_exploded = explode("/", $gettype['mime']);
                    $type = $type_exploded[1];
                    $actualImageName = time() . rand(1, 10) . '.' . $type;
                    $imageName = WWW_ROOT . '/img/user_image/' . $actualImageName;
                    $file_is = file_put_contents($imageName, file_get_contents($this->request->data['User']['logo']));
                    $this->request->data['User']['logo'] = $actualImageName;
                }
                $this->request->data['User']['company_id'] = $this->request->data['User']['company_id'];
                $this->request->data['User']['type'] = "company_license";
                $this->request->data['User']['username'] = $this->request->data['User']['company_name'];
                $this->request->data['User']['email'] = $this->request->data['User']['email'];
                $this->request->data['User']['password'] = $this->Auth->password($tokenPasswordIs);
                $this->request->data['User']['token'] = sha1($tokenPasswordIs);

                $this->request->data['User']['status'] = 1;
                $this->request->data['User']['last_active'] = time();
                if ($this->User->save($this->request->data)) {
                    $lastAddedLicense = $this->User->getLastInsertID();
                    $last_signup = $this->User->findById($lastAddedLicense);
                    $this->loadModel('LicenseDetail');
                    $this->request->data['LicenseDetail']['user_id'] = $lastAddedLicense;
                    $this->request->data['LicenseDetail']['billing_contact'] = $this->request->data['User']['billing_contact'];
                    $this->request->data['LicenseDetail']['company_name'] = $this->request->data['User']['company_name'];
                    $this->request->data['LicenseDetail']['phone'] = $this->request->data['User']['phone'];
                    $this->request->data['LicenseDetail']['website'] = $this->request->data['User']['website'];
                    $this->request->data['LicenseDetail']['email'] = $this->request->data['User']['email'];
                    $this->request->data['LicenseDetail']['nmls'] = $this->request->data['User']['nmls'];
                    $this->request->data['LicenseDetail']['logo'] = $this->request->data['User']['logo'];
                    $this->request->data['LicenseDetail']['visa'] = $this->request->data['User']['visa'];
                    $this->request->data['LicenseDetail']['street'] = $this->request->data['User']['street'];
                    $this->request->data['LicenseDetail']['expiry_month'] = $this->request->data['User']['expiry_month'];
                    $this->request->data['LicenseDetail']['expiry_year'] = $this->request->data['User']['expiry_year'];
                    $this->request->data['LicenseDetail']['city'] = $this->request->data['User']['city'];
                    $this->request->data['LicenseDetail']['card_holder'] = $this->request->data['User']['card_holder'];
                    $this->request->data['LicenseDetail']['state'] = $this->request->data['User']['state'];
                    $this->request->data['LicenseDetail']['card_code'] = $this->request->data['User']['card_code'];
                    $this->request->data['LicenseDetail']['zip'] = $this->request->data['User']['zip'];
                    $this->request->data['LicenseDetail']['status'] = 1;
                    $this->request->data['LicenseDetail']['created'] = time();
                    $this->request->data['LicenseDetail']['modified'] = time();

                    $this->set($this->request->data);
                    if ($this->LicenseDetail->save($this->request->data)) {
                        $messageToUser = "Your account has been added as a Company License on Reverse Advisor.Your details are as follows:";
                        $Email = new CakeEmail();
                        $Email->from(array('reply@reverseadvisor.com' => 'Account creation as comapny license'));
                        $Email->to($this->request->data['User']['email']);
                        $Email->template('company_license');
                        $Email->emailFormat('html');
                        $Email->viewVars(array('userName' => $this->request->data['User']['username'], 'username' => $this->request->data['User']['email'], 'password' => $tokenPasswordIs, 'usertoken' => $last_signup['User']['token'], 'messageToUser' => $messageToUser));
                        $Email->subject('ReverseAdvisor:Account Creation as Company License');
                        $Email->send();
                        $response['message'] = "Company License added successfuly.";
//                        $response['url'] = BASE_URL;
                        $response['status'] = 'success';
                        echo json_encode($response);
                        die;
                    }
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

    // add company license from popup
    public function superadmin_addCompanyLicense() {
        $this->autoRender = false;
        $this->loadModel('LicenseDetail');
        if ($this->request->is('post')) {
            $this->LicenseDetail->set($this->request->data);
            if ($this->LicenseDetail->validates()) {
                $this->set($this->request->data);
                $tokenPasswordIs = mt_rand(1, 99999999);
                //unique ccompany id
                $haskKey = "CMP" . $this->generateKey();
                $this->loadModel('User');
                $checkIfAlready = $this->User->find('first', array('conditions' => array('User.company_id' => $haskKey, 'type' => 'company_license')));
                if ($checkIfAlready) {
                    $haskKey = $this->generateKey();
                } else {
                    $haskKey = $haskKey;
                }
                $this->request->data['LicenseDetail']['company_id'] = $haskKey;
                $this->set($this->request->data);
                if ($this->LicenseDetail->save($this->request->data)) {
                    $last_added_id = $this->LicenseDetail->getLastInsertID();
                    $response = array("status" => true, "last_id" => $last_added_id, "license_name" => $this->request->data['LicenseDetail']['company_name'], "message" => 'Company license added successfuly.');
                    echo json_encode($response);
                    die;
                }
            } else {
                $errors = $this->LicenseDetail->validationErrors;
                //  pr($errors);
                $response['status'] = 'error';
                $response['message'] = 'The Data could not be saved. Please, try again.';
                $response['errors'] = $errors;
                echo json_encode($response);
                die;
            }
        }
    }

    //manage the current added license after saving from popup
    public function superadmin_manage_added_license($license_id = null, $action = null) {
        $this->layout = 'ajax';
        $this->loadModel('LicenseDetail');
        //action set
        if (isset($action) && (!empty($action))) {
            $disabled = "";
        } else {
            $disabled = "disabled";
        }
        $this->set('disabled', $disabled);
        //unique ccompany id
        $haskKey = "CMP" . $this->generateKey();
        $this->loadModel('User');
        $checkIfAlready = $this->User->find('first', array('conditions' => array('User.company_id' => $haskKey, 'type' => 'company_license')));
        if ($checkIfAlready) {
            $haskKey = $this->generateKey();
        } else {
            $haskKey = $haskKey;
        }
        $this->set('rand_user_id', $haskKey);
        //licensed id
        if ((isset($license_id)) and ( !empty($license_id))) {
            $this->set("licensee_id", $license_id);
        }
        //get license name and nmls from id:
        $getInfo = $this->LicenseDetail->find('first', array('conditions' => array('LicenseDetail.id' => $license_id)));
        $this->set('license_details', $getInfo);
        //get the states list for billing info page
        $this->loadModel('State');
        $stateList = $this->State->find('list', array('fields' => array('State.state_code')));
        $this->set('stateList', $stateList);
        //get the added company admin
        $getAdmins = $this->User->find('all', array('order' => array('User.id' => 'DESC'), 'conditions' => array('User.type' => 'company_admin', 'User.license_id' => $license_id), 'recursive' => -1));
        if ($getAdmins) {
            $this->set('all_company_admins', $getAdmins);
        }
        //all the added company admins under this comany are
        foreach ($getAdmins as $key => $value) {
            $companyAdminIdsAre[] = $value['User']['id'];
        }
        if (!empty($companyAdminIdsAre)) {
            //get the added paid users from employee section,to show in users tab
            $this->loadModel('EmployeeDetail');
            $getPaidUsers = $this->EmployeeDetail->find('all', array('conditions' => array('EmployeeDetail.paid_status' => 1, 'EmployeeDetail.comp_id' => $companyAdminIdsAre), 'recursive' => -1, 'fields' => array('EmployeeDetail.id', 'EmployeeDetail.comp_id', 'EmployeeDetail.emp_first_name', 'EmployeeDetail.emp_last_name', 'EmployeeDetail.emp_middle_name', 'EmployeeDetail.created')));
            if ($getPaidUsers) {
                $this->set('all_paid_users', $getPaidUsers);
            }
        }

        //get the fee defaults
        $this->loadModel('FeeDefault');
        $getDetailedFees = $this->FeeDefault->find('all', array('order' => array('FeeDefault.id' => 'ASC')));
        if ($getDetailedFees) {
            $this->set('feesDetails', $getDetailedFees);
        }
        //get the applicable fee defaults set for a particular company license
        $this->loadModel('LicenseApplicableFees');
        $getapplicableLicenseFees = $this->LicenseApplicableFees->find('all', array('order' => array('LicenseApplicableFees.id' => 'ASC'), 'conditions' => array('LicenseApplicableFees.license_id' => $license_id)));
        if ($getapplicableLicenseFees) {
            $this->set('getapplicableLicenseFees', $getapplicableLicenseFees);
        }
        //get the reports data (licensed users)
        $licensed_users = $this->User->find('all', array('conditions' => array('User.type' => 'company_admin'), 'fields' => array('User.total_time_spent')));
        if ($licensed_users) {
            $this->Set('licensed_users_activity', $licensed_users);
        }
    }

    //add company info from manage tab
    public function superadmin_addCompanyInfo($licenseId = null) {
        $this->autoRender = false;

        if ($this->request->is('post') or ( $this->request->is('put'))) {
            $this->loadModel('LicenseDetail');

            $phone = str_replace("(", "", str_replace(")", "", str_replace("-", "", $this->request->data['LicenseDetail']['phone'])));
            $this->request->data['LicenseDetail']['phone'] = $phone;
            $getData = $this->LicenseDetail->find('first', array('conditions' => array('LicenseDetail.id' => $licenseId), 'fields' => array('LicenseDetail.logo', 'LicenseDetail.id')));

            if (isset($this->request->data['LicenseDetail']['logo']) and ( !empty($this->request->data['LicenseDetail']['logo']))) {
                $type = substr($this->request->data['LicenseDetail']['logo'], 5, strpos($this->request->data['LicenseDetail']['logo'], ';') - 5);
                if ($type == "image/png" or $type == "image/jpeg") {
                    $gettype = getimagesize($this->request->data['LicenseDetail']['logo']);
                    $type_exploded = explode("/", $gettype['mime']);
                    $type = $type_exploded[1];
                    $actualImageName = time() . rand(1, 10) . '.' . $type;
                    $imageName = WWW_ROOT . '/upload/License/' . $actualImageName;
                    $file_is = file_put_contents($imageName, file_get_contents($this->request->data['LicenseDetail']['logo']));
                    $this->request->data['LicenseDetail']['logo'] = $actualImageName;
                } else {
                    $response['message'] = "Please upload the logo from PNG ,JPG or JPEG formats.";
                    $response['status'] = 'imageTypeError';
                    echo json_encode($response);
                    die;
                }
            } else {
                $this->request->data['LicenseDetail']['logo'] = $getData['LicenseDetail']['logo'];
            }
            $this->LicenseDetail->set($this->request->data);
            //pr($this->request->data);
            $this->LicenseDetail->id = $licenseId;
            if ($this->LicenseDetail->validates()) {

                $this->request->data['LicenseDetail']['company_id'] = $this->request->data['LicenseDetail']['company_id'];
                $this->request->data['LicenseDetail']['billing_contact'] = $this->request->data['LicenseDetail']['billing_contact'];

                $this->request->data['LicenseDetail']['website'] = $this->request->data['LicenseDetail']['website'];
                $this->request->data['LicenseDetail']['phone'] = $this->request->data['LicenseDetail']['phone'];
                $this->request->data['LicenseDetail']['email'] = $this->request->data['LicenseDetail']['email'];

                $this->LicenseDetail->set($this->request->data);
                if ($this->LicenseDetail->save($this->request->data)) {
                    $response['message'] = "Company information saved successfuly.";
                    $response['status'] = 'success';
                    echo json_encode($response);
                }
            } else {
                $errors = $this->LicenseDetail->validationErrors;
                $response['status'] = 'error';
                $response['message'] = 'The Data could not be saved. Please, try again.';
                echo json_encode($errors);
                die;
            }
        }
    }

    //add billing info from manage tab
    public function superadmin_addBillingInfo($licenseId = null) {
        $this->autoRender = false;
        if ($this->request->is('post')) {
            $this->loadModel('LicenseDetail');
            $this->LicenseDetail->set($this->request->data);
            if ($this->LicenseDetail->validates()) {
                $this->LicenseDetail->id = $licenseId;
                $this->LicenseDetail->set($this->request->data);
                if ($this->LicenseDetail->save($this->request->data)) {
                    $response['message'] = "Billing information saved successfuly.";
                    $response['status'] = 'success';
                    echo json_encode($response);
                }
            } else {
                $errors = $this->LicenseDetail->validationErrors;
                $response['status'] = 'error';
                $response['message'] = 'The Data could not be saved. Please, try again.';
                echo json_encode($errors);
                die;
            }
        }
    }

    //software inputs page
    public function superadmin_softwareInputs() {
        $this->layout = 'ajax';
        //get the HUD inputs values(first section)
        $this->loadModel('HudInput');
        $getAllHudInputs = $this->HudInput->find('first', array('order' => array('HudInput.id' => 'DESC')));
        $this->set('hudInputs', $getAllHudInputs);
        //get the residual income by regions
        $this->loadModel('HudIncomeByRegions');
        $getAllResIncomeByRegion = $this->HudIncomeByRegions->find('all');
        $this->set('residualIncomes', $getAllResIncomeByRegion);
        //if HUD inputs are submit save them
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $this->HudInput->id = 1;
            if (isset($data['value']) and ( !empty($data['value']))) {
                $replaceThis = array("$", "%", ",");
                $withThis = array("", "", "");
                //$value_is = str_replace($replaceThis, $withThis, $data['value']);

                $value_is = $data['value'];
                if (is_numeric($value_is)) {
                    $saved = $this->HudInput->saveField($data['key'], $value_is);
                    if ($saved) {
                        $response['status'] = true;
                        $response['message'] = "saved successful";
                    }
                } else {
                    $response['status'] = false;
                    $response['message'] = "not saved";
                }
                echo json_encode($response);
                die;
            } else {
                $value_is = "0.00";
            }
        }
        //get the added states under the region
        $this->loadModel('ResidualStatesByRegions');
        $getAddedStates = $this->ResidualStatesByRegions->find('all', array('order' => array('ResidualStatesByRegions.region_id' => 'ASC')));
        $this->loadModel('States');
        $this->loadModel('ResidualStatesByRegions');
        $arraystates['Northeast'] = array();
        $arraystates['Midwest'] = array();
        $arraystates['South'] = array();
        $arraystates['West'] = array();
        foreach ($getAddedStates as $key => $values) {
            if ($values['ResidualStatesByRegions']['region_id'] == "1") {
                $Northeast = $this->States->find('first', array('conditions' => array('States.id' => $values['ResidualStatesByRegions']['state_id']), 'fields' => array('States.state_code', 'States.id')));

                $arraystates['Northeast'][]['state'] = $Northeast['States']['state_code'];
            }
            if ($values['ResidualStatesByRegions']['region_id'] == "2") {
                $Midwest = $this->States->find('first', array('conditions' => array('States.id' => $values['ResidualStatesByRegions']['state_id']), 'fields' => array('States.state_code', 'States.id')));
                $arraystates['Midwest'][]['state'] = $Midwest['States']['state_code'];
            }

            if ($values['ResidualStatesByRegions']['region_id'] == "3") {
                $South = $this->States->find('first', array('conditions' => array('States.id' => $values['ResidualStatesByRegions']['state_id']), 'fields' => array('States.state_code', 'States.id')));
                $arraystates['South'][]['state'] = $South['States']['state_code'];
            }

            if ($values['ResidualStatesByRegions']['region_id'] == "4") {
                $West = $this->States->find('first', array('conditions' => array('States.id' => $values['ResidualStatesByRegions']['state_id']), 'fields' => array('States.state_code', 'States.id')));
                $arraystates['West'][]['state'] = $West['States']['state_code'];
            }
        }

        $this->set('addedRegionsShow', $arraystates);
        //get the last update of the plf table uploaded to show on screen
        $this->loadModel('PlfTable');
        $lastpPlfUpdate = $this->PlfTable->find('first', array('order' => array('PlfTable.id' => "DESC"), 'fields' => array('PlfTable.created')));
        $lastPlfUploaded = date(" M d, Y", strtotime($lastpPlfUpdate['PlfTable']['created']));
        if ($lastPlfUploaded) {
            $this->set('plfLastUpload', $lastPlfUploaded);
        }
        //get the last update of the plf table uploaded to show on screen
        $this->loadModel('TalcRecord');
        $lastTalcUpdate = $this->TalcRecord->find('first', array('order' => array('TalcRecord.id' => "DESC"), 'fields' => array('TalcRecord.created')));
        $lastTalcUploaded = date("M d, Y", strtotime($lastTalcUpdate['TalcRecord']['created']));
        if ($lastTalcUploaded) {
            $this->set('talcLastUpload', $lastTalcUploaded);
        }
    }

    //edit menus
    public function superadmin_editMenus() {
        $this->layout = 'ajax';
        //to show the filled form data on page
        $this->loadModel('EditMenu');
        $menuOptions = $this->EditMenu->find('all');
        $this->set('editMenuOptions', $menuOptions);
        if ($this->request->is('post')) {
            $data = $this->request->data;
            foreach ($data['EditMenu'] as $key => $value) {
                $this->request->data = array();
                $this->EditMenu->id = $value['id'];
                //$menu=$value['menu'];
                if (!empty($value['menu'])) {
                    $this->request->data['EditMenu']['menu'] = $value['menu'];
                }
                if (!empty($value['default_value'])) {
                    $this->request->data['EditMenu']['default_value'] = $value['default_value'];
                }
                $updateData = $this->EditMenu->save($this->request->data);
            }

            if ($updateData) {
                $response['status'] = "success";
                $response['message'] = "Success,Edit Menus-Dropdown Content default choice(s) saved successfuly!";
            } else {
                $response['status'] = "error";
                $response['message'] = "Error,Please try again!";
            }
            echo json_encode($response);
            die;
        }
    }

    //set fees default
    public function superadmin_setFeeDefaults() {
        $this->layout = 'ajax';
        $this->loadModel('FeeDefault');
        $getDetailedFees = $this->FeeDefault->find('all', array('order' => array('FeeDefault.id' => 'ASC')));
        if ($getDetailedFees) {
            $this->set('feesDetails', $getDetailedFees);
        }
    }

    //reports and stats
    public function superadmin_reportsAndStats() {
        $this->layout = 'ajax';
    }

    //delete the users
    public function superadmin_deleteCompanyLicense($modelname = null, $recordId = null) {

        $this->autoRender = false;
        $deleteLicense = $this->deleteRecord($modelname, $recordId);
        if ($deleteLicense) {
            //delete the corresponding company admins from users as well.
            $this->loadModel('User');
            $getRecordId = $this->User->find('all', array('conditions' => array('User.license_id' => $recordId), 'fields' => array('User.id')));
            foreach ($getRecordId as $key => $value) {
                $deleteCompanyAdmin = $this->User->delete($value['User']['id']);
            }
        }
        if ($deleteLicense) {
            $response['status'] = "success";
            $response['message'] = "Success,Company license deleted successfuly!";
        } else {
            $response['status'] = "error";
            $response['message'] = "Error,Please try again!";
        }
        echo json_encode($response);
        die;
    }

    //delete the users
    public function superadmin_suspendLicense($link = null) {

        $this->autoRender = false;
        $company_id = $this->params['pass']['0'];
        $status = $this->params['pass']['1'];
        $this->loadModel('LicenseDetail');
        $this->LicenseDetail->id = $company_id;
        $updateStatus = $this->LicenseDetail->saveField('status', $status);
        if ($status == "2") {
            $link = BASE_URL . 'superadmin/superadmin/suspendLicense/' . $company_id . '/1';
            $image = BASE_URL . 'images/suspended.png';
            $message = "Success,Company License suspended successfuly!";
        } else {
            $link = BASE_URL . 'superadmin/superadmin/suspendLicense/' . $company_id . '/2';
            $image = BASE_URL . 'images/suspend.png';
            $message = "Success,Company License activated successfuly!";
        }
        if ($updateStatus) {
            //suspend corresponding company admins as well from user table.
            $this->loadModel('User');
            $getRecords = $this->User->find('all', array('conditions' => array('User.license_id' => $company_id), 'fields' => array('User.id', 'User.status')));
            foreach ($getRecords as $key => $value) {
                $this->User->id = $value['User']['id'];
                $updateStatusOfAdmin = $this->User->saveField('status', $status);
            }
            $response['status'] = "success";
            $response['message'] = $message;
            $response['image'] = $image;
            $response['link'] = $link;
        } else {
            $response['status'] = "error";
            $response['message'] = "Error,Please try again!";
        }
        echo json_encode($response);
        die;
    }

    //edit the company license
    public function superadmin_updateLicense($recordId = null) {
        $this->layout = 'ajax';

        //get the data based on the recordid
        if ((isset($recordId) and ( !empty($recordId)))) {
            $this->loadModel('User');
            $this->loadModel('LicenseDetail');
            // $this->User->Behaviors->load('Containable');
            $getTheLicenseDetails = $this->User->find('first', array('conditions' => array('User.id' => $recordId),
                'contain' => array('LicenseDetail.user_id', 'LicenseDetail.company_name', 'LicenseDetail.phone', 'LicenseDetail.user_id'),
                'fields' => array('User.company_id', 'User.email',)
            ));
            if ($getTheLicenseDetails) {
                //set the user details
                $this->set('licenseDetails', $getTheLicenseDetails);
            }
        }
    }

    //total licensed users
    public function superadmin_licensedUsers() {
        $this->layout = 'ajax';
        //get all the added licenses users from the employeee section
        $this->loadModel('EmployeeDetail');
        $this->loadModel('LicenseDetail');
        $getLicensedUsers = $this->EmployeeDetail->find('all', array('conditions' => array('EmployeeDetail.paid_status' => 1),
            'contain' => array('EmployeeAssignment.user_type'),
            'fields' => array('EmployeeDetail.comp_id', 'EmployeeDetail.emp_first_name', 'EmployeeDetail.emp_middle_name', 'EmployeeDetail.emp_last_name', 'EmployeeDetail.created')));

        $licensedData = array();
        foreach ($getLicensedUsers as $key => $value) {
            //get the company data
            $getTheCompanyData = $this->User->find('first', array('conditions' => array('User.id' => $value['EmployeeDetail']['comp_id']), 'fields' => array('User.id', 'User.first_name', 'User.last_name', 'User.created', 'User.license_id')));
//get the company admin data
            $getTheCompanyAdminData = $this->LicenseDetail->find('first', array('conditions' => array('LicenseDetail.id' => $getTheCompanyData['User']['license_id']), 'fields' => array('LicenseDetail.id', 'LicenseDetail.company_name', 'LicenseDetail.created')));

//creating the custom array
            $licensedData[$key]['Company']['company_name'] = $getTheCompanyAdminData['LicenseDetail']['company_name'];
            $licensedData[$key]['Company']['company_created'] = $getTheCompanyAdminData['LicenseDetail']['created'];
            $licensedData[$key]['Company']['type'] = "Company";
            $licensedData[$key]['CompanyAdmin']['company_admin_f_name'] = $getTheCompanyData['User']['first_name'];
            $licensedData[$key]['CompanyAdmin']['company_admin_l_name'] = $getTheCompanyData['User']['last_name'];
            $licensedData[$key]['CompanyAdmin']['company_admin_created'] = $getTheCompanyData['User']['created'];
            $licensedData[$key]['CompanyAdmin']['company_admin_type'] = "Company Admin";
            $licensedData[$key]['EmployeeDetail']['emp_first_name'] = $value['EmployeeDetail']['emp_first_name'];
            $licensedData[$key]['EmployeeDetail']['emp_middle_name'] = $value['EmployeeDetail']['emp_middle_name'];
            $licensedData[$key]['EmployeeDetail']['emp_last_name'] = $value['EmployeeDetail']['emp_last_name'];
            $licensedData[$key]['EmployeeDetail']['created'] = $value['EmployeeDetail']['created'];
            $licensedData[$key]['EmployeeDetail']['user_type'] = @$value['EmployeeAssignment'][0]['user_type'];
        }
        if (!empty($licensedData)) {
            $countLicensedUsersIs = count($licensedData);
            $this->set('countLicensedUsers', $countLicensedUsersIs);
            $this->set('getLicensedUsers', $licensedData);
        }
    }

    //total partner accounts
    public function superadmin_partnerAccounts() {
        $this->layout = 'ajax';
    }

    //total partner clients
    public function superadmin_partnerClients() {
        $this->layout = 'ajax';
    }

    //total scenarious 
    public function superadmin_totalScenarios() {
        $this->layout = 'ajax';
    }

    //company clients
    public function superadmin_companyClients() {
        $this->layout = 'ajax';
    }

    //manage licenses from the create licenses and then click on the button
    public function superadmin_createCompanyAdmin($license_id = null, $admin_id = null) {

        $this->layout = 'ajax';
        //get the company id based on the license id
        $this->loadModel('LicenseDetail');
        $comp_id = $this->LicenseDetail->find('first', array('conditions' => array('LicenseDetail.id' => $license_id), 'fields' => array('LicenseDetail.company_id')));
        $haskKey = $comp_id['LicenseDetail']['company_id'] . "_Admin" . $this->generateKey();
        $this->loadModel('User');
        $checkIfAlready = $this->User->find('first', array('conditions' => array('User.company_id' => $haskKey, 'type' => 'company_admin')));
        if ($checkIfAlready) {
            $haskKey = $this->generateKey();
        } else {
            $haskKey = $haskKey;
        }

        $this->set('rand_user_id', $haskKey);
        $this->set('license_id', $license_id);
        $this->set('admin_id', $admin_id);
        if (isset($admin_id) and ( !empty($admin_id))) {
            $getAdminData = $this->User->find('first', array('conditions' => array('User.id' => $admin_id, 'User.license_id' => $license_id, 'type' => 'company_admin'), 'recursive' => -1));
            $this->set('getAdminData', $getAdminData);
        }

        // pr($getAdminData);
    }

    //manage licenses from the create licenses and then click on the button
    public function superadmin_manage_licenses() {
        $this->layout = 'ajax';
    }

    //create new company admin 
    public function superadmin_addCompanyAdmin($license_id = null, $admin_id = null) {
        $this->autoRender = false;
        $this->loadModel('User');
        if ($this->request->is('post')) {
            $phoneNum=$this->request->data['User']['phone_no'] ;
            //get the license name
            $this->loadModel('LicenseDetail');
            $licenseName = $this->LicenseDetail->find('first', array('conditions' => array('LicenseDetail.id' => $license_id), 'fields' => array('LicenseDetail.company_name', 'LicenseDetail.id')));
            $this->User->set($this->request->data);
            if (isset($admin_id) and ( !empty($admin_id))) {
                $this->User->id = $admin_id;
            }
            $phone = str_replace("(", "", str_replace(")", "", str_replace("-", "", $this->request->data['User']['phone_no'])));

            $this->request->data['User']['phone_no'] = $phone;
            $this->User->set($this->request->data);
            //check if already exist in the employee
            $this->loadModel('EmployeeDetail');
             $ifAlreadyExist=$this->EmployeeDetail->find('first',array('conditions'=>array('EmployeeDetail.business_email'=>$this->request->data['User']['email']),'recursive'=>0,'fields'=>array('EmployeeDetail.business_email'))); 
             if(!empty($ifAlreadyExist))
             {
                $errors['email']['0']  = "Email already exists";
                $response['errors'] = $errors;
                echo json_encode($response);
                die;
             }
             else
             {
            if ($this->User->validates()) {
                $tokenPasswordIs = mt_rand(1, 99999999);
                $this->request->data['User']['type'] = "company_admin";
                $this->request->data['User']['username'] = $this->request->data['User']['title'];
                if (empty($admin_id)) {
                    $this->request->data['User']['password'] = $this->Auth->password($tokenPasswordIs);
                    $this->request->data['User']['token'] = sha1($tokenPasswordIs);
                }
                $this->request->data['User']['status'] = 1;
                $this->request->data['User']['account_verified'] = 1;
                $this->request->data['User']['last_active'] = time();
                if ($this->User->save($this->request->data)) {
                    $lastAddedCompanyAdmin = $this->User->getLastInsertID();
                    $last_signup = $this->User->findById($lastAddedCompanyAdmin);
                    //insert the record in employee detail as well.
                     $employeeData['EmployeeDetail']['comp_id']=$lastAddedCompanyAdmin;
                    $employeeData['EmployeeDetail']['cell']=$phoneNum;
                    $employeeData['EmployeeDetail']['emp_first_name']=$this->request->data['User']['first_name'];
                    $employeeData['EmployeeDetail']['emp_last_name']=$this->request->data['User']['last_name'];
                    $employeeData['EmployeeDetail']['business_email']=$this->request->data['User']['email'];
                    $this->EmployeeDetail->set($employeeData);
                    $this->EmployeeDetail->save($employeeData,array('validate'=>false));
                    //inserting the 603 rows in loan fees table ,fetched from default loan fees table
                    $this->loadModel('Loanfee');
                    $insert = $this->Loanfee->query("insert into `loanfees`(`hud_id`,`field`,`value`,`hud`,`tolerance`) SELECT `hud_id`,`field`,`value`,`hud`,`tolerance` FROM `default_loan_fees`");
                    //updating the last 603 record added in loan fees table 
                    $update = $this->Loanfee->updateAll(array('Loanfee.user_id' => $lastAddedCompanyAdmin), array('Loanfee.user_id' => 0));

                    if (empty($admin_id)) {


                        $messageToUser = "Your company account has been setup,therefore you're the company admin.Your details are as follows:";
                        $otherMessage = "You can change you password by login yourself.";
                        $Email = new CakeEmail();
                        $Email->from(array('reply@reverseadvisor.com' => 'Account setup as company admin'));
                        $Email->to($this->request->data['User']['email']);
                        $Email->template('company_admin');
                        $Email->emailFormat('html');
                        $Email->viewVars(array('firstName' => $this->request->data['User']['first_name'], 'username' => $this->request->data['User']['email'], 'password' => $tokenPasswordIs, 'usertoken' => $last_signup['User']['token'], 'messageToUser' => $messageToUser, 'otherMessage' => $otherMessage));
                        $Email->subject('ReverseAdvisor:Account setup as Company Admin');
                        $Email->send();
                        $resp_mesg = "Company admin added successfuly.";
                    } else {
                        $resp_mesg = "Company admin updated successfuly.";
                    }

                    //get the current latest data
                    $getTableData = $this->User->find('all', array('order' => array('User.id' => 'DESC'), 'conditions' => array('User.license_id' => $license_id)));
                    $htmlData = "";
                    $i = 0;
                    foreach ($getTableData as $key => $value) {
                        if ($i % 2 == 0) {
                            $htmlData .= '<tr class="rw_back">';
                        } else {

                            $htmlData .= '<tr class="">';
                        }
                        $htmlData .='<td>' . $value['User']['first_name'] . '</td>';
                        $htmlData .='<td>' . $value['User']['last_name'] . '</td>';
                        $htmlData .='<td class="tabl_clr phoneNo">' . $value['User']['phone_no'] . '</td>';
                        $htmlData .='<td class="tabl_clr">' . $value['User']['email'] . '</td>';
                        //$vari="superadmin/superadmin/createCompanyAdmin/$license_id/".$value["User"]["id"];
                        $test = $value["User"]["id"];
                        // $ngurl='openPopUp("get","'.$vari.'","'.$test.'")';
                        $neee = 'create_company_admin_' . $test;
                        $ttt = "createCompanyAdmin/$license_id/$test";
                        $FINA = "openPopUp('get','$ttt','$neee')";
                        $htmlData .='<td class="text-center"><a href="#" ng-click="' . $FINA . '" id="editCompanyAdmin"><img alt="" src="/developer/images/edit.png"></a></td>';
//                        
                        $delurl = BASE_URL . "superadmin/superadmin/deleteCompanyAdmin/User/" . $value["User"]["id"];
                        $htmlData .='<td class="text-center"><a href="#"><img alt="" src="/developer/images/trash.png" link="' . $delurl . '" class="deleteMe"></a></td>';
                        $htmlData .= '</tr>';
                        $i++;
                    }
                    $response = array("status" => true, "last_id" => $licenseName['LicenseDetail']['id'], "license_name" => $licenseName['LicenseDetail']['company_name'], "message" => $resp_mesg, 'htmldata' => $htmlData);
                    echo json_encode($response);
                    die;
                }
            } 
            else {
                $errors = $this->User->validationErrors;
                 $response['status'] = 'error';
                $response['message'] = 'The Data could not be saved. Please, try again.';
                $response['errors'] = $errors;
                echo json_encode($response);
                die;
            }
        }
        }
    }

    //terms and conditions popup
    public function superadmin_termsAndPrivacy() {
        $this->layout = 'ajax';
    }

    //get users list for send message popup
    public function superadmin_getUsersList() {
        $this->autoRender = false;
        $users_filter = $this->request->data['search_filter'];
        $search_keyword = $this->request->data['search_ind_user'];
        $this->loadModel('User');
        $conds = "";
        if ($users_filter == "all_company_admins") {
            $this->loadModel('EmployeeDetail');
            $cond = array('User.type' => 'company_admin');
            $conds = array(
            'conditions' => array('AND' => $cond, 'OR' => array('User.first_name LIKE ' => "%" . $search_keyword . "%", 'User.last_name LIKE ' => "%" . $search_keyword . "%")), 'fields' => array('User.email', 'User.id', 'User.first_name', 'User.last_name', 'User.type'), 'recursive' => 0);
         $mailClient = $this->User->find('all', $conds);
        } elseif ($users_filter == "all_licensed_users") {
             $cond = array('EmployeeDetail.paid_status' => 1);
             $conds = array(
            'conditions' => array('AND' => $cond, 'OR' => array('EmployeeDetail.emp_first_name LIKE ' => "%" . $search_keyword . "%", 'EmployeeDetail.emp_last_name LIKE ' => "%" . $search_keyword . "%")), 'fields' => array('EmployeeDetail.comp_id', 'EmployeeDetail.emp_first_name', 'EmployeeDetail.emp_middle_name', 'EmployeeDetail.emp_last_name', 'EmployeeDetail.business_email'), 'recursive' => 0);
             $mailClient = $this->EmployeeDetail->find('all', $conds);
//             $mailClient = $this->EmployeeDetail->find('all', array('conditions' => array('EmployeeDetail.paid_status' => 1),
//            'recursive' => 0,
//            'fields' => array('EmployeeDetail.comp_id', 'EmployeeDetail.emp_first_name', 'EmployeeDetail.emp_middle_name', 'EmployeeDetail.emp_last_name', 'EmployeeDetail.business_email')));
        } else {
            $cond['AND'] = array('User.type' => 'xyz');
        }
        $customMailUsers=array();
        foreach($mailClient as $userKey=>$userVal)
        {
             if (array_key_exists('User', $userVal)) {
               $customMailUsers[$userKey]['User']['email']=$userVal['User']['email'];
               $customMailUsers[$userKey]['User']['id']=$userVal['User']['id'];
               $customMailUsers[$userKey]['User']['first_name']=$userVal['User']['first_name'];
               $customMailUsers[$userKey]['User']['last_name']=$userVal['User']['last_name'];
               $customMailUsers[$userKey]['User']['type']="company_admin";
              }
             else
             {
               $customMailUsers[$userKey]['User']['email']=$userVal['EmployeeDetail']['business_email'];
               $customMailUsers[$userKey]['User']['id']=$userVal['EmployeeDetail']['id'];
               $customMailUsers[$userKey]['User']['first_name']=$userVal['EmployeeDetail']['emp_first_name'];
               $customMailUsers[$userKey]['User']['last_name']=$userVal['EmployeeDetail']['emp_last_name'];
               $customMailUsers[$userKey]['User']['type']="licensed_user";
             }
            
        }
       if (!empty($customMailUsers)) {
            $htmlData = '<select class="selectpicker" name="data[SystemMessage][mail_users][]" multiple>';
            foreach ($customMailUsers as $key => $value) {
                $htmlData .='<option  selected value="' . $value['User']['id'] . '">' . $value['User']['first_name'] . " " . $value['User']['last_name'] . '</option>';
            }
            $htmlData .='</select>';
            $response = array('status' => true, 'message' => 'success', 'htmlResponse' => $htmlData);
            echo json_encode($response);
            die;
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Please select atleast one state for region';
            echo json_encode($response);
            die;
        }
    }

    //send message popup
    public function superadmin_sendMessage() {
        $this->layout = 'ajax';
        if ($this->request->is('post')) {
            $this->loadModel('SystemMessage');
            $this->SystemMessage->set($this->request->data);
            if ($this->SystemMessage->validates()) {
//                pr($this->request->data);
//                die;
                $this->loadModel('User');
                $areaOfemail = $this->request->data['SystemMessage']['filter'];
                $mail_users = $this->request->data['SystemMessage']['mail_users'];
                // $ind_users = serialize($mail_users);
                $mailClient = $this->User->find('list', array('conditions' => array('User.type' => 'company_admin', 'User.id' => $mail_users), 'fields' => array('User.email'), 'recursive' => 0));
                //save in database for all the user only a single row in system messages table
                $maildata['SystemMessage']['filter'] = $areaOfemail;
                //  $maildata['SystemMessage']['ind_user'] = $ind_users;
                $maildata['SystemMessage']['subject'] = $this->request->data['SystemMessage']['subject'];
                $maildata['SystemMessage']['message_body'] = $this->request->data['SystemMessage']['message_body'];
                if ($this->SystemMessage->save($maildata))
                    if (!empty($mailClient)) {
                        $lastRecord = $this->SystemMessage->getLastInsertID();
                        $this->loadModel('SystemMessageUsers');
                        foreach ($mail_users as $key => $value) {
                            $this->SystemMessageUsers->create();
                            $mailUserData['SystemMessageUsers']['system_message_id'] = $lastRecord;
                            $mailUserData['SystemMessageUsers']['comp_id'] = $value;
                            $mailUserData['SystemMessageUsers']['read_status'] = "unread";
                            $this->SystemMessageUsers->save($mailUserData);
                        }
                        $messageToUser = $this->request->data['SystemMessage']['message_body'];
                        $Email = new CakeEmail();
                        $Email->from(array('reply@reverseadvisor.com' => 'System Message'));
                        $Email->to($mailClient);
                        $Email->template('send_message');
                        $Email->emailFormat('html');
                        $Email->viewVars(array('messageToUser' => $messageToUser));
                        $Email->subject($this->request->data['SystemMessage']['subject']);
                        $send = $Email->send();
                        $response['status'] = 'success';
                        $response['message'] = 'System message has been sent.';
                        echo json_encode($response);
                        die;
                    } else {
                        $response['status'] = 'error';
                        $response['message'] = 'No company admins added yet.';
                        echo json_encode($response);
                        die;
                    }
            } else {
                $errors = $this->SystemMessage->validationErrors;
                $response['status'] = 'error';
                $response['message'] = 'The Data could not be saved. Please, try again.';
                echo json_encode($errors);
                die;
            }
        }
    }

    //settings page only view
    public function superadmin_settings() {
        $this->layout = 'ajax';
    }

    //settings/reset password page functionality and saving
    public function superadmin_resetPassword() {
        $this->autoRender = false;
        if ($this->request->is('post')) {
            //pr($_SESSION['Auth']['User']);
            $this->loadModel('User');
            $this->User->set($this->request->data);
            if ($this->User->validates()) {
                $user_id = $_SESSION['Auth']['User']['id'];
                $this->User->id = $user_id;
                $newpassord = $this->Auth->password($this->request->data['User']['new_password']);
                $this->request->data['User']['password'] = $newpassord;
                $this->set($this->request->data);
                if ($this->User->save($this->request->data)) {
                    $_SESSION['Auth']['User']['password'] = $newpassord;
                    $response['message'] = "New Password set successfuly.";
                    $response['status'] = 'success';
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

    //add new comapny license popup only
    public function superadmin_newCompanyLicense() {
        $this->layout = 'ajax';
    }

    //add new comapny admin popup only
    public function superadmin_newCompanyAdmin() {
        $this->layout = 'ajax';
    }

    //delete company admin from manage admin tab under company license
    public function superadmin_deleteCompanyAdmin($modelname = null, $recordId = null) {

        $this->autoRender = false;
        $deleteAdmin = $this->deleteRecord($modelname, $recordId);
        if ($deleteAdmin) {
            $response['status'] = "success";
            $response['message'] = "Success,Company admin deleted successfuly!";
        } else {
            $response['status'] = "error";
            $response['message'] = "Error,Please try again!";
        }
        echo json_encode($response);
        die;
    }

    //save fee defaults
    public function superadmin_addFeeDefault() {
        $this->autoRender = false;
        if ($this->request->is('post')) {
            $this->loadModel('FeeDefault');
            $this->FeeDefault->id = $this->request->data['id'];
            if (isset($this->request->data['value']) and ( !empty($this->request->data['value']))) {
                $replaceThis = array("$", ",");
                $withThis = array("", "");
                $value_is = str_replace($replaceThis, $withThis, $this->request->data['value']);

                // $value_is = $data['value'];
                if (is_numeric($value_is)) {
                    $save = $this->FeeDefault->saveField($this->request->data['key'], $value_is);
                    if ($save) {
                        $response['status'] = true;
                        $response['message'] = "saved successful";
                    }
                } else {
                    $response['status'] = false;
                    $response['message'] = "not saved";
                }
                echo json_encode($response);
                die;
            } else {
                $value_is = "0.00";
            }
        }
    }

    //apply fees to a company license
    public function superadmin_applyFeesToLicense($license_id = null) {
        $this->autoRender = false;
        $this->loadModel('LicenseApplicableFees');
        //get the applicable fee defaults set for a particular company license
        $this->loadModel('LicenseApplicableFees');
        $getapplicableLicenseFees = $this->LicenseApplicableFees->find('all', array('order' => array('LicenseApplicableFees.id' => 'ASC'), 'conditions' => array('LicenseApplicableFees.license_id' => $license_id)));
        if ($getapplicableLicenseFees) {
            $this->set('getapplicableLicenseFees', $getapplicableLicenseFees);
        }
        //check if already,then update otherwise create 11 records for each license in license applicable fees
        $ifAlready = $this->LicenseApplicableFees->find('count', array('conditions' => array('LicenseApplicableFees.license_id' => $license_id)));
        if ($ifAlready > 0) {
            $this->LicenseApplicableFees->deleteAll(array('LicenseApplicableFees.license_id' => $license_id), false);
        }
        $data = $this->request->data;
//        pr($data);
//         die;
        foreach ($data['LicenseApplicableFees'] as $key => $value) {

            $this->request->data['LicenseApplicableFees'] = $value;
            $this->request->data['LicenseApplicableFees']['license_id'] = $this->request->data['license_id'];
            if (!empty($value['use_fee'])) {
                $replaceThis = array("$", ",", "%");
                $withThis = array("", "", "");
                $use_fee = str_replace($replaceThis, $withThis, $value['use_fee']);
            } else {
                $use_fee = "0.00";
            }

            //$this->request->data['LicenseApplicableFees']['use_fee'] = str_replace("$", "", $value['use_fee']);
            $this->request->data['LicenseApplicableFees']['use_fee'] = str_replace("$", "", $use_fee);

            if (empty($value['default_fee_value'])) {
                $fee_value_key = $value['default_fee_field'];
                $this->request->data['LicenseApplicableFees']['default_fee_value'] = $value[$fee_value_key];
            }
            if (!isset($value['status'])) {
                $this->request->data['LicenseApplicableFees']['status'] = "off";
            }
            $this->set($this->request->data);
            $this->LicenseApplicableFees->create();
            $save = $this->LicenseApplicableFees->save($this->request->data);
            if ($save) {
                $response = array("status" => "success", "message" => "Licenses fees  saved successfuly.");
            } else {
                $response = array("status" => "success", "message" => "Please try agian.");
            }
        }
        echo json_encode($response);
        die;
    }

    //software inputs residual income by regions
    public function superadmin_saveResidualIncomeByRegions() {
        $this->autoRender = false;
        if ($this->request->is('post')) {
            $data = $this->request->data;
            //pr($data);
            $this->loadModel('HudIncomeByRegions');
            $this->HudIncomeByRegions->id = $data['id'];
            //$value_is = preg_replace('/[^A-Za-z0-9\-\']/', '', $data['value']);
            $value_is = $data['value'];
            if (is_numeric($value_is)) {
                $save = $this->HudIncomeByRegions->saveField($data['key'], $value_is);
                if ($save) {
                    $response['status'] = true;
                    $response['message'] = "saved successful";
                }
            } else {
                $response['status'] = false;
                $response['message'] = "not saved";
            }
            echo json_encode($response);
            die;
        }
    }

    //edit regions
    public function superadmin_editRegions() {
        $this->layout = 'ajax';
        //get all the regions
        $this->loadModel('Region');
        $getAllRegions = $this->Region->find('all', array('fields' => array('Region.id', 'Region.region')));
        $this->set('allRegons', $getAllRegions);
        //get all the states
        $this->loadModel('State');
        $getAllStates = $this->State->find('all', array('fields' => array('State.id', 'State.state_code', 'State.state')));
        $this->set('allStates', $getAllStates);
        //if the request of saving the regions from popup
        $this->loadModel('ResidualStatesByRegions');

        if ($this->request->is('post')) {
            $this->ResidualStatesByRegions->set($this->request->data);
            if ($this->ResidualStatesByRegions->validates()) {

                $region_id = $this->request->data['ResidualStatesByRegions']['region_id'];
                if (isset($this->request->data['ResidualStatesByRegions']['state_id'])) {
                    $states = $this->request->data['ResidualStatesByRegions']['state_id'];
                    foreach ($states as $values) {
                        $if_alreadyThen_delete = $this->ResidualStatesByRegions->find('all', array('conditions' => array('ResidualStatesByRegions.state_id' => $values)));
                        if ($if_alreadyThen_delete) {
                            foreach ($if_alreadyThen_delete as $deleteKey => $deleteIt) {
                                $deleteAlready = $this->ResidualStatesByRegions->deleteAll(array('ResidualStatesByRegions.id' => $deleteIt['ResidualStatesByRegions']['id']));
                            }
                        }
                        $this->ResidualStatesByRegions->create();
                        $this->request->data['ResidualStatesByRegions']['region_id'] = $region_id;
                        $this->request->data['ResidualStatesByRegions']['state_id'] = $values;
                        $save = $this->ResidualStatesByRegions->save($this->request->data);
                    }
                    $this->loadModel('States');
                    //get the all states under the region so that to change the data on screen while adding
                    $getAddedStates = $this->ResidualStatesByRegions->find('all', array('order' => array('ResidualStatesByRegions.region_id' => 'ASC')));
                    $arraystates['Northeast'] = array();
                    $arraystates['Midwest'] = array();
                    $arraystates['South'] = array();
                    $arraystates['West'] = array();
                    foreach ($getAddedStates as $key => $values) {
                        if ($values['ResidualStatesByRegions']['region_id'] == "1") {
                            $Northeast = $this->States->find('first', array('conditions' => array('States.id' => $values['ResidualStatesByRegions']['state_id']), 'fields' => array('States.state_code', 'States.id')));

                            $arraystates['Northeast'][]['state'] = $Northeast['States']['state_code'];
                        }
                        if ($values['ResidualStatesByRegions']['region_id'] == "2") {
                            $Midwest = $this->States->find('first', array('conditions' => array('States.id' => $values['ResidualStatesByRegions']['state_id']), 'fields' => array('States.state_code', 'States.id')));
                            $arraystates['Midwest'][]['state'] = $Midwest['States']['state_code'];
                        }
                        if ($values['ResidualStatesByRegions']['region_id'] == "3") {
                            $South = $this->States->find('first', array('conditions' => array('States.id' => $values['ResidualStatesByRegions']['state_id']), 'fields' => array('States.state_code', 'States.id')));
                            $arraystates['South'][]['state'] = $South['States']['state_code'];
                        }
                        if ($values['ResidualStatesByRegions']['region_id'] == "4") {
                            $West = $this->States->find('first', array('conditions' => array('States.id' => $values['ResidualStatesByRegions']['state_id']), 'fields' => array('States.state_code', 'States.id')));
                            $arraystates['West'][]['state'] = $West['States']['state_code'];
                        }
                    }

                    $htmlResponse = '<div class="col-md-12 tabl_padd software_inputs spc "><div class="table-responsive high_res"><table class="table "><tbody>';
                    foreach ($arraystates as $statekey => $stateValue) {
                        $htmlResponse .='<tr ><td>' . $statekey;
                        $htmlResponse .='</td> <td><div class="btn-group states_dv" data-toggle="buttons">';
                        if (!empty($arraystates[$statekey])) {
                            foreach ($arraystates[$statekey] as $sk => $sv) {
                                $htmlResponse .='<label class="btn btn-primary"><input type="checkbox" >' . $sv['state'];

                                $htmlResponse .='</label>';
                            }
                        } else {
                            $htmlResponse .="No State(s) Added.";
                        }
                        $htmlResponse .='</div></td></tr>';
                    }
                    $htmlResponse .='</tbody></table></div></div>';

                    $response = array('status' => true, 'message' => 'State(s) successfuly saved under region', 'htmlResponse' => $htmlResponse);
                    echo json_encode($response);
                    die;
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Please select atleast one state for region';
                    echo json_encode($response);
                    die;
                }
            } else {
                $errors = $this->ResidualStatesByRegions->validationErrors;
                $response['errors'] = $errors;
                echo json_encode($response);
                die;
            }
        }
    }

    //get states on change of the region on the popup
    public function superadmin_getStates($region_id = null) {
        $this->autoRender = false;
        $this->loadModel('State');
        $this->loadModel('ResidualStatesByRegions');
        $getStatesBasedOnRegion = $this->ResidualStatesByRegions->find('list', array('conditions' => array('ResidualStatesByRegions.region_id' => $region_id), 'fields' => array('ResidualStatesByRegions.state_id')));
        //pr($getStatesBasedOnRegion);

        $allStates = $this->State->find('all', array('fields' => array('State.id', 'State.state_code', 'State.state')));

        $htmlCheckedStates = "";
        $final_k = 1;
        $htmlCheckedStates.='<div class="col-md-3 col-sm-3 col-xs-3   res_states_dv  spc">';

        $count = 1;
        $innerCounter = 0;

        foreach ($allStates as $key => $value) {
            $value_is = $value['State']['id'];
            $state_code = $value['State']['state_code'];
            $statename = $value['State']['state'];
            $checked = "";
            if (in_array($value_is, $getStatesBasedOnRegion)) {
                $checked = "checked";
            }
            $htmlCheckedStates .='<div  class="checkbox"><label><input type="checkbox"  value="' . $value_is . '" name="data[ResidualStatesByRegions][state_id][]" ' . $checked . ' >' . $statename . "(" . $state_code . ")";
            $htmlCheckedStates .='</label></div>';
            if ($count % 13 == 0) {
                $innerCounter++;
                $htmlCheckedStates .='</div>';
                if ($innerCounter < 4) {
                    $htmlCheckedStates .='<div class="col-md-3 col-sm-3 col-xs-3   res_states_dv  spc">';
                }
            }
            $count++;
        }

        $htmlCheckedStates .='</div>';

        $response = array('status' => true, 'message' => 'State(s) got', 'htmlCheckedStates' => $htmlCheckedStates);
        echo json_encode($response);
        die;
    }

    //upload the plf table xlsx file
    public function superadmin_uploadPlfTable() {

        $this->autoRender = FALSE;
        $this->loadModel('PlfTable');

        if ($this->request->is('post')) {
            if ($this->PlfTable->validates()) {
                $this->set($this->request->data);
                $filecheck_csv = basename($this->request->data['PlfTable']['plf_table']['name']);
                $ext_csv_verify = substr($filecheck_csv, strrpos($filecheck_csv, '.') + 1);
                if ($ext_csv_verify == "csv" or $ext_csv_verify == "xlsx") {

                    $filename = str_replace(" ", "_", time() . $this->request->data['PlfTable']['plf_table']['name']);
                    $fileNameIs = WWW_ROOT . 'upload/SoftwareInputs/' . $filename;
                    if (move_uploaded_file($this->request->data['PlfTable']['plf_table']['tmp_name'], $fileNameIs)) {
                        $itWorked = chmod('upload/SoftwareInputs/' . $filename, 0777);
                        include 'phpexcel-master/Classes/PHPExcel/IOFactory.php';
                        // This is the file path to be uploaded.
                        $inputFileName = 'upload/SoftwareInputs/' . $filename;
                        try {
                            $objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
                        } catch (Exception $e) {
                            //die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());

                            $response['status'] = 'error';
                            $response['message'] = 'Some thing happend wrong,please try again uploading of HUD PLF file.';
                            echo json_encode($response);
                            die;
                        }

                        $allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
                        $arrayCount = count($allDataInSheet);  // Here get total count of row in that Excel sheet
                        unset($allDataInSheet[1]);

                        if ($allDataInSheet) {
                            $truncate = $this->PlfTable->query("truncate `plf_tables`");
                        }
                        $this->request->data = array();
                        foreach ($allDataInSheet as $keyPlf => $valuePlf) {
                            $this->request->data['PlfTable']['age'] = $valuePlf['A'];
                            $this->request->data['PlfTable']['rate'] = $valuePlf['B'];
                            $this->request->data['PlfTable']['plf'] = $valuePlf['C'];
                            $this->PlfTable->create();
                            $this->PlfTable->set($this->request->data);
                            $save = $this->PlfTable->save($this->request->data);
                        }
                        if ($save) {
                            $unlink = unlink($fileNameIs);
                            $lastRecord = $this->PlfTable->getLastInsertID();
                            $getLastRecord = $this->PlfTable->findById($lastRecord);
                            $lastUpdated = date("M d, Y", strtotime($getLastRecord['PlfTable']['created']));
                            $response['status'] = 'success';
                            $response['updatedOn'] = $lastUpdated;
                            $response['message'] = 'Data successfuly imported in database.';
                            echo json_encode($response);
                            die;
                        }
                    }
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Please upload xlsx file only.';
                    echo json_encode($response);
                    die;
                }
            } else {
                $errors = $this->PlfTable->validationErrors;
                $response['status'] = 'error';
                $response['message'] = 'The Data could not be saved. Please, try again.';
                $response['errors'] = $errors;
                echo json_encode($response);
                die;
            }
        }
    }

    //upload the talc table xlsx file
    public function superadmin_uploadTalcTable() {

        $this->autoRender = FALSE;
        $this->loadModel('TalcRecord');

        if ($this->request->is('post')) {
            if ($this->TalcRecord->validates()) {
                $this->set($this->request->data);
                $filecheck_csv = basename($this->request->data['TalcRecord']['talc_table']['name']);
                $ext_csv_verify = substr($filecheck_csv, strrpos($filecheck_csv, '.') + 1);
                if ($ext_csv_verify == "csv" or $ext_csv_verify == "xlsx") {

                    $filename = str_replace(" ", "_", time() . $this->request->data['TalcRecord']['talc_table']['name']);
                    $fileNameIs = WWW_ROOT . 'upload/SoftwareInputs/' . $filename;
                    if (move_uploaded_file($this->request->data['TalcRecord']['talc_table']['tmp_name'], $fileNameIs)) {
                        $itWorked = chmod('upload/SoftwareInputs/' . $filename, 0777);
                        include 'phpexcel-master/Classes/PHPExcel/IOFactory.php';
                        // This is the file path to be uploaded.
                        $inputFileName = 'upload/SoftwareInputs/' . $filename;
                        try {
                            $objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
                        } catch (Exception $e) {
                            //die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());

                            $response['status'] = 'error';
                            $response['message'] = 'Some thing happend wrong,please try again uploading the HUD Loan Periods for TALC file.';
                            echo json_encode($response);
                            die;
                        }

                        $allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
                        $arrayCount = count($allDataInSheet);  // Here get total count of row in that Excel sheet
                        unset($allDataInSheet[1]);

                        if ($allDataInSheet) {
                            $truncate = $this->TalcRecord->query("truncate `talc_records`");
                        }
                        $this->request->data = array();
                        foreach ($allDataInSheet as $keyTalc => $valueTalc) {
                            $this->request->data['TalcRecord']['youngest_borrower'] = $valueTalc['A'];
                            $this->request->data['TalcRecord']['loan_period_1'] = $valueTalc['B'];
                            $this->request->data['TalcRecord']['loan_period_2'] = $valueTalc['C'];
                            $this->request->data['TalcRecord']['loan_period_3'] = $valueTalc['D'];
                            $this->TalcRecord->create();
                            $this->TalcRecord->set($this->request->data);
                            $save = $this->TalcRecord->save($this->request->data);
                        }
                        if ($save) {
                            $unlink = unlink($fileNameIs);
                            $lastRecord = $this->TalcRecord->getLastInsertID();
                            $getLastRecord = $this->TalcRecord->findById($lastRecord);
                            $lastUpdated = date("M d, Y", strtotime($getLastRecord['TalcRecord']['created']));
                            $response['status'] = 'success';
                            $response['updatedOn'] = $lastUpdated;
                            $response['message'] = 'Data successfuly imported in database.';
                            echo json_encode($response);
                            die;
                        }
                    }
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Please upload xlsx file only.';
                    echo json_encode($response);
                    die;
                }
            } else {
                $errors = $this->PlfTable->validationErrors;
                $response['status'] = 'error';
                $response['message'] = 'The Data could not be saved. Please, try again.';
                $response['errors'] = $errors;
                echo json_encode($response);
                die;
            }
        }
    }

    //view the plf  records in the drop down
    public function superadmin_viewPlfRecords() {
        $this->layout = 'ajax';
        $this->loadModel('PlfTable');
        $getRecordCount = $this->PlfTable->find('count');
        if ($getRecordCount > 0) {
//            $this->paginate = array('limit' => 8, 'order' => array('PlfTable.id' => 'ASC'));
//            $getAllPlfvalue = $this->Paginate('PlfTable');
            $getAllPlfvalue = $this->PlfTable->find('all');
        }
        $this->set('showAllPlfRecords', $getAllPlfvalue);
    }

    //view the TALC records in the drop down
    public function superadmin_viewTalcRecords() {
        $this->layout = 'ajax';
        $this->loadModel('TalcRecord');
        $getRecordCount = $this->TalcRecord->find('count');
        if ($getRecordCount > 0) {
            $getAllTalcvalue = $this->TalcRecord->find('all');
        }
        $this->set('showAllTalcRecords', $getAllTalcvalue);
    }

    // view the employee details from the User sub tab edit click popup
    public function superadmin_viewEmployeeDetails($employee_id = null) {
        $this->layout = 'ajax';
        //get the employees of the particular company admin
        $this->loadModel('EmployeeDetail');
        $getEmps = $this->EmployeeDetail->find('all', array('conditions' => array('EmployeeDetail.id' => $employee_id, 'EmployeeDetail.status' => '1'), 'fields' => array('EmployeeDetail.emp_first_name', 'EmployeeDetail.emp_middle_name', 'EmployeeDetail.emp_last_name', 'EmployeeDetail.created', 'EmployeeDetail.status'), 'recursive' => 0));
        $this->set('employee_details', $getEmps);
    }

    //setting tab
    public function superadmin_logout_settings() {
        $this->layout = 'ajax';
        $this->loadModel('SuperadminSetting');
        //get the already added settings
        $settingsAre = $this->SuperadminSetting->find('first', array('fields' => array('SuperadminSetting.id', 'SuperadminSetting.logout_time', 'SuperadminSetting.not_auto_logout_users')));
        $this->set('settingsAre', $settingsAre);

        if ($this->request->is('post')) {
            $data = $this->request->data;
            $this->SuperadminSetting->id = 1;
            if (isset($data['status']) and ( !empty($data['status']))) {
                $saveAutoLogout = $this->SuperadminSetting->saveField($data['logout_key'], $data['status']);
                if ($saveAutoLogout) {
                    $response['status'] = true;
                    $response['message'] = "saved successfuly";
                } else {
                    $response['status'] = false;
                    $response['message'] = "not saved";
                }
                echo json_encode($response);
                die;
            }

            if (isset($data['time_value']) and ( !empty($data['time_value']))) {
                $value_is = $data['time_value'];
                if (is_numeric($value_is)) {
                    $saved = $this->SuperadminSetting->saveField($data['key'], $value_is);
                    if ($saved) {
                        $response['status'] = true;
                        $response['message'] = "saved successful";
                    }
                } else {
                    $response['status'] = false;
                    $response['message'] = "not saved";
                }
                echo json_encode($response);
                die;
            } else {
                $value_is = $settingsAre['SuperadminSetting']['logout_time'];
            }
        }
    }

}
