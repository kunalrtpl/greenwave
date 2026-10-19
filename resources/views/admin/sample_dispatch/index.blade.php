@extends('layouts.adminLayout.backendLayout')
@section('content')
{{-- ════════════════════════════════════════════════════════════════════
DISPATCH HISTORY — one block per challan (dispatch_ref_no), listing every
request line that went out on it.
═══════════════════════════════════════════════════════════════════════ --}}
@include('admin.sampling.partials.styles')
<div class="page-content-wrapper">
   <div class="page-content">
      <ul class="page-breadcrumb breadcrumb" style="margin-bottom:14px;">
         <li><a href="{{ url('admin/dashboard') }}">Dashboard</a><i class="fa fa-circle"></i></li>
         <li><span>{{ $title }}</span></li>
      </ul>

      @if(Session::has('flash_message_success'))
      <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('flash_message_success') }}</div>
      @endif

      <div class="sdx">
         <div class="sdx-head">
            <div>
               <h1>Sample Dispatches</h1>
               <div class="sdx-meta">{{ $dispatches->total() }} dispatched line(s) across {{ $grouped->count() }} challan(s) on this page</div>
            </div>
            <div class="sdx-tools">
               <a class="sdx-btn ghost" href="{{ route('admin.sampling-requests.index', ['status' => 'approved']) }}"><i class="fa fa-list"></i> Pending Dispatch</a>
            </div>
         </div>

         <div class="sdx-card">
            <div class="sdx-cb">
               <form method="get" action="{{ route('admin.sample-dispatch.index') }}">
                  <div class="sdx-grid g4 sdx-filters">
                     <div class="sdx-f">
                        <label>Answerable To</label>
                        <select class="sdx-in" name="answerable_type">
                           <option value="">All</option>
                           <option value="user" {{ request('answerable_type') === 'user' ? 'selected' : '' }}>Employee</option>
                           <option value="dealer" {{ request('answerable_type') === 'dealer' ? 'selected' : '' }}>Dealer</option>
                        </select>
                     </div>
                     <div class="sdx-f">
                        <label>Employee</label>
                        <select class="sdx-in sdx-s2" name="user_id">
                           <option value="">All</option>
                           @foreach($employees as $employee)
                           <option value="{{ $employee->id }}" {{ request('user_id') == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                           @endforeach
                        </select>
                     </div>
                     <div class="sdx-f">
                        <label>Dealer</label>
                        <select class="sdx-in sdx-s2" name="dealer_id">
                           <option value="">All</option>
                           @foreach($dealers as $dealer)
                           <option value="{{ $dealer->id }}" {{ request('dealer_id') == $dealer->id ? 'selected' : '' }}>{{ $dealer->business_name ?: $dealer->name }}</option>
                           @endforeach
                        </select>
                     </div>
                     <div class="sdx-f">
                        <label>Dispatch Type</label>
                        <select class="sdx-in" name="dispatch_type">
                           <option value="">All</option>
                           <option value="request" {{ request('dispatch_type') === 'request' ? 'selected' : '' }}>Against Request</option>
                           <option value="voluntary" {{ request('dispatch_type') === 'voluntary' ? 'selected' : '' }}>Voluntary</option>
                        </select>
                     </div>
                     {{-- Receipt is a flag on the dispatched line, not a request
                          status — the same way Readiness sits inside approved. --}}
                     <div class="sdx-f">
                        <label>Receipt</label>
                        <select class="sdx-in" name="receipt">
                           <option value="">All</option>
                           <option value="received" {{ request('receipt') === 'received' ? 'selected' : '' }}>Received</option>
                           <option value="not_received" {{ request('receipt') === 'not_received' ? 'selected' : '' }}>Not received</option>
                        </select>
                     </div>
                     <div class="sdx-f">
                        <label>From Date</label>
                        <input type="date" class="sdx-in data" name="from_date" value="{{ request('from_date') }}">
                     </div>
                     <div class="sdx-f">
                        <label>To Date</label>
                        <input type="date" class="sdx-in data" name="to_date" value="{{ request('to_date') }}">
                     </div>
                     <div class="sdx-f">
                        <label>Search</label>
                        <input class="sdx-in" name="keyword" value="{{ request('keyword') }}" placeholder="Dispatch / Challan / LR / Batch / SR ref">
                     </div>
                     <div class="sdx-f act">
                        <label>&nbsp;</label>
                        <div class="btns">
                           <button class="sdx-btn g sm" type="submit"><i class="fa fa-search"></i> Filter</button>
                           <a class="sdx-btn ghost sm" href="{{ route('admin.sample-dispatch.index') }}">Reset</a>
                        </div>
                     </div>
                  </div>
               </form>
            </div>
         </div>

         @if($dispatches->isEmpty())
         <div class="sdx-card"><div class="sdx-cb">
            <div class="sdx-empty"><i class="fa fa-truck"></i>No dispatches found.</div>
         </div></div>
         @else
            @foreach($grouped as $refNo => $lines)
            @php $head = $lines->first(); @endphp
            <div class="sdx-card">
               <div class="sdx-ch">
                  <div class="t">
                     <span class="ix"><i class="fa fa-truck"></i></span>
                     <div>{{ $refNo }}
                        <small>
                           {{ $head->dispatch_date ? $head->dispatch_date->format('d M Y') : '' }}
                           &middot; Challan {{ $head->invoice_no ?: '—' }}
                           @if($head->invoice_date) ({{ $head->invoice_date->format('d M Y') }}) @endif
                           &middot; {{ $head->transport ?: '—' }} &middot; LR {{ $head->lr_no ?: '—' }}
                        </small>
                     </div>
                  </div>
                  @php
                     $voluntaryCount = $lines->filter(function ($l) { return optional($l->sampling_request)->is_voluntarily; })->count();
                  @endphp
                  <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                     @if($voluntaryCount === $lines->count())
                     <span class="sdx-chip vol"><i class="fa fa-paper-plane-o"></i> Voluntary</span>
                     @elseif($voluntaryCount === 0)
                     <span class="sdx-chip req"><i class="fa fa-file-text-o"></i> Against Request</span>
                     @else
                     <span class="sdx-chip warn">Mixed — {{ $voluntaryCount }} voluntary</span>
                     @endif
                     <span class="sdx-chip mute">{{ $lines->count() }} line(s) &middot; {{ $lines->sum('dispatch_qty') }} Kg</span>
                     @php $receivedCount = $lines->where('is_received', true)->count(); @endphp
                     @if($receivedCount === $lines->count())
                     <span class="sdx-chip ok"><i class="fa fa-check"></i> Received</span>
                     @elseif($receivedCount === 0)
                     <span class="sdx-chip warn"><i class="fa fa-truck"></i> Not received</span>
                     @else
                     <span class="sdx-chip warn">{{ $receivedCount }} of {{ $lines->count() }} received</span>
                     @endif
                  </div>
               </div>
               <div class="sdx-cb">
                  <div class="sdx-scroll">
                     <table class="sdx-table fit">
                        <thead>
                           <tr>
                              <th>Request</th>
                              <th>Accountable Person</th>
                              <th>Product</th>
                              <th class="num">Qty</th>
                              <th>Batch</th>
                              <th>Sent To</th>
                              <th>Address</th>
                              <th>Dispatched By</th>
                              <th>Receipt</th>
                           </tr>
                        </thead>
                        <tbody>
                           @foreach($lines as $line)
                           <tr>
                              <td>
                                 @if($line->sampling_request)
                                 <a class="pn num" href="{{ route('admin.sampling-requests.show', $line->sampling_request_id) }}">{{ $line->sampling_request->reference_no }}</a>
                                 <div style="margin-top:4px">
                                    @if($line->sampling_request->is_voluntarily)
                                    <span class="sdx-chip vol">Voluntary</span>
                                    @else
                                    <span class="sdx-chip req">Request</span>
                                    @endif
                                 </div>
                                 @else
                                 <span class="pc">—</span>
                                 @endif
                              </td>
                              <td>
                                 @php $req = $line->sampling_request; @endphp
                                 {{ $req ? ($req->ownerName() ?: '—') : '—' }}
                                 @if($req)
                                 <div class="pc">{{ $req->ownerType() }}</div>
                                 @endif
                              </td>
                              <td>
                                 <div class="pn">{{ optional($line->product)->product_name ?: '—' }}</div>
                                 <div class="pc">{{ optional($line->product)->product_code }}</div>
                              </td>
                              <td class="num"><b>{{ $line->dispatch_qty }} Kg</b></td>
                              <td class="num">{{ $line->batch_no ?: '—' }}</td>
                              <td>
                                 {{ $line->dispatchPartyName() ?: '—' }}
                                 <div class="pc">{{ $line->dispatch_to }}</div>
                              </td>
                              <td class="pc" style="white-space:pre-line;max-width:170px">{{ $line->dispatch_address ?: '—' }}</td>
                              <td>{{ optional($line->dispatched_by)->name ?: '—' }}</td>
                              <td>
                                 @if($line->is_received)
                                 <span class="sdx-chip ok"><i class="fa fa-check"></i> Received</span>
                                 <div class="pc" style="margin-top:4px">{{ $line->received_at ? $line->received_at->format('d M Y, h:i A') : '' }}</div>
                                 @else
                                 <span class="sdx-chip warn"><i class="fa fa-truck"></i> Not received</span>
                                 @endif
                              </td>
                           </tr>
                           @endforeach
                        </tbody>
                     </table>
                  </div>
                  @if($head->remarks)
                  <div class="sdx-hint" style="margin-top:10px"><i class="fa fa-comment-o"></i> {{ $head->remarks }}</div>
                  @endif
               </div>
            </div>
            @endforeach

            <div style="margin-top:4px">{{ $dispatches->links() }}</div>
         @endif
      </div>
   </div>
</div>

<script>
$(function () {
   $('.sdx-s2').select2({ width: '100%' });
});
</script>
@endsection
