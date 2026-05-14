<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">
@include('reports._pdf_header', ['title' => 'In-transit Material – Date Wise'])
</head><body>

@php $grandQty = 0; @endphp

@forelse($data['reportData']['dates'] as $dateGroup)
@php $grandQty += $dateGroup['date_qty']; @endphp

<div class="date-bar">Invoice Date: {{ $dateGroup['date'] }}</div>
<table style="margin-bottom:14px;">
  <thead>
    <tr class="sub-hdr">
      <th style="width:90px">Inv. Date</th>
      <th style="width:130px">Inv. No.</th>
      <th>Product Name</th>
      <th class="r" style="width:80px">Qty (kg)</th>
      <th style="width:90px">L.R. Date</th>
      <th style="width:120px">LR No.</th>
    </tr>
  </thead>
  <tbody>
    @foreach($dateGroup['lines'] as $line)
    <tr>
      <td>{{ $line['inv_date'] }}</td>
      <td>{{ $line['inv_no'] }}</td>
      <td>{{ $line['product_name'] }}</td>
      <td class="r">{{ number_format($line['qty']) }} kg</td>
      <td>{{ $line['lr_date'] ?: '—' }}</td>
      <td>{{ $line['lr_no'] ?: '—' }}</td>
    </tr>
    @endforeach
    <tr class="tot">
      <td colspan="3" class="r">Total Qty</td>
      <td class="r">{{ number_format($dateGroup['date_qty']) }} kg</td>
      <td colspan="2"></td>
    </tr>
  </tbody>
</table>

@empty
<p style="padding:20px;text-align:center;color:#90a4ae;font-style:italic;">No in-transit material found.</p>
@endforelse

<div class="grand">
  <span>Grand Total Qty</span>
  <span>{{ number_format($grandQty) }} kg</span>
</div>

<div class="footer"><span>Greenwave — Confidential</span><span>Auto-generated.</span></div>
</body></html>
