@extends('layouts.adminLayout.backendLayout')

@section('content')
<div class="page-content-wrapper">
<div class="page-content">

{{-- ================= HEADER ================= --}}
<div class="page-head">
    <div class="page-title">
        <h1>
            Daily Visit Reports
            <small class="text-muted">Executive activity dashboard</small>
        </h1>
    </div>
</div>

{{-- ================= FILTER PANEL ================= --}}
<div class="portlet light bordered">
<div class="portlet-body">
<form method="GET" action="{{ url('admin/dvrs') }}" class="row">

    <div class="col-md-3">
        <label class="bold">Employee</label>
        <select class="form-control select2" name="user_id">
            <option value="">All Employees</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}"
                    {{ request('user_id')==$user->id?'selected':'' }}>
                    {{ $user->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-2">
        <label class="bold">Month</label>
        <select class="form-control" name="month">
            <option value="">All</option>
            @for($m=1;$m<=12;$m++)
                <option value="{{ $m }}" {{ request('month')==$m?'selected':'' }}>
                    {{ date('F', mktime(0,0,0,$m,1)) }}
                </option>
            @endfor
        </select>
    </div>

    <div class="col-md-2">
        <label class="bold">Year</label>
        <select class="form-control" name="year">
            <option value="">All</option>
            @for($y=date('Y');$y>=2022;$y--)
                <option value="{{ $y }}" {{ request('year')==$y?'selected':'' }}>
                    {{ $y }}
                </option>
            @endfor
        </select>
    </div>

    <div class="col-md-2">
        <button class="btn btn-success btn-block" style="margin-top:25px">
            <i class="fa fa-filter"></i> Apply
        </button>
    </div>

</form>
</div>
</div>

{{-- ================= DVR GRID ================= --}}
<div class="row">
@foreach($dvrs as $dvr)
@php
    $customerName = $dvr->customer
        ? $dvr->customer->name
        : optional($dvr->customer_register_request)->name;
@endphp

<div class="col-md-6">
<div class="dvr-premium-card">

    {{-- TOP STRIP --}}
    <div class="dvr-top">
        <div>
            <div class="label text-muted small">CUSTOMER</div>
            <div class="dvr-title">{{ $customerName ?? 'N/A' }}</div>
        </div>

        <div class="dvr-metric">
            {{ $dvr->trials_count }}
            <span>TRIALS</span>
        </div>
    </div>

    {{-- META --}}
    <div class="dvr-meta">
        <div>
            <span class="meta-label">EMPLOYEE</span>
            <span>{{ $dvr->user->name }}</span>
        </div>
        <div>
            <span class="meta-label">DATE</span>
            <span>{{ $dvr->dvr_date }}</span>
        </div>
    </div>

    {{-- PURPOSE --}}
    <div class="dvr-section">
        <span class="meta-label">PURPOSE</span>
        <div>{{ $dvr->purpose_of_visit ?? '-' }}</div>
    </div>

    {{-- PRODUCTS --}}
    @if($dvr->products->count())
    <div class="dvr-section">
        <span class="meta-label">PRODUCTS</span><br>
        @foreach($dvr->products as $p)
            <span class="product-chip">
                {{ $p->productinfo->product_name ?? 'Product' }}
            </span>
        @endforeach
    </div>
    @endif

    {{-- CONTACT --}}
    @if($dvr->customer_contact_info)
    <div class="dvr-section text-muted">
        <i class="fa fa-phone"></i>
        {{ $dvr->customer_contact_info->name }}
        ({{ $dvr->customer_contact_info->mobile_number }})
    </div>
    @endif

    {{-- FOOTER --}}
    <div class="dvr-footer">
        <div>
            <span class="badge badge-primary">{{ strtoupper($dvr->visit_type ?? 'OFFICIAL') }}</span>
            @if($dvr->dvr_verified_date_time)
                <span class="badge badge-success">VERIFIED</span>
            @else
                <span class="badge badge-warning">PENDING</span>
            @endif
        </div>

        <a href="{{ url('admin/dvrs/'.$dvr->id) }}"
           class="btn btn-sm btn-outline-primary">
            View DVR →
        </a>
    </div>

</div>
</div>
@endforeach
</div>

<div class="text-center">
    {{ $dvrs->links() }}
</div>

</div>
</div>

{{-- ================= PREMIUM CSS ================= --}}
<style>
.dvr-premium-card{
    background:#fff;
    border-radius:10px;
    padding:20px;
    margin-bottom:30px;
    box-shadow:0 6px 20px rgba(0,0,0,0.08);
    transition:.25s ease;
}
.dvr-premium-card:hover{
    transform:translateY(-3px);
    box-shadow:0 14px 30px rgba(0,0,0,0.15);
}
.dvr-top{
    display:flex;
    justify-content:space-between;
    align-items:center;
}
.dvr-title{
    font-size:18px;
    font-weight:600;
}
.dvr-metric{
    font-size:28px;
    font-weight:700;
    color:#20c997;
    text-align:center;
}
.dvr-metric span{
    display:block;
    font-size:11px;
    color:#888;
}
.dvr-meta{
    display:flex;
    justify-content:space-between;
    padding:12px 0;
    border-bottom:1px dashed #eee;
    margin-bottom:12px;
}
.meta-label{
    font-size:11px;
    font-weight:600;
    color:#999;
    display:block;
}
.dvr-section{
    margin-bottom:10px;
}
.product-chip{
    display:inline-block;
    background:#f1f5ff;
    color:#3558e8;
    padding:5px 12px;
    border-radius:20px;
    font-size:12px;
    margin:4px 4px 0 0;
}
.dvr-footer{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-top:15px;
}
</style>
@endsection
