{{--
   Sent to each active dealer linked to a product (dealer_linked_products)
   when the product is marked as discontinued from Product Pricing.
--}}
@extends('emails.product_discontinued.layout')

@section('title', 'Product Discontinued')
@section('heading', 'Product Discontinuation Notice')
@section('ribbon', $product->product_name)
@section('ribbon_badge')<span class="badge-red" style="background-color:#c62828;">Discontinued</span>@endsection

@section('greeting', 'Dear ' . ($recipient->person ?: $recipient->name ?: 'Partner') . ',')
@section('intro')
   We would like to inform you that <strong>{{ $product->product_name }}</strong>, which is linked to
   {{ $recipient->name ?: 'your dealership' }}, has been discontinued with effect from
   <strong>{{ $discontinuedOn }}</strong>. We thank you for your continued partnership with Greenwave.
@endsection

@section('content')
@include('emails.product_discontinued._product')

<tr>
   <td class="section section-last">
      <div class="notice" style="background:#fff4f2; border:1px solid #ffccbc; border-left:5px solid #e64a19; border-radius:10px; padding:16px 18px;">
         <div class="notice-title">What this means for you</div>
         <table role="presentation" class="steps" width="100%">
            <tr><td class="step-dot">1.</td><td>New purchase orders for this product can no longer be placed.</td></tr>
            <tr><td class="step-dot">2.</td><td>Please inform your customers who use this product about the change.</td></tr>
            <tr><td class="step-dot">3.</td><td>For pending orders or stock in hand, please get in touch with your Greenwave representative.</td></tr>
         </table>
      </div>
   </td>
</tr>
@endsection
