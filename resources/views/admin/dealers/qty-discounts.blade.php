@extends('layouts.adminLayout.backendLayout')
@section('content')

<div class="page-content-wrapper">
<div class="page-content">

    <div class="page-head">
        <div class="page-title">
            <h1>Dealer Qty Discount</h1>
        </div>
    </div>

    <ul class="page-breadcrumb breadcrumb">
        <li>
            <a href="{{url('admin/dashboard')}}">Dashboard</a>
        </li>
    </ul>

    @if(isset($_GET['s']))
        <div class="alert alert-success">
            <strong>Success!</strong> Record updated successfully.
        </div>
    @endif

    <div class="portlet light">

        {{-- Toolbar --}}
        <div class="table-toolbar">
            <div class="row">

                <div class="col-md-3">
                    <a href="{{ url('/admin/add-edit-qty-discount') . 
                        (request('dealer_id') ? '?dealer_id='.request('dealer_id') : '') }}" 
                       class="btn btn-primary">
                       Add Discount
                    </a>
                </div>

                {{-- Dealer Filter --}}
                <div class="col-md-4">
                    <form method="GET">
                        <select name="dealer_id"
                                class="form-control select2"
                                onchange="this.form.submit()">
                            <option value="">All Dealers</option>
                            @foreach($dealers as $dealer)
                                <option value="{{$dealer->id}}"
                                    {{ request('dealer_id')==$dealer->id ? 'selected':'' }}>
                                    {{$dealer->business_name}}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>

            </div>
        </div>

        {{-- MAIN TABLE --}}
        <div class="table-container">
            <table class="table table-bordered table-striped">

                <thead>
                    <tr>
                        <th width="20%">Dealer</th>
                        <th width="20%">Product</th>
                        <th>Discount Details</th>
                    </tr>
                </thead>

                <tbody>

                @forelse($groupedDiscounts as $dealerId => $products)

                    @php
                        $dealerName = optional($products->first()->first()->dealer)->business_name;
                    @endphp

                    @foreach($products as $productId => $discounts)

                        <tr>
                            <td>
                                {{ $dealerName }}
                            </td>

                            <td>
                                {{ optional($discounts->first()->product)->product_name }}
                            </td>

                            <td>
                                <table class="table table-bordered">
                                    <tr>
                                        <th>From - To</th>
                                        <th>Discount</th>
                                        <th width="100">Actions</th>
                                    </tr>

                                    @foreach($discounts as $dis)
                                    <tr>
                                        <td>
                                            {{ $dis->range_from }} - {{ $dis->range_to }}
                                        </td>
                                        <td>
                                            {{ $dis->discount }}%
                                        </td>
                                        <td>
                                            <a class="btn btn-xs green"
                                               href="{{url('/admin/add-edit-qty-discount/'.$dis->id)}}">
                                                <i class="fa fa-edit"></i>
                                            </a>

                                            <a class="btn btn-xs red"
                                               onclick="return confirm('Delete?');"
                                               href="{{url('/admin/delete-qty-discount/'.$dis->id)}}">
                                                <i class="fa fa-times"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach

                                </table>
                            </td>
                        </tr>

                    @endforeach

                @empty
                    <tr>
                        <td colspan="3" class="text-center">
                            No Records Found
                        </td>
                    </tr>
                @endforelse

                </tbody>

            </table>
        </div>

    </div>

</div>
</div>

@endsection