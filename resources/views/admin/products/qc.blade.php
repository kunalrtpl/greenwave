@extends('layouts.adminLayout.backendLayout')
@section('content')
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-head">
            <div class="page-title">
                <h1>Products Management </h1>
            </div>
        </div>
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{ url('admin/dashboard') }}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{ url('admin/products') }}">Products </a>
            </li>
        </ul>
        <div class="row">
            @if(Session::has('flash_message_success'))
                <div role="alert" class="alert alert-success alert-dismissible fade in"> <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"><span aria-hidden="true"></span></button> <strong>Success!</strong> {!! session('flash_message_success') !!} </div>
            @endif
            <div class="col-md-12 ">
                <div class="portlet blue-hoki box ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-gift"></i>{{ $title }}
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form  role="form" class="form-horizontal" method="post" action="{{url('/admin/product-qc/'.$productid)}}" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                @if($productdata['is_trader_product'] ===0)
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Technical Literature </label>
                                    <div class="col-md-4">
                                        <input type="file" class="form-control" name="technical_literature">
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-technical_literature"></h4>
                                        @if(!empty($productdata['technical_literature']))
                                            <a target="_blank" href="{{url('/images/ProductDocuments/'.$productdata['technical_literature'])}}">View Technical Literature</a>|
                                            <a href="{{url('/admin/delete-product-document/technical_literature/'.$productdata['id'])}}">Delete</a>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">MSDS </label>
                                    <div class="col-md-4">
                                        <input type="file" class="form-control" name="msds">
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-msds"></h4>
                                        @if(!empty($productdata['msds']))
                                            <a target="_blank" href="{{url('/images/ProductDocuments/'.$productdata['msds'])}}">View MSDS</a>|
                                            <a href="{{url('/admin/delete-product-document/msds/'.$productdata['id'])}}">Delete</a>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">GOTS Certified <span class="asteric">*</span></label>
                                    <div class="col-md-4" style="margin-top:8px;">
                                        <?php $gotsArr = array('Yes','No') ?>
                                        @foreach($gotsArr as $gotsINfo)
                                            <label>
                                            <input type="radio" name="gots_certification" value="{{$gotsINfo}}" @if(!empty($productdata) && $productdata['gots_certification'] ==$gotsINfo )  checked  @endif  />&nbsp;{{ucwords($gotsINfo)}}&nbsp;
                                        </label>
                                        @endforeach
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-gots_certification"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">ZDHC Certified <span class="asteric">*</span></label>
                                    <div class="col-md-4" style="margin-top:8px;">
                                        <?php $zdhcArr = array('Yes','No') ?>
                                        @foreach($zdhcArr as $zdhcInfo)
                                            <label>
                                            <input type="radio" name="zdhc_certification" value="{{$zdhcInfo}}" @if(!empty($productdata) && $productdata['zdhc_certification'] ==$zdhcInfo )  checked  @endif  />&nbsp;{{ucwords($zdhcInfo)}}&nbsp;
                                        </label>
                                        @endforeach
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-zdhc_certification"></h4>
                                    </div>
                                </div>
                                <div class="form-group" id="zdhc_pid_group" style="display:none;">
                                    <label class="col-md-3 control-label">ZDHC PID Number</label>
                                    <div class="col-md-4">
                                        <input placeholder="ZDHC PID Number" type="text" name="zdhc_pid" class="form-control" value="{{ $productdata['zdhc_pid'] ?? '' }}">
                                        <h4 class="text-center text-danger pt-3" id="Product-zdhc_pid"></h4>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">Oekotex Certified <span class="asteric">*</span></label>
                                    <div class="col-md-4" style="margin-top:8px;">
                                        <?php $OekotexArr = array('Yes','No') ?>
                                        @foreach($OekotexArr as $OekotexInfo)
                                            <label>
                                            <input type="radio" name="oekotex_certified" value="{{$OekotexInfo}}" @if(!empty($productdata) && $productdata['oekotex_certified'] ==$OekotexInfo )  checked  @endif  />&nbsp;{{ucwords($OekotexInfo)}}&nbsp;
                                        </label>
                                        @endforeach
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-oekotex_certified"></h4>
                                    </div>
                                </div>
                                @endif
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
                                <div id="collapse{{$ckey}}" class="panel-collapse collapse">
                                    <div class="panel-body">
                                         <table class="table table-bordered">
                                            <thead>
                                                <th width="35%">Parameter</th>
                                                <th>Standard Reading</th>
                                                <th>Remarks</th>
                                            </thead>
                                            @foreach($checklist['subchecklists'] as $checklist)
                                            <?php $selChecklistInfo = array(); ?>
                                            @if(!empty($productdata) && !empty($productdata['id']))
                                            <?php $selChecklistInfo = \App\ProductChecklist::getprochecklist($productdata['id'],$checklist['id']); ?>
                                            @endif
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="checklist_ids[]" value="{{$checklist['id']}}">
                                                {{$checklist['name']}}</td>
                                                <td>
                                                    <input  style="color:gray" placeholder="Enter Range" class="form-control" type="text" name="ranges[]" value="{{(!empty($selChecklistInfo['range']))?$selChecklistInfo['range']: '' }}">
                                                </td>
                                                <td>
                                                    <textarea placeholder="Enter Remarks"  style="color:gray" class="form-control" type="number" name="remarks[]">{{(!empty($selChecklistInfo['remarks']))?$selChecklistInfo['remarks']: '' }}</textarea>
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
            @endif
        @endforeach
        <div class="form-group">
    <label class="col-md-3 control-label">Additional Information</label>
    <div class="col-md-8">
        <table class="table table-bordered" id="additional-info-table">
            <thead>
                <tr>
                    <th style="width:5%">#</th>
                    <th style="width:40%">Label</th>
                    <th style="width:40%">Value</th>
                    <th style="width:15%"></th>
                </tr>
            </thead>
            <tbody>
                @php
                    $additionalInfo = $productdata['additional_information'] ?? [];
                @endphp
                @if(!empty($additionalInfo))
                    @foreach($additionalInfo as $index => $info)
                    <tr>
                        <td class="sr-no">{{ $index + 1 }}</td>
                        <td><input type="text" name="additional_info_labels[]" class="form-control" value="{{ $info['label'] ?? '' }}"></td>
                        <td><input type="text" name="additional_info_values[]" class="form-control" value="{{ $info['value'] ?? '' }}"></td>
                        <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fa fa-trash"></i></button></td>
                    </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
        <button type="button" class="btn btn-primary btn-sm" id="add-additional-row"><i class="fa fa-plus"></i> Add Row</button>
    </div>
</div>
                            <div class="form-group">
                                    <label class="col-md-3 control-label">Remarks</label>
                                    <div class="col-md-4">
                                        <textarea placeholder="Enter Remarks." class="form-control" name="qc_remarks">{{(!empty($productdata['qc_remarks']))?$productdata['qc_remarks']: '' }}</textarea>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Product-qc_remarks"></h4>
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
<script>
    $(document).ready(function () {
        function updateSerialNumbers() {
            $('#additional-info-table tbody tr').each(function(index) {
                $(this).find('.sr-no').text(index + 1);
            });
        }

        $('#add-additional-row').click(function () {
            var rowCount = $('#additional-info-table tbody tr').length + 1;
            var newRow = `
                <tr>
                    <td class="sr-no">${rowCount}</td>
                    <td><input type="text" name="additional_info_labels[]" class="form-control" /></td>
                    <td><input type="text" name="additional_info_values[]" class="form-control" /></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fa fa-trash"></i></button></td>
                </tr>
            `;
            $('#additional-info-table tbody').append(newRow);
        });

        $('#additional-info-table').on('click', '.remove-row', function () {
            $(this).closest('tr').remove();
            updateSerialNumbers();
        });
    });
</script>
<script>
$(document).ready(function () {
    function toggleZdhcField() {
        let value = $('input[name="zdhc_certification"]:checked').val();
        if (value === 'Yes') {
            $('#zdhc_pid_group').show();
        } else {
            $('#zdhc_pid_group').hide();
        }
    }

    // On page load
    toggleZdhcField();

    // On change
    $('input[name="zdhc_certification"]').on('change', function () {
        toggleZdhcField();
    });
});
</script>

@endsection