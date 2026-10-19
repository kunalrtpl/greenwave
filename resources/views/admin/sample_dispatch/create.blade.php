@extends('layouts.adminLayout.backendLayout')
@section('content')
{{-- ════════════════════════════════════════════════════════════════════
SAMPLE DISPATCH — one page, two modes.

  Against Request  tick any number of approved-and-ready requests and fill the
                   qty / batch for each. Requests stores has not marked ready
                   are greyed out rather than hidden, so the desk can see what
                   it is still waiting on.
  Voluntarily      the admin sends material with nothing requested from the
                   field; a sampling request flagged is_voluntarily is created
                   for each product so history and stock still line up.

Where it goes differs by mode: against a request each one carries its own
"Dispatch To" (shown after its batch no), while a voluntary dispatch picks one
destination for all its products. Either way the destination is written onto
every row, so each saved row reads on its own later.
═══════════════════════════════════════════════════════════════════════ --}}
@include('admin.sampling.partials.styles')
<div class="page-content-wrapper">
   <div class="page-content">
      <ul class="page-breadcrumb breadcrumb" style="margin-bottom:14px;">
         <li><a href="{{ url('admin/dashboard') }}">Dashboard</a><i class="fa fa-circle"></i></li>
         <li><a href="{{ route('admin.sample-dispatch.index') }}">Sample Dispatches</a><i class="fa fa-circle"></i></li>
         <li><span>{{ $title }}</span></li>
      </ul>

      <div class="sdx" id="sdPage">
         <div class="sdx-head">
            <div>
               <h1>Sample Dispatch</h1>
               <div class="sdx-meta">Dispatch against approved requests, or send a voluntary sample with no request behind it — then enter the common destination, challan &amp; transport.</div>
            </div>
            <a class="sdx-btn ghost sm" href="{{ route('admin.sample-dispatch.index') }}"><i class="fa fa-arrow-left"></i> Back to list</a>
         </div>

         <div class="sdx-alert" id="sdAlert"></div>

         <form id="sdForm" method="post" action="javascript:;" autocomplete="off">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            {{-- ── 1. What is going out ────────────────────────── --}}
            <div class="sdx-card">
               <div class="sdx-ch">
                  <div class="t"><span class="ix">1</span><div>Dispatch Type<small>Against an approved request, or a voluntary sample the admin is sending on their own</small></div></div>
               </div>
               <div class="sdx-cb">
                  <div class="sdx-grid g3">
                     <div class="sdx-f">
                        <label>Accountable Person <span class="rq">*</span></label>
                        <div class="sdx-seg" id="sdAnswerableSeg">
                           <label class="on"><input type="radio" name="answerable_type" value="user" checked><span>Employee</span></label>
                           <label><input type="radio" name="answerable_type" value="dealer"><span>Dealer</span></label>
                        </div>
                        <div class="sdx-err" data-err="answerable_type"></div>
                     </div>
                     <div class="sdx-f" id="sdTypeWrap">
                        <label>Dispatch Type <span class="rq">*</span></label>
                        <div class="sdx-seg">
                           <label class="on"><input type="radio" name="dispatch_type" value="request" checked><span>Against Request</span></label>
                           <label><input type="radio" name="dispatch_type" value="voluntary"><span>Voluntarily</span></label>
                        </div>
                        <div class="sdx-err" data-err="dispatch_type"></div>
                     </div>
                     <div class="sdx-f sdx-hide" id="sdUserWrap">
                        <label>Answerable Employee <span class="rq">*</span></label>
                        <select class="sdx-in sdx-s2" name="user_id" id="sdVolUser">
                           <option value="">— Select employee —</option>
                           @foreach($employees as $employee)
                           <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                           @endforeach
                        </select>
                        <div class="sdx-hint">A sampling request is created against this employee and flagged as voluntary.</div>
                        <div class="sdx-err" data-err="user_id"></div>
                     </div>
                     <div class="sdx-f sdx-hide" id="sdDealerWrap">
                        <label>Answerable Dealer <span class="rq">*</span></label>
                        <select class="sdx-in sdx-s2" name="dealer_id" id="sdDealer">
                           <option value="">— Select dealer —</option>
                           @foreach($dealers as $dealer)
                           <option value="{{ $dealer->id }}"
                                   data-address="{{ trim(($dealer->business_name ?: $dealer->name) . "\n" . ($dealer->shipping_address ?: $dealer->address) . ($dealer->owner_mobile ? "\nM. " . $dealer->owner_mobile : '')) }}">
                              {{ $dealer->business_name ?: $dealer->name }}@if($dealer->city) — {{ $dealer->city }}@endif
                           </option>
                           @endforeach
                        </select>
                        <div class="sdx-hint">Head-office dealers only. A dealer's samples are always recorded as voluntary.</div>
                        <div class="sdx-err" data-err="dealer_id"></div>
                     </div>
                  </div>
               </div>
            </div>

            {{-- ── 2a. Voluntary products ──────────────────────── --}}
            <div class="sdx-card sdx-hide" id="sdVoluntaryCard">
               <div class="sdx-ch">
                  <div class="t"><span class="ix">2</span><div>Products<small>Qty is calculated from pack size × no of packs</small></div></div>
               </div>
               <div class="sdx-cb">
                  <div class="sdx-scroll">
                     <table class="sdx-table fixed" id="sdVolTable">
                        <thead>
                           {{-- Proportional widths so the columns stay balanced
                                at any screen size instead of one stretching. --}}
                           <tr>
                              <th style="width:30%">Product</th>
                              <th style="width:16%">Pack Size</th>
                              <th style="width:16%">No of Packs</th>
                              <th style="width:14%">Qty (Kg)</th>
                              <th style="width:19%">Batch No</th>
                              <th style="width:5%"></th>
                           </tr>
                        </thead>
                        <tbody></tbody>
                     </table>
                  </div>
                  <div class="sdx-err" data-err="voluntary_items" style="margin-top:8px"></div>
                  <div style="margin-top:14px">
                     <button type="button" class="sdx-btn g sm" id="sdVolAdd"><i class="fa fa-plus"></i> Add Product</button>
                  </div>
               </div>
            </div>

            {{-- ── 2b. Pick approved requests ──────────────────── --}}
            <div class="sdx-card" id="sdRequestCard">
               <div class="sdx-ch">
                  <div class="t"><span class="ix">2</span><div>Sample Requests<small>Approved requests with qty still pending — tick the ones going out on this challan</small></div></div>
                  <span class="sdx-chip ok" id="sdPickedChip">0 selected</span>
               </div>
               <div class="sdx-cb">
                  <div class="sdx-grid g3 sdx-filters" style="margin-bottom:16px">
                     <div class="sdx-f">
                        <label>Employee <span class="rq">*</span></label>
                        <select class="sdx-in sdx-s2" id="sdUser">
                           <option value="">— Select employee —</option>
                           @foreach($employees as $employee)
                           <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                           @endforeach
                        </select>
                        <div class="sdx-hint">Requests load once an employee is chosen.</div>
                     </div>
                     <div class="sdx-f">
                        <label>Search</label>
                        <input class="sdx-in" id="sdKeyword" placeholder="SR ref no / product / customer">
                     </div>
                     <div class="sdx-f act">
                        <label>&nbsp;</label>
                        <div class="btns">
                           <button type="button" class="sdx-btn g sm" id="sdReload"><i class="fa fa-refresh"></i> Reload</button>
                           <label class="check"><input type="checkbox" id="sdOnlyReady" checked> Only show ready</label>
                        </div>
                     </div>
                  </div>

                  <div id="sdRequests"></div>
                  <div class="sdx-err" data-err="items" style="margin-top:8px"></div>
               </div>
            </div>

            {{-- ── 3. Dispatch To — one destination for the save ─────
                 Common to everything above and written onto every row, so each
                 saved row still reads on its own later. Against a request the
                 rows also show, read-only, where the field user asked for it to
                 go — if those disagree the admin is warned and picks. --}}
            <div class="sdx-card" id="sdDispatchCard">
               <div class="sdx-ch">
                  <div class="t"><span class="ix">3</span><div>Dispatch To<small id="sdDispatchToSub">Common to every request above — saved against each one</small></div></div>
               </div>
               <div class="sdx-cb">
                  <div class="sdx-alert" id="sdPartyConflict" style="margin-bottom:16px"></div>
                  <div class="sdx-grid g3 sdx-dispatch-to">
                     <div class="sdx-f">
                        <label>Send To</label>
                        <div class="sdx-seg" id="sdPartySeg">
                           <label><input type="radio" name="party_type" value="dealer"><span>Dealer</span></label>
                           <label><input type="radio" name="party_type" value="customer"><span>Customer</span></label>
                           <label class="on" id="sdPartyEmpOpt"><input type="radio" name="party_type" value="user" checked><span>Employee</span></label>
                        </div>
                     </div>
                     <div class="sdx-f">
                        <label id="sdPartyLabel">Employee</label>
                        <select class="sdx-in sm" id="sdParty"></select>
                        {{-- When the dispatch belongs to a dealer and is going to
                             that dealer, there is nothing to choose. --}}
                        <input type="text" class="sdx-in sm sdx-hide" id="sdPartyFixed" readonly>
                        <div class="sdx-err" data-err="dispatch_party"></div>
                     </div>
                     <div class="sdx-f">
                        <label>Dispatch Address</label>
                        <textarea class="sdx-in sm" id="sdAddress" placeholder="Auto-filled on selection"></textarea>
                     </div>
                  </div>
               </div>
            </div>

            {{-- ── 4. Challan & transport ──────────────────────── --}}
            <div class="sdx-card">
               <div class="sdx-ch">
                  <div class="t"><span class="ix">4</span><div>Challan &amp; Transport<small>Common to the whole dispatch — the dispatch number is generated on save</small></div></div>
               </div>
               <div class="sdx-cb">
                  <div class="sdx-grid sdx-challan">
                     <div class="sdx-f">
                        <label>Dispatch Date <span class="rq">*</span></label>
                        <input type="date" class="sdx-in data" name="dispatch_date" value="{{ date('Y-m-d') }}">
                        <div class="sdx-err" data-err="dispatch_date"></div>
                     </div>
                     <div class="sdx-f">
                        <label>Challan No <span class="rq">*</span></label>
                        <input type="text" class="sdx-in data" name="invoice_no" placeholder="e.g. CH-1024">
                        <div class="sdx-err" data-err="invoice_no"></div>
                     </div>
                     <div class="sdx-f">
                        <label>Challan Date</label>
                        <input type="date" class="sdx-in data" name="invoice_date" value="{{ date('Y-m-d') }}">
                        <div class="sdx-err" data-err="invoice_date"></div>
                     </div>
                     {{-- Own line: Transport keeps its natural width and the LR no
                          sits straight after it, taking the rest of the row. --}}
                     <div class="sdx-f full sdx-transport-row">
                        <div class="sdx-f">
                           <label>Transport <span class="rq">*</span></label>
                           <div class="sdx-seg">
                              <label class="on"><input type="radio" name="transport" value="Courier" checked><span>Courier</span></label>
                              <label><input type="radio" name="transport" value="Transport"><span>Transport</span></label>
                           </div>
                           <div class="sdx-err" data-err="transport"></div>
                        </div>
                        <div class="sdx-f grow">
                           {{-- Label follows the transport mode; the field itself
                                stays `lr_no` so the stored column never changes. --}}
                           <label><span id="sdLrLabel">Docket No</span> <span class="rq">*</span></label>
                           <input type="text" class="sdx-in data" name="lr_no" id="sdLrNo" placeholder="Docket No">
                           <div class="sdx-err" data-err="lr_no"></div>
                        </div>
                     </div>
                     <div class="sdx-f full">
                        <label>Remarks</label>
                        <textarea class="sdx-in sm" name="remarks" placeholder="Optional"></textarea>
                        <div class="sdx-err" data-err="remarks"></div>
                     </div>
                  </div>
               </div>
            </div>

            <div class="sdx-actions">
               <div class="note"><i class="fa fa-info-circle"></i> Saving records the dispatch and closes any request whose full approved qty has now gone out.</div>
               <button type="button" class="sdx-btn g" id="sdSubmit"><i class="fa fa-truck"></i> Save &amp; Dispatch</button>
            </div>
         </form>
      </div>
   </div>
</div>

<script>
$(function () {
   var URLS = {
      store:    @json(route('admin.sample-dispatch.store')),
      requests: @json(route('admin.sample-dispatch.requests')),
      parties:  @json(route('admin.sampling-parties'))
   };

   var PRODUCTS   = @json($products);
   var PACK_SIZES = @json(App\SamplingRequest::PACK_SIZES);

   var rows = [];   // everything fetched
   var open = {};   // id => true for the ticked rows, so a reload keeps the selection
   var volSeq = 0;  // row counter for the voluntary table

   function mode(){ return answerableType() === 'dealer' ? 'voluntary' : $('input[name=dispatch_type]:checked').val(); }
   function answerableType(){ return $('input[name=answerable_type]:checked').val(); }
   function isDealerMode(){ return answerableType() === 'dealer'; }
   function selectedDealer(){
      var id = $('#sdDealer').val();
      if (!id) { return null; }
      var $o = $('#sdDealer option:selected');
      return { id: id, text: $o.text().trim(), address: $o.data('address') || '' };
   }
   function esc(v){ return $('<div>').text(v == null ? '' : v).html(); }
   function num(v){ return v == null ? '' : v; }
   function packSizesFor(form){ return PACK_SIZES[String(form || '').toLowerCase()] || PACK_SIZES['default']; }

   /* ── Segmented controls ─────────────────────────────── */
   $(document).on('change', '.sdx-seg input[type=radio]', function(){
      $(this).closest('.sdx-seg').find('label').removeClass('on');
      $(this).closest('label').addClass('on');
   });

   $('.sdx-s2').select2({ width: '100%' });

   /* ══════════════ Dispatch type ══════════════ */
   function applyMode(){
      var dealerMode = isDealerMode();
      var voluntary  = mode() === 'voluntary';

      // A dealer has no requests to dispatch against, so the choice is hidden
      // and the save is always recorded as voluntary.
      $('#sdTypeWrap').toggleClass('sdx-hide', dealerMode);
      $('#sdDealerWrap').toggleClass('sdx-hide', !dealerMode);
      $('#sdDealer').prop('disabled', !dealerMode);

      // Material belonging to a dealer goes to that dealer or to a customer —
      // never to an employee.
      $('#sdPartyEmpOpt').toggleClass('sdx-hide', dealerMode);
      if (dealerMode && partyType() === 'user') {
         $('#sdPartySeg input[value=dealer]').prop('checked', true);
         $('#sdPartySeg label').removeClass('on');
         $('#sdPartySeg input:checked').closest('label').addClass('on');
      }

      $('#sdVoluntaryCard').toggleClass('sdx-hide', !voluntary);
      $('#sdRequestCard').toggleClass('sdx-hide', voluntary);
      $('#sdDispatchToSub').text(voluntary
         ? 'Common to every product above — saved against each one'
         : 'Common to every request above — saved against each one');

      // Disabled inputs are not serialized, so the hidden mode contributes
      // nothing to the post even though its markup stays in the DOM.
      $('#sdVoluntaryCard').find('input, select, textarea').prop('disabled', !voluntary);

      $('#sdVolUser').prop('disabled', dealerMode || !voluntary);
      $('#sdUserWrap').toggleClass('sdx-hide', dealerMode || !voluntary);
      $('#sdRequestCard').find('input[name], select[name], textarea[name]').each(function(){
         var id = $(this).closest('tr.sd-detail').attr('data-detail');
         $(this).prop('disabled', voluntary || !id || !open[id]);
      });

      if (voluntary && !$('#sdVolTable tbody tr').length) { addVolRow(); }
      pushPartyToRows();
   }

   $('input[name=dispatch_type]').on('change', applyMode);

   /* ── A courier gives a docket no, a transporter an LR no ── */
   function applyTransportLabel(){
      var label = $('input[name=transport]:checked').val() === 'Transport' ? 'LR No' : 'Docket No';
      $('#sdLrLabel').text(label);
      $('#sdLrNo').attr('placeholder', label);
   }

   $('input[name=transport]').on('change', applyTransportLabel);
   $('input[name=answerable_type]').on('change', function(){ applyMode(); initParty(); });
   $('#sdDealer').on('change', function(){ if (isDealerMode()) { initParty(); } });

   /* ══════════════ Against request: load the pickable list ══════════════ */
   /** The employee selected for this dispatch, in whichever mode is active. */
   function selectedEmployee(){
      var $src = mode() === 'voluntary' ? $('#sdVolUser') : $('#sdUser');
      var id = $src.val();
      return id ? { id: id, text: $src.find('option:selected').text().trim() } : null;
   }

   function load(){
      // Nothing is listed until an employee is chosen — the desk always works
      // one employee at a time and a mixed list is not useful.
      if (!$('#sdUser').val()) {
         rows = [];
         open = {};
         $('#sdRequests').html('<div class="sdx-empty"><i class="fa fa-user-o"></i>Select an employee to load their approved requests.</div>');
         updateCount();
         return;
      }

      $('.loadingDiv').show();
      $.get(URLS.requests, { user_id: $('#sdUser').val(), keyword: $('#sdKeyword').val() }, function(res){
         $('.loadingDiv').hide();
         rows = res.requests;
         render();
      }).fail(function(){
         $('.loadingDiv').hide();
         $('#sdRequests').html('<div class="sdx-empty"><i class="fa fa-exclamation-triangle"></i>Could not load requests. Please try again.</div>');
      });
   }

   $('#sdReload').on('click', load);
   $('#sdUser').on('change', function(){ open = {}; load(); });
   $('#sdOnlyReady').on('change', render);
   $('#sdKeyword').on('keypress', function(e){ if (e.which === 13) { e.preventDefault(); load(); } });

   function visibleRows(){
      return $('#sdOnlyReady').is(':checked') ? rows.filter(function(r){ return r.is_ready; }) : rows;
   }

   function render(){
      var list = visibleRows();

      if (!list.length) {
         $('#sdRequests').html('<div class="sdx-empty"><i class="fa fa-inbox"></i>'
            + ($('#sdOnlyReady').is(':checked')
               ? 'No requests have been marked ready for dispatch. Untick "Only show ready" to see what is still waiting on stores.'
               : 'No approved requests are pending dispatch.')
            + '</div>');
         updateCount();
         return;
      }

      var html = '<div class="sdx-scroll"><table class="sdx-table fit"><thead><tr>'
         + '<th style="width:38px"></th><th>Ref No</th><th>Employee</th><th>Product</th>'
         + '<th class="num">Approved</th><th class="num">Sent</th><th class="num">Pending</th><th>Readiness</th>'
         + '</tr></thead><tbody>';

      $.each(list, function(_, r){
         var picked = !!open[r.id];
         html += '<tr class="' + (r.is_ready ? (picked ? 'picked' : '') : 'locked') + '" data-row="' + r.id + '">'
            + '<td>' + (r.is_ready
                  ? '<input type="checkbox" class="sd-pick" data-id="' + r.id + '"' + (picked ? ' checked' : '') + '>'
                  : '<i class="fa fa-lock" style="color:#b9c5bf"></i>') + '</td>'
            + '<td><div class="pn num">' + esc(r.reference_no) + '</div><div class="pc">' + esc(r.request_date) + '</div></td>'
            + '<td>' + esc(r.employee) + (r.customer ? '<div class="pc">' + esc(r.customer) + '</div>' : '') + '</td>'
            + '<td><div class="pn">' + esc(r.product_name) + '</div><div class="pc">' + esc(r.product_code) + '</div></td>'
            + '<td class="num">' + num(r.approved_qty) + ' Kg</td>'
            + '<td class="num">' + num(r.dispatched) + ' Kg</td>'
            + '<td class="num"><b>' + num(r.pending_qty) + ' Kg</b></td>'
            + '<td>' + (r.is_ready
                  ? '<span class="sdx-chip ok"><i class="fa fa-check"></i> Ready</span>'
                    + (r.ready_at ? '<div class="pc">' + esc(r.ready_at) + '</div>' : '')
                    + (r.ready_remarks ? '<div class="pc">' + esc(r.ready_remarks) + '</div>' : '')
                  : '<span class="sdx-chip mute">Not ready yet</span>') + '</td>'
            + '</tr>'
            + '<tr class="sd-detail" data-detail="' + r.id + '"' + (picked ? '' : ' style="display:none"') + '>'
            + '<td colspan="8" style="background:#fbfcfb">' + detailHtml(r) + '</td></tr>';
      });

      html += '</tbody></table></div>';
      $('#sdRequests').html(html);

      $.each(list, function(_, r){
         if (open[r.id]) { $('tr[data-detail="' + r.id + '"]').find('input, select, textarea').prop('disabled', false); }
      });
      reconcileRequestParties();
      pushPartyToRows();
      updateCount();
   }

   /**
    * The per-request panel carries only what differs between requests: qty and
    * batch. Where it goes is chosen once for the whole dispatch and copied in
    * through these hidden inputs, so each saved row still stands on its own.
    */
   function detailHtml(r){
      var n = 'items[' + r.id + ']';
      var e = 'items.' + r.id + '.';

      var askedFor = r.party
         ? esc(r.party.text) + ' <span class="pc">(' + esc(partyTypeLabel(r.party_type)) + ')</span>'
         : '<span class="pc">Not specified</span>';

      return '<input type="hidden" name="' + n + '[sampling_request_id]" value="' + r.id + '" disabled>'
         + '<div class="sdx-sub" style="margin-top:0">'
         + '  <div class="h">' + esc(r.reference_no) + '</div>'
         + '  <div class="sdx-grid sdx-row5">'

         + '    <div class="sdx-f"><label>Qty (Kg) <span class="rq">*</span></label>'
         + '      <input type="number" step="0.001" min="0.001" max="' + r.pending_qty + '" class="sdx-in sm data" name="' + n + '[dispatch_qty]" value="' + r.pending_qty + '" disabled>'
         + '      <div class="sdx-hint">Pending: ' + num(r.pending_qty) + ' Kg</div>'
         + '      <div class="sdx-err" data-err="' + e + 'dispatch_qty"></div></div>'

         + '    <div class="sdx-f"><label>Batch No</label>'
         + '      <input type="text" class="sdx-in sm data" name="' + n + '[batch_no]" placeholder="Optional" disabled>'
         + '      <div class="sdx-err" data-err="' + e + 'batch_no"></div></div>'

         // Read-only: what the field user asked for on this request, carried
         // through approval. Where it actually goes is chosen once below.
         + '    <div class="sdx-f"><label>Send To <span class="sdx-hint" style="text-transform:none;font-weight:400">(from the request)</span></label>'
         + '      <div class="sdx-readonly">' + askedFor
         + (r.address ? '<div class="pc" style="margin-top:4px;white-space:pre-line">' + esc(r.address) + '</div>' : '')
         + '      </div></div>'

         // Filled from the Dispatch To section below, so every row saves its
         // own copy of the one destination the admin chose.
         + '  </div>'
         + '  <input type="hidden" class="sd-party-type" name="' + n + '[dispatch_party_type]" disabled>'
         + '  <input type="hidden" class="sd-dealer" name="' + n + '[dispatch_dealer_id]" disabled>'
         + '  <input type="hidden" class="sd-customer" name="' + n + '[dispatch_customer_id]" disabled>'
         + '  <input type="hidden" class="sd-user" name="' + n + '[dispatch_user_id]" disabled>'
         + '  <input type="hidden" class="sd-address" name="' + n + '[dispatch_address]" disabled>'
         + '  <div class="sdx-err" data-err="' + e + 'dispatch_dealer_id"></div>'
         + '  <div class="sdx-err" data-err="' + e + 'dispatch_customer_id"></div>'
         + '</div>';
   }

   function partyTypeLabel(t){
      return t === 'dealer' ? 'Dealer' : (t === 'customer' ? 'Customer' : 'Employee');
   }

   /* ── Where the selected requests say they should go ──── */
   /** The ticked requests, as objects. */
   function pickedRequests(){
      return rows.filter(function(r){ return open[r.id]; });
   }

   /**
    * If every ticked request names the same destination, adopt it. If they
    * disagree, say so in red and leave the choice to the admin.
    */
   function reconcileRequestParties(){
      var picked = pickedRequests();
      var $warn  = $('#sdPartyConflict');

      if (mode() === 'voluntary' || !picked.length) {
         $warn.hide().empty();
         return;
      }

      var seen = {};
      picked.forEach(function(r){
         var id = r.party ? r.party.id : '';
         seen[r.party_type + ':' + id] = r;
      });
      var keys = Object.keys(seen);

      if (keys.length === 1) {
         $warn.hide().empty();
         var r = seen[keys[0]];
         if (r.party_type && r.party) { adoptParty(r.party_type, r.party, r.address); }
         return;
      }

      // More than one destination among the selected requests.
      var list = picked.map(function(r){
         return '<li><b>' + esc(r.reference_no) + '</b> → '
              + (r.party ? esc(r.party.text) + ' (' + esc(partyTypeLabel(r.party_type)) + ')' : 'not specified')
              + '</li>';
      }).join('');

      $warn.html('<strong>The selected requests ask for different destinations.</strong>'
         + '<ul>' + list + '</ul>'
         + 'Choose the correct one under Dispatch To below — it is saved against every request on this challan.').show();
   }

   /** Set the shared Dispatch To from a request's own destination. */
   function adoptParty(type, party, address){
      $('#sdPartySeg input[value="' + type + '"]').prop('checked', true);
      $('#sdPartySeg label').removeClass('on');
      $('#sdPartySeg input:checked').closest('label').addClass('on');
      initParty(party, address);
   }

   /* ── Ticking a request ──────────────────────────────── */
   $(document).on('change', '.sd-pick', function(){
      var id  = $(this).data('id');
      var on  = $(this).is(':checked');
      var $tr = $(this).closest('tr');
      var r   = rows.filter(function(x){ return x.id == id; })[0];

      open[id] = on;
      $tr.toggleClass('picked', on);
      $('tr[data-detail="' + id + '"]').toggle(on)
         .find('input, select, textarea').prop('disabled', !on);

      reconcileRequestParties();
      pushPartyToRows();
      updateCount();
   });

   function updateCount(){
      var n = Object.keys(open).filter(function(k){ return open[k]; }).length;
      $('#sdPickedChip').text(n + ' selected');
   }

   /* ══════════════ Voluntary products ══════════════ */
   function addVolRow(){
      var i = volSeq++;
      var n = 'voluntary_items[' + i + ']';
      var e = 'voluntary_items.' + i + '.';

      var opts = '<option value=""></option>';
      $.each(PRODUCTS, function(_, p){
         opts += '<option value="' + p.id + '" data-form="' + esc(p.physical_form) + '">'
              + esc(p.product_name) + (p.product_code ? ' (' + esc(p.product_code) + ')' : '') + '</option>';
      });

      var $row = $('<tr class="sd-vol-row">'
         + '<td><select class="sdx-in sm vol-product" name="' + n + '[product_id]">' + opts + '</select>'
         + '  <div class="sdx-err" data-err="' + e + 'product_id"></div></td>'
         + '<td><select class="sdx-in sm vol-pack" name="' + n + '[approved_pack_size]"></select>'
         + '  <div class="sdx-err" data-err="' + e + 'approved_pack_size"></div></td>'
         + '<td><input type="number" min="1" step="1" value="1" class="sdx-in sm data vol-packs" name="' + n + '[approved_no_of_packs]">'
         + '  <div class="sdx-err" data-err="' + e + 'approved_no_of_packs"></div></td>'
         + '<td class="num"><b class="vol-qty">0</b> Kg<div class="sdx-hint">auto</div></td>'
         + '<td><input type="text" class="sdx-in sm data" name="' + n + '[batch_no]" placeholder="Optional">'
         + '  <div class="sdx-err" data-err="' + e + 'batch_no"></div></td>'
         + '<td><button type="button" class="sdx-btn icon vol-remove" title="Remove"><i class="fa fa-trash"></i></button>'
         + '  <input type="hidden" class="sd-party-type" name="' + n + '[dispatch_party_type]">'
         + '  <input type="hidden" class="sd-dealer" name="' + n + '[dispatch_dealer_id]">'
         + '  <input type="hidden" class="sd-customer" name="' + n + '[dispatch_customer_id]">'
         + '  <input type="hidden" class="sd-user" name="' + n + '[dispatch_user_id]">'
         + '  <input type="hidden" class="sd-address" name="' + n + '[dispatch_address]"></td>'
         + '</tr>');

      $('#sdVolTable tbody').append($row);
      $row.find('.vol-product').select2({ width: '100%', placeholder: '— Select Product —' });
      setVolPackSizes($row);
      calcVolRow($row);
      pushPartyToRows();
   }

   $('#sdVolAdd').on('click', addVolRow);

   /** Pack sizes come from the product's physical form, same as the request screen. */
   function setVolPackSizes($row){
      var form  = $row.find('.vol-product option:selected').data('form');
      var $pack = $row.find('.vol-pack').empty();

      if (!$row.find('.vol-product').val()) {
         $pack.append('<option value="">—</option>');
         return;
      }
      $.each(packSizesFor(form), function(_, size){
         $pack.append('<option value="' + size + '">' + size + ' Kg</option>');
      });
   }

   /** Qty is never typed in: pack size x no of packs. */
   function calcVolRow($row){
      var pack  = parseFloat($row.find('.vol-pack').val()) || 0;
      var packs = parseInt($row.find('.vol-packs').val(), 10) || 0;
      $row.find('.vol-qty').text(Math.round(pack * packs * 1000) / 1000);
   }

   $(document).on('change', '.vol-product', function(){
      var $row = $(this).closest('tr');
      setVolPackSizes($row);
      calcVolRow($row);
   });

   $(document).on('input change', '.vol-pack, .vol-packs', function(){
      calcVolRow($(this).closest('tr'));
   });

   $(document).on('click', '.vol-remove', function(){
      if ($('#sdVolTable tbody tr').length === 1) { alert('At least one product is required.'); return; }
      $(this).closest('tr').remove();
   });

   $('#sdVolUser').on('change', function(){
      if (partyType() === 'user' && !$('#sdParty').val()) { initParty(); }
   });

   /* ══════════════ Dispatch To — one destination ══════════════ */
   function partyType(){ return $('#sdPartySeg input:checked').val() || 'user'; }

   function initParty(preselect, address){
      var t = partyType();
      $('#sdPartyLabel').text(t === 'dealer' ? 'Dealer' : (t === 'customer' ? 'Customer' : 'Employee'));

      // Dealer's own material going to that dealer: it is already decided, so
      // show the name rather than a picker.
      var fixedDealer = isDealerMode() && t === 'dealer';
      $('#sdPartyFixed').toggleClass('sdx-hide', !fixedDealer);
      $('#sdParty').next('.select2-container').toggleClass('sdx-hide', fixedDealer);

      if (fixedDealer) {
         var dealer = selectedDealer();
         $('#sdPartyFixed').val(dealer ? dealer.text : '— Select the dealer above —');
         if (dealer) { $('#sdAddress').val(dealer.address); }
         setFixedParty(dealer ? dealer.id : '');
         return;
      }

      var $p = $('#sdParty');
      if ($p.hasClass('select2-hidden-accessible')) { $p.select2('destroy'); }
      $p.empty().select2({
         width: '100%',
         placeholder: '— Search —',
         ajax: {
            url: URLS.parties, dataType: 'json', delay: 250,
            data: function(params){ return { type: partyType(), q: params.term || '' }; },
            processResults: function(data){ return { results: data.results }; }
         }
      });

      if (preselect && preselect.id) {
         $p.append(new Option(preselect.text, preselect.id, true, true)).trigger('change');
         if (address) { $('#sdAddress').val(address); }
      } else if (t === 'user') {
         // Sending to the employee means the one selected above.
         var employee = selectedEmployee();
         if (employee) { $p.append(new Option(employee.text, employee.id, true, true)).trigger('change'); }
      }

      pushPartyToRows();
   }

   /** Drives the hidden inputs when the party is fixed rather than picked. */
   var fixedPartyId = '';
   function setFixedParty(id){
      fixedPartyId = id;
      pushPartyToRows();
   }

   /** The chosen party id, whether it came from the picker or is fixed. */
   function chosenPartyId(){
      return (isDealerMode() && partyType() === 'dealer') ? fixedPartyId : ($('#sdParty').val() || '');
   }

   /** Copy the one chosen destination onto every row taking part in the save. */
   function pushPartyToRows(){
      var t       = partyType();
      var id      = chosenPartyId();
      var address = $('#sdAddress').val();

      var $targets = mode() === 'voluntary'
         ? $('#sdVolTable tbody tr')
         : $('tr.sd-detail').filter(function(){ return open[$(this).attr('data-detail')]; });

      $targets.each(function(){
         var $row = $(this);
         $row.find('.sd-party-type').val(t);
         $row.find('.sd-dealer').val(t === 'dealer' ? id : '');
         $row.find('.sd-customer').val(t === 'customer' ? id : '');
         $row.find('.sd-user').val(t === 'user' ? id : '');
         $row.find('.sd-address').val(address);
      });
   }

   $('#sdParty').on('select2:select', function(e){
      if (e.params.data.address) { $('#sdAddress').val(e.params.data.address); }
      pushPartyToRows();
   });

   $('#sdPartySeg input').on('change', function(){
      $('#sdAddress').val('');
      initParty();
   });

   $('#sdAddress').on('input change', pushPartyToRows);

   /* ══════════════ Submit ══════════════ */
   function clearErrors(){
      $('#sdAlert').hide().empty();
      $('#sdPage .sdx-err').hide().text('');
      $('#sdPage .has-err').removeClass('has-err');
   }

   function showErrors(errors){
      var unplaced = [];
      $.each(errors, function(field, msgs){
         var $el = $('#sdPage .sdx-err').filter(function(){ return $(this).attr('data-err') === field; });
         if ($el.length) {
            $el.text(msgs[0]).show();
            $el.closest('.sdx-f').find('.sdx-in').first().addClass('has-err');
            $el.closest('tr.sd-detail').show();
         } else {
            unplaced.push(msgs[0]);
         }
      });
      if (unplaced.length) {
         $('#sdAlert').html('<strong>Please fix the following:</strong><ul>' + unplaced.map(function(m){ return '<li>' + esc(m) + '</li>'; }).join('') + '</ul>').show();
      }
      var $first = $('#sdPage .sdx-err:visible, #sdAlert:visible').first();
      if ($first.length) { $('html,body').animate({ scrollTop: $first.offset().top - 150 }, 400); }
   }

   $('#sdSubmit').on('click', function(){
      var $btn = $(this);
      clearErrors();
      pushPartyToRows();

      var voluntary = mode() === 'voluntary';
      var picked = voluntary
         ? $('#sdVolTable tbody tr').length
         : Object.keys(open).filter(function(k){ return open[k]; }).length;

      if (!picked) {
         $('#sdAlert').text(voluntary
            ? 'Add at least one product to dispatch.'
            : 'Select at least one sample request to dispatch.').show();
         $('html,body').animate({ scrollTop: $('#sdAlert').offset().top - 150 }, 400);
         return;
      }

      if (!chosenPartyId()) {
         $('#sdAlert').text('Choose where this dispatch is going under "Dispatch To".').show();
         $('html,body').animate({ scrollTop: $('#sdAlert').offset().top - 150 }, 400);
         return;
      }

      if (!confirm('Dispatch ' + picked + (voluntary ? ' voluntary sample(s)' : ' request(s)') + ' on this challan?')) { return; }

      $btn.prop('disabled', true);
      $('.loadingDiv').show();
      $.ajax({
         url: URLS.store,
         type: 'POST',
         data: $('#sdForm').serialize(),
         headers: { 'Accept': 'application/json' },
         success: function(res){ window.location.href = res.url; },
         error: function(xhr){
            $('.loadingDiv').hide();
            $btn.prop('disabled', false);
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
               showErrors(xhr.responseJSON.errors);
            } else {
               $('#sdAlert').text('Server error. Please try again.').show();
               $('html,body').animate({ scrollTop: $('#sdAlert').offset().top - 150 }, 400);
            }
         }
      });
   });

   load();
   applyMode();
   applyTransportLabel();
   initParty();
});
</script>
@endsection
