@extends('layouts.adminLayout.backendLayout')

@section('content')
<div class="page-content-wrapper">
<div class="page-content">

{{-- =====================================================
| BACK BUTTON
===================================================== --}}
<a href="{{ url('admin/dvrs') }}" class="btn btn-default">
    <i class="fa fa-arrow-left"></i> Back to DVR List
</a>

<hr>

{{-- =====================================================
| DVR BASIC INFO
===================================================== --}}
<div class="portlet light bordered">
<div class="portlet-body">

<h3 class="bold">
    {{ $dvr->user->name }} – DVR Details
</h3>

<p><strong>DVR Date:</strong> {{ $dvr->dvr_date }}</p>
<p><strong>Time:</strong>
   {{ $dvr->start_time ?? '-' }} to {{ $dvr->end_time ?? '-' }}
</p>

<p><strong>Customer:</strong>
   {{ optional($dvr->customer)->name ?? 'N/A' }}
</p>

<p><strong>Visit Type:</strong>
   {{ $dvr->visit_type ?? '-' }}
</p>

<p><strong>Purpose of Visit:</strong><br>
   {{ $dvr->purpose_of_visit ?? '-' }}
</p>

<p><strong>Visit Details:</strong><br>
   {{ $dvr->visit_detail ?? '-' }}
</p>

<p><strong>Remarks:</strong><br>
   {{ $dvr->remarks ?? '-' }}
</p>

<hr>

{{-- =====================================================
| PRODUCTS DISCUSSED
===================================================== --}}
<h4>Products Discussed</h4>
@if($dvr->products->count())
    @foreach($dvr->products as $product)
        <span class="label label-success">
            {{ $product->productinfo->name ?? 'N/A' }}
        </span>
    @endforeach
@else
    <p class="text-muted">No products linked.</p>
@endif

<hr>

{{-- =====================================================
| TRIALS INFORMATION
===================================================== --}}
<h4>Trials</h4>
@if($dvr->trials->count())
    @foreach($dvr->trials as $trial)
        <div class="well well-sm">
            <strong>Trial #{{ $trial->trial_number }}</strong>
            <p><strong>Objective:</strong> {{ $trial->objective }}</p>
            <p><strong>Status:</strong> {{ $trial->status }}</p>
        </div>
    @endforeach
@else
    <p class="text-muted">No trials associated.</p>
@endif

<hr>

{{-- =====================================================
| CUSTOMER CONTACTS
===================================================== --}}
<h4>Customer Contacts Met</h4>
@if($dvr->customerContacts->count())
    @foreach($dvr->customerContacts as $cc)
        <p>
            {{ $cc->customerContact->name ?? '' }}
            ({{ $cc->customerContact->mobile_number ?? '' }})
        </p>
    @endforeach
@else
    <p class="text-muted">No customer contacts recorded.</p>
@endif

<hr>

{{-- =====================================================
| ATTACHMENTS
===================================================== --}}
<h4>Attachments</h4>
@if($dvr->attachments->count())
    @foreach($dvr->attachments as $file)
        <a href="{{ asset('uploads/'.$file->file) }}"
           target="_blank"
           class="btn btn-sm green margin-bottom-5">
            <i class="fa fa-paperclip"></i>
            {{ $file->label ?? 'View File' }}
        </a>
    @endforeach
@else
    <p class="text-muted">No attachments uploaded.</p>
@endif

</div>
</div>

</div>
</div>
@endsection
