@extends('layouts.adminLayout.backendLayout')
@section('content')
@php
$stage      = $dealerdata['stage'] ?? 'evaluation';
$isSubmitted = !empty($dealerdata['onboarding_form_submitted']);
$isSubSaved  = ($dealerdata['dealer_type'] ?? 'dealer') === 'sub dealer';
$selDealers  = !empty($dealerdata['linked_dealers']) ? explode(',', $dealerdata['linked_dealers']) : [];
$stageOrder  = ['evaluation' => 1, 'onboarding' => 2, 'confirmed' => 3];
$stageNum    = $stageOrder[$stage] ?? 1;
@endphp
{{-- ════════════════════════════════════════════════════════════════════
CHANNEL PARTNER — single page, sectioned edit
Sections: Classification · Evaluation · Onboarding · Submitted · Confirm
Stage transitions handled via hidden `action` posted to save().
═══════════════════════════════════════════════════════════════════════ --}}
<style>
   #cpEdit{
   --ink:#14241c;--ink2:#3a4a42;--muted:#7b8a82;--line:#e6ebe8;--line2:#eef2f0;
   --g:#1f7a44;--gd:#155e34;--gsoft:#e8f3ec;--gsoft2:#f1f8f3;
   --amber:#9a6a12;--amberS:#fdf4e3;--blue:#1f5e9a;--blueS:#e9f1fa;
   --r:11px;--rs:8px;--mono:'JetBrains Mono',Menlo,Consolas,monospace;
   font-family:'Inter','Segoe UI',system-ui,sans-serif;color:var(--ink);
   }
   #cpEdit *{box-sizing:border-box}
   #cpEdit .cpx-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:18px}
   #cpEdit .cpx-head h1{font-size:21px;font-weight:800;margin:0 0 6px;letter-spacing:-.3px;color:var(--ink)}
   #cpEdit .cpx-meta{display:flex;gap:13px;flex-wrap:wrap;color:var(--muted);font-size:12.5px;align-items:center}
   #cpEdit .cpx-meta .id{font-family:var(--mono);color:var(--ink2)}
   #cpEdit .cpx-chip{display:inline-flex;align-items:center;gap:6px;padding:4px 11px;border-radius:30px;font-size:11px;font-weight:700;letter-spacing:.3px;text-transform:uppercase}
   #cpEdit .cpx-chip.primary{background:#efeafe;color:#5b3bbd}
   #cpEdit .cpx-chip.sub{background:#fdeede;color:#b05a13}
   #cpEdit .cpx-chip.auth{background:var(--gsoft);color:var(--gd)}
   #cpEdit .cpx-stages{display:flex;gap:10px;background:#fff;border:1px solid var(--line);border-radius:var(--r);padding:14px 18px;margin-bottom:20px;box-shadow:0 1px 2px rgba(20,36,28,.04)}
   #cpEdit .cpx-sp{flex:1;display:flex;align-items:center;gap:10px;position:relative}
   #cpEdit .cpx-sp .n{width:30px;height:30px;border-radius:50%;display:grid;place-items:center;font-weight:700;font-size:13px;background:#f1f4f2;color:var(--muted);border:2px solid var(--line);flex-shrink:0}
   #cpEdit .cpx-sp.done .n{background:var(--g);border-color:var(--g);color:#fff}
   #cpEdit .cpx-sp.active .n{border-color:var(--g);color:var(--g);box-shadow:0 0 0 4px var(--gsoft)}
   #cpEdit .cpx-sp .nm{font-size:13px;font-weight:700}
   #cpEdit .cpx-sp .ds{font-size:11px;color:var(--muted)}
   #cpEdit .cpx-sp.muted .nm{color:var(--muted)}
   #cpEdit .cpx-sp::after{content:"";position:absolute;right:-10px;top:15px;width:10px;height:2px;background:var(--line)}
   #cpEdit .cpx-sp:last-child::after{display:none}
   #cpEdit .cpx-card{background:#fff;border:1px solid var(--line);border-radius:var(--r);box-shadow:0 1px 2px rgba(20,36,28,.04),0 6px 18px rgba(20,36,28,.04);margin-bottom:18px;overflow:hidden}
   #cpEdit .cpx-card.muted{opacity:.62}
   #cpEdit .cpx-ch{padding:16px 24px;border-bottom:1px solid var(--line2);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
   #cpEdit .cpx-ch .t{font-size:15px;font-weight:700;display:flex;align-items:center;gap:9px}
   #cpEdit .cpx-ch .t .ix{width:26px;height:26px;border-radius:7px;background:var(--gsoft);color:var(--gd);display:grid;place-items:center;font-size:12px}
   #cpEdit .cpx-ch .t small{display:block;font-weight:400;color:var(--muted);font-size:12px;margin-top:1px}
   #cpEdit .cpx-cb{padding:22px 24px}
   #cpEdit .cpx-shdr{font-size:11px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--gd);display:flex;align-items:center;gap:8px;margin:4px 0 16px}
   #cpEdit .cpx-shdr::after{content:"";flex:1;height:1px;background:var(--line2)}
   #cpEdit .cpx-shdr.mt{margin-top:28px}
   #cpEdit .cpx-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:15px 20px}
   #cpEdit .cpx-grid.g3{grid-template-columns:repeat(3,1fr)}
   #cpEdit .cpx-f{display:flex;flex-direction:column;gap:5px}
   #cpEdit .cpx-f.full{grid-column:1/-1}
   #cpEdit .cpx-f>label{font-size:11.5px;font-weight:600;color:var(--ink2);text-transform:uppercase;letter-spacing:.3px;margin:0}
   #cpEdit .cpx-f .rq{color:#d14343}
   #cpEdit .cpx-in{height:40px;border:1.4px solid var(--line);border-radius:var(--rs);padding:0 12px;font-size:13.5px;background:#fff;color:var(--ink);width:100%;transition:.15s}
   #cpEdit .cpx-in:focus{outline:none;border-color:var(--g);box-shadow:0 0 0 3px var(--gsoft)}
   #cpEdit textarea.cpx-in{height:auto;min-height:62px;padding:10px 12px;resize:vertical}
   #cpEdit .cpx-in.lock{background:#f6f8f7;color:var(--ink2);cursor:not-allowed}
   #cpEdit .cpx-in.data{font-family:var(--mono);font-size:13px}
   #cpEdit .cpx-hint{font-size:11px;color:var(--muted)}
   #cpEdit .cpx-err{font-size:12px;color:#d14343;display:none}
   #cpEdit .cpx-unit{position:relative}
   #cpEdit .cpx-unit .pre{position:absolute;left:11px;top:0;height:40px;display:grid;place-items:center;color:var(--muted);font-family:var(--mono);font-size:13px}
   #cpEdit .cpx-unit .cpx-in{padding-left:26px}
   #cpEdit .select2-container{width:100%!important}
   #cpEdit .select2-container .select2-selection--single,
   #cpEdit .select2-container .select2-selection--multiple{min-height:40px;border:1.4px solid var(--line)!important;border-radius:var(--rs)!important}
   #cpEdit .cpx-seg{display:inline-flex;background:#f1f4f2;border:1px solid var(--line);border-radius:9px;padding:3px;gap:3px}
   #cpEdit .cpx-seg label{margin:0;border-radius:7px;font-size:13px;font-weight:600;color:var(--ink2);padding:7px 16px;cursor:pointer;transition:.15s}
   #cpEdit .cpx-seg input{display:none}
   #cpEdit .cpx-seg label.on{background:#fff;color:var(--gd);box-shadow:0 1px 3px rgba(20,36,28,.1)}
   #cpEdit .cpx-checks{border:1px solid var(--line);border-radius:var(--rs);overflow:hidden;max-width:460px}
   #cpEdit .cpx-checks.full{max-width:none}
   #cpEdit .cpx-checks.scroll{max-height:330px;overflow:auto}
   #cpEdit .cpx-crow{display:flex;align-items:center;justify-content:space-between;padding:11px 14px;border-bottom:1px solid var(--line2);font-size:13.5px}
   #cpEdit .cpx-crow:last-child{border-bottom:none}
   #cpEdit .cpx-crow .lbl{font-weight:500;display:flex;align-items:center;gap:10px}
   #cpEdit .cpx-crow .lbl small{color:var(--muted);font-weight:400}
   #cpEdit .cpx-crow .lbl .num{display:inline-grid;place-items:center;width:22px;height:22px;border-radius:6px;background:var(--gsoft);color:var(--gd);font-size:11px;font-weight:700;font-family:var(--mono);flex-shrink:0}
   #cpEdit .cpx-crow .mod-check{width:18px;height:18px;cursor:pointer;accent-color:var(--g);flex-shrink:0}
   #cpEdit .cpx-checks-head{display:flex;align-items:center;justify-content:space-between;padding:9px 14px;background:#f4f6f9;border-bottom:1px solid var(--line2);font-size:11px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;color:var(--muted)}
   #cpEdit .cpx-sw{position:relative;width:38px;height:21px;border-radius:30px;background:#cfd8d3;cursor:pointer;transition:.2s;flex-shrink:0}
   #cpEdit .cpx-sw::after{content:"";position:absolute;top:2px;left:2px;width:17px;height:17px;border-radius:50%;background:#fff;transition:.2s;box-shadow:0 1px 2px rgba(0,0,0,.2)}
   #cpEdit .cpx-sw.on{background:var(--g)}
   #cpEdit .cpx-sw.on::after{transform:translateX(17px)}
   #cpEdit .cpx-docs{display:grid;grid-template-columns:repeat(2,1fr);gap:11px;margin-top:13px}
   #cpEdit .cpx-doc{display:flex;align-items:flex-start;gap:11px;border:1px solid var(--line);border-radius:var(--rs);padding:11px 13px}
   #cpEdit .cpx-doc .ic{width:34px;height:34px;border-radius:7px;background:var(--blueS);color:var(--blue);display:grid;place-items:center;font-size:14px;flex-shrink:0}
   #cpEdit .cpx-doc .nm{font-size:12.5px;font-weight:600}
   #cpEdit .cpx-doc a{font-size:12px;font-weight:600;color:var(--gd);text-decoration:none;flex-shrink:0;align-self:center}
   #cpEdit .cpx-upl{display:inline-flex;align-items:center;gap:6px;margin-top:8px;padding:5px 11px;border:1.4px dashed #c6d2cc;border-radius:7px;font-size:11.5px;font-weight:600;color:var(--gd);cursor:pointer;transition:.15s;background:var(--gsoft2)}
   #cpEdit .cpx-upl:hover{border-color:var(--g);background:var(--gsoft)}
   #cpEdit .cpx-file{display:none}
   #cpEdit .cpx-fname{font-size:11px;color:var(--ink2);margin-top:5px;word-break:break-all}
   #cpEdit .cpx-fname.set{color:var(--g);font-weight:600}
   #cpEdit .cpx-pdf{display:flex;align-items:center;gap:13px;background:var(--gsoft2);border:1px solid #d8ece0;border-radius:var(--rs);padding:13px 16px;margin-bottom:18px}
   #cpEdit .cpx-pdf .ic{width:38px;height:38px;border-radius:9px;background:#fff;border:1px solid #d8ece0;display:grid;place-items:center;color:#c1392b;font-size:16px}
   #cpEdit .cpx-pdf b{font-size:13px}#cpEdit .cpx-pdf .ds{font-size:11.5px;color:var(--muted)}
   #cpEdit .cpx-link{background:var(--gsoft2);border:1px solid #d8ece0;border-radius:var(--rs);padding:16px}
   #cpEdit .cpx-link .ttl{font-size:12.5px;font-weight:700;color:var(--gd);margin-bottom:10px}
   #cpEdit .cpx-link .lrow{display:flex;gap:8px;flex-wrap:wrap}
   #cpEdit .cpx-link .lrow .cpx-in{flex:1;min-width:200px;font-family:var(--mono);font-size:12.5px}
   #cpEdit .cpx-lstat{margin-top:11px;font-size:12px;color:var(--ink2);display:flex;align-items:center;gap:7px}
   #cpEdit .cpx-lstat .dot{width:8px;height:8px;border-radius:50%}
   #cpEdit .cpx-lstat .dot.wait{background:var(--amber)}
   #cpEdit .cpx-lstat .dot.ok{background:var(--g)}
   #cpEdit .cpx-empty{text-align:center;padding:36px 20px}
   #cpEdit .cpx-empty .ic{width:60px;height:60px;border-radius:50%;background:var(--amberS);color:var(--amber);display:grid;place-items:center;font-size:24px;margin:0 auto 13px}
   #cpEdit .cpx-empty h3{margin:0 0 6px;font-size:15px}
   #cpEdit .cpx-empty p{margin:0 auto;color:var(--muted);max-width:380px;font-size:13px}
   #cpEdit .cpx-btn{display:inline-flex;align-items:center;gap:8px;border:none;border-radius:9px;font-size:13.5px;font-weight:600;padding:10px 18px;cursor:pointer;transition:.15s;text-decoration:none;line-height:1.2}
   #cpEdit .cpx-btn.g{background:var(--g);color:#fff}#cpEdit .cpx-btn.g:hover{background:var(--gd);color:#fff}
   #cpEdit .cpx-btn.ghost{background:#fff;border:1.4px solid var(--line);color:var(--ink2)}#cpEdit .cpx-btn.ghost:hover{border-color:#c6d2cc;color:var(--ink)}
   #cpEdit .cpx-btn.amber{background:var(--amber);color:#fff}
   #cpEdit .cpx-btn.sm{padding:7px 13px;font-size:12.5px}
   #cpEdit .cpx-btn[disabled]{opacity:.5;cursor:not-allowed}
   #cpEdit .cpx-actions{position:sticky;bottom:0;background:#fff;border:1px solid var(--line);border-top:2px solid var(--g);border-radius:var(--r);box-shadow:0 -4px 18px rgba(20,36,28,.06);padding:14px 22px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:6px;z-index:20}
   #cpEdit .cpx-actions .note{font-size:12px;color:var(--muted);display:flex;align-items:center;gap:7px}
   #cpEdit .cpx-hide{display:none!important}
   @media(max-width:768px){
   #cpEdit .cpx-grid,#cpEdit .cpx-grid.g3,#cpEdit .cpx-docs{grid-template-columns:1fr}
   #cpEdit .cpx-stages{flex-direction:column}
   #cpEdit .cpx-sp::after{display:none}
   }
</style>
<div class="page-content-wrapper">
   <div class="page-content">
      <ul class="page-breadcrumb breadcrumb" style="margin-bottom:14px;">
         <li><a href="{{ url('admin/dashboard') }}">Dashboard</a><i class="fa fa-circle"></i></li>
         <li><a href="{{ route('admin.channel-partners.index') }}">Channel Partners</a><i class="fa fa-circle"></i></li>
         <li><span>{{ $title }}</span></li>
      </ul>
      @if(isset($_GET['s']))
      <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button><strong>Saved.</strong> Changes have been stored.</div>
      @endif
      <div id="cpEdit">
         {{-- ── Header ───────────────────────────────────────────────── --}}
         <div class="cpx-head">
            <div>
               <h1>{{ !empty($dealerdata) ? ($dealerdata['business_name'] ?: 'Unnamed Partner') : 'New Channel Partner' }}</h1>
               <div class="cpx-meta">
                  @if(!empty($dealerdata['id']))<span class="id">#GW-{{ $dealerdata['id'] }}</span>@endif
                  @if(!empty($dealerdata['city']))<span><i class="fa fa-map-marker"></i> {{ $dealerdata['city'] }}</span>@endif
                  @if(!empty($dealerdata['owner_mobile']))<span><i class="fa fa-phone"></i> {{ $dealerdata['owner_mobile'] }}</span>@endif
                  <span id="cpHeadType" class="cpx-chip {{ $isSubSaved ? 'sub':'primary' }}">{{ $isSubSaved ? 'Sub-Dealer':'Primary Dealer' }}</span>
                  @if(!empty($dealerdata['is_authenticated']))<span class="cpx-chip auth"><i class="fa fa-shield"></i> OTP &amp; Pin Enabled</span>@endif
               </div>
            </div>
            <a class="cpx-btn ghost sm" href="{{ route('admin.channel-partners.index') }}"><i class="fa fa-arrow-left"></i> Back to list</a>
         </div>
         {{-- ── Stage status bar ─────────────────────────────────────── --}}
         <div class="cpx-stages">
            <div class="cpx-sp {{ $stageNum>1?'done':'' }} {{ $stageNum==1?'active':'' }} {{ $stageNum<1?'muted':'' }}">
               <div class="n">{{ $stageNum>1 ? '✓' : '1' }}</div>
               <div><div class="nm">Evaluation</div><div class="ds">Lead captured</div></div>
            </div>
            <div class="cpx-sp {{ $stageNum>2?'done':'' }} {{ $stageNum==2?'active':'' }} {{ $stageNum<2?'muted':'' }}">
               <div class="n">{{ $stageNum>2 ? '✓' : '2' }}</div>
               <div><div class="nm">Onboarding</div><div class="ds">{{ $isSubmitted ? 'Form submitted' : 'Set terms · send link' }}</div></div>
            </div>
            <div class="cpx-sp {{ $stageNum>=3?'active':'' }} {{ $stageNum<3?'muted':'' }} {{ $stageNum>3?'done':'' }}">
               <div class="n">{{ $stageNum>=3 ? '✓' : '3' }}</div>
               <div><div class="nm">Confirmed</div><div class="ds">{{ $stageNum>=3 ? 'Active partner' : 'Review · confirm' }}</div></div>
            </div>
         </div>
         {{-- ════════════════════ SINGLE FORM ════════════════════ --}}
         <form id="CPForm" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="dealerid" value="{{ $dealerdata['id'] ?? '' }}">
            <input type="hidden" name="action" id="cpAction" value="save">
            {{-- ── SECTION 1: Evaluation ──────────────────────────────── --}}
            <div class="cpx-card">
               <div class="cpx-ch">
                  <div class="t"><span class="ix">1</span><div>Evaluation Details<small>Captured from the Dealer Evaluation Form</small></div></div>
               </div>
               <div class="cpx-cb">
                  @if(!empty($dealerdata['evaluation_form_pdf']))
                  <div class="cpx-pdf">
                     <div class="ic"><i class="fa fa-file-pdf-o"></i></div>
                     <div><b>Dealer Evaluation Form</b><div class="ds">Attached to this master</div></div>
                     <a class="cpx-btn ghost sm" style="margin-left:auto" target="_blank" href="{{ asset($dealerdata['evaluation_form_pdf']) }}">View PDF</a>
                  </div>
                  @endif
                  <div class="cpx-grid">
                     <div class="cpx-f">
                        <label>Business Name <span class="rq">*</span></label><input class="cpx-in" name="business_name" value="{{ $dealerdata['business_name'] ?? '' }}" placeholder="e.g. ABC Trading Co.">
                        <div class="cpx-err" id="Dealer-business_name"></div>
                     </div>
                     <div class="cpx-f"><label>Short Name</label><input class="cpx-in" name="short_name" value="{{ $dealerdata['short_name'] ?? '' }}" placeholder="e.g. ABC"></div>
                     <div class="cpx-f">
                        <label>Primary Contact Person <span class="rq">*</span></label><input class="cpx-in" name="name" value="{{ $dealerdata['name'] ?? '' }}" placeholder="Full name">
                        <div class="cpx-err" id="Dealer-name"></div>
                     </div>
                     <div class="cpx-f"><label>Designation</label><input class="cpx-in" name="designation" value="{{ $dealerdata['designation'] ?? '' }}" placeholder="e.g. Proprietor"></div>
                     <div class="cpx-f">
                        <label>Mobile No. <span class="rq">*</span></label><input class="cpx-in data" name="owner_mobile" value="{{ $dealerdata['owner_mobile'] ?? '' }}" placeholder="10-digit">
                        <div class="cpx-err" id="Dealer-owner_mobile"></div>
                     </div>
                     <div class="cpx-f">
                        <label>E-mail ID <span class="rq">*</span></label><input class="cpx-in" name="email" value="{{ $dealerdata['email'] ?? '' }}" placeholder="email@example.com">
                        <div class="cpx-err" id="Dealer-email"></div>
                     </div>
                     <div class="cpx-f">
                        <label>City <span class="rq">*</span></label>
                        <select class="cpx-in cpx-select2" name="city">
                           <option value="">— Select City —</option>
                           @foreach(getcities() as $city)
                           <option value="{{ $city['city_name'] }}" {{ (!empty($dealerdata) && ($dealerdata['city'] ?? '')==$city['city_name']) ? 'selected':'' }}>{{ $city['city_name'] }}</option>
                           @endforeach
                        </select>
                        <div class="cpx-err" id="Dealer-city"></div>
                     </div>
                     <div class="cpx-f"><label>Source of Lead</label><input class="cpx-in" name="source_of_lead" value="{{ $dealerdata['source_of_lead'] ?? '' }}" placeholder="e.g. Marketing Executive"></div>
                     <div class="cpx-f"><label>Office Phone</label><input class="cpx-in data" name="office_phone" value="{{ $dealerdata['office_phone'] ?? '' }}" placeholder="Landline (optional)"></div>
                  </div>
               </div>
            </div>
            {{-- ── SECTION 2: Onboarding (terms) ──────────────────────── --}}
            <div class="cpx-card">
               <div class="cpx-ch">
                  <div class="t"><span class="ix">2</span><div>Onboarding — Commercial Terms<small>Set terms, then generate the partner's onboarding link</small></div></div>
               </div>
               <div class="cpx-cb">
                  <div class="cpx-shdr">Partner Type</div>
                  <div class="cpx-grid">
                     <div class="cpx-f">
                        <label>Partner Type <span class="rq">*</span></label>
                        <div class="cpx-seg" id="typeSeg">
                           <label class="{{ !$isSubSaved ? 'on':'' }}"><input type="radio" name="dealer_type" value="dealer" {{ !$isSubSaved ? 'checked':'' }}><span>Primary Dealer</span></label>
                           <label class="{{ $isSubSaved ? 'on':'' }}"><input type="radio" name="dealer_type" value="sub dealer" {{ $isSubSaved ? 'checked':'' }}><span>Sub-Dealer</span></label>
                        </div>
                        <div class="cpx-err" id="Dealer-dealer_type"></div>
                     </div>
                     <div class="cpx-f" id="rowLinkedDealer" style="{{ $isSubSaved ? '' : 'display:none' }}">
                        <label>Primary Dealer <span class="rq">*</span></label>
                        <select class="cpx-in cpx-select2" name="linked_dealer_id">
                           <option value="">— Select Primary Dealer —</option>
                           @foreach($parentDealers as $pd)
                           <option value="{{ $pd['id'] }}" {{ (!empty($dealerdata['linked_dealer_id']) && $dealerdata['linked_dealer_id']==$pd['id']) ? 'selected':'' }}>{{ $pd['business_name'] }}</option>
                           @endforeach
                        </select>
                        <div class="cpx-err" id="Dealer-linked_dealer_id"></div>
                     </div>
                  </div>
                  <div class="cpx-shdr mt">Territory &amp; Terms</div>
                  <div class="cpx-grid">
                     <div class="cpx-f full">
                        <label>Area of Operations / Territory <span class="rq">*</span></label>
                        <select class="cpx-in cpx-select2" name="operating_cities[]" multiple>
                        @foreach(getcities() as $city)
                        <option value="{{ $city['city_name'] }}" {{ (!empty($operatingCities) && in_array($city['city_name'],$operatingCities)) ? 'selected':'' }}>{{ $city['city_name'] }}</option>
                        @endforeach
                        </select>
                        <div class="cpx-hint">These cities appear as Territory on the partner's onboarding form.</div>
                        <div class="cpx-err" id="Dealer-operating_cities"></div>
                     </div>
                  </div>
                  <div id="primaryTerms">
                     <div class="cpx-grid" style="margin-top:15px">
                        <div class="cpx-f">
                           <label>Channel Partner Status <span class="rq">*</span></label>
                           <div class="cpx-seg">
                              <label class="{{ (($dealerdata['cp_status'] ?? 'provisional')=='provisional') ? 'on':'' }}"><input type="radio" name="cp_status" value="provisional" {{ (($dealerdata['cp_status'] ?? 'provisional')=='provisional') ? 'checked':'' }}><span>Provisional</span></label>
                              <label class="{{ (($dealerdata['cp_status'] ?? '')=='authorized') ? 'on':'' }}"><input type="radio" name="cp_status" value="authorized" {{ (($dealerdata['cp_status'] ?? '')=='authorized') ? 'checked':'' }}><span>Authorized</span></label>
                           </div>
                           <div class="cpx-hint">Shown to the partner as their approved status on the onboarding form.</div>
                        </div>
                     </div>
                     <div class="cpx-grid g3" style="margin-top:15px">
                        <div class="cpx-f">
                           <label>Payment Term (days) <span class="rq">*</span></label><input class="cpx-in data" type="number" name="payment_term" value="{{ $dealerdata['payment_term'] ?? '' }}" placeholder="30">
                           <div class="cpx-err" id="Dealer-payment_term"></div>
                        </div>
                        <div class="cpx-f"><label>Basic Discount (%)</label><input class="cpx-in data" type="number" step="0.01" name="basic_discount" value="{{ $dealerdata['basic_discount'] ?? '' }}" placeholder="0.00"></div>
                        <div class="cpx-f"><label>CD · 7 days (%)</label><input class="cpx-in data" type="number" step="0.01" name="cd_7days" value="{{ $dealerdata['cd_7days'] ?? '' }}" placeholder="0.00"></div>
                        <div class="cpx-f"><label>CD · Advance (%)</label><input class="cpx-in data" type="number" step="0.01" name="cd_advance" value="{{ $dealerdata['cd_advance'] ?? '' }}" placeholder="0.00"></div>
                        <div class="cpx-f">
                           <label>Approved Security Deposit <span class="rq">*</span></label>
                           <div class="cpx-unit"><span class="pre">₹</span><input class="cpx-in data" name="security_amount" id="security_amount" value="{{ $dealerdata['security_amount'] ?? '' }}" placeholder="0"></div>
                        </div>
                        <div class="cpx-f"><label>Interest on Security (%)</label><input class="cpx-in data" name="interest_rate_on_security" value="{{ $dealerdata['interest_rate_on_security'] ?? '' }}" placeholder="0.00"></div>
                        <div class="cpx-f"><label>Credit Multiple</label><input class="cpx-in data" name="credit_multiple" id="credit_multiple" value="{{ $dealerdata['credit_multiple'] ?? '' }}" placeholder="e.g. 3"></div>
                        <div class="cpx-f">
                           <label>Credit Limit <span class="rq">*</span></label>
                           <div class="cpx-unit"><span class="pre">₹</span><input class="cpx-in data" name="credit_allowed" id="credit_allowed" value="{{ $dealerdata['credit_allowed'] ?? '' }}" placeholder="Auto"></div>
                        </div>
                     </div>
                  </div>
                  @if($stageNum >= 2 && !$isSubmitted)
                  <div class="cpx-shdr mt">Onboarding Link</div>
                  <div class="cpx-link">
                     <div class="ttl"><i class="fa fa-link"></i> Secure onboarding link · valid 24 hours</div>
                     <div class="lrow">
                        <input class="cpx-in" id="obLink" readonly placeholder="Click “Generate / Send Link” to create a fresh link">
                        <button type="button" class="cpx-btn ghost sm" id="btnCopyLink"><i class="fa fa-copy"></i> Copy</button>
                        <button type="button" class="cpx-btn g sm" id="btnSendLink" data-id="{{ $dealerdata['id'] }}"><i class="fa fa-send"></i> Generate / Send Link</button>
                     </div>
                     <div class="cpx-lstat">
                        <span class="dot {{ $isSubmitted ? 'ok':'wait' }}"></span>
                        @if($isSubmitted)
                        Partner submitted the form on {{ !empty($dealerdata['onboarding_submitted_at']) ? \Carbon\Carbon::parse($dealerdata['onboarding_submitted_at'])->format('d M Y, h:i A') : '—' }}
                        @else
                        Awaiting submission — generate a link and share it with the partner.
                        @endif
                     </div>
                     <div class="cpx-lstat cpx-hide" id="linkErr" style="color:#d14343"></div>
                  </div>
                  @else
                  @if($stage=="confirmed")
                     @else

                     <div class="cpx-shdr mt">Onboarding Link</div>
                     <p class="cpx-hint" style="font-size:12.5px">Move this partner to <b>Onboarding</b> (button below) to activate the onboarding link.</p>
                     @endif
                  @endif
               </div>
            </div>
            {{-- ── SECTION: Submitted by Partner (only after submission) ── --}}
            @if($isSubmitted)
            <div class="cpx-card">
               <div class="cpx-ch">
                  <div class="t"><span class="ix"><i class="fa fa-inbox"></i></span><div>Submitted by Partner<small>What the partner filled in the onboarding form</small></div></div>
                  <span class="cpx-chip auth"><i class="fa fa-check"></i> Submitted</span>
               </div>
               <div class="cpx-cb">
                  @if(!empty($dealerdata['onboarding_form_pdf']))
                  <div class="cpx-pdf">
                     <div class="ic"><i class="fa fa-file-pdf-o"></i></div>
                     <div><b>Submitted Onboarding Form</b><div class="ds">Auto-generated from the partner's submission</div></div>
                     <a class="cpx-btn ghost sm" style="margin-left:auto" target="_blank" href="{{ asset($dealerdata['onboarding_form_pdf']) }}">View PDF</a>
                  </div>
                  @endif
                  <p class="cpx-hint" style="margin:-2px 0 14px"><i class="fa fa-pencil"></i> Edit anything the partner submitted, then Save. (Firm Name, Contact &amp; City are edited in <b>Evaluation Details</b> above.)</p>
                  @if(!$isSubSaved)
                  <div class="cpx-grid">
                     <div class="cpx-f"><label>GST No.</label><input class="cpx-in data" name="gst_no" value="{{ $dealerdata['gst_no'] ?? '' }}" placeholder="GST Number"></div>
                     <div class="cpx-f"><label>PAN Number</label><input class="cpx-in data" name="pan_no" value="{{ $dealerdata['pan_no'] ?? '' }}"></div>
                     <div class="cpx-f"><label>Business Constitution</label>
                        <select class="cpx-in" name="business_constitution">
                        @foreach(['Proprietorship','Partnership Firm','LLP','Private Limited Company','Public Limited Company','Other'] as $bc)
                        <option value="{{ $bc }}" {{ ($dealerdata['business_constitution'] ?? '')==$bc ? 'selected':'' }}>{{ $bc }}</option>
                        @endforeach
                        </select>
                     </div>
                     <div class="cpx-f full"><label>Billing Address</label><textarea class="cpx-in" name="billing_address" rows="2">{{ $dealerdata['billing_address'] ?? '' }}</textarea></div>
                     <div class="cpx-f full"><label>Shipping Address</label><textarea class="cpx-in" name="shipping_address" rows="2">{{ $dealerdata['shipping_address'] ?? '' }}</textarea></div>
                     <div class="cpx-f"><label>Bank Name</label><input class="cpx-in" name="bank_name" value="{{ $dealerdata['bank_name'] ?? '' }}"></div>
                     <div class="cpx-f"><label>Account Name</label><input class="cpx-in" name="bank_account_name" value="{{ $dealerdata['bank_account_name'] ?? '' }}"></div>
                     <div class="cpx-f"><label>Account Number</label><input class="cpx-in data" name="bank_account_number" value="{{ $dealerdata['bank_account_number'] ?? '' }}"></div>
                     <div class="cpx-f"><label>IFSC Code</label><input class="cpx-in data" name="bank_ifsc" value="{{ $dealerdata['bank_ifsc'] ?? '' }}"></div>
                     <div class="cpx-f"><label>Accounts Contact Person</label><input class="cpx-in" name="accounts_contact_person" value="{{ $dealerdata['accounts_contact_person'] ?? '' }}"></div>
                     <div class="cpx-f"><label>Accounts Mobile</label><input class="cpx-in data" name="accounts_mobile" value="{{ $dealerdata['accounts_mobile'] ?? '' }}"></div>
                     <div class="cpx-f"><label>Accounts Email</label><input class="cpx-in" name="accounts_email" value="{{ $dealerdata['accounts_email'] ?? '' }}"></div>
                  </div>
                  @else
                  {{-- Sub-dealer: only Address & GST are captured --}}
                  <div class="cpx-grid">
                     <div class="cpx-f full"><label>Address</label><input class="cpx-in" name="address" value="{{ $dealerdata['address'] ?? '' }}" placeholder="Full address">
                        <div class="cpx-err" id="Dealer-address"></div>
                     </div>
                     <div class="cpx-f"><label>GST No.</label><input class="cpx-in data" name="gst_no" value="{{ $dealerdata['gst_no'] ?? '' }}" placeholder="GST Number"></div>
                  </div>
                  @endif
                  <div class="cpx-hint" style="margin-top:12px">Declaration: <b style="color:var(--gd)">{{ !empty($dealerdata['declaration_accepted']) ? 'Accepted' : 'Not accepted' }}</b>{{ !empty($dealerdata['declaration_accepted_at']) ? ' · '.\Carbon\Carbon::parse($dealerdata['declaration_accepted_at'])->format('d M Y, h:i A') : '' }}</div>
                  @if(!$isSubSaved)
                  <div class="cpx-shdr mt">Documents <small style="text-transform:none;color:var(--muted);font-weight:400">— replace any wrong file the partner uploaded</small></div>
                  @php
                     $docs = [
                        'doc_gst_certificate'  => 'GST Certificate',
                        'doc_pan_card'         => 'PAN Card',
                        'doc_cancelled_cheque' => 'Cancelled Cheque',
                        'doc_visiting_card'    => 'Visiting Card',
                     ];
                  @endphp
                  <div class="cpx-docs">
                     @foreach($docs as $dk => $dlabel)
                     <div class="cpx-doc">
                        <div class="ic"><i class="fa fa-paperclip"></i></div>
                        <div style="flex:1;min-width:0">
                           <div class="nm">{{ $dlabel }}</div>
                           @if(!empty($dealerdata[$dk]))
                              <div class="ds" style="font-size:11px;color:var(--muted)"><i class="fa fa-check-circle" style="color:var(--g)"></i> Uploaded</div>
                           @else
                              <div class="ds" style="font-size:11px;color:#d14343"><i class="fa fa-exclamation-circle"></i> Not provided</div>
                           @endif
                           <label class="cpx-upl" for="upl_{{ $dk }}">
                              <i class="fa fa-upload"></i>
                              <span>{{ !empty($dealerdata[$dk]) ? 'Replace file' : 'Upload file' }}</span>
                           </label>
                           <input type="file" id="upl_{{ $dk }}" name="{{ $dk }}" class="cpx-file"
                                  accept=".pdf,.jpg,.jpeg,.png"
                                  onchange="cpFileName(this,'fn_{{ $dk }}')">
                           <div class="cpx-fname" id="fn_{{ $dk }}"></div>
                           <div class="cpx-err" id="Dealer-{{ $dk }}"></div>
                        </div>
                        @if(!empty($dealerdata[$dk]))
                           <a target="_blank" href="{{ asset($dealerdata[$dk]) }}">View</a>
                        @endif
                     </div>
                     @endforeach
                  </div>
                  @endif
               </div>
            </div>
            @else
            {{-- Address & GST are submitted by the partner during onboarding.
                 Keep existing values on the form (hidden) so a pre-submission
                 Save does not wipe them. --}}
            <input type="hidden" name="address" value="{{ $dealerdata['address'] ?? '' }}">
            <input type="hidden" name="gst_no"  value="{{ $dealerdata['gst_no'] ?? '' }}">
            @endif
            {{-- ── SECTION 3: Final Confirmation (admin) ──────────────── --}}
            <div class="cpx-card">
               <div class="cpx-ch">
                  <div class="t"><span class="ix">3</span><div>Final Confirmation<small>Admin review &amp; activation</small></div></div>
               </div>
               <div class="cpx-cb">
                  @if(!$isSubmitted)
                  <div class="cpx-empty">
                     <div class="ic"><i class="fa fa-lock"></i></div>
                     <h3>The partner hasn't filled the onboarding form yet</h3>
                     @if($stage=='evaluation')
                     <p>Move this partner to <b>Onboarding</b> and share the onboarding link. Once they open it and submit their details, this section unlocks for review &amp; confirmation.</p>
                     @else
                     <p>Waiting for the partner to open the onboarding link and submit their form. You can resend the link from the <b>Onboarding</b> section above — this section unlocks once they submit.</p>
                     @endif
                  </div>
                  @else
                  <input type="hidden" name="confirmation_visible" value="1">
                  {{-- Primary-only confirmation --}}
                  <div id="confPrimary">
                     <div class="cpx-shdr">Deposit &amp; Status</div>
                     <div class="cpx-grid g3">
                        <div class="cpx-f">
                           <label>Security Deposit Status <span class="rq">*</span></label>
                           <div class="cpx-seg" id="depStatusSeg">
                              <label class="{{ (($dealerdata['security_deposit_status'] ?? '')=='received') ? 'on':'' }}"><input type="radio" name="security_deposit_status" value="received" {{ (($dealerdata['security_deposit_status'] ?? '')=='received') ? 'checked':'' }}><span>Received</span></label>
                              <label class="{{ (($dealerdata['security_deposit_status'] ?? '')=='waived') ? 'on':'' }}"><input type="radio" name="security_deposit_status" value="waived" {{ (($dealerdata['security_deposit_status'] ?? '')=='waived') ? 'checked':'' }}><span>Waived</span></label>
                           </div>
                           <div class="cpx-err" id="Dealer-security_deposit_status"></div>
                        </div>
                        <div class="cpx-f depReceivedOnly">
                           <label>Amount of Deposit Received</label>
                           <div class="cpx-unit"><span class="pre">₹</span><input class="cpx-in data" name="security_deposit_received_amount" value="{{ $dealerdata['security_deposit_received_amount'] ?? '' }}" placeholder="0"></div>
                        </div>
                        <div class="cpx-f depReceivedOnly"><label>Deposit Credit Details</label><input class="cpx-in" name="deposit_credit_details" value="{{ $dealerdata['deposit_credit_details'] ?? '' }}" placeholder="Cheque/UTR No. &amp; date"></div>
                     </div>
                     <div class="cpx-shdr mt">Verification Checklist</div>
                     <div class="cpx-checks">
                        <div class="cpx-crow"><div class="lbl">GST No. checked</div><div class="cpx-sw {{ !empty($dealerdata['gst_checked']) ? 'on':'' }}" data-check="gst_checked" onclick="cpToggle(this)"></div></div>
                        <div class="cpx-crow"><div class="lbl">PAN checked</div><div class="cpx-sw {{ !empty($dealerdata['pan_checked']) ? 'on':'' }}" data-check="pan_checked" onclick="cpToggle(this)"></div></div>
                        <div class="cpx-crow"><div class="lbl">Bank details checked</div><div class="cpx-sw {{ !empty($dealerdata['bank_details_checked']) ? 'on':'' }}" data-check="bank_details_checked" onclick="cpToggle(this)"></div></div>
                     </div>
                     <input type="hidden" name="gst_checked"          id="hid_gst_checked"          value="{{ !empty($dealerdata['gst_checked']) ? 1:0 }}">
                     <input type="hidden" name="pan_checked"          id="hid_pan_checked"          value="{{ !empty($dealerdata['pan_checked']) ? 1:0 }}">
                     <input type="hidden" name="bank_details_checked" id="hid_bank_details_checked" value="{{ !empty($dealerdata['bank_details_checked']) ? 1:0 }}">
                  </div>
                  <div class="cpx-shdr mt">Catalogue &amp; Access</div>
                  <div class="cpx-grid">
                     <div class="cpx-f">
                        <label>Product Types</label>
                        @php $noneSelected = empty($selProductTypes); @endphp
                        <select class="cpx-in cpx-select2" name="product_types[]" multiple>
                        @foreach(product_types() as $pkey => $protype)
                        <option value="{{ $pkey }}" {{ (in_array($pkey,$selProductTypes) || ($noneSelected && $loop->first)) ? 'selected':'' }}>{{ $protype }}</option>
                        @endforeach
                        </select>
                        <div class="cpx-hint">Default: Greenwave Textile Products</div>
                     </div>
                     <div class="cpx-f" id="rowLinkedDealers"><label>Linked Dealers</label>
                        <select class="cpx-in cpx-select2" name="linked_dealers[]" multiple>
                        @foreach($otherDealers as $d)
                        <option value="{{ $d['id'] }}" {{ in_array($d['id'],$selDealers) ? 'selected':'' }}>{{ $d['business_name'] }}</option>
                        @endforeach
                        </select>
                     </div>
                     <div class="cpx-f">
                        <label>Linked Products</label>
                        <a class="cpx-btn ghost sm" style="width:max-content" href="{{ route('admin.dealers.products', $dealerdata['id'] ?? 0) }}"><i class="fa fa-link"></i> Manage linked products ({{ $dealerdata['linked_products_count'] ?? (isset($selLinkedProids) ? count($selLinkedProids) : 0) }})</a>
                     </div>
                     @if(!empty($linkedCustomers))
                     <div class="cpx-f">
                        <label>Linked Customers <strong style="color:#d14343">({{ count($linkedCustomers) }})</strong></label>
                        <details>
                           <summary style="cursor:pointer;font-size:13px;color:var(--gd)">View customers</summary>
                           <div style="max-height:160px;overflow:auto;border:1px solid var(--line);border-radius:8px;margin-top:6px;padding:6px 10px;font-size:13px">
                              @foreach($linkedCustomers as $k=>$cust)
                              <div>{{ $k+1 }}. {{ $cust['name'] }}</div>
                              @endforeach
                           </div>
                        </details>
                     </div>
                     @endif
                  </div>
                  {{-- ── ACTIVATION — moved below Modules; Show Class removed ── --}}
                  <div class="cpx-shdr mt">Activation</div>
                  <div class="cpx-grid">
                     <div class="cpx-f">
                        <label>OTP &amp; Pin Enabled <small style="text-transform:none;color:var(--muted)"></small></label>
                        <div class="cpx-seg">
                           <label class="{{ (empty($dealerdata) || ($dealerdata['is_authenticated'] ?? 1)==1) ? 'on':'' }}"><input type="radio" name="is_authenticated" value="1" {{ (empty($dealerdata) || ($dealerdata['is_authenticated'] ?? 1)==1) ? 'checked':'' }}><span>Yes</span></label>
                           <label class="{{ (!empty($dealerdata) && ($dealerdata['is_authenticated'] ?? 1)==0) ? 'on':'' }}"><input type="radio" name="is_authenticated" value="0" {{ (!empty($dealerdata) && ($dealerdata['is_authenticated'] ?? 1)==0) ? 'checked':'' }}><span>No</span></label>
                        </div>
                     </div>
                     <div class="cpx-f">
                        <label>Account Status</label>
                        <div class="cpx-seg">
                           <label class="{{ (empty($dealerdata) || ($dealerdata['status'] ?? 1)==1) ? 'on':'' }}"><input type="radio" name="status" value="1" {{ (empty($dealerdata) || ($dealerdata['status'] ?? 1)==1) ? 'checked':'' }}><span>Active</span></label>
                           <label class="{{ (!empty($dealerdata) && ($dealerdata['status'] ?? 1)==0) ? 'on':'' }}"><input type="radio" name="status" value="0" {{ (!empty($dealerdata) && ($dealerdata['status'] ?? 1)==0) ? 'checked':'' }}><span>Inactive</span></label>
                        </div>
                     </div>
                  </div>
                  {{-- ── MODULES TO ACCESS — identical toggle style to Verification Checklist ── --}}
                  <div class="cpx-shdr mt">Modules to Access</div>
                  <div class="cpx-checks full scroll">
                     <div class="cpx-checks-head"><span>Module</span><span>Allow</span></div>
                     @foreach(app_roles('dealer') as $pkey => $role)
                     @php $hideRole51 = $role['id']==51 && (empty($dealerdata['id']) || $dealerdata['id']!=3); @endphp
                     @if(!$hideRole51)
                     @php $modOn = in_array($role['key'],$selAppRoles); @endphp
                     <div class="cpx-crow" id="role-row-{{ $role['id'] }}">
                        <div class="lbl"><span class="num">{{ $pkey+1 }}</span> {{ $role['name_admin'] }}</div>
                        <div class="cpx-sw {{ $modOn ? 'on':'' }}" onclick="cpModToggle(this)"></div>
                        <input type="checkbox" class="mod-check cpx-hide" name="app_roles[]" value="{{ $role['key'] }}" {{ $modOn ? 'checked':'' }}>
                     </div>
                     @endif
                     @endforeach
                  </div>
                  
                  @endif
               </div>
            </div>
            {{-- ── Sticky action bar ────────────────────────────────── --}}
            <div class="cpx-actions">
               <div class="note">
                  @if($stage=='confirmed')
                  <span style="color:var(--gd);font-weight:600"><i class="fa fa-check-circle"></i> Confirmed partner — active</span>
                  @elseif($stage=='onboarding' && !$isSubmitted)
                  <i class="fa fa-info-circle"></i> Partner must submit the onboarding form before you can confirm.
                  @else
                  <i class="fa fa-info-circle"></i> All sections save together.
                  @endif
               </div>
               <div style="display:flex;gap:9px;flex-wrap:wrap">
                  <button type="button" class="cpx-btn ghost" onclick="cpSubmit('save')"><i class="fa fa-save"></i> Save</button>
                  @if($stage=='evaluation')
                  <button type="button" class="cpx-btn g" onclick="cpSubmit('onboard')"><i class="fa fa-arrow-right"></i> Save &amp; Move to Onboarding</button>
                  @elseif($stage=='onboarding')
                  <button type="button" class="cpx-btn g" onclick="cpSubmit('confirm')" {{ $isSubmitted ? '' : 'disabled title=Awaiting-submission' }}><i class="fa fa-check"></i> Save &amp; Confirm Partner</button>
                  @endif
               </div>
            </div>
         </form>
      </div>
   </div>
</div>
<script>
   $(function () {
     function refreshType(){
       var t = $('input[name=dealer_type]:checked').val();
       var isSub = (t === 'sub dealer');
       $('#typeSeg label').removeClass('on');
       $('input[name=dealer_type]:checked').closest('label').addClass('on');
       $('#cpHeadType').removeClass('primary sub').addClass(isSub?'sub':'primary').text(isSub?'Sub-Dealer':'Primary Dealer');
       $('#rowLinkedDealer').toggle(isSub);
       $('#primaryTerms').toggle(!isSub);
       $('#confPrimary').toggle(!isSub);
       $('#rowLinkedDealers').toggle(!isSub);
       if(!isSub){ $('[name=linked_dealer_id]').val('').trigger('change'); }
       [28,32,33,34,35,40,50,52].forEach(function(rid){
         var row = document.getElementById('role-row-'+rid); if(!row) return;
         if(isSub){
           row.style.display='none';
           var cb=row.querySelector('input.mod-check'); if(cb) cb.checked=false;
           var sw=row.querySelector('.cpx-sw'); if(sw) sw.classList.remove('on');
         }
         else row.style.display='';
       });
     }
     $('input[name=dealer_type]').on('change', refreshType);
     $('.cpx-seg input[type=radio]').on('change', function(){
       $(this).closest('.cpx-seg').find('label').removeClass('on');
       $(this).closest('label').addClass('on');
     });
     refreshType();
     $(document).on('keyup', '#credit_multiple, #security_amount', function(){
       var sec=parseFloat($('#security_amount').val())||0, mul=parseFloat($('#credit_multiple').val())||0;
       if(mul>0) $('#credit_allowed').val((sec*mul).toFixed(2));
     });
     $('.cpx-select2').select2({ width:'resolve' });

     // Show Amount + Deposit Credit Details only when "Received" is selected
     function refreshDepStatus(){
       var val = $('input[name=security_deposit_status]:checked').val();
       $('.depReceivedOnly').toggle(val === 'received');
     }
     $('input[name=security_deposit_status]').on('change', refreshDepStatus);
     refreshDepStatus();
     window.cpSubmit = function(action){
       $('#cpAction').val(action);
       $('.cpx-err').hide().text('');
       $('.loadingDiv').show();
       $.ajax({
         url: '{{ route("admin.channel-partners.save") }}',
         type:'POST', data:new FormData(document.getElementById('CPForm')),
         processData:false, contentType:false,
         success:function(res){
           $('.loadingDiv').hide();
           if(!res.status){
             if(res.errors){
               $.each(res.errors, function(field,msgs){ var el=$('#Dealer-'+field); el.text(msgs[0]).show(); });
               var first=$('.cpx-err:visible').first();
               if(first.length) $('html,body').animate({scrollTop:first.offset().top-150},500);
             } else { alert(res.message || 'Could not save.'); }
           } else { window.location.href = res.url; }
         },
         error:function(){ $('.loadingDiv').hide(); alert('Server error. Please try again.'); }
       });
     };
     $('#btnSendLink').on('click', function(){
       var id=$(this).data('id'), $b=$(this);
       $b.prop('disabled',true).html('<i class="fa fa-spinner fa-spin"></i> Generating…');
       $('#linkErr').hide();
       $.ajax({
         url:'/admin/channel-partners/'+id+'/send-link', type:'POST',
         data:{_token:'{{ csrf_token() }}'},
         success:function(res){
           if(res.status){ $('#obLink').val(res.link); }
           else { $('#linkErr').text(res.message).show(); }
         },
         error:function(){ $('#linkErr').text('Server error. Please try again.').show(); },
         complete:function(){ $b.prop('disabled',false).html('<i class="fa fa-send"></i> Generate / Send Link'); }
       });
     });
     $('#btnCopyLink').on('click', function(){
       var el=document.getElementById('obLink'); if(!el.value) return;
       el.select(); document.execCommand('copy');
       $(this).html('<i class="fa fa-check"></i> Copied');
       setTimeout(()=>$('#btnCopyLink').html('<i class="fa fa-copy"></i> Copy'),1800);
     });
   });
   function cpToggle(el){
     el.classList.toggle('on');
     var key=el.getAttribute('data-check');
     document.getElementById('hid_'+key).value = el.classList.contains('on') ? 1 : 0;
   }
   // module toggle → drive the row's hidden app_roles[] checkbox
   function cpModToggle(el){
     el.classList.toggle('on');
     var cb = el.parentNode.querySelector('input.mod-check');
     if(cb) cb.checked = el.classList.contains('on');
   }
   // show chosen filename under each document upload input
   function cpFileName(input, targetId){
     var t=document.getElementById(targetId); if(!t) return;
     if(input.files && input.files.length){
       t.textContent='New: '+input.files[0].name;
       t.classList.add('set');
     } else { t.textContent=''; t.classList.remove('set'); }
   }
</script>
@endsection