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
                            <span class="caption-subject font-green-sharp bold uppercase">Finalize D.O.</span>
                            <span class="caption-helper">manage records...</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-container">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr role="row" class="heading">
                                        <th width="15%">
                                            Dealer/ Customer
                                        </th>
                                        <th width="15%">
                                            Name
                                        </th>
                                        <th width="70%">
                                            Products
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
@foreach($users as $user)
    <tr>
        <td>
            @if($user['action'] =='dealer')
                Dealer
            @else
                Customer
            @endif
        </td>
        <td>
            {{$user['name']}}
        </td>
        <td>
            <table class="table table-bordered table-striped">
                <?php  $sale_invoices = \App\SaleInvoice::getinvoices($user); 
                //echo "<pre>"; print_r($sale_invoices); die;
                ?>
                @if(!empty($sale_invoices['sale_invoices']))
                    <tr>
                        <th width="40%">Product Name</th>
                        <th width="40%">Qty</th>
                        <th>#</th>
                        <th width="20%">Actions</th>
                    </tr>
                    <?php $totalQty = 0; ?>
                    @foreach($sale_invoices['sale_invoices'] as  $key=> $sale_invoice)
                        @foreach($sale_invoice['invoice_items'] as $ikey => $invoiceItem)
                        <?php $totalQty += $invoiceItem['qty'] ?>
                        <tr>
                            <td>{{$invoiceItem['productinfo']['product_name']}}</td>
                            <td>
                                {{$invoiceItem['qty']}} kg 
                            </td>
                            <td><a class="btn btn-xs btn-danger" href="{{url('admin/undo-finalize-do/'.$sale_invoice['id'].'/'.$invoiceItem['purchase_order_item_id'])}}"><i class="fa fa-times"></i></a></td>
                            @if($key==0) 
                                <td rowspan="{{count($sale_invoices['sale_invoices'])}}">
                                    <a data-saleinvoiceids="{{$sale_invoices['sale_invoice_ids']}}" href="javascript:;" class="btn btn-sm btn-primary green generateDO">Generate D.O.</a>
                                </td>
                            @endif
                        </tr>
                        @endforeach
                    @endforeach
                    <tr>
                        <th>Total</th>
                        <th>{{$totalQty}}kg</th>
                        <th></th>
                    </tr>
                @endif
            </table>
        </td>
    </tr>
@endforeach
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

    $(document).on('click','.generateDO',function(){
        $('.loadingDiv').show();
        var sale_invoice_ids = $(this).data('saleinvoiceids');
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

