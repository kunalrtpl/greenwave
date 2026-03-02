@extends('layouts.adminLayout.backendLayout')
@section('content')

<style>
    /* FLOATING SAVE BAR STYLES */
    .floating-save-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        padding: 15px 0;
        border-top: 1px solid #e1e5ec;
        z-index: 1000;
        box-shadow: 0 -5px 15px rgba(0,0,0,0.08);
        text-align: center;
    }
    
    /* Spacing for the bottom of the page so content isn't hidden by the bar */
    .page-content { padding-bottom: 100px !important; }
    
    .btn-save-float {
        padding: 10px 40px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        border-radius: 30px !important;
        box-shadow: 0 4px 10px rgba(76, 175, 80, 0.3);
    }

    /* Highlight rows that have a discount */
    .has-discount {
        background-color: #f9fff9 !important;
    }
    .has-discount td:first-child {
        font-weight: 600;
        color: #2e7d32;
    }
</style>

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
                <div role="alert" class="alert alert-success alert-dismissible fade in"> 
                    <button aria-label="Close" data-dismiss="alert" class="close" type="button"><span aria-hidden="true">×</span></button> 
                    <strong>Success!</strong> {!! session('flash_message_success') !!} 
                </div>
            @endif
            @if(Session::has('flash_message_error'))
                <div role="alert" class="alert alert-danger alert-dismissible fade in"> 
                    <button aria-label="Close" data-dismiss="alert" class="close" type="button"><span aria-hidden="true">×</span></button> 
                    <strong>Error!</strong> {!! session('flash_message_error') !!} 
                </div>
            @endif

            <div class="col-md-12">
                <div class="portlet blue-hoki box">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-gift"></i>{{ $title }}
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form role="form" class="form-horizontal" method="post" action="{{url('/admin/dealer-special-discount/'.$dealerid)}}" enctype="multipart/form-data" autocomplete="off">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                            
                            <div class="form-body">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="70%">Product Name</th>
                                            <th width="30%">Discount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            // 1. Pre-fetch discounts to avoid double queries and enable sorting
                                            $productsWithData = collect($products)->map(function($product) use ($dealerid) {
                                                $info = \App\DealerSpecialDiscount::getSpecialDis($product['id'], $dealerid);
                                                $product['current_discount'] = is_object($info) ? $info->discount : null;
                                                return $product;
                                            })->sortByDesc('current_discount');
                                        @endphp

                                        @foreach($productsWithData as $product)
                                            <tr class="{{ $product['current_discount'] > 0 ? 'has-discount' : '' }}">
                                                <td>
                                                    {{$product['product_name']}}
                                                    @if($product['current_discount'] > 0)
                                                        <span class="label label-success" style="margin-left:10px;">Active Discount</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <input step="0.01" 
                                                           placeholder="Enter Discount" 
                                                           class="form-control discount-input" 
                                                           type="number" 
                                                           name="discounts[{{$product['id']}}]" 
                                                           value="{{ $product['current_discount'] }}">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="floating-save-bar">
                                <button class="btn green btn-save-float" type="submit">
                                    <i class="fa fa-check"></i> Submit Changes
                                </button>
                                <a href="{{ url('admin/dealers') }}" class="btn btn-default" style="border-radius:30px !important;">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection