<br>
@php
    $hasItems   = $sampleDetails->sampleitems->count() > 0;
    $isDisabled = $sampleDetails->sample_edited === 'yes';
@endphp

<style>
.select2-container,
.select2-dropdown {
    z-index: 99999 !important;
}

/* Empty state */
.empty-sample-box {
    padding: 40px 20px;
    border: 2px dashed #d6dce2;
    background: #fafcff;
}

.empty-icon {
    font-size: 64px;
    color: #4b77be;
    margin-bottom: 15px;
}

.empty-title {
    font-weight: 600;
    margin-bottom: 10px;
}
.form-disabled {
    pointer-events: none;
    opacity: 0.6;
    filter: grayscale(30%);
}

/* 🔥 Re-enable interaction ONLY here */
.form-disabled .always-active {
    pointer-events: auto;
    opacity: 1;
    filter: none;
}

/* Optional: make it visually active */
.form-disabled .always-active label,
.form-disabled .always-active input,
.form-disabled .always-active a {
    cursor: pointer;
}
/* Active status section inside disabled form */
.form-disabled .always-active {
    pointer-events: auto;
    opacity: 1;
    filter: none;

    background: #ffffff;
    border: 1px solid #4caf50;
    padding: 8px 10px;
    border-radius: 4px;

    box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.15);
}

</style>

{{-- EMPTY STATE --}}
@if(!$hasItems && !$isDisabled)
<div class="row">
    <div class="col-md-12">
        <div class="portlet light bordered text-center empty-sample-box">
            <div class="portlet-body">
                <i class="fa fa-cubes empty-icon"></i>
                <h3 class="empty-title">No Products Added</h3>
                <p class="text-muted">
                    This sampling request has no products yet.<br>
                    Click below to add the first product.
                </p>
                <button class="btn btn-lg btn-primary"
                    data-toggle="modal"
                    data-target="#addProductModal">
                    <i class="fa fa-plus"></i> Add Product
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- PRODUCT TABLE --}}
@if($hasItems)
<div class="row {{ $isDisabled ? 'form-disabled' : '' }}">
    <div class="col-md-12">
        <div class="portlet blue-hoki box">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cubes"></i> Product Details
                </div>
            </div>

            <div class="portlet-body">
                <form method="post" action="{{ url('/admin/update-sampling-qty') }}">
                    @csrf
                    <input type="hidden" name="sampling_id" value="{{ $sampleDetails->id }}">

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="5%">Type</th>
                                    <th width="15%">Product</th>
                                    <th width="10%">Pack Size</th>
                                    <th width="10%">No of Packs</th>
                                    <th width="10%">Qty (Kg)</th>
                                    <th width="10%">Value (₹)</th>
                                    <th width="10%">Transport</th>
                                    <th width="15%">Comments</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($sampleDetails->sampleitems as $item)
                                @if($item->requested_from =="user")
                                    {{-- REQUESTED ROW --}}
                                    <tr style="background:#f4f8fb;">
                                        <td><span class="label label-info">Requested</span></td>
                                        <td>{{ $item->requested_product->product_name ?? '-' }}</td>
                                        <td>{{ $item->pack_size }}</td>
                                        <td>{{ $item->no_of_packs }}</td>
                                        <td>{{ $item->qty }} Kg</td>
                                        <td>—</td>
                                        <td>—</td>
                                        <td>{{ $item->remarks }}</td>
                                    </tr>
                                @endif
                                {{-- APPROVED ROW --}}
                                <tr>
                                    @if($item->requested_from =="user")
                                        <td><span class="label label-success">Approved</span></td>
                                    @elseif($item->requested_from =="admin")
                                        <td>
                                            <span class="label label-danger">Added By Admin</span>
                                        <br><br>
                                        <a href="javascript:void(0);"
                                           class="btn btn-xs btn-danger delete-sample-item"
                                           data-id="{{ $item->id }}">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                        </td>
                                    @endif

                                    <td>
                                        <select name="product_ids[]"
                                            class="form-control select2 approved-product"
                                            required>
                                            @foreach($products as $product)
                                            <option value="{{ $product->id }}"
                                                data-form="{{ strtolower($product->physical_form) }}"
                                                data-price="{{ $product->dealer_price }}"
                                                {{ $product->id == $item->product_id ? 'selected' : '' }}>
                                                {{ $product->product_name }}
                                            </option>
                                            @endforeach
                                        </select>

                                        <br>
                                        <small class="text-muted">
                                            Dealer Price: ₹
                                            <strong class="dealer-price">
                                                {{ optional($products->firstWhere('id',$item->product_id))->dealer_price ?? 0 }}
                                            </strong>
                                        </small>

                                        <input type="hidden" name="dealer_prices[]" class="hiddenDealerPrice"
                                            value="{{ optional($products->firstWhere('id',$item->product_id))->dealer_price ?? 0 }}">
                                        <input type="hidden" name="item_ids[]" value="{{ $item->id }}">

                                        <div class="always-active">
                                            <?php $statuses = array('On Hold','Cancel', 'Urgent'); ?>
                                            @foreach($statuses as $skey => $status)
                                                <div class="form-check">
                                                    <input
                                                        data-orderitemid="{{ $item->id }}"
                                                        class="form-check-input urgentOrderItem"
                                                        type="radio"
                                                        name="orderitemstatus[{{ $item->id }}]"
                                                        id="{{ $item->id }}{{ $skey }}"
                                                        value="{{ $status }}"
                                                        @if($item->item_action == $status) checked @endif
                                                    >
                                                    <label class="form-check-label" for="{{ $item->id }}{{ $skey }}">
                                                        {{ $status }}
                                                    </label>
                                                </div>
                                            @endforeach

                                            <a data-orderitemid="{{ $item->id }}"
                                               class="btn btn-xs btn-danger clearItemStatus"
                                               href="javascript:;">
                                                Clear
                                            </a>
                                        </div>

                                    </td>

                                    <td>
                                        <select name="actual_pack_sizes[]"
                                            class="form-control actual-pack-size"
                                            data-selected="{{ $item->actual_pack_size }}"
                                            required></select>
                                    </td>

                                    <td>
                                        <input type="number"
                                            name="actual_no_of_packs[]"
                                            class="form-control actual-packs"
                                            min="1"
                                            value="{{ $item->actual_no_of_packs }}"
                                            required>
                                    </td>

                                    <td>
                                        <span class="actual-qty-text">0</span> Kg
                                        <input type="hidden"
                                            name="actual_qtys[]"
                                            class="form-control actual-qty"
                                            value="{{ $item->actual_qty }}">
                                    </td>

                                    <td>₹ <span class="row-value">0</span></td>

                                    <td>
                                        <select name="required_through[]" class="form-control" required>
                                            <option value="">Select</option>
                                            <option value="courier" {{ $item->required_through == 'courier' ? 'selected' : '' }}>Courier</option>
                                            <option value="transport" {{ $item->required_through == 'transport' ? 'selected' : '' }}>Transport</option>
                                        </select>
                                    </td>

                                    <td>
                                        <textarea class="form-control"
                                            placeholder="Enter Remarks"
                                            name="comments[]">{{ $item->comments }}</textarea>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row">
                        <div class="col-xs-6 text-left">
                            @if(!$isDisabled)
                                <button type="button" class="btn btn-sm btn-primary"
                                        data-toggle="modal"
                                        data-target="#addProductModal">
                                    <i class="fa fa-plus"></i> Add Product
                                </button>
                            @endif
                        </div>

                        <div class="col-xs-6 text-right">
                            @if($isDisabled)
                                <b>Approved</b>
                            @else
                                <button class="btn btn-success"
                                        onclick="return confirm('Are you sure you want to approve this sample request?')">
                                    Submit &amp; Approve
                                </button>
                            @endif
                        </div>
                    </div>


                </form>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ADD PRODUCT MODAL --}}
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="{{ url('/admin/sampling/add-item') }}">
                @csrf
                <input type="hidden" name="sampling_id" value="{{ $sampleDetails->id }}">

                <div class="modal-header">
                    <h4 class="modal-title">Add Product</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>Product</label>
                        <select name="product_id"
                            class="form-control select2 add-product-select"
                            required>
                            <option value="">Select Product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}"
                                    data-form="{{ strtolower($product->physical_form) }}">
                                    {{ $product->product_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Pack Size</label>
                        <select name="pack_size"
                            class="form-control add-pack-size"
                            required>
                            <option value="">Please Select</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>No of Packs</label>
                        <input type="number"
                            name="no_of_packs"
                            class="form-control"
                            min="1"
                            placeholder="Enter No. of Packs"
                            required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>
