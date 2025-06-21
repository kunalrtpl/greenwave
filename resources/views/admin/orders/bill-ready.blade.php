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
                <h1>Orders Management</h1>
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
                            <span class="caption-subject font-green-sharp bold uppercase">Bill Ready, Despatch Required</span>
                            <span class="caption-helper">manage records...</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-container">
                            <table class="table table-striped table-bordered table-hover" id="datatable_ajax">
                                <thead>
                                    <tr role="row" class="heading">
                                        <th>
                                            Date of Invoice
                                        </th>
                                        <th>
                                            Invoice No.
                                        </th>
                                        <th>Type</th>
                                        <th>
                                            Dealer/ Customer 
                                        </th>
                                        <th>
                                            Product
                                        </th>
                                        <th>
                                            Qty
                                        </th>
                                        <th>
                                            Actions
                                        </th>
                                    </tr>
                                    <tr role="row" class="filter">
                                        <td>
                                            <input type="date" class="form-control form-filter input-sm" name="invoice_date">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-filter input-sm" name="invoice_info" placeholder="Search Invoice" @if(isset($_GET['invoice_info'])) value="{{$_GET['invoice_info']}}" @endif>
                                        </td>
                                        <td>
                                            <select class="form-control form-filter input-sm" name="user_type">
                                                <option value="">All</option>
                                                <option value="Dealer">Dealer</option>
                                                <option value="Customer">Customer</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-filter input-sm" name="dealer_info" placeholder="Search By Dealer" @if(isset($_GET['dealer_info'])) value="{{$_GET['dealer_info']}}" @endif>
                                            <br>
                                            <input type="text" class="form-control form-filter input-sm" name="customer_info" placeholder="Search By Customer" @if(isset($_GET['customer_info'])) value="{{$_GET['customer_info']}}" @endif>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-filter input-sm" name="product_name" placeholder="Search By Product Name" @if(isset($_GET['product_name'])) value="{{$_GET['product_name']}}" @endif>
                                        </td>
                                        <td></td>
                                        <td>
                                             @if(isset($_GET['dealer_info']))
                                                <a href="{{url('/admin/bill-ready')}}">Reset Filters</a>
                                            @else
                                            <div class="margin-bottom-5">
                                                <button class="btn btn-sm yellow filter-submit margin-bottom"><i title="Search" class="fa fa-search"></i></button>
                                                <button class="btn btn-sm red filter-cancel"><i title="Reset" class="fa fa-refresh"></i></button>
                                            </div>
                                            @endif
                                            <input id="checkAll" type="checkbox" data-saleinvoice="all" value="all" class="getBillReady">
                                            <a style="display: none;" id="BillBulkUpdate" href="javascript:;" class="btn btn-sm btn-success">Update</a>
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
<div class="modal fade" id="DoTransortModal" tabindex="-1" role="dialog" aria-labelledby="DoTransortModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title" id="DoTransortModalLabel">Update Transport Details</h5>
            </div>
            <form action="{{url('/admin/update-transport-details')}}" method="post" autocomplete="off">@csrf
                <input type="hidden" name="dealer_name">
                <input type="hidden" name="pro_name">
                <input type="hidden" name="sale_invoice_id">
                <input type="hidden" name="sale_invoice_no">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="transport-name" class="col-form-label">Transport Name:</label>
                        <input type="text" name="transport_name" placeholder="Enter Transport Name" class="form-control" id="transport-name" required>
                    </div>
                    <div class="form-group">
                        <label for="sale-date" class="col-form-label">Dispatch Date:</label>
                        <input type="date" name="dispatch_date" class="form-control" id="sale-date" required>
                    </div>
                    <div class="form-group">
                        <label for="invoice-number" class="col-form-label">LR Number:</label>
                        <input type="text" name="lr_no" placeholder="Enter LR Number" class="form-control" id="invoice-number" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="BulkUpdateTransortModal" tabindex="-1" role="dialog" aria-labelledby="BulkUpdateTransortModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title" id="BulkUpdateTransortModalLabel">Update Transport Details</h5>
            </div>
            <form action="{{url('/admin/update-bulk-transport-details')}}" method="post" autocomplete="off">@csrf
                <input type="hidden" name="dealer_name">
                <input type="hidden" name="pro_name">
                <input type="hidden" name="sale_invoice_id">
                <input type="hidden" name="sale_invoice_no">
                <input type="hidden" name="sale_invoice_ids" value="">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="transport-name" class="col-form-label">Transport Name:</label>
                        <input type="text" name="transport_name" placeholder="Enter Transport Name" class="form-control" id="transport-name" required>
                    </div>
                    <div class="form-group">
                        <label for="sale-date" class="col-form-label">Dispatch Date:</label>
                        <input type="date" name="dispatch_date" class="form-control" id="sale-date" required>
                    </div>
                    <div class="form-group">
                        <label for="invoice-number" class="col-form-label">LR Number:</label>
                        <input type="text" name="lr_no" placeholder="Enter LR Number" class="form-control" id="invoice-number" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).on('click','.updateTransportDetails',function(){
        var saleInvoiceid =  $(this).data('saleinvoiceid');
        $('[name="dealer_name"]').val($('[name=dealer_info]').val());
        $('[name="pro_name"]').val($('[name=product_name]').val());
        $('[name="sale_invoice_no"]').val($('[name=invoice_info]').val());
        $('#DoTransortModal').modal('show');
        $('[name="sale_invoice_id"]').val(saleInvoiceid);
    })
</script>
<script type="text/javascript">
    $(document).on('change','.getBillReady',function(e){
        var saleinvoice = $(this).data('saleinvoice');
        var check_status = 0;
        if(saleinvoice=="all"){
            var ids = [];
            i = 0;
            var table= $(e.target).closest('table');
            $('td input:checkbox',table).each(function(key,value) {
                if(key!=0){
                    ids[i++] = $(this).data('saleinvoice');
                }
            });
            ids = ids.join(",");
            if(this.checked) {
                $('td input:checkbox',table).prop('checked',this.checked);
            }else{
                $('td input:checkbox',table).prop('checked',false);
            }
        }
        var doids = [];
        $('td input:checkbox:checked').each(function(i,e) {
            if(e.value !="all"){
                doids.push(e.value);
            }
        });
        doids = doids.join(',');
        if(doids != ""){
            $('[name=sale_invoice_ids]').val(doids);
            $('#BillBulkUpdate').show();
        }else{
            $('#BillBulkUpdate').hide();
        }
        console.log(doids);
    });

    $(document).on('click','#BillBulkUpdate',function(){
        $('#BulkUpdateTransortModal').modal('show');
    })

    $(document).on('click','.UpdateSaleiInvoice',function(){
        var saleInvoiceid =  $(this).data('saleinvoiceid');
        $('[name="dealer_name"]').val($('[name=dealer_info]').val());
        $('[name="pro_name"]').val($('[name=product_name]').val());
        $('#saleInvoiceModal').modal('show');
        $('[name="sale_invoice_id"]').val(saleInvoiceid);
    })
</script>
@stop

