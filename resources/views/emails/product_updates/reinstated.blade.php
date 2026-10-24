{{-- Discont. unticked: a discontinued product is available again. --}}
{{-- "Hi Ajay," for an employee, "Dear Kamal Singhania," for a dealer. --}}
@php
   $isDealer = ($audience ?? 'employee') === 'dealer';
   $greeting = $isDealer
      ? 'Dear ' . (($recipient->person ?? null) ?: ($recipient->name ?: 'Partner')) . ','
      : 'Hi ' . ($recipient->name ?: 'there') . ',';
@endphp
@extends('emails.product_updates.layout')

@section('title', 'Product Available Again')
@section('heading', 'Product Available Again')
@section('ribbon', $product->product_name)
@section('ribbon_badge')<span class="ribbon-badge">Available Again</span>@endsection

@section('greeting', $greeting)
@section('intro')
   Good news! <strong>{{ $product->product_name }}</strong>, which was previously discontinued, has been
   reinstated and is available again with effect from <strong>{{ $effectiveOn }}</strong>.
   @if($isDealer) We look forward to your orders. @endif
@endsection

@section('content')
@include('emails.product_updates._product', ['statusLabel' => 'Available (reinstated)', 'statusColor' => '#2e7d32'])
@include('emails.product_updates._remarks')
@include('emails.product_updates._steps', ['tone' => 'good', 'steps' => $isDealer ? [
   'You can place purchase orders for this product again.',
   'Let your customers who used this product know that it is back.',
   'For current pricing and dispatch times, please get in touch with your Greenwave representative.',
] : [
   'You can offer this product to customers and book orders for it again.',
   'Let the customers and dealers who used it know that it is back.',
   'For current pricing and dispatch times, please check with the head office.',
]])
@endsection
