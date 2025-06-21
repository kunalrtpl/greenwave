<!DOCTYPE html>
<html lang="en" class="ie8 no-js">
<html lang="en" class="ie9 no-js">
<html lang="en">
	<head>
		<meta charset="utf-8"/>
		<title>Admin Login - {{config('constants.project_name')}}</title>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8">
		<meta name="csrf-token" content="{{ csrf_token() }}" />
		<meta name="theme-color" content="#ffffff">
		<link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
		<link rel="stylesheet" href="{{ URL::asset('css/backend_css/font-awesome/css/font-awesome.min.css') }}" />
		<link rel="stylesheet" href="{{ URL::asset('css/backend_css/bootstrap/css/bootstrap.min.css') }}" />
		<link rel="stylesheet" href="{{ URL::asset('css/backend_css/bootstrap/css/formValidation.min.css') }}" />
		<link rel="stylesheet" href="{{ URL::asset('css/backend_css/login-soft.css') }}" />
		<link rel="stylesheet" href="{{ URL::asset('css/backend_css/components-rounded.css') }}" />
		<link rel="stylesheet" href="{{ URL::asset('css/backend_css/layout.css') }}" />
		<!-- Login page styles ends here -->
	</head>
	<body class="login">
		<div class="logo">
			<a href="javascript:;">
				<img src="" alt=""/>
			</a>
		</div>
		<div class="menu-toggler sidebar-toggler">
		</div>
		@yield('content')
	<!-- Scripts starts from here -->
		<script src="{!! asset('js/backend_js/jquery.min.js') !!}" type="text/javascript"></script>
		<script src="{!! asset('js/backend_js/bootstrap/js/bootstrap.min.js') !!}" type="text/javascript"></script>
		<script src="{!! asset('js/backend_js/formValidation.min.js') !!}" type="text/javascript"></script>
		<script src="{!! asset('js/backend_js/Framework/bootstrap.js') !!}" type="text/javascript"></script>
		<script src="{!! asset('js/backend_js/jquery.backstretch.min.js') !!}" type="text/javascript"></script>
		<script src="{!! asset('js/backend_js/metronic.js') !!}" type="text/javascript"></script>
		<script src="{!! asset('js/backend_js/layout.js') !!}" type="text/javascript"></script>
		<script src="{!! asset('js/backend_js/demo.js') !!}" type="text/javascript"></script>
		<script type="text/javascript">
			jQuery(document).ready(function() {     
	  			Metronic.init(); // init metronic core components
				Layout.init(); // init current layout
	  			Demo.init();
	       		$.backstretch([
		       		'{{ URL::asset('/images/LoginImages/first.jpg') }}',
			        '{{ URL::asset('/images/LoginImages/1.jpg') }}',
			        '{{ URL::asset('/images/LoginImages/3.jpg') }}'
		        ], {
		         	fade: 1000,
		          	duration: 1000
		    		}
		    	);

    			$.ajaxSetup({
			        headers:{
			            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			        }
			    });

			    jQuery('#forget-password').click(function () {
			        jQuery('.login-form').hide();
			        jQuery('.forget-form').show();
			    });

			    jQuery('#back-btn').click(function () {
			        jQuery('.login-form').show();
			        jQuery('.forget-form').hide();
    			});

				$('#admin-login-form').formValidation({
			        framework: 'bootstrap',
			        excluded: [':disabled'],
			        message: 'This value is not valid',
			        icon:{
			            valid: 'glyphicon glyphicon-ok',
			            invalid: 'glyphicon glyphicon-remove',
			            validating: 'glyphicon glyphicon-refresh'
			        },
			        err:{
			            container: 'popover'
			        },
        			fields:{
            			"username":{
                			validators:{
                    			notEmpty:{
                        			message: 'Please enter username or email'
                    			}
                			}
            			},
			            "password":{
			                validators:{
			                    notEmpty:{
			                        message: 'Please enter your password'
			                    }
			                }
            			}
        			}
    			});
			});
    	</script>
	</body>	
</html>