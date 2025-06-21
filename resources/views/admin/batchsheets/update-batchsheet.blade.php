@extends('layouts.adminLayout.backendLayout')
@section('content')
<style type="text/css">
    .wtBox{
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 5px;
        gap: 10px;
    }
    .wtBox h6{
        width: 60px;
    }
</style>
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-head">
            <div class="page-title">
                <h1>BatchSheet Management </h1>
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
                        <form id="BatchSheetForm" role="form" class="form-horizontal" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                <div class="form-group">
                                   <label class="col-md-3 control-label">Product Name </label>
                                    <div class="col-md-6">
                                        <p class="form-control">{{$batchDetails['product']['product_name']}}</p>
                                    </div>
                                </div>
                                <div class="form-group">
                                   <label class="col-md-3 control-label">Batch No. </label>
                                    <div class="col-md-6">
                                        <p class="form-control">{{$batchDetails['batch_no']?? ''}}</p>
                                    </div>
                                </div>
                                <div class="form-group">
                                   <label class="col-md-3 control-label">Batch Size</label>
                                    <div class="col-md-6">
                                        <p class="form-control">{{$batchDetails['batch_size'] ?? ''}} kg</p>
                                    </div>
                                </div>
                                <div class="form-group">
                                   <label class="col-md-3 control-label">Standard Pack Type</label>
                                    <div class="col-md-6">
                                        <p class="form-control">{{$batchDetails['standard_packing']['name']?? ''}}</p>
                                    </div>
                                </div>
                                <div class="form-group">
                                   <label class="col-md-3 control-label">Standard Fill Size</label>
                                    <div class="col-md-6">
                                        <p class="form-control">{{$batchDetails['standard_fill_size']?? ''}} kg</p>
                                    </div>
                                </div>
                                <!-- <div class="form-group">
                                   <label class="col-md-3 control-label">Batch Size </label>
                                    <div class="col-md-6">
                                        <p class="form-control">{{$batchDetails['batch_size']}}</p>
                                    </div>
                                </div>
                                <div class="form-group">
                                   <label class="col-md-3 control-label">Machine No.</label>
                                    <div class="col-md-6">
                                        <p class="form-control">{{$batchDetails['machine_number']}}</p>
                                    </div>
                                </div>
                                <div class="form-group">
                                   <label class="col-md-3 control-label">Machine Capacity</label>
                                    <div class="col-md-6">
                                        <p class="form-control">{{$batchDetails['machine_capacity']}}</p>
                                    </div>
                                </div>
                                <div class="form-group">
                                   <label class="col-md-3 control-label">Operator Name</label>
                                    <div class="col-md-6">
                                        <p class="form-control">{{$batchDetails['operator_name']}}</p>
                                    </div>
                                </div>
                                <div class="form-group">
                                   <label class="col-md-3 control-label">   No. of Packings Required</label>
                                    <div class="col-md-6">
                                        <p class="form-control">{{$batchDetails['batch_size']/$batchDetails['product']['productpacking']['size']}}</p>
                                    </div>
                                </div> -->
@if($batchDetails['status'] == "RM Requested")
    <input type="hidden" name="status" value="RM Issued">
    <div class="form-group">
        <div class="col-md-12" style="margin-top:8px;">
            <table class="table table-bordered">
                <tr>
                    <th width="10%">Sr. No.</th>
                    <th width="10%">Type</th>
                    <th width="15%">Name</th>
                    <th width="15%">Batch No.</th>
                    <th width="15%">Qty</th>
                    <th width="20%">Remarks</th>
                </tr>
                @foreach($batchDetails['batchsheet_requirements'] as $key=> $requirement)
                    <tr>
                        <td>{{++$key}}</td> 
                        <td>
                            @if(!empty($requirement['raw_material_inventory_id']))
                                RM
                            @elseif($requirement['product_inventory_id'])
                                SRM
                            @endif
                        </td>
                        <td>
                            @if(!empty($requirement['rawmaterial_inventory']))
                                {{$requirement['rawmaterial_inventory']['rawmaterial']['name']}}
                            @elseif(!empty($requirement['product_inventory']))
                                {{$requirement['product_inventory']['product']['product_name']}}
                            @endif
                        </td> 
                        <td>
                            @if(!empty($requirement['rawmaterial_inventory']))
                                {{$requirement['rawmaterial_inventory']['supplier_batch_no']}}
                                <br>
                                <small>({{$requirement['rawmaterial_inventory']['remaining_stock']}} kg)</small>
                            @elseif(!empty($requirement['product_inventory']))
                                {{$requirement['product_inventory']['supplier_batch_no']}}
                                <br>
                                <small>({{$requirement['product_inventory']['remaining_stock']}} kg)</small>
                            @endif  
                        </td> 
                        <td>
                            {{$requirement['qty']}}
                        </td> 
                        <td>
                            <textarea placeholder="Enter Remarks..." class="form-control" name="remarks[{{$requirement['id']}}]"></textarea>
                        </td>
                    </tr>
                @endforeach
            </table>
            <h4 class="text-center text-danger pt-3" style="display: none;" id="BatchSheetErr-requirements"></h4>
        </div>
    </div>
    <?php /*@foreach($batchDetails['materials'] as $materialInfo)
        <div class="form-group">
            <?php $rmApprovedBatches = \App\RawMaterialInventory::approvedRms($materialInfo['raw_material_id']);?>
            <div class="col-md-12" style="margin-top:8px;">
                <table class="table table-bordered">
                    <tr>
                        <th class="text-center" colspan="4">{{$materialInfo['rawmaterial']['coding']}} </th>
                    </tr>
                    <tr>
                        <th>Batch No</th>
                        <!-- <th>Supplier Btach No.</th> -->
                        <th>Available Stock</th>
                        <th>Issue Qty <span style="color:red;">(Required Stock :- {{$materialInfo['qty']}} kg)</span></th>
                    </tr>
                    @if(!empty($rmApprovedBatches))
                        @foreach($rmApprovedBatches as $approvedBatch)
                            <tr>
                                <td>{{$approvedBatch['batch_no']}}</td>
                                <!-- <td>{{$approvedBatch['supplier_batch_no']}}</td> -->
                                <td>{{$approvedBatch['remaining_stock']}}</td>
                                <td>
                                    <input class="form-control" placeholder="Enter Qty" type="number" step="0.01"  name="issue_qty[{{$approvedBatch['id']}}]">
                                    <h4 class="text-center text-danger pt-3" style="display: none;" id="BatchSheetErr-{{$approvedBatch['id']}}"></h4>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                        <td class="text-center" colspan="4">No batches found for this Raw Material</td>
                    </tr>
                    @endif
                </table>
                <h4 class="text-center text-danger pt-3" style="display: none;" id="BatchSheetMaterialErr-{{$materialInfo['id']}}"></h4>
            </div>
        </div>
    @endforeach */?>


                            @elseif($batchDetails['status'] == "RM Issued")
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
        <h4 class="text-center text-danger pt-3" style="display: none;" id="BatchSheetMaterialErr-packing_type_id"></h4>
                                        </div>
                                    </div>
                                <input type="hidden" name="status" value="Sample Sent to Lab">
                                <!-- <div class="form-group">
                                   <label class="col-md-3 control-label">Batch Start Time</label>
                                    <div class="col-md-6">
                                        <input type="datetime-local" name="batch_start_time" class="form-control" required>
                                    </div>
                                </div> -->
                                <!-- <div class="form-group">
                                   <label class="col-md-3 control-label">Expected Batch Out Time</label>
                                    <div class="col-md-6">
                                        <p class="form-control" id="ExpectedBatchOutTime"></p>
                                        <input type="hidden" name="expected_batch_out_time">
                                    </div>
                                </div> -->
                                <!-- <div class="form-group">
                                   <label class="col-md-3 control-label">Batch Out Time</label>
                                    <div class="col-md-6">
                                        <input type="datetime-local" name="batch_out_complete" class="form-control" required>
                                    </div>
                                </div> -->
                            @elseif($batchDetails['status'] == "Sample Sent to Lab")
                                <input type="hidden" name="status" value="Sample Received by Lab">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Remarks </label>
                                    <div class="col-md-4">
                                        <textarea   placeholder="Remarks..." name="remarks" style="color:gray" class="form-control"></textarea>
                                    </div>
                                </div>
                            @elseif($batchDetails['status'] =='Sample Received by Lab')
                                <input type="hidden" name="status" value="QC Process Initiated">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Remarks </label>
                                    <div class="col-md-4">
                                        <textarea   placeholder="Remarks..." name="remarks" style="color:gray" class="form-control"></textarea>
                                    </div>
                                </div>
                            @elseif($batchDetails['status'] =='QC Process Initiated')

<p><b>QC criteria</b></p>
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
                                    @if(!empty($batchDetails) && !empty($batchDetails['product_id']))
                                    <?php $selChecklistInfo = \App\ProductChecklist::getprochecklist($batchDetails['product_id'],$checklist['id']); ?>
                                    @endif
                                    @if(!empty($selChecklistInfo['range']))
                                    <tr>
                                        <td>
                                            <input type="hidden" name="checklist_ids[]" value="{{$checklist['id']}}">
                                        {{$checklist['name']}}</td>
                                        <td>
                                            <input type="hidden" name="product_ranges[]" value="{{$selChecklistInfo['range']}}">
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
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Remarks </label>
                                    <div class="col-md-4">
                                        <textarea   placeholder="Remarks..." name="remarks" style="color:gray" class="form-control"></textarea>
                                    </div>
                                </div>
                            @elseif($batchDetails['status'] =='Ready for Packing')
                                <input type="hidden" name="status" value="Material Received by Packing Department">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Remarks </label>
                                    <div class="col-md-4">
                                        <textarea   placeholder="Remarks..." name="remarks" style="color:gray" class="form-control"></textarea>
                                    </div>
                                </div>
                            @elseif($batchDetails['status'] =="Material Received by Packing Department")
                            <input type="hidden" name="status" value="Ready for Dispatch">
<div class="form-group">
    <label class="col-md-3 control-label">Packing Wastage </label>
    <div class="col-md-4">
        <input placeholder="Enter Packing Wastage" type="number" name="packing_wastage" class="form-control" step="0.001">
    </div>
</div>
<div class="form-group">
    <label class="col-md-3 control-label">Net Batch Size</label>
    <div class="col-md-4">
        <p class="form-control" id="NetBatchSize">{{$batchDetails['batch_size']}}</p>
    </div>
</div>
<table class="table table-bordered table-stripped" id="finalPackingTable">
    <tr>
        <th colspan="3" style="font-size: 15px; background-color: #D3D3D3; text-align: center;border-right: 5px solid;">FINAL PACKING DETAILS</th>
        <th colspan="2" style="font-size: 15px; text-align: center; background-color: #bdd6ee;border-right: 5px solid;">PACKING CONSUMPTION</th>
        <th colspan="2" style="font-size: 15px; text-align: center; background-color: #ffe599; border-right: 5px solid;">LABEL CONSUMPTION</th>
        <th style="background-color: #D3D3D3;">#</th>
    </tr>
    <tr>
        <!-- <th width="20%" style="text-align: center; background-color:#EEEEEE;">Packing Type <br> <small>(Tare Weight)</small></th> -->
        <th width="8%" style="text-align: center; background-color:#EEEEEE;">No. of Packs</th>
        <th width="10%" style="text-align: center; background-color:#EEEEEE;">Net Weight Per Pack  <br> <small>(kg)</small></th>
        <th width="10%" style="text-align: center; background-color:#EEEEEE; border-right: 5px solid;">Total Material Filled <br> <small>(kg)</small></th>
        <th width="20%" style="text-align: center; background-color:#deeaf6;">Packing Type</th>
        <th width="15%" style="text-align: center; background-color:#deeaf6;border-right: 5px solid;">No. of Packs Consumed</th>
        <th width="15%" style="text-align: center; background-color:#fff2cc;">Label Type</th>
        <th style="text-align: center; background-color:#fff2cc; border-right: 5px solid;">No. of Labels Consumed</th>
        <th width="10%" style="text-align: center; background-color:#EEEEEE;">Actions</th>
    </tr>
    <tr>
        
        <td>
            <input placeholder="No. of Packs" type="number" name="final_no_of_packs[]" class="form-control finalNoOfPacks">
        </td>
        <td>
            <input placeholder="Net Weight Per Pack" type="number" name="final_net_fill_size[]" class="form-control finalNetFillSize">
            <p class="text-center"></p>
        </td>
        <td style="border-right: 5px solid;">
            <p class="text-center"></p>
        </td>
        <td>
            <?php $packing_types = \App\PackingType::packing_types();
            ?>
            <select class="form-control finalPackingType" name="final_packing_types[]">
                <option value="">N/A</option>
                @foreach($packing_types as $packingType)
                    <option data-tare_weight="{{$packingType['tare_weight']}}" data-stock="{{$packingType['stock']}}" value="{{$packingType['id']}}">{{$packingType['name']}}</option>
                @endforeach
            </select>
            <p class="text-left"></p>
        </td>
        <td style="border-right: 5px solid;">
            <input placeholder="No. of Packs Consumed" type="number" name="packs_consumed[]" class="form-control" required>
            <br>
            <span class="weightClass" style="display:none;">
                <span class="wtBox"><h6><b>Gross Wt.</b></h6><input type="number" name="final_gross_weight" class="form-control finalGrossWeight"></span>
                <span class="wtBox"><h6><b>Net Wt.</b></h6><input type="number" name="final_net_weight" class="form-control finalNetWeight"></span>
            </span>
        </td>
        <td>
            <select class="form-control" name="labels[]">
                <option value="">N/A</option>
                @foreach($lables as $label)
                    <option value="{{$label['id']}}">{{$label['label_type']}}</option>
                @endforeach
            </select>
        </td>
        <td style="border-right: 5px solid;">
            <input placeholder="No. of Labels Consumed" type="number" name="labels_consumed[]" class="form-control">
        </td>
        <td>
            <a class="btn btn-sm btn-danger removePackingTypeRow" href="javascript:;"><i class="fa fa-times"></i></a>
        </td>
    </tr>
</table>
<button type="button" id="addMorePackingType">Add More</button>
<h4 class="text-center text-danger pt-3" style="display: none;" id="BatchSheetErr-final_packing_errors"></h4>
<div class="form-group">
                                    <label class="col-md-3 control-label">Remarks </label>
                                    <div class="col-md-4">
                                        <textarea   placeholder="Remarks..." name="remarks" style="color:gray" class="form-control"></textarea>
                                    </div>
                                </div>
                            @endif
                            </div>
                            <div class="form-actions right1 text-center">
                                <button class="btn green" type="submit">
                                    @if($batchDetails['status'] == "RM Requested")
                                        RM Issued
                                    @elseif($batchDetails['status'] == "RM Issued")
                                        Sample Sent to Lab
                                    @elseif($batchDetails['status'] == "Sample Sent to Lab")
                                        Sample Received by Lab
                                    @elseif($batchDetails['status'] == "Sample Received by Lab")
                                        QC Process Initiated
                                    @elseif($batchDetails['status'] == "Ready for Packing")
                                        Material Received by Packing Department
                                    @else
                                    Submit
                                    @endif
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">

    $(document).on("keyup",'.finalNetFillSize',function(){  
        var netfillsize = $(this).val();
        var noofpacks = $(this).closest('tr').children('td:eq(0)').children().val();
        var packingtype = $(this).closest('tr').children('td:eq(1)').children().val();
        //var tareWeight = $(this).closest('tr').children('td:eq(0)').children().find(':selected').data('tare_weight');
        /*if(packingtype ==""){
            $(this).val('');
            alert('Please select packing type');
            return false;
        }else if(noofpacks <=0){
            $(this).val('');
            alert('Please enter No of packs');
            return false;
        }*/
        var materialFilled = noofpacks * netfillsize;
        //alert(materialFilled);
        //var totalnetfillsize =  (parseFloat(tareWeight)+parseFloat(netfillsize)).toFixed(3);

        //$(this).closest('tr').children('td:eq(2)').find('p').html('<small>('+totalnetfillsize+'kg)</small>');

        $(this).closest('tr').children('td:eq(2)').find('p').html(materialFilled+' kg ');
        
    });
    
    $(document).on("keyup",'.finalNoOfPacks',function(){  
        $(this).closest('tr').children('td:eq(2)').children().val('');
        $(this).closest('tr').children('td:eq(3)').find('p').html('');
    });

    $(document).on("keyup",'[name=packing_wastage]',function(){
        var packing_wastage = $(this).val();
        var batchsize = "<?php echo $batchDetails['batch_size'] ?>";
        if(packing_wastage !=""){
            var netbatchsize =  parseFloat(batchsize) - parseFloat(packing_wastage);
        }else{
            var netbatchsize = batchsize;
        }
        $('#NetBatchSize').html(netbatchsize);
    });

    $(document).on("change",'.finalPackingType',function(){
        var packingType = $(this).find(':selected').val(); 
        var tareWeight =  $(this).find(':selected').data('tare_weight');
        var stock =  $(this).find(':selected').data('stock');
        if(packingType != ""){
            $(this).closest('tr').children('td:eq(3)').find('p').html('<small>Tare Weight: '+tareWeight+'kg <br>'+'Available Stock: '+stock+'</small>');
            $(this).closest('tr').children('td:eq(4)').find('.weightClass').show();
            $(this).closest('tr').children('td:eq(4)').find('.finalGrossWeight').prop('required',true);
            $(this).closest('tr').children('td:eq(4)').find('.finalNetWeight').prop('required',true);
        }else{
            $(this).closest('tr').children('td:eq(3)').find('p').html('');
            $(this).closest('tr').children('td:eq(4)').find('.weightClass').hide();
            $(this).closest('tr').children('td:eq(4)').find('.finalGrossWeight').prop('required',false);
            $(this).closest('tr').children('td:eq(4)').find('.finalNetWeight').prop('required',false);
            $(this).closest('tr').children('td:eq(4)').find('.finalGrossWeight').val('');
            $(this).closest('tr').children('td:eq(4)').find('.finalNetWeight').val('');

        }
        //$(this).closest('tr').children('td:eq(4)').find('p').html(packingType);
        //alert(tareWeight);
    });

    $(document).on('click','#addMorePackingType',function(){
        var clone = $("#finalPackingTable tr:last").clone().find('input').val('').end().find('.weightClass').hide().end().insertAfter("#finalPackingTable tr:last");
        $("#finalPackingTable tr:last").find('p').html('');
    })

    $(document).on('click','.removePackingTypeRow',function(){
        var rowCount = $('#finalPackingTable tr').length -1;
        if(rowCount ==1){
            alert('You can not delete this right now because one row must be present in this table');
        }else{
            $(this).closest('tr').remove();
        }
    });

    $(document).on('change','[name=batch_start_time]',function(){
        var batchStartTime = $(this).val();
        var dt = new Date(batchStartTime);
        var duration = "<?php echo $batchDetails['product']['batch_out_duration'] ?>";
        dt.setHours( dt.getHours() + parseInt(duration) ) *1000;
        var curr_date = dt.getDate();
        if(curr_date <= 9){
            curr_date = '0'+curr_date;
        }
        var curr_month = dt.getMonth() + 1; //Months are zero based
        if(curr_month <= 9){
            curr_month = '0'+curr_month;
        }
        var curr_year = dt.getFullYear();
        var hours = dt.getHours();
        var mins = dt.getMinutes();
        var expectedBatchStartTime  = curr_year+"-"+curr_month+"-"+curr_date+" "+hours+":"+mins+':00';
        $('#ExpectedBatchOutTime').text(expectedBatchStartTime);
        $('[name=expected_batch_out_time]').val(expectedBatchStartTime);
    })

    $("#BatchSheetForm").submit(function(e){
        var batchid = "<?php echo $batchDetails['id'] ?>";
        $('.loadingDiv').show();
        e.preventDefault();
        var formdata = $("#BatchSheetForm").serialize();
        $.ajax({
            url: '/admin/update-batch-sheet/'+batchid,
            type:'POST',
            data: formdata,
            success: function(data) {
                $('.loadingDiv').hide();
                if(!data.status){
                    if(data.type=='batch_size_error'){
                        alert(data.message);
                    }else if(data.type=='issueQty'){
                        $.each(data.errors, function (i, error) {
                            $('#BatchSheetErr-'+i).addClass('error-triggered');
                            $('#BatchSheetErr-'+i).attr('style', '');
                            $('#BatchSheetErr-'+i).html(error);
                            setTimeout(function () {
                                $('#BatchSheetErr-'+i).css({
                                    'display': 'none'
                                });
                            $('#BatchSheetErr-'+i).removeClass('error-triggered');
                            }, 5000);
                        });
                    }else{
                        $.each(data.errors, function (i, error) {
                            $('#BatchSheetMaterialErr-'+i).addClass('error-triggered');
                            $('#BatchSheetMaterialErr-'+i).attr('style', '');
                            $('#BatchSheetMaterialErr-'+i).html(error);
                            setTimeout(function () {
                                $('#BatchSheetMaterialErr-'+i).css({
                                    'display': 'none'
                                });
                            $('#BatchSheetMaterialErr-'+i).removeClass('error-triggered');
                            }, 5000);
                        });
                    }
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
<style>
    .panel-heading .accordion-toggle:after {
    /* symbol for "opening" panels */
    font-family: 'Glyphicons Halflings';  /* essential for enabling glyphicon */
    content: "\e114";    /* adjust as needed, taken from bootstrap.css */
    float: right;        /* adjust as needed */
    color: grey;         /* adjust as needed */
}
.panel-heading .accordion-toggle.collapsed:after {
    /* symbol for "collapsed" panels */
    content: "\e080";    /* adjust as needed, taken from bootstrap.css */
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