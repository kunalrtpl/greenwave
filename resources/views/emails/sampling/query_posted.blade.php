{{--
   One template for both directions. $audience says who is reading it:
   'admin'    the employee has asked something
   'employee' the admin has replied
--}}
@php
   $fromEmployee = $query->sender_type === 'employee';
   $nq = function ($v) {
      return ($v === null || $v === '') ? '—' : rtrim(rtrim(number_format((float) $v, 3, '.', ''), '0'), '.');
   };
@endphp
@extends('emails.sampling.layout')

@section('title', 'Sample Request Query')
@section('heading', $fromEmployee ? 'New Query on a Sample Request' : 'Query on Your Sample Request')
@section('ribbon', $request->reference_no . ' · ' . $threadCount . ' message(s)')
@section('ribbon_badge')<span class="ribbon-badge">{{ $fromEmployee ? 'Needs a Reply' : 'Query Raised' }}</span>@endsection

@section('greeting', 'Hi ' . ($audience === 'admin' ? 'there' : (optional($request->user)->name ?: 'there')) . ',')
@section('intro')
   @if($fromEmployee)
      <strong>{{ optional($query->sender)->name ?: 'An employee' }}</strong> has
      asked a question about sample request <strong>{{ $request->reference_no }}</strong>.
   @else
      <strong>{{ optional($query->sender)->name ?: 'The admin' }}</strong> has
      raised a query on your sample request <strong>{{ $request->reference_no }}</strong>.
   @endif
@endsection

@section('content')
<tr>
   <td class="section">
      <div class="section-label">The Message</div>
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
             style="background-color:#f1f8e9; border:1px solid #c5e1a5; border-left:5px solid #558b2f; border-radius:10px;">
         <tr>
            <td style="padding:16px 18px;">
               <div style="font-size:11px; color:#7cb342; font-weight:700; text-transform:uppercase; margin-bottom:8px;">
                  {{ optional($query->sender)->name ?: ($fromEmployee ? 'Employee' : 'Admin') }}
                  · {{ $query->created_at ? $query->created_at->format('d M Y, h:i A') : '' }}
               </div>
               <div style="font-size:13.5px; color:#33691e; line-height:1.7;">{!! nl2br(e($query->message)) !!}</div>
            </td>
         </tr>
      </table>
   </td>
</tr>

<tr>
   <td class="section">
      <div class="section-label">Request Details</div>
      <table role="presentation" class="info-table">
         <tr>
            <td class="info-row-label">Ref No</td>
            <td class="info-row-value"><strong>{{ $request->reference_no }}</strong>
               @if($request->is_voluntarily) &middot; Voluntary @endif
            </td>
         </tr>
         <tr>
            <td class="info-row-label">Status</td>
            <td class="info-row-value">{{ $request->status_label }}</td>
         </tr>
         <tr>
            <td class="info-row-label">Raised By</td>
            <td class="info-row-value">{{ optional($request->user)->name ?: '—' }}
               @if(optional($request->user)->mobile)<br>{{ $request->user->mobile }}@endif
            </td>
         </tr>
         <tr>
            <td class="info-row-label">Raised On</td>
            <td class="info-row-value">{{ optional($request->request_date)->format('d M Y') }}</td>
         </tr>
         @if($request->customer)
         <tr>
            <td class="info-row-label">Customer</td>
            <td class="info-row-value">{{ $request->customer->name }}</td>
         </tr>
         @endif
      </table>
   </td>
</tr>

{{-- Everything the employee asked for, and what has been approved against it,
     so neither side has to open the panel to answer the query. --}}
<tr>
   <td class="section">
      <div class="section-label">Product Requested</div>
      <table role="presentation" class="items-table">
         <tr class="items-header" bgcolor="#2e7d32" style="background-color:#2e7d32;">
            <td style="color:#ffffff; padding:11px 12px; font-size:10px; font-weight:700; text-transform:uppercase;">&nbsp;</td>
            <td style="color:#ffffff; padding:11px 12px; font-size:10px; font-weight:700; text-transform:uppercase;">Product</td>
            <td class="center" style="color:#ffffff; padding:11px 12px; font-size:10px; font-weight:700; text-transform:uppercase; text-align:center;">Pack</td>
            <td class="right" style="color:#ffffff; padding:11px 12px; font-size:10px; font-weight:700; text-transform:uppercase; text-align:right;">Qty</td>
         </tr>
         <tr>
            <td style="font-weight:700;">Requested</td>
            <td>
               <div class="item-name">{{ optional($request->requested_product)->product_name ?: optional($request->product)->product_name ?: '—' }}</div>
               <div class="item-code">{{ optional($request->requested_product)->product_code ?: optional($request->product)->product_code }}</div>
               @if($request->pack_size_info)<div class="item-code">Pack info: {{ $request->pack_size_info }}</div>@endif
            </td>
            <td class="center">
               @if($request->no_of_packs || $request->pack_size)
                  {{ $request->no_of_packs ?: '—' }} &times; {{ $nq($request->pack_size) }} Kg
               @else — @endif
            </td>
            <td class="right" style="font-weight:700;">{{ $nq($request->requested_qty) }} Kg</td>
         </tr>
         @if($request->approved_qty !== null)
         <tr>
            <td style="font-weight:700;">Approved</td>
            <td>
               <div class="item-name">{{ optional($request->product)->product_name ?: '—' }}</div>
               <div class="item-code">{{ optional($request->product)->product_code }}</div>
               @if($request->requested_product_id && $request->product_id && $request->requested_product_id != $request->product_id)
               <div class="item-code" style="color:#ef6c00;">Product changed by the admin</div>
               @endif
            </td>
            <td class="center">{{ $request->approved_no_of_packs ?: '—' }} &times; {{ $nq($request->approved_pack_size) }} Kg</td>
            <td class="right" style="font-weight:700;">{{ $nq($request->approved_qty) }} Kg</td>
         </tr>
         <tr>
            <td style="font-weight:700;">Dispatched</td>
            <td class="item-code">Pending: {{ $nq($request->pending_qty) }} Kg</td>
            <td class="center">—</td>
            <td class="right" style="font-weight:700;">{{ $nq($request->total_dispatched_qty) }} Kg</td>
         </tr>
         @endif
      </table>

      @if($request->item_remarks)
      <table role="presentation" class="info-table" style="margin-top:14px;">
         <tr>
            <td class="info-row-label">Item Remarks</td>
            <td class="info-row-value">{{ $request->item_remarks }}</td>
         </tr>
      </table>
      @endif
   </td>
</tr>

@if($request->purpose_reason_for_request || $request->application_plan || $request->dispatch_address || $request->decline_reason)
<tr>
   <td class="section">
      <div class="section-label">Why It Was Asked For</div>
      <table role="presentation" class="info-table">
         @if($request->purpose_reason_for_request)
         <tr>
            <td class="info-row-label">Purpose</td>
            <td class="info-row-value">{{ $request->purpose_reason_for_request }}</td>
         </tr>
         @endif
         @if($request->application_plan)
         <tr>
            <td class="info-row-label">Application Plan</td>
            <td class="info-row-value">{{ $request->application_plan }}</td>
         </tr>
         @endif
         @if($request->dispatch_to || $request->dispatch_address)
         <tr>
            <td class="info-row-label">Dispatch To</td>
            <td class="info-row-value">
               {{ $request->dispatchPartyName() ?: '—' }}
               @if($request->dispatch_to)<div class="item-code">{{ $request->dispatch_to }}</div>@endif
               @if($request->dispatch_address)<div class="item-code" style="white-space:pre-line;">{{ $request->dispatch_address }}</div>@endif
            </td>
         </tr>
         @endif
         @if($request->decline_reason)
         <tr>
            <td class="info-row-label">Decline Reason</td>
            <td class="info-row-value">{{ $request->decline_reason }}</td>
         </tr>
         @endif
      </table>
   </td>
</tr>
@endif

@if($request->target_competitor)
<tr>
   <td class="section">
      <div class="note">
         <strong>Competitor being targeted:</strong>
         {{ $request->competitor_product_name ?: '—' }}
         @if($request->competitor_make) by {{ $request->competitor_make }} @endif
         @if($request->competitor_monthly_requirement_kg) &middot; {{ $nq($request->competitor_monthly_requirement_kg) }} Kg / month @endif
         @if($request->competitor_expectation)<br>{{ $request->competitor_expectation }}@endif
      </div>
   </td>
</tr>
@endif

<tr><td style="height:28px; line-height:28px; font-size:0;">&nbsp;</td></tr>
@endsection
