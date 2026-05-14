<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">
@include('reports._pdf_header', ['title' => 'In-transit Material – Product Wise'])
</head><body>

@php $sno = 1; $grandQty = 0; @endphp

@forelse($data['reportData']['products'] as $product)
@php $grandQty += $product['total_qty']; @endphp

<table style="margin-bottom:0;">
  <tbody>
    <tr class="prod-hdr">
      <td style="width:36px">{{ $sno++ }}.</td>
      <td><strong>{{ $product['product_name'] }}</strong>
        <span class="pack" style="margin-left:6px;">{{ $product['packing_size'] }}</span>
      </td>
      <td class="r" style="width:100px">{{ number_format($product['total_qty']) }} kg</td>
    </tr>
  </tbody>
</table>

<table style="margin-bottom:14px;">
  <thead>
    <tr class="sub-hdr">
      <th style="width:90px">Date</th>
      <th style="width:130px">Inv. No.</th>
      <th class="r" style="width:80px">Qty (kg)</th>
      <th style="width:90px">L.R. Date</th>
      <th>LR No.</th>
    </tr>
  </thead>
  <tbody>
    @foreach($product['invoices'] as $inv)
    <tr>
      <td>{{ $inv['inv_date'] }}</td>
      <td>{{ $inv['inv_no'] }}</td>
      <td class="r">{{ number_format($inv['qty']) }} kg</td>
      <td>{{ $inv['lr_date'] ?: '—' }}</td>
      <td>{{ $inv['lr_no'] ?: '—' }}</td>
    </tr>
    @endforeach
  </tbody>
</table>

@empty
<p style="padding:20px;text-align:center;color:#90a4ae;font-style:italic;">No in-transit material found.</p>
@endforelse

<div class="grand">
  <span>Total Qty</span><span>{{ number_format($grandQty) }} kg</span>
</div>

<div class="footer"><span>Greenwave — Confidential</span><span>Auto-generated.</span></div>
</body></html>
