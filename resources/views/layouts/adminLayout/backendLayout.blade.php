<!DOCTYPE html>
<html lang="en" class="ie8 no-js">
<html lang="en" class="ie9 no-js">
<html lang="en">
	<head>
    	<meta charset="utf-8"/>
	    <title>@if(isset($title)) {{$title}} - {{config('constants.project_name')}} @endif</title>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8">
		<meta content="" name="description"/>
		<meta content="" name="author"/>
		<meta name="csrf-token" content="{{ csrf_token() }}" />
		<meta name="theme-color" content="#ffffff">
	    <!-- styles Starts -->
		<link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
		<link rel="stylesheet" href="{{ URL::asset('css/backend_css/font-awesome/css/font-awesome.min.css') }}" />
		<link rel="stylesheet" href="{{ URL::asset('css/backend_css/simple-line-icons/simple-line-icons.min.css') }}" />
		<link rel="stylesheet" href="{{ URL::asset('css/backend_css/bootstrap/css/bootstrap.min.css') }}" />
		<link rel="stylesheet" href="{{ URL::asset('css/backend_css/bootstrap/css/formValidation.min.css') }}" />
		<link href="{!! asset('css/backend_css/bootstrap-switch.min.css') !!}" rel="stylesheet" type="text/css"/>
		<link href="{!! asset('css/backend_css/bootstrap-fileinput.css') !!}" rel="stylesheet" type="text/css"/>
		<link rel="stylesheet" href="{{ URL::asset('css/backend_css/tasks.css') }}" />
		<link rel="stylesheet" href="{{ URL::asset('css/backend_css/components-rounded.css') }}" />
		<link rel="stylesheet" href="{{ URL::asset('css/backend_css/plugins.css') }}" />
		<link rel="stylesheet" href="{{ URL::asset('css/backend_css/layout.css') }}" />
		<link rel="stylesheet" href="{{ URL::asset('css/backend_css/light.css') }}" />
		<link rel="stylesheet" href="{{ URL::asset('css/backend_css/custom.css') }}" />
		<link rel="stylesheet" href="{{ URL::asset('css/backend_css/profile.css') }}" />
		<link rel="stylesheet" href="{{ URL::asset('css/backend_css/datepicker.min.css') }}" />
		<link rel="stylesheet" href="{{ asset('css/backend_css/select2.min.css')}}" />
		<link rel="stylesheet" href="{{ URL::asset('css/backend_css/admin.css') }}" />
		<!-- styles ends here -->
		<script src="{!! asset('js/backend_js/jquery.min.js') !!}" type="text/javascript"></script>
		<script src="{!! asset('js/backend_js/bootstrap.min.js') !!}" type="text/javascript"></script>
		<script src="{!! asset('js/backend_js/bootstrap-hover-dropdown.min.js') !!}" type="text/javascript"></script>
		<script src="{!! asset('js/backend_js/jquery.slimscroll.min.js') !!}" type="text/javascript"></script>
		<script src="{!! asset('js/backend_js/jquery.blockui.min.js') !!}" type="text/javascript"></script>
		<script src="{!! asset('js/backend_js/formValidation.min.js') !!}" type="text/javascript"></script>
		<script src="{!! asset('js/backend_js/Framework/bootstrap.js') !!}" type="text/javascript"></script>
		<script src="{!! asset('js/backend_js/jquery.cokie.min.js') !!}" type="text/javascript"></script>
		<script src="{!! asset('js/backend_js/bootstrap-switch.min.js') !!}" type="text/javascript"></script>
		<script src="{!! asset('js/backend_js/bootstrap-fileinput.js') !!}" type="text/javascript"></script>
		<script src="{!! asset('js/backend_js/jquery.dataTables.min.js') !!}" type="text/javascript"></script>
		<script src="{!! asset('js/backend_js/dataTables.bootstrap.js') !!}" type="text/javascript"></script>
		<script src="{!! asset('js/backend_js/datatable.js') !!}" type="text/javascript"></script>
		<script src="{!! asset('js/backend_js/table-ajax.js') !!}" type="text/javascript"></script>
		<script src="{!! asset('js/backend_js/metronic.js') !!}" type="text/javascript"></script>
		<script src="{!! asset('js/backend_js/layout.js') !!}" type="text/javascript"></script>
		<script src="{!! asset('js/backend_js/demo.js') !!}" type="text/javascript"></script>
		<script src="{!! asset('js/backend_js/tasks.js') !!}" type="text/javascript"></script>
		<script src="{{ asset('js/backend_js/bootstrap-select.min.js')}}" type="text/javascript"></script>

		<script src="{!! asset('js/backend_js/datepicker.min.js') !!}" type="text/javascript"></script>
		<script src="{!! asset('js/backend_js/select2.min.js') !!}" type="text/javascript"></script>
		
	</head>
	<style type="text/css">
		.asteric{
			color: red;
		}
		/* Chrome, Safari, Edge, Opera */
		input::-webkit-outer-spin-button,
		input::-webkit-inner-spin-button {
		  -webkit-appearance: none;
		  margin: 0;
		}

		/* Firefox */
		input[type=number] {
		  -moz-appearance: textfield;
		}
		.bold-hr {
		    border: none; /* Removes the default border */
		    height: 5px; /* Sets the thickness */
		    background-color: black; /* Sets the color */
		    margin: 20px 0; /* Adjusts spacing above and below */
		}
		.red{
			color: red;
		}
		.dark-line {
		    border: none;
		    height: 2px;
		    background-color: #333;
		}
		.highlight-label {
	        font-weight: 600;
	        background-color: #000;  /* black background */
	        color: #fff;             /* white text */
	        padding: 4px 8px;
	        display: inline-block;
	        border-radius: 4px;
	    }
	    .highlight-sub-label {
		    font-weight: 600;
		    background-color: #ffe082;   /* soft yellow highlight */
		    font-size:  11px;
		    padding: 4px 8px;
		    display: inline-block;
		    border-radius: 4px;
		}

	</style>
	<body class="page-header-fixed page-sidebar-closed-hide-logo page-sidebar-closed-hide-logo">
		@include('layouts.adminLayout.adminheader')
		<div class="clearfix">
		</div>
		<div class="page-container">
			@include('layouts.adminLayout.adminsidebar')
			@yield('content')
		</div>
		@include('layouts.adminLayout.admin-footer')
		<div class="loadingDiv" style="display:none;">
		</div>
	</body>
</html>
   <!-- The core Firebase JS SDK is always required and must be listed first -->
    <script src="https://www.gstatic.com/firebasejs/8.4.2/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/7.16.1/firebase-messaging.js"></script>
    <script>
    var firebaseConfig = {
        apiKey: "AIzaSyB-LrF0UK-pbDS5NB_kBJOg0dcYnTrvHeM",
        authDomain: "coral-arbor-159107.firebaseapp.com",
        projectId: "coral-arbor-159107",
        storageBucket: "coral-arbor-159107.appspot.com",
        messagingSenderId: "354914230669",
        appId: "1:354914230669:web:15e6b5d591ae42f4ef9dc4",
        measurementId: "G-EG9LWLDJ07"
    };
    firebase.initializeApp(firebaseConfig);

    const messaging = firebase.messaging();
    messaging
        .requestPermission()
        .then(function () {
            console.log('Notification permission granted.');
            // get the token in the form of promise
            return messaging.getToken();
        })
        .then(function (token) {
            saveNotificationToken(token);
        })
        .catch(function (err) {
            console.log('Unable to get permission to notify.', err);
        });
        let enableForegroundNotification = true;
        messaging.onMessage(function (payload) {
        console.log('Message received. ', payload);
        if (enableForegroundNotification) {
            let notification = payload.notification;
            const notificationOptions = {
                body: notification.body,
                icon: notification.icon,
                click_action: notification.click_action,
                data : {url:notification.click_action},
                actions: [{action: "open_url",title: "Read Now"}]
            };
            navigator.serviceWorker
                .getRegistrations()
                .then((registration) => {
                    registration[0].showNotification(notification.title,notificationOptions);
                });
        }
    });
    </script>
    <script type="text/javascript">
        function saveNotificationToken(currentToken) {
            $.ajax({
                data : {notification_token:currentToken, "_token":"{{ csrf_token() }}"},
                url : "{{url('admin/save-notification-token')}}",
                type : 'POST',
                success:function(resp){
                },
                error:function(){
                    //alert('Error');
                }
            })
        }
    </script>
    <!-- <script type="text/javascript">
    	if (Notification.permission === 'granted') {
		    //do something
		    $('#PushNotificationModal').hide();
		    $('#allow-push-notification-bar').hide();
		}else if (Notification.permission === 'default'){
			$('#PushNotificationModal').modal('show');
		    $('#allow-push-notification-bar').show();
		}
		$('#allow-push-notification').click(function () {
		    $('#allow-push-notification-bar').hide();
		    Notification.requestPermission().then(function (status) {
		        if (status === 'denied') {
		        	alert('You have denied the notification from us. if you hit mistakenly and want to allow notification go to browser setting and allow it.');
		            //do something
		        }else if (status === 'granted') {
		        	$('#PushNotificationModal').modal('hide');
		           	messaging.requestPermission()
			        .then(function () {
			            console.log('Notification permission granted.');
			            // get the token in the form of promise
			            return messaging.getToken();
			        })
			        .then(function (token) {
			            saveNotificationToken(token);
			        })
			        .catch(function (err) {
			            console.log('Unable to get permission to notify.', err);
			        });
		        }
		    });
		});
    </script> -->