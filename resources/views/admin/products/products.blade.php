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
                <h1>Products Management</h1>
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
                            <span class="caption-subject font-green-sharp bold uppercase">Products</span>
                            <span class="caption-helper">manage records...</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-toolbar">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="btn-group">
                                       <a href="{{action('Admin\ProductsController@addEditProduct')}}" class="btn btn-primary">Add Product</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="table-container">
                            <table class="table table-striped table-bordered table-hover" id="datatable_ajax">
                                <thead>
                                    <tr role="row" class="heading">
                                        <th class="text-center" width="4%">S.No.</th>
                                        <th>Product Type</th>
                                        <th>Product Name</th>
                                        <th class="text-center">Not Available</th>
                                        <th class="text-center">Discontinued</th> {{-- NEW --}}
                                        <th>Version</th>
                                        <th class="text-right">Dealer Price</th>
                                        <th class="text-center">Status</th>
                                        <th>Actions</th>
                                    </tr>
                                    <tr role="row" class="filter">
                                        <td></td>
                                        <td>
                                            <div class="form-group">
                                                <select class="form-control form-filter input-sm" name="product_type">
                                                    <option value="-1">All</option>
                                                    @foreach(product_types() as $key=> $productType)
                                                        <option value="{{$key}}">{{$productType}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-filter input-sm" name="product_name" placeholder="Product Name">
                                        </td>
                                        <td>
                                            <div class="form-group">
                                                <select class="form-control form-filter input-sm" name="not_available">
                                                    <option value="-1">All</option>
                                                    <option value="1">Yes</option>
                                                    <option value="0">No</option>
                                                </select>
                                            </div>
                                        </td>
                                        <td> {{-- NEW filter for discontinued --}}
                                            <div class="form-group">
                                                <select class="form-control form-filter input-sm" name="discontinued">
                                                    <option value="-1">All</option>
                                                    <option value="1">Yes</option>
                                                    <option value="0">No</option>
                                                </select>
                                            </div>
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td>
                                            <div class="form-group">
                                                <select class="form-control form-filter input-sm" name="status">
                                                    <option value="All">All</option>
                                                    <option value="Active">Active</option>
                                                    <option value="Inactive">Inactive</option>
                                                </select>
                                            </div>
                                        </td>
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
<script type="text/javascript">
    $(document).on('change', '.not_available_toggle', function() {
        var $this = $(this);
        var productId = $this.data('id');
        var value = $this.is(':checked') ? 1 : 0;

        var $row = $this.closest('tr');
        var $discontinued = $row.find('.discontinued_toggle');

        if(value == 1 && $discontinued.is(':checked')) {
            alert('Cannot mark as Not Available because this product is already marked as Discontinued.');
            $this.prop('checked', false);
            return;
        }

        // disable/enable the other checkbox
        if(value == 1) {
            $discontinued.prop('disabled', true);
        } else {
            $discontinued.prop('disabled', false);
        }

        $.ajax({
            url: '/admin/products/toggle-not-available',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                product_id: productId,
                not_available: value
            },
            success: function(response) {
                if(response.status == 'success') {
                    // optional toast
                }
            },
            error: function() {
                alert('Failed to update. Please try again.');
                $this.prop('checked', !$this.is(':checked'));
                $discontinued.prop('disabled', !$discontinued.is(':disabled'));
            }
        });
    });

    $(document).on('change', '.discontinued_toggle', function() {
        var $this = $(this);
        var productId = $this.data('id');
        var value = $this.is(':checked') ? 1 : 0;

        var $row = $this.closest('tr');
        var $notAvailable = $row.find('.not_available_toggle');

        if(value == 1 && $notAvailable.is(':checked')) {
            alert('Cannot mark as Discontinued because this product is already marked as Not Available.');
            $this.prop('checked', false);
            return;
        }

        // disable/enable the other checkbox
        if(value == 1) {
            $notAvailable.prop('disabled', true);
        } else {
            $notAvailable.prop('disabled', false);
        }

        $.ajax({
            url: '/admin/products/toggle-discontinued',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                product_id: productId,
                discontinued: value
            },
            success: function(response) {
                if(response.status == 'success') {
                    // optional toast
                }
            },
            error: function() {
                alert('Failed to update. Please try again.');
                $this.prop('checked', !$this.is(':checked'));
                $notAvailable.prop('disabled', !$notAvailable.is(':disabled'));
            }
        });
    });

    window.history.pushState("", "", "/admin/products");
</script>
@stop