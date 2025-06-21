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
                        <form id="updateOspStatus" role="form" class="form-horizontal" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Current Track Status </label>
                                    <div class="col-md-4">
                                        <p class="form-control">
                                            <a data-ospinvid="{{$ospdetails['id']}}" href="javascript:;" class="OSPtracking"><span class="badge badge-success">{{$ospdetails['status']}}</span></a>
                                        </p>
                                    </div>
                                </div> 
                                <?php $submitBtnVal = 'Submit'; ?>
                                @if($ospdetails['status'] =='Incoming Material')
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
                                        <label class="col-md-3 control-label">Packing Size <span class="asteric">*</span></label>
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
                                @elseif($ospdetails['status'] =='Sample Sent to Lab')
                                    <?php $submitBtnVal = 'Sample Received by Lab'; ?>
                                    <input type="hidden" name="status" value="{{$submitBtnVal}}">
                                @elseif($ospdetails['status'] =='Sample Received by Lab')
                                <?php $submitBtnVal = 'QC Process Initiated'; ?>
                                    <input type="hidden" name="status" value="{{$submitBtnVal}}">
                                @elseif($ospdetails['status'] =='QC Process Initiated')
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Download</label>
                                        <div class="col-md-4">
                                            <p class="form-control"><a href="{{url('/admin/inventory/osp-pdf/'.$ospdetails['id'].'/unfilled')}}">Click here to download</a></p>
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
                                                    <th>Qty (Kgs)</th>
                                                    <th>Code</th>
                                                    <th>Sent to Lab</th>
                                                </tbody>
                                                <tr>
                                                    <td>{{$ospdetails['serial_no']}}</td>
                                                    <td>{{$ospdetails['no_of_samples']}}</td>
                                                    <td>{{$ospdetails['no_of_packs']}}</td>
                                                    <td>{{$ospdetails['stock']}}</td>
                                                    <td>{{$ospdetails['product']['product_code']}}</td>
                                                    <td>{{date('d M Y h:iA',strtotime($ospdetails['osp_history'][0]['created_at']))}}<br>
                                                        ({{$ospdetails['osp_history'][0]['updateby']['name']}})
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
@for($i=1; $i<= $ospdetails['no_of_samples']; $i++)
<div class="form-group">
    <label class="col-md-3 control-label">QC Criteria for Sample {{$i}} <span class="asteric">*</span></label>
    <div class="col-md-9" style="margin-top:8px;">
        <table class="table table-bordered">
            <thead>
                <th width="35%">Parameter</th>
                <th width="20%">Standard Reading</th>
                <th>Batch Reading</th>
                <th>Remarks</th>
            </thead>
            <input type="hidden" name="samples[]" value="{{$i}}">
            @foreach($ospdetails['osp_checklists'] as $checklist)
                <tr>
                    <input type="hidden" name="ospinv_ids[{{$i}}][]" value="{{$ospdetails['id']}}">
                    <input type="hidden" name="osp_ids[{{$i}}][]" value="{{$ospdetails['product_id']}}">
                    <input type="hidden" name="checklist_ids[{{$i}}][]" value="{{$checklist['checklist']['id']}}">
                    <td>
                        {{$checklist['checklist']['name']}}
                    </td>
                    <input type="hidden" name="product_ranges[{{$i}}][]" value="{{$checklist['range']}}">
                    <td>
                        {{$checklist['range']}}
                    </td>
                    <td>
                        <input  style="color:gray" placeholder="Enter Range" class="form-control" type="number" name="ranges[{{$i}}][]" required>
                    </td>
                    <td>
                        <textarea placeholder="Enter Remarks"  style="color:gray" class="form-control" type="number" name="qc_remarks[{{$i}}][]"></textarea>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
</div>
@endfor
                                    
                                    <div class="form-group">
                                    <label class="col-md-3 control-label">QC Process Status <span class="asteric">*</span></label>
                                    <div class="col-md-6" style="margin-top:8px;">
                                        <?php $statusArr = array('QC Approved','QC Rejected','Re-Process Advised') ?>
                                        @foreach($statusArr as $skey=> $statusInfo)
                                            <label>
                                            <input type="radio" name="status" value="{{$statusInfo}}" @if($skey ==0) checked @endif />&nbsp;{{ucwords($statusInfo)}}&nbsp;
                                        </label>
                                        @endforeach
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Inventory-qc_status"></h4>
                                    </div>
                                </div>
                                <?php /*@elseif($ospdetails['status'] =='QC Approved')
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Details</label>
                                        <div class="col-md-9">
                                            <table class="table table-bordered">
                                                <tbody>
                                                    <th>Serial No</th>
                                                    <th>No. of Samples</th>
                                                    <th>No. of Packs</th>
                                                    <th>Qty (Kgs)</th>
                                                    <th>Code</th>
                                                    <th>Sent to Lab</th>
                                                </tbody>
                                                <tr>
                                                    <td>{{$ospdetails['serial_no']}}</td>
                                                    <td>{{$ospdetails['no_of_samples']}}</td>
                                                    <td>{{$ospdetails['no_of_packs']}}</td>
                                                    <td>{{$ospdetails['stock']}}</td>
                                                    <td>{{$ospdetails['product']['product_code']}}</td>
                                                    <td>{{date('d M Y h:iA',strtotime($ospdetails['osp_history'][0]['created_at']))}}<br>
                                                        ({{$ospdetails['osp_history'][0]['updateby']['name']}})
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    <?php $submitBtnVal = 'Packing & Labelling Done'; ?>
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Change of Packing Required <span class="asteric">*</span></label>
                                        <div class="col-md-4">
                                            <select class="form-control" name="change_required">
                                                <option value="no">No</option>
                                                <option value="yes">Yes</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div id="PackingRequiredDiv" style="display: none;">
                                        <div class="form-group">
                                            <label class="col-md-3 control-label">Packing Size <span class="asteric">*</span></label>
                                            <div class="col-md-4">
                                                <?php $sizes = \App\PackingSize::sizes();
                                                ?>
                                                <select class="form-control select2" name="change_packing_size_id">
                                                    <option value="">Please Select</option>
                                                    @foreach($sizes as $sizeinfo)
                                                        <optgroup label="{{$sizeinfo['type']}}">
                                                           @foreach($sizeinfo['sizes'] as $size)
                                                                <option value="{{$size['id']}}">{{$size['size']}} kg</option>
                                                           @endforeach 
                                                        </optgroup>
                                                    @endforeach
                                                </select>
                                                <h4 class="text-center text-danger pt-3" style="display: none;" id="Inventory-change_packing_size_id"></h4>
                                            </div>
                                        </div> 
                                        <div class="form-group">
                                            <label class="col-md-3 control-label">Material Fill per Pack (kg) <span class="asteric">*</span></label>
                                            <div class="col-md-4">
                                                <input  type="number" placeholder="Material Fill per Pack" name="material_fill" style="color:gray" class="form-control"/>
                                                <h4 class="text-center text-danger pt-3" style="display: none;" id="Inventory-material_fill"></h4>
                                            </div>
                                        </div> 
                                        <div class="form-group">
                                            <label class="col-md-3 control-label">No of Packs Required <span class="asteric">*</span></label>
                                            <div class="col-md-4">
                                                <input  type="number" placeholder="No of Packs" name="no_of_packs_required" style="color:gray" class="form-control"/>
                                                <h4 class="text-center text-danger pt-3" style="display: none;" id="Inventory-no_of_packs_required"></h4>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="status" value="Packing & Labelling">*/?>
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
@include('admin.inventories.osp.osp-tracking')
<script type="text/javascript">

    $(document).on('change','[name=change_required]',function(){
        var packingRequired = $(this).val();
        $('#PackingRequiredDiv').hide();
        $("[name=material_fill]").removeAttr('required');
        $("[name=change_packing_size_id]").removeAttr('required');
        if(packingRequired =="yes"){
            $('#PackingRequiredDiv').show();
            $("[name=material_fill]").attr('required', 'required');
            $("[name=change_packing_size_id]").attr('required', 'required');
            refreshSelect2();
        }
    })

    $("#updateOspStatus").submit(function(e){
        $('.loadingDiv').show();
        e.preventDefault();
        var formdata = $("#updateOspStatus").serialize();
        $.ajax({
            url: '/admin/inventory/update-osp-status/'+"<?php echo $ospdetails['id'] ?>",
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