@extends('layouts.adminLayout.backendLayout')
@section('content')
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
                <a href="{{ url('admin/dealer-atod') }}">ATod </a>
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
                        <form id="AtodDiscountForm" role="form" class="form-horizontal" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Financial Year<span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <select class="form-control select2" name="financial_year" required>
                                            <?php $starting_year ="2021"; ?>
                                            @for($starting_year; $starting_year <= date('Y'); $starting_year++) 
                                            <?php $fin_year = $starting_year."-".($starting_year +1); ?>
                                            <option value="{{$fin_year}}" @if(isset($atoddata['financial_year']) && $atoddata['financial_year'] == $fin_year) selected @endif>{{$fin_year}}</option>';
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Range From<span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Range From" name="range_from" style="color:gray" class="form-control" @if($atodid) value="{{(!empty($atoddata['range_from']))?$atoddata['range_from']: '0' }}" @endif/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="ATOD-range_from"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Range To<span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Range To" name="range_to" style="color:gray" class="form-control" value="{{(!empty($atoddata['range_to']))?$atoddata['range_to']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="ATOD-range_to"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Discount (in %.) <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <input  type="text" placeholder="Discount" name="discount" style="color:gray" class="form-control" value="{{(!empty($atoddata['discount']))?$atoddata['discount']: '' }}"/>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="ATOD-discount"></h4>
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
    var atodid =""; 
    <?php if(!empty($atoddata['id'])){?>
        atodid = "<?php echo $atoddata['id']; ?>";
    <?php } ?>
    $("#AtodDiscountForm").submit(function(e){
        $('.loadingDiv').show();
        e.preventDefault();
        var formdata = $("#AtodDiscountForm").serialize()+"&atodid="+atodid;;
        $.ajax({
            url: '/admin/save-atod',
            type:'POST',
            data: formdata,
            success: function(data) {
                $('.loadingDiv').hide();
                if(!data.status){
                    $.each(data.errors, function (i, error) {
                        $('#ATOD-'+i).addClass('error-triggered');
                        $('#ATOD-'+i).attr('style', '');
                        $('#ATOD-'+i).html(error);
                        setTimeout(function () {
                            $('#ATOD-'+i).css({
                                'display': 'none'
                            });
                        $('#ATOD-'+i).removeClass('error-triggered');
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