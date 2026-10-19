{{--
   Sent to the admin when an employee raises a request. One email per request,
   whatever the number of products.
--}}
@extends('emails.sampling.layout')

@section('title', 'New Sample Request')
@section('heading', 'New Sample Request')
@section('ribbon', $groupReference . ' · ' . $requests->count() . ' product(s)')
@section('ribbon_badge')<span class="ribbon-badge">Action Required</span>@endsection

@section('greeting', 'Hello,')
@section('intro')
   <strong>{{ optional($employee)->name ?: 'An employee' }}</strong> has raised a sample request.
   Each product is approved or declined on its own from the admin panel.
@endsection

@section('content')
<tr>
   <td class="section">
      <div class="section-label">Raised By</div>
      <table role="presentation" class="info-table">
         <tr>
            <td class="info-row-label">Employee</td>
            <td class="info-row-value">{{ optional($employee)->name ?: '—' }}</td>
         </tr>
         <tr>
            <td class="info-row-label">Mobile / Email</td>
            <td class="info-row-value">{{ optional($employee)->mobile ?: '—' }}<br>{{ optional($employee)->email }}</td>
         </tr>
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
      </table>
   </td>
</tr>

<tr>
   <td class="section">
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
               @if($line->item_remarks)<div class="item-code">{{ $line->item_remarks }}</div>@endif
            </td>
            <td class="center">{{ $line->no_of_packs }} × {{ rtrim(rtrim(number_format((float) $line->pack_size, 3, '.', ''), '0'), '.') }} Kg</td>
            <td class="right" style="font-weight:700;">{{ rtrim(rtrim(number_format((float) $line->requested_qty, 3, '.', ''), '0'), '.') }} Kg</td>
         </tr>
         @endforeach
      </table>
   </td>
</tr>

@if($head->target_competitor)
<tr>
   <td class="section">
      <div class="note">
         <strong>Competitor being targeted:</strong>
         {{ $head->competitor_product_name ?: '—' }}
         @if($head->competitor_make) by {{ $head->competitor_make }} @endif
         @if($head->competitor_monthly_requirement_kg) · {{ rtrim(rtrim(number_format((float) $head->competitor_monthly_requirement_kg, 3, '.', ''), '0'), '.') }} Kg / month @endif
         @if($head->competitor_expectation)<br>{{ $head->competitor_expectation }}@endif
      </div>
   </td>
</tr>
@endif
@endsection

{{-- One click from the email to the screen this is acted on. --}}
@section('cta_text')
   {{ $requests->count() > 1 ? 'These products are' : 'This product is' }} waiting for your decision
   in the Pending Sample Requests list.
@endsection
@section('cta')
   <a href="{{ $pendingUrl }}" style="display:inline-block; padding:13px 30px; font-size:14px; font-weight:700; color:#ffffff; text-decoration:none;">Open Pending Sample Requests</a>
@endsection
