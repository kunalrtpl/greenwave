<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8">
@include('reports._pdf_header', ['title' => 'Pending Customer Orders – Date Wise'])
</head>
<body>

@php $grandQty = 0; @endphp

@forelse($data['reportData']['dates'] as $dateGroup)
@php $grandQty += $dateGroup['date_qty']; @endphp

<div class="date-bar">{{ $dateGroup['date'] }}</div>
<table class="rpt" style="margin-bottom:14px;">
  <thead>
    <tr class="sub-hdr">
      <th style="width:80px">Date</th>
      <th style="width:130px">Customer</th>
      <th>Product Name</th>
      <th class="r" style="width:80px">Order Qty</th>
      <th class="r" style="width:85px">Pending Qty</th>
      <th class="c" style="width:60px">Age</th>
    </tr>
  </thead>
  <tbody>
    @php $alt = 0; @endphp
    @foreach($dateGroup['lines'] as $line)
    @php
      $alt++;
      $cls = $line['age_days'] <= 7 ? 'age-fresh' : ($line['age_days'] <= 20 ? 'age-mid' : 'age-old');
    @endphp
    <tr class="{{ $alt % 2 == 0 ? 'alt' : '' }}">
      <td>{{ $line['po_date'] }}</td>
      <td>{{ $line['actor_name'] ?? '—' }}</td>
      <td>
        {{ $line['product_name'] ?? '' }}
        @if(!empty($line['packing_size']))
          <span class="prod-pack">({{ $line['packing_size'] }})</span>
        @endif
      </td>
      <td class="r">{{ number_format($line['ordered_qty']) }}</td>
      <td class="r"><strong>{{ number_format($line['pending_qty']) }}</strong></td>
      <td class="c"><span class="age {{ $cls }}">{{ $line['age_days'] }}d</span></td>
    </tr>
    @endforeach
    <tr class="tot">
      <td colspan="4" class="r">Date Total</td>
      <td class="r">{{ number_format($dateGroup['date_qty']) }} kg</td>
      <td></td>
    </tr>
  </tbody>
</table>

@empty
<p style="padding:16px;text-align:center;color:#aaa;font-style:italic;">No pending customer orders.</p>
@endforelse

<table class="grand-bar">
  <tr>
    <td>GRAND TOTAL PENDING QTY</td>
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
