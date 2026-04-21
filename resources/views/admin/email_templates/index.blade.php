@extends('layouts.adminLayout.backendLayout')

@section('content')
<div class="page-content-wrapper">
<div class="page-content">

    <div class="page-head">
        <div class="page-title">
            <h1>Email Templates</h1>
        </div>
    </div>

    <ul class="page-breadcrumb breadcrumb">
        <li><a href="{{ url('admin/dashboard') }}">Dashboard</a></li>
        <li class="active">Email Templates</li>
    </ul>

    @if(session('flash_message_success'))
        <div class="alert alert-success">
            <strong>Success!</strong> {{ session('flash_message_success') }}
        </div>
    @endif

    <div class="portlet light">
        <div class="portlet-title">
            <div class="caption">
                <i class="icon-envelope font-green"></i>
                <span class="caption-subject font-green bold uppercase">Email Templates</span>
            </div>
        </div>
        <div class="portlet-body">

            <table class="table table-striped table-bordered table-hover" id="email-templates-table">
                <thead>
                    <tr>
                        <th style="width: 30px;">#</th>
                        <th>Name</th>
                        <th>Event Key</th>
                        <th>Subject</th>
                        <th>Blade View</th>
                        <th style="width: 80px; text-align: center;">Status</th>
                        <th style="width: 80px; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($templates as $i => $template)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><strong>{{ $template->name }}</strong></td>
                        <td><code>{{ $template->event_key }}</code></td>
                        <td>{{ $template->subject }}</td>
                        <td><code>{{ $template->blade_view }}</code></td>
                        <td style="text-align: center;">
                            @if($template->is_active)
                                <span class="label label-success">Active</span>
                            @else
                                <span class="label label-danger">Inactive</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <a href="{{ url('admin/email-templates/' . $template->id . '/edit') }}"
                               class="btn btn-xs btn-warning">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>

</div>
</div>
<script>
    $(document).ready(function () {
        $('#email-templates-table').DataTable({
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: false, targets: [6] }
            ]
        });
    });
</script>
@endsection