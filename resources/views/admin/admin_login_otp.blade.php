@extends('layouts.adminLayout.admin_login')
@section('content')

<div class="content">
	<form id="admin-login-form" class="login-form" method="POST" action="{{ url('/admin') }}">
		@csrf

		<h3 class="form-title">Login with Mobile OTP</h3>

		@include('common.errors')
		@if(Session::has('flash_message_error'))
			<div role="alert" class="alert alert-danger alert-dismissible fade in"> <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"></button> <strong>Error!</strong> {!! session('flash_message_error') !!} </div>
		@endif
		@if(Session::has('flash_message_success'))
		    <div role="alert" class="alert alert-success alert-dismissible fade in"> <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"></button> <strong>Success!</strong> {!! session('flash_message_success') !!} </div>
		@endif
		{{-- Mobile Input --}}
		<div class="form-group">
			<label class="control-label">Mobile Number</label>
			<div class="input-icon">
				<i class="fa fa-mobile"></i>
				<input type="text" class="form-control" name="mobile" id="mobile" placeholder="Enter mobile number" required  value="{{ old('mobile') }}" >
			</div>
			<button type="button" id="send-otp-btn" class="btn btn-sm btn-info" style="margin-top: 10px;">Send OTP</button>
			<strong><div id="otp-message" class="text-success" style="margin-top: 5px; display: none; color: #FFA500;"></div></strong>
		</div>

		{{-- OTP Input --}}
		<div class="form-group">
			<label class="control-label">OTP</label>
			<div class="input-icon">
				<i class="fa fa-key"></i>
				<input type="text" class="form-control" name="otp" id="otp" placeholder="Enter OTP" required>
			</div>
		</div>

		{{-- Submit --}}
		<div class="form-actions">
			<button type="submit" class="btn blue pull-right">Login <i class="m-icon-swapright m-icon-white"></i></button>
		</div>
	</form>
</div>

<div class="copyright">
	{{ date('Y') }} &copy; {{ config('constants.project_name') }}
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
	const sendOtpBtn = document.getElementById('send-otp-btn');
	const mobileInput = document.getElementById('mobile');
	const otpMsg = document.getElementById('otp-message');

	sendOtpBtn.addEventListener('click', function () {
		const mobile = mobileInput.value.trim();

		if (!/^\d{10}$/.test(mobile)) {
			alert('Please enter a valid 10-digit mobile number.');
			return;
		}

		sendOtpBtn.disabled = true;
		otpMsg.style.display = 'none';

		fetch('{{ route("admin.send.otp") }}', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-CSRF-TOKEN': '{{ csrf_token() }}'
			},
			body: JSON.stringify({ mobile })
		})
		.then(res => res.json())
		.then(data => {
			sendOtpBtn.disabled = false;
			if (data.status) {
				otpMsg.innerText = data.message;
				otpMsg.style.display = 'block';
			} else {
				alert(data.message);
			}
		})
		.catch(() => {
			sendOtpBtn.disabled = false;
			alert('Error sending OTP. Please try again.');
		});
	});
});
</script>

@endsection
