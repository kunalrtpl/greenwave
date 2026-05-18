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
      <td colspan="4" class="r" style="font-size:9px;color:#aaa;">Date Subtotal</td>
      <td class="r">{{ number_format($dateGroup['date_qty']) }} kg</td>
      <td></td>
    </tr>
  </tbody>
</table>

@empty
<p style="padding:16px;text-align:center;color:#aaa;font-style:italic;">No pending customer orders.</p>
@endforelse

<table style="width:100%;border-collapse:collapse;margin-top:2px;">
  <tr>
    <td style="width:80%;"></td>
    <td style="width:20%;background:#2d2d2d;border:1px solid #1a1a1a;padding:7px 10px;text-align:right;">
      <span style="font-size:9px;color:#B1D83C;text-transform:uppercase;letter-spacing:0.3px;">Grand Total Qty</span><br>
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
