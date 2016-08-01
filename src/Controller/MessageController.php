<?php

class MessageController extends AppController {

    var $name = "Message";

    public function company_notification() {
     $this->layout='ajax';
     $this->loadModel('SystemMessageUser');
     $SystemMessageUser=$this->SystemMessageUser->find('all',array(
         'conditions'=>array(
             'SystemMessageUser.comp_id'=>$this->Auth->user('id')
         ),
     ));     
     $this->set('message',$SystemMessageUser);
    }

}
?>

