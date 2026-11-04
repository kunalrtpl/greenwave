{{--
   Sent back to the employee who raised the transfer, once the other one has
   answered. One template for both answers — the status says which it is.
--}}
@php
   $declined = $transfer->status === 'declined';
   $qty = function ($v) { return rtrim(rtrim(number_format((float) $v, 3, '.', ''), '0'), '.') . ' Kg'; };
@endphp
@extends('emails.sampling.layout')

@section('title', $declined ? 'Stock Transfer Refused' : 'Stock Transfer Accepted')
@section('heading', $declined ? 'Stock Transfer Refused' : 'Stock Transfer Accepted')
@section('ribbon', $transfer->reference_no . ' · ' . $transfer->items->count() . ' product(s)')
@section('ribbon_badge')<span class="ribbon-badge">{{ $transfer->status_label }}</span>@endsection

@section('greeting', 'Hi ' . (optional($sender)->name ?: 'there') . ',')
@section('intro')
   @if($declined)
      <strong>{{ optional($receiver)->name ?: 'The employee' }}</strong> has refused the stock you
      offered. It has been released back to your own stock and is yours to use again.
   @else
      <strong>{{ optional($receiver)->name ?: 'The employee' }}</strong> has accepted the stock
      transfer. The qty below has left your stock and is now theirs.
   @endif
@endsection

@section('content')
<tr>
   <td class="section">
      <div class="section-label">Transfer Details</div>
      <table role="presentation" class="info-table">
         <tr>
            <td class="info-row-label">Ref No</td>
            <td class="info-row-value"><strong>{{ $transfer->reference_no }}</strong></td>
         </tr>
         <tr>
            <td class="info-row-label">{{ $declined ? 'Refused By' : 'Accepted By' }}</td>
            <td class="info-row-value">{{ optional($receiver)->name ?: '—' }}</td>
         </tr>
         <tr>
            <td class="info-row-label">{{ $declined ? 'Refused On' : 'Accepted On' }}</td>
            <td class="info-row-value">
               {{ optional($declined ? $transfer->declined_at : $transfer->approved_at)->format('d M Y, h:i A') }}
            </td>
         </tr>
         <tr>
            <td class="info-row-label">Raised On</td>
            <td class="info-row-value">{{ optional($transfer->transfer_date)->format('d M Y') }}</td>
         </tr>
      </table>
   </td>
</tr>

<tr>
   <td class="section{{ $declined && $transfer->decline_reason ? '' : ' section-last' }}">
      <div class="section-label">Stock {{ $declined ? 'Offered' : 'Transferred' }}</div>
      <table role="presentation" class="items-table">
         <tr class="items-header" bgcolor="#2e7d32" style="background-color:#2e7d32;">
            <td style="color:#ffffff; padding:11px 12px; font-size:10px; font-weight:700; text-transform:uppercase;">Product</td>
            <td class="right" style="color:#ffffff; padding:11px 12px; font-size:10px; font-weight:700; text-transform:uppercase; text-align:right;">Qty</td>
         </tr>
         @foreach($transfer->items as $item)
         <tr>
            <td>
               <div class="item-name">{{ optional($item->product)->product_name ?: '—' }}</div>
               <div class="item-code">{{ optional($item->product)->product_code }}</div>
            </td>
            <td class="right" style="font-weight:700;">{{ $qty($item->qty) }}</td>
         </tr>
         @endforeach
         <tr>
            <td style="font-weight:700;">Total</td>
            <td class="right" style="font-weight:700;">{{ $qty($transfer->totalQty()) }}</td>
         </tr>
      </table>
   </td>
</tr>

@if($declined && $transfer->decline_reason)
<tr>
   <td class="section section-last">
      <div class="note"><strong>Reason:</strong> {{ $transfer->decline_reason }}</div>
   </td>
</tr>
@endif
@endsection
