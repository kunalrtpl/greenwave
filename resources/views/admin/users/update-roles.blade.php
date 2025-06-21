@extends('layouts.adminLayout.backendLayout')
@section('content')
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-head">
            <div class="page-title">
                <h1>Employee Management</h1>
            </div>
        </div>
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{!! action('Admin\AdminController@dashboard') !!}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{ url('/admin/users') }}">Employees</a>
            </li>
        </ul>
        @if(Session::has('flash_message_success'))
            <div role="alert" class="alert alert-success alert-dismissible fade in">
                <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>Success!</strong> {!! session('flash_message_success') !!}
            </div>
        @endif

        <div class="row">
            <div class="col-md-12">
                <div class="portlet blue-hoki box">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-gift"></i>{{ $title }}
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form id="subadminForm" role="form" class="form-horizontal" method="post" action="{{ url('admin/update-role/'.$userid) }}">
                            @csrf
                            <div class="form-body">

                                @foreach($getModules as $module)
                                    @php
                                        $roleDetail = $getRoleDetails->firstWhere('module_id', $module['id']);

                                        $viewChecked = $roleDetail && $roleDetail['view_access'] == 1 ? 'checked' : '';
                                        $editChecked = $roleDetail && $roleDetail['edit_access'] == 1 ? 'checked' : '';
                                        $deleteChecked = $roleDetail && $roleDetail['delete_access'] == 1 ? 'checked' : '';

                                        // Decode JSON extra_permissions or default empty array
                                        $extraPermissions = [];
                                        if ($roleDetail && !empty($roleDetail['extra_permissions'])) {
                                            $extraPermissions = json_decode($roleDetail['extra_permissions'], true) ?: [];
                                        }

                                        // Parse extra_fields from module (comma separated string)
                                        $extraFields = [];
                                        if (!empty($module['extra_fields'])) {
                                            $extraFields = explode(',', $module['extra_fields']);
                                        }
                                    @endphp

                                    <input type="hidden" name="module_id[{{ $module['id'] }}][id]" value="{{ $module['id'] }}">

                                    <div class="form-group">
                                        <label class="col-md-3 control-label">{{ $module['name'] }}:</label>
                                        <div class="col-md-9">

                                            <label class="checkbox-inline" style="margin-right: 15px;">
                                                <input type="checkbox" name="module_id[{{ $module['id'] }}][view_access]" value="1" {{ $viewChecked }}>
                                                View Only
                                            </label>

                                            @if(!empty($module['edit_route']))
                                                <label class="checkbox-inline" style="margin-right: 15px;">
                                                    <input type="checkbox" name="module_id[{{ $module['id'] }}][edit_access]" value="1" {{ $editChecked }}>
                                                    View/Edit
                                                </label>
                                            @endif

                                            @if(!empty($module['delete_route']))
                                                <label class="checkbox-inline" style="margin-right: 15px;">
                                                    <input type="checkbox" name="module_id[{{ $module['id'] }}][delete_access]" value="1" {{ $deleteChecked }}>
                                                    View/Edit/Delete
                                                </label>
                                            @endif

                                            {{-- Extra permissions checkboxes --}}
                                            @foreach($extraFields as $field)
                                                @php $field = trim($field); @endphp
                                                <label class="checkbox-inline" style="margin-right: 15px;">
                                                    <input type="checkbox"
                                                           name="module_id[{{ $module['id'] }}][extra_permissions][{{ $field }}]"
                                                           value="1"
                                                           {{ isset($extraPermissions[$field]) && $extraPermissions[$field] == 1 ? 'checked' : '' }}>
                                                    {{ ucfirst(str_replace('_', ' ', $field)) }}
                                                </label>
                                            @endforeach

                                        </div>
                                    </div>
                                @endforeach

                                <hr class="bold-hr">

                                {{-- Your other inventory & status access selects here... --}}
                                <?php 
                                $materialStatusArr = materialStatus(); 
                                array_push($materialStatusArr, 'QC Approved', 'QC Rejected');
                                ?>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">View Inventory Status Access:</label>
                                    <div class="col-md-4">
                                        @php $selViewInvAccess = explode(',', $userinfo['view_inventory_access']); @endphp
                                        <select class="form-control select2" name="view_inventory_access[]" multiple>
                                            @foreach($materialStatusArr as $materialStatus)
                                                <option value="{{ $materialStatus }}" @if(in_array($materialStatus, $selViewInvAccess)) selected @endif>{{ $materialStatus }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-3 control-label">Update Inventory Status Access:</label>
                                    <div class="col-md-4">
                                        @php $selUpdateInvAccess = explode(',', $userinfo['update_inventory_access']); @endphp
                                        <select class="form-control select2" name="update_inventory_access[]" multiple>
                                            @foreach($materialStatusArr as $materialStatus)
                                                <option value="{{ $materialStatus }}" @if(in_array($materialStatus, $selUpdateInvAccess)) selected @endif>{{ $materialStatus }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Repeat similarly for OSP and IHP status access sections as you have in original code --}}

                            </div>

                            <div class="form-actions right1 text-center">
                                <button class="btn green" type="submit">Submit</button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
