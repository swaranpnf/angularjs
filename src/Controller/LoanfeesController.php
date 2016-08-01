<?php

/* Controller for setting up loan fees */

class LoanfeesController extends AppController {

    var $name = 'Loanfee';
    var $uses = array('Loanfee');

    public function beforeFilter() {
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes
        parent::beforeFilter();
        $this->Auth->allow(array());
    }

    public function company_index() {
        $this->layout = 'ajax';
        $this->loadModel('Hud');
        $this->Hud->hasMany['Loanfee']['conditions']['user_id'] = $_SESSION['Auth']["User"]['id'];
        $huds = $this->Hud->find('all');
        $this->loadModel('Lender');
        $lenders = $this->Lender->find('all', array('conditions' => array(
                'Lender.user_id' => $_SESSION['Auth']['User']['id'],
                'Lender.status' => 1,
        )));
        $this->set('lenders', $lenders);
        $this->set('huds', $huds);
    }

    public function company_getfee($lender_id) {
        $this->layout = 'ajax';
        $this->loadModel('Hud');
        $this->loadModel('LenderHudFees');
        $LenderHudFees = $this->LenderHudFees->find('all', array(
            'conditions' => array(
                'LenderHudFees.lender_id' => $lender_id
            ),
            'order' => array(
                'LenderHudFees.hud'
            )
        ));
        $this->set('LenderHudFees', $LenderHudFees);
//        pr($LenderHudFees); exit;
    }

    public function company_update($id = null) {
        $this->layout = 'ajax';
        $this->loadModel('Loanfee');
        if ($id !== null) {
            $data = array();
            $data['Loanfee']['id'] = $id;
            $data['Loanfee']['value'] = $_POST['value'];
            $this->Loanfee->save($data);
            echo json_encode(array('status' => true));
            exit;
        } else {
            echo json_encode(array('status' => false));
            exit;
        }
    }

    public function company_updatehudfees($id = null) {
        $this->layout = 'ajax';
        $this->loadModel('LenderHudFees');
        if ($id !== null) {
            $data = array();
            $data['LenderHudFees']['id'] = $id;
            $data['LenderHudFees'][$_POST['field']] = $_POST['value'];
//            pr($data); exit;
            $this->LenderHudFees->save($data);
            echo json_encode(array('status' => true));
            exit;
        } else {
            echo json_encode(array('status' => false));
            exit;
        }
    }

    public function company_deletehudfees($id = null) {
        $this->layout = 'ajax';
        $this->loadModel('LenderHudFees');
        if ($id !== null) {
            $this->LenderHudFees->delete($id);
            echo json_encode(array('status' => true));
            exit;
        } else {
            echo json_encode(array('status' => false));
            exit;
        }
    }

    public function testing($data, $space = null) {
        if ($space == null) {
            $space = "";
        }
        foreach ($data as $key => $value) {
            if ($value['children']) {
                $space .="_";
                echo $space . $value['Category']['name'];
                echo "<br>";
                $this->testing($value['children'], $space);
            } else {
                echo $space . $value['Category']['name'];
                echo "<br>";
            }
        }
    }

    public function company_test() {
        $this->loadModel('Role');
//        $data['Role']['parent_id'] = 2;
//        $data['Role']['name'] = 'Child2';
//        $data['Role']['comp_id'] = 33;
//        $this->Role->save($data);
//        exit;
        
        $data = $this->Role->generateTreeList(
                array('comp_id'=>$_SESSION['Auth']['User']['id']), null, null, '&nbsp;&nbsp;'
        );
//        pr($data); exit;
        $this->set("roles" , $data);
    }

}
