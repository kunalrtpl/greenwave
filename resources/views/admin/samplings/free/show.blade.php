@extends('layouts.adminLayout.backendLayout')
@section('content')
<style>
    .info-table {
    width: 100%;
    }
    .info-table td {
    padding: 6px 0;
    vertical-align: top;
    }
    .info-table .label-col {
    width: 140px;
    font-weight: 600;
    text-align: left;
    color: #333;
    }
    .info-table .colon-col {
    width: 10px;
    }
    .info-table .value-col {
    color: #555;
    }
    /* Equal height rows for Metronic (desktop only) */
    @media (min-width: 992px) {
        .equal-height-row {
            display: flex;
        }
        .equal-height-row > .col-md-6 {
            display: flex;
        }
        .equal-height-row .portlet {
            width: 100%;
            margin-bottom: 0;
        }
    }
</style>

<div class="page-content-wrapper">
    <div class="page-content">
        <!-- PAGE HEADER -->
        <div class="page-head">
            <div class="page-title">
                <h1>Sampling Detail</h1>
            </div>
            <div class="page-toolbar">
                <a href="{{ route('sampling.download.pdf', $sampleDetails->id) }}"
                    class="btn btn-sm red"
                    target="_blank">
                <i class="fa fa-file-pdf-o"></i> Download PDF
                </a>
            </div>
        </div>
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{ url('admin/dashboard') }}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{ url('admin/free-sampling') }}">
                Sampling #{{ $sampleDetails->id }}
                </a>
            </li>
        </ul>
        <!-- ================= FIRST ROW ================= -->
        <div class="row equal-height-row">
            <!-- SAMPLE INFO -->
            <div class="col-md-6">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption font-blue bold">
                            Sample Request Information
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="info-table">
                            <tr>
                                <td class="label-col">Sample ID</td>
                                <td class="colon-col">:</td>
                                <td class="value-col">{{ $sampleDetails->id }}</td>
                            </tr>
                            <tr>
                                <td class="label-col">Ref No</td>
                                <td class="colon-col">:</td>
                                <td class="value-col">{{ $sampleDetails->sample_ref_no_string }}</td>
                            </tr>
                            <tr>
                                <td class="label-col">Created At</td>
                                <td class="colon-col">:</td>
                                <td class="value-col">
                                    {{ $sampleDetails->created_at->format('d M Y, h:i A') }}
                                </td>
                            </tr>
                            <tr>
                                <td class="label-col">Status</td>
                                <td class="colon-col">:</td>
                                <td class="value-col">
                                    <span class="label label-info">
                                    {{ ucfirst($sampleDetails->sample_status) }}
                                    </span> &nbsp; 
                                    @if(in_array($sampleDetails->sample_status,['pending','on hold']))
                                    <button class="btn btn-xs btn-primary margin-left-10"
                                        data-toggle="modal"
                                        data-target="#DealerPOstatusModal">
                                    Update Status
                                    </button>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <!-- CUSTOMER INFO -->
            <div class="col-md-6">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption font-green bold">
                            Customer Details
                        </div>
                    </div>
                    <div class="portlet-body">
                        @if($sampleDetails->customer)
                        <table class="info-table">
                            <tr>
                                <td class="label-col">Name</td>
                                <td class="colon-col">:</td>
                                <td class="value-col">{{ $sampleDetails->customer->name }}</td>
                            </tr>
                            <tr>
                                <td class="label-col">Contact</td>
                                <td class="colon-col">:</td>
                                <td class="value-col">{{ $sampleDetails->customer->contact_person_name }}</td>
                            </tr>
                            <tr>
                                <td class="label-col">Mobile</td>
                                <td class="colon-col">:</td>
                                <td class="value-col">{{ $sampleDetails->customer->mobile }}</td>
                            </tr>
                            <tr>
                                <td class="label-col">Address</td>
                                <td class="colon-col">:</td>
                                <td class="value-col">{{ $sampleDetails->customer->address }}</td>
                            </tr>
                        </table>
                        @else
                        <div class="alert alert-warning">
                            <i class="fa fa-info-circle"></i>
                            Customer not provided in request.
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <br>
        <!-- ================= SECOND ROW ================= -->
        <div class="row equal-height-row">
            <!-- EXECUTIVE -->
            <div class="col-md-6">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption font-purple bold">
                            Executive Details
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="info-table">
                            <tr>
                                <td class="label-col">Name</td>
                                <td class="colon-col">:</td>
                                <td class="value-col">{{ $sampleDetails->user->name }}</td>
                            </tr>
                            <tr>
                                <td class="label-col">Email</td>
                                <td class="colon-col">:</td>
                                <td class="value-col">{{ $sampleDetails->user->email }}</td>
                            </tr>
                            <tr>
                                <td class="label-col">Mobile</td>
                                <td class="colon-col">:</td>
                                <td class="value-col">{{ $sampleDetails->user->mobile }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <!-- DISPATCH -->
            <div class="col-md-6">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption font-red bold">
                            Dispatch Details
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="info-table">
                            <tr>
                                <td class="label-col">Dispatch To</td>
                                <td class="colon-col">:</td>
                                <td class="value-col">{{ $sampleDetails->dispatch_to }}</td>
                            </tr>
                            <tr>
                                <td class="label-col">Address</td>
                                <td class="colon-col">:</td>
                                <td class="value-col">{{ $sampleDetails->dispatch_address }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- PRODUCTS SECTION -->
        @include('admin.samplings.free.partials.product_section')
    </div>
</div>
<!-- ================= STATUS MODAL ================= -->
<div class="modal fade" id="DealerPOstatusModal" tabindex="-1" role="dialog" aria-labelledby="DealerPOstatusModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title" id="DealerPOstatusModalLabel">Update Status</h5>
            </div>
            <form action="{{url('/admin/update-sampling-status')}}" method="post">@csrf
                <div class="modal-body">
                    <input type="hidden" name="sampling_id" value="{{$sampleDetails['id']}}">
                    <div class="form-group">
                        <label for="recipient-name" class="col-form-label">Status:</label>
                        <select class="form-control" name="sample_status" required>
                            <option value="">Please Select</option>
                            <option value="rejected">Rejected</option>
                            <option value="on hold">On Hold</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="recipient-name" class="col-form-label">Reason:</label>
                        <select class="form-control" name="reason" required>
                            <option value="">Please Select</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="recipient-name" class="col-form-label">Comments:</label>
                        <textarea class="form-control" name="comments"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- ================= AUTO QTY CALC ================= -->
<script>
function setPackSizes(row) {
    let productOption = row.find('.approved-product option:selected');
    let physicalForm = (productOption.data('form') || '').toLowerCase();
    let packSelect = row.find('.actual-pack-size');
    let savedPack = packSelect.data('selected');

    packSelect.empty();

    if (physicalForm === 'liquid') {
        packSelect.append('<option value="1">1 Kg</option>');
        packSelect.append('<option value="5">5 Kg</option>');
    } else {
        packSelect.append('<option value="1">1 Kg</option>');
    }

    if (savedPack && packSelect.find('option[value="'+savedPack+'"]').length) {
        packSelect.val(savedPack);
    } else {
        packSelect.prop('selectedIndex', 0);
    }
}

function calculateRow(row) {
    let pack  = parseFloat(row.find('.actual-pack-size').val()) || 0;
    let packs = parseFloat(row.find('.actual-packs').val()) || 0;
    let price = parseFloat(row.find('.dealer-price').text()) || 0;

    let qty   = pack * packs;
    let value = qty * price;

    row.find('.actual-qty').val(qty);
    row.find('.actual-qty-text').text(qty);
    row.find('.row-value').text(value.toFixed(2));
}

/* INIT ON LOAD – ONLY APPROVED ROWS */
$('.approved-product').each(function () {
    let row = $(this).closest('tr');

    setPackSizes(row);

    let selected = $(this).find(':selected');
    row.find('.dealer-price').text(selected.data('price') || 0);

    calculateRow(row);
});

/* PRODUCT CHANGE */
$(document).on('change', '.approved-product', function () {
    let row = $(this).closest('tr');
    let selected = $(this).find(':selected');

    row.find('.dealer-price').text(selected.data('price') || 0);
    row.find('.hiddenDealerPrice').val(selected.data('price') || 0);
    row.find('.actual-pack-size').data('selected', null);

    setPackSizes(row);
    calculateRow(row);
});

/* PACK SIZE / PACK COUNT CHANGE */
$(document).on('input change', '.actual-pack-size, .actual-packs', function () {
    calculateRow($(this).closest('tr'));
});
</script>
<script>
$(document).on('change', '.add-product-select', function () {

    let form = $(this).find(':selected').data('form');
    let pack = $('.add-pack-size');

    pack.empty();

    if (form === 'liquid') {
        pack.append('<option value="1">1 Kg</option>');
        pack.append('<option value="5">5 Kg</option>');
    } else {
        pack.append('<option value="1">1 Kg</option>');
    }
});
</script>
<script>
$(document).ready(function () {

    $('#addProductModal').on('shown.bs.modal', function () {
        $(this).find('.select2').select2({
            dropdownParent: $('#addProductModal'),
            width: '100%'
        });
    });

});
</script>

<script type="text/javascript">
    $(document).on('change','[name=sample_status]',function(){
        var status = $(this).val();
        if(status =="rejected"){
            $('[name=reason]').html('');
            $('[name=reason]').append('<option value="">Please Select</option><option value="Order declined due to unavailablity of material">Order declined due to unavailablity of material</option><option value="Order declined as the Product has been stopped">Order declined as the Product has been stopped</option><option value="Other">Other</option>');
        }else if(status =="on hold"){
            $('[name=reason]').html('');
            $('[name=reason]').append('<option value="">Please Select</option><option value="Order has been put on hold pending due payment">Order has been put on hold pending due payment</option><option value="Order has been put on hold as material availability/ price can not be confirmed right now">Order has been put on hold as material availability/ price can not be confirmed right now</option><option value="Other">Other</option>');
        }   
    })
</script>
<script>
$(document).on('click', '.delete-sample-item', function () {

    if (!confirm('Are you sure you want to delete this product?')) {
        return;
    }

    var itemId = $(this).data('id');
    $('.loadingDiv').show();
    $.ajax({
        url: "{{ route('admin.sampling.deleteItem') }}",
        type: "POST",
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            item_id: itemId
        },
        success: function (response) {
            location.reload(); // simple & safe
        },
        error: function () {
            alert('Unable to delete item');
        }
    });
});
</script>
<script type="text/javascript">
    $(document).on('change','.urgentOrderItem',function(){
        var orderitemid = $(this).data('orderitemid');
        var value = $( this ).val();
         $.ajax({
            data : {value:value,orderitemid: orderitemid},
            url : '/admin/mark-urgent-sample-item',
            type :'post',
            success:function(resp){
            },
            error:function(){

            }

        })
    })
</script>
<script type="text/javascript">
    $(document).on('click','.clearItemStatus',function(){
        var orderitemid = $(this).data('orderitemid');
        var value = '';
         $.ajax({
            data : {value:value,orderitemid: orderitemid},
            url : '/admin/mark-urgent-sample-item',
            type :'post',
            success:function(resp){
                $('#'+orderitemid+'0').attr("checked" , false );
                $('#'+orderitemid+'1').attr("checked" , false );
                $('#'+orderitemid+'2').attr("checked" , false );
            },
            error:function(){

            }

        })
    })
</script>
@stop