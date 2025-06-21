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
                <a href="{{ url('admin/dealers') }}">Dealers </a>
            </li>
        </ul>
        <div class="row">
            @if(Session::has('flash_message_success'))
                <div role="alert" class="alert alert-success alert-dismissible fade in"> <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"><span aria-hidden="true">×</span></button> <strong>Success!</strong> {!! session('flash_message_success') !!} </div>
            @endif
            @if(Session::has('flash_message_error'))
                <div role="alert" class="alert alert-danger alert-dismissible fade in"> <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"><span aria-hidden="true">×</span></button> <strong>Error!</strong> {!! session('flash_message_error') !!} </div>
            @endif
            <div class="col-md-12 ">
                <div class="portlet blue-hoki box ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-gift"></i>{{ $title }}
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form  role="form" class="form-horizontal" method="post" action="{{url('/admin/manage-dealer-stock/'.$dealerid)}}" enctype="multipart/form-data" autocomplete="off">
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th width="25%">Product Name</th>
                                            <th width="25%">Stock In Hand</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($linkedProducts as $product)
                                            <?php $getStockInhand = \App\DealerProduct::getStockInhand($product['product_id'],$dealerid) ?>
                                            <tr>
                                                <td>{{$product['product']['product_name']}}</td>
                                                <td>
                                                    <input placeholder="Enter Stock In hand" class="form-control" type="number" name="stocks[{{$product['product_id']}}]" 
                                                    @if(is_object($getStockInhand)) value="{{$getStockInhand->stock_in_hand}}" @endif>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
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
@endsection