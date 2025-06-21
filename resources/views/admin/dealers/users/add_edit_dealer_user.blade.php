@extends('layouts.adminLayout.backendLayout')
@section('content')
 <?php $designationsArr = array('Owner','Manager') ?>
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-head">
            <div class="page-title">
                <h1>Dealers Management </h1>
            </div>
        </div>
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{ url('admin/dashboard') }}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{ url('admin/dealers') }}">Dealers </a>
            </li>
        </ul>
        <div class="row">
            <div class="col-md-12 ">
                <div class="portlet blue-hoki box ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-gift"></i>{{ $title }}
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form id="DealerForm" role="form" class="form-horizontal" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <input type="hidden" name="dealer_type" value="dealer">
                            <input type="hidden" name="parent_id" value="{{$parentDealerId}}">
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Name <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Name" name="name" style="color:gray" class="form-control" value="{{(!empty($dealerdata['name']))?$dealerdata['name']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Dealer-name"></h4>
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Department</label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Department" name="department" style="color:gray" class="form-control" value="{{(!empty($dealerdata['department']))?$dealerdata['department']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Dealer-department"></h4>
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Designation</label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Designation" name="designation" style="color:gray" class="form-control" value="{{(!empty($dealerdata['designation']))?$dealerdata['designation']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Dealer-designation"></h4>
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Mobile <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Mobile" name="owner_mobile" style="color:gray" class="form-control" value="{{(!empty($dealerdata['owner_mobile']))?$dealerdata['owner_mobile']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Dealer-owner_mobile"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Email</label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Email" name="email" style="color:gray" class="form-control" value="{{(!empty($dealerdata['email']))?$dealerdata['email']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Dealer-email"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Status <span class="asteric">*</span></label>
                                    <div class="col-md-4" style="margin-top:8px;">
                                        <?php $statusArr = array('1'=>'Active','0'=>'Inactive') ?>
                                        @foreach($statusArr as $skey=> $status)
                                            <label>
                                            <input type="radio" name="status" value="{{$skey}}" @if(!empty($dealerdata) && $dealerdata['status'] ==$skey ) checked @else @if($skey ==1) checked @endif @endif />&nbsp;{{ucwords($status)}}&nbsp;
                                        </label>
                                        @endforeach
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="User-status"></h4>
                                    </div>
                                </div>
                                @if($parentShowClass == "Yes")
                                <div class="form-group">
                                    <label class="col-md-3 control-label"> Show Class <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <select class="form-control" name="show_class" required>
                                            <option value="">Please Select</option>
                                            @foreach(classes() as $pkey=> $showclass)
                                                <option value="{{$showclass}}" @if(empty($dealerdata) ) @if($pkey==1) selected @endif @else @if($dealerdata['show_class'] ==$showclass) selected @endif @endif>{{$showclass}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                @endif
<div class="form-group">
    <label class="col-md-3 control-label">Modules to Access? <b class="red">({{count($selAppRoles)}})</b> <span class="asteric">*</span></label>
    <div class="col-md-6">
        <div class="panel-group" id="accordion-module">
            <div class="panel panel-default">
                <div class="panel-heading text-center">
                    <h4 class="panel-title">
                        <a style="text-decoration:none;" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-module" href="#collapseTwo">
                        </a>
                    </h4>
                </div>
                <div id="collapseTwo" class="panel-collapse collapse">
                    <div class="panel-body">
                        <table class="table table-bordered">
                            <tr>
                                <th>Sr. No.</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                            @foreach($appRoles as $pkey=> $role)
                                <tr>
                                    <td>{{++$pkey}}</td>
                                   <td>
                                       {{$role['name_admin']}}
                                   </td> 
                                   <td>
                                       <input type="checkbox" name="app_roles[]" value="{{$role['key']}}" @if(in_array($role['key'],$selAppRoles)) checked @endif>
                                   </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
                            </div>
                            @if(!empty($dealerdata['id']))
                                <input type="hidden" name="dealerid" value="{{$dealerdata['id']}}">
                            @else
                                <input type="hidden" name="dealerid" value="">
                            @endif
                            <div class="form-actions right1 text-center">
                                <button class="btn green" type="submit">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        $("#DealerForm").submit(function(e){
            $('.loadingDiv').show();
            e.preventDefault();
            var formdata = new FormData(this);
            $.ajax({
                url: '/admin/save-dealer-user',
                type:'POST',
                data: formdata,
                processData: false,
                contentType: false,
                success: function(data) {
                    $('.loadingDiv').hide();
                    if(!data.status){
                        $.each(data.errors, function (i, error) {
                            $('#Dealer-'+i).addClass('error-triggered');
                            $('#Dealer-'+i).attr('style', '');
                            $('#Dealer-'+i).html(error);
                            setTimeout(function () {
                                $('#Dealer-'+i).css({
                                    'display': 'none'
                                });
                            $('#Dealer-'+i).removeClass('error-triggered');
                            }, 5000);
                        });
                        $('html,body').animate({
                            scrollTop: $('.error-triggered').first().stop().offset().top - 200
                        }, 1000);
                    }else{
                        window.location.href= data.url;
                    }
                }
            });
        });
    })
</script>
<style>
    .panel-heading .accordion-toggle:after {
    /* symbol for "opening" panels */
    font-family: 'Glyphicons Halflings';  /* essential for enabling glyphicon */
    content: "\e114";    /* adjust as needed, taken from bootstrap.css */
    float: left;        /* adjust as needed */
    color: #4a8c17 !important;         /* adjust as needed */
}
.panel-heading .accordion-toggle.collapsed:after {
    /* symbol for "collapsed" panels */
    content: "\e080";    /* adjust as needed, taken from bootstrap.css */
}
.panel-default>.panel-heading {
    background-color: transparent !important;
    height: 40px;
}
.panel-heading .accordion-toggle:after
{
    color:#fff;
}
.panel-title>a:hover
{
    color:#fff;
}

</style>
@endsection