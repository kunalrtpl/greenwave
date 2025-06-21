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
                        <form  role="form" class="form-horizontal" method="post" action="{{url('/admin/dealer-special-discount/'.$dealerid)}}" enctype="multipart/form-data" autocomplete="off">
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
                            <div class="form-body">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th width="25%">Product Name</th>
                                            <th width="25%">Discount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($products as $product)
                                            <?php $getDiscountInfo = \App\DealerSpecialDiscount::getSpecialDis($product['id'],$dealerid) ?>
                                            <tr>
                                                <td>{{$product['product_name']}} <!-- @if(in_array($product['id'],$linkedProducts)) <span class="badge badge-success">Linked</span> @else <span class="badge badge-danger">Not Linked</span>  @endif --></td>
                                                <td>
                                                    <input step="0.01"
                                                    placeholder="Enter Discount" class="form-control" type="number" name="discounts[{{$product['id']}}]" @if(is_object($getDiscountInfo)) value="{{$getDiscountInfo->discount}}" @endif>
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