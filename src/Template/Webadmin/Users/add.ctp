<div id="contentHeader">
    <h1>Users Management</h1>
</div> <!-- #contentHeader -->	

<div class="container">
    <div class="grid-24">
        <div class="widget">
            <div class="widget-header">
                <span class="icon-article"></span>
                <h3>Add Users</h3>
            </div> <!-- .widget-header -->

            <div class="widget-content" ng-app="myApp" ng-controller="usersCtrl">
                <?php echo $this->Form->create('User',array('class'=>'form uniformForm validateForm','url'=>array('controller'=>'users','action'=>'add'))); ?>
                    <div class="field-group">
                        <label for="required">First Name</label>
                        <div class="field">
                           <?php echo $this->Form->input('firstname',array('ng-model'=>'user.firstname','label'=>false,'required'=>false));?> 
                        </div>
                    </div> <!-- .field-group -->
                    <div class="field-group">
                        <label for="required">Last Name</label>
                        <div class="field">
                           <?php echo $this->Form->input('lastname',array('ng-model'=>'user.lastname','label'=>false,'required'=>false));?> 
                        </div>
                    </div> <!-- .field-group -->
                    <div class="field-group">
                        <label for="required">User Name</label>
                        <div class="field">
                           <?php echo $this->Form->input('username',array('ng-model'=>'user.username','label'=>false,'required'=>false));?> 
                        </div>
                    </div> <!-- .field-group -->

                    <div class="actions">						
                        <button ng-click="addUser(user)" type="button" class="btn btn-info">Add User</button>
                    </div> <!-- .actions -->

                <?php echo $this->Form->end(); ?>
            </div> <!-- .widget-content -->
        </div> <!-- .widget -->	
    </div> <!-- .grid -->
</div> <!-- .container -->