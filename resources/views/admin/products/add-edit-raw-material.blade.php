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
                <a href="{{ url('admin/raw-materials') }}">Raw Materials </a>
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
                        <form id="RawMaterialForm" role="form" class="form-horizontal" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Name <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Name" name="name" style="color:gray" class="form-control" value="{{(!empty($rawmaterialdata['name']))?$rawmaterialdata['name']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="RawMaterial-name"></h4>
                                    </div>
                                </div>
                                <!-- <div class="form-group">
                                    <label class="col-md-3 control-label">Coding <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Coding" name="coding" style="color:gray" class="form-control" value="{{(!empty($rawmaterialdata['coding']))?$rawmaterialdata['coding']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="RawMaterial-coding"></h4>
                                    </div>
                                </div>  -->
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Landed Price <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Landed Price" name="price" style="color:gray" class="form-control" value="{{(!empty($rawmaterialdata['price']))?$rawmaterialdata['price']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="RawMaterial-price"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Shelf Life (in months)<span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Shelf Life" name="shelf_life" style="color:gray" class="form-control" value="{{(!empty($rawmaterialdata['shelf_life']))?$rawmaterialdata['shelf_life']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="RawMaterial-shelf_life"></h4>
                                    </div>
                                </div> 
                                <!-- @if(!empty($rawmaterialdata['opening_stock']))
                                    
                                @else
                                    <div class="form-group">
                                        <label class="col-md-3 control-label">Opening Stock <span class="asteric">*</span></label>
                                        <div class="col-md-4">
                                            <input  type="text" placeholder="Opening Stock" name="opening_stock" style="color:gray" class="form-control" value="{{(!empty($rawmaterialdata['opening_stock']))?$rawmaterialdata['opening_stock']: '' }}"/>
                                            <h4 class="text-center text-danger pt-3" style="display: none;" id="RawMaterial-opening_stock"></h4>
                                        </div>
                                    </div>
                                @endif -->
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Status <span class="asteric">*</span></label>
                                    <div class="col-md-4" style="margin-top:8px;">
                                        <?php $statusArr = array('1'=>'Active','0'=>'Inactive') ?>
                                        @foreach($statusArr as $skey=> $status)
                                            <label>
                                            <input type="radio" name="status" value="{{$skey}}" @if(!empty($rawmaterialdata) && $rawmaterialdata['status'] ==$skey ) checked @else @if($skey ==1) checked @endif @endif />&nbsp;{{ucwords($status)}}&nbsp;
                                        </label>
                                        @endforeach
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="User-status"></h4>
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
                                            @if(!empty($rawmaterialdata) && !empty($rawmaterialdata['id']))
                                            <?php $selChecklistInfo = \App\RawMaterialChecklist::getrmchecklist($rawmaterialdata['id'],$checklist['id']); ?>
                                            @endif
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="checklist_ids[]" value="{{$checklist['id']}}">
                                                    @if(isset($selChecklistInfo['id']))
                                                    <input type="hidden" name="rm_checklistds[]" value="{{$selChecklistInfo['id']}}">
                                                    @endif
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
    var rawmaterialid =""; 
    <?php if(!empty($rawmaterialdata['id'])){?>
        rawmaterialid = "<?php echo $rawmaterialdata['id']; ?>";
    <?php } ?>
    $("#RawMaterialForm").submit(function(e){
        $('.loadingDiv').show();
        e.preventDefault();
        var formdata = $("#RawMaterialForm").serialize()+"&rawmaterialid="+rawmaterialid;;
        $.ajax({
            url: '/admin/save-raw-material',
            type:'POST',
            data: formdata,
            success: function(data) {
                $('.loadingDiv').hide();
                if(!data.status){
                    $.each(data.errors, function (i, error) {
                        $('#RawMaterial-'+i).addClass('error-triggered');
                        $('#RawMaterial-'+i).attr('style', '');
                        $('#RawMaterial-'+i).html(error);
                        setTimeout(function () {
                            $('#RawMaterial-'+i).css({
                                'display': 'none'
                            });
                        $('#RawMaterial-'+i).removeClass('error-triggered');
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