@extends('layouts.adminLayout.backendLayout')
@section('content')
<style>
   #prList{--ink:#14241c;--ink2:#3a4a42;--muted:#7b8a82;--line:#e6ebe8;--line2:#eef2f0;--g:#1f7a44;--gd:#155e34;--gsoft:#e8f3ec;--amber:#9a6a12;--amberS:#fdf4e3;--red:#c0392b;--redS:#fdecea;--r:11px;--rs:8px;--mono:'JetBrains Mono',Menlo,Consolas,monospace;font-family:'Inter','Segoe UI',system-ui,sans-serif;color:var(--ink)}
   #prList .cpx-head{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:16px}
   #prList .cpx-head h1{font-size:21px;font-weight:800;margin:0;letter-spacing:-.3px}
   #prList .cpx-card{background:#fff;border:1px solid var(--line);border-radius:var(--r);box-shadow:0 1px 2px rgba(20,36,28,.04),0 6px 18px rgba(20,36,28,.04);overflow:hidden}
   #prList .tabs{display:flex;gap:6px;padding:14px 18px;border-bottom:1px solid var(--line2);flex-wrap:wrap;align-items:center}
   #prList .tabs a{padding:7px 15px;border-radius:8px;font-size:13px;font-weight:600;color:var(--ink2);text-decoration:none;border:1.4px solid var(--line)}
   #prList .tabs a.on{background:var(--gsoft);color:var(--gd);border-color:#cfe6d8}
   #prList .tabs a .n{font-family:var(--mono);font-size:11px;background:#fff;border-radius:20px;padding:1px 7px;margin-left:5px;border:1px solid var(--line)}
   #prList .tabs form{margin-left:auto;display:flex;gap:8px}
   #prList .tabs input{height:34px;border:1.4px solid var(--line);border-radius:8px;padding:0 11px;font-size:13px}
   #prList table{width:100%;border-collapse:collapse;font-size:13px}
   #prList th{font-size:11px;font-weight:700;letter-spacing:.4px;text-transform:uppercase;color:var(--muted);text-align:left;padding:11px 14px;border-bottom:1px solid var(--line2);background:#fbfcfb}
   #prList td{padding:11px 14px;border-bottom:1px solid var(--line2);vertical-align:middle}
   #prList tr:hover td{background:#fbfcfb}
   #prList .mono{font-family:var(--mono);font-size:12.5px}
   #prList .chip{display:inline-flex;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:.3px}
   #prList .chip.P{background:var(--amberS);color:var(--amber)}
   #prList .chip.A{background:var(--gsoft);color:var(--gd)}
   #prList .chip.R{background:var(--redS);color:var(--red)}
   #prList .neg{color:var(--red);font-weight:700}
   #prList .pos{color:var(--gd);font-weight:700}
   #prList .btnv{display:inline-flex;align-items:center;gap:6px;padding:6px 13px;border-radius:8px;font-size:12.5px;font-weight:600;background:var(--g);color:#fff;text-decoration:none}
   #prList .btnv:hover{background:var(--gd);color:#fff}
   #prList .empty{text-align:center;padding:44px;color:var(--muted)}
   #prList .pager{padding:13px 18px}
</style>
<div class="page-content-wrapper">
   <div class="page-content">
      <ul class="page-breadcrumb breadcrumb" style="margin-bottom:14px;">
         <li><a href="{{ url('admin/dashboard') }}">Dashboard</a><i class="fa fa-circle"></i></li>
         <li><span>Price Requests</span></li>
      </ul>
      @if(Session::has('flash_message_success'))
      <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{!! session('flash_message_success') !!}</div>
      @endif
      @if(Session::has('flash_message_error'))
      <div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{!! session('flash_message_error') !!}</div>
      @endif
      <div id="prList">
         <div class="cpx-head"><h1>Executive Price Requests</h1></div>
         <div class="cpx-card">
            <div class="tabs">
               @foreach(['Pending','Approved','Rejected','All'] as $tab)
               <a href="{{ url('admin/price-requests?status='.$tab) }}" class="{{ $status==$tab ? 'on':'' }}">
                  {{ $tab }}
                  @if($tab!='All')<span class="n">{{ $counts[$tab] ?? 0 }}</span>@endif
               </a>
               @endforeach
               <form method="get" action="{{ url('admin/price-requests') }}">
                  <input type="hidden" name="status" value="{{ $status }}">
                  <input type="text" name="customer" value="{{ request('customer') }}" placeholder="Search customer…">
               </form>
            </div>
            <div style="overflow-x:auto">
               <table>
                  <thead>
                     <tr>
                        <th>#</th><th>Date</th><th>Executive</th><th>Customer</th><th>Product</th>
                        <th>Packing</th><th style="text-align:right">Final Price</th><th style="text-align:right">MSP</th>
                        <th style="text-align:right">Realization</th><th>Status</th><th></th>
                     </tr>
                  </thead>
                  <tbody>
                     @forelse($requests as $pr)
                     <tr>
                        <td class="mono">PR-{{ $pr->id }}</td>
                        <td class="mono">{{ $pr->created_at->format('d M Y') }}</td>
                        <td>{{ $pr->user->name ?? '—' }}</td>
                        <td><b>{{ ucwords($pr->customer->name ?? '—') }}</b><br><small style="color:var(--muted)">{{ $pr->customer->business_model ?? '' }}</small></td>
                        <td>{{ $pr->product->product_name ?? '—' }}</td>
                        <td class="mono">{{ $pr->packing_size }}</td>
                        <td class="mono" style="text-align:right">₹ {{ number_format($pr->final_customer_price,2) }}</td>
                        <td class="mono" style="text-align:right">₹ {{ number_format($pr->final_msp,2) }}</td>
                        <td class="mono {{ $pr->additional_realization < 0 ? 'neg':'pos' }}" style="text-align:right">{{ number_format($pr->additional_realization,2) }}</td>
                        <td><span class="chip {{ substr($pr->status,0,1) }}">{{ $pr->status }}</span></td>
                        <td><a class="btnv" href="{{ url('admin/price-requests/'.$pr->id) }}"><i class="fa fa-eye"></i> Review</a></td>
                     </tr>
                     @empty
                     <tr><td colspan="11" class="empty">No {{ strtolower($status) }} price requests.</td></tr>
                     @endforelse
                  </tbody>
               </table>
            </div>
            <div class="pager">{{ $requests->links() }}</div>
         </div>
      </div>
   </div>
</div>
@endsection
