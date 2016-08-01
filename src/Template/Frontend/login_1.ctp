<section class="login">
    <div class="login-container">
        <div class="col-sm-12"> <img class="men-icon" src="images/men-icon.png">
            
            <div class="login-form">
              <div class="errorMessages"></div>  
                <?php
                echo $this->Form->create('User', array(
                    'type' => 'post',
                    'id' => 'Login',
                    'url' => array('controller' => 'Login', 'action' => 'ajax_login'),
                ));
                ?>
                <?php echo $this->Session->flash(); ?>  
                <h6>Login</h6>
                <div class="errorMessages"></div>
                <div class="input-row">
                    <?php echo $this->Form->input('email1', array('label' => FALSE, 'required' => FALSE, 'placeholder' => 'Email', 'value' => @$_COOKIE['email'])) ?>
                    <span><img src="images/star.png"></span> 
                </div>
                <div class="input-row">
                    <?php echo $this->Form->input('password', array('label' => FALSE, 'required' => FALSE, 'placeholder' => 'Password', 'type' => 'password', 'value' => @$_COOKIE['password'])) ?>
                    <span><img src="images/star.png"></span> 
                </div>
                <div class="row">
                    <h5>
                        <label>
                            <input type="checkbox" class="terms" name="data[User][remember_me]" <?php if (!empty($_COOKIE['email']) && !empty($_COOKIE['password'])) {
                        echo 'checked';
                    } ?>>
                            <span>&nbsp;</span> </label>
                        Remember me  </h5>
                    <a href="#">Forgot password ?</a> </div>
                <button class="btn login-form-btn">LOGIN</button>
<?php echo $this->Form->end(); ?>
            </div>
        </div>
        <div class="col-sm-12 account"> <span>Don’t have an account ? <a href="<?php echo BASE_URL . 'signup'; ?>">Create New</a></span> </div>
    </div>
</section>