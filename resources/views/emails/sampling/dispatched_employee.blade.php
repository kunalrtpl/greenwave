{{--
   Sent when material goes out. One email per dispatch per employee: a single
   challan covering three of their requests is one email listing all three.
--}}
@php
   $qty = function ($v) { return rtrim(rtrim(number_format((float) $v, 3, '.', ''), '0'), '.') . ' Kg'; };
@endphp
@extends('emails.sampling.layout')

@section('title', 'Sample Dispatched')
@section('heading', 'Your Sample Has Been Dispatched')
@section('ribbon', $dispatch['ref_no'] . ' · ' . $lines->count() . ' request(s)')
@section('ribbon_badge')<span class="ribbon-badge">Dispatched</span>@endsection

@section('greeting', 'Hi ' . (optional($employee)->name ?: 'there') . ',')
@section('intro')
   Your requested sample(s) have been dispatched! Once they arrive, please go to the In-transit section in the app to confirm receipt and keep your stock accurate.
@endsection

@section('content')
<tr>
   <td class="section">
      <div class="section-label">Dispatch Details</div>
      <table role="presentation" class="info-table">
         <tr>
            <td class="info-row-label">Dispatch No</td>
            <td class="info-row-value"><strong>{{ $dispatch['ref_no'] }}</strong></td>
         </tr>
         <tr>
            <td class="info-row-label">Dispatch Date</td>
            <td class="info-row-value">{{ $dispatch['date'] }}</td>
         </tr>
         <tr>
            <td class="info-row-label">Challan No</td>
            <td class="info-row-value">{{ $dispatch['invoice_no'] ?: '—' }}</td>
         </tr>
         <tr>
            <td class="info-row-label">{{ strtolower($dispatch['transport']) === 'courier' ? 'Courier / Docket No' : 'Transport / LR No' }}</td>
            <td class="info-row-value">{{ $dispatch['transport'] }} · {{ $dispatch['lr_no'] ?: '—' }}</td>
         </tr>
         @if($dispatch['address'])
         <tr>
            <td class="info-row-label">Sent To</td>
            <td class="info-row-value">{!! nl2br(e($dispatch['address'])) !!}</td>
         </tr>
         @endif
      </table>
   </td>
</tr>

<tr>
   <td class="section section-last">
      <div class="section-label">What Was Sent</div>
      <table role="presentation" class="items-table">
         <tr class="items-header" bgcolor="#2e7d32" style="background-color:#2e7d32;">
            <td style="color:#ffffff; padding:11px 12px; font-size:10px; font-weight:700; text-transform:uppercase;">Ref No</td>
            <td style="color:#ffffff; padding:11px 12px; font-size:10px; font-weight:700; text-transform:uppercase;">Product</td>
            <td class="center" style="color:#ffffff; padding:11px 12px; font-size:10px; font-weight:700; text-transform:uppercase; text-align:center;">Batch</td>
            <td class="right" style="color:#ffffff; padding:11px 12px; font-size:10px; font-weight:700; text-transform:uppercase; text-align:right;">Qty Sent</td>
            <td class="right" style="color:#ffffff; padding:11px 12px; font-size:10px; font-weight:700; text-transform:uppercase; text-align:right;">Still Pending</td>
         </tr>
         @foreach($lines as $line)
         <tr>
            <td style="font-weight:700;">{{ optional($line['request'])->reference_no }}</td>
            <td>
               <div class="item-name">{{ optional($line['product'])->product_name ?: '—' }}</div>
               <div class="item-code">{{ optional($line['product'])->product_code }}</div>
            </td>
            <td class="center">{{ $line['batch_no'] ?: '—' }}</td>
            <td class="right" style="font-weight:700;">{{ $qty($line['dispatch_qty']) }}</td>
            <td class="right">{{ $qty($line['pending_qty']) }}</td>
         </tr>
         @endforeach
      </table>
   </td>
</tr>
@endsection
