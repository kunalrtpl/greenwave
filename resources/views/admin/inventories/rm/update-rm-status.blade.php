@extends('layouts.adminLayout.backendLayout')
@section('content')
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-head">
            <div class="page-title">
                <h1>Inventory Management </h1>
            </div>
        </div>
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{ url('admin/dashboard') }}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
        </ul>
        @if(isset($_GET['m']))
            <div role="alert" class="alert alert-success alert-dismissible fade in"> <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"><span aria-hidden="true"></span></button> <strong>Success!</strong> {!! $_GET['m'] !!} </div>
        @endif
        <div class="row">
            <div class="col-md-12 ">
                <div class="portlet blue-hoki box ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-gift"></i>{{ $title }}
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form id="updateRmStatus" role="form" class="form-horizontal" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Current Track Status </label>
                                    <div class="col-md-4">
                                        <p class="form-control">
                                            <a data-rminvid="{{$rmdetails['id']}}" href="javascript:;" class="rmtracking"><span class="badge badge-success">{{$rmdetails['status']}}</span></a>
                                        </p>
                                    </div>
                                </div> 
                                <?php $submitBtnVal = 'Submit'; ?>
                                @if($rmdetails['status'] =='Incoming Material')
                                    <?php $submitBtnVal = 'Sample Sent to Lab'; ?>
                                    <input type="hidden" name="status" value="{{$submitBtnVal}}">
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">No. of Samples <span class="asteric">*</span></label>
                                        <div class="col-md-4">
                                            <select class="form-control" name="no_of_samples">
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                            </select>
                                            <h4 class="text-center text-danger pt-3" style="display: none;" id="Inventory-no_of_samples"></h4>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Packing Type <span class="asteric">*</span></label>
                                        <div class="col-md-4">
                                            <?php $packing_types = \App\PackingType::packing_types('lab_sample');
                                            ?>
        <select class="form-control select2" name="packing_type_id" required>
            <option value="">Please Select</option>
            @foreach($packing_types as $packingType)
                <option value="{{$packingType['id']}}">{{$packingType['name']}}</option>
            @endforeach
        </select>
        <h4 class="text-center text-danger pt-3" style="display: none;" id="Inventory-packing_type_id"></h4>
                                        </div>
                                    </div> 
                                @elseif($rmdetails['status'] =='Sample Sent to Lab')
                                    <?php $submitBtnVal = 'Sample Received by Lab'; ?>
                                    <input type="hidden" name="status" value="{{$submitBtnVal}}">
                                @elseif($rmdetails['status'] =='Sample Received by Lab')
                                <?php $submitBtnVal = 'QC Process Initiated'; ?>
                                    <input type="hidden" name="status" value="{{$submitBtnVal}}">
                                @elseif($rmdetails['status'] =='QC Process Initiated')
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Download</label>
                                        <div class="col-md-4">
                                            <p class="form-control"><a href="{{url('/admin/inventory/rm-pdf/'.$rmdetails['id'].'/unfilled')}}">Click here to download</a></p>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Details</label>
                                        <div class="col-md-9">
                                            <table class="table table-bordered">
                                                <tbody>
                                                    <th>Serial No</th>
                                                    <th>No. of Samples</th>
                                                    <th>No. of Packs</th>
                                                    <th>Qty (kgs)</th>
                                                    <th>Coding</th>
                                                    <th>Sent to Lab</th>
                                                </tbody>
                                                <tr>
                                                    <td>{{$rmdetails['serial_no']}}</td>
                                                    <td>{{$rmdetails['no_of_samples']}}</td>
                                                    <td>{{$rmdetails['no_of_packs']}}</td>
                                                    <td>{{$rmdetails['stock']}}</td>
                                                    <td>{{$rmdetails['rawmaterial']['coding']}}</td>
                                                    <td>{{date('d M Y h:iA',strtotime($rmdetails['rm_history'][0]['created_at']))}}<br>
                                                        ({{$rmdetails['rm_history'][0]['updateby']['name']}})
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
@foreach(qc_checklists() as $ckey=> $checklist)
    @if(!empty($checklist['subchecklists']))
        <div class="form-group">
            <div class="col-sm-12">
                <div class="panel-group" id="accordion{{$ckey}}">
                    <div class="panel panel-default">
                        <div style="background-color: #3e7674;color:#fff;" class="panel-heading text-center">
                            <h4 class="panel-title">
                            <a style="text-decoration:none;" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion{{$ckey}}" href="#collapse{{$ckey}}">
                                {{$checklist['name']}}
                            </a>
                            </h4>
                        </div>
                        <div id="collapse{{$ckey}}" class="panel-collapse collapse in">
                            <div class="panel-body">
                                 <table class="table table-bordered">
                                    <thead>
                                        <th width="35%">Parameter</th>
                                        <th>Standard Reading</th>
                                        <th>Batch Reading</th>
                                        <th>Remarks</th>
                                    </thead>
                                    @foreach($checklist['subchecklists'] as $checklist)
                                    <?php $selChecklistInfo = array(); ?>
                                    @if(!empty($rmdetails) && !empty($rmdetails['raw_material_id']))
                                    <?php $selChecklistInfo = \App\RawMaterialChecklist::getrmchecklist($rmdetails['raw_material_id'],$checklist['id']); ?>
                                    @endif
                                    @if(!empty($selChecklistInfo['range']))
                                    <tr>
                                        <td>
                                            <input type="hidden" name="checklist_ids[]" value="{{$checklist['id']}}">
                                            <input type="hidden" name="rm_ids[]" value="{{$rmdetails['raw_material_id']}}">
                                            <input type="hidden" name="rminv_ids[]" value="{{$rmdetails['id']}}">
                                        {{$checklist['name']}}</td>
                                        <td>
                                            <input type="hidden" name="raw_material_ranges[]" value="{{$selChecklistInfo['range']}}">
                                            {{(!empty($selChecklistInfo['range']))?$selChecklistInfo['range']: '' }}
                                        </td>
                                        <td>
                                            <input  style="color:gray" placeholder="Enter Range" class="form-control" type="number" name="ranges[]" required>
                                        </td>
                                        <td>
                                            <textarea placeholder="Enter Remarks"  style="color:gray" class="form-control" type="number" name="qc_remarks[]"></textarea>
                                        </td>
                                    </tr>
                                    @endif
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach

                                    <input type="hidden" name="status" value="">
                                    <div class="form-group">
                                    <label class="col-md-3 control-label">QC Process Status <span class="asteric">*</span></label>
                                    <div class="col-md-4" style="margin-top:8px;">
                                        <?php $statusArr = array('QC Approved','QC Rejected') ?>
                                        @foreach($statusArr as $skey=> $statusInfo)
                                            <label>
                                            <input type="radio" name="qc_status" value="{{$statusInfo}}" @if($skey ==0) checked @endif />&nbsp;{{ucwords($statusInfo)}}&nbsp;
                                        </label>
                                        @endforeach
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Inventory-qc_status"></h4>
                                    </div>
                                </div>
                                @endif     
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Remarks </label>
                                    <div class="col-md-4">
                                        <textarea   placeholder="Remarks..." name="remarks" style="color:gray" class="form-control"></textarea>
                                    </div>
                                </div> 
                            </div>
                            <div class="form-actions right1 text-center">
                                <button class="btn green" type="submit">{{$submitBtnVal}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin.inventories.rm.rm-tracking')
<script type="text/javascript">
    $("#updateRmStatus").submit(function(e){
        $('.loadingDiv').show();
        e.preventDefault();
        var formdata = $("#updateRmStatus").serialize();
        $.ajax({
            url: '/admin/inventory/update-rm-status/'+"<?php echo $rmdetails['id'] ?>",
            type:'POST',
            data: formdata,
            success: function(data) {
                $('.loadingDiv').hide();
                if(!data.status){
                    $.each(data.errors, function (i, error) {
                        $('#Inventory-'+i).addClass('error-triggered');
                        $('#Inventory-'+i).attr('style', '');
                        $('#Inventory-'+i).html(error);
                        setTimeout(function () {
                            $('#Inventory-'+i).css({
                                'display': 'none'
                            });
                        $('#Inventory-'+i).removeClass('error-triggered');
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