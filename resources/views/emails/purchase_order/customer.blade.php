<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { 
            font-family: Arial, Helvetica, sans-serif; 
            background:#f4f6f8; 
            padding:20px; 
        }
        .email-container { 
            max-width:750px; 
            margin:auto; 
            background:#ffffff; 
            padding:25px; 
            border-radius:12px; 
            box-shadow:0px 3px 12px rgba(0,0,0,0.1);
        }
        .logo { text-align:center; margin-bottom:25px; }
        .logo img { max-width:220px; }
        .header { 
            background:#007a3d; 
            color:#fff; 
            padding:18px; 
            font-size:22px; 
            font-weight:bold; 
            border-radius:8px; 
            text-align:center; 
            letter-spacing:0.5px;
        }
        .intro-box {
            background:#e8f5e9;
            padding:18px;
            border-left:5px solid #2e7d32;
            border-radius:8px;
            margin-top:20px;
            font-size:14px;
            color:#2e7d32;
            line-height:1.6;
        }
        .section-title { 
            font-size:18px; 
            font-weight:bold; 
            margin-top:30px; 
            margin-bottom:12px;
            color:#333;
        }
        table { width:100%; border-collapse:collapse; margin-top:15px; font-size:14px; }
        table th { 
            background:#f2f2f2; 
            padding:10px; 
            text-align:left; 
            border-bottom:1px solid #ddd; 
            font-weight:bold;
        }
        table td { 
            padding:10px; 
            border-bottom:1px solid #ececec;
        }
        .summary-box { 
            background:#fafafa; 
            padding:15px; 
            border-radius:8px; 
            margin-top:20px; 
            border:1px solid #e5e5e5;
        }
        .footer { 
            margin-top:35px; 
            font-size:12px; 
            color:#888; 
            text-align:center; 
        }
    </style>
</head>

<body>
<div class="email-container">

    <!-- Logo -->
    <div class="logo">
        <img src="https://g2app.in/images/greenwave-logo-1-275-sl.jpg" alt="Greenwave Logo">
    </div>

    <!-- Header -->
    <div class="header">
        Your Purchase Order is Confirmed
    </div>

    <!-- Intro Box -->
    <div class="intro-box">
        Dear <strong>{{ $po->customer->name ?? 'Customer' }}</strong>,<br><br>
        Thank you for placing your order with Greenwave.  
        Your Purchase Order has been successfully submitted and is now being processed.
    </div>

    <!-- PO Details -->
    <div class="section-title">Purchase Order Details</div>

    <p><strong>PO Number:</strong> {{ $po->po_ref_no_string }}</p>
    <p><strong>PO Date:</strong> {{ \Carbon\Carbon::parse($po->po_date)->format('d M Y') }}</p>
    <p><strong>Remarks:</strong> {{ $po->remarks }}</p>

    <!-- Items Table -->
    <div class="section-title">Order Items</div>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Qty (Kgs)</th>

                <!-- Pricing hidden (future-ready) -->
                <!--
                <th>Price</th>
                <th>Net Price</th>
                <th>Total</th>
                -->
            </tr>
        </thead>

        <tbody>
        @foreach($po->orderitems as $item)
            <tr>
                <td>{{ $item->product->product_name ?? '' }}</td>
                <td>{{ $item->qty }} Kgs</td>

                <!-- Hidden pricing -->
                <!--
                <td>₹{{ number_format($item->product_price,2) }}</td>
                <td>₹{{ number_format($item->net_price,2) }}</td>
                <td>₹{{ number_format($item->qty * $item->net_price,2) }}</td>
                -->
            </tr>
        @endforeach
        </tbody>
    </table>

    <!-- Hidden Summary -->
    <!--
    <div class="summary-box">
        <p><strong>Subtotal:</strong> ₹{{ number_format($po->price,2) }}</p>
        <p><strong>GST ({{ $po->gst_per }}%):</strong> ₹{{ number_format($po->gst,2) }}</p>
        <p style="font-size:18px;"><strong>Grand Total:</strong> ₹{{ number_format($po->grand_total,2) }}</strong></p>
    </div>
    -->

    <p style="margin-top:25px;">
        Thank you for choosing <strong>Greenwave</strong>.  
        We value your business and are committed to serving you with the best quality.
    </p>

    <div class="footer">
        This is an automated email. Please do not reply.
    </div>

</div>
</body>
</html>
