@extends('layouts.adminLayout.backendLayout')
@section('content')
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- PAGE HEADER -->
        <div class="page-head">
            <div class="page-title">
                <h1>Sampling Detail</h1>
            </div>
        </div>
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{ url('admin/dashboard') }}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <span>Sampling #{{ $sampleDetails->id }}</span>
            </li>
        </ul>
        <!-- ================= TOP INFO ================= -->
        <div class="row">
            <div class="col-md-6">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption font-blue bold">Sample Request Information</div>
                    </div>
                    <div class="portlet-body">
                        <p><b>Sample ID:</b> {{ $sampleDetails->id }}</p>
                        <p><b>Ref No:</b> {{ $sampleDetails->sample_ref_no_string }}</p>
                        <p><b>Created At:</b> {{ $sampleDetails->created_at->format('d M Y, h:i A') }}</p>
                        <p>
                            <b>Status:</b>
                            <span class="label label-info">{{ ucfirst($sampleDetails->sample_status) }}</span>
                        </p>
                        @if(in_array($sampleDetails->sample_status,['pending','on hold']))
                        <button class="btn btn-xs btn-primary" data-toggle="modal" data-target="#DealerPOstatusModal">
                        Update Status
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption font-green bold">Customer Details</div>
                    </div>
                    <div class="portlet-body">
                        <p><b>Name:</b> {{ $sampleDetails->customer->name ?? '-' }}</p>
                        <p><b>Contact:</b> {{ $sampleDetails->customer->contact_person_name ?? '-' }}</p>
                        <p><b>Mobile:</b> {{ $sampleDetails->customer->mobile ?? '-' }}</p>
                        <p><b>Address:</b> {{ $sampleDetails->customer->address ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- EXECUTIVE -->
            <div class="col-md-6">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption font-purple bold">Executive Details</div>
                    </div>
                    <div class="portlet-body">
                        <p><b>Name:</b> {{ $sampleDetails->user->name }}</p>
                        <p><b>Email:</b> {{ $sampleDetails->user->email }}</p>
                        <p><b>Mobile:</b> {{ $sampleDetails->user->mobile }}</p>
                    </div>
                </div>
            </div>
            <!-- DISPATCH -->
            <div class="col-md-6">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption font-red bold">Dispatch Details</div>
                    </div>
                    <div class="portlet-body">
                        <p><b>Dispatch To:</b> {{ $sampleDetails->dispatch_to }}</p>
                        <p><b>Address:</b> {{ $sampleDetails->dispatch_address }}</p>
                    </div>
                </div>
            </div>
        </div>
        <?php /*
        @include('admin.samplings.free.partials.product_section')
        */?>
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
    row.find('.actual-pack-size').data('selected', null);

    setPackSizes(row);
    calculateRow(row);
});

/* PACK SIZE / PACK COUNT CHANGE */
$(document).on('input change', '.actual-pack-size, .actual-packs', function () {
    calculateRow($(this).closest('tr'));
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
@stop