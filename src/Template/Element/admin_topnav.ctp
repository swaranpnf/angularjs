<div id="topNav">
    <ul>
        <li>
            <a href="#menuProfile" class="menu"></a>

            <div id="menuProfile" class="menu-container menu-dropdown">
                <div class="menu-content">
                    <ul class="">
                        <li><a href="javascript:;">Edit Profile</a></li>
                        <li><a href="javascript:;">Edit Settings</a></li>
                                  </ul>
                </div>
            </div>
        </li>
        <li><?php echo $this->Html->link('Logout',array('controller'=>'users','action'=>'logout')); ?>   </li>
    </ul>
</div> <!-- #topNav -->
