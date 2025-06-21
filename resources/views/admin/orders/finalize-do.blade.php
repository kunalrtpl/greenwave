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
        @if(Session::has('flash_message_success'))
            <div role="alert" class="alert alert-success alert-dismissible fade in"> <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"></button> <strong>Success!</strong> {!! session('flash_message_success') !!} </div>
        @endif
        @if(isset($_GET['s']))
            <div role="alert" class="alert alert-success alert-dismissible fade in"> <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"><span aria-hidden="true"></span></button> <strong>Success!</strong> Record has been updated Sucessfully. </div>
        @endif
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-sharp bold uppercase">Finalize DO</span>
                            <span class="caption-helper">manage records...</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-container">
                            <table class="table table-striped table-bordered table-hover" id="datatable_ajax">
                                <thead>
                                    <tr role="row" class="heading">
                                        <th>
                                          Type  
                                        </th>
                                        <!-- <th>
                                            PO No.
                                        </th> -->
                                        <th>
                                            Dealer/Customer
                                        </th>
                                        <!-- <th>
                                            PO No.
                                        </th> -->
                                        <th>
                                            Product
                                        </th>
                                        <!-- <th>
                                            Dealer Price
                                        </th> -->
                                        <th>
                                            Qty
                                        </th>
                                        <!-- <th>
                                            Batch No.
                                        </th> -->
                                        <th>
                                            Actions
                                        </th>
                                    </tr>
                                    <tr role="row" class="filter">
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
                                        <!-- <td></td> -->
                                        <!-- <td></td> -->
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
                                            <a style="display: none;" id="GenerateDoNumbers" href="javascript:;" class="btn btn-sm btn-success">Generate</a>
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
<input type="hidden" name="sale_invoice_ids">
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
            $('#GenerateDoNumbers').show();
        }else{
            $('#GenerateDoNumbers').hide();
        }
        console.log(doids);
    });

    $(document).on('click','#GenerateDoNumbers',function(){
        var sale_invoice_ids = $('[name=sale_invoice_ids]').val();
        $.ajax({
            data : {sale_invoice_ids:sale_invoice_ids},
            type : 'POST',
            url : '/admin/generate-do-numbers',
            success:function(resp){
                window.location.href = resp.url;
            },
        })
    })
</script>
@stop

