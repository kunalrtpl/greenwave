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
                            <span class="caption-subject font-green-sharp bold uppercase">DO Ready, Invoice Required</span>
                            <span class="caption-helper">manage records...</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-container">
                            <table class="table table-striped table-bordered table-hover" id="datatable_ajax">
                                <thead>
                                    <tr role="row" class="heading">
                                        <!-- <th>
                                            
                                        </th> -->
                                        <th>
                                            PO No.
                                        </th>
                                        <th>
                                            DO No.
                                        </th>
                                        <th>Type</th>
                                        <th>
                                            Dealer/ Customer
                                        </th>
                                        <!-- <th>
                                            PO No.
                                        </th> -->
                                        <th>
                                            Product
                                        </th>
                                        <th>
                                            Price
                                        </th>
                                        <th>
                                            Qty
                                        </th>
                                        <th>
                                            Batch No.
                                        </th>
                                        <th>
                                            Actions
                                        </th>
                                    </tr>
                                    <tr role="row" class="filter">
                                        <td>
                                            <input placeholder="PO NO." type="text" class="form-control form-filter input-sm" name="po_no">
                                        </td>
                                        <td>
                                            <input placeholder="DO NO." type="text" class="form-control form-filter input-sm" name="do_no">
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
                                        <!-- <td></td> -->
                                        <td>
                                            <input type="text" class="form-control form-filter input-sm" name="product_name" placeholder="Search By Product Name" @if(isset($_GET['product_name'])) value="{{$_GET['product_name']}}" @endif>
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td>
                                            @if(isset($_GET['dealer_info']))
                                                <a href="{{url('/admin/do-ready')}}">Reset Filters</a>
                                            @else
                                            <div class="margin-bottom-5">
                                                <button class="btn btn-sm yellow filter-submit margin-bottom"><i title="Search" class="fa fa-search"></i></button>
                                                <button class="btn btn-sm red filter-cancel"><i title="Reset" class="fa fa-refresh"></i></button>
                                            </div>
                                            @endif
                                            <input id="checkAll" type="checkbox" data-saleinvoice="all" value="all" class="getDoReady">
                                            <a style="display: none;" id="DoBulkUpdate" href="javascript:;" class="btn btn-sm btn-success">Update</a>
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
<div class="modal fade" id="saleInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="saleInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title" id="saleInvoiceModalLabel">Sale Invoice</h5>
            </div>
            <form action="{{url('/admin/update-sale-invoice')}}" method="post" autocomplete="off">@csrf
                <input type="hidden" name="dealer_name">
                <input type="hidden" name="pro_name">
                <input type="hidden" name="sale_invoice_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="sale-date" class="col-form-label">Date:</label>
                        <input type="date" name="sale_invoice_date" class="form-control" id="sale-date" required>
                    </div>
                    <div class="form-group">
                        <label for="invoice-number" class="col-form-label">Invoice Number:</label>
                        <input type="text" name="invoice_number" placeholder="Enter Invoice Number" class="form-control" id="invoice-number" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="BulkUpdatesaleInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="BulkUpdatesaleInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title" id="BulkUpdatesaleInvoiceModalLabel">Sale Invoice</h5>
            </div>
            <form action="{{url('/admin/update-bulk-sale-invoice')}}" method="post" autocomplete="off">@csrf
                <input type="hidden" name="sale_invoice_ids" value="">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="sale-date" class="col-form-label">Date:</label>
                        <input type="date" name="sale_invoice_date" class="form-control" id="sale-date" required>
                    </div>
                    <div class="form-group">
                        <label for="invoice-number" class="col-form-label">Invoice Number:</label>
                        <input type="text" name="invoice_number" placeholder="Enter Invoice Number" class="form-control" id="invoice-number" required>
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
    $(document).on('change','.getDoReady',function(e){
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
            $('#DoBulkUpdate').show();
        }else{
            $('#DoBulkUpdate').hide();
        }
        console.log(doids);
    });

    $(document).on('click','#DoBulkUpdate',function(){
        $('#BulkUpdatesaleInvoiceModal').modal('show');
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

