<div id="login">
    <h1>Dashboard</h1>
    <div id="login_panel">
        <?php echo $this->Form->create(); ?>
        <?php // echo $this->Form->create('Users', array('url' => array('controller' => 'users', 'action' => 'login'))); ?>
        <div class="login_fields">
            <div class="field">
                <label for="email">Email</label>
                <?php echo $this->Form->input('username',array('placeholder'=>'email@example.com','label'=>false,'required'=>FALSE)); ?>
	
            </div>

            <div class="field">
                <label for="password">Password <small><a href="javascript:;">Forgot Password?</a></small></label>
                <?php echo $this->Form->input('password',array('placeholder'=>'Passwoprd','label'=>false,'required'=>FALSE)); ?>
                
               		
            </div>
        </div> 			
        <div class="login_actions">
            <button type="submit" class="btn btn-primary" tabindex="3">Login</button>
        </div>
        <?php echo $this->Form->end(); ?>
    </div>	
</div> 