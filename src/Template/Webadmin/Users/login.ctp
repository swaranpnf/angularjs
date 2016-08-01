<div id="login">
    <h1>Dashboard</h1>
    <div id="login_panel">
        <?php echo $this->Flash->render(); ?>
        <?php echo $this->Form->create(); ?>
        <div class="login_fields">
            <div class="field">
                <label for="email">Username</label>
                <?php echo $this->Form->input('username',array('placeholder'=>'Username','label'=>false,'required'=>FALSE)); ?>
	
            </div>

            <div class="field">
                <label for="password">Password <small><a href="javascript:;">Forgot Password?</a></small></label>
                <?php echo $this->Form->input('password',array('placeholder'=>'Password','label'=>false,'required'=>FALSE)); ?>
                
               		
            </div>
        </div> 			
        <div class="login_actions">
            <button type="submit" class="btn btn-primary" tabindex="3">Login</button>
        </div>
        <?php echo $this->Form->end(); ?>
    </div>	
</div> 