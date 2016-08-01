<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title>ReverseAdvisor</title>
        <link rel="shortcut icon" href="<?php echo BASE_URL; ?>favicon.ico" type="image/x-icon" />

        <!-- Bootstrap -->
        <?php echo $this->Html->css(array('bootstrap', 'style', 'font-awesome', 'square/_all','sweetalert')); ?>
        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
          <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>
    <body>
        <div class="wrapper">
            <section class="hdr res_padd">
                <div class="container_fluid">
                    <div class="">
                        <div class="col-md-12 ">
                            <div class="col-md-4 col-sm-4  col-xs-6 logo_new  spc">
                                <?php if (isset($_SESSION['Auth']['User']['type']) and $_SESSION['Auth']['User']['type'] == 'company_license') {
                                    ?>
                                    <a href="<?php echo BASE_URL; ?>company">
                                        <?php
                                    } else {
                                        ?>
                                        <a href="<?php echo BASE_URL; ?>">
                                            <?php }
                                        ?>

                                        <img src="<?php echo BASE_URL; ?>images/logo_1.png" alt="Logo" />
                                    </a>
                            </div>
                            <div class="col-md-6 col-sm-6 col-xs-6 ryt_btns spc">
                                <ul>
                                    <?php if (empty($user['User']['username'])) { ?>
                                        <li><a href="<?php echo BASE_URL . 'login'; ?>">Login</a> </li>
                                        <li><a href="<?php echo BASE_URL . 'signup'; ?>">Sign Up</a></li>
                                    <?php } else { ?>
                                        <li><a href="<?php echo BASE_URL . 'login/logout'; ?>">Logout</a></li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
