@extends('layouts.adminLayout.backendLayout')

@section('content')

<div class="page-content-wrapper">
    <div class="page-content">

        {{-- PAGE TITLE --}}
        <div class="page-head">
            <div class="page-title">
                <h1>Attendance Viewer</h1>
            </div>
        </div>

        {{-- BREADCRUMB --}}
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{ url('admin/dashboard') }}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <span>Attendance View</span>
            </li>
        </ul>

        {{-- FILTER FORM --}}
        <div class="row">
            <div class="col-md-12">
                <div class="portlet blue-hoki box">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-filter"></i> Filter Attendance
                        </div>
                    </div>
                    <div class="portlet-body form">

                        <form method="GET" class="form-horizontal">
                            {{-- This hidden input tells controller that filter is applied --}}
                            <input type="hidden" name="filter" value="1">

                            <div class="form-body">

                                {{-- Employee --}}
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Employee <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <select name="user_id" class="form-control select2" required>
                                            <option value="">Please Select</option>
                                            @foreach($users as $u)
                                                <option value="{{ $u->id }}" {{ $selectedUser == $u->id ? 'selected' : '' }}>
                                                    {{ $u->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Month --}}
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Month <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <select name="month" class="form-control" required>
                                            @for($m=1;$m<=12;$m++)
                                                <option value="{{ $m }}" {{ $m == $currentMonth ? 'selected' : '' }}>
                                                    {{ date("F", mktime(0,0,0,$m,1)) }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>

                                {{-- Year --}}
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Year <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <select name="year" class="form-control" required>
                                            <option value="">Please Select</option>
                                            @foreach($years as $y)
                                                <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>
                                                    {{ $y }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                            </div>

                            <div class="form-actions text-center">
                                <button type="submit" class="btn green">
                                    Show Attendance
                                </button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>

        <style>
            .present-row  { background:#d6f5d6; }   /* light green */
            .absent-row   { background:#ffcccc; }   /* light red */
            .leave-row    { background:#fff0b3; }   /* light yellow */
            .holiday-row  { background:#e6e6e6; }   /* light grey */
            .late-text    { color:#ff0000; font-weight:bold; }
        </style>


        {{-- RESULTS TABLE (ONLY IF DATA EXISTS) --}}
        @if(!empty($attendanceData))
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet box green">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="fa fa-table"></i> Attendance Result
                            </div>
                        </div>
                        <div class="portlet-body">

                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th style="width:110px;">Date</th>
                                            <th style="width:70px;">In</th>
                                            <th style="width:70px;">Out</th>
                                            <th style="width:90px;">Status</th>
                                            <th>Remarks</th>
                                            <th style="width:60px;">Calc</th>
                                            <th style="width:110px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($attendanceData as $d)

                                            @php
                                                // Match PDF logic for row color
                                                $rowClass = $d['status'] == 'Present' ? 'present-row' :
                                                            ($d['status'] == 'Absent' ? 'absent-row' :
                                                            ($d['status'] == 'Leave' ? 'leave-row' : 'holiday-row'));

                                                // Late flag (same as PDF: check "Late" in remarks)
                                                $isLate = strpos($d['remarks'], 'Late') !== false;
                                            @endphp

                                            <tr class="{{ $rowClass }}">
                                                <td>
                                                    {{ \Carbon\Carbon::parse($d['date'])->format('d/m/Y') }}
                                                    <br>
                                                    <small>{{ $d['day'] }}</small>
                                                </td>

                                                {{-- In time (red if late) --}}
                                                <td class="{{ $isLate ? 'late-text' : '' }}">
                                                    @if($d['status'] == 'Present')
                                                    {{ $d['in'] }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>

                                                <td>{{ $d['out'] }}</td>
                                                <td>
                                                {{ $d['status'] }}
                                                {{-- Check if edited by admin --}}
                                                @if(isset($d['is_edited']) && $d['is_edited'] == 1)
                                                    <span title="Edited by Admin" 
                                                          style="background:#0052cc;color:white;font-size:10px;padding:2px 4px;border-radius:3px;margin-left:4px;cursor:pointer;">
                                                        E
                                                    </span>
                                                @endif
                                                </td>

                                                {{-- Remarks: red if late --}}
                                                <td>
                                                    @if($isLate)
                                                        <span class="late-text">{{ $d['remarks'] }}</span>
                                                    @else
                                                        {{ $d['remarks'] }}
                                                    @endif
                                                </td>

                                                <td>{{ $d['calc'] }}</td>

                                                <td>
                                                    {{-- Hide Change Status button on Sundays --}}
                                                    @if($d['day'] != 'Sun')
                                                        <button type="button"
                                                            class="btn btn-xs blue editAttendanceBtn"
                                                            data-date="{{ $d['date'] }}"
                                                            data-status="{{ $d['status'] }}">
                                                            Change Status
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>

                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        @endif



        {{-- MODAL FOR CHANGE STATUS --}}
        <div class="modal fade" id="statusModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">

                <form id="updateAttendanceForm">
                    @csrf

                    <input type="hidden" name="user_id" value="{{ $selectedUser }}">
                    <input type="hidden" name="date" id="modalDate">

                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Change Attendance Status</h4>
                        </div>
                        <div class="modal-body">

                            <div class="form-group">
                                <label>Date</label>
                                <input type="text" id="modalDateLabel" class="form-control" disabled>
                            </div>

                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" id="modalStatus" class="form-control" required>
                                    <option value="">Please Select</option>
                                    <option value="present">Present (Full Day)</option>
                                    <option value="half_day">Present (Half Day)</option>
                                    <option value="absent">Absent</option>
                                    <option value="leave">Leave</option>
                                    <option value="holiday">Holiday</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Remarks (optional)</label>
                                <textarea name="remarks" class="form-control" rows="2"></textarea>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn default" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn green">Update</button>
                        </div>
                    </div>

                </form>

            </div>
        </div>

    </div>
</div>

<script>
    // When "Change Status" clicked
    $(document).on('click', '.editAttendanceBtn', function () {
        var date   = $(this).data('date');
        var status = $(this).data('status');

        $('#modalDate').val(date);
        $('#modalDateLabel').val(date);
        //$('#modalStatus').val(status); // Pre-select current (if matching one of options)

        $('#statusModal').modal('show');
    });

    // Handle AJAX submit
    $('#updateAttendanceForm').on('submit', function (e) {
        e.preventDefault();

        var form = $(this);

        $.ajax({
            url: "{{ url('admin/attendance/update-status') }}",
            method: "POST",
            data: form.serialize(),
            success: function (resp) {
                if (resp.status) {
                    alert(resp.message || 'Updated successfully.');
                    $('#statusModal').modal('hide');
                    // Reload page to reflect changes
                    window.location.reload();
                } else {
                    alert(resp.message || 'Something went wrong.');
                }
            },
            error: function (xhr) {
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    alert(xhr.responseJSON.message);
                } else {
                    alert('Server error, please try again.');
                }
            }
        });
    });
</script>
@endsection