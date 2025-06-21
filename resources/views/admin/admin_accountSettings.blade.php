@extends('layouts.adminLayout.backendLayout')
@section('content')
<div class="page-content-wrapper">
	<div class="page-content">
		<div class="page-head">
			<div class="page-title">
				<h1>Admin Account <small>admin account page </small></h1>
			</div>
		</div>
		<ul class="page-breadcrumb breadcrumb">
			<li>
				<a href="{{ action('Admin\AdminController@dashboard') }}">Dashboard</a>
			</li>
		</ul>
		<div class="row">
			<div class="col-md-12">
				<div class="profile-sidebar" style="width:250px;">
					<div class="profile-sidebar" style="width: 250px;">
						@include('layouts.adminLayout.profilesidebar')
					</div>
					<div></div>
				</div>
				<div class="profile-content">
					<div class="row">
						<div class="col-md-12">
							<div class="portlet light">
								<div class="portlet-title tabbable-line">
									<div class="caption caption-md">
									@if(Session::has('flash_message_error'))
										<div role="alert" class="alert alert-danger alert-dismissible fade in"> <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"><span aria-hidden="true">×</span></button> <strong>Error!</strong> {!! session('flash_message_error') !!} </div>
									@endif
									@if(Session::has('flash_message_success'))
										<div role="alert" class="alert alert-success alert-dismissible fade in"> <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"><span aria-hidden="true">×</span></button> <strong>Success!</strong> {!! session('flash_message_success') !!} </div>
									@endif
									<i class="icon-globe theme-font hide"></i>
										<span class="caption-subject font-blue-madison bold uppercase">Profile Account</span>
									</div>
									<ul class="nav nav-tabs">
										<li class="active">
											<a href="#tab_1_1" data-toggle="tab">Personal Info</a>
										</li>
										<li>
											<a href="#tab_1_2" data-toggle="tab">Change Logo</a>
										</li>
										<li>
											<a href="#tab_1_3" data-toggle="tab">Change Password</a>
										</li>
									</ul>
								</div>
								<div class="portlet-body">
									<div class="tab-content">
										<div class="tab-pane active" id="tab_1_1">
											<form role="form" id="edit_form" method="POST" action="{{ action('Admin\AdminController@settings') }}">
											<input type="hidden" name="_token" value="{{ csrf_token() }}">
												<div class="form-group">
													<label class="control-label">Name</label>
													<input type="text" placeholder="Name" name="name" value="<?= $admindata['name']?>" class="form-control" required/>
												</div>
												<div class="form-group">
													<label class="control-label">Email</label>
													<input type="text" placeholder="Email" name="email" value="<?= $admindata['email']?>" class="form-control" required/>
												</div>
												<div class="form-group">
													<label class="control-label">Email</label>
													<input type="text" placeholder="Mobile" name="mobile" value="<?= $admindata['mobile']?>" class="form-control" required/>
												</div>
												
												<div class="margiv-top-10">
													<input type="submit" class="btn green-haze" value="Save changes">
												</div>
											</form>
										</div>
										<div class="tab-pane" id="tab_1_2">
											<div id="Error" style="Color:red;display:none;">
                                				<label>Logo must be of type: .jpg, .png, .jpeg, or .gif.</label>
                                			</div>
											<form method="post" id="change_logo" action="{!! action('Admin\AdminController@changeAdminLogo') !!}" enctype="multipart/form-data" role="form">
												<input type="hidden" name="_token" value="{{ csrf_token() }}">
												<div class="form-group">
													<div class="fileinput fileinput-new" data-provides="fileinput">
														<div class="fileinput-new thumbnail" style="width:300px;">
														<?php $img=Auth::user()->image;
														if($img!="")
														{?>
															<img src="{{ asset('images/AdminImages/'.$img) }}" class="img-responsive" alt="">
														<?php }?>
														</div>
														<div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 200px; max-height: 150px;">
														</div>
														<div>
															<span class="btn default btn-file">
															<span class="fileinput-new">
															Select image </span>
															<span class="fileinput-exists">
															Change </span>
															<input type="file" name="image" id="Image">
															</span>
															<a href="#" class="btn default fileinput-exists" data-dismiss="fileinput">
															Remove </a>
														</div>
													</div>
													<input type="hidden" name="oldImage" value="<?= $admindata['image']?>">
													<div class="clearfix margin-top-10">
														<span class="label label-danger">NOTE! </span>
														<span>&nbsp;&nbsp;Attached image thumbnail is supported in Latest Firefox, Chrome, Opera, Safari and Internet Explorer 10 only </span>
													</div>
												</div>
												<div class="margin-top-10">
													<input type="submit" class="btn green-haze" value="Submit">
												</div>
											</form>
										</div>
										<div class="tab-pane" id="tab_1_3">
											<form method="POST" action="{!! action('Admin\AdminController@changeAdminPassword') !!}">
											<input type="hidden" name="_token" value="{{ csrf_token() }}">
												<div class="form-group">
													<label class="control-label">Current Password</label>
													<input type="password" name="password" class="form-control"/>
												</div>
												<div class="form-group">
													<label class="control-label">New Password</label>
													<input type="password" name="new_password" class="form-control"/>
												</div>
												<div class="form-group">
													<label class="control-label">Re-type New Password</label>
													<input type="password" name="re_password" class="form-control"/>
												</div>
												<div class="margin-top-10">
													<input type="submit" class="btn green-haze" value="Change Password">
												</div>
											</form>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="page-footer">
	<div class="scroll-to-top">
		<i class="icon-arrow-up"></i>
	</div>
</div>
<style>
.has-feedback label ~ .form-control-feedback {
  top: 36px;
}
</style>
@stop