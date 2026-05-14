<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8">
@include('reports._pdf_header', [
  'title' => 'Pending PO – Date Wise' . ($data['ctx']->withPrice ? ' with Price' : '')
])
</head>
<body>

@php $grandQty = 0; $grandVal = 0; @endphp

@forelse($data['reportData']['dates'] as $dateGroup)
@php $grandQty += $dateGroup['date_qty']; $grandVal += $dateGroup['date_value']; @endphp

<div class="date-bar">{{ $dateGroup['date'] }}</div>
<table class="rpt" style="margin-bottom:14px;">
  <thead>
    <tr class="sub-hdr">
      <th style="width:80px">PO Date</th>
      <th style="width:110px">PO Ref No.</th>
      <th>Product Name</th>
      <th class="r" style="width:72px">Order Qty</th>
      <th class="r" style="width:75px">Pending Qty</th>
      @if($data['ctx']->withPrice)
      <th class="r" style="width:72px">Price (&#8377;)</th>
      <th class="r" style="width:85px">Value (&#8377;)</th>
      @endif
      <th class="c" style="width:50px">Age</th>
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
      <td>{{ $line['po_ref_no'] ?: '—' }}</td>
      <td>
        {{ $line['product_name'] ?? '' }}
        @if(!empty($line['packing_size']))
          <span class="prod-pack">({{ $line['packing_size'] }})</span>
        @endif
      </td>
      <td class="r">{{ number_format($line['ordered_qty']) }}</td>
      <td class="r"><strong>{{ number_format($line['pending_qty']) }}</strong></td>
      @if($data['ctx']->withPrice)
      <td class="r">{{ number_format($line['unit_price'] ?? 0, 2) }}</td>
      <td class="r">{{ number_format($line['line_value'] ?? 0, 2) }}</td>
      @endif
      <td class="c"><span class="age {{ $cls }}">{{ $line['age_days'] }}d</span></td>
    </tr>
    @endforeach
    <tr class="tot">
      <td colspan="{{ $data['ctx']->withPrice ? 4 : 4 }}" class="r">Date Total</td>
      <td class="r">{{ number_format($dateGroup['date_qty']) }} kg</td>
      @if($data['ctx']->withPrice)
      <td></td>
      <td class="r">&#8377; {{ number_format($dateGroup['date_value'], 2) }}</td>
      @endif
      <td></td>
    </tr>
  </tbody>
</table>

@empty
<p style="padding:16px;text-align:center;color:#aaa;font-style:italic;">No pending orders found.</p>
@endforelse

<table class="grand-bar">
  <tr>
    <td>GRAND TOTAL QTY</td>
    <td class="r">{{ number_format($grandQty) }} kg</td>
    <td class="r" style="width:180px">TOTAL VALUE &nbsp; &#8377; {{ number_format($grandVal, 2) }}</td>
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
