<!doctype html>
<html class="no-js" lang="en">
    <head>
        <title>Admin - Dashboard</title>
        <meta charset="utf-8" />
        <meta name="description" content="" />
        <meta name="author" content="" />		
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php echo $this->Html->css(array('all.css')); ?>
    </head>
    <body>
        <div id="wrapper">
            <?php echo $this->element('admin_header'); ?> 
            <?php echo $this->element('admin_sidebar'); ?> 
            <div id="content" class="dynamicData">		
                <?php echo $this->fetch('content'); ?>
            </div> 
            <?php echo $this->element('admin_topnav'); ?> 
        </div> 
        <div id="footer">
            Copyright &copy; 2012, AngularJs.
        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.3/angular.min.js"></script>
        <?php echo $this->Html->script(array('all.js', 'app.js','usersController.js')); ?>
    </body>
</html>