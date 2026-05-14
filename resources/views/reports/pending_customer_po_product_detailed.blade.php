<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8">
@include('reports._pdf_header', ['title' => 'Pending Customer Orders – Product Wise (Detailed)'])
</head>
<body>

@php $sno = 1; $grandQty = 0; @endphp

@forelse($data['reportData']['products'] as $product)
@php $grandQty += $product['total_pending_qty']; @endphp

<table class="rpt" style="margin-bottom:0;">
  <tbody>
    <tr class="prod-hdr">
      <td style="width:28px">{{ $sno++ }}.</td>
      <td>
        {{ $product['product_name'] }}
        <span style="font-size:9px;font-weight:normal;font-style:italic;color:#444;">
          &nbsp;({{ $product['packing_size'] }})
        </span>
      </td>
      <td class="r" style="width:90px">{{ number_format($product['total_pending_qty']) }} kg</td>
    </tr>
  </tbody>
</table>

<table class="rpt" style="margin-bottom:14px;">
  <thead>
    <tr class="sub-hdr">
      <th style="width:80px">Date</th>
      <th style="width:140px">Customer</th>
      <th class="r" style="width:80px">Order Qty</th>
      <th class="r" style="width:85px">Pending Qty</th>
      <th class="c" style="width:65px">Age</th>
    </tr>
  </thead>
  <tbody>
    @php $alt = 0; @endphp
    @foreach($product['orders'] as $order)
    @php
      $alt++;
      $cls = $order['age_days'] <= 7 ? 'age-fresh' : ($order['age_days'] <= 20 ? 'age-mid' : 'age-old');
    @endphp
    <tr class="{{ $alt % 2 == 0 ? 'alt' : '' }}">
      <td>{{ $order['po_date'] }}</td>
      <td>{{ $order['customer_name'] ?? '—' }}</td>
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

<table class="grand-bar">
  <tr>
    <td>TOTAL PENDING QTY</td>
    <td class="r">{{ number_format($grandQty) }} kg</td>
  </tr>
</table>

<table class="footer-table">
  <tr>
    <td>Auto-generated report. Pending qty is real-time at time of generation.</td>
    <td class="r">Greenwave — Confidential</td>
  </tr>
</table>
</body>
</html>
