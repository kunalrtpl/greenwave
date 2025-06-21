@extends('layouts.adminLayout.backendLayout')

@section('content')
<div class="page-content-wrapper">
    <div class="page-content">
        <div class="page-head">
            <div class="page-title">
                <h1>Voluntary Dispatches</h1>
            </div>
        </div>
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{ url('admin/dashboard') }}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="#">Voluntary Dispatches</a>
            </li>
        </ul>
        <div class="row">
            <div class="col-md-12">
                <div class="portlet blue-hoki box">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-truck"></i> Dispatch Form
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form id="VoluntaryDispatch" role="form" class="form-horizontal" method="post" action="javascript:;">
                            @csrf
                            <div class="form-body">
                                <!-- Date of Entry -->
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Date of Entry</label>
                                    <div class="col-md-4">
                                        <input type="date" name="date_of_entry" class="form-control" value="{{ date('Y-m-d') }}" readonly />
                                    </div>
                                </div>

                                <!-- Dispatch Made To -->
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Dispatch Made to</label>
                                    <div class="col-md-4">
                                        <select name="dispatch_to" class="form-control" id="dispatch_to">
                                            <option value="dealer">Dealer</option>
                                            <option value="executive">Executive</option>
                                        </select>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Voluntary-dispatch_to"></h4>
                                    </div>
                                </div>
                                <!-- Dealer Section -->
                                <div class="form-group" id="dealer_section">
                                    <label class="col-md-3 control-label">Dealer</label>
                                    <div class="col-md-4">
                                        <select name="dealer_id" class="form-control select2">
                                            <option value="">Please Select</option>
                                            @foreach($dealers as $dealer)
                                                <option value="{{ $dealer->id }}">{{ $dealer->business_name }}</option>
                                            @endforeach
                                        </select>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Voluntary-dealer_id"></h4>
                                    </div>
                                </div>

                                <!-- Executive Section -->
                                <div class="form-group" id="executive_section" style="display: none;">
                                    <label class="col-md-3 control-label">Executive</label>
                                    <div class="col-md-4">
                                        <select name="user_id" class="form-control select2">
                                            <option value="">Please Select</option>
                                            @foreach($executives as $executive)
                                                <option value="{{ $executive->id }}">{{ $executive->name }}</option>
                                            @endforeach
                                        </select>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Voluntary-user_id"></h4>
                                    </div>
                                </div>

                                <!-- Product Name -->
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Product Name</label>
                                    <div class="col-md-4">
                                        <select name="product_id" class="form-control select2">
                                            <option value="">Please Select</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                                            @endforeach
                                        </select>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Voluntary-product_id"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Dispatch Planning Table</label>
                                    <div class="col-md-9">
                                        <!-- Batch Consumptions Table -->
                                        <div class="table-responsive">
                                            <table class="table table-bordered" id="batchConsumptionTable">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center">Sr.No</th>
                                                        <th class="text-center">Batch No.</th>
                                                        <th>Status</th>
                                                        <th class="text-center">Available Qty <br> <small>(Pack Size)</small></th>
                                                        <th>Issued Qty (kg)</th>
                                                        <th>No. of Packs</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="batchConsumptionBody">
                                                    <tr>
                                                        <td colspan="6" class="text-center">Select a product to load data</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <hr class="bold-hr">
                                <!-- Dispatch Basis -->
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Dispatch Basis</label>
                                    <div class="col-md-4">
                                        <select name="dispatch_basis" class="form-control" id="dispatch_basis">
                                            <option value="free" selected>Free</option>
                                            <option value="paid">Paid</option>
                                        </select>
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Voluntary-dispatch_basis"></h4>
                                    </div>
                                </div>

                                <!-- Invoice No. -->
                                <div class="form-group" id="invoice_section" style="display: none;">
                                    <label class="col-md-3 control-label">Invoice No.</label>
                                    <div class="col-md-4">
                                        <input type="text" name="invoice_no" class="form-control" placeholder="Enter Invoice No.">
                                    <h4 class="text-center text-danger pt-3" style="display: none;" id="Voluntary-invoice_no"></h4>
                                    </div>
                                </div>

                                <!-- Challan No. -->
                                <div class="form-group" id="challan_section">
                                    <label class="col-md-3 control-label">Challan No.</label>
                                    <div class="col-md-4">
                                        <input type="text" name="challan_no" class="form-control" placeholder="Enter Challan No.">
                                    <h4 class="text-center text-danger pt-3" style="display: none;" id="Voluntary-challan_no"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Date</label>
                                    <div class="col-md-4">
                                        <input type="date" name="dispatch_date" class="form-control" value="" />
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Voluntary-dispatch_date"></h4>
                                    </div>
                                </div>
                                <hr class="bold-hr">
                                <!-- Sent Through -->
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Sent Through</label>
                                    <div class="col-md-4">
                                        <select name="sent_through" class="form-control" id="sent_through">
                                            <option value="transport" selected>Transport</option>
                                            <option value="courier">Courier</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- GR No. -->
                                <div class="form-group" id="gr_section">
                                    <label class="col-md-3 control-label">GR No.</label>
                                    <div class="col-md-4">
                                        <input type="text" name="gr_no" class="form-control" placeholder="Enter GR No.">
                                    <h4 class="text-center text-danger pt-3" style="display: none;" id="Voluntary-gr_no"></h4>
                                    </div>
                                </div>

                                <!-- POD No. -->
                                <div class="form-group" id="pod_section" style="display: none;">
                                    <label class="col-md-3 control-label">POD No.</label>
                                    <div class="col-md-4">
                                        <input type="text" name="pod_no" class="form-control" placeholder="Enter POD No.">
                                     <h4 class="text-center text-danger pt-3" style="display: none;" id="Voluntary-pod_no"></h4>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-3 control-label">Date</label>
                                    <div class="col-md-4">
                                        <input type="date" name="sent_date" class="form-control" value="" />
                                        <h4 class="text-center text-danger pt-3" style="display: none;" id="Voluntary-sent_date"></h4>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions text-center">
                                <button class="btn green" type="submit">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        function updateDispatchBasis() {
            if ($('#dispatch_to').val() === 'executive') {
                $('#dispatch_basis').val('free').prop('disabled', true);
            } else {
                $('#dispatch_basis').prop('disabled', false);
            }
            updateInvoiceChallanFields();
            $('.select2').select2();
        }

        function updateInvoiceChallanFields() {
            if ($('#dispatch_basis').val() === 'paid') {
                $('#challan_section').hide();
                $('#invoice_section').show();
            } else {
                $('#challan_section').show();
                $('#invoice_section').hide();
            }
        }

        function updateSentThroughFields() {
            if ($('#sent_through').val() === 'courier') {
                $('#gr_section').hide();
                $('#pod_section').show();
            } else {
                $('#gr_section').show();
                $('#pod_section').hide();
            }
        }

        $('#dispatch_to').change(function () {
            if ($(this).val() === 'dealer') {
                $('#dealer_section').show();
                $('#executive_section').hide();
                $('#dispatch_basis').html('<option value="free">Free</option><option value="paid">Paid</option>');
            } else {
                $('#dealer_section').hide();
                $('#executive_section').show();
                $('#dispatch_basis').html('<option value="free" selected>Free</option>');
            }
            updateDispatchBasis();
        });

        $('#dispatch_basis').change(function () {
            updateInvoiceChallanFields();
        });

        $('#sent_through').change(function () {
            updateSentThroughFields();
        });

        // Initial Call to Set Visibility on Page Load
        updateDispatchBasis();
        updateInvoiceChallanFields();
        updateSentThroughFields();
    });
</script>
<script type="text/javascript">
    $("#VoluntaryDispatch").submit(function(e){
        $('.loadingDiv').show();
        e.preventDefault();
         var formdata = new FormData(this);
        $.ajax({
            url: "{{url('/admin/voluntary-dispatch/store')}}",
            type:'POST',
            data: formdata,
            processData: false,
            contentType: false,
            success: function(data) {
                $('.loadingDiv').hide();
                if(!data.status){
                    $.each(data.errors, function (i, error) {
                        $('#Voluntary-'+i).addClass('error-triggered');
                        $('#Voluntary-'+i).attr('style', '');
                        $('#Voluntary-'+i).html(error);
                        setTimeout(function () {
                            $('#Voluntary-'+i).css({
                                'display': 'none'
                            });
                        $('#Voluntary-'+i).removeClass('error-triggered');
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
    $(document).ready(function() {
    $(document).on('change','[name=product_id]',function() {
        let productId = $(this).val();
        let tableBody = $('#batchConsumptionBody');

        if (productId) {
            $.ajax({
                url: "{{ url('admin/fetch-product-batch-consumptions') }}",
                type: "GET",
                data: { product_id: productId },
                beforeSend: function() {
                    tableBody.html('<tr><td colspan="8" class="text-center">Loading...</td></tr>');
                },
                success: function(response) {
                    tableBody.html(response.html);
                },
                error: function() {
                    tableBody.html('<tr><td colspan="8" class="text-center text-danger">Error fetching data</td></tr>');
                }
            });
        } else {
            tableBody.html('<tr><td colspan="8" class="text-center">Select a product to load data</td></tr>');
        }
    });
});

</script>
@endsection
