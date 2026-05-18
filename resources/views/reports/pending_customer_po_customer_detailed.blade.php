<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8">
@include('reports._pdf_header', ['title' => 'Pending Customer Orders – Customer Wise (Detailed)'])
</head>
<body>

@php $sno = 1; $grandQty = 0; @endphp

@forelse($data['reportData']['actors'] as $actor)
@php $grandQty += $actor['total_qty']; @endphp

<table class="rpt" style="margin-bottom:0;border-bottom:none;">
  <tbody>
    <tr class="prod-hdr">
      <td style="width:28px">{{ $sno++ }}.</td>
      <td><strong>{{ $actor['actor_name'] }}</strong></td>
      <td class="r" style="width:95px">{{ number_format($actor['total_qty']) }} kg</td>
    </tr>
  </tbody>
</table>

<table class="rpt" style="margin-bottom:14px;border-top:none;">
  <thead>
    <tr class="sub-hdr">
      <th style="width:82px">Date</th>
      <th>Product Name</th>
      <th class="r" style="width:82px">Order Qty</th>
      <th class="r" style="width:88px">Pending Qty</th>
      <th class="c" style="width:62px">Age</th>
    </tr>
  </thead>
  <tbody>
    @php $alt = 0; @endphp
    @foreach($actor['orders'] as $order)
    @php
      $alt++;
      $cls = $order['age_days'] <= 7 ? 'age-fresh' : ($order['age_days'] <= 20 ? 'age-mid' : 'age-old');
    @endphp
    <tr class="{{ $alt % 2 == 0 ? 'alt' : '' }}">
      <td>{{ $order['po_date'] }}</td>
      <td>
        <strong>{{ $order['product_name'] }}</strong>
        <span class="prod-pack">({{ $order['packing_size'] }})</span>
      </td>
      <td class="r">{{ number_format($order['ordered_qty']) }}</td>
      <td class="r"><strong>{{ number_format($order['pending_qty']) }}</strong></td>
      <td class="c"><span class="age {{ $cls }}">{{ $order['age_days'] }}d</span></td>
    </tr>
    @endforeach
  </tbody>
</table>

@empty
<p style="padding:16px;text-align:center;color:#aaa;font-style:italic;">No pending customer orders.</p>
@endforelse

<table style="width:100%;border-collapse:collapse;margin-top:2px;">
  <tr>
    <td style="width:80%;"></td>
    <td style="width:20%;background:#2d2d2d;border:1px solid #1a1a1a;padding:7px 10px;text-align:right;">
      <span style="font-size:9px;color:#B1D83C;text-transform:uppercase;letter-spacing:0.3px;">Total Pending Qty</span><br>
      <strong style="font-size:13px;color:#fff;">{{ number_format($grandQty) }} kg</strong>
    </td>
  </tr>
</table>

<table class="footer-table">
  <tr>
    <td>Auto-generated. Pending qty is real-time at time of generation.</td>
    <td class="r">Greenwave — Confidential</td>
  </tr>
</table>
</body>
</html>
