<div class="portlet light profile-sidebar-portlet">
	<div class="profile-userpic">
		<img style="height:150px;" src="{{ asset('images/AdminImages/'.$admindata['image']) }}" class="img-responsive"/>
	</div>
	<div class="profile-usertitle">
		<div class="profile-usertitle-name">
				<?php echo $admindata['name']?>
		</div>
		<div class="profile-usertitle-job">
		</div>
	</div>
	<div class="profile-usermenu">
		<ul class="nav">
			<?php if(Session::get('active')==3)
            {?>
            <li class="active ">
            <?php }
            else
            { ?> <li>
            <?php } ?>
				<a href="{{ action('Admin\AdminController@profile')}}">
				<i class="icon-home"></i>
				Overview </a>
			</li>
			<?php if(Session::get('active')==4)
            {?>
            <li class="active ">
            <?php }
            else
            { ?> <li>
            <?php } ?>
				<a  href="{{ action('Admin\AdminController@settings') }}">
				<i class="icon-settings"></i>
				Account Settings </a>
			</li>
		</ul>
	</div>
</div>