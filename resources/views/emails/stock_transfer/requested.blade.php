{{--
   Sent to the employee being offered the stock, the moment the transfer is
   raised. It is their move: nothing happens until they accept or refuse.
--}}
@php
   $qty = function ($v) { return rtrim(rtrim(number_format((float) $v, 3, '.', ''), '0'), '.') . ' Kg'; };
@endphp
@extends('emails.sampling.layout')

@section('title', 'Stock Transfer Request')
@section('heading', 'Stock Transfer Request')
@section('ribbon', $transfer->reference_no . ' · ' . $transfer->items->count() . ' product(s)')
@section('ribbon_badge')<span class="ribbon-badge">Your Approval Needed</span>@endsection

@section('greeting', 'Hi ' . (optional($receiver)->name ?: 'there') . ',')
@section('intro')
   <strong>{{ optional($sender)->name ?: 'An employee' }}</strong> wants to transfer sample stock to you.
   It is held aside for you until you accept or refuse it from the app.
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
            <td class="info-row-label">From</td>
            <td class="info-row-value">{{ optional($sender)->name ?: '—' }}
               @if(optional($sender)->mobile)<br>{{ $sender->mobile }}@endif
            </td>
         </tr>
         <tr>
            <td class="info-row-label">Raised On</td>
            <td class="info-row-value">{{ optional($transfer->transfer_date)->format('d M Y') }}</td>
         </tr>
         @if($transfer->remarks)
         <tr>
            <td class="info-row-label">Remarks</td>
            <td class="info-row-value">{{ $transfer->remarks }}</td>
         </tr>
         @endif
      </table>
   </td>
</tr>

<tr>
   <td class="section section-last">
      <div class="section-label">Stock Being Transferred</div>
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

      <div class="note" style="margin-top:14px">
         Open the app to accept or refuse this transfer. The stock only becomes yours once you accept it.
      </div>
   </td>
</tr>
@endsection
