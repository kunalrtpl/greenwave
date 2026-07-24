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
                                        <input  type="text" placeholder="Email" name="email" id="dealerEmailInput" style="color:gray" class="form-control" value="{{(!empty($dealerdata['email']))?$dealerdata['email']: '' }}"/>
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

                                {{-- ===== MODULES TO ACCESS ===== --}}
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

                                {{-- ===== EMAIL TEMPLATES SECTION ===== --}}
                                @if(!empty($dealerEmailTemplates) && count($dealerEmailTemplates) > 0)
                                <div class="form-group" id="emailNotificationsSection" style="display:none;">
                                    <label class="col-md-3 control-label">
                                        Email Notifications
                                        <b class="red">({{ count($selEmailTemplates) }})</b>
                                        <span class="asteric">*</span>
                                    </label>
                                    <div class="col-md-6">
                                        <div class="panel-group" id="accordion-email">
                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <h4 class="panel-title" style="display:flex; align-items:center; justify-content:space-between;">
                                                        <a style="text-decoration:none; flex:1;" 
                                                           class="accordion-toggle collapsed" 
                                                           data-toggle="collapse" 
                                                           data-parent="#accordion-email" 
                                                           href="#collapseEmailTemplates">
                                                            Click to select email notifications for this dealer
                                                        </a>
                                                        <span>
                                                            <button type="button" class="btn btn-xs btn-success" id="selectAllEmails" style="margin-right:4px;">
                                                                <i class="fa fa-check-square-o"></i> All
                                                            </button>
                                                            <button type="button" class="btn btn-xs btn-default" id="deselectAllEmails">
                                                                <i class="fa fa-square-o"></i> None
                                                            </button>
                                                        </span>
                                                    </h4>
                                                </div>
                                                <div id="collapseEmailTemplates" class="panel-collapse collapse @if(count($selEmailTemplates) > 0) in @endif">
                                                    <div class="panel-body" style="padding:0;">
                                                        <table class="table table-bordered table-hover" style="margin-bottom:0;">
                                                            <thead>
                                                                <tr>
                                                                    <th style="width:50px;">Sr.</th>
                                                                    <th>Template Name</th>
                                                                    <th>Event Key</th>
                                                                    <th>Subject</th>
                                                                    <th style="width:60px; text-align:center;">Enable</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($dealerEmailTemplates as $tkey => $template)
                                                                <tr class="email-template-row @if(in_array($template['id'], $selEmailTemplates)) active-template @endif"
                                                                    style="cursor:pointer;"
                                                                    onclick="toggleEmailTemplate({{ $template['id'] }})">
                                                                    <td>{{ $tkey + 1 }}</td>
                                                                    <td>
                                                                        <strong>{{ $template['name'] }}</strong>
                                                                    </td>
                                                                    <td>
                                                                        <code style="font-size:11px;">{{ $template['event_key'] }}</code>
                                                                    </td>
                                                                    <td style="font-size:12px; color:#666;">
                                                                        {{ \Illuminate\Support\Str::limit($template['subject'], 50) }}
                                                                    </td>
                                                                    <td style="text-align:center;" onclick="event.stopPropagation();">
                                                                        <input type="checkbox" 
                                                                               class="email-template-checkbox" 
                                                                               name="email_templates[]" 
                                                                               value="{{ $template['id'] }}"
                                                                               id="et_{{ $template['id'] }}"
                                                                               @if(in_array($template['id'], $selEmailTemplates)) checked @endif
                                                                               onchange="updateEmailTemplateCount()">
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Dealer-email_templates"></h4>
                                    </div>
                                </div>
                                @endif
                                {{-- ===== END EMAIL TEMPLATES SECTION ===== --}}

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
    // ===== Email Template helpers =====
    function toggleEmailTemplate(id) {
        var cb = document.getElementById('et_' + id);
        cb.checked = !cb.checked;
        updateEmailTemplateCount();
    }

    function updateEmailTemplateCount() {
        var checked = document.querySelectorAll('.email-template-checkbox:checked').length;
        var labels = document.querySelectorAll('.control-label b.red');
        if (labels.length >= 2) {
            labels[labels.length - 1].textContent = '(' + checked + ')';
        }
        document.querySelectorAll('.email-template-row').forEach(function(row) {
            var cb = row.querySelector('.email-template-checkbox');
            if (cb && cb.checked) {
                row.classList.add('active-template');
            } else {
                row.classList.remove('active-template');
            }
        });
    }

    document.getElementById('selectAllEmails') && document.getElementById('selectAllEmails').addEventListener('click', function(e) {
        e.stopPropagation();
        document.querySelectorAll('.email-template-checkbox').forEach(function(cb) { cb.checked = true; });
        updateEmailTemplateCount();
    });

    document.getElementById('deselectAllEmails') && document.getElementById('deselectAllEmails').addEventListener('click', function(e) {
        e.stopPropagation();
        document.querySelectorAll('.email-template-checkbox').forEach(function(cb) { cb.checked = false; });
        updateEmailTemplateCount();
    });

    // ===== Show/Hide Email Notifications based on email field =====
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim());
    }

    function toggleEmailNotificationsSection() {
        var emailVal = $('#dealerEmailInput').val();
        if (isValidEmail(emailVal)) {
            $('#emailNotificationsSection').slideDown(200);
        } else {
            $('#emailNotificationsSection').slideUp(200);
        }
    }

    // ===== Form Submit =====
    $(document).ready(function(){
        updateEmailTemplateCount();

        // Check on page load (handles edit mode where email is already filled)
        toggleEmailNotificationsSection();

        // Check on every keystroke / paste / autofill
        $('#dealerEmailInput').on('input keyup change paste', function() {
            // Small delay to allow paste to complete
            setTimeout(toggleEmailNotificationsSection, 100);
        });

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
                                $('#Dealer-'+i).css({'display': 'none'});
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
    });
</script>

<style>
    /* ===== Existing accordion styles ===== */
    .panel-heading .accordion-toggle:after {
        font-family: 'Glyphicons Halflings';
        content: "\e114";
        float: left;
        color: #4a8c17 !important;
    }
    .panel-heading .accordion-toggle.collapsed:after {
        content: "\e080";
    }
    .panel-default>.panel-heading {
        background-color: transparent !important;
        min-height: 40px;
        height: auto;
    }
    .panel-heading .accordion-toggle:after { color:#fff; }
    .panel-title>a:hover { color:#fff; }

    /* ===== Email templates table styles ===== */
    .email-template-row:hover {
        background-color: #f0f7ff !important;
    }
    .email-template-row.active-template {
        background-color: #e8f5e9 !important;
    }
    .email-template-row.active-template td {
        color: #2e7d32;
    }
    #accordion-email .panel-heading {
        padding: 8px 12px;
    }
    #accordion-email .panel-heading h4 {
        font-size: 13px;
    }
    #accordion-email .panel-heading a {
        color: #555;
    }
    #accordion-email .panel-heading a:hover {
        color: #333;
    }
    .email-template-checkbox {
        width: 16px;
        height: 16px;
        cursor: pointer;
    }
</style>
@endsection