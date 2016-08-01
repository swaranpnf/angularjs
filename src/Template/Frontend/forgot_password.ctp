<section class="login_frm">
   
   <div class="container">
   <div class="row">
   <div class="col-md-12 margn ">
 
    <img src="<?php  echo $this->webroot;?>images/login_img.png" alt="" class="img-responsive"/>
   <div class="login-dv col-md-7 col-md-offset-3 col-sm-8 col-sm-offset-4">
       <?php echo $this->Session->flash(); ?>  
       <div class="errorMessages"></div>  
     <h3>Forgot Password</h3>
   <?php echo $this->Form->create('User', array('type' => 'post', 'id' => 'ForgotPassword', 'url' => array('controller' => 'Login', 'action' => 'ajax_forgot_password'))); ?>
     <div id="reg_success" class="hide">
                <div class="alert alert-success">
    <button data-dismiss="alert" class="close">x</button>
    <strong id="suc_mesg"></strong> 
</div>
  </div>
    <div class="form-group">
 
    <?php echo $this->Form->input('email1', array('class'=>'form-control','label' => FALSE, 'required' => FALSE, 'placeholder' => 'Email', 'value' => @$_COOKIE['email'])) ?>
    
 <span class="star">*</span>
    </div>
       <div class="form-group"> 

 </div>
    <button class="btn btn-default" type="submit">Send Request</button>
   <?php echo $this->Form->end(); ?>
  
      <div class="clearfix"></div>
      
    
   </div>
      <p>  Already have an account ?<a href="<?php  echo BASE_URL.'login';?>"> Login</a></p>
 
   
   </div>
 
   </div>
   </div>
   </section>
     <div class="push"></div>
   </div>