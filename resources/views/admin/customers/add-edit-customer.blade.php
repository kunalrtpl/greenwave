@extends('layouts.adminLayout.backendLayout')
@section('content')
@php
    $isEdit        = !empty($customerdata['id']);
    $businessModel = $customerdata['business_model'] ?? '';
    $paymentTerm   = $customerdata['payment_term'] ?? '';
    $freightBasis  = $customerdata['freight_basis'] ?? 'Paid by Company';
    $showProducts  = in_array($businessModel, ['Direct Customer','Hybrid']);
    $showDealer    = in_array($businessModel, ['Dealer','Hybrid']);
    $selActivites  = (!empty($customerdata['activity'])) ? explode(',', $customerdata['activity']) : [];
    /* products dropdown for the current product type (refreshed via AJAX on change) */
    $productOptions = fetchProducts($customerdata['customer_product_type'] ?? 0);
@endphp
{{-- ════════════════════════════════════════════════════════════════════
CUSTOMER — single page, sectioned add/edit (channel-partner design)
Sections: Business · Primary User · Add-on Users · Model & Linking ·
          Products (Direct Customer / Hybrid) · Business Card · Status
═══════════════════════════════════════════════════════════════════════ --}}
<style>
   #custEdit{
   --ink:#14241c;--ink2:#3a4a42;--muted:#7b8a82;--line:#e6ebe8;--line2:#eef2f0;
   --g:#1f7a44;--gd:#155e34;--gsoft:#e8f3ec;--gsoft2:#f1f8f3;
   --amber:#9a6a12;--amberS:#fdf4e3;--blue:#1f5e9a;--blueS:#e9f1fa;
   --red:#c0392b;--redS:#fdecea;
   --r:11px;--rs:8px;--mono:'JetBrains Mono',Menlo,Consolas,monospace;
   font-family:'Inter','Segoe UI',system-ui,sans-serif;color:var(--ink);
   }
   #custEdit *{box-sizing:border-box}
   #custEdit .cpx-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:18px}
   #custEdit .cpx-head h1{font-size:21px;font-weight:800;margin:0 0 6px;letter-spacing:-.3px;color:var(--ink)}
   #custEdit .cpx-meta{display:flex;gap:13px;flex-wrap:wrap;color:var(--muted);font-size:12.5px;align-items:center}
   #custEdit .cpx-meta .id{font-family:var(--mono);color:var(--ink2)}
   #custEdit .cpx-chip{display:inline-flex;align-items:center;gap:6px;padding:4px 11px;border-radius:30px;font-size:11px;font-weight:700;letter-spacing:.3px;text-transform:uppercase}
   #custEdit .cpx-chip.dc{background:var(--gsoft);color:var(--gd)}
   #custEdit .cpx-chip.dl{background:#efeafe;color:#5b3bbd}
   #custEdit .cpx-chip.hy{background:#fdeede;color:#b05a13}
   #custEdit .cpx-card{background:#fff;border:1px solid var(--line);border-radius:var(--r);box-shadow:0 1px 2px rgba(20,36,28,.04),0 6px 18px rgba(20,36,28,.04);margin-bottom:18px;overflow:hidden}
   #custEdit .cpx-ch{padding:16px 24px;border-bottom:1px solid var(--line2);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
   #custEdit .cpx-ch .t{font-size:15px;font-weight:700;display:flex;align-items:center;gap:9px}
   #custEdit .cpx-ch .t .ix{width:26px;height:26px;border-radius:7px;background:var(--gsoft);color:var(--gd);display:grid;place-items:center;font-size:12px}
   #custEdit .cpx-ch .t small{display:block;font-weight:400;color:var(--muted);font-size:12px;margin-top:1px}
   #custEdit .cpx-cb{padding:22px 24px}
   #custEdit .cpx-shdr{font-size:11px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--gd);display:flex;align-items:center;gap:8px;margin:4px 0 16px}
   #custEdit .cpx-shdr::after{content:"";flex:1;height:1px;background:var(--line2)}
   #custEdit .cpx-shdr.mt{margin-top:28px}
   #custEdit .cpx-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:15px 20px}
   #custEdit .cpx-grid.g3{grid-template-columns:repeat(3,1fr)}
   #custEdit .cpx-f{display:flex;flex-direction:column;gap:5px}
   #custEdit .cpx-f.full{grid-column:1/-1}
   #custEdit .cpx-f>label{font-size:11.5px;font-weight:600;color:var(--ink2);text-transform:uppercase;letter-spacing:.3px;margin:0}
   #custEdit .cpx-f .rq{color:#d14343}
   #custEdit .cpx-in{height:40px;border:1.4px solid var(--line);border-radius:var(--rs);padding:0 12px;font-size:13.5px;background:#fff;color:var(--ink);width:100%;transition:.15s}
   #custEdit .cpx-in:focus{outline:none;border-color:var(--g);box-shadow:0 0 0 3px var(--gsoft)}
   #custEdit .cpx-in.data{font-family:var(--mono);font-size:13px}
   #custEdit .cpx-hint{font-size:11px;color:var(--muted)}
   #custEdit .cpx-err{font-size:12px;color:#d14343;display:none}
   #custEdit .cpx-unit{position:relative}
   #custEdit .cpx-unit .pre{position:absolute;left:11px;top:0;height:40px;display:grid;place-items:center;color:var(--muted);font-family:var(--mono);font-size:13px}
   #custEdit .cpx-unit .cpx-in{padding-left:26px}
   #custEdit .select2-container{width:100%!important}
   #custEdit .select2-container .select2-selection--single,
   #custEdit .select2-container .select2-selection--multiple{min-height:40px;border:1.4px solid var(--line)!important;border-radius:var(--rs)!important}
   #custEdit .cpx-seg{display:inline-flex;background:#f1f4f2;border:1px solid var(--line);border-radius:9px;padding:3px;gap:3px;flex-wrap:wrap}
   #custEdit .cpx-seg label{margin:0;border-radius:7px;font-size:13px;font-weight:600;color:var(--ink2);padding:7px 16px;cursor:pointer;transition:.15s}
   #custEdit .cpx-seg input{display:none}
   #custEdit .cpx-seg label.on{background:#fff;color:var(--gd);box-shadow:0 1px 3px rgba(20,36,28,.1)}
   #custEdit .cpx-btn{display:inline-flex;align-items:center;gap:8px;border:none;border-radius:9px;font-size:13.5px;font-weight:600;padding:10px 18px;cursor:pointer;transition:.15s;text-decoration:none;line-height:1.2}
   #custEdit .cpx-btn.g{background:var(--g);color:#fff}#custEdit .cpx-btn.g:hover{background:var(--gd);color:#fff}
   #custEdit .cpx-btn.ghost{background:#fff;border:1.4px solid var(--line);color:var(--ink2)}#custEdit .cpx-btn.ghost:hover{border-color:#c6d2cc;color:var(--ink)}
   #custEdit .cpx-btn.red{background:var(--redS);color:var(--red)}
   #custEdit .cpx-btn.sm{padding:7px 13px;font-size:12.5px}
   #custEdit .cpx-actions{position:sticky;bottom:0;background:#fff;border:1px solid var(--line);border-top:2px solid var(--g);border-radius:var(--r);box-shadow:0 -4px 18px rgba(20,36,28,.06);padding:14px 22px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:6px;z-index:20}
   #custEdit .cpx-actions .note{font-size:12px;color:var(--muted);display:flex;align-items:center;gap:7px}
   #custEdit .cpx-hide{display:none!important}
   /* ── Add-on user rows ─────────────────────────────────────── */
   #custEdit .emp-row{display:grid;grid-template-columns:1.2fr 1.2fr 1fr 1.4fr auto;gap:10px;align-items:start;padding:12px;border:1px solid var(--line);border-radius:var(--rs);margin-bottom:10px;background:#fbfcfb}
   #custEdit .emp-row .cpx-f>label{font-size:10.5px}
   /* ── Linked executive rows ────────────────────────────────── */
   #custEdit .exec-row{display:grid;grid-template-columns:1fr 1.6fr auto;gap:10px;align-items:start;padding:12px;border:1px solid var(--line);border-radius:var(--rs);margin-bottom:10px;background:#fbfcfb}
   #custEdit .exec-row .cpx-f>label{font-size:10.5px}
   /* ── Product cards (per screenshot) ───────────────────────── */
   #custEdit .prod-card{border:1.4px solid var(--line);border-radius:var(--r);margin-bottom:16px;overflow:hidden;background:#fff}
   #custEdit .prod-card .p-head{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:11px 16px;background:var(--gsoft2);border-bottom:1px solid var(--line2)}
   #custEdit .prod-card .p-head .pt{font-size:13px;font-weight:700;color:var(--gd);display:flex;align-items:center;gap:8px}
   #custEdit .prod-card .p-head .pt .pn{display:inline-grid;place-items:center;width:22px;height:22px;border-radius:6px;background:#fff;border:1px solid #d8ece0;color:var(--gd);font-size:11px;font-weight:700;font-family:var(--mono)}
   #custEdit .prod-body{display:grid;grid-template-columns:1.15fr 1fr;gap:0}
   #custEdit .prod-left{padding:18px 20px;border-right:1px solid var(--line2)}
   #custEdit .prod-left .cpx-grid{grid-template-columns:1fr 1fr;gap:13px 16px}
   /* viability info panel */
   #custEdit .via{padding:18px 20px;background:#fbfcfb}
   #custEdit .via .v-title{font-size:11px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--blue);display:flex;align-items:center;gap:7px;margin-bottom:12px}
   #custEdit .via .v-title .dot{width:8px;height:8px;border-radius:50%;background:var(--blue)}
   #custEdit .via.special .v-title{color:var(--amber)}
   #custEdit .via.special .v-title .dot{background:var(--amber)}
   #custEdit .v-row{display:flex;align-items:baseline;justify-content:space-between;gap:10px;padding:6px 0;border-bottom:1px dashed var(--line2);font-size:12.5px}
   #custEdit .v-row:last-child{border-bottom:none}
   #custEdit .v-row .k{color:var(--ink2);font-weight:500}
   #custEdit .v-row .k small{display:block;color:var(--muted);font-weight:400;font-size:10.5px}
   #custEdit .v-row .v{font-family:var(--mono);font-weight:600;font-size:13px}
   #custEdit .v-row.msp{background:#fffbe6;margin:4px -8px 0;padding:8px;border-radius:7px;border-bottom:none}
   #custEdit .v-row.real{margin:2px -8px 0;padding:8px;border-radius:7px;border-bottom:none}
   #custEdit .v-row.real.ok{background:var(--gsoft)}
   #custEdit .v-row.real.ok .v{color:var(--gd)}
   #custEdit .v-row.real.bad{background:var(--redS)}
   #custEdit .v-row.real.bad .v{color:var(--red)}
   /* special block */
   #custEdit .spec-wrap{border-top:1px solid var(--line2);padding:14px 20px;background:#fff}
   #custEdit .spec-wrap .sp-toggle{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
   #custEdit .spec-wrap .sp-toggle .lbl{font-size:12px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;color:var(--amber)}
   #custEdit .spec-body{display:grid;grid-template-columns:1.15fr 1fr;gap:0;margin-top:14px;border:1.4px solid #f2e3c8;border-radius:var(--rs);overflow:hidden}
   #custEdit .spec-body .prod-left{background:#fffdf8}
   #custEdit .spec-body .via{background:#fdf8ee}
   @media(max-width:900px){
   #custEdit .prod-body,#custEdit .spec-body{grid-template-columns:1fr}
   #custEdit .prod-left{border-right:none;border-bottom:1px solid var(--line2)}
   #custEdit .emp-row,#custEdit .exec-row{grid-template-columns:1fr 1fr}
   }
   @media(max-width:768px){
   #custEdit .cpx-grid,#custEdit .cpx-grid.g3,#custEdit .prod-left .cpx-grid{grid-template-columns:1fr}
   #custEdit .emp-row,#custEdit .exec-row{grid-template-columns:1fr}
   }
</style>
<div class="page-content-wrapper">
   <div class="page-content">
      <ul class="page-breadcrumb breadcrumb" style="margin-bottom:14px;">
         <li><a href="{{ url('admin/dashboard') }}">Dashboard</a><i class="fa fa-circle"></i></li>
         <li><a href="{{ url('admin/customers') }}">Customers</a><i class="fa fa-circle"></i></li>
         <li><span>{{ $title }}</span></li>
      </ul>
      <div id="custEdit">
         {{-- ── Header ───────────────────────────────────────────────── --}}
         <div class="cpx-head">
            <div>
               <h1>{{ $isEdit ? ($customerdata['name'] ?: 'Unnamed Customer') : 'New Customer' }}</h1>
               <div class="cpx-meta">
                  @if($isEdit)<span class="id">#CU-{{ $customerdata['id'] }}</span>@endif
                  @if(!empty($customerdata['mobile']))<span><i class="fa fa-phone"></i> {{ $customerdata['mobile'] }}</span>@endif
                  <span id="custHeadModel" class="cpx-chip {{ $businessModel=='Hybrid' ? 'hy' : ($businessModel=='Dealer' ? 'dl' : 'dc') }}">{{ $businessModel ?: 'No Business Model' }}</span>
               </div>
            </div>
            <a class="cpx-btn ghost sm" href="{{ url('admin/customers') }}"><i class="fa fa-arrow-left"></i> Back to list</a>
         </div>

         <form id="Customerform" method="post" action="javascript:;" enctype="multipart/form-data" autocomplete="off">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="customerid" value="{{ $customerdata['id'] ?? '' }}">
            @if(isset($_GET['ref']))<input type="hidden" name="register_request_id" value="{{ $_GET['ref'] }}">@endif
            @if(isset($_GET['empref']))<input type="hidden" name="customer_register_request_id" value="{{ $_GET['empref'] }}">@endif

            {{-- ── SECTION 1: Business Details ─────────────────────────── --}}
            <div class="cpx-card">
               <div class="cpx-ch"><div class="t"><span class="ix">1</span><div>Business Details<small>Firm identity &amp; area</small></div></div></div>
               <div class="cpx-cb">
                  <div class="cpx-grid">
                     <div class="cpx-f">
                        <label>Business Name <span class="rq">*</span></label>
                        <input class="cpx-in" name="name" value="{{ $customerdata['name'] ?? '' }}" placeholder="e.g. ABC Textiles">
                        <div class="cpx-err" id="Customer-name"></div>
                     </div>
                     <div class="cpx-f">
                        <label>City <span class="rq">*</span></label>
                        <select class="cpx-in cpx-select2" name="cities[]" required>
                           @foreach(getcities() as $city)
                           <option value="{{ $city['city_name'] }}" {{ in_array($city['city_name'],$selCities) ? 'selected':'' }}>{{ $city['city_name'] }}</option>
                           @endforeach
                        </select>
                        <div class="cpx-err" id="Customer-cities"></div>
                     </div>
                     <div class="cpx-f full">
                        <label>Address</label>
                        <input class="cpx-in" name="address" value="{{ $customerdata['address'] ?? '' }}" placeholder="Full address">
                        <div class="cpx-err" id="Customer-address"></div>
                     </div>
                     <div class="cpx-f full">
                        <label>Business Activity</label>
                        <select class="cpx-in cpx-select2" name="activity[]" multiple>
                           @foreach(activities() as $activity)
                           <option value="{{ $activity }}" {{ in_array($activity,$selActivites) ? 'selected':'' }}>{{ $activity }}</option>
                           @endforeach
                        </select>
                        <div class="cpx-err" id="Customer-activity"></div>
                     </div>
                  </div>
               </div>
            </div>

            {{-- ── SECTION 2: Primary User ─────────────────────────────── --}}
            <div class="cpx-card">
               <div class="cpx-ch"><div class="t"><span class="ix">2</span><div>Primary User<small>Main contact who logs in to the app</small></div></div></div>
               <div class="cpx-cb">
                  <div class="cpx-grid">
                     <div class="cpx-f">
                        <label>Name</label>
                        <input class="cpx-in" name="contact_person_name" value="{{ $customerdata['contact_person_name'] ?? '' }}" placeholder="Full name">
                        <div class="cpx-err" id="Customer-contact_person_name"></div>
                     </div>
                     <div class="cpx-f">
                        <label>Designation</label>
                        <select class="cpx-in" name="designation">
                           <option value="">— Select —</option>
                           @foreach(getDesignations() as $dkey => $designation)
                           <option value="{{ $dkey }}" {{ (!empty($customerdata['designation']) && $customerdata['designation']==$dkey) ? 'selected':'' }}>{{ $designation }}</option>
                           @endforeach
                        </select>
                        <div class="cpx-err" id="Customer-designation"></div>
                     </div>
                     <div class="cpx-f">
                        <label>Mobile No. <span class="rq">*</span></label>
                        <input class="cpx-in data" name="mobile" value="{{ $customerdata['mobile'] ?? '' }}" placeholder="10-digit">
                        <div class="cpx-err" id="Customer-mobile"></div>
                     </div>
                     <div class="cpx-f">
                        <label>E-mail ID</label>
                        <input class="cpx-in" name="email" value="{{ $customerdata['email'] ?? '' }}" placeholder="email@example.com">
                        <div class="cpx-err" id="Customer-email"></div>
                     </div>
                     <div class="cpx-f full">
                        <label>Location Address</label>
                        <input class="cpx-in" name="location_address" value="{{ $customerdata['location_address'] ?? '' }}" placeholder="Geo location address">
                        <div class="cpx-err" id="Customer-location_address"></div>
                     </div>
                  </div>
               </div>
            </div>

            {{-- ── SECTION 3: Add-on Users ─────────────────────────────── --}}
            <div class="cpx-card">
               <div class="cpx-ch">
                  <div class="t"><span class="ix">3</span><div>Add-on Users<small>Extra people from this business</small></div></div>
                  <button type="button" class="cpx-btn ghost sm" id="addAssignRow"><i class="fa fa-plus"></i> Add user</button>
               </div>
               <div class="cpx-cb">
                  <div id="EmpRows">
                     @if(!empty($customerdata['employees']))
                        @foreach($customerdata['employees'] as $custekey => $custEmp)
                        <div class="emp-row">
                           <input type="hidden" name="cust_emp_id[]" value="{{ $custEmp['id'] }}">
                           <div class="cpx-f"><label>Designation</label>
                              <select class="cpx-in" name="designations[]" required>
                                 <option value="">— Select —</option>
                                 @foreach(getDesignations() as $dkey => $designation)
                                 <option value="{{ $dkey }}" {{ $custEmp['designation']==$dkey ? 'selected':'' }}>{{ $designation }}</option>
                                 @endforeach
                              </select>
                           </div>
                           <div class="cpx-f"><label>Name</label><input class="cpx-in" name="names[]" value="{{ $custEmp['name'] }}" required></div>
                           <div class="cpx-f"><label>Mobile</label><input class="cpx-in data" type="number" name="mobiles[]" value="{{ $custEmp['mobile'] }}" required></div>
                           <div class="cpx-f"><label>Email</label><input class="cpx-in" type="email" name="emails[]" value="{{ $custEmp['email'] }}" required></div>
                           <div class="cpx-f"><label>Remove</label>
                              <label style="display:flex;align-items:center;gap:6px;height:40px;font-size:12.5px;color:var(--red);cursor:pointer">
                                 <input type="checkbox" name="is_delete[{{ $custekey }}]" value="1" style="accent-color:var(--red)"> Delete
                              </label>
                           </div>
                        </div>
                        @endforeach
                     @endif
                  </div>
                  <p class="cpx-hint" id="empEmptyHint" style="{{ !empty($customerdata['employees']) ? 'display:none' : '' }}">No add-on users yet — click “Add user”.</p>
               </div>
            </div>

            {{-- ── SECTION 4: Linked Executives ────────────────────────── --}}
            <div class="cpx-card">
               <div class="cpx-ch">
                  <div class="t"><span class="ix">4</span><div>Linked Executives<small>Marketing employees serving this customer</small></div></div>
                  <button type="button" class="cpx-btn ghost sm" id="addExecRow"><i class="fa fa-plus"></i> Add executive</button>
               </div>
               <div class="cpx-cb">
                  <div id="ExecRows">
                     @if(!empty($customerdata['user_customer_shares']))
                        @foreach($customerdata['user_customer_shares'] as $share)
                        <div class="exec-row">
                           <div class="cpx-f"><label>Date</label>
                              <input class="cpx-in data" type="date" name="user_dates[]" value="{{ !empty($share['user_date']) ? date('Y-m-d', strtotime($share['user_date'])) : '' }}" required>
                           </div>
                           <div class="cpx-f"><label>Employee</label>
                              <select class="cpx-in" name="marketing_user_ids[]" required>
                                 <option value="">— Select Employee —</option>
                                 @foreach($users as $user)
                                 <option value="{{ $user['id'] }}" {{ $share['user_id']==$user['id'] ? 'selected':'' }}>{{ $user['name'] }}</option>
                                 @endforeach
                              </select>
                           </div>
                           <div class="cpx-f"><label>&nbsp;</label>
                              <button type="button" class="cpx-btn red sm execRowRemove" style="height:40px"><i class="fa fa-times"></i> Remove</button>
                           </div>
                        </div>
                        @endforeach
                     @endif
                  </div>
                  <p class="cpx-hint" id="execEmptyHint" style="{{ !empty($customerdata['user_customer_shares']) ? 'display:none' : '' }}">No executives linked yet — click “Add executive”.</p>
               </div>
            </div>

            {{-- ── SECTION 5: Business Model & Products ────────────────── --}}
            <div class="cpx-card">
               <div class="cpx-ch"><div class="t"><span class="ix">5</span><div>Business Model &amp; Products<small>How this customer buys, terms &amp; per-product pricing</small></div></div></div>
               <div class="cpx-cb">
                  <div class="cpx-grid">
                     <div class="cpx-f">
                        <label>Product Type <span class="rq">*</span></label>
                        <select class="cpx-in" name="customer_product_type" id="ProductTypeSel">
                           @foreach(product_types() as $pkey => $protype)
                           <option value="{{ $pkey }}" {{ (isset($customerdata['customer_product_type']) ? $customerdata['customer_product_type']==$pkey : $pkey==0) ? 'selected':'' }}>{{ $protype }}</option>
                           @endforeach
                        </select>
                     </div>
                     <div class="cpx-f">
                        <label>Business Model <span class="rq">*</span></label>
                        <select class="cpx-in cpx-select2" name="business_model" id="BusinessModelSel">
                           <option value="">— Select —</option>
                           @foreach(buisnesModels() as $bm)
                           <option value="{{ $bm }}" {{ $businessModel==$bm ? 'selected':'' }}>{{ $bm }}</option>
                           @endforeach
                        </select>
                        <div class="cpx-hint">Products &amp; pricing below apply to <b>Direct Customer</b> and <b>Hybrid</b>. Hybrid is also linked to a dealer.</div>
                        <div class="cpx-err" id="Customer-business_model"></div>
                     </div>
                     <div class="cpx-f" id="DealerDiv" style="{{ $showDealer ? '' : 'display:none' }}">
                        <label>Linked Dealer <span class="rq">*</span></label>
                        <select class="cpx-in cpx-select2" name="dealer_id">
                           <option value="">— Select Dealer —</option>
                           @foreach(dealers() as $dealer)
                           <option value="{{ $dealer['id'] }}" {{ (!empty($customerdata['dealer_id']) && $customerdata['dealer_id']==$dealer['id']) ? 'selected':'' }}>{{ $dealer['business_name'] }}</option>
                           @endforeach
                        </select>
                        <div class="cpx-err" id="Customer-dealer_id"></div>
                     </div>
                  </div>

                  {{-- Commercial terms — Direct Customer & Hybrid --}}
                  <div id="CommercialTerms" style="{{ $showProducts ? '' : 'display:none' }}">
                     <div class="cpx-shdr mt">Commercial Terms</div>
                     <div class="cpx-grid g3">
                        <div class="cpx-f">
                           <label>Payment Terms <span class="rq">*</span></label>
                           <select class="cpx-in" name="payment_term" id="PaymentTermSel">
                              <option value="">— Select —</option>
                              @foreach(payment_terms() as $pt)
                              <option value="{{ $pt }}" data-premium="{{ direct_sales_premium($pt) }}" {{ $paymentTerm==$pt ? 'selected':'' }}>{{ $pt }}</option>
                              @endforeach
                           </select>
                           <div class="cpx-hint">Sets Direct Sales Premium: 60d 5% · 45d 4% · 30d 3% · 15d 2% · 1-7d 2% · Advance 1%</div>
                           <div class="cpx-err" id="Customer-payment_term"></div>
                        </div>
                        <div class="cpx-f">
                           <label>Freight Basis <span class="rq">*</span></label>
                           <select class="cpx-in" name="freight_basis" id="FreightBasisSel">
                              <option value="Paid by Company" {{ $freightBasis=='Paid by Company' ? 'selected':'' }}>Paid by Company</option>
                              <option value="Paid by Customer" {{ $freightBasis=='Paid by Customer' ? 'selected':'' }}>Paid by Customer</option>
                           </select>
                           <div class="cpx-err" id="Customer-freight_basis"></div>
                        </div>
                        <div class="cpx-f" id="FreightField" style="{{ $freightBasis=='Paid by Company' ? '' : 'display:none' }}">
                           <label>Freight (Rs./kg)</label>
                           <div class="cpx-unit"><span class="pre">₹</span><input class="cpx-in data" type="number" step="0.01" name="freight" id="FreightInput" value="{{ $customerdata['freight'] ?? '' }}" placeholder="0.00"></div>
                           <div class="cpx-hint">To fill only when freight is paid by company.</div>
                        </div>
                     </div>
                  </div>

                  {{-- Products — same card, Direct Customer & Hybrid only --}}
                  <div id="ProductsCard" style="{{ $showProducts ? '' : 'display:none' }}">
                     <div class="cpx-shdr mt" style="align-items:center">Products
                        <button type="button" class="cpx-btn g sm addProductBtn" style="margin-left:auto"><i class="fa fa-plus"></i> Add product</button>
                     </div>
                     <p class="cpx-hint" style="margin:-8px 0 12px">Selling price, MOQ &amp; live viability check per product.</p>
                     <div id="ProductCards"></div>
                     <p class="cpx-hint" id="prodEmptyHint">No products added yet — click “Add product”.</p>
                     <div class="cpx-err" id="Customer-linking_error"></div>
                     <button type="button" class="cpx-btn g addProductBtn" id="addProductBottom" style="width:100%;justify-content:center;margin-top:6px;display:none"><i class="fa fa-plus"></i> Add another product</button>
                  </div>

               </div>
            </div>

            {{-- ── SECTION 6: Business Card Upload ─────────────────────── --}}
            <div class="cpx-card">
               <div class="cpx-ch"><div class="t"><span class="ix">6</span><div>Business Card<small>Front &amp; back photos</small></div></div></div>
               <div class="cpx-cb">
                  <div class="cpx-grid">
                     <div class="cpx-f">
                        <label>Business Card (1)</label>
                        <input type="file" class="cpx-in" name="business_card" style="padding-top:7px">
                        <div class="cpx-err" id="Customer-business_card"></div>
                        @if(!empty($customerdata['business_card_url']))
                        <a href="{{ asset($customerdata['business_card_url']) }}" target="_blank">
                           <img src="{{ asset($customerdata['business_card_url']) }}" alt="Business Card Front" style="max-height:130px;margin-top:8px;border:1px solid var(--line);border-radius:8px;padding:4px;">
                        </a>
                        @endif
                     </div>
                     <div class="cpx-f">
                        <label>Business Card (2)</label>
                        <input type="file" class="cpx-in" name="business_card_two" style="padding-top:7px">
                        <div class="cpx-err" id="Customer-business_card_two"></div>
                        @if(!empty($customerdata['business_card_two_url']))
                        <a href="{{ asset($customerdata['business_card_two_url']) }}" target="_blank">
                           <img src="{{ asset($customerdata['business_card_two_url']) }}" alt="Business Card Back" style="max-height:130px;margin-top:8px;border:1px solid var(--line);border-radius:8px;padding:4px;">
                        </a>
                        @endif
                     </div>
                  </div>
               </div>
            </div>

            {{-- ── SECTION 7: Meta & Status ────────────────────────────── --}}
            <div class="cpx-card">
               <div class="cpx-ch"><div class="t"><span class="ix">7</span><div>Status &amp; Meta</div></div></div>
               <div class="cpx-cb">
                  <div class="cpx-grid g3">
                     @if(!empty($requestReceivedFrom))
                     <div class="cpx-f"><label>Request Received From</label><input class="cpx-in" value="{{ $requestReceivedFrom }}" readonly style="background:#f6f8f7"></div>
                     @endif
                     @if(!empty($customerCreatedBy))
                     <div class="cpx-f"><label>Customer Created By</label><input class="cpx-in" value="{{ $customerCreatedBy }}" readonly style="background:#f6f8f7"></div>
                     @endif
                     <div class="cpx-f">
                        <label>Status <span class="rq">*</span></label>
                        @php $curStatus = isset($customerdata['status']) ? (string)$customerdata['status'] : '1'; @endphp
                        <div class="cpx-seg">
                           <label class="{{ $curStatus=='1' ? 'on':'' }}"><input type="radio" name="status" value="1" {{ $curStatus=='1' ? 'checked':'' }}><span>Active</span></label>
                           <label class="{{ $curStatus=='0' ? 'on':'' }}"><input type="radio" name="status" value="0" {{ $curStatus=='0' ? 'checked':'' }}><span>Inactive</span></label>
                        </div>
                        <div class="cpx-err" id="Customer-status"></div>
                     </div>
                  </div>
               </div>
            </div>

            {{-- ── Sticky action bar ───────────────────────────────────── --}}
            <div class="cpx-actions">
               <div class="note"><i class="fa fa-info-circle"></i> Viability figures are informational — they don't block saving.</div>
               <button type="submit" class="cpx-btn g"><i class="fa fa-save"></i> {{ $isEdit ? 'Save Customer' : 'Create Customer' }}</button>
            </div>
         </form>
      </div>
   </div>
</div>

{{-- Master product options (cloned into each product card select) --}}
<select id="masterProductOptions" class="cpx-hide" style="display:none">
   <option value="" data-dp="0" data-packing="50 kg">— Select Product —</option>
   @foreach($productOptions as $p)
   <option value="{{ $p['id'] }}" data-dp="{{ getProductStandardDp($p['id']) }}" data-packing="{{ getProductStandardPacking($p['id']) }}">{{ $p['product_name'] }}</option>
   @endforeach
</select>

<script type="text/javascript">
(function($){
   /* ══════════ Config mirrored from PHP helpers — keep in sync ══════════ */
   var PACKING_COST = { 'Standard': 0, '5kg*2': 25, '1kg*10': 35 };
   var PACKING_LABEL = { 'Standard': 'Standard (50 kg)', '5kg*2': '5kg*2', '1kg*10': '1kg*10' };
   var PREMIUM = { 'Advance':1, '1-7 days':2, '15 days':2, '30 days':3, '45 days':4, '60 days':5 };

   var prodIdx = 0;
   var existingProducts = {!! json_encode(collect($existingNetProducts ?? [])->map(function($r){
        $r = is_array($r) ? $r : $r->toArray();
        return [
            'id'                            => $r['id'] ?? '',
            'product_id'                    => $r['product_id'] ?? '',
            'packing_size'                  => $r['packing_type'] ?: 'Standard',
            'customer_selling_price'        => $r['net_price'] ?? '',
            'moq'                           => $r['moq'] ?? '',
            'selling_expense_basis'         => $r['selling_expense_basis'] ?? '%',
            'selling_expense_value'         => $r['selling_expense_value'] ?? '',
            'has_special'                   => $r['has_special'] ?? 'no',
            'special_moq'                   => $r['for_qty'] ?? '',
            'special_basis'                 => $r['applicable_type'] ?: 'Special Discount',
            'special_value'                 => $r['value'] ?? '',
            'special_selling_expense_basis' => $r['special_selling_expense_basis'] ?? '%',
            'special_selling_expense_value' => $r['special_selling_expense_value'] ?? '',
        ];
   })->values()) !!};

   function num(v){ v = parseFloat(v); return isNaN(v) ? 0 : v; }
   function fmt(v){ return num(v).toFixed(2); }

   function seLabel(){
      return ($('#BusinessModelSel').val() === 'Direct Customer') ? 'ORC' : 'Selling Expense';
   }
   function globalPremium(){ return PREMIUM[$('#PaymentTermSel').val()] || 0; }
   function globalFreight(){
      return ($('#FreightBasisSel').val() === 'Paid by Company') ? num($('#FreightInput').val()) : 0;
   }

   /* ══════════ Viability math — mirrors customer_viability_check() ══════ */
   function viability(dp, packing, expenseBasis, expenseValue, sellingPrice){
      var premium  = globalPremium();
      var base     = dp * (1 + premium/100);
      var packCost = PACKING_COST[packing] || 0;
      var freight  = globalFreight();
      var expenses = (expenseBasis === '%') ? sellingPrice * (expenseValue/100) : expenseValue;
      var msp      = base + packCost + freight + expenses;
      return {
         dp: dp, premium: premium, base: base, packCost: packCost, freight: freight,
         expenses: expenses, msp: msp, realization: sellingPrice - msp
      };
   }

   function viaPanel(prefix, specialCls){
      return '' +
      '<div class="via '+(specialCls||'')+'">' +
        '<div class="v-title"><span class="dot"></span> Viability Check <span style="font-weight:400;text-transform:none;letter-spacing:0;color:var(--muted)">· informational</span></div>' +
        '<div class="v-row"><div class="k">Standard DP<small>fetched from Product Pricing</small></div><div class="v" data-v="'+prefix+'dp">0.00</div></div>' +
        '<div class="v-row"><div class="k">Direct Sales Premium<small>static, as per Payment Term</small></div><div class="v" data-v="'+prefix+'premium">0%</div></div>' +
        '<div class="v-row"><div class="k">Base Price<small>automatic (calculated)</small></div><div class="v" data-v="'+prefix+'base">0.00</div></div>' +
        '<div class="v-row"><div class="k">Additional Packing Cost<small>only for 5kg*2 / 1kg*10</small></div><div class="v" data-v="'+prefix+'pack">0.00</div></div>' +
        '<div class="v-row"><div class="k">Freight (Rs./kg)<small>if paid by company</small></div><div class="v" data-v="'+prefix+'freight">0.00</div></div>' +
        '<div class="v-row"><div class="k"><span class="seLabel">Selling Expense</span>s (Rs./kg)<small>automatic (calculated)</small></div><div class="v" data-v="'+prefix+'exp">0.00</div></div>' +
        '<div class="v-row msp"><div class="k"><b>Minimum Selling Price</b></div><div class="v" data-v="'+prefix+'msp">0.00</div></div>' +
        '<div class="v-row real ok" data-v-row="'+prefix+'real"><div class="k"><b>Additional Realization</b></div><div class="v" data-v="'+prefix+'real">0.00</div></div>' +
      '</div>';
   }

   /* ══════════ Product card template ══════════ */
   function productCardHtml(i){
      var opts = document.getElementById('masterProductOptions').innerHTML;
      var packOpts = '';
      $.each(PACKING_LABEL, function(k, lbl){ packOpts += '<option value="'+k+'">'+lbl+'</option>'; });

      return '' +
      '<div class="prod-card" data-idx="'+i+'">' +
        '<div class="p-head">' +
          '<div class="pt"><span class="pn">'+' P '+'</span><span class="p-title">Product</span></div>' +
          '<button type="button" class="cpx-btn red sm removeProduct"><i class="fa fa-times"></i> Remove</button>' +
        '</div>' +
        '<div class="prod-body">' +
          '<div class="prod-left">' +
            '<div class="cpx-shdr" style="margin-top:0">Product Details</div>' +
            '<div class="cpx-grid">' +
              '<div class="cpx-f full"><label>Product Name <span class="rq">*</span></label>' +
                '<select class="cpx-in prodSelect" name="net_products['+i+'][product_id]" required style="width:100%">'+opts+'</select></div>' +
              '<div class="cpx-f"><label>Packing Size <span class="rq">*</span></label>' +
                '<select class="cpx-in f-packing" name="net_products['+i+'][packing_size]">'+packOpts+'</select>' +
                '<div class="cpx-hint pack-hint">Default: Standard (50 kg)</div></div>' +
              '<div class="cpx-f"><label>Customer Selling Price <span class="rq">*</span></label>' +
                '<div class="cpx-unit"><span class="pre">₹</span><input class="cpx-in data f-csp" type="number" step="0.01" name="net_products['+i+'][customer_selling_price]" placeholder="to fill" required></div></div>' +
              '<div class="cpx-f"><label>MOQ (kg)</label>' +
                '<input class="cpx-in data f-moq" type="number" step="0.001" name="net_products['+i+'][moq]" placeholder="to fill (if any)"></div>' +
              '<div class="cpx-f"><label><span class="seLabel">Selling Expense</span> / Basis <span class="rq">*</span></label>' +
                '<select class="cpx-in f-seb" name="net_products['+i+'][selling_expense_basis]">' +
                  '<option value="%">%</option><option value="Rs/kg">Rs/kg</option></select></div>' +
              '<div class="cpx-f"><label><span class="seLabel">Selling Expense</span> / Value <span class="rq">*</span></label>' +
                '<input class="cpx-in data f-sev" type="number" step="0.001" name="net_products['+i+'][selling_expense_value]" placeholder="to fill"></div>' +
            '</div>' +
            '<input type="hidden" name="net_products['+i+'][id]" class="f-rowid">' +
          '</div>' +
          viaPanel('m-') +
        '</div>' +
        '<div class="spec-wrap">' +
          '<div class="sp-toggle">' +
            '<span class="lbl">Conditional Special Price / Special Discount (if any)</span>' +
            '<div class="cpx-seg">' +
              '<label class="on"><input type="radio" name="net_products['+i+'][has_special]" value="no" checked><span>No</span></label>' +
              '<label><input type="radio" name="net_products['+i+'][has_special]" value="yes"><span>Yes</span></label>' +
            '</div>' +
          '</div>' +
          '<div class="spec-body cpx-hide">' +
            '<div class="prod-left">' +
              '<div class="cpx-grid">' +
                '<div class="cpx-f"><label>For MOQ (kg) <span class="rq">*</span></label>' +
                  '<input class="cpx-in data f-smoq" type="number" step="0.001" name="net_products['+i+'][special_moq]" placeholder="e.g. 2000"></div>' +
                '<div class="cpx-f"><label>Select Basis <span class="rq">*</span></label>' +
                  '<select class="cpx-in f-sbasis" name="net_products['+i+'][special_basis]">' +
                    '<option value="Special Discount">Special Discount</option>' +
                    '<option value="Special Price">Special Price</option></select></div>' +
                '<div class="cpx-f"><label>Special Price / Special Discount <span class="rq">*</span></label>' +
                  '<input class="cpx-in data f-svalue" type="number" step="0.01" name="net_products['+i+'][special_value]" placeholder="Rs. if Price · % if Discount"></div>' +
                '<div class="cpx-f"><label>Net Price (after discount)</label>' +
                  '<input class="cpx-in data f-snet" readonly style="background:#fffbe6;font-weight:700" placeholder="automatic (calculated)"></div>' +
                '<div class="cpx-f"><label><span class="seLabel">Selling Expense</span> / Basis</label>' +
                  '<select class="cpx-in f-sseb" name="net_products['+i+'][special_selling_expense_basis]">' +
                    '<option value="%">%</option><option value="Rs/kg">Rs/kg</option></select>' +
                  '<div class="cpx-hint">Pre-filled from above (but editable)</div></div>' +
                '<div class="cpx-f"><label><span class="seLabel">Selling Expense</span> / Value</label>' +
                  '<input class="cpx-in data f-ssev" type="number" step="0.001" name="net_products['+i+'][special_selling_expense_value]">' +
                  '<div class="cpx-hint">Pre-filled from above (but editable)</div></div>' +
              '</div>' +
            '</div>' +
            viaPanel('s-','special') +
          '</div>' +
        '</div>' +
      '</div>';
   }

   /* ══════════ Card lifecycle ══════════ */
   function addProductCard(data){
      var i = prodIdx++;
      var $card = $(productCardHtml(i));
      $('#ProductCards').append($card);
      $('#prodEmptyHint').hide();

      if(data){
         $card.find('.f-rowid').val(data.id || '');
         $card.find('.prodSelect').val(String(data.product_id || ''));
         $card.find('.f-packing').val(data.packing_size || 'Standard');
         $card.find('.f-csp').val(data.customer_selling_price);
         $card.find('.f-moq').val(data.moq);
         $card.find('.f-seb').val(data.selling_expense_basis || '%');
         $card.find('.f-sev').val(data.selling_expense_value);
         if((data.has_special || 'no') === 'yes'){
            $card.find('input[value=yes]').prop('checked', true).closest('label').addClass('on')
                 .siblings().removeClass('on');
            $card.find('.spec-body').removeClass('cpx-hide');
         }
         $card.find('.f-smoq').val(data.special_moq);
         $card.find('.f-sbasis').val(data.special_basis || 'Special Discount');
         $card.find('.f-svalue').val(data.special_value);
         $card.find('.f-sseb').val(data.special_selling_expense_basis || '%');
         $card.find('.f-ssev').val(data.special_selling_expense_value);
      }
      /* searchable product dropdown — init select2 AFTER the card is in the
         DOM and the value is set, so it renders the selected product */
      $card.find('.prodSelect').select2({
         width: '100%',
         placeholder: '— Select Product —'
      });
      refreshLabels($card);
      recalcCard($card);
      renumberCards();
   }

   function renumberCards(){
      $('#ProductCards .prod-card').each(function(n){
         $(this).find('.pn').text(n+1);
      });
      var count = $('#ProductCards .prod-card').length;
      $('#prodEmptyHint').toggle(!count);
      $('#addProductBottom').toggle(count > 0);
   }

   /* ══════════ Recalculation ══════════ */
   function setV($card, key, val){ $card.find('[data-v="'+key+'"]').text(val); }

   function paintRealization($card, prefix, realization){
      var $row = $card.find('[data-v-row="'+prefix+'real"]');
      $row.removeClass('ok bad').addClass(realization >= 0 ? 'ok' : 'bad');
      setV($card, prefix+'real', fmt(realization));
   }

   function recalcCard($card){
      var $sel = $card.find('.prodSelect option:selected');
      var dp   = num($sel.data('dp'));
      var pack = $card.find('.f-packing').val();
      var csp  = num($card.find('.f-csp').val());
      var seb  = $card.find('.f-seb').val();
      var sev  = num($card.find('.f-sev').val());

      var title = $sel.text();
      $card.find('.p-title').text(title && $card.find('.prodSelect').val() ? title : 'Product');

      /* "Standard" packing label comes from the product master (value stays 'Standard') */
      var stdPack = $sel.data('packing') || '50 kg';
      $card.find('.f-packing option[value=Standard]').text('Standard ('+stdPack+')');
      $card.find('.pack-hint').text('Default: Standard ('+stdPack+')');

      var m = viability(dp, pack, seb, sev, csp);
      setV($card,'m-dp',fmt(m.dp)); setV($card,'m-premium',m.premium+'%');
      setV($card,'m-base',fmt(m.base)); setV($card,'m-pack',fmt(m.packCost));
      setV($card,'m-freight',fmt(m.freight)); setV($card,'m-exp',m.expenses.toFixed(3).replace(/0+$/,'').replace(/\.$/,'') || '0');
      setV($card,'m-msp',fmt(m.msp)); paintRealization($card,'m-',m.realization);

      /* special block */
      if($card.find('input[name$="[has_special]"][value=yes]').is(':checked')){
         var sbasis = $card.find('.f-sbasis').val();
         var sval   = num($card.find('.f-svalue').val());
         var netPrice = (sbasis === 'Special Discount') ? csp * (1 - sval/100) : sval;
         $card.find('.f-snet').val(fmt(netPrice));

         var sseb = $card.find('.f-sseb').val();
         var ssev = num($card.find('.f-ssev').val());
         var s = viability(dp, pack, sseb, ssev, netPrice);
         setV($card,'s-dp',fmt(s.dp)); setV($card,'s-premium',s.premium+'%');
         setV($card,'s-base',fmt(s.base)); setV($card,'s-pack',fmt(s.packCost));
         setV($card,'s-freight',fmt(s.freight)); setV($card,'s-exp',s.expenses.toFixed(3).replace(/0+$/,'').replace(/\.$/,'') || '0');
         setV($card,'s-msp',fmt(s.msp)); paintRealization($card,'s-',s.realization);
      }
   }
   function recalcAll(){ $('#ProductCards .prod-card').each(function(){ recalcCard($(this)); }); }

   function refreshLabels($scope){
      ($scope || $('#custEdit')).find('.seLabel').text(seLabel());
   }

   /* ══════════ Business model behaviour ══════════ */
   function refreshBusinessModel(){
      var bm = $('#BusinessModelSel').val();
      var showDealer   = (bm === 'Dealer' || bm === 'Hybrid');
      var showProducts = (bm === 'Direct Customer' || bm === 'Hybrid');
      $('#DealerDiv').toggle(showDealer);
      if(!showDealer) $('[name=dealer_id]').val('').trigger('change.select2');
      $('#CommercialTerms').toggle(showProducts);
      $('#ProductsCard').toggle(showProducts);
      $('#custHeadModel').removeClass('dc dl hy')
         .addClass(bm==='Hybrid' ? 'hy' : (bm==='Dealer' ? 'dl' : 'dc'))
         .text(bm || 'No Business Model');
      refreshLabels();
      recalcAll();
   }

   /* ══════════ Events ══════════ */
   $(document).ready(function(){
      $('.cpx-select2').select2({ width:'resolve' });

      /* bootstrap existing product rows */
      $.each(existingProducts, function(_, row){ addProductCard(row); });
      refreshBusinessModel();

      $(document).on('click', '.addProductBtn', function(){
         addProductCard(null);
         var $last = $('#ProductCards .prod-card').last();
         if($last.length) $('html,body').animate({ scrollTop: $last.offset().top - 90 }, 400);
      });

      $(document).on('click', '.removeProduct', function(){
         if(confirm('Remove this product?')){
            $(this).closest('.prod-card').remove();
            renumberCards();
         }
      });

      /* segmented radios */
      $(document).on('change', '#custEdit .cpx-seg input[type=radio]', function(){
         $(this).closest('.cpx-seg').find('label').removeClass('on');
         $(this).closest('label').addClass('on');
      });

      /* special yes/no */
      $(document).on('change', 'input[name$="[has_special]"]', function(){
         var $card = $(this).closest('.prod-card');
         var yes = $(this).val() === 'yes' && $(this).is(':checked');
         $card.find('.spec-body').toggleClass('cpx-hide', !yes);
         if(yes){
            /* pre-fill expense basis/value from the main block if empty */
            if($card.find('.f-ssev').val() === '') $card.find('.f-ssev').val($card.find('.f-sev').val());
            $card.find('.f-sseb').val($card.find('.f-seb').val());
         }
         recalcCard($card);
      });

      /* any pricing input inside a card */
      $(document).on('change keyup', '.prod-card select, .prod-card input', function(){
         recalcCard($(this).closest('.prod-card'));
      });

      /* global drivers */
      $('#BusinessModelSel').on('change', refreshBusinessModel);
      $('#PaymentTermSel').on('change', recalcAll);
      $('#FreightInput').on('keyup change', recalcAll);
      $('#FreightBasisSel').on('change', function(){
         $('#FreightField').toggle($(this).val() === 'Paid by Company');
         recalcAll();
      });

      /* product type change → reload product options (keeps selections when possible) */
      $('#ProductTypeSel').on('change', function(){
         var type = $(this).val();
         $('.loadingDiv').show();
         $.ajax({
            url: '{{ route('customer.fetch.products.by.type') }}',
            type: 'GET',
            data: { type: type },
            success: function(products){
               var html = '<option value="" data-dp="0" data-packing="50 kg">— Select Product —</option>';
               $.each(products, function(_, p){
                  html += '<option value="'+p.id+'" data-dp="'+(p.dp || 0)+'" data-packing="'+(p.packing || '50 kg')+'">'+p.product_name+'</option>';
               });
               $('#masterProductOptions').html(html);
               $('.prodSelect').each(function(){
                  var cur = $(this).val();
                  $(this).html(html).val(cur);
                  if($(this).val() === null) $(this).val('');
                  $(this).trigger('change.select2');   /* refresh select2's rendered text */
               });
               recalcAll();
               $('.loadingDiv').hide();
            },
            error: function(){ $('.loadingDiv').hide(); }
         });
      });

      /* add-on user rows */
      $('#addAssignRow').on('click', function(){
         var desig = '';
         @foreach(getDesignations() as $dkey => $designation)
         desig += '<option value="{{ $dkey }}">{{ $designation }}</option>';
         @endforeach
         var row = '' +
         '<div class="emp-row">' +
           '<div class="cpx-f"><label>Designation</label><select class="cpx-in" name="designations[]" required><option value="">— Select —</option>'+desig+'</select></div>' +
           '<div class="cpx-f"><label>Name</label><input class="cpx-in" name="names[]" required></div>' +
           '<div class="cpx-f"><label>Mobile</label><input class="cpx-in data" type="number" name="mobiles[]" required></div>' +
           '<div class="cpx-f"><label>Email</label><input class="cpx-in" type="email" name="emails[]" required></div>' +
           '<div class="cpx-f"><label>&nbsp;</label><button type="button" class="cpx-btn red sm empRowRemove" style="height:40px"><i class="fa fa-times"></i> Remove</button></div>' +
         '</div>';
         $('#EmpRows').append(row);
         $('#empEmptyHint').hide();
      });
      $(document).on('click', '.empRowRemove', function(){
         $(this).closest('.emp-row').remove();
         if(!$('#EmpRows .emp-row').length) $('#empEmptyHint').show();
      });

      /* linked executive rows */
      $('#addExecRow').on('click', function(){
         var execOpts = '<option value="">— Select Employee —</option>';
         @foreach($users as $user)
         execOpts += '<option value="{{ $user['id'] }}">{{ $user['name'] }}</option>';
         @endforeach
         var row = '' +
         '<div class="exec-row">' +
           '<div class="cpx-f"><label>Date</label><input class="cpx-in data" type="date" name="user_dates[]" value="{{ date('Y-m-d') }}" required></div>' +
           '<div class="cpx-f"><label>Employee</label><select class="cpx-in" name="marketing_user_ids[]" required>'+execOpts+'</select></div>' +
           '<div class="cpx-f"><label>&nbsp;</label><button type="button" class="cpx-btn red sm execRowRemove" style="height:40px"><i class="fa fa-times"></i> Remove</button></div>' +
         '</div>';
         $('#ExecRows').append(row);
         $('#execEmptyHint').hide();
      });
      $(document).on('click', '.execRowRemove', function(){
         $(this).closest('.exec-row').remove();
         if(!$('#ExecRows .exec-row').length) $('#execEmptyHint').show();
      });

      /* form submit (AJAX, same endpoint) */
      $('#Customerform').submit(function(e){
         e.preventDefault();
         $('.cpx-err').hide().text('');
         $('.loadingDiv').show();
         var formdata = new FormData(this);
         $.ajax({
            url: '/admin/save-customer',
            type: 'POST',
            data: formdata,
            processData: false,
            contentType: false,
            success: function(data){
               $('.loadingDiv').hide();
               if(!data.status){
                  $.each(data.errors || {}, function(i, error){
                     var $el = $('#Customer-'+i);
                     $el.text($.isArray(error) ? error[0] : error).show();
                  });
                  var first = $('.cpx-err:visible').first();
                  if(first.length) $('html,body').animate({ scrollTop: first.offset().top - 160 }, 600);
                  else alert(data.message || 'Could not save.');
               }else{
                  window.location.href = data.url;
               }
            },
            error: function(){ $('.loadingDiv').hide(); alert('Server error. Please try again.'); }
         });
      });
   });
})(jQuery);
</script>
@endsection