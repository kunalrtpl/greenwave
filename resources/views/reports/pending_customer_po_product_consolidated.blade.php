<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">
@include('reports._pdf_header', ['title' => 'Pending Customer Orders – Product Wise (Consolidated)'])
</head><body>

<table>
  <thead><tr>
    <th style="width:42px">S.No.</th>
    <th>Product Name</th>
    <th>Pack Size</th>
    <th class="r" style="width:100px">Qty (kg)</th>
  </tr></thead>
  <tbody>
    @php $i=1; @endphp
    @forelse($data['reportData']['rows'] as $row)
    <tr>
      <td class="c">{{ $i++ }}.</td>
      <td><strong>{{ $row['product_name'] }}</strong></td>
      <td><span class="pack {{ $row['is_mini_pack'] ? 'mini' : '' }}">{{ $row['packing_size'] }}</span></td>
      <td class="r">{{ number_format($row['total_qty']) }} kg</td>
    </tr>
    @empty
    <tr><td colspan="4" class="c" style="padding:18px;color:#90a4ae;font-style:italic;">No pending customer orders.</td></tr>
    @endforelse
  </tbody>
  <tfoot>
    <tr class="tot">
      <td colspan="3" class="r">Total</td>
      <td class="r">{{ number_format($data['reportData']['total_qty']) }} kg</td>
    </tr>
  </tfoot>
</table>

<div class="footer"><span>Greenwave — Confidential</span><span>Auto-generated.</span></div>
</body></html>
