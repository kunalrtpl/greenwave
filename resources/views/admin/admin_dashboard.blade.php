@extends('layouts.adminLayout.backendLayout')
@section('content')
<div class="page-content-wrapper">
	<div class="page-content">
		<div class="page-head">
			<div class="page-title">
				<h1>Dashboard</h1>
			</div>
		</div>
		@if(Session::has('flash_message_error'))
            <div role="alert" class="alert alert-danger alert-dismissible fade in"> <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"><span aria-hidden="true"></span></button> <strong>Error!</strong> {!! session('flash_message_error') !!} </div>
        @endif
		<div class="row margin-top-10">
			@foreach($getModules as $key => $module)
			<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
				<div class="dashboard-stat2">
					<div class="display">
						<div class="number">
							<h3 class="font-purple-soft">{!!$module['table_count']!!}</h3>
							<small>{{$module['name']}}</small>
						</div>
						<div class="icon">
							<i class="{{$module['icon']}}"></i>
						</div>
					</div>
					<div class="progress-info">
	                    <div class="status">
	                        <div class="status-title">
	                            <a href="{{url($module['view_route'])}}">View More Details</a>
	                        </div>
	                    </div>
	                </div>
				</div>
			</div>
			@endforeach
		</div>
	</div>
</div>
@endsection