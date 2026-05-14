<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8">
@include('reports._pdf_header', ['title' => 'Pending PO – Product Wise (Consolidated)'])
</head>
<body>

<table class="rpt">
  <thead>
    <tr>
      <th style="width:38px">S.No.</th>
      <th>Product Name</th>
      <th class="r" style="width:90px">Qty (kg)</th>
      <th class="r" style="width:110px">Value (&#8377;)</th>
    </tr>
  </thead>
  <tbody>
    @php $i = 1; @endphp
    @forelse($data['reportData']['rows'] as $row)
    <tr class="{{ $i % 2 == 0 ? 'alt' : '' }}">
      <td class="c">{{ $i++ }}.</td>
      <td>
        <strong>{{ $row['product_name'] }}</strong>
        <span class="prod-pack">({{ $row['packing_size'] }})</span>
      </td>
      <td class="r">{{ number_format($row['total_qty']) }}</td>
      <td class="r">&#8377; {{ number_format($row['total_value'], 2) }}</td>
    </tr>
    @empty
    <tr><td colspan="4" class="c" style="padding:16px;color:#aaa;font-style:italic;">No pending orders found.</td></tr>
    @endforelse
  </tbody>
  <tfoot>
    <tr class="tot">
      <td colspan="2" class="r">TOTAL</td>
      <td class="r">{{ number_format($data['reportData']['total_qty']) }} kg</td>
      <td class="r">&#8377; {{ number_format($data['reportData']['total_value'], 2) }}</td>
    </tr>
  </tfoot>
</table>

<table class="footer-table">
  <tr>
    <td>Auto-generated report. Pending qty is real-time at time of generation.</td>
    <td class="r">Greenwave — Confidential</td>
  </tr>
</table>

</body>
</html>
