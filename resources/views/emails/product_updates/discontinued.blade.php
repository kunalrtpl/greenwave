{{-- Discont. ticked. One email per linked employee / dealer ($audience). --}}
{{-- "Hi Ajay," for an employee, "Dear Kamal Singhania," for a dealer. --}}
@php
   $isDealer = ($audience ?? 'employee') === 'dealer';
   $greeting = $isDealer
      ? 'Dear ' . (($recipient->person ?? null) ?: ($recipient->name ?: 'Partner')) . ','
      : 'Hi ' . ($recipient->name ?: 'there') . ',';
@endphp
@extends('emails.product_updates.layout')

@section('title', 'Product Discontinued')
@section('heading', $isDealer ? 'Product Discontinuation Notice' : 'Product Discontinued')
@section('ribbon', $product->product_name)
@section('ribbon_badge')<span class="badge-red" style="background-color:#c62828;">Discontinued</span>@endsection

@section('greeting', $greeting)
@section('intro')
   @if($isDealer)
      We would like to inform you that <strong>{{ $product->product_name }}</strong>, which is linked to
      {{ $recipient->name ?: 'your dealership' }}, has been discontinued with effect from
      <strong>{{ $effectiveOn }}</strong>. We thank you for your continued partnership with Greenwave.
   @else
      Please note that <strong>{{ $product->product_name }}</strong>, one of the products linked to you,
      has been discontinued with effect from <strong>{{ $effectiveOn }}</strong>.
   @endif
@endsection

@section('content')
@include('emails.product_updates._product', ['statusLabel' => 'Discontinued', 'statusColor' => '#c62828'])
@include('emails.product_updates._remarks')
@include('emails.product_updates._steps', ['tone' => 'warn', 'steps' => $isDealer ? [
   'New purchase orders for this product can no longer be placed.',
   'Please inform your customers who use this product about the change.',
   'For pending orders or stock in hand, please get in touch with your Greenwave representative.',
] : [
   'Please do not offer this product or book new orders for it.',
   'Let your customers and dealers who use it know about the change.',
   'For pending orders or existing stock, please check with the head office.',
]])
@endsection
