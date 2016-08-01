<section class="login_frm">
   
   <div class="container">
   <div class="row">
   <div class="col-md-12 margn ">
 
    <img src="<?php  echo $this->webroot;?>images/login_img.png" alt="" class="img-responsive"/>
   <div class="login-dv col-md-7 col-md-offset-3 col-sm-8 col-sm-offset-4">
       <?php echo $this->Session->flash(); ?>  
       <div class="errorMessages"></div>  
     <h3>Set a new password</h3>
   <?php echo $this->Form->create('User', array('type' => 'post', 'id' => 'ResetPassword', 'url' => array('controller' => 'Frontend', 'action' => 'resetYourPassword'))); ?>
     <div id="reg_success" class="hide">
                <div class="alert alert-success">
    <button data-dismiss="alert" class="close">x</button>
    <strong id="suc_mesg"></strong> 
</div>
  </div>
    <div class="form-group">
 <?php if(!empty($email_to_reset)) {
 echo $this->Form->input('email1', array('class'=>'form-control','label' => FALSE, 'required' => FALSE, 'placeholder' => 'Email', 'value' => $email_to_reset,'readonly'=>'readonly'));
 }
 else {
  echo $this->Form->input('email1', array('class'=>'form-control','label' => FALSE, 'required' => FALSE, 'placeholder' => 'Email', 'value' => $email_to_reset)) ;   
 }
?>
    
 <span class="star">*</span>
    </div>
     <div class="form-group">
       
      <?php echo $this->Form->input('password', array('class'=>'form-control','label' => FALSE, 'required' => FALSE, 'placeholder' => 'Password', 'type' => 'password')) ?>
      
      <span class="star">*</span></div> 
      <div class="form-group">
       
      <?php echo $this->Form->input('con_password', array('class'=>'form-control','label' => FALSE, 'required' => FALSE, 'placeholder' => 'Confirm Password', 'type' => 'password')) ?>
      
      <span class="star">*</span></div> 
     <button class="btn btn-default" type="submit">Set New Password</button>
   <?php echo $this->Form->end(); ?>
    <div class="clearfix"></div>
     </div>
      <p>  Don’t have an account ?<a href="<?php  echo BASE_URL.'signup';?>"> Create New</a></p>
 
   
   </div>
 
   </div>
   </div>
   </section>
     <div class="push"></div>
   </div>