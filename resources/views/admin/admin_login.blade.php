@extends('layouts.adminLayout.admin_login')
@section('content')
<div class="content">
	<form id="admin-login-form" class="login-form" action="{{ action('Admin\AdminController@emaillogin')}}" method="post">
		@include('common.errors')
		@if(Session::has('flash_message_error'))
			<div role="alert" class="alert alert-danger alert-dismissible fade in"> <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"></button> <strong>Error!</strong> {!! session('flash_message_error') !!} </div>
		@endif
		@if(Session::has('flash_message_success'))
		    <div role="alert" class="alert alert-success alert-dismissible fade in"> <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"></button> <strong>Success!</strong> {!! session('flash_message_success') !!} </div>
		@endif
		<input type="hidden" name="_token" value="{{ csrf_token() }}">
		<h3 class="form-title">Login to your account</h3>
		<div class="form-group">
			<label class="control-label visible-ie8 visible-ie9">Username</label>
			<div class="input-icon">
				<i class="fa fa-user"></i>
				<input class="form-control placeholder-no-fix" type="text"  placeholder="Email" name="email"/>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label visible-ie8 visible-ie9">Password</label>
			<div class="input-icon">
				<i class="fa fa-lock"></i>
				<input class="form-control placeholder-no-fix" type="password" placeholder="Password" name="password"/>
			</div>
		</div>
		<div class="form-actions">
			<?php /*<label class="checkbox">
			@if(isset($stayTuned))
				<input type="checkbox" name="remember" value="1" checked="checked"/>
			@else
				<input type="checkbox" name="remember" value="1"/>
			@endif Remember me </label>>*/?>
			<button type="submit" class="btn blue pull-right">
			Login <i class="m-icon-swapright m-icon-white"></i>
			</button>
		</div>
		<div class="forget-password">
			<h4>Forgot your password ?</h4>
			<p>
				 no worries, click <a href="javascript:;">
				here </a>
				to reset your password.
			</p>
		</div>
	</form>
	<form class="forget-form" id="admin-forget-form" action="javascript:;" method="post">
		<input type="hidden" name="_token" value="{{ csrf_token() }}">
		<h3>Forget Password ?</h3>
		<p>
			 Enter your e-mail address below to reset your password.
		</p>
		<div class="form-group">
			<div class="input-icon">
				<i class="fa fa-envelope"></i>
				<input class="form-control placeholder-no-fix" type="text" autocomplete="off" placeholder="Email" name="email"/>
			</div>
		</div>
		<div class="form-actions">
			<button type="button" id="back-btn" class="btn">
			<i class="m-icon-swapleft"></i> Back </button>
			<button type="submit" class="btn blue pull-right">
			Submit <i class="m-icon-swapright m-icon-white"></i>
			</button>
		</div>
	</form>
</div>
<div class="copyright">
	 <?php echo date('Y'); ?> &copy; {{config('constants.project_name')}}
</div>
@endsection
