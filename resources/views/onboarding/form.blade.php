<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $isSubDealer ? 'Sub-Dealer' : 'Channel Partner' }} Onboarding · Greenwave Global</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
:root{
  --ink:#14241c; --ink2:#3a4a42; --muted:#6b7a72;
  --green:#1f7a44; --green-d:#155e34; --green-l:#2f9d59;
  --soft:#e9f4ee; --soft2:#f3faf6;
  --line:#e2e8e4; --line2:#eef2f0;
  --amber:#9a6a12; --amber-s:#fdf6e8;
  --r:14px; --rs:10px;
}
*{box-sizing:border-box}
html{-webkit-text-size-adjust:100%}
body{
  margin:0; background:
    radial-gradient(1200px 480px at 100% -10%, #e3f3ea 0%, transparent 60%),
    linear-gradient(180deg,#f4f8f6 0%, #eef4f0 100%);
  min-height:100vh; font-family:'Inter',system-ui,sans-serif; color:var(--ink); font-size:15px; line-height:1.55;
}

/* header */
.hdr{position:sticky; top:0; z-index:50; background:rgba(255,255,255,.92); backdrop-filter:blur(8px); border-bottom:1px solid var(--line)}
.hdr-in{max-width:880px; margin:0 auto; padding:12px 20px; display:flex; align-items:center; justify-content:space-between; gap:14px}
.hdr-in img{height:38px; width:auto; display:block}
.secure{display:inline-flex; align-items:center; gap:7px; background:var(--soft); color:var(--green-d); border:1px solid #cfe6d8; padding:5px 12px; border-radius:30px; font-size:12px; font-weight:600; white-space:nowrap}

/* hero */
.hero{background:linear-gradient(135deg,#16261d 0%, #1d3a28 55%, #14301f 100%); color:#fff; position:relative; overflow:hidden}
.hero::after{content:""; position:absolute; right:-60px; top:-60px; width:230px; height:230px; border-radius:50%; background:rgba(47,157,89,.18)}
.hero-in{max-width:880px; margin:0 auto; padding:34px 20px 40px; text-align:center; position:relative; z-index:1}
.chip{display:inline-flex; align-items:center; gap:7px; padding:5px 14px; border-radius:30px; font-size:11.5px; font-weight:700; letter-spacing:.5px; text-transform:uppercase}
.chip.primary{background:rgba(111,66,193,.28); color:#e2d8ff; border:1px solid rgba(150,110,230,.4)}
.chip.sub{background:rgba(253,140,40,.25); color:#ffe2bd; border:1px solid rgba(253,140,40,.4)}
.greet{display:inline-flex; align-items:center; gap:7px; margin-top:14px; background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.18); padding:6px 14px; border-radius:30px; font-size:13px; color:#dff3e7}
.hero h1{font-size:25px; font-weight:800; margin:14px 0 8px; letter-spacing:-.4px}
.hero p{font-size:13.5px; color:#a9cdb8; max-width:480px; margin:0 auto}

/* layout */
.wrap{max-width:880px; margin:-22px auto 48px; padding:0 16px; position:relative; z-index:2}
.stack{display:flex; flex-direction:column; gap:16px}

/* card / section */
.sec{background:#fff; border:1px solid var(--line); border-radius:var(--r); box-shadow:0 1px 2px rgba(20,36,28,.04), 0 10px 30px rgba(20,36,28,.05); overflow:hidden}
.sec-h{display:flex; align-items:center; gap:13px; padding:18px 22px; border-bottom:1px solid var(--line2)}
.sec-h .n{width:34px; height:34px; border-radius:10px; background:var(--soft); color:var(--green-d); display:grid; place-items:center; font-weight:800; font-size:14px; flex-shrink:0}
.sec-h .tt{font-size:16px; font-weight:700; letter-spacing:-.2px}
.sec-h .tt small{display:block; font-weight:400; color:var(--muted); font-size:12.5px; margin-top:1px}
.sec-b{padding:20px 22px}
@media(max-width:560px){ .sec-h{padding:15px 16px} .sec-b{padding:16px} }

/* note line */
.note{display:flex; gap:8px; align-items:flex-start; font-size:12.5px; color:var(--muted); margin:-2px 0 16px}
.note i{color:var(--green); margin-top:2px}
.note em{font-style:normal; color:var(--ink2); font-weight:600}

/* fields */
.grid2{display:grid; grid-template-columns:1fr 1fr; gap:14px 18px}
@media(max-width:600px){ .grid2{grid-template-columns:1fr} }
.fg{display:flex; flex-direction:column; gap:6px; margin-bottom:2px}
.fg.col{grid-column:1/-1}
.fg label{font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.3px; color:var(--ink2)}
.fg label .rq{color:#d14343; margin-left:1px}
.fg label .opt{text-transform:none; letter-spacing:0; color:var(--muted); font-weight:400}
.inp{height:46px; border:1.5px solid var(--line); border-radius:var(--rs); padding:0 14px; font:inherit; font-size:15px; background:#fff; color:var(--ink); width:100%; transition:.15s; -webkit-appearance:none}
.inp:focus{outline:none; border-color:var(--green); box-shadow:0 0 0 4px rgba(31,122,68,.12)}
textarea.inp{height:auto; min-height:84px; padding:11px 14px; resize:vertical; line-height:1.5}
.inp.lock{background:#f4f7f5; color:var(--muted); cursor:not-allowed; border-style:dashed}
.tag{display:inline-flex; align-items:center; gap:5px; font-size:11px; font-weight:600; color:var(--muted)}
.tag.edit{color:var(--green-d)}
.tag i{font-size:11px}
.err{font-size:12.5px; color:#d14343; font-weight:500}

/* radio chips */
.chips{display:flex; flex-wrap:wrap; gap:9px}
.chipr{position:relative; border:1.5px solid var(--line); border-radius:var(--rs); padding:11px 15px; cursor:pointer; transition:.15s; display:flex; align-items:center; gap:9px; font-size:14px; font-weight:500; user-select:none}
.chipr input{position:absolute; opacity:0; pointer-events:none}
.chipr .dot{width:17px; height:17px; border-radius:50%; border:2px solid #c4cfc8; flex-shrink:0; display:grid; place-items:center; transition:.15s}
.chipr:hover{border-color:var(--green-l); background:var(--soft2)}
.chipr.sel{border-color:var(--green); background:var(--soft); color:var(--green-d); font-weight:600}
.chipr.sel .dot{border-color:var(--green); background:var(--green)}
.chipr.sel .dot::after{content:""; width:6px; height:6px; border-radius:50%; background:#fff}

/* checkbox row */
.checkrow{display:flex; align-items:center; gap:9px; font-size:14px; color:var(--ink2); cursor:pointer; padding:4px 0}
.checkrow input{width:18px; height:18px; accent-color:var(--green); cursor:pointer}

/* uploads */
.up{position:relative; border:2px dashed var(--line); border-radius:var(--rs); padding:18px 14px; text-align:center; background:#fafcfb; cursor:pointer; transition:.2s}
.up:hover{border-color:var(--green); background:var(--soft2)}
.up input[type=file]{position:absolute; inset:0; width:100%; height:100%; opacity:0; cursor:pointer}
.up .ui{font-size:24px; color:#b6c3bb}
.up .ul{font-size:13px; color:var(--ink2); font-weight:600; margin-top:5px; word-break:break-word}
.up .uh{font-size:11px; color:var(--muted); margin-top:2px}
.up.has{border-color:var(--green); border-style:solid; background:#f1fbf4}
.up.has .ui{color:var(--green)}

/* approved terms */
.terms{display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:1px; background:var(--line2); border:1px solid var(--line2); border-radius:var(--rs); overflow:hidden}
.terms .ti{background:#fff; padding:13px 15px}
.terms .tl{font-size:10.5px; text-transform:uppercase; letter-spacing:.4px; color:var(--muted); margin-bottom:4px}
.terms .tv{font-size:15px; font-weight:700; color:var(--ink)}

/* tnc */
.tnc{background:#fafbfa; border:1px solid var(--line); border-radius:var(--rs); padding:16px 18px; max-height:230px; overflow-y:auto; font-size:13px; color:var(--ink2); line-height:1.7}
.tnc p{margin:0 0 11px}
.tnc p:last-child{margin:0}

/* declaration */
.decl{display:flex; gap:12px; align-items:flex-start; background:var(--soft); border:1px solid #cfe6d8; border-radius:var(--rs); padding:15px 17px; margin-top:16px; cursor:pointer}
.decl input{width:19px; height:19px; accent-color:var(--green); flex-shrink:0; margin-top:1px; cursor:pointer}
.decl span{font-size:13.5px; color:var(--green-d); font-weight:500; line-height:1.5}

/* intro */
.intro-sec .sec-b{padding:22px 24px}
.intro-title{font-size:18px; font-weight:800; color:var(--ink); margin:0 0 12px; letter-spacing:-.3px}
.intro-p{font-size:13.5px; color:var(--ink2); margin:0 0 10px; line-height:1.65}
.intro-p:last-child{margin:0}
.intro-p strong{color:var(--green-d); font-weight:700}
@media(max-width:560px){ .intro-sec .sec-b{padding:16px} }

/* submit */
.submit-wrap{padding:4px 22px 24px}
.btn-submit{width:100%; border:none; border-radius:12px; background:linear-gradient(135deg,var(--green),var(--green-d)); color:#fff; font:inherit; font-size:16px; font-weight:700; padding:16px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:10px; box-shadow:0 8px 22px rgba(31,122,68,.32); transition:.18s}
.btn-submit:hover{transform:translateY(-1px); box-shadow:0 12px 28px rgba(31,122,68,.4)}
.btn-submit:active{transform:translateY(0)}
.btn-submit[disabled]{opacity:.7; cursor:wait}

/* alert */
.alert{background:#fdecea; border:1px solid #f6c4bf; color:#b3261e; border-radius:var(--rs); padding:14px 16px; font-size:13.5px}
.alert ul{margin:8px 0 0; padding-left:20px}

footer{text-align:center; padding:24px 16px 34px; color:#9aa8a0; font-size:12px}

/* reveal animation — fast, no stagger, safe for forms */
.sec{opacity:0; animation:rise .3s ease forwards}
@keyframes rise{from{opacity:0; transform:translateY(8px)} to{opacity:1; transform:none}}
@media (prefers-reduced-motion:reduce){ .sec{animation:none; opacity:1; transform:none} }
</style>
</head>
<body>

<header class="hdr">
  <div class="hdr-in">
    <img src="https://g2app.in/images/greenwave-logo-1-275-sl.jpg" alt="Greenwave Global">
    <span class="secure"><i class="fa fa-lock"></i> Secure form</span>
  </div>
</header>

<div class="hero">
  <div class="hero-in">
    <span class="chip {{ $isSubDealer ? 'sub' : 'primary' }}">
      <i class="fa {{ $isSubDealer ? 'fa-sitemap' : 'fa-handshake-o' }}"></i>
      {{ $isSubDealer ? 'Sub-Dealer' : 'Primary Dealer' }}
    </span>
    <div class="greet"><i class="fa fa-user"></i> Welcome, {{ $dealer->name }}</div>
    <h1>{{ $isSubDealer ? 'Sub-Dealer' : 'Channel Partner' }} Onboarding</h1>
    <p>Please review, complete and submit this form to activate your partnership with Greenwave Global Limited.</p>
  </div>
</div>

<div class="wrap">

  @if($errors->any())
  <div class="alert" style="margin-bottom:16px;">
    <strong><i class="fa fa-exclamation-triangle"></i> Please fix the following:</strong>
    <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
  @endif

  <form action="{{ route('onboarding.submit', $token) }}" method="POST" enctype="multipart/form-data" id="obForm" novalidate>
    @csrf

    <div class="stack">

    {{-- ════════════ INTRO / WELCOME ════════════ --}}
    <div class="sec intro-sec">
      <div class="sec-b">
        <h2 class="intro-title">{{ $isSubDealer ? 'Sub-Dealer' : 'Channel Partner' }} Onboarding Form</h2>
        <p class="intro-p">Thank you for your interest in partnering with <strong>Greenwave Global Limited</strong>.</p>
        <p class="intro-p">Based on our discussions and internal evaluation, your organization has been approved for onboarding as a {{ $isSubDealer ? 'Sub-Dealer' : 'Channel Partner' }}. Please review, update (where permitted), complete and submit this form along with the required documents.</p>
      </div>
    </div>

    {{-- ════════════ SHARED: PARTNER / YOUR DETAILS ════════════ --}}
    <div class="sec">
      <div class="sec-h">
        <div class="n">1</div>
        <div class="tt">{{ $isSubDealer ? 'Your Details' : 'Channel Partner Details' }}<small>Pre-filled from our discussions — verify and update where allowed</small></div>
      </div>
      <div class="sec-b">
        <div class="note"><i class="fa fa-info-circle"></i><div><em>Editable</em> fields can be updated. <em>Locked</em> fields are fixed for security.</div></div>
        <div class="grid2">
          <div class="fg">
            <label>{{ $isSubDealer ? 'Business Name' : 'Firm Name' }} <span class="rq">*</span></label>
            <input type="text" name="business_name" class="inp" value="{{ old('business_name',$dealer->business_name) }}" required>
            <span class="tag edit"><i class="fa fa-pencil"></i> Editable</span>
            @error('business_name')<div class="err">{{ $message }}</div>@enderror
          </div>
          <div class="fg">
            <label>{{ $isSubDealer ? 'Primary Contact Person' : 'Contact Person' }} <span class="rq">*</span></label>
            <input type="text" name="name" class="inp" value="{{ old('name',$dealer->name) }}" required>
            <span class="tag edit"><i class="fa fa-pencil"></i> Editable</span>
            @error('name')<div class="err">{{ $message }}</div>@enderror
          </div>
          <div class="fg">
            <label>Designation <span class="rq">*</span></label>
            <input type="text" name="designation" class="inp" value="{{ old('designation',$dealer->designation) }}" required>
            <span class="tag edit"><i class="fa fa-pencil"></i> Editable</span>
            @error('designation')<div class="err">{{ $message }}</div>@enderror
          </div>
          <div class="fg">
            <label>Mobile Number <span class="rq">*</span></label>
            <input type="text" class="inp lock" value="{{ $dealer->owner_mobile }}" readonly>
            <span class="tag"><i class="fa fa-lock"></i> Locked</span>
          </div>
          <div class="fg">
            <label>Email ID <span class="rq">*</span></label>
            <input type="text" class="inp lock" value="{{ $dealer->email }}" readonly>
            <span class="tag"><i class="fa fa-lock"></i> Locked</span>
          </div>
          <div class="fg">
            <label>City <span class="rq">*</span></label>
            <input type="text" name="city" class="inp" value="{{ old('city',$dealer->city) }}" required>
            <span class="tag edit"><i class="fa fa-pencil"></i> Editable</span>
            @error('city')<div class="err">{{ $message }}</div>@enderror
          </div>
          <div class="fg">
            <label>State <span class="opt">(automatic)</span></label>
            <input type="text" class="inp lock" value="{{ $state ?? '—' }}" readonly>
            <span class="tag"><i class="fa fa-lock"></i> Auto</span>
          </div>
          @if($isSubDealer)
          <div class="fg">
            <label>Linked Primary Dealer</label>
            <input type="text" class="inp lock" value="{{ $parentDealer->business_name ?? '—' }}" readonly>
            <span class="tag"><i class="fa fa-lock"></i> Locked</span>
          </div>
          <div class="fg col">
            <label>Territory</label>
            <input type="text" class="inp lock" value="{{ $operatingCities ?: '—' }}" readonly>
            <span class="tag"><i class="fa fa-lock"></i> Locked</span>
          </div>
          @endif
        </div>
      </div>
    </div>

    @if(!$isSubDealer)
    {{-- ════════════ PRIMARY: BUSINESS DETAILS ════════════ --}}
    <div class="sec">
      <div class="sec-h"><div class="n">2</div><div class="tt">Business Details<small>Your firm's tax &amp; address information</small></div></div>
      <div class="sec-b">
        <div class="fg col" style="margin-bottom:16px">
          <label>Business Constitution <span class="rq">*</span></label>
          <div class="chips">
            @foreach(['Proprietorship','Partnership Firm','LLP','Private Limited Company','Public Limited Company','Other'] as $bc)
            <label class="chipr {{ old('business_constitution',$dealer->business_constitution)==$bc ? 'sel':'' }}">
              <input type="radio" name="business_constitution" value="{{ $bc }}" {{ old('business_constitution',$dealer->business_constitution)==$bc ? 'checked':'' }} required>
              <span class="dot"></span>{{ $bc }}
            </label>
            @endforeach
          </div>
          @error('business_constitution')<div class="err" style="margin-top:7px">{{ $message }}</div>@enderror
        </div>

        <div class="grid2">
          <div class="fg">
            <label>GST Number <span class="rq">*</span></label>
            <input type="text" name="gst_no" class="inp" placeholder="27AABCD1234E1Z5" value="{{ old('gst_no',$dealer->gst_no) }}" required>
            @error('gst_no')<div class="err">{{ $message }}</div>@enderror
          </div>
          <div class="fg">
            <label>PAN Number <span class="rq">*</span></label>
            <input type="text" name="pan_no" class="inp" placeholder="AABCD1234E" value="{{ old('pan_no',$dealer->pan_no) }}" required>
            @error('pan_no')<div class="err">{{ $message }}</div>@enderror
          </div>
          <div class="fg col">
            <label>Billing Address <span class="rq">*</span></label>
            <textarea name="billing_address" class="inp" rows="3" required>{{ old('billing_address',$dealer->billing_address) }}</textarea>
            @error('billing_address')<div class="err">{{ $message }}</div>@enderror
          </div>
          <div class="fg col">
            <label class="checkrow">
              <input type="checkbox" id="sameCheck" name="same_as_billing" value="1" {{ old('same_as_billing',$dealer->same_as_billing) ? 'checked':'' }}>
              <span style="text-transform:none;letter-spacing:0;font-weight:500;color:var(--ink2)">Shipping address is same as billing address</span>
            </label>
          </div>
          <div class="fg col" id="shipGroup">
            <label>Shipping Address</label>
            <textarea name="shipping_address" class="inp" rows="3">{{ old('shipping_address',$dealer->shipping_address) }}</textarea>
          </div>
        </div>

        <div class="note" style="margin:18px 0 12px"><i class="fa fa-phone"></i><div><em>Accounts contact</em> &mdash; optional, for billing &amp; payment coordination</div></div>
        <div class="grid2">
          <div class="fg"><label>Contact Person <span class="opt">(optional)</span></label><input type="text" name="accounts_contact_person" class="inp" value="{{ old('accounts_contact_person',$dealer->accounts_contact_person) }}"></div>
          <div class="fg"><label>Mobile <span class="opt">(optional)</span></label><input type="text" name="accounts_mobile" class="inp" value="{{ old('accounts_mobile',$dealer->accounts_mobile) }}"></div>
          <div class="fg col"><label>Email <span class="opt">(optional)</span></label><input type="email" name="accounts_email" class="inp" value="{{ old('accounts_email',$dealer->accounts_email) }}"></div>
        </div>
      </div>
    </div>

    {{-- ════════════ PRIMARY: BANK DETAILS ════════════ --}}
    <div class="sec">
      <div class="sec-h"><div class="n">3</div><div class="tt">Bank Details<small>For payments and refunds</small></div></div>
      <div class="sec-b">
        <div class="grid2">
          <div class="fg"><label>Bank Name <span class="rq">*</span></label><input type="text" name="bank_name" class="inp" placeholder="e.g. HDFC Bank" value="{{ old('bank_name',$dealer->bank_name) }}" required>@error('bank_name')<div class="err">{{ $message }}</div>@enderror</div>
          <div class="fg"><label>Account Name <span class="rq">*</span></label><input type="text" name="bank_account_name" class="inp" placeholder="As on bank account" value="{{ old('bank_account_name',$dealer->bank_account_name) }}" required>@error('bank_account_name')<div class="err">{{ $message }}</div>@enderror</div>
          <div class="fg"><label>Account Number <span class="rq">*</span></label><input type="text" name="bank_account_number" class="inp" value="{{ old('bank_account_number',$dealer->bank_account_number) }}" required>@error('bank_account_number')<div class="err">{{ $message }}</div>@enderror</div>
          <div class="fg"><label>IFSC Code <span class="rq">*</span></label><input type="text" name="bank_ifsc" class="inp" placeholder="e.g. HDFC0001234" value="{{ old('bank_ifsc',$dealer->bank_ifsc) }}" required>@error('bank_ifsc')<div class="err">{{ $message }}</div>@enderror</div>
        </div>
      </div>
    </div>

    {{-- ════════════ PRIMARY: DOCUMENTS ════════════ --}}
    <div class="sec">
      <div class="sec-h"><div class="n">4</div><div class="tt">Document Uploads<small>PDF, JPG or PNG · up to 5 MB each</small></div></div>
      <div class="sec-b">
        <div class="grid2">
          @foreach([['doc_gst_certificate','GST Certificate',true],['doc_pan_card','PAN Card',true],['doc_cancelled_cheque','Cancelled Cheque',true],['doc_visiting_card','Visiting Card',false]] as $d)
          <div class="fg">
            <label>{{ $d[1] }} @if($d[2])<span class="rq">*</span>@else<span class="opt">(optional)</span>@endif</label>
            <div class="up" id="uz-{{ $d[0] }}">
              <input type="file" name="{{ $d[0] }}" accept=".pdf,.jpg,.jpeg,.png" {{ $d[2] ? 'required':'' }} onchange="pick(this,'{{ $d[0] }}')">
              <div class="ui"><i class="fa fa-cloud-upload"></i></div>
              <div class="ul" id="ul-{{ $d[0] }}">Tap to upload</div>
              <div class="uh">PDF / JPG / PNG · max 5 MB</div>
            </div>
            @error($d[0])<div class="err">{{ $message }}</div>@enderror
          </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- ════════════ PRIMARY: APPROVED TERMS (read-only) ════════════ --}}
    <div class="sec">
      <div class="sec-h"><div class="n">5</div><div class="tt">Approved Commercial Terms<small>Pre-approved for your partnership</small></div></div>
      <div class="sec-b">
        <div class="terms">
          <div class="ti"><div class="tl">Channel Partner Status</div><div class="tv">{{ $dealer->cp_status ? ucfirst($dealer->cp_status) : '—' }}</div></div>
          <div class="ti"><div class="tl">Territory</div><div class="tv">{{ $operatingCities ?: '—' }}</div></div>
          <div class="ti"><div class="tl">Payment Term</div><div class="tv">{{ $dealer->payment_term ? $dealer->payment_term.' days' : '—' }}</div></div>
          <div class="ti"><div class="tl">Credit Limit</div><div class="tv">{{ $dealer->credit_allowed ? '₹'.number_format($dealer->credit_allowed) : '—' }}</div></div>
          <div class="ti"><div class="tl">Security Deposit <span style="font-weight:400;color:var(--muted)">(refundable)</span></div><div class="tv">{{ $dealer->security_amount ? '₹'.number_format($dealer->security_amount) : '—' }}</div></div>
        </div>
      </div>
    </div>
    @endif

    {{-- ════════════ TERMS & CONDITIONS ════════════ --}}
    <div class="sec">
      <div class="sec-h"><div class="n">{{ $isSubDealer ? '2' : '6' }}</div><div class="tt">Terms &amp; Conditions<small>Please read before accepting</small></div></div>
      <div class="sec-b">
        <div class="tnc">
          @if(!$isSubDealer)
          <p>1. This channel partnership is <strong>non-exclusive</strong>. Greenwave reserves the right to appoint additional channel partners in the same or any other territory.</p>
          <p>2. Greenwave reserves the right to revise product prices, discount structures, payment terms, credit limits and other commercial policies from time to time.</p>
          <p>3. Any approved security deposit shall be deposited before activation of the approved credit facility, unless otherwise approved by Greenwave.</p>
          <p>4. The Channel Partner shall ensure timely payment of all dues and comply with agreed commercial terms.</p>
          <p>5. The Channel Partner shall not make any unauthorized commitments, representations or claims on behalf of Greenwave.</p>
          <p>6. Greenwave reserves the right to suspend supplies, withdraw credit facilities or terminate the channel partnership in case of payment default, misconduct, policy violation or non-performance.</p>
          <p>7. Any information shared by Greenwave shall be treated as confidential. Disputes shall be subject to courts in <strong>Thane, Maharashtra</strong>.</p>
          @else
          <p>1. The Sub-Dealer shall not make any unauthorized commitments, representations or claims on behalf of Greenwave.</p>
          <p>2. Greenwave reserves the right to terminate the sub-dealership in case of misconduct, policy violation or non-performance.</p>
          <p>3. Any information shared by Greenwave shall be treated as confidential. Disputes shall be subject to courts in <strong>Thane, Maharashtra</strong>.</p>
          @endif
        </div>

        <label class="decl">
          <input type="checkbox" name="declaration_accepted" value="1" required>
          <span>I confirm the information provided is true and correct, and I have read, understood and accepted the {{ $isSubDealer ? 'Sub-Dealer' : 'Commercial' }} Terms &amp; Conditions of Greenwave Global Limited. <span class="rq">*</span></span>
        </label>
        @error('declaration_accepted')<div class="err" style="margin-top:8px">{{ $message }}</div>@enderror
      </div>
      <div class="submit-wrap">
        <button type="submit" class="btn-submit" id="subBtn"><i class="fa fa-paper-plane"></i> Submit onboarding form</button>
      </div>
    </div>

    </div>{{-- /stack --}}
  </form>
</div>

<footer>© {{ date('Y') }} Greenwave Global Limited · This link is secure and expires in 24 hours.</footer>

<script>
// radio chip selection
document.querySelectorAll('.chipr').forEach(function(c){
  c.addEventListener('click', function(){
    var nm = this.querySelector('input').name;
    document.querySelectorAll('[name="'+nm+'"]').forEach(function(i){ i.closest('.chipr').classList.remove('sel'); });
    this.classList.add('sel'); this.querySelector('input').checked = true;
  });
});

// same as billing
var same = document.getElementById('sameCheck'), shipG = document.getElementById('shipGroup');
function syncShip(){ if(shipG) shipG.style.display = (same && same.checked) ? 'none' : ''; }
if(same){ same.addEventListener('change', syncShip); syncShip(); }

// file picker label
function pick(inp, f){
  var z = document.getElementById('uz-'+f);
  if(inp.files && inp.files[0]){
    z.classList.add('has');
    z.querySelector('.ui i').className = 'fa fa-check-circle';
    document.getElementById('ul-'+f).textContent = inp.files[0].name;
  } else {
    z.classList.remove('has');
    z.querySelector('.ui i').className = 'fa fa-cloud-upload';
    document.getElementById('ul-'+f).textContent = 'Tap to upload';
  }
}

// submit state
document.getElementById('obForm').addEventListener('submit', function(){
  var b = document.getElementById('subBtn');
  b.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Submitting…'; b.disabled = true;
});
</script>
</body>
</html>