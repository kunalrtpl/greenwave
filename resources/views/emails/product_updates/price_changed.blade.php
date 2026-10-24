{{-- The standard dealer price was raised or lowered. --}}
{{-- "Hi Ajay," for an employee, "Dear Kamal Singhania," for a dealer. --}}
@php
   $isDealer = ($audience ?? 'employee') === 'dealer';
   $greeting = $isDealer
      ? 'Dear ' . (($recipient->person ?? null) ?: ($recipient->name ?: 'Partner')) . ','
      : 'Hi ' . ($recipient->name ?: 'there') . ',';
@endphp
@php
   $money = function ($v) { return $v === null ? '—' : '₹' . number_format((float) $v, 2); };
   $up    = $priceDirection === 'increased';
   $down  = $priceDirection === 'decreased';
   $tone  = $up ? '#c62828' : ($down ? '#2e7d32' : '#1565c0');
   $arrow = $up ? '▲' : ($down ? '▼' : '');
@endphp
@extends('emails.product_updates.layout')

@section('title', 'Dealer Price ' . ucfirst($priceDirection))
@section('heading', 'Dealer Price ' . ($up ? 'Increase' : ($down ? 'Reduction' : 'Update')))
@section('ribbon', $product->product_name)
@section('ribbon_badge')<span class="badge-red" style="background-color:{{ $tone }};">Price {{ ucfirst($priceDirection) }} {{ $arrow }}</span>@endsection

@section('greeting', $greeting)
@section('intro')
   @if($oldPrice !== null && ($up || $down))
      Please note that the dealer price of <strong>{{ $product->product_name }}</strong> has been
      <strong style="color:{{ $tone }};">{{ $priceDirection }}</strong> from <strong>{{ $money($oldPrice) }}</strong>
      to <strong>{{ $money($newPrice) }}</strong> with effect from <strong>{{ $effectiveOn }}</strong>.
   @else
      Please note that the dealer price of <strong>{{ $product->product_name }}</strong> is now
      <strong>{{ $money($newPrice) }}</strong> with effect from <strong>{{ $effectiveOn }}</strong>.
   @endif
@endsection

@section('content')
<tr>
   <td class="section">
      <div class="section-label">Revised Dealer Price</div>
      <table role="presentation" class="info-table price-grid" width="100%">
         <tr>
            @if($oldPrice !== null)
            <td style="width:33%; border-right:1px solid #e8f5e9;">
               <div class="price-cap">Previous</div>
               <div class="price-old">{{ $money($oldPrice) }}</div>
            </td>
            @endif
            <td style="border-right:1px solid #e8f5e9;">
               <div class="price-cap">New Price</div>
               <div class="price-new">{{ $money($newPrice) }}</div>
            </td>
            @if($priceDiff !== null && $priceDiff != 0)
            <td style="width:33%;">
               <div class="price-cap">Change</div>
               <div style="font-size:16px; font-weight:800; color:{{ $tone }};">
                  {{ $arrow }} {{ $money(abs($priceDiff)) }}
               </div>
               @if($pricePct !== null)
               <div style="font-size:12px; font-weight:700; color:{{ $tone }}; padding-top:2px;">
                  {{ $up ? '+' : '−' }}{{ number_format(abs($pricePct), 1) }}%
               </div>
               @endif
            </td>
            @endif
         </tr>
      </table>
   </td>
</tr>

@include('emails.product_updates._product', ['statusLabel' => 'Price ' . $priceDirection, 'statusColor' => $tone])
@include('emails.product_updates._remarks')
@include('emails.product_updates._steps', ['tone' => $down ? 'good' : 'warn', 'steps' => $isDealer ? [
   'Purchase orders placed from ' . $effectiveOn . ' will be at the new dealer price.',
   'Please review the selling price to your customers accordingly.',
   'For orders already placed, please get in touch with your Greenwave representative.',
] : [
   'Please use the new dealer price for all quotations and orders from ' . $effectiveOn . '.',
   'Let your customers and dealers know about the revised price.',
   'For orders already placed, please check with the head office.',
]])
@endsection
