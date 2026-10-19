@extends('layouts.adminLayout.backendLayout')
@section('content')
{{-- ════════════════════════════════════════════════════════════════════
SAMPLE REQUEST DETAIL — one product (SR-n.m).

Each product of a request is approved or declined on its own, so this page
deals with exactly one. The product block follows the old sampling screen: a
read-only "Requested" row holding exactly what the app sent, and an editable
"Approved" row underneath where pack size is a dropdown limited by the
product's physical form and qty is always pack size × no of packs — never
typed in.
═══════════════════════════════════════════════════════════════════════ --}}
@include('admin.sampling.partials.styles')
@php
   $r         = $samplingRequest;
   $editable  = $r->status === 'pending';
   $chip      = ['pending' => 'warn', 'approved' => 'req', 'closed' => 'ok', 'declined' => 'bad'][$r->status];
   $partyType = $r->dispatch_dealer_id ? 'dealer' : ($r->dispatch_customer_id ? 'customer' : 'user');
   $partyId   = $r->dispatch_dealer_id ?: ($r->dispatch_customer_id ?: $r->dispatch_user_id);
   $partyText = $r->dispatchPartyName();
   $yn        = function ($v) { return $v ? 'Yes' : 'No'; };
   $val       = function ($v) { return ($v === null || $v === '') ? null : $v; };
   $kg        = function ($v) { return $v === null ? '—' : rtrim(rtrim(number_format($v, 3, '.', ''), '0'), '.') . ' Kg'; };
   $nq        = function ($v) { return $v === null ? '—' : rtrim(rtrim(number_format($v, 3, '.', ''), '0'), '.'); };
@endphp
<div class="page-content-wrapper">
   <div class="page-content">
      <ul class="page-breadcrumb breadcrumb" style="margin-bottom:14px;">
         <li><a href="{{ url('admin/dashboard') }}">Dashboard</a><i class="fa fa-circle"></i></li>
         <li><a href="{{ route('admin.sampling-requests.index', ['status' => $r->status]) }}">Sample Requests</a><i class="fa fa-circle"></i></li>
         <li><span>{{ $r->reference_no }}</span></li>
      </ul>

      @if(Session::has('flash_message_success'))
      <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('flash_message_success') }}</div>
      @endif

      <div class="sdx" id="srPage">
         <div class="sdx-head">
            <div>
               <h1>{{ $r->reference_no }} <span class="sdx-chip {{ $chip }}" style="vertical-align:middle;margin-left:6px">{{ $r->status_label }}</span>
                  @if($r->status === 'approved')
                     @if($r->is_ready)
                     <span class="sdx-chip ok" style="vertical-align:middle"><i class="fa fa-check"></i> Ready</span>
                     @else
                     <span class="sdx-chip mute" style="vertical-align:middle">Not ready yet</span>
                     @endif
                  @endif
                  @if($r->is_voluntarily)
                  <span class="sdx-chip vol" style="vertical-align:middle">Voluntary</span>
                  @endif
               </h1>
               <div class="sdx-meta">
                  {{ $r->is_voluntarily ? 'Raised by the admin at dispatch time, answerable to' : 'Raised by' }}
                  {{ optional($r->user)->name ?: '—' }} on {{ $r->request_date ? $r->request_date->format('d M Y') : '—' }}
               </div>
            </div>
            <div class="sdx-tools">
               <a class="sdx-btn ghost sm" href="{{ route('admin.sampling-requests.index', ['status' => $r->status]) }}"><i class="fa fa-arrow-left"></i> Back to list</a>
            </div>
         </div>

         <div class="sdx-alert" id="srAlert"></div>

         @if($r->status === 'declined')
         <div class="sdx-note">
            <b>Declined</b> by {{ optional($r->declined_by)->name ?: '—' }} on {{ $r->declined_at ? $r->declined_at->format('d M Y, h:i A') : '—' }}.<br>
            Reason: {{ $r->decline_reason }}
         </div>
         @elseif($r->status === 'closed')
         <div class="sdx-note ok">
            <b>Closed</b> on {{ $r->closed_at ? $r->closed_at->format('d M Y, h:i A') : '—' }} — the full approved qty of {{ $kg($r->approved_qty) }} has been dispatched.
         </div>
         @elseif($r->status === 'approved' && $r->pending_qty > 0 && $r->total_dispatched_qty > 0)
         <div class="sdx-note">
            Partially dispatched — {{ $kg($r->total_dispatched_qty) }} of {{ $kg($r->approved_qty) }} sent, <b>{{ $kg($r->pending_qty) }} still pending</b>.
         </div>
         @endif

         {{-- ── Numbers at a glance ─────────────────────────────── --}}
         <div class="sdx-stats">
            <div class="sdx-stat"><div class="k">Requested Qty</div><div class="v">{{ $kg($r->requested_qty) }}</div></div>
            <div class="sdx-stat hi"><div class="k">Approved Qty</div><div class="v">{{ $kg($r->approved_qty) }}</div></div>
            <div class="sdx-stat"><div class="k">Dispatched</div><div class="v">{{ $kg($r->total_dispatched_qty) }}</div></div>
            <div class="sdx-stat warn"><div class="k">Pending</div><div class="v">{{ $kg($r->pending_qty) }}</div></div>
         </div>

         <form id="srForm" method="post" action="javascript:;" autocomplete="off">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            {{-- ── 1. Who raised it ────────────────────────────── --}}
            <div class="sdx-card">
               <div class="sdx-ch">
                  <div class="t"><span class="ix">1</span><div>Requester &amp; Customer<small>Who asked for this sample and who it is meant for</small></div></div>
               </div>
               <div class="sdx-cb">
                  <div class="sdx-grid">
                     <div class="sdx-kv one">
                        <div><div class="k">Raised By</div><div class="v">{{ optional($r->user)->name ?: '—' }}</div></div>
                        <div><div class="k">Designation</div><div class="v {{ optional($r->user)->designation ? '' : 'empty' }}">{{ optional($r->user)->designation ?: '—' }}</div></div>
                        <div><div class="k">Mobile / Email</div><div class="v num">{{ optional($r->user)->mobile ?: '—' }}<br>{{ optional($r->user)->email ?: '' }}</div></div>
                        <div><div class="k">Raised Through</div><div class="v">{{ ucfirst($r->raised_by) }} app</div></div>
                     </div>
                     <div class="sdx-kv one">
                        <div>
                           <div class="k">Customer</div>
                           @if($editable)
                           <select class="sdx-in" name="customer_id" id="srCustomer">
                              <option value="">— Not linked to a customer —</option>
                              @if($r->customer)
                              <option value="{{ $r->customer->id }}" selected>{{ $r->customer->name }}</option>
                              @endif
                           </select>
                           <div class="sdx-err" data-err="customer_id"></div>
                           @else
                           <div class="v {{ $r->customer ? '' : 'empty' }}">{{ optional($r->customer)->name ?: '— Not linked to a customer —' }}</div>
                           @endif
                        </div>
                        @if($r->customer)
                        <div><div class="k">Contact Person</div><div class="v {{ $r->customer->contact_person_name ? '' : 'empty' }}">{{ $r->customer->contact_person_name ?: '—' }}</div></div>
                        <div><div class="k">Mobile / Email</div><div class="v num">{{ $r->customer->mobile ?: '—' }}<br>{{ $r->customer->email ?: '' }}</div></div>
                        <div><div class="k">Address</div><div class="v {{ $r->customer->address ? '' : 'empty' }}">{{ $r->customer->address ?: '—' }}</div></div>
                        @endif
                        <div><div class="k">Specific Customer?</div><div class="v">{{ $yn($r->is_specific_customer) }}</div></div>
                     </div>
                  </div>
               </div>
            </div>

            {{-- ── 2. Product ──────────────────────────────────── --}}
            <div class="sdx-card">
               <div class="sdx-ch">
                  <div class="t"><span class="ix">2</span><div>Product<small>{{ $editable ? 'Qty is calculated from pack size × no of packs — it cannot be typed in' : 'Locked once the request has been approved or declined' }}</small></div></div>
                  @if($r->requested_product_id && $r->requested_product_id != $r->product_id)
                  <span class="sdx-chip warn"><i class="fa fa-exchange"></i> Product changed by admin</span>
                  @endif
               </div>
               <div class="sdx-cb">
                  <div class="sdx-scroll">
                     <table class="sdx-table" id="srProduct">
                        <thead>
                           <tr>
                              <th style="width:110px">Type</th>
                              <th style="min-width:190px">Product</th>
                              <th style="width:150px">Pack Size</th>
                              <th style="width:130px">No of Packs</th>
                              <th style="width:120px">Qty (Kg)</th>
                              <th style="width:130px">Value (₹)</th>
                              <th>Remarks</th>
                           </tr>
                        </thead>
                        <tbody>
                           {{-- Requested: exactly what the app sent, never edited --}}
                           <tr style="background:#f7f9f8">
                              <td><span class="sdx-chip req">Requested</span></td>
                              <td>
                                 <div class="pn">{{ optional($r->requested_product)->product_name ?: '—' }}</div>
                                 <div class="pc">{{ optional($r->requested_product)->product_code }}</div>
                                 @if($r->pack_size_info)<div class="pc">Pack info: {{ $r->pack_size_info }}</div>@endif
                              </td>
                              <td class="num">{{ $r->pack_size ? $nq($r->pack_size) . ' Kg' : '—' }}</td>
                              <td class="num">{{ $r->no_of_packs ?: '—' }}</td>
                              <td class="num">{{ $kg($r->requested_qty) }}</td>
                              <td class="num">—</td>
                              <td class="pc">{{ $r->item_remarks ?: '—' }}</td>
                           </tr>

                           {{-- Approved: what the admin authorises --}}
                           <tr id="srApprovedRow" class="{{ $r->status === 'declined' ? 'locked' : '' }}">
                              <td><span class="sdx-chip {{ $editable ? 'ok' : $chip }}">{{ $editable ? 'To Approve' : ucfirst($r->status) }}</span></td>
                              <td>
                                 @if($editable)
                                 <select class="sdx-in sm" name="product_id" id="srProductSelect">
                                    @foreach($products as $product)
                                    @php
                                       // A product the request still points at may have been
                                       // deactivated since; say so rather than hide it.
                                       $label = $product->product_name
                                          . ($product->product_code ? ' (' . $product->product_code . ')' : '')
                                          . (!empty($product->is_inactive) ? ' — Inactive' : '');
                                    @endphp
                                    <option value="{{ $product->id }}"
                                            data-form="{{ strtolower($product->physical_form) }}"
                                            data-price="{{ $product->dealer_price }}"
                                            {{ $r->product_id == $product->id ? 'selected' : '' }}>
                                       {{ $label }}
                                    </option>
                                    @endforeach
                                 </select>
                                 <div class="sdx-hint" style="margin-top:5px">Dealer Price: ₹ <b id="srPrice">0</b> / Kg</div>
                                 <div class="sdx-err" data-err="product_id"></div>
                                 @else
                                 <div class="pn">{{ optional($r->product)->product_name ?: '—' }}</div>
                                 <div class="pc">{{ optional($r->product)->product_code }}</div>
                                 @if($r->dealer_price)<div class="pc">Dealer Price: ₹ {{ number_format($r->dealer_price, 2) }} / Kg</div>@endif
                                 @endif
                              </td>
                              <td>
                                 @if($editable)
                                 <select class="sdx-in sm" name="approved_pack_size" id="srPack" data-selected="{{ $r->approved_pack_size }}"></select>
                                 <div class="sdx-err" data-err="approved_pack_size"></div>
                                 @php
                                    // The app may ask for a pack size this product cannot go
                                    // out in; the dropdown cannot offer it, so say why the
                                    // approved row does not match what was requested.
                                    $allowedPacks = App\SamplingRequest::packSizesFor(optional($r->product)->physical_form);
                                 @endphp
                                 @if($r->pack_size && !in_array((float) $r->pack_size, array_map('floatval', $allowedPacks)))
                                 <div class="sdx-hint" style="margin-top:5px;color:#b45309">
                                    {{ $nq($r->pack_size) }} Kg packs were asked for — this product only goes out in
                                    {{ implode(' or ', $allowedPacks) }} Kg packs.
                                 </div>
                                 @endif
                                 @else
                                 <span class="num">{{ $r->approved_pack_size ? $nq($r->approved_pack_size) . ' Kg' : '—' }}</span>
                                 @endif
                              </td>
                              <td>
                                 @if($editable)
                                 <input type="number" min="1" step="1" class="sdx-in sm data" name="approved_no_of_packs" id="srPacks" value="{{ $r->approved_no_of_packs ?: 1 }}">
                                 <div class="sdx-err" data-err="approved_no_of_packs"></div>
                                 @else
                                 <span class="num">{{ $r->approved_no_of_packs ?: '—' }}</span>
                                 @endif
                              </td>
                              <td class="num">
                                 @if($editable)
                                 <b><span id="srQty">0</span> Kg</b>
                                 <div class="sdx-hint">auto</div>
                                 @else
                                 <b>{{ $kg($r->approved_qty) }}</b>
                                 @endif
                              </td>
                              <td class="num">
                                 @if($editable)
                                 ₹ <span id="srValue">0.00</span>
                                 @else
                                 {{ $r->approved_value === null ? '—' : '₹ ' . number_format($r->approved_value, 2) }}
                                 @endif
                              </td>
                              <td class="pc">
                                 @if($editable)
                                 Approve fewer packs to give less than what was requested.
                                 @else
                                 {{ $r->approval_remarks ?: '—' }}
                                 @endif
                              </td>
                           </tr>
                        </tbody>
                     </table>
                  </div>
               </div>
            </div>

            {{-- ── 3. Purpose & competitor ─────────────────────── --}}
            <div class="sdx-card">
               <div class="sdx-ch">
                  <div class="t"><span class="ix">3</span><div>Purpose &amp; Competitor Details<small>Why the sample is needed and what it is being tried against</small></div></div>
               </div>
               <div class="sdx-cb">
                  <div class="sdx-grid">
                     <div class="sdx-f full">
                        <label>Purpose / Reason for Request</label>
                        @if($editable)
                        <textarea class="sdx-in sm" name="purpose_reason_for_request">{{ $r->purpose_reason_for_request }}</textarea>
                        <div class="sdx-err" data-err="purpose_reason_for_request"></div>
                        @else
                        <div class="sdx-kv one"><div class="v {{ $val($r->purpose_reason_for_request) ? '' : 'empty' }}">{{ $r->purpose_reason_for_request ?: '—' }}</div></div>
                        @endif
                     </div>
                     <div class="sdx-f full">
                        <label>Application Plan</label>
                        @if($editable)
                        <textarea class="sdx-in sm" name="application_plan">{{ $r->application_plan }}</textarea>
                        <div class="sdx-err" data-err="application_plan"></div>
                        @else
                        <div class="sdx-kv one"><div class="v {{ $val($r->application_plan) ? '' : 'empty' }}">{{ $r->application_plan ?: '—' }}</div></div>
                        @endif
                     </div>
                  </div>

                  <div class="sdx-sub">
                     <div class="h">Competitor being targeted — {{ $yn($r->target_competitor) }} @if($r->product_known) &middot; product known @endif</div>
                     @if($editable)
                     <div class="sdx-grid g3">
                        <div class="sdx-f"><label>Category</label><input class="sdx-in sm" name="competitor_category" value="{{ $r->competitor_category }}"><div class="sdx-err" data-err="competitor_category"></div></div>
                        <div class="sdx-f"><label>Product Name</label><input class="sdx-in sm" name="competitor_product_name" value="{{ $r->competitor_product_name }}"><div class="sdx-err" data-err="competitor_product_name"></div></div>
                        <div class="sdx-f"><label>Make</label><input class="sdx-in sm" name="competitor_make" value="{{ $r->competitor_make }}"><div class="sdx-err" data-err="competitor_make"></div></div>
                        <div class="sdx-f"><label>Dealer Price</label><input type="number" step="0.01" min="0" class="sdx-in sm data" name="competitor_dealer_price" value="{{ $r->competitor_dealer_price }}"><div class="sdx-err" data-err="competitor_dealer_price"></div></div>
                        <div class="sdx-f"><label>Customer Price</label><input type="number" step="0.01" min="0" class="sdx-in sm data" name="competitor_customer_price" value="{{ $r->competitor_customer_price }}"><div class="sdx-err" data-err="competitor_customer_price"></div></div>
                        <div class="sdx-f"><label>Dosage</label><input class="sdx-in sm" name="competitor_dosage" value="{{ $r->competitor_dosage }}"><div class="sdx-err" data-err="competitor_dosage"></div></div>
                        <div class="sdx-f"><label>Monthly Requirement (Kg)</label><input type="number" step="0.001" min="0" class="sdx-in sm data" name="competitor_monthly_requirement_kg" value="{{ $r->competitor_monthly_requirement_kg }}"><div class="sdx-err" data-err="competitor_monthly_requirement_kg"></div></div>
                        <div class="sdx-f full"><label>Specialty</label><textarea class="sdx-in sm" name="competitor_specialty">{{ $r->competitor_specialty }}</textarea></div>
                        <div class="sdx-f full"><label>Application Details</label><textarea class="sdx-in sm" name="competitor_application_details">{{ $r->competitor_application_details }}</textarea></div>
                        <div class="sdx-f full"><label>Expectation</label><textarea class="sdx-in sm" name="competitor_expectation">{{ $r->competitor_expectation }}</textarea></div>
                     </div>
                     @else
                     <div class="sdx-kv">
                        <div><div class="k">Category</div><div class="v {{ $val($r->competitor_category) ? '' : 'empty' }}">{{ $r->competitor_category ?: '—' }}</div></div>
                        <div><div class="k">Product Name</div><div class="v {{ $val($r->competitor_product_name) ? '' : 'empty' }}">{{ $r->competitor_product_name ?: '—' }}</div></div>
                        <div><div class="k">Make</div><div class="v {{ $val($r->competitor_make) ? '' : 'empty' }}">{{ $r->competitor_make ?: '—' }}</div></div>
                        <div><div class="k">Dealer Price</div><div class="v num {{ $val($r->competitor_dealer_price) ? '' : 'empty' }}">{{ $r->competitor_dealer_price ?: '—' }}</div></div>
                        <div><div class="k">Customer Price</div><div class="v num {{ $val($r->competitor_customer_price) ? '' : 'empty' }}">{{ $r->competitor_customer_price ?: '—' }}</div></div>
                        <div><div class="k">Dosage</div><div class="v {{ $val($r->competitor_dosage) ? '' : 'empty' }}">{{ $r->competitor_dosage ?: '—' }}</div></div>
                        <div><div class="k">Monthly Requirement</div><div class="v num {{ $val($r->competitor_monthly_requirement_kg) ? '' : 'empty' }}">{{ $r->competitor_monthly_requirement_kg ?: '—' }}</div></div>
                        <div><div class="k">Specialty</div><div class="v {{ $val($r->competitor_specialty) ? '' : 'empty' }}">{{ $r->competitor_specialty ?: '—' }}</div></div>
                        <div><div class="k">Application Details</div><div class="v {{ $val($r->competitor_application_details) ? '' : 'empty' }}">{{ $r->competitor_application_details ?: '—' }}</div></div>
                        <div><div class="k">Expectation</div><div class="v {{ $val($r->competitor_expectation) ? '' : 'empty' }}">{{ $r->competitor_expectation ?: '—' }}</div></div>
                     </div>
                     @endif
                  </div>
               </div>
            </div>

            {{-- ── 4. Dispatch to ──────────────────────────────── --}}
            <div class="sdx-card">
               <div class="sdx-ch">
                  <div class="t"><span class="ix">4</span><div>Dispatch To<small>Where the material should go — the dispatch screen prefills from here</small></div></div>
               </div>
               <div class="sdx-cb">
                  @if($editable)
                  <div class="sdx-grid">
                     <div class="sdx-f">
                        <label>Send To</label>
                        <div class="sdx-seg">
                           <label class="{{ $partyType === 'dealer' ? 'on' : '' }}"><input type="radio" name="dispatch_party_type" value="dealer" {{ $partyType === 'dealer' ? 'checked' : '' }}><span>Dealer</span></label>
                           <label class="{{ $partyType === 'customer' ? 'on' : '' }}"><input type="radio" name="dispatch_party_type" value="customer" {{ $partyType === 'customer' ? 'checked' : '' }}><span>Customer</span></label>
                           <label class="{{ $partyType === 'user' ? 'on' : '' }}"><input type="radio" name="dispatch_party_type" value="user" {{ $partyType === 'user' ? 'checked' : '' }}><span>Employee</span></label>
                        </div>
                     </div>
                     <div class="sdx-f">
                        <label><span id="srPartyLabel">Party</span></label>
                        <select class="sdx-in" id="srParty"></select>
                        <input type="hidden" name="dispatch_dealer_id" id="srDealerId" value="{{ $r->dispatch_dealer_id }}">
                        <input type="hidden" name="dispatch_customer_id" id="srCustomerId" value="{{ $r->dispatch_customer_id }}">
                        <input type="hidden" name="dispatch_user_id" id="srUserId" value="{{ $r->dispatch_user_id }}">
                        <div class="sdx-err" data-err="dispatch_dealer_id"></div>
                        <div class="sdx-err" data-err="dispatch_customer_id"></div>
                     </div>
                     <div class="sdx-f full">
                        <label>Dispatch Address</label>
                        <textarea class="sdx-in" name="dispatch_address" id="srAddress">{{ $r->dispatch_address }}</textarea>
                        <div class="sdx-err" data-err="dispatch_address"></div>
                     </div>
                  </div>
                  @else
                  <div class="sdx-kv">
                     <div><div class="k">Send To</div><div class="v">{{ $r->dispatch_to ?: ucfirst($partyType) }}</div></div>
                     <div><div class="k">Party</div><div class="v {{ $partyText ? '' : 'empty' }}">{{ $partyText ?: '—' }}</div></div>
                     <div style="grid-column:1/-1"><div class="k">Dispatch Address</div><div class="v {{ $val($r->dispatch_address) ? '' : 'empty' }}">{{ $r->dispatch_address ?: '—' }}</div></div>
                  </div>
                  @endif
               </div>
            </div>

            @if($editable)
            <div class="sdx-card">
               <div class="sdx-cb">
                  <div class="sdx-f">
                     <label>Reason for the changes above</label>
                     <input class="sdx-in" name="change_remarks" placeholder="Optional — shown in the activity log">
                  </div>
               </div>
            </div>
            @endif
         </form>

         {{-- ── Approval summary ────────────────────────────────── --}}
         @if(in_array($r->status, ['approved', 'closed']))
         <div class="sdx-card">
            <div class="sdx-ch"><div class="t"><span class="ix"><i class="fa fa-check"></i></span><div>Approval<small>What the admin authorised</small></div></div></div>
            <div class="sdx-cb">
               <div class="sdx-kv">
                  <div><div class="k">Approved Qty</div><div class="v num">{{ $kg($r->approved_qty) }} <span class="sdx-hint">{{ $r->approved_no_of_packs }} × {{ $nq($r->approved_pack_size) }} Kg, of {{ $kg($r->requested_qty) }} requested</span></div></div>
                  <div><div class="k">Approved By</div><div class="v">{{ optional($r->approved_by)->name ?: '—' }}<br><span class="sdx-hint">{{ $r->approved_at ? $r->approved_at->format('d M Y, h:i A') : '' }}</span></div></div>
                  <div style="grid-column:1/-1"><div class="k">Approval Remarks</div><div class="v {{ $val($r->approval_remarks) ? '' : 'empty' }}">{{ $r->approval_remarks ?: '—' }}</div></div>
                  @if($r->is_ready)
                  <div><div class="k">Marked Ready By</div><div class="v">{{ optional($r->ready_by)->name ?: '—' }}<br><span class="sdx-hint">{{ $r->ready_at ? $r->ready_at->format('d M Y, h:i A') : '' }}</span></div></div>
                  <div><div class="k">Ready Remarks</div><div class="v {{ $val($r->ready_remarks) ? '' : 'empty' }}">{{ $r->ready_remarks ?: '—' }}</div></div>
                  @endif
               </div>
            </div>
         </div>
         @endif

         {{-- ── Dispatch history ────────────────────────────────── --}}
         @if($r->dispatches->count())
         <div class="sdx-card">
            <div class="sdx-ch"><div class="t"><span class="ix"><i class="fa fa-truck"></i></span><div>Dispatches<small>{{ $r->dispatches->count() }} dispatch(es) against this product</small></div></div></div>
            <div class="sdx-cb">
               <div class="sdx-scroll">
                  <table class="sdx-table fit">
                     <thead><tr><th>Dispatch No</th><th>Date</th><th>Product</th><th class="num">Qty</th><th>Batch</th><th>Challan</th><th>Transport / LR</th><th>Sent To</th><th>By</th><th>Receipt</th></tr></thead>
                     <tbody>
                        @foreach($r->dispatches as $d)
                        <tr>
                           <td class="num pn">{{ $d->dispatch_ref_no }}</td>
                           <td class="num">{{ $d->dispatch_date ? $d->dispatch_date->format('d M Y') : '—' }}</td>
                           <td>{{ optional($d->product)->product_name ?: '—' }}</td>
                           <td class="num"><b>{{ $kg($d->dispatch_qty) }}</b></td>
                           <td class="num">{{ $d->batch_no ?: '—' }}</td>
                           <td class="num">{{ $d->invoice_no ?: '—' }}<div class="pc">{{ $d->invoice_date ? $d->invoice_date->format('d M Y') : '' }}</div></td>
                           <td>{{ $d->transport ?: '—' }}<div class="pc num">{{ $d->lr_no }}</div></td>
                           <td>{{ $d->dispatchPartyName() ?: '—' }}<div class="pc">{{ $d->dispatch_to }}</div></td>
                           <td>{{ optional($d->dispatched_by)->name ?: '—' }}</td>
                           {{-- Receipt is the employee's confirmation, not a status of the
                                request: this line can be received while the request stays closed. --}}
                           <td>
                              @if($d->is_received)
                              <span class="sdx-chip ok"><i class="fa fa-check"></i> Received</span>
                              <div class="pc" style="margin-top:4px">{{ $d->received_at ? $d->received_at->format('d M Y, h:i A') : '' }}</div>
                              @else
                              <span class="sdx-chip warn"><i class="fa fa-truck"></i> Not received</span>
                              @endif
                           </td>
                        </tr>
                        @endforeach
                     </tbody>
                  </table>
               </div>
            </div>
         </div>
         @endif

         {{-- ── Query thread ────────────────────────────────────── --}}
         <div class="sdx-card">
            <div class="sdx-ch">
               <div class="t"><span class="ix"><i class="fa fa-comments-o"></i></span><div>Queries<small>Conversation with {{ optional($r->user)->name ?: 'the employee' }}</small></div></div>
               <span class="sdx-chip {{ $thread['unread_admin'] > 0 ? 'bad' : 'mute' }}">
                  {{ $thread['total'] }} message(s){{ $thread['unread_admin'] > 0 ? ' · ' . $thread['unread_admin'] . ' unread' : '' }}
               </span>
            </div>
            <div class="sdx-cb">
               <div class="sdx-alert" id="sqAlert"></div>

               <div class="sq-thread" id="sqThread">
                  @forelse($thread['queries'] as $q)
                  <div class="sq-msg {{ $q['sender_type'] }}">
                     <div class="who">{{ $q['sender_name'] ?: ucfirst($q['sender_type']) }}</div>{{ $q['message'] }}<div class="when">{{ $q['created_at'] }}</div>
                  </div>
                  @empty
                  <div class="sq-empty">
                     <i class="fa fa-comments-o" style="display:block;font-size:24px;margin-bottom:8px;opacity:.5"></i>
                     No messages yet.{{ $thread['can_post'] ? ' Start the conversation below.' : '' }}
                  </div>
                  @endforelse
               </div>

               @if($thread['can_post'])
               <div style="margin-top:16px">
                  <div class="sdx-f">
                     <label>Write a Reply</label>
                     <textarea class="sdx-in" id="sqMessage" placeholder="Type your reply… (Ctrl+Enter to send)"></textarea>
                     <div class="sdx-err" data-err="message"></div>
                  </div>
                  <div style="margin-top:10px">
                     <button type="button" class="sdx-btn g" id="sqSend"><i class="fa fa-paper-plane"></i> Send</button>
                  </div>
               </div>
               @else
               <div class="sdx-note" style="margin-top:16px">{{ $thread['closed_reason'] }}</div>
               @endif
            </div>
         </div>

         {{-- ── Activity log ────────────────────────────────────── --}}
         <div class="sdx-card">
            <div class="sdx-ch"><div class="t"><span class="ix"><i class="fa fa-history"></i></span><div>Activity Log<small>What was requested, what changed and who changed it</small></div></div></div>
            <div class="sdx-cb">
               @if($r->logs->isEmpty())
               <div class="sdx-empty"><i class="fa fa-history"></i>Nothing recorded yet.</div>
               @else
               <ul class="sdx-tl">
                  @foreach($r->logs as $log)
                  <li class="{{ in_array($log->action, ['created', 'updated']) ? 'mute' : '' }}">
                     <div class="a">{{ $log->action_label }}</div>
                     <div class="m">{{ optional($log->user)->name ?: 'System' }} &middot; {{ $log->created_at->format('d M Y, h:i A') }}</div>
                     @if($log->remarks)<div class="d">{{ $log->remarks }}</div>@endif
                     @if($log->changes)
                     <div class="chg">
                        @foreach($log->changes as $field => $change)
                        <div>
                           <b>{{ isset($change['label']) ? $change['label'] : $field }}:</b>
                           <span class="was">{{ ($change['from'] === null || $change['from'] === '') ? '(blank)' : $change['from'] }}</span>
                           &rarr;
                           <span class="now">{{ ($change['to'] === null || $change['to'] === '') ? '(blank)' : $change['to'] }}</span>
                        </div>
                        @endforeach
                     </div>
                     @endif
                  </li>
                  @endforeach
               </ul>
               @endif
            </div>
         </div>

         {{-- ── Sticky actions ──────────────────────────────────────
              Declining stays available after approval and after the ready
              mark, right up until something is dispatched — the admin is
              allowed to change their mind. --}}
         @php $canDecline = $r->canBeDeclined(); @endphp
         @if($editable || $canDecline)
         <div class="sdx-actions">
            <div class="note">
               <i class="fa fa-info-circle"></i>
               @if($editable)
               Approving reserves the qty against {{ optional($r->user)->name ?: 'the employee' }}'s free-sample stock.
               @elseif(!$r->is_ready)
               Marking ready tells the dispatch desk this material is packed and can go out.
               Declining now returns the reserved {{ $kg($r->approved_qty) }} to
               {{ optional($r->user)->name ?: 'the employee' }}'s free-sample stock.
               @else
               This material is packed and waiting at the dispatch desk. Declining now takes it off
               the desk and returns the reserved {{ $kg($r->approved_qty) }} to
               {{ optional($r->user)->name ?: 'the employee' }}'s free-sample stock.
               @endif
            </div>
            <div style="display:flex;gap:9px;flex-wrap:wrap">
               @if($editable)
               <button type="button" class="sdx-btn ghost" id="srSave"><i class="fa fa-save"></i> Save Changes</button>
               @endif
               @if($canDecline)
               <button type="button" class="sdx-btn r" id="srDeclineBtn"><i class="fa fa-ban"></i> Decline</button>
               @endif
               @if($editable)
               <button type="button" class="sdx-btn g" id="srApproveBtn"><i class="fa fa-check"></i> Approve</button>
               @elseif(!$r->is_ready)
               <button type="button" class="sdx-btn g" id="srReadyBtn"><i class="fa fa-cube"></i> Mark as Ready</button>
               @endif
            </div>
         </div>
         @endif
      </div>
   </div>
</div>

{{-- ════════════════ Modals ════════════════ --}}
<div class="sdx-modal" id="srApproveModal">
   <div class="box sdx">
      <div class="h">Approve {{ $r->reference_no }} <span class="x" data-close>&times;</span></div>
      <div class="b">
         <div class="sdx-note ok" id="srApproveSummary" style="margin-bottom:14px"></div>
         <div class="sdx-f">
            <label>Approval Remarks</label>
            <textarea class="sdx-in" id="srApprovalRemarks" placeholder="Optional — visible in the activity log"></textarea>
            <div class="sdx-err" data-err="approval_remarks"></div>
         </div>
      </div>
      <div class="f">
         <button class="sdx-btn ghost" data-close>Cancel</button>
         <button class="sdx-btn g" id="srApproveConfirm"><i class="fa fa-check"></i> Approve Request</button>
      </div>
   </div>
</div>

<div class="sdx-modal" id="srDeclineModal">
   <div class="box sdx">
      <div class="h">Decline {{ $r->reference_no }} <span class="x" data-close>&times;</span></div>
      <div class="b">
         @if($r->status === 'approved')
         <div class="sdx-note" style="margin-bottom:14px">
            This request was approved for <b>{{ $kg($r->approved_qty) }}</b>{{ $r->is_ready ? ' and is already marked ready' : '' }}.
            Declining it releases that qty back to {{ optional($r->user)->name ?: 'the employee' }}'s
            free-sample stock and removes it from the dispatch screen.
         </div>
         @endif
         <div class="sdx-f">
            <label>Reason for Declining <span class="rq">*</span></label>
            <textarea class="sdx-in" id="srDeclineReason" placeholder="Why is this request being refused?"></textarea>
            <div class="sdx-err" data-err="decline_reason"></div>
         </div>
      </div>
      <div class="f">
         <button class="sdx-btn ghost" data-close>Cancel</button>
         <button class="sdx-btn r" id="srDeclineConfirm"><i class="fa fa-ban"></i> Decline Request</button>
      </div>
   </div>
</div>

<div class="sdx-modal" id="srReadyModal">
   <div class="box sdx">
      <div class="h">Mark {{ $r->reference_no }} ready <span class="x" data-close>&times;</span></div>
      <div class="b">
         <div class="sdx-f">
            <label>Ready Remarks</label>
            <textarea class="sdx-in" id="srReadyRemarks" placeholder="Optional — batch prepared, packing notes, etc."></textarea>
            <div class="sdx-err" data-err="ready_remarks"></div>
         </div>
      </div>
      <div class="f">
         <button class="sdx-btn ghost" data-close>Cancel</button>
         <button class="sdx-btn g" id="srReadyConfirm"><i class="fa fa-cube"></i> Mark as Ready</button>
      </div>
   </div>
</div>

<script>
$(function () {
   var URLS = {
      queries:    @json(route('admin.sampling-requests.queries', $r->id)),
      raiseQuery: @json(route('admin.sampling-requests.raise-query', $r->id)),
      update:  @json(route('admin.sampling-requests.update', $r->id)),
      approve: @json(route('admin.sampling-requests.approve', $r->id)),
      decline: @json(route('admin.sampling-requests.decline', $r->id)),
      ready:   @json(route('admin.sampling-requests.mark-ready', $r->id)),
      parties: @json(route('admin.sampling-parties'))
   };
   var EDITABLE   = @json($editable);
   var TOKEN      = @json(csrf_token());
   var PACK_SIZES = @json(App\SamplingRequest::PACK_SIZES);

   function esc(v){ return $('<div>').text(v == null ? '' : v).html(); }

   /* ── Query thread ───────────────────────────────────── */
   function renderThread(queries) {
      if (!queries.length) {
         $('#sqThread').html('<div class="sq-empty">No messages yet.</div>');
         return;
      }
      var html = '';
      $.each(queries, function (_, q) {
         html += '<div class="sq-msg ' + q.sender_type + '">'
              + '<div class="who">' + esc(q.sender_name || q.sender_type) + '</div>'
              + esc(q.message)
              + '<div class="when">' + esc(q.created_at) + '</div></div>';
      });
      $('#sqThread').html(html);
      $('#sqThread').scrollTop($('#sqThread')[0].scrollHeight);
   }

   if ($('#sqThread').length) { $('#sqThread').scrollTop($('#sqThread')[0].scrollHeight); }

   $('#sqSend').on('click', function () {
      var message = $.trim($('#sqMessage').val());
      if (!message) { $('#sqMessage').focus(); return; }

      var $btn = $(this).prop('disabled', true);
      $('#sqAlert').hide().empty();

      $.ajax({
         url: URLS.raiseQuery, type: 'POST',
         data: { _token: TOKEN, message: message },
         headers: { 'Accept': 'application/json' },
         success: function () {
            $('#sqMessage').val('');
            $.get(URLS.queries, function (r) { renderThread(r.queries); });
            $btn.prop('disabled', false);
         },
         error: function (xhr) {
            $btn.prop('disabled', false);
            var msg = 'Could not send. Please try again.';
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
               msg = Object.values(xhr.responseJSON.errors)[0][0];
            }
            $('#sqAlert').text(msg).show();
         }
      });
   });

   $('#sqMessage').on('keydown', function (e) {
      if (e.ctrlKey && e.key === 'Enter') { $('#sqSend').trigger('click'); }
   });

   /* ── Modals ─────────────────────────────────────────── */
   function openModal(id){ $('#' + id).addClass('on'); }
   function closeModal(){ $('.sdx-modal').removeClass('on'); }
   $('.sdx-modal').on('click', function(e){ if (e.target === this) { closeModal(); } });
   $('[data-close]').on('click', closeModal);

   /* ── Errors ─────────────────────────────────────────── */
   function clearErrors(){
      $('#srAlert').hide().empty();
      $('.sdx-err').hide().text('');
      $('.has-err').removeClass('has-err');
   }

   function showErrors(errors){
      var unplaced = [];
      $.each(errors, function(field, msgs){
         var $el = $('.sdx-err').filter(function(){ return $(this).attr('data-err') === field; });
         if ($el.length) {
            $el.text(msgs[0]).show();
            $el.closest('.sdx-f, td').find('.sdx-in').first().addClass('has-err');
         } else {
            unplaced.push(msgs[0]);
         }
      });
      if (unplaced.length) {
         $('#srAlert').html('<strong>Please fix the following:</strong><ul>' + unplaced.map(function(m){ return '<li>' + esc(m) + '</li>'; }).join('') + '</ul>').show();
      }
      closeModal();
      var $first = $('.sdx-err:visible, #srAlert:visible').first();
      if ($first.length) { $('html,body').animate({ scrollTop: $first.offset().top - 150 }, 400); }
   }

   function post(url, data, $btn){
      clearErrors();
      if ($btn) { $btn.prop('disabled', true); }
      $('.loadingDiv').show();
      $.ajax({
         url: url, type: 'POST', data: data,
         headers: { 'Accept': 'application/json' },
         success: function(res){ window.location.href = res.url; },
         error: function(xhr){
            $('.loadingDiv').hide();
            if ($btn) { $btn.prop('disabled', false); }
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
               showErrors(xhr.responseJSON.errors);
            } else {
               $('#srAlert').text('Server error. Please try again.').show();
               closeModal();
            }
         }
      });
   }

   /* ── Decline — available at every stage before dispatch ─ */
   $('#srDeclineBtn').on('click', function(){ openModal('srDeclineModal'); });
   $('#srDeclineConfirm').on('click', function(){
      post(URLS.decline, { _token: TOKEN, decline_reason: $('#srDeclineReason').val() }, $(this));
   });

   if (!EDITABLE) {
      /* ── Approved: the ready mark, plus the decline above ── */
      $('#srReadyBtn').on('click', function(){ openModal('srReadyModal'); });
      $('#srReadyConfirm').on('click', function(){
         post(URLS.ready, { _token: TOKEN, ready_remarks: $('#srReadyRemarks').val() }, $(this));
      });
      return;
   }

   /* ══════════════ Pending: the editable form ══════════════ */
   $('#srProductSelect').select2({ width: '100%' });

   $('.sdx-seg input[type=radio]').on('change', function(){
      $(this).closest('.sdx-seg').find('label').removeClass('on');
      $(this).closest('label').addClass('on');
   });

   /* ── Pack size dropdown + auto qty ──────────────────── */
   /** Pack sizes a product can go out in, from its physical form. */
   function packSizesFor(form){
      return PACK_SIZES[String(form || '').toLowerCase()] || PACK_SIZES['default'];
   }

   /** Rebuild the pack-size options, keeping the current choice when still valid. */
   function setPackSizes(){
      var form  = $('#srProductSelect option:selected').data('form');
      var $pack = $('#srPack');
      var saved = $pack.attr('data-selected');

      $pack.empty();
      $.each(packSizesFor(form), function(_, size){
         $pack.append('<option value="' + size + '">' + size + ' Kg</option>');
      });

      if (saved && $pack.find('option[value="' + parseFloat(saved) + '"]').length) {
         $pack.val(parseFloat(saved));
      } else {
         $pack.prop('selectedIndex', 0);
      }
   }

   /** Qty is never typed in: it is always pack size x no of packs. */
   function calculate(){
      var pack  = parseFloat($('#srPack').val()) || 0;
      var packs = parseInt($('#srPacks').val(), 10) || 0;
      var price = parseFloat($('#srProductSelect option:selected').data('price')) || 0;
      var qty   = Math.round(pack * packs * 1000) / 1000;

      $('#srQty').text(qty);
      $('#srPrice').text(price);
      $('#srValue').text((qty * price).toFixed(2));
      return qty;
   }

   $('#srProductSelect').on('change', function(){
      $('#srPack').removeAttr('data-selected');
      setPackSizes();
      calculate();
   });

   $('#srPack, #srPacks').on('input change', calculate);

   setPackSizes();
   calculate();

   /* ── Customer picker ────────────────────────────────── */
   $('#srCustomer').select2({
      width: '100%',
      placeholder: '— Not linked to a customer —',
      allowClear: true,
      ajax: {
         url: URLS.parties, dataType: 'json', delay: 250,
         data: function(params){ return { type: 'customer', q: params.term || '' }; },
         processResults: function(data){ return { results: data.results }; }
      }
   });

   /* ── Dispatch-to party picker ───────────────────────── */
   var PARTY = { type: @json($partyType), id: @json($partyId), text: @json($partyText) };

   function partyType(){ return $('input[name=dispatch_party_type]:checked').val(); }

   function syncPartyIds(){
      var id = $('#srParty').val() || '';
      var t  = partyType();
      $('#srDealerId').val(t === 'dealer' ? id : '');
      $('#srCustomerId').val(t === 'customer' ? id : '');
      $('#srUserId').val(t === 'user' ? id : '');
   }

   function initParty(preselect){
      var t = partyType();
      $('#srPartyLabel').text(t === 'dealer' ? 'Dealer' : (t === 'customer' ? 'Customer' : 'Employee'));
      var $p = $('#srParty');
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
      }
      syncPartyIds();
   }

   $('#srParty').on('select2:select', function(e){
      if (e.params.data.address) { $('#srAddress').val(e.params.data.address); }
      syncPartyIds();
   });

   $('input[name=dispatch_party_type]').on('change', function(){ initParty(null); });
   initParty(PARTY);

   /* ── Actions ────────────────────────────────────────── */
   function formData(){
      syncPartyIds();
      var data = $('#srForm').serializeArray();
      data.push({ name: 'dispatch_party_type', value: partyType() });
      return data;
   }

   $('#srSave').on('click', function(){
      post(URLS.update, formData(), $(this));
   });

   $('#srApproveBtn').on('click', function(){
      var qty = calculate();
      $('#srApproveSummary').html(
         esc($('#srProductSelect option:selected').text().trim()) + '<br><b>' + qty + ' Kg</b> — '
         + esc($('#srPacks').val()) + ' × ' + esc($('#srPack').val()) + ' Kg packs'
         + ' <span style="opacity:.75">(requested {{ $nq($r->requested_qty) }} Kg)</span>'
      );
      openModal('srApproveModal');
   });

   $('#srApproveConfirm').on('click', function(){
      var data = formData();
      data.push({ name: 'approval_remarks', value: $('#srApprovalRemarks').val() });
      post(URLS.approve, data, $(this));
   });


});
</script>
@endsection
