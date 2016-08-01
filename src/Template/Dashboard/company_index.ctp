<section class="login_frm">

    <div class="container">
        <div class="row">
            <div class="col-md-12 margn ">

                <img src="<?php echo BASE_URL; ?>images/login_img.png" alt="" class="img-responsive"/>
                <div class="login-dv col-md-7 col-md-offset-3 col-sm-8 col-sm-offset-4">
                    <h3>Welcome 
                        <?php if ($user['User']['username'] != "superadmin") {
                            echo $user['User']['username'];
                        } else {
                            echo $user['Auth']['User']['username'];
                        } ?></h3>
                    <div class="clearfix"></div>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <p> Next features <span style="color:red"><strong>COMING SOON..... !!</strong></span></p>
                </div>

            </div>

        </div>
    </div>
</section>
<div class="push"></div>
</div>