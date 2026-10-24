{{-- N.A. unticked: a temporarily unavailable product is back in stock. --}}
{{-- "Hi Ajay," for an employee, "Dear Kamal Singhania," for a dealer. --}}
@php
   $isDealer = ($audience ?? 'employee') === 'dealer';
   $greeting = $isDealer
      ? 'Dear ' . (($recipient->person ?? null) ?: ($recipient->name ?: 'Partner')) . ','
      : 'Hi ' . ($recipient->name ?: 'there') . ',';
@endphp
@extends('emails.product_updates.layout')

@section('title', 'Product Back in Stock')
@section('heading', 'Product Back in Stock')
@section('ribbon', $product->product_name)
@section('ribbon_badge')<span class="ribbon-badge">Available</span>@endsection

@section('greeting', $greeting)
@section('intro')
   Good news! <strong>{{ $product->product_name }}</strong>, which was temporarily not available, is back in
   stock and available again with effect from <strong>{{ $effectiveOn }}</strong>.
@endsection

@section('content')
@include('emails.product_updates._product', ['statusLabel' => 'Available', 'statusColor' => '#2e7d32'])
@include('emails.product_updates._remarks')
@include('emails.product_updates._steps', ['tone' => 'good', 'steps' => $isDealer ? [
   'You can place purchase orders for this product again.',
   'Let your customers who were waiting for it know that it is back in stock.',
] : [
   'You can offer this product and book orders for it again.',
   'Let the customers and dealers who were waiting for it know that it is back in stock.',
]])
@endsection
