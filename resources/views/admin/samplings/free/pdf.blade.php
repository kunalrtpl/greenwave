<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sampling Detail</title>

    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 12px;
            color: #333;
        }

        h2 {
            margin-bottom: 20px;
        }

        .section {
            border: 1px solid #ddd;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .section-title {
            font-weight: bold;
            font-size: 14px;
            padding-bottom: 6px;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            color: #2c3e50;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 5px 0;
            vertical-align: top;
        }

        .label {
            width: 35%;
            color: #666;
        }

        /* Product section must start on new page */
        .product-page {
            page-break-before: always;
        }

        .product-card {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 12px;
            border-radius: 4px;
        }

        .product-title {
            font-weight: bold;
            margin-bottom: 6px;
            color: #1a5276;
        }

        .footer-note {
            font-size: 10px;
            color: #999;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<h2 align="center">Free Sample Request Details</h2>
<!-- REQUEST INFO -->
<div class="section">
    <div class="section-title">Sample Request Information</div>
    <table>
        <tr>
            <td class="label">Sample Request ID</td>
            <td>: {{ $sampling->id }}</td>
        </tr>
        <tr>
            <td class="label">Sample Ref No</td>
            <td>: {{ $sampling->sample_ref_no_string }}</td>
        </tr>
        <tr>
            <td class="label">Request Created At</td>
            <td>: {{ $sampling->created_at ? $sampling->created_at->format('d M Y, h:i A') : '-' }}</td>
        </tr>
    </table>
</div>
<!-- EXECUTIVE -->
<div class="section">
    <div class="section-title">Executive Details</div>
    <table>
        <tr><td class="label">Name</td><td>: {{ $sampling->user->name ?? '-' }}</td></tr>
        <tr><td class="label">Email</td><td>: {{ $sampling->user->email ?? '-' }}</td></tr>
        <tr><td class="label">Mobile</td><td>: {{ $sampling->user->mobile ?? '-' }}</td></tr>
    </table>
</div>

<!-- CUSTOMER -->
<div class="section">
    <div class="section-title">Customer Details</div>

    @if($sampling->customer)
        <table>
            <tr>
                <td class="label">Customer Name</td>
                <td>: {{ $sampling->customer->name }}</td>
            </tr>
            <tr>
                <td class="label">Contact Person</td>
                <td>: {{ $sampling->customer->contact_person_name }}</td>
            </tr>
            <tr>
                <td class="label">Mobile</td>
                <td>: {{ $sampling->customer->mobile }}</td>
            </tr>
            <tr>
                <td class="label">Address</td>
                <td>: {{ $sampling->customer->address }}</td>
            </tr>
            <tr>
                <td class="label">Business Model</td>
                <td>: {{ $sampling->customer->business_model }}</td>
            </tr>
        </table>
    @else
        <div class="alert alert-warning" style="margin:0;">
            <strong>Note:</strong> Customer not added in request.
        </div>
    @endif
</div>
<!-- PURPOSE -->
<div class="section">
    <div class="section-title">Purpose</div>
    <table>
        <tr>
            <td class="label">Reason</td>
            <td>: {{ $sampling->purpose_reason_for_request }}</td>
        </tr>
    </table>
</div>

<!-- DISPATCH -->
<div class="section">
    <div class="section-title">Dispatch Details</div>
    <table>
        <tr><td class="label">Dispatch To</td><td>: {{ $sampling->dispatch_to }}</td></tr>
        <tr><td class="label">Address</td><td>: {{ $sampling->dispatch_address }}</td></tr>
    </table>
</div>
<!-- PRODUCTS (PAGE 2 ALWAYS) -->
<div class="section product-page">
    <div class="section-title">Product Details</div>

    @foreach($sampling->sampleitems as $key => $item)
        @if($item->requested_from == "user")
            <div class="product-card">
                <div class="product-title">Product {{ $key + 1 }}</div>

                <table>
                    <tr>
                        <td class="label">Product Name</td>
                        <td>: {{ $item->requested_product->product_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Pack Size</td>
                        <td>: {{ $item->pack_size }}</td>
                    </tr>
                    <tr>
                        <td class="label">No. of Packs</td>
                        <td>: {{ $item->no_of_packs }}</td>
                    </tr>
                    <tr>
                        <td class="label">Required Qty</td>
                        <td>: {{ $item->qty }} kg</td>
                    </tr>
                    <tr>
                        <td class="label">Remarks</td>
                        <td>: {{ $item->remarks ?? '--' }}</td>
                    </tr>
                </table>
            </div>
        @endif
    @endforeach
</div>

<div class="footer-note">
    This is a system generated document.
</div>

</body>
</html>
