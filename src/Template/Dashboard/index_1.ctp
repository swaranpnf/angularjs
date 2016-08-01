<section class="login">
    <div class="login-container">
        <div class="col-sm-12"> <img class="men-icon" src="<?php ?>images/men-icon.png">
            <div class="login-form">
                
                <h6>Welcome 
                    <?php 
                    if($user['User']['username']!="superadmin") { echo $user['User']['username']; } else { echo $user['Auth']['User']['username']; } ?></h6>
            </div>
        </div>
    </div>
</section>