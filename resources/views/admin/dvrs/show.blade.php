@extends('layouts.adminLayout.backendLayout')

@section('content')
<div class="page-content-wrapper">
<div class="page-content">

{{-- ==================== BREADCRUMB ==================== --}}
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li><a href="{{ url('admin/dashboard') }}"><i class="fa fa-home"></i> Home</a><i class="fa fa-angle-right"></i></li>
        <li><a href="{{ url('admin/dvrs') }}">Daily Visit Reports</a><i class="fa fa-angle-right"></i></li>
        <li><a href="#">DVR #{{ $dvr->id }}</a></li>
    </ul>
</div>

@php
    $customerName = $dvr->customer
        ? $dvr->customer->name
        : optional($dvr->customer_register_request)->name;

    // Status calculations
    $entryStatus = ($dvr->visit_recorded === 'On Site') ? 'Real Time Entry' : 'Post Visit Entry';
    $entryClass  = ($dvr->visit_recorded === 'On Site') ? 'label-success' : 'label-warning';

    $siteStatus = $dvr->site_type ?? '—';
    $siteClass  = ($dvr->site_type === 'On Site') ? 'label-info' : 'label-default';

    $metStatus = $dvr->have_you_met ? 'Met Customer' : 'No Meeting';
    $metClass  = $dvr->have_you_met ? 'label-success' : 'label-danger';

    $detailStatus = $dvr->is_submitted ? 'Visit Detail Added' : 'Visit Detail Pending';
    $detailClass  = $dvr->is_submitted ? 'label-success' : 'label-warning';

    $verifyStatus = $dvr->dvr_verified_date_time ? 'Verified' : 'Not Verified';
    $verifyClass  = $dvr->dvr_verified_date_time ? 'label-success' : 'label-warning';

    $visitTypeClass = ($dvr->visit_type === 'Official') ? 'label-primary' : 'label-default';

    // Work hours
    $workHours = '—';
    if ($attendance && $attendance->in_time && $attendance->out_time) {
        $in  = \Carbon\Carbon::parse($attendance->in_time);
        $out = \Carbon\Carbon::parse($attendance->out_time);
        $d   = $in->diff($out);
        $workHours = $d->h . 'h ' . $d->i . 'm';
    }
@endphp

{{-- ==================== DVR HEADER ==================== --}}
<div class="portlet light bordered" style="border-top: 4px solid #2980b9;">
    <div class="portlet-body">
        <div class="row">
            <div class="col-md-8">
                <h3 style="margin:0; font-weight:700; color:#2c3e50;">
                    <i class="fa fa-map-marker text-primary"></i>
                    {{ $customerName ?? 'Customer N/A' }}
                </h3>
                <p class="text-muted" style="margin:5px 0 0;">
                    DVR #{{ $dvr->id }} &nbsp;|&nbsp;
                    {{ \Carbon\Carbon::parse($dvr->dvr_date)->format('l, d M Y') }}
                    &nbsp;|&nbsp; By: <strong>{{ optional($dvr->user)->name }}</strong>
                </p>
                <div style="margin-top:10px;">
                    <span class="label label-lg {{ $visitTypeClass }}">{{ $dvr->visit_type ?? 'Official' }}</span>
                    <span class="label label-lg {{ $entryClass }}">{{ $entryStatus }}</span>
                    <span class="label label-lg {{ $siteClass }}">{{ $siteStatus }}</span>
                    <span class="label label-lg {{ $metClass }}">{{ $metStatus }}</span>
                    <span class="label label-lg {{ $detailClass }}">{{ $detailStatus }}</span>
                    <span class="label label-lg {{ $verifyClass }}">{{ $verifyStatus }}</span>
                </div>
            </div>
            <div class="col-md-4 text-right">
                <a href="{{ url('admin/dvrs') }}" class="btn btn-default">
                    <i class="fa fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">

{{-- ==================== LEFT COLUMN ==================== --}}
<div class="col-md-8">

    {{-- VISIT INFO --}}
    <div class="portlet light bordered">
        <div class="portlet-title">
            <div class="caption"><i class="fa fa-info-circle font-blue"></i>
                <span class="caption-subject font-blue bold uppercase">Visit Information</span>
            </div>
        </div>
        <div class="portlet-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-condensed dvr-info-table">
                        <tr>
                            <td class="dvr-info-label"><i class="fa fa-calendar"></i> DVR Date</td>
                            <td><strong>{{ \Carbon\Carbon::parse($dvr->dvr_date)->format('d M Y') }}</strong></td>
                        </tr>
                        <tr>
                            <td class="dvr-info-label"><i class="fa fa-clock-o"></i> Check In</td>
                            <td>{{ $dvr->start_time ? \Carbon\Carbon::parse($dvr->start_time)->format('h:i A') : '—' }}</td>
                        </tr>
                        <tr>
                            <td class="dvr-info-label"><i class="fa fa-clock-o"></i> Check Out</td>
                            <td>{{ $dvr->end_time ? \Carbon\Carbon::parse($dvr->end_time)->format('h:i A') : '—' }}</td>
                        </tr>
                        <tr>
                            <td class="dvr-info-label"><i class="fa fa-map-pin"></i> Start Location</td>
                            <td><small>{{ $dvr->start_location ?? '—' }}</small></td>
                        </tr>
                        <tr>
                            <td class="dvr-info-label"><i class="fa fa-map-pin"></i> End Location</td>
                            <td><small>{{ $dvr->end_location ?? '—' }}</small></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-condensed dvr-info-table">
                        <tr>
                            <td class="dvr-info-label"><i class="fa fa-car"></i> Conveyance</td>
                            <td>{{ $dvr->conveyance_type ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="dvr-info-label"><i class="fa fa-check-square-o"></i> Visit Recorded</td>
                            <td>{{ $dvr->visit_recorded ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="dvr-info-label"><i class="fa fa-shield"></i> Site Type</td>
                            <td>{{ $dvr->site_type ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="dvr-info-label"><i class="fa fa-calendar-check-o"></i> Verified At</td>
                            <td>{{ $dvr->dvr_verified_date_time
                                ? \Carbon\Carbon::parse($dvr->dvr_verified_date_time)->format('d M Y h:i A')
                                : '<span class="label label-warning">Pending</span>' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($dvr->purpose_of_visit)
            <div class="dvr-section-box">
                <span class="dvr-section-label"><i class="fa fa-bullseye"></i> Purpose of Visit</span>
                <div style="margin-top:6px;">
                    @foreach(array_filter(array_map('trim', explode(',,,', $dvr->purpose_of_visit))) as $p)
                        <span class="label label-primary" style="display:inline-block; margin:2px; font-size:11px;">{{ $p }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            @if($dvr->other_purpose)
            <div class="dvr-section-box">
                <span class="dvr-section-label"><i class="fa fa-comment-o"></i> Other Purpose</span>
                <p style="margin:5px 0 0;">{{ $dvr->other_purpose }}</p>
            </div>
            @endif

            @if($dvr->visit_detail)
            <div class="dvr-section-box">
                <span class="dvr-section-label"><i class="fa fa-file-text-o"></i> Visit Details</span>
                <p style="margin:5px 0 0;">{{ $dvr->visit_detail }}</p>
            </div>
            @endif

            @if($dvr->remarks)
            <div class="dvr-section-box">
                <span class="dvr-section-label"><i class="fa fa-pencil"></i> Remarks</span>
                <p style="margin:5px 0 0;">{{ $dvr->remarks }}</p>
            </div>
            @endif

            @if($dvr->next_plan)
            <div class="dvr-section-box" style="border-left-color: #27ae60;">
                <span class="dvr-section-label" style="color:#27ae60;"><i class="fa fa-forward"></i> Next Plan</span>
                <p style="margin:5px 0 0;">{{ $dvr->next_plan }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- TRIALS --}}
    @if($dvr->trials->count())
    <div class="portlet light bordered">
        <div class="portlet-title">
            <div class="caption"><i class="fa fa-flask font-purple"></i>
                <span class="caption-subject font-purple bold uppercase">Trials ({{ $dvr->trials->count() }})</span>
            </div>
        </div>
        <div class="portlet-body">
            @foreach($dvr->trials as $i => $trial)
            @php
                if (!$trial->trial_done) {
                    $tStatus = 'Trial Not Done'; $tClass = 'label-danger';
                } else {
                    $hasRpt = $trial->attachments->where('type','trial_report')->count() > 0 || !is_null($trial->trial_report_id);
                    $tStatus = $hasRpt ? 'Report Attached' : 'Trial Report Pending';
                    $tClass  = $hasRpt ? 'label-success' : 'label-warning';
                }
            @endphp
            <div class="dvr-trial-card">
                <div class="dvr-trial-header">
                    <strong><i class="fa fa-flask"></i> Trial #{{ $i+1 }}</strong>
                    <div>
                        <span class="label {{ $trial->trial_done ? 'label-success' : 'label-default' }}">
                            {{ $trial->trial_done ? 'Done' : 'Not Done' }}
                        </span>
                        <span class="label {{ $tClass }}">{{ $tStatus }}</span>
                        @if($trial->trial_type)
                            <span class="label label-info">{{ $trial->trial_type }}</span>
                        @endif
                    </div>
                </div>
                @if($trial->objective)
                <p style="margin:5px 0 0; font-size:12px;"><strong>Objective:</strong> {{ $trial->objective }}</p>
                @endif
                @if($trial->products->count())
                <div style="margin-top:6px;">
                    <span class="dvr-info-label"><i class="fa fa-tag"></i> Products:</span>
                    @foreach($trial->products as $tp)
                        <span class="label label-info" style="margin:2px;">{{ optional($tp->productinfo)->product_name ?? '—' }}</span>
                    @endforeach
                </div>
                @endif
                @if($trial->remarks)
                <p style="margin:5px 0 0; font-size:12px; color:#666;"><i class="fa fa-comment-o"></i> {{ $trial->remarks }}</p>
                @endif
                @if($trial->attachments->count())
                <div style="margin-top:8px;">
                    <span class="dvr-info-label"><i class="fa fa-paperclip"></i> Attachments:</span>
                    @foreach($trial->attachments as $ta)
                        <a href="{{ url('DvrAttachments/'.$dvr->id.'/'.$ta->file) }}" target="_blank"
                           class="btn btn-xs btn-default" style="margin:2px;">
                            <i class="fa fa-file-image-o"></i> {{ $ta->label ?? $ta->type }}
                        </a>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- PRODUCTS --}}
    @if($dvr->products->count())
    <div class="portlet light bordered">
        <div class="portlet-title">
            <div class="caption"><i class="fa fa-tags font-green"></i>
                <span class="caption-subject font-green bold uppercase">Products Discussed</span>
            </div>
        </div>
        <div class="portlet-body">
            <div class="row">
                @foreach($dvr->products as $p)
                <div class="col-md-4">
                    <div class="dvr-product-card">
                        <i class="fa fa-tag text-info" style="font-size:20px;"></i>
                        <div style="margin-top:5px; font-size:12px; font-weight:600;">
                            {{ optional($p->productinfo)->product_name ?? 'Product' }}
                        </div>
                        @if($p->quantity ?? false)
                        <small class="text-muted">Qty: {{ $p->quantity }}</small>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- ATTACHMENTS --}}
    @if($dvr->attachments->count())
    <div class="portlet light bordered">
        <div class="portlet-title">
            <div class="caption"><i class="fa fa-paperclip font-dark"></i>
                <span class="caption-subject font-dark bold uppercase">DVR Attachments</span>
            </div>
        </div>
        <div class="portlet-body">
            <div class="row">
                @foreach($dvr->attachments as $att)
                <div class="col-md-3" style="margin-bottom:15px;">
                    <div class="dvr-attachment-card">
                        <i class="fa fa-file-image-o" style="font-size:30px; color:#95a5a6;"></i>
                        <div style="font-size:11px; margin-top:5px; word-break:break-all;">
                            {{ $att->label ?? $att->type }}
                        </div>
                        <a href="{{ $att->file_url }}" target="_blank" class="btn btn-xs btn-primary" style="margin-top:8px;">
                            <i class="fa fa-eye"></i> View
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

</div>{{-- end left col --}}

{{-- ==================== RIGHT COLUMN ==================== --}}
<div class="col-md-4">

    {{-- CUSTOMER INFO --}}
    <div class="portlet light bordered">
        <div class="portlet-title">
            <div class="caption"><i class="fa fa-building-o font-blue"></i>
                <span class="caption-subject font-blue bold uppercase">Customer</span>
            </div>
        </div>
        <div class="portlet-body">
            <div class="dvr-customer-detail">
                <div class="dvr-customer-name">{{ $customerName ?? 'N/A' }}</div>
                @if($dvr->customer)
                    <span class="label label-success">Registered Customer</span>
                    <table class="table table-condensed dvr-info-table" style="margin-top:10px;">
                        <tr>
                            <td class="dvr-info-label">Category</td>
                            <td>{{ $dvr->customer->category }}</td>
                        </tr>
                        <tr>
                            <td class="dvr-info-label">Activity</td>
                            <td>{{ Str::limit($dvr->customer->activity, 50) }}</td>
                        </tr>
                        <tr>
                            <td class="dvr-info-label">Mobile</td>
                            <td>{{ $dvr->customer->mobile }}</td>
                        </tr>
                        <tr>
                            <td class="dvr-info-label">Address</td>
                            <td><small>{{ $dvr->customer->address }}</small></td>
                        </tr>
                    </table>
                @elseif($dvr->customer_register_request)
                    <span class="label label-warning">Register Request</span>
                    <table class="table table-condensed dvr-info-table" style="margin-top:10px;">
                        <tr>
                            <td class="dvr-info-label">Activity</td>
                            <td>{{ $dvr->customer_register_request->activity }}</td>
                        </tr>
                        <tr>
                            <td class="dvr-info-label">Category</td>
                            <td>{{ $dvr->customer_register_request->category }}</td>
                        </tr>
                        <tr>
                            <td class="dvr-info-label">Mobile</td>
                            <td>{{ $dvr->customer_register_request->mobile }}</td>
                        </tr>
                        <tr>
                            <td class="dvr-info-label">Status</td>
                            <td><span class="label label-default">{{ $dvr->customer_register_request->status }}</span></td>
                        </tr>
                    </table>
                @endif
            </div>
        </div>
    </div>

    {{-- CONTACTS MET --}}
    <div class="portlet light bordered">
        <div class="portlet-title">
            <div class="caption"><i class="fa fa-users font-blue-hoki"></i>
                <span class="caption-subject font-blue-hoki bold uppercase">Contacts Met</span>
            </div>
        </div>
        <div class="portlet-body">
            @if($dvr->customerContacts->count())
                @foreach($dvr->customerContacts as $cc)
                    @if($cc->customerContact)
                    <div class="dvr-contact-detail-card">
                        <div class="dvr-contact-avatar">
                            <i class="fa fa-user-circle-o"></i>
                        </div>
                        <div class="dvr-contact-info">
                            <strong>{{ $cc->customerContact->name }}</strong>
                            @if($cc->customerContact->designation)
                            <div><small class="text-muted">{{ $cc->customerContact->designation }}</small></div>
                            @endif
                            @if($cc->customerContact->department ?? false)
                            <div><small class="text-muted"><i class="fa fa-briefcase"></i> {{ $cc->customerContact->department }}</small></div>
                            @endif
                            @if($cc->customerContact->mobile_number)
                            <div><small><i class="fa fa-phone text-success"></i> {{ $cc->customerContact->mobile_number }}</small></div>
                            @endif
                        </div>
                    </div>
                    @endif
                @endforeach
            @elseif($dvr->customer_contact_info)
                <div class="dvr-contact-detail-card">
                    <div class="dvr-contact-avatar">
                        <i class="fa fa-user-circle-o"></i>
                    </div>
                    <div class="dvr-contact-info">
                        <strong>{{ $dvr->customer_contact_info->name }}</strong>
                        @if($dvr->customer_contact_info->designation)
                        <div><small class="text-muted">{{ $dvr->customer_contact_info->designation }}</small></div>
                        @endif
                        @if($dvr->customer_contact_info->mobile_number)
                        <div><small><i class="fa fa-phone text-success"></i> {{ $dvr->customer_contact_info->mobile_number }}</small></div>
                        @endif
                    </div>
                </div>
            @else
                <p class="text-muted text-center"><i class="fa fa-user-times"></i> No contacts recorded</p>
            @endif
        </div>
    </div>

    {{-- ATTENDANCE --}}
    <div class="portlet light bordered">
        <div class="portlet-title">
            <div class="caption"><i class="fa fa-id-card-o font-green"></i>
                <span class="caption-subject font-green bold uppercase">Attendance</span>
            </div>
        </div>
        <div class="portlet-body">
            @if($attendance)
            <table class="table table-condensed dvr-info-table">
                <tr>
                    <td class="dvr-info-label"><i class="fa fa-sign-in text-success"></i> Punch In</td>
                    <td><strong>{{ $attendance->in_time ?? '—' }}</strong></td>
                </tr>
                <tr>
                    <td class="dvr-info-label"><i class="fa fa-sign-out text-danger"></i> Punch Out</td>
                    <td><strong>{{ $attendance->out_time ?? '—' }}</strong></td>
                </tr>
                <tr>
                    <td class="dvr-info-label"><i class="fa fa-clock-o"></i> Work Hours</td>
                    <td><strong>{{ $workHours }}</strong></td>
                </tr>
                <tr>
                    <td class="dvr-info-label"><i class="fa fa-map-marker"></i> In Location</td>
                    <td><small>{{ $attendance->in_place_of_attendance ?? '—' }}</small></td>
                </tr>
                <tr>
                    <td class="dvr-info-label"><i class="fa fa-map-marker"></i> Out Location</td>
                    <td><small>{{ $attendance->out_place_of_attendance ?? '—' }}</small></td>
                </tr>
                <tr>
                    <td class="dvr-info-label"><i class="fa fa-check-circle"></i> Status</td>
                    <td>
                        <span class="label {{ $attendance->status === 'Present' ? 'label-success' : 'label-danger' }}">
                            {{ $attendance->status ?? '—' }}
                        </span>
                    </td>
                </tr>
            </table>
            @else
                <p class="text-muted text-center"><i class="fa fa-calendar-times-o"></i> No attendance record for this date</p>
            @endif
        </div>
    </div>

    {{-- STATUS SUMMARY --}}
    <div class="portlet light bordered">
        <div class="portlet-title">
            <div class="caption"><i class="fa fa-info font-yellow-gold"></i>
                <span class="caption-subject font-yellow-gold bold uppercase">Status Summary</span>
            </div>
        </div>
        <div class="portlet-body">
            <div class="dvr-status-summary">
                <div class="dvr-status-row">
                    <span class="dvr-status-key">Visit Type</span>
                    <span class="label {{ $visitTypeClass }}">{{ $dvr->visit_type ?? 'Official' }}</span>
                </div>
                <div class="dvr-status-row">
                    <span class="dvr-status-key">Entry Mode</span>
                    <span class="label {{ $entryClass }}">{{ $entryStatus }}</span>
                </div>
                <div class="dvr-status-row">
                    <span class="dvr-status-key">Site Type</span>
                    <span class="label {{ $siteClass }}">{{ $siteStatus }}</span>
                </div>
                <div class="dvr-status-row">
                    <span class="dvr-status-key">Meeting</span>
                    <span class="label {{ $metClass }}">{{ $metStatus }}</span>
                </div>
                <div class="dvr-status-row">
                    <span class="dvr-status-key">Visit Detail</span>
                    <span class="label {{ $detailClass }}">{{ $detailStatus }}</span>
                </div>
                <div class="dvr-status-row">
                    <span class="dvr-status-key">Verification</span>
                    <span class="label {{ $verifyClass }}">{{ $verifyStatus }}</span>
                </div>
            </div>
        </div>
    </div>

</div>{{-- end right col --}}

</div>{{-- end row --}}

</div>
</div>

<style>
.dvr-info-table td { padding: 5px 8px; font-size: 12px; border: none; }
.dvr-info-label { color: #95a5a6; font-weight: 600; width: 120px; white-space: nowrap; }

.dvr-section-box {
    border-left: 3px solid #3498db;
    padding: 8px 12px;
    margin-bottom: 12px;
    background: #f8fbff;
    border-radius: 0 4px 4px 0;
}
.dvr-section-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #3498db; }

.dvr-trial-card {
    border: 1px solid #e8ecf1;
    border-radius: 6px;
    padding: 12px;
    margin-bottom: 12px;
    background: #fafcff;
}
.dvr-trial-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 5px;
}

.dvr-product-card {
    border: 1px solid #e0f0ff;
    border-radius: 6px;
    padding: 12px;
    text-align: center;
    background: #f5faff;
    margin-bottom: 12px;
}

.dvr-attachment-card {
    border: 1px dashed #ccc;
    border-radius: 6px;
    padding: 12px;
    text-align: center;
    background: #fafafa;
}

.dvr-customer-name { font-size: 18px; font-weight: 700; color: #2c3e50; margin-bottom: 5px; }
.dvr-customer-detail { padding: 5px; }

.dvr-contact-detail-card {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px;
    border: 1px solid #e8ecf1;
    border-radius: 6px;
    margin-bottom: 8px;
    background: #fafcff;
}
.dvr-contact-avatar { font-size: 32px; color: #3498db; }
.dvr-contact-info { font-size: 12px; flex: 1; }

.dvr-status-summary { padding: 5px; }
.dvr-status-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 7px 0;
    border-bottom: 1px dashed #eee;
}
.dvr-status-row:last-child { border-bottom: none; }
.dvr-status-key { font-size: 12px; color: #666; font-weight: 600; }
</style>

@endsection