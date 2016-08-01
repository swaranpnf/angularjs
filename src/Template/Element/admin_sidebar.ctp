	
<div id="sidebar">		

    <ul id="mainNav">			
        <li id="navDashboard" class="nav active">
            <span class="icon-home"></span>
            <?php echo $this->Html->link('Dashboard', array('controller' => 'dashboard', 'action' => 'index')); ?>
        </li>

        <li id="navPages" class="nav">
            <span class="icon-document-alt-stroke"></span>
            <a href="javascript:;">User Management</a>				<ul class="subNav">
                <li><?php echo $this->Html->link('Add User', array('controller' => 'users', 'action' => 'add')); ?></li>
                <li><?php echo $this->Html->link('Manage Users', array('controller' => 'users', 'action' => 'manage')); ?></li>

            </ul>						

        </li>	


    </ul>

</div> <!-- #sidebar -->
