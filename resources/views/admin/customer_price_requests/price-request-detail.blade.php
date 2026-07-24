@extends('layouts.adminLayout.backendLayout')
@section('content')
@php
   $isPending = $pr->status === 'Pending';
   $canApprove = $isPending && empty($check['mismatches']);
   $seLabel = selling_expense_label($customer->business_model); // 'ORC' (Hybrid) or 'Selling Expense'
@endphp
<style>
   #prView{--ink:#14241c;--ink2:#3a4a42;--muted:#7b8a82;--line:#e6ebe8;--line2:#eef2f0;--g:#1f7a44;--gd:#155e34;--gsoft:#e8f3ec;--gsoft2:#f1f8f3;--amber:#9a6a12;--amberS:#fdf4e3;--blue:#1f5e9a;--red:#c0392b;--redS:#fdecea;--r:11px;--rs:8px;--mono:'JetBrains Mono',Menlo,Consolas,monospace;font-family:'Inter','Segoe UI',system-ui,sans-serif;color:var(--ink)}
   #prView .cpx-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:16px}
   #prView .cpx-head h1{font-size:21px;font-weight:800;margin:0 0 6px;letter-spacing:-.3px}
   #prView .cpx-meta{display:flex;gap:13px;flex-wrap:wrap;color:var(--muted);font-size:12.5px;align-items:center}
   #prView .chip{display:inline-flex;padding:4px 11px;border-radius:30px;font-size:11px;font-weight:700;letter-spacing:.3px;text-transform:uppercase}
   #prView .chip.P{background:var(--amberS);color:var(--amber)}
   #prView .chip.A{background:var(--gsoft);color:var(--gd)}
   #prView .chip.R{background:var(--redS);color:var(--red)}
   #prView .cpx-card{background:#fff;border:1px solid var(--line);border-radius:var(--r);box-shadow:0 1px 2px rgba(20,36,28,.04),0 6px 18px rgba(20,36,28,.04);margin-bottom:18px;overflow:hidden}
   #prView .cpx-ch{padding:15px 22px;border-bottom:1px solid var(--line2);font-size:14px;font-weight:700}
   #prView .cpx-cb{padding:20px 22px}
   #prView .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px 18px}
   #prView .f label{display:block;font-size:10.5px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;color:var(--muted);margin:0 0 3px}
   #prView .f .v{font-size:13.5px;font-weight:600}
   #prView .f .v.mono{font-family:var(--mono)}
   #prView .ein{width:100%;height:38px;border:1.4px solid var(--line);border-radius:var(--rs);padding:0 11px;font-size:13.5px;background:#fff;color:var(--ink)}
   #prView .ein.mono{font-family:var(--mono)}
   #prView .ein:focus{outline:none;border-color:var(--g);box-shadow:0 0 0 3px var(--gsoft)}
   #prView table.cmp{width:100%;border-collapse:collapse;font-size:13px}
   #prView table.cmp th{font-size:11px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;color:var(--muted);text-align:left;padding:9px 12px;border-bottom:1px solid var(--line2);background:#fbfcfb}
   #prView table.cmp td{padding:10px 12px;border-bottom:1px solid var(--line2);font-family:var(--mono);font-size:12.5px}
   #prView .st{font-family:'Inter',sans-serif;font-weight:700;font-size:11.5px;padding:3px 10px;border-radius:20px;display:inline-flex}
   #prView .st.ok{background:var(--gsoft);color:var(--gd)}
   #prView .st.bad{background:var(--redS);color:var(--red)}
   #prView .st.fill{background:#e9f1fa;color:var(--blue)}
   #prView .banner{padding:13px 16px;border-radius:var(--rs);font-size:13px;margin-bottom:16px;display:flex;gap:10px;align-items:flex-start}
   #prView .banner.bad{background:var(--redS);color:var(--red);border:1px solid #f2c4bd}
   #prView .banner.ok{background:var(--gsoft2);color:var(--gd);border:1px solid #d8ece0}
   #prView .v-row{display:flex;justify-content:space-between;gap:10px;padding:7px 0;border-bottom:1px dashed var(--line2);font-size:12.5px}
   #prView .v-row .v{font-family:var(--mono);font-weight:600}
   #prView .v-row.hl{background:#fffbe6;margin:0 -8px;padding:8px;border-radius:7px;border-bottom:none}
   #prView .v-row.real{margin:2px -8px 0;padding:8px;border-radius:7px;border-bottom:none}
   #prView .v-row.real.ok{background:var(--gsoft)}#prView .v-row.real.ok .v{color:var(--gd)}
   #prView .v-row.real.bad{background:var(--redS)}#prView .v-row.real.bad .v{color:var(--red)}
   #prView .cpx-btn{display:inline-flex;align-items:center;gap:8px;border:none;border-radius:9px;font-size:13.5px;font-weight:600;padding:10px 18px;cursor:pointer;text-decoration:none;line-height:1.2}
   #prView .cpx-btn.g{background:var(--g);color:#fff}#prView .cpx-btn.g:hover{background:var(--gd);color:#fff}
   #prView .cpx-btn.red{background:var(--red);color:#fff}
   #prView .cpx-btn.ghost{background:#fff;border:1.4px solid var(--line);color:var(--ink2)}
   #prView .cpx-btn[disabled]{opacity:.5;cursor:not-allowed}
   #prView textarea{width:100%;border:1.4px solid var(--line);border-radius:var(--rs);padding:10px 12px;font-size:13.5px;min-height:70px}
   #prView .actions{display:flex;gap:10px;flex-wrap:wrap;align-items:center;justify-content:flex-end;padding:16px 22px;border-top:1px solid var(--line2);background:#fbfcfb}
   /* ── Request Details rows (like Viability Check) ── */
   #prView .d-row{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:8px 0;border-bottom:1px dashed var(--line2);font-size:13px}
   #prView .d-row:last-child{border-bottom:none}
   #prView .d-row .dk{color:var(--ink2);font-weight:500;white-space:nowrap;flex:0 0 200px}
   #prView .d-row .dk small{display:block;color:var(--muted);font-weight:400;font-size:10.5px;margin-top:1px;font-family:'Inter','Segoe UI',system-ui,sans-serif}
   #prView .d-row .dv{font-family:var(--mono);font-weight:600;font-size:13px;text-align:right;flex:1}
   #prView .d-row .dv.plain{font-family:'Inter','Segoe UI',system-ui,sans-serif}
   #prView .d-row .dv select.ein,#prView .d-row .dv input.ein{width:100%;max-width:260px;text-align:left}
   @media(max-width:820px){#prView .d-row .dk{flex:0 0 140px}}
   @media(max-width:600px){#prView .grid{grid-template-columns:1fr};#prView .d-row{flex-direction:column;align-items:flex-start;gap:4px};#prView .d-row .dv{text-align:left;width:100%};#prView .d-row .dv select.ein,#prView .d-row .dv input.ein{max-width:100%}}
</style>
<div class="page-content-wrapper">
   <div class="page-content">
      <ul class="page-breadcrumb breadcrumb" style="margin-bottom:14px;">
         <li><a href="{{ url('admin/dashboard') }}">Dashboard</a><i class="fa fa-circle"></i></li>
         <li><a href="{{ url('admin/price-requests') }}">Price Requests</a><i class="fa fa-circle"></i></li>
         <li><span>PR-{{ $pr->id }}</span></li>
      </ul>
      @if(Session::has('flash_message_error'))
      <div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{!! session('flash_message_error') !!}</div>
      @endif
      <div id="prView">
         {{-- Header --}}
         <div class="cpx-head">
            <div>
               <h1>Price Request <span style="font-family:var(--mono)">#PR-{{ $pr->id }}</span></h1>
               <div class="cpx-meta">
                  <span><i class="fa fa-user"></i> {{ $pr->user->name ?? '—' }}</span>
                  <span><i class="fa fa-clock-o"></i> {{ $pr->created_at->format('d M Y, h:i A') }}</span>
                  <span class="chip {{ substr($pr->status,0,1) }}">{{ $pr->status }}</span>
               </div>
            </div>
            <a class="cpx-btn ghost" href="{{ url('admin/price-requests') }}"><i class="fa fa-arrow-left"></i> Back to list</a>
         </div>

         @if($pr->status=='Rejected')
         <div class="banner bad"><i class="fa fa-times-circle" style="margin-top:2px"></i><div><b>Rejected</b> by {{ $pr->action_user->name ?? '—' }} on {{ $pr->action_at ? \Carbon\Carbon::parse($pr->action_at)->format('d M Y, h:i A') : '—' }}<br>Reason: {{ $pr->reject_reason }}</div></div>
         @elseif($pr->status=='Approved')
         <div class="banner ok"><i class="fa fa-check-circle" style="margin-top:2px"></i><div><b>Approved</b> by {{ $pr->action_user->name ?? '—' }} on {{ $pr->action_at ? \Carbon\Carbon::parse($pr->action_at)->format('d M Y, h:i A') : '—' }} — saved to customer products.</div></div>
         @endif

         {{-- Single form wraps everything so edited fields post with Approve --}}
         <form id="prApproveForm" method="post" action="{{ url('admin/price-requests/'.$pr->id.'/approve') }}">
            {{ csrf_field() }}
            <input type="hidden" name="_dp" id="pr_dp" value="{{ $viability['standard_dp'] }}">
            <input type="hidden" name="_std_packing" id="pr_std_packing" value="{{ getProductStandardPacking($pr->product_id) }}">

         <div style="display:grid;grid-template-columns:1.4fr 1fr;gap:18px;align-items:start">
            <div>
               {{-- Request detail (editable except Customer & Product) --}}
               <div class="cpx-card">
                  <div class="cpx-ch">Request Details @if($isPending)<span style="font-weight:400;color:var(--muted);font-size:12px">— editable before approval (except customer &amp; product)</span>@endif</div>
                  <div class="cpx-cb">
                     @if($isPending)
                     @php
                        $ptLocked = trim((string)$customer->payment_term) !== '';
                        $fbLocked = trim((string)$customer->freight_basis) !== '';
                        $frLocked = !($customer->freight === null || $customer->freight === '');
                     @endphp
                     <div class="d-row"><div class="dk">Customer <small>locked</small></div><div class="dv plain">{{ ucwords($customer->name) }}</div></div>
                     <div class="d-row"><div class="dk">Business Model</div><div class="dv plain">{{ $customer->business_model }}</div></div>
                     <div class="d-row"><div class="dk">Product <small>locked</small></div><div class="dv plain">{{ $pr->product->product_name ?? '—' }}</div></div>
                     <div class="d-row">
                        <div class="dk">Payment Term @if($ptLocked)<small>from master</small>@endif</div>
                        <div class="dv">
                           @if($ptLocked)
                              {{ $pr->payment_term }}
                              <input type="hidden" name="payment_term" id="e_payment_term" value="{{ $pr->payment_term }}" data-premium="{{ direct_sales_premium($pr->payment_term) }}">
                           @else
                              <select class="ein" name="payment_term" id="e_payment_term">
                                 @foreach(payment_terms() as $pt)
                                 <option value="{{ $pt }}" data-premium="{{ direct_sales_premium($pt) }}" {{ $pr->payment_term==$pt ? 'selected':'' }}>{{ $pt }}</option>
                                 @endforeach
                              </select>
                           @endif
                        </div>
                     </div>
                     <div class="d-row">
                        <div class="dk">Freight Basis @if($fbLocked)<small>from master</small>@endif</div>
                        <div class="dv">
                           @if($fbLocked)
                              {{ $pr->freight_basis }}
                              <input type="hidden" name="freight_basis" id="e_freight_basis" value="{{ $pr->freight_basis }}">
                           @else
                              <select class="ein" name="freight_basis" id="e_freight_basis">
                                 <option value="Paid by Company" {{ $pr->freight_basis=='Paid by Company' ? 'selected':'' }}>Paid by Company</option>
                                 <option value="Paid by Customer" {{ $pr->freight_basis=='Paid by Customer' ? 'selected':'' }}>Paid by Customer</option>
                              </select>
                           @endif
                        </div>
                     </div>
                     <div class="d-row" id="freight_row">
                        <div class="dk">Freight (Rs./kg) @if($frLocked)<small>from master</small>@endif</div>
                        <div class="dv">
                           @if($frLocked)
                              ₹ {{ number_format($pr->freight,2) }}
                              <input type="hidden" name="freight" id="e_freight" value="{{ $pr->freight }}">
                           @else
                              <input class="ein mono" type="number" step="0.01" min="0" name="freight" id="e_freight" value="{{ $pr->freight }}">
                           @endif
                        </div>
                     </div>
                     <div class="d-row">
                        <div class="dk">Packing Size</div>
                        <div class="dv">
                           <select class="ein" name="packing_size" id="e_packing_size">
                              @foreach(packing_sizes() as $pk => $pklbl)
                              <option value="{{ $pk }}" {{ $pr->packing_size==$pk ? 'selected':'' }}>{{ $pklbl }}</option>
                              @endforeach
                           </select>
                        </div>
                     </div>
                     <div class="d-row">
                        <div class="dk">Final Customer Price (Rs./kg)</div>
                        <div class="dv"><input class="ein mono" type="number" step="0.01" min="0" name="final_customer_price" id="e_final_customer_price" value="{{ $pr->final_customer_price }}"></div>
                     </div>
                     <div class="d-row">
                        <div class="dk">{{ $seLabel }} Basis</div>
                        <div class="dv">
                           <select class="ein" name="selling_expense_basis" id="e_selling_expense_basis">
                              <option value="%" {{ $pr->selling_expense_basis=='%' ? 'selected':'' }}>%</option>
                              <option value="Rs/kg" {{ $pr->selling_expense_basis=='Rs/kg' ? 'selected':'' }}>Rs/kg</option>
                           </select>
                        </div>
                     </div>
                     <div class="d-row">
                        <div class="dk">{{ $seLabel }} Value</div>
                        <div class="dv"><input class="ein mono" type="number" step="0.001" min="0" name="selling_expense_value" id="e_selling_expense_value" value="{{ rtrim(rtrim(number_format($pr->selling_expense_value,3,'.',''),'0'),'.') }}"></div>
                     </div>
                     {{-- final_msp / selling_expenses / additional_realization are recomputed & posted as hidden --}}
                     <input type="hidden" name="final_msp" id="e_final_msp" value="{{ $pr->final_msp }}">
                     <input type="hidden" name="selling_expenses" id="e_selling_expenses" value="{{ $pr->selling_expenses }}">
                     <input type="hidden" name="additional_realization" id="e_additional_realization" value="{{ $pr->additional_realization }}">
                     @else
                     <div class="d-row"><div class="dk">Customer</div><div class="dv plain">{{ ucwords($customer->name) }}</div></div>
                     <div class="d-row"><div class="dk">Business Model</div><div class="dv plain">{{ $customer->business_model }}</div></div>
                     <div class="d-row"><div class="dk">Product</div><div class="dv plain">{{ $pr->product->product_name ?? '—' }}</div></div>
                     <div class="d-row"><div class="dk">Payment Term</div><div class="dv">{{ $pr->payment_term }}</div></div>
                     <div class="d-row"><div class="dk">Freight Basis</div><div class="dv">{{ $pr->freight_basis }}</div></div>
                     <div class="d-row"><div class="dk">Freight (Rs./kg)</div><div class="dv">₹ {{ number_format($pr->freight,2) }}</div></div>
                     <div class="d-row"><div class="dk">Packing Size</div><div class="dv">{{ $pr->packing_size }}</div></div>
                     <div class="d-row"><div class="dk">Final Customer Price (Rs./kg)</div><div class="dv">₹ {{ number_format($pr->final_customer_price,2) }}</div></div>
                     <div class="d-row"><div class="dk">Final MSP (Rs./kg)</div><div class="dv">₹ {{ number_format($pr->final_msp,2) }}</div></div>
                     <div class="d-row"><div class="dk">{{ selling_expense_label($customer->business_model) }} ({{ $pr->selling_expense_basis }})</div><div class="dv">{{ rtrim(rtrim(number_format($pr->selling_expense_value,3,'.',''), '0'),'.') }}</div></div>
                     <div class="d-row"><div class="dk">Selling Expenses (Rs./kg)</div><div class="dv">{{ rtrim(rtrim(number_format($pr->selling_expenses,3,'.',''), '0'),'.') }}</div></div>
                     <div class="d-row"><div class="dk">Additional Realization (Rs./kg)</div><div class="dv" style="color:{{ $pr->additional_realization < 0 ? 'var(--red)':'var(--gd)' }}">{{ number_format($pr->additional_realization,2) }}</div></div>
                     @endif
                     @if($existing)
                     <div class="banner" style="background:var(--amberS);color:var(--amber);border:1px solid #f2e3c8;margin:16px 0 0"><i class="fa fa-exclamation-triangle" style="margin-top:2px"></i><div>An existing price for this product is on file (₹ {{ number_format($existing->net_price,2) }}, {{ $existing->packing_type }}). Approving will <b>replace</b> it.</div></div>
                     @endif
                  </div>
               </div>

               {{-- Customer master check --}}
               <div class="cpx-card">
                  <div class="cpx-ch">Customer Master Check <span style="font-weight:400;color:var(--muted);font-size:12px">— payment term, freight basis &amp; freight must agree</span></div>
                  <div class="cpx-cb" style="padding:0">
                     <table class="cmp">
                        <thead><tr><th>Field</th><th>Customer Master</th><th>Request</th><th>Result</th></tr></thead>
                        <tbody>
                           @php
                              $rows = [
                                 ['Payment Term',  trim((string)$customer->payment_term),  $pr->payment_term, 'e_payment_term'],
                                 ['Freight Basis', trim((string)$customer->freight_basis), $pr->freight_basis, 'e_freight_basis'],
                                 ['Freight',       ($customer->freight===null || $customer->freight==='') ? '' : (float)$customer->freight, (float)$pr->freight, 'e_freight'],
                              ];
                           @endphp
                           @foreach($rows as $row)
                           @php
                              $masterEmpty = ($row[1] === '' || $row[1] === null);
                              $match = !$masterEmpty && (is_numeric($row[1]) ? abs($row[1]-$row[2])<0.001 : $row[1]===$row[2]);
                           @endphp
                           <tr data-cmp="{{ $row[3] }}" data-master="{{ $masterEmpty ? '' : $row[1] }}" data-empty="{{ $masterEmpty ? 1 : 0 }}">
                              <td style="font-family:'Inter',sans-serif;font-weight:600">{{ $row[0] }}</td>
                              <td>{{ $masterEmpty ? '— empty —' : $row[1] }}</td>
                              <td class="cmp-req">{{ $row[2] }}</td>
                              <td class="cmp-res">
                                 @if($masterEmpty)<span class="st fill">Will update master</span>
                                 @elseif($match)<span class="st ok">Match</span>
                                 @else<span class="st bad">Mismatch</span>@endif
                              </td>
                           </tr>
                           @endforeach
                        </tbody>
                     </table>
                     <div id="cmpBannerWrap">
                     @if(!empty($check['mismatches']) && $isPending)
                     <div class="banner bad" style="margin:14px 16px"><i class="fa fa-ban" style="margin-top:2px"></i><div><b>Approval blocked.</b> Fix the customer master (or reject this request):<br>{{ implode(' | ', $check['mismatches']) }}<br><a href="{{ url('admin/add-edit-customer/'.$customer->id) }}" style="color:var(--red);text-decoration:underline">Open customer master →</a></div></div>
                     @elseif($isPending)
                     <div class="banner ok" style="margin:14px 16px"><i class="fa fa-check" style="margin-top:2px"></i><div>All checks pass. @if(!empty($check['fill'])) Empty master fields ({{ implode(', ', array_keys($check['fill'])) }}) will be filled from this request on approval.@endif</div></div>
                     @endif
                     </div>
                  </div>
               </div>
            </div>

            {{-- Viability (recomputed) --}}
            <div class="cpx-card">
               <div class="cpx-ch">Viability Check <span style="font-weight:400;color:var(--muted);font-size:12px">· {{ $isPending ? 'live' : 'recomputed now' }}</span></div>
               <div class="cpx-cb">
                  <div class="v-row"><span>Standard DP <small style="display:block;color:var(--muted)">latest effective price today</small></span><span class="v" id="v_dp">{{ number_format($viability['standard_dp'],2) }}</span></div>
                  <div class="v-row"><span>Direct Sales Premium</span><span class="v" id="v_premium">{{ $viability['premium_percent'] }}%</span></div>
                  <div class="v-row"><span>Base Price</span><span class="v" id="v_base">{{ number_format($viability['base_price'],2) }}</span></div>
                  <div class="v-row"><span>Additional Packing Cost</span><span class="v" id="v_pack">{{ number_format($viability['packing_cost'],2) }}</span></div>
                  <div class="v-row"><span>Freight (Rs./kg)</span><span class="v" id="v_freight">{{ number_format($viability['freight'],2) }}</span></div>
                  <div class="v-row"><span>{{ selling_expense_label($customer->business_model) }} (Rs./kg)</span><span class="v" id="v_exp">{{ number_format($viability['selling_expenses'],3) }}</span></div>
                  <div class="v-row hl"><span><b>Minimum Selling Price</b></span><span class="v" id="v_msp">{{ number_format($viability['minimum_selling_price'],2) }}</span></div>
                  <div class="v-row real {{ $viability['viable'] ? 'ok':'bad' }}" id="v_real_row"><span><b>Additional Realization</b></span><span class="v" id="v_real">{{ number_format($viability['additional_realization'],2) }}</span></div>
               </div>
            </div>
         </div>

         {{-- Actions --}}
         @if($isPending)
         <div class="cpx-card">
            <div class="actions" style="border-top:none;justify-content:space-between">
               <div style="display:flex;gap:10px;flex:1;min-width:280px;align-items:flex-start">
                  <textarea form="prRejectForm" name="reject_reason" placeholder="Reason for rejection (required to reject)…" style="flex:1"></textarea>
                  <button type="submit" form="prRejectForm" class="cpx-btn red" onclick="return confirm('Reject this price request?')"><i class="fa fa-times"></i> Reject</button>
               </div>
               <button type="submit" class="cpx-btn g" id="approveBtn" {{ $canApprove ? '' : 'disabled title="Blocked by customer master mismatch"' }} onclick="return confirm('Approve and save this price to the customer\'s products?')"><i class="fa fa-check"></i> Approve &amp; Save Price</button>
            </div>
         </div>
         @endif
         </form>
         {{-- separate reject form (reason is linked via form="prRejectForm") --}}
         <form id="prRejectForm" method="post" action="{{ url('admin/price-requests/'.$pr->id.'/reject') }}">{{ csrf_field() }}</form>
      </div>
   </div>
</div>
@if($isPending)
<script type="text/javascript">
(function($){
   var PACKING_COST = { 'Standard': 0, '5kg*2': 25, '1kg*10': 35 };
   var PREMIUM = { 'Advance':1, '1-7 days':2, '15 days':2, '30 days':3, '45 days':4, '60 days':5 };
   var DP = parseFloat($('#pr_dp').val()) || 0;

   function num(v){ v = parseFloat(v); return isNaN(v) ? 0 : v; }
   function fmt(v){ return num(v).toFixed(2); }

   function recalc(){
      var pt     = $('#e_payment_term').val();
      var fbasis = $('#e_freight_basis').val();
      var freight= (fbasis === 'Paid by Company') ? num($('#e_freight').val()) : 0;
      var pack   = $('#e_packing_size').val();
      var csp    = num($('#e_final_customer_price').val());
      var seb    = $('#e_selling_expense_basis').val();
      var sev    = num($('#e_selling_expense_value').val());

      var premium  = PREMIUM[pt] || 0;
      var base     = DP * (1 + premium/100);
      var packCost = PACKING_COST[pack] || 0;
      var expenses = (seb === '%') ? csp * (sev/100) : sev;
      var msp      = base + packCost + freight + expenses;
      var real     = csp - msp;

      $('#v_dp').text(fmt(DP));
      $('#v_premium').text(premium + '%');
      $('#v_base').text(fmt(base));
      $('#v_pack').text(fmt(packCost));
      $('#v_freight').text(fmt(freight));
      $('#v_exp').text(num(expenses).toFixed(3));
      $('#v_msp').text(fmt(msp));
      $('#v_real').text(fmt(real));
      $('#v_real_row').removeClass('ok bad').addClass(real >= 0 ? 'ok' : 'bad');

      /* post-back hidden fields */
      $('#e_final_msp').val(msp.toFixed(2));
      $('#e_selling_expenses').val(num(expenses).toFixed(3));
      $('#e_additional_realization').val(real.toFixed(2));

      /* freight row visibility — only when freight is editable (not master-locked) */
      var $fr = $('#e_freight');
      if($fr.hasClass('ein')){
         $('#freight_row').toggle(fbasis === 'Paid by Company');
      }

      updateMasterCheck(pt, fbasis, freight);
   }

   /* Re-run the customer-master comparison client-side so admin sees it live */
   function updateMasterCheck(pt, fbasis, freight){
      var reqMap = { 'e_payment_term': pt, 'e_freight_basis': fbasis, 'e_freight': String(freight) };
      var anyMismatch = false;

      $('table.cmp tbody tr').each(function(){
         var $tr = $(this);
         var key = $tr.data('cmp');
         var master = String($tr.data('master'));
         var empty = String($tr.data('empty')) === '1';
         var reqVal = reqMap[key];

         $tr.find('.cmp-req').text(key === 'e_freight' ? num(reqVal).toString() : reqVal);

         var html, match;
         if(empty){
            html = '<span class="st fill">Will update master</span>';
         } else {
            if(key === 'e_freight'){ match = Math.abs(num(master) - num(reqVal)) < 0.001; }
            else { match = (master === reqVal); }
            html = match ? '<span class="st ok">Match</span>' : '<span class="st bad">Mismatch</span>';
            if(!match) anyMismatch = true;
         }
         $tr.find('.cmp-res').html(html);
      });

      /* toggle approve button + banner */
      $('#approveBtn').prop('disabled', anyMismatch);
      if(anyMismatch){
         $('#cmpBannerWrap').html('<div class="banner bad" style="margin:14px 16px"><i class="fa fa-ban" style="margin-top:2px"></i><div><b>Approval blocked.</b> Payment Term / Freight Basis / Freight don\'t match the customer master. Edit the fields to match, fix the customer master, or reject this request.</div></div>');
      } else {
         $('#cmpBannerWrap').html('<div class="banner ok" style="margin:14px 16px"><i class="fa fa-check" style="margin-top:2px"></i><div>All checks pass. Empty master fields will be filled from this request on approval.</div></div>');
      }
   }

   $(function(){
      $('#prApproveForm').on('change keyup', '.ein', recalc);
      recalc();
   });
})(jQuery);
</script>
@endif
@endsection