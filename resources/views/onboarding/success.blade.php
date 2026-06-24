<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Submitted – Greenwave Global</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{background:linear-gradient(135deg,#e8f5e9,#e0f2f1);min-height:100vh;font-family:'Segoe UI',system-ui,sans-serif;display:flex;flex-direction:column;align-items:center;}
header{width:100%;background:#1a2332;padding:13px 22px;display:flex;align-items:center;gap:12px;}
.lb{width:38px;height:38px;background:#2e7d32;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:17px;font-weight:900;}
.lt{color:#fff;font-size:15px;font-weight:700;}
.box{max-width:540px;width:100%;margin:60px 20px;background:#fff;border-radius:12px;padding:46px 38px;text-align:center;box-shadow:0 8px 40px rgba(0,0,0,.1);}
.sico{width:76px;height:76px;background:linear-gradient(135deg,#2e7d32,#00796b);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 22px;font-size:34px;color:#fff;animation:pop .5s ease-out;}
@keyframes pop{0%{transform:scale(0);opacity:0}70%{transform:scale(1.15)}100%{transform:scale(1);opacity:1}}
h1{font-size:25px;font-weight:700;color:#1a2332;margin-bottom:9px;}
.sub{font-size:14px;color:#555;line-height:1.6;margin-bottom:26px;}
.ig{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:26px;}
.ic{flex:1;min-width:110px;background:#f4f6f9;border-radius:8px;padding:13px;}
.ic .il{font-size:10px;text-transform:uppercase;letter-spacing:.5px;color:#888;}
.ic .iv{font-size:14px;font-weight:700;color:#1a2332;margin-top:3px;}
.wn{background:#e8f5e9;border-radius:8px;padding:16px;text-align:left;}
.wn h3{font-size:12px;text-transform:uppercase;letter-spacing:.5px;color:#2e7d32;margin-bottom:10px;}
.wn li{font-size:13px;color:#444;padding:5px 0;display:flex;align-items:flex-start;gap:8px;list-style:none;}
.wn li i{color:#2e7d32;margin-top:2px;}
footer{color:#aaa;font-size:12px;padding:20px;}
</style>
</head>
<body>
<header><div class="lb">G</div><span class="lt">Greenwave Global Limited</span></header>
<div class="box">
    <div class="sico"><i class="fa fa-check"></i></div>
    <h1>Form Submitted!</h1>
    <p class="sub">Thank you, <strong>{{ $dealer->name }}</strong>. Your {{ $isSubDealer ? 'sub-dealer' : 'channel partner' }} onboarding form has been received. Our team will review and get in touch with you shortly.</p>
    <div class="ig">
        <div class="ic"><div class="il">Business</div><div class="iv">{{ $dealer->business_name ?: $dealer->name }}</div></div>
        <div class="ic"><div class="il">Type</div><div class="iv">{{ $isSubDealer ? 'Sub Dealer' : 'Primary Dealer' }}</div></div>
        <div class="ic"><div class="il">Submitted</div><div class="iv">{{ now()->format('d M Y') }}</div></div>
    </div>
    <div class="wn">
        <h3><i class="fa fa-road"></i>&nbsp; What happens next?</h3>
        <ul>
            <li><i class="fa fa-check-circle"></i><span>Our team will verify the documents you provided.</span></li>
            <li><i class="fa fa-check-circle"></i><span>You will receive a call/email within 2–3 business days.</span></li>
            <li><i class="fa fa-check-circle"></i><span>Once verified, your account will be activated with login credentials.</span></li>
        </ul>
    </div>
</div>
<footer>© {{ date('Y') }} Greenwave Global Limited · All rights reserved</footer>
</body>
</html>
