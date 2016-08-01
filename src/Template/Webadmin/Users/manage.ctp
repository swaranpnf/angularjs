<div id="contentHeader">
    <h1>User Management</h1>
</div> <!-- #contentHeader -->	

<div class="container">

    <div class="grid-24">	

        <div class="widget widget-table">

            <div class="widget-header">
                <span class="icon-list"></span>
                <h3 class="icon chart">Users</h3>		
            </div>

            <div class="widget-content" >

                <form method="post" ng-app="myApp" ng-controller="usersCtrl">
                   
                    <p ng-bind="success"></p>

                    <table class="table table-bordered table-striped data-table">
                        <thead>
                            <tr>
                                <th>Sr No</th>
                                <th>Firstname</th>
                                <th>Lastname</th>
                                <th>Username</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($users)) {
                                $i = 1;
                                foreach ($users as $key => $value) {
                                    ?>
                                    <tr class="gradeA">
                                        <td><?php echo $i; ?></td>
                                        <td><?php echo $value->firstname ?></td>
                                        <td><?php echo $value->lastname ?></td>
                                        <td><?php echo $value->username ?></td>
                                        <td>
                                            <?php if ($value->status == 1) { ?>
                                                <a href="javascript:void(0)" ng-click="changeStatus(<?php echo $value->id; ?>)">Active</a>


                                                <?php
                                            } else {
                                                ?>
                                                <a href="javascript:void(0)" ng-click="changeStatus(<?php echo $value->id; ?>)">Deactive</a>
                                                <?php
                                            }
                                            ?></td>
                                        <td class="center"> 
                                            <a href="javascript:void(0)" ng-click="deleteRecord(<?php echo $value->id; ?>)">Delete</a> 
                                           <?php echo $this->Html->link('Edit',array('controller'=>'users','action'=>'edit/'.$value->id)); ?> 
                                    </tr>

                                    <?php
                                    $i++;
                                }
                            }
                            ?>

                        </tbody>

                    </table>

                    <!--<button type="button" ng-click="changeStatus(todotest)">Check</button>-->
                </form>
            </div> <!-- .widget-content -->

        </div> <!-- .widget -->











    </div> <!-- .grid -->





</div> <!-- .container -->
