@extends('layouts.adminLayout.backendLayout')
@section('content')

<style>
/* =========================================================
   PREMIUM SECTION STYLING — Metronic + Bootstrap 3
   ========================================================= */

/* Section card */
.emp-section {
    background: #fff;
    border: 1px solid #e4e8ee;
    border-radius: 6px;
    margin-bottom: 24px;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
    overflow: hidden;
}
.emp-section-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 13px 20px;
    border-bottom: 1px solid #e4e8ee;
}
.emp-section-header .section-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    color: #fff;
    flex-shrink: 0;
}
.emp-section-header h4 {
    margin: 0;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: .3px;
    color: #3a3f51;
}
.emp-section-body {
    padding: 20px 20px 8px;
}

/* Section colour accents */
.sec-blue   .section-icon { background: #4b8af4; }
.sec-green  .section-icon { background: #36a854; }
.sec-orange .section-icon { background: #f5a623; }
.sec-purple .section-icon { background: #7c4dff; }
.sec-teal   .section-icon { background: #00897b; }
.sec-red    .section-icon { background: #e53935; }
.sec-indigo .section-icon { background: #3949ab; }

.sec-blue   .emp-section-header { background: #f0f5ff; border-bottom-color: #d0e0fc; }
.sec-green  .emp-section-header { background: #f0faf3; border-bottom-color: #c6e9d1; }
.sec-orange .emp-section-header { background: #fffbf0; border-bottom-color: #fce8b0; }
.sec-purple .emp-section-header { background: #f5f0ff; border-bottom-color: #d9ccff; }
.sec-teal   .emp-section-header { background: #f0faf9; border-bottom-color: #b2dfdb; }
.sec-red    .emp-section-header { background: #fff5f5; border-bottom-color: #ffcdd2; }
.sec-indigo .emp-section-header { background: #f0f2ff; border-bottom-color: #c5cae9; }

/* Field label */
.emp-section .control-label {
    font-weight: 600;
    color: #555;
    font-size: 13px;
}
.emp-section .form-control {
    border-color: #d0d6e0;
    border-radius: 4px;
    color: #444;
    font-size: 13px;
    height: 36px;
}
.emp-section .form-control:focus {
    border-color: #4b8af4;
    box-shadow: 0 0 0 2px rgba(75,138,244,.15);
}
.emp-section textarea.form-control { height: auto; }

/* Required asterisk */
.asteric { color: #e53935; margin-left: 2px; }

/* Radio / checkbox row */
.radio-inline-group label {
    font-weight: 500;
    color: #555;
    margin-right: 16px;
    cursor: pointer;
}
.radio-inline-group input[type=radio] { margin-right: 4px; }

/* ---- Attachments table ---- */
#attachmentsTable {
    border: 1px solid #e4e8ee;
    border-radius: 4px;
    overflow: hidden;
    font-size: 13px;
}
#attachmentsTable thead th {
    background: #f8f9fb;
    border-bottom: 2px solid #e4e8ee;
    color: #3a3f51;
    font-weight: 700;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .4px;
    padding: 10px 12px;
}
#attachmentsTable tbody td {
    vertical-align: middle;
    padding: 9px 12px;
    border-bottom: 1px solid #f0f2f5;
}
#attachmentsTable tbody tr:last-child td { border-bottom: none; }
#attachmentsTable tbody tr:hover { background: #fafbfd; }
.att-label-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    background: #eef2fc;
    color: #3949ab;
    font-size: 12px;
    font-weight: 600;
}
.att-file-info {
    font-size: 11px;
    color: #999;
    margin-top: 2px;
}
.btn-att-view {
    padding: 3px 10px;
    font-size: 11px;
}

/* ---- Inline dept details ---- */
#InlineDeptDetails .form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
    color: #555;
}
#InlineDeptDetails .form-group { margin-bottom: 15px; }
#InlineDeptDetails .select2-container { width: 100% !important; }
#InlineDeptDetails .form-control { width: 100%; }

/* Panel accordion */
.panel-heading .accordion-toggle:after {
    font-family: 'Glyphicons Halflings';
    content: "\e114";
    float: left;
    color: #4a8c17 !important;
}
.panel-heading .accordion-toggle.collapsed:after { content: "\e080"; }
.panel-default>.panel-heading { background-color: transparent !important; height: 40px; }
.panel-heading .accordion-toggle:after { color: #fff; }
.panel-title>a:hover { color: #fff; }

/* ---- User Department region grid ---- */
/* Uses pure flexbox so Region and Sub Region headers/selects
   are always pixel-perfectly aligned on the same row */
.dept-region-grid {
    display: flex;
    gap: 20px;
    align-items: flex-start;
    margin-top: 4px;
}
.dept-region-col {
    flex: 1;
    min-width: 0;
}
/* Header row inside each column: label left, Select All pill right */
.dept-col-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 26px;
    margin-bottom: 6px;
}
.dept-col-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #3a3f51;
    display: flex;
    align-items: center;
    gap: 5px;
}
.dept-col-label i { color: #00897b; }

/* Select All pill */
.dept-selectall-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #e8f5f3;
    border: 1px solid #b2dfdb;
    border-radius: 12px;
    padding: 2px 10px 2px 7px;
    font-size: 11px;
    font-weight: 600;
    color: #00897b;
    cursor: pointer;
    white-space: nowrap;
    margin-bottom: 0;
    transition: background .15s;
    line-height: 1.5;
}
.dept-selectall-pill:hover { background: #c8ece8; }
.dept-selectall-pill input[type=checkbox] {
    width: 13px;
    height: 13px;
    margin: 0;
    cursor: pointer;
    accent-color: #00897b;
    flex-shrink: 0;
}
/* Region divider */
.dept-region-divider {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 10px 0 16px;
}
.dept-region-divider::before,
.dept-region-divider::after { content:''; flex:1; height:1px; background:#e0e6ef; }
.dept-region-divider span {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: #00897b;
    display: flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
}
/* Hint below sub region */
.dept-hint {
    font-size: 11px;
    color: #bbb;
    margin-top: 5px;
    display: flex;
    align-items: center;
    gap: 4px;
}
.dept-hint i { color: #00897b; }

/* Submit bar */
.form-submit-bar {
    background: #f8f9fb;
    border-top: 1px solid #e4e8ee;
    padding: 16px 20px;
    text-align: right;
    border-radius: 0 0 6px 6px;
    margin-top: 10px;
}
/* ---- Sub-region city pills ---- */
.city-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #f0faf9;
    border: 1px solid #b2dfdb;
    border-radius: 12px;
    padding: 3px 10px;
    font-size: 11px;
    font-weight: 600;
    color: #00695c;
}
.city-pill i { font-size: 10px; color: #00897b; }
.city-pill-group-label {
    font-size: 11px;
    font-weight: 700;
    color: #888;
    width: 100%;
    margin-top: 6px;
    margin-bottom: 2px;
    display: flex;
    align-items: center;
    gap: 5px;
}
.city-pill-group-label:first-child { margin-top: 0; }
</style>

<div class="page-content-wrapper">
    <div class="page-content">

        {{-- Page head --}}
        <div class="page-head">
            <div class="page-title">
                <h1>Employees Management
                    <small>/ {{ $title }}</small>
                </h1>
            </div>
        </div>

        {{-- Breadcrumb --}}
        <ul class="page-breadcrumb breadcrumb">
            <li><a href="{{ url('admin/dashboard') }}">Dashboard</a><i class="fa fa-circle"></i></li>
            <li><a href="{{ url('admin/users') }}">Employees</a><i class="fa fa-circle"></i></li>
            <li><span>{{ $title }}</span></li>
        </ul>

        <?php
            /* ── Pre-load existing dept data for edit mode ───────────── */
            $existingDept        = (!empty($empdata['departments'])) ? $empdata['departments'][0] : [];
            $existingDeptId      = (!empty($existingDept['department_id'])) ? $existingDept['department_id'] : '';
            $existingReportTo    = (!empty($existingDept['report_to']))     ? $existingDept['report_to']     : '';
            $existingSubRegionIds = [];
            $existingRegionIds    = [];
            $existingDeptRegions  = [];
            if (!empty($existingDept['subregions'])) {
                foreach ($existingDept['subregions'] as $sr) {
                    $existingSubRegionIds[] = (string)$sr['sub_region_id'];
                }
                if (!empty($existingSubRegionIds)) {
                    $srRows = DB::table('regions')->whereIn('id', $existingSubRegionIds)->get();
                    foreach ($srRows as $srRow) {
                        $existingRegionIds[]   = (string)$srRow->parent_id;
                        $existingDeptRegions[] = $srRow->parent_id . '#' . $srRow->id;
                    }
                    $existingRegionIds = array_unique($existingRegionIds);
                }
            }

            /* ── Predefined attachment labels ────────────────────────── */
            $predefinedAttachmentLabels = [
                'Offer Letter',
                'Incentive Plan',
                'Appointment Letter',
                'Salary Structure',
                'HR Policy - Leave',
                'HR Policy - Travel & Expense',
                'Employee Information Form',
                'Bank Account Details',
                'Education Certificate 1',
                'Education Certificate 2',
                'Education Certificate 3',
            ];

            /* ── Existing attachments (edit mode) ────────────────────── */
            $existingAttachments = [];
            if (!empty($empdata['id'])) {
                $existingAttachments = DB::table('user_attachments')
                    ->where('user_id', $empdata['id'])
                    ->orderBy('id')
                    ->get();
                $existingAttachments = json_decode(json_encode($existingAttachments), true);
            }
        ?>

        <form id="Employeeform" role="form" class="form-horizontal" method="post"
              action="javascript:;" enctype="multipart/form-data" autocomplete="off">
            <input type="hidden" name="_token" value="{{{ csrf_token() }}}"/>
            @if(!empty($empdata['id']))
                <input type="hidden" name="employeeid" value="{{ $empdata['id'] }}">
            @else
                <input type="hidden" name="employeeid" value="">
            @endif

            {{-- ═══════════════════════════════════════════════════════
                 SECTION 1 — Basic Information
                 ═══════════════════════════════════════════════════════ --}}
            <div class="emp-section sec-blue">
                <div class="emp-section-header">
                    <div class="section-icon"><i class="fa fa-user"></i></div>
                    <h4>Basic Information</h4>
                </div>
                <div class="emp-section-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Name <span class="asteric">*</span></label>
                                <div class="col-md-8">
                                    <input type="text" placeholder="Full Name" name="name" class="form-control"
                                           value="{{ !empty($empdata['name']) ? $empdata['name'] : '' }}"/>
                                    <h4 class="text-danger" style="display:none;font-size:12px;" id="Employee-name"></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Designation <span class="asteric">*</span></label>
                                <div class="col-md-8">
                                    <input type="text" placeholder="Designation" name="designation" class="form-control"
                                           value="{{ !empty($empdata['designation']) ? $empdata['designation'] : '' }}"/>
                                    <h4 class="text-danger" style="display:none;font-size:12px;" id="Employee-designation"></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Level <span class="asteric">*</span></label>
                                <div class="col-md-8">
                                    <select class="form-control select2" name="level" id="employee_level" required>
                                        <option value="">Please Select</option>
                                        @foreach(range(1, 4) as $lvl)
                                            <option value="{{ $lvl }}"
                                                {{ (!empty($empdata) && isset($empdata['level']) && $empdata['level'] == $lvl) ? 'selected' : '' }}>
                                                Level {{ $lvl }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <h4 class="text-danger" style="display:none;font-size:12px;" id="Employee-level"></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Mobile <span class="asteric">*</span></label>
                                <div class="col-md-8">
                                    <input type="text" placeholder="10-digit Mobile" name="mobile" class="form-control"
                                           value="{{ !empty($empdata['mobile']) ? $empdata['mobile'] : '' }}"/>
                                    <h4 class="text-danger" style="display:none;font-size:12px;" id="Employee-mobile"></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Email <span class="asteric">*</span></label>
                                <div class="col-md-8">
                                    <input type="text" placeholder="Email Address" name="email" class="form-control"
                                           value="{{ !empty($empdata['email']) ? $empdata['email'] : '' }}"/>
                                    <h4 class="text-danger" style="display:none;font-size:12px;" id="Employee-email"></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Date of Birth</label>
                                <div class="col-md-8">
                                    <div class="input-group input-append date datePicker">
                                        <input placeholder="YYYY-MM-DD" type="text" name="dob"
                                               class="form-control datePicker"
                                               value="{{ !empty($empdata['dob']) ? $empdata['dob'] : '' }}"/>
                                        <span class="input-group-addon add-on"><span class="glyphicon glyphicon-calendar"></span></span>
                                    </div>
                                    <h4 class="text-danger" style="display:none;font-size:12px;" id="Employee-dob"></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Gender</label>
                                <div class="col-md-8" style="padding-top:7px;">
                                    <div class="radio-inline-group">
                                        @foreach(['Male','Female'] as $gender)
                                            <label>
                                                <input type="radio" name="gender" value="{{ $gender }}"
                                                    @if(!empty($empdata) && $empdata['gender'] == $gender) checked @endif/>
                                                {{ $gender }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Base City <span class="asteric">*</span></label>
                                <div class="col-md-8">
                                    <select class="form-control select2" name="base_city">
                                        <option value="">Please Select</option>
                                        @foreach(getcities() as $city)
                                            <option value="{{ $city['city_name'] }}"
                                                {{ (!empty($empdata) && $empdata['base_city'] == $city['city_name']) ? 'selected' : '' }}>
                                                {{ $city['city_name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Status <span class="asteric">*</span></label>
                                <div class="col-md-8" style="padding-top:7px;">
                                    <div class="radio-inline-group">
                                        @foreach(['1'=>'Active','0'=>'Inactive'] as $skey=>$status)
                                            <label>
                                                <input type="radio" name="status" value="{{ $skey }}"
                                                    @if(!empty($empdata) && $empdata['status'] == $skey) checked
                                                    @elseif($skey == 1) checked @endif/>
                                                {{ $status }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Authorized <span class="asteric">*</span></label>
                                <div class="col-md-8" style="padding-top:7px;">
                                    <div class="radio-inline-group">
                                        <label>
                                            <input type="radio" name="is_authenticated" value="1"
                                                @if(empty($empdata) || $empdata['is_authenticated'] == 1) checked @endif/>
                                            Yes
                                        </label>
                                        <label>
                                            <input type="radio" name="is_authenticated" value="0"
                                                @if(!empty($empdata) && $empdata['is_authenticated'] == 0) checked @endif/>
                                            No
                                        </label>
                                    </div>
                                    <h4 class="text-danger" style="display:none;font-size:12px;" id="Employee-is_authenticated"></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Profile Photo</label>
                                <div class="col-md-8">
                                    <div data-provides="fileinput" class="fileinput fileinput-new">
                                        <div class="fileinput-new thumbnail" style="width:80px;height:80px;border-radius:4px;overflow:hidden;">
                                            <?php if(!empty($empdata['image'])){
                                                $path = "images/AdminImages/".$empdata['image'];
                                            if(file_exists($path)){ ?>
                                                <img style="width:80px;height:80px;object-fit:cover;" src="{{ asset('images/AdminImages/'.$empdata['image'])}}">
                                            <?php }else{ ?>
                                                <img style="width:80px;height:80px;object-fit:cover;" src="{{ asset('images/default.png') }}">
                                            <?php } }else{ ?>
                                                <img style="width:80px;height:80px;object-fit:cover;" src="{{ asset('images/default.png') }}">
                                            <?php } ?>
                                        </div>
                                        <div style="max-width:200px;max-height:80px;" class="fileinput-preview fileinput-exists thumbnail"></div>
                                        <div style="margin-top:8px;">
                                            <span class="btn btn-sm btn-default btn-file">
                                                <span class="fileinput-new"><i class="fa fa-upload"></i> Upload Photo</span>
                                                <span class="fileinput-exists"><i class="fa fa-upload"></i> Change Photo</span>
                                                <input type="file" id="Image" name="image" accept="image/*">
                                            </span>
                                            <a data-dismiss="fileinput" class="btn btn-sm btn-danger fileinput-exists" href="#">
                                                <i class="fa fa-times"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════
                 SECTION 2 — Contact & Address
                 ═══════════════════════════════════════════════════════ --}}
            <div class="emp-section sec-green">
                <div class="emp-section-header">
                    <div class="section-icon"><i class="fa fa-map-marker"></i></div>
                    <h4>Contact &amp; Address</h4>
                </div>
                <div class="emp-section-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Home Landline</label>
                                <div class="col-md-8">
                                    <input type="text" placeholder="Landline Number" name="home_landline_no" class="form-control"
                                           value="{{ !empty($empdata['home_landline_no']) ? $empdata['home_landline_no'] : '' }}"/>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Emergency Contact Name</label>
                                <div class="col-md-8">
                                    <input type="text" placeholder="Emergency Contact Person" name="emergency_contact_person" class="form-control"
                                           value="{{ !empty($empdata['emergency_contact_person']) ? $empdata['emergency_contact_person'] : '' }}"/>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Emergency Mobile</label>
                                <div class="col-md-8">
                                    <input type="text" placeholder="Emergency Contact Mobile" name="emergency_contact_person_mobile" class="form-control"
                                           value="{{ !empty($empdata['emergency_contact_person_mobile']) ? $empdata['emergency_contact_person_mobile'] : '' }}"/>
                                    <h4 class="text-danger" style="display:none;font-size:12px;" id="Employee-emergency_contact_person_mobile"></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Correspondence Address</label>
                                <div class="col-md-8">
                                    <input type="text" placeholder="Correspondence Address" name="correspondence_address" class="form-control" id="correspondenceAddr"
                                           value="{{ !empty($empdata['correspondence_address']) ? $empdata['correspondence_address'] : '' }}"/>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Permanent Address
                                    <br><small class="text-muted" style="font-weight:400;">(auto-fills from above)</small>
                                </label>
                                <div class="col-md-8">
                                    <input type="text" placeholder="Permanent Address" name="permanent_address" class="form-control" id="permanentAddr"
                                           value="{{ !empty($empdata['permanent_address']) ? $empdata['permanent_address'] : '' }}"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════
                 SECTION 3 — Identity & Documents
                 ═══════════════════════════════════════════════════════ --}}
            <div class="emp-section sec-orange">
                <div class="emp-section-header">
                    <div class="section-icon"><i class="fa fa-id-card"></i></div>
                    <h4>Identity &amp; Documents</h4>
                </div>
                <div class="emp-section-body">
                    <div class="row">
                        {{-- PAN --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">PAN Number</label>
                                <div class="col-md-8">
                                    <input type="text" placeholder="PAN" name="pan" class="form-control"
                                           value="{{ !empty($empdata['pan']) ? $empdata['pan'] : '' }}"/>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">PAN Proof</label>
                                <div class="col-md-8">
                                    <input type="file" name="pan_proof" class="form-control"/>
                                    @if(!empty($empdata['pan_proof']))
                                        <a target="_blank" href="{{ url('/images/UserProofs/'.$empdata['pan_proof']) }}" class="btn btn-xs btn-info btn-att-view" style="margin-top:5px;">
                                            <i class="fa fa-eye"></i> View
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        {{-- Aadhar --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Aadhar Number</label>
                                <div class="col-md-8">
                                    <input type="text" placeholder="Aadhar" name="aadhar" class="form-control"
                                           value="{{ !empty($empdata['aadhar']) ? $empdata['aadhar'] : '' }}"/>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Aadhar Proof</label>
                                <div class="col-md-8">
                                    <input type="file" name="aadhar_proof" class="form-control"/>
                                    @if(!empty($empdata['aadhar_proof']))
                                        <a target="_blank" href="{{ url('/images/UserProofs/'.$empdata['aadhar_proof']) }}" class="btn btn-xs btn-info btn-att-view" style="margin-top:5px;">
                                            <i class="fa fa-eye"></i> View
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        {{-- Driving License --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Driving License</label>
                                <div class="col-md-8">
                                    <input type="text" placeholder="DL Number" name="driving_license" class="form-control"
                                           value="{{ !empty($empdata['driving_license']) ? $empdata['driving_license'] : '' }}"/>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">DL Proof</label>
                                <div class="col-md-8">
                                    <input type="file" name="driving_license_proof" class="form-control"/>
                                    @if(!empty($empdata['driving_license_proof']))
                                        <a target="_blank" href="{{ url('/images/UserProofs/'.$empdata['driving_license_proof']) }}" class="btn btn-xs btn-info btn-att-view" style="margin-top:5px;">
                                            <i class="fa fa-eye"></i> View
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════
                 SECTION 4 — Employment Details
                 ═══════════════════════════════════════════════════════ --}}
            <div class="emp-section sec-purple">
                <div class="emp-section-header">
                    <div class="section-icon"><i class="fa fa-briefcase"></i></div>
                    <h4>Employment Details</h4>
                </div>
                <div class="emp-section-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Joining Date</label>
                                <div class="col-md-8">
                                    <div class="input-group input-append date datePicker">
                                        <input placeholder="YYYY-MM-DD" type="text" name="joining_date"
                                               class="form-control datePicker"
                                               value="{{ !empty($empdata['joining_date']) ? $empdata['joining_date'] : '' }}"/>
                                        <span class="input-group-addon add-on"><span class="glyphicon glyphicon-calendar"></span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Probation Period</label>
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="probation_period"
                                               placeholder="e.g. 90"
                                               value="{{ !empty($empdata['probation_period']) ? $empdata['probation_period'] : '' }}"/>
                                        <span class="input-group-addon">days</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Salary Account No.</label>
                                <div class="col-md-8">
                                    <input type="text" placeholder="Account Number" name="salary_account_no" class="form-control"
                                           value="{{ !empty($empdata['salary_account_no']) ? $empdata['salary_account_no'] : '' }}"/>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Travel Charges / KM</label>
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <span class="input-group-addon">₹</span>
                                        <input type="number" placeholder="0.00" name="travel_charges_per_km" class="form-control"
                                               value="{{ !empty($empdata['travel_charges_per_km']) ? $empdata['travel_charges_per_km'] : '' }}"/>
                                        <h4 class="text-danger" style="display:none;font-size:12px;" id="Employee-travel_charges_per_km"></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════
                 SECTION 5 — User Department (fully inline, no AJAX)
                 ═══════════════════════════════════════════════════════ --}}
            <?php
                /* Reporting users — same query as old appendDesignationInfo */
                $inlineReportingUsers = DB::table('users')
                    ->select('id','name','designation')
                    ->where('status', 1)
                    ->when(!empty($empdata['id']), function($q) use ($empdata){
                        return $q->where('id','!=',$empdata['id']);
                    })
                    ->get();
                $inlineReportingUsers = json_decode(json_encode($inlineReportingUsers), true);

                /* Is existing dept Marketing? */
                $existingDeptIsMarketing = false;
                if (!empty($existingDeptId)) {
                    $existingDeptRow = DB::table('departments')->where('id', $existingDeptId)->first();
                    if ($existingDeptRow && $existingDeptRow->department == 'Marketing') {
                        $existingDeptIsMarketing = true;
                    }
                }

                /* All parent regions */
                $allParentRegions = DB::table('regions')
                    ->where(function($q){ $q->whereNull('parent_id')->orWhere('parent_id',0); })
                    ->get();
                $allParentRegions = json_decode(json_encode($allParentRegions), true);

                /* Pre-load sub-regions for edit mode */
                /* Pre-load sub-regions for edit mode */
                $preloadedSubRegions = [];
                $preloadedCitiesBySubRegion = [];   // NEW
                if ($existingDeptIsMarketing && !empty($existingRegionIds)) {
                    $preloadedSubRegions = DB::table('regions')
                        ->whereIn('parent_id', $existingRegionIds)
                        ->get();
                    $preloadedSubRegions = json_decode(json_encode($preloadedSubRegions), true);

                    // NEW — load cities for already-selected sub-regions
                    if (!empty($existingSubRegionIds)) {
                        $preloadedCityRows = DB::table('region_cities')
                            ->whereIn('region_id', $existingSubRegionIds)
                            ->orderBy('city')
                            ->get();
                        foreach ($preloadedCityRows as $cityRow) {
                            $preloadedCitiesBySubRegion[$cityRow->region_id][] = $cityRow->city;
                        }
                    }
                }
            ?>
            <div class="emp-section sec-teal">
                <div class="emp-section-header">
                    <div class="section-icon"><i class="fa fa-sitemap"></i></div>
                    <h4>User Department</h4>
                </div>
                <div class="emp-section-body">

                    {{-- Department + Report To side by side --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Department <span class="asteric">*</span></label>
                                <div class="col-md-8">
                                    <select class="form-control select2" id="inline_department_id" name="inline_department_id" required>
                                        <option value="">Please Select</option>
                                        @foreach(departments() as $deptinfo)
                                            <option value="{{ $deptinfo['id'] }}"
                                                data-is-marketing="{{ $deptinfo['department'] == 'Marketing' ? '1' : '0' }}"
                                                data-is-admin="{{ $deptinfo['department'] == 'Administration' ? '1' : '0' }}"
                                                {{ $existingDeptId == $deptinfo['id'] ? 'selected' : '' }}>
                                                {{ $deptinfo['department'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <h4 class="text-danger" style="display:none;font-size:12px;" id="Employee-user_depts"></h4>
                                </div>
                            </div>
                        </div>
                        <?php
                            $hideReportToOnLoad = true; // default: hidden
                            if (!empty($existingDeptId)) {
                                $existingDeptName = DB::table('departments')->where('id', $existingDeptId)->value('department');
                                // Show Report To only if dept is selected AND it's NOT Administration
                                $hideReportToOnLoad = ($existingDeptName === 'Administration');
                            }
                        ?>
                        <div class="col-md-6" id="reportToWrapper" style="{{ $hideReportToOnLoad ? 'display:none;' : '' }}">
                            <div class="form-group">
                                <label class="col-md-4 control-label">
                                    Report To <span class="asteric report-to-required-star">*</span>
                                </label>
                                <div class="col-md-8">
                                    <select class="form-control select2" id="inline_report_to" name="inline_report_to">
                                        <option value="">Please Select</option>
                                        @foreach($inlineReportingUsers as $ru)
                                            <option value="{{ $ru['id'] }}"
                                                {{ $existingReportTo == $ru['id'] ? 'selected' : '' }}>
                                                {{ $ru['name'] }}@if($ru['id'] != 1 && !empty($ru['designation'])) ({{ $ru['designation'] }})@endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Region + Sub Region row (Marketing only) --}}
                    <div id="regionSectionWrapper" style="{{ $existingDeptIsMarketing ? '' : 'display:none;' }}">
                        <div class="dept-region-divider">
                            <span><i class="fa fa-map"></i> Region Assignment</span>
                        </div>
                        <div class="dept-region-grid">
                            {{-- Region --}}
                            <div class="dept-region-col">
                                <div class="dept-col-header">
                                    <div class="dept-col-label"><i class="fa fa-globe"></i> Region</div>
                                    <label class="dept-selectall-pill">
                                        <input type="checkbox" id="SelectAllRegion"
                                            {{ (count($existingRegionIds) > 0 && count($existingRegionIds) == count($allParentRegions)) ? 'checked' : '' }}>
                                        Select All
                                    </label>
                                </div>
                                <select class="form-control getRegions select2" id="inline_regions" name="regions[]" multiple>
                                    @foreach($allParentRegions as $region)
                                        <option value="{{ $region['id'] }}"
                                            {{ in_array($region['id'], $existingRegionIds) ? 'selected' : '' }}>
                                            {{ $region['region'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- Sub Region --}}
                            <div class="dept-region-col">
                                <div class="dept-col-header">
                                    <div class="dept-col-label"><i class="fa fa-map-pin"></i> Sub Region</div>
                                    <label class="dept-selectall-pill">
                                        <input type="checkbox" id="SelectAllSubRegion"
                                            {{ (count($existingSubRegionIds) > 0 && count($existingSubRegionIds) == count($preloadedSubRegions)) ? 'checked' : '' }}>
                                        Select All
                                    </label>
                                </div>
                                <select class="form-control subRegions select2" id="inline_subregions" name="subregions[]" multiple>
                                    @foreach($preloadedSubRegions as $subRegion)
                                        <option value="{{ $subRegion['id'] }}"
                                            {{ in_array($subRegion['id'], $existingSubRegionIds) ? 'selected' : '' }}>
                                            {{ $subRegion['region'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @if(empty($preloadedSubRegions))
                                    <div class="dept-hint"><i class="fa fa-info-circle"></i> Select a region first</div>
                                @endif

                                {{-- ── Cities under selected sub-regions ── --}}
                                <div id="subRegionCitiesBox" style="margin-top:10px;display:none;">
                                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#3a3f51;margin-bottom:6px;">
                                        <i class="fa fa-building" style="color:#00897b;"></i> Cities in Selected Sub-Regions
                                    </div>
                                    <div id="subRegionCitiesList" style="display:flex;flex-wrap:wrap;gap:5px;"></div>
                                </div>
                                @if(empty($preloadedSubRegions))
                                    <div class="dept-hint"><i class="fa fa-info-circle"></i> Select a region first</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="inline_user_dept_json" name="user_depts[]" value="">
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════
                 SECTION 6 — Product Types & Access
                 ═══════════════════════════════════════════════════════ --}}
            <div class="emp-section sec-indigo">
                <div class="emp-section-header">
                    <div class="section-icon"><i class="fa fa-cubes"></i></div>
                    <h4>Product Types &amp; System Access</h4>
                </div>
                <div class="emp-section-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Product Types</label>
                                <div class="col-md-8">
                                    <select class="form-control select2" name="product_types[]" multiple>
                                        @foreach(product_types() as $pkey => $protype)
                                            <option value="{{ $pkey }}" @if(in_array($pkey,$selProductTypes)) selected @endif>{{ $protype }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Show Weightage <span class="asteric">*</span></label>
                                <div class="col-md-8">
                                    <select class="form-control" name="show_weightage">
                                        <option value="">Please Select</option>
                                        @foreach(classes() as $form => $showWeightage)
                                            <option value="{{ $showWeightage }}"
                                                @if(empty($empdata)) @if($pkey==1) selected @endif
                                                @else @if($empdata['show_weightage'] == $showWeightage) selected @endif @endif>
                                                {{ $showWeightage }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Conveyance Allowed <span class="asteric">*</span></label>
                                <div class="col-md-8">
                                    <select class="form-control" name="conveyance_selection_allowed">
                                        <option value="">Please Select</option>
                                        <option value="1" @if(empty($empdata)) selected @else @if($empdata['conveyance_selection_allowed']==1) selected @endif @endif>Yes</option>
                                        <option value="0" @if(!empty($empdata) && $empdata['conveyance_selection_allowed']==0) selected @endif>No</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">Web Access</label>
                                <div class="col-md-8">
                                    <select class="form-control" name="web_access" required>
                                        <option value="">Please Select</option>
                                        @foreach(classes() as $key => $webAccess)
                                            <option value="{{ $webAccess }}"
                                                @if(empty($empdata)) @if($key==1) selected @endif
                                                @else @if($empdata['web_access'] == $webAccess) selected @endif @endif>
                                                {{ $webAccess }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-md-4 control-label">App Access</label>
                                <div class="col-md-8">
                                    <select class="form-control" name="app_access" required>
                                        <option value="">Please Select</option>
                                        @foreach(classes() as $key => $appAccess)
                                            <option value="{{ $appAccess }}"
                                                @if(empty($empdata)) @if($key==1) selected @endif
                                                @else @if($empdata['app_access'] == $appAccess) selected @endif @endif>
                                                {{ $appAccess }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- App Modules (shown only when App Access = Yes) --}}
                    <div id="AppAccessArea" @if(isset($empdata['app_access']) && $empdata['app_access']=="Yes") @else style="display:none;" @endif>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="col-md-2 control-label">App Modules <b class="red">({{ count($selAppRoles) }})</b> <span class="asteric">*</span></label>
                                    <div class="col-md-10">
                                        <div class="panel-group" id="accordion-module">
                                            <div class="panel panel-default">
                                                <div class="panel-heading text-center">
                                                    <h4 class="panel-title">
                                                        <a style="text-decoration:none;" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-module" href="#collapseTwo"></a>
                                                    </h4>
                                                </div>
                                                <div id="collapseTwo" class="panel-collapse collapse">
                                                    <div class="panel-body">
                                                        <table class="table table-bordered table-condensed">
                                                            <tr><th>#</th><th>Module</th><th>Allow</th></tr>
                                                            @foreach(app_roles('executive') as $pkey => $role)
                                                                <tr>
                                                                    <td>{{ ++$pkey }}</td>
                                                                    <td>{{ $role['name_admin'] }}</td>
                                                                    <td><input type="checkbox" name="app_roles[]" value="{{ $role['key'] }}" @if(in_array($role['key'],$selAppRoles)) checked @endif></td>
                                                                </tr>
                                                            @endforeach
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Linked Customers --}}
                    @if(isset($empdata['shares']) && !empty($empdata['shares']))
                    <div class="row" style="margin-top:10px;">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="col-md-2 control-label">Linked Customers <b class="red">({{ count($empdata['shares']) }})</b></label>
                                <div class="col-md-10">
                                    <div class="panel-group" id="accordion">
                                        <div class="panel panel-default">
                                            <div class="panel-heading text-center">
                                                <h4 class="panel-title">
                                                    <a style="text-decoration:none;" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseOne"></a>
                                                </h4>
                                            </div>
                                            <div id="collapseOne" class="panel-collapse collapse">
                                                <div class="panel-body">
                                                    <table class="table table-bordered table-condensed">
                                                        <tr><td><b>Sr.</b></td><td><b>Customer</b></td></tr>
                                                        @foreach($empdata['shares'] as $skey => $share)
                                                            <tr>
                                                                <td>{{ ++$skey }}</td>
                                                                <td>{{ $share['customer']['name'] }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════
                 SECTION 7 — Attachments
                 ═══════════════════════════════════════════════════════ --}}
            <div class="emp-section sec-red">
                <div class="emp-section-header">
                    <div class="section-icon"><i class="fa fa-paperclip"></i></div>
                    <h4>Attachments</h4>
                    <span style="margin-left:auto;font-size:11px;color:#888;">Only PDF files accepted &bull; Max 5MB each</span>
                </div>
                <div class="emp-section-body" style="padding-bottom:16px;">
                    <div class="table-responsive">
                        <table class="table" id="attachmentsTable">
                            <thead>
                                <tr>
                                    <th style="width:220px;">Label</th>
                                    <th>Upload <small class="text-muted">(PDF only)</small></th>
                                    <th style="width:130px;text-align:center;">Show in App</th>
                                    <th style="width:100px;text-align:center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="attachmentsTbody">

                                {{-- ── Predefined labels (always present, not deletable) ── --}}
                                @foreach($predefinedAttachmentLabels as $idx => $label)
                                    <?php
                                        /* Find existing saved attachment for this label */
                                        $existingAtt = null;
                                        foreach($existingAttachments as $att) {
                                            if($att['label'] === $label) {
                                                $existingAtt = $att;
                                                break;
                                            }
                                        }
                                    ?>
                                    <tr class="att-predefined-row" data-label="{{ $label }}">
                                        <td>
                                            <span class="att-label-badge"><i class="fa fa-file-pdf-o" style="margin-right:4px;color:#e53935;"></i>{{ $label }}</span>
                                            {{-- Hidden to carry label on submit --}}
                                            <input type="hidden" name="attachments[{{ $idx }}][label]" value="{{ $label }}">
                                            @if(!empty($existingAtt))
                                                <input type="hidden" name="attachments[{{ $idx }}][existing_id]" value="{{ $existingAtt['id'] }}">
                                            @endif
                                        </td>
                                        <td>
                                            <input type="file" name="attachments[{{ $idx }}][file]"
                                                   accept="application/pdf"
                                                   class="form-control att-file-input"
                                                   style="height:auto;padding:4px;">
                                            @if(!empty($existingAtt))
                                                <div class="att-file-info">
                                                    <i class="fa fa-check-circle text-success"></i>
                                                    File already uploaded. Upload again to replace.
                                                </div>
                                            @endif
                                        </td>
                                        <td style="text-align:center;">
                                            <input type="checkbox" name="attachments[{{ $idx }}][show_in_app]" value="1"
                                                   @if(!empty($existingAtt) && $existingAtt['show_in_app']) checked @endif
                                                   style="width:18px;height:18px;cursor:pointer;">
                                        </td>
                                        <td style="text-align:center;">
                                            @if(!empty($existingAtt))
                                                <a href="{{ url('/images/UserAttachments/'.$existingAtt['file_path']) }}"
                                                   target="_blank"
                                                   class="btn btn-xs btn-info btn-att-view"
                                                   title="View uploaded file">
                                                    <i class="fa fa-eye"></i> View
                                                </a>
                                            @else
                                                <span class="text-muted" style="font-size:11px;">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach

                                {{-- ── Extra saved attachments (not in predefined list, edit mode) ── --}}
                                @foreach($existingAttachments as $att)
                                    @if(!in_array($att['label'], $predefinedAttachmentLabels))
                                        <?php $extraIdx = 'extra_' . $att['id']; ?>
                                        <tr class="att-extra-row att-saved-extra-row">
                                            <td>
                                                <span class="att-label-badge">
                                                    <i class="fa fa-file-pdf-o" style="margin-right:4px;color:#e53935;"></i>
                                                    {{ $att['label'] }}
                                                </span>
                                                <input type="hidden" name="attachments[{{ $extraIdx }}][label]" value="{{ $att['label'] }}">
                                                <input type="hidden" name="attachments[{{ $extraIdx }}][existing_id]" value="{{ $att['id'] }}">
                                            </td>
                                            <td>
                                                <input type="file" name="attachments[{{ $extraIdx }}][file]"
                                                       accept="application/pdf"
                                                       class="form-control att-file-input"
                                                       style="height:auto;padding:4px;">
                                                <div class="att-file-info">
                                                    <i class="fa fa-check-circle text-success"></i>
                                                    File already uploaded. Upload again to replace.
                                                </div>
                                            </td>
                                            <td style="text-align:center;">
                                                <input type="checkbox" name="attachments[{{ $extraIdx }}][show_in_app]" value="1"
                                                       @if($att['show_in_app']) checked @endif
                                                       style="width:18px;height:18px;cursor:pointer;">
                                            </td>
                                            <td style="text-align:center;">
                                                <a href="{{ url('/images/UserAttachments/'.$att['file_path']) }}"
                                                   target="_blank"
                                                   class="btn btn-xs btn-info btn-att-view"
                                                   title="View">
                                                    <i class="fa fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach

                                {{-- ── New dynamic rows added via "Add More" ── --}}
                                {{-- rendered by JS below --}}

                            </tbody>
                        </table>
                    </div>

                    {{-- "Add More" button --}}
                    <div style="margin-top:12px;">
                        <button type="button" id="addMoreAttachment" class="btn btn-sm btn-default">
                            <i class="fa fa-plus"></i> Add More
                        </button>
                    </div>

                    {{-- 
                        Inline entry form — uses a plain div (NOT a nested table)
                        to avoid DOM issues with nested tables causing select2 resets.
                        Mimics the table column widths using inline-flex.
                    --}}
                    <div id="addAttachmentDropdown" style="display:none;margin-top:12px;border:1px dashed #d0d6e0;border-radius:4px;background:#fafbfd;padding:12px 10px;">
                        <div style="display:flex;align-items:flex-start;gap:12px;flex-wrap:wrap;">
                            {{-- Label --}}
                            <div style="flex:0 0 210px;">
                                <input type="text" id="newAttachmentLabel"
                                       class="form-control"
                                       placeholder="e.g. Experience Letter"
                                       style="font-size:13px;">
                                <small class="text-muted">Type any label name</small>
                            </div>
                            {{-- File --}}
                            <div style="flex:1;min-width:200px;">
                                <input type="file" id="newAttachmentFile"
                                       accept="application/pdf"
                                       class="form-control"
                                       style="height:auto;padding:4px;font-size:13px;">
                                <small class="text-muted">PDF only</small>
                            </div>
                            {{-- Show in App --}}
                            <div style="flex:0 0 120px;text-align:center;padding-top:6px;">
                                <label style="font-size:12px;color:#666;display:block;margin-bottom:4px;">Show in App</label>
                                <input type="checkbox" id="newAttachmentShowInApp"
                                       value="1"
                                       style="width:18px;height:18px;cursor:pointer;">
                            </div>
                            {{-- Actions --}}
                            <div style="flex:0 0 auto;display:flex;gap:6px;align-items:center;padding-top:2px;">
                                <button type="button" id="confirmAddAttachment" class="btn btn-sm btn-primary">
                                    <i class="fa fa-check"></i> Add Row
                                </button>
                                <button type="button" id="cancelAddAttachment" class="btn btn-sm btn-default">
                                    <i class="fa fa-times"></i> Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="form-submit-bar">
                <a href="{{ url('admin/users') }}" class="btn btn-default" style="margin-right:10px;">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
                <button class="btn btn-success btn-lg" type="submit">
                    <i class="fa fa-check"></i> &nbsp;{{ !empty($empdata['id']) ? 'Update Employee' : 'Save Employee' }}
                </button>
            </div>

        </form>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function(){

    var subToRegionMap = {};

    // ── Correspondence → Permanent address mirror ──────────────────
    $('#correspondenceAddr').on('keyup', function(){
        $('#permanentAddr').val($(this).val());
    });

    // ── App Access toggle ──────────────────────────────────────────
    $(document).on('change','[name=app_access]',function(){
        $('#AppAccessArea').hide();
        if($(this).val() == "Yes"){ $('#AppAccessArea').show(); }
    });

    // ── Web Access password toggle ─────────────────────────────────
    function togglePasswordField(){
        var v = $('[name="web_access"]').val();
        if(v && v.toLowerCase()==='yes'){ $('#web-password-group').show(); }
        else { $('#web-password-group').hide(); }
    }
    togglePasswordField();
    $('[name="web_access"]').on('change', togglePasswordField);

    // ═══════════════════════════════════════════════════════════════
    // INLINE DEPARTMENT LOGIC — No AJAX to append-designation-info.
    // Department, Report To, Region, Sub Region are all inline.
    // Only sub-region options still load via AJAX on region change.
    // ═══════════════════════════════════════════════════════════════

    // ── Department change → show/hide Report To and Region block ──
    $(document).on('change','#inline_department_id', function(){
        var selected    = $(this).find('option:selected');
        var isMarketing = selected.data('is-marketing') == '1';
        var isAdmin     = selected.data('is-admin') == '1';
        var deptVal     = $(this).val();

        // Hide Report To for Administration or when no dept selected
        if(deptVal && !isAdmin){
            $('#reportToWrapper').show();
            // Make report_to required for non-admin departments
            $('#inline_report_to').attr('required', true);
            $('.report-to-required-star').show();
        } else {
            $('#reportToWrapper').hide();
            $('#inline_report_to').val('').trigger('change.select2');
            $('#inline_report_to').removeAttr('required');
            $('.report-to-required-star').hide();
        }

        // Region block only for Marketing
        if(isMarketing){
            $('#regionSectionWrapper').show();
        } else {
            $('#regionSectionWrapper').hide();
            $('#inline_regions').val(null).trigger('change.select2');
            $('#inline_subregions').html('').val(null);
            subToRegionMap = {};
        }

        rebuildDeptJson();
    });

    // ── Report To change → rebuild JSON ───────────────────────────
    $(document).on('change','#inline_report_to', function(){
        rebuildDeptJson();
    });

    // ── Region change → fetch sub-regions via AJAX ────────────────
    $(document).on('change','.getRegions', function(){
        var selectedRegionIds = $(this).val() || [];

        if(!selectedRegionIds || selectedRegionIds.length === 0){
            $('#inline_subregions').html('');
            subToRegionMap = {};
            rebuildDeptJson();
            return;
        }

        $('.loadingDiv').show();

        // Fetch all sub-regions for display
        $.ajax({
            data : { regions: selectedRegionIds },
            url  : '/admin/get-sub-regions',
            type : 'POST',
            success: function(resp){
                $('#inline_subregions').html(resp);
                // Only initialize select2 on the subregion select specifically
                // DO NOT call refreshSelect2() globally — resets region selections
                if(typeof $.fn.select2 !== 'undefined'){
                    $('#inline_subregions').select2();
                }
                $('.loadingDiv').hide();

                // Build subToRegionMap (per-region to know parentId for each subId)
                subToRegionMap = {};
                var pending = selectedRegionIds.length;
                $.each(selectedRegionIds, function(i, regionId){
                    $.ajax({
                        data : { regions: [regionId] },
                        url  : '/admin/get-sub-regions',
                        type : 'POST',
                        success: function(singleResp){
                            $('<select>'+singleResp+'</select>').find('option').each(function(){
                                subToRegionMap[$(this).val()] = regionId;
                            });
                            pending--;
                            if(pending === 0){ rebuildDeptJson(); }
                        }
                    });
                });
            },
            error: function(){ $('.loadingDiv').hide(); }
        });
    });

    // ── Sub Region change → rebuild JSON ──────────────────────────
    // ── Sub Region change → rebuild JSON + load cities ────────────
    $(document).on('change','.subRegions', function(){
        rebuildDeptJson();
        loadCitiesForSelectedSubRegions();
    });

    // ── Load cities for currently selected sub-regions ────────────
    function loadCitiesForSelectedSubRegions(){
        var selectedSubIds = $('#inline_subregions').val() || [];

        if(!selectedSubIds || selectedSubIds.length === 0){
            $('#subRegionCitiesBox').hide();
            $('#subRegionCitiesList').html('');
            return;
        }

        $.ajax({
            url  : '/admin/get-cities-by-subregions',
            type : 'POST',
            data : {
                _token      : $('[name="_token"]').val(),
                sub_region_ids : selectedSubIds
            },
            success: function(resp){
                if(resp.status && Object.keys(resp.data).length > 0){
                    // Build sub-region name map
                    var subRegionNames = {};
                    $('#inline_subregions option').each(function(){
                        subRegionNames[$(this).val()] = $(this).text();
                    });
                    renderCitiesBox(resp.data, subRegionNames);
                } else {
                    $('#subRegionCitiesBox').hide();
                    $('#subRegionCitiesList').html('');
                }
            },
            error: function(){
                $('#subRegionCitiesBox').hide();
            }
        });
    }

    // ── Render the cities pills box ───────────────────────────────
    function renderCitiesBox(citiesBySubId, subRegionNames){
        var $list = $('#subRegionCitiesList');
        $list.html('');

        $.each(citiesBySubId, function(subId, cities){
            var regionLabel = subRegionNames[subId] || ('Sub Region ' + subId);

            $list.append(
                '<div class="city-pill-group-label">'
                + '<i class="fa fa-map-pin"></i> ' + regionLabel
                + ' <span style="font-weight:400;color:#aaa;">(' + cities.length + ' cities)</span>'
                + '</div>'
            );

            $.each(cities, function(i, city){
                $list.append(
                    '<span class="city-pill"><i class="fa fa-map-marker"></i>' + city + '</span>'
                );
            });
        });

        $('#subRegionCitiesBox').show();
    }

    // ── Select All Regions ────────────────────────────────────────
    $(document).on('change','#SelectAllRegion', function(){
        $('#inline_regions option').prop('selected', $(this).is(':checked'));
        $('#inline_regions').trigger('change');
    });

    // ── Select All Sub Regions ────────────────────────────────────
    $(document).on('change','#SelectAllSubRegion', function(){
        $('#inline_subregions option').prop('selected', $(this).is(':checked'));
        $('#inline_subregions').trigger('change');
        rebuildDeptJson();
    });

    // ── rebuildDeptJson ───────────────────────────────────────────
    // Builds the hidden input saveUser() reads.
    // Shape: { department_id, report_to, dept_regions[], products[], customer_ids[] }
    // dept_regions entries: "parentRegionId#subRegionId"
    function rebuildDeptJson(){
        var deptId   = $('#inline_department_id').val();
        if(!deptId){ $('#inline_user_dept_json').val(''); return; }

        var selected = $('#inline_department_id').find('option:selected');
        var isAdmin  = selected.data('is-admin') == '1';

        // If Administration dept, force report_to to null
        var reportTo = (isAdmin || $('#reportToWrapper').is(':hidden'))
                        ? null
                        : ($('#inline_report_to').val() || null);

        var deptRegions = [];
        $('#inline_subregions option:selected').each(function(){
            var subId = $(this).val();
            var rid   = subToRegionMap[subId];
            if(subId && rid){ deptRegions.push(rid+'#'+subId); }
        });

        $('#inline_user_dept_json').val(JSON.stringify({
            department_id : deptId,
            report_to     : reportTo,
            dept_regions  : deptRegions,
            products      : [],
            customer_ids  : []
        }));
    }

    // ── Edit page: build subToRegionMap from server-rendered data ─
    // Sub-regions are already in the DOM (rendered server-side).
    // We only need the map to build dept_regions on form submit.
    (function initEditMap(){
        var selectedRegionIds = [];
        $('#inline_regions option:selected').each(function(){
            selectedRegionIds.push($(this).val());
        });
        if(selectedRegionIds.length === 0){ rebuildDeptJson(); return; }

        subToRegionMap = {};
        var pending = selectedRegionIds.length;
        $.each(selectedRegionIds, function(i, regionId){
            $.ajax({
                data : { regions: [regionId] },
                url  : '/admin/get-sub-regions',
                type : 'POST',
                success: function(singleResp){
                    $('<select>'+singleResp+'</select>').find('option').each(function(){
                        subToRegionMap[$(this).val()] = regionId;
                    });
                    pending--;
                    if(pending === 0){
                        // Re-init only subregion select2 — NOT globally
                        if(typeof $.fn.select2 !== 'undefined'){
                            $('#inline_subregions').select2();
                        }
                        rebuildDeptJson();
                        loadCitiesForSelectedSubRegions(); // NEW
                    }
                }
            });
        });
    })();

    // On page load for edit: trigger initial rebuildDeptJson if dept selected
    if($('#inline_department_id').val()){ rebuildDeptJson(); }

    // ═══════════════════════════════════════════════════════════════
    // ATTACHMENTS — Add More logic
    // ═══════════════════════════════════════════════════════════════
    var newAttRowIndex = 1000; // Start high to avoid collisions with predefined indices

    $('#addMoreAttachment').on('click', function(){
        $('#addAttachmentDropdown').slideDown(150);
        // DO NOT call refreshSelect2() here — it would reset ALL select2 dropdowns
        // including region/sub-region, losing selected values
        $('#newAttachmentLabel').focus();
    });

    $('#cancelAddAttachment').on('click', function(){
        $('#addAttachmentDropdown').slideUp(150);
        $('#newAttachmentLabel').val('');
        $('#newAttachmentFile').val('');
        $('#newAttachmentShowInApp').prop('checked', false);
    });

    $('#confirmAddAttachment').on('click', function(){
        var label = $.trim($('#newAttachmentLabel').val());
        if(!label){
            $('#newAttachmentLabel').focus();
            alert('Please enter a label name.');
            return;
        }

        // Validate PDF
        var fileInput = document.getElementById('newAttachmentFile');
        if(fileInput.files.length > 0 && fileInput.files[0].type !== 'application/pdf'){
            alert('Only PDF files are allowed.');
            $('#newAttachmentFile').val('');
            return;
        }

        // Check duplicate label
        var alreadyExists = false;
        $('#attachmentsTbody tr').each(function(){
            if($(this).data('label') === label){
                alreadyExists = true; return false;
            }
        });
        if(alreadyExists){
            alert('A row with this label already exists.');
            return;
        }

        var idx        = newAttRowIndex++;
        var showInApp  = $('#newAttachmentShowInApp').is(':checked') ? 'checked' : '';

        // Build the new row — file input carries the already-selected file via
        // a cloned input so the file reference is preserved in the FormData.
        var $newFileInput = $('<input type="file" name="attachments['+idx+'][file]" accept="application/pdf" class="form-control att-file-input" style="height:auto;padding:4px;">');

        // Transfer the selected file by creating a DataTransfer object
        if(fileInput.files.length > 0){
            try {
                var dt = new DataTransfer();
                dt.items.add(fileInput.files[0]);
                $newFileInput[0].files = dt.files;
            } catch(e) {
                // Fallback: file won't transfer (older browsers) — user sees empty input
            }
        }

        var $row = $('<tr class="att-extra-row att-new-row" data-label="'+label+'"></tr>');

        var $tdLabel = $('<td></td>').html(
            '<span class="att-label-badge"><i class="fa fa-file-pdf-o" style="margin-right:4px;color:#e53935;"></i>'+label+'</span>'
            + '<input type="hidden" name="attachments['+idx+'][label]" value="'+label+'">'
        );

        var $tdFile = $('<td></td>').append($newFileInput);

        var $tdShow = $('<td style="text-align:center;vertical-align:middle;"></td>').html(
            '<input type="checkbox" name="attachments['+idx+'][show_in_app]" value="1" '+showInApp+' style="width:18px;height:18px;cursor:pointer;">'
        );

        var $tdAction = $('<td style="text-align:center;vertical-align:middle;"></td>').html(
            '<button type="button" class="btn btn-xs btn-danger btn-remove-att-row" title="Remove"><i class="fa fa-trash"></i></button>'
        );

        $row.append($tdLabel).append($tdFile).append($tdShow).append($tdAction);
        $('#attachmentsTbody').append($row);

        // Reset the inline form and hide it
        $('#newAttachmentLabel').val('');
        $('#newAttachmentFile').val('');
        $('#newAttachmentShowInApp').prop('checked', false);
        $('#addAttachmentDropdown').slideUp(150);
        // DO NOT call refreshSelect2() here — it resets region/subregion selections
    });

    // Remove new row (only new rows have the delete button)
    $(document).on('click', '.btn-remove-att-row', function(){
        $(this).closest('tr').remove();
    });

    // PDF-only validation on file inputs
    $(document).on('change', '.att-file-input', function(){
        var file = this.files[0];
        if(file && file.type !== 'application/pdf'){
            alert('Only PDF files are allowed for attachments.');
            $(this).val('');
        }
    });
    // ── Preload cities on edit page ───────────────────────────────
    (function preloadCities(){
        var preloaded = @json($preloadedCitiesBySubRegion ?? []);
        if(Object.keys(preloaded).length === 0){ return; }

        // Build sub-region name map from the select options
        var subRegionNames = {};
        $('#inline_subregions option').each(function(){
            subRegionNames[$(this).val()] = $(this).text();
        });

        renderCitiesBox(preloaded, subRegionNames);
    })();
    // ═══════════════════════════════════════════════════════════════
    // FORM SUBMIT
    // ═══════════════════════════════════════════════════════════════
    $("#Employeeform").submit(function(e){
        e.preventDefault();
        rebuildDeptJson();

        var deptJsonVal = $('#inline_user_dept_json').val();
        if(!deptJsonVal){
            $('#Employee-user_depts').attr('style','').html('Please select a department').show();
            setTimeout(function(){ $('#Employee-user_depts').hide(); }, 5000);
            $('html,body').animate({ scrollTop: $('#inline_department_id').offset().top - 150 }, 800);
            return;
        }

        // Validate: report_to mandatory for non-Administration departments
        var selected = $('#inline_department_id').find('option:selected');
        var isAdmin  = selected.data('is-admin') == '1';
        if(!isAdmin && $('#reportToWrapper').is(':visible') && !$('#inline_report_to').val()){
            $('#Employee-user_depts').attr('style','').html('Please select a Report To person').show();
            setTimeout(function(){ $('#Employee-user_depts').hide(); }, 5000);
            $('html,body').animate({ scrollTop: $('#inline_report_to').offset().top - 150 }, 800);
            return;
        }

        $('.loadingDiv').show();
        var formdata = new FormData(this);

        $.ajax({
            url         : '/admin/save-user',
            type        : 'POST',
            data        : formdata,
            processData : false,
            contentType : false,
            success: function(data){
                $('.loadingDiv').hide();
                if(!data.status){
                    $.each(data.errors, function(i, error){
                        $('#Employee-'+i).addClass('error-triggered').attr('style','').html(error);
                        setTimeout(function(){
                            $('#Employee-'+i).css({'display':'none'}).removeClass('error-triggered');
                        }, 5000);
                    });
                    $('html,body').animate({
                        scrollTop: $('.error-triggered').first().stop().offset().top - 150
                    }, 1000);
                } else {
                    window.location.href = data.url;
                }
            }
        });
    });

}); // end ready
</script>
@endsection