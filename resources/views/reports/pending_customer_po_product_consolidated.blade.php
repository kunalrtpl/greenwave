<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8">
@include('reports._pdf_style')
</head>
<body>
@include('reports._pdf_header', ['title' => 'Pending Customer Orders – Product Wise (Consolidated)'])

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

<table class="callout-total">
  <tr>
    <td class="spacer"></td>
    <td class="box">
      <span class="ct-lbl">Total Qty</span><br>
      <span class="ct-val">{{ number_format($data['reportData']['total_qty']) }} kg</span>
    </td>
  </tr>
</table>

<table class="footer-table">
  <tr>
    <td>Auto-generated. Pending qty is real-time at time of generation.</td>
  </tr>
</table>
</body>
</html>
