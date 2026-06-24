<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Already Submitted – Greenwave Global</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{background:linear-gradient(135deg,#e8f5e9,#e0f2f1);min-height:100vh;font-family:'Segoe UI',system-ui,sans-serif;display:flex;flex-direction:column;align-items:center;}
header{width:100%;background:#1a2332;padding:13px 22px;display:flex;align-items:center;gap:12px;}
.lb{width:38px;height:38px;background:#2e7d32;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:17px;font-weight:900;}
.lt{color:#fff;font-size:15px;font-weight:700;}
.box{max-width:460px;width:100%;margin:80px 20px;background:#fff;border-radius:12px;padding:46px 38px;text-align:center;box-shadow:0 8px 40px rgba(0,0,0,.1);}
.ico{width:70px;height:70px;background:#d4edda;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:30px;color:#155724;}
h1{font-size:23px;font-weight:700;color:#1a2332;margin-bottom:9px;}
p{font-size:14px;color:#666;line-height:1.6;}
footer{color:#aaa;font-size:12px;padding:20px;}
</style>
</head>
<body>
<header><div class="lb">G</div><span class="lt">Greenwave Global Limited</span></header>
<div class="box">
    <div class="ico"><i class="fa fa-check-circle"></i></div>
    <h1>Already Submitted</h1>
    <p>Your onboarding form for <strong>{{ $dealer->business_name ?: $dealer->name }}</strong> has already been submitted. Our team will be in touch with you soon.</p>
</div>
<footer>© {{ date('Y') }} Greenwave Global Limited · All rights reserved</footer>
</body>
</html>
