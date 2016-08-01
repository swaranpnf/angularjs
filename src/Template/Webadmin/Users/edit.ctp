<div id="contentHeader">
    <h1>Users Management</h1>
</div> <!-- #contentHeader -->	

<div class="container">
    <div class="grid-24">
        <div class="widget">
            <div class="widget-header">
                <span class="icon-article"></span>
                <h3>Edit User</h3>
            </div> <!-- .widget-header -->

            <div class="widget-content" ng-app="myApp" ng-controller="usersCtrl">
                <?php echo $this->Form->create('User'); ?>
                <div class="field-group">
                    <label for="required">First Name</label>
                    <div class="field">
                        <?php echo $this->Form->input('firstname', array('ng-model' => 'user.firstname', 'label' => false, 'required' => false, 'value' => $users->firstname)); ?> 
                    </div>
                </div> <!-- .field-group -->
                <div class="field-group">
                    <label for="required">Last Name</label>
                    <div class="field">
                        <?php echo $this->Form->input('lastname', array('ng-model' => 'user.lastname', 'label' => false, 'required' => false, 'value' => $users->lastname)); ?> 
                    </div>
                </div> <!-- .field-group -->
                <div class="field-group">
                    <label for="required">User Name</label>
                    <div class="field">
                        <?php echo $this->Form->input('username', array('ng-model' => 'user.username', 'label' => false, 'required' => false, 'value' => $users->username)); ?> 
                    </div>
                </div> <!-- .field-group -->

                <div class="actions">						
                    <button ng-click="editRecord(user)" type="button" class="btn btn-info">Edit User</button>
                </div> <!-- .actions -->

                <?php echo $this->Form->end(); ?>
            </div> <!-- .widget-content -->
        </div> <!-- .widget -->	
    </div> <!-- .grid -->
</div> <!-- .container -->