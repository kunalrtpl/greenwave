@extends('layouts.adminLayout.backendLayout')

@section('content')
<style>
    /* ── BASE ─────────────────────────────────────────────────── */
    .page-content { padding-bottom: 90px !important; }

    .att-portlet { border-radius: 8px !important; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }

    /* ── FY TAB PILLS (left sidebar) ──────────────────────────── */
    .nav-pills.nav-stacked > li > a {
        border-radius: 6px; margin-bottom: 6px;
        font-weight: 600; font-size: 13px;
        color: #555; background: #f4f6f9;
        padding: 10px 14px;
        display: flex; justify-content: space-between; align-items: center;
        border: 1px solid #e1e5ec;
        transition: all 0.2s;
    }
    .nav-pills.nav-stacked > li.active > a,
    .nav-pills.nav-stacked > li.active > a:hover {
        background-color: #3598dc !important;
        border-color: #3598dc; color: #fff;
    }
    .nav-pills.nav-stacked > li > a:hover {
        background: #ebf5fd; color: #3598dc; border-color: #3598dc;
    }
    .fy-badge {
        background: rgba(255,255,255,0.3); color: #fff;
        padding: 2px 8px; border-radius: 10px; font-size: 11px;
    }
    li:not(.active) .fy-badge {
        background: #d4e6f7; color: #3598dc;
    }

    /* ── LEAVE TYPE CARDS ─────────────────────────────────────── */
    .leave-card {
        border: 1px solid #e8ecf1;
        border-radius: 10px;
        margin-bottom: 18px;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        transition: box-shadow 0.2s;
    }
    .leave-card:hover { box-shadow: 0 3px 12px rgba(0,0,0,0.09); }

    .leave-card-header {
        padding: 13px 18px;
        display: flex; align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #e8ecf1;
    }
    .leave-card-header .lt-name {
        font-weight: 700; font-size: 14px; color: #2d3748;
        display: flex; align-items: center; gap: 10px;
    }
    .lt-code-badge {
        font-size: 10px; font-weight: 800; padding: 3px 9px;
        border-radius: 20px; letter-spacing: 1px; text-transform: uppercase;
    }
    .badge-sl  { background: #fce8e8; color: #c53030; }
    .badge-cl  { background: #e8f4e8; color: #276749; }
    .badge-el  { background: #ebf5ff; color: #1a56db; }
    .badge-lwp { background: #f5f0ff; color: #6b21a8; }
    .badge-default { background: #f1f5f9; color: #475569; }

    .leave-card-body { padding: 18px; background: #fafbfc; }

    /* ── STAT PILLS (read-only quota display) ─────────────────── */
    .quota-stats { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 16px; }
    .stat-pill {
        padding: 6px 14px; border-radius: 20px;
        font-size: 12px; font-weight: 600;
        display: flex; align-items: center; gap: 5px;
        border: 1px solid transparent;
    }
    .stat-pill .stat-val { font-size: 15px; font-weight: 800; }
    .pill-total     { background: #eef2ff; color: #3730a3; border-color: #c7d2fe; }
    .pill-used      { background: #fff7ed; color: #c2410c; border-color: #fed7aa; }
    .pill-remaining { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
    .pill-na        { background: #f8fafc; color: #94a3b8; border-color: #e2e8f0; font-style: italic; }

    /* ── FORM FIELDS ──────────────────────────────────────────── */
    .settings-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 14px;
    }
    .field-group label {
        font-size: 11px; font-weight: 700; color: #64748b;
        text-transform: uppercase; letter-spacing: 0.6px;
        display: block; margin-bottom: 5px;
    }
    .field-group .form-control {
        border-radius: 6px; font-size: 13px;
        border: 1px solid #dde1e7; height: 36px;
        transition: border 0.2s;
    }
    .field-group .form-control:focus { border-color: #3598dc; box-shadow: 0 0 0 3px rgba(53,152,220,0.12); }
    .field-group .form-control[readonly] {
        background: #f8fafc; color: #94a3b8; cursor: not-allowed;
    }

    /* ── TOGGLE SWITCH ────────────────────────────────────────── */
    .toggle-wrap { display: flex; align-items: center; gap: 10px; padding-top: 6px; }
    .toggle-switch { position: relative; display: inline-block; width: 44px; height: 24px; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
        position: absolute; cursor: pointer; inset: 0;
        background: #cbd5e1; border-radius: 24px; transition: 0.3s;
    }
    .toggle-slider:before {
        content: ''; position: absolute;
        width: 18px; height: 18px; left: 3px; bottom: 3px;
        background: white; border-radius: 50%; transition: 0.3s;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }
    input:checked + .toggle-slider { background: #3598dc; }
    input:checked + .toggle-slider:before { transform: translateX(20px); }
    .toggle-label { font-size: 12px; font-weight: 600; color: #475569; }

    /* ── EL INFO BOX ──────────────────────────────────────────── */
    .el-info-box {
        background: linear-gradient(135deg, #eff6ff 0%, #e0f2fe 100%);
        border: 1px solid #bae6fd; border-radius: 8px;
        padding: 10px 14px; margin-bottom: 14px;
        font-size: 12px; color: #0369a1; font-weight: 500;
    }
    .el-info-box i { margin-right: 5px; }

    /* ── LWP INFO BOX ─────────────────────────────────────────── */
    .lwp-info-box {
        background: #faf5ff; border: 1px dashed #c084fc;
        border-radius: 8px; padding: 12px 16px;
        color: #7c3aed; font-size: 12px; font-weight: 500;
    }

    /* ── FLOATING SAVE BAR ────────────────────────────────────── */
    .floating-save-bar {
        position: fixed; bottom: 0; left: 0; right: 0;
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(10px);
        padding: 14px 0;
        border-top: 1px solid #e1e5ec;
        z-index: 1000;
        box-shadow: 0 -4px 15px rgba(0,0,0,0.07);
        text-align: center;
    }
    .btn-save-float {
        padding: 10px 44px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px;
        border-radius: 30px !important;
        box-shadow: 0 4px 12px rgba(53,152,220,0.35);
        font-size: 13px;
    }
    .save-fy-label {
        font-size: 12px; color: #64748b; margin-bottom: 6px;
        font-weight: 500;
    }
    .save-fy-label strong { color: #3598dc; }

    /* ── HEADER COUNTER ───────────────────────────────────────── */
    .fy-counter-wrap {
        display: inline-flex; align-items: center; gap: 8px;
        background: #ebf5ff; border: 1px solid #3598dc;
        padding: 4px 14px; border-radius: 20px; margin-left: 12px;
        font-size: 12px; color: #3598dc; font-weight: 700;
    }

    /* ── NO QUOTA NOTE ────────────────────────────────────────── */
    .no-quota-note {
        text-align: center; padding: 20px;
        color: #94a3b8; font-size: 13px; font-style: italic;
    }
</style>

<div class="page-content-wrapper">
    <div class="page-content">

        {{-- Alerts --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissable">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
                <i class="fa fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissable">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
                <i class="fa fa-times-circle"></i> {{ session('error') }}
            </div>
        @endif

        <div class="portlet light bordered att-portlet">
            <div class="portlet-title">
                <div class="caption font-blue-sharp">
                    <i class="fa fa-calendar-check-o font-blue-sharp"></i>
                    <span class="caption-subject bold uppercase">
                        Attendance Settings &mdash; {{ $user->name }}
                    </span>
                    <span class="fy-counter-wrap">
                        <i class="fa fa-sliders"></i>
                        FY: <span id="active-fy-label">{{ $currentFy }}</span>
                    </span>
                </div>
                <div class="actions">
                    <a href="{{ url()->previous() }}" class="btn btn-sm btn-default">
                        <i class="fa fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            <div class="portlet-body">
                <div class="row">

                    {{-- ── LEFT: FY Selector ─────────────────────────────── --}}
                    <div class="col-md-3">
                        <p class="text-muted" style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">
                            Financial Year
                        </p>
                        <ul class="nav nav-pills nav-stacked" id="fy-tabs">
                            @foreach($availableFys as $idx => $fy)
                                @php
                                    $totalConfigured = 0;
                                    foreach($leaveTypes as $lt) {
                                        if(isset($data[$fy][$lt->id]['setting_exists']) && $data[$fy][$lt->id]['setting_exists']) {
                                            $totalConfigured++;
                                        }
                                    }
                                @endphp
                                <li class="{{ $idx === 0 ? 'active' : '' }}">
                                    <a href="#fy-{{ str_replace('-','_',$fy) }}" data-toggle="tab" data-fy="{{ $fy }}"
                                       onclick="updateActiveFy('{{ $fy }}')">
                                        <span><i class="fa fa-calendar-o"></i> &nbsp;{{ $fy }}</span>
                                        <span class="fy-badge">{{ $totalConfigured }}/{{ $leaveTypes->count() }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        {{-- User info card --}}
                        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:14px;margin-top:20px;">
                            <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;margin-bottom:8px;">Employee</div>
                            <div style="font-weight:700;color:#1e293b;font-size:14px;">{{ $user->name }}</div>
                            <div style="font-size:12px;color:#64748b;margin-top:3px;">{{ $user->email }}</div>
                            @if($user->department ?? null)
                            <div style="margin-top:6px;">
                                <span style="background:#e0f2fe;color:#0369a1;font-size:11px;padding:2px 8px;border-radius:10px;font-weight:600;">
                                    {{ $user->department }}
                                </span>
                            </div>
                            @endif
                        </div>

                        {{-- Legend --}}
                        <div style="margin-top:16px;padding:12px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;font-size:11px;color:#78350f;">
                            <div style="font-weight:700;margin-bottom:6px;"><i class="fa fa-info-circle"></i> Notes</div>
                            <div>• <strong>SL/CL</strong>: Edit annual quota directly.</div>
                            <div style="margin-top:4px;">• <strong>EL</strong>: Quota grows via monthly cron. Configure accrual rate &amp; carry forward here.</div>
                            <div style="margin-top:4px;">• <strong>LWP</strong>: No quota to track.</div>
                            <div style="margin-top:4px;">• Live stats are <strong>read-only</strong>.</div>
                        </div>
                    </div>

                    {{-- ── RIGHT: Tab Content ────────────────────────────── --}}
                    <div class="col-md-9">
                        <div class="tab-content">

                            @foreach($availableFys as $idx => $fy)
                            @php $fyKey = str_replace('-','_',$fy); @endphp

                            <div class="tab-pane {{ $idx === 0 ? 'active' : '' }}" id="fy-{{ $fyKey }}">

                                <form method="POST"
                                      action="{{ route('admin.users.attendance.settings.save', $user->id) }}"
                                      id="form-{{ $fyKey }}">
                                    @csrf
                                    <input type="hidden" name="financial_year" value="{{ $fy }}">

                                    @foreach($leaveTypes as $ltIdx => $lt)
                                    @php
                                        $d      = $data[$fy][$lt->id];
                                        $isEL   = $lt->code === 'EL';
                                        $isLWP  = !$lt->has_quota;
                                        $isSLCL = $lt->has_quota && $lt->quota_editable && !$isEL;
                                        $codes  = ['SL'=>'badge-sl','CL'=>'badge-cl','EL'=>'badge-el','LWP'=>'badge-lwp'];
                                        $badgeClass = $codes[$lt->code] ?? 'badge-default';
                                    @endphp

                                    <input type="hidden" name="settings[{{ $ltIdx }}][leave_type_id]" value="{{ $lt->id }}">

                                    <div class="leave-card">

                                        {{-- Card Header --}}
                                        <div class="leave-card-header" style="background:#fff;">
                                            <div class="lt-name">
                                                <span class="lt-code-badge {{ $badgeClass }}">{{ $lt->code }}</span>
                                                {{ $lt->name }}
                                            </div>

                                            {{-- Live quota stats (read-only) --}}
                                            @if($lt->has_quota)
                                            <div class="quota-stats" style="margin:0;">
                                                <div class="stat-pill pill-total">
                                                    Total <span class="stat-val">&nbsp;{{ number_format($d['total_quota'],1) }}</span>
                                                </div>
                                                <div class="stat-pill pill-used">
                                                    Used <span class="stat-val">&nbsp;{{ number_format($d['used_quota'],1) }}</span>
                                                </div>
                                                <div class="stat-pill pill-remaining">
                                                    Left <span class="stat-val">&nbsp;{{ number_format($d['remaining'],1) }}</span>
                                                </div>
                                            </div>
                                            @else
                                            <div class="stat-pill pill-na">No quota tracked</div>
                                            @endif
                                        </div>

                                        {{-- Card Body --}}
                                        <div class="leave-card-body">

                                            @if($isLWP)
                                                {{-- LWP — no settings needed --}}
                                                <div class="lwp-info-box">
                                                    <i class="fa fa-info-circle"></i>
                                                    Leave Without Pay has no quota or accrual settings.
                                                    It is always available as a leave type and does not
                                                    deduct from any balance.
                                                </div>

                                            @elseif($isEL)
                                                {{-- EL — accrual config --}}
                                                <div class="el-info-box">
                                                    <i class="fa fa-bolt"></i>
                                                    <strong>Earned Leave</strong> quota is managed automatically by the
                                                    monthly cron job. Configure the accrual rate and carry-forward
                                                    policy below. The live balance above updates each month.
                                                </div>

                                                <div class="settings-grid">
                                                    <div class="field-group">
                                                        <label>Monthly Accrual (days)</label>
                                                        <input type="number"
                                                               name="settings[{{ $ltIdx }}][monthly_accrual]"
                                                               class="form-control"
                                                               value="{{ $d['monthly_accrual'] }}"
                                                               step="0.25" min="0" max="31"
                                                               placeholder="e.g. 1.25">
                                                        <small class="text-muted">Days added per month by cron</small>
                                                    </div>

                                                    <div class="field-group">
                                                        <label>Carry Forward Limit (days)</label>
                                                        <input type="number"
                                                               name="settings[{{ $ltIdx }}][carry_forward_limit]"
                                                               class="form-control"
                                                               value="{{ $d['carry_forward_limit'] }}"
                                                               step="0.5" min="0"
                                                               placeholder="e.g. 10">
                                                        <small class="text-muted">Max EL balance allowed. 0 = no cap</small>
                                                    </div>

                                                    <div class="field-group">
                                                        <label>Carry Forward to Next FY</label>
                                                        <div class="toggle-wrap">
                                                            <label class="toggle-switch">
                                                                <input type="hidden"   name="settings[{{ $ltIdx }}][carry_forward]" value="0">
                                                                <input type="checkbox" name="settings[{{ $ltIdx }}][carry_forward]"
                                                                       value="1"
                                                                       {{ $d['carry_forward'] ? 'checked' : '' }}>
                                                                <span class="toggle-slider"></span>
                                                            </label>
                                                            <span class="toggle-label cf-label-{{ $fy }}-{{ $lt->id }}">
                                                                {{ $d['carry_forward'] ? 'Yes — Balance carries forward' : 'No — Resets at FY start' }}
                                                            </span>
                                                        </div>
                                                    </div>

                                                    {{-- Yearly projection --}}
                                                    <div class="field-group">
                                                        <label>Annual Projection</label>
                                                        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:6px;padding:8px 12px;">
                                                            <span style="font-size:18px;font-weight:800;color:#1d4ed8;">
                                                                {{ number_format($d['monthly_accrual'] * 12, 2) }}
                                                            </span>
                                                            <span style="font-size:11px;color:#94a3b8;"> days/year</span>
                                                        </div>
                                                        <small class="text-muted">Based on current monthly accrual × 12</small>
                                                    </div>
                                                </div>

                                            @else
                                                {{-- SL / CL — annual quota edit --}}
                                                <div class="settings-grid">
                                                    <div class="field-group">
                                                        <label>Annual Quota (days)</label>
                                                        <input type="number"
                                                               name="settings[{{ $ltIdx }}][annual_quota]"
                                                               class="form-control"
                                                               value="{{ $d['annual_quota'] }}"
                                                               step="0.5" min="0" max="365"
                                                               placeholder="e.g. 12">
                                                        <small class="text-muted">
                                                            Global default: {{ $lt->default_quota }} days.
                                                            Changing this will also update the live quota total
                                                            (but will NOT reduce already-used leaves).
                                                        </small>
                                                    </div>

                                                    <div class="field-group">
                                                        <label>Used (read-only)</label>
                                                        <input type="text" class="form-control"
                                                               value="{{ number_format($d['used_quota'],1) }} days used"
                                                               readonly>
                                                        <small class="text-muted">Deducted when user applies leave</small>
                                                    </div>

                                                    <div class="field-group">
                                                        <label>Remaining (read-only)</label>
                                                        <input type="text" class="form-control"
                                                               value="{{ number_format($d['remaining'],1) }} days left"
                                                               readonly
                                                               style="color:{{ $d['remaining'] > 0 ? '#15803d' : '#dc2626' }};font-weight:700;">
                                                    </div>
                                                </div>
                                            @endif

                                        </div>{{-- /card-body --}}
                                    </div>{{-- /leave-card --}}
                                    @endforeach

                                </form>
                            </div>{{-- /tab-pane --}}
                            @endforeach

                        </div>{{-- /tab-content --}}
                    </div>{{-- /col-md-9 --}}
                </div>{{-- /row --}}
            </div>{{-- /portlet-body --}}
        </div>{{-- /portlet --}}

        {{-- ── FLOATING SAVE BAR ─────────────────────────────────────── --}}
        <div class="floating-save-bar">
            <div class="save-fy-label">
                Saving settings for FY: <strong id="save-fy-display">{{ $currentFy }}</strong>
            </div>
            <button type="button" class="btn btn-primary blue btn-save-float" onclick="submitActiveForm()">
                <i class="fa fa-check"></i> Save Changes
            </button>
        </div>

    </div>{{-- /page-content --}}
</div>{{-- /page-content-wrapper --}}

<script>
$(document).ready(function () {

    // ── Update carry-forward toggle labels ──────────────────────────────────
    $(document).on('change', 'input[type="checkbox"][name*="carry_forward"]', function () {
        var $card  = $(this).closest('.leave-card');
        var $label = $(this).closest('.toggle-wrap').find('.toggle-label');
        $label.text(this.checked ? 'Yes — Balance carries forward' : 'No — Resets at FY start');
    });

    // ── Live annual projection update ───────────────────────────────────────
    $(document).on('input', 'input[name*="monthly_accrual"]', function () {
        var val        = parseFloat($(this).val()) || 0;
        var projection = (val * 12).toFixed(2);
        $(this).closest('.leave-card-body')
               .find('.settings-grid')
               .find('[style*="font-size:18px"]')
               .text(projection);
    });
});

// Track which FY tab is active
var activeFy = '{{ $currentFy }}';

function updateActiveFy(fy) {
    activeFy = fy;
    $('#active-fy-label').text(fy);
    $('#save-fy-display').text(fy);
}

// Submit the form for the currently visible FY tab
function submitActiveForm() {
    var fyKey  = activeFy.replace('-', '_');
    var formId = 'form-' + fyKey;
    var $form  = $('#' + formId);

    if ($form.length === 0) {
        alert('Could not find the form for FY ' + activeFy);
        return;
    }
    $form.submit();
}
</script>
@endsection