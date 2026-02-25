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
                <a href="{{ url('admin/qty-discounts') }}">Dealer Qty Discount</a>
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
                        <form id="QtyDiscountForm" role="form" class="form-horizontal" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">
                                        Select Dealer <span class="asteric">*</span>
                                    </label>
                                    <div class="col-md-4">
                                        <select class="form-control select2" name="dealer_id">
                                            <option value="">Please Select</option>

                                            @foreach($dealers as $dealer)
                                                <option value="{{$dealer->id}}"
                                                    {{
                                                        // 1️⃣ If editing → use saved value
                                                        (!empty($qtydiscountdata['dealer_id']) &&
                                                        $qtydiscountdata['dealer_id'] == $dealer->id)

                                                        ||

                                                        // 2️⃣ If adding → use query string dealer_id
                                                        (empty($qtydiscountdata['dealer_id']) &&
                                                        request('dealer_id') == $dealer->id)

                                                        ? 'selected' : ''
                                                    }}>
                                                    {{$dealer->business_name}}
                                                </option>
                                            @endforeach

                                        </select>

                                        <h4 class="text-center text-danger pt-3"
                                            style="display:none;"
                                            id="Incentive-dealer_id">
                                        </h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Select Product <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <select class="form-control select2" name="product_id">
                                            <option value="">Please Select</option>
                                            @foreach(products() as $product)
                                                <option value="{{$product['id']}}" {{(!empty($qtydiscountdata['product_id']) && $qtydiscountdata['product_id']==$product['id'] )?'selected': '' }}>{{$product['product_name']}}</option>
                                            @endforeach
                                        </select>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Incentive-product_id"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Range From (kg.)<span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                       <input  type="text" placeholder="Range From" name="range_from" style="color:gray" class="form-control" @if($qtydiscountid) value="{{(!empty($qtydiscountdata['range_from']))?$qtydiscountdata['range_from']: '0' }}" @endif />
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Incentive-range_from"></h4>
                                            
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Range To (Kg.)<span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Range To" name="range_to" style="color:gray" class="form-control" value="{{(!empty($qtydiscountdata['range_to']))?$qtydiscountdata['range_to']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Incentive-range_to"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Discount (in %.) <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Discount" name="discount" style="color:gray" class="form-control" @if($qtydiscountid)  value="{{(!empty($qtydiscountdata['discount']))?$qtydiscountdata['discount']: '0' }}" @endif/>
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
    var qtydiscountid =""; 
    <?php if(!empty($qtydiscountdata['id'])){?>
        qtydiscountid = "<?php echo $qtydiscountdata['id']; ?>";
    <?php } ?>
    $("#QtyDiscountForm").submit(function(e){
        $('.loadingDiv').show();
        e.preventDefault();
        var formdata = $("#QtyDiscountForm").serialize()+"&qtydiscountid="+qtydiscountid;
        $.ajax({
            url: '/admin/save-qty-discount',
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