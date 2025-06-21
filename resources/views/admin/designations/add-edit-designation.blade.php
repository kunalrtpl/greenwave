@extends('layouts.adminLayout.backendLayout')
@section('content')
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-head">
            <div class="page-title">
                <h1>Designation Management </h1>
            </div>
        </div>
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{ url('admin/dashboard') }}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{ url('admin/designations') }}">Designations </a>
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
                        <form id="DesignationForm" role="form" class="form-horizontal" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                <div class="form-group">
                                    <?php $depts = departments();
                                    ?>
                                    <label class="col-md-3 control-label">Department <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <select class="form-control select2" name="department_id">
                                            <option value="">Please Select</option>
                                            @foreach($depts as $deptinfo)
                                                <option value="{{$deptinfo['id']}}" {{(!empty($designationdata['department_id']) && $designationdata['department_id']== $deptinfo['id'] )?'selected': '' }}>{{$deptinfo['department']}}</option>
                                            @endforeach
                                        </select>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Designation-department_id"></h4>
                                    </div>
                                </div>
                                <div id="ParentDesignation">
                                    @if(!empty($designationdata) && !empty($designationdata['parent_id']))
                                        @include('admin.designations.parent-designation')
                                    @endif
                                </div> 
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Designation <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Designation Name" name="designation" style="color:gray" class="form-control" value="{{(!empty($designationdata['designation']))?$designationdata['designation']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Designation-designation"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Incentive Applicable?</label>
                                    <div class="col-md-4">
                                        <input  type="checkbox" name="incentive_applicable" style="color:gray; margin-top: 9px;" {{(!empty($designationdata['incentive_applicable']) && $designationdata['incentive_applicable'] =="1" )?'checked': '' }} value="1" />
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Designation-incentive_applicable"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Type <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <?php $typeArr = array('none','region','products'); ?>
                                        <select class="form-control" name="type">
                                            @foreach($typeArr as $type)
                                                <option value="{{$type}}" @if(!empty($designationdata['type']) && $designationdata['type']==$type) selected @endif>{{ucwords($type)}}</option>
                                            @endforeach
                                        </select>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Designation-having_region"></h4>
                                    </div>
                                </div> 
                                <div id="MultipleSubRegion" @if(!empty($designationdata) && $designationdata['type']=='region') @else style="display:none;" @endif>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Multiple Region?</label>
                                        <div class="col-md-4">
                                            <input  type="checkbox"  name="multiple_region" style="color:gray; margin-top: 9px;" {{(!empty($designationdata['multiple_region']) && $designationdata['multiple_region'] =="1" )?'checked': '' }} value="1"/>
                                            <h4 class="text-center text-danger pt-3" style="display: none;" id="Designation-multiple_region"></h4>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Multiple Sub Region?</label>
                                        <div class="col-md-4">
                                            <input  type="checkbox" name="multiple_sub_region" style="color:gray; margin-top: 9px;" {{(!empty($designationdata['multiple_sub_region']) && $designationdata['multiple_sub_region'] =="1" )?'checked': '' }} value="1" />
                                            <h4 class="text-center text-danger pt-3" style="display: none;" id="Designation-multiple_sub_region"></h4>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                    <label class="col-md-3 control-label">Customer Selection?</label>
                                    <div class="col-md-4">
                                        <input  type="checkbox" name="having_customer" style="color:gray; margin-top: 9px;" {{(!empty($designationdata['having_customer']) && $designationdata['having_customer'] =="1" )?'checked': '' }} value="1" />
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Designation-having_customer"></h4>
                                    </div>
                                </div>     
                                </div>
                            </div>
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
    var designationid =""; 
    <?php if(!empty($designationdata['id'])){?>
        designationid = "<?php echo $designationdata['id']; ?>";
    <?php } ?>


    $(document).on('change','[name=department_id]',function(){
        $('.loadingDiv').show();
        var deptid = $(this).val();
        $.ajax({
            data : {department_id:deptid},
            type : 'POST',
            url  : '/admin/get-dept-designation',
            success:function(resp){
                $('#ParentDesignation').html(resp.view);
                refreshSelect2();
                $('.loadingDiv').hide();
            }
        })
    })

    $(document).on('change','[name=type]',function(){
        if ($(this).val()=='region'){ 
            $('#MultipleSubRegion').show();
        }else{
            $('#MultipleSubRegion').hide();
            $('[name=multiple_region]').prop('checked', false); 
            $('[name=multiple_sub_region]').prop('checked', false); 
            $('[name=having_customer]').prop('checked', false); 
        }
    })

    $("#DesignationForm").submit(function(e){
        $('.loadingDiv').show();
        e.preventDefault();
        var formdata = $("#DesignationForm").serialize()+"&designationid="+designationid;;
        $.ajax({
            url: '/admin/save-designation',
            type:'POST',
            data: formdata,
            success: function(data) {
                $('.loadingDiv').hide();
                if(!data.status){
                    $.each(data.errors, function (i, error) {
                        $('#Designation-'+i).addClass('error-triggered');
                        $('#Designation-'+i).attr('style', '');
                        $('#Designation-'+i).html(error);
                        setTimeout(function () {
                            $('#Designation-'+i).css({
                                'display': 'none'
                            });
                        $('#Designation-'+i).removeClass('error-triggered');
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
</script>
@endsection