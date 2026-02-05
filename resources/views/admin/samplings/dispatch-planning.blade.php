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
        @if(isset($_GET['s']))
            <div role="alert" class="alert alert-success alert-dismissible fade in"> <button aria-label="Close" data-dismiss="alert" style="text-indent: 0;" class="close" type="button"><span aria-hidden="true"></span></button> <strong>Success!</strong> Record has been updated Sucessfully. </div>
        @endif
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-sharp bold uppercase">Sample Dispatch Planning</span>
                            <span class="caption-helper">manage records...</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="table-container">
                            <table class="table table-striped table-bordered table-hover" id="datatable_ajax">
                                <thead>
                                    <tr role="row" class="heading">
                                        
                                        <th width="15%">
                                            Ref No.<br>(Date)
                                        </th>
                                        <th>
                                            Executive
                                        </th>
                                        <th>
                                            Product <br> (Product Code)
                                        </th>
                                        <th>
                                            Pending Qty <br>(Pack Size)
                                        </th>
                                        <th>
                                            Courier/ Transport
                                        </th>
                                        <th>
                                            Actions
                                        </th>
                                    </tr>
                                    <tr role="row" class="filter">
                                        <td>
                                            <input placeholder="Ref No." type="text" class="form-control form-filter input-sm" name="ref_no">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-filter input-sm" name="executive_info" placeholder="Search By Executive">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-filter input-sm" name="product_name" placeholder="Search By Product Name">
                                            <br>
                                            <select class="form-control form-filter input-sm" name="urgent">
                                                <option value="">All</option>
                                                <option value="On Hold">On Hold</option>
                                                <option value="Cancel">Cancel</option>
                                                <option value="Urgent">Urgent</option>
                                            </select>
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td>
                                            @if(isset($_GET['dealer_info']))
                                                <a href="{{url('/admin/po-dispatch-planning')}}">Reset Filters</a>
                                            @else
                                                <div class="margin-bottom-5">
                                                    <button class="btn btn-sm yellow filter-submit margin-bottom"><i title="Search" class="fa fa-search"></i></button>
                                                    <button class="btn btn-sm red filter-cancel"><i title="Reset" class="fa fa-refresh"></i></button>
                                                </div>

                                            @endif
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
<span id="DispatchQtyHTML">
    
</span>
<div class="modal fade" id="PoDispatchModal" tabindex="-1" role="dialog" aria-labelledby="PoDispatchModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="PoDispatchModalLabel">Update Sample Dispatch Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="UpdateProductSampleDispatchQty" action="javascript:;" method="post">@csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="issue-batch-no" class="col-form-label">Name</label>
                        <p class="form-control proDispatchUserName"></p>
                    </div>
                    <div class="form-group">
                        <label for="issue-batch-no" class="col-form-label">Product Name</label>
                        <p class="form-control proDispatchProdName"></p>
                    </div>
                    <div class="form-group">
                        <label for="issue-batch-no" class="col-form-label">Batch No.</label>
                        <input type="text" name="batch_no" class="form-control" id="issue-batch-no" required>
                    </div>
                    <div class="form-group">
                        <label for="issue-stock" class="col-form-label">Issue Stock</label>
                        <input type="number" name="issue_stock" class="form-control" id="issue-stock">
                    </div>
                    <h4 class="text-center text-danger pt-3" style="display: none;" id="IssueStock-total_stock_error"></h4>
                </div>
                <input type="hidden" name="order_item_id">
                <input type="hidden" name="dealer_info">
                <input type="hidden" name="product_name">
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).on('click','.openDispatchItemModal',function(){
        var orderitemid = $(this).data('orderitemid');
        var dealer_info = $('[name="dealer_info"]').val();
        var product_name = $('[name="product_name"]').val();
        var proname = $(this).data('productname');
        var username = $(this).data('username');
        $('.proDispatchProdName').text(proname);
        $('.proDispatchUserName').text(username);
        $('[name=order_item_id]').val(orderitemid);
        $('[name=dealer_info]').val(dealer_info);
        $('[name=product_name]').val(product_name);
        $('#PoDispatchModal').modal('show');
    })
</script>
<script type="text/javascript">
    $(document).on('submit','#UpdateProductDispatchQty',function(e){
        $('.loadingDiv').show();
        e.preventDefault();
        var formdata = $("#UpdateProductDispatchQty").serialize();
        $.ajax({
            url: '/admin/update-product-dispatch-qty',
            type:'POST',
            data: formdata,
            success: function(data) {
                $('.loadingDiv').hide();
                if(!data.status){
                    $.each(data.errors, function (i, error) {
                        $('#BatchSheetErr-'+i).addClass('error-triggered');
                        $('#BatchSheetErr-'+i).attr('style', '');
                        $('#BatchSheetErr-'+i).html(error);
                        setTimeout(function () {
                            $('#BatchSheetErr-'+i).css({
                                'display': 'none'
                            });
                        $('#BatchSheetErr-'+i).removeClass('error-triggered');
                        }, 5000);
                    });
                    $('html,body').animate({
                        scrollTop: $('.error-triggered').first().stop().offset().top - 200
                    }, 1000);
                }else{
                    window.location.href= data.url;
                }
            }
        });
    });
</script>

<script type="text/javascript">
    $(document).on('submit','#UpdateProductSampleDispatchQty',function(e){
        $('.loadingDiv').show();
        e.preventDefault();
        var formdata = $("#UpdateProductSampleDispatchQty").serialize();
        $.ajax({
            url: '/admin/update-pro-sample-dispatch-qty',
            type:'POST',
            data: formdata,
            success: function(data) {
                $('.loadingDiv').hide();
                if(!data.status){
                    $.each(data.errors, function (i, error) {
                        $('#IssueStock-'+i).addClass('error-triggered');
                        $('#IssueStock-'+i).attr('style', '');
                        $('#IssueStock-'+i).html(error);
                        setTimeout(function () {
                            $('#IssueStock-'+i).css({
                                'display': 'none'
                            });
                        $('#IssueStock-'+i).removeClass('error-triggered');
                        }, 5000);
                    });
                    $('html,body').animate({
                        scrollTop: $('.error-triggered').first().stop().offset().top - 200
                    }, 1000);
                }else{
                    window.location.href= data.url;
                }
            }
        });
    });
</script>
@stop


