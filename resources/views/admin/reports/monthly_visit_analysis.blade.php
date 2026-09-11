@extends('layouts.adminLayout.backendLayout')

@section('content')
<style>
    .page-content { padding-bottom: 40px !important; }

    .portlet.light.bordered {
        border-radius: 6px;
        box-shadow: 0 1px 8px rgba(0,0,0,0.08);
        border: 1px solid #dde3ec;
    }

    /* ── Filter strip — same pattern as the Pending Orders report ── */
    .filter-strip {
        display: flex; align-items: flex-end; gap: 10px; flex-wrap: wrap;
        background: #f4f6fa; border: 1px solid #dde3ec;
        border-radius: 6px; padding: 10px 14px; margin-bottom: 14px;
    }
    .filter-strip .fg { display: flex; flex-direction: column; gap: 3px; flex-shrink: 0; }
    .filter-strip .fg > label {
        font-size: 10px; font-weight: 700; color: #5a6a85;
        text-transform: uppercase; letter-spacing: 0.5px;
        margin: 0; white-space: nowrap;
    }
    .filter-strip select {
        height: 30px; border: 1px solid #c8d0dc; border-radius: 4px !important;
        font-size: 12px; padding: 0 8px; background: #fff; color: #2d3748;
    }
    #filter-month { width: 170px; }
    #filter-year  { width: 110px; }

    .f-sep { width: 1px; height: 30px; background: #d5dbe8; flex-shrink: 0; margin: 0 2px; }

    .btn-apply {
        height: 30px; padding: 0 16px; font-size: 12px; font-weight: 700;
        border-radius: 4px !important; border: 1px solid #3598dc;
        background: #3598dc; color: #fff; cursor: pointer;
        text-transform: uppercase; letter-spacing: 0.4px;
        display: inline-flex; align-items: center; gap: 5px;
        transition: all 0.15s; white-space: nowrap;
    }
    .btn-apply:hover { background: #2a80c0; color: #fff; }

    .month-note {
        margin-left: auto; font-size: 11.5px; color: #718096;
        display: flex; align-items: center; gap: 6px; padding-bottom: 5px;
    }
    .month-note strong { color: #2d3748; }

    /* ── Employee table ── */
    .report-wrap { width: 100%; overflow-x: auto; }
    .report-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .report-table thead tr th {
        background: #eef1f7; color: #4a5568; font-weight: 700; font-size: 11px;
        text-transform: uppercase; letter-spacing: 0.55px; padding: 10px 12px;
        border: 1px solid #d5dbe8; white-space: nowrap; text-align: left;
    }
    .report-table thead tr th.center { text-align: center; }
    .report-table tbody td {
        padding: 8px 12px; border: 1px solid #e4e9f2; vertical-align: middle;
        color: #2d3748; background: #fff;
    }
    .report-table tbody td.center { text-align: center; }
    .report-table tbody tr:nth-child(even) td { background: #fafbfd; }
    .report-table tbody tr:hover td { background: #f8faff; }

    .emp-name { font-weight: 600; color: #2d3748; }
    .emp-meta { font-size: 11px; color: #a0aec0; }

    .btn-generate-row {
        display: inline-flex; align-items: center; gap: 5px;
        height: 28px; padding: 0 12px; font-size: 11px; font-weight: 700;
        border-radius: 4px !important; border: 1px solid #e53e3e;
        background: #fff5f5; color: #c53030; cursor: pointer;
        text-decoration: none; white-space: nowrap; transition: all 0.15s;
        text-transform: uppercase; letter-spacing: 0.4px;
    }
    .btn-generate-row:hover { background: #e53e3e; color: #fff; text-decoration: none; }

    .empty-state { text-align: center; padding: 40px 20px; color: #a0aec0; font-style: italic; }
</style>

<div class="page-content-wrapper">
    <div class="page-content">
        <div class="portlet light bordered">

            <div class="portlet-title">
                <div class="caption font-blue-sharp">
                    <i class="fa fa-line-chart font-blue-sharp"></i>
                    <span class="caption-subject bold uppercase">Monthly Visit Analysis</span>
                </div>
            </div>

            <div class="portlet-body">

                @if(Session::has('flash_message_error'))
                <div role="alert" class="alert alert-danger alert-dismissible fade in">
                    <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"><span aria-hidden="true"></span></button>
                    <strong>Error!</strong> {!! session('flash_message_error') !!}
                </div>
                @endif

                {{-- Month / Year selection --}}
                <form method="GET" action="{{ route('admin.monthly-visit-analysis.index') }}" id="month-form">
                    <div class="filter-strip">

                        <div class="fg">
                            <label><i class="fa fa-calendar"></i> Month</label>
                            <select name="month" id="filter-month">
                                @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                                @endfor
                            </select>
                        </div>

                        <div class="fg">
                            <label><i class="fa fa-calendar-o"></i> Year</label>
                            <select name="year" id="filter-year">
                                @for($y = (int) date('Y'); $y >= $firstYear; $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        <span class="f-sep"></span>

                        <div class="fg">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn-apply">
                                <i class="fa fa-refresh"></i> Apply
                            </button>
                        </div>

                        <div class="month-note">
                            <i class="fa fa-info-circle"></i>
                            Reports will be generated for <strong>{{ $monthLabel }}</strong>
                        </div>

                    </div>
                </form>

                {{-- Marketing employees this report is produced for --}}
                <div class="report-wrap">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th class="center" style="width:60px">#</th>
                                <th>Employee</th>
                                <th style="width:260px">Email</th>
                                <th class="center" style="width:200px">Report</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $i => $emp)
                            <tr>
                                <td class="center">{{ $i + 1 }}</td>
                                <td>
                                    <span class="emp-name">{{ $emp->name }}</span>
                                    @if(!empty($emp->designation))
                                    <span class="emp-meta">&nbsp;— {{ $emp->designation }}</span>
                                    @endif
                                </td>
                                <td>{{ $emp->email }}</td>
                                <td class="center">
                                    <a class="btn-generate-row"
                                       target="_blank"
                                       href="{{ route('admin.monthly-visit-analysis.pdf', ['user_id' => $emp->id, 'month' => $month, 'year' => $year]) }}">
                                        <i class="fa fa-file-pdf-o"></i> Generate PDF
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="empty-state">No marketing employees are eligible for this report.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    // Keep every row's PDF link in step with the month/year pickers without
    // needing an "Apply" round-trip first.
    function syncPdfLinks() {
        var month = $('#filter-month').val();
        var year  = $('#filter-year').val();

        $('.btn-generate-row').each(function () {
            var url = new URL($(this).attr('href'), window.location.origin);
            url.searchParams.set('month', month);
            url.searchParams.set('year', year);
            $(this).attr('href', url.toString());
        });

        $('.month-note strong').text(
            $('#filter-month option:selected').text() + ' ' + year
        );
    }

    $('#filter-month, #filter-year').on('change', syncPdfLinks);
});
</script>

@endsection
