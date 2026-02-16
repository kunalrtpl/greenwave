<div class="form-group mt-20">
    <label class="col-md-12">
        <p class="highlight-sub-label">Product Under Discounts Model</p>
    </label>
    <br><br>
    <div class="col-md-12">
        <table class="table table-bordered" id="discountProductsTable">
            <thead>
                <tr style="background-color: #f9f9f9;">
                    <th>S.No.</th>
                    <th>Product Type</th>
                    <th width="20%">Product Name</th>
                    <th>Disc. (%)</th>
                    <th>MOQ (kg)</th>
                    <th>Special Disc. (%)</th>
                    <th>For Qty (kg)</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @foreach($customerDiscounts as $index => $discount)
                @php
                    $selectedProduct = collect($products)->firstWhere('id', $discount->product_id);
                    $marketPrice = 0;

                    if($selectedProduct && isset($selectedProduct['pricings'][0])) {
                        $marketPrice = $selectedProduct['pricings'][0]['market_price'];
                    }

                    $afterDiscount = $marketPrice - ($marketPrice * $discount->discount / 100);
                    $finalPrice = $afterDiscount - ($afterDiscount * ($discount->special_discount ?? 0) / 100);
                @endphp

                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>Greenwave Textile Products</td>

                    <!-- PRODUCT -->
                    <td>
                        <select name="discount_products[{{ $index + 1 }}][product_id]"
                                class="form-control product-select select2"
                                style="min-width: 250px;" required>
                            <option value="">Select Product</option>

                            @foreach($products as $product)
                                @php
                                    $price = isset($product['pricings'][0]) 
                                            ? $product['pricings'][0]['market_price'] 
                                            : 0;
                                @endphp
                                <option value="{{ $product['id'] }}"
                                        data-price="{{ $price }}"
                                        @if($product['id'] == $discount->product_id) selected @endif>
                                    {{ $product['product_name'] }}
                                </option>
                            @endforeach
                        </select>

                        <small class="text-success market-price-display">
                            @if($marketPrice)
                                Rs. {{ number_format($marketPrice,2) }}
                            @endif
                        </small>
                    </td>

                    <!-- NORMAL DISCOUNT -->
                    <td>
                        <input type="number"
                               name="discount_products[{{ $index + 1 }}][discount]"
                               class="form-control discount-input"
                               step="0.01"
                               min="0"
                               value="{{ $discount->discount }}"
                               placeholder="Enter Discount"
                               required>

                        <small class="text-primary final-price-display">
                            @if($discount->discount)
                                Rs. {{ number_format($afterDiscount,2) }}
                            @endif
                        </small>
                    </td>

                    <!-- MOQ -->
                    <td>
                        <input type="number"
                               name="discount_products[{{ $index + 1 }}][moq]"
                               class="form-control"
                               step="0.001"
                               min="0"
                               value="{{ $discount->moq }}"
                               placeholder="Enter MOQ"
                               required>
                    </td>

                    <!-- SPECIAL DISCOUNT -->
                    <td>
                        <input type="number"
                               name="discount_products[{{ $index + 1 }}][special_discount]"
                               class="form-control special-discount-input"
                               step="0.01"
                               min="0"
                               value="{{ $discount->special_discount ?? '' }}"
                               placeholder="Enter Special Discount">

                        <small class="text-primary special-final-display">
                            @if(!empty($discount->special_discount))
                                Rs. {{ number_format($finalPrice,2) }}
                            @endif
                        </small>
                    </td>

                    <!-- FOR QTY -->
                    <td>
                        <input type="number"
                               name="discount_products[{{ $index + 1 }}][min_qty]"
                               class="form-control"
                               min="0"
                               value="{{ $discount->min_qty ?? '' }}"
                               placeholder="Enter Min Qty">
                    </td>

                    <td>
                        <button type="button"
                                class="btn btn-danger btn-sm"
                                onclick="removeProductRow(this)">
                            <i class="fa fa-times"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <button type="button" class="btn btn-primary" onclick="addDiscountProductRow()">Add New</button>
    </div>
</div>

<script>
let rowIndex = document.querySelectorAll('#discountProductsTable tbody tr').length;

/* ADD ROW */
function addDiscountProductRow() {

    rowIndex++;

    const options = `
        @foreach($products as $product)
            @php
                $price = isset($product['pricings'][0]) 
                        ? $product['pricings'][0]['market_price'] 
                        : 0;
            @endphp
            <option value="{{ $product['id'] }}" data-price="{{ $price }}">
                {{ $product['product_name'] }}
            </option>
        @endforeach
    `;

    const row = document.createElement('tr');

    row.innerHTML = `
        <td>${rowIndex}</td>
        <td>Greenwave Textile Products</td>

        <td>
            <select name="discount_products[${rowIndex}][product_id]"
                    class="form-control product-select select2"
                    style="min-width: 250px;" required>
                <option value="">Select Product</option>
                ${options}
            </select>
            <small class="text-success market-price-display"></small>
        </td>

        <td>
            <input type="number"
                   name="discount_products[${rowIndex}][discount]"
                   class="form-control discount-input"
                   step="0.01"
                   min="0"
                   placeholder="Enter Discount" required>
            <small class="text-primary final-price-display"></small>
        </td>

        <td><input type="number" name="discount_products[${rowIndex}][moq]" class="form-control" step="0.001" min="0" placeholder="Enter MOQ"></td>

        <td>
            <input type="number"
                   name="discount_products[${rowIndex}][special_discount]"
                   class="form-control special-discount-input"
                   step="0.01"
                   min="0"
                   placeholder="Enter Special Discount">
            <small class="text-danger special-final-display"></small>
        </td>

        <td><input type="number" name="discount_products[${rowIndex}][min_qty]" class="form-control" min="0" placeholder="Enter Min Qty"></td>

        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeProductRow(this)"><i class="fa fa-times"></i></button></td>
    `;

    document.querySelector('#discountProductsTable tbody').appendChild(row);
    $(row).find('.select2').select2({ width: 'resolve' });
}

/* REMOVE ROW */
function removeProductRow(button) {
    button.closest('tr').remove();
    updateRowNumbers();
}

function updateRowNumbers() {
    $('#discountProductsTable tbody tr').each(function(i, row) {
        $(row).find('td:first').text(i + 1);
    });
    rowIndex = $('#discountProductsTable tbody tr').length;
}

/* DUPLICATE CHECK */
function validateDuplicateProduct(currentSelect) {

    const selectedVal = parseInt($(currentSelect).val());
    if (!selectedVal) return;

    let isDuplicate = false;

    $('.product-select').not(currentSelect).each(function () {
        if (parseInt($(this).val()) === selectedVal) {
            isDuplicate = true;
        }
    });

    if (isDuplicate) {
        alert('This product is already selected in another row.');
        $(currentSelect).val('').trigger('change');
    }
}

/* CALCULATION */
function calculateRow(row) {

    const selectedOption = $(row).find('.product-select option:selected');
    const price = parseFloat(selectedOption.data('price')) || 0;

    const discount = parseFloat($(row).find('.discount-input').val()) || 0;
    const specialDiscount = parseFloat($(row).find('.special-discount-input').val()) || 0;

    const afterDiscount = price - (price * discount / 100);
    const finalPrice = price - (price * specialDiscount / 100);

    $(row).find('.market-price-display').html(
        price ? `Rs. ${price.toFixed(2)}` : ''
    );

    $(row).find('.final-price-display').html(
        discount ? `Rs. ${afterDiscount.toFixed(2)}` : ''
    );

    $(row).find('.special-final-display').html(
        specialDiscount ? `Rs. ${finalPrice.toFixed(2)}` : ''
    );
}

/* EVENTS */
$(document).on('change', '.product-select', function () {
    validateDuplicateProduct(this);
    calculateRow($(this).closest('tr'));
});

$(document).on('input', '.discount-input', function () {
    calculateRow($(this).closest('tr'));
});

$(document).on('input', '.special-discount-input', function () {
    calculateRow($(this).closest('tr'));
});

$(document).ready(function() {
    $('.select2').select2({ width: 'resolve' });

    $('#discountProductsTable tbody tr').each(function () {
        calculateRow(this);
    });
});
</script>
