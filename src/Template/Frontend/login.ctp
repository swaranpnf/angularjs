<section class="login_frm">
   
   <div class="container">
   <div class="row">
   <div class="col-md-12 margn ">
 
    <img src="<?php  echo BASE_URL;?>images/login_img.png" alt="" class="img-responsive"/>
   <div class="login-dv col-md-7 col-md-offset-3 col-sm-8 col-sm-offset-4">
       <?php //echo $this->Session->flash(); ?>  
       <div class="errorMessages"></div>  
     <h3>LOGIN</h3>
   <?php echo $this->Form->create('User', array('type' => 'post', 'id' => 'Login', 'url' => array('controller' => 'Login', 'action' => 'ajax_login'))); ?>
    <div class="form-group">
 
    <?php echo $this->Form->input('email1', array('class'=>'form-control','label' => FALSE, 'required' => FALSE, 'placeholder' => 'Email', 'value' => @$_COOKIE['email'])) ?>
    
 <span class="star">*</span>
    </div>
     <div class="form-group">
       
      <?php echo $this->Form->input('password', array('class'=>'form-control','label' => FALSE, 'required' => FALSE, 'placeholder' => 'Password', 'type' => 'password', 'value' => @$_COOKIE['password'])) ?>
      
      <span class="star">*</span></div> 
       <div class="form-group"> 
<div class="checkbox"> <label> 
        <input type="checkbox" class="terms" name="data[User][remember_me]" <?php if (!empty($_COOKIE['email']) && !empty($_COOKIE['password'])) { echo 'checked'; } ?>>
        Remember me
 </label>     <span><a href="<?php  echo BASE_URL.'forgotPassword'?>">Forgot Password ?</a></span></div>
 </div>
    <button class="btn btn-default login_btn1" type="submit">LOGIN</button>
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