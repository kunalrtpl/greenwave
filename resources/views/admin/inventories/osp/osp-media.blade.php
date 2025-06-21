@extends('layouts.adminLayout.backendLayout')
@section('content')
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-head">
            <div class="page-title">
                <h1>Sale Return Material</h1>
            </div>
        </div>
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{ url('admin/dashboard') }}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{ url('admin/inventory/osp') }}">Sale Return Material </a>
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
                        <form id="OspMediaForm" role="form" class="form-horizontal" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off"> 
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <input type="hidden" name="product_inventory_id" value="{{$details['id']}}">
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Product Name:</label>
                                    <div class="col-md-9">
                                        <p class="form-control">{{$details['product']['product_name']}}</p>
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Upload File :</label>
                                    <div class="col-md-9">
                                        <input class="form-control" type="file" name="media" required>
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
        @if(!empty($details['medias']))
            <div class="row">
                <div class="col-md-12 ">
                    <div class="portlet blue-hoki box ">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-gift"></i>List of Media
                            </div>
                        </div>
                        <div class="portlet-body">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th scope="col">File</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                    @foreach($details['medias'] as $mkey=> $media)
                                        <tr>
                                            <td>
                                                <a target="_blank" href="{{url('/InventoryMedias/'.$media['file'])}}">Media {{++$mkey}}</a>
                                            </td>
                                            <td>
                                                <a  class="btn btn-xs btn-danger" href="{{url('admin/delete-osp-media/'.$media['id'])}}"><i class="fa fa-times"></i></a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </thead>
                                <tbody>
                              </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
<script type="text/javascript">
    $("#OspMediaForm").submit(function(e){
        $('.loadingDiv').show();
        var formdata = new FormData(this);
        $.ajax({
            url: "{{url('/admin/save-osp-media')}}",
            type:'POST',
            data: formdata,
            processData: false,
            contentType: false,
            success: function(data) {
                $('.loadingDiv').hide();
                if(!data.status){
                    $.each(data.errors, function (i, error) {
                        $('#RmInventoryMedia-'+i).addClass('error-triggered');
                        $('#RmInventoryMedia-'+i).attr('style', '');
                        $('#RmInventoryMedia-'+i).html(error);
                        setTimeout(function () {
                            $('#RmInventoryMedia-'+i).css({
                                'display': 'none'
                            });
                        $('#RmInventoryMedia-'+i).removeClass('error-triggered');
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