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
                <h1>Data Management</h1>
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
                            <span class="caption-subject font-green-sharp bold uppercase">Lost Sale Report</span>
                            <span class="caption-helper">manage records...</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-container">
                            <table class="table table-striped table-bordered table-hover" id="datatable_ajax">
                                <thead>
                                    <tr role="row" class="heading">
                                        <th width="10%">
                                            Date
                                        </th>
                                         <th>
                                            Customer Name
                                        </th>
                                        <th>
                                            Product Name
                                        </th>
                                        <th>
                                            Reason
                                        </th>
                                        <th>
                                            Replaced by Product Name
                                        </th>
                                        <th>
                                            Executive
                                        </th>
                                        <th>
                                            Remarks
                                        </th>
                                        <th>
                                            Actions
                                        </th>
                                    </tr>
                                    <tr role="row" class="filter">
                                        <td></td>
                                        <td>
                                            <input type="text" class="form-control form-filter input-sm" name="customer_name" placeholder="Customer Name">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-filter input-sm" name="product_name" placeholder="Product Name">
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td>
                                            <input type="text" class="form-control form-filter input-sm" name="executive" placeholder="Executive">
                                        </td>
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
<div class="modal fade" id="LostSaleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title" id="exampleModalLabel">View Details</h5>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-striped" id="appendDetails">
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
        $(document).on('click','.getSaleReportDetails',function(){
            $('.loadingDiv').show();
            var lost_sale_id = $(this).data('lost_sale_id');
            $.ajax({
                data : {lost_sale_id: lost_sale_id},
                url : "/admin/lost-sales-info-detail",
                type : 'post',
                success:function(resp){
                    $('.loadingDiv').hide();
                    $('#LostSaleModal').modal('show');
                    $('#appendDetails').html(resp.view);
                },
                error:function(){

                }
            })
        })
    })
</script>
@stop





