<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8">
@include('reports._pdf_style')
</head>
<body>
@include('reports._pdf_header', ['title' => 'In-Transit Material – Date Wise'])

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

<table class="callout-total">
  <tr>
    <td class="spacer"></td>
    <td class="box">
      <span class="ct-lbl">Total In-Transit Qty</span><br>
      <span class="ct-val">{{ number_format($grandQty) }} kg</span>
    </td>
  </tr>
</table>

<table class="footer-table">
  <tr>
    <td>In-transit = Invoice raised but delivery not yet confirmed.</td>
  </tr>
</table>
</body>
</html>
