<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8">
@include('reports._pdf_header', ['title' => 'Pending Customer Orders – Product Wise (Consolidated)'])
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
      <td class="r">{{ number_format($row['total_qty']) }}</td>
    </tr>
    @empty
    <tr><td colspan="3" class="c" style="padding:18px;color:#aaa;font-style:italic;">No pending customer orders.</td></tr>
    @endforelse
  </tbody>
</table>

<table style="width:100%;border-collapse:collapse;margin-top:2px;">
  <tr>
    <td style="width:80%;"></td>
    <td style="width:20%;background:#2d2d2d;border:1px solid #1a1a1a;padding:7px 10px;text-align:right;">
      <span style="font-size:9px;color:#B1D83C;text-transform:uppercase;letter-spacing:0.3px;">Total Qty</span><br>
      <strong style="font-size:13px;color:#fff;">{{ number_format($data['reportData']['total_qty']) }} kg</strong>
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
