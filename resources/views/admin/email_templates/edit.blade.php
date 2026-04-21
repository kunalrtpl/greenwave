@extends('layouts.adminLayout.backendLayout')

@section('content')
<div class="page-content-wrapper">
<div class="page-content">

    <div class="page-head">
        <div class="page-title">
            <h1>Edit Email Template</h1>
        </div>
    </div>

    <ul class="page-breadcrumb breadcrumb">
        <li><a href="{{ url('admin/dashboard') }}">Dashboard</a></li>
        <li><a href="{{ url('admin/email-templates') }}">Email Templates</a></li>
        <li class="active">Edit</li>
    </ul>

    <div class="portlet light">
        <div class="portlet-title">
            <div class="caption">
                <i class="icon-envelope font-green"></i>
                <span class="caption-subject font-green bold uppercase">Edit: {{ $template->name }}</span>
            </div>
        </div>
        <div class="portlet-body">

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul style="margin: 0; padding-left: 18px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ url('admin/email-templates/' . $template->id) }}" method="POST">
                {{ csrf_field() }}

                {{-- ── Read-only Info ── --}}
                <div class="portlet light" style="border: 1px solid #e5e5e5; margin-bottom: 20px;">
                    <div class="portlet-title" style="padding: 10px 16px; background: #f9f9f9; border-bottom: 1px solid #e5e5e5;">
                        <div class="caption">
                            <i class="fa fa-info-circle font-blue"></i>
                            <span class="caption-subject font-blue bold">Template Info (Read Only)</span>
                        </div>
                    </div>
                    <div class="portlet-body" style="padding: 16px;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Event Key</label>
                                    <p class="form-control-static">
                                        <code>{{ $template->event_key }}</code>
                                        <small class="text-muted" style="margin-left: 8px;">— used in EmailService::send()</small>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label">Blade View</label>
                                    <p class="form-control-static">
                                        <code>{{ $template->blade_view }}</code>
                                        <small class="text-muted" style="margin-left: 8px;">— email template file</small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Editable Fields ── --}}
                <div class="row">

                    {{-- Name --}}
                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                            <label class="control-label">Template Name <span class="required">*</span></label>
                            <input type="text"
                                   name="name"
                                   class="form-control"
                                   value="{{ old('name', $template->name) }}"
                                   placeholder="e.g. Admin — New PO Created">
                            @if($errors->has('name'))
                                <span class="help-block">{{ $errors->first('name') }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">Status</label>
                            <div class="mt-checkbox-list" style="padding-top: 6px;">
                                <label class="mt-checkbox mt-checkbox-outline">
                                    <input type="checkbox"
                                           name="is_active"
                                           value="1"
                                           {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                                    Active (uncheck to disable this template)
                                    <span></span>
                                </label>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Subject --}}
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group {{ $errors->has('subject') ? 'has-error' : '' }}">
                            <label class="control-label">
                                Email Subject <span class="required">*</span>
                            </label>
                            <input type="text"
                                   name="subject"
                                   class="form-control"
                                   value="{{ old('subject', $template->subject) }}"
                                   placeholder="e.g. New Purchase Order Received - {po.po_ref_no_string}">
                            @if($errors->has('subject'))
                                <span class="help-block">{{ $errors->first('subject') }}</span>
                            @endif
                            <span class="help-block" style="color: #888;">
                                <i class="fa fa-info-circle"></i>
                                Use <code>{variable.field}</code> for dynamic values.
                                Example: <code>{po.po_ref_no_string}</code>, <code>{user.name}</code>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- TO Emails --}}
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label">TO Emails</label>
                            <input type="text"
                                   name="to_emails"
                                   class="form-control"
                                   value="{{ old('to_emails', implode(', ', (array) ($template->to_emails ?? []))) }}"
                                   placeholder="admin@example.com, manager@example.com">
                            <span class="help-block" style="color: #888;">
                                <i class="fa fa-info-circle"></i>
                                Comma-separated email addresses. Leave empty if recipient is set dynamically in code (e.g. dealer or customer email).
                            </span>
                        </div>
                    </div>
                </div>

                {{-- CC and BCC --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">CC Emails</label>
                            <input type="text"
                                   name="cc_emails"
                                   class="form-control"
                                   value="{{ old('cc_emails', implode(', ', (array) ($template->cc_emails ?? []))) }}"
                                   placeholder="cc@example.com, another@example.com">
                            <span class="help-block" style="color: #888;">
                                <i class="fa fa-info-circle"></i>
                                Comma-separated. Leave empty to skip CC.
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">BCC Emails</label>
                            <input type="text"
                                   name="bcc_emails"
                                   class="form-control"
                                   value="{{ old('bcc_emails', implode(', ', (array) ($template->bcc_emails ?? []))) }}"
                                   placeholder="bcc@example.com">
                            <span class="help-block" style="color: #888;">
                                <i class="fa fa-info-circle"></i>
                                Comma-separated. Leave empty to skip BCC.
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-actions" style="padding: 16px 0 0; border-top: 1px solid #eee; margin-top: 10px;">
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-save"></i> Save Changes
                            </button>
                            <a href="{{ url('admin/email-templates') }}" class="btn btn-default" style="margin-left: 8px;">
                                <i class="fa fa-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>

            </form>

        </div>
    </div>

</div>
</div>
@endsection