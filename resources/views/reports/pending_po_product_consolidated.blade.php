<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8">
@include('reports._pdf_header', ['title' => 'Pending PO – Product Wise (Consolidated)'])
</head>
<body>

<table class="rpt">
  <thead>
    <tr>
      <th style="width:36px">S.No.</th>
      <th>Product Name</th>
      <th class="r" style="width:100px">Qty (kg)</th>
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

{{-- Totals: spacer left, both values stacked on right --}}
<table class="summary-table">
  <tr>
    <td class="summary-spacer"></td>
    <td class="summary-box">
      <span class="summary-label">Total Qty</span>
      <span class="summary-qty">{{ number_format($data['reportData']['total_qty']) }} kg</span>
      <hr class="summary-divider">
      <span class="summary-label">Total Value</span>
      <span class="summary-value">&#8377;&nbsp;{{ number_format($data['reportData']['total_value'], 2) }}</span>
    </td>
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