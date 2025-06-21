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
                <h1>Samplings Management</h1>
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
                            <span class="caption-subject font-green-sharp bold uppercase">Sample Finalize D.O.</span>
                            <span class="caption-helper">manage records...</span>
                        </div>
                    </div>
                    <p class="text-left">
                        <a style="font-size: 20px;" href="{{url('admin/sample-finalize-do/paid')}}" class="btn btn-success btn-outline-rounded @if($type=="paid") green @else grey @endif">Paid Samples</a>
                        <a style="font-size: 20px;" href="{{url('admin/sample-finalize-do/free')}}" class="btn btn-success btn-outline-rounded @if($type=="free") green @else grey @endif">Free Samples</a>
                    </p>
                    <div class="portlet-body">
                        <div class="table-container">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr role="row" class="heading">
                                        <th width="15%">
                                            Dealer/ Executive
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
<?php $requiredThroughs = array('courier','transport'); ?>
@foreach($users as $user)
    <tr>
        <td>
            @if(isset($user['dealer_id']))
                Dealer
            @else
                Executive
            @endif
        </td>
        <td>
            {{$user['name']}}
        </td>
        <td>
            @foreach($requiredThroughs as $required)
                <table class="table table-bordered table-striped">
                    <?php  $sale_invoices = \App\SamplingSaleInvoice::getinvoices($user,$required); ?>
                    @if(!empty($sale_invoices['sale_invoices']))
                        <tr>
                            <th colspan="3" style="background-color: green; color: white;">{{strtoupper($required)}}</th>
                        </tr>
                        <tr>
                            <th width="40%">Product Name</th>
                            <th width="40%">Qty- (Pack Size)</th>
                            <th>#</th>
                            <th width="20%">Actions</th>
                        </tr>
                        <?php $totalQty = 0; ?>
                        @foreach($sale_invoices['sale_invoices'] as  $key=> $sale_invoice)
                            <?php $totalQty += $sale_invoice['qty'] ?>
                            <tr>
                                <td>{{$sale_invoice['product_name']}}</td>
                                <td>
                                    {{$sale_invoice['qty']}} kg - ({{$sale_invoice['actual_pack_size']}} kg Packing)
                                </td>
                                <td>
                                    <a class="btn btn-xs btn-danger" href="{{url('admin/undo-sampling-finalize-do/'.$sale_invoice['sale_invoice_id'].'/'.$sale_invoice['sampling_item_id'])}}"><i class="fa fa-times"></i></a></td>
                                @if($key==0) 
                                    <td rowspan="{{count($sale_invoices['sale_invoices'])}}">
                                        <a data-saleinvoiceids="{{$sale_invoices['sale_invoice_ids']}}" href="javascript:;" class="btn btn-sm btn-primary green generateSampleDO">Generate D.O.</a>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                        <tr>
                            <th>Total</th>
                            <th>{{$totalQty}}kg</th>
                            <th></th>
                        </tr>
                    @endif
                </table>
            @endforeach
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
    $(document).on('click','.generateSampleDO',function(){
        $('.loadingDiv').show();
        var ids = $(this).data('saleinvoiceids');
        $.ajax({
            data : {sale_invoice_ids:ids,type: '<?php echo $type ?>'},
            type : 'POST',
            url : '/admin/sampling-generate-do-numbers',
            success:function(resp){
                window.location.href = resp.url;
            },
        })
    })
</script>
@stop


