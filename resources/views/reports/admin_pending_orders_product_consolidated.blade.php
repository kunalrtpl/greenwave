<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8">
@include('reports._pdf_style')
</head>
<body>
@include('reports._pdf_header', ['title' => 'Pending Orders – Product Wise (Consolidated)'])

<table class="rpt">
  <thead>
    <tr>
      <th style="width:8%">S.No.</th>
      <th style="width:66%">Product Name</th>
      <th class="r" style="width:26%">Qty (kg)</th>
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
      <td class="r">{{ number_format($row['total_qty']) }} kg</td>
    </tr>
    @empty
    <tr>
      <td colspan="3" class="c" style="padding:18px;color:#aaa;font-style:italic;">
        No pending orders found.
      </td>
    </tr>
    @endforelse
  </tbody>
</table>

<table class="summary-table">
  <tr>
    <td class="summary-spacer"></td>
    <td class="summary-box">
      <span class="summary-label">Total Qty</span>
      <span class="summary-qty">{{ number_format($data['reportData']['total_qty']) }} kg</span>
      <hr class="summary-divider">
      <span class="summary-label">Total Value</span>
      <span class="summary-value">&#8377;&nbsp;{{ format_indian_number($data['reportData']['total_value']) }}</span>
    </td>
  </tr>
</table>

<table class="footer-table">
  <tr>
    <td>Auto-generated report. Pending qty is real-time at time of generation. Covers all dealers &amp; customers.</td>
  </tr>
</table>

</body>
</html>
