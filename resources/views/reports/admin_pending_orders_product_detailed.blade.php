<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8">
@include('reports._pdf_style')
</head>
<body>
@include('reports._pdf_header', ['title' => 'Pending Orders – Product Wise (Detailed)'])

@php $sno = 1; $grandQty = 0; @endphp

@forelse($data['reportData']['products'] as $product)
@php $grandQty += $product['total_pending_qty']; @endphp

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
    </tr>
  </thead>
</table>

{{-- Order lines --}}
<table class="rpt" style="margin-bottom:16px;">
  <thead>
    <tr class="sub-hdr">
      <th style="width:12%">PO Date</th>
      <th style="width:22%">Dealer / Customer</th>
      <th style="width:22%">PO Ref No.</th>
      <th class="r" style="width:13%">Order Qty</th>
      <th class="r" style="width:15%">Pending Qty</th>
      <th class="c" style="width:16%">Days</th>
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
      <td>{{ $order['actor_name'] ?: '—' }}</td>
      <td>{{ $order['po_ref_no'] ?: '—' }}</td>
      <td class="r">{{ number_format($order['ordered_qty']) }}</td>
      <td class="r"><strong>{{ number_format($order['pending_qty']) }}</strong></td>
      <td class="c">({{ $daysLabel }})</td>
    </tr>
    @endforeach
  </tbody>
</table>

@empty
<p style="padding:16px;text-align:center;color:#aaa;font-style:italic;">No pending orders found.</p>
@endforelse

<table class="summary-table">
  <tr>
    <td class="summary-spacer"></td>
    <td class="summary-box">
      <span class="summary-label">Total Pending Qty</span>
      <span class="summary-qty">{{ number_format($grandQty) }} kg</span>
    </td>
  </tr>
</table>

<table class="footer-table">
  <tr>
    <td>Auto-generated. Pending qty is real-time at time of generation. Covers all dealers &amp; customers.</td>
  </tr>
</table>

</body>
</html>
