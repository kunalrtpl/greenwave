@extends('layouts.adminLayout.backendLayout')
@section('content')
{{-- ════════════════════════════════════════════════════════════════════
SAMPLE REQUESTS — one screen for all four statuses (?status=), so the sidebar
can point four separately permissionable modules at this same route.

One product is one request: an API post carrying three products is split into
three independent requests (SR-1.1, SR-1.2, SR-1.3), each listed on its own
row and approved, declined and dispatched on its own.
═══════════════════════════════════════════════════════════════════════ --}}
@include('admin.sampling.partials.styles')
@php
   $tabs = [
       'all'      => ['label' => 'All',                    'icon' => 'fa-list'],
       'pending'  => ['label' => 'Pending',                'icon' => 'fa-hourglass-half'],
       'approved' => ['label' => 'Approved', 'icon' => 'fa-check'],
       'closed'   => ['label' => 'Closed',                 'icon' => 'fa-archive'],
       'declined' => ['label' => 'Declined',               'icon' => 'fa-ban'],
   ];
   $chipClass = ['pending' => 'warn', 'approved' => 'req', 'closed' => 'ok', 'declined' => 'bad'];
   $kg = function ($v) { return $v === null ? '—' : rtrim(rtrim(number_format($v, 3, '.', ''), '0'), '.') . ' Kg'; };
   $nq = function ($v) { return $v === null ? '' : rtrim(rtrim(number_format($v, 3, '.', ''), '0'), '.'); };
@endphp
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
               <h1>{{ $title }}</h1>
               <div class="sdx-meta"><span id="srTotal">{{ $requests->total() }}</span> request(s) &middot; one product per request</div>
            </div>
            <div class="sdx-tools">
               @if(in_array($status, ['approved', 'all']))
               <a class="sdx-btn g" href="{{ route('admin.sample-dispatch.create') }}"><i class="fa fa-truck"></i> Dispatch Samples</a>
               @endif
               <a class="sdx-btn ghost" href="{{ route('admin.sample-dispatch.index') }}"><i class="fa fa-list"></i> Dispatch History</a>
            </div>
         </div>

         {{-- ── Filters ─────────────────────────────────────────── --}}
         <div class="sdx-card">
            <div class="sdx-cb">
               <form method="get" action="{{ App\Http\Controllers\Admin\SamplingRequestController::statusUrl($pageStatus) }}" id="srFilters">
                  <div class="sdx-filterbar">
                     <div class="sdx-f">
                        <label>Employee</label>
                        <select class="sdx-in sdx-s2 sdx-auto" name="user_id">
                           <option value="">All</option>
                           @foreach($employees as $employee)
                           <option value="{{ $employee->id }}" {{ request('user_id') == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                           @endforeach
                        </select>
                     </div>
                     <div class="sdx-f">
                        <label>Product</label>
                        <select class="sdx-in sdx-s2 sdx-auto" name="product_id">
                           <option value="">All</option>
                           @foreach($products as $product)
                           <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>{{ $product->product_name }}</option>
                           @endforeach
                        </select>
                     </div>

                     {{-- Status is only worth filtering on the combined screen;
                          the others are already a single status. --}}
                     <input type="hidden" name="status" id="srStatus" value="{{ $status }}">
                     @if($pageStatus === 'all')
                     <div class="sdx-f badges">
                        <label>Status</label>
                        <div class="sdx-badges">
                           @foreach($tabs as $key => $tab)
                           <a href="javascript:;" data-status="{{ $key }}"
                              class="sdx-badge {{ $status === $key ? 'on' : '' }} {{ $key }}">
                              {{ $tab['label'] }} <span class="n">{{ $counts[$key] }}</span>
                           </a>
                           @endforeach
                        </div>
                     </div>
                     @endif
                     <div class="sdx-f {{ $status === 'approved' ? '' : 'sdx-hide' }}" id="srReadinessWrap">
                        <label>Readiness</label>
                        <select class="sdx-in sdx-auto" name="readiness">
                           <option value="">All</option>
                           <option value="ready" {{ request('readiness') === 'ready' ? 'selected' : '' }}>Ready</option>
                           <option value="not_ready" {{ request('readiness') === 'not_ready' ? 'selected' : '' }}>Not ready yet</option>
                        </select>
                     </div>

                     <div class="sdx-f search">
                        <label>Search</label>
                        <div class="sdx-search">
                           <input class="sdx-in" name="keyword" value="{{ request('keyword') }}" placeholder="SR ref no / customer / employee">
                           <button class="sdx-btn g" type="submit"><i class="fa fa-search"></i> Search</button>
                           @if(request()->hasAny(['user_id', 'product_id', 'readiness', 'keyword']))
                           <a class="sdx-btn ghost" href="{{ App\Http\Controllers\Admin\SamplingRequestController::statusUrl($pageStatus) }}">Reset</a>
                           @endif
                        </div>
                     </div>
                  </div>
               </form>
            </div>
         </div>

         {{-- ── Listing (swapped in by AJAX on every filter change) ── --}}
         <div id="srList">
            @include('admin.sampling_requests.partials.table')
         </div>

      </div>
   </div>
</div>

{{-- ════════════ Query thread ════════════ --}}
<div class="sdx-modal" id="sqModal">
   <div class="box sdx" style="max-width:620px">
      <div class="h">Query — <span id="sqRef"></span> <span class="x" data-sq-close>&times;</span></div>
      <div class="b">
         <div class="sdx-alert" id="sqAlert"></div>
         <div class="sq-thread" id="sqThread"></div>

         <div id="sqComposer" style="margin-top:16px">
            <div class="sdx-f">
               <label>Write a Reply</label>
               <textarea class="sdx-in" id="sqMessage" placeholder="Type your reply… (Ctrl+Enter to send)"></textarea>
               <div class="sdx-err" data-err="message"></div>
            </div>
         </div>
         <div class="sdx-note" id="sqClosed" style="display:none;margin-top:16px"></div>
      </div>
      <div class="f">
         <button class="sdx-btn ghost" data-sq-close>Close</button>
         <button class="sdx-btn g" id="sqSend"><i class="fa fa-paper-plane"></i> Send</button>
      </div>
   </div>
</div>

<script>
$(function () {
   $('.sdx-s2').select2({ width: '100%' });

   var BASE = @json(App\Http\Controllers\Admin\SamplingRequestController::statusUrl($pageStatus));

   /**
    * Every filter applies over AJAX: only the listing is swapped, so the page
    * does not jump and the URL stays the clean one the menu linked to.
    */
   function reload(url) {
      $('.loadingDiv').show();
      $.get(url || BASE, $('#srFilters').serialize(), function (html) {
         var $html = $(html);
         $('#srList').html($html);
         $('#srTotal').text($html.filter('[data-total]').data('total'));
         $('.loadingDiv').hide();
      }).fail(function () {
         $('.loadingDiv').hide();
         $('#srList').html('<div class="sdx-card"><div class="sdx-cb"><div class="sdx-empty">'
            + '<i class="fa fa-exclamation-triangle"></i>Could not load the list. Please try again.</div></div></div>');
      });
   }

   $('#srFilters').on('change', '.sdx-auto', function () { reload(); });

   // Status badges behave like any other filter.
   $('#srFilters').on('click', '.sdx-badge', function () {
      var status = $(this).data('status');
      $('#srStatus').val(status);
      $(this).closest('.sdx-badges').find('.sdx-badge').removeClass('on');
      $(this).addClass('on');

      // Readiness only means anything while looking at approved requests.
      $('#srReadinessWrap').toggleClass('sdx-hide', status !== 'approved');
      if (status !== 'approved') { $('#srReadinessWrap select').val(''); }

      reload();
   });

   // The search box waits for its button (or Enter).
   $('#srFilters').on('submit', function (e) { e.preventDefault(); reload(); });

   // Paginate without leaving the page either.
   $('#srList').on('click', '.pagination a', function (e) {
      e.preventDefault();
      reload($(this).attr('href'));
   });

   /* ══════════════ Query thread ══════════════ */
   var BASE_Q    = @json(url('admin/sampling-requests'));
   var sqId      = null;
   var sqCanPost = false;

   function esc(v){ return $('<div>').text(v == null ? '' : v).html(); }

   function renderThread(queries) {
      if (!queries.length) {
         $('#sqThread').html('<div class="sq-empty"><i class="fa fa-comments-o" style="display:block;font-size:24px;margin-bottom:8px;opacity:.5"></i>'
            + 'No messages yet. Start the conversation below.</div>');
         return;
      }
      var html = '';
      $.each(queries, function (_, q) {
         html += '<div class="sq-msg ' + q.sender_type + '">'
              + '<div class="who">' + esc(q.sender_name || (q.sender_type === 'admin' ? 'Admin' : 'Employee')) + '</div>'
              + esc(q.message)
              + '<div class="when">' + esc(q.created_at) + '</div>'
              + '</div>';
      });
      $('#sqThread').html(html);
      $('#sqThread').scrollTop($('#sqThread')[0].scrollHeight);
   }

   /** Closed and declined requests keep their history but lose the composer. */
   function applyCanPost(canPost, reason) {
      sqCanPost = canPost;
      $('#sqComposer').toggle(canPost);
      $('#sqSend').toggle(canPost);
      $('#sqClosed').toggle(!canPost).text(reason || '');
   }

   $('#srList').on('click', '.sq-open', function () {
      sqId = $(this).data('id');
      var $btn = $(this);

      $('#sqRef').text($(this).data('ref'));
      $('#sqAlert').hide().empty();
      $('#sqMessage').val('');
      $('#sqThread').html('<div class="sq-empty">Loading…</div>');
      $('#sqModal').addClass('on');

      $.get(BASE_Q + '/' + sqId + '/queries', function (res) {
         renderThread(res.queries);
         applyCanPost(res.can_post, res.closed_reason);
         // Opening the thread has cleared the unread flags server side.
         $btn.removeClass('unread').find('.n').text(res.total);
      }).fail(function () {
         $('#sqThread').html('<div class="sq-empty">Could not load the thread. Please try again.</div>');
      });
   });

   $('#sqSend').on('click', function () {
      if (!sqCanPost) { return; }
      var message = $.trim($('#sqMessage').val());
      if (!message) { $('#sqMessage').focus(); return; }

      var $btn = $(this).prop('disabled', true);
      $('#sqAlert').hide().empty();

      $.ajax({
         url: BASE_Q + '/' + sqId + '/raise-query',
         type: 'POST',
         data: { _token: @json(csrf_token()), message: message },
         headers: { 'Accept': 'application/json' },
         success: function (res) {
            $('#sqMessage').val('');
            $.get(BASE_Q + '/' + sqId + '/queries', function (r) { renderThread(r.queries); });
            $('#srList').find('.sq-open[data-id="' + sqId + '"] .n').text(res.total);
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

   $('[data-sq-close]').on('click', function () { $('#sqModal').removeClass('on'); });
   $('#sqModal').on('click', function (e) { if (e.target === this) { $(this).removeClass('on'); } });
});
</script>
@endsection
