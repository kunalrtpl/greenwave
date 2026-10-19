{{-- The listing itself, swapped in on its own whenever a filter changes. --}}
@php
   $chipClass = ['pending' => 'warn', 'approved' => 'req', 'closed' => 'ok', 'declined' => 'bad'];
   $kg = function ($v) { return $v === null ? '—' : rtrim(rtrim(number_format($v, 3, '.', ''), '0'), '.') . ' Kg'; };
   $nq = function ($v) { return $v === null ? '' : rtrim(rtrim(number_format($v, 3, '.', ''), '0'), '.'); };
   $labels = ['all' => '', 'pending' => 'pending ', 'approved' => 'approved ', 'closed' => 'closed ', 'declined' => 'declined '];
@endphp
<div data-total="{{ $requests->total() }}">
         <div class="sdx-card">
            <div class="sdx-cb">
               @if($requests->isEmpty())
               <div class="sdx-empty"><i class="fa fa-inbox"></i>No {{ $labels[$status] }}requests found.</div>
               @else
               <div class="sdx-scroll">
                  {{-- `fit` keeps the whole listing inside the page: tighter
                       cells, short qty headers and no column demanding more
                       width than its content needs, so no sideways scroll. --}}
                  <table class="sdx-table fit">
                     <thead>
                        <tr>
                           <th style="width:92px">Ref No</th>
                           <th>Raised By</th>
                           <th>Customer</th>
                           <th>Product</th>
                           <th class="num" title="Requested Qty">Req.</th>
                           <th class="num" title="Approved Qty">Appr.</th>
                           <th class="num" title="Dispatched Qty">Disp.</th>
                           <th class="num" title="Pending Qty">Pend.</th>
                           <th>Status</th>
                           <th class="center">Query</th>
                           <th class="center" style="width:58px"></th>
                        </tr>
                     </thead>
                     <tbody>
                        @foreach($requests as $req)
                        <tr>
                           <td>
                              <div class="pn num">{{ $req->reference_no }}</div>
                              <div class="pc">{{ $req->request_date ? $req->request_date->format('d M Y') : '' }}</div>
                              @if($req->is_voluntarily)
                              <span class="sdx-chip vol">Voluntary</span>
                              @endif
                           </td>
                           <td>{{ optional($req->user)->name ?: '—' }}</td>
                           <td>{{ optional($req->customer)->name ?: '—' }}</td>
                           <td>
                              <div class="pn">{{ optional($req->product)->product_name ?: '—' }}</div>
                              <div class="pc">
                                 {{ optional($req->product)->product_code }}
                                 @if($req->approved_pack_size)
                                 &middot; {{ $req->approved_no_of_packs }} × {{ $nq($req->approved_pack_size) }} Kg
                                 @elseif($req->pack_size)
                                 &middot; {{ $req->no_of_packs }} × {{ $nq($req->pack_size) }} Kg
                                 @endif
                              </div>
                              @if($req->requested_product_id && $req->requested_product_id != $req->product_id)
                              <div class="pc" style="margin-top:3px"><i class="fa fa-exchange"></i> asked for {{ optional($req->requested_product)->product_name }}</div>
                              @endif
                           </td>
                           <td class="num">{{ $kg($req->requested_qty) }}</td>
                           <td class="num">{{ $kg($req->approved_qty) }}</td>
                           <td class="num">{{ $kg($req->total_dispatched_qty) }}</td>
                           <td class="num">
                              @if($req->status === 'approved' && $req->pending_qty > 0)
                              <b style="color:#9a6a12">{{ $kg($req->pending_qty) }}</b>
                              @else
                              {{ $kg($req->pending_qty) }}
                              @endif
                           </td>
                           <td>
                              <span class="sdx-chip {{ $chipClass[$req->status] }}" title="{{ $req->status_label }}">{{ $req->status_short }}</span>
                              @if($req->status === 'approved')
                                 @if($req->is_ready)
                                 <div style="margin-top:5px"><span class="sdx-chip ok"><i class="fa fa-check"></i> Ready</span></div>
                                 @else
                                 <div style="margin-top:5px"><span class="sdx-chip mute">Not ready</span></div>
                                 @endif
                              @elseif($req->status === 'declined' && $req->decline_reason)
                                 <div class="pc" style="margin-top:4px">{{ $req->decline_reason }}</div>
                              @endif
                           </td>
                           {{-- Query thread. Always openable so closed and declined
                                requests can still be read back; the modal itself
                                hides the composer when posting is shut. --}}
                           @php
                              $qTotal  = isset($queryCounts[$req->id]) ? $queryCounts[$req->id] : 0;
                              $qUnread = isset($queryUnread[$req->id]) ? $queryUnread[$req->id] : 0;
                           @endphp
                           <td class="center">
                              <button type="button"
                                      class="sdx-qbtn sq-open {{ $qUnread > 0 ? 'unread' : '' }}"
                                      data-id="{{ $req->id }}"
                                      data-ref="{{ $req->reference_no }}"
                                      title="{{ $qUnread > 0 ? $qUnread . ' unread' : 'Query thread' }}">
                                 <i class="fa fa-comments-o"></i>
                                 <span class="n">{{ $qUnread > 0 ? $qUnread : $qTotal }}</span>
                              </button>
                           </td>
                           <td class="center">
                              <a class="sdx-btn ghost sm" title="View details"
                                 href="{{ route('admin.sampling-requests.show', $req->id) }}"><i class="fa fa-eye"></i></a>
                           </td>
                        </tr>
                        @endforeach
                     </tbody>
                  </table>
               </div>
               <div style="margin-top:14px">{{ $requests->links() }}</div>
               @endif
            </div>
         </div>
</div>
