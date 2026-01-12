<!DOCTYPE html>
<html>
<head>
    <title></title>

    <style>
        body {
            font-family: Calibri, Arial, sans-serif;
            font-size: 14px;
            background: #ffffff;
            color: #000;
        }

        @page {
            size: A4;
            margin: 10mm;
        }

        /* ================= HEADERS ================= */
        .main-title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 12px;
        }

        .product-name {
            background: #0b5a2b;
            color: #ffffff;
            font-weight: bold;
            padding: 6px 12px;
            display: inline-block;
            margin-bottom: 15px;
        }

        .module-box {
            border: 2px solid #000;
            padding: 12px;
            margin-top: 20px;
        }

        .section-header {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .pack-size {
            float: right;
            background: #8b3e12;
            color: #ffffff;
            padding: 6px 12px;
            font-weight: bold;
        }

        /* ================= TABLE ================= */
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; /* 🔥 CRITICAL */
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            white-space: nowrap;   /* 🔥 NO WRAP */
            font-size: 13px;
        }

        th {
            background: #f2f2f2;
            font-weight: bold;
        }

        .data-row {
            background: #fde9d9;
        }

        .total-row td {
            background: #8b3e12;
            color: #ffffff;
            font-weight: bold;
        }

        .packing-loss {
            margin-top: 10px;
            text-align: right;
            font-style: italic;
        }

        /* ================= SUMMARY ================= */
        .summary-box {
            margin-top: 15px;
            width: 55%;
            float: right;
            border-collapse: collapse;
        }

        .summary-box td {
            border: 1px solid #000;
            padding: 6px;
            background: #e9f2fb;
            font-weight: bold;
        }

        .summary-box td:last-child {
            text-align: right;
        }

        .grand-total td {
            background: #1f5a91;
            color: #fff;
            font-size: 16px;
        }

        .additional-cost td {
            background: #ffff00;
            font-weight: bold;
            font-size: 16px;
        }

        .pdf-btn {
            float: right;
            margin-bottom: 10px;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .pdf-btn {
                display: none !important;
            }
        }
    </style>
</head>

<body>

<script>
    function printPage() {
        const oldTitle = document.title;
        document.title = "{{ $product->product_name }}";
        window.print();
        setTimeout(() => document.title = oldTitle, 1000);
    }
</script>

<div class="pdf-btn">
    <button type="button" onclick="printPage()">Generate PDF</button>
</div>

<div style="clear: both;"></div>

<div class="main-title">Additional Cost (Mini Pack Order)</div>

<div class="product-name">{{ $product->product_name }}</div>

{{-- ================= STANDARD PACK ================= --}}
<div class="module-box">
    <div class="section-header">
        Standard Pack Cost (per kg)
        <span class="pack-size">({{ $standardPack['standardPackKg'] }} kg)</span>
        <div style="clear: both;"></div>
    </div>

    <table>
        <colgroup>
            <col style="width:5%">
            <col style="width:22%">
            <col style="width:23%">
            <col style="width:10%">
            <col style="width:13%">
            <col style="width:12%">
            <col style="width:15%">
        </colgroup>

        <thead>
        <tr>
            <th style="font-size: 11px;">S.No</th>
            <th>Description</th>
            <th>Details</th>
            <th>No. of <br> Units</th>
            <th>Unit Price <br>(Rs.)</th>
            <th>Order Size <br>(kg)</th>
            <th>Cost <br>(Rs. per kg)</th>
        </tr>
        </thead>

        <tbody>
        @foreach($standardPack['rows'] as $i => $row)
        <tr class="data-row">
            <td>{{ $i+1 }}</td>
            <td>{{ $row['description'] }}</td>
            <td>{{ $row['details'] }}</td>
            <td>{{ $row['units'] }}</td>
            <td>{{ $row['unit_price'] }}</td>
            <td>{{ $row['order_size'] }}</td>
            <td>{{ $row['cost_per_kg'] }}</td>
        </tr>
        @endforeach

        <tr class="total-row">
            <td colspan="6">Packing Cost (per kg)</td>
            <td>{{ $standardPack['totalPerKg'] }}</td>
        </tr>
        </tbody>
    </table>

    <div class="packing-loss">
        Packing Loss {{ $standardPack['packing_loss'] }}%
    </div>
</div>

@if(isset($miniPack1kg10))
{{-- ================= MINI PACK ================= --}}
<div class="module-box">
    <div class="section-header">
        Mini Pack Cost (per kg)
        <span class="pack-size">({{$miniPack1kg10['pack_label']}})</span>
        <div style="clear: both;"></div>
    </div>

    <table>
        <colgroup>
            <col style="width:5%">
            <col style="width:22%">
            <col style="width:23%">
            <col style="width:10%">
            <col style="width:13%">
            <col style="width:12%">
            <col style="width:15%">
        </colgroup>

        <thead>
        <tr>
            <th style="font-size: 11px;">S.No</th>
            <th>Description</th>
            <th>Details</th>
            <th>No. of <br> Units</th>
            <th>Unit Price <br>(Rs.)</th>
            <th>Order Size <br>(kg)</th>
            <th>Cost <br>(Rs. per kg)</th>
        </tr>
        </thead>

        <tbody>
        @foreach($miniPack1kg10['rows'] as $i => $row)
        <tr class="data-row">
            <td>{{ $i+1 }}</td>
            <td>{{ $row['description'] }}</td>
            <td>{{ $row['details'] }}</td>
            <td>{{ $row['units'] }}</td>
            <td>{{ $row['unit_price'] }}</td>
            <td>{{ $row['order_size'] }}</td>
            <td>{{ $row['cost_per_kg'] }}</td>
        </tr>
        @endforeach

        <tr class="total-row">
            <td colspan="6">Packing Cost (per kg)</td>
            <td>{{ $miniPack1kg10['packing_cost_per_kg'] }}</td>
        </tr>
        </tbody>
    </table>
    <div class="packing-loss">
        Packing Loss {{ $miniPack1kg10['packing_loss'] }}
    </div>
    <table class="summary-box">
        <tr>
            <td>Packing Loss Difference</td>
            <td>{{ $miniPack1kg10['loss_difference'] }}</td>
        </tr>
        <tr>
            <td>Dealer Price</td>
            <td>{{ $miniPack1kg10['dealer_price'] }}</td>
        </tr>
        <tr>
            <td>Packing Loss (per kg)</td>
            <td>{{ $miniPack1kg10['packing_loss_cost'] }}</td>
        </tr>
        <tr>
            <td>Total Mini Pack Cost (per kg)</td>
            <td>{{ $miniPack1kg10['total_mini_pack_cost'] }}</td>
        </tr>
        <tr class="additional-cost">
            <td>Additional Cost</td>
            <td>{{ $miniPack1kg10['additional_cost'] }}</td>
        </tr>
    </table>

    <div style="clear: both;"></div>
</div>
@endif

@if(isset($miniPack5kg2))
{{-- ================= MINI PACK ================= --}}
<div class="module-box">
    <div class="section-header">
        Mini Pack Cost (per kg)
        <span class="pack-size">({{$miniPack5kg2['pack_label']}})</span>
        <div style="clear: both;"></div>
    </div>

    <table>
        <colgroup>
            <col style="width:5%">
            <col style="width:22%">
            <col style="width:23%">
            <col style="width:10%">
            <col style="width:13%">
            <col style="width:12%">
            <col style="width:15%">
        </colgroup>

        <thead>
        <tr>
            <th style="font-size: 11px;">S.No</th>
            <th>Description</th>
            <th>Details</th>
            <th>No. of <br> Units</th>
            <th>Unit Price <br>(Rs.)</th>
            <th>Order Size <br>(kg)</th>
            <th>Cost <br>(Rs. per kg)</th>
        </tr>
        </thead>

        <tbody>
        @foreach($miniPack5kg2['rows'] as $i => $row)
        <tr class="data-row">
            <td>{{ $i+1 }}</td>
            <td>{{ $row['description'] }}</td>
            <td>{{ $row['details'] }}</td>
            <td>{{ $row['units'] }}</td>
            <td>{{ $row['unit_price'] }}</td>
            <td>{{ $row['order_size'] }}</td>
            <td>{{ $row['cost_per_kg'] }}</td>
        </tr>
        @endforeach

        <tr class="total-row">
            <td colspan="6">Packing Cost (per kg)</td>
            <td>{{ $miniPack5kg2['packing_cost_per_kg'] }}</td>
        </tr>
        </tbody>
    </table>
    <div class="packing-loss">
        Packing Loss {{ $miniPack5kg2['packing_loss'] }}
    </div>
    <table class="summary-box">
        <tr>
            <td>Packing Loss Difference</td>
            <td>{{ $miniPack5kg2['loss_difference'] }}</td>
        </tr>
        <tr>
            <td>Dealer Price</td>
            <td>{{ $miniPack5kg2['dealer_price'] }}</td>
        </tr>
        <tr>
            <td>Packing Loss (per kg)</td>
            <td>{{ $miniPack5kg2['packing_loss_cost'] }}</td>
        </tr>
        <tr>
            <td>Total Mini Pack Cost (per kg)</td>
            <td>{{ $miniPack5kg2['total_mini_pack_cost'] }}</td>
        </tr>
        <tr class="additional-cost">
            <td>Additional Cost</td>
            <td>{{ $miniPack5kg2['additional_cost'] }}</td>
        </tr>
    </table>

    <div style="clear: both;"></div>
</div>
@endif


@if(isset($miniPack1kg12))
{{-- ================= MINI PACK ================= --}}
<div class="module-box">
    <div class="section-header">
        Mini Pack Cost (per kg)
        <span class="pack-size">({{$miniPack1kg12['pack_label']}})</span>
        <div style="clear: both;"></div>
    </div>

    <table>
        <colgroup>
            <col style="width:5%">
            <col style="width:22%">
            <col style="width:23%">
            <col style="width:10%">
            <col style="width:13%">
            <col style="width:12%">
            <col style="width:15%">
        </colgroup>

        <thead>
        <tr>
            <th style="font-size: 11px;">S.No</th>
            <th>Description</th>
            <th>Details</th>
            <th>No. of <br> Units</th>
            <th>Unit Price <br>(Rs.)</th>
            <th>Order Size <br>(kg)</th>
            <th>Cost <br>(Rs. per kg)</th>
        </tr>
        </thead>

        <tbody>
        @foreach($miniPack1kg12['rows'] as $i => $row)
        <tr class="data-row">
            <td>{{ $i+1 }}</td>
            <td>{{ $row['description'] }}</td>
            <td>{{ $row['details'] }}</td>
            <td>{{ $row['units'] }}</td>
            <td>{{ $row['unit_price'] }}</td>
            <td>{{ $row['order_size'] }}</td>
            <td>{{ $row['cost_per_kg'] }}</td>
        </tr>
        @endforeach

        <tr class="total-row">
            <td colspan="6">Packing Cost (per kg)</td>
            <td>{{ $miniPack1kg12['packing_cost_per_kg'] }}</td>
        </tr>
        </tbody>
    </table>
    <div class="packing-loss">
        Packing Loss {{ $miniPack1kg12['packing_loss'] }}
    </div>
    <table class="summary-box">
        <tr>
            <td>Packing Loss Difference</td>
            <td>{{ $miniPack1kg12['loss_difference'] }}</td>
        </tr>
        <tr>
            <td>Dealer Price</td>
            <td>{{ $miniPack1kg12['dealer_price'] }}</td>
        </tr>
        <tr>
            <td>Packing Loss (per kg)</td>
            <td>{{ $miniPack1kg12['packing_loss_cost'] }}</td>
        </tr>
        <tr>
            <td>Total Mini Pack Cost (per kg)</td>
            <td>{{ $miniPack1kg12['total_mini_pack_cost'] }}</td>
        </tr>
        <tr class="additional-cost">
            <td>Additional Cost</td>
            <td>{{ $miniPack1kg12['additional_cost'] }}</td>
        </tr>
    </table>

    <div style="clear: both;"></div>
</div>
@endif
</body>
</html>
