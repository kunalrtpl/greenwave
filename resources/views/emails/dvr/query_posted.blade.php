{{--
   One template for both directions. $audience says who is reading it:
   'employee' an admin has raised a query on the employee's DVR
   'admin'    the employee has responded; sent to every admin on the thread
--}}
@php
   $fromAdmin = $query->sender_type === 'admin';
   $employee  = $dvr->user;
@endphp
@extends('emails.sampling.layout')

@section('title', 'CVR Query')
@section('heading', $fromAdmin ? 'Query Raised on Your CVR' : 'Query Responded on a CVR')
@section('ribbon', $dvrRef . ' · ' . $threadCount . ' message(s)')
@section('ribbon_badge')<span class="ribbon-badge">{{ $fromAdmin ? 'Your Response Needed' : 'Query Responded' }}</span>@endsection

@section('greeting', 'Hi ' . ($audience === 'employee' ? (optional($employee)->name ?: 'there') : 'there') . ',')
@section('intro')
   @if($fromAdmin)
      <strong>{{ optional($query->sender)->name ?: 'The admin' }}</strong> has raised a query on your
      CVR for <strong>{{ $customer ?: 'a customer' }}</strong>. Please respond to it from the app.
   @else
      <strong>{{ optional($query->sender)->name ?: optional($employee)->name ?: 'The employee' }}</strong>
      has responded to the query on their CVR for <strong>{{ $customer ?: 'a customer' }}</strong>.
   @endif
@endsection

@section('content')
<tr>
   <td class="section">
      <div class="section-label">{{ $fromAdmin ? 'The Query' : 'The Response' }}</div>
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
             style="background-color:#f1f8e9; border:1px solid #c5e1a5; border-left:5px solid #558b2f; border-radius:10px;">
         <tr>
            <td style="padding:16px 18px;">
               <div style="font-size:11px; color:#7cb342; font-weight:700; text-transform:uppercase; margin-bottom:8px;">
                  {{ optional($query->sender)->name ?: ($fromAdmin ? 'Admin' : 'Employee') }}
                  · {{ $query->created_at ? $query->created_at->format('d M Y, h:i A') : '' }}
               </div>
               <div style="font-size:13.5px; color:#33691e; line-height:1.7;">{!! nl2br(e($query->message)) !!}</div>
            </td>
         </tr>
      </table>
   </td>
</tr>

<tr>
   <td class="section">
      <div class="section-label">CVR Details</div>
      <table role="presentation" class="info-table">
         <tr>
            <td class="info-row-label">CVR</td>
            <td class="info-row-value"><strong>#{{ $dvr->id }}</strong> &middot; {{ $dvr->dvr_status }}</td>
         </tr>
         <tr>
            <td class="info-row-label">Visit Date</td>
            <td class="info-row-value">{{ $dvr->dvr_date ? \Carbon\Carbon::parse($dvr->dvr_date)->format('d M Y (l)') : '—' }}</td>
         </tr>
         <tr>
            <td class="info-row-label">Employee</td>
            <td class="info-row-value">{{ optional($employee)->name ?: '—' }}
               @if(optional($employee)->mobile)<br>{{ $employee->mobile }}@endif
            </td>
         </tr>
         <tr>
            <td class="info-row-label">Customer</td>
            <td class="info-row-value">{{ $customer ?: '—' }}</td>
         </tr>
         @if($dvr->start_time || $dvr->end_time)
         <tr>
            <td class="info-row-label">Check-In / Out</td>
            <td class="info-row-value">
               {{ $dvr->start_time ? \Carbon\Carbon::parse($dvr->start_time)->format('h:i A') : '—' }}
               &ndash;
               {{ $dvr->end_time ? \Carbon\Carbon::parse($dvr->end_time)->format('h:i A') : '—' }}
            </td>
         </tr>
         @endif
         @if($dvr->visit_detail)
         <tr>
            <td class="info-row-label">Visit Details</td>
            <td class="info-row-value">{!! nl2br(e($dvr->visit_detail)) !!}</td>
         </tr>
         @endif
      </table>
   </td>
</tr>

{{-- The whole conversation so far, so it can be read without opening the panel. --}}
@if($thread->count() > 1)
<tr>
   <td class="section">
      <div class="section-label">Conversation So Far</div>
      <table role="presentation" class="info-table">
         @foreach($thread as $m)
         <tr>
            <td class="info-row-label" style="white-space:nowrap;">
               {{ optional($m->sender)->name ?: ($m->sender_type === 'admin' ? 'Admin' : 'Employee') }}
               <div style="font-size:10px; color:#9e9e9e; font-weight:400;">{{ $m->created_at ? $m->created_at->format('d M, h:i A') : '' }}</div>
            </td>
            <td class="info-row-value">{!! nl2br(e($m->message)) !!}</td>
         </tr>
         @endforeach
      </table>
   </td>
</tr>
@endif

<tr><td style="height:28px; line-height:28px; font-size:0;">&nbsp;</td></tr>
@endsection

{{-- Only the admins' copy gets a button: the employee answers from the app. --}}
@if($audience === 'admin')
@section('cta_text', 'Open the CVR in the admin panel to reply or verify it.')
@section('cta')
   <a href="{{ $panelUrl }}" style="display:inline-block; padding:13px 30px; font-size:14px; font-weight:700; color:#ffffff; text-decoration:none;">Open CVR #{{ $dvr->id }}</a>
@endsection
@endif
