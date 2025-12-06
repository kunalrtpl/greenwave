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
                <a href="{{ url('admin/packing-types') }}">Packing Types </a>
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
                        <form id="PackingTypeForm" role="form" class="form-horizontal" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Packing Type <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Packing Type" name="name" style="color:gray" class="form-control" value="{{(!empty($packingtypedata['name']))?$packingtypedata['name']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="PackingType-name"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Tare weight <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Tare weight" name="tare_weight" style="color:gray" class="form-control" value="{{(!empty($packingtypedata['tare_weight']))?$packingtypedata['tare_weight']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="PackingType-tare_weight"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Price <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Price" name="price" style="color:gray" class="form-control" value="{{(!empty($packingtypedata['price']))?$packingtypedata['price']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="PackingType-price"></h4>
                                    </div>
                                </div>   
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Use for Lab Sample</label>
                                    <div class="col-md-4">
                                        <input style="margin-top: 12px;" type="checkbox" name="lab_sample" value="1" @if(isset($packingtypedata['lab_sample']) && $packingtypedata['lab_sample'] ==1) checked @endif>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Use for Additional Packing <span class="asteric">*</span></label>
                                    <div class="col-md-4" style="margin-top:8px;">

                                        @php 
                                            $additional = $packingtypedata['additional_packing'] ?? 0;
                                        @endphp

                                        <label>
                                            <input type="radio" name="additional_packing" value="1"
                                                @if($additional == 1) checked @endif> Yes
                                        </label>

                                        <label style="margin-left:15px;">
                                            <input type="radio" name="additional_packing" value="0"
                                                @if($additional == 0) checked @endif> No
                                        </label>

                                        <h4 class="text-danger text-center" id="PackingType-additional_packing" style="display:none;"></h4>
                                    </div>
                                </div>

                                <!-- IF YES -->
                                <div class="form-group" id="facilitation_cost_group" style="display:none;">
                                    <label class="col-md-3 control-label">Packing Facilitation Cost (Rs/Kg) <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input type="text" name="facilitation_cost" class="form-control"
                                            value="{{ $packingtypedata['facilitation_cost'] ?? '' }}" placeholder="0.00">
                                        <h4 class="text-danger text-center" id="PackingType-facilitation_cost" style="display:none;"></h4>
                                    </div>
                                </div>

                                <!-- IF NO -->
                                <div class="form-group" id="packing_loss_group" style="display:none;">
                                    <label class="col-md-3 control-label">Packing Loss % (if any)</label>
                                    <div class="col-md-4">
                                        <input type="text" name="packing_loss" class="form-control"
                                            value="{{ $packingtypedata['packing_loss'] ?? '' }}" placeholder="0 - 100">
                                        <h4 class="text-danger text-center" id="PackingType-packing_loss" style="display:none;"></h4>
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
    var packingtypeid =""; 
    <?php if(!empty($packingtypedata['id'])){?>
        packingtypeid = "<?php echo $packingtypedata['id']; ?>";
    <?php } ?>
    $("#PackingTypeForm").submit(function(e){
        $('.loadingDiv').show();
        e.preventDefault();
        var formdata = $("#PackingTypeForm").serialize()+"&packingtypeid="+packingtypeid;;
        $.ajax({
            url: '/admin/save-packing-type',
            type:'POST',
            data: formdata,
            success: function(data) {
                $('.loadingDiv').hide();
                if(!data.status){
                    $.each(data.errors, function (i, error) {
                        $('#PackingType-'+i).addClass('error-triggered');
                        $('#PackingType-'+i).attr('style', '');
                        $('#PackingType-'+i).html(error);
                        setTimeout(function () {
                            $('#PackingType-'+i).css({
                                'display': 'none'
                            });
                        $('#PackingType-'+i).removeClass('error-triggered');
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
<script>
$(document).ready(function () {

    function togglePackingFields() {
        let value = $('input[name="additional_packing"]:checked').val();

        if (value === "1") {
            $('#facilitation_cost_group').show();
            $('#packing_loss_group').hide();
        } else {
            $('#facilitation_cost_group').hide();
            $('#packing_loss_group').show();
        }
    }

    // On page load (edit mode)
    togglePackingFields();

    // On change
    $('input[name="additional_packing"]').on('change', function () {
        togglePackingFields();
    });
});
</script>


@endsection