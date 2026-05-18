<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8">
@include('reports._pdf_header', [
  'title' => 'Pending PO – Product Wise (Detailed)' . ($data['ctx']->withPrice ? ' with Price' : '')
])
</head>
<body>

@php $sno = 1; $grandQty = 0; $grandVal = 0; @endphp

@forelse($data['reportData']['products'] as $product)
@php $grandQty += $product['total_pending_qty']; $grandVal += $product['total_value']; @endphp

{{-- Product header: same dark style as consolidated thead --}}
<table class="rpt" style="margin-bottom:0;">
  <thead>
    <tr>
      <th style="width:28px">{{ $sno++ }}.</th>
      <th>
        {{ $product['product_name'] }}
        <span class="prod-pack">({{ $product['packing_size'] }})</span>
      </th>
      <th class="r" style="width:100px">{{ number_format($product['total_pending_qty']) }} kg</th>
      <th class="r" style="width:120px">&#8377;&nbsp;{{ number_format($product['total_value'], 2) }}</th>
    </tr>
  </thead>
</table>

{{-- Order lines --}}
<table class="rpt" style="margin-bottom:16px;">
  <thead>
    <tr class="sub-hdr">
      <th style="width:82px">PO Date</th>
      <th>PO Ref No.</th>
      <th class="r" style="width:82px">Order Qty</th>
      <th class="r" style="width:88px">Pending Qty</th>
      @if($data['ctx']->withPrice)
      <th class="r" style="width:78px">Price (&#8377;)</th>
      <th class="r" style="width:90px">Value (&#8377;)</th>
      @endif
      <th class="c" style="width:72px">Days</th>
    </tr>
  </thead>
  <tbody>
    @php $alt = 0; @endphp
    @foreach($product['orders'] as $order)
    @php
      $alt++;
      $d = $order['age_days'];
      $daysLabel = $d == 0 ? '0 days' : ($d == 1 ? '1 day' : $d . ' days');
    @endphp
    <tr class="{{ $alt % 2 == 0 ? 'alt' : '' }}">
      <td>{{ $order['po_date'] }}</td>
      <td>{{ $order['po_ref_no'] ?: '—' }}</td>
      <td class="r">{{ number_format($order['ordered_qty']) }}</td>
      <td class="r"><strong>{{ number_format($order['pending_qty']) }}</strong></td>
      @if($data['ctx']->withPrice)
      <td class="r">{{ number_format($order['unit_price'] ?? 0, 2) }}</td>
      <td class="r">{{ number_format($order['line_value'] ?? 0, 2) }}</td>
      @endif
      <td class="c">({{ $daysLabel }})</td>
    </tr>
    @endforeach
  </tbody>
</table>

@empty
<p style="padding:16px;text-align:center;color:#aaa;font-style:italic;">No pending orders found.</p>
@endforelse

{{-- Summary: same as consolidated --}}
<table class="summary-table">
  <tr>
    <td class="summary-spacer"></td>
    <td class="summary-box">
      <span class="summary-label">Total Pending Qty</span>
      <span class="summary-qty">{{ number_format($grandQty) }} kg</span>
      <hr class="summary-divider">
      <span class="summary-label">Total Value</span>
      <span class="summary-value">&#8377;&nbsp;{{ number_format($grandVal, 2) }}</span>
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