{{--
   Sent to the employee when one product of their request is approved or
   declined. One email per product, because each is decided on its own.
--}}
@php
   $declined = $request->status === 'declined';
   // A request can be declined after it was approved, so the employee may
   // already have been told it was coming: say plainly that it is withdrawn.
   $withdrawn = $declined && $request->approved_at;
   $qty = function ($v) { return $v === null ? '—' : rtrim(rtrim(number_format((float) $v, 3, '.', ''), '0'), '.') . ' Kg'; };
@endphp
@extends('emails.sampling.layout')

@section('title', $declined ? 'Sample Request Declined' : 'Sample Request Approved')
@section('heading', $declined ? 'Sample Request Declined' : 'Sample Request Approved')
@section('ribbon', $request->reference_no)
@section('ribbon_badge')<span class="ribbon-badge">{{ $declined ? 'Declined' : 'Approved' }}</span>@endsection

@section('greeting', 'Hi ' . (optional($employee)->name ?: 'there') . ',')
@section('intro')
   @if($withdrawn)
      The approval given on {{ $request->approved_at->format('d M Y') }} for this product has been
      withdrawn and your request has now been declined. The qty that was reserved against your
      free-sample stock has been released.
   @elseif($declined)
      We regret to inform you that your request for a product sample could not be approved at this time.
   @else
      Your sample request for this product has been approved and is currently queued for dispatch. We’ll notify you as soon as it’s on its way.
   @endif
@endsection

@section('content')
<tr>
   <td class="section{{ $declined ? ' section-last' : '' }}">
      <div class="section-label">{{ $declined ? 'Declined Product' : 'Approved Product' }}</div>
      <table role="presentation" class="info-table">
         <tr>
            <td class="info-row-label">Product</td>
            <td class="info-row-value">
               <strong>{{ optional($request->product)->product_name ?: '—' }}</strong>
               @if(optional($request->product)->product_code)<br><span style="font-size:11px;color:#9ccc65;">{{ $request->product->product_code }}</span>@endif
            </td>
         </tr>
         @if($request->requested_product_id && $request->requested_product_id != $request->product_id)
         <tr>
            <td class="info-row-label">You Asked For</td>
            <td class="info-row-value">{{ optional($request->requested_product)->product_name }}</td>
         </tr>
         @endif
         <tr>
            <td class="info-row-label">Requested Qty</td>
            <td class="info-row-value">{{ $qty($request->requested_qty) }}</td>
         </tr>
         @if($withdrawn)
         <tr>
            <td class="info-row-label">Previously Approved</td>
            <td class="info-row-value">{{ $qty($request->approved_qty) }} &middot; released back to your stock</td>
         </tr>
         @endif
         @if(!$declined)
         <tr>
            <td class="info-row-label">Approved Qty</td>
            <td class="info-row-value"><strong>{{ $qty($request->approved_qty) }}</strong>
               @if($request->approved_no_of_packs)
               <span style="font-size:11px;color:#9ccc65;">({{ $request->approved_no_of_packs }} × {{ rtrim(rtrim(number_format((float) $request->approved_pack_size, 3, '.', ''), '0'), '.') }} Kg)</span>
               @endif
            </td>
         </tr>
         @endif
         @if($request->customer)
         <tr>
            <td class="info-row-label">Customer</td>
            <td class="info-row-value">{{ $request->customer->name }}</td>
         </tr>
         @endif
      </table>
   </td>
</tr>

@if($declined && $request->decline_reason)
<tr>
   <td class="section section-last">
      <div class="note"><strong>Reason:</strong> {{ $request->decline_reason }}</div>
   </td>
</tr>
@elseif(!$declined && $request->approval_remarks)
<tr>
   <td class="section section-last">
      <div class="note"><strong>Remarks:</strong> {{ $request->approval_remarks }}</div>
   </td>
</tr>
@elseif(!$declined)
<tr><td class="section section-last"></td></tr>
@endif
@endsection
