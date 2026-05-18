<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8">
@include('reports._pdf_header', ['title' => 'In-Transit Material – Date Wise'])
</head>
<body>

@php $grandQty = 0; @endphp

@forelse($data['reportData']['dates'] as $dateGroup)
@php $grandQty += $dateGroup['date_qty']; @endphp

<div class="date-bar">Invoice Date: {{ $dateGroup['date'] }}</div>
<table class="rpt" style="margin-bottom:14px;">
  <thead>
    <tr class="sub-hdr">
      <th style="width:80px">Inv. Date</th>
      <th style="width:120px">Invoice No.</th>
      <th>Product Name</th>
      <th class="r" style="width:75px">Qty (kg)</th>
      <th style="width:80px">LR Date</th>
      <th style="width:120px">LR No.</th>
    </tr>
  </thead>
  <tbody>
    @php $alt = 0; @endphp
    @foreach($dateGroup['lines'] as $line)
    @php $alt++; @endphp
    <tr class="{{ $alt % 2 == 0 ? 'alt' : '' }}">
      <td>{{ $line['inv_date'] }}</td>
      <td>{{ $line['inv_no'] }}</td>
      <td>{{ $line['product_name'] }}</td>
      <td class="r"><strong>{{ number_format($line['qty']) }}</strong></td>
      <td>{{ $line['lr_date'] ?: '—' }}</td>
      <td>{{ $line['lr_no'] ?: '—' }}</td>
    </tr>
    @endforeach
    <tr class="tot">
      <td colspan="3" class="r" style="font-size:9px;color:#aaa;">Date Subtotal</td>
      <td class="r">{{ number_format($dateGroup['date_qty']) }} kg</td>
      <td colspan="2"></td>
    </tr>
  </tbody>
</table>

@empty
<p style="padding:16px;text-align:center;color:#aaa;font-style:italic;">No in-transit material found.</p>
@endforelse

<table style="width:100%;border-collapse:collapse;margin-top:2px;">
  <tr>
    <td style="width:80%;"></td>
    <td style="width:20%;background:#2d2d2d;border:1px solid #1a1a1a;padding:7px 10px;text-align:right;">
      <span style="font-size:9px;color:#B1D83C;text-transform:uppercase;letter-spacing:0.3px;">Total In-Transit Qty</span><br>
      <strong style="font-size:13px;color:#fff;">{{ number_format($grandQty) }} kg</strong>
    </td>
  </tr>
</table>

<table class="footer-table">
  <tr>
    <td>In-transit = Invoice raised but delivery not yet confirmed.</td>
    <td class="r">Greenwave — Confidential</td>
  </tr>
</table>
</body>
</html>
