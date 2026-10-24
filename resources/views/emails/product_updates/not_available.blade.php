{{-- N.A. ticked: temporarily not available (not discontinued). --}}
{{-- "Hi Ajay," for an employee, "Dear Kamal Singhania," for a dealer. --}}
@php
   $isDealer = ($audience ?? 'employee') === 'dealer';
   $greeting = $isDealer
      ? 'Dear ' . (($recipient->person ?? null) ?: ($recipient->name ?: 'Partner')) . ','
      : 'Hi ' . ($recipient->name ?: 'there') . ',';
@endphp
@extends('emails.product_updates.layout')

@section('title', 'Product Temporarily Not Available')
@section('heading', 'Product Temporarily Not Available')
@section('ribbon', $product->product_name)
@section('ribbon_badge')<span class="badge-red" style="background-color:#e65100;">Not Available</span>@endsection

@section('greeting', $greeting)
@section('intro')
   Please note that <strong>{{ $product->product_name }}</strong> is temporarily not available with effect
   from <strong>{{ $effectiveOn }}</strong>. The product has <strong>not</strong> been discontinued — we will
   let you know as soon as it is available again.
@endsection

@section('content')
@include('emails.product_updates._product', ['statusLabel' => 'Temporarily not available', 'statusColor' => '#e65100'])
@include('emails.product_updates._remarks')
@include('emails.product_updates._steps', ['tone' => 'warn', 'steps' => $isDealer ? [
   'New orders for this product cannot be fulfilled for the time being.',
   'Please let your customers who use it know about the short delay.',
   'For pending orders, please get in touch with your Greenwave representative.',
] : [
   'Please avoid committing new orders or delivery dates for this product until further notice.',
   'Let your customers and dealers who use it know about the short delay.',
   'For pending orders, please check with the head office.',
]])
@endsection
