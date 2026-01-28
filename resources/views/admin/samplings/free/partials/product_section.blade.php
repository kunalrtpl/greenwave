<div class="row">
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
                                            <th width="10%">Type</th>
                                            <th width="25%">Product</th>
                                            <th width="15%">Pack Size</th>
                                            <th width="15%">No of Packs</th>
                                            <th width="15%">Qty (Kg)</th>
                                            <th width="10%">Value (₹)</th>
                                            <th width="10%">Transport</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sampleDetails->sampleitems as $item)
                                        <!-- ================= REQUESTED ROW ================= -->
                                        <tr style="background:#f4f8fb;">
                                            <td>
                                                <span class="label label-info">Requested</span>
                                            </td>
                                            <td>
                                                {{ $item->requested_product->product_name ?? '-' }}
                                            </td>
                                            <td>
                                                {{ $item->pack_size }}
                                            </td>
                                            <td>
                                                {{ $item->no_of_packs }}
                                            </td>
                                            <td>
                                                {{ $item->qty }} Kg
                                            </td>
                                            <td>—</td>
                                            <td>—</td>
                                        </tr>
                                        <!-- ================= APPROVED ROW ================= -->
                                        <tr>
                                            <td>
                                                <span class="label label-success">Approved</span>
                                            </td>
                                            <!-- PRODUCT -->
                                            <td>
                                                <select name="product_ids[]"
                                                    class="form-control select2 approved-product">
                                                @foreach($products as $product)
                                                <option value="{{ $product->id }}"
                                                data-form="{{ $product->physical_form }}"
                                                data-price="{{ $product->dealer_price }}"
                                                {{ $product->id == $item->product_id ? 'selected' : '' }}>
                                                {{ $product->product_name }}
                                                </option>
                                                @endforeach
                                                </select>
                                                <small class="text-muted">
                                                Dealer Price: ₹
                                                <strong class="dealer-price">
                                                {{ optional($products->firstWhere('id',$item->product_id))->dealer_price ?? 0 }}
                                                </strong>
                                                </small>
                                                <input type="hidden" name="item_ids[]" value="{{ $item->id }}">
                                            </td>
                                            <!-- PACK SIZE -->
                                            <td>
                                                <select name="actual_pack_sizes[]"
                                                    class="form-control actual-pack-size"
                                                    data-selected="{{ $item->actual_pack_size }}"
                                                    required>
                                                </select>
                                            </td>
                                            <!-- NO OF PACKS -->
                                            <td>
                                                <input type="number"
                                                    name="actual_no_of_packs[]"
                                                    class="form-control actual-packs"
                                                    min="1"
                                                    value="{{ $item->actual_no_of_packs }}"
                                                    required>
                                            </td>
                                            <!-- QTY -->
                                            <td>
                                                <input type="text"
                                                    name="actual_qtys[]"
                                                    class="form-control actual-qty"
                                                    value="{{ $item->actual_qty }}"
                                                    readonly>
                                            </td>
                                            <!-- VALUE -->
                                            <td>
                                                ₹ <span class="row-value">0</span>
                                            </td>
                                            <!-- TRANSPORT -->
                                            <td>
                                                <select name="required_through[]" class="form-control" required>
                                                    <option value="">Select</option>
                                                    <option value="courier" {{ $sampleDetails->required_through == 'courier' ? 'selected' : '' }}>
                                                    Courier
                                                    </option>
                                                    <option value="transport" {{ $sampleDetails->required_through == 'transport' ? 'selected' : '' }}>
                                                    Transport
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-right">
                                <button class="btn btn-success"
                                    onclick="return confirm('Are you sure you want to approve this sample request?')">
                                Submit & Approve
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>