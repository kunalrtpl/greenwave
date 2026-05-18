<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8">
@include('reports._pdf_header', ['title' => 'In-Transit Material – Product Wise'])
</head>
<body>

@php $sno = 1; $grandQty = 0; @endphp

@forelse($data['reportData']['products'] as $product)
@php $grandQty += $product['total_qty']; @endphp

<table class="rpt" style="margin-bottom:0;border-bottom:none;">
  <tbody>
    <tr class="prod-hdr">
      <td style="width:28px">{{ $sno++ }}.</td>
      <td>
        {{ $product['product_name'] }}
        <span style="font-size:9px;font-weight:normal;font-style:italic;color:#444;">&nbsp;({{ $product['packing_size'] }})</span>
      </td>
      <td class="r" style="width:95px">{{ number_format($product['total_qty']) }} kg</td>
    </tr>
  </tbody>
</table>

<table class="rpt" style="margin-bottom:14px;border-top:none;">
  <thead>
    <tr class="sub-hdr">
      <th style="width:82px">Date</th>
      <th style="width:130px">Invoice No.</th>
      <th class="r" style="width:82px">Qty (kg)</th>
      <th style="width:82px">LR Date</th>
      <th>LR No.</th>
    </tr>
  </thead>
  <tbody>
    @php $alt = 0; @endphp
    @foreach($product['invoices'] as $inv)
    @php $alt++; @endphp
    <tr class="{{ $alt % 2 == 0 ? 'alt' : '' }}">
      <td>{{ $inv['inv_date'] }}</td>
      <td>{{ $inv['inv_no'] }}</td>
      <td class="r"><strong>{{ number_format($inv['qty']) }}</strong></td>
      <td>{{ $inv['lr_date'] ?: '—' }}</td>
      <td>{{ $inv['lr_no'] ?: '—' }}</td>
    </tr>
    @endforeach
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
