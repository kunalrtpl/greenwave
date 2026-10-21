{{--
   Sent to each active employee linked to a product (user_products) when the
   product is marked as discontinued from Product Pricing. One email each.
--}}
@extends('emails.product_discontinued.layout')

@section('title', 'Product Discontinued')
@section('heading', 'Product Discontinued')
@section('ribbon', $product->product_name)
@section('ribbon_badge')<span class="badge-red" style="background-color:#c62828;">Discontinued</span>@endsection

@section('greeting', 'Hi ' . ($recipient->name ?: 'there') . ',')
@section('intro')
   Please note that <strong>{{ $product->product_name }}</strong>, one of the products linked to you,
   has been discontinued with effect from <strong>{{ $discontinuedOn }}</strong>.
@endsection

@section('content')
@include('emails.product_discontinued._product')

<tr>
   <td class="section section-last">
      <div class="notice" style="background:#fff4f2; border:1px solid #ffccbc; border-left:5px solid #e64a19; border-radius:10px; padding:16px 18px;">
         <div class="notice-title">What this means for you</div>
         <table role="presentation" class="steps" width="100%">
            <tr><td class="step-dot">1.</td><td>Please do not offer this product or book new orders for it.</td></tr>
            <tr><td class="step-dot">2.</td><td>Let your customers and dealers who use it know about the change.</td></tr>
            <tr><td class="step-dot">3.</td><td>For pending orders or existing stock, please check with the head office.</td></tr>
         </table>
      </div>
   </td>
</tr>
@endsection
