@extends('layouts.adminLayout.backendLayout')
@section('content')
<style>
.table-scrollable table tbody tr td{
    vertical-align: middle;
}
</style>
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-head">
            <div class="page-title">
                <h1>Customers Management</h1>
            </div>
        </div>
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{url('admin/dashboard')}}">Dashboard</a>
            </li>
        </ul>
         @if(Session::has('flash_message_error'))
            <div role="alert" class="alert alert-danger alert-dismissible fade in"> <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"><span aria-hidden="true"></span></button> <strong>Error!</strong> {!! session('flash_message_error') !!} </div>
        @endif
        @if(isset($_GET['s']))
            <div role="alert" class="alert alert-success alert-dismissible fade in"> <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"><span aria-hidden="true"></span></button> <strong>Success!</strong> Record has been updated Sucessfully. </div>
        @endif
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-sharp bold uppercase">Customer Registration Requests</span>
                            <span class="caption-helper">manage records...</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-container">
                            <table class="table table-striped table-bordered table-hover" id="datatable_ajax">
                                <thead>
                                    <tr role="row" class="heading">
                                        <th>
                                            Date
                                        </th>
                                        <th>
                                            Creator
                                        </th>
                                        <th>
                                            Name
                                        </th>
                                        <th>
                                            City
                                        </th>
                                        <th>
                                            Status
                                        </th>
                                        <th>
                                            Actions
                                        </th>
                                    </tr>
                                    <tr role="row" class="filter">
                                        <td>
                                            
                                        </td>
                                        <td>
                                            
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-filter input-sm" name="name" placeholder="Name">
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td>
                                            <div class="margin-bottom-5">
                                                <button class="btn btn-sm yellow filter-submit margin-bottom"><i title="Search" class="fa fa-search"></i></button>
                                                <button class="btn btn-sm red filter-cancel"><i title="Reset" class="fa fa-refresh"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="customerDetailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailsModalLabel">Customer Register Request Details</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="detailsContent">
        <!-- Details will be loaded here -->
        <div class="text-center">Loading...</div>
      </div>
    </div>
  </div>
</div>
<!-- Close Reason Modal -->
<div class="modal fade" id="closeReasonModal" tabindex="-1" role="dialog" aria-labelledby="closeModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form id="closeRequestForm">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title" id="closeModalLabel">Close Request</h4>
        </div>
        <div class="modal-body">
          <input type="hidden" id="closeRequestId">
          <div class="form-group">
            <label for="closeRemarks">Reason for Closing:</label>
            <textarea class="form-control" id="closeRemarks" name="close_remarks" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger">Submit</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    $(document).on('click','.view-details-btn',function() {
        var id = $(this).data('id');
        $('#customerDetailsModal').modal('show');

        // Fetch data using AJAX
        $.ajax({
            url: '/admin/customer-register-request/' + id,
            method: 'GET',
            success: function(response) {
                $('#detailsContent').html(response);
            },
            error: function() {
                $('#detailsContent').html('<div class="text-danger">Failed to load data.</div>');
            }
        });
    });
});
</script>
<script>
$(document).ready(function () {
    // Handle submit for verify form
    $(document).on('submit', '#verifyForm', function (e) {
        e.preventDefault();

        if (!$('#verifyCheckbox').is(':checked')) {
            alert('Please check the verification checkbox.');
            return;
        }

        const id = $('[name=customer_register_request_id]').val(); // pass `data-id` correctly
        const remarks = $('#verifyRemarks').val();

        $.ajax({
            url: '/admin/customer-register-request/' + id + '/verify',
            method: 'POST',
            data: {
                verify_remarks: remarks,
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                alert(response.message);
                $('#customerDetailsModal').modal('hide');
                location.reload(); // Optional: reload the page or list
            },
            error: function (xhr) {
                alert(xhr.responseJSON.message || 'Verification failed.');
            }
        });
    });
});
</script>
<script>
$(document).ready(function() {
    let closeId;

    // Open the modal
    $(document).on('click', '.open-close-modal-btn', function() {
        closeId = $(this).data('id');
        $('#closeRequestId').val(closeId);
        $('#closeRemarks').val('');
        $('#closeReasonModal').modal('show');
    });

    // Submit close request
    $('#closeRequestForm').submit(function(e) {
        e.preventDefault();
        var remarks = $('#closeRemarks').val();
        var id = $('#closeRequestId').val();

        $.ajax({
            url: '/admin/close-customer-register-request/' + id,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                close_remarks: remarks
            },
            success: function(response) {
                alert('Request closed successfully.');
                $('#closeReasonModal').modal('hide');
                location.reload();
            },
            error: function(xhr) {
                alert('Failed to close request.');
            }
        });
    });
});
</script>

@stop


