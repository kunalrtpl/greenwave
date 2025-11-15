@extends('layouts.adminLayout.backendLayout')
@section('content')

<div class="page-content-wrapper">
    <div class="page-content">

        <div class="page-head">
            <div class="page-title">
                <h1>Attendance Report Generator</h1>
            </div>
        </div>

        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{ url('admin/dashboard') }}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <span>Attendance Report</span>
            </li>
        </ul>

        <div class="row">
            <div class="col-md-12 ">
                <div class="portlet blue-hoki box">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-calendar"></i> Generate Attendance PDF
                        </div>
                    </div>

                    <div class="portlet-body form">

                        {{-- 🔥 SIMPLE DIRECT SUBMIT FORM --}}
                        <form method="POST" action="{{ route('attendance.generate') }}" class="form-horizontal">
                            @csrf

                            <div class="form-body">

                                {{-- USER --}}
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Employee <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <select name="user_id" class="form-control select2" required>
                                            <option value="">Please Select</option>
                                            @foreach($users as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- MONTH --}}
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Month <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <select name="month" class="form-control">
                                            @for($m=1;$m<=12;$m++)
                                                <option value="{{ $m }}" {{ $m == $currentMonth ? 'selected' : '' }}>
                                                    {{ date("F", mktime(0,0,0,$m,1)) }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>

                                {{-- YEAR --}}
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Year <span class="asteric">*</span></label>
                                    <div class="col-md-4">
                                        <select name="year" class="form-control">
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
                                    Generate PDF
                                </button>
                            </div>

                        </form>
                        {{-- END FORM --}}

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
