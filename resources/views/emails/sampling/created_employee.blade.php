{{--
   Sent to the employee who raised the request, once per request — a post
   carrying four products is one email listing all four, not four emails.
--}}
@extends('emails.sampling.layout')

@section('title', 'Sample Request Received')
@section('heading', 'Sample Request Received')
@section('ribbon', $groupReference . ' · ' . $requests->count() . ' product(s)')
@section('ribbon_badge')<span class="ribbon-badge">Pending Approval</span>@endsection

@section('greeting', 'Hi ' . (optional($employee)->name ?: 'there') . ',')
@section('intro')
   Your sample request has been received and is now waiting for approval.
   You will get another email for each product once it has been decided on.
@endsection

@section('content')
<tr>
   <td class="section">
      <div class="section-label">Request Details</div>
      <table role="presentation" class="info-table">
         <tr>
            <td class="info-row-label">Raised On</td>
            <td class="info-row-value">{{ optional($head->request_date)->format('d M Y') }}</td>
         </tr>
         @if($head->customer)
         <tr>
            <td class="info-row-label">Customer</td>
            <td class="info-row-value">{{ $head->customer->name }}</td>
         </tr>
         @endif
         @if($head->purpose_reason_for_request)
         <tr>
            <td class="info-row-label">Purpose</td>
            <td class="info-row-value">{{ $head->purpose_reason_for_request }}</td>
         </tr>
         @endif
         @if($head->dispatch_address)
         <tr>
            <td class="info-row-label">Dispatch To</td>
            <td class="info-row-value">{!! nl2br(e($head->dispatch_address)) !!}</td>
         </tr>
         @endif
      </table>
   </td>
</tr>

<tr>
   <td class="section section-last">
      <div class="section-label">Products Requested</div>
      <table role="presentation" class="items-table">
         <tr class="items-header" bgcolor="#2e7d32" style="background-color:#2e7d32;">
            <td style="color:#ffffff; padding:11px 12px; font-size:10px; font-weight:700; text-transform:uppercase;">Ref No</td>
            <td style="color:#ffffff; padding:11px 12px; font-size:10px; font-weight:700; text-transform:uppercase;">Product</td>
            <td class="center" style="color:#ffffff; padding:11px 12px; font-size:10px; font-weight:700; text-transform:uppercase; text-align:center;">Pack</td>
            <td class="right" style="color:#ffffff; padding:11px 12px; font-size:10px; font-weight:700; text-transform:uppercase; text-align:right;">Qty</td>
         </tr>
         @foreach($requests as $line)
         <tr>
            <td style="font-weight:700;">{{ $line->reference_no }}</td>
            <td>
               <div class="item-name">{{ optional($line->product)->product_name ?: '—' }}</div>
               <div class="item-code">{{ optional($line->product)->product_code }}</div>
            </td>
            <td class="center">{{ $line->no_of_packs }} × {{ rtrim(rtrim(number_format((float) $line->pack_size, 3, '.', ''), '0'), '.') }} Kg</td>
            <td class="right" style="font-weight:700;">{{ rtrim(rtrim(number_format((float) $line->requested_qty, 3, '.', ''), '0'), '.') }} Kg</td>
         </tr>
         @endforeach
      </table>
   </td>
</tr>
@endsection
