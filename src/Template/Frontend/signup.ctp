<section class="login_frm signupfrm">
   
   <div class="container">
   <div class="row">
   <div class="col-md-12 margn ">
 
    <img src="<?php  echo $this->webroot;?>images/login_img.png" alt="" class="img-responsive"/>
   <div class="login-dv col-md-7 col-md-offset-3 col-sm-8 col-sm-offset-4">
     <h3>Create an Account<small>Free To Consumers</small></h3>
     <?php echo $this->Session->flash(); ?>  
        <?php echo $this->Form->create('User', array('type' => 'post', 'id' => 'Signup', 'url' => array('controller' => 'Login', 'action' => 'ajax_signup')));?>
     <div id="reg_success" class="hide">
                <div class="alert alert-success">
    <button data-dismiss="alert" class="close">x</button>
    <strong id="suc_mesg"></strong> 
</div>
  </div>
      <div class="form-group usr_img">
  <?php echo $this->Form->input('username', array('class'=>'form-control','label' => FALSE, 'required' => FALSE, 'placeholder' => 'Username')) ?>
    
 <span class="star">*</span>
    </div>
    <div class="form-group">
  <?php echo $this->Form->input('email', array('class'=>'form-control','label' => FALSE, 'required' => FALSE, 'placeholder' => 'Email','type'=>'text')) ?>
    
 <span class="star">*</span>
    </div>
   
     <div class="form-group">
      
      <?php echo $this->Form->input('password', array('class'=>'form-control','label' => FALSE, 'required' => FALSE, 'placeholder' => 'Password', 'type' => 'password')) ?>
      <span class="star">*</span></div> 
       <div class="form-group frm_txt">
 
   <p>ReverseAdvisor gives everyone the same features with ability to evalute unlimited scenarios for up to 3 different people, such as a parent, a friend, or yourself. Lenders and financial advisors are offered powerful tools to help individual clients with unlimited profiles and scenarios. Visit  ReverseAdvisorPro.com for all options for retirement planning professionals.</p>

    </div>
       <div class="form-group"> 
<div class="checkbox this_is_for_term"> <label> 
          <input type="checkbox" name='data[User][terms]' class='terms'>
          <span class="forTerms"> I agree to ReverseAdvisor's  </span> <span><a href="#" data-toggle="modal" data-target="#myModal"> Terms of Service</a></span>
 </label>  </div>
 </div>
    <button class="btn btn-default" type="submit">Sign Up Free</button>
   <?php echo $this->Form->end(); ?>
  
      <div class="clearfix"></div>
      
    
   </div>
      <p >  I already have an account  <a href="<?php  echo BASE_URL.'login'?>"> Login</a></p>
 
   
   </div>
 
   </div>
   </div>
   </section>
     <div class="push"></div>
   </div>