@extends('layouts.adminLayout.backendLayout')
@section('content')
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-head">
            <div class="page-title">
                <h1>Employee Management </h1>
            </div>
        </div>
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{ url('admin/dashboard') }}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{ url('admin/product-discounts') }}">SPSOD</a>
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
                        <form id="ProductDiscountForm" role="form" class="form-horizontal" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Range From (Amt.)<span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                       <input  type="text" placeholder="Range From" name="range_from" style="color:gray" class="form-control" value="{{(!empty($prodiscountdata['range_from']))?$prodiscountdata['range_from']: '' }}" />
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Incentive-range_from"></h4>
                                            
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Range To (Amt.)<span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Range To" name="range_to" style="color:gray" class="form-control" value="{{(!empty($prodiscountdata['range_to']))?$prodiscountdata['range_to']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Incentive-range_to"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Discount (in %.) <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Discount" name="discount" style="color:gray" class="form-control" value="{{(!empty($prodiscountdata['discount']))?$prodiscountdata['discount']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Incentive-discount"></h4>
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
    var productdiscountid =""; 
    <?php if(!empty($prodiscountdata['id'])){?>
        productdiscountid = "<?php echo $prodiscountdata['id']; ?>";
    <?php } ?>
    $("#ProductDiscountForm").submit(function(e){
        $('.loadingDiv').show();
        e.preventDefault();
        var formdata = $("#ProductDiscountForm").serialize()+"&productdiscountid="+productdiscountid;
        $.ajax({
            url: '/admin/save-product-discount',
            type:'POST',
            data: formdata,
            success: function(data) {
                $('.loadingDiv').hide();
                if(!data.status){
                    $.each(data.errors, function (i, error) {
                        $('#Incentive-'+i).addClass('error-triggered');
                        $('#Incentive-'+i).attr('style', '');
                        $('#Incentive-'+i).html(error);
                        setTimeout(function () {
                            $('#Incentive-'+i).css({
                                'display': 'none'
                            });
                        $('#Incentive-'+i).removeClass('error-triggered');
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